<?php
/**
 * @author 狼一
 * @brief Redis Key值管理
 */
namespace Admin\Controller;

class RedisManageController extends CommonController {
    private $redis;
    private $RedisKeysConfig;

    public function _initialize(){
        parent::_initialize();
        $this->redis = new \Org\Util\ThinkRedis();
        $this->RedisKeysConfig = getRedisKeys();
    }

    //Key值管理列表
    public function index(){
        $RedisKeys = array();
        foreach($this->RedisKeysConfig['Keys'] as $key => $val){
            $RedisKeys['Keys'][$key]['remark'] = $val;
            if(strpos($key,"\$") === false){
                $RedisKeys['Keys'][$key]['refresh'] = 1;
            }else{
                $RedisKeys['Keys'][$key]['refresh'] = 0;
            }
        }

        foreach($this->RedisKeysConfig['hKeys'] as $key => $val){
            $RedisKeys['hKeys'][$key]['remark'] = $val['remark'];
            if(strpos($key,"\$") === false){
                $RedisKeys['hKeys'][$key]['refresh'] = 1;
            }else{
                $RedisKeys['hKeys'][$key]['refresh'] = 0;
            }
        }

        $this->assign('RedisKeys',$RedisKeys);
        $this->display();
    }

    //Key详情
    public function key_detail(){
        $key = I('get.key');
        $value = $this->redis->get($key);

        $this->assign('key',$key);
        $this->assign('value',$value);
        $this->display();
    }

    //hKey详情
    public function hKey_detail(){
        $key = I('get.key');
        $RedisKey = I('get.RedisKey');
        $RedisHashKey = I('get.RedisHashKey');

        $value = array();
        $hKeys = array();
        $refresh_url = '';
        if($key){
            if($RedisKey && $RedisHashKey){
                if(strpos($RedisKey,"\$") === false && strpos($RedisHashKey,"\$") === false){
                    $refresh_url = U('Admin/RedisManage/refresh_key/key/'.$RedisKey.'/hKey/'.$RedisHashKey);
                    $value = $this->redis->hGet($RedisKey,$RedisHashKey);
                }
            }elseif($RedisKey && strpos($RedisKey,"\$") === false){
                $refresh_url = U('Admin/RedisManage/refresh_key/key/'.$RedisKey);
                $hKeyValue = $this->redis->hGetAll($RedisKey);
                if($hKeyValue){
                    foreach($hKeyValue as $k => $v){
                        $value[$k] = json_decode($v,true);
                    }
                    $value = json_encode($value);
                }
            }else{
                if(strpos($key,"\$") === false){
                    $refresh_url = U('Admin/RedisManage/refresh_key/key/'.$key);
                    $hKeyValue = $this->redis->hGetAll($key);
                    if($hKeyValue){
                        foreach($hKeyValue as $k => $v){
                            $value[$k] = json_decode($v,true);
                        }
                        $value = json_encode($value);
                    }
                }
            }
            $hKeys = $this->RedisKeysConfig['hKeys'][$key]['HashKey'];
        }

        $Redis_Key_Hashkey = '';
        if($RedisKey){
            $Redis_Key_Hashkey .= $RedisKey;
        }else{
            $Redis_Key_Hashkey .= $key;
        }
        if($RedisHashKey){
            $Redis_Key_Hashkey .= ' - '.$RedisHashKey;
        }
        $this->assign('key',$key);
        $this->assign('RedisKey',$RedisKey);
        $this->assign('RedisHashKey',$RedisHashKey);
        $this->assign('hKeys',$hKeys);
        $this->assign('value',$value);
        $this->assign('refresh_url',$refresh_url);
        $this->assign('Redis_Key_Hashkey',$Redis_Key_Hashkey);
        $this->display();
    }

    //刷新单个key值
    public function refresh_key(){
        $key = I('get.key');
        $hashKey = I('get.hKey');
        if($key && strpos($key,"\$") === false){
            if($hashKey && strpos($hashKey,"\$") === false){
                $this->redis->hDel($key,$hashKey);
            }else{
                $this->redis->expire($key,0);
            }
        }
        $this->success('',U('Admin/RedisManage/index'));
    }

    //批量刷新key
    public function refresh_keys(){
        $keys = I('post.keys');
        foreach($keys as $key){
            if($key && strpos($key,"\$") === false){
                $this->redis->expire($key,0);
            }
        }
        $this->success('',U('Admin/RedisManage/index'));
    }

    //刷新redis
    public function refresh_redis(){
        $this->redis->flushDB();
        $this->success('',U('Admin/RedisManage/index'));
    }
}