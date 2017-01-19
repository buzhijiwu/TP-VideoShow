<?php
namespace Home\Controller;

class ToplistController extends CommonController
{
    
    public function index(){
        $this->display();
    }
    
    //主播收入榜
    public function LoadTopEmceeList(){
        $CommonRedis = new CommonRedisController();
        $range = I('post.range');
        $limit = 10; 
        $data = $CommonRedis->getTopEmceeEarnList($range,$limit);    
        echo json_encode($data);    
    }

    //用户消费榜
    public function LoadRichList(){
        $CommonRedis = new CommonRedisController();
        $range = I('post.range');
        $limit = 10; 
        $data = $CommonRedis->getTopUserRichList($range,$limit);    
        echo json_encode($data);         
    }

    //新增用户关注榜
    public function LoadNewUserFansList(){
        $CommonRedis = new CommonRedisController();
        $range = I('post.range');
        $limit = 10; 
        $data = $CommonRedis->getNewUserFansList($range,$limit);    
        echo json_encode($data);         
    }

    //主播直播时长榜
    public function LoadEmceeLiveTimeList(){
        $CommonRedis = new CommonRedisController();
        $range = I('post.range');
        $limit = 10; 
        $data = $CommonRedis->getEmceeLiveTimeList($range,$limit);    
        echo json_encode($data);        
    }

    //用户在线时长榜
    public function LoadUserOnlineTimeList(){
        $CommonRedis = new CommonRedisController();
        $range = I('post.range');
        $limit = 10; 
        $data = $CommonRedis->getUserOnlineTimeList($range,$limit);    
        echo json_encode($data);        
    }    

    //运动大师排行榜
    public function LoadSportMastersList(){
        $CommonRedis = new CommonRedisController();
        $range = I('post.range');
        $limit = 10; 
        $data = $CommonRedis->getSportMastersList($range,$limit);    
        echo json_encode($data);        
    }

    //主播免费礼物榜
    public function LoadEmceeFreeGiftList(){
        $CommonRedis = new CommonRedisController();
        $range = I('post.range');
        $limit = 10;
        $data = $CommonRedis->getEmceeFreeGiftList($range,$limit);
        echo json_encode($data);
    }
}

?>