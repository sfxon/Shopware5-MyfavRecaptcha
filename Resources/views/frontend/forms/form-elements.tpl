{extends file="parent:frontend/forms/form-elements.tpl"}

{*  Google reCAPTCHA: Markup *}
{block name="frontend_forms_form_elements_form_captcha"}
    {if $myfavRecaptcha.showRecaptchaForForms && $myfavRecaptcha.recaptchaAPIKey}
        {if $reCaptchaErrorForms}
            {include file="frontend/index/snippets/recaptcha_error.tpl"}
        {/if}
    {else}
        {$smarty.block.parent}
    {/if}
{/block}

{block name="frontend_forms_form_elements_form_submit"}
    {if $myfavRecaptcha.showRecaptchaForForms && $myfavRecaptcha.recaptchaAPIKey}
        {* reCAPTCHA Version 3 *}
        <div class="buttons">
            <input type="hidden" class="myfav-recaptcha-public-key" value="{$myfavRecaptcha.recaptchaAPIKey}" />
	        <input type="hidden" name="g-recaptcha-response" class="g-recaptcha-response" />
            <input type="hidden" name="Submit" value="1" />
            <button class="g-recaptcha myfav--submit-invisible-recaptcha-form btn is--primary is--icon-right"
                    type="submit"
                    name="Submit"
                    value="submit"
                    data-sitekey="{$myfavRecaptcha.recaptchaAPIKey}"
                    data-callback="onSubmitInvisibleRecaptchaForm">
                {s name='SupportActionSubmit' namespace='frontend/forms/elements'}{/s}
                <i class="icon--arrow-right"></i>
            </button>
        </div>
    {else}
        {$smarty.block.parent}
    {/if}
{/block}

{block name='frontend_forms_form_elements_form_privacy'}
	{$smarty.block.parent}
    
    <div class="myfav-inform-privacy-recaptcha">
    	{s name="myfav_inform_privacy_recaptcha"}Wir verwenden Google Recaptcha. Beim Klick auf Weiter stimmen Sie dem Nachladen von Fonts und Google Recaptcha von Google zu. Beim Ladevorgang werden Daten an Google übertragen.{/s}
    </div>
{/block}