<?php

namespace MyfavRecaptcha\Setup;


class ConstantsProvider
{
    const GOOGLE_RECAPTCHA_VERIFY_URL = 'https://www.google.com/recaptcha/api/siteverify';

    // MAIL TEMPLATES
    const SG_FAILED_LOGIN_MAIL_TEMPLATE = 'sgFailedLogin';
    const SG_MODIFIED_FILES_MAIL_TEMPLATE = 'sgModifiedFiles';
    const SG_LOCKED_ACCOUNT_MAIL_TEMPLATE = 'sgLockedAccount';

    // SESSION
    const SESSION_LOGIN_CAPTCHA_ERROR = 'sgLoginCaptchaError';
    const SESSION_BLOG_COMMENT_CAPTCHA_ERROR = 'sgBlogCommentCaptchaError';
}