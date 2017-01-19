<?php
namespace Api\Controller;
use Think\Controller;
/**
 * 登录注册相关接口
 *
 * 主要处理与登录注册相关的业务逻辑
 * doRegister  注册
 * doLogin  登录
 * thirdPartyLogin		第三方登录
 * checkUserLoginStatus	验证用户登录状态
 * sendVietnamSms		发送越南短信验证码
 * modifyPassword		修改密码
 * forgetPassword		忘记密码
 * getCountryno		获取国家码列表
 * checkUsernameRegister	验证用户名是否已注册
 */
class LoginRegisterController extends CommonController {

    public function _initialize(){
        parent::_initialize();
    }

    /**
     * 用户注册
     * @param phoneno：手机号
     * @param password：密码
     * @param countryno：国家码
     * @param verifycode：验证码
     * @param appkey：密钥
     * @param lastloginip：最后登录IP
     */
    public function doRegister($inputParams){
        $phoneno = $inputParams['phoneno'];
        $postpassword = md5($inputParams['password']);
        $countryno = $inputParams['countryno'];
        $verifycode = $inputParams['verifycode'];
        $appkey = $inputParams['appkey'];
        $lastloginip = $inputParams['lastloginip'];
        $distributeid = (int)$inputParams['distributeid'];

        //验证手机号是否已经存在
        $queryCheckUsername = array(
            'username' => $phoneno
        );
        $userInfo = M('Member')->where($queryCheckUsername)->find();
        if($userInfo){
            $data['status'] = 400002;
            $data['message'] = lan('400002', 'Api', $this->lanPackage);
            return $data;
        }

        //根据不同国家码，检测验证码
        $checkVerifyCode = false;
        switch ($countryno) {
            case '84' : //越南
                //查询数据库
                $dbSmsrecord = M("Smsrecord");
                $querySmsArr = array(
                    'phoneno' => $phoneno,
                    'smstype' => 0,
                    'verifycode' =>  $verifycode,
                    'senddate' => date('Y-m-d'),
                );
                $smsRecord = $dbSmsrecord->where($querySmsArr)->find();
                if ($smsRecord) {
                    $checkVerifyCode = true;
                }
                break;
            default :   //其他
                //请求mob的短信接口验证
                $responsejson = sendsms($appkey, $phoneno, $countryno, $verifycode);
                $response = json_decode($responsejson, true);
                if ($response['status'] == 200) {
                    $checkVerifyCode = true;
                }
                break;
        }

        //验证码错误
        if (!$checkVerifyCode) {
            $data['status'] = 400003;
            $data['message'] = lan('400003', 'Api', $this->lanPackage);
            return $data;
        }

        $dbMember = M('Member');
        $nowTime = date('Y-m-d H:i:s');

        //新增用户
        $salt = getRandomCode(4);
        $password = md5($postpassword . $salt);
        $insertArr = array(
            'userno' => $phoneno,
            'roomno' => getRoomno(),
            'username' => $phoneno,
            'nickname' => getWaashowNickname($phoneno),
            'password' => $password,
            'salt' => $salt,
            'userlevel' => 0,
            'smallheadpic' => '/Public/Public/Images/HeadImg/default.png',
            'countrycode' => $countryno,
            'registertime' => $nowTime,
            'lastlogintime' => $nowTime,
            'lastloginip' => $lastloginip,
            'token' => 'App'.date('YmdHis').$phoneno.$password,
            'distributeid' => $distributeid
        );
        $userid = $dbMember->add($insertArr);
        if (!$userid) {
            $data['status'] = -1;
            $data['message'] = lan('-1', 'Api', $this->lanPackage);
            return $data;
        }

        //更新roomno
        $userCond = array(
            'userid' => $userid
        );
        $roomno = getUserRoomno($userid);
        $newUserInfo['roomno'] = $roomno;
        $dbMember->where($userCond)->save($newUserInfo);

        //新增Room记录
        $insertRoomArr = array(
            'roomno' => $roomno,
            'roomname' => $insertArr['nickname'],
            'createtime' => $insertArr['registertime']
        );
        $resultAddRoom = M('Room')->add($insertRoomArr);
        if ($resultAddRoom === false) {
            $data['status'] = -1;
            $data['message'] = lan('-1', 'Api', $this->lanPackage);
            return $data;
        }

        //新增Balance记录
        $insertBalArr = array(
            'userid' => $userid,
            'spendmoney' => 0,
            'earnmoney' => 0,
            'balance' => 0,
            'point' => 0,
            'totalrecharge' => 0,
            'createtime' => $nowTime,
            'effectivetime' => $nowTime,
            'expiretime' => date('Y-m-d H:i:s', mktime(0,0,0,1,1,2037))
        );
        $resultAddBalance = M('Balance')->add($insertBalArr);
        if ($resultAddBalance === false) {
            $data['status'] = -1;
            $data['message'] = lan('-1', 'Api', $this->lanPackage);
            return $data;
        }

        //注册成功返回数据
        $data = array(
            'status' => 200,
            'message' => lan('200', 'Api', $this->lanPackage),
            'datalist' => array(
                'userid' => $userid,
                'userno' => $phoneno,
                'roomno' => $roomno,
                'niceno' => '',
                'showroomno' => $roomno,
                'familyid' => '11',
                'username' => $phoneno,
                'nickname' => $insertArr['nickname'],
                'vipid' => '0',
                'guardid' => '0',
                'userlevel' => '0',
                'countrycode' => $countryno,
                'registertime' => $insertArr['registertime'],
                'lastlogintime' => $insertArr['lastlogintime'],
                'province' => '',
                'city' => '',
                'smallheadpic' => '/Public/Public/Images/HeadImg/default.png',
                'bigheadpic' => '',
                'lastloginip' => $appkey,
                'isemcee' => '0',
                'token' => $insertArr['token'],
                'forbidlist' => array(),
                'emceeinfo' => array(),
                'usertype' => '0',
                'balance' => '0',
                'freegiftcount' => '0',    //免费礼物数量
            )
        );
        return $data;
    }

    /**
     * 用户登录
     * @param phoneno：手机号
     * @param password：密码
     * @param countryno：国家码
     * @param lastloginip：最后登录IP
     */
    public function doLogin($inputParams){
        $phoneno = $inputParams['phoneno'];
        $password = $inputParams['password'];
        $countryno = $inputParams['countryno'];
        $lastloginip = $inputParams['lastloginip'];
        //$distributeid = (int)$inputParams['distributeid'];

        //验证手机号是否存在
        $dbMember = M('Member');
        $queryUsername = array(
            'username' => $phoneno
        );
        $userInfo = D('Member', 'Modapi')->getMemberInfoByWhereConf($queryUsername);
        if (!$userInfo) {
            $data['status'] = 400004;
            $data['message'] = lan('400004', 'Api', $this->lanPackage);
            return $data;
        }

        //验证国家码是否正确
        if ($countryno != $userInfo['countrycode']) {
            $data['status'] = 400004;
            $data['message'] = lan('400004', 'Api', $this->lanPackage);
            return $data;
        }

        //验证用户是否被禁用或删除
        if ($userInfo['status'] == 1) {
            $data['status'] = 400005;
            $data['message'] = lan('400005', 'Api', $this->lanPackage);
            return $data;
        }

        //获取用户密码信息
        $userPassInfo = M('Member')
            ->where(array('userid'=>$userInfo['userid']))
            ->field('password,salt')
            ->find();

        //验证密码是否正确
        $postpassword = md5($password);
        $password = md5($postpassword . $userPassInfo['salt']);
        if ($password != $userPassInfo['password']) {
            $data['status'] = 400006;
            $data['message'] = lan('400006', 'Api', $this->lanPackage);
            return $data;
        }

        //登录成功更新登录信息
        $nowTime = date('Y-m-d H:i:s');
        $newToken = 'App'.date('YmdHis'). $phoneno . $password;
        $updateData = array(
            'lastlogintime' => $nowTime,
            'lastloginip' => $lastloginip,
            'token' => $newToken,
            //'distributeid' => $distributeid
        );
        $queryUserid = array(
            'userid' => $userInfo['userid']
        );
        $result = $dbMember->where($queryUserid)->save($updateData);
        if ($result === false) {
            $data['status'] = 400001;
            $data['message'] = lan('400001', 'Api', $this->lanPackage);
            return $data;
        }

        //登录成功，返回数据
        $emceeInfo = array();
        if ($userInfo['isemcee'] == 1) {
            $emceeInfo = D('Emceeproperty','Modapi')->getEmceeProInfo($queryUserid);    //获取主播信息
        }
        $balanceInfo = M('Balance')
            ->where($queryUserid)
            ->field('balance,spendmoney,earnmoney')
            ->find();
        $data['status'] = 200;
        $data['message'] = lan('200', 'Api', $this->lanPackage);
        $data['datalist'] = $userInfo;
        $data['datalist']['showroomno'] = $this->getShowroomno($userInfo);
        $data['datalist']['vipid'] = D('Viprecord','Modapi')->getMyTopVipid($userInfo['userid']);
        $data['datalist']['token'] = $newToken;
        $data['datalist']['emceeinfo'] = $emceeInfo;
        $data['datalist']['equipmentinfo'] = D('Equipment','Modapi')
            ->getMyUseEquipments($userInfo['userid'], $this->lantype);
        $data['datalist']['balance'] = $balanceInfo['balance'];
        $data['datalist']['spendmoney'] = $balanceInfo['spendmoney'];
        $data['datalist']['earnmoney'] = $balanceInfo['earnmoney'];
        $data['datalist']['freegiftcount'] = $this->getFreeGiftcount($userInfo['userid']);
        return $data;
    }

    /**
     * 第三方登录
     * @param thirdparty：第三方id 0：自己系统 1：Facebook 2：Google 3：Twitter
     * @param tpuserid：系统保存的userid
     * @param tpusername：系统保存的用户名
     * @param smallheadpic：第三方头像
     * @param token：第三方token
     * @param devicetype：设备类型
     * @param lastloginip：最后登录IP
     */
    public function thirdPartyLogin($inputParams){
        $thirdparty = $inputParams['thirdparty'];
        $tpuserid = $inputParams['tpuserid'];
        $token = $inputParams['token'];
        $devicetype = $inputParams['devicetype'];
        $lastloginip = $inputParams['lastloginip'];
        $smallheadpic = $inputParams['smallheadpic'];
        $distributeid = (int)$inputParams['distributeid'];

        //验证用户是否存在
        $tpUserCond = array(
            'thirdparty' => $thirdparty,
            'identifier' => $tpuserid
        );
        $dbMember = M('Member');
        $userInfo = D('Member', 'Modapi')->getMemberInfoByWhereConf($tpUserCond);

        $nowTime = date('Y-m-d H:i:s');
        $newToken = 'App'.date('YmdHis').$thirdparty.$tpuserid;
        if ($userInfo) {    //用户存在验证登录
            //验证用户是否被禁用或删除
            if ($userInfo['status'] == 1) {
                $data['status'] = 400005;
                $data['message'] = lan('400005', 'Api', $this->lanPackage);
                return $data;
            }

            //更新头像，如果之前存在头像并且不是第三方头像，则不再更新第三方头像
            if ($userInfo['smallheadpic'] && substr($userInfo['smallheadpic'],0,4) != 'http')
            {
                $smallheadpic = $userInfo['smallheadpic'];
            }
            else if ($inputParams['smallheadpic'])
            {
                $smallheadpic = getSmallHeadpicUrl($smallheadpic, $userInfo['userid']);
                $userInfo['smallheadpic'] = $smallheadpic;
            }
            $updateData = array(
                'lastlogintime' => $nowTime,
                'lastloginip' => $lastloginip,
                'token' => $newToken,
                'devicetype' => $devicetype,
                'smallheadpic' => $smallheadpic,
                //'distributeid' => $distributeid
            );
            $queryUserid = array(
                'userid' => $userInfo['userid']
            );
            $dbMember->where($queryUserid)->save($updateData);

            //登录成功，返回数据
            $emceeInfo = array();
            if ($userInfo['isemcee'] == 1) {
                $emceeInfo = D('Emceeproperty','Modapi')->getEmceeProInfo($queryUserid);    //获取主播信息
            }
            $balanceInfo = M('Balance')
                ->where($queryUserid)
                ->field('balance,spendmoney,earnmoney')
                ->find();
            $data['status'] = 200;
            $data['message'] = lan('200', 'Api', $this->lanPackage);
            $data['datalist'] = $userInfo;
            $data['datalist']['showroomno'] = $this->getShowroomno($userInfo);
            $data['datalist']['vipid'] = D('Viprecord','Modapi')->getMyTopVipid($userInfo['userid']);
            $data['datalist']['token'] = $newToken;
            $data['datalist']['emceeinfo'] = $emceeInfo;
            $data['datalist']['equipmentinfo'] = D('Equipment','Modapi')
                ->getMyUseEquipments($userInfo['userid'], $this->lantype);
            $data['datalist']['balance'] = $balanceInfo['balance'];
            $data['datalist']['spendmoney'] = $balanceInfo['spendmoney'];
            $data['datalist']['earnmoney'] = $balanceInfo['earnmoney'];
            $data['datalist']['freegiftcount'] = $this->getFreeGiftcount($userInfo['userid']);
            return $data;
        } else {  //第三方第一次登录，注册新用户
            $insertMember = array(
                'thirdparty' => $inputParams['thirdparty'],
                'identifier' => $inputParams['tpuserid'],
                'roomno' => getRoomno(),
                'username' => $inputParams['tpusername'],
                'nickname' => $inputParams['tpusername'],
                'smallheadpic' => '/Public/Public/Images/HeadImg/default.png',
                'salt' => '',
                'userlevel' => 0,
                'registertime' => $nowTime,
                'lastlogintime' => $nowTime,
                'token' => $newToken,
                'distributeid' => $distributeid
            );

            $userid = $dbMember->add($insertMember);
            if (!$userid) {
                $data['status'] = -1;
                $data['message'] = lan('-1', 'Api', $this->lanPackage);
                return $data;
            }

            //更新roomno
            $userCond = array(
                'userid' => $userid
            );
            $roomno = getUserRoomno($userid);
            if ($inputParams['smallheadpic'])
            {
                $smallheadpic = getSmallHeadpicUrl($inputParams['smallheadpic'], $userid);
                $newUserInfo['smallheadpic'] = $smallheadpic;
            }

            $newUserInfo['roomno'] = $roomno;
            $dbMember->where($userCond)->save($newUserInfo);

            //新增Room记录
            $insertRoomArr = array(
                'roomno' => $roomno,
                'roomname' => $insertMember['nickname'],
                'createtime' => $insertMember['registertime']
            );
            $resultAddRoom = M('Room')->add($insertRoomArr);
            if ($resultAddRoom === false) {
                $data['status'] = -1;
                $data['message'] = lan('-1', 'Api', $this->lanPackage);
                return $data;
            }

            //新增Balance记录
            $insertBalArr = array(
                'userid' => $userid,
                'spendmoney' => 0,
                'earnmoney' => 0,
                'balance' => 0,
                'point' => 0,
                'totalrecharge' => 0,
                'createtime' => $nowTime,
                'effectivetime' => $nowTime,
                'expiretime' => date('Y-m-d H:i:s', mktime(0,0,0,1,1,2037))
            );
            $resultAddBalance = M('Balance')->add($insertBalArr);
            if ($resultAddBalance === false) {
                $data['status'] = -1;
                $data['message'] = lan('-1', 'Api', $this->lanPackage);
                return $data;
            }

            //注册成功返回数据
            $data = array(
                'status' => 200,
                'message' => lan('200', 'Api', $this->lanPackage),
                'datalist' => array(
                    'userid' => $userid,
                    'identifier' => $inputParams['tpuserid'],
                    'roomno' => $roomno,
                    'nickname' => $insertMember['nickname'],
                    'userlevel' => 0,
                    'vipid' => 0,
                    'guardid' => 0,
                    'isemcee' => 0,
                    'familyid' => 0,
                    'registertime' => $insertMember['registertime'],
                    'lastlogintime' => $insertMember['lastlogintime'],
                    'token' => $insertMember['token'],
                    'smallheadpic' => $insertMember['smallheadpic'],
                    'bigheadpic' => '',
                    'usertype' => $userInfo['usertype'],
                    'balance' => '0',
                    'freegiftcount' => '0',    //免费礼物数量
                )
            );
            return $data;
        }
    }

    /**
     * 验证用户登录状态
     * @param userid：用户userid
     * @param token：用户登录的token
     */
    public function checkUserLoginStatus($inputParams){
        $userid = $inputParams['userid'];
        $token = $inputParams['token'];

        //验证token是否正确
        $queryUserid = array(
            'userid' => $userid
        );
        $userInfo = D('Member', 'Modapi')->getMemberInfoByWhereConf($queryUserid);
        if (empty($token) || empty($userInfo) || $token != $userInfo['token']) {
            $data['status'] = 400001;
            $data['message'] = lan('400001', 'Api', $this->lanPackage);
        } else {    //验证通过，返回数据
            $emceeInfo = array();
            if ($userInfo['isemcee'] == 1) {
                $emceeInfo = D('Emceeproperty','Modapi')->getEmceeProInfo($queryUserid);    //获取主播信息
            }
            $balanceInfo = M('Balance')
                ->where($queryUserid)
                ->field('balance,spendmoney,earnmoney')
                ->find();
            $data['status'] = 200;
            $data['message'] = lan('200', 'Api', $this->lanPackage);
            $data['datalist'] = $userInfo;
            $data['datalist']['showroomno'] = $this->getShowroomno($userInfo);
            $data['datalist']['vipid'] = D('Viprecord','Modapi')->getMyTopVipid($userInfo['userid']);
            $data['datalist']['emceeinfo'] = $emceeInfo;
            $data['datalist']['equipmentinfo'] = D('Equipment','Modapi')
                ->getMyUseEquipments($userInfo['userid'], $this->lantype);
            $data['datalist']['balance'] = $balanceInfo['balance'];
            $data['datalist']['spendmoney'] = $balanceInfo['spendmoney'];
            $data['datalist']['earnmoney'] = $balanceInfo['earnmoney'];
        }
        return $data;
    }

    /**
     * 发送越南短信验证码
     * @param phoneno：手机号
     * @param countryno：国家码
     * @param lantype：语言类型
     */
    public function sendVietnamSms($inputParams){
        //验证国家码
        $username = $inputParams['phoneno'];
        $countryno = $inputParams['countryno'];
        if($countryno != 84){
            $data['status'] = 400;
            $data['message'] = lan('400', 'Api', $this->lanPackage);
            return $data;
        }

        //验证短信次数
        $dbSmsrecord = M("Smsrecord");
        $querySmsArr = array(
            'phoneno' => $username,
            'smstype' => 0,
            'senddate' => date('Y-m-d')
        );
        $smsRecord = $dbSmsrecord->where($querySmsArr)->find();
        if ($smsRecord && $smsRecord['smstimes'] >= 3) {
            $data['status'] = 400007;
            $data['message'] = lan('400007', 'Api', $this->lanPackage);
            return $data;
        }

        //定义发送内容
        $content = M("Smsdefinition")->where(array('lantype' => $this->lantype))->getField('smscontent');
        if(!$content){
            $content = "The verify code of Waashow is";
        }
        $vericode = getRandomVerify();
        $content = $content . " " . $vericode;

        //发送越南短信验证码配置信息
        $url = 'http://api.abenla.com/Service.asmx/';
        $loginName = "AB6PYLX";
        $passWord = md5("K7ECMHN34");
        $brandName = "n/a";
        $serviceTypeId = "9";
        //生成签名
        $sign = md5($loginName . '-' . $passWord . '-' . $brandName . '-' . $serviceTypeId);

        //发送短信
        $objContent = array (
            'PhoneNumber' => $username,
            'Message' => $content,
            'SmsGuid' => '6b9b5e52-28a1-4c10-a7f5-5826e23799b1',
            'ContentType' => '1'
        );
        $strContent = json_encode($objContent);
        $client = simplexml_load_file($url . 'SendSms2?loginName=' . $loginName . '&brandName=' . $brandName . '&serviceTypeId=' . $serviceTypeId . '&content=' . $strContent . '&Sign=' . $sign);
        $Code_array = json_decode(json_encode($client->Code), true);
        $resultcode = $Code_array[0];   //发送状态
        $Message_array = json_decode(json_encode($client->Message), true);
        $message = $Message_array[0];   //发送结果提示

        //发送失败
        if ($resultcode != '106') {
            $data['status'] = -1;
            $data['message'] = lan('-1', 'Api', $this->lanPackage);
            return $data;
        }

        //发送成功更新保存数据
        if ($smsRecord) {
            $updateArr = array(
                'smscontent' => $content,
                'verifycode' => $vericode,
                'smstimes' => array('exp', 'smstimes+1'),
                'sendtime' => $smsRecord['sendtime']. ',' .date('H:i:s')
            );
            $result = $dbSmsrecord->where($querySmsArr)->save($updateArr);
        } else {
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
            $result = $dbSmsrecord->add($insertSmsArr);
        }

        //返回结果
        if ($result === false) {
            $data['status'] = -1;
            $data['message'] = lan('-1', 'Api', $this->lanPackage);
        } else {
            $data['status'] = 200;
            $data['message'] = lan('200', 'Api', $this->lanPackage);
        }
        return $data;
    }

    /**
     * 修改密码
     * @param userid：登录用户userid
     * @param oldpwd：旧密码
     * @param newpwd：新密码
     * @param confirmpwd：确认密码
     */
    public function modifyPassword($inputParams){
        $userid = $inputParams['userid'];
        $oldpwd = $inputParams['oldpwd'];
        $newpwd = $inputParams['newpwd'];
        $confirmpwd = $inputParams['confirmpwd'];

        //验证确认密码
        if ($newpwd != $confirmpwd) {
            $data['status'] = 400009;
            $data['message'] = lan('400009', 'Api', $this->lanPackage);
            return $data;
        }

        //验证密码
        $queryUserid = array(
            'userid' => $userid
        );
        $dbMember = M('Member');
        $userInfo = $dbMember->where($queryUserid)->find();
        $mdOldpwd = md5(md5($oldpwd).$userInfo['salt']);
        if ($mdOldpwd != $userInfo['password']) {
            $data['status'] = 400008;
            $data['message'] = lan('400008', 'Api', $this->lanPackage);
            return $data;
        }

        //验证新密码格式
        $result = preg_match("/[0-9a-zA-Z_]{6,16}/is",$newpwd);
        if (!$result) {
            $data['status'] = 400010;
            $data['message'] = lan('400010', 'Api', $this->lanPackage);
            return $data;
        }

        //更新密码
        $editData['password'] = md5(md5($newpwd).$userInfo['salt']);
        $result = $dbMember->where($queryUserid)->save($editData);

        //返回结果
        if ($result === false) {
            $data['status'] = -1;
            $data['message'] = lan('-1', 'Api', $this->lanPackage);
        } else {
            $data['status'] = 200;
            $data['message'] = lan('200', 'Api', $this->lanPackage);
        }
        return $data;
    }

    /**
     * 忘记密码
     * @param phoneno：手机号
     * @param password：密码
     * @param countryno：国家码
     * @param verifycode：验证码
     * @param appkey：密钥
     */
    public function forgetPassword($inputParams){
        $phoneno = $inputParams['phoneno'];
        $postpassword = md5($inputParams['password']);
        $countryno = $inputParams['countryno'];
        $verifycode = $inputParams['verifycode'];
        $appkey = $inputParams['appkey'];

        //验证手机号是否存在
        $dbMember = M('Member');
        $queryUsername = array(
            'username' => $phoneno
        );
        $userInfo = $dbMember->where($queryUsername)->find();
        if (!$userInfo) {
            $data['status'] = 400004;
            $data['message'] = lan('400004', 'Api', $this->lanPackage);
            return $data;
        }

        //验证密码格式
        $result = preg_match("/[0-9a-zA-Z_]{6,16}/is",$postpassword);
        if (!$result) {
            $data['status'] = 400010;
            $data['message'] = lan('400010', 'Api', $this->lanPackage);
            return $data;
        }

        //校验验证码
        $checkVerifyCode = false;
        if ($countryno == '84') {
            $querySmsArr = array(
                'phoneno' => $phoneno,
                'smstype' => 0,
                'verifycode' =>  $verifycode,
                'senddate' => date('Y-m-d'),
            );
            $smsRecord = M("Smsrecord")->where($querySmsArr)->find();
            if ($smsRecord) {
                $checkVerifyCode = true;
            }
        } else {
            $responsejson = sendsms($appkey, $phoneno, $countryno, $verifycode);
            $response = json_decode($responsejson, true);
            if ($response['status'] == 200) {
                $checkVerifyCode = true;
            }
        }

        //验证码错误
        if (!$checkVerifyCode) {
            $data['status'] = 400003;
            $data['message'] = lan('400003', 'Api', $this->lanPackage);
            return $data;
        }

        //更新密码
        $password = md5($postpassword . $userInfo['salt']);
        $updateArr = array(
            'password' => $password,
            'lastlogintime' => date('Y-m-d H:i:s'),
            'lastloginip' => $appkey,
            'token' => 'App'.date('YmdHis').$phoneno.$password
        );
        $result = $dbMember->where($queryUsername)->save($updateArr);

        //返回结果
        if ($result === false) {
            $data['status'] = -1;
            $data['message'] = lan('-1', 'Api', $this->lanPackage);
        } else {
            $data['status'] = 200;
            $data['message'] = lan('200', 'Api', $this->lanPackage);
        }
        return $data;
    }

    /**
     * 获取国家码列表
     * @param lantype：语言类型
     */
    public function getCountryno(){
        $where = array(
            'lantype' => $this->lantype
        );
        $countryList = M("Country")->where($where)->select();

        //返回数据
        $data = array(
            'status' => 200,
            'message' => lan('200', 'Api', $this->lanPackage),
            'datalist' => $countryList
        );
        return $data;
    }

    /**
     * 验证用户名是否已经注册
     * @param phoneno：手机号
     * @param countryno：国家码
     */
    public function checkUsernameRegister($inputParams){
        $phoneno = $inputParams['phoneno'];
        $countryno = $inputParams['countryno'];

        $where = array(
            'username' => $phoneno,
            'countrycode' => $countryno
        );
        $result = M('Member')->where($where)->find();

        //返回结果
        if ($result) {
            $data['status'] = 400002;
            $data['message'] = lan('400002', 'Api', $this->lanPackage);
        } else {
            $data['status'] = 400004;
            $data['message'] = lan('400004', 'Api', $this->lanPackage);
        }
        return $data;
    }

    /**
     * 根据用户ID获取免费礼物数量
     * @param userId：用户ID
     */
    private function getFreeGiftcount($userId){
        //获取免费礼物最大配置
        $where = array(
            'key' => 'FREE_GIFT_MAX_COUNT',
            'lantype' => $this->lantype
        );
        $freegiftcount = M('Systemset')->where($where)->getField('value');
        //获取用户免费礼物数量
        $whereFreeGift = array(
            'userid' => $userId,
            'isused' => 0
        );
        $free_gift = M('freegift')->where($whereFreeGift)->order('userfreegiftid DESC')->find();
        $user_free_gift_count = (int)$free_gift['giftcount'];
        if($user_free_gift_count <= $freegiftcount){
            $freegiftcount = $user_free_gift_count;
        }
        //更新礼物数量为已使用
        if($free_gift){
            M('freegift')->where($whereFreeGift)->save(array('isused'=>1));
        }
        return (string)$freegiftcount;
    }
}