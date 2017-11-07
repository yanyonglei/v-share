<?php
/**
 * Created by PhpStorm.
 * User:yanyl
 * Date: 2017/10/13
 * Time: 11:12
 */
namespace app\index\controller;
use Couchbase\UserSettings;
use think\Controller;
use app\index\model\Ucpaas;
use  app\index\model\User;
use app\index\model\QQ;
class Auth  extends  Controller
{
    /** 功能：注册界面的显示
     * @return mixed
     */
    public function register()
    {
        $this->assign('title','V 视频注册');
        return $this->fetch('index/auth/register');
    }
    /**
     * 注册函数
     */
    public function  doRegister(){
        //获取用户名
        $username=input("post.username");
        //密码
        $password=input("post.password");
        //手机号
        $phone=input('post.phone');

        //获取注册的Ip地址
        if($_SERVER['REMOTE_ADDR']=='::1'){
            $regip='127.0.0.1';
        }else{
            $regip=$_SERVER['REMOTE_ADDR'];
        }
        $data=[
            'username'=>$username,
            'password'=>md5($password),
            'phone'=>$phone,
            //用户的类型
            'type'=>0,
            //性别
            'sex'=>'2',
            //用户注册时间
            'rtime'=>time(),
            //允许登陆
            'status'=>1,
            'rip'=>ip2long($regip),
            'grade'=>200

        ];
        $user=new User();
        $user->data($data);
        $res=$user->save();
        if($res){
            return json(['status'=>1,'msg'=>'注册成功']);
        }else{
            return json(['status'=>1,'msg'=>'注册失败']);
        }
    }
    /** 功能:登陆界面的显示
     * @return mixed
     */
    public function login()
    {
        return $this->fetch('index/auth/login');
    }
    /**
     *
     * @return \think\response\Json
     */
    public function  doLogin(){
        //防sql注入处理
        $username=addslashes(trim(input('post.username')));
        //接收密码
        $password=md5(trim(input('post.password')));
        //查询数据库看是否有用户的信息
        $info=User::where(['username'=>$username,'password'=>$password])->find();

        //获取登陆的Ip地址
        if($_SERVER['REMOTE_ADDR']=='::1'){
            $cip='127.0.0.1';
        }else{
            $cip=$_SERVER['REMOTE_ADDR'];
        }
        if($info){

            if($info['status']==0){
                return json(['status'=>2,'msg'=>'您目前不允许登陆,联系管理员']);
            }
            //将数据信息存入session 内

            $data=['ctime'=>time(),

                    'cip'=>ip2long($cip),
                ];
            //讲登陆登陆信息加入数据库
            $user=new User();
            $res=$user->where('id',$info->id)->update($data);
            if($res){
                session('user',$info->toArray());
                return json(['status'=>1,'msg'=>'登陆成功','redirect_url'=>'../../']);
            }else{
                return json(['status'=>0,'msg'=>'用户名或者密码错误']);
            }
        }else{
            return json(['status'=>0,'msg'=>'用户名或者密码错误']);
        }
    }
    /**
     * 检测用户名是否存在
     * @return \think\response\Json
     */
    public function checkUser(){
        $username=input('post.username');
        //查询数据库 是否存在
        $info=User::where(['username'=>$username])->find();

        if($info){
            return json(['status'=>1,'msg'=>'用户名已存在']);
        }else{
            return json(['status'=>0,'msg'=>'用户名不存在']);
        }
    }
    /*
     * 获取手机验证码
     */
    public  function getCode(){
        $phone=input('post.phone');

        $options['accountsid']='daf4e50a2cce0d482a9465c92e4d0836';
        $options['token']='0dc7a164baeb1a32a78599c1ebfaf93b';


        //初始化 $options必填
        $ucpass = new Ucpaas($options);

        $string=join('',array_rand(range(0,9),4));

        $appId = "deeeec811cd1425aa99c830131cdde4a";
        $to = $phone;
        $templateId = "139977";
        $param=$string;

        $strJson= $ucpass->templateSMS($appId,$to,$templateId,$param);
        $str=substr($strJson,21,6);

        if($str=="000000"){
            return json(['status'=>1,'msg'=>'短信发送成功','code'=>$string]);
        }else{
            return json(['status'=>0,'msg'=>'短信发送失败,稍后再试']);
        }
    }
    /**
     * 退出账号的方法
     */
    public function loginOut(){
        session(null);
        $this->success('退出成功',url('/?s=index/auth/login'));
    }
    /**
     * 第三方登录
     */

  public function qqLogin(){

      $open = new QQ();
      $code=$_GET['code'];
    //  var_dump($code);

       $info=$open->me($code);
      $username=$info['name'];
      $img=$info['img'];
      $uniq=$info['uniq'];
      $from=$info['from'];
      $sex=$info['sex'];

      //用uniq查询数据看信息是否被记录
      $user=new User();

     $res= $user->where('uniq',$uniq)->find();
     //结果空说明保存
     if(!$res){

         //获取登陆的Ip地址
         if($_SERVER['REMOTE_ADDR']=='::1'){
             $cip='127.0.0.1';
         }else{
             $cip=$_SERVER['REMOTE_ADDR'];
         }

         //信息加入数据库
         $data=[
           'username'=>$username,
           'picture'=>$img,
             'uniq'=>$uniq,
             'from'=>$from,
             'ctime'=>time(),
             'cip'=>ip2long($cip),
             'sex'=>$sex,
             'type'=>0,
             'status'=>1
         ];
         $user->data($data);
        $resLogin= $user->save();
        //查询数据是否加入数据库
        if($resLogin){
            //重复新搜索数据
            $infos=$user->where('uniq',$uniq)->find();
            if($infos){
                session('user',$infos);
                //跳转首页
                echo '<meta http-equiv="Refresh" content="1; url=http://www.yanyonglei.com" />';
            }
        }else{
            echo '<meta http-equiv="Refresh" content="1; url=http://www.yanyonglei.com" />';
        }
     }else{
         //信息加入session
         session('user',$res);
         echo '<meta http-equiv="Refresh" content="1; url=http://www.yanyonglei.com" />';
     }
  }

}