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
 * Class Account
 * This subscriber handles reCaptcha on the detail-page in-stock-notification form.
 * @package MyfavRecaptcha
 * @author Mittwald CM Service GmbH & Co. KG <opensource@mittwald.de>
 * @author Mindfav Software, Steve Krämer <steve@mindfav.com>
 */
class AccountSubscriber implements SubscriberInterface
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
    )
    {
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
            'Enlight_Controller_Action_PreDispatch_Frontend_Account' => ['onPreDispatchAccount', 999],
            'Enlight_Controller_Action_PostDispatchSecure_Frontend_Account' => ['onPostDispatchAccount', 999],
        ];
    }

    /**
     * @param Enlight_Event_EventArgs $args
     * @throws \Exception
     * This is called when the Form is submitted
     */
    public function onPreDispatchAccount(\Enlight_Event_EventArgs $args)
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

        if ($this->pluginConfig['showRecaptchaForPasswordreset']) {
            $email = $request->getParam('email');
            $view->assign('myfavRecaptcha', $this->pluginConfig);

            $this->reCaptchaService = $this->container->get('myfav_recaptcha.services.recaptcha_service');
            $reCaptcha = $this->reCaptchaService->initReCaptcha($controller, $this->pluginConfig, $this->client);

            if ($reCaptcha === 'error') {
                $request->setParam('sysg_email_storage', $email);
                $request->setParam('email', 0);
                $this->captchaError = true;
            } else {
                $this->captchaError = false;
            }

            $request->setParam('sysg_passwordreset_captchaError', $this->captchaError);
            $view->assign('reCaptchaErrorPasswordreset', $this->captchaError);
        }
    }

    /**
     * @param Enlight_Event_EventArgs $args
     * @throws \Exception
     * This is called when the Page is loaded
     */
    public function onPostDispatchAccount(\Enlight_Event_EventArgs $args)
    {
        /** @var Enlight_Controller_Action $controller */

        $controller = $args->get('subject');

        /** @var Enlight_View_Default $view */
        $view = $controller->View();

        /** @var Enlight_Controller_Request_Request $request */
        $request = $controller->Request();

        if ($request->getActionName() !== 'password') {
            return;
        }

        if ($this->pluginConfig['showRecaptchaForPasswordreset']) {
            $view->assign('myfavRecaptcha', $this->pluginConfig);

            if ($request->getParam('sysg_passwordreset_captchaError') === true) {
                $email = $request->getParam('sysg_email_storage');
                $this->captchaError = true;
                if ($email) {
                    $view->assign('sErrorMessages', null);
                    $view->assign('sysgEmailStorage', $email);
                }
            } else {
                $this->captchaError = false;
            }

            $view->assign('reCaptchaErrorPasswordreset', $this->captchaError);
        }
    }
}
