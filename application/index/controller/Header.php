<?php

namespace app\index\controller;

use think\Controller;

class Header extends Controller
{
    public function index()
    {

        return  $this->fetch('index/header');
    }
}