<?php 
namespace app\admin\controller;
use think\Controller;
use think\Db;
use app\admin\model\Video;
use app\admin\controller\BaseController;
class Admin extends BaseController
{
    public function admin()
    {
        $this->assign('title','后台管理');
        return $this->fetch('admin/admin');

    }
    //后台页面头部
    public function admin_top()
    {
        //获取后台管理员账户信息
        return $this->fetch('admin/admin_top');
    }
    //左侧导航menu
    public function admin_left_menu()
    {
        //获取后台管理员账户信息
        return $this->fetch('admin/admin_left_menu');
    }
    //展开合闭按钮
    public function admin_button()
    {
        //获取后台管理员账户信息
        return $this->fetch('admin/admin_button');
    }
}
