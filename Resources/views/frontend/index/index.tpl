{extends file="parent:frontend/index/index.tpl"}

{* Google reCAPTCHA: script *}
{block name="frontend_index_header_javascript_jquery_lib"}
    {$smarty.block.parent}
    {if $myfavRecaptcha.showRecaptchaForUserRegistration
    || $myfavRecaptcha.showRecaptchaForForms
    || $myfavRecaptcha.showRecaptchaForNewsletter
    || $myfavRecaptcha.showRecaptchaForRatingForm
    || $myfavRecaptcha.showRecaptchaForInStockForm
    || $myfavRecaptcha.showRecaptchaForBlog
    || $myfavRecaptcha.showRecaptchaForLogin}
        {if $myfavRecaptcha.recaptchaAPIKey}
            {* reCAPTCHA Version 3 *}
			{* <!--
			<script src='https://www.google.com/recaptcha/api.js?render={$myfavRecaptcha.recaptchaAPIKey}'></script>
			<script>
                grecaptcha.ready(function () {
                    grecaptcha.execute('{$myfavRecaptcha.recaptchaAPIKey}', {
                        action: 'homepage'
                    })
					.then(function (token) {
						$("[name='g-recaptcha-response']").val(token);
					});
                });
            </script>
            --> *}
        {/if}
    {/if}
{/block}
