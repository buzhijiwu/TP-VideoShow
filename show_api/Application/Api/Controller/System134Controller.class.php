<?php
namespace Api\Controller;
use Think\Controller;
/**
 * 系统相关接口
 *
 * getTopList            获取排行榜数据
 *
 */
class System134Controller extends CommonController {

    /**
     * 获取发现页排行榜数据
     * 根据排行榜类型、时间范围和数据长度获得相应排行榜数据
     */
    public function getTopList($inputParams){
        $CommonRedis = new CommonRedisController();
        $range = $inputParams['range'];  //排行榜范围
        $limit = $inputParams['limit'];  //查找条数
        switch ($inputParams['toplist_type']) {
            case '0':  //主播收入榜
                $datalist = $CommonRedis->getTopEmceeEarnList($range,$limit);
                break;
            case '1':  //用户消费榜
                $datalist = $CommonRedis->getTopUserRichList($range,$limit);
                break;
            case '2':  //新增用户关注榜
                $datalist = $CommonRedis->getNewUserFansList($range,$limit);
                break;                  
            case '3':  //主播直播时长榜
                $datalist = $CommonRedis->getEmceeLiveTimeList($range,$limit);
                break;
            case '4':  //用户在线时长榜
                $datalist = $CommonRedis->getUserOnlineTimeList($range,$limit);
                break;
            default:  //运动大师榜
                $datalist = $CommonRedis->getSportMastersList($range,$limit);
        }
        $data = array(
            'status' => 200,
            'message' => lan('200', 'Api', $this->lanPackage),
            'datalist' => $datalist['data']
        );
        return $data;
    }	
}