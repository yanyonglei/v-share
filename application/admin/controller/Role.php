<?php
/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 2017/10/23
 * Time: 10:17
 *
 * 角色控制器
 */

namespace app\admin\controller;


use think\Controller;
use app\admin\model\Role as RoleModel;

class Role extends BaseController
{
    public function addRole(){

        $rname=input('post.name');
        $remark=input('post.remark');

        $pid=input('post.pid');

        //查询数据库是存在该角色

        $role=new RoleModel();
        $resRole= $role->where('rname',$rname)->find();

        if($resRole){
            return json(['status'=>1,'msg'=>'此角色已经存在~~']);
        }

        $data['rname']=$rname;
        $data['remark']=$remark;
        $data['pid']=$pid;
        $role->data($data);
        $resAdd=$role->save();
        if($resAdd){
            return json(['status'=>2,'msg'=>'添加成功']);
        }else{
            return json(['status'=>0,'msg'=>'添加失败']);
        }
    }

    public function deleteRole(){


        $rid=$_POST['rid'];
        $role=new RoleModel();
        $res=  $role->where('rid',$rid)->delete();
        $count=$role->count();
        if($res){
            return json(['status'=>1,',msg'=>'删除成功','count'=>$count]);
        }else{
            return json(['status'=>0,',msg'=>'删除失败']);
        }
    }
}