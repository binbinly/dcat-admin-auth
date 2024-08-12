<?php

namespace AdminExtAuth\Actions;

use Dcat\Admin\Actions\Response;
use AdminExtAuth\Models\AdminUser;
use Dcat\Admin\Grid\RowAction;

class UnbindRowAction extends RowAction
{

    protected $title = '解绑';

    /**
     * @return Response
     */
    public function handle()
    {
        AdminUser::query()->where('id', $this->getKey())->update(['google_2fa_secret' => '']);
        return $this->response()->success('解绑成功')->refresh();
    }


    /**
     * @return string[]
     */
    public function confirm()
    {
        return [
            '确定解绑吗？',
            '解绑后需要重新绑定二次验证'
        ];
    }
}
