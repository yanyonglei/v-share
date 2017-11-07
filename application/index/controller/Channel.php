<?php
/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 2017/10/14
 * Time: 13:06
 */

namespace app\index\controller;


use think\Controller;
use app\index\model\Channel as ChannelModel;

class Channel extends  Controller
{
    /**
     * @return mixed 视频分类模块
     */

    public function  index(){
        

        $channel= ChannelModel::all(['status'=>1]);
        $userInfo=session('user');
        //  var_dump($userInfo);
        $this->assign('userInfo',$userInfo);
        $this->assign('channel',$channel);
        return $this->fetch('index/channel');
    }
}