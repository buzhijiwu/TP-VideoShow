<?php
namespace Home\Controller;

use Think\Model;
class RegisterController extends CommonController
{
    public function index() {
        if(IS_POST){
            //验证提交的参数
            $field = array (
                'username', 'password', 'verifycode', 'countryno'
            );
            $this->checkParameter($field);

            $username = I('POST.username','', 'trim');
            $password = I('POST.password','', 'trim');
            $verifycode = I('POST.verifycode','', 'trim');
            $countryno = I('POST.countryno','', 'trim');

            //验证验证码
            if($countryno == '84'){  //越南
                $querySmsArr = array(
                    'phoneno' => $username,
                    'smstype' => 0,
                    'verifycode' =>  $verifycode,
                    'senddate' => date('Y-m-d'),
                );
                $smsrecord = M('Smsrecord')->where($querySmsArr)->find();
                if(!$smsrecord){
                    $data = array(
                        'status' => 0,
                        'message' => lan('LAN_VERIFY_ERROR', 'Home'),
                    );
                    $this->ajaxReturn($data);
                }
            }else{  //其他国家
                $response = checkSmsCode($username,$countryno,$verifycode);
                if($response['status'] != 200){
                    $data = array(
                        'status' => 0,
                        'message' => lan('LAN_VERIFY_ERROR', 'Home'),
                    );
                    $this->ajaxReturn($data);
                }
            }

            //验证码校验
            /* $rules = array(
             array('name','','数据名称已存在',0,'unique',1),
             //array(验证字段1,验证规则,错误提示,[验证条件,附加规则,验证时间]),
             array('verify','require','验证码必须！'), //默认情况下用正则进行验证
             array('name','','帐号名称已经存在！',0,'unique',1), // 在新增的时候验证name字段是否唯一
             array('value',array(1,2,3),'值的范围不正确！',2,'in'), // 当值不为空的时候判断是否在一个范围内
             array('repassword','password','确认密码不正确',0,'confirm'), // 验证确认密码是否和密码一致
             array('password','checkPwd','密码格式不正确',0,'function'), // 自定义函数验证密码格式
            ); */
            if($countryno == 84){     //越南
                $rule = array (
                    //用户名必须
                    array('username', 'require', lan("USERNAME_ISNULL","Common")),
                    // 在新增的时候验证name字段是否唯一
                    array('username', '', lan("USERNAME_IS_EXIST","Common"), 0, 'unique', 1),
                    // 用户名长度校验
                    array('username', '/^[0-9]\d{6,16}$/', lan("USERNAME_LENGTH_ERROR","Common")),
                    // 密码长度校验
                    array('password', '/^[0-9a-zA-Z_]{6,16}$/is', lan("PASSWORD_LENGTH_ERROR","Common"))
                );
            }else{
                $rule = array (
                    //用户名必须
                    array('username', 'require', lan("USERNAME_ISNULL","Common")),
                    // 在新增的时候验证name字段是否唯一
                    array('username', '', lan("USERNAME_IS_EXIST","Common"), 0, 'unique', 1),
                    // 用户名长度校验
                    array('username', '/^1[34578]\d{9}$/', lan("USERNAME_LENGTH_ERROR","Common")),
                    // 密码长度校验
                    array('password', '/^[0-9a-zA-Z_]{6,16}$/is', lan("PASSWORD_LENGTH_ERROR","Common"))
                );
            } 
            $db_member = D('Member');
            $result = $db_member->field($field)->validate($rule)->create();
            
            if(!$result) {
                $data = array(
                    'status' => 0,
                    'message' => $db_member->getError(),
                );
                $this->ajaxReturn($data);
            }

            //添加注册用户
            $salt = getRandomCode(4);
            $token = 'PC'.date('YmdHis').$username.$password;
            $insertArr = array(
                'userno' => $username,
                'roomno' => getRoomno(),
                'username' => $username,
                'nickname' => getWaashowNickname($username),
                'password' => md5(md5($password).$salt),
                'salt' => $salt,
                'userlevel' => 0,
                'smallheadpic' => '/Public/Public/Images/HeadImg/default.png',
                'usertype' => 0,
                'countrycode' => $countryno,
                'registertime' => date('Y-m-d H:i:s'),
                'lastlogintime' => date('Y-m-d H:i:s'),
                'lastloginip' => get_client_ip(),
                'isemcee' => 0,
                'token' => $token
            );

            //事务
            $tran = new Model();
            $tran->startTrans();

            $userInfoResult = $tran->table('ws_member')->add($insertArr);
            $selectfield = array(
                'userid', 'userno', 'username', 'roomno', 'niceno', 'familyid', 'nickname','userlevel', 'province',
                'city', 'smallheadpic', 'bigheadpic','lastlogintime', 'lastloginip','isemcee', 'isvirtual',
                'isvip', 'usertype','token'
            );

            $userinfo = $db_member->where(array('username' => $insertArr['username']))->field($selectfield)->find();
            $userCond = array('userid' => $userinfo['userid']);
            $roomno = getUserRoomno($userinfo['userid']);
            $newUserInfo['roomno'] = $roomno;
            $tran->table('ws_member')->where($userCond)->save($newUserInfo);

            $insertRoomArr = array(
                'roomno' => $roomno,
                'roomname' => $insertArr['nickname'],
                'createtime' => $insertArr['registertime']
            );

            $roomResult = $tran->table('ws_room')->add($insertRoomArr);

            $insertBalArr = array(
                'userid' => $userinfo['userid'],
                'spendmoney' => 0,
                'earnmoney' => 0,
                'balance' => 0,
                'point' => 0,
                'totalrecharge' => 0,
                'createtime' => date('Y-m-d H:i:s'),
                'effectivetime' => date('Y-m-d H:i:s'),
                'expiretime' => date('Y-m-d H:i:s', mktime(0,0,0,1,1,2037))
            );
            $balanceResult = $tran->table('ws_balance')->add($insertBalArr);

            $userinfo['grade'] = 0;
            $userinfo['nextlevel'] = 1;
            $db_Levelconfig = D('Levelconfig');
            $nextLevelInfo = $db_Levelconfig->getUserLevelInfoByLevel($userinfo['nextlevel']);
            $userinfo['nextgradepic'] = $nextLevelInfo['smalllevelpic'];

            if ($userInfoResult && $balanceResult && $roomResult) {
                $tran->commit();
                // 写入SESSION
                $userinfo['token'] = $token;
                $this->setSessionCookie($userinfo, $insertBalArr);

                $data['status'] = 1;
                $data['message'] = lan('1', 'Home');
                $data['datalist'] = $userinfo;
                echo json_encode($data);
            }
            else{
                $tran->rollback();
                $data['status'] = 3;
                $data['message'] = lan('OPERATION_FAILED', 'Home');
                echo json_encode($data);
            }
        }
    }
    
    /**
     * 检查用户注册信息
     */
    public function checkUsernameRegister()
    {
        $field = array (
            'username',
            'countryno'
        );
                
        $this->checkParameter($field);
        $countryno = I('post.countryno','', 'trim');
        $username = I('post.username','', 'trim');        
        if ($countryno == '86') {
            $rule = array (
                //用户名必须
                array('username', 'require', lan("USERNAME_ISNULL","Common")),
                // 在新增的时候验证name字段是否唯一
                array('username', '', lan("USERNAME_IS_EXIST","Common"), 0, 'unique', 1),
                // 用户名长度校验
                array('username', '/^1[34578]\d{9}$/', lan("USERNAME_LENGTH_ERROR","Common")),
            );            
        }
        elseif($countryno == '84'){
            if(substr($username, 0, 1) != "0"){
                $data = array(
                    'status' => 3,
                    'message' => lan('MOBILENO_NOT_ADD_ZORE', 'Home')
                );
                echo json_encode($data);       
                exit;         
            }            
            $rule = array (
                //用户名必须
                array('username', 'require', lan("USERNAME_ISNULL","Common")),
                // 在新增的时候验证name字段是否唯一
                array('username', '', lan("USERNAME_IS_EXIST","Common"), 0, 'unique', 1),
                // 用户名长度校验
                array('username', '/^[0-9]\d{6,16}$/', lan("USERNAME_LENGTH_ERROR","Common")),
            );
        }
        else{
            $rule = array (
                //用户名必须
                array('username', 'require', lan("USERNAME_ISNULL","Common")),
                // 在新增的时候验证name字段是否唯一
                array('username', '', lan("USERNAME_IS_EXIST","Common"), 0, 'unique', 1),
                // 用户名长度校验
                array('username', '/^[0-9]\d{6,16}$/', lan("USERNAME_LENGTH_ERROR","Common")),
            );            
        }

        $db_member = D('Member');
        $result = $db_member->field($field)->validate($rule)->create();

        if(!$result) {
            $errorInfo = array(
                'status' => 0,
                'message' => $db_member->getError(),
            );
            echo json_encode($errorInfo);
        }
        else{
            $data = array(
                'status' => 2,
                'message' => lan('1', 'Home')
            );
            echo json_encode($data);
        }
    }
    
    public function sendSmsToUser(){
        $username = I('POST.phoneno','', 'trim');   //手机号
        $countryno = I('POST.countryno','', 'trim');   //国家码

        //验证手机号
        if(!$username){
            $data = array(
                'status' => 0,
                'message' => lan("USERNAME_ISNULL", "Common")
            );
            $this->ajaxReturn($data);
        }

        //查询当天手机验证码发送记录
        $mSmsrecord = M("Smsrecord");
        $querySmsArr = array(
            'phoneno' => $username,
            'smstype' => 0,
            'senddate' => date('Y-m-d')
        );
        $smsrecord = $mSmsrecord->where($querySmsArr)->find();

        //验证发送次数
        if($smsrecord && $smsrecord['smstimes'] >= 3){
            $data = array(
                'status' => 0,
                'message' => lan("EXCEEDMAXSENDSMSTIMES", "Home")
            );
            $this->ajaxReturn($data);
        }

        if($countryno == '84'){     //越南国家码
            $url = 'http://api.abenla.com/Service.asmx/';
            $loginName = "AB6PYLX"; //(isset($_REQUEST["txtLoginName"])) ? strtoupper($_REQUEST["txtLoginName"]) : "";
            $passWord = md5("K7ECMHN34"); //(isset($_REQUEST["txtPassWord"])) ? md5($_REQUEST["txtPassWord"]) : "";
            $brandName = "n/a"; //$brandName = "n/a" for longcode  waashow for brandname (isset($_REQUEST["txtBrandName"])) ? $_REQUEST["txtBrandName"] : "";ABENLA
            $serviceTypeId = "9";  // 短信发送类型：1.brandname 2.Mob 9.longcode
            $sign = md5($loginName . '-' . $passWord . '-' . $brandName . '-' . $serviceTypeId);

            //定义发送内容
            if($smsrecord){
                $vericode = $smsrecord['verifycode'];
                $content = $smsrecord['smscontent'];
            }else{
                $content = M("Smsdefinition")->where(array('lantype' => $this->lan))->getField('smscontent');
                if(!$content){
                    $content = "The verify code of Waashow is";
                }
                $vericode = getRandomVerify();  //四位随机码
                $content = $content . " " . $vericode;
            }

            //发送验证码
            $objContent = array (
                'PhoneNumber' => $username,
                'Message' => $content,
                'SmsGuid' => '6b9b5e52-28a1-4c10-a7f5-5826e23799b1',
                'ContentType' => '1'
            );
            $strContent = json_encode($objContent);
            $client = simplexml_load_file($url . 'SendSms2?loginName=' . $loginName . '&brandName=' . $brandName . '&serviceTypeId=' . $serviceTypeId . '&content=' . $strContent . '&Sign=' . $sign);
            $Code_array = json_decode(json_encode($client->Code), true);
            $resultcode = $Code_array[0];
            $Message_array = json_decode(json_encode($client->Message), true);
            $message = $Message_array[0];

            //发送失败
            if($resultcode != "106"){
                $data = array(
                    'status' => $resultcode,
                    'message' => $message
                );
                $this->ajaxReturn($data);
            }
        }else{  //其他国家验证码短信发送
            $response = sendSmsCode($username,$countryno);
            if($response['status'] != 200){ //发送失败
                $data = array(
                    'status' => $response['status'],
                    'message' => lan('PARAMETER_ERROR','Home')
                );
                $this->ajaxReturn($data);
            }
            $serviceTypeId = "2";  // 短信发送类型：1.brandname 2.Mob 9.longcode
            $content = '';
            $vericode = '';
        }

        //验证码发送成功
        if($smsrecord){
            $updatearr = array(
                'smstimes' => array('exp', 'smstimes+1'),
                'sendtime' => $smsrecord['sendtime']. ',' .date('H:i:s')
            );
            $result = $mSmsrecord->where($querySmsArr)->save($updatearr);
        }else{
            $insertSmsArr = array(
                'phoneno' => $username,
                'smstype' => 0,
                'smsservicetype' => $serviceTypeId,
                'smscontent' => $content,
                'verifycode' => $vericode,
                'smstimes' => 1,
                'senddate' => date('Y-m-d'),
                'sendtime' => date('H:i:s'),
            );
            $result = $mSmsrecord->add($insertSmsArr);
        }

        //数据库更新结果
        if($result === false){
            $data = array(
                'status' => 0,
                'message' => $mSmsrecord->getError()
            );
            $this->ajaxReturn($data);
        }else{
            $data = array(
                'status' => 1
            );
            $this->ajaxReturn($data);
        }
    }
    
    public function testSmsSend(){
        $url = 'http://api.abenla.com/Service.asmx/';
        
        $action = "SendSms2"; //"SendSms2"; //$_REQUEST["Action"];
        $loginName = "AB6PYLX"; //(isset($_REQUEST["txtLoginName"])) ? strtoupper($_REQUEST["txtLoginName"]) : "";
        $passWord = md5("K7ECMHN34"); //(isset($_REQUEST["txtPassWord"])) ? md5($_REQUEST["txtPassWord"]) : "";
        $brandName = "ABENLA"; //(isset($_REQUEST["txtBrandName"])) ? $_REQUEST["txtBrandName"] : ""; ABENLA 1  Waashow 9
        $content = "Waashow verifycode 111111"; //(isset($_REQUEST["txtContent"])) ? $_REQUEST["txtContent"] : "";
        $serviceTypeId = "9";
        
        if(isset($action)){
            switch($action){
                case 'CheckConnection':
                    //$loginName = (isset($_REQUEST["txtLoginName"])) ? strtoupper($_REQUEST["txtLoginName"]) : "";
                    //$passWord = (isset($_REQUEST["txtPassWord"])) ? md5($loginName . '-' . md5($_REQUEST["txtPassWord"])) : "";
                    $signtext =  md5($loginName . '-' . $passWord);
                    $client = simplexml_load_file($url . 'CheckConnection?loginName=' . $loginName . '&Sign=' . $signtext);
                    break;
                case 'GetBalance':
                    $loginName = (isset($_REQUEST["txtLoginName"])) ? strtoupper($_REQUEST["txtLoginName"]) : "";
                    $passWord = (isset($_REQUEST["txtPassWord"])) ? md5($loginName . '-' . md5($_REQUEST["txtPassWord"])) : "";
                    $client = simplexml_load_file($url . 'GetBalance?loginName=' . $loginName . '&Sign=' . $passWord);
                    break;
                case 'GetBrandName':
                    //$loginName = (isset($_REQUEST["txtLoginName"])) ? strtoupper($_REQUEST["txtLoginName"]) : "";
                    //$passWord = (isset($_REQUEST["txtPassWord"])) ? md5($loginName . '-' . md5($_REQUEST["txtPassWord"])) :
                    $signtext =  md5($loginName . '-' . $passWord);"";
                    $client = simplexml_load_file($url . 'GetBrandName?loginName=' . $loginName . '&Sign=' . $signtext);
                    break;
                case 'GetSmsStatus':
                    $loginName = (isset($_REQUEST["txtLoginName"])) ? strtoupper($_REQUEST["txtLoginName"]) : "";
                    $passWord = (isset($_REQUEST["txtPassWord"])) ? md5($_REQUEST["txtPassWord"]) : "";
                    $smsGuid = (isset($_REQUEST["txtSmsGuid"])) ? $_REQUEST["txtSmsGuid"] : "";
                    $createDate = (isset($_REQUEST["txtCreateDate"])) ? $_REQUEST["txtCreateDate"] : 0;
                    $sign = md5($loginName . '-' . $passWord . '-' . $smsGuid . '-' . '1' . '-' . $createDate);
                    $client = simplexml_load_file($url . 'GetSmsStatus?loginName=' . $loginName . '&smsGuid=' . $smsGuid . '&serviceTypeId=1&createdDate=' . $createDate . '&Sign=' . $sign);
                    break;
                case 'SendSms':
                    //$loginName = (isset($_REQUEST["txtLoginName"])) ? strtoupper($_REQUEST["txtLoginName"]) : "";
                    //$passWord = (isset($_REQUEST["txtPassWord"])) ? md5($_REQUEST["txtPassWord"]) : "";
                    //$brandName = (isset($_REQUEST["txtBrandName"])) ? $_REQUEST["txtBrandName"] : "";
                    //$content = (isset($_REQUEST["txtContent"])) ? $_REQUEST["txtContent"] : "";
                    $sign = md5($loginName . '-' . $passWord . '-' . $brandName . '-' . '1');
                    $client = simplexml_load_file($url . 'SendSms?loginName=' . $loginName . '&brandName=' . $brandName . '&serviceTypeId=1&content=' . $content . '&Sign=' . $sign);
                    break;
                case 'SendSms2':
                    //$loginName = (isset($_REQUEST["txtLoginName"])) ? strtoupper($_REQUEST["txtLoginName"]) : "";
                    //$passWord = (isset($_REQUEST["txtPassWord"])) ? md5(trim($_REQUEST["txtPassWord"])) : "";
                    //$brandName = (isset($_REQUEST["txtBrandName"])) ? strtoupper($_REQUEST["txtBrandName"]) : "";
                    //$content = (isset($_REQUEST["txtContent"])) ? $_REQUEST["txtContent"] : "";
        
                    $sign = md5($loginName . '-' . $passWord . '-' . $brandName . '-' . $serviceTypeId);
        
                    $objContent = Array (
                        'PhoneNumber' => '0934867870',
                        'Message' => $content,
                        'SmsGuid' => '6b9b5e52-28a1-4c10-a7f5-5826e23799be',
                        'ContentType' => '1'
                    );
                    $strContent = json_encode($objContent);
        
                    $client = simplexml_load_file($url . 'SendSms2?loginName=' . $loginName . '&brandName=' . $brandName . '&serviceTypeId=' . $serviceTypeId . '&content=' . $strContent . '&Sign=' . $sign);
                    break;
            }
        }
        
        $result = ($client->Code != 106) ? '<font color = "red"> (Error) </font>'  : '<font color = "green"> (Success) </font>';
        echo '<b><font size = "14px">Result Test Function</font></b><br /><hr /><br />';
        echo 'Code : <b> ' . $client->Code . $result . '</b><br />';
        echo 'Message : <b><i>' . $client->Message . '</i></b><br />';
        ////////////////////////////////////////////////////////////////////////////////////////////////////////////
        //For Action "GetBalance" (Current Balance Of Client)
        ////////////////////////////////////////////////////////////////////////////////////////////////////////////
        if($client->Amount != null && $client->Amount != '')
        {
            echo 'Amount : <b><i>' . $client->Amount . '</i></b><br />';
        }
        ////////////////////////////////////////////////////////////////////////////////////////////////////////////
        //For Action "GetSmsStatus" (Status Of Lastest Sms Send By Client)
        ////////////////////////////////////////////////////////////////////////////////////////////////////////////
        if($client->SmsStatus != null && $client->SmsStatus != '')
        {
            echo 'Sms Status : <b>' . $client->SmsStatus . '</b><br />';
        }
        
        if($client->SendDate != null && $client->SendDate != '')
        {
            echo 'Send Date : <b>' . $client->SendDate . '</b>';
        }
        ////////////////////////////////////////////////////////////////////////////////////////////////////////////
        //For Action "SendSms" (Total Sms Send Success & Total Sms Send Fail in this transaction of client)
        ////////////////////////////////////////////////////////////////////////////////////////////////////////////
        if($client->TotalSuccessSms != null && $client->TotalSuccessSms != '')
        {
            echo 'Total Success : <b>' . $client->TotalSuccessSms . '</b><br />';
        }
        if($client->TotalFailSms != null && $client->TotalFailSms != '')
        {
            echo 'Total Fail : <b>' . $client->TotalFailSms . '</b>';
        }
        if(count($client->BrandNameList) > 0)
        {
            $i = 1;
            foreach($client->BrandNameList as $strBrandName)
            {
                echo 'Brand Name ' . $i . ': ' . $strBrandName->string;
            }
        }
        
        
        echo '<br /><br /><hr />';
        echo 'Abenla Client SMS API V 1.0';
    }
}

?>