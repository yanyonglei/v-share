<?php
namespace app\index\controller;
use think\View;
use think\Db;
use think\Controller;
use app\index\model\Article;
use app\index\model\User;
use app\index\model\QQ;
class Index extends Controller
{
    /**
     * @return string 主页显示
     */
    public function index()
    {
             if(isset($_GET['code'])){

                $code=$_GET['code'];

                $this->qqLogin($code);
             }


            $view=new View();
            //读取用户信息
            $userInfo=session('user');
            //当期状态下的总数

            $videoAll= Db::view('user','username')
                ->view('video','*','video.uid=user.id')
                ->view('channel','name','channel.id=video.type and video.status=1')
                ->order('video.utime desc')->select();
            //总页数
            $pageAll=ceil(count($videoAll)/5);
            $this->assign('pages',$pageAll);

            //最新
            //最新是视频查询 根据时间
            $videoInfo = Db::view('user','username')
                ->view('video','*','video.uid=user.id')
                ->view('channel','name','channel.id=video.type and video.status=1')
                ->where('video.grade','=','0')->order('video.utime desc')->limit(0,5)->select();
             //多表联查 3表联查询 最热 根据浏览量
             $videoInfo1 = Db::view('user','username')
                ->view('video','*','video.uid=user.id')
                ->view('channel','name','channel.id=video.type and video.status=1')
                ->where('video.grade','=','0')->order('video.playcount desc')->limit(0,5)->select();
             //多表联查 3表联查询 随便看看 全部
            $view->assign('videoInfo',$videoInfo);
            $view->assign('videoInfo1',$videoInfo1);

            //随便看看
            $videoInfo2 = Db::view('user','username')
                  ->view('video','*','video.uid=user.id')
                  ->view('channel','name','channel.id=video.type and video.status=1')
                  ->where('video.grade','=','0')->limit(0,5)->select();

            $view->assign('videoInfo2',$videoInfo2);

            //Vip付费专区

            $videoInfo3 = Db::view('user','username')
                ->view('video','*','video.uid=user.id')
                ->view('channel','name','channel.id=video.type and video.status=1')
                ->where('video.grade','>','0')->order('id','desc')->limit(0,5)->select();

            $view->assign('videoInfo3',$videoInfo3);
            //获取总条数
            $result = Db::name('video')->count();
            //获取总页数，每页显示数自定义
            $pages = ceil($result/3);



            //添加天气接口
            $view->assign('pages',$pages);
            //天气接口
            $url="http://www.sojson.com/open/api/weather/json.shtml?city=北京";
          //  $url="http://api.jisuapi.com/weather/query?appkey=910fb582f77c42c8&city=北京";
            $json=file_get_contents($url);
            $weather=json_decode($json,true);

            if(isset($weather['data'])){

                 $arrWeather=$weather['data']['forecast'];
        
                $view->assign('weather',$arrWeather);
            }
        
            //笑话接口
            $url="http://api.jisuapi.com/xiaohua/text?pagenum=1&pagesize=20&sort=addtime&appkey=910fb582f77c42c8";
            $json=file_get_contents($url);
            $array=json_decode($json,true);

            if($array['status'] != 0)
            {
                  $view->assign("joke",'api睡着了');
                
            }else{

                 $new=$array['result']['list'];
                  if(count($new)>10){
                    
                    $k=mt_rand(0,count($new));
                   $view->assign("joke",$new[$k]['content']);
                   // $view->assign("joke",'');
                   
                }
            }
        
        $view->assign('pages',$pages);

        $view->assign('userInfo',$userInfo);
        return  $view->fetch('index/index');
    }


     //付费视频

    public function buy()
    {
        $name = input('post.username');

        $r = Db::name('user')->where(['username'=>$name])->find();
        //用户id
        $uid = $r['id'];
        //视频id
        $vid = input('post.vid');

        $res = Db::name('buy')->where(['uid'=>$uid,'vid'=>$vid])->find();

        if($res)
        {
            return json(["status"=>1,"msg"=>"视频已购买请前往观看"]);
        }else{
            return json(["status"=>0,"msg"=>"请先购买"]);
        }
    }


   
    public function money()
    {


        $vid = input('post.vid');
        $grade = input('post.grade');


        //判断用户积分是否满足购买要求
       // $res = Db::name('user')->where(['id'=>$uid])->find();
        $name = input('post.username');


        $r = Db::name('user')->where(['username'=>$name])->find();
        $uid = $r['id'];
        $fen = $r['grade']-$grade;


        if($fen>=0)
        {
            $data =[
                'uid'=>$uid,
                'vid'=>$vid,
                'grade'=>$grade,
                'btime'=>time(),
                'status'=>1
            ];


            $result = Db::name('buy')->insert($data);


            Db::name('user')->where(['id'=>$uid])->update(['grade'=>$fen]);


            return json(["status"=>1,"msg"=>"购买成功,欢迎观看"]);


        }else{
            return json(["status"=>0,"msg"=>"积分不够,暂时不能观看"]);
        }
        
    }
    //视频分页
    public function videoYe(){

        $page=input('post.page');

        if(!is_numeric($page)){
            $this->error('页数非数字');
        }
        $videoInfo = Db::view('user','username')
            ->view('video','*','video.uid=user.id')
            ->view('channel','name','channel.id=video.type and video.status=1')
            ->order('video.utime desc')->page("$page,5")->select();
        if($videoInfo){
            return json($videoInfo);
        }
    }


	//文章列表
	public function article()
    {


        $userInfo=session('user');

        $this->assign('userInfo',$userInfo);
        $list=Db::name('article')->order('id desc')->page("0,3")->select();
        //获取总条数

        $result = Db::name('article')->count();
        //获取总页数，每页显示数自定义
        $pages = ceil($result/3);
        $this->assign('page',$pages);
        if($list){

            $this->assign('list',$list);
        }
        $this->assign('title','v-视频 幕后');
        return  $this->fetch('index/article');
    }

    //分页
    public function fenYe(){

        $page=$_POST['page'];
        //var_dump($page);
        if(!is_numeric($page)){
            $this->error('页数非数字');
        }

        $userInfo=Db::name('article')->page("$page,3")->order('id desc')->select();

        //var_dump($userInfo);
        if($userInfo){
            return json($userInfo);
        }

    }



    //文章详情列表
    public function article_detail()
    {

        $view=new View();
        $userInfo=session('user');
        //获取文章详情(id)
        $id = input('param.id');
        //var_dump($id);
        //数据库查询
        $res = Article::get(['id'=>$id]);
        $count = $res['rcount']+1;
        $data = [
          'rcount'=>$count
        ];

        $resu = Db::name('article')->where(['id'=>$id])->update($data);
        $view->assign('res',$res);
        $view->assign('userInfo',$userInfo);

        return  $view->fetch('index/article_detail');
    }


        /*404页面*/
    public function tip()
    {
        return $this->fetch();

    }
        /**
         * 获取笑话
         */
    public function getJoke()
    {

           //笑话接口
            $url="http://api.jisuapi.com/xiaohua/text?pagenum=1&pagesize=10&sort=addtime&appkey=910fb582f77c42c8";
            $json=file_get_contents($url);
            $array=json_decode($json,true);

            if($array['status'] != 0)
            {
                  return json(['joke'=>'接口api不想和你说话']);
                
            }else{

                 $new=$array['result']['list'];
                  if(count($new)>10){
                    $k=mt_rand(0,count($new));
                    return json(['joke' => $new[$k]['content']]);    
                }
            }
           

     }





  public function qqLogin($code){

        $open=new QQ();
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
               // echo '<meta http-equiv="Refresh" content="1; url=http://www.yanyonglei.top" />';
            }
        }else{
            //echo '<meta http-equiv="Refresh" content="1; url=http://www.yanyonglei.top" />';
        }
     }else{
         //信息加入session
         session('user',$res);
         echo '<meta http-equiv="Refresh" content="1; url=http://www.yanyonglei.top" />';
     }
   }


   public function wisdom(){

      $question=input('post.question');

      if($question=="刘恒黑"){

        return json(['msg'=>'你说的对，他是千锋最黑的！！！^_^']);
      }
      $url="http://api.jisuapi.com/iqa/query?appkey=910fb582f77c42c8&question=".$question;

      $json=file_get_contents($url);
      $jsonarr = json_decode($json, true);
      //var_dump($jsonarr);
      if($jsonarr['status'] != 0)
      {
          return json(['msg'=>'机器人还在睡觉']);
      }else{
          return json(['msg'=>$jsonarr['result']['content']]);
      } 
   }


}
