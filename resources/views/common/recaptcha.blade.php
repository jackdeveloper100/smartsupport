<?php if (config('setting.google_recaptcha')) { ?>
    <div style="margin : 10px 0 ;">
        <script src="https://www.google.com/recaptcha/api.js" async defer></script>
        <div class="g-recaptcha" data-sitekey="{{config('setting.google_recaptcha_public_key')}}"></div>
    </div>
<?php } ?>