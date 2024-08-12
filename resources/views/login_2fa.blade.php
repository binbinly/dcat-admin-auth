<fieldset class="form-label-group form-group position-relative has-icon-left">
    <input
        type="text"
        class="form-control {{ $errors->has('google_2fa_code') ? 'is-invalid' : '' }}"
        name="google_2fa_code"
        placeholder="请输入 Google 2fa 验证码"/>

    <div class="form-control-position">
        <i class="fa fa-google"></i>
    </div>

    <label for="code">Google 2fa 验证码</label>

    <div class="help-block with-errors"></div>
    @if($errors->has('google_2fa_code'))
        <span class="invalid-feedback text-danger" role="alert">
                @foreach($errors->get('google_2fa_code') as $message)
                <span class="control-label" for="inputError"><i class="feather icon-x-circle"></i> {{$message}}</span>
                <br>
            @endforeach
              </span>
    @endif
</fieldset>
