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
 * Class Forms
 * This subscriber handles reCaptcha on the detail-page in-stock-notification form.
 * @package MyfavRecaptcha
 * @author Mittwald CM Service GmbH & Co. KG <opensource@mittwald.de>
 * @author Mindfav Software, Steve Krämer <steve@mindfav.com>
 */
class FormsSubscriber implements SubscriberInterface
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
     * Notification constructor
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
            'Enlight_Controller_Action_PreDispatch_Frontend_Forms' => ['onPreDispatchForms',10],
            'Enlight_Controller_Action_PostDispatchSecure_Frontend_Forms' => ['onPostDispatchForms',10],
        ];
    }


    /**
     * @param Enlight_Event_EventArgs $args
     * @throws \Exception
     */
    public function onPreDispatchForms(\Enlight_Event_EventArgs $args)
    {
        /** @var Enlight_Controller_Action $controller */
        $controller = $args->get('subject');

        /** @var Enlight_View_Default $view */
        $view = $controller->View();

        /** @var Enlight_Controller_Request_Request $request */
        $request = $controller->Request();

        if (!$request->isPost()) {
            return;
        }

        if ($this->pluginConfig['showRecaptchaForForms']) {
            $view->assign('myfavRecaptcha', $this->pluginConfig);
            
            $this->reCaptchaService = $this->container->get('myfav_recaptcha.services.recaptcha_service');
            $reCaptcha = $this->reCaptchaService->initReCaptcha($controller, $this->pluginConfig, $this->client);

            if ($reCaptcha === 'error') {
                $request->setParam('Submit', 0);
                $this->captchaError = true;
            } else {
                $this->captchaError = false;
            }

            $request->setParam('sysg_forms_captchaError', $this->captchaError);
            $view->assign('reCaptchaErrorForms', $this->captchaError);
        }
    }

    
    /**
     * @param Enlight_Event_EventArgs $args
     * @throws \Exception
     */
    public function onPostDispatchForms(\Enlight_Event_EventArgs $args)
    {
        /** @var Enlight_Controller_Action $controller */
        $controller = $args->get('subject');

        /** @var Enlight_View_Default $view */
        $view = $controller->View();

        /** @var Enlight_Controller_Request_Request $request */
        $request = $controller->Request();


        if ($request->getActionName() !== 'index') {
            return;
        }

        if ($this->pluginConfig['showRecaptchaForForms']) {
            $view->assign('myfavRecaptcha', $this->pluginConfig);

            if ($request->getParam('sysg_forms_captchaError') === true) {
                $this->captchaError = true;
            } else {
                $this->captchaError = false;
            }

            $view->assign('reCaptchaErrorForms', $this->captchaError);
        }
    }
}
