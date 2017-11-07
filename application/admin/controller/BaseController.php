<?php
namespace app\admin\controller;
use think\Controller;
use think\Request;
use think\Db;
class BaseController extends Controller
{
    public $ig_url=[
        '/admin/admin/admin',
        '/admin/login/admin_login',
        '/admin/admin/adminLoginOut',
    ];
    //后台基础类登录验证
    public function _initialize()
    {
        if(empty(session('admin.username')) || session('admin.username')=='')
        {
            //echo '没有登录 请登录在操作';
            $this->error('你没有登录请重新登录',url('/?s=admin/login/admin_login'));
        }
        //获得用户所拥有的权限
        $privarr_urls=$this->getRole();
        //取得当前节点名
        $request=Request::instance();
        $model=$request->module();
        $controller= $request->controller();
        $action=$request->action();

        $jiedian='/'.$model.'/'.strtolower($controller).'/'.strtolower($action);
        if(in_array($jiedian, $this->ig_url))
        {	
            return true;//返回的是什么数据怎么写
        }
        //如果是超级管理员已不需要判断 1是超级管理员
        if(session('admin.pid')==1)
        {
            return true;//返回的是什么数据怎么写
        }
        // 	//判断当前访问页面权限的链接是否在用户所有链接中
        if(!in_array($jiedian, $privarr_urls)){

            //var_dump('你没有权限操作，请联系管理员');
            $this->error('你没有权限操作当前页面，请联系管理员','','','1000');
         //   echo "你没有权限操作，请联系管理员";

        }
    }
    // //在框架里加入统一验证方法
    // public function first()
    // {
    // 	if(!$this->checkLogin() ) {
    // 		//echo '没有登录 请登录在操作';
    // 		$this->error('你没有登录请重新登录',url('index/adminuser/register'));
    // 	}

    /*
    *
    *判断逻辑认证
    *取出指定用户所有角色
    *在通过角色 取出 所属 权限关系
    *在权限表中取出所有权限
    */

    // 取出所有权限
    // public function Accessall()
    // {
    // 	$priv_urls=[];
    // 	$urlslist=Db::name('access')->select();
    // 	if($urlslist)
    // 		{
    // 			foreach($urllist as $val)
    // 			{
    // 				$tmp_urls=json_decode($val['urls'],true);
    // 				$priv_urls=array_merge($privarr_urls,$tmp_urls);
    // 			}
    // 		}
    // 	return $priv_urls;
    // }

    /*
    *获取用户所有的权限
    *取出指定用户所有角色
    *在通过角色 取出 所属 权限关系
    *在权限表中取出所有权限
    */

    //取出用户的所有权限
    public function getRole($uid=0)	//$uid=0默认
    {
        //var_dump(session('admin'));
        //获取用户的id
       // $uid=session('user.id');

        $privarr_urls=[];
        $urllist=Db::name('permission')->where('id', 'in' ,session('admin.node_id'))->select();
        if($urllist)
        {
            foreach ($urllist as $key => $value) {

                $tmp_urls = $value['name'];
                $privarr_urls[]=$tmp_urls;

            }

        }
      //  var_dump($privarr_urls);
        return $privarr_urls;




        //取出用户所述的角色
      /*  $role_idss=Db::name('role_user')->where('user_id',$uid)->select();
        $role_ids = '';
        if($role_idss){
            $role_ids=$role_idss[0]['role_id'];
            if($role_ids)
            {
                //在通过角色取出所述的权限
                $access_ids=Db::name('role_permission')->where('role_id','in',$role_ids)->select();
               // var_dump($access_ids);
                $node_id=$access_ids[0]['node_id'];
                //echo db('role_permission')->getLastSql();die;
                //dump($access_ids);die;
                //在全县表中所有的权限连接
                $urllist=Db::name('permission')->where('id', 'in' ,session('user.node_id'))->select();
                if($urllist)
                {
                    foreach ($urllist as $key => $value) {

                        $tmp_urls = $value['name'];
                        $privarr_urls[]=$tmp_urls;

                    }

                }
            }

        }*/


    }

}