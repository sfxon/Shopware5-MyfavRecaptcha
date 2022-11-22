<?php

namespace MyfavRecaptcha\Services;

use Enlight_Config;
use Enlight_Controller_Action;
use Exception;
use GuzzleHttp\ClientInterface;
use MyfavRecaptcha\Setup\ConstantsProvider;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Shopware_Components_Snippet_Manager;

/**
 * Class ReCaptchaService
 * @package MyfavRecaptcha
 * @author Mittwald CM Service GmbH & Co. KG <opensource@mittwald.de>
 * @author Mindfav Software, Steve Krämer <steve@mindfav.com>
 */
class ReCaptchaService
{
    /** @var ContainerInterface */
    private $container;

    /** @var Shopware_Components_Snippet_Manager */
    protected $snippets;

    /** @var bool */
    protected $captchaChecked = false;
    private $lastScore = 0;

    /**
     * Detail constructor.
     * @param ContainerInterface $container
     * @param Shopware_Components_Snippet_Manager $snippets
     */
    public function __construct(
        ContainerInterface $container,
        \Shopware_Components_Snippet_Manager $snippets
    )
    {
        $this->container = $container;
        $this->snippets = $snippets;
    }

    /**
     * @param Enlight_Controller_Action $controller
     * @param Enlight_Config $pluginConfig
     * @param ClientInterface $client
     * @return mixed
     * @throws Exception
     * @author Mittwald CM Service GmbH & Co. KG <opensource@mittwald.de>
     * @author Mindfav Software, Steve Krämer <steve@mindfav.com>
     */
    public function initReCaptcha($controller, $pluginConfig, $client)
    {
        if (!$this->captchaChecked && $pluginConfig['recaptchaSecretKey']) {

            /** init VARS */
            $postData = $controller->Request()->getPost();
            $sVersion = Shopware()->Config()->get('Version');
            $gCaptchaResponse = isset($postData['g-recaptcha-response']) ? $postData['g-recaptcha-response'] : false;
            $responseData = null;

            if (version_compare($sVersion, "5.7.0", "<")) {
                /**
                 * Legacy-Handling
                 * Shopware Versions below 5.7.0: use old guzzle-factory from Symfony 3.4
                 * */
                $responseData = $this->getResponseDataLegacy($client, $pluginConfig, $gCaptchaResponse);
            } else {
                /**
                 * New-Handling
                 * Shopware 5.7.0 or greater: use new guzzle-factory from Symfony 4.4
                 * */
                $responseData = $this->getResponseData($client, $pluginConfig, $gCaptchaResponse);
            }

            $this->captchaChecked = true;

            if (!$responseData['success']) {
                if (is_array($responseData['error-codes']) &&
                    (in_array('missing-input-secret', $responseData['error-codes']) ||
                        in_array('invalid-input-secret', $responseData['error-codes']))
                ) {
                    // could do logging here.
                }

                $controller->View()->sStatus = [
                    'code' => 5,
                    'message' => $this->snippets->getNamespace('plugins/MyfavRecaptcha/reCAPTCHA')
                        ->get('captchaFailed', 'Captcha-Überprüfung fehlgeschlagen', true)
                ];
                return 'error';
            } else {
                if(!isset($responseData['score'])) {
                    $controller->View()->sStatus = [
                        'code' => 5,
                        'message' => $this->snippets->getNamespace('plugins/MyfavRecaptcha/reCAPTCHA')
                            ->get('captchaFailed', 'Captcha-Überprüfung fehlgeschlagen', true)
                    ];
                    return 'error';
                }

                $this->lastScore = $responseData['score'];
                return 'success';
            }
        }
        return null;
    }

    /**
     * @param Enlight_Config $pluginConfig
     * @param ClientInterface $client
     * @param $gCaptchaResponse
     * @return mixed
     */
    public function getResponseDataLegacy($client, $pluginConfig, $gCaptchaResponse)
    {
        $responseData = null;

        $response = $client->post(ConstantsProvider::GOOGLE_RECAPTCHA_VERIFY_URL, [
            'body' => [
                'secret' => $pluginConfig['recaptchaSecretKey'],
                'response' => $gCaptchaResponse
            ]
        ]);

        if (!is_null($response)) {
            $responseData = json_decode($response->getBody(), true);
        }

        return $responseData;
    }

    /**
     * @param Enlight_Config $pluginConfig
     * @param ClientInterface $client
     * @param $gCaptchaResponse
     * @return mixed
     */
    public function getResponseData($client, $pluginConfig, $gCaptchaResponse)
    {
        $responseData = null;

        $response = $client->post(
            ConstantsProvider::GOOGLE_RECAPTCHA_VERIFY_URL,
            [
                'form_params' => [
                    'secret' => $pluginConfig['recaptchaSecretKey'],
                    'response' => $gCaptchaResponse
                ]
            ]
        );

        if (!is_null($response)) {
            $responseData = json_decode($response->getBody(), true);
        }

        return $responseData;
    }

    public function getLastScore() {
        return $this->lastScore;
    }
}
