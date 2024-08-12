<?php

namespace AdminExtAuth\Http\Middleware;

use Dcat\Admin\Admin;
use AdminExtAuth\AuthServiceProvider;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Session;

class AuthCheck
{
    public function handle(Request $request, \Closure $next)
    {
        // 单用户登陆验证
        if(Admin::user() && Admin::user()->last_session && Session::getId() != Admin::user()->last_session){
            if($request->path() != 'auth/logout') {
                return admin_redirect('/auth/logout');
            }
        }
        // 开启 2fa 验证 必须先设置 Google 2fa Code
        $open = !app()->environment('local') && AuthServiceProvider::setting('2fa_open');
        if($open && Admin::user() && !Admin::user()->google_2fa_secret) {
            if($request->path() != 'auth/setting') {
                return admin_redirect('auth/setting#tab_2');
            }
        }
        return $next($request);
    }

}
