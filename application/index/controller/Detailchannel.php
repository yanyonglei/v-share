<?php
/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 2017/10/14
 * Time: 14:14
 */
namespace app\index\controller;
use think\Controller;
use app\index\model\Channel as ChannelModel;
use app\index\model\Video;
use think\Db;
class Detailchannel extends Controller
{
    /**
     * 详细的视频页
     * @return mixed
     */
    public function  index(){

      
        //接收参数
        $type=input('param.id');
        //查询频道的所有内容
        $channel= ChannelModel::all(['status'=>1]);
        $this->assign('channel',$channel);
        //类型
        $this->assign('id',$type);

        //获取频道的名称
        $name=ChannelModel::all(['status'=>1,'id'=>$type])[0]->name;
        $this->assign('name',$name);

        //获取用户的所有信息
        //查询 当前类下全部是视频
        $video=Video::all(['type'=>$type,'grade'=>0]);
        $videoInfo=Db::name('video')->page("1,8")->where(['type'=>$type,'status'=>1,'grade'=>0])->select();

        $this->assign('title','频道');
        $this->assign('video',$videoInfo);
        //获取当前类型下的数据总数
        $this->assign('count',count($video));

        $this->assign('title',$name);
        
        $userInfo=session('user');
        //  var_dump($userInfo);
        $this->assign('userInfo',$userInfo);

        //分页总数数
        $this->assign('pages',ceil(count($video)/8));

        return $this->fetch('index/detailchannel');
    }

    /**
     * 滚动加载 分页函数
     * 每页显示数8 条数据
     * @return \think\response\Json
     */
    public function fenYe(){

        //获取当前页
        $page=input('post.page');
        //获取视频的类型
        $id=input('post.id');
        $videoInfo=Db::name('video')->page("$page,8")->where('type',$id)->select();

        return json($videoInfo);
    }
}