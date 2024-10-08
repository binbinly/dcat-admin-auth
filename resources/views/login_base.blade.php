<style>
  .login-box {
    margin-top: -10rem;
    padding: 5px;
  }

  .login-card-body {
    padding: 1.5rem 1.8rem 1.6rem;
  }

  .card, .card-body {
    border-radius: .25rem
  }

  .login-btn {
    padding-left: 2rem !important;;
    padding-right: 1.5rem !important;
  }

  .content {
    overflow-x: hidden;
  }

  .form-group .control-label {
    text-align: left;
  }
</style>

<div class="login-page" @if(config('admin.login_background_image'))style="background: url({{config('admin.login_background_image')}}) no-repeat;background-size: cover;"@endif>
  <div class="login-box">
    <div class="login-logo mb-2" style="font-weight: bold;color: #fff">
      {{ config('admin.name') }}
    </div>
    <div class="card">
      <div class="card-body login-card-body shadow-100">
        <p class="login-box-msg mt-1 mb-1">{{ __('admin.welcome_back') }}</p>

        <form id="login-form" method="POST" action="{{ admin_url('auth/login') }}">

          <input type="hidden" name="_token" value="{{ csrf_token() }}"/>

          <fieldset class="form-label-group form-group position-relative has-icon-left">
            <input
                type="text"
                class="form-control {{ $errors->has('username') ? 'is-invalid' : '' }}"
                name="username"
                placeholder="{{ trans('admin.username') }}"
                value="{{ old('username') }}"
                required
                autofocus
            >

            <div class="form-control-position">
              <i class="feather icon-user"></i>
            </div>

            <label for="email">{{ trans('admin.username') }}</label>

            <div class="help-block with-errors"></div>
            @if($errors->has('username'))
              <span class="invalid-feedback text-danger" role="alert">
                @foreach($errors->get('username') as $message)
                  <span class="control-label" for="inputError"><i class="feather icon-x-circle"></i> {{$message}}</span>
                  <br>
                @endforeach
              </span>
            @endif
          </fieldset>

          <fieldset class="form-label-group form-group position-relative has-icon-left">
            <input
                minlength="5"
                maxlength="20"
                id="password"
                type="password"
                class="form-control {{ $errors->has('password') ? 'is-invalid' : '' }}"
                name="password"
                placeholder="{{ trans('admin.password') }}"
                required
                autocomplete="current-password"
            >

            <div class="form-control-position">
              <i class="feather icon-lock"></i>
            </div>
            <label for="password">{{ trans('admin.password') }}</label>

            <div class="help-block with-errors"></div>
            @if($errors->has('password'))
              <span class="invalid-feedback text-danger" role="alert">
                @foreach($errors->get('password') as $message)
                  <span class="control-label" for="inputError"><i class="feather icon-x-circle"></i> {{$message}}</span>
                  <br>
                @endforeach
              </span>
            @endif
          </fieldset>

          @yield('content')

        </form>
      </div>
    </div>
  </div>
</div>

<script>
    function captchaTokenCheck(showMessage) {
        if ($('#token').attr('value').length === 0) {
            if (showMessage) {
                toastr.error('请先验证');
            }
            return false;
        }
        return true;
    }
</script>
@yield('js')
