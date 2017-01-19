<?php
/**
 * 公共方法，使用redis缓存机制处理数据
 */
namespace Supervise\Controller;
use Think\Controller;

class CommonRedisController extends Controller {
    private $redis;

    public function _initialize(){
        $this->redis = new \Org\Util\ThinkRedis();
    }

    //禁播设置
    public function setBanLive($banid) {
        $banInfo =  M('Banrecord')->where(array('banid'=>$banid))->find();
        $key = 'BanLive';
        $hashKey = 'Emcee_'.$banInfo['userid'];
        if ($banInfo['bantime']>0) {
            $failuretime = date('Y-m-d H:i:s',strtotime('+'.$banInfo['bantime'].' minutes'));
        }else {
            $failuretime = $banInfo['bantime'];  //永久禁播为-1
        }
        $value = array(
            'failuretime' => $failuretime
        );
        $value = json_encode($value);
        $this->redis->hSet($key,$hashKey,$value);
    }

}