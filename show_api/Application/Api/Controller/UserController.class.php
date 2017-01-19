<?php
namespace Api\Controller;
use Think\Controller;
/**
 * 用户相关接口
 *
 * 主要处理与user相关的业务逻辑
 * getMyInformation	    获取我的个人信息
 * getUserInfoByUserid  获取用户信息
 * getPrivileges		获取特权信息
 * getShareInfo		    获取分享信息
 * getMyTasks		    获取用户所参与的任务
 * getMyVipinfos		获取用户VIP信息
 * getMyMessages		获取用户消息记录
 * getFriendEmcees		获取用户关注列表支持分页查询
 * getMyFamily		    获取用户家族信息
 * getMySeeHistory		获取用户观看历史
 * getMyEquipments		获取用户的固定资产例如座驾信息
 * getBuyRecord		    获取购买记录
 * getRechargeRecord	获取用户充值记录
 * 
 * modifyUseEquipment	修改用户座驾
 * modifyUserInfo		修改用户昵称
 * delOrReadMessage	    删除消息,或将消息设置为已读 
 * modifyHeadPic        修改用户头像
 *
 */
class UserController extends CommonController {

	/**
	 * 获取我的个人信息
	 * @param userid: 当前用户userid
	 */
	public function getMyInformation133($inputParams){
		$userid = $inputParams['userid'];
		$userno = $inputParams['userno'];

		$where = array(
				'userid' => $userid
		);

		$db_Balance = D('Balance', 'Modapi');
		$memberinfo = D('Member', 'Modapi')->getMemberInfoByUserID($userid);  //用户信息
		$balanceinfo = $db_Balance->getBalanceByUserid($userid);  //余额信息
		$tasknumber = M('Mytask')->where($where)->count();  //任务数量
		$friendcount = D('Friend', 'Modapi')->getUserFriendCount($userid);  //粉丝数量
		$whereEquip['userid'] = $userid;
		$whereEquip['effectivetime'] = array('elt',date('Y-m-d H:i:s'));
		$whereEquip['expiretime'] = array('gt',date('Y-m-d H:i:s'));
		$equipnumber = M('Equipment')->where($whereEquip)->count();  //座驾数量
		$messagenumber =  M('Message')->where($where)->count();  //消息数量

		if ($memberinfo['isemcee'] == 1) {
			$emceeinfo = D('Emceeproperty', 'Modapi')->getEmceeProInfo($where);
			$levelwhere = array(
					'levelid' => $emceeinfo['emceelevel'],
					'leveltype' => 0,
					'lantype' => $this->lantype
			);
			$levelconf = D('Levelconfig', 'Modapi')->getLevelconfig($levelwhere);
		} else {
			$emceeinfo = array(
					'isforbidden' => '0'
			);
			$levelwhere = array(
					'levelid' => $memberinfo['userlevel'],
					'leveltype' => 1,
					'lantype' => $this->lantype
			);
			$levelconf = D('Levelconfig', 'Modapi')->getLevelconfig($levelwhere);
		}
		$memberinfo = array_merge($emceeinfo, $memberinfo,$levelconf);
		$vipid = D('Viprecord', 'Modapi')->getMyTopVipid($userid);

		$data['status'] = 200;
		$data['message'] = lan('200', 'Api', $this->lanPackage);
		$data['datalist'] = array(
				'userinfor' => $memberinfo,
				'balanceinfo' => $balanceinfo,
				'vipid' => (string)$vipid,
				'tasknumber' => $tasknumber,
				'friendcount' => $friendcount,
				'equipnumber' => $equipnumber,
				'messages' =>$messagenumber
		);
		return $data;
	}

    /**
     * 获取我的个人信息
     * @param userid: 当前用户userid     
     */	
    public function getMyInformation($inputParams){
	    $userid = $inputParams['userid'];
	    $userno = $inputParams['userno'];

	    $where = array(
	        'userid' => $userid
	    );
	    
	    $db_Balance = D('Balance', 'Modapi');
		$memberinfo = D('Member', 'Modapi')->getMemberInfoByUserID($userid);  //用户信息
	    $balanceinfo = $db_Balance->getBalanceByUserid($userid);  //余额信息
	    $tasknumber = M('Mytask')->where($where)->count();  //任务数量
	    $friendcount = D('Friend', 'Modapi')->getUserFriendCount($userid);  //粉丝数量    
	    $whereEquip['userid'] = $userid;
	    $whereEquip['effectivetime'] = array('elt',date('Y-m-d H:i:s'));
	    $whereEquip['expiretime'] = array('gt',date('Y-m-d H:i:s'));	        
	    $equipnumber = M('Equipment')->where($whereEquip)->count();  //座驾数量
	    $messagenumber =  M('Message')->where($where)->count();  //消息数量

        if ($memberinfo['isemcee'] == 1) {
            $emceeinfo = D('Emceeproperty', 'Modapi')->getEmceeProInfo($where);
        } else {
            $emceeinfo = array(
                'isforbidden' => '0'
            );
        }

        $vipid = D('Viprecord', 'Modapi')->getMyTopVipid($userid);

	    $data['status'] = 200;
	    $data['message'] = lan('200', 'Api', $this->lanPackage);
		$data['datalist'] = $memberinfo;
		$data['datalist']['showroomno'] = $this->getShowroomno($memberinfo);
		$data['datalist']['vipid'] = (string)$vipid;
		$data['datalist']['emceeinfo'] = $emceeinfo;
		$data['datalist']['equipmentinfo'] = D('Equipment','Modapi')
				->getMyUseEquipments($userid, $this->lantype);
		$data['datalist']['balance'] = $balanceinfo['balance'];
		$data['datalist']['spendmoney'] = $balanceinfo['spendmoney'];
		$data['datalist']['earnmoney'] = $balanceinfo['earnmoney'];
		$data['datalist']['point'] = $balanceinfo['point'];
		$data['datalist']['tasknumber'] = $tasknumber;
		$data['datalist']['friendcount'] = $friendcount;
		$data['datalist']['equipnumber'] = $equipnumber;
		$data['datalist']['messages'] = $messagenumber;
	    return $data;
    }

    /**
     * 根据userid获取用户信息
     * @param userid: 当前用户userid     
     */ 
    public function getUserInfoByUserid($inputParams){
		$userid = $inputParams['userid'];
		$db_Member = D('Member', 'Modapi');
		$userInfo = $db_Member->getMemberInfoByUserID($userid);
		$userInfo['showroomno'] = $this->getShowroomno($userInfo);
		$db_Viprecord = D('Viprecord', 'Modapi');
		$userInfo['vipid'] = $db_Viprecord->getMyTopVipid($userid);	

		$data['status'] = 200;
		$data['message'] = lan('200', 'Api', $this->lanPackage);
		$data['datalist'] = $userInfo; 
		return $data;   	
    } 

    /**
     * 获取特权信息
     * @param type: 类型, 0 VIP 1 守护
     * @param ownerid: 拥有者的id, 如：vipid,守护id
     * @param userid: 当前用户userid     
     */ 
    public function getPrivileges($inputParams){
        $type = $inputParams['type'];
        $ownerid = $inputParams['ownerid'];
        $userid = $inputParams['userid'];
        //获取特权信息     
		$db_Privilege = D('Privilege', 'Modapi');
		$privileges = $db_Privilege->getPrivileges4Display($ownerid, $type, $this->lantype);
		$db_Viprecord = D('Viprecord', 'Modapi');
		//获取用户当前vip信息
		$vips = array();
		if ($userid) {
		    $myvips = $db_Viprecord->getMyVips($userid, $this->lantype);
            foreach($myvips as $key => $val){
            	$vips[$val['vipid']]['vipid'] = $val['vipid'];
                $vips[$val['vipid']]['expiretime'] = $val['expiretime'];
                $vips[$val['vipid']]['vipname'] = $val['vipname'];
                $vips[$val['vipid']]['appsmallviplogo'] = $val['appsmallviplogo'];
                $vips[$val['vipid']]['appbigviplogo'] = $val['appbigviplogo'];
            }
            $vips = array_values($vips);
		}

		$data['status'] = 200;
		$data['message'] = lan('200', 'Api', $this->lanPackage);
		$data['privileges'] = $privileges;
		$data['myvips'] = $vips;
		return $data;	
    }  

    /**
     * 获取分享信息
     * @param userid: 用户userid   
     * @param emceeuserid: 主播userid      
     */
	public function getShareInfo($inputParams){
		$inputParams['sharetype'] = empty($inputParams['sharetype']) ? 0 : $inputParams['sharetype'];
		$db_Member = D('Member', 'Modapi');
		$emceeInfo = $db_Member->getMemberInfoByUserID($inputParams['emceeuserid']);

		$db_Sharedefinition = M('Sharedefinition');
		$whereCond = array(
			'sharetypeid' => $inputParams['sharetype'],
			'lantype' => $this->lantype
		);		
		$shareInfo = $db_Sharedefinition->where($whereCond)->find();
		$data['status'] = 200;
		$data['message'] = lan('200', 'Api', $this->lanPackage);
		$data['sharepath'] = $shareInfo['sharepath'];
		if ($inputParams['sharetype'] == 0) {
			$data['sharepath'] = $shareInfo['sharepath'].$emceeInfo['roomno'].'.html';
		}		
		$data['sharetitle'] = $shareInfo['sharetitle'];
		$data['sharedesc'] = $shareInfo['sharedesc'];
		$data['isdefault'] = $shareInfo['isdefault'];
		return $data;
	}  

    /**
     * 获取用户所参与的任务
     * @param userid: 当前用户userid     
     */
    public function getMyTasks($inputParams){
	    $userid = $inputParams['userid'];
	    $pageno = empty($inputParams['pageno']) ? 0 : $inputParams['pageno'];
	    $pagesize = empty($inputParams['pagesize']) ? 8 : $inputParams['pagesize'];
	    $db_Mytask = D('Mytask', 'Modapi');
	    $taskinfos = $db_Mytask->getAllMyTasks($userid, $this->lantype, $pageno, $pagesize);
        //是否结束加载: 0 未结束 1 结束
        $data['is_end'] = 0;
        $count = ($pageno + 1) * $pagesize;
        if ($count >= $taskinfos['total_count']) {
            $data['is_end'] = 1;
        }
	    $data['status'] = 200;
	    $data['message'] = lan('200', 'Api', $this->lanPackage);
	    $data['datalist'] = $taskinfos['data'];
	    return $data;
    }	

    /**
     * 获取用户VIP信息
     * @param userid: 用户userid     
     */
	public function getMyVipinfos($inputParams){
	    $userid = $inputParams['userid'];
	    $viprecords = D('Viprecord', 'Modapi')->getMyVips($userid,$this->lantype);
	    $data['status'] = 200;
	    $data['message'] = lan('200', 'Api', $this->lanPackage);
	    $data['datalist'] = $viprecords;
	    return $data;
	}    

    /**
     * 获取用户消息记录
     * @param userid: 用户userid     
     */
	public function getMyMessages($inputParams){
	    $userid = $inputParams['userid'];
	    $pageno = empty($inputParams['pageno']) ? 0 : $inputParams['pageno'];
	    $pagesize = empty($inputParams['pagesize']) ? 8 : $inputParams['pagesize'];
	    $lantype = $inputParams['lantype'] ? $inputParams['lantype'] : 'vi';

	    $where = array(
	        'userid' => $userid,
			'lantype' => $lantype
	    );
        //消息列表
	    $messages =  M('Message')
	        ->where($where)
	        ->limit($pageno*$pagesize.','.$pagesize)
	        ->select();
	    //所有消息数量
	    $total_count = M('Message')->where($where)->count();   
        //未读消息数量
        $where_unread = array(
            'userid' => $userid,
            'read' => 0,
			'lantype' => $lantype
        );
        $unread_message_count = M('Message')->where($where_unread)->count();

	    //是否结束加载: 0 未结束 1 结束
        $data['is_end'] = 0;
        $count = ($pageno + 1) * $pagesize;
        if ($count >= $total_count) {
            $data['is_end'] = 1;
        }
	    $data['status'] = 200;
	    $data['message'] = lan('200', 'Api', $this->lanPackage);
	    $data['datalist'] = $messages;
        $data['message_count'] = array(
            'total_count' => $total_count,
            'unread_count' => $unread_message_count
        );
	    return $data;
	}

    /**
     * 获取用户关注列表
     * @param userid: 用户userid
     * @param pageno: 页码
     * @param pagesize: 每页长度      
     */
    public function getFriendEmcees($inputParams){
	    $userid = $inputParams['userid'];
	    $pageno = empty($inputParams['pageno']) ? 0 : $inputParams['pageno'];
	    $pagesize = empty($inputParams['pagesize']) ? 8 : $inputParams['pagesize'];
	
	    $friendusers = D('Friend', 'Modapi')->getAllFriendUsers($userid, $pageno, $pagesize);
	    //是否结束加载: 0 未结束 1 结束
        $data['is_end'] = 0;
        $count = ($pageno + 1) * $pagesize;
        if ($count >= $friendusers['total_count']) {
            $data['is_end'] = 1;
        }
	    $data['status'] = 200;
	    $data['message'] = lan('200', 'Api', $this->lanPackage);
	    $data['datalist'] = $friendusers['data'];
	    return $data;
    }

    /**
     * 获取用户关注主播tags列表
     * @param userid: 用户userid
     */
    public function getFriendEmceeTags($inputParams){
        $userid = $inputParams['userid'];
        $where = array(
            'userid' => $userid,
            'status' => '0'
        );
        $result = M('Friend')->field('DISTINCT(emceeuserid)')->where($where)->select();
        foreach($result as $k){
            $list[] = 'emcee'.$k['emceeuserid'];
        }
        $data['status'] = 200;
        $data['message'] = lan('200', 'Api', $this->lanPackage);
        $data['datalist'] = $list;
        return $data;
    }

    /**
     * 获取用户家族信息
     * @param userid: 用户userid
     * @param pageno: 页码
     * @param pagesize: 每页长度
     */
    public function getMyFamily($inputParams){
	    $userid = $inputParams['userid'];
	    $where = array(
	        'userid' => $userid
	    );
	    $memberinfo = M('Member')->where($where)->field('familyid')->find();
	    $familyinfo = D('Family', 'Modapi')->getFamilyById($memberinfo['familyid']);
	    $data['status'] = 200;
	    $data['message'] = lan('200', 'Api', $this->lanPackage);
	    $data['datalist'] = $familyinfo;
	    return $data;
    } 

    /**
     * 获取用户观看历史
     * @param userid: 用户userid
     * @param pageno: 页码
     * @param pagesize: 每页长度     
     */
    public function getMySeeHistory($inputParams){
	    $userid = $inputParams['userid'];
	    $pageno = empty($inputParams['pageno']) ? 0 : $inputParams['pageno'];
	    $pagesize = empty($inputParams['pagesize']) ? 8 : $inputParams['pagesize'];
	
	    $where = array(
	        'userid' => $userid
	    );
	    $seehistorys = D('Seehistory', 'Modapi')
	        ->getAllSeeHisEmcees($userid, $pageno, $pagesize);
        //是否结束加载: 0 未结束 1 结束
        $data['is_end'] = 0;
        $count = ($pageno + 1) * $pagesize;
        if ($count >= $seehistorys['total_count']) {
            $data['is_end'] = 1;
        }
	    $data['status'] = 200;
	    $data['message'] = lan('200', 'Api', $this->lanPackage);
	    $data['datalist'] = $seehistorys['data'];
	    return $data;
    }  

    /**
     * 获取用户的固定资产例如座驾信息
     * @param userid: 用户userid
     * @param pageno: 页码
     * @param pagesize: 每页长度 
     */
	public function getMyEquipments($inputParams){
	    $userid = $inputParams['userid'];
	    $pageno = empty($inputParams['pageno']) ? 0 : $inputParams['pageno'];
	    $pagesize = empty($inputParams['pagesize']) ? 8 : $inputParams['pagesize'];
	
	    $equipments = D('Equipment', 'Modapi')->getMyEquipments($userid, $this->lantype, $pageno, $pagesize);
        //是否结束加载: 0 未结束 1 结束
//        $data['is_end'] = 0;
//        $count = ($pageno + 1) * $pagesize;
//        if ($count >= $equipments['total_count']) {
//            $data['is_end'] = 1;
//        }
        $data['is_end'] = 1;    //一个座驾存在多条记录，所以不再分页
	    $data['status'] = 200;
	    $data['message'] = lan('200', 'Api', $this->lanPackage);
	    $data['datalist'] = $equipments;
	    return $data;
	} 

    /**
     * 获取用户消费记录
     * @param userid: 用户userid
     * @param pageno: 页码
     * @param pagesize: 每页长度 
     */
    public function getBuyRecord($inputParams){
	    $userid = $inputParams['userid'];
	    $pageno = empty($inputParams['pageno']) ? 0 : $inputParams['pageno'];
	    $pagesize = empty($inputParams['pagesize']) ? 8 : $inputParams['pagesize'];
		$queryCond['userid'] = $userid;
		$queryCond['tradetype'] = array('in', '2,6,7');  //tradetype: 2 购买商品 6 靓号 7 vip
		$db_Spenddetail = D('Spenddetail', 'Modapi');
		$result = $db_Spenddetail->getConsumeList($queryCond, $pageno, $pagesize);
        //是否结束加载: 0 未结束 1 结束
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
     * 获取用户充值记录
     * @param userid: 用户userid
     * @param pageno: 页码
     * @param pagesize: 每页长度 
     */    
    public function getRechargeRecord($inputParams){
	    $userid = $inputParams['userid'];
	    $pageno = empty($inputParams['pageno']) ? 0 : $inputParams['pageno'];
	    $pagesize = empty($inputParams['pagesize']) ? 8 : $inputParams['pagesize'];    	
		$db_Rechargedetail = D('Rechargedetail', 'Modapi');
		$rechargedetails = $db_Rechargedetail
		    ->getRechargeDetailByUserid($userid, $pageno, $pagesize);
        //用户秀币余额
		$db_Balance = D('Balance', 'Modapi');
		$balance = $db_Balance->getBalanceByUserid($userid);
        //是否结束加载: 0 未结束 1 结束
        $data['is_end'] = 0;
        $count = ($pageno + 1) * $pagesize;
        if ($count >= $rechargedetails['total_count']) {
            $data['is_end'] = 1;
        }
		$data['status'] = 200;
		$data['message'] = lan('200', 'Api', $this->lanPackage);
		$data['datalist'] = array(
				'rechargerecord' => $rechargedetails['data'],
				'balance' => $balance['balance']
		);		
		return $data;
    }	

    /**
     * 修改用户的座驾
     * @param userid: 用户userid     
     * @param newequipid: 新座驾id
     * @param oldequipid: 原座驾id
     */
    public function modifyUseEquipment($inputParams){
		$userid = $inputParams['userid'];  	
		$newequipid = $inputParams['newequipid'];
		$db_Equipment = M('Equipment');
        //根据equipid获取用户新座驾commodityid
        $newCommodityid = $db_Equipment->where(array('equipid'=>$newequipid))->getField('commodityid');
        //将原座驾状态变成不在使用
		$oldUseData['isused'] = 0;
        $notUsedCond = array(
            'userid' => $userid,
            'isused' => 1
        );        
		$oldUseResult = $db_Equipment
		    ->where($notUsedCond)
		    ->save($oldUseData);
        if($oldUseResult === false ){
            $data['status'] = -1;
            $data['message'] = lan('-1', 'Api', $this->lanPackage);
            return $data;
        }
		//将新座驾状态变成在使用
        $newUseData['isused'] = 1;
        $newUsedCond = array(
            'userid' => $userid,
            'commodityid' => $newCommodityid,
            'expiretime' => array('gt',date('Y-m-d H:i:s'))
        );
		$newUseResult = $db_Equipment
		    ->where($newUsedCond)
		    ->save($newUseData);
        if($newUseResult === false ){
            $data['status'] = -1;
            $data['message'] = lan('-1', 'Api', $this->lanPackage);
            return $data;
        }

        $data['status'] = 200;
        $data['message'] = lan('200', 'Api', $this->lanPackage);
        return $data;
    } 

    /**
     * 修改用户信息
     * @param userid: 用户userid     
     * @param nickname: 昵称
     * @param sex: 性别
     * @param birthday: 生日 
     */
    public function modifyUserInfo($inputParams){
		$userid = $inputParams['userid'];  	
		$nickname = trim($inputParams['nickname']);  	
		$sex = $inputParams['sex']; 
		$birthday = $inputParams['birthday'];
        //验证用户昵称是否包含脏话
        $filterWords = require_once('./Application/Api/Common/Language/filterWords.php');
        $arr = array();
        foreach ($filterWords as $k => $val) {
            $arr = explode(' ', $val);
            $str = '';
            foreach ($arr as $key => $value) {
                $str = $str.'('.$value.')|';
            }
            $str = substr($str,0,strlen($str)-1);
            if (preg_match('/^'.$str.'$/',$nickname)) {
                $data['status'] = 401005;
                $data['message'] = lan('401005','Api',$this->lanPackage);
                return $data;
            }
        }		
        //验证用户昵称是否有特殊字符，目前只过滤"<"、">"、"/"、"\"、"'"、"""、"?"
        if (preg_match("/<|>|\/|\\\\|\'|\"|\?/",$nickname)) {
            $data['status'] = 401001;
            $data['message'] = lan('401001','Api',$this->lanPackage);
            return $data;
        }
        //验证昵称长度
        if (strlen($nickname)>50 || empty($nickname)) {
            $data['status'] = 401002;
            $data['message'] = lan('401002','Api',$this->lanPackage);
            return $data;
        }
		$db_Member = M('Member');
        //验证用户昵称是否存在
        $nicknameCond['_string'] = 'BINARY nickname = "'.$nickname.
            '" AND userid != '.$userid;
        $userNickInfo = $db_Member
            ->field('userid')
            ->where($nicknameCond)
            ->find();
        if ($userNickInfo) {
            $data['status'] = 401004;
            $data['message'] = lan('401004','Api',$this->lanPackage);
            return $data;          
        }        

		$editData['nickname'] = $nickname;
		$editData['sex'] = $sex;
		$editData['birthday'] = $birthday;
		$result = $db_Member
		    ->where(array('userid' => $userid))
		    ->save($editData);
        if ($result === false) {
		    $data['status'] = -1;
		    $data['message'] = lan('-1', 'Api', $this->lanPackage);
        }else{
		    $data['status'] = 200;
		    $data['message'] = lan('200', 'Api', $this->lanPackage);
        }
        return $data;
    }  

    /**
     * 删除消息,或将消息设置为已读
     * @param userid: 用户userid     
     * @param messageids: 消息id
     * @param type: 操作类型 0 删除 1 设为已读     
     */
    public function delOrReadMessage($inputParams){
		$db_Message = M('Message');
		$userid = $inputParams['userid'];
		$type = $inputParams['type'];
		$messageids = $inputParams['messageids'];
		$updateCond['messageid'] = array('in', trim($messageids));
		$updateCond['userid'] = array('eq', $userid);		
        switch ($type) {
        	case 1:  //设为已读
			    $newMegInfo['read'] = 1;
			    $result = $db_Message->where($updateCond)->save($newMegInfo);
        		break;
        	case 0:  //删除
			    $result = $db_Message->where($updateCond)->delete();
        		break;
        	default:
        	    break;	
        }
        if ($result === false) {
			$data['status'] = -1;
			$data['message'] = lan('-1', 'Api', $this->lanPackage);
			return $data;
        }
		$data['status'] = 200;
		$data['message'] = lan('200', 'Api', $this->lanPackage);
        return $data;  	
    }

    /**
     * 修改用户头像
     * @param userid: 用户userid
     * @param type：设1:表示修改大头像，其他值表示修改小头像
     */
    public function modifyHeadPic($inputParams){
        $type = $inputParams['type'] ? $inputParams['type'] : 0;
        $userid = $inputParams['userid'] ? $inputParams['userid'] : 0;

        if ($type == 1) {
            $filePath = '/Uploads/HeadImg/268200/';
        }else{
            $filePath = '/Uploads/HeadImg/120120/';
        }
        //文件上传远程服务器
        $file = 'file';
        $fileName = date('YmdHis').'_'.$userid;
        $ftpFile = ftpFile($file, $filePath, $fileName);
        if($ftpFile['code'] != 200){
            $data['status'] = 401003;
            $data['message'] = lan('401003', 'Api', $this->lanPackage);
            return $data;
        }
        $fileurl = $ftpFile['msg'];

        //上传成功保存数据
        $dbMember = M('Member');
        $whereUserid = array(
            'userid' => $userid
        );
        $userInfo = $dbMember->where($whereUserid)->find();
        if ($type == 1) {
            //修改大头像
            $editData['bigheadpic'] = $fileurl;
            $result = $dbMember->where($whereUserid)->save($editData);
            if($result === false){
                $data['status'] = -1;
                $data['message'] = lan('-1', 'Api', $this->lanPackage);
                return $data;
            }

            //保存成功删除老图片
            $oldbigheadpic = $userInfo['bigheadpic'];
            if ($editData['bigheadpic'] != $oldbigheadpic) {
                ftpDelete($oldbigheadpic);  //删除老图片
            }
        } else {
            //修改小头像
            $editData['smallheadpic'] = $fileurl;
            $result = $dbMember->where($whereUserid)->save($editData);
            if($result === false){
                $data['status'] = -1;
                $data['message'] = lan('-1', 'Api', $this->lanPackage);
                return $data;
            }

            //保存成功删除老图片
            $oldsmallheadpic = $userInfo['smallheadpic'];
            if ($editData['smallheadpic'] != $oldsmallheadpic && $oldsmallheadpic != '/Public/Public/Images/HeadImg/default.png') {
                ftpDelete($oldsmallheadpic);    //删除老图片
            }
        }

        //返回数据
        $data['status'] = 200;
        $data['message'] = lan('200', 'Api', $this->lanPackage);
        $data['headpicpath'] = $editData['smallheadpic'];
        $data['bigheadpic'] = $editData['bigheadpic'];
        return $data;
    }

    /**
     * 获取用户余额
     * @param userid: 用户userid     
     */
    public function getUserBalance($inputParams){
    	$userid = $inputParams['userid'];
        //当前用户余额(秀币)
        $db_Balance = M('Balance');
        $where['userid'] = $userid;
		$balance = $db_Balance
		    ->where($where)
		    ->getField('balance');
		$data['status'] = 200;
		$data['message'] = lan('200', 'Api', $this->lanPackage);
		$data['balance'] = $balance;
		return $data;
    }
}