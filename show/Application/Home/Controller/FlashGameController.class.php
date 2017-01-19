<?php
namespace Home\Controller;
use Think\Controller;

/**
 * 
 * FLASH游戏接口控制器，涉及砸金蛋，打地鼠游戏
 * @author jiuwei 2016-09-22
 *
 */
class FlashGameController extends Controller
{
    private $eggAwardArr = array(10,10,10,10,10,10,10,30,30,30,10,10,10,10,10,10,10,30,30,100,
        10,10,10,10,10,10,10,30,30,30,10,10,10,10,10,10,10,30,30,100,
        10,10,10,10,10,10,10,30,30,30,10,10,10,10,10,10,10,30,30,100,
        10,10,10,10,10,10,10,30,30,100,10,10,10,10,10,10,10,30,30,30,
        10,10,10,10,10,10,10,30,30,100,10,10,10,10,10,10,10,30,30,30
    );
    
    private $digAwardArr = array(10,10,10,10,10,10,10,30,30,100,10,10,10,10,10,10,10,30,30,30,
        10,10,10,10,10,10,10,30,30,100,10,10,10,10,10,10,10,30,30,30,
        10,10,10,10,10,10,10,30,30,30,10,10,10,10,10,10,10,30,30,100,
        10,10,10,10,10,10,10,30,30,100,10,10,10,10,10,10,10,30,30,30,
        10,10,10,10,10,10,10,30,30,30,10,10,10,10,10,10,10,30,30,100,
        10,10,10,10,10,10,10,30,30,100,10,10,10,10,10,10,10,30,30,30,
        10,10,10,10,10,10,10,30,30,30,10,10,10,10,10,10,10,30,30,100,
        10,10,10,10,10,10,10,30,30,100,10,10,10,10,10,10,10,30,30,30,
        10,10,10,10,10,10,10,30,30,30,10,10,10,10,10,10,10,30,30,100,
        10,10,10,10,10,10,10,30,30,100,10,10,10,10,10,10,10,30,30,30,
        10,10,10,10,10,10,10,30,30,30,10,10,10,10,10,10,10,30,30,100,
        10,10,10,10,10,10,10,30,30,100,10,10,10,10,10,10,10,30,30,30,
        10,10,10,10,10,10,10,30,30,30,10,10,10,10,10,10,10,30,30,100,
        10,10,10,10,10,10,10,30,30,100,10,10,10,10,10,10,10,30,30,30,
        10,10,10,10,10,10,10,30,30,30,10,10,10,10,10,10,10,30,30,100,
        10,10,10,10,10,10,10,30,30,100,10,10,10,10,10,10,10,30,30,30,
        10,10,10,10,10,10,10,30,30,100,10,10,10,10,10,10,10,30,30,100,
        10,10,10,10,10,10,10,30,30,100,10,10,10,10,10,10,10,30,30,100
    );
    
    public function index(){
        echo $this->getRandEggAward($this->eggAwardArr);
    }
    
    /**
     * 玩砸蛋游戏 添加玩游戏的记录 以及获得的奖励
     */
    public function playZaegg(){
        
        $userid = $_POST["userid"];
        $emceeuserid = $_POST["emceeuserid"];
        $needmoney = $_POST["needmoney"];
        
        //echo $userid .'|'.$emceeuserid .'|'.$needmoney;
        if(empty($userid) || $userid < 0){
            $result['status'] = 3;
            $result['message'] = lan('400001', 'Home');
            echo json_encode($result);
            exit();
        }
        
        //生成奖励数据
        $playaward = $this->getRandEggAward($this->eggAwardArr);
        
        $dBalance = D("Balance");
        $balinfor = $dBalance->where(array('userid' => $userid))->find();
        
        // 判断虚拟币是否足够
        if ($balinfor['balance'] < $needmoney) {
            $result['status'] = 2;
            $result['message'] = lan('BALANCE_NOT_ENOUGH', 'Home');
            echo json_encode($result);
            exit();
        }
        
        $updatearr = array(
            'balance' => array('exp','balance-' . ($needmoney-$playaward)),
            'spendmoney' => array('exp','spendmoney+' . $needmoney)
        );
        
        $dBalance->where(array('userid' => $userid))->save($updatearr);
        
        /* //只有真实用户消费才算入主播收入，userid小于等于1000是运营账号
        if ($userid > 1000){
            $updateemceearr = array(
                'earnmoney' => array('exp','earnmoney+' . $needmoney)
            );
            $dBalance->where(array('userid' => $emceeuserid))->save($updateemceearr);
        } */
        
        $insertRec = array(
            'userid' => $userid,
            'emceeuserid' => $emceeuserid,
            'costmoney' => $needmoney,
            'playaward' => $playaward,
            'playtime' => date('Y-m-d H:i:s')            
        );
        
        D('Playeggrecord')->add($insertRec);
        $result['status'] = 1;
        $result['message'] = lan('OBTAIN_SHOWMONEY', 'Home') .' '.$playaward.' '.lan('MONEY_UNIT', 'Home');
        $result['playaward'] = $playaward;
        
        echo json_encode($result);
    }
    
    /**
     * 砸金蛋钱判断余额是否足够
     */
    public function queryBalance(){
        $userid = $_POST["userid"];
        $needmoney = $_POST["needmoney"];
        //$userid = $_REQUEST["userid"];
        //$needmoney = 1;
        
        if(empty($userid) || $userid < 0){
            $result['status'] = 3;
            $result['message'] = lan('400001', 'Home');
            echo json_encode($result);
            exit();
        }
        $dBalance = D("Balance");
        $balinfor = $dBalance->where(array('userid' => $userid))->find();
        
        // 判断虚拟币是否足够
        if ($balinfor['balance'] < $needmoney) {
            $result['status'] = 2;
            $result['message'] = lan('BALANCE_NOT_ENOUGH', 'Home');
            echo json_encode($result);
            exit();
        }
        
        $updatearr = array(
            'balance' => array('exp','balance-' . $needmoney),
            'spendmoney' => array('exp','spendmoney+' . $needmoney)
        );
        $dBalance->where(array('userid' => $userid))->save($updatearr);
        
        /* //只有真实用户消费才算入主播收入，userid小于等于1000是运营账号
         if ($userid > 1000){
         $updateemceearr = array(
         'earnmoney' => array('exp','earnmoney+' . $needmoney)
         );
         $dBalance->where(array('userid' => $emceeuserid))->save($updateemceearr);
         } */
        
        $result['status'] = 1;
        $result['message'] = lan('SUCCESSFUL', 'Home');
        
        echo json_encode($result);
        
    }
    
    /**
     * 打地鼠游戏结果请求结果
     */
    public function playDaDigletts(){
    
        $userid = $_POST["userid"];
        $emceeuserid = $_POST["emceeuserid"];
        $needmoney = $_POST["needmoney"];
        $playscore = $_POST["playscore"];
        
        if(empty($userid) || $userid < 0){
            $result['status'] = 2;
            $result['message'] = lan('PARAMETER_ERROR', 'Home');
            echo json_encode($result);
            exit();
        }
        
        $playaward = $playscore; //$this->getRandDiglettAward($this->digAwardArr, $playscore);
        
        $updatearr = array(
            'balance' => array('exp','balance+' . $playaward)
        );
        D("Balance")->where(array('userid' => $userid))->save($updatearr);
        
        $insertRec = array(
            'userid' => $userid,
            'emceeuserid' => $emceeuserid,
            'costmoney' => $needmoney,
            'playscore' => $playscore,
            'playaward' => $playaward,
            'playtime' => date('Y-m-d H:i:s')            
        );
        
        D('Playdiglettrecord')->add($insertRec);
        
        $result['status'] = 1;
        $result['message'] = lan('OBTAIN_SHOWMONEY', 'Home') .' '.$playaward.' '.lan('MONEY_UNIT', 'Home');
        $result['playaward'] = $playaward;
        
        echo json_encode($result);
    }
    
    private function getRandEggAward($eggAwardAVar){
        return $eggAwardAVar[rand(0,99)];
    }
    
    private function getRandDiglettAward($digAwardArr, $playscore){
        return $digAwardArr[$playscore*10 + rand($playscore-2,$playscore)];
    }
    
}

?>