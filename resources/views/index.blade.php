@extends(Dcat\Admin\Auth\AuthServiceProvider::instance()->getName().'::login_base')
@section('content')
    @if($config['2fa'])
    @include(Dcat\Admin\Auth\AuthServiceProvider::instance()->getName().'::login_2fa')
    @endif
    @include(Dcat\Admin\Auth\AuthServiceProvider::instance()->getName().'::login_remember')
    <fieldset class="form-label-group form-group position-relative has-icon-left">
        <input type="hidden" id="token" name="token" value="">
        <div class="help-block with-errors"></div>
        @if($errors->has('token'))
            <span class="invalid-feedback text-danger" role="alert">
                @foreach($errors->get('token') as $message)
                <span class="control-label" for="inputError"><i class="feather icon-x-circle"></i> {{$message}}</span>
                <br>
            @endforeach
              </span>
        @endif
    </fieldset>
    @include(Dcat\Admin\Auth\AuthServiceProvider::instance()->getName().'::login_button')
@endsection
@section('js')
    @if($config['key'])
    <script src="{{ rtrim($config['domain']) }}/recaptcha/api.js?render={{ $config['key'] }}"></script>
    @endif
    <script>
        Dcat.ready(function () {
            // ajax表单提交
            $('#login-form').form({
                validate: true,
                before: function (param) {
                    @if($config['key'])
                    if (!captchaTokenCheck(false)) {
                        grecaptcha.execute('{{ $config['key'] }}', {action: 'login'}).then(function (token) {
                            $('#token').attr('value', token);
                            $('#loginButton').click();
                        });
                        return false;
                    }
                    @endif
                },
                success: function () {
                    //
                },
                error: function () {
                    $('#token').attr('value', '');
                }
            });
        });
    </script>
@endsection
