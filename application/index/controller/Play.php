<?php
/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 2017/10/14
 * Time: 14:47
 */

namespace app\index\controller;
use think\Controller;
use think\Db;
use app\index\model\Video;
use app\index\model\Down;
use app\index\model\Collection;
class Play extends Controller
{

    /*
     * 播放页面
     */
    public function  index(){


        //读取用户信息
        $userInfo=session('user');

        //获取视频id
        $id = input('param.id');

        //当前视频type
        $res=Db::view('video','*')
            ->view('channel','name','channel.id=video.type and video.id='.$id)->select()[0];

        //当前是的type
        //查询相同类型的视频
        $videoInfo= Db::name('video')->where(['type'=>$res['type'],'status'=>1,'grade'=>0])->select();

        //查询播放排行较高的视频
        $videoHot=Db::name('video')->where('status=1 and playcount>10 and grade=0')
            ->order('playcount desc')->limit(0,6)->select();

        // dump($videoHot);
        $this->assign('videoHot',$videoHot);

        $this->assign('videoInfo',$videoInfo);

        $count = $res['playcount']+1;
        $data = [
            'playcount'=>$count
        ];
        $resu = Db::name('video')->where(['id'=>$id])->update($data);


        //两表联合查询视频作者
        $uid = $res['uid'];
        $auth = Db::name('user')->where(['id'=>$uid])->find();

      /*  if($userInfo!=''){
             $currentUsrId=$userInfo['id'];
       
        }*/
         //查询该用户的所有视频
            $allVideo = Db::view('user','username')->view('video','*',"user.id=video.uid and video.playcount>5 and video.status=1 and video.grade=0 and  video.uid=$uid")->limit(0,6)->select();
            $this->assign('allVideo',$allVideo);
       
        //查询当前视频的所有评论信息
        $commentInfo=Db::view('user','*')
            ->view('comment','*','comment.uid=user.id and comment.vid='.$id)->select();
        $this->assign('commentInfo',$commentInfo);

        //查询视频评论信息

        $this->assign('res',$res);
        $this->assign('auth',$auth);
        //  $this->assign('result',$result);

        $this->assign('userInfo',$userInfo);
        $this->assign('title',$res['title']);
        return $this->fetch('index/play');
    }

    /**
     *
     *
     */
    public function reply()
    {
        //内容
        $content = input('post.content');
        //视频id
        $vid=input('post.vid');
        //用户信息
        $user= session('user');
        $uid = $user['id'];
        $data = [
            'uid'=>$uid,
            'content'=>$content,
            'ptime'=>time(),
            'vid'=>$vid,
            'type'=>0
        ];

        $result = Db::name('comment')->insert($data);
        //var_dump($result);
        //最后一条数据id
        $cId = Db::name('comment')->getLastInsID();;
        //多表联查最后一条数据

        if($result){
            $commentInfo=Db::view('user','*')
                ->view('comment','*',"comment.uid=user.id and comment.uid=$uid and comment.id=$cId and comment.vid=$vid")->select();
            if($commentInfo){
                return json($commentInfo);
            }else{
                return json(['status'=>0,'msg'=>'失败']);
            }
        }else{
            return json(['status'=>0,'msg'=>'失败']);
        }

    }



    //对评论的回复

    public function ply()
    {
        //对应评论
        $hid = input('post.hid');
        //视频id
        $vid = input('post.vid');
        $content = input('post.content');
        $user = session('user');
        //用户id
        $uid = $user['id'];
        $data = [
            'uid' => $uid,
            'content' => $content,
            'atime' => time(),
            'type' => 1,
            'hid' => $hid,
            'vid' => $vid
        ];

        $reply = Db::name('comment')->insert($data);

        if ($reply) {
            $replyInfo = Db::view('user', '*')
                ->view('comment', '*', "comment.uid=user.id and comment.uid=$uid and comment.type=1 and comment.vid=$vid")->select();
            if ($replyInfo) {
                return json($replyInfo);
            } else {
                return json(['status' => 0, 'msg' => '回复评论失败']);

            }
        }else{
                return json(['status' => 0, 'msg' => '回复评论失败']);
            }

    }
    /**
     * 视频的下载记录
     */

    public function  downLoad(){

        //视频的id
        $id=input('post.id');
        //点击的次数
        // $k=input('input.k');

        $user =session('user');
        //记录用户id
        $uid=$user['id'];
        //下载时间
        $time=time();

        $data=[
            'uid'=>$uid,
            'vid'=>$id,
            'dtime'=>$time
        ];
        //数据下载成功
        $down=new Down();
        $down->data($data);
        $down->save();

        //更新video表中dcount下载次数
        $count=$down->where('vid',$id)->count();
        $video=new Video();
        $video->where('id',$id)->update(['dcount'=>$count]);

    }


    /**
     * 视频的收藏功能函数
     */
    public function collection(){
        //视频的id
        $id=input('post.id');
        //点击的次数
        // $k=input('input.k');

        $user =session('user');
        //当前的时间
        $time=time();

        $uid=$user['id'];

        //查询当视频是否被收藏
        $videoInfo=Collection::where(['vid'=>$id,'uid'=>$uid])->find();
       // var_dump($videoInfo);
        if($videoInfo){
            return json(['status'=>'1','msg'=>'视频已经被您收藏']);
        }else{

            $data=['uid'=>$uid,'vid'=>$id,'time'=>$time];
            //初始化
            $collection=new Collection();
            //数据加入数据库
            $collection->data($data);
            $res=$collection->save();
            if($res)
            {
                return json(['status'=>2,'msg'=>'收藏成功!!!^_^']);
            }
        }

    }
}