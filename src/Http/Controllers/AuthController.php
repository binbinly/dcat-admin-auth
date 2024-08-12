<?php

namespace AdminExtAuth\Http\Controllers;

use Dcat\Admin\Admin;
use Dcat\Admin\Form;
use AdminExtAuth\AuthServiceProvider;
use AdminExtAuth\Models\AdminUser;
use Dcat\Admin\Http\Controllers\AuthController as BaseAuthController;
use Dcat\Admin\Layout\Content;
use Dcat\Admin\Models\Administrator;
use GuzzleHttp\Client;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Session;
use Illuminate\Support\Facades\Validator;
use PragmaRX\Google2FA\Google2FA;
use Dcat\Admin\Widgets\Tab;

class AuthController extends BaseAuthController
{

    public function getLogin(Content $content)
    {
        if ($this->guard()->check()) {
            return redirect($this->getRedirectPath());
        }

        $config['domain'] = rtrim(AuthServiceProvider::setting('domain') ?? 'https://recaptcha.net');
        $config['key'] = !app()->environment('local') ? AuthServiceProvider::setting('site_key') : '';
        $config['2fa'] = !app()->environment('local') ? AuthServiceProvider::setting('2fa_open') : false;
        $name = AuthServiceProvider::instance()->getName();
        $view = view($name.'::index', compact('config'));
        return $content->full()->body($view);
    }

    public function postLogin(Request $request)
    {
        $validatorCode = Validator::make($request->only(['google_2fa_code']), [
            'google_2fa_code' => 'nullable|numeric|digits:6',
        ], [], [
            'google_2fa_code' => 'Google 2FA 验证码',
        ]);

        if ($validatorCode->fails()) {
            return $this->validationErrorsResponse($validatorCode);
        }

        $result = $this->validateCaptchaV3($request);
        if ($result === true) {
            $open2Fa = !app()->environment('local') && AuthServiceProvider::setting('2fa_open');
            $code = $request->input('google_2fa_code');
            if ($open2Fa && !$code) {
                return $this->validationErrorsResponse(['google_2fa_code' => 'Google 2FA 验证码不能为空']);
            }
            try {
                $rsp = parent::postLogin($request);
                if ($user = $this->guard()->user()) {
                    if ($user->status != AdminUser::STATUS_ACTIVE) {
                        $this->guard()->logout();
                        return $this->validationErrorsResponse(['username' => '用户已被封禁']);
                    }
                    if ($open2Fa && $user->google_2fa_secret) {
                        if (!(new Google2FA())->verifyKey($user->google_2fa_secret, $code)) {
                            return $this->validationErrorsResponse(['google_2fa_code' => 'Google 2FA 验证码不正确，请刷新页面后再重试']);
                        }
                    }
                    $user->last_session = Session::getId();
                    $user->save();
                }
            }Catch (\Exception $exception) {
                $this->guard() && $this->guard()->logout();
                return $this->validationErrorsResponse(['username' => $exception->getMessage()]);
            }
            return $rsp;
        }

        return $this->validationErrorsResponse(['token' => $result]);
    }

    public function getSetting(Content $content)
    {
        $tab = Tab::make();
        $tab->add('基础信息', $this->basicForm()->render(), true, 1);
        $tab->add('安全', $this->basicForm(1)->render(), false, 2);
        return $content
            ->title(trans('admin.user_setting'))
            ->body($tab->render());
    }

    public function putSetting()
    {
        if(request()->get('type') == 1) {
            return $this->secureForm()->update(Admin::user()->getKey());
        }
        $form = $this->settingForm();

        if (! $this->validateCredentialsWhenUpdatingPassword()) {
            $form->responseValidationMessages('old_password', trans('admin.old_password_error'));
        }

        return $form->update(Admin::user()->getKey());
    }

    protected function basicForm($type = 0)
    {
        if($type == 1) {
            $form = $this->secureForm();
        } else {
            $form = $this->settingForm();
        }
        $form->tools(
            function (Form\Tools $tools) {
                $tools->disableList();
            }
        );
        return $form->edit(Admin::user()->getKey());
    }

    protected function secureForm(): Form
    {
        return new Form(new Administrator(), function (Form $form) {
            $form->action(admin_url('auth/setting?type=1'));

            $form->disableCreatingCheck();
            $form->disableEditingCheck();
            $form->disableViewCheck();

            $form->tools(function (Form\Tools $tools) {
                $tools->disableView();
                $tools->disableDelete();
            });

            if(Admin::user()->google_2fa_secret) {
                $form->html('Google 2FA 已绑定');
                $form->disableFooter();
            } else {
                $google2fa = new \PragmaRX\Google2FAQRCode\Google2FA();
                $secret = $google2fa->generateSecretKey(32);
                $qrCodeUrl = $google2fa->getQRCodeInline(
                    'admin',
                    'admin@admin.com',
                    $secret
                );
                $form->hidden('google_2fa_secret')->value($secret);
                $form->html($qrCodeUrl, 'Google 2FA 二维码');
                $form->text('code', 'Google 2FA 验证码')->required();
                $form->title('绑定');
                $form->saving(function (Form $form) use ($google2fa) {
                    if (!$google2fa->verifyKey($form->google_2fa_secret, $form->code)) {
                        return $form->response()->error('Google 2FA 验证码不正确');
                    }

                    $form->deleteInput('code');
                });

                $form->saved(function (Form $form) {
                    return $form
                        ->response()
                        ->success(trans('admin.update_succeeded'))
                        ->redirect('auth/setting#tab_2');
                });
            }
        });
    }

    private function validateCaptchaV3(Request $request)
    {
        if(app()->environment('local')) {
            return true;
        }
        $secret = AuthServiceProvider::setting('secret_key');
        if(!$secret) return true;

        $token = $request->input('token', '');
        if (!$token) {
            return $this->validationErrorsResponse(['token' => '非法操作']);
        }

        $params = [
            'secret' => $secret,
            'response' => $token,
            'remoteip' => $request->ip(),
        ];

        $url = rtrim(AuthServiceProvider::setting('domain') ?? 'https://recaptcha.net') . '/recaptcha/api/siteverify';
        $response = $this->captchaHttp()->post($url, ['form_params' => $params,]);
        $statusCode = $response->getStatusCode();
        $contents = $response->getBody()->getContents();
        if (200 != $statusCode) {
            return $this->validationErrorsResponse(['token' => '请求异常:'.$contents]);
        }
        $result = json_decode($contents, true);
        if (true === $result['success'] && $result['score'] >= AuthServiceProvider::setting('score') ?? 0.7) {
            return true;
        }

        return $result['error-codes'][0] ?? '验证失败';
    }

    private function captchaHttp(): Client
    {
        return new Client([
            'timeout' => AuthServiceProvider::setting('timeout', 5),
            'verify' => false,
            'http_errors' => false,
        ]);
    }
}
