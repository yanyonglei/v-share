<?php
namespace app\admin\controller;
use think\Controller;
use app\admin\model\User as UserModel;
use think\Db;
use app\admin\model\Role;
use app\admin\model\RoleUser;
class User extends BaseController
{
	// 用户列表
    public function user()
    {
        //获取后台管理员账户信息
      /*  $admin = session('user');
        if ($admin) {
            $this->assign('admin', $admin);
        } else {
            $this->error('您没有登陆', 'admin/admin_login');
            return false;
        }*/



        $userInfo = UserModel::where("isnull(uder)")->select();

        $this->assign('userInfo', $userInfo);
        return $this->fetch('user/user');
    }
	//添加管理员用户
    public function add_user()
    {
        //查询角色表
        $role=new Role();

        $roles=$role->all();

     //  var_dump($roles);
        $this->assign('roles',$roles);

    	return $this->fetch('user/add_user');
    }
    /**
     * 改变改变用户状态页面
     */
    public function doStatus(){

        //获取用户id
        $id=$_POST['id'];

        $status=$_POST['status'];

        $user=new UserModel();

        $res=$user->where('id',$id)->update(['status'=>$status]);

        if($res){
            echo json_encode(['status'=>1,'msg'=>'操作成功']);
        }else{
            echo json_encode(['status'=>0,'msg'=>'操作失败']);
        }
    }
    //单一删除用户
    public function  doDelete(){
        //读去当前的用户id
        $id=$_POST['id'];
        $user=new UserModel();
        $res=$user->where('id',$id)->delete();

         if($res){
             echo json_encode(['status'=>1,'msg'=>'删除成功']);
         }else{
             echo json_encode(['status'=>0,'msg'=>'删除成功']);
         }
    }
    //批量删除
    public function check(){
        $aid = $_POST['delt'];

        $where['id'] = array('in',$aid);




        $res = Db::name('article')->where($where)->delete();

        if($res)
        {
            return json(['status'=>1,'msg'=>'删除成功','res'=>$res]);
        }else{
            return json(['status'=>0,'msg'=>'删除失败']);
        }
    }

//添加用户
    public function doAdd(){
        $user=new UserModel();
       $username=input('post.username');
       //查询用户是否已经添加过
      $info =$user->where(['username'=>$username])->find();
      if($info){
          return json(['status'=>1,'msg'=>'用户名已存在']);
      }

       $password=input('post.password');
        //获取管理员
       $uder=input('post.uder');
       $summary=input('post.summary');

       $time=time();

        //获取登陆的Ip地址
        if($_SERVER['REMOTE_ADDR']=='::1'){
            $rip='127.0.0.1';
        }else{
            $rip=$_SERVER['REMOTE_ADDR'];
        }
        $rip=ip2long($rip);
        $data=[
                'username'=>$username,
                'password'=>md5($password),
                'uder'=>$uder,
                'summary'=>$summary,
                'rtime'=>$time,
                'rip'=>$rip,
                'status'=>1,
                'sex'=>2
            ];
        $user->data($data);
        $res=$user->save();
        if($res){
            //将数据信息 角色 权限信息加入数数据表 role_user内
            $user_id=$user->getLastInsID();
            $userRole=new RoleUser();
            $da['user_id']=$user_id;
            $da['role_id']=$uder;
            $userRole->data($da);
            $info=$userRole->save();
           if($info){
               return json(['status'=>3,'msg'=>'添加成功']);
           }else{
               return json(['status'=>2,'msg'=>'删除失败']);
           }
        }else{
            return json(['status'=>2,'msg'=>'删除失败']);
        }
    }
    //修改密码
    public function change_password()
    {     $admin=session('admin');
        $this->assign('admin',$admin);

        
        return $this->fetch('user/change_password');
    }
//修改密码页面
    public function up(){
        $admin=session('admin');
        $xiu=new UserModel();
        $password = input('post.password');
        $username = $admin['username'];
        $data = [
            'password'=>md5($password)
        ];

        $resu = Db::name('user')->where('username',$username)->update($data);
        if($resu){
            return json(['status'=>1,'msg'=>'修改成功']);
        }else{
            return json(['status'=>0,'msg'=>'修改失败']);
        }
    }
}