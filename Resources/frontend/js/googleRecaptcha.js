/**
 * googleRecaptcha.js
 *
 * Handle invisible Google recaptcha v3
 * Implemented using jquery to avoid challange of changing form-ids insinde the shopware templates.
 * furthermore each type of form-valication has its own callback function.
 *
 * Documentation:
 * https://developers.google.com/recaptcha/docs/invisible
 *
 * */
$(document).ready(function () {
	/**
	 * function handleSubmit
	 *
	 * Global Function to handle Submit
	 * This is used in all callback-Functions
	 */
	function handleSubmit(button, form) {
		// Disable the button
		$(button).attr('disabled', 'disabled');
		$(button).prop('disabled', true);
		var googleRecaptchaKey = $('.myfav-recaptcha-public-key').val();
		
		$.getScript( "https://www.google.com/recaptcha/api.js?render=" + googleRecaptchaKey)
        .done(function( script, textStatus ) {
            if(typeof grecaptcha !== "undefined") {
                grecaptcha.ready(function () {
                    grecaptcha.execute(googleRecaptchaKey, {
                        action: 'handleGoogleForm'
                    })
                    .then(function (token) {
						$(form).find('.g-recaptcha-response').val(token);
						$(form).submit();
                    });
                });
            }
        });
	}


	/**
	 * function onSubmitInvisibleRecaptchaForm
	 *
	 * this will handle the validation of invisible recaptcha v3 on shopware forms.
	 * */
	function onSubmitInvisibleRecaptchaForm(event) {
		event.stopPropagation();
		event.preventDefault();
		
		var button = $('.myfav--submit-invisible-recaptcha-form');
		var form = button.closest('form');
		
		handleSubmit(button, form);
	};

	/**
	 * function onSubmitInvisibleRecaptchaNotification
	 *
	 * this will handle the validation of invisible recaptcha v3 on product notification forms.
	 * */
	function onSubmitInvisibleRecaptchaNotification(event) {
		event.stopPropagation();
		event.preventDefault();
		
		var button = $('.myfav--submit-invisible-recaptcha-notification');
		var form = button.closest('form');
		
		handleSubmit(button, form);
	}

	/**
	 * function onSubmitInvisibleRecaptchaFooterNewsletter
	 *
	 * this will handle the validation of invisible recaptcha v3 on the footer newsletter forms.
	 * */
	function onSubmitInvisibleRecaptchaFooterNewsletter(event) {
		event.stopPropagation();
		event.preventDefault();
		
		var button = $('.myfav--submit-invisible-recaptcha-footerNewsletter');
		var form = button.closest('form');
		
		handleSubmit(button, form);
	}


	/**
	 * function onSubmitInvisibleRecaptchaFormNewsletter
	 *
	 * this will handle the validation of invisible recaptcha v3 on the main newsletter forms.
	 * */
	function onSubmitInvisibleRecaptchaFormNewsletter(event) {
		event.stopPropagation();
		event.preventDefault();
		
		var button = $('.myfav--submit-invisible-recaptcha-formNewsletter');
		var form = button.closest('form');
		
		handleSubmit(button, form);
	}


	/**
	 * function onSubmitInvisibleRecaptchaRegister
	 *
	 * this will handle the validation of invisible recaptcha v3 on the register form.
	 * */
	/*var onSubmitInvisibleRecaptchaRegister = async function (token) {*/
	function onSubmitInvisibleRecaptchaRegister(event) {
		event.stopPropagation();
		event.preventDefault();
		
		var button = $('.myfav--submit-invisible-recaptcha-register');
		var form = button.closest('form');
		
		var isValid = true;
		
		// Check all form fields
		$('#register--form .is--required:not(:disabled)').each(function() {
			var tmpval = $(this).val();
			
			if(tmpval === null || tmpval.length == 0) {
				// Check if this is in register-shipping, and register shipping is hidden.
				var isRegisterShipping = $(this).closest('.register--shipping');
				
				if(isRegisterShipping.length > 0) {
					if(!$(isRegisterShipping).hasClass('is--hidden')) {
						console.log('Fehlerhaftes Feld bei abweichende Lieferadresse, weil die aktiviert ist.');
						isValid = false;
					}
					
				// Prüfen, ob es das Passwort-Feld ist, und ob das Passwort-Feld auch sichtbar ist.
				} else if($(this).attr('id') == 'register_personal_password') {
					var passwordContainer = $(this).closest('.register--account-information');
					
					if(passwordContainer.length > 0 && !$(passwordContainer).hasClass('is--hidden')) {
						console.log('Fehlerhaftes Passwort-Feld, weil das nicht leer ist.');
						isValid = false;
					}
				} else {
					console.log('Fehlerhaftes sonstiges Feld: ', this);
					isValid = false;
				}
			}
		});
		
		if(!isValid) {
			return;
		}
		
		handleSubmit(button, form);
	}

	/**
	 * function onSubmitInvisibleRecaptchaForgotPassword
	 *
	 * this will handle the validation of invisible recaptcha v3 on the forgot-password form.
	 * */
	function onSubmitInvisibleRecaptchaForgotPassword(event) {
		event.stopPropagation();
		event.preventDefault();
		
		var button = $('.myfav--submit-invisible-recaptcha-forgotpassword');
		var form = button.closest('form');
		
		handleSubmit(button, form);
	}
	
	/* Register event handlers */
	$('.myfav--submit-invisible-recaptcha-formNewsletter').on('click', function(event) {
		onSubmitInvisibleRecaptchaFormNewsletter(event);
	});
	
	$('.myfav--submit-invisible-recaptcha-register').on('click', function(event) {
		onSubmitInvisibleRecaptchaRegister(event);
	});
	
	$('.myfav--submit-invisible-recaptcha-notification').on('click', function(event) {
		onSubmitInvisibleRecaptchaNotification(event);
	});
	
	$('.myfav--submit-invisible-recaptcha-footerNewsletter').on('click', function(event) {
		onSubmitInvisibleRecaptchaFooterNewsletter(event);
	});
	
	$('.myfav--submit-invisible-recaptcha-form').on('click', function(event) {
		onSubmitInvisibleRecaptchaForm(event);
	});
	
	$('.myfav--submit-invisible-recaptcha-forgotpassword').on('click', function(event) {
		onSubmitInvisibleRecaptchaForgotPassword(event);
	});
	
	/* Helper for shopping worlds - many shop owners use custom snippets in shopping worlds,
	   so this helps, to make the captcha work there, too */
	$.subscribe("plugin/swEmotionLoader/onLoadEmotionFinished", function(me) {
		$('.myfav--submit-invisible-recaptcha-footerNewsletter').off('click');
		$('.myfav--submit-invisible-recaptcha-footerNewsletter').on('click', function(event) {
			onSubmitInvisibleRecaptchaFooterNewsletter(event);
		});
	});
});