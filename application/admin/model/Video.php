<?php

namespace app\admin\model;

use think\Model;
class Video extends Model{
    //查询address表中所有数据
    public function sel_all(){
        $arr = $this->name('video')->select();
        return $this->list_level($arr,$type=0,$level=0);
    }
    //递归遍历数据
    public function list_level($arr,$type=0,$level=0){
        //定义一个静态数组
        static $data = array();
        foreach($arr as $k => $v){
            if($v['type'] == $type){
                $v['level'] = $level;
                $data[] = $v;
                $this->list_level($arr,$v['id'],$level+1);
            }
        }
        return $data;
    }
}