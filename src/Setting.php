<?php

namespace AdminExtAuth;

use Dcat\Admin\Extend\Setting as Form;

class Setting extends Form
{
    public function form()
    {
        $this->text('domain', 'reCAPTCHA domain')->default('https://recaptcha.net')->required();
        $this->text('site_key', 'reCAPTCHA siteKey')->required();
        $this->text('secret_key', 'reCAPTCHA secretKey')->required();
        $this->text('timeout', 'reCAPTCHA Timeout')->default(5)->required();
        $this->text('score', 'reCAPTCHA Score')->default(0.7)->required();
        $this->switch('2fa_open', '2fa Open')->default(0)->required();
    }
}
