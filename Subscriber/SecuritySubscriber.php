<?php

namespace MyfavRecaptcha\Subscriber;

use Enlight\Event\SubscriberInterface;
use Shopware\Components\Model\ModelManager;
use Shopware_Components_TemplateMail;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\HttpFoundation\Request;
use DateTime;
use Doctrine\Common\Collections\ArrayCollection;
use Enlight_Components_Db_Adapter_Pdo_Mysql;
use Enlight_Config;
use Enlight_Controller_Action;
use Enlight_Event_EventArgs;
use Enlight_Exception;
use Enlight_Hook_HookArgs;
use Exception;
use GuzzleHttp\ClientInterface;
use Shopware\Components\DependencyInjection\Container;
use Shopware\Components\HttpClient\GuzzleFactory;
use Shopware_Components_Config;
use Shopware_Components_Snippet_Manager;
use Shopware_Controllers_Backend_Login;
use Shopware_Controllers_Frontend_Forms;
use Shopware_Controllers_Frontend_Newsletter;
use Shopware_Controllers_Frontend_Register;
use Zend_Db_Statement_Exception;

/**
 * Class SecuritySubscriber
 * @package MyfavRecaptcha
 * @author Mittwald CM Service GmbH & Co. KG <opensource@mittwald.de>
 * @author Mindfav Software, Steve Krämer <steve@mindfav.com>
 */
class SecuritySubscriber implements SubscriberInterface
{

    /** @var ContainerInterface */
    private $container;

    /** @var ModelManager */
    protected $modelManager;

    /** @var Enlight_Config */
    protected $pluginConfig;

    /** @var Shopware_Components_Config */
    protected $shopConfig;

    /** @var Enlight_Components_Db_Adapter_Pdo_Mysql */
    protected $db;

    /** @var string */
    protected $pluginPath;

    /** @var ClientInterface */
    protected $client;

    /** @var Shopware_Components_Snippet_Manager */
    protected $snippets;

    /** @var bool */
    protected $captchaChecked = false;

    /** @var ReCaptchaService */
    protected $reCaptchaService;
	
	    /** @var string shopwareVersion */
    protected $shopwareVersion;

    /**
     * Detail constructor.
     * @param ContainerInterface $container
     * @param Enlight_Components_Db_Adapter_Pdo_Mysql $db
     * @param Shopware_Components_TemplateMail $templateMail
     * @param GuzzleFactory $guzzleFactory
     * @param string $pluginPath
     * @param Shopware_Components_Snippet_Manager $snippets
     */
    public function __construct(
        ContainerInterface $container,
        Enlight_Components_Db_Adapter_Pdo_Mysql $db,
        GuzzleFactory $guzzleFactory,
        $pluginPath,
        \Shopware_Components_Snippet_Manager $snippets
    )
    {
        $this->container = $container;
        $this->modelManager = $this->container->get('models');
		$this->pluginConfig = $this->container->get('shopware.plugin.config_reader')->getByPluginName('MyfavRecaptcha', $this->container->get('shop'));

        $this->shopConfig = $this->container->get('config');
        $this->db = $db;
        $this->client = $guzzleFactory->createClient();
        $this->snippets = $snippets;
        $this->pluginPath = $pluginPath;
        $this->reCaptchaService = $this->container->get('myfav_recaptcha.services.recaptcha_service');
        $this->shopwareVersion = Shopware()->Config()->get('Version');
    }

    /**
     * {@inheritdoc}
     */
    public static function getSubscribedEvents()
    {
        return [
            'Enlight_Controller_Action_PostDispatchSecure_Frontend' => 'addNewsletterRecaptcha',
            'Enlight_Controller_Action_PostDispatchSecure_Frontend_Newsletter' => 'addNewsletterRecaptcha',
            'Enlight_Controller_Action_PostDispatchSecure_Frontend_Register' => 'onFrontendRegister',
            /*'Enlight_Controller_Action_PostDispatchSecure_Frontend_Account' => 'onFrontendAccount',
            'Enlight_Controller_Action_PostDispatch_Frontend_Register' => 'enhanceAjaxPasswordValidation',*/
            'Enlight_Controller_Action_Frontend_Register_saveRegister' => 'onSaveRegister',
            'Enlight_Controller_Action_Frontend_Newsletter_index' => 'onSaveNewsletter',
        ];
    }


    /**
     * replacement for newsletter index action.
     * will check the google reCAPTCHA and pipe data to original action, if captcha is valid
     * or captcha validation is not activated.
     *
     * @param Enlight_Event_EventArgs $args
     * @return bool|null
     * @throws Exception
     */
    public function onSaveNewsletter(Enlight_Event_EventArgs $args)
    {
        // @var Shopware_Controllers_Frontend_Newsletter $controller
        $controller = $args->get('subject');

        $postData = $controller->Request()->getPost();

        if (isset($controller->Request()->sUnsubscribe)) {
            return null;
        }

        $system = $this->container->get('system');
        $controller->View()->_POST = $system->_POST->toArray();

        if (!isset($system->_POST['newsletter'])) {
            return null;
        }

        if ($this->pluginConfig['showRecaptchaForNewsletter'] && !$this->captchaChecked && $this->pluginConfig['recaptchaSecretKey']) {
            $gCaptchaResponse = isset($postData['g-recaptcha-response']) ? $postData['g-recaptcha-response'] : false;
            $responseData = null;

            if (version_compare($this->shopwareVersion, "5.7.0", "<")) {
                
                $responseData = $this->reCaptchaService->getResponseDataLegacy($this->client, $this->pluginConfig, $gCaptchaResponse);
            } else {
                
                $responseData = $this->reCaptchaService->getResponseData($this->client, $this->pluginConfig, $gCaptchaResponse);
            }

            $this->captchaChecked = true;

            if (!$responseData['success']) {
                if (is_array($responseData['error-codes']) &&
                    (in_array('missing-input-secret', $responseData['error-codes']) ||
                        in_array('invalid-input-secret', $responseData['error-codes']))
                ) {
                    $this->logger->error('reCAPTCHA', 'secret is not valid.');
                }

                $controller->View()->sStatus = ['code' => 5, 'message' => $this->snippets->getNamespace('plugins/MyfavRecaptcha/reCAPTCHA')
                    ->get('captchaFailed', 'Captcha-Überprüfung fehlgeschlagen', true)];
                return true;
            }
        }

        return null;
    }

    /**
     * replacement for save register. will check the google reCAPTCHA and pipe data to original action, if captcha is valid
     * or captcha validation is not activated.
     *
     * @param Enlight_Event_EventArgs $args
     * @return bool|null
     * @throws Exception
     */
    public function onSaveRegister(Enlight_Event_EventArgs $args)
    {
        // @var Shopware_Controllers_Frontend_Register $controller
        $controller = $args->get('subject');

        $postData = $controller->Request()->getPost();

        $errors = array(
            'personal' => array()
        );

        if ($this->pluginConfig['showRecaptchaForUserRegistration'] && !$this->captchaChecked && $this->pluginConfig['recaptchaSecretKey']) {
            $gCaptchaResponse = isset($postData['g-recaptcha-response']) ? $postData['g-recaptcha-response'] : false;
            $responseData = null;

            if (version_compare($this->shopwareVersion, "5.7.0", "<")) {
            	// Legacy-Handling
                // Shopware Versions below 5.7.0: use old guzzle-factory from Symfony 3.4
                $responseData = $this->reCaptchaService->getResponseDataLegacy($this->client, $this->pluginConfig, $gCaptchaResponse);
            } else {
                // New-Handling
                // Shopware 5.7.0 or greater: use new guzzle-factory from Symfony 4.4
                $responseData = $this->reCaptchaService->getResponseData($this->client, $this->pluginConfig, $gCaptchaResponse);
            }

            if (!$responseData['success']) {
                if (is_array($responseData['error-codes']) &&
                    (in_array('missing-input-secret', $responseData['error-codes']) ||
                        in_array('invalid-input-secret', $responseData['error-codes']))
                ) {
                    $this->logger->error('reCAPTCHA', 'secret is not valid.');
                }

                $errors['personal']['captcha'] = $this->snippets->getNamespace('plugins/MyfavRecaptcha/reCAPTCHA')
                    ->get('captchaFailed', 'Captcha-Überprüfung fehlgeschlagen', true);

            }

            $this->captchaChecked = true;
        }
		
        if (count($errors['personal']) > 0) {
            $controller->View()->assign('errors', $errors);
            $controller->View()->assign($postData);
            $controller->forward('index');
            return true;
        }

        return null;
    }



    /**
     * add our frontend templates for password strength
     *
     * @param Enlight_Event_EventArgs $args
     */
    /*
	public function onFrontendAccount(Enlight_Event_EventArgs $args)
    {
        if (!$this->pluginConfig['showPasswordStrengthForgotPasswordReset'] &&
            !$this->pluginConfig['showPasswordStrengthAccountPasswordReset']
        ) {
            return;
        }

        
        // @var Enlight_Controller_Action $controller
        $controller = $args->get('subject');
        $view = $controller->View();
        $view->assign('mstConfig', $this->pluginConfig);
    }


    /**
     * add our frontend templates for password strength and reCAPTCHA if necessary
     *
     * @param Enlight_Event_EventArgs $args
     */
    public function onFrontendRegister(Enlight_Event_EventArgs $args)
    {
        if (!$this->pluginConfig['showPasswordStrengthForUserRegistration'] &&
            !$this->pluginConfig['showRecaptchaForUserRegistration']) {
            return;
        }

        // @var Enlight_Controller_Action $controller
        $controller = $args->get('subject');
        $view = $controller->View();
        $view->assign('myfavRecaptcha', $this->pluginConfig);
    }


    /**
     * add our frontend templates for reCAPTCHA if necessary
     *
     * @param Enlight_Event_EventArgs $args
     */
	public function addNewsletterRecaptcha(Enlight_Event_EventArgs $args)
    {
        if (!$this->pluginConfig['showRecaptchaForNewsletter'] || !$this->pluginConfig['recaptchaAPIKey']) {
            return;
        }

        // @var Enlight_Controller_Action $controller
        $controller = $args->get('subject');
        $view = $controller->View();
        $view->assign('myfavRecaptcha', $this->pluginConfig);
    }
}
