{extends file="parent:frontend/newsletter/index.tpl"}

{*  Google reCAPTCHA hinzufügen *}
{block name="frontend_newsletter_form_submit"}
    {* reCAPTCHA Version 3 *}
    {if $myfavRecaptcha.showRecaptchaForNewsletter && $myfavRecaptcha.recaptchaAPIKey}
        <input type="hidden" class="myfav-recaptcha-public-key" value="{$myfavRecaptcha.recaptchaAPIKey}" />
        <input type="hidden" name="g-recaptcha-response" class="g-recaptcha-response" />
        <div class="register--action">
            <button class="g-recaptcha myfav--submit-invisible-recaptcha-formNewsletter btn is--primary right is--icon-right"
                    type="submit"
                    name="{s name="sNewsletterButton"}{/s}"
                    data-sitekey="{$myfavRecaptcha.recaptchaAPIKey}"
                    data-callback="onSubmitInvisibleRecaptchaRegister">
                {s name="sNewsletterButton" namespace="frontend/newsletter/index"}{/s}
                <i class="icon--arrow-right"></i>
            </button>
        </div>
    {else}
    	{$smarty.block.parent}
    {/if}
{/block}

{block name="frontend_newsletter_form_privacy"}
	{$smarty.block.parent}
    
    <div class="myfav-inform-privacy-recaptcha">
    	{s name="myfav_inform_privacy_recaptcha"}Wir verwenden Google Recaptcha. Beim Klick auf Weiter stimmen Sie dem Nachladen von Fonts und Google Recaptcha von Google zu. Beim Ladevorgang werden Daten an Google übertragen.{/s}
    </div>
{/block}