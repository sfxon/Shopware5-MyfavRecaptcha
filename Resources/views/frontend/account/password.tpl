{extends file="parent:frontend/account/password.tpl"}

{*  Google reCAPTCHA: Markup *}
{block name="frontend_account_password_reset_content"}
    {if $myfavRecaptcha.showRecaptchaForPasswordreset && $myfavRecaptcha.recaptchaAPIKey}
            {* reCAPTCHA Version 3 *}
            {$smarty.block.parent}
            <input type="hidden" class="myfav-recaptcha-public-key" value="{$myfavRecaptcha.recaptchaAPIKey}" />
	        <input type="hidden" name="g-recaptcha-response" class="g-recaptcha-response" />
        {if $reCaptchaErrorPasswordreset}
            {include file="frontend/index/snippets/recaptcha_error.tpl"}
        {/if}
    {else}
        {$smarty.block.parent}
    {/if}
{/block}

{block name="frontend_account_password_reset_actions"}
    {if $myfavRecaptcha.showRecaptchaForPasswordreset && $myfavRecaptcha.recaptchaAPIKey}
        {* reCAPTCHA Version 3 *}
        <div class="myfav-inform-privacy-recaptcha">
        	<div class="password-reset--form-content panel--body is--wide is--align-center">
	            {s name="myfav_inform_privacy_recaptcha"}Wir verwenden Google Recaptcha. Beim Klick auf Weiter stimmen Sie dem Nachladen von Fonts und Google Recaptcha von Google zu. Beim Ladevorgang werden Daten an Google übertragen.{/s}
            </div>
        </div>
        
        <div class="password-reset--form-actions panel--actions is--wide is--align-center">
            <a href="{url controller='account'}"
               class="password-reset--link btn is--secondary is--icon-left is--center">
                <i class="icon--arrow-left"></i>{s name="PasswordLinkBack"}{/s}
            </a>
            <button class="g-recaptcha myfav--submit-invisible-recaptcha-forgotpassword password-reset--link btn is--primary is--icon-right is--center"
                    type="submit"
                    data-sitekey="{$myfavRecaptcha.recaptchaAPIKey}"
                    data-callback="onSubmitInvisibleRecaptchaForgotPassword">
                {s name="PasswordSendAction"}{/s}
                <i class="icon--arrow-right"></i>
            </button>
        </div>
    {else}
        {$smarty.block.parent}
    {/if}
{/block}
