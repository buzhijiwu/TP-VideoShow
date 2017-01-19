<?php
/**
 * 商城控制器
 */
namespace Home\Controller;

use Think\Model;
class MallController extends CommonController {
		
	/**
	 * 会员
	 */
    function index()
    {
        $vips = D('Vipdefinition')->getAllVips($this->lan);
        $db_Discount = D('Discount');
        $db_Privilege = D('Privilege');

        foreach ($vips as $k=>$v)
        {
            $vips[$k]['threemonth'] = $db_Discount->getDiscountPrice(1, 3, $v['vipprice']);
            $vips[$k]['twelvemonth'] = $db_Discount->getDiscountPrice(1, 12, $v['vipprice']);
            $vips[$k]['privileges'] = $db_Privilege->getVipDefinePrivileges($v['vipid'], $this->lan);
        }

       //dump($vips);
        $this->assign('vips', $vips);
        $this->assign('menu', 1);

        $this->display();
    }

    /**
     * status : 0:成功，1：用户没有登录，2：用户余额不足，3：操作失败
     *
     */
    public function buyVip(){
        $this->checkUser();
        $inputParams = array(
            'vipid' => I('POST.vipid', 1, 'intval'),
            'duration' => I('POST.duration', 1, 'intval'),
            'userid' => session('userid')
        );

        $db_Vipdefinition = D('Vipdefinition');
        $vipCond = array(
            'vipid'=> $inputParams['vipid'],
            'lantype' => $this->lan
        );
        $vip = $db_Vipdefinition->where($vipCond)->find();
        $db_Discount = D('Discount');
        $spendamount = $db_Discount->getDiscountPrice(2, $inputParams['duration'], $vip['vipprice']);
        $checkBalanceResult = $this->checkUserBalance($inputParams['userid'], $spendamount);

        if ($checkBalanceResult)
        {
            $this->doBuyVip($inputParams, $vip, $spendamount);
        }
        else
        {
            $result['status'] = 2;
            $result['msg'] = lan('BALANCE_NOT_ENOUGH', 'Home');
            echo json_encode($result);
        }
    }

    /**
     * @param $inputParams
     * @param $commodity
     */
    private function doBuyVip($inputParams, $vip, $spendamount){
        $db_Member = D('Member');
        $userInfo = $db_Member->getMemberInfoByUserID($inputParams['userid']);
        $tran = new Model();
        $tran->startTrans();
        //插入spenddetail数据
        $spenddetail['userid'] = $inputParams['userid'];
        $spenddetail['targetid'] = $inputParams['userid'];
        $spenddetail['familyid'] = $userInfo['familyid'];
        $spenddetail['tradetype'] = 7;
        $spenddetail['giftid'] = $vip['vipid'];
        $spenddetail['giftname'] = $vip['vipname'];
        $spenddetail['gifticon'] = $vip['pcsmallviplogo'];
        $spenddetail['giftprice'] = $vip['vipprice'];
        $spenddetail['giftcount'] = $inputParams['duration'];
        $spenddetail['spendamount'] = $spendamount;
        $spenddetail['tradetime'] = date('Y-m-d H:i:s');
        $spenddetail['status'] = 1;
        //$spendResult = $tran->table('ws_spenddetail')->add($spenddetail);
        $spendResult = $this->processSpendRecordWithTrans($tran, $spenddetail);
        //更新余额
        $balance = array(
            'balance' => array('exp', 'balance-'.$spendamount),
            'spendmoney' => array('exp', 'spendmoney+'.$spendamount),
        );
        $balanceResult = $tran->table('ws_balance')->where('userid=' . $inputParams['userid'])->save($balance);
        $balanceCond = array('userid' => $inputParams['userid']);
        $balance = D('Balance')->where($balanceCond)->field('spendmoney,balance')->find();
        //用户等级
        $userNewLevel = D('Levelconfig')->getUserLevelBySpendMoney($balance['spendmoney']);
        if ($userNewLevel && $userNewLevel != $userInfo['userlevel'])
        {
            $userNewInfo['userlevel'] = $userNewLevel;
            $userInfo['userlevel'] = $userNewLevel;            
            $userNewInfo1 = $this->updateUserNextlevelAndGrade($userInfo);
        }

        //添加viprecord数据
        $hasViprecord = D('Viprecord')->getViprecordByUseridAndVipid($inputParams['userid'], $vip['vipid']);
        if($hasViprecord){
            $viprecord['effectivetime'] = $hasViprecord['expiretime'];
            $viprecord['expiretime'] = date("Y-m-d H:i:s",strtotime('+'.$inputParams['duration'].' months',strtotime($hasViprecord['expiretime'])));
        }else{
            $viprecord['effectivetime'] = date('Y-m-d H:i:s');
            $viprecord['expiretime'] = date("Y-m-d H:i:s",strtotime('+'.$inputParams['duration'].' months'));
        }
        $viprecord['userid'] = $inputParams['userid'];
        $viprecord['vipid'] = $vip['vipid'];
        $viprecord['vipname'] = $vip['vipname'];
        $viprecord['pcsmallvippic'] = $vip['pcsmallviplogo'];
        $viprecord['appsmallvippic'] = $vip['appsmallviplogo'];
        $viprecord['spendmoney'] = $spendamount;
        $viprecordResult = $tran->table('ws_viprecord')->add($viprecord);

        $userNewInfo['isvip'] = 1;
        $userInfoResult = $tran->table('ws_member')->where('userid=' . $inputParams['userid'])->save($userNewInfo);
        if ($spendResult && $balanceResult && $viprecordResult) {
            $tran->commit();
            $result['status'] = 0;
            $result['msg'] = lan('OPERATION_SUCCESSFUL', 'Home');
            if ($userNewInfo1['nextlevel']&&$userNewInfo1['grade']) {
                $result['nextlevel'] = $userNewInfo1['nextlevel'];
                $result['grade'] = $userNewInfo1['grade']; 
                session('nextlevel',$userNewInfo1['nextlevel']);
                session('grade',$userNewInfo1['grade']);                
            }   
            session('balance',$balance['balance']);        
            echo json_encode($result);
        } else {
            $tran->rollback();
            $result['status'] = 3;
            $result['msg'] = lan('OPERATION_FAILED', 'Home');
            echo json_encode($result);
        }
    }

    private function checkUserBalance($userid, $price)
    {
        $userCond = array('userid' => $userid);
        $db_Balance = D('Balance');
        $balance = $db_Balance->where($userCond)->field('balance')->find();
        if ($price > $balance['balance'])
        {
            return false;
        }
        else
        {
            return true;
        }
    }

    /**
     * 靓号
     */
    public function nicenumber()
    {
        $db_Nicenumber = D('Nicenumber');
        $assign['fiveBit'] = $db_Nicenumber->getNicenos(5);
        $assign['sixBit'] = $db_Nicenumber->getNicenos(6);
        $assign['sevenBit'] = $db_Nicenumber->getNicenos(7);
        $assign['eightBit'] = $db_Nicenumber->getNicenos(8);
        $assign['hot'] = $db_Nicenumber->getHotNicenos();
        $assign['fiveBitHot'] = $db_Nicenumber->getHotNicenos(5, 0, 3);
        $assign['sixBitHot'] = $db_Nicenumber->getHotNicenos(6, 0, 3);
        $assign['sevenBitHot'] = $db_Nicenumber->getHotNicenos(7, 0, 3);
        $assign['eightBitHot'] = $db_Nicenumber->getHotNicenos(8, 0, 3);
        $this->assign($assign);
        $this->assign('menu', 2);
        $this->display();
    }

    /**
     * 换一换
     */
    public function getNiceno()
    {
        $nolength = I('POST.nolength', 6, 'trim');
        $db_Nicenumber = D('Nicenumber');
        $nicenos = $db_Nicenumber->getNicenos($nolength);
        echo json_encode($nicenos);
    }

    /**
     * status:0:靓号存在，且未出售，1：靓号存在，但已出售，2：靓号不存在
     */
    public function searchNiceno()
    {
        $searchno = I('POST.searchno', 6, 'trim');
        $queryCond = array(
            'niceno' => $searchno,
        );
        $db_Nicenumber = D('Nicenumber');
        $niceno = $db_Nicenumber->where($queryCond)->find();
        if ($niceno)
        {
            if ($niceno['isused'])
            {
                //靓号存在，但已出售
                $result['status'] = 1;
                $result['msg'] = lan('NICENO_IS_SALED', 'Home');
            }
            else
            {
                //靓号存在，且未出售
                $result['status'] = 0;
                $result['msg'] = lan('OPERATION_SUCCESSFUL', 'Home');
                $result['niceno'] = $niceno;
            }
        }
        else
        {
            //该靓号不存在
            $result['status'] = 2;
            $result['msg'] = lan('NICENO_IS_NOT_EXIST', 'Home');
        }
        echo json_encode($result);
    }

    /**
     * status : 0:成功，1：用户没有登录，2：用户余额不足，3：操作失败,4:靓号已被购买
     *
     */
    public function buyNiceno()
    {
        $this->checkUser();
        $inputParams = array(
            'userid' => session('userid'),
            'niceno' => I('POST.niceno', 6, 'trim'),
            'duration' => I('POST.duration', 6, 'trim'),
        );

        $db_Nicenumber = D('Nicenumber');
        $niceno = $db_Nicenumber->where(array('niceno' => array('eq', $inputParams['niceno'])))->find();
        if ($niceno['isused']) {
            $result['status'] = 4;
            $result['msg'] = lan('NICENO_IS_SALED', 'Home');
            echo json_encode($result);
            exit;
        }

        $db_Discount = D('Discount');
        $spendamount = $db_Discount->getDiscountPrice(2, $inputParams['duration'], $niceno['price']);
        $checkBalanceResult = $this->checkUserBalance($inputParams['userid'], $spendamount);
        if ($checkBalanceResult) {
            $this->doBuyNiceno($inputParams, $niceno, $spendamount);
        } else {
            $result['status'] = 2;
            $result['msg'] = lan('BALANCE_NOT_ENOUGH', 'Home');
            echo json_encode($result);
        }
    }

    private function doBuyNiceno($inputParams, $niceno, $spendamount)
    {
        $db_Member = D('Member');
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
        //$spendResult = $tran->table('ws_spenddetail')->add($spenddetail);
        $spendResult = $this->processSpendRecordWithTrans($tran, $spenddetail);
        //更新余额
        $balance = array(
            'balance' => array('exp', 'balance-'.$spendamount),
            'spendmoney' => array('exp', 'spendmoney+'.$spendamount),
        );
        $balanceResult = $tran->table('ws_balance')->where('userid=' . $inputParams['userid'])->save($balance);
        $balanceCond = array('userid' => $inputParams['userid']);
        $balance = D('Balance')->where($balanceCond)->field('spendmoney')->find();
        //用户等级
        $userNewLevel = D('Levelconfig')->getUserLevelBySpendMoney($balance['spendmoney']);
        if ($userNewLevel && $userNewLevel != $userInfo['userlevel'])
        {
            $userNewInfo['userlevel'] = $userNewLevel;
            $userInfo['userlevel'] = $userNewLevel;            
            $userNewInfo1 = $this->updateUserNextlevelAndGrade($userInfo);            

        }
        //更新靓号数据，如果用户有多个靓号，那么使用当前购买的靓号，其他靓号置为未使用
        $buyNicenoData['userid'] = $inputParams['userid'];
        $buyNicenoData['isused'] = 1;
        $buyNicenoData['operatetime'] = date('Y-m-d H:i:s');
        $buyNicenoResult = $tran->table('ws_nicenumber')->where('niceno=' . $inputParams['niceno'])->save($buyNicenoData);
        $userNewInfo['niceno'] = $inputParams['niceno'];
        $userInfoResult = $tran->table('ws_member')->where('userid=' . $inputParams['userid'])->save($userNewInfo);

        $oldNicenoCond = array(
            'userid' => $inputParams['userid'],
            'isused' => 1
        );
        $oldNicenoCond['effectivetime'] = array('elt',date('Y-m-d H:i:s'));
        $oldNicenoCond['expiretime'] = array('gt',date('Y-m-d H:i:s'));
        $oldNiceno['isused'] = 0;
        $oldNiceno['operatetime'] = date('Y-m-d H:i:s');
        $oldNicenoResult = $tran->table('ws_nicenumrecord')->where($oldNicenoCond)->save($oldNiceno);

        $nicenoRecord['userid'] = $inputParams['userid'];
        $nicenoRecord['nicenumber'] = $inputParams['niceno'];
        $nicenoRecord['spendmoney'] = $spendamount;
        $nicenoRecord['isused'] = 1;
        $nicenoRecord['effectivetime'] = date('Y-m-d H:i:s');
        $nicenoRecord['expiretime'] = date("Y-m-d H:i:s",strtotime('+'.$inputParams['duration'].' months', time()));
        $nicenoRecord['operatetime'] = date('Y-m-d H:i:s');
        $nicenoRecordResult = $tran->table('ws_nicenumrecord')->add($nicenoRecord);

        if ($spendResult && $balanceResult && $buyNicenoResult && $userInfoResult && $nicenoRecordResult) {
            $tran->commit();
            $result['status'] = 0;
            $result['msg'] = lan('OPERATION_SUCCESSFUL', 'Home');
            if ($userNewInfo1['nextlevel']&&$userNewInfo1['grade']) {
                $result['nextlevel'] = $userNewInfo1['nextlevel'];
                $result['grade'] = $userNewInfo1['grade']; 
                session('nextlevel',$userNewInfo1['nextlevel']);
                session('grade',$userNewInfo1['grade']);                
            }   
            session('balance',$userNewInfo1['balance']);             
            echo json_encode($result);
        } else {
            $tran->rollback();
            $result['status'] = 3;
            $result['msg'] = lan('OPERATION_FAILED', 'Home');
            echo json_encode($result);
        }
    }
    /**
     * 守护
     */
    public function guardInfor(){
        $db_Guarddefinition = D('Guarddefinition');
        $db_Privilege = D('Privilege');
        $guardList = $db_Guarddefinition->getAllGuards($this->lan);

        foreach ($guardList as $key => $value)
        {
            $guardList[$key]['privilege'] = $db_Privilege->getGuardDefinePrivileges($value['guardid'], $this->lan);
        }
        $this->assign('guardlist', $guardList);
        $this->assign('menu', 3);
        $this->display();
    }
    /**
     * 座驾
     */
    public function equipmentlist(){
      //  $cars = D('Commodity')->getAllMotoring(1,$this->lan);
        $assign['cars'] = D('Commodity')->getAllMotoring(1,$this->lan);
       // dump($cars);
        $this->assign($assign);
        $this->assign('menu', 4);
        $this->display();
    }

    /**
     * status : 0:成功，1：用户没有登录，2：用户余额不足，3：操作失败
     *
     */
    public function buyCar(){
        $this->checkUser();
        $inputParams = array(
            'userid' => session('userid'),
            'comid' => I('POST.comid', 1, 'intval'),
            'duration' => I('POST.duration', 2, 'intval'),
           );

        $db_Commodity = D('Commodity');
        $commodityCond = array(
            'commodityid' => $inputParams['comid'],
            'commoditytype' => 1,
            'lantype' => $this->lan
        );
        $commodity = $db_Commodity->where($commodityCond)->find();
        $db_Discount = D('Discount');
        $spendamount = $db_Discount->getDiscountPrice(2, $inputParams['duration'], $commodity['commodityprice']);
        $checkBalanceResult = $this->checkUserBalance($inputParams['userid'], $spendamount);

        if ($checkBalanceResult) {
            $this->doBuyEquipment($inputParams, $commodity, $spendamount);
        } else {
            $result['status'] = 2;
            $result['msg'] = lan('BALANCE_NOT_ENOUGH', 'Home');
            echo json_encode($result);
        }
    }

    private function doBuyEquipment($inputParams, $commodity, $spendamount){
        $db_Member = D('Member');
        $userInfo = $db_Member->getMemberInfoByUserID($inputParams['userid']);
        $tran = new Model();
        $tran->startTrans();
        //插入spenddetail数据
        $spenddetail['userid'] = $inputParams['userid'];
        $spenddetail['targetid'] = $inputParams['userid'];
        $spenddetail['familyid'] = $userInfo['familyid'];
        $spenddetail['tradetype'] = 2;
        $spenddetail['giftid'] = $commodity['commodityid'];
        $spenddetail['giftname'] = $commodity['commodityname'];
        $spenddetail['gifticon'] = $commodity['appsmallpic'];
        $spenddetail['giftprice'] = $commodity['commodityprice'];
        $spenddetail['giftcount'] = $inputParams['duration'];
        $spenddetail['spendamount'] = $spendamount;
        $spenddetail['tradetime'] = date('Y-m-d H:i:s');
        $spenddetail['status'] = 1;
        //$spendResult = $tran->table('ws_spenddetail')->add($spenddetail);
        $spendResult = $this->processSpendRecordWithTrans($tran, $spenddetail);
        //更新余额
        $balance = array(
            'balance' => array('exp', 'balance-'.$spendamount),
            'spendmoney' => array('exp', 'spendmoney+'.$spendamount),
        );
        $balanceResult = $tran->table('ws_balance')->where('userid=' . $inputParams['userid'])->save($balance);
        $balanceCond = array('userid' => $inputParams['userid']);
        $balance = D('Balance')->where($balanceCond)->field('spendmoney')->find();
        //用户等级
        $userNewLevel = D('Levelconfig')->getUserLevelBySpendMoney($balance['spendmoney']);
        if ($userNewLevel && $userNewLevel != $userInfo['userlevel']){
            $userNewInfo['userlevel'] = $userNewLevel;
            $tran->table('ws_member')->where('userid=' . $inputParams['userid'])->save($userNewInfo);
            $userInfo['userlevel'] = $userNewLevel;            
            $userNewInfo1 = $this->updateUserNextlevelAndGrade($userInfo);            
        }

        //修改或添加equipment数据
        $hasEquipment = D('Equipment')->getEquipmentByUseridAndComid($inputParams['userid'], $commodity['commodityid']);
        if ($hasEquipment){
            $equipment['isused'] = $hasEquipment['isused'];
            $equipment['effectivetime'] = $hasEquipment['expiretime'];
            $equipment['expiretime'] = date("Y-m-d H:i:s",strtotime('+'.$inputParams['duration'].' months',strtotime($hasEquipment['expiretime'])));
        }else{
            //查看用户是否有未过期的正在使用的座驾
            $equipment_isused = D('Equipment')->getMyEquipmentsByCon(array('userid' => $inputParams['userid']));
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
            $equipment['expiretime'] = date("Y-m-d H:i:s", strtotime('+' . $inputParams['duration'] . ' months'));
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
            $result['status'] = 0;
            $result['msg'] = lan('OPERATION_SUCCESSFUL', 'Home');
            if ($userNewInfo1['nextlevel']&&$userNewInfo1['grade']) {
                $result['nextlevel'] = $userNewInfo1['nextlevel'];
                $result['grade'] = $userNewInfo1['grade']; 
                session('nextlevel',$userNewInfo1['nextlevel']);
                session('grade',$userNewInfo1['grade']);                
            }            
            session('balance',$userNewInfo1['balance']);    
            echo json_encode($result);
        } else {
            $tran->rollback();
            $result['status'] = 3;
            $result['msg'] = lan('OPERATION_FAILED', 'Home');
            echo json_encode($result);
        }
    }

}