<?php
/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 2017/10/14
 * Time: 15:11
 */

namespace app\index\controller;


use think\Controller;
use think\Db;

class Search extends Controller
{
    public function  index(){

        $userInfo=session('user');

        $this->assign('userInfo',$userInfo);
        return $this->fetch('index/search');
    }

    public function cha()
    {
        $keyword = input('post.keyword');


        $res = Db::name('video')->where('title|content','like',"%$keyword%")->select();

       if($res)
       {
           return json($res);
       }else{
           return json(['status'=>0,'msg'=>'结果不存在']);
       }

    }

}