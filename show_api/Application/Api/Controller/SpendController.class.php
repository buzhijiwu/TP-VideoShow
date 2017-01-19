<?php
namespace Api\Controller;
use Think\Model;
use Think\Controller;
/**
 * 消费相关接口
 *
 * 主要处理与购买消费相关的业务逻辑
 * buyVip		   购买VIP
 * buyEquipment    购买座驾
 * buyNiceno	   购买靓号
 * buyGuard        购买守护
 * buySeat	       购买沙发
 * sendGift		   赠送礼物
 *
 */
class SpendController extends CommonController {

    /**
     * 购买VIP
     * @param userid: 用户userid     
     * @param vipid: vipid
     * @param duration: 时长 月
     */
    public function buyVip($inputParams){
    	$userid = $inputParams['userid'];    	
    	$vipid = $inputParams['vipid'];
    	$duration = $inputParams['duration'];

		$db_Vipdefinition = D('Vipdefinition', 'Modapi');
		$vip = $db_Vipdefinition->getVipByVipid($vipid, $this->lantype);
		//获取折扣
		$db_Discount = D('Discount', 'Modapi');
		$comtype = 1;  //商品类型 1 vip 2 座驾 3 守护 4 靓号
		$discount = $db_Discount->getDiscount($comtype, $duration);
		if (!$discount) {
			$discount = 1;
		}
		//商品总价
		$spendamount = $vip['vipprice'] * $duration * $discount;
		//检查用户余额
		$checkBalanceResult = $this->checkUserBalance($userid, $spendamount);
		if (200 == $checkBalanceResult['status']) {
			return $this->doBuyVip($inputParams, $vip, $spendamount);
		} else {
			return $checkBalanceResult;
		}
    }

	/**
     * 购买VIP	
	 * @param $inputParams: 请求参数
	 * @param $vip: vip信息
	 * @param $spendamount: 商品总价
	 */
	private function doBuyVip($inputParams, $vip, $spendamount){
		$db_Member = D('Member', 'Modapi');
		$userInfo = $db_Member->getMemberInfoByUserID($inputParams['userid']);
		$tran = new Model();
		$tran->startTrans();
		//插入spenddetail数据
		$spenddetail = array(
			'userid' => $inputParams['userid'],
			'targetid' => $inputParams['targetid'],
			'familyid' => $userInfo['familyid'],
			'tradetype' => 7,  //购买vip
			'giftid' => $vip['vipid'],
			'giftname' => $vip['vipname'],
			'gifticon' => $vip['pcsmallviplogo'],
			'giftprice' => $vip['vipprice'],
			'giftcount' => $inputParams['duration'],  //购买时长 月
			'spendamount' => $spendamount,
			'tradetime' => date('Y-m-d H:i:s'),
			'status' => 1  //交易成功
		);
		$spendResult = $this->processSpendRecordWithTrans($tran, $spenddetail);
		//更新余额
		$balance = array(
				'balance' => array('exp', 'balance-'.$spendamount),
				'spendmoney' => array('exp', 'spendmoney+'.$spendamount),
		);
		$balanceResult = $tran
		    ->table('ws_balance')
		    ->where('userid=' . $inputParams['userid'])
		    ->save($balance);
		//获取余额信息
		$balance = D('Balance', 'Modapi')
		    ->getBalanceByUserid($inputParams['userid']);
        //添加viprecord数据
		$hasViprecord = D('Viprecord', 'Modapi')->getViprecordByUseridAndVipid($inputParams['userid'], $vip['vipid']);
        if($hasViprecord){
            $viprecord['effectivetime'] = $hasViprecord['expiretime'];
            $viprecord['expiretime'] = date("Y-m-d H:i:s",strtotime('+'.$inputParams['duration'].' months',strtotime($hasViprecord['expiretime'])));
        }else{
            $viprecord['effectivetime'] = date('Y-m-d H:i:s');
            $viprecord['expiretime'] = date("Y-m-d H:i:s",strtotime('+'.$inputParams['duration'].' months', time()));
        }
        $viprecord['userid'] = $inputParams['userid'];
        $viprecord['vipid'] = $vip['vipid'];
        $viprecord['vipname'] = $vip['vipname'];
        $viprecord['pcsmallvippic'] = $vip['pcsmallviplogo'];
        $viprecord['appsmallvippic'] = $vip['appsmallviplogo'];
        $viprecord['spendmoney'] = $spendamount;
        $viprecordResult = $tran->table('ws_viprecord')->add($viprecord);

		//member表是否vip状态改为1
        $userNewInfo['isvip'] = 1;
		//用户等级
		$userNewLevel = D('Levelconfig', 'Modapi')->getUserLevelBySpendMoney($balance['spendmoney'], $this->lantype);
		$userNewInfo['userlevel'] = $userInfo['userlevel'];
		if ($userNewLevel && $userNewLevel != $userInfo['userlevel']) {
		    $userNewInfo['userlevel'] = $userNewLevel;
		}
		//更新用户信息		
		$userInfoResult = $tran->table('ws_member')
		    ->where('userid=' . $inputParams['userid'])
		    ->save($userNewInfo);

		if ($spendResult && $balanceResult && $viprecordResult) {
			$tran->commit();
		    $data['status'] = 200;
		    $data['message'] = lan('BUY_SUCCESS', 'Api', $this->lantype);
            if (!$balance['balance']) {
                $balance['balance'] = 0;
            }
            $data['balance'] = $balance['balance'];
		} else {
			$tran->rollback();
		    $data['status'] = -1;
		    $data['message'] = lan('-1', 'Api', $this->lanPackage);
		}
		return $data;
	}

    /**
     * 购买座驾
     * @param userid: 用户userid     
     * @param comid: 座驾id
     * @param duration: 时长 月
     */
    public function buyEquipment($inputParams){
    	// $inputParams['userid'] = 1130;
    	// $inputParams['comid'] = 13;
    	// $inputParams['duration'] = 2;    	
		$db_Commodity = M('Commodity');
		$commodityCond = array(
				'commodityid'=> $inputParams['comid'],
				'commoditytype'=> 1,
				'lantype' => $this->lantype
		);
		$commodity = $db_Commodity
		    ->where($commodityCond)
		    ->find();
		//获取折扣
		$db_Discount = D('Discount', 'Modapi');
		$comtype = 2;  //商品类型 1 vip 2 座驾 3 守护 4 靓号
		$discount = $db_Discount->getDiscount($comtype, $inputParams['duration']);
		if (!$discount) {
			$discount = 1;
		}
		//商品总价
		$spendamount = $commodity['commodityprice'] * $inputParams['duration'] * $discount;
		//检查用户余额
		$checkBalanceResult = $this->checkUserBalance($inputParams['userid'], $spendamount);
		if (200 == $checkBalanceResult['status']) {
			return $this->doBuyEquipment($inputParams, $commodity, $spendamount);
		} else {
			return $checkBalanceResult;
		}		    
    }

	/**
     * 购买座驾
	 * @param $inputParams: 请求参数
	 * @param $commodity: 座驾信息
	 * @param $spendamount: 商品总价
	 */	
	private function doBuyEquipment($inputParams, $commodity, $spendamount){
		$db_Member = D('Member', 'Modapi');
		$userInfo = $db_Member->getMemberInfoByUserID($inputParams['userid']);
		$tran = new Model();
		$tran->startTrans();
		//插入spenddetail数据
		$spenddetail['userid'] = $inputParams['userid'];
		$spenddetail['targetid'] = $inputParams['userid'];
		$spenddetail['familyid'] = $userInfo['familyid'];
		$spenddetail['tradetype'] = 2;  //购买座驾
		$spenddetail['giftid'] = $commodity['commodityid'];
		$spenddetail['giftname'] = $commodity['commodityname'];
		$spenddetail['gifticon'] = $commodity['appsmallpic'];
		$spenddetail['giftprice'] = $commodity['commodityprice'];
		$spenddetail['giftcount'] = $inputParams['duration'];
		$spenddetail['spendamount'] = $spendamount;
		$spenddetail['tradetime'] = date('Y-m-d H:i:s');
		$spenddetail['status'] = 1;  //交易成功
		$spendResult = $this->processSpendRecordWithTrans($tran, $spenddetail);
		//更新余额
		$balance = array(
				'balance' => array('exp', 'balance-'.$spendamount),
				'spendmoney' => array('exp', 'spendmoney+'.$spendamount),
		);
		$balanceResult = $tran
		    ->table('ws_balance')
		    ->where('userid=' . $inputParams['userid'])
		    ->save($balance);		
		//获取余额信息
		$balance = D('Balance', 'Modapi')
		    ->getBalanceByUserid($inputParams['userid']);
		//用户等级
		$userNewLevel = D('Levelconfig', 'Modapi')
		    ->getUserLevelBySpendMoney($balance['spendmoney'], $this->lantype);
		if ($userNewLevel && $userNewLevel != $userInfo['userlevel']) {
		    $userNewInfo['userlevel'] = $userNewLevel;
		    $tran->table('ws_member')
		        ->where('userid=' . $inputParams['userid'])
		        ->save($userNewInfo);
		}

        //修改或添加equipment数据
        $hasEquipment = D('Equipment', 'Modapi')->getEquipmentByUseridAndComid($inputParams['userid'], $commodity['commodityid']);
        if ($hasEquipment){
            $equipment['isused'] = $hasEquipment['isused'];
            $equipment['effectivetime'] = $hasEquipment['expiretime'];
            $equipment['expiretime'] = date("Y-m-d H:i:s",strtotime('+'.$inputParams['duration'].' months',strtotime($hasEquipment['expiretime'])));
        }else{
            //查看用户是否有未过期的正在使用的座驾
            $equipment_isused = D('Equipment', 'Modapi')->getMyEquipmentsByCon(array('userid' => $inputParams['userid']));
            if($equipment_isused){
                $equipment['isused'] = 0;
            }else{
                //更新所有失效的座驾为未使用
                $oldEquipment['isused'] = 0;
                $oldEquipment['operatetime'] = date('Y-m-d H:i:s');
                $oldEquipmentCond = array(
                    'userid' => $inputParams['userid'],
                    'isused' => 1
                );
                $tran->table('ws_equipment')->where($oldEquipmentCond)->save($oldEquipment);
                //设置赠送的座驾为使用
                $equipment['isused'] = 1;
            }
            $equipment['effectivetime'] = date('Y-m-d H:i:s');
            $equipment['expiretime'] = date("Y-m-d H:i:s", strtotime('+' . $inputParams['duration'] . ' months', time()));
        }
        $equipment['userid'] = $inputParams['userid'];
        $equipment['commodityid'] = $commodity['commodityid'];
        $equipment['commodityname'] = $commodity['commodityname'];
        $equipment['commodityflashid'] = $commodity['commodityflashid'];
        $equipment['pcbigpic'] = $commodity['pcbigpic'];
        $equipment['pcsmallpic'] = $commodity['pcsmallpic'];
        $equipment['appbigpic'] = $commodity['appbigpic'];
        $equipment['appsmallpic'] = $commodity['appsmallpic'];
        $equipment['commodityswf'] = $commodity['commodityswf'];
        $equipment['spendmoney'] = $spendamount;
        $equipment['operatetime'] = date('Y-m-d H:i:s');
        $equipmentResult = $tran->table('ws_equipment')->add($equipment);

		if ($spendResult && $balanceResult && $equipmentResult) {
			$tran->commit();
		    $data['status'] = 200;
		    $data['message'] = lan('BUY_SUCCESS', 'Api', $this->lantype);
            if (!$balance['balance']) {
                $balance['balance'] = 0;
            }
            $data['balance'] = $balance['balance'];

			$whereEquip['userid'] = $inputParams['userid'];
			$whereEquip['effectivetime'] = array('elt',date('Y-m-d H:i:s'));
			$whereEquip['expiretime'] = array('gt',date('Y-m-d H:i:s'));
			$equipnumber = M('Equipment')->where($whereEquip)->count();  //座驾数量
			$data['equipnumber'] = $equipnumber;
		} else {
			$tran->rollback();
		    $data['status'] = -1;
		    $data['message'] = lan('-1', 'Api', $this->lanPackage);
		}
		return $data;		
	}

    /**
     * 购买靓号
     * @param userid: 用户userid     
     * @param niceno: 靓号
     * @param duration: 时长 月
     */
    public function buyNiceno($inputParams){
    	// $inputParams['userid'] = 1130;
    	// $inputParams['niceno'] = 11114;
    	// $inputParams['duration'] = 2;     	
		$db_Nicenumber = M('Nicenumber');
		$niceno = $db_Nicenumber
		    ->where(array('niceno'=>array('eq', $inputParams['niceno'])))
		    ->find();
		if (1 == $niceno['isused']) {
			$data['status'] = 405002;
			$data['message'] = lan('405002', 'Api', $this->lanPackage);
			return $data;
		}
		//获取折扣
		$db_Discount = D('Discount', 'Modapi');
		$comtype = 4;  //商品类型 1 vip 2 座驾 3 守护 4 靓号
		$discount = $db_Discount->getDiscount($comtype, $inputParams['duration']);
		if (!$discount) {
			$discount = 1;
		}
		//商品总价
		$spendamount = $niceno['price'] * $inputParams['duration'] * $discount;
		//检查用户余额
		$checkBalanceResult = $this
		    ->checkUserBalance($inputParams['userid'], $spendamount);
		if (200 == $checkBalanceResult['status']) {
			return $this->doBuyNiceno($inputParams, $niceno, $spendamount);
		} else {
			return $checkBalanceResult;
		}		 	
    }

	/**
     * 购买靓号
	 * @param $inputParams: 请求参数
	 * @param $niceno: 要购买的靓号
	 * @param $spendamount: 商品总价
	 */	
	private function doBuyNiceno($inputParams, $niceno, $spendamount){
		$db_Member = D('Member', 'Modapi');
		$userInfo = $db_Member->getMemberInfoByUserID($inputParams['userid']);
		$tran = new Model();
		$tran->startTrans();
		//插入spenddetail数据
		$spenddetail['userid'] = $inputParams['userid'];
		$spenddetail['targetid'] = $inputParams['userid'];
		$spenddetail['familyid'] = $userInfo['familyid'];
		$spenddetail['tradetype'] = 6;
		$spenddetail['giftname'] = $inputParams['niceno'];
		$spenddetail['giftprice'] = $niceno['price'];
		$spenddetail['giftcount'] = $inputParams['duration'];
		$spenddetail['spendamount'] = $spendamount;
		$spenddetail['tradetime'] = date('Y-m-d H:i:s');
		$spenddetail['status'] = 1;
		$spendResult = $this->processSpendRecordWithTrans($tran, $spenddetail);
		//更新余额
		$balance = array(
				'balance' => array('exp', 'balance-'.$spendamount),
				'spendmoney' => array('exp', 'spendmoney+'.$spendamount),
		);
		$balanceResult = $tran
		    ->table('ws_balance')
		    ->where('userid=' . $inputParams['userid'])
		    ->save($balance);
		//获取余额信息
		$balance = D('Balance', 'Modapi')
		    ->getBalanceByUserid($inputParams['userid']);
		//用户等级
		$userNewLevel = D('Levelconfig', 'Modapi')
		    ->getUserLevelBySpendMoney($balance['spendmoney'],$this->lantype);
		if ($userNewLevel && $userNewLevel != $userInfo['userlevel']) {
		    $userNewInfo['userlevel'] = $userNewLevel;
			$tran->table('ws_member')
			    ->where('userid=' . $inputParams['userid'])
			    ->save($userNewInfo);
		}
		//购买的靓号状态变成已使用
		$buyNicenoData['userid'] = $inputParams['userid'];
		$buyNicenoData['isused'] = 1;
		$buyNicenoData['operatetime'] = date('Y-m-d H:i:s');
		$buyNicenoResult = $tran
		    ->table('ws_nicenumber')
		    ->where('niceno=' . $inputParams['niceno'])
		    ->save($buyNicenoData);
		$userNicenoData['niceno'] = $inputParams['niceno'];
		$userInfoResult = $tran
		    ->table('ws_member')
		    ->where('userid=' . $inputParams['userid'])
		    ->save($userNicenoData);
        //原先购买的靓号变成未使用
		$oldNicenoCond = array(
				'userid' => $inputParams['userid'],
				'isused' => 1
		);
		$oldNicenoCond['effectivetime'] = array('elt',date('Y-m-d H:i:s'));
		$oldNicenoCond['expiretime'] = array('gt',date('Y-m-d H:i:s'));
		$oldNiceno['isused'] = 0;
		$oldNiceno['operatetime'] = date('Y-m-d H:i:s');
		$oldNicenoResult = $tran
		    ->table('ws_nicenumrecord')
		    ->where($oldNicenoCond)
		    ->save($oldNiceno);
        //增加靓号记录
		$nicenoRecord['userid'] = $inputParams['userid'];
		$nicenoRecord['nicenumber'] = $inputParams['niceno'];
		$nicenoRecord['spendmoney'] = $spendamount;
		$nicenoRecord['isused'] = 1;
		$nicenoRecord['effectivetime'] = date('Y-m-d H:i:s');
		$nicenoRecord['expiretime'] = date("Y-m-d H:i:s",
			strtotime('+'.$inputParams['duration'].'months', time()));
		$nicenoRecord['operatetime'] = date('Y-m-d H:i:s');
		$nicenoRecordResult = $tran
		    ->table('ws_nicenumrecord')
		    ->add($nicenoRecord);

		if ($spendResult && $balanceResult && $buyNicenoResult && $nicenoRecordResult) {
			$tran->commit();
		    $data['status'] = 200;
		    $data['message'] = lan('BUY_SUCCESS', 'Api', $this->lantype);
            if (!$balance['balance']) {
                $balance['balance'] = 0;
            }
            $data['balance'] = $balance['balance'];
		} else {
			$tran->rollback();
		    $data['status'] = -1;
		    $data['message'] = lan('-1', 'Api', $this->lanPackage);
		}
		return $data;			
	}

    /**
     * 购买守护
     * @param userid: 用户userid     
     * @param emceeuserid: 主播userid     
     * @param guardid: 守护id
     * @param gdduration: 守护时长 月
     * @param price: 总金额 
     */
    public function buyGuard($inputParams){
    	// $inputParams['userid'] = 1130;
    	// $inputParams['emceeuserid'] = 1132;  
    	// $inputParams['guardid'] = 2;  
    	// $inputParams['gdduration'] = 3;     	  	  	
		//根据守护id获取守护信息
        $guardInfo = D('Guarddefinition', 'Modapi')
            ->getGuardDefById($inputParams['guardid'], $this->lantype);
		//商品总价
		$spendamount = $guardInfo['gdprice'] * $inputParams['gdduration'];     
		//检查用户余额
		$checkBalanceResult = $this
		    ->checkUserBalance($inputParams['userid'], $spendamount);
		if (200 == $checkBalanceResult['status']) {
			return $this->doBuyGuard($inputParams, $spendamount, $guardInfo);
		} else {
			return $checkBalanceResult;
		}		
    }

    /**
     * 购买守护
	 * @param $inputParams: 请求参数
	 * @param $guardInfo: 守护信息
	 * @param $spendamount: 商品总价
	 */	
	private function doBuyGuard($inputParams, $spendamount, $guardInfo){
	    $emceeuserid =$inputParams['emceeuserid'];
	    $userid =$inputParams['userid'];
		$tran = new Model();
		$tran->startTrans();	    
	    //更新用户余额和主播收入
		$userBalance = array(
				'balance' => array('exp', 'balance-' . $spendamount),
				'spendmoney' => array('exp', 'spendmoney+' . $spendamount)
		);
		$db_Balance = M('Balance');
		$userBalanceResult = $tran->table('ws_balance')
		    ->where(array('userid'=>$userid))
		    ->save($userBalance);
		if ($userid > 1000) {
			$emceeBalance = array(
				'earnmoney' => array('exp', 'earnmoney+' . $spendamount)
		    );
		    $emceeBalanceResult = $tran->table('ws_balance')
		        ->where(array('userid'=>$emceeuserid))
		        ->save($emceeBalance);
		}
		$db_Guard = M('Guard');
        //该用户是否存在该主播的守护
		$selectGuardArr = array(
		    'emceeuserid' => $emceeuserid,
		    'userid' => $userid,
		    'guardid' => $inputParams['guardid'],
		    'expiretime' => array('gt', date("Y-m-d H:i:s" ,time()))
		);
		$existGuard = $db_Guard->where($selectGuardArr)->find();
		//主播已有守护数
		$selectCountArr = array(
		    'emceeuserid' => $emceeuserid,
		    'expiretime' => array('gt', date("Y-m-d H:i:s" ,time()))
		);
		$guardnum = $db_Guard->where($selectCountArr)->count('gid');
		//用户存在该主播守护，更新记录时间，否则插入新记录		
		if ($existGuard) {
		    $updateGuardArr = array(
		        'expiretime' => date("Y-m-d H:i:s",
		        	strtotime($existGuard['expiretime'].'+'.$inputParams['gdduration'].' months'))
		    );
		    $data['remaindays'] = round((strtotime($updateGuardArr['expiretime']) - time())/3600/24);
			$data['expiretime'] = $updateGuardArr['expiretime'];
		    $guardResult = $tran->table('ws_guard')->where($selectGuardArr)->save($updateGuardArr);
		} else {
		    $insertGuardArr = array(
		        'emceeuserid' => $emceeuserid,
		        'userid' => $userid,
		        'guardid' => $inputParams['guardid'],
		        'gdname' => $guardInfo['gdname'],
		        'gdbrand' => $guardInfo['gdbrand'],
		        'price' => $spendamount,
		        'effectivetime' => date('Y-m-d H:i:s'),
		        'expiretime' => date('Y-m-d H:i:s', strtotime("+" . $inputParams['gdduration']. " month")),
		        'createtime' => date('Y-m-d H:i:s'),
		        'sort' => $guardnum+1
		    );
		    $guardResult = $tran->table('ws_guard')->add($insertGuardArr);
		    $data['remaindays'] = round((strtotime($insertGuardArr['expiretime']) - time())/3600/24);
			$data['expiretime'] = $insertGuardArr['expiretime'];
		}
		
		$db_Member = M('Member');
		
		//主播信息
		$emceeCond = array('userid' => $emceeuserid);
		$emceemember =$db_Member->where($emceeCond)->field($this->memberfield)->find();
		$emceeBalance = $db_Balance->where($emceeCond)->find();
		//用户信息
		$userCond = array('userid' => $userid);
		$member = $db_Member->where($userCond)->field($this->memberfield)->find();
		$balinfo = $db_Balance->where($userCond)->find();
		//插入earndetail数据
		$insertEarn = array(
		    'userid' => $emceeuserid,
		    'fromid' => $userid,
		    'familyid' => $emceemember['familyid'],
		    'tradetype' => 9,
		    'giftid' => $guardInfo['gdid'],
		    'giftname' => $guardInfo['gdname'],
		    'gifticon' => $guardInfo['gdbrand'],
            'giftprice' => $guardInfo['gdprice'],		    
		    'giftcount' => $inputParams['gdduration'],
		    'earnamount' => $spendamount,
		    'tradetime' => date('Y-m-d H:i:s'),
		    'content' => $member['nickname'].' '.lan('SPEND','Api', $this->lantype).' '.$spendamount.' '.
		        lan('MONEY_UNIT','Api', $this->lantype).' '.lan('BECOMETOBE','Api', $this->lantype).' '.
		        $emceemember['nickname'].' '. lan('GUARD','Api', $this->lantype)
		);
		$earnResult = $this->processEmceeEarnWithTrans($tran, $insertEarn);
		//插入spenddetail数据
		$insertSpend = array(
		    'userid' => $userid,
		    'targetid' => $emceeuserid,
		    'familyid' => $member['familyid'],
		    'tradetype' => 9,
		    'giftid' => $guardInfo['gdid'],
		    'giftname' => $guardInfo['gdname'],
		    'gifticon' => $guardInfo['gdbrand'],
		    'giftprice' => $guardInfo['gdprice'], 
		    'giftcount' => $inputParams['gdduration'],
		    'spendamount' => $spendamount,
		    'tradetime' => date('Y-m-d H:i:s'),
		    'content' => $member['nickname'].' '.lan('SPEND','Api', $this->lantype).' '.$spendamount.' '.
		        lan('MONEY_UNIT','Api', $this->lantype).' '.lan('BECOMETOBE','Api', $this->lantype).' '.
		        $emceemember['nickname'].' '. lan('GUARD','Api', $this->lantype)
		);
		$spendResult = $this->processSpendRecordWithTrans($tran, $insertSpend);
		//更新用户与主播等级
		$this->updateUserlevel($member, $balinfo);
		$this->updateEmceelevel($emceemember, $emceeBalance);
		
		if ($spendResult && $userBalanceResult && $guardResult) {
			$tran->commit();
		    $data['status'] = 200;
		    $data['message'] = lan('BUY_SUCCESS', 'Api', $this->lantype);
            if (!$balinfo['balance']) {
                $balinfo['balance'] = '0';
            }
            $data['balance'] = $balinfo['balance'];
		} else {
			$tran->rollback();
		    $data['status'] = -1;
		    $data['message'] = lan('-1', 'Api', $this->lanPackage);
		}
		return $data;
	}

    /**
     * 购买沙发
     * @param emceeuserid: 主播userid     
     * @param seatseqid: 座位序列ID   
     * @param userid: 坐沙发用户id
     * @param price: 总金额 
     */
    public function buySeat($inputParams){
    	//验证沙发信息
		$checkresult = $this->checkSeatInfo($inputParams);
		if (200 == $checkresult['status']) {
			return $this->doBuySeat($inputParams);
		} else {
			return $checkresult;
		}    	
    }

	/**
     * 购买沙发
	 * @param $inputParams: 请求参数
	 */	
	private function doBuySeat($inputParams){
	    $price = $inputParams['price'];
	    $seatuserid = $inputParams['userid']; //坐沙发用户id
	    $userid = $inputParams['emceeuserid']; //主播userid
		$seatseqid = $inputParams['seatseqid'];	   
		$tran = new Model();
		$tran->startTrans();

		$db_Seat = M('Seat');
		$sofadef = M('Seatdefinition')
		    ->where(array('lantype'=>$this->lantype))
		    ->find();
        
		$seatcount = $price/$sofadef['seatprice'];  //抢沙发数量
		$seatCond = array(
				'seatseqid' => $seatseqid,
				'userid' => $userid,
		);
		//存在沙发记录则更新，不存在则插入新记录
		$seatInfo = $db_Seat->where($seatCond)->find();
		if ($seatInfo) {
			$updateArr = array(
				'seatuserid' => $seatuserid,
			    'seatcount'=>$seatcount,
				'price'=> $price,
				'createtime' => date('Y-m-d H:i:s')
			);
			$seatResult = $tran->table('ws_seat')->where($seatCond)->save($updateArr);
		} else {
			$insertArr = array(
					'seatseqid' => $seatseqid,
					'userid' => $userid,
					'seatuserid' => $seatuserid,
			        'seatcount'=>$seatcount,
					'price' => $price,
					'createtime' => date('Y-m-d H:i:s')
			);
			$seatResult = $tran->table('ws_seat')->add($insertArr);
		}
        //更新用户余额
		$userBalance = array(
		    'balance' => array('exp', 'balance-' . $price),
		    'spendmoney' => array('exp', 'spendmoney+' . $price),
		);
		$db_Balance = M('Balance');
		$userBalanceResult = $tran->table('ws_balance')
		    ->where(array('userid'=>$seatuserid))
		    ->save($userBalance);
        //更新主播赚的金额
        if ($userid > 1000) {
            $updatEmceeEarn = array(
                'earnmoney' => array('exp', 'earnmoney+' . $price),
            );
            $emceeBalanceResult = $tran->table('ws_balance')
                ->where(array('userid'=>$userid))
                ->save($updatEmceeEarn);
        }

        $dMember = M('Member');
        //主播信息
		$emceeCond = array('userid' => $userid);
		$emceemember =$dMember->where($emceeCond)->field($this->memberfield)->find();
		$emceeBalance = $db_Balance->where($emceeCond)->find();
		//用户信息
		$userCond = array('userid' => $seatuserid);
		$member = $dMember->where($userCond)->field($this->memberfield)->find();
		$balinfo = $db_Balance->where($userCond)->find();
        //插入earndetail数据
        $insertEarn = array(
            'userid' => $userid,
            'fromid' => $seatuserid,
            'familyid' => $member['familyid'],
            'tradetype' => 4,
            'giftid' => $sofadef['seatdid'],
            'giftname' => $sofadef['seatname'],
            'gifticon' => $sofadef['seatpic'],
            'giftprice' => $sofadef['gdprice'],
            'giftcount' => $seatcount,
            'earnamount' => $price,
            'tradetime' => date('Y-m-d H:i:s'),
            'content' => $member['nickname'].' '.lan('GRAB','Api', $this->lantype).' '.$emceemember['nickname'].
                ' '.$seatseqid.' '.lan('POSITION','Api', $this->lantype). ' ' .$seatcount.' '.$sofadef['seatname']
        );
        $earnResult = $this->processEmceeEarnWithTrans($tran, $insertEarn);
        //插入spenddetail数据
        $insertSpend = array(
            'userid' => $seatuserid,
            'targetid' => $userid,
            'familyid' => $member['familyid'],
            'tradetype' => 4,
            'giftid' => $sofadef['seatdid'],
            'giftname' => $sofadef['seatname'],
            'gifticon' => $sofadef['seatpic'],
            'giftcount' => $seatcount,
            'spendamount' => $price,
            'tradetime' => date('Y-m-d H:i:s'),
            'content' => $member['nickname'].' '.lan('GRAB','Api', $this->lantype).' '.$emceemember['nickname'].
                ' '.$seatseqid.' '.lan('POSITION','Api', $this->lantype). ' ' .$seatcount.' '.$sofadef['seatname']
        );
		$spendResult = $this->processSpendRecordWithTrans($tran, $insertSpend);
        
        //更新用户和主播等级
        $this->updateUserlevel($member, $balinfo);
        $this->updateEmceelevel($emceemember, $emceeBalance);
        
		if ($spendResult && $userBalanceResult) {
			$tran->commit();
		    $data['status'] = 200;
		    $data['message'] = lan('BUY_SUCCESS', 'Api', $this->lantype);
            if (!$balinfo['balance']) {
                $balinfo['balance'] = '0';
            }
            $data['balance'] = $balinfo['balance'];
		} else {
			$tran->rollback();
		    $data['status'] = -1;
		    $data['message'] = lan('-1', 'Api', $this->lanPackage);
		}
		return $data;		
	}

    /**
     * 赠送礼物
     * @param userid: 用户userid     
     * @param emceeuserid: 主播userid
     * @param giftid: 礼物id
     * @param giftcount: 礼物数量 
     */
    public function sendGift($inputParams){
		$giftdef = M('Gift')
		    ->where(array('giftid' => $inputParams['giftid'],'lantype'=>$this->lantype))
		    ->find();
		$consumeMoney = $giftdef['price'] * $inputParams['giftcount'];
		$inputParams['consumeMoney'] = $consumeMoney;  //总金额
		$inputParams['giftdef'] = $giftdef;  //礼物信息
		//检查用户余额
		$checkBalanceResult = $this
		    ->checkUserBalance($inputParams['userid'], $consumeMoney);
		if (200 == $checkBalanceResult['status']) {
			return $this->doSendGift($inputParams);
		} else {
			return $checkBalanceResult;
		}			
    }

    /**
     * 赠送免费礼物
     * @param userid: 用户userid
     * @param emceeuserid: 主播userid
     * @param giftid: 礼物id
     * @param giftcount: 礼物数量
     */
    public function sendFreeGift($inputParams){
        $userid = $inputParams['userid'];
        $emceeuserid = $inputParams['emceeuserid'];
        $giftid = $inputParams['giftid'];
        $giftcount = $inputParams['giftcount'];

        //不能赠送给自己
        if($userid == $emceeuserid){
            $data['status'] = -1;
            $data['message'] = lan('-1', 'Api', $this->lanPackage);
            return $data;
        }

        //添加赠送记录
        $insertData = array(
            'userid' => $emceeuserid,
            'fromid' => $userid,
            'giftid' => $giftid,
            'giftcount' => $giftcount,
            'addtime' => date('Y-m-d H:i:s'),
        );
        $userfreegiftid = M('freegiftrecord')->add($insertData);

        if (!$userfreegiftid) {
            $data['status'] = -1;
            $data['message'] = lan('-1', 'Api', $this->lanPackage);
        } else {
            //验证用户是否中奖，如果中奖，随机获取一种奖项
            $this->setFreeGiftReward($userid, $userfreegiftid);

            //获取用户余额
            $userCond = array('userid' => $userid);
            $data['balance'] = M('Balance')->where($userCond)->getField('balance');

            $data['status'] = 200;
            $data['message'] = lan('200', 'Api', $this->lanPackage);
        }
        return $data;
    }

    /**
     * 赠送礼物
	 * @param $inputParams: 请求参数
	 */	
	private function doSendGift($inputParams){
		$userid = $inputParams['userid'];
		$emceeuserid = $inputParams['emceeuserid'];
		$db_Member = M('Member');
		$tran = new Model();
		$tran->startTrans();
		//更新用户余额
	    $needmoney = $inputParams['consumeMoney'];
		$userBalance = array(
				'balance' => array('exp', 'balance-' . $needmoney),
				'spendmoney' => array('exp', 'spendmoney+' . $needmoney),
		);
		$db_Balance = M('Balance');
		$userBalanceResult = $tran
		    ->table('ws_balance')
		    ->where('userid=' . $userid)
		    ->save($userBalance);		
		//更新主播收入
		if ($userid > 1000) {
			$emceeBalance = array(
				'earnmoney' => array('exp', 'earnmoney+' . $needmoney),
		    );
		    $emceeBalanceResult = $tran
		        ->table('ws_balance')
		        ->where('userid=' . $emceeuserid)
		        ->save($emceeBalance);		    
		}
		
		//主播信息
		$emceeCond = array('userid' => $emceeuserid);
		$emceemember =$db_Member->where($emceeCond)->field($this->memberfield)->find();
		$emceeBalance = $db_Balance->where($emceeCond)->find();
		//用户信息
		$userCond = array('userid' => $userid);
		$member = $db_Member->where($userCond)->field($this->memberfield)->find();
		$balinfo = $db_Balance->where($userCond)->find();
		//插入earndetail数据
		$insertEarn = array(
				'userid' => $emceeuserid,
				'fromid' => $userid,
				'familyid' => $emceemember['familyid'],
				'tradetype' => 0,
				'giftid' => $inputParams['giftdef']['giftid'],
				'giftname' => $inputParams['giftdef']['giftname'],
				'gifticon' => $inputParams['giftdef']['smallimgsrc'],
				'giftprice' => $inputParams['giftdef']['price'],
				'giftcount' => $inputParams['giftcount'],
				'earnamount' => $inputParams['consumeMoney'],
			 	'tradetime' => date('Y-m-d H:i:s'),
		        'content' => $member['nickname'] . ' ' . lan('PRESENT', 'Api') . ' ' . $emceemember['nickname'] .
		             ' ' . $inputParams['giftcount'] . ' ' . $inputParams['giftdef']['giftname']
		);
		$earnResult = $this->processEmceeEarnWithTrans($tran, $insertEarn);
        //插入spenddetail数据
		$insertSpend = array(
				'userid' => $userid,
				'targetid' => $emceeuserid,
				'familyid' => $member['familyid'],
				'tradetype' => 1,
				'giftid' => $inputParams['giftdef']['giftid'],
				'giftname' => $inputParams['giftdef']['giftname'],
				'gifticon' => $inputParams['giftdef']['smallimgsrc'],
				'giftprice' => $inputParams['giftdef']['price'],
				'giftcount' => $inputParams['giftcount'],
				'spendamount' => $inputParams['consumeMoney'],
				'tradetime' => date('Y-m-d H:i:s'),
		        'content' => $member['nickname'] . ' ' . lan('PRESENT', 'Api') . ' ' . $emceemember['nickname'] .
		             ' ' . $inputParams['giftcount'] . ' ' . $inputParams['giftdef']['giftname']
		);
		$spendResult = $this->processSpendRecordWithTrans($tran, $insertSpend);
		//更新用户和主播等级
		$this->updateUserlevel($member, $balinfo);
		$this->updateEmceelevel($emceemember, $emceeBalance);

		if ($spendResult && $userBalanceResult) {
			$tran->commit();
		    $data['status'] = 200;
		    $data['message'] = lan('BUY_SUCCESS', 'Api', $this->lantype);
            if (!$balinfo['balance']) {
                $balinfo['balance'] = '0';
            }
            $data['balance'] = $balinfo['balance'];
		} else {
			$tran->rollback();
		    $data['status'] = -1;
		    $data['message'] = lan('-1', 'Api', $this->lanPackage);
		}
		return $data;			
	}

    /**
     * 检查用户余额
     * @param userid: 用户userid     
     * @param price: 商品价格
     */
	private function checkUserBalance($userid, $price){
		$userCond = array(
			'userid' => $userid
		);
		$db_Balance = M('Balance');
		$balance = $db_Balance
		    ->where($userCond)
		    ->field('balance')
		    ->find();
		if ($price <= 0) {
			$data['status'] = 400;
			$data['message'] = lan('400', 'Api', $this->lanPackage);
			return $data;
		} else if($price > $balance['balance']) {
			$data['status'] = 405001;
			$data['message'] = lan('405001', 'Api', $this->lanPackage);
			return $data;
		} else {
			$data['status'] = 200;
			$data['message'] = lan('200', 'Api', $this->lanPackage);
			return $data;
		}

	} 

	/**
	 * 该方法用于消费事物处理	
	 * userid小于1000 记录到Marketspend表，大于1000 记录到Spenddetail表	 
	 * @param $spendrecord
	 */
	private function processSpendRecordWithTrans($tran, $spendrecord){
		if ($spendrecord['userid'] > 1000){
			$spendResult = $tran->table('ws_spenddetail')->add($spendrecord);
		} else {
			$spendResult = $tran->table('ws_marketspend')->add($spendrecord);
		}
		return $spendResult;
	}

	/**
	 * 该方法用于消费非事物处理
	 * userid小于1000 记录到Marketspend表，大于1000 记录到Spenddetail表	 
	 * @param $spendrecord
	 */
	private function processSpendRecord($spendrecord){
		if ($spendrecord['userid'] > 1000) {
			M('Spenddetail')->add($spendrecord);
		} else {
			M('Marketspend')->add($spendrecord);
		}
	}

	/**
	 * 该方法用于主播收入事务处理
	 * @param $earnrecord
	 */
	private function processEmceeEarnWithTrans($tran, $earnrecord){
		if ($earnrecord['fromid'] > 1000) {
			$earnResult = $tran->table('ws_earndetail')->add($earnrecord);
		}
		return $earnResult;
	}

	/**
	 * 该方法用于主播收入非事务处理
	 * @param $earnrecord
	 */
	private function processEmceeEarn($earnrecord){
		if ($earnrecord['fromid'] > 1000) {
			M('Earndetail')->add($earnrecord);
		}
	}

	/**
	 * 校验购买沙发信息
	 * @param $inputParams
	 */
	private function checkSeatInfo($inputParams){
		if (!$inputParams['seatseqid']) {
			$data['status'] = 400;
			$data['message'] = lan('400', 'Api', $this->lanPackage);
			return $data;
		}
		$userCond = array('userid' => $inputParams['userid']);
		$db_Member = D('Member', 'Modapi');
		$db_Balance = M('Balance');
		$balance = $db_Balance->where($userCond)->field('balance')->find();
		//检查用户余额
		if ($inputParams['price'] > $balance['balance']) {
			$data['status'] = 405001;
			$data['message'] = lan('405001', 'Api', $this->lanPackage);
			return $data;
		}

		$db_Seat = M('Seat');
		$seatCond = array(
				'seatseqid' => $inputParams['seatseqid'],
				'userid' => $inputParams['emceeuserid'],
		);
		$seatInfo = $db_Seat->where($seatCond)->find();
		//验证沙发是否可以购买
		if ($seatInfo && ($inputParams['price'] <= $seatInfo['price'])) {
			$memberinfo = $db_Member->getMemberInfoByUserID($seatInfo['seatuserid']);
			$data['status'] = 405003;
			$data['message'] = lan('405003', 'Api', $this->lanPackage);
			$data['datalist'] = array(
					'seatuserinfo' => $memberinfo,
					'curseatprice' => $seatInfo['price']);
			return $data;
		}

		$data['status'] = 200;
		$data['message'] = lan('200', 'Api', $this->lanPackage);
		return $data;
	}

    /**
     * 验证用户是否中奖，如果中奖，随机获取一种奖项
     * @param userid：用户ID
     * @param userfreegiftid：用户赠送免费礼物序号
     */
    private function setFreeGiftReward($userid, $userfreegiftid){
        $where = array(
            'key' => 'FREE_GIFT_REWARD_NUMBER',
            'lantype' => $this->lantype
        );
        $free_gift_reward_number = M('Systemset')->where($where)->getField('value');//获取免费礼物最大配置
        if(!$userfreegiftid || ($userfreegiftid%$free_gift_reward_number) != 0){
            return false;
        }

        //随机获取一种奖项，并保存中奖纪录
        $freeGiftRewardRule = M('freegiftrewardrule')->order('rand()')->find();
        $free_gift_reward_data = array(
            'userid' => $userid,
            'number' => $userfreegiftid,
            'type' => $freeGiftRewardRule['type'],
            'type_id' => $freeGiftRewardRule['type_id'],
            'value' => $freeGiftRewardRule['value'],
            'addtime' => date('Y-m-d H:i:s'),
        );
        $result = M('freegiftreward')->add($free_gift_reward_data);
        if(!$result){
            return false;
        }

        //根据奖项，添加响应奖励记录
        $type = $freeGiftRewardRule['type'];
        $reward_content = '';
        switch($type){
            case '1':   //VIP奖励
                $free_vipid = (int)$freeGiftRewardRule['type_id'];  //VIP等级ID
                $free_vip_validdays = (int)$freeGiftRewardRule['value'];//有效天数
                //获取用户未过期的该等级的VIP记录
                $where_vip = array(
                    'userid' => $userid,
                    'vipid' => $free_vipid,
                    'expiretime' => array('gt',date('Y-m-d H:i:s'))
                );
                $my_vip = M('viprecord')->where($where_vip)->order('expiretime DESC')->find();
                if($my_vip && $my_vip['expiretime']){
                    $free_vip_effectivetime = $my_vip['expiretime'];
                    $free_vip_expiretime = date('Y-m-d H:i:s',strtotime('+'.$free_vip_validdays.' day',strtotime($my_vip['expiretime'])));
                }else{
                    $free_vip_effectivetime = date('Y-m-d H:i:s');
                    $free_vip_expiretime = date('Y-m-d H:i:s',strtotime('+'.$free_vip_validdays.' day'));
                }
                //获取该等级的VIP定义值
                $where_vipdefinition = array(
                    'vipid' => $free_vipid,
                    'lantype' => $this->lantype
                );
                $vipdefinition = M('vipdefinition')->where($where_vipdefinition)->find();
                //添加新VIP记录
                $free_vip_data = array(
                    'userid' => $userid,
                    'vipid' => $free_vipid,
                    'vipname' => $vipdefinition['vipname'],
                    'pcsmallvippic' => $vipdefinition['pcsmallviplogo'],
                    'appsmallvippic' => $vipdefinition['appsmallviplogo'],
                    'spendmoney' => 0,
                    'ispresent' => 1,
                    'effectivetime' => $free_vip_effectivetime,
                    'expiretime' => $free_vip_expiretime
                );
                $result = M('viprecord')->add($free_vip_data);

                //中奖奖项内容
                $reward_content = $vipdefinition['vipname'].$freeGiftRewardRule['value'].lan('DAYS','Api',$this->lantype);
                break;
            case '2':   //座驾奖励
                $dbeQuipment = M('equipment');
                $free_commodityid = (int)$freeGiftRewardRule['type_id'];    //座驾ID
                $free_equipment_validdays = (int)$freeGiftRewardRule['value'];//有效天数
                //获取用户未过期的该座驾的记录
                $where_equipment = array(
                    'userid' => $userid,
                    'commodityid' => $free_commodityid,
                    'expiretime' => array('gt',date('Y-m-d H:i:s'))
                );
                $my_equipment = $dbeQuipment->where($where_equipment)->order('expiretime DESC')->find();
                if($my_equipment && $my_equipment['expiretime']){
                    //只要之前有为过期的相同的座驾，是否使用和之前的保持一致
                    $my_equipment_isused = $my_equipment['isused'];
                    $free_equipment_effectivetime = $my_equipment['expiretime'];
                    $free_equipment_expiretime = date('Y-m-d H:i:s',strtotime('+'.$free_equipment_validdays.' day',strtotime($my_equipment['expiretime'])));
                }else{
                    //查看用户是否有未过期的正在使用的座驾
                    $where_equipment_isused = array(
                        'userid' => $userid,
                        'isused' => 1,
                        'expiretime' => array('gt',date('Y-m-d H:i:s'))
                    );
                    $equipment_isused = $dbeQuipment->where($where_equipment_isused)->find();
                    if($equipment_isused){
                        $my_equipment_isused = 0;
                    }else{
                        //更新所有失效的座驾为未使用
                        $notUsed['isused'] = 0;
                        $UsedCond = array(
                            'userid' => $userid,
                            'isused' => 1
                        );
                        $dbeQuipment->where($UsedCond)->save($notUsed);
                        //设置赠送的座驾为使用
                        $my_equipment_isused = 1;
                    }
                    $free_equipment_effectivetime = date('Y-m-d H:i:s');
                    $free_equipment_expiretime = date('Y-m-d H:i:s',strtotime('+'.$free_equipment_validdays.' day'));
                }
                //获取该座驾的定义值
                $where_commodity = array(
                    'commodityid' => $free_commodityid,
                    'lantype' => $this->lantype
                );
                $commodity = M('commodity')->where($where_commodity)->find();
                //添加新座驾记录
                $free_equipment_data = array(
                    'userid' => $userid,
                    'commodityid' => $free_commodityid,
                    'commodityname' => $commodity['commodityname'],
                    'commodityflashid' => $commodity['commodityflashid'],
                    'commodityswf' => $commodity['commodityswf'],
                    'pcbigpic' => $commodity['pcbigpic'],
                    'pcsmallpic' => $commodity['pcsmallpic'],
                    'appbigpic' => $commodity['appbigpic'],
                    'appsmallpic' => $commodity['appsmallpic'],
                    'spendmoney' => 0,
                    'isused' => $my_equipment_isused,
                    'ispresent' => 1,
                    'effectivetime' => $free_equipment_effectivetime,
                    'expiretime' => $free_equipment_expiretime,
                    'operatetime' => date('Y-m-d H:i:s'),
                );
                $result = $dbeQuipment->add($free_equipment_data);

                //中奖奖项内容
                $reward_content = $commodity['commodityname'].$freeGiftRewardRule['value'].lan('DAYS','Api',$this->lantype);
                break;
            case '3':   //秀币奖励
                //更新用户余额
                $UsedCond = array(
                    'userid' => $userid
                );
                $result = M('balance')->where($UsedCond)->setInc('balance',$freeGiftRewardRule['value']);

                //中奖奖项内容
                $reward_content = $freeGiftRewardRule['value'].lan('MONEY_UNIT','Api',$this->lantype);
                break;
            default:
                $result = false;
                break;
        }

        if($result !== false){
            //发送消息通知
            $addtime = date('Y-m-d H:i:s');
            $nickname = M('member')->where(array('userid' => $userid))->getField('nickname');
            $title = lan('SYSTEM_MESSAGE','Api',$this->lantype);
            $content = lan('FREE_GIFT_REWARD_MESSAGE','Api',$this->lantype);
            $content = str_replace('{NICKNAME}',$nickname,$content);    //替换昵称
            $content = str_replace('{CONTENT}',$reward_content,$content);    //替换奖励内容
            $message = array(
                'userid' => $userid,
                'messagetype' => 0, //0系统消息、1好友消息
                'title' => $title,
                'content' => $content,
                'lantype' => $this->lantype,
                'read' => 0,    //是否已读，0未读、1已读
                'createtime' => $addtime
            );
            M('message')->add($message);
        }
        return true;
    }

	/**
	 * 发送弹幕
	 * @param userid: 用户userid
	 * @param userid: 用户userid
	 */
	public function sendFlyScreen($inputParams)
	{
		$consumeMoney = 3;
		$inputParams['consumeMoney'] = $consumeMoney;
		//检查用户余额
		$checkBalanceResult = $this
				->checkUserBalance($inputParams['userid'], $consumeMoney);
		if (200 == $checkBalanceResult['status']) {
			return $this->doSendFlyScreen($inputParams);
		} else {
			return $checkBalanceResult;
		}
	}

	/**
	 * 发送弹幕
	 * @param userid: 用户userid
	 */
	private function doSendFlyScreen($inputParams)
	{
		$userid = $inputParams['userid'];
		$db_Member = M('Member');
		$tran = new Model();
		$tran->startTrans();
		//更新用户余额
		$needmoney = $inputParams['consumeMoney'];
		$userBalance = array(
				'balance' => array('exp', 'balance-' . $needmoney),
				'spendmoney' => array('exp', 'spendmoney+' . $needmoney),
		);
		$db_Balance = M('Balance');
		$userBalanceResult = $tran
				->table('ws_balance')
				->where('userid=' . $userid)
				->save($userBalance);

		//用户信息
		$userCond = array('userid' => $userid);
		$member = $db_Member->where($userCond)->field($this->memberfield)->find();
		$balinfo = $db_Balance->where($userCond)->find();

		//插入spenddetail数据
		$insertSpend = array(
				'userid' => $userid,
				'targetid' => $inputParams['emceeuserid'],
				'familyid' => $member['familyid'],
				'tradetype' => 8,
				'giftid' => 0,
				'giftname' => 'flyscreen',
				'gifticon' => '',
				'giftprice' => $needmoney,
				'giftcount' => 1,
				'spendamount' => $needmoney,
				'tradetime' => date('Y-m-d H:i:s'),
				'content' => $member['nickname'].' Send a fly screen'
		);

		$spendResult = $this->processSpendRecordWithTrans($tran, $insertSpend);
		//更新用户等级
		$this->updateUserlevel($member, $balinfo);

		if ($spendResult && $userBalanceResult) {
			$tran->commit();
			$data['status'] = 200;
			$data['message'] = lan('BUY_SUCCESS', 'Api', $this->lantype);
			if (!$balinfo['balance']) {
				$balinfo['balance'] = '0';
			}
			$data['balance'] = $balinfo['balance'];
		} else {
			$tran->rollback();
			$data['status'] = -1;
			$data['message'] = lan('-1', 'Api', $this->lanPackage);
		}
		return $data;
	}
}