<?php
namespace Api\Controller;
use Think\Controller;
/**
 * 充值相关接口
 *
 * 主要处理与充值相关的业务逻辑
 * getRechargeChannels  获取充值渠道
 * userRecharge  用户充值
 * rechbycallingcard  通过充值卡和游戏卡充值
 * rechargeByBank  通过LOCALBANK 充值
 * rechargeByVisa  通过VISA 充值
 * rechargeLog  充值添加日志
 *
 */
class RechargeController extends CommonController {

    public function _initialize(){
        parent::_initialize();
    }

    /**
     * 获取充值渠道
     * @param userid：当前登录用户ID
     * @param token：用户token值
     * @param devicetype：设备类型 0 安卓 1 iOS 2PC
     */
    public function getRechargeChannels($inputParams){
        $deviceType = $inputParams['devicetype'] ? $inputParams['devicetype'] : 0;

        //获取所有充值渠道
        $rechannels = D('Rechargechannel', 'Modapi')->getAllReChannels($deviceType,$this->lantype);

        //返回数据
        $data = array(
            'status' => 200,
            'message' => lan('200', 'Api', $this->lanPackage),
            'datalist' => $rechannels
        );
        return $data;
    }

    public function getRechargeChannels135($inputParams){
        $deviceType = $inputParams['devicetype'] ? $inputParams['devicetype'] : 0;

        //获取所有充值渠道
        $rechannels = D('Rechargechannel', 'Modapi')->getAllReChannels($deviceType,$this->lantype);

        //保留四组支付方式
        $rechannels_new = array();
        foreach($rechannels as $key => $val){
            if($key < 4){
                $rechannels_new[] = $val;
            }
        }

        //返回数据
        $data = array(
            'status' => 200,
            'message' => lan('200', 'Api', $this->lanPackage),
            'datalist' => $rechannels_new
        );
        return $data;
    }

    /**
     * 用户充值（仅iOS使用）
     * @param userid：当前登录用户ID
     * @param targetid：充值目标用户id（给这个用户充值，如果是给自己充值，那么userid和targetid相同）
     * @param type：商家名称
     * @param orderno：订单号
     * @param rechargedefid：充值秀币与当地货币记录
     * @param sellerid：运营商ID 或者游戏厂家ID 或者 银行卡ID
     * @param status：充值状态 0：失败 1：成功 2：处理中
     * @param channelid：充值渠道ID
     * @param devicetype：设备类型 0 安卓 1 iOS 2PC
     * @param rechargetype：充值类型 0 充值卡 1游戏卡 2 ATM DEBIT 3 VISA MATER 4 SMS+ 5 INTERNET BANK 6 AGENT 7 Applestore 8 Paypal
     * @param deviceid：设备唯一号
     * @param requestid：请求序列id
     * @param applereceipt：用于到AppStore市场校验的秘钥
     */
    public function userRecharge($inputParams){
        //调用充值接口，先添加日志记录
        file_put_contents(__ROOT__."Data/ios/ios-".date('Ymd').".log","Time：".date("Y-m-d H:i:s")."Recharge Request:".json_encode($inputParams)."\n\n",FILE_APPEND);

        //查询充值日志，是否有未校验的记录
        $dbRechargelog = M('Rechargelog');
        $rechargeLogCond = array(
            'userid' => $inputParams['userid'],
            'requestid' => $inputParams['requestid'],
            'serverstatus' => 0,    //服务端状态 0：未校验 1：成功 2：失败
        );
        $rechargeLog = $dbRechargelog->where($rechargeLogCond)->find();
        if (!$rechargeLog) {
            $data['status'] = 500;
            $data['message'] = lan('500', 'Api', $this->lanPackage);
            return $data;
        }

        //验证充值记录
        $receiptData = array(
            'receipt-data' => $inputParams['applereceipt']
        );
        $jsonReceiptData = json_encode($receiptData);
        $url = 'https://buy.itunes.apple.com/verifyReceipt';  //正式验证地址
        //版本升级时苹果官方沙盒充值测试，固定这个userid到沙盒环境校验
        if ((1555 ==  $inputParams['userid']) || (1785 ==  $inputParams['userid']))
        {
            $url = 'https://sandbox.itunes.apple.com/verifyReceipt'; //测试验证地址
        }
		//$url = 'https://sandbox.itunes.apple.com/verifyReceipt'; //测试验证地址
        $response = $this->http_post_data($url, $jsonReceiptData);
         //添加日志记录验证结果
        file_put_contents(__ROOT__."Data/ios/ios-".date('Ymd').".log","Time：".date("Y-m-d H:i:s")."Apple response:".json_encode($response)."\n\n",FILE_APPEND);

        if ($response['status'] == '0') {   //校验成功
            $transaction_id = $response['receipt']['in_app'][0]['transaction_id'];
            $bundle_id = $response['receipt']['bundle_id'];
            if (($transaction_id == $inputParams['orderno']) && ($bundle_id == 'com.xlingmao.jiuwei')) {
                //查询充值记录
                $dbRechargedetail = M('Rechargedetail');
                $rechargeCond = array(
                    'channelid' => $inputParams['channelid'],
                    'rechargetype' => $inputParams['rechargetype'],
                    'orderno' => $inputParams['orderno'],
                );
                $sameOrderno = $dbRechargedetail->where($rechargeCond)->select();
                if ($sameOrderno) {
                    //充值记录中是否已有相同订单号
                    $updateRechargeLog = array(
                        'serverstatus' => 3,
                        'applestatus' => $response['status'],
                        'transactionid' => $response['receipt']['in_app'][0]['transaction_id'],
                        'productid' => $response['receipt']['in_app'][0]['product_id'],
                        'orderno' => $inputParams['orderno'],
                        'bundleid' => $response['receipt']['bundle_id'],
                        'responsetime' => date('Y-m-d H:i:s'),
                    );
                    $dbRechargelog->where($rechargeLogCond)->save($updateRechargeLog);

                    $data['status'] = 404002;
                    $data['message'] = lan('404002', 'Api', $this->lanPackage);
                    return $data;
                } else {
                    $updateRechargeLog = array(
                        'serverstatus' => 1,
                        'applestatus' => $response['status'],
                        'transactionid' => $response['receipt']['in_app'][0]['transaction_id'],
                        'productid' => $response['receipt']['in_app'][0]['product_id'],
                        'orderno' => $inputParams['orderno'],
                        'bundleid' => $response['receipt']['bundle_id'],
                        'responsetime' => date('Y-m-d H:i:s'),
                    );
                    $dbRechargelog->where($rechargeLogCond)->save($updateRechargeLog);

                    //根据充值ID获取充值规则定义
                    $rechDefId = $response['receipt']['in_app'][0]['product_id'];
                    $rechargeDef = D('Rechargedefinition', 'Modapi')->getReDefByRechdefid($rechDefId, $inputParams['channelid'], $inputParams['rechargetype'], $inputParams['devicetype'], $this->lantype);

                    //验证通过，执行充值
                    $this->doRecharge($inputParams, $rechargeDef);

                    //获取用户余额
                    $balance = M('Balance')->where(array('userid' => $inputParams['userid']))->getField('balance');
                    if (!$balance) {
                        $balance = 0;
                    }

                    //返回结果
                    $data['status'] = 200;
                    $data['message'] = lan('200', 'Api', $this->lanPackage);
                    $data['balance'] = $balance;
                    return $data;
                }
            } else {
                //不是在waashow平台的消费
                $updateRechargeLog = array(
                    'serverstatus' => 5,
                    'applestatus' => $response['status'],
                    'transactionid' => $response['receipt']['in_app'][0]['transaction_id'],
                    'productid' => $response['receipt']['in_app'][0]['product_id'],
                    'orderno' => $inputParams['orderno'],
                    'bundleid' => $response['receipt']['bundle_id'],
                    'responsetime' => date('Y-m-d H:i:s'),
                );
                $dbRechargelog->where($rechargeLogCond)->save($updateRechargeLog);

                $data['status'] = 500;
                $data['message'] = lan('500', 'Api', $this->lanPackage);
                return $data;
            }
        } else {
            //校验失败
            $updateRechargeLog = array(
                'serverstatus' => 4,
                'applestatus' => $response['status'],
                'transactionid' => $response['receipt']['in_app'][0]['transaction_id'],
                'productid' => $response['receipt']['in_app'][0]['product_id'],
                'orderno' => $inputParams['orderno'],
                'bundleid' => $response['receipt']['bundle_id'],
                'responsetime' => date('Y-m-d H:i:s')
            );
            $dbRechargelog->where($rechargeLogCond)->save($updateRechargeLog);

            $data['status'] = 404001;
            $data['message'] = lan('404001', 'Api', $this->lanPackage);
            return $data;
        }
    }

    /**
     * 充值日志
     * @param userid：当前登录用户ID
     * @param deviceid：设备唯一号
     */
    public function rechargeLog($inputParams){
        $userid = $inputParams['userid'];
        $deviceid = $inputParams['deviceid'];
        $reqproductid = $inputParams['reqproductid'];

        //查询一个小时内的充值次数
        $dbRechargelog = M('Rechargelog');
        $rechargeLogCond = array(
            'userid' => $userid,
            'requesttime' => array('gt', date('Y-m-d H:i:s',strtotime('-1 hours')))
        );
        $rechlogCount = $dbRechargelog->where($rechargeLogCond)->count();

        //一小时最多充值30次
        if ($rechlogCount >= 30) {
            $data['status'] = 404003;
            $data['message'] = lan('404003', 'Api', $this->lanPackage);
            return $data;
        }

        //添加充值日志
        $rechargeLog = array(
            'userid' => $userid,
            'reqproductid' => $reqproductid,
            'serverstatus' => 0,
            'deviceid' => $deviceid,
            'requesttime' => date('Y-m-d H:i:s'),
        );
        $result = $dbRechargelog->add($rechargeLog);

        //返回结果
        if ($result === false) {
            $data['status'] = -1;
            $data['message'] = lan('-1', 'Api', $this->lanPackage);
        } else {
            $data['status'] = 200;
            $data['message'] = lan('200', 'Api', $this->lanPackage);
            $data['requestid'] = $result;
        }
        return $data;
    }

    /**
     * 通过充值卡和游戏卡充值
     * @param userid：当前登录用户ID
     * @param type：商家名称
     * @param channelid：充值渠道ID
     * @param sellerid：运营商ID 或者游戏厂家ID 或者 银行卡ID
     * @param devicetype：设备类型 0 安卓 1 iOS 2PC
     * @param rechargetype：充值类型 0 充值卡 1游戏卡 2 ATM DEBIT 3 VISA MATER 4 SMS+ 5 INTERNET BANK 6 AGENT 7 Applestore 8 Paypal
     * @param pin：密码
     * @param serial：账号
     * @param distributeid: 应用商店渠道
     *
     1pay查询接口
        $data_ep = "access_key=" . $access_key . "&pin=" . $pin . "&serial=" . $serial . "&transId=&transRef=" . $transRef . "&type=" . $type;
        $signature_ep = hash_hmac("sha256", $data_ep, $secret);
        $data_ep.= "&signature=" . $signature_ep;
        $query_api_ep = $this->execPostRequest('https://api.1pay.vn/card-charging/v5/query', $data_ep);
        $decode_cardCharging=json_decode($json_cardCharging,true);  // decode json
        $description_ep = $decode_cardCharging["description"];   // transaction description
        $status_ep = $decode_cardCharging["status"];
        $amount_ep = $decode_cardCharging["amount"];       // card's amount
        // Merchant handle SQL
        //echo "2".$description_ep."=".$status_ep."=".$amount_ep;
     */
    public function rechbycallingcard($inputParams){
        $devicetype = $inputParams['devicetype'];
        $userid = $inputParams['userid'] ? $inputParams['userid'] : -1;
        $type = $inputParams['type'];
        $pin = $inputParams['pin'];
        $serial = $inputParams['serial'];
        $channelid = $inputParams['channelid'] ? $inputParams['channelid'] : -1;
        $sellerid = $inputParams['sellerid'] ? $inputParams['sellerid'] : -1;
        $rechargetype = $inputParams['rechargetype'] ? $inputParams['rechargetype'] : -1;
        $distributeid = $inputParams['distributeid'] ? (int)$inputParams['distributeid'] : 0;

        //验证必要参数
        if(!$pin || !$serial){
            $data['status'] = 400;
            $data['message'] = lan('400', 'Api', $this->lanPackage);
            return $data;
        }

        //调用1pay充值接口
        $transRef = $pin . getRandomCode(); //商家交易号
        $access_key = '6cj87xppb6ql4grs8g27'; //1pay定义的key值
        $secret = 'dhsirq1kt34af5vmp1t6i111jfugn0d0'; //1pay定义的secret值
        $json_data = "access_key=" . $access_key . "&pin=" . $pin . "&serial=" . $serial . "&transRef=" . $transRef . "&type=" . $type;
        $signature = hash_hmac("sha256", $json_data, $secret);
        $json_data .= "&signature=" . $signature;
        $json_cardCharging = $this->execPostRequest('https://api.1pay.vn/card-charging/v5/topup', $json_data);
        $decode_cardCharging = json_decode($json_cardCharging,true);
        error_log("0=" . $json_data);
        error_log("1=" . $json_cardCharging);

        //验证充值结果
        $description = $decode_cardCharging["description"];   //充值状态描述
        $status = $decode_cardCharging["status"];
        $amount = $decode_cardCharging["amount"];       //卡余额
        $transId = $decode_cardCharging["transId"];
        $ratio = 0.01;
        $showamount = $amount * $ratio;

        //余额不足
        if ($amount <= 0) {
            $data['status'] = 404002;
            $data['message'] = $description;
            return $data;
        }

        //验证充值结果，添加充值记录
        $dbRechargedetail = M('Rechargedetail');
        if($status == "00"){
            //查询充值记录
            $rechrecord = $dbRechargedetail->where(array('userid' =>$userid))->find();

            //充值成功
            $insertReDet = array(
                'userid' => $userid,
                'targetid' => $userid,  //充值目标用户id（给这个用户充值，如果是给自己充值，那么userid和targetid相同）
                'channelid' => $channelid, //充值渠道ID
                'sellerid' => $sellerid,  //运营商ID 或者游戏厂家ID 或者 银行卡ID
                'rechargetype' => $rechargetype, //充值类型 0 充值卡 1游戏卡 2 ATM DEBIT 3 VISA MATER 4  SMS+ 5 INTERNET BANK 6 AGENT
                'devicetype' => $devicetype,
                'type' => 0,   //充值类型\r\n0：用户给自己充值\r\n1：代理给用户充值\r\n2：普通用户给其他人充值
                'orderno' => $transId,
                'amount' => $amount,
                'localunit' =>'VND',
                'showamount' => $showamount,
                'rechargetime' => date('Y-m-d H:i:s'),
                'status' => 1,  //充值状态\r\n0：失败\r\n1：成功\r\n2：处理中
                'distributeid' => $distributeid
            );
            $dbRechargedetail->add($insertReDet);

            //首次充值赠送10%秀币
            if(!$rechrecord){
                $insertReDisc = array(
                    'userid' =>$userid,
                    'targetid' =>$userid,
                    'channelid' =>$channelid,
                    'sellerid' =>$sellerid,
                    'rechargetype' =>$rechargetype,
                    'devicetype' =>$devicetype,
                    'type' =>0,
                    'orderno' =>$transId,
                    'amount' =>$amount,
                    'localunit' =>'VND',
                    'showamount' =>$showamount*0.1,
                    'rechargetime' =>date('Y-m-d H:i:s'),
                    'status' => 1,
                    'ispresent'=> 1
                );
                $dbRechargedetail->add($insertReDisc);

                //首次充值，赠送VIP座驾
                $this->rechargeAcitivity($userid);
                $showamount = $showamount*1.1;
            }

            //更新用户余额
            $updatBalarr = array(
                'balance' => array('exp', 'balance+' . $showamount),
                'point' => array('exp', 'point+' . $amount),
                'totalrecharge' => array('exp', 'totalrecharge+' . $amount)
            );
            M('Balance')->where(array('userid' =>$userid))->save($updatBalarr);

            //获取用户余额
            $balance = M('Balance')->where(array('userid' => $inputParams['userid']))->getField('balance');
            if (!$balance) {
                $balance = 0;
            }

            //返回结果
            $data['status'] = 200;
            $data['message'] = lan('200', 'Api', $this->lanPackage);
            $data['balance'] = $balance;
            return $data;
        }else{
            //充值失败，插入充值记录
            $insertReDet = array(
                'userid' => $userid,
                'targetid' => $userid,  //充值目标用户id（给这个用户充值，如果是给自己充值，那么userid和targetid相同）
                'channelid' => $channelid, //充值渠道ID
                'sellerid' => $sellerid,  //运营商ID 或者游戏厂家ID 或者 银行卡ID
                'rechargetype' => $rechargetype, //充值类型 0 充值卡 1游戏卡 2 ATM DEBIT 3 VISA MATER 4  SMS+ 5 INTERNET BANK 6 AGENT
                'devicetype' => $devicetype,
                'type' => 0,   //充值类型\r\n0：用户给自己充值\r\n1：代理给用户充值\r\n2：普通用户给其他人充值
                'orderno' => $transId,
                'amount' => $amount,
                'showamount' => $showamount,
                'rechargetime' => date('Y-m-d H:i:s'),
                'status' => 0,  //充值状态\r\n0：失败\r\n1：成功\r\n2：处理中
                'content' => $decode_cardCharging['description'],
                'distributeid' => $distributeid
            );
            $dbRechargedetail->add($insertReDet);

            $data['status'] = 404002;
            $data['message'] = $description;
            return $data;
        }
    }

    /**
     * 通过 LocalBank 充值
     * @param userid：当前登录用户ID
     * @param amount：localmoney，本地货币金额
     * @param showamount：rechargeamount，充值秀币金额
     * @param channelid：充值渠道ID
     * @param devicetype：设备类型 0 安卓 1 iOS 2PC
     * @param rechargetype：充值类型 0 充值卡 1游戏卡 2 ATM DEBIT 3 VISA MATER 4 SMS+ 5 INTERNET BANK 6 AGENT 7 Applestore 8 Paypal
     * @param distributeid: 应用商店渠道
     */
    public function rechargeByBank($inputParams){
        $userid = $inputParams['userid'];
        $amount = $inputParams['amount'];
        $showamount = $inputParams['showamount'];
        $channelid = $inputParams['channelid'];
        $rechargetype = $inputParams['rechargetype'];
        $devicetype = $inputParams['devicetype'];
        $distributeid = (int)$inputParams['distributeid'];

        //定义参数及回调地址
        $access_key = '6cj87xppb6ql4grs8g27'; //require your access key from 1pay
        $secret = 'dhsirq1kt34af5vmp1t6i111jfugn0d0'; //require your secret key from 1pay
        $return_url = "http://www.waashow.vn/Rechargecenter/rechargeByBankResult";

        //定义充值参数
        $command = 'request_transaction';
        $order_id = $userid.",".$showamount.",".$channelid.",".$rechargetype.",".$devicetype.",".time().",".$distributeid;  //$_POST['order_id'];
        $order_info = $userid . " nap by bank at waashow";  // $_POST['order_info'];

        //调用充值接口
        $json_data = "access_key=".$access_key."&amount=".$amount."&command=".$command."&order_id=".$order_id."&order_info=".$order_info."&return_url=".$return_url;
        $signature = hash_hmac("sha256", $json_data, $secret);
        $json_data .= "&signature=" . $signature;
        $json_bankCharging = $this->execPostRequest('http://api.1pay.vn/bank-charging/service', $json_data);
        error_log("bank0=" . $json_data);
        error_log("bank1=" . $json_bankCharging);
        //Ex: {"pay_url":"http://api.1pay.vn/bank-charging/sml/nd/order?token=LuNIFOeClp9d8SI7XWNG7O%2BvM8GsLAO%2BAHWJVsaF0%3D", "status":"init", "trans_ref":"16aa72d82f1940144b533e788a6bcb6"}
        $decode_bankCharging = json_decode($json_bankCharging,true);
        $pay_url = $decode_bankCharging["pay_url"];

        //返回充值的URL，安卓端执行充值
        $data['status'] = 200;
        $data['message'] = lan('200', 'Api', $this->lanPackage);
        $data['payurl'] = $pay_url;
        return $data;
    }

    /**
     * 通过 VISA 充值
     * @param userid：当前登录用户ID
     * @param amount：localmoney，本地货币金额
     * @param showamount：rechargeamount，充值秀币金额
     * @param channelid：充值渠道ID
     * @param sellerid：运营商ID 或者游戏厂家ID 或者 银行卡ID
     * @param devicetype：设备类型 0 安卓 1 iOS 2PC
     * @param rechargetype：充值类型 0 充值卡 1游戏卡 2 ATM DEBIT 3 VISA MATER 4 SMS+ 5 INTERNET BANK 6 AGENT 7 Applestore 8 Paypal
     *
     * 参数说明：
        access_key	representing the product of merchant which is declared in 1Pay system
        order_id	The bill code exclusively represents the transaction (less than 50 characters)
        order_info	Describing the invoice
        amount	The amount of money needs to be transacted
        return_url	URL address to which the transaction is redirected after doing payment, is built by merchant to get the result from 1Pay.
        1Pay's system send the request in form of HTTP GET.
        signature	a row of string, used to control the security:
        access_key=$access_key&order_id=$order_id&order_info=$order_info&amount=$a mount is hmac by the algorithm of SHA256
        //?userid=2&showamount=100&amount=10000&channelid=1&rechargetype=3&sellerid=7
     */
    public function rechargeByVisa($inputParams){
        $userid = $inputParams['userid'];
        $amount = $inputParams['amount'];
        $showamount = $inputParams['showamount'];
        $channelid = $inputParams['channelid'];
        $rechargetype = $inputParams['rechargetype'];
        $sellerid = $inputParams['sellerid'];
        $devicetype = $inputParams['devicetype'];
        $distributeid = (int)$inputParams['distributeid'];

        //定义参数及回调地址
        $access_key = '6cj87xppb6ql4grs8g27'; //require your access key from 1pay
        $secret = 'dhsirq1kt34af5vmp1t6i111jfugn0d0'; //require your secret key from 1pay
        $return_url = "http://www.waashow.vn/Rechargecenter/rechargeByVisaResult";

        //定义充值参数
        $command = 'request_transaction';
        $order_id = $userid.",".$showamount.",".$channelid.",".$rechargetype.",".$sellerid.",".$devicetype.",".time().",".$distributeid;  //$_POST['order_id'];
        $order_info = $userid . " nap by visa at waashow";  // $_POST['order_info'];

        //调用充值接口
        $json_data = "access_key=".$access_key."&amount=".$amount."&order_id=".$order_id."&order_info=".$order_info;
        $signature = hash_hmac("sha256", $json_data, $secret);
        $json_data.= "&return_url=".$return_url."&signature=".$signature;
        $json_bankCharging = $this->execPostRequest('http://visa.1pay.vn/visa-charging/api/handle/request', $json_data);
        error_log($json_data);
        error_log($json_bankCharging);
        //Ex: {"pay_url":"http://api.1pay.vn/bank-charging/sml/nd/order?token=LuNIFOeClp9d8SI7XWNG7O%2BvM8GsLAO%2BAHWJVsaF0%3D", "status":"init", "trans_ref":"16aa72d82f1940144b533e788a6bcb6"}
        $decode_bankCharging=json_decode($json_bankCharging,true);  // decode json
        $pay_url = $decode_bankCharging["pay_url"];

        //返回充值的URL，安卓端执行充值
        $data['status'] = 200;
        $data['message'] = lan('200', 'Api', $this->lanPackage);
        $data['payurl'] = $pay_url;
        return $data;
    }

    /**
     * 通过支付宝充值
     * @param userid：当前登录用户ID
     * @param amount：localmoney，本地货币金额
     * @param showamount：rechargeamount，充值秀币金额
     * @param channelid：充值渠道ID
     * @param sellerid：运营商ID 或者游戏厂家ID 或者 银行卡ID
     * @param devicetype：设备类型 0 安卓 1 iOS 2PC
     * @param rechargetype：充值类型 充值类型 0 充值卡 1游戏卡 2 ATM DEBIT 3 VISA MATER 4 SMS+ 5 INTERNET BANK 6 AGENT 7 Applestore 8 Paypal 9 Oceanpayment 10 支付宝
     */
    public function rechargeByAlipay($inputParams){
        $userid = $inputParams['userid'];
        $channelid = $inputParams['channelid'];
        $sellerid = $inputParams['sellerid'];
        $rechargetype = $inputParams['rechargetype'];
        $devicetype = $inputParams['devicetype'];
        $distributeid = $inputParams['distributeid'];
        $showamount = $inputParams['showamount'];
        $amount = $inputParams['amount'];

        //创建订单号
        $orderno = $this->createOrderNo($rechargetype,$userid);

        //支付宝充值，添加充值记录
        $insertReDet = array(
            'userid' => $userid,
            'targetid' => $userid,  //充值目标用户id（给这个用户充值，如果是给自己充值，那么userid和targetid相同）
            'channelid' => $channelid, //充值渠道ID
            'sellerid' => $sellerid,  //运营商ID 或者游戏厂家ID 或者 银行卡ID
            'rechargetype' => $rechargetype, //充值类型 充值类型 0 充值卡 1游戏卡 2 ATM DEBIT 3 VISA MATER 4 SMS+ 5 INTERNET BANK 6 AGENT 7 Applestore 8 Paypal 9 Oceanpayment 10 支付宝
            'devicetype' => $devicetype,
            'type' => 0,   //充值类型\r\n0：用户给自己充值\r\n1：代理给用户充值\r\n2：普通用户给其他人充值
            'orderno' => $orderno,
            'amount' => $amount,
            'localunit' =>'RMB',
            'showamount' => $showamount,
            'rechargetime' => date('Y-m-d H:i:s'),
            'status' => 2,  //充值状态：0失败 1成功 2处理中
            'distributeid' => $distributeid
        );
        $result = M('rechargedetail')->add($insertReDet);
        if($result === false){
            $data['status'] = -1;
            $data['message'] = lan('-1', 'Api', $this->lanPackage);
            return $data;
        }

        //生成要提交的参数
        require_once("alipay/alipay.config.php");
        require_once("alipay/lib/alipay_notify.class.php");
        require_once("alipay/lib/alipay_rsa.function.php");
        require_once("alipay/lib/alipay_core.function.php");
        $alipayData = array(
            'partner' => $alipay_config['partner'], //签约的支付宝账号对应的支付宝唯一用户号。以2088开头的16位纯数字组成。
            'seller_id' => $alipay_config['seller_id'],    //卖家支付宝账号（邮箱或手机号码格式）或其对应的支付宝唯一用户号（以2088开头的纯16位数字）
            'out_trade_no' => $orderno, //商户订单号
            'subject' => $showamount.'秀币',    //商品的标题/交易标题/订单标题/订单关键字等
            'body' => 'Waashow官方秀币充值',    //对一笔交易的具体描述信息。如果是多种商品，请将商品描述字符串累加传给body
            'total_fee' => $amount,    //该笔订单的资金总额，单位为RMB-Yuan。取值范围为[0.01，100000000.00]，精确到小数点后两位
            'notify_url' => $alipay_config['notify_url'],    //支付宝服务器主动通知商户网站里指定的页面http路径。
            'service' => 'mobile.securitypay.pay', //接口名称，固定值
            'payment_type' => '1',    //支付类型。默认值为：1（商品购买）
            '_input_charset' => 'utf-8', //商户网站使用的编码格式，固定为UTF-8。
            'sign_type' => 'RSA', //签名类型
        );

        date_default_timezone_set("PRC");
        //将要提交的数组所有元素，按照“参数='参数值'”的模式用“&”字符拼接成字符串。
        $prestr  = "";
        while (list ($key, $val) = each ($alipayData)) {
            $prestr .= $key.'="'.$val.'"&';
        }
        //去掉最后一个&字符
        $prestr = substr($prestr,0,count($prestr)-2);
        //如果存在转义字符，那么去掉转义
        if(get_magic_quotes_gpc()){
            $prestr = stripslashes($prestr);
        }
        //将待签名字符串使用私钥签名,且做urlencode. 注意：请求到支付宝只需要做一次urlencode.
        $rsa_sign = urlencode(rsaSign($prestr, $alipay_config['private_key']));
        //把签名得到的sign和签名类型sign_type拼接在待签名字符串后面。
        $payString = $prestr.'&sign='.'"'.$rsa_sign.'"'.'&sign_type='.'"'.$alipayData['sign_type'].'"';
        //保存签名之后字符串
        file_put_contents(__ROOT__."Data/alipay/alipay-".date('Ymd').".log","执行日期：".date("Y-m-d H:i:s")." ==> Sign_".$userid."\r\n".$payString."\n\n",FILE_APPEND);

        //返回支付宝充值拼接好的字符串，APP端执行充值
        $data['status'] = 200;
        $data['message'] = lan('200', 'Api', $this->lanPackage);
        $data['paystring'] = $payString;
        return $data;
    }

    /**
     * 通过paypal充值（安卓）
     * @param userid：当前登录用户ID
     * @param rechargedefid：充值秀币与当地货币记录id
     * @param rechargetype：充值类型 充值类型 0 充值卡 1游戏卡 2 ATM DEBIT 3 VISA MATER 4 SMS+ 5 INTERNET BANK 6 AGENT 7 Applestore 8 Paypal 9 Oceanpayment 10 支付宝     
     * @param devicetype：设备类型 0 安卓 1 iOS 2PC
     * @param distributeid：应用商店渠道ID
     */
    public function rechargeByPaypal($inputParams){
        $userid = $inputParams['userid'];
        $rechargedefid = $inputParams['rechargedefid'];
        $rechargetype = $inputParams['rechargetype'];
        $devicetype = $inputParams['devicetype'];
        $distributeid = $inputParams['distributeid'];

        $map_de = array(
            'rechargedefid' => $rechargedefid,
            'lantype' => $this->lantype
        );            
        $rechargedefinition = M('rechargedefinition')->where($map_de)->find();
        if (!$rechargedefinition) {
            $data['status'] = -1;
            $data['message'] = lan('-1', 'Api', $this->lanPackage);
            return $data;
        }

        //创建订单号
        $orderno = $this->createOrderNo($rechargetype,$userid);

        //要提交的参数
        $cmd = '_xclick';  //"立即购买"按钮
        $custom = $userid.'_'.$devicetype.'_'.$distributeid;  //用户自定义域，不展现给买家，这里传userid、devicetype、distributeid
        $quantity = '1';  //物品数量
        $no_note = '0';
        $no_shipping = '1';  //不强制要求买家邮寄地址
        $currency_code = 'USD';  //货币种类
        $invoice = $orderno;  //订单编号
        $item_number = $rechargedefid;  //跟踪购买的传递变量
        $amount = $rechargedefinition['localmoney'];  //商品价格
        $item_name = $rechargedefinition['rechargeamount'];  //商品名字
        //是否用沙盒测试 true:沙盒 false:正式
        if (C('PAYPAL_USE_SANDBOX') == true) {
            $commit_url = 'https://www.sandbox.paypal.com/cgi-bin/webscr';  //测试的支付提交url
            $business = 'seller@xlingmao.com';  //测试的商家账户
            $notify_url = 'http://sr.waashow.cn/Rechargecenter/paypalResultipn';  //测试的即时通知url
            $return = 'http://sr.waashow.cn/Rechargecenter/paypalResult';  //测试的交易成功的返回页面
        } else {
            $commit_url = 'https://www.paypal.com/cgi-bin/webscr';  //正式的支付提交url
            $business = C('PAYPAL_ACCOUNT');  //收款的商家账户
            $notify_url = C('PAYPAL_NOTIFY_URL');  //正式的即时通知url
            $return = C('PAYPAL_RETURN');  //正式的交易成功的返回页面
        }                
        $url = $commit_url.'?item_number='.$item_number.'&amount='.$amount.
            '&custom='.$custom.'&cmd='.$cmd.'&business='.$business.
            '&item_name='.$item_name.' Xu&quantity='.$quantity.
            '&no_note='.$no_note.'&no_shipping='.$no_shipping.
            '&currency_code='.$currency_code.'&invoice='.$invoice.
            '&return='.$return.'&notify_url='.$notify_url;

        //返回支付宝充值拼接好的字符串，APP端执行充值
        $data['status'] = 200;
        $data['message'] = lan('200', 'Api', $this->lanPackage);
        $data['paystring'] = $url;
        return $data;
    }

    /**
     * 执行充值
     * @param userid：当前登录用户ID
     * @param targetid：充值目标用户id（给这个用户充值，如果是给自己充值，那么userid和targetid相同）
     * @param type：商家名称
     * @param orderno：订单号
     * @param rechargedefid：充值秀币与当地货币记录
     * @param sellerid：运营商ID 或者游戏厂家ID 或者 银行卡ID
     * @param status：充值状态 0：失败 1：成功 2：处理中
     * @param channelid：充值渠道ID
     * @param devicetype：设备类型 0 安卓 1 iOS 2PC
     * @param rechargetype：充值类型 0 充值卡 1游戏卡 2 ATM DEBIT 3 VISA MATER 4 SMS+ 5 INTERNET BANK 6 AGENT 7 Applestore 8 Paypal
     * @param deviceid：设备唯一号
     * @param requestid：请求序列id
     * @param applereceipt：用于到AppStore市场校验的秘钥
     */
    private function doRecharge($inputParams, $rechargeDef){
        //根据userid判断是不是首次充值
        $dbRechargedetail = M('Rechargedetail');
        $rechrecord = $dbRechargedetail->where(array('userid' =>$inputParams['userid']))->find();

        //插入充值记录
        $insertArr = array(
            'userid' => $inputParams['userid'],
            'targetid' => $inputParams['targetid'],
            'channelid' => $inputParams['channelid'],
            'sellerid' => $inputParams['sellerid'],
            'rechargetype' => $inputParams['rechargetype'],
            'devicetype' => $inputParams['devicetype'],
            'type' => $inputParams['type'],
            'orderno' => $inputParams['orderno'],
            'amount' => $rechargeDef['localmoney'],
            'localunit' => $rechargeDef['localunit'],
            'showamount' => $rechargeDef['rechargeamount'],
            'rechargetime' => date('Y-m-d H:i:s'),
            'status' => $inputParams['status'],
        );
        $dbRechargedetail->add($insertArr);

        //首次充值赠送10%秀币
        $showamount = $rechargeDef['rechargeamount'];
        if(!$rechrecord){
            //插入赠送记录
            $insertReDisc = array(
                'userid' => $inputParams['userid'],
                'targetid' => $inputParams['targetid'],
                'channelid' => $inputParams['channelid'],
                'sellerid' => $inputParams['sellerid'],
                'rechargetype' => $inputParams['rechargetype'],
                'devicetype' => $inputParams['devicetype'],
                'type' => $inputParams['type'],
                'orderno' => $inputParams['orderno'],
                'amount' => $rechargeDef['localmoney'],
                'localunit' => $rechargeDef['localunit'],
                'showamount' => $showamount * 0.1,
                'rechargetime' => date('Y-m-d H:i:s'),
                'status' => $inputParams['status'],
                'ispresent'=> 1
            );
            $dbRechargedetail->add($insertReDisc);
            //充值记录写入日志
            error_log("ios_recharge=" . $dbRechargedetail->getLastSql());

            //充值活动，赠送VIP座驾
            $this->rechargeAcitivity($inputParams['userid']);
            $showamount = $showamount * 1.1;
        }

        //更新用户余额
        $balance = array(
            'balance' => array('exp', 'balance+' . $showamount),
            'point' => array('exp', 'point+' . $rechargeDef['localmoney']),
            'totalrecharge' => array('exp', 'totalrecharge+' . $rechargeDef['localmoney']),
        );
        M('Balance')->where(array('userid' => $inputParams['userid']))->save($balance);
    }

    /**
     * 充值活动 送7天VIP 送7天自行车座驾
     * @param $userid：用户ID
     */
    private function rechargeAcitivity($userid){
        //赠送7天高级VIP
        $vipdef = M('Vipdefinition')->where(array('vipid'=>1, 'lantype'=>$this->lantype))->find();
        $hasViprecord = D('Viprecord', 'Modapi')->getViprecordByUseridAndVipid($userid, $vipdef['vipid']);
        if($hasViprecord){
            $viprecord['effectivetime'] = $hasViprecord['expiretime'];
            $viprecord['expiretime'] = date("Y-m-d H:i:s",strtotime('+7 days',strtotime($hasViprecord['expiretime'])));
        }else{
            $viprecord['effectivetime'] = date('Y-m-d H:i:s');
            $viprecord['expiretime'] = date("Y-m-d H:i:s",strtotime('+7 days', time()));
        }
        $viprecord['userid'] = $userid;
        $viprecord['vipid'] = $vipdef['vipid'];
        $viprecord['vipname'] = $vipdef['vipname'];
        $viprecord['pcsmallvippic'] = $vipdef['pcsmallviplogo'];
        $viprecord['appsmallvippic'] = $vipdef['appsmallviplogo'];
        $viprecord['spendmoney'] = 0;
        $viprecord['ispresent'] = 1;
        M('Viprecord')->add($viprecord);

        //赠送7天自行车座驾
        $commodity =  M('Commodity')->where(array('commodityid'=>14, 'lantype'=>$this->lantype))->find();
        $hasEquipment = D('Equipment', 'Modapi')->getEquipmentByUseridAndComid($userid, $commodity['commodityid']);
        if ($hasEquipment){
            $equipment['isused'] = $hasEquipment['isused'];
            $equipment['effectivetime'] = $hasEquipment['expiretime'];
            $equipment['expiretime'] = date("Y-m-d H:i:s",strtotime('+7 days',strtotime($hasEquipment['expiretime'])));
        }else{
            //查看用户是否有未过期的正在使用的座驾
            $equipment_isused = D('Equipment', 'Modapi')->getMyEquipmentsByCon(array('userid' => $userid));
            if($equipment_isused){
                $equipment['isused'] = 0;
            }else{
                //更新所有失效的座驾为未使用
                $oldEquipment['isused'] = 0;
                $oldEquipment['operatetime'] = date('Y-m-d H:i:s');
                $oldEquipmentCond = array(
                    'userid' => $userid,
                    'isused' => 1
                );
                M('Equipment')->where($oldEquipmentCond)->save($oldEquipment);
                //设置赠送的座驾为使用
                $equipment['isused'] = 1;
            }
            $equipment['effectivetime'] = date('Y-m-d H:i:s');
            $equipment['expiretime'] = date("Y-m-d H:i:s", strtotime('+7 days', time()));
        }
        $equipment['userid'] = $userid;
        $equipment['commodityid'] = $commodity['commodityid'];
        $equipment['commodityname'] = $commodity['commodityname'];
        $equipment['commodityflashid'] = $commodity['commodityflashid'];
        $equipment['pcbigpic'] = $commodity['pcbigpic'];
        $equipment['pcsmallpic'] = $commodity['pcsmallpic'];
        $equipment['appbigpic'] = $commodity['appbigpic'];
        $equipment['appsmallpic'] = $commodity['appsmallpic'];
        $equipment['commodityswf'] = $commodity['commodityswf'];
        $equipment['spendmoney'] = 0;
        $equipment['ispresent'] = 1;
        $equipment['operatetime'] = date('Y-m-d H:i:s');
        M('Equipment')->add($equipment);
    }

    /**
     * iOS充值，构造curl请求
     */
    private function http_post_data($url, $jsonData){
        $curl_handle=curl_init();
        curl_setopt($curl_handle,CURLOPT_URL, $url);
        curl_setopt($curl_handle,CURLOPT_RETURNTRANSFER, true);
        curl_setopt($curl_handle,CURLOPT_HEADER, 0);
        curl_setopt($curl_handle,CURLOPT_POST, true);
        curl_setopt($curl_handle,CURLOPT_POSTFIELDS, $jsonData);
        curl_setopt($curl_handle,CURLOPT_SSL_VERIFYHOST, 0);
        curl_setopt($curl_handle,CURLOPT_SSL_VERIFYPEER, 0);
        $response_json =curl_exec($curl_handle);
        $response =json_decode($response_json,true);
        curl_close($curl_handle);

        return $response;
    }

    /**
     * 安卓充值，构造curl请求
     */
    private function execPostRequest($url, $data){
        $ch = curl_init();

        curl_setopt($ch, CURLOPT_CONNECTTIMEOUT,0);
        curl_setopt($ch, CURLOPT_TIMEOUT, 30);
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_POST, 1);
        curl_setopt($ch, CURLOPT_POSTFIELDS, $data);
        curl_setopt($ch, CURLOPT_USERAGENT, "Mozilla/5.0");
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
        curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, 2);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
        $result = curl_exec($ch);

        curl_close($ch);
        return $result;
    }
}