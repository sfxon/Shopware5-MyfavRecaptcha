# Shopware-5-MyfavRecaptcha

Asynchronous Implementation of Google Recaptcha v3 for Shopware 5. Loads recaptcha, when the button for sending has been clicked.

It only supports version 3 of google reCAPTCHA.

This plugin is based on the original Mittwald Security Plugin for Shopware. The Google Recaptcha functionality has been extracted and integrated into this plugin.

Licensed under GPLv3, as the original plugin.

---

## 1. Installation

### 1.1 Shopware Backend

1. Download in Plugin Store

2. Install Plugin

3. Configure Plugin
  - set Google Recaptcha API-Key
  - set Google Recaptcha API-Secret
  - enable the integration on the pages, you like to have it on.

---

### 1.2 From sources

1. Download from any source where it is available

2. Unzip

3. Upload the files to your stores plugin folder (The folder you install it in has to be called [shoproot]/custom/plugins/MyfavRecaptcha).

4. Install via Backend (Plugin-Menu) or CLI

5. Configure Plugin
  - set Google Recaptcha API-Key
  - set Google Recaptcha API-Secret
  - enable the integration on the pages, you like to have it on.
  
### Shopping Worlds / Einkaufswelten

We added an option for shopping world forms.
You can design your forms, like newsletter registrations.
A general listener on the shopping-world-loaded event was added.

If you give your submit button the class `myfav--submit-invisible-recaptcha-footerNewsletter`, it will attach the listener for this button.

Here is an example of a shopping world element, you could build with a code block:

```
<h2 class="home--newsletter-title">Newsletteranmeldung</h2>
<form class="newsletter--form home--newsletter" action="#your-url" method="post">
<input type="hidden" value="1" name="subscribeToNewsletter">
<input type="hidden" class="myfav-recaptcha-public-key" value="6LdsvS8cAAAAAC--67vArJ6rBSnub3D424YARqxi">
<input type="hidden" name="g-recaptcha-response" class="g-recaptcha-response">
<div class="content">
<input type="email" aria-label="Ihre E-Mail Adresse" name="newsletter" class="newsletter--field">
<input type="hidden" name="redirect">
<button type="button" aria-label="Newsletter abonnieren" class="newsletter--button btn myfav--submit-invisible-recaptcha-footerNewsletter" onclick="onSubmitInvisibleRecaptchaFooterNewsletter()">
<i class="icon--mail"></i>
</button>
</div>
<p>Abonniere unseren Newsletter und verpasse keine Neuigkeiten</p>
<p class="privacy-information home--newsletter-privacy">
Ich habe die <a title="Datenschutzbestimmungen" href="https://www.motoment.com/datenschutz" target="_blank">Datenschutzbestimmungen</a> zur Kenntnis genommen.
</p>
<div class="myfav-inform-privacy-recaptcha"><p class="privacy-information home--newsletter-privacy">
Wir verwenden Google Recaptcha. Beim Klick auf Weiter stimmen Sie dem Nachladen von Fonts und Google Recaptcha von Google zu. Beim Ladevorgang werden Daten an Google übertragen.
</p></div>
<input type="hidden" name="__csrf_token" value=""></form>```

