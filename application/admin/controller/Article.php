<?php
namespace app\admin\controller;
use think\Controller;
use think\Db;
class Article extends BaseController
{
	//文章列表
    public function article()
    {

        $res = Db::view('user','username')->view('article','*','article.uid=user.id')->select();

        $count = Db::name('article')->count();
        $this->assign('count',$count);
        $this->assign('res',$res);
    	return $this->fetch('article/article');
    }
	
	//单一删除
	public function del(){
        $id = $_POST['id'];
        $res = Db::name('article')->where('id',$id)->delete();
       // var_dump($res);
        if($res){
            return json($res);
        }

    }
//批量删除
    public function check(){
	    $aid = $_POST['delt'];

	    $where['id'] = array('in',$aid);

        $res = Db::name('article')->where($where)->delete();

        if($res)
        {
            return json(['status'=>1,'msg'=>'删除成功','res'=>$res]);
        }else{
            return json(['status'=>0,'msg'=>'删除失败']);
        }
    }

  

}
