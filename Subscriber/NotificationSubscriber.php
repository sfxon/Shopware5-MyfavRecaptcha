<?php

namespace MyfavRecaptcha\Subscriber;

use Enlight\Event\SubscriberInterface;
use Enlight_Config;
use Enlight_Controller_Action;
use Enlight_Event_EventArgs;
use Enlight_View_Default;
use GuzzleHttp\ClientInterface;
use Shopware\Components\HttpClient\GuzzleFactory;
use Symfony\Component\DependencyInjection\ContainerInterface;
use MyfavRecaptcha\Services\ReCaptchaService;

/**
 * Class Notification
 * This subscriber handles reCaptcha on the detail-page in-stock-notification form.
 * @package MyfavRecaptcha
 * @author Mittwald CM Service GmbH & Co. KG <opensource@mittwald.de>
 * @author Mindfav Software, Steve Krämer <steve@mindfav.com>
 */
class NotificationSubscriber implements SubscriberInterface
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
            'Enlight_Controller_Action_PreDispatch_Frontend_Detail' => ['beforeDetailnotifyAction',10],
            'Enlight_Controller_Action_PostDispatchSecure_Frontend_Detail' => ['afterDetailnotifyAction',10],
        ];
    }


    /**
     * @param Enlight_Event_EventArgs $args
     * @throws \Exception
     */
    public function beforeDetailnotifyAction(\Enlight_Event_EventArgs $args)
    {
        /** @var Enlight_Controller_Action $controller */
        $controller = $args->get('subject');

        /** @var Enlight_View_Default $view */
        $view = $controller->View();

        /** @var Enlight_Controller_Request_Request $request */
        $request = $controller->Request();

        if ($request->getActionName() !== 'notify') {
            return;
        }

        if ($this->pluginConfig['showRecaptchaForInStockForm']) {
            $view->assign('myfavRecaptcha', $this->pluginConfig);
            
            $this->reCaptchaService = $this->container->get('myfav_recaptcha.services.recaptcha_service');
            $reCaptcha = $this->reCaptchaService->initReCaptcha($controller, $this->pluginConfig, $this->client);

            if ($reCaptcha === 'error') {
                $this->captchaError = true;
                $request->setParam('notifyOrdernumber', '');
            } else {
                $this->captchaError = false;
            }

            $request->setParam('sysg_notification_captchaError', $this->captchaError);
            $view->assign('reCaptchaErrorNotification', $this->captchaError);
        }
    }

    
    /**
     * @param Enlight_Event_EventArgs $args
     * @throws \Exception
     */
    public function afterDetailnotifyAction(\Enlight_Event_EventArgs $args)
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

        if ($this->pluginConfig['showRecaptchaForInStockForm']) {
            $view->assign('myfavRecaptcha', $this->pluginConfig);

            if ($request->getParam('sysg_notification_captchaError') === true) {
                $this->captchaError = true;
            } else {
                $this->captchaError = false;
            }

            $view->assign('reCaptchaErrorNotification', $this->captchaError);
        }
    }
}
