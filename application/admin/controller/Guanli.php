<?php
//管理员信息
namespace app\admin\controller;
use think\Controller;
use app\admin\model\User;
use think\Db;
use app\admin\model\Role;
use app\admin\model\RoleUser;
use app\admin\model\Permission;
use app\admin\model\RolePermission;
use app\admin\controller\BaseController;
class Guanli extends BaseController
{
	//管理员列表
    public function gl_list()
    {
        $info=Db::view('user','*')->view('role','*','user.uder=role.pid')
            ->view('role_user','*','user.id=role_user.user_id and role.rid=role_user.role_id')
            ->view('role_permission','*','role_user.role_id =role_permission.role_id')->select();

        $userInfo=Db::view('user','*')
            ->view('role_user ','*','user.id = role_user.user_id')
            ->view('role','*','role.rid=role_user.role_id')->select();
        //var_dump($userInfo);

        $count=RoleUser::count();
       // var_dump($count);

        $this->assign('count',$count);
       $this->assign('userInfo',$userInfo);
    	return $this->fetch('guanli/gl_list');
    }
    /**
     * 改变改变用户状态页面
     */
    public function doStatus(){

        //获取用户id
        $id=$_POST['id'];

        $status=$_POST['status'];

        $user=new User();

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

        $user=new User();
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


    //角色管理
    public function gl_role()
    {

        $role=new Role();
        //查询所有的角色

        $resAll= $role->select();

        //查询角色总数

        $count=  $role->count();
        $this->assign('count',$count);
        //var_dump($resAll);
        $this->assign('roleAll',$resAll);
    	return $this->fetch('guanli/gl_role');
    }
	
	//角色添加
    public function add_role()
    {
        /*$admin=session('user');
        if($admin){
            $this->assign('admin',$admin);
        }else{
            $this->error('您没有登陆','admin/admin_login');
            return false;
        }*/
    	return $this->fetch('guanli/add_role');
    }
	
	//管理员添加
    public function add_admin()
    {
       /* $admin=session('user');
        if($admin){
            $this->assign('admin',$admin);
        }else{
            $this->error('您没有登陆','admin/admin_login');
            return false;
        }*/
    	return $this->fetch('guanli/add_admin');
    }
    //权限管理
    public function gl_perssion(){

        $permission=new Permission();

       $resPermission= $permission->select();

        $count=$permission->count();

        $this->assign('count',$count);


        $this->assign('permission',$resPermission);
        return $this->fetch('guanli/gl_perssion');
    }
    //模板引擎
    public function  add_perssion(){


        return $this->fetch('guanli/add_perssion');
    }


    //模板引擎 更新
    public function  update_perssion(){

        return $this->fetch('guanli/update_perssion');
    }

    public function do_role_permission(){

        $permission=new Permission();

        $permissionInfo= $permission->select();
        $id=input('param.id');
        $this->assign('id',$id);
        //权限分层
        $info= $this->list_level($permissionInfo,$pid=0,$level=0);
        $this->assign('info',$info);

        return $this->fetch('guanli/do_role_permission');
    }

    //递归遍历数据
    public function list_level($arr,$pid=0,$level=0){
        //定义一个静态数组
        static $data = array();
        foreach($arr as $k => $v){
            if($v['pid'] == $pid){
                $v['level'] = $level;
                $data[] = $v;
                $this->list_level($arr,$v['id'],$level+1);
            }
        }
        return $data;
    }
    public function  addRp(){
        $permission=input('post.perssions');

        $role_id=input('post.id');

        // var_dump($_GET);
        if(!empty($permission)&&!empty($role_id)){

            $permission=trim($permission,',');
            $data=[
                'role_id'=>$role_id,
                "node_id"=>$permission
            ];

           // Db::name('role_permission');
            $rp=new RolePermission();
            $rp->data($data);
             $res= $rp->save();

             if($res){

                 return json(['status'=>1,'msg'=>'添加成功']);
             }else{
                 return json(['status'=>0,'msg'=>'添加失败']);
             }
        }
    }

}
