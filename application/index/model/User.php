<?php
/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 2017/10/14
 * Time: 18:58
 */

namespace app\index\model;

use think\Model;
class User extends Model
{

    /**
     * 类型的转换
     * @param $value
     * @return mixed
     */
    public function getSexAttr($value)
    {
        $status = [0=>'女',1=>'男',2=>'未知'];
        return $status[$value];
    }
}