{extends file="parent:frontend/index/footer-navigation.tpl"}

{*  Google reCAPTCHA hinzufügen *}
{block name="frontend_index_footer_column_newsletter_form_submit"}
    {* reCAPTCHA Version 3 *}
    {if $myfavRecaptcha.showRecaptchaForNewsletter && $myfavRecaptcha.recaptchaAPIKey}
        <input type="hidden" class="myfav-recaptcha-public-key" value="{$myfavRecaptcha.recaptchaAPIKey}" />
        <input type="hidden" name="g-recaptcha-response" class="g-recaptcha-response" />
        <div class="register--action">
            <button class="g-recaptcha myfav--submit-invisible-recaptcha-footerNewsletter newsletter--button btn"
                    name="Submit"
                    data-sitekey="{$myfavRecaptcha.recaptchaAPIKey}"
                    data-callback="onSubmitInvisibleRecaptchaRegister">
                <i class="icon--mail"></i>
                <span class="button--text">{s name='IndexFooterNewsletterSubmit' namespace="frontend/index/menu_footer"}{/s}</span>
            </button>
        </div>
    {else}
    	{$smarty.block.parent}
    {/if}
{/block}

{block name="frontend_index_footer_column_newsletter_privacy"}
	{$smarty.block.parent}
    
    <div class="myfav-inform-privacy-recaptcha">
    	{s name="myfav_inform_privacy_recaptcha"}Wir verwenden Google Recaptcha. Beim Klick auf Weiter stimmen Sie dem Nachladen von Fonts und Google Recaptcha von Google zu. Beim Ladevorgang werden Daten an Google übertragen.{/s}
    </div>
{/block}