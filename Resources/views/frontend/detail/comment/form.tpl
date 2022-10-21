{extends file="parent:frontend/detail/comment/form.tpl"}

{*  Google reCAPTCHA: Markup *}
{block name='frontend_detail_comment_input_actions_submit'}
    {if $myfavRecaptcha.showRecaptchaForRatingForm && $myfavRecaptcha.recaptchaAPIKey}
        {* reCAPTCHA Version 3 *}
        <input type="hidden" class="myfav-recaptcha-public-key" value="{$myfavRecaptcha.recaptchaAPIKey}" />
        <input type="hidden" name="g-recaptcha-response" class="g-recaptcha-response" />

        {if $reCaptchaErrorRating}
            {include file="frontend/index/snippets/recaptcha_error.tpl"}
        {/if}

        <div class="myfav-inform-privacy-recaptcha">
            <div class="password-reset--form-content panel--body is--wide is--align-center">
                {s name="myfav_inform_privacy_recaptcha"}Wir verwenden Google Recaptcha. Beim Klick auf Weiter stimmen Sie dem Nachladen von Fonts und Google Recaptcha von Google zu. Beim Ladevorgang werden Daten an Google übertragen.{/s}
            </div>
        </div>

        <button class="g-recaptcha myfav--submit-invisible-recaptcha-comment btn is--primary"
                type="submit"
                name="{s name="DetailCommentActionSaveReCaptchaInvisible"}Speichern{/s}"
                data-sitekey="{$myfavRecaptcha.recaptchaAPIKey}"
                data-callback="onSubmitInvisibleRecaptchaComment">
            {s name="DetailCommentActionSaveReCaptchaInvisible"}Speichern{/s}
        </button>
    {else}
        {$smarty.block.parent}
    {/if}
{/block}
