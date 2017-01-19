<?php
/* *
 * 功能：支付宝服务器异步通知页面
 * 版本：1.0
 * 日期：2016-06-06
 * 说明：
 * 以下代码只是为了方便商户测试而提供的样例代码，商户可以根据自己网站的需要，按照技术文档编写,并非一定要使用该代码。
 * 该代码仅供学习和研究支付宝接口使用，只是提供一个参考。


 *************************页面功能说明*************************
 * 创建该页面文件时，请留心该页面文件中无任何HTML代码及空格。
 * 该页面不能在本机电脑测试，请到服务器上做测试。请确保外部可以访问该页面。
 * 该页面调试工具请使用写文本函数logResult，该函数已被默认关闭，见alipay_notify_class.php中的函数verifyNotify
 * 如果没有收到该页面返回的 success 信息，支付宝会在24小时内按一定的时间策略重发通知
 */
require_once("alipay.config.php");
require_once("lib/alipay_notify.class.php");
require_once("lib/alipay_rsa.function.php");
require_once("lib/alipay_core.function.php");

//计算得出通知验证结果
$alipayNotify = new AlipayNotify($alipay_config);
//判断成功之后使用getResponse方法判断是否是支付宝发来的异步通知。
if($alipayNotify->getResponse($_POST['notify_id']) !== 'true'){
    logResult("Error：Response fail\r\n".json_encode($_POST));
    echo "response fail";exit;
}
//使用支付宝公钥验证签名
if(!$alipayNotify->getSignVeryfy($_POST, $_POST['sign'])){
    logResult("Error：Sign fail\r\n".json_encode($_POST));
    echo "sign fail";exit;
}
logResult("Success：\r\n".json_encode($_POST));

//签名验证通过，处理业务逻辑
$out_trade_no = $_POST['out_trade_no']; //商户订单号
$trade_no = $_POST['trade_no']; //支付宝交易号
$trade_status = $_POST['trade_status']; //交易状态

/*
 * 交易状态TRADE_SUCCESS的通知触发条件是商户签约的产品支持退款功能的前提下，买家付款成功；
 * 交易状态TRADE_FINISHED的通知触发条件是商户签约的产品不支持退款功能的前提下，买家付款成功；或者，商户签约的产品支持退款功能的前提下，交易已经成功并且已经超过可退款期限；
 * 交易成功之后，商户（高级即时到账或机票平台商）可调用批量退款接口，系统会发送退款通知给商户，具体内容请参见批量退款接口文档；
 * */
if($trade_status == 'TRADE_SUCCESS'){
    $alipayResult = new alipayResult();
    //开始事务
    $alipayResult->db->autocommit(false);

    //根据商户订单号获取充值记录
    $rechargeDetail = $alipayResult->getRechargedetail($out_trade_no);
    if(!$rechargeDetail){
        logResult("Error：Not find recharge detail\r\n".json_encode($_POST));exit;
    }
    //更新充值记录
    $updateRechargeDetail = $alipayResult->updateRechargeDetail($rechargeDetail,$trade_no);
    if(!$updateRechargeDetail){
        $alipayResult->db->rollback();
        logResult("Error：Update recharge detail fail\r\n".json_encode($_POST));exit;
    }
    //判断是否首次充值
    $getOtherRechargeDetail = $alipayResult->getOtherRechargeDetail($rechargeDetail);
    $showamount = $rechargeDetail['showamount'];
    if(!$getOtherRechargeDetail){
        //首次充值赠送10%秀币，VIP及座驾
        $firstRechargePresent = $alipayResult->firstRechargePresent($rechargeDetail);
        if(!$firstRechargePresent){
            $alipayResult->db->rollback();
            logResult("Error：First recharge present fail\r\n".json_encode($_POST));exit;
        }
        $showamount = $rechargeDetail['showamount'] * 1.1;
    }
    //更新用户余额
    $updateBalance = $alipayResult->updateBalance($rechargeDetail,$showamount);
    if(!$updateBalance){
        $alipayResult->db->rollback();
        logResult("Error：Update balance fail\r\n".json_encode($_POST));exit;
    }

    $alipayResult->db->commit();
    echo "success";exit;
}

/**
 * 支付宝充值，异步通知，业务处理
 */
class alipayResult{
    public $db;
    public function __construct(){
        //连接数据库
        if (!$this->db) {
            $config = require_once("../../Conf/config.php");
            $this->db = mysqli_connect($config['DB_HOST'], $config['DB_USER'], $config['DB_PWD'], $config['DB_NAME']);
            if (!$this->db) {
                logResult("Error：Could not connect (".mysqli_connect_error().")\r\n".json_encode($config));exit;
            }
        }
    }

    //根据商户订单号获取充值记录
    public function getRechargedetail($out_trade_no){
        $query = "SELECT * FROM ws_rechargedetail "
            ." WHERE orderno = '".$out_trade_no."' "
            ." AND status = '2' ";
        $result = $this->mysqliSelectOneResult($query);
        return $result;
    }

    //更新充值记录
    public function updateRechargeDetail($rechargeDetail,$trade_no){
        $query = "UPDATE ws_rechargedetail SET "
            ." status = '1',"
            ." orderid = '".$trade_no."'"
            ." WHERE rechargeid = '".$rechargeDetail['rechargeid']."'";
        $result = mysqli_query($this->db, $query);
        return $result;
    }

    //判断是否首次充值
    public function getOtherRechargeDetail($rechargeDetail){
        $query = "SELECT * FROM ws_rechargedetail "
            ." WHERE rechargeid != '".$rechargeDetail['rechargeid']."' "
            ." AND userid = '".$rechargeDetail['userid']."' "
            ." AND status = '1' ";
        $result = $this->mysqliSelectOneResult($query);
        return $result;
    }

    //首次充值赠送10%秀币，VIP及座驾
    public function firstRechargePresent($rechargeDetail){
        //赠送秀币
        $query = "INSERT INTO ws_rechargedetail SET "
            ." userid = '".$rechargeDetail['userid']."',"
            ." targetid = '".$rechargeDetail['targetid']."',"
            ." channelid = '".$rechargeDetail['channelid']."',"
            ." sellerid = '".$rechargeDetail['sellerid']."',"
            ." rechargetype = '".$rechargeDetail['rechargetype']."',"
            ." devicetype = '".$rechargeDetail['devicetype']."',"
            ." type = '".$rechargeDetail['type']."',"
            ." orderno = '".$rechargeDetail['orderno']."',"
            ." amount = '".$rechargeDetail['amount']."',"
            ." localunit = '".$rechargeDetail['localunit']."',"
            ." showamount = '".$rechargeDetail['showamount'] * 0.1."',"
            ." rechargetime = '".date('Y-m-d H:i:s')."',"
            ." status = '1',"
            ." ispresent = '1'";
        $result = mysqli_query($this->db, $query);
        if(!$result){
            return false;
        }

        //赠送7天高级VIP
        $vipDefaultQuery = "SELECT * FROM ws_vipdefinition "
            ." WHERE vipid = '1' ";
        $vipDefault = $this->mysqliSelectOneResult($vipDefaultQuery);

        $hasViprecordQuery = "SELECT * FROM ws_viprecord "
            ." WHERE vipid = '".$vipDefault['vipid']."' "
            ." AND userid = '".$rechargeDetail['userid']."' "
            ." AND expiretime > '".date('Y-m-d H:i:s')."' "
            ." ORDER BY expiretime DESC ";
        $hasViprecord = $this->mysqliSelectOneResult($hasViprecordQuery);

        $insertViprecordQuery = "INSERT INTO ws_viprecord SET "
            ." userid = '".$rechargeDetail['userid']."',"
            ." vipid = '".$vipDefault['vipid']."',"
            ." vipname = '".$vipDefault['vipname']."',"
            ." pcsmallvippic = '".$vipDefault['pcsmallviplogo']."',"
            ." appsmallvippic = '".$vipDefault['appsmallviplogo']."',";
        if($hasViprecord){
            $insertViprecordQuery = $insertViprecordQuery
                ." effectivetime = '".$hasViprecord['expiretime']."',"
                ." expiretime = '".date("Y-m-d H:i:s",strtotime('+7 days',strtotime($hasViprecord['expiretime'])))."',";
        }else{
            $insertViprecordQuery = $insertViprecordQuery
                ." effectivetime = '".date('Y-m-d H:i:s')."',"
                ." expiretime = '".date("Y-m-d H:i:s",strtotime('+7 days', time()))."',";
        }
        $insertViprecordQuery = $insertViprecordQuery
            ." spendmoney = '0',"
            ." ispresent = '1'";
        $insertViprecord = mysqli_query($this->db, $insertViprecordQuery);
        if(!$insertViprecord){
            return false;
        }

        //赠送7天自行车座驾
        $commodityQuery = "SELECT * FROM ws_commodity "
            ." WHERE commodityid = '14' ";
        $commodity = $this->mysqliSelectOneResult($commodityQuery);

        $hasEquipmentQuery = "SELECT * FROM ws_equipment "
            ." WHERE commodityid = '".$commodity['commodityid']."' "
            ." AND userid = '".$rechargeDetail['userid']."' "
            ." AND expiretime > '".date('Y-m-d H:i:s')."' "
            ." ORDER BY expiretime DESC ";
        $hasEquipment = $this->mysqliSelectOneResult($hasEquipmentQuery);

        $insertEquipmentQuery = "INSERT INTO ws_equipment SET "
            ." userid = '".$rechargeDetail['userid']."',"
            ." commodityid = '".$commodity['commodityid']."',"
            ." commodityname = '".$commodity['commodityname']."',"
            ." commodityflashid = '".$commodity['commodityflashid']."',"
            ." pcbigpic = '".$commodity['pcbigpic']."',"
            ." pcsmallpic = '".$commodity['pcsmallpic']."',"
            ." appbigpic = '".$commodity['appbigpic']."',"
            ." appsmallpic = '".$commodity['appsmallpic']."',"
            ." commodityswf = '".$commodity['commodityswf']."',";
        if ($hasEquipment){
            $insertEquipmentQuery = $insertEquipmentQuery
                ." isused = '".$hasEquipment['isused']."',"
                ." effectivetime = '".$hasEquipment['expiretime']."',"
                ." expiretime = '".date("Y-m-d H:i:s",strtotime('+7 days',strtotime($hasEquipment['expiretime'])))."',";
        }else{
            //查看用户是否有未过期的正在使用的座驾
            $hasEquipmentIsusedQuery = "SELECT * FROM ws_equipment "
                ." WHERE userid = '".$rechargeDetail['userid']."' "
                ." AND expiretime > '".date('Y-m-d H:i:s')."' "
                ." AND isused > '1' "
                ." ORDER BY expiretime DESC ";
            $hasEquipmentIsused = $this->mysqliSelectOneResult($hasEquipmentIsusedQuery);
            if($hasEquipmentIsused){
                $insertEquipmentQuery = $insertEquipmentQuery." isused = '0',";
            }else{
                //更新所有失效的座驾为未使用
                $oldEquipmentUpdateQuery = "UPDATE ws_equipment SET "
                    ." isused = '0',"
                    ." operatetime = '".date('Y-m-d H:i:s')."'"
                    ." WHERE userid = '".$rechargeDetail['userid']."'"
                    ." AND isused = '1'";
                mysqli_query($this->db, $oldEquipmentUpdateQuery);
                //设置赠送的座驾为使用
                $insertEquipmentQuery = $insertEquipmentQuery." isused = '1',";
            }
            $insertEquipmentQuery = $insertEquipmentQuery
                ." effectivetime = '".date('Y-m-d H:i:s')."',"
                ." expiretime = '".date("Y-m-d H:i:s", strtotime('+7 days', time()))."',";
        }

        $insertEquipmentQuery = $insertEquipmentQuery
            ." spendmoney = '0',"
            ." ispresent = '1',"
            ." operatetime = '".date('Y-m-d H:i:s')."'";
        $insertEquipment = mysqli_query($this->db, $insertEquipmentQuery);
        if(!$insertEquipment){
            return false;
        }

        return true;
    }

    //更新用户余额
    public function updateBalance($rechargeDetail,$showamount){
        //积分和总充值金额
        $SiteconfigQuery = "SELECT * FROM ws_siteconfig "
            ." WHERE sconfigid = '1' ";
        $siteconfig = $this->mysqliSelectOneResult($SiteconfigQuery);
        $totalrecharge_amount = $rechargeDetail['showamount']/$siteconfig['ratio'];

        $query = "UPDATE ws_balance SET "
            ." balance = balance+".$showamount.","
            ." point = point+".$totalrecharge_amount.","
            ." totalrecharge = totalrecharge+".$totalrecharge_amount
            ." WHERE userid = '".$rechargeDetail['userid']."'";
        $result = mysqli_query($this->db, $query);
        return $result;
    }

    //获取一条记录，并将结果集转换为数组
    private function mysqliSelectOneResult($query){
        $result = mysqli_query($this->db, $query);
        $row = mysqli_fetch_array($result,MYSQL_ASSOC);
        return $row;
    }

    //获取多条记录，并将结果集转换为数组
    private function mysqliSelectResult($query){
        $result = mysqli_query($this->db, $query);
        $arr = array();
        while ($row = mysqli_fetch_array($result,MYSQL_ASSOC)) {
            $arr[] = $row;
        }
        return $arr;
    }

}
?>