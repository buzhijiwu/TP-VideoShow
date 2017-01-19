<?php
/**
 * 充值中心控制器
 */
namespace Home\Controller;
use Think\Model;
class RechargecenterController extends CommonController {

    public function _initialize()
    {
        parent::_initialize();

        $db_Siteconfig = D('Siteconfig');
        $siteconfig = $db_Siteconfig->field('ratio')->find();
        $this->assign('ratio', $siteconfig['ratio']);
        $db_Member = D('Member');
        $userInfo = $db_Member->getMemberInfoByUserId(session('userid'));
        $this->assign('userinfo', $userInfo);
    }
	/**
	 * 电话卡
	 */
    public function index()
    {
        $db_Rechargechannel = D('Rechargechannel');
        $callCardChannels = $db_Rechargechannel->getReChannelsByType(0, 2, $this->lan);
        $this->assign('callcardchannels', $callCardChannels);
        //dump($callCardChannels);
        $this->assign('menu', 5);
        $this->assign('rechargemenu', 1);
        $this->display();
    }

    public function rechbycallingcd()
    {
        header("Content-type:text/html;charset=utf-8");
        
        $devicetype = I('POST.devicetype', '2', 'trim');
        $userid = I('POST.userid', '-1', 'intval');
        $type = I('POST.sellername', '', 'trim');
        $pin = I('POST.pin', '', 'trim');
        $serial = I('POST.serial', '', 'trim');
        $channelid = I('POST.channelid', '-1', 'intval');
        $sellerid = I('POST.sellerid', '-1', 'intval');
        $rechargetype = I('POST.rechargetype', '-1', 'intval');
        
        if(empty($pin) || empty($serial)){
            $errorInfo = array(
                'status' => 0,
                'message' => lan('PARAMETER_ERROR', 'Home'),
            );
            echo json_encode($errorInfo);
            die;
        }
        
        $transRef = $pin . getRandomVerify(); //merchant's transaction reference
        $access_key = '6cj87xppb6ql4grs8g27'; //require your access key from 1pay
        $secret = 'dhsirq1kt34af5vmp1t6i111jfugn0d0'; //require your secret key from 1pay
        //$type = 'mobifone'; //viettel, mobifone, vinaphone, vietnamobile, gate, vcoin, zing
        //$pin = '925286840476';
        //$serial = '046261000001177';
        $data = "access_key=" . $access_key . "&pin=" . $pin . "&serial=" . $serial . "&transRef=" . $transRef . "&type=" . $type;
        $signature = hash_hmac("sha256", $data, $secret);
        $data.= "&signature=" . $signature;
        //Mobifone serial 046261000001177
        //pin 925286840476
        
        error_log(date('Y-m-d H:i:s')."=".$data);
        $json_cardCharging = $this->execPostRequest('https://api.1pay.vn/card-charging/v5/topup', $data);
        error_log(date('Y-m-d H:i:s')."=".$json_cardCharging);
        
        $decode_cardCharging=json_decode($json_cardCharging,true);  // decode json
        
        if (isset($decode_cardCharging)) {
            $refDetail = D('Rechargedetail');
            $description = $decode_cardCharging["description"];   // transaction description
            $status = $decode_cardCharging["status"];
            $amount = $decode_cardCharging["amount"];       // card's amount
            $transId = $decode_cardCharging["transId"];
            
            $resultdata['status'] = $status;
            $resultdata['message'] = $decode_cardCharging['description'];
            $ratio = 0.01;
            
            //error_log("2=".$amount);
            if($amount > 0){
                //$rechargeDef = D("Rechargedefinition")->getRechargeDefByAmount($devicetype,$amount,$this->lan);
                //error_log("3=".$status);
                if($status == "00"){
                    /* 'targetid' =>$userid,  //充值目标用户id（给这个用户充值，如果是给自己充值，那么userid和targetid相同）
                    'channelid' =>$channelid, //充值渠道ID
                    'sellerid' =>$sellerid,  //运营商ID 或者游戏厂家ID 或者 银行卡ID
                    'rechargetype' =>$rechargetype, //充值类型 0 充值卡 1游戏卡 2 ATM DEBIT 3 VISA MATER 4  SMS+ 5 INTERNET BANK 6 AGENT
                    'devicetype' =>$devicetype,
                    'type' =>0,   //充值类型\r\n0：用户给自己充值\r\n1：代理给用户充值\r\n2：普通用户给其他人充值
                    'status' => 1  //充值状态\r\n0：失败\r\n1：成功\r\n2：处理中 */
                    $rechrecord = $refDetail->where(array('targetid' =>$userid))->find();
                    
                    $insertReDet = array(
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
                        'showamount' =>$amount*$ratio,
                        'rechargetime' =>date('Y-m-d H:i:s'),
                        'status' => 1
                    );
                    
                    //error_log("11=".$amount);
                    $refDetail->add($insertReDet);
                    $reTotalAmount = $amount*$ratio;
                    
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
                            'showamount' =>$amount*$ratio*0.1,
                            'ispresent'=> 1,
                            'rechargetime' =>date('Y-m-d H:i:s'),
                            'status' => 1
                        );
                        
                        $refDetail->add($insertReDisc);
                        $this->rechargeAcitivity($userid);
                        $reTotalAmount = $amount*$ratio*1.1;
                    }
                    
                    //error_log("15=".$reTotalAmount);
                    $updatBalarr = array(
                        'balance' => array('exp', 'balance+' . $reTotalAmount),
                        'point' => array('exp', 'point+' . $amount),
                        'totalrecharge' => array('exp', 'totalrecharge+' . $amount)
                    );
                    D('Balance')->where(array('userid' =>$userid))->save($updatBalarr);
                    $this->querySetUserBalance($userid);
                    
                    $resultdata['status'] = 1;
                }else{
                    $insertReDet = array(
                        'userid' =>$userid,
                        'targetid' =>$userid,  //充值目标用户id（给这个用户充值，如果是给自己充值，那么userid和targetid相同）
                        'channelid' =>$channelid, //充值渠道ID
                        'sellerid' =>$sellerid,  //运营商ID 或者游戏厂家ID 或者 银行卡ID
                        'rechargetype' =>$rechargetype, //充值类型 0 充值卡 1游戏卡 2 ATM DEBIT 3 VISA MATER 4  SMS+ 5 INTERNET BANK 6 AGENT
                        'devicetype' =>$devicetype,
                        'type' =>0,   //充值类型\r\n0：用户给自己充值\r\n1：代理给用户充值\r\n2：普通用户给其他人充值
                        'orderno' =>$transId,
                        'amount' =>$amount,
                        'showamount' =>$amount*$ratio,
                        'rechargetime' =>date('Y-m-d H:i:s'),
                        'status' => 0,  //充值状态\r\n0：失败\r\n1：成功\r\n2：处理中
                        'content' => $decode_cardCharging['description']
                    );
                    //error_log("12=".$amount);
                    $refDetail->add($insertReDet);
                }
            }else{
	            $resultdata['status'] = 0;
	            //$resultdata['message'] = 'Card balance is zero.';
	        }
	        //error_log(json_encode($resultdata));
            echo json_encode($resultdata);
            // xử lý dữ liệu của merchant
            //echo "1".$description."=".$status."=".$amount."=".$transId;
        }
        else {
            // run query API's endpoint
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
        }
    }
    
    /**
     * 1Pay SMS+充值 MO流程
     */
    public function rechargeBy1PaySmsPlusMo()
    {
        $amountArr = array("5000","10000","15000","20000","30000","50000","100000");
        //var_dump($splitarr);
        //die;
        
        $arParams['access_key'] = $_GET['access_key'] ? $_GET['access_key'] : '';
        $arParams['amount'] = $_GET['amount'] ? $_GET['amount'] : '';
        $arParams['command_code'] = $_GET['command_code'] ? $_GET['command_code'] : '';
        $arParams['mo_message'] = $_GET['mo_message'] ? $_GET['mo_message'] : '';
        $arParams['msisdn'] = $_GET['msisdn'] ? $_GET['msisdn'] : '';
        $arParams['telco'] = $_GET['telco'] ? $_GET['telco'] : '';
        $arParams['signature'] = $_GET['signature'] ? $_GET['signature'] : '';
        
        $data = "access_key=" . $arParams['access_key'] . "&amount=" . $arParams['amount'] . "&command_code=" . $arParams['command_code'] . "&mo_message=" . $arParams['mo_message'] . "&msisdn=" . $arParams['msisdn'] . "&telco=" . $arParams['telco'];
        
        $secret = 'dhsirq1kt34af5vmp1t6i111jfugn0d0'; // product's secret key (get value from 1Pay product detail)
        $signature = hash_hmac("sha256", $data, $secret); // create signature to check
        $arResponse['type'] = 'text';
        
        //error_log("sms0=".$arParams['mo_message']);
        //error_log("sms0=".$arParams['signature']);
        //error_log("sms0=".$signature);
        //error_log("sms0=".$data);
        
        // kiem tra signature neu can
        if ($arParams['signature'] == $signature) {
            if(in_array($arParams['amount'], $amountArr)){
                $moMessageArr = split("[ ]+", $arParams['mo_message']);
                if(count($moMessageArr) == 3){
                    $userid = $moMessageArr[2];

                    $member = D('Member')->getMemberInfo(array('userid'=>$userid));
                    if($member){
                        $arResponse['status'] = 1;
                        $arResponse['sms'] = 'Hop le';
                    }else{
                        $arResponse['status'] = 0;
                        $arResponse['sms'] = 'Your username ' . $member['username'] .' is not exist,please check';
                    }
                }else{
                    $arResponse['status'] = 0;
                    $arResponse['sms'] = 'Your mo message it not right';
                }                
            }else{
                $arResponse['status'] = 0;
                $arResponse['sms'] = 'Your recharge amount is not right,Please recharge 5000 vnd,10000 vnd,15000 vnd,20000 vnd,30000 vnd,50000 vnd,100000 vnd';
            }
            // if sms content and amount and ... are ok. return success case            
        } else {
            // if not. return fail case
            $arResponse['status'] = 0;
            $arResponse['sms'] = 'Khong hop le';
        }
        // return json for 1pay system
        echo json_encode($arResponse);
    }
    
    /**
     * 1Pay SMS+充值 
     */
    public function rechargeBy1PaySmsplus()
    {
        $arParams['access_key'] = $_GET['access_key'] ? $_GET['access_key'] : '';
        $arParams['command_code'] = $_GET['command_code'] ? $_GET['command_code'] : '';
        $arParams['mo_message'] = $_GET['mo_message'] ? $_GET['mo_message'] : '';
        $arParams['msisdn'] = $_GET['msisdn'] ? $_GET['msisdn'] : '';
        $arParams['request_id'] = $_GET['request_id'] ? $_GET['request_id'] : '';
        $arParams['request_time'] = $_GET['request_time'] ? $_GET['request_time'] : '';
        $arParams['amount'] = $_GET['amount'] ? $_GET['amount'] : '';
        $arParams['signature'] = $_GET['signature'] ? $_GET['signature'] : '';
        $arParams['error_code'] = $_GET['error_code'] ? $_GET['error_code'] : '';
        $arParams['error_message'] = $_GET['error_message'] ? $_GET['error_message'] : '';
        $data = "access_key=" . $arParams['access_key'] . "&amount=" . $arParams['amount'] . "&command_code=" . $arParams['command_code'] . "&error_code=" . $arParams['error_code'] . "&error_message=" . $arParams['error_message'] . "&mo_message=" . $arParams['mo_message'] . "&msisdn=" . $arParams['msisdn'] . "&request_id=" . $arParams['request_id'] . "&request_time=" . $arParams['request_time'];
        $secret = 'dhsirq1kt34af5vmp1t6i111jfugn0d0'; // product's secret key (get value from 1Pay product detail)
        $signature = hash_hmac("sha256", $data, $secret); // create signature to check
        $arResponse['type'] = 'text';
        
        //error_log($arParams['mo_message']);
        //error_log($arParams['signature']);
        //error_log($signature);
        //error_log($data);
        
        // kiem tra signature neu can
        if ($arParams['signature'] == $signature) {
            $moMessageArr = split("[ ]+", $arParams['mo_message']);
            if(count($moMessageArr) == 3){
                $userid = $moMessageArr[2];
                $refDetail = D('Rechargedetail');
                $ratio = 0.007;
                $member = D('Member')->getMemberInfo(array('userid'=>$userid));
                if($member){
                    $repeatrecord = $refDetail->where(array('orderno' =>$arParams['request_id']))->find();
                    
                    if($repeatrecord){
                        $arResponse['status'] = $repeatrecord['status'];
                        $arResponse['sms'] = 'You send sms recharge request repeatly';
                    }else{
                        $reTotalAmount = $arParams['amount']*$ratio;
                        $rechrecord = $refDetail->where(array('targetid' =>$member['userid']))->find();
                        
                        $insertReDet = array(
                            'userid' =>$member['userid'],
                            'targetid' =>$member['userid'],  //充值目标用户id（给这个用户充值，如果是给自己充值，那么userid和targetid相同）
                            'channelid' =>1, //充值渠道ID 1 1PAY
                            'sellerid' =>-1,  //运营商ID 或者游戏厂家ID 或者 银行卡ID
                            'rechargetype' =>4, //充值类型 0 充值卡 1游戏卡 2 ATM DEBIT 3 VISA MATER 4  SMS+ 5 INTERNET BANK 6 AGENT
                            'devicetype' =>2,//设备类型 0 安卓  1 iOS  2PC
                            'type' =>0,   //充值类型\r\n0：用户给自己充值\r\n1：代理给用户充值\r\n2：普通用户给其他人充值
                            'orderno' =>$arParams['request_id'],
                            'amount' =>$arParams['amount'],
                            'localunit' =>'VND',
                            'showamount' =>$reTotalAmount,
                            'rechargetime' =>date('Y-m-d H:i:s'),
                            'status' => 1,  //充值状态\r\n0：失败\r\n1：成功\r\n2：处理中
                            'rechargemsisdn' => $arParams['msisdn']
                        );
                        
                        //error_log("11=".$amount);
                        $refDetail->add($insertReDet);
                        
                        if(!$rechrecord){
                            $insertReDisc = array(
                                'userid' =>$member['userid'],
                                'targetid' =>$member['userid'],  //充值目标用户id（给这个用户充值，如果是给自己充值，那么userid和targetid相同）
                                'channelid' =>1, //充值渠道ID 1 1PAY
                                'sellerid' =>-1,  //运营商ID 或者游戏厂家ID 或者 银行卡ID
                                'rechargetype' =>4, //充值类型 0 充值卡 1游戏卡 2 ATM DEBIT 3 VISA MATER 4  SMS+ 5 INTERNET BANK 6 AGENT
                                'devicetype' =>2,//设备类型 0 安卓  1 iOS  2PC
                                'type' =>0,   //充值类型\r\n0：用户给自己充值\r\n1：代理给用户充值\r\n2：普通用户给其他人充值
                                'orderno' =>$arParams['request_id'],
                                'amount' =>$arParams['amount'],
                                'localunit' =>'VND',
                                'showamount' =>$reTotalAmount*0.1,
                                'rechargetime' =>date('Y-m-d H:i:s'),
                                'status' => 1,  //充值状态\r\n0：失败\r\n1：成功\r\n2：处理中
                                'rechargemsisdn' => $arParams['msisdn'],
                                'ispresent'=> 1
                            );
                        
                            $refDetail->add($insertReDisc);
                            $this->rechargeAcitivity($member['userid']);
                            $reTotalAmount = $reTotalAmount*1.1;
                        }
                        
                        $updatBalarr = array(
                            'balance' => array('exp', 'balance+' . $reTotalAmount),
                            'point' => array('exp', 'point+' . $arParams['amount']),
                            'totalrecharge' => array('exp', 'totalrecharge+' . $arParams['amount'])
                        );
                        
                        D('Balance')->where(array('userid' =>$member['userid']))->save($updatBalarr);
                        $this->querySetUserBalance($member['userid']);
                        $arResponse['status'] = 1;
                        $arResponse['sms'] = 'Giao dich thanh cong '. $arParams['amount'] .' vnd. Lien he 0934867870 de biet them chi tiet';
                        //成功交易[金额]越南盾。联系[热线]了解更多详情
                    }
                }else{
                    $arResponse['status'] = 0;
                    $arResponse['sms'] = 'Your username ' . $member['username'] .' is not exist,please check';
                }
            }else{
                $arResponse['status'] = 0;
                $arResponse['sms'] = 'Your mo message it not right';
            }
            //error_log("sucess=" .$data);
        } else {
            // if not. return fail case
            $arResponse['status'] = 0;
            $arResponse['sms'] = 'Giao dich khong thanh cong. Lien he 0934867870 de biet them chi tiet.';
            //error_log("fail=" .$data);
        }
        // return json for 1pay system
        echo json_encode($arResponse);
    }
    
    /**
     * 通过LOCALBANK 充值
     */
    public function rechargeByBank(){
        $access_key = '6cj87xppb6ql4grs8g27'; //require your access key from 1pay
        $secret = 'dhsirq1kt34af5vmp1t6i111jfugn0d0'; //require your secret key from 1pay
        $return_url = "http://www.waashow.vn/Rechargecenter/rechargeByBankResult";
        
        
        $userid = I('GET.userid', '-1', 'intval');
        $amount = I('GET.amount', '-1', 'intval');
        $showamount = I('GET.showamount', '-1', 'intval');
        $channelid = I('GET.channelid', '-1', 'intval');
        $rechargetype = I('GET.rechargetype', '-1', 'intval');
        $devicetype = 2;//设备类型 0 安卓  1 iOS  2PC
        
        $command = 'request_transaction';
        //$amount = "20000";  // >10000 $_POST['amount']; 
        $order_id = $userid.",".$showamount.",".$channelid.",".$rechargetype.",".$devicetype.",".time();  //$_POST['order_id'];
        $order_info = $userid . " nap by bank at waashow";  // $_POST['order_info'];
        
        //var_dump($amount.",".$order_id);
        //die;
         
        $data = "access_key=".$access_key."&amount=".$amount."&command=".$command."&order_id=".$order_id."&order_info=".$order_info."&return_url=".$return_url;
        $signature = hash_hmac("sha256", $data, $secret);
        $data.= "&signature=".$signature;
        $json_bankCharging = $this->execPostRequest('http://api.1pay.vn/bank-charging/service', $data);
        error_log("bank=".$data);
        error_log("bank=".$json_bankCharging);
        //Ex: {"pay_url":"http://api.1pay.vn/bank-charging/sml/nd/order?token=LuNIFOeClp9d8SI7XWNG7O%2BvM8GsLAO%2BAHWJVsaF0%3D", "status":"init", "trans_ref":"16aa72d82f1940144b533e788a6bcb6"}
        $decode_bankCharging=json_decode($json_bankCharging,true);  // decode json
        $pay_url = $decode_bankCharging["pay_url"];
        header("Location: $pay_url");
    }
    
    public function rechargeByBankResult(){
        //$access_key=l6apnlfseia0ooa12gwp&amount=10000&card_name=Ng%C3%A2n+h%C3%A0ng+TMCP+Ngo%E1%BA%A1i+th%C6%B0%C6%A1ng+Vi%E1%BB%87t+Nam&card_type=VCB&order_id=001&order_info=test+dich+vu&order_type=ND&request_time=2014-12-30T17%3A50%3A11Z&response_code=00&response_message=Giao+dich+thanh+cong&response_time=2014-12-30T17%3A52%3A12Z&signature=eb7aef260a18c835582964e840d63f68b9f84d9704bac7b16c8ff7f1ac9bd0d8&trans_ref=44df289349c74a7d9690ad27ed217094&trans_status=finish
        
        $trans_ref = isset($_GET["trans_ref"]) ? $_GET["trans_ref"] : NULL;  //Code transaction
        $response_code = isset($_GET["response_code"]) ? $_GET["response_code"] : NULL;
        $amount = isset($_GET["amount"]) ? $_GET["amount"] : NULL;
        $card_name = isset($_GET["card_name"]) ? $_GET["card_name"] : NULL;	//Branch of Bank’s name which user implement transaction
        $card_type = isset($_GET["card_type"]) ? $_GET["card_type"] : NULL;	//Type of Bank card
        $order_id = isset($_GET["order_id"]) ? $_GET["order_id"] : NULL;
        $order_info = isset($_GET["order_info"]) ? $_GET["order_info"] : NULL;  //Describle invoice
        $order_type = isset($_GET["order_type"]) ? $_GET["order_type"] : NULL;  //Receive value: ND
        $request_time = isset($_GET["request_time"]) ? $_GET["request_time"] : NULL;  //Time start transaction in form iso, example: 2013-07-06T22:54:50Z
        $response_message = isset($_GET["response_message"]) ? $_GET["response_message"] : NULL;  //Descible invoice
        $response_time = isset($_GET["response_time"]) ? $_GET["response_time"] : NULL;  //Time complete transaction in form iso, example: 2013-07-06T22:54:50Z
        
        $orderidArr = explode(',',$order_id);
        $userid = $orderidArr[0];
        $showamount = $orderidArr[1];
        $channelid = $orderidArr[2];
        $rechargetype = $orderidArr[3];
        $devicetype = $orderidArr[4];
        $time = $orderidArr[5];
        $distributeid = $orderidArr[6] ? (int)$orderidArr[6] : 0;
        //trans_status	Describle status transaction

        //保存订单号
        $orderno = $userid.",".$showamount.",".$channelid.",".$rechargetype.",".$devicetype.",".$time;

        $access_key = '6cj87xppb6ql4grs8g27'; //require your access key from 1pay
        $secret = 'dhsirq1kt34af5vmp1t6i111jfugn0d0'; //require your secret key from 1pay
        $return_url = "http://www.waashow.vn/Rechargecenter/rechargeByBankResult"; // returl url
        
        $bankResponse['status'] = $response_code;
        $bankResponse['message'] = $response_message;
        error_log("bankresult=".$response_code);
        
        if($response_code == "00"){
            $command = "close_transaction";
        
            $data = "access_key=".$access_key."&command=".$command."&trans_ref=".$trans_ref;
            $signature = hash_hmac("sha256", $data, $secret);
            $data.= "&signature=" . $signature;
             
            $json_bankCharging = $this->execPostRequest('http://api.1pay.vn/bank-charging/service', $data);
            error_log("bankresult=".$data);
            error_log("bankresult=".$json_bankCharging);
            
            $decode_bankCharging=json_decode($json_bankCharging,true);  // decode json
            // Ex: {"amount":10000,"trans_status":"close","response_time": "2014-12-31T00:52:12Z","response_message":"Giao dịch thành công","response_code":"00","order_info":"test dich vu","order_id":"001","trans_ref":"44df289349c74a7d9690ad27ed217094", "request_time":"2014-12-31T00:50:11Z","order_type":"ND"}
             
            $response_message = $decode_bankCharging["response_message"];
            $response_code = $decode_bankCharging["response_code"];
            $amount = $decode_bankCharging["amount"];
            
            $bankResponse['status'] = $response_code;
            $bankResponse['message'] = $response_message;
            
            if($response_code == "00"){
                //echo $response_message."-".$amount;
                $refDetail = D('Rechargedetail');
                $ratio = 0.01;
                $member = D('Member')->getMemberInfo(array('userid'=>$userid));
                if($member){
                    $repeatrecord = $refDetail->where(array('orderno' =>$orderno))->find();
                    
                    if($repeatrecord){
                        $bankResponse['status'] = $repeatrecord['status'];
                        $bankResponse['message'] = 'You send bank recharge request repeatly';
                    }else{
                        $showamount = $amount*$ratio;
                        $insertReDet = array(
                            'userid' =>$userid,
                            'targetid' =>$userid,  //充值目标用户id（给这个用户充值，如果是给自己充值，那么userid和targetid相同）
                            'channelid' =>$channelid, //充值渠道ID 1 1PAY
                            'sellerid' =>-1,  //运营商ID 或者游戏厂家ID 或者 银行卡ID
                            'rechargetype' =>$rechargetype, //充值类型 0 充值卡 1游戏卡 2 ATM DEBIT 3 VISA MATER 4  SMS+ 5 INTERNET BANK 6 AGENT
                            'devicetype' => $devicetype,
                            'type' =>0,   //充值类型\r\n0：用户给自己充值\r\n1：代理给用户充值\r\n2：普通用户给其他人充值
                            'orderno' =>$orderno,
                            'amount' =>$amount,
                            'localunit' =>'VND',
                            'showamount' =>$showamount,
                            'rechargetime' =>date('Y-m-d H:i:s'),
                            'status' => 1,  //充值状态\r\n0：失败\r\n1：成功\r\n2：处理中
                            'distributeid' => $distributeid,
                        );
                        
                        $rechrecord = $refDetail->where(array('targetid' =>$userid))->find();
                        
                        //error_log("11=".$amount);
                        $refDetail->add($insertReDet);
                        
                        if(!$rechrecord){
                            $insertReDisc = array(
                                'userid' =>$userid,
                                'targetid' =>$userid,  //充值目标用户id（给这个用户充值，如果是给自己充值，那么userid和targetid相同）
                                'channelid' =>$channelid, //充值渠道ID 1 1PAY
                                'sellerid' =>-1,  //运营商ID 或者游戏厂家ID 或者 银行卡ID
                                'rechargetype' =>$rechargetype, //充值类型 0 充值卡 1游戏卡 2 ATM DEBIT 3 VISA MATER 4  SMS+ 5 INTERNET BANK 6 AGENT
                                'devicetype' => $devicetype,
                                'type' =>0,   //充值类型\r\n0：用户给自己充值\r\n1：代理给用户充值\r\n2：普通用户给其他人充值
                                'orderno' =>$orderno,
                                'amount' =>$amount,
                                'localunit' =>'VND',
                                'showamount' =>$showamount*0.1,
                                'rechargetime' =>date('Y-m-d H:i:s'),
                                'status' => 1,  //充值状态\r\n0：失败\r\n1：成功\r\n2：处理中
                                'ispresent'=> 1
                            );
                        
                            $refDetail->add($insertReDisc);
                            $this->rechargeAcitivity($member['userid']);
                            $showamount = $showamount*1.1;
                        }
                        
                        $updatBalarr = array(
                            'balance' => array('exp', 'balance+' . $showamount),
                            'point' => array('exp', 'point+' . $amount),
                            'totalrecharge' => array('exp', 'totalrecharge+' . $amount)
                        );
                        D('Balance')->where(array('userid' =>$userid))->save($updatBalarr);
                        
                        if($devicetype == "2"){
                            $this->querySetUserBalance($userid);
                        }
                    }
                    //成功交易[金额]越南盾。联系[热线]了解更多详情
                }else{
                    $bankResponse['status'] = 0;
                    $bankResponse['message'] = 'User is not exist,please check';
                }
            }
        }
        if($devicetype == "2"){
            redirect(U('Rechargecenter/savings_card'));
        }else{
            echo $bankResponse['message'];
        }
    }
    
    /**
     * 通过VISA 充值
     */
    public function rechargeByVisa(){
        /**
        access_key	representing the product of merchant which is declared in 1Pay system
        order_id	The bill code exclusively represents the transaction (less than 50 characters)
        order_info	Describing the invoice
        amount	The amount of money needs to be transacted
        return_url	URL address to which the transaction is redirected after doing payment, is built by merchant to get the result from 1Pay.
        1Pay's system send the request in form of HTTP GET.
        signature	a row of string, used to control the security:
        access_key=$access_key&order_id=$order_id&order_info=$order_info&amount=$a mount is hmac by the algorithm of SHA256
        */
        //?userid=2&showamount=100&amount=10000&channelid=1&rechargetype=3&sellerid=7
        
        $access_key = '6cj87xppb6ql4grs8g27'; //require your access key from 1pay
        $secret = 'dhsirq1kt34af5vmp1t6i111jfugn0d0'; //require your secret key from 1pay
        $return_url = "http://www.waashow.vn/Rechargecenter/rechargeByVisaResult";
        $userid = I('GET.userid', '-1', 'intval');
        $amount = I('GET.amount', '-1', 'intval');
        $showamount = I('GET.showamount', '-1', 'intval');
        $channelid = I('GET.channelid', '-1', 'intval');
        $rechargetype = I('GET.rechargetype', '-1', 'intval');
        $sellerid = I('GET.sellerid', '-1', 'intval');
        $devicetype = 2;
        $command = 'request_transaction';
        //$amount = "20000";  // >10000 $_POST['amount'];
        $order_id = $userid.",".$showamount.",".$channelid.",".$rechargetype.",".$sellerid.",".$devicetype.",".time();  //$_POST['order_id'];
        $order_info = $userid . " nap by visa at waashow";  // $_POST['order_info'];
    
        //var_dump($amount.",".$order_id);
        //die;
        $data = "access_key=".$access_key."&amount=".$amount."&order_id=".$order_id."&order_info=".$order_info;
        $signature = hash_hmac("sha256", $data, $secret);
        $data.= "&return_url=".$return_url."&signature=".$signature;
        //var_dump($data);
        //die;
        $json_bankCharging = $this->execPostRequest('http://visa.1pay.vn/visa-charging/api/handle/request', $data);
        //Ex: {"pay_url":"http://api.1pay.vn/bank-charging/sml/nd/order?token=LuNIFOeClp9d8SI7XWNG7O%2BvM8GsLAO%2BAHWJVsaF0%3D", "status":"init", "trans_ref":"16aa72d82f1940144b533e788a6bcb6"}
        $decode_bankCharging=json_decode($json_bankCharging,true);  // decode json
        $pay_url = $decode_bankCharging["pay_url"];
        //var_dump($pay_url);
        
        header("Location: $pay_url");
    }
    
    public function rechargeByVisaResult(){
        //$access_key=l6apnlfseia0ooa12gwp&amount=10000&card_name=Ng%C3%A2n+h%C3%A0ng+TMCP+Ngo%E1%BA%A1i+th%C6%B0%C6%A1ng+Vi%E1%BB%87t+Nam&card_type=VCB&order_id=001&order_info=test+dich+vu&order_type=ND&request_time=2014-12-30T17%3A50%3A11Z&response_code=00&response_message=Giao+dich+thanh+cong&response_time=2014-12-30T17%3A52%3A12Z&signature=eb7aef260a18c835582964e840d63f68b9f84d9704bac7b16c8ff7f1ac9bd0d8&trans_ref=44df289349c74a7d9690ad27ed217094&trans_status=finish
        $trans_ref = isset($_GET["trans_ref"]) ? $_GET["trans_ref"] : NULL;  //Code transaction
        $response_code = isset($_GET["response_code"]) ? $_GET["response_code"] : NULL;
        $amount = isset($_GET["amount"]) ? $_GET["amount"] : NULL;
        $card_name = isset($_GET["card_name"]) ? $_GET["card_name"] : NULL;	//Branch of Bank’s name which user implement transaction
        $card_type = isset($_GET["card_type"]) ? $_GET["card_type"] : NULL;	//Type of Bank card
        $order_id = isset($_GET["order_id"]) ? $_GET["order_id"] : NULL;
        $order_info = isset($_GET["order_info"]) ? $_GET["order_info"] : NULL;  //Describle invoice
        $order_type = isset($_GET["order_type"]) ? $_GET["order_type"] : NULL;  //Receive value: ND
        $request_time = isset($_GET["request_time"]) ? $_GET["request_time"] : NULL;  //Time start transaction in form iso, example: 2013-07-06T22:54:50Z
        $response_message = isset($_GET["response_message"]) ? $_GET["response_message"] : NULL;  //Descible invoice
        $response_time = isset($_GET["response_time"]) ? $_GET["response_time"] : NULL;  //Time complete transaction in form iso, example: 2013-07-06T22:54:50Z
    
        /* http://www.waashow.vn/Rechargecenter/rechargeByVisaResult?
        access_key=6cj87xppb6ql4grs8g27&amount=90000&order_id=1099%2C900%2C1%2C3%2C7%2C2&
        order_info=1099+nap+by+visa+at+waashow&order_type=QT&request_time=2016-04-20T17%3A12%3A39Z&response_code=00
        &response_message=Approved&response_time=2016-04-20T17%3A17%3A19Z
        &signature=843b678b64be4d71dd4981c56637697c40f0ba49f4254782604283ab1d6706be
        &trans_ref=c2bc7a52a44045c6b06a950ec8b10af3&trans_status=finish
        */
        
        //error_log("VisaR0=".$_SERVER["HTTP_REFERER"]);
        //error_log("VisaR1=".$_SERVER["REQUEST_URI"]);
           
        $orderidArr = explode(',',$order_id);
        $userid = $orderidArr[0];
        $showamount = $orderidArr[1];
        $channelid = $orderidArr[2];
        $rechargetype = $orderidArr[3];
        $sellerid = $orderidArr[4];
        $devicetype = $orderidArr[5];
        $time = $orderidArr[6];
        $distributeid = $orderidArr[7] ? (int)$orderidArr[7] : 0;
        //trans_status	Describle status transaction

        //保存订单号
        $orderno = $userid.",".$showamount.",".$channelid.",".$rechargetype.",".$sellerid.",".$devicetype.",".$time;
    
        $bankResponse['status'] = $response_code;
        $bankResponse['message'] = $response_message;
        if($response_code == "00"){
                $refDetail = D('Rechargedetail');
                $ratio = 0.01;
                $member = D('Member')->getMemberInfo(array('userid'=>$userid));
                if($member){
                    $repeatrecord = $refDetail->where(array('orderno' =>$orderno))->find();
                    
                    if($repeatrecord){
                        $bankResponse['status'] = $repeatrecord['status'];
                        $bankResponse['message'] = 'You send bank recharge request repeatly';
                    }else{
                        $showamount = $amount*$ratio;
                        $insertReDet = array(
                            'userid' =>$userid,
                            'targetid' =>$userid,  //充值目标用户id（给这个用户充值，如果是给自己充值，那么userid和targetid相同）
                            'channelid' =>$channelid, //充值渠道ID 1 1PAY
                            'sellerid' =>$sellerid,  //运营商ID 或者游戏厂家ID 或者 银行卡ID
                            'rechargetype' =>$rechargetype, //充值类型 0 充值卡 1游戏卡 2 ATM DEBIT 3 VISA MATER 4  SMS+ 5 INTERNET BANK 6 AGENT
                            'devicetype' => $devicetype,
                            'type' =>0,   //充值类型\r\n0：用户给自己充值\r\n1：代理给用户充值\r\n2：普通用户给其他人充值
                            'orderno' =>$orderno,
                            'amount' =>$amount,
                            'localunit' =>'VND',
                            'showamount' =>$showamount,
                            'rechargetime' =>date('Y-m-d H:i:s'),
                            'status' => 1,  //充值状态\r\n0：失败\r\n1：成功\r\n2：处理中
                            'distributeid' => $distributeid,
                        );
                        
                        $rechrecord = $refDetail->where(array('targetid' =>$userid))->find();
                        //error_log("11=".$amount);
                        $refDetail->add($insertReDet);
                        
                        if(!$rechrecord){
                            $insertReDisc = array(
                                'userid' =>$userid,
                                'targetid' =>$userid,  //充值目标用户id（给这个用户充值，如果是给自己充值，那么userid和targetid相同）
                                'channelid' =>$channelid, //充值渠道ID 1 1PAY
                                'sellerid' =>-1,  //运营商ID 或者游戏厂家ID 或者 银行卡ID
                                'rechargetype' =>$rechargetype, //充值类型 0 充值卡 1游戏卡 2 ATM DEBIT 3 VISA MATER 4  SMS+ 5 INTERNET BANK 6 AGENT
                                'devicetype' => $devicetype,
                                'type' =>0,   //充值类型\r\n0：用户给自己充值\r\n1：代理给用户充值\r\n2：普通用户给其他人充值
                                'orderno' =>$orderno,
                                'amount' =>$amount,
                                'localunit' =>'VND',
                                'showamount' =>$showamount*0.1,
                                'rechargetime' =>date('Y-m-d H:i:s'),
                                'status' => 1,  //充值状态\r\n0：失败\r\n1：成功\r\n2：处理中
                                'ispresent'=> 1
                            );
                        
                            $refDetail->add($insertReDisc);
                            $this->rechargeAcitivity($member['userid']);
                            $showamount = $showamount*1.1;
                        }
                        
                        $updatBalarr = array(
                            'balance' => array('exp', 'balance+' . $showamount),
                            'point' => array('exp', 'point+' . $amount),
                            'totalrecharge' => array('exp', 'totalrecharge+' . $amount)
                        );
                        
                        D('Balance')->where(array('userid' =>$userid))->save($updatBalarr);
                        if($devicetype == "2"){
                            $this->querySetUserBalance($userid);
                        }
                    }
                    //redirect(U('Rechargecenter/credit_card'));
                    //成功交易[金额]越南盾。联系[热线]了解更多详情
                }else{
                    $bankResponse['status'] = 0;
                    $bankResponse['message'] = 'User is not exist,please check';
                }
        }
        if($devicetype == "2"){
            redirect(U('Rechargecenter/credit_card'));
        }else{
            echo $bankResponse['message']; //json_encode($bankResponse);
        }
    }
    
    /**
     * 
     * @param 用户ID $userid
     */
    private function querySetUserBalance($userid){
        $db_Balance = D('Balance');
        $userCond = array('userid' => $userid);
        $balanceInfo = $db_Balance->where($userCond)->find();
        if(!$balanceInfo){
            $balanceInfo['balance'] = 0;
        }
        
        session('balance', $balanceInfo['balance']);
        cookie('balance', $balanceInfo['balance'], 604800);
    }
    
    /**
     * 
     * @param CRUL请求连接 $url
     * @param CRUL请求参数 $data
     * @return mixed
     */
    private function execPostRequest($url, $data)
    {
        // open connection
        $ch = curl_init();

        // set the url, number of POST vars, POST data
        curl_setopt($ch, CURLOPT_CONNECTTIMEOUT,0);
        curl_setopt($ch, CURLOPT_TIMEOUT, 30);
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_POST, 1);
        curl_setopt($ch, CURLOPT_POSTFIELDS, $data);
        curl_setopt($ch, CURLOPT_USERAGENT, $_SERVER['HTTP_USER_AGENT']);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
        curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, 2);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);

        // execute post
        $result = curl_exec($ch);

        // close connection
        curl_close($ch);
        return $result;
    }
    
    public function rechargeAcitivity($userid){
        //赠送7天高级VIP
        $vipdef = M('Vipdefinition')->where(array('vipid'=>1, 'lantype'=>$this->lan))->find();
        $hasViprecord = D('Viprecord')->getViprecordByUseridAndVipid($userid, $vipdef['vipid']);
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
        $commodity =  D('Commodity')->where(array('commodityid'=>14, 'lantype'=>$this->lan))->find();
        $Equipment = D('Equipment');
        $hasEquipment = $Equipment->getEquipmentByUseridAndComid($userid, $commodity['commodityid']);
        if ($hasEquipment){
            $equipment['isused'] = $hasEquipment['isused'];
            $equipment['effectivetime'] = $hasEquipment['expiretime'];
            $equipment['expiretime'] = date("Y-m-d H:i:s",strtotime('+7 days',strtotime($hasEquipment['expiretime'])));
        }else{
            //查看用户是否有未过期的正在使用的座驾
            $equipment_isused = $Equipment->getMyEquipmentsByCon(array('userid' => $userid));
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
                $Equipment->where($oldEquipmentCond)->save($oldEquipment);
                //设置赠送的座驾为使用
                $equipment['isused'] = 1;
            }
            $equipment['effectivetime'] = date('Y-m-d H:i:s');
            $equipment['expiretime'] = date("Y-m-d H:i:s", strtotime('+7 days'));
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
        $Equipment->add($equipment);
    }

    /**
     * 游戏卡
     */
    public function game_card()
    {
        $db_Rechargechannel = D('Rechargechannel');
        $gameCardChannels = $db_Rechargechannel->getReChannelsByType(1, 2, $this->lan);
        $this->assign('gamecardchannels', $gameCardChannels);

        $this->assign('menu', 5);
        $this->assign('rechargemenu', 2);
        $this->display();
    }

    /**
     * 储蓄卡
     */
    public function savings_card()
    {
        $db_Rechargechannel = D('Rechargechannel');
        $saveCardChannels = $db_Rechargechannel->getReChannelsByType(2, 2, $this->lan);
//        $db_Rechargedefinition = D('Rechargedefinition');
//        $rechargedefs = $db_Rechargedefinition->getReDefByChannelAndType(1, 2, 2, $this->lan);
        $this->assign('savecardchannels', $saveCardChannels);
//        $this->assign('rechargedefs', $rechargedefs);
        $this->assign('menu', 5);
        $this->assign('rechargemenu', 3);
        $this->display();
    }


    /**
     * 信用卡
     */
    public function credit_card()
    {
        $db_Rechargechannel = D('Rechargechannel');
        $creditCardChannels = $db_Rechargechannel->getReChannelsByType(3, 2, $this->lan);
//        $db_Rechargedefinition = D('Rechargedefinition');
//        $rechargedefs = $db_Rechargedefinition->getAllReDefinitions(2, $this->lan);
        $this->assign('creditcardchannels', $creditCardChannels);

//        $this->assign('rechargedefs', $rechargedefs);
        $this->assign('menu', 5);
        $this->assign('rechargemenu', 4);
        $this->display();
    }

    /**
     * paypal
     */
    public function paypal(){
        //paypal充值提交验证
        if (I('get.item_number')) {
            $userid = session('userid') > 0 ? session('userid') : 0;
            $map_de = array(
                'rechargedefid' => I('get.item_number'),
                'lantype' => $this->lan
            );            
            $rechargedefinition = M('rechargedefinition')->where($map_de)->find();
            if ($rechargedefinition['localmoney'] == I('get.amount') && $userid > 0) {
                //生成订单号
                $type = 8; //代表paypal充值
                $orderno = $this->createOrderNo($type, $userid);
                $devicetype = 2;  //PC
                $distributeid = 0;
                //要提交的参数
                $cmd = '_xclick';  //"立即购买"按钮
                $custom = $userid.'_'.$devicetype.'_'.$distributeid;  //用户自定义域，不展现给买家，这里传userid、devicetype、distributeid
                $quantity = '1';  //物品数量
                $no_note = '0';
                $no_shipping = '1';  //不强制要求买家邮寄地址
                $currency_code = 'USD';  //货币种类
                $invoice = $orderno;  //订单编号
                $item_number = I('get.item_number');  //跟踪购买的传递变量
                $amount = I('get.amount');  //商品价格
                $item_name = I('get.item_name');  //商品名字
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
                header('Location: '.$url);die;  //跳转到paypal充值页面
            } else {
                $this->assign('payment_status', 'not_commit');

                $db_Rechargechannel = D('Rechargechannel');
                $paypalChannels = $db_Rechargechannel->getReChannelsByType(8, 2, $this->lan);
                $this->assign('paypalChannels', $paypalChannels);
        
                $this->assign('menu', 5);
                $this->assign('rechargemenu', 7);
                $this->display();                
            }
        } else {
            $db_Rechargechannel = D('Rechargechannel');
            $paypalChannels = $db_Rechargechannel->getReChannelsByType(8, 2, $this->lan);
            $this->assign('paypalChannels', $paypalChannels);
    
            $this->assign('menu', 5);
            $this->assign('rechargemenu', 7);
            $this->display();            
        }
    }

    public function paypalResult(){
        if (C('PAYPAL_USE_SANDBOX') == true) {
            $pp_hostname = "www.sandbox.paypal.com";
            $auth_token = "D7UU2JwqkNbHFEZ08HeC-pggBE_Y5wFPczB19BYESG87evETtHm0tp4PnZG";
        } else {
            $pp_hostname = "www.paypal.com";
            $auth_token = "98W9a4lnTRa4EWKtfKvIeGPCTz3km-KW12u11VQ5No4mcN6Cey9-nCQtUUu";
        }
        // $pp_hostname = "www.paypal.com";
        // $pp_hostname = "www.sandbox.paypal.com"; // Change to www.sandbox.paypal.com to test against sandbox
        // read the post from PayPal system and add 'cmd'
        $req = 'cmd=_notify-synch';
         
        $tx_token = $_GET['tx'];
        // $auth_token = "D7UU2JwqkNbHFEZ08HeC-pggBE_Y5wFPczB19BYESG87evETtHm0tp4PnZG";
        // $auth_token = "98W9a4lnTRa4EWKtfKvIeGPCTz3km-KW12u11VQ5No4mcN6Cey9-nCQtUUu";
        $req .= "&tx=$tx_token&at=$auth_token";
         
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, "https://$pp_hostname/cgi-bin/webscr");
        curl_setopt($ch, CURLOPT_POST, 1);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER,1);
        curl_setopt($ch, CURLOPT_POSTFIELDS, $req);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, 1);
        //set cacert.pem verisign certificate path in curl using 'CURLOPT_CAINFO' field here,
        //if your server does not bundled with default verisign certificates.
        curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, 2);
        curl_setopt($ch, CURLOPT_HTTPHEADER, array("Host: $pp_hostname"));
        $res = curl_exec($ch);
        curl_close($ch);
        if(!$res){
            header('Location: /Rechargecenter/paypal');
            exit;
        }else{
             // parse the data
            $lines = explode("\n", $res);
            $keyarray = array();
            if (strcmp ($lines[0], "SUCCESS") == 0) {
                for ($i=1; $i<count($lines);$i++){
                list($key,$val) = explode("=", $lines[$i]);
                $keyarray[urldecode($key)] = urldecode($val);
            }
            // check the payment_status is Completed
            // check that txn_id has not been previously processed
            // check that receiver_email is your Primary PayPal email
            // check that payment_amount/payment_currency are correct
            // process payment

            $customArr = explode('_', $keyarray['custom']);
            $devicetype = $customArr[1];

            if ($keyarray['payment_status'] == 'Completed') {
                if ($devicetype == '0') {
                    $map_de = array(
                        'rechargedefid' => $keyarray['item_number'],
                        'lantype' => 'en'
                    );
                    $rechargedefinition = M('rechargedefinition')->where($map_de)->find();
                    $showamount = $rechargedefinition['rechargeamount'];
                    $url = 'android://chargeSuccess/'.$showamount;
                    header('Location: '.$url);
                    exit;
                }                
                $this->assign('payment_status', 'Completed');
            }else{
                if ($devicetype == '0') {
                    $url = 'android://chargeFailed/'.lan('RECHARGE_FAIL', 'Home');
                    header('Location: '.$url);
                    exit;
                }                
                $this->assign('payment_status', 'failed');
            }
    
            $db_Rechargechannel = D('Rechargechannel');
            $paypalChannels = $db_Rechargechannel->getReChannelsByType(8, 2, $this->lan);
            $this->assign('paypalChannels', $paypalChannels);
    
            $this->assign('menu', 5);
            $this->assign('rechargemenu', 7);
            $this->display('paypal');

            // $firstname = $keyarray['first_name'];
            // $lastname = $keyarray['last_name'];
            // $itemname = $keyarray['item_name'];
            // $amount = $keyarray['payment_gross'];
             
            // echo ("<p><h3>Thank you for your purchase!</h3></p>");
             
            // echo ("<b>Payment Details</b><br>\n");
            // echo ("<li>Name: $firstname $lastname</li>\n");
            // echo ("<li>Item: $itemname</li>\n");
            // echo ("<li>Amount: $amount</li>\n");
            // echo ("");
            }
            else if (strcmp ($lines[0], "FAIL") == 0) {
                // log for manual investigation
            }
        }

        // $this->display();
    }

    public function paypalResultipn()
    {
        $logPath = __ROOT__."Data/pay/paypal-".date('Ymd').".log";
        file_put_contents($logPath,date("Y-m-d H:i:s")."\r\n".json_encode($_POST)."\r\n\n",FILE_APPEND);

        define("DEBUG", 1);

        // Set to 0 once you're ready to go live
        // define("USE_SANDBOX", 1);
        // define("USE_SANDBOX", 0);


        // define("LOG_FILE", "./ipn.log");

        $raw_post_data = file_get_contents('php://input');
        $raw_post_array = explode('&', $raw_post_data);
        $myPost = array();
        foreach ($raw_post_array as $keyval) {
            $keyval = explode ('=', $keyval);
            if (count($keyval) == 2)
                $myPost[$keyval[0]] = urldecode($keyval[1]);

            // if ($keyval[0] == 'item_number') {
            //     $myPost[$keyval[0]] = 11111;
            // }
        }
        // read the post from PayPal system and add 'cmd'
        $req = 'cmd=_notify-validate';
        if(function_exists('get_magic_quotes_gpc')) {
            $get_magic_quotes_exists = true;
        }
        foreach ($myPost as $key => $value) {
            if($get_magic_quotes_exists == true && get_magic_quotes_gpc() == 1) {
                $value = urlencode(stripslashes($value));
            } else {
                $value = urlencode($value);
            }
            $req .= "&$key=$value";
        }

        // Post IPN data back to PayPal to validate the IPN data is genuine
        // Without this step anyone can fake IPN data

        if (C('PAYPAL_USE_SANDBOX') == true) {
            $paypal_url = "https://www.sandbox.paypal.com/cgi-bin/webscr";
            $business = 'seller@xlingmao.com';  //测试的收款的商家账户
        } else {
            $paypal_url = "https://www.paypal.com/cgi-bin/webscr";
            $business = C('PAYPAL_ACCOUNT');  //收款的商家账户
        }

        $ch = curl_init($paypal_url);
        if ($ch == FALSE) {
            return FALSE;
        }

        curl_setopt($ch, CURLOPT_HTTP_VERSION, CURL_HTTP_VERSION_1_1);
        curl_setopt($ch, CURLOPT_POST, 1);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER,1);
        curl_setopt($ch, CURLOPT_POSTFIELDS, $req);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, 1);
        curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, 2);
        curl_setopt($ch, CURLOPT_FORBID_REUSE, 1);

        if(DEBUG == true) {
            curl_setopt($ch, CURLOPT_HEADER, 1);
            curl_setopt($ch, CURLINFO_HEADER_OUT, 1);
        }

        // CONFIG: Optional proxy configuration
        //curl_setopt($ch, CURLOPT_PROXY, $proxy);
        //curl_setopt($ch, CURLOPT_HTTPPROXYTUNNEL, 1);
        
        // Set TCP timeout to 30 seconds
        curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, 30);
        curl_setopt($ch, CURLOPT_HTTPHEADER, array('Connection: Close'));

        // CONFIG: Please download 'cacert.pem' from "http://curl.haxx.se/docs/caextract.html" and set the directory path
        // of the certificate as shown below. Ensure the file is readable by the webserver.
        // This is mandatory for some environments.
        
        //$cert = __DIR__ . "./cacert.pem";
        //curl_setopt($ch, CURLOPT_CAINFO, $cert);

        $res = curl_exec($ch);
        if (curl_errno($ch) != 0) // cURL error
        {
            if(DEBUG == true) {
                error_log(date('[Y-m-d H:i e] '). "Can't connect to PayPal to validate IPN message: " . curl_error($ch) . PHP_EOL);
            }
            curl_close($ch);
            exit;

        } else {
            // Log the entire HTTP response if debug is switched on.
            if(DEBUG == true) {
                error_log(date('[Y-m-d H:i e] '). "HTTP request of validation request:". curl_getinfo($ch, CURLINFO_HEADER_OUT) ." for IPN payload: $req" . PHP_EOL);
                error_log(date('[Y-m-d H:i e] '). "HTTP response of validation request: $res" . PHP_EOL);
            }
            curl_close($ch);
        }

        // Inspect IPN validation result and act accordingly

        // Split response headers and payload, a better way for strcmp
        $tokens = explode("\r\n\r\n", trim($res));
        $res = trim(end($tokens));

        if (strcmp ($res, "VERIFIED") == 0) {
            // check whether the payment_status is Completed
            // check that txn_id has not been previously processed
            // check that receiver_email is your PayPal email
            // check that payment_amount/payment_currency are correct
            // process payment and mark item as paid.

            // assign posted variables to local variables
            //$item_name = $_POST['item_name'];
            //$item_number = $_POST['item_number'];
            //$payment_status = $_POST['payment_status'];
            //$payment_amount = $_POST['mc_gross'];
            //$payment_currency = $_POST['mc_currency'];
            //$txn_id = $_POST['txn_id'];
            //$receiver_email = $_POST['receiver_email'];
            //$payer_email = $_POST['payer_email'];

            //验证商户账号是否正确
            if ($business != $_POST['receiver_email']) {
                error_log(date('[Y-m-d H:i e] '). "Verified IPN: receiver_email is error". PHP_EOL);
                error_log(date('[Y-m-d H:i e] '). "Verified IPN: $req ". PHP_EOL);
                exit;
            }

            $refDetail = D('Rechargedetail');
            $payment_status = $_POST['payment_status'];
            $orderid =  $_POST['txn_id']; //paypal交易号
            $orderid_no = $refDetail->where(array('orderid' =>$orderid))->find();

            $customArr = explode('_', $_POST['custom']);
            $userid = $customArr[0];
            $devicetype = $customArr[1];
            $distributeid = $customArr[2];

            $channelid = 5; //paypal充值
            $sellerid = 11;
            $rechargetype = 8; 
            $orderno = $_POST['invoice'];
            $amount = $_POST['mc_gross'];
            $item_number = $_POST['item_number'];
            $map_de = array(
                'rechargedefid' => $item_number,
                'lantype' => $this->lan
            );
            $rechargedefinition = M('rechargedefinition')->where($map_de)->find();
            $showamount = $rechargedefinition['rechargeamount'];                
            //回调通知确认后业务处理
            if ($payment_status == 'Completed' && !$orderid_no) {
                // $ratio = 0.01;
                $member = D('Member')->getMemberInfo(array('userid'=>$userid));
                if($member){
                    // $showamount = $amount*$ratio;
                    $insertReDet = array(
                        'userid' =>$userid,
                        'targetid' =>$userid,  //充值目标用户id（给这个用户充值，如果是给自己充值，那么userid和targetid相同）
                        'channelid' =>$channelid, //充值渠道ID 1 1PAY
                        'sellerid' =>$sellerid,  //运营商ID 或者游戏厂家ID 或者 银行卡ID
                        'rechargetype' =>$rechargetype, //充值类型 0 充值卡 1游戏卡 2 ATM DEBIT 3 VISA MATER 4  SMS+ 5 INTERNET BANK 6 AGENT
                        'devicetype' => $devicetype,
                        'type' =>0,   //充值类型\r\n0：用户给自己充值\r\n1：代理给用户充值\r\n2：普通用户给其他人充值
                        'orderno' =>$orderno,
                        'orderid' =>$orderid,  //第三方充值平台订单号
                        'amount' =>$amount,
                        'localunit' =>'USD',
                        'showamount' =>$showamount,
                        'rechargetime' =>date('Y-m-d H:i:s'),
                        'status' => 1,  //充值状态\r\n0：失败\r\n1：成功\r\n2：处理中
                        'distributeid' => $distributeid,  //应用商店渠道ID
                    );
                    
                    $rechrecord = $refDetail->where(array('targetid' =>$userid))->find();
                    //error_log("11=".$amount);
                    $refDetail->add($insertReDet);

                    //积分和总充值金额
                    $db_Siteconfig = M('Siteconfig');
                    $siteconfig = $db_Siteconfig->field('ratio')->find();
                    $totalrecharge_amount = $showamount/$siteconfig['ratio'];

                    if(!$rechrecord){
                        $insertReDisc = array(
                            'userid' =>$userid,
                            'targetid' =>$userid,  //充值目标用户id（给这个用户充值，如果是给自己充值，那么userid和targetid相同）
                            'channelid' =>$channelid, //充值渠道ID 1 1PAY
                            'sellerid' =>-1,  //运营商ID 或者游戏厂家ID 或者 银行卡ID
                            'rechargetype' =>$rechargetype, //充值类型 0 充值卡 1游戏卡 2 ATM DEBIT 3 VISA MATER 4  SMS+ 5 INTERNET BANK 6 AGENT
                            'devicetype' => $devicetype,
                            'type' =>0,   //充值类型\r\n0：用户给自己充值\r\n1：代理给用户充值\r\n2：普通用户给其他人充值
                            'orderno' =>$orderno,
                            'orderid' =>$orderid,  //第三方充值平台订单号
                            'amount' =>$amount,
                            'localunit' =>'USD',
                            'showamount' =>$showamount*0.1,
                            'rechargetime' =>date('Y-m-d H:i:s'),
                            'status' => 1,  //充值状态\r\n0：失败\r\n1：成功\r\n2：处理中
                            'ispresent'=> 1
                        );
                
                        $refDetail->add($insertReDisc);
                        $this->rechargeAcitivity($userid);
                        $showamount = $showamount*1.1;
                    }

                    $updatBalarr = array(
                        'balance' => array('exp', 'balance+' . $showamount),
                        'point' => array('exp', 'point+' . $totalrecharge_amount),
                        'totalrecharge' => array('exp', 'totalrecharge+' . $totalrecharge_amount)
                    );
                
                    D('Balance')->where(array('userid' =>$userid))->save($updatBalarr);
                    if($devicetype == "2"){
                        $this->querySetUserBalance($userid);
                    }
                    //redirect(U('Rechargecenter/credit_card'));
                    //成功交易[金额]越南盾。联系[热线]了解更多详情
                }else{
                    error_log(date('[Y-m-d H:i e] '). "Verified IPN: User is not exist,please check". PHP_EOL);
                }                  
            } else {
                $insertReDet = array(
                    'userid' =>$userid,
                    'targetid' =>$userid,  //充值目标用户id（给这个用户充值，如果是给自己充值，那么userid和targetid相同）
                    'channelid' =>$channelid, //充值渠道ID
                    'sellerid' =>$sellerid,  //运营商ID 或者游戏厂家ID 或者 银行卡ID
                    'rechargetype' =>$rechargetype, //充值类型 0 充值卡 1游戏卡 2 ATM DEBIT 3 VISA MATER 4  SMS+ 5 INTERNET BANK 6 AGENT
                    'devicetype' =>$devicetype,
                    'type' =>0,   //充值类型\r\n0：用户给自己充值\r\n1：代理给用户充值\r\n2：普通用户给其他人充值
                    'orderno' =>$orderno,
                    'amount' =>$amount,
                    'showamount' =>$showamount,
                    'rechargetime' =>date('Y-m-d H:i:s'),
                    'status' => 0,  //充值状态\r\n0：失败\r\n1：成功\r\n2：处理中
                    'content' => $payment_status,
                    'distributeid' => $distributeid,  //应用商店渠道ID
                );
                $refDetail->add($insertReDet);                
            }


            if(DEBUG == true) {
                error_log(date('[Y-m-d H:i e] '). "Verified IPN: $req ". PHP_EOL);
            }
        } else if (strcmp ($res, "INVALID") == 0) {
            // log for manual investigation
            // Add business logic here which deals with invalid IPN messages
            if(DEBUG == true) {
                error_log(date('[Y-m-d H:i e] '). "Invalid IPN: $req" . PHP_EOL);
            }
        }
    }    

    public function rechargeAgreement() {
        $this->assign('lantype',$this->lan);
        $this->display();
    }


    /**
     * sms充值界面展现
     */
    public function sms()
    {
        $db_Rechargechannel = D('Rechargechannel');
        $smsChannels = $db_Rechargechannel->getReChannelsByType(4, 2, $this->lan);
        $this->assign('smschannels', $smsChannels);
        //dump($smsChannels);
        $this->assign('menu', 5);
        $this->assign('rechargemenu', 6);
        $this->display();
    }

    public function internet_banking()
    {
        $this->assign('menu', 5);
        $this->assign('rechargemenu', 5);
        $this->display();
    }

    /**
     * 钱海
     */
    public function oceanpayment(){
        $db_Rechargechannel = D('Rechargechannel');
        $oceanpaymentChannels = $db_Rechargechannel->getReChannelsByType(9, 2, $this->lan);        
        //钱海充值提交
        if (IS_POST) {
            $rechargedefid = I('post.rechargedefid');
            $amount = I('post.amount');
            //验证金额
            $map_de = array(
                'rechargedefid' => $rechargedefid,
                'lantype' => $this->lan
            );
            $rechargedefinition = M('rechargedefinition')->where($map_de)->find(); 
            if (!$rechargedefinition['rechargeamount'] || $rechargedefinition['localmoney'] != $amount) {
                $this->assign('payment_status', '-1');
                $this->assign('oceanpaymentChannels', $oceanpaymentChannels);           
                $this->assign('menu', 5);
                $this->assign('rechargemenu', 8);
                $this->display();   
                exit;                
            }
                       
            $account = C('OCEAN_ACCOUNT');  //Oceanpayment账户
            $terminal = C('OCEAN_TERMINAL');  //终端号  
            $secureCode = C('OCEAN_SECURECODE');
            $order_currency = 'USD';  //交易币种      
           // $url = 'https://secure.oceanpayment.com/gateway/service/test';  //测试提交地址
            $url = 'https://secure.oceanpayment.com/gateway/service/pay';  //生产提交地址

            $type = 9; //代表钱海充值
            $userid = session('userid') > 0 ? session('userid') : 0;
            $order_number = $this->createOrderNo($type,$userid);

            //添加充值记录
            $channelid = 6; //钱海充值
            $sellerid = 12;
            $rechargetype = 9; 
            $insertReDet = array(
                'userid' => $userid,
                'targetid' => $userid,  //充值目标用户id（给这个用户充值，如果是给自己充值，那么userid和targetid相同）
                'channelid' => $channelid, //充值渠道ID
                'sellerid' => $sellerid,  //运营商ID 或者游戏厂家ID 或者 银行卡ID
                'rechargetype' => $rechargetype, //充值类型 0 充值卡 1游戏卡 2 ATM DEBIT 3 VISA MATER 4 SMS+ 5 INTERNET BANK 6 AGENT 7 Applestore 8 Paypal 9 Oceanpayment 10 Alipay 11 PayDollar
                'devicetype' => 2,  //pc
                'type' => 0,   //充值类型\r\n0：用户给自己充值\r\n1：代理给用户充值\r\n2：普通用户给其他人充值
                'orderno' => $order_number,
                'amount' => $amount,
                'localunit' =>'USD',
                'showamount' => $rechargedefinition['rechargeamount'],
                'rechargetime' => date('Y-m-d H:i:s'),
                'status' => 2,  //充值状态：0失败 1成功 2处理中
            );
            $result = M('rechargedetail')->add($insertReDet);
            if($result === false){
                $this->assign('payment_status', -1);
                $this->assign('oceanpaymentChannels', $oceanpaymentChannels);
                $this->assign('menu', 5);
                $this->assign('rechargemenu', 8);
                $this->display();
                exit;
            }
            
            //生成支付参数
            $order_amount = $amount;  //本地货币
            $showamount = $rechargedefinition['rechargeamount'];  //秀币
            $backUrl = 'http://'.$_SERVER['HTTP_HOST'].'/Rechargecenter/oceanpayment_syn';  //同步回调地址
            $noticeUrl = 'http://'.$_SERVER['HTTP_HOST'].'/Rechargecenter/oceanpayment_asy';  //异步回调地址
            $billing_firstName = 'N/A';  //消费者的名
            $billing_lastName = 'N/A';  //消费者的姓 
            $billing_email = $userid.'@waashow.com';  //消费者的邮箱 
            $billing_phone = 'N/A';  //消费者的电话
            $methods = 'Credit Card';  //支付方式
            $billing_country = 'N/A';  //消费者的账单国家
            $billing_city = 'N/A';  //消费者的城市
            $billing_address = 'N/A';  //消费者的详细地址
            $billing_zip = 'N/A';  //消费者的邮编
            $productSku = 'N/A';  //产品SKU
            $productName = $showamount.'Xu';  //产品名称
            $productNum = 1;  //产品数量
            $order_notes = $userid.'-'.$rechargedefid;  //订单备注

            //签名
            $signValue = hash("sha256",$account.$terminal.$backUrl.$order_number.$order_currency.$order_amount.
                $billing_firstName.$billing_lastName.$billing_email.$secureCode);

            echo "<form style='display:none;' id='upForm' name='upForm' method='post' action='".$url."'>
                <input type='hidden' name='account' value='".$account."' />
                <input type='hidden' name='terminal' value='".$terminal."' />
                <input type='hidden' name='order_number' value='".$order_number."' />
                <input type='hidden' name='order_currency' value='".$order_currency."' />
                <input type='hidden' name='order_amount' value='".$order_amount."' />
                <input type='hidden' name='signValue' value='".$signValue."' />
                <input type='hidden' name='backUrl' value='".$backUrl."' />
                <input type='hidden' name='noticeUrl' value='".$noticeUrl."' />
                <input type='hidden' name='billing_firstName' value='".$billing_firstName."' />
                <input type='hidden' name='billing_lastName' value='".$billing_lastName."' />
                <input type='hidden' name='billing_email' value='".$billing_email."' />
                <input type='hidden' name='billing_phone' value='".$billing_phone."' />
                <input type='hidden' name='methods' value='".$methods."' />
                <input type='hidden' name='billing_country' value='".$billing_country."' />
                <input type='hidden' name='billing_city' value='".$billing_city."' />
                <input type='hidden' name='billing_address' value='".$billing_address."' />
                <input type='hidden' name='billing_zip' value='".$billing_zip."' />
                <input type='hidden' name='productSku' value='".$productSku."' />
                <input type='hidden' name='productName' value='".$productName."' />
                <input type='hidden' name='productNum' value='".$productNum."' />
                <input type='hidden' name='order_notes' value='".$order_notes."' />                
                </form>
                <script type='text/javascript'>
                function load_submit(){
                    document.upForm.submit();
                }
                load_submit();
                </script>";

        } else {
            $this->assign('oceanpaymentChannels', $oceanpaymentChannels);
            $this->assign('menu', 5);
            $this->assign('rechargemenu', 8);
            $this->display();
        }
    }

    /**
     * 钱海同步回调
     */
    public function oceanpayment_syn(){
        if (IS_POST) {
            //渠道信息
            $db_Rechargechannel = D('Rechargechannel');
            $oceanpaymentChannels = $db_Rechargechannel->getReChannelsByType(9, 2, $this->lan);

            //返回参数
            $account = C('OCEAN_ACCOUNT');  //Oceanpayment账户
            $terminal = C('OCEAN_TERMINAL');  //终端号  
            $secureCode = C('OCEAN_SECURECODE');
            $order_number = I('post.order_number');
            $order_currency = I('post.order_currency');
            $order_amount = I('post.order_amount');
            $order_notes = I('post.order_notes');
            $card_number = I('post.card_number');
            $payment_id = I('post.payment_id');
            $payment_authType = I('post.payment_authType');
            $payment_status = I('post.payment_status');
            $payment_details = I('post.payment_details');
            $payment_risk = I('post.payment_risk');
            $payment_id = I('post.payment_id');  //支付 ID，Oceanpayment 的支付唯一单号
            //验证签名
            $sign_str = hash("sha256",$account.$terminal.$order_number.$order_currency.$order_amount.
                $order_notes.$card_number.$payment_id.$payment_authType.$payment_status.$payment_details.
                $payment_risk.$secureCode);
            $sign = strtoupper($sign_str);
            if ($sign != I('post.signValue')) {
                $this->assign('payment_status', '-1');

                $this->assign('oceanpaymentChannels', $oceanpaymentChannels);           
                $this->assign('menu', 5);
                $this->assign('rechargemenu', 8);
                $this->display('oceanpayment');   
                exit;              
            }

            $refDetail = M('Rechargedetail');
            $arr = explode("-", $order_notes);
            $userid = $arr[0];
            $rechargedefid = $arr[1];
            $channelid = 6; //钱海充值
            $sellerid = 12;
            $rechargetype = 9; 
            $devicetype = 2; //pc
            $orderno = $order_number;
            $amount = $order_amount;  
            //支付状态
            switch ($payment_status) {
                case '1':  //支付成功状态的业务处理
                    //开启事务
                    $tran = new Model();
                    $tran->startTrans();
        
                    //根据商户订单号获取充值记录
                    $whereRechargeDeatil = array(
                        'orderno' => $orderno,
                        'status' => 2
                    );
                    $rechargeDetail = $refDetail->where($whereRechargeDeatil)->find();
                    if(!$rechargeDetail){
                        $this->assign('payment_status', '-1');

                        $this->assign('oceanpaymentChannels', $oceanpaymentChannels);           
                        $this->assign('menu', 5);
                        $this->assign('rechargemenu', 8);
                        $this->display('oceanpayment');
                        exit;
                    }
                    $userid = $rechargeDetail['userid'];
                    $showamount = $rechargeDetail['showamount'];
        
                    //更新充值记录
                    $rechargeDatailData = array(
                        'orderid' => $payment_id,
                        'status' => 1
                    );
                    $updateRechargeDetail = $refDetail->where($whereRechargeDeatil)->save($rechargeDatailData);
                    if(!$updateRechargeDetail){
                        $tran->rollback();exit;
                    }

                    //积分和总充值金额
                    $db_Siteconfig = M('Siteconfig');
                    $siteconfig = $db_Siteconfig->field('ratio')->find();
                    $totalrecharge_amount = $showamount/$siteconfig['ratio'];

                    //判断是否首次充值
                    $whereOtherRechargeDetail = array(
                        'userid' => array('eq',$userid),
                        'status' => array('eq',1),
                        'orderno' => array('neq',$orderno)
                    );
                    $getOtherRechargeDetail = $refDetail->where($whereOtherRechargeDetail)->find();
                    if(!$getOtherRechargeDetail){
                        //首次充值赠送10%秀币，VIP及座驾
                        $insertReDisc = array(
                            'userid' => $userid,
                            'targetid' => $userid,  //充值目标用户id（给这个用户充值，如果是给自己充值，那么userid和targetid相同）
                            'channelid' => $channelid, //充值渠道ID 1 1PAY
                            'sellerid' => -1,  //运营商ID 或者游戏厂家ID 或者 银行卡ID
                            'rechargetype' => $rechargetype, //充值类型 0 充值卡 1游戏卡 2 ATM DEBIT 3 VISA MATER 4  SMS+ 5 INTERNET BANK 6 AGENT
                            'devicetype' => $devicetype,
                            'type' =>0,   //充值类型\r\n0：用户给自己充值\r\n1：代理给用户充值\r\n2：普通用户给其他人充值
                            'orderno' => $orderno,
                            'amount' => $amount,
                            'localunit' =>'USD',
                            'showamount' => $showamount*0.1,
                            'rechargetime' =>date('Y-m-d H:i:s'),
                            'status' => 1,  //充值状态\r\n0：失败\r\n1：成功\r\n2：处理中
                            'ispresent'=> 1
                        );
        
                        $refDetail->add($insertReDisc);
                        $this->rechargeAcitivity($userid);
                        $showamount = $showamount*1.1;
                    }
        
                    //更新用户余额
                    $updatBalarr = array(
                        'balance' => array('exp', 'balance+' . $showamount),
                        'point' => array('exp', 'point+' . $totalrecharge_amount),
                        'totalrecharge' => array('exp', 'totalrecharge+' . $totalrecharge_amount)
                    );
                    $updateBalanceResult = M('Balance')->where(array('userid' =>$userid))->save($updatBalarr);
                    if($updateBalanceResult === false){
                        $tran->rollback();exit;
                    }
                    $tran->commit();
                    $this->querySetUserBalance($userid);
        
                    $this->assign('payment_status', 'success');
                    break;
                default:
                    $this->assign('payment_status', 'failed');
                    break;
            }

            $this->assign('oceanpaymentChannels', $oceanpaymentChannels);           
            $this->assign('menu', 5);
            $this->assign('rechargemenu', 8);
            $this->display('oceanpayment');            
        }
    }

    /**
     * 钱海异步回调
     */
    public function oceanpayment_asy(){
        //接收xml并解析成数组
        $return_xml = $GLOBALS['HTTP_RAW_POST_DATA'];
        $xml =  (array)simplexml_load_string($return_xml, 'SimpleXMLElement', LIBXML_NOCDATA);

        $logPath = __ROOT__."Data/pay/oceanpayment-".date('Ymd').".log";
        file_put_contents($logPath,date("Y-m-d H:i:s")."\r\n".json_encode($xml)."\r\n\n",FILE_APPEND);

        //返回参数
        $account = C('OCEAN_ACCOUNT');  //Oceanpayment账户
        $terminal = C('OCEAN_TERMINAL');  //终端号  
        $secureCode = C('OCEAN_SECURECODE');
        $order_number = $xml['order_number'];
        $order_currency = $xml['order_currency'];
        $order_amount = $xml['order_amount'];
        $order_notes = $xml['order_notes'];
        $card_number = $xml['card_number'];
        $payment_id = $xml['payment_id'];
        $payment_authType = $xml['payment_authType'];
        $payment_status = $xml['payment_status'];
        $payment_details = $xml['payment_details'];
        $payment_risk = $xml['payment_risk'];
        $signValue = $xml['signValue'];
        //验证签名
        $sign_str = hash("sha256",$account.$terminal.$order_number.$order_currency.$order_amount.
            $order_notes.$card_number.$payment_id.$payment_authType.$payment_status.$payment_details.
            $payment_risk.$secureCode);
        $sign = strtoupper($sign_str);
        if ($sign != $signValue) {
            error_log(date('[Y-m-d H:i e] '). "Signature verification is not passed" . PHP_EOL);
            exit;              
        }

        $refDetail = M('Rechargedetail');
        $arr = explode("-", $order_notes);
        $userid = $arr[0];
        $rechargedefid = $arr[1];
        $channelid = 6; //钱海充值
        $sellerid = 12;
        $rechargetype = 9; 
        $devicetype = 2; //pc
        $orderno = $order_number;
        $amount = $order_amount;
        //支付状态
        switch ($payment_status) {
            case '1':  //支付成功状态的业务处理
                //开启事务
                $tran = new Model();
                $tran->startTrans();
        
                //根据商户订单号获取充值记录
                $whereRechargeDeatil = array(
                    'orderno' => $orderno,
                    'status' => 2
                );
                $rechargeDetail = $refDetail->where($whereRechargeDeatil)->find();
                if(!$rechargeDetail){
                    echo 'receive-ok';  //响应异步回调
                    exit;
                }
                $userid = $rechargeDetail['userid'];
                $showamount = $rechargeDetail['showamount'];
        
                //更新充值记录
                $rechargeDatailData = array(
                    'orderid' => $payment_id,
                    'status' => 1
                );
                $updateRechargeDetail = $refDetail->where($whereRechargeDeatil)->save($rechargeDatailData);
                if(!$updateRechargeDetail){
                    $tran->rollback();exit;
                }

                //积分和总充值金额
                $db_Siteconfig = M('Siteconfig');
                $siteconfig = $db_Siteconfig->field('ratio')->find();
                $totalrecharge_amount = $showamount/$siteconfig['ratio'];

                //判断是否首次充值
                $whereOtherRechargeDetail = array(
                    'userid' => array('eq',$userid),
                    'status' => array('eq',1),
                    'orderno' => array('neq',$orderno)
                );
                $getOtherRechargeDetail = $refDetail->where($whereOtherRechargeDetail)->find();
                if(!$getOtherRechargeDetail){
                    //首次充值赠送10%秀币，VIP及座驾
                    $insertReDisc = array(
                        'userid' => $userid,
                        'targetid' => $userid,  //充值目标用户id（给这个用户充值，如果是给自己充值，那么userid和targetid相同）
                        'channelid' => $channelid, //充值渠道ID 1 1PAY
                        'sellerid' => -1,  //运营商ID 或者游戏厂家ID 或者 银行卡ID
                        'rechargetype' => $rechargetype, //充值类型 0 充值卡 1游戏卡 2 ATM DEBIT 3 VISA MATER 4  SMS+ 5 INTERNET BANK 6 AGENT
                        'devicetype' => $devicetype,
                        'type' =>0,   //充值类型\r\n0：用户给自己充值\r\n1：代理给用户充值\r\n2：普通用户给其他人充值
                        'orderno' => $orderno,
                        'amount' => $amount,
                        'localunit' =>'USD',
                        'showamount' => $showamount*0.1,
                        'rechargetime' =>date('Y-m-d H:i:s'),
                        'status' => 1,  //充值状态\r\n0：失败\r\n1：成功\r\n2：处理中
                        'ispresent'=> 1
                    );
        
                    $refDetail->add($insertReDisc);
                    $this->rechargeAcitivity($userid);
                    $showamount = $showamount*1.1;
                }
        
                //更新用户余额
                $updatBalarr = array(
                    'balance' => array('exp', 'balance+' . $showamount),
                    'point' => array('exp', 'point+' . $totalrecharge_amount),
                    'totalrecharge' => array('exp', 'totalrecharge+' . $totalrecharge_amount)
                );
                $updateBalanceResult = M('Balance')->where(array('userid' =>$userid))->save($updatBalarr);
                if($updateBalanceResult === false){
                    $tran->rollback();exit;
                }
                $tran->commit();
                $this->querySetUserBalance($userid);

                echo 'receive-ok';  //响应异步回调
                break;
            default:
                echo 'receive-ok';  //响应异步回调
                break;
        }
    }

    //传款易支付
    public function paydollar(){
        $db_Rechargechannel = D('Rechargechannel');
        $paydollarChannels = $db_Rechargechannel->getReChannelsByType(11, 2, $this->lan);
        //传款易支付
        if (IS_POST) {
            $rechargedefid = I('post.rechargedefid');
            $amount = I('post.amount');
            $userid = session('userid') ? (int)session('userid') : 0;

            //验证金额
            $map_de = array(
                'rechargedefid' => $rechargedefid,
                'lantype' => $this->lan
            );
            $rechargedefinition = M('rechargedefinition')->where($map_de)->find();
            if ($userid == 0 || !$rechargedefinition['rechargeamount'] || $rechargedefinition['localmoney'] != $amount) {
                $this->assign('payment_status', -1);
                $this->assign('paydollarChannels', $paydollarChannels);
                $this->assign('menu', 5);
                $this->assign('rechargemenu', 9);
                $this->display();exit;
            }

            //添加充值记录
            $orderno = $this->createOrderNo(11,$userid);
            $insertReDet = array(
                'userid' => $userid,
                'targetid' => $userid,  //充值目标用户id（给这个用户充值，如果是给自己充值，那么userid和targetid相同）
                'channelid' => 8, //充值渠道ID
                'sellerid' => 14,  //运营商ID 或者游戏厂家ID 或者 银行卡ID
                'rechargetype' => 11, //充值类型 0 充值卡 1游戏卡 2 ATM DEBIT 3 VISA MATER 4 SMS+ 5 INTERNET BANK 6 AGENT 7 Applestore 8 Paypal 9 Oceanpayment 10 Alipay 11 PayDollar
                'devicetype' => 2,
                'type' => 0,   //充值类型\r\n0：用户给自己充值\r\n1：代理给用户充值\r\n2：普通用户给其他人充值
                'orderno' => $orderno,
                'amount' => $amount,
                'localunit' =>'RMB',
                'showamount' => $rechargedefinition['rechargeamount'],
                'rechargetime' => date('Y-m-d H:i:s'),
                'status' => 2,  //充值状态：0失败 1成功 2处理中
            );
            $result = M('rechargedetail')->add($insertReDet);
            if($result === false){
                $this->assign('payment_status', -1);
                $this->assign('paydollarChannels', $paydollarChannels);
                $this->assign('menu', 5);
                $this->assign('rechargemenu', 9);
                $this->display();exit;
            }

            //生成支付参数
            $merchantId = C('PAYDOLLAR_MERCHANTID');    //商户号
            $orderRef = $orderno;   //商户订单号
            $currCode = '156';  //支付货币的种类 “344”- HKD、“840”- USD、“702”- SGD、“156”- CNY（RMB）、“392”- JPY、“901”- TWD、“036”- AUD、“978”- EUR、“826”- GBP、“124”- CAD
            $paymentType = 'N'; //支付类型：“N” - 消费交易、“H” - 预授权交易
            $mpsMode = "NIL";   //多货币处理服务（MPS）模式：“NIL”或没有提供 - 关闭MPS（没有货币转换）、“SCP” - 开启MPS‘简单货币转换’、“DCC” - 开启MPS‘动态货币转换’、“MCP” - 开启MPS‘多货币计价’
            $payMethod = "ALL"; //支付方式：“ALL” - 所有有效支付方式、“CC” - 信用卡支付、“PPS” - PayDollar的PPS支付、“PAYPAL” - PayDollar的PayPal支付、“CHINAPAY” - PayDollar的China UnionPay支付、“ALIPAY” - PayDollar的ALIPAY支付、“TENPAY” - PayDollar的TENPAY支付、“99BILL” - PayDollar的99BILL支付
            $lang = "X";    //支付页面的 语言即 “C” - 繁体中文、“E” - 英语、“X” - 简体中文、“K” - 朝鲜语、“J” - 日语、“T” - 泰国语
            $successUrl = 'http://'.$_SERVER['HTTP_HOST'].'/Home/Rechargecenter/payDollarReturnUrl?orderno='.$orderno.'&status=success';  //支付成功同步回调
            $failUrl = 'http://'.$_SERVER['HTTP_HOST'].'/Home/Rechargecenter/payDollarReturnUrl?orderno='.$orderno.'&status=failed';  //支付失败同步回调
            $cancelUrl = 'http://'.$_SERVER['HTTP_HOST'].'/Home/Rechargecenter/payDollarReturnUrl?orderno='.$orderno.'&status=cancel';  //取消支付同步回调

            //生成签名数据字符串
            $secureHashSecret = C('PAYDOLLAR_SECRET');  //密钥
            $buffer = $merchantId . '|' . $orderRef . '|' . $currCode . '|' . $amount . '|' . $paymentType . '|' . $secureHashSecret;
            $secureHash = sha1($buffer);

            echo '<!DOCTYPE html PUBLIC "-//W3C//DTD HTML 4.01 Transitional//EN" "http://www.w3.org/TR/html4/loose.dtd">
                    <html>
                        <head>
                            <meta http-equiv="Content-Type" content="text/html; charset=UTF-8">
                            <title>Insert title here</title>
                        </head>
                        <body>
                        <form style="display:none;" id="payFormCcard" name="payFormCcard" method="post" action="https://www.paydollar.com/b2c2/eng/payment/payForm.jsp">
                            <input type="hidden" name="merchantId" value="'.$merchantId.'">
                            <input type="hidden" name="amount" value="'.$amount.'" >
                            <input type="hidden" name="orderRef" value="'.$orderRef.'">
                            <input type="hidden" name="currCode" value="'.$currCode.'" >
                            <input type="hidden" name="mpsMode" value="'.$mpsMode.'" >
                            <input type="hidden" name="successUrl" value="'.$successUrl.'">
                            <input type="hidden" name="failUrl" value="'.$failUrl.'">
                            <input type="hidden" name="cancelUrl" value="'.$cancelUrl.'">
                            <input type="hidden" name="payType" value="'.$paymentType.'">
                            <input type="hidden" name="lang" value="'.$lang.'">
                            <input type="hidden" name="payMethod" value="'.$payMethod.'">
                            <input type="hidden" name="secureHash" value="'.$secureHash.'">
                        </form>
                        <script type="text/javascript">
                            document.getElementById("payFormCcard").submit();
                        </script>
                        </body>
                    </html>';exit;
        }

        $this->assign('paydollarChannels', $paydollarChannels);
        $this->assign('menu', 5);
        $this->assign('rechargemenu', 9);
        $this->display();
    }

    //传款易支付，同步通知回调地址
    public function payDollarReturnUrl(){
        $status = I('get.status','');
        if($status == 'success'){   //同步回调成功，更新余额
            $this->querySetUserBalance(session('userid'));
        }
        $db_Rechargechannel = D('Rechargechannel');
        $paydollarChannels = $db_Rechargechannel->getReChannelsByType(11, 2, $this->lan);
        $this->assign('paydollarChannels', $paydollarChannels);
        $this->assign('payment_status', $status);
        $this->assign('menu', 5);
        $this->assign('rechargemenu', 9);
        $this->display('paydollar');
    }

    //传款易支付，异步通知回调地址
    public function payDollarNotifyUrl(){
        $logPath = __ROOT__."Data/pay/paydollar-".date('Ymd').".log";
        file_put_contents($logPath,date("Y-m-d H:i:s")."\r\n".json_encode($_POST)."\r\n\n",FILE_APPEND);

        $src = $_POST['src']; //返回银行主机状态码（次），详细请参考附录A
        $prc = $_POST['prc']; //返回银行主机状态码（主），详细请参考附录A
        $successcode = $_POST['successcode']; //0 - 成功，1 - 失败，其他 - 错误
        $ref = $_POST['Ref']; //商家的订单参考号
        $payRef = $_POST['PayRef']; //PayDollar的支付参考号
        $amt = $_POST['Amt']; //交易金额
        $cur = $_POST['Cur']; //交易货币种类
        $payerAuth = $_POST['payerAuth']; //付款人认证状态
        $secureHash = $_POST['secureHash']; //提取 PayDollar datafeed 中的 secure hash

        //生成需要验证的哈希码(Secure Hash)
        $secureHashSecret = C('PAYDOLLAR_SECRET');  //密钥
        $buffer = $src . '|' . $prc . '|' . $successcode . '|' . $ref . '|' . $payRef . '|' . $cur . '|' . $amt . '|' . $payerAuth . '|' . $secureHashSecret;
        $verifyData = sha1($buffer);
        if($verifyData != $secureHash){
            file_put_contents($logPath,date("Y-m-d H:i:s")."Verify Fail\r\n\n",FILE_APPEND);
            echo 'Verify Fail';exit;
        }

        if ($successcode == '0') {  //交易成功
            //开启事务
            $tran = new Model();
            $tran->startTrans();

            //根据商户订单号获取充值记录
            $whereRechargeDeatil = array(
                'orderno' => $ref,
                'status' => 2
            );
            $dbRechargeDetail = M('rechargedetail');
            $rechargeDetail = $dbRechargeDetail->where($whereRechargeDeatil)->find();
            if(!$rechargeDetail){
                exit;
            }
            $userid = $rechargeDetail['userid'];
            $showamount = $rechargeDetail['showamount'];
            //积分和总充值金额
            $db_Siteconfig = M('Siteconfig');
            $siteconfig = $db_Siteconfig->field('ratio')->find();
            $totalrecharge_amount = $showamount/$siteconfig['ratio'];

            //更新充值记录
            $rechargeDatailData = array(
                'orderid' => $payRef,
                'status' => 1
            );
            $updateRechargeDetail = $dbRechargeDetail->where($whereRechargeDeatil)->save($rechargeDatailData);
            if(!$updateRechargeDetail){
                $tran->rollback();exit;
            }

            //判断是否首次充值
            $whereOtherRechargeDetail = array(
                'userid' => array('eq',$userid),
                'status' => array('eq',1),
                'orderno' => array('neq',$ref)
            );
            $getOtherRechargeDetail = $dbRechargeDetail->where($whereOtherRechargeDetail)->find();
            if(!$getOtherRechargeDetail){
                //首次充值赠送10%秀币，VIP及座驾
                $insertReDisc = array(
                    'userid' => $userid,
                    'targetid' => $userid,  //充值目标用户id（给这个用户充值，如果是给自己充值，那么userid和targetid相同）
                    'channelid' => 8, //充值渠道ID 1 1PAY
                    'sellerid' => -1,  //运营商ID 或者游戏厂家ID 或者 银行卡ID
                    'rechargetype' => 11, //充值类型 0 充值卡 1游戏卡 2 ATM DEBIT 3 VISA MATER 4  SMS+ 5 INTERNET BANK 6 AGENT
                    'devicetype' => 2,
                    'type' =>0,   //充值类型\r\n0：用户给自己充值\r\n1：代理给用户充值\r\n2：普通用户给其他人充值
                    'orderno' => $ref,
                    'amount' => $amt,
                    'localunit' =>'RMB',
                    'showamount' => $showamount*0.1,
                    'rechargetime' =>date('Y-m-d H:i:s'),
                    'status' => 1,  //充值状态\r\n0：失败\r\n1：成功\r\n2：处理中
                    'ispresent'=> 1
                );

                $dbRechargeDetail->add($insertReDisc);
                $this->rechargeAcitivity($userid);
                $showamount = $showamount*1.1;
            }

            //更新用户余额
            $updatBalarr = array(
                'balance' => array('exp', 'balance+' . $showamount),
                'point' => array('exp', 'point+' . $totalrecharge_amount),
                'totalrecharge' => array('exp', 'totalrecharge+' . $totalrecharge_amount)
            );
            $updateBalanceResult = M('Balance')->where(array('userid' =>$userid))->save($updatBalarr);
            if($updateBalanceResult === false){
                $tran->rollback();exit;
            }

            $tran->commit();
            echo 'OK';exit;
        } else {    //交易失败
            echo 'TO DO Payment Fail Logic';exit;
        }
    }

    //生成订单号
    private  function createOrderNo($type,$id){
        $orderno = date('YmdHis').rand(1000,9999).$type.$id;
        return $orderno;
    }
}