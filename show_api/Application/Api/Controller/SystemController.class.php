<?php
namespace Api\Controller;
use Think\Controller;
/**
 * 系统相关接口
 *
 * 主要获取首页、发现页、商城的列表信息
 * getSystemInfo         获取系统信息
 * getIndexEmcees        获取首页主播
 * getRollpic            获取轮播图 
 * searchEmcee           搜索主播 
 * topSearchEmcee        热搜推荐 
 * getTopListRank        获取排行榜列表
 * getTopList            获取排行榜数据
 * getNearbyEmcces       获取附近主播
 * getMallInformation    获取APP商城信息
 *
 */
class SystemController extends CommonController {

    /**
     * APP入口，获取系统信息
     * @param devicetype：设备类型 0 安卓 1 iOS 2PC
     */
    public function getSystemInfo($inputParams){
        $devicetype = $inputParams['devicetype'];

        $data['status'] = 200;
        $data['message'] = lan('200', 'Api', $this->lanPackage);
        $data['datalist'] = array(
            'domainname'  => $this->getSystemInfoList('DOMAIN_NAME' , $this->lantype),
            'rtmppath'    => $this->getSystemInfoList('RTMP_PATH' , $this->lantype),
            'nodejspath'  => $this->getSystemInfoList('NODEJS_NEW_PATH' , $this->lantype),
            'loadimgpath' => $this->getSystemInfoList('LOAD_IMG_PATH' , $this->lantype),
            'editimgpath' => $this->getSystemInfoList('EDIT_IMG_PATH' , $this->lantype),
            'shutuptime'  => $this->getSystemInfoList('SHUTUP_TIME' , $this->lantype),
            'apppushpath'    => $this->getSystemInfoList('APP_PUSH_PATH' , $this->lantype),
            'showtopten'    => $this->getSystemInfoList('SHOW_TOP_TEN' , $this->lantype),
            'registeragreement'  => $this->getSystemInfoList('REGISTER_AGREEMNET_PATH' , $this->lantype),
            'rechargeagreement'  => $this->getSystemInfoList('RECHARGE_AGREEMNET_PATH' , $this->lantype),
            'emceesignagreement'  => $this->getSystemInfoList('EMCEE_SIGN_AGREEMNET_PATH' , $this->lantype),
            'allowshowuserlevel' => $this->getSystemInfoList('ALLOW_SHOW_USERLEVEL' , 'vi'),
            'allowshowtime' => $this->getSystemInfoList('ALLOW_SHOW_TIME' , 'vi'),
            'exchangeagreement' => $this->getSystemInfoList('EXCHANGE_AGREEMENT' , $this->lantype),
            'screenshotslimit' => $this->getSystemInfoList('SCREENSHOTS_LIMIT' , $this->lantype),
        );

        if ($devicetype == 0) { //安卓
            $data['datalist']['audiobitrate'] = $this->getSystemInfoList('ANDROID_AUDIO_BITRATE' , 'vi');
            $data['datalist']['videowidth'] = $this->getSystemInfoList('ANDROID_VIDEO_WIDTH' , 'vi');
            $data['datalist']['videoheight'] = $this->getSystemInfoList('ANDROID_VIDEO_HEIGHT' , 'vi');
            $data['datalist']['videofps'] = $this->getSystemInfoList('ANDROID_VIDEO_FPS' , 'vi');
            $data['datalist']['videobitrate'] = $this->getSystemInfoList('ANDROID_VIDEO_BITRATE' , 'vi');
            $data['datalist']['imgprocparam'] = $this->getSystemInfoList('ANDROID_IMG_PROC_PARAM' , 'vi');
            $data['datalist']['hidesportgame'] = $this->getSystemInfoList('ANDROID_HIDE_SOPRT_GAME' , 'vi');
            $data['datalist']['newsdkdistribute'] = $this->getSystemInfoList('ANDROID_NEW_SDK_DISTRIBUTE' , 'vi');
        } else {    //iOS
            $data['datalist']['audiobitrate'] = $this->getSystemInfoList('IOS_AUDIO_BITRATE' , 'vi');
            $data['datalist']['videowidth'] = $this->getSystemInfoList('IOS_VIDEO_WIDTH' , 'vi');
            $data['datalist']['videoheight'] = $this->getSystemInfoList('IOS_VIDEO_HEIGHT' , 'vi');
            $data['datalist']['videofps'] = $this->getSystemInfoList('IOS_VIDEO_FPS' , 'vi');
            $data['datalist']['videobitrate'] = $this->getSystemInfoList('IOS_VIDEO_BITRATE' , 'vi');
            $data['datalist']['imgprocparam'] = $this->getSystemInfoList('IOS_IMG_PROC_PARAM' , 'vi');
            $data['datalist']['hidesportgame'] = $this->getSystemInfoList('IOS_HIDE_SOPRT_GAME' , 'vi');
        }

        //获取版本相关信息
        $versioninfo = M('versioninfo')->where(array('lantype' => $this->lantype))->order('id DESC')->find();
        if ($devicetype == 0) {
            //安卓apk下载地址
            $android_download_link = '';
            $distributeid = (int)$inputParams['distributeid'];   //应用市场渠道
            if ($distributeid) {
                $where_versionapk = array(
                    'versioninfoid' => $versioninfo['versioninfoid'],
                    'distributeid' => $distributeid,
                );
                $android_download_link = M('versionapk')->where($where_versionapk)->getField('download_link');
            }
            if (!$android_download_link) {
                $android_download_link = $versioninfo['android_download_link'];
            }
            $data['versioninfo'] = array(
                'android_new_version'  =>  $versioninfo['android_new_version'], //安卓最新版本
                'android_download_link'  =>  $android_download_link, //安卓下载链接
                'android_apk_size'  =>  $versioninfo['android_apk_size'], //安卓最新apk大小
                'android_new_code'  =>  (int)$versioninfo['android_new_code'], //安卓最新code
                'android_forced_upgrade_code'  =>  (int)$versioninfo['android_forced_upgrade_code'], //安卓强制升级code
                'android_released_time'  =>  $versioninfo['android_released_time'], //安卓发布时间
                'android_note'  =>  $versioninfo['android_note'], //安卓升级说明
            );
        } else {
            $data['versioninfo'] = array(
                'ios_new_version'  =>  (int)$versioninfo['ios_new_version'], //iOS最新版本
                'ios_forced_upgrade_version'  =>  (int)$versioninfo['ios_forced_upgrade_version'], //iOS强制升级版本
                'ios_download_link'  =>  $versioninfo['ios_download_link'], //iOS下载链接
                'ios_released_time'  =>  $versioninfo['ios_released_time'], //iOS发布时间
                'ios_note'  =>  $versioninfo['ios_note'], //iOS升级说明
            );
        }
        return $data;
    }

    /**
     * 获取APP首页主播
     * 根据type获取主播列表(热门、最新、关注)
     */	
    public function getIndexEmcees($inputParams){
        $userid = $inputParams['userid'];
        $pageno = $inputParams['pageno'] ? $inputParams['pageno'] : 0;
        $pagesize = $inputParams['pagesize'] ? $inputParams['pagesize'] : 10;
        $type = $inputParams['type'];        
        switch ($type) {
        	case 'new': //最新
        		$result = D('Emceeproperty', 'Modapi')->getNewEmceesList($pageno,$pagesize);
        		break;
        	case 'follow': //关注
        		$result = D('Emceeproperty', 'Modapi')->getFollowEmceesList($userid,$pageno,$pagesize);
        		break;        	
        	default: //热门
        		$result = D('Emceeproperty', 'Modapi')->getHotEmceesList($pageno,$pagesize);
        }

        $data['is_end'] = 0;
        $count = ($pageno + 1) * $pagesize;
        if ($count >= $result['total_count']) {
            $data['is_end'] = 1;
        }
        $data['status'] = 200;
        $data['message'] = lan('200', 'Api', $this->lanPackage);
        $data['datalist'] = $result['data'];
        return $data;
    }

    /**
     * 获取发现页排行榜列表
     */
    public function getTopListRank135($inputParams){
        $field = array('toplistid', 'toplistname', 'iostoplistpic', 'androidtoplistpic', 'lantype');
        $map = array(
            'lantype' => $this->lantype,
            'isshow' => 1
        );
        $order = 'sort';
        $result = M('Toplist')
            ->where($map)
            ->field($field)
            ->order($order)
            ->select();

        foreach ($result as $k => $val) {
            $val['iostoplistpic'] = str_replace('.png', '_135.png', $val['iostoplistpic']);
            $result[$k]['iostoplistpic'] = $val['iostoplistpic'];
        }

        $data['status'] = 200;
        $data['message'] = lan('200', 'Api', $this->lanPackage);
        $data['datalist'] = $result;
        return $data;
    }

    /**
     * 获取发现页排行榜列表
     */
    public function getTopListRank($inputParams){
        $field = array('toplistid', 'toplistname', 'iostoplistpic', 'androidtoplistpic', 'lantype');
        $map = array(
            'lantype' => $this->lantype,
            'isshow' => 1
        );
        $order = 'sort';
        $result = M('Toplist')
            ->where($map)
            ->field($field)
            ->order($order)
            ->select();
        $data['status'] = 200;
        $data['message'] = lan('200', 'Api', $this->lanPackage);
        $data['datalist'] = $result;
        return $data;
    }

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
            case '2':  //主播直播时长榜
                $datalist = $CommonRedis->getEmceeLiveTimeList($range,$limit);
                break;
            case '3':  //用户在线时长榜
                $datalist = $CommonRedis->getUserOnlineTimeList($range,$limit);
                break;
            case '4':  //新增用户关注榜
                $datalist = $CommonRedis->getNewUserFansList($range,$limit);
                break;
            case '5':  //运动大师榜
                $datalist = $CommonRedis->getSportMastersList($range,$limit);
                break;
            case '6':  //主播免费礼物榜
                $datalist = $CommonRedis->getEmceeFreeGiftList($range,$limit);
                break;
            default:
                $datalist = $CommonRedis->getTopEmceeEarnList($range,$limit);
        }
        $data = array(
            'status' => 200,
            'message' => lan('200', 'Api', $this->lanPackage),
            'datalist' => $datalist
        );
        return $data;
    }

    /**
     * 搜索主播
     * 根据昵称、房间号搜索主播
     */
    public function searchEmcee($inputParams){
        $nickname = $inputParams['nickname'];
        $roomno = $inputParams['roomno'];
        $pageno = empty($inputParams['pageno']) ? 0 : $inputParams['pageno'];
        $pagesize = empty($inputParams['pagesize']) ? 8 : $inputParams['pagesize'];
        $db_Emceeproperty = D('Emceeproperty', 'Modapi');

        if ($nickname) {
            $emcees = $db_Emceeproperty->searchEmceeByNickname($nickname, $pageno, $pagesize);
        } else if ($roomno) {
            $emcees = $db_Emceeproperty->searchEmceeByRoomno($roomno, $pageno, $pagesize);
        }

        $data['is_end'] = 0;
        $count = ($pageno + 1) * $pagesize;
        if ($count >= $emcees['total_count']) {
            $data['is_end'] = 1;
        }
        $data['status'] = 200;
        $data['message'] = lan('200', 'Api', $this->lanPackage);
        $data['datalist'] = $emcees['data'];
        return $data;
    }

    /**
     * 热搜推荐
     * 获取热门搜索的主播
     */
	public function topSearchEmcee($inputParams)
	{
		$db_Emceeproperty = D('Emceeproperty', 'Modapi');
		$emcees = $db_Emceeproperty->topSearchEmcee();
		$data['status'] = 200;
		$data['message'] = lan('200', 'Api', $this->lanPackage);
		$data['datalist'] = $emcees;
		return $data;
	} 

    /**
     * 附近主播
     * 根据经纬度查询附近主播
     */
	public function getNearbyEmcces($inputParams){
		$longitude = $inputParams['longitude'];
	    $latitude = $inputParams['latitude'];
	    $pageno = $inputParams['pageno'];
	    $pagesize = $inputParams['pagesize'];
	    $db_Emceeproperty = D('Emceeproperty', 'Modapi');
	    $nearemcees = $db_Emceeproperty->getNearbyEmcees($longitude, $latitude, $pageno, $pagesize);

        $data['is_end'] = 0;
        $count = ($pageno + 1) * $pagesize;
        if ($count >= $nearemcees['total_count']) {
            $data['is_end'] = 1;
        }
        //没有查询到附近主播返回热门主播
        if ($nearemcees['total_count'] == 0) {
            $nearemcees = $db_Emceeproperty->getHotEmceesList();
            $data['is_end'] = 1;
        }

        $data['status'] = 200;
        $data['message'] = lan('200', 'Api', $this->lanPackage);
        $data['datalist'] = $nearemcees['data'];
        return $data;
	}

    /**
     * 获取轮播图
     * 获取APP当前语言下的所有轮播图和活动
     * @param devicetype: 设备类型
     */
    public function getRollpic($inputParams){
        $devicetype = (int)$inputParams['devicetype'] ? (int)$inputParams['devicetype'] : 0;
		$db_Rollpic = D('Rollpic' ,'Modapi');
		$result = $db_Rollpic->getRollpic($this->lantype,$devicetype);
        $data['status'] = 200;
        $data['message'] = lan('200', 'Api', $this->lanPackage);
        //轮播图
        $data['datalist'] = $result;
        //活动
        $activity = D('Activity', 'Modapi')->getActivity($this->lantype,$devicetype);
        $data['activity'] = $activity;
		return $data;	
    }

    /**
     * 获取APP商城信息
     * @param lantype：语言类型
     * @param devicetype: 设备类型 0 安卓 1 iOS
     * @param cateid: 菜单类别id
     * @param userid: 当前用户userid
     * @param niceno: 要搜索的靓号（非必传）
     */
    public function getMallInformation($inputParams){
	    $devicetype = $inputParams['devicetype'];
	    $pageno = 0;
	    $pagesize =20;
	    if ($inputParams['pageno'] != '') {
	        $pageno = $inputParams['pageno'];
	    }
	    if ($inputParams['pagesize'] != '') {
	        $pagesize = $inputParams['pagesize'];
	    }
	    $cateid = $inputParams['cateid'];
	    $userid = $inputParams['userid'];	    
        $niceno = $inputParams['niceno'];

        //当前用户余额(秀币)
        $db_Balance = D('Balance', 'Modapi');
		$balance = $db_Balance->getBalanceByUserid($userid);

        $nicenowhere['isused'] = 0;
        if ($niceno) {
            $nicenowhere['niceno'] = array('like', '%'.$niceno.'%');
        }
        $countAll = M('Nicenumber')->where($nicenowhere)->count(); 		
	    
	    $data['status'] = 200;
	    $data['message'] = lan('200', 'Api', $this->lanPackage);
	    $data['balance'] = $balance['balance'];	    
	    switch ($cateid) {
            case '1000':
                $data['datalist'] = array(
                    'is_end' => 1,
                    'cateid' => $cateid,
                    'catename' => lan('CAR', 'Api', $this->lantype),
                    'mallcontens' => D('Commodity', 'Modapi')->getAllMotoring(1, $this->lantype)
                );
                break;
            case '1001':
                $data['datalist'] = array(
                    'is_end' => 1,
                    'cateid' => $cateid,
                    'catename' => lan('VIP', 'Api', $this->lantype),
                    'mallcontens' => D('Vipdefinition', 'Modapi')->getAllVips($this->lantype)
                );
                break;
            case '1002':
                $nicenos = D('Nicenumber', 'Modapi')->getAllNicenos($pageno, $pagesize, $this->lantype, $niceno);
                $data['datalist'] = array(
                    'is_end' => 0,
                    'cateid' => $cateid,
                    'catename' => lan('NICENO', 'Api', $this->lantype),
                    'mallcontens' => $nicenos,
                    'numdesc' => $this->getSystemInfoList('NICENO_DESC' , $this->lantype)
                );    
                $count = ($pageno + 1) * $pagesize;
                if ($count >= $countAll) {
                    $data['datalist']['is_end'] = 1;
                }                           
                break;
            case '1003':
				$data['datalist'] = array(
                    'is_end' => 1,
                    'cateid' => $cateid,
                    'catename' => lan('RECHARGE', 'Api', $this->lantype),
                    'mallcontens' => D('Rechargedefinition', 'Modapi')->getAllReDefinitions($devicetype, $this->lantype)
                );
                break;
            case '1004':
                $data['datalist'] = array(
                    'is_end' => 1,
                    'cateid' => $cateid,
                    'catename' => lan('GUARD', 'Api', $this->lantype),
                    'mallcontens' => D('Guarddefinition', 'Modapi')->getAllGuards($this->lantype)
                );
                break;
            default:
                $nicenos = D('Nicenumber', 'Modapi')->getAllNicenos($pageno, $pagesize, $this->lantype, $niceno);
                $nicenosarr = array(
                    'is_end' => 0,
                    'cateid' => $cateid,
                    'catename' => lan('NICENO', 'Api', $this->lantype),
                    'nicenos' => $nicenos,
                    'numdesc' => $this->getSystemInfoList('NICENO_DESC' , $this->lantype)
                );
                $count = ($pageno + 1) * $pagesize;
                if ($count >= $countAll) {
                    $nicenosarr['is_end'] = 1;
                } 
                
				$data['datalist'] = array(
                    array(
                        'is_end' => 1,
                        'cateid' => '1000',
                        'catename' => lan('CAR', 'Api', $this->lantype),
                        'cars' => D('Commodity', 'Modapi')->getAllMotoring(1, $this->lantype)
                    ),
                    array(
                        'is_end' => 1,
                        'cateid' => '1001',
                        'catename' => lan('VIP', 'Api', $this->lantype),
                        'vips' => D('Vipdefinition', 'Modapi')->getAllVips($this->lantype)
                    ),
                    $nicenosarr,
                    array(
                        'is_end' => 1,
                        'cateid' => '1003',
                        'catename' => lan('RECHARGE', 'Api', $this->lantype),
                        'rechannels' => D('Rechargedefinition', 'Modapi')->getAllReDefinitions($devicetype, $this->lantype)
                    ),
                    array(
                        'is_end' => 1,
                        'cateid' => '1004',
                        'catename' => lan('GUARD', 'Api', $this->lantype),
                        'guards' => D('Guarddefinition', 'Modapi')->getAllGuards($this->lantype)
                    )
                );
                break;
        }
        return $data;
    } 

    /**
     * 获取过滤的脏话列表
     */
    public function getFilterWords(){
        $data['status'] = 200;
        $data['message'] = lan('200', 'Api', $this->lanPackage);
        $filterWords = require_once('./Application/Api/Common/Language/filterWords.php');
        $data['filterWords'] = $filterWords;
        return $data;
    }

	/**
	 * 获取系统信息方法
     * @param key: 键名
     * @param lantype: 语言类型
	 */
	private function getSystemInfoList($key,$lantype){
        $where = array(
			'key' => $key,
			'lantype' => $lantype            
        );
        $db_Systemset = M('Systemset');
        $result = $db_Systemset->where($where)->find();
        return $result['value'];
	}       
}