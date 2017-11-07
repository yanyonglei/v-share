<?php
/**
 * Created by PhpStorm.
 * User: 闫永磊
 * Date: 2017/10/13
 * Time: 10:11
 */

namespace app\index\controller;

use think\Controller;
use app\index\model\Article;

use app\index\model\User as UserModel;
use app\index\model\Video;

use app\index\model\Channel as ChannelModel;
use app\index\model\Collection;
use think\Db;

class User extends  Controller
{
    /**
     * 用户个人主页
     * @return mixed
     */
    public function  index(){

        //获取用户信息
        $userInfo=session('user');
        if($userInfo){
            $this->assign('userInfo',$userInfo);
        }else{
            $this->error('用户为登陆,请登录','/?s=index/auth/login');
        }

        //读取用户id
        $uid=$userInfo['id'];

        $collection=new Collection();
        //收藏总数
        $count=$collection->where('uid',$uid)->count();
        $this->assign('count',$count);

        //三表联查
       $info=Db::view('video','title,path,image')
               ->view('collection','time,id,vid','video.id=collection.vid and  collection.uid='.$uid)
           ->view('channel','name','video.type=channel.id')->page("1,2")->select();

     //  dump($info);
        //总页数
        $pages=ceil($count/2);
        $this->assign('pages',$pages);

        $this->assign('info',$info);
        return $this->fetch('/index/user/index');
    }
    /**
     * 用户设置页面
     * @return mixed
     */
    public function  setting(){
        $user=session('user');
        $id=$user['id'];

        $userModel=new UserModel();
        $userInfo=$userModel->where('id',$id)->find();

        if($userInfo){
            $this->assign('userInfo',$userInfo->toArray());
        }else{
            $this->error('用户为登陆,请登录','/?s=index/auth/login');
        }
       // $this->assign('userInfo',$userInfo);

        return $this->fetch('/index/user/setting');
    }
	//判断邮箱格式是否正确
	public function checkEmail()
	{
		
		$youxiang = '/^\w+@(\w+\.)+(cn|net|com|edu)$/';//邮箱格式a-zA-Z0-9
		$email = input('post.email');
		if(preg_match($youxiang,$email,$matches)){
		 
		  return json(['status'=>1,'msg'=>'email格式不合法']);
		 
		}else{
            return json(['status'=>0,'msg'=>'email格式合法']);
        }
	}
    /**
     * 用户详细信息
     * @return mixed
     *
     */
    public function  info(){
        $userInfo=session('user');
        if($userInfo){
            $this->assign('userInfo',$userInfo);
        }else{
            $this->error('用户未登陆,请登录','/?s=index/auth/login');
        }
        $this->assign('title','个人信息');
        $this->assign('ip',long2ip($userInfo['rip']));
        return $this->fetch('/index/user/info');
    }

    /**
     *上传视频页面
     * @return mixed
     */
    public function  add_video(){

        //
        $channel= ChannelModel::all(['status'=>1]);
        $userInfo=session('user');
        if($userInfo){
            $this->assign('userInfo',$userInfo);
        }else{
            $this->error('用户为登陆,请登录','/?s=index/auth/login');
        }
        $this->assign('channel',$channel);
        return $this->fetch('/index/user/add_video');

    }
	/*
     *
     * 发表文章界面
     * */
    public function  add_article(){

        $userInfo=session('user');

        if($userInfo){
            $this->assign('userInfo',$userInfo);
        }else {

          $this->error('未登录,','/?s=index/auth/login');
        }
        return $this->fetch('/index/user/add_article');
    }

    /**
     * 接收上传图片的函数
     */
    public function  uploadImg(){
        // 获取上传文件
        $file = request() -> file('myfile');
        // 验证图片,并移动图片到框架目录下。
       // $info = $file-> move(ROOT_PATH.'public'.DS.'uploads');
        //设置取消时间文件夹
        $info=$file->rule('uniqid')-> move(ROOT_PATH.'public'.DS.'uploads');
       // var_dump($info);
        if($info){

            $mes = $info->getFilename();
            $mes="./public/uploads/".$mes;
            $data=['msg'=>$mes];
            $str=str_replace("\\/","/",json_encode($data));
            echo $str;
        }else{

            $mes = $file->getError();
            echo '{"msg":"'.$mes.'"}';
        }
    }

    /**
     * 
     * 接收上传视频的函数
     */
    public function  uploadVideo(){
        // 获取上传文件
        $file = request() -> file('myfile');
        // 验证图片,并移动图片到框架目录下。
      //  $info = $file-> move(ROOT_PATH.'public'.DS.'uploads');
        $info = $file->rule('uniqid')->move(ROOT_PATH.'public'.DS.'uploads');
        if($info){

            $mes = $info->getFilename();
          //  $mes="./public/uploads/".date('Ymd',time())."/".$mes;
            $mes="./public/uploads/".$mes;
            // var_dump($mes);
            $data=['msg'=>$mes];
            $str=str_replace("\\/","/",json_encode($data));
            echo $str;
        }else{

            $mes = $file->getError();
            echo '{"msg":"'.$mes.'"}';
        }
    }




    /**
     * 发表文章函数
     */
    public function  loadArticle(){

        $imagePath=input('post.imagePath');
        $imgInfo=pathinfo($imagePath);
        //var_dump($imgInfo);
        $url=str_replace('\\/','/','index/user/add_article');
        $type=['png','jpg','gif','jpeg','bmp'];

       if(!in_array($imgInfo['extension'],$type)){
            $this->error('图片类型错误重新上传',$url);
            return ;
        }

        $title=input('post.title');
        $tag=input('post.tag');
        $content=input('post.content');

        if(empty($content) || empty($imagePath) || empty($tag)||empty($content)){

                $this->error('图片、标签、标题、内容、不能为空');
        }
        //读取当前的用户的数据信息
        $user=session('user');

        $data=[
            'uid'=>$user['id'],
            'title'=>$title,
            'tag'=>$tag,
            'content'=>$content,
            'time'=>time(),
            'image'=>$imagePath
        ];
        //信息保存数据库
        $article=new Article();
        $article->data($data);
        $res= $article->save();
        if($res){
            return json(['status'=>1,'msg'=>'发表成功']);
        }else{
            return json(['status'=>0,'msg'=>'发表失败']);
        }

    }
    /**
     * 修改用户信息函数
     */
    public function  doModify(){
        //头像信息
         $image=input('post.image');
        $imgInfo=pathinfo($image);
        $url=str_replace('\\/','/','index/user/setting');
        $type=['png','jpg','gif','jpeg','bmp'];

        if(!in_array($imgInfo['extension'],$type)){
            $this->error('图片类型错误重新上传',$url);
            return ;
        }
        //用户名
        $nickname=input('post.nickname');
        //实名
        $realName=input('post.realname');
        //性别
        $sex=input('post.sex');
        //邮箱
        $email=input('post.email');

        //密码
        $password=input('post.passeord');
        if(!empty($password)){
            $password=md5($password);
        }
        //生日
        $birthday=input('post.birthday');
        //地址
        $address=input('post.address');
        //简介
        $summary=input('post.summary');

        $data['username']=$nickname;
        if(!empty($password)){
            $data['password']=$password;
        }
        $data['realname']=$realName;
        $data['picture']=$image;
        $data['sex']=$sex;
        $data['address']=$address;
        $data['birthday']=$birthday;
        $data['summary']=$summary;
        $data['email']=$email;

        //获取用户信息
        $user=session('user');
        //判断是否实名认证
        if(!empty($realName)){

            if($user['type']==0){
                //超级会员
                $data['type']=1;
            }
        }

        $id=$user['id'];
      // $id=$user['id'];
        //更新数据库信息
        $userModel=new UserModel();

        $res=$userModel->where('id',$id)->update($data);

          if($res){
             //更新session 数据
              $info=$userModel->where('id',$id)->find();
              session('user',$info->toArray());
             //查询用户数据
              return json(['status'=>1,'msg'=>'修改成功','realname'=>$info['realname'],'type'=>$info['type']]);
          }else{
              return json(['status'=>0,'msg'=>'修改失败']);
          }

    }


    public function loadVideo(){


        //获取视频的封面信息
        $img=input('post.img');
        //判断图片类型
        $imgInfo=pathinfo($img);
        $url=str_replace('\\/','/','index/user/add_video');
        $type=['png','jpg','gif','jpeg','bmp'];

        if(!in_array($imgInfo['extension'],$type)){
            $this->error('图片类型错误重新上传',$url);
            return ;
        }
        //获取视频信息
        $videoPath=input('post.videopath');

        // 判断视频类型
       $videoInfo=pathinfo($videoPath);
       // $url=str_replace('\\/','/','index/user/add_video');
       if($videoInfo['extension']!='mp4'){
           $this->error('视频类型错误重新上传',$url);
           return ;
       }
        //视频标题
        $title=input('post.title');
        $tag=input('post.tag');
        $grade = input('post.grade');
        $summary=input('post.summary');
        $size=input('post.size');
        $time=time();

        $user=session('user');
        //获取用户id
        $id=$user['id'];

        $data=[

            'uid'=>$id,
            'image'=>$img,
            'type'=>$tag,
           'title'=>$title,
            'path'=>$videoPath,
            'content'=>$summary,
            'utime'=>$time,
            'size'=>$size,
            'status'=>1,
            'grade'=>$grade
        ];
        //将数据信息加入数据库
        $video=new Video();
        $video->data($data);
        $res= $video->save();

       if($res){
           return json(['status'=>1,"msg"=>'视频上传成功']);
       }else{
           return json(['status'=>0,"msg"=>'视频上传失败']);
       }
    }


    /**
     * 删除收藏信息
     */
    public function  delete(){

        $id=input('post.id');
        $userInfo=session('user');


        $collection=new Collection();
        $res=$collection->where('id',$id)->delete();


        //读取用户id
        $uid=$userInfo['id'];

        $collection=new Collection();
        //收藏总数
        $count=$collection->where('uid',$uid)->count();


        if($res){
            return json(['status'=>1,'msg'=>'删除成功','count'=>$count]);
        }else{
            return json(['status'=>0,'msg'=>'删除成功']);
        }
    }



    /**
     * 分页是分页数据函数
     */

    public function  fenYe(){
        //获取当前页
        $page=input('post.page');
        //用户id
        $user=session('user');
        $uid=$user['id'];
        $info=Db::view('video','title,path,image')
            ->view('collection','time,id,vid','video.id=collection.vid  and collection.uid='.$uid)
            ->view('channel','name','video.type=channel.id')->page("$page,2")->select();
        if($info){
            return json($info);
        }
    }

    //删除原先的图片函数

    public function deleteImg(){

        $path=input('post.image');

        if($path=="http://www.yanyonglei.top/public/static/image/default.png" || $path=="./public/static/image/default.png"){
            return ;
        }
        //干掉未保在数据库内文件
        unlink($path);
    }
}