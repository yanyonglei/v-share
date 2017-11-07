<?php
//类型
namespace app\admin\controller;
use think\Controller;
use think\Db;


class Channel extends BaseController
{
    //视频类型
    public function channel()
    {

        $res = Db::name('channel')->select();
        $count = Db::name('channel')->count();
        $this->assign('res',$res);
        $this->assign('count',$count);

        return $this->fetch('channel/channel');
    }




    //单一删除
    public function ded(){
        $id = $_POST['id'];
        //var_dump($id);
        $res = Db::name('channel')->where('id',$id)->delete();
        //var_dump($res);die;
        if($res){
            return json($res);
        }

    }

    //下载记录批量删除
    public function shanchu(){
        $aid = $_POST['delt'];

        $where['id'] = array('in',$aid);

        $res = Db::name('channel')->where($where)->delete();

        if($res)
        {
            return json(['status'=>1,'msg'=>'删除成功','res'=>$res]);
        }else{
            return json(['status'=>0,'msg'=>'删除失败']);
        }
    }

    //添加视频类型

    public function add_type()
    {

        return $this->fetch('channel/add_type');
    }

    public function add()
    {
        $content = input('post.content');

        $resu = Db::name('channel')->where(['name' => $content])->find();

        if ($resu) {
            return json(['status' => 0, 'msg' => '类型已存在']);
        } else {

            $data = [
                'name' => $content,
                'pid' => 12,
                'time' => time(),
                'status' => 1
            ];
            $res = Db::name('channel')->insert($data);

            if ($res) {
                return json(['status' => 1, 'msg' => '添加成功']);
            } else {
                return json(['status' => 0, 'msg' => '添加失败']);
            }
            $this->assign('res', $res);
        }
    }
}
