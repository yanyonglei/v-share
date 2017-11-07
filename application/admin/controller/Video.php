<?php
namespace app\admin\controller;
use think\Controller;
use think\Db;

class Video extends BaseController
{
	//视频列表
    public function video_list()
    {
        //获取后台管理员账户信息

        /*$admin=session('user');
        if($admin){
            $this->assign('admin',$admin);
        }else{
            $this->error('您没有登陆','admin/admin_login');
            return false;

        }*/

        $res = Db::view('user','username')->view('video','*','video.uid=user.id')->select();



        //三表联查视频列表信息
        $res = Db::view('user','username')->view('video','*','video.uid=user.id')->
            view('channel','name','channel.id=video.type')->select();
        //var_dump($res);

        $count = Db::name('video')->count();
        $this->assign('count',$count);
        if($res)
        {
            $this->assign('res',$res);
        }
        return $this->fetch('video/video_list');
    }

    /**
     * 改变改变用户状态页面
     */
    public function doStatus(){

        //获取用户id
        $id=$_POST['id'];

        $status=$_POST['status'];

       $res = Db::name('video')->where('id',$id)->update(['status'=>$status]);


        if($res){
            echo json_encode(['status'=>1,'msg'=>'操作成功']);
        }else{
            echo json_encode(['status'=>0,'msg'=>'操作失败']);
        }

    }

    public function doStatu(){

        //获取用户id
        $id=$_POST['id'];

        $status=$_POST['status'];

        $rest = Db::name('video')->where('id',$id)->update(['status'=>$status]);

        if($rest){
            echo json_encode(['status'=>1,'msg'=>'操作成功']);
        }else{
            echo json_encode(['status'=>0,'msg'=>'操作失败']);
        }
    }

    //单一删除
    public function del(){
        $id = $_POST['id'];

        $res = Db::name('video')->where('id',$id)->delete();
        // var_dump($res);
        if($res){
            return json($res);
        }

    }

    //批量删除
    public function check(){
        $aid = $_POST['delt'];

        $where['id'] = array('in',$aid);

        $res = Db::name('video')->where($where)->delete();

        if($res)
        {
            return json(['status'=>1,'msg'=>'删除成功','res'=>$res]);
        }else{
            return json(['status'=>0,'msg'=>'删除失败']);
        }
    }



    //视频评论
    public function video_reply()
    {
        //获取后台管理员账户信息




        //三表联查查询评论视频信息
       $res = Db::view('video','title')->view('comment','*','comment.vid=video.id')->
           view('user','username','user.id=comment.uid')->select();
        $count = Db::name('comment')->count();
        $this->assign('res',$res);
        $this->assign('count',$count);
    	return $this->fetch('video/video_reply');
    }

    //评论删除
    //单一删除
    public function dec(){
        $id = $_POST['id'];
        //var_dump($id);
        $res = Db::name('comment')->where('id',$id)->delete();
         //var_dump($res);die;
        if($res){
            return json($res);
        }

    }


    //批量删除
    public function checked(){
        $aid = $_POST['delt'];

        $where['id'] = array('in',$aid);

        $res = Db::name('comment')->where($where)->delete();

        if($res)
        {
            return json(['status'=>1,'msg'=>'删除成功','res'=>$res]);
        }else{
            return json(['status'=>0,'msg'=>'删除失败']);
        }
    }


	//视频下载记录
    public function video_download()
    {
        //获取后台管理员账户信息

        /*$admin=session('user');
        if($admin){
            $this->assign('admin',$admin);
        }else{
            $this->error('您没有登陆','admin/admin_login');
            return false;

        }*/



        //三表联查查询视频信息
        $res = Db::view('video','title,path')->view('down','*','down.vid=video.id')->
        view('user','username','user.id=down.uid')->select();
        //
        $count = Db::name('down')->count();
        $this->assign('res',$res);
        $this->assign('count',$count);

    	return $this->fetch('video/video_download');
    }

    //下载记录删除
    //单一删除
    public function ded(){
        $id = $_POST['id'];
        //var_dump($id);
        $res = Db::name('down')->where('id',$id)->delete();
        //var_dump($res);die;
        if($res){
            return json($res);
        }

    }


    //下载记录批量删除
    public function shanchu(){
        $aid = $_POST['delt'];

        $where['id'] = array('in',$aid);

        $res = Db::name('down')->where($where)->delete();

        if($res)
        {
            return json(['status'=>1,'msg'=>'删除成功','res'=>$res]);
        }else{
            return json(['status'=>0,'msg'=>'删除失败']);
        }
    }
	
	//视频浏览记录
    public function video_see()
    {
        //获取后台管理员账户信息

      

        $res = Db::view('user','username,rip,phone')->view('video','*','video.uid=user.id')->select();

        $count = Db::name('video')->count();
        $this->assign('count',$count);
        if($res)
        {
            $this->assign('res',$res);
        }

    	return $this->fetch('video/video_see');
    }
	
	//视频分享记录
    public function video_share()
    {
        //获取后台管理员账户信息

        
    	return $this->fetch('video/video_share');
    }
    //视频订单
     public function video_buy()
    {
        //三表联查查询视频信息
        $res = Db::view('user','username')->view('buy','*','buy.uid=user.id')->
        view('video','path,title','video.id=buy.vid')->select();
        //
        $count = Db::name('buy')->count();
        $this->assign('res',$res);
        $this->assign('count',$count);
        return $this->fetch('video/video_buy');
    }


    //订单记录批量删除
    public function shan(){
        $aid = $_POST['delt'];


        $where['id'] = array('in',$aid);


        $res = Db::name('buy')->where($where)->delete();


        if($res)
        {
            return json(['status'=>1,'msg'=>'删除成功','res'=>$res]);
        }else{
            return json(['status'=>0,'msg'=>'删除失败']);
        }
    }


    //订单记录删除
    //单一删除
    public function de(){
        $id = $_POST['id'];
        //var_dump($id);
        $res = Db::name('buy')->where('id',$id)->delete();
        //var_dump($res);die;
        if($res){
            return json($res);
        }


    }

	
	


}
