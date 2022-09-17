{extends file="parent:frontend/plugins/notification/index.tpl"}

{*  Google reCAPTCHA hinzufügen *}
{block name="frontend_detail_index_notification_button"}
    {if $myfavRecaptcha.showRecaptchaForInStockForm && $myfavRecaptcha.recaptchaAPIKey}
    	{* reCAPTCHA Version 3 *}
        <input type="hidden" class="myfav-recaptcha-public-key" value="{$myfavRecaptcha.recaptchaAPIKey}" />
        <input type="hidden" name="g-recaptcha-response" class="g-recaptcha-response" />
        <button class="g-recaptcha myfav--submit-invisible-recaptcha-notification notification--button btn is--center block"
                type="submit"
                id="instock--grecaptcha"
                data-sitekey="{$myfavRecaptcha.recaptchaAPIKey}"
                data-callback="onSubmitInvisibleRecaptchaNotification">
            <i class="icon--mail"></i>
        </button>
        
        {if $reCaptchaErrorNotification}
            {include file="frontend/index/snippets/recaptcha_error.tpl"}
        {/if}
    {else}
        {$smarty.block.parent}
    {/if}
{/block}


{block name="frontend_detail_index_notification_privacy"}
	{$smarty.block.parent}
    
    {if $myfavRecaptcha.showRecaptchaForInStockForm && $myfavRecaptcha.recaptchaAPIKey}
        <div class="myfav-inform-privacy-recaptcha">
            {s name="myfav_inform_privacy_recaptcha"}Wir verwenden Google Recaptcha. Beim Klick auf Weiter stimmen Sie dem Nachladen von Fonts und Google Recaptcha von Google zu. Beim Ladevorgang werden Daten an Google übertragen.{/s}
        </div>
    {/if}
{/block}