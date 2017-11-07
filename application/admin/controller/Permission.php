<?php
/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 2017/10/23
 * Time: 15:16
 */

namespace app\admin\controller;


use app\admin\model\Permission as PermissionModel;

class Permission extends BaseController
{
    /**
     * 添加权限s
     */
        public function  addPerssion(){


            //获取表单数据
            $name=input('post.name');
            $title=input('post.title');
            $level=input('post.level');
            $pid=input('post.pid');
            $sort=input('post.sort');

            $perssion=new PermissionModel();
            //检测该权限是否存在
            $resPerssion= $perssion->where('name',$name)->find();
            if($resPerssion){

                return json(['status'=>1,'msg'=>'此权限已经存在~~']);
            }
            $data=[
                'name'=>$name,
                'title'=>$title,
                'level'=>$level,
                'pid'=>$pid,
                'sort'=>$sort,
                'status'=>1
            ];

            //权限加入数据库
            $perssion->data($data);
            $resAdd=$perssion->save();
            if($resAdd){
                return json(['status'=>2,'msg'=>'添加成功']);
            }else{
                return json(['status'=>0,'msg'=>'添加失败']);
            }

        }
        public function deletePermission(){
            //   var_dump($_POST);

            $id=$_POST['id'];
            $permission=new PermissionModel();
            $res= $permission->where('id',$id)->delete();

            $count=$permission->count();

            if($res){
                return json(['status'=>1,',msg'=>'删除成功','count'=>$count]);
            }else{
                return json(['status'=>0,',msg'=>'删除失败']);
            }
        }



        public function updatePerssion(){



            //获取表单数据
            $name=input('post.name');
            $title=input('post.title');
            $level=input('post.level');
            $pid=input('post.pid');
            $sort=input('post.sort');


            $data=[
                'name'=>$name,
                'title'=>$title,
                'level'=>$level,
                'pid'=>$pid,
                'sort'=>$sort,
                'status'=>1
            ];
            $id=input('post.id');
            $perssion=new PermissionModel();
            $res=$perssion->where('id',$id)->update($data);
            if($res){
                return json(['status'=>1,'msg'=>'修改成功']);
            }else{
                return json(['status'=>0,'msg'=>'修改失败']);
            }
        }
}