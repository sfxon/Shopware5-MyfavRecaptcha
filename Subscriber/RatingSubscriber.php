<?php

namespace MyfavRecaptcha\Subscriber;

use Enlight\Event\SubscriberInterface;
use Enlight_Config;
use Enlight_Controller_Action;
use Enlight_Event_EventArgs;
use Enlight_View_Default;
use GuzzleHttp\ClientInterface;
use MyfavRecaptcha\Services\ReCaptchaService;
use Shopware\Components\HttpClient\GuzzleFactory;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * Class Rating
 * @package MittwaldSecurityTools
 * @author Mittwald CM Service GmbH & Co. KG <opensource@mittwald.de>
 */
class RatingSubscriber implements SubscriberInterface
{
    /** @var ContainerInterface */
    private $container;

    /** @var ClientInterface */
    protected $client;

    /** @var Enlight_Config */
    protected $pluginConfig;

    /** @var ReCaptchaService */
    protected $reCaptchaService;

    /** @var mixed */
    protected $captchaError;

    /**
     * Detail constructor.
     * @param ContainerInterface $container
     * @param GuzzleFactory $guzzleFactory
     */
    public function __construct(
        ContainerInterface $container,
        GuzzleFactory $guzzleFactory
    ) {
        $this->container = $container;
        $this->client = $guzzleFactory->createClient();
        $this->pluginConfig = $this->container->get('shopware.plugin.config_reader')->getByPluginName('MyfavRecaptcha', $this->container->get('shop'));
    }

    /**
     * {@inheritdoc}
     */
    public static function getSubscribedEvents()
    {
        return [
            'Enlight_Controller_Action_PreDispatch_Frontend_Detail' => 'onDetailRating'
        ];
    }

    public function onDetailRating(\Enlight_Event_EventArgs $args) {
        $action = Shopware()->Container()->get('front')->Request();
        $action = $action->get('action');
        
        if($action == 'rating') {
            $this->onDetailRatingAction($args);
        }
    }


    /**
     * Rating action method
     * We need to replace the ratingAction Method in order to validate the reCaptcha.
     *
     * @param \Enlight_Hook_HookArgs $args
     * @throws \Exception
     * @author Mittwald CM Service GmbH & Co. KG <opensource@mittwald.de>
     * @return mixed
     */
    public function onDetailRatingAction(\Enlight_Event_EventArgs  $args)
    {
        if(isset($_POST['myfavAlreadyProcessed'])) {
            return;
        }
        
        /** @var Enlight_Controller_Action $controller */
        $controller = $args->getSubject();

        /** @var Enlight_View_Default $view */
        $view = $controller->View();

        /** @var Enlight_Controller_Request_Request $request */
        $request = $controller->Request();

        $id = (int) $request->sArticle;
        
        if (empty($id)) {
            return $controller->forward('error');
        }

        $product = Shopware()->Modules()->Articles()->sGetArticleNameByArticleId($id);
        if (empty($product)) {
            return $controller->forward('error');
        }

        /** Captcha Validation - START */
        //if (is_null($request->getParam('sConfirmation'))) {
        if ($this->pluginConfig['showRecaptchaForRatingForm']) {
            $view->assign('myfavRecaptcha', $this->pluginConfig);

            $this->reCaptchaService = $this->container->get('myfav_recaptcha.services.recaptcha_service');
            $reCaptcha = $this->reCaptchaService->initReCaptcha($controller, $this->pluginConfig, $this->client);

            if ($reCaptcha === 'error') {
                $this->captchaError = true;
                $sErrorFlag['sCaptcha'] = true;
            } else {
                $lastScore = $this->reCaptchaService->getLastScore();

                if($lastScore < 0.9) {
                    $this->captchaError = true;
                    $sErrorFlag['sCaptcha'] = true;
                } else {
                    $this->captchaError = false;
                }
            }

            $request->setParam('sysg_rating_captchaError', $this->captchaError);
        }
        //}
        /** Captcha Validation - END */

        $voteConfirmed = false;

        if ($hash = $request->getParam('sConfirmation')) {
            $getVote = Shopware()->Db()->fetchRow('
                SELECT * FROM s_core_optin WHERE hash = ?
            ', [$hash]);
            if (!empty($getVote['data'])) {
                Shopware()->System()->_POST = unserialize($getVote['data'], ['allowed_classes' => false]);
                $voteConfirmed = true;
                Shopware()->Db()->query('DELETE FROM s_core_optin WHERE hash = ?', [$hash]);
                
                unset(Shopware()->Config()->sOPTINVOTE);
                Shopware()->Modules()->Articles()->sSaveComment($id);

                die('Vielen Dank für Ihre Bewertung. Sie haben die Bewertung hiermit erfolgreich bestätigt. Wir werden diese prüfen und in Kürze freischalten.');
            }
        }

        if (empty(Shopware()->System()->_POST['sVoteSummary'])) {
            $sErrorFlag['sVoteSummary'] = true;
        }

        if (!$voteConfirmed) {
            /** @var \Shopware\Components\Captcha\CaptchaValidator $captchaValidator */
            $captchaValidator = $this->container->get('shopware.captcha.validator');

            if (!$captchaValidator->validate($request)) {
                $sErrorFlag['sCaptcha'] = true;
            }
        }

        $validator = $this->container->get('validator.email');
        if (!empty(Shopware()->Config()->sOPTINVOTE)
            && (empty(Shopware()->System()->_POST['sVoteMail'])
                || !$validator->isValid(Shopware()->System()->_POST['sVoteMail']))
        ) {
            $sErrorFlag['sVoteMail'] = true;
        }

        if (empty($sErrorFlag)) {
            if (!empty(Shopware()->Config()->sOPTINVOTE)
                && !$voteConfirmed && empty(Shopware()->Session()->sUserId)
            ) {
                /*
                Wir lassen den Standard-Controller die Arbeit machen.
                Dieser Controller hier hat lediglich im Pre-Dispatch
                geprüft, ob die Anfrage valid ist.
                */
            } else {
                unset(Shopware()->Config()->sOPTINVOTE);
                Shopware()->Modules()->Articles()->sSaveComment($id);
            }
        } else {
            $view->assign('sFormData', Shopware()->System()->_POST->toArray());
            $view->assign('sErrorFlag', $sErrorFlag);
            die('invalid form');
        }

        $view->assign('sAction', 'ratingAction');

        $_POST['myfavAlreadyProcessed'] = true;
    }


    /**
     * @param Enlight_Event_EventArgs $args
     * @throws \Exception
     */
    /*
    public function afterDetailRatingAction(Enlight_Event_EventArgs $args)
    {

        /** @var Enlight_Controller_Action $controller *//*
        $controller = $args->get('subject');

        /** @var Enlight_View_Default $view *//*
        $view = $controller->View();

        /** @var Enlight_Controller_Request_Request $request *//*
        $request = $controller->Request();

        if ($request->getActionName() !== 'index') {
            return;
        }

        if ($this->pluginConfig['showRecaptchaForRatingForm']) {
            $view->assign('myfavRecaptcha', $this->pluginConfig);

            if ($request->getParam('sysg_rating_captchaError') === true) {
                $this->captchaError = true;
                $sErrorFlag['sCaptcha'] = true;
                $view->assign('sErrorFlag', $sErrorFlag);
            } else {
                $this->captchaError = false;
            }
        }
    }
    */
}
