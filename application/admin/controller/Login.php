<?php
namespace app\admin\controller;

use think\Db;
use think\Controller;

class Login extends Controller
{
    //登录验证

    public function doLogin()
    {
        $username = input('post.username');
        $password = input('post.password');
        //从数据库查询是否是管理员
        $userInfo = Db::name('user')->where(['username'=>$username,'password'=>md5($password)])->find();
        if($userInfo)
        {
            if($userInfo['uder']==''){

                return json(['status'=>3,'msg'=>'您不是管理员无法进入,请联系管理员']);
            }else{
                $info=Db::view('user','*')->view('role','*','user.uder=role.pid')
                    ->view('role_user','*','user.id=role_user.user_id and user.id='.$userInfo['id'])
                    ->view('role_permission','*','role_user.role_id =role_permission.role_id')->select();

                if($info){
                    session('admin',$info[0]);
                    return json(['status'=>1,'msg'=>'登录成功']);
                }

            }
        }else{
            return json(['status'=>0,'msg'=>'用户名或者密码不正确']);
        }

    }


    //网站后台登录页面
    public function admin_login()
    {
        return $this->fetch('admin/admin_login');
    }


    /**
     * 后台退出函数
     */
    public function adminLoginOut(){
        session(null);
        $this->success('退出成功',url('/?s=admin/login/admin_login'));

    }

}