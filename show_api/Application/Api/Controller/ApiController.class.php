<?php
namespace Api\Controller;
use Think\Controller;
/**
 * APP接口统一接入类
 *
 * 1、APP端接口统一进入本控制器
 * 2、验证接口请求版本
 * 3、1.3.3之前的版本，转向到 ApioldController
 * 4、1.3.3以后的版本（包括1.3.3），转向到 ApinewController
 *
 * @author : langyi
 * @Date: 2016-07-09
 */
class ApiController extends Controller {
    public function _initialize(){
        //根据版本号，将接口转向到不同的Api控制层
        $version = I('post.version',100,'trim');
        if ($version >= 133) {
            $Api = new ApinewController();
        } else {
            $Api = new ApioldController();
        }
        $action = ACTION_NAME;
        $Api->$action();
    }
}