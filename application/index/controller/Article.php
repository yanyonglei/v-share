<?php
/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 2017/10/17
 * Time: 21:53
 */

namespace app\index\controller;


use think\Controller;
use think\Db;

class Article extends Controller
{
    //文章列表
    public function index()
    {
       // $this=new View();
        $userInfo=session('user');

        if($userInfo){
            $this->assign('userInfo',$userInfo);
        }else{
            $this->error('用户为登陆,请登录','/?s=index/auth/login');
        }

        $this->assign('userInfo',$userInfo);
        $list=Db::name('article')->page('0,3')->select();

        //获取总条数

        $result = Db::name('article')->count();
        //获取总页数，每页显示数自定义
        $pages = ceil($result/3);


        $this->assign('page',$pages);


        if($list){
            $this->assign('list',$list);
        }

        $this->assign('title','文章-V 视频');
        return   $this->fetch('index/article');
    }

}