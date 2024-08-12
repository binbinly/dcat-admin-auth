<?php

namespace AdminExtAuth\Models;

use Dcat\Admin\Models\Administrator;

/**
 * @property string $username
 * @property string $password
 * @property string $google_2fa_secret
 * @property string $last_session
 * @property int $status
 */
class AdminUser extends Administrator
{
    // 禁用
    public const STATUS_DISABLE = 0;
    // 启用
    public const STATUS_ACTIVE = 1;
}
