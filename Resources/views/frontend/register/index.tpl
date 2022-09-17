{extends file="parent:frontend/register/index.tpl"}

{*  Google reCAPTCHA hinzufügen *}
{block name="frontend_register_index_form_submit"}
    {* reCAPTCHA Version 3 *}
    {if $myfavRecaptcha.showRecaptchaForUserRegistration && $myfavRecaptcha.recaptchaAPIKey}
        <input type="hidden" class="myfav-recaptcha-public-key" value="{$myfavRecaptcha.recaptchaAPIKey}" />
        <input type="hidden" name="g-recaptcha-response" class="g-recaptcha-response" />
        <div class="register--action">
            <button class="g-recaptcha myfav--submit-invisible-recaptcha-register register--submit btn is--primary is--large is--icon-right"
                    name="Submit"
                    data-sitekey="{$myfavRecaptcha.recaptchaAPIKey}"
                    data-callback="onSubmitInvisibleRecaptchaRegister">
                {s name="RegisterIndexNewActionSubmit" namespace="frontend/register/index"}{/s}
                <i class="icon--arrow-right"></i>
            </button>
        </div>
    {else}
    	{$smarty.block.parent}
    {/if}
{/block}

{block name="frontend_register_index_form_privacy_content"}
	{$smarty.block.parent}
    
    {if $myfavRecaptcha.showRecaptchaForUserRegistration && $myfavRecaptcha.recaptchaAPIKey}
        <div class="myfav-inform-privacy-recaptcha">
            {s name="myfav_inform_privacy_recaptcha"}Wir verwenden Google Recaptcha. Beim Klick auf Weiter stimmen Sie dem Nachladen von Fonts und Google Recaptcha von Google zu. Beim Ladevorgang werden Daten an Google übertragen.{/s}
        </div>
    {/if}
{/block}