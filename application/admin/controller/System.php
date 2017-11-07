<?php
//后台首页
namespace app\admin\controller;

use think\Db;

class System extends BaseController


{
	//网站后台管理系统
    public function system()
    {
        $res = Db::name('system')->select();

        $this->assign('res',$res);

    	return $this->fetch('system/system');
    }



}
