<?php
namespace Api\Controller;
use Think\Controller;
/**
 * APP接口，1.3.3以后的版本使用
 *
 * 1、APP端接口统一进入本控制器
 * 2、函数：decrypt，对接口上传数据的解密，
 * 3、独立接收语言类型
 * 4、根据功能模块，调用指定控制器里的函数，并接收返回值
 * 5、函数：encrypt，进行数据的加密
 * 6、对接收的数据进行加密，并进行接口数据的返回：$this->ajaxReturn($json_data);
 *
 */
class ApinewController extends Controller {
    private $encryptDecryptKey; //加密解密约定的秘钥key值
    private $encryptDecryptSignKey; //加密解密约定验证签名的key值
    private $inputParams;   //提交的参数
    private $lantype;   //发起请求客户端的语言类型
    private $lanPackage;   //所使用的语言包文件

    public function _initialize(){
        $this->encryptDecryptKey = 'waashow-ShanRuoC';
        $this->encryptDecryptSignKey = 'ShanRuoCom';

        $this->lantype = I('post.lantype', '', 'trim') ? I('post.lantype', '', 'trim') : 'vi';
        $lanTypes = C('LAN_TYPE');
        if (!in_array($this->lantype, $lanTypes)) {
            $this->lantype = 'en';
        }

        $this->lanPackage = 'code_' . $this->lantype;
        $this->checkInputParam();   //数据解密，验证提交的参数
    }

    /**
     * 预留函数
     * 升级之后可以进行简单的测试
     * 必须在生产环境下测试的功能，可以使用此函数
     */
    public function index(){
        echo 'Apinew-'.date('Y-m-d H:i:s');exit;
    }

    /**
     * APP入口，获取系统信息
     * @param devicetype：设备类型 0 安卓 1 iOS 2PC
     */
    public function getSystemInfo(){
        $parameterArray = array('devicetype');
        $this->validateParams($parameterArray); //验证参数是否缺少
        $System = new SystemController();
        $data = $System->getSystemInfo($this->inputParams);
        $this->responseData($data);
    }

    /**
     * 用户注册
     * @param phoneno：手机号
     * @param password：密码
     * @param countryno：国家码
     * @param verifycode：验证码
     * @param appkey：密钥
     * @param lantype：语言类型
     * @param lastloginip：最后登录IP
     */
    public function doregister(){
        $parameterArray = array(
            'phoneno' , 'password' , 'countryno', 'verifycode', 'appkey', 'lastloginip'
        );
        $this->validateParams($parameterArray); //验证参数是否缺少
        $LoginRegister = new LoginRegisterController();
        $data = $LoginRegister->doRegister($this->inputParams);
        $this->responseData($data);
    }

    /**
     * 用户登录
     * @param phoneno：手机号
     * @param password：密码
     * @param countryno：国家码
     * @param lastloginip：最后登录IP
     */
    public function dologin(){
        $parameterArray = array(
            'phoneno' , 'password' , 'countryno', 'lastloginip'
        );
        $this->validateParams($parameterArray); //验证参数是否缺少
        $LoginRegister = new LoginRegisterController();
        $data = $LoginRegister->doLogin($this->inputParams);
        $this->responseData($data);
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
    public function thirdPartyLogin(){
        $parameterArray = array(
            'thirdparty' , 'tpuserid', 'tpusername', 'smallheadpic', 'token', 'devicetype', 'lastloginip'
        );
        $this->validateParams($parameterArray); //验证参数是否缺少
        $LoginRegister = new LoginRegisterController();
        $data = $LoginRegister->thirdPartyLogin($this->inputParams);
        $this->responseData($data);
    }

    /**
     * 验证用户登录状态
     * @param userid：用户userid
     * @param token：用户登录的token
     */
    public function checkUserLoginStatus(){
        $parameterArray = array(
            'userid', 'token'
        );
        $this->validateParams($parameterArray); //验证参数是否缺少
        $LoginRegister = new LoginRegisterController();
        $data = $LoginRegister->checkUserLoginStatus($this->inputParams);
        $this->responseData($data);
    }

    /**
     * 发送越南短信验证码
     * @param phoneno：手机号
     * @param countryno：国家码
     * @param lantype：语言类型
     */
    public function sendVietnamSms(){
        $parameterArray = array(
            'phoneno', 'countryno', 'lantype'
        );
        $this->validateParams($parameterArray); //验证参数是否缺少
        $LoginRegister = new LoginRegisterController();
        $data = $LoginRegister->sendVietnamSms($this->inputParams);
        $this->responseData($data);
    }

    /**
     * 修改密码
     * @param userid：登录用户userid
     * @param oldpwd：旧密码
     * @param newpwd：新密码
     * @param confirmpwd：确认密码
     * @param token：登录用户token值
     */
    public function modifyPassword(){
        $this->checkUserToken();    //验证登录信息
        $parameterArray = array(
            'userid', 'oldpwd','newpwd','confirmpwd','token'
        );
        $this->validateParams($parameterArray); //验证参数是否缺少
        $LoginRegister = new LoginRegisterController();
        $data = $LoginRegister->modifyPassword($this->inputParams);
        $this->responseData($data);
    }

    /**
     * 忘记密码
     * @param phoneno：手机号
     * @param password：密码
     * @param countryno：国家码
     * @param verifycode：验证码
     * @param appkey：密钥
     */
    public function forgetPassword(){
        $parameterArray = array(
            'phoneno' , 'password' , 'countryno', 'verifycode', 'appkey'
        );
        $this->validateParams($parameterArray); //验证参数是否缺少
        $LoginRegister = new LoginRegisterController();
        $data = $LoginRegister->forgetPassword($this->inputParams);
        $this->responseData($data);
    }

    /**
     * 获取国家码列表
     * @param lantype：语言类型
     */
    public function getCountryno(){
        $parameterArray = array('lantype');
        $this->validateParams($parameterArray); //验证参数是否缺少
        $LoginRegister = new LoginRegisterController();
        $data = $LoginRegister->getCountryno();
        $this->responseData($data);
    }

    /**
     * 验证用户名是否已经注册
     * @param phoneno：手机号
     * @param countryno：国家码
     */
    public function checkUsernameRegister(){
        $parameterArray = array(
            'phoneno', 'countryno'
        );
        $this->validateParams($parameterArray); //验证参数是否缺少
        $LoginRegister = new LoginRegisterController();
        $data = $LoginRegister->checkUsernameRegister($this->inputParams);
        $this->responseData($data);
    }

    /**
     * 开始直播
     * @param userid：登录用户userid
     * @param token：登录用户token值
     * @param roomno：房间号
     */
    public function createAPPLiveroom(){
        $this->checkUserToken();    //验证登录信息
        $parameterArray = array(
            'userid', 'roomno', 'token'
        );
        $this->validateParams($parameterArray); //验证参数是否缺少
        $OpenLive = new OpenLiveController();
        $data = $OpenLive->createAPPLiveroom($this->inputParams);
        $this->responseData($data);
    }

    /**
     * 停止直播
     * @param userid：登录用户userid
     * @param roomno：房间号
     */
    public function stopAPPLiveroom(){
        $parameterArray = array(
            'userid', 'roomno'
        );
        $this->validateParams($parameterArray); //验证参数是否缺少
        $OpenLive = new OpenLiveController();
        $data = $OpenLive->stopAPPLiveroom($this->inputParams);
        $this->responseData($data);
    }

    /**
     * 禁播操作接口
     * 获取禁播违规列表数据
     */
    public function banAction(){
        $OpenLive = new OpenLiveController();
        $data = $OpenLive->banAction($this->inputParams);
        $this->responseData($data);
    }

    /**
     * 禁播
     * @param emceeuserid：主播ID
     * @param type：违规类型
     * @param content：违规说明
     * @param violatelevel：违规等级
     * @param bantime：禁播时长
     * @param punishmoney：处罚秀币
     * @param userid：处理人(用户id)
     */
    public function doBan(){
        $this->checkUserToken();    //验证登录信息
        $parameterArray = array(
            'emceeuserid','type','violatelevel','bantime','punishmoney','userid'
        );
        $this->validateParams($parameterArray); //验证参数是否缺少
        $OpenLive = new OpenLiveController();
        $data = $OpenLive->doBan($this->inputParams);
        $this->responseData($data);
    }

    /**
     * 进入直播间
     * 获取APP拉流端地址，根据livetype 0 安卓 1IOS 2PC获取不同的拉流地址
     * @param userid：登录用户ID
     * @param token：登录用户token
     * @param emceeuserid：主播ID
     * @param livetype：直播类型 0：安卓直播 1：IOS直播 2：PC直播
     */
    public function enterToLiveRoom(){
        $this->checkUserToken();    //验证登录信息
        $parameterArray = array(
            'userid', 'emceeuserid', 'livetype'
        );
        $this->validateParams($parameterArray); //验证参数是否缺少
        $OpenLive = new OpenLiveController();
        $version = I('post.version',100,'trim');
        if (135 > $version) {
            $data = $OpenLive->enterToLiveRoom133($this->inputParams);
        } else {
            $data = $OpenLive->enterToLiveRoom($this->inputParams);
        }
        $this->responseData($data);
    }

    /**
     * 获取我的守护
     * @param pageno：页码，默认从0开始，表示第一页
     * @param pagesize：每页返回记录数
     */
    public function getMyGuard(){
        $this->checkUserToken();    //验证登录信息
        $parameterArray = array(
            'pageno', 'pagesize'
        );
        $this->validateParams($parameterArray); //验证参数是否缺少
        $Liveroom = new LiveroomController();
        $data = $Liveroom->getMyGuard($this->inputParams);
        $this->responseData($data);
    }

    /**
     * 获取主播的守护和沙发
     * @param emceeuserid：主播userid
     */
    public function getGuardAndSeat(){
        $parameterArray = array('emceeuserid');
        $this->validateParams($parameterArray); //验证参数是否缺少
        $Liveroom = new LiveroomController();
        $data = $Liveroom->getGuardAndSeat($this->inputParams);
        $this->responseData($data);
    }

    /**
     * 获取礼物列表和用户余额
     * @param userid：用户ID
     */
    public function getSaleGiftsAndBalance(){
        $this->checkUserToken();    //验证登录信息
        $Liveroom = new LiveroomController();
        $version = I('post.version',100,'trim');
        if ($version < 136) {
            $data = $Liveroom->getSaleGiftsAndBalance136($this->inputParams);
        } else {
            $data = $Liveroom->getSaleGiftsAndBalance($this->inputParams);
        }
        $this->responseData($data);
    }

    /**
     * 拉黑用户
     * @param userid：登录用户ID
     * @param forbiduserid：被拉黑用户id
     */
    public function addForbid(){
        $this->checkUserToken();    //验证登录信息
        $parameterArray = array(
            'userid', 'forbiduserid'
        );
        $this->validateParams($parameterArray); //验证参数是否缺少
        $Liveroom = new LiveroomController();
        $data = $Liveroom->addForbid($this->inputParams);
        $this->responseData($data);
    }

    /**
     * 禁言
     * @param userid：当前登录用户ID
     * @param forbiduserid：被禁言的用户ID
     * @param emceeuserid：当前房间的主播ID
     */
    public function recordShutup(){
        $this->checkUserToken();    //验证登录信息
        $parameterArray = array(
            'userid', 'forbidenuserid', 'emceeuserid'
        );
        $this->validateParams($parameterArray); //验证参数是否缺少
        $Liveroom = new LiveroomController();
        $data = $Liveroom->recordShutup($this->inputParams);
        $this->responseData($data);
    }

    /**
     * 踢人
     * @param userid：当前登录用户ID
     * @param kickeduserid：被踢的用户ID
     * @param emceeuserid：当前房间的主播ID
     */
    public function recordKick(){
        $this->checkUserToken();    //验证登录信息
        $parameterArray = array(
            'userid', 'kickeduserid', 'emceeuserid'
        );
        $this->validateParams($parameterArray); //验证参数是否缺少
        $Liveroom = new LiveroomController();
        $data = $Liveroom->recordKick($this->inputParams);
        $this->responseData($data);
    }

    /**
     * 添加/取消 关注
     * @param userid：当前登录用户ID
     * @param emceeuserid：主播ID
     * @param type：操作类型：0取消关注、1添加关注
     */
    public function addOrDelFriend(){
        $this->checkUserToken();    //验证登录信息
        $parameterArray = array(
            'userid', 'emceeuserid', 'type'
        );
        $this->validateParams($parameterArray); //验证参数是否缺少
        $Liveroom = new LiveroomController();
        $data = $Liveroom->addOrDelFriend($this->inputParams);
        $this->responseData($data);
    }

    /**
     * 验证用户是否已关注
     * @param userid：当前登录用户ID
     * @param emceeuserid：主播ID
     */
    public function checkIsFriend(){
        $this->checkUserToken();    //验证登录信息
        $parameterArray = array(
            'userid', 'emceeuserid'
        );
        $this->validateParams($parameterArray); //验证参数是否缺少
        $Liveroom = new LiveroomController();
        $data = $Liveroom->checkIsFriend($this->inputParams);
        $this->responseData($data);
    }

    /**
     * 验证用户是否被禁言
     * @param userid：当前登录用户ID
     * @param emceeuserid：主播ID
     */
    public function checkIsShutup(){
        $this->checkUserToken();    //验证登录信息
        $parameterArray = array(
            'userid', 'emceeuserid'
        );
        $this->validateParams($parameterArray); //验证参数是否缺少
        $Liveroom = new LiveroomController();
        $data = $Liveroom->checkIsShutup($this->inputParams);
        $this->responseData($data);
    }

    /**
     * 验证用户是否被踢出
     * @param userid：当前登录用户ID
     * @param emceeuserid：主播ID
     */
    public function checkIsKick(){
        $this->checkUserToken();    //验证登录信息
        $parameterArray = array(
            'userid', 'emceeuserid'
        );
        $this->validateParams($parameterArray); //验证参数是否缺少
        $Liveroom = new LiveroomController();
        $data = $Liveroom->checkIsKick($this->inputParams);
        $this->responseData($data);
    }

    /**
     * 添加用户分享记录
     * @param userid：当前登录用户ID
     * @param emceeuserid：主播ID
     * @param sharetype：分享类型 0:直播间分享 1：视频分享
     * @param shareplat：分享平台 1：Facebook 2：Google 3：Twitter 4 : zing
     * @param devicetype：设备类型 0 安卓 1 iOS 2PC
     */
    public function addSharerecord(){
        $this->checkUserToken();    //验证登录信息
        $parameterArray = array(
            'userid', 'emceeuserid', 'sharetype', 'shareplat', 'devicetype'
        );
        $this->validateParams($parameterArray); //验证参数是否缺少
        $Liveroom = new LiveroomController();
        $data = $Liveroom->addSharerecord($this->inputParams);
        $this->responseData($data);
    }

    /**
     * 添加观看记录
     * @param userid：当前登录用户ID
     * @param emceeuserid：主播ID
     * @param type：操作类型 1、进入房间 0、退出房间
     */
    public function addSeehistory(){
        $parameterArray = array(
            'userid', 'emceeuserid','type'
        );
        $this->validateParams($parameterArray); //验证参数是否缺少
        $Liveroom = new LiveroomController();
        $data = $Liveroom->addSeehistory($this->inputParams);
        $this->responseData($data);
    }

    /**
     * 添加用户反馈
     * @param userid：当前登录用户ID
     * @param devicetype：设备类型 0 安卓 1 iOS 2PC
     * @param fbcontent：反馈内容
     */
    public function addFeedback(){
        $this->checkUserToken();    //验证登录信息
        $parameterArray = array(
            'userid', 'devicetype', 'fbcontent',
        );
        $this->validateParams($parameterArray); //验证参数是否缺少
        $Liveroom = new LiveroomController();
        $data = $Liveroom->addFeedback($this->inputParams);
        $this->responseData($data);
    }

    /**
     * 添加用户举报
     * @param userid：登录用户ID
     * @param reporteduid：被举报用户ID
     * @param devicetype：设备类型 0 安卓 1 iOS 2PC
     * @param type：举报类型
     * @param content：举报内容
     */
    public function addReport(){
        $this->checkUserToken(); //验证登录
        $parameterArray = array('userid', 'reporteduid', 'devicetype');
        $this->validateParams($parameterArray); //验证参数是否缺少
        $Liveroom = new LiveroomController();
        $data = $Liveroom->addReport($this->inputParams);
        $this->responseData($data);
    }

    /**
     * 获取充值渠道
     * @param userid：当前登录用户ID
     * @param token：用户token值
     */
    public function getRechargeChannels(){
        $this->checkUserToken();    //验证登录信息
        $Recharge = new RechargeController();
        $version = I('post.version',100,'trim');
        if ($version < 135) {
            $data = $Recharge->getRechargeChannels135($this->inputParams);
        } else {
            $data = $Recharge->getRechargeChannels($this->inputParams);
        }
        $this->responseData($data);
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
    public function userRecharge(){
        $parameterArray = array(
            'userid', 'targetid', 'type', 'orderno', 'rechargedefid', 'sellerid', 'status', 'channelid', 'devicetype', 'rechargetype', 'deviceid', 'requestid', 'applereceipt'
        );
        $this->validateParams($parameterArray); //验证参数是否缺少
        $Recharge = new RechargeController();
        $data = $Recharge->userRecharge($this->inputParams);
        $this->responseData($data);
    }

    /**
     * 充值日志
     * @param userid：当前登录用户ID
     * @param deviceid：设备唯一号
     */
    public function rechargeLog(){
        $parameterArray = array(
            'userid', 'deviceid'
        );
        $this->validateParams($parameterArray); //验证参数是否缺少
        $Recharge = new RechargeController();
        $data = $Recharge->rechargeLog($this->inputParams);
        $this->responseData($data);
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
     */
    public function rechbycallingcard(){
        $parameterArray = array(
            'userid', 'type', 'channelid', 'sellerid', 'devicetype', 'rechargetype', 'pin', 'serial'
        );
        $this->validateParams($parameterArray); //验证参数是否缺少
        $Recharge = new RechargeController();
        $data = $Recharge->rechbycallingcard($this->inputParams);
        $this->responseData($data);
    }

    /**
     * 通过 LocalBank 充值
     * @param userid：当前登录用户ID
     * @param amount：localmoney，本地货币金额
     * @param showamount：rechargeamount，充值秀币金额
     * @param channelid：充值渠道ID
     * @param devicetype：设备类型 0 安卓 1 iOS 2PC
     * @param rechargetype：充值类型 0 充值卡 1游戏卡 2 ATM DEBIT 3 VISA MATER 4 SMS+ 5 INTERNET BANK 6 AGENT 7 Applestore 8 Paypal
     */
    public function rechargeByBank(){
        $parameterArray = array(
            'userid', 'amount', 'showamount', 'channelid', 'devicetype', 'rechargetype'
        );
        $this->validateParams($parameterArray); //验证参数是否缺少
        $Recharge = new RechargeController();
        $data = $Recharge->rechargeByBank($this->inputParams);
        $this->responseData($data);
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
     */
    public function rechargeByVisa(){
        $parameterArray = array(
            'userid', 'amount', 'showamount', 'channelid', 'sellerid', 'devicetype', 'rechargetype'
        );
        $this->validateParams($parameterArray); //验证参数是否缺少
        $Recharge = new RechargeController();
        $data = $Recharge->rechargeByVisa($this->inputParams);
        $this->responseData($data);
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
    public function rechargeByAlipay(){
        $parameterArray = array(
            'userid', 'amount', 'showamount', 'channelid', 'sellerid', 'devicetype', 'rechargetype'
        );
        $this->validateParams($parameterArray); //验证参数是否缺少
        $Recharge = new RechargeController();
        $data = $Recharge->rechargeByAlipay($this->inputParams);
        $this->responseData($data);
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
        $parameterArray = array(
            'userid', 'rechargedefid', 'rechargetype', 'devicetype', 'distributeid'
        );
        $this->validateParams($parameterArray); //验证参数是否缺少
        $Recharge = new RechargeController();
        $data = $Recharge->rechargeByPaypal($this->inputParams);
        $this->responseData($data);
    }

    /**
     * 获取APP首页主播
     * 根据type获取主播列表(热门、最新、关注)
     * @param userid: 用户userid
     * @param type: 首页列表类型    
     * @param pageno: 页码
     * @param pagesize: 每页长度      
     */ 
    public function getIndexEmcees(){
        $parameterArray = array(
            'userid', 'type', 'pageno', 'pagesize'
        );
        $this->validateParams($parameterArray); //验证参数是否缺少
        $System = new SystemController();
        $data = $System->getIndexEmcees($this->inputParams);
        $this->responseData($data);        
    }

    /**
     * 获取发现页排行榜列表
     */
    public function getTopListRank(){
        $System = new SystemController();
        $version = I('post.version',100,'trim');
        if (135 == $version) {
            $data = $System->getTopListRank135($this->inputParams);
        } else {
            $data = $System->getTopListRank($this->inputParams);
        }        
        $this->responseData($data);
    }

    /**
     * 获取发现页排行榜数据
     * @param toplist_type: 排行榜类型  
     * @param limit: 数据长度
     * @param range: 时间范围     
     */
    public function getTopList(){
        $parameterArray = array(
            'toplist_type', 'limit', 'range'
        );
        $this->validateParams($parameterArray); //验证参数是否缺少
        $version = I('post.version',100,'trim');
        if (135 > $version) {
            $System = new System134Controller();
        } else {
            $System = new SystemController();
        }
        $data = $System->getTopList($this->inputParams);
        $this->responseData($data);
    }

    /**
     * 搜索主播
     * 根据昵称、房间号搜索主播
     * @param nickname: 主播昵称    
     * @param roomno: 主播房间号     
     * @param pageno: 页码
     * @param pagesize: 每页长度       
     */
    public function searchEmcee(){
        $parameterArray = array(
            'nickname', 'roomno', 'pageno', 'pagesize'
        );
        $this->validateParams($parameterArray); //验证参数是否缺少
        $System = new SystemController();
        $data = $System->searchEmcee($this->inputParams);
        $this->responseData($data);
    }

    /**
     * 热搜推荐
     * 获取热门搜索的主播
     */
    public function topSearchEmcee(){
        $System = new SystemController();
        $data = $System->topSearchEmcee($this->inputParams);
        $this->responseData($data);
    }    

    /**
     * 附近主播
     * 根据经纬度查询附近主播
     * @param longitude: 经度
     * @param latitude: 纬度     
     * @param pageno: 页码
     * @param pagesize: 每页长度      
     */
    public function getNearbyEmcces(){
        $parameterArray = array(
            'longitude', 'latitude', 'pageno', 'pagesize'
        );
        $this->validateParams($parameterArray); //验证参数是否缺少
        $System = new SystemController();
        $data = $System->getNearbyEmcces($this->inputParams);
        $this->responseData($data);        
    }

    /**
     * 获取轮播图
     * 获取APP当前语言下的所有轮播图和活动
     * @param devicetype: 设备类型
     */
    public function getRollpic(){
        $parameterArray = array('devicetype');
        $this->validateParams($parameterArray); //验证参数是否缺少
        $System = new SystemController();
        $data = $System->getRollpic($this->inputParams);
        $this->responseData($data);  
    }

    /**
     * 获取APP商城信息
     * @param lantype：语言类型
     * @param devicetype: 设备类型 0 安卓 1 iOS
     * @param cateid: 菜单类别id
     * @param userid: 用户userid
     * @param niceno: 要搜索的靓号（非必传）
     */
    public function getMallInformation(){
        $parameterArray = array(
            'lantype', 'devicetype', 'cateid', 'userid'
        );
        $this->validateParams($parameterArray); //验证参数是否缺少
        $System = new SystemController();
        $data = $System->getMallInformation($this->inputParams);
        $this->responseData($data);  
    }

    /**
     * 获取我的个人信息
     * @param userid: 用户userid     
     */ 
    public function getMyInformation(){
        $this->checkUserToken();    //验证登录信息
        $User = new UserController();
        $version = I('post.version',100,'trim');
        if (133 == $version) {
            $data = $User->getMyInformation133($this->inputParams);
        } else {
            $data = $User->getMyInformation($this->inputParams);
        }
        $this->responseData($data);        
    }

    /**
     * 根据userid获取用户信息
     * @param userid: 用户userid     
     */ 
    public function getUserInfoByUserid(){
        $this->checkUserToken();    //验证登录信息
        $User = new UserController();
        $data = $User->getUserInfoByUserid($this->inputParams);
        $this->responseData($data);  
    } 

    /**
     * 获取特权信息
     * @param type: 类型, 0 VIP 1 守护
     * @param ownerid: 拥有者的id, 如：vipid,守护id    
     */ 
    public function getPrivileges(){
        $parameterArray = array('type', 'ownerid');    //定义需要验证的参数
        $this->validateParams($parameterArray); //验证参数是否缺少
        $User = new UserController();
        $data = $User->getPrivileges($this->inputParams);
        $this->responseData($data);  
    }        

    /**
     * 获取分享信息
     * @param userid: 用户userid   
     * @param emceeuserid: 主播userid      
     */
    public function getShareInfo(){
        $parameterArray = array('userid', 'emceeuserid');    //定义需要验证的参数
        $this->validateParams($parameterArray); //验证参数是否缺少
        $User = new UserController();
        $data = $User->getShareInfo($this->inputParams);
        $this->responseData($data);         
    }

    /**
     * 获取用户所参与的任务
     * @param userid: 用户userid
     * @param pageno: 页码
     * @param pagesize: 每页长度        
     */
    public function getMyTasks(){
        $this->checkUserToken();    //验证登录信息
        $parameterArray = array('pageno', 'pagesize');    //定义需要验证的参数
        $this->validateParams($parameterArray); //验证参数是否缺少        
        $User = new UserController();
        $data = $User->getMyTasks($this->inputParams);
        $this->responseData($data);        
    }

    /**
     * 获取用户VIP信息
     * @param userid: 用户userid     
     */
    public function getMyVipinfos(){
        $this->checkUserToken();    //验证登录信息
        $User = new UserController();
        $data = $User->getMyVipinfos($this->inputParams);
        $this->responseData($data);         
    }

    /**
     * 获取用户消息记录
     * @param userid: 用户userid   
     * @param pageno: 页码
     * @param pagesize: 每页长度
     * @param lantype: 语言类型
     */
    public function getMyMessages(){
        $this->checkUserToken();    //验证登录信息
        $parameterArray = array('pageno', 'pagesize', 'lantype');    //定义需要验证的参数
        $this->validateParams($parameterArray); //验证参数是否缺少  
        $User = new UserController();
        $data = $User->getMyMessages($this->inputParams);
        $this->responseData($data);         
    }    

    /**
     * 获取用户关注列表
     * @param userid: 用户userid
     * @param pageno: 页码
     * @param pagesize: 每页长度      
     */
    public function getFriendEmcees(){
        $this->checkUserToken();    //验证登录信息
        $parameterArray = array('pageno', 'pagesize');    //定义需要验证的参数
        $this->validateParams($parameterArray); //验证参数是否缺少  
        $User = new UserController();
        $data = $User->getFriendEmcees($this->inputParams);
        $this->responseData($data);         
    }

    /**
     * 获取用户关注主播tags列表
     * @param userid: 用户userid
     */
    public function getFriendEmceeTags(){
        $this->checkUserToken();    //验证登录信息
        $parameterArray = array('userid');    //定义需要验证的参数
        $this->validateParams($parameterArray); //验证参数是否缺少
        $User = new UserController();
        $data = $User->getFriendEmceeTags($this->inputParams);
        $this->responseData($data);
    }

    /**
     * 获取用户家族信息
     * @param userid: 用户userid
     */
    public function getMyFamily(){
        $this->checkUserToken();    //验证登录信息
        $User = new UserController();
        $data = $User->getMyFamily($this->inputParams);
        $this->responseData($data);         
    }

    /**
     * 获取用户观看历史
     * @param userid: 用户userid
     * @param pageno: 页码
     * @param pagesize: 每页长度     
     */
    public function getMySeeHistory(){
        $this->checkUserToken();    //验证登录信息
        $parameterArray = array('pageno', 'pagesize');    //定义需要验证的参数
        $this->validateParams($parameterArray); //验证参数是否缺少  
        $User = new UserController();
        $data = $User->getMySeeHistory($this->inputParams);
        $this->responseData($data);        
    }        

    /**
     * 获取用户的固定资产例如座驾信息
     * @param userid: 用户userid
     * @param pageno: 页码
     * @param pagesize: 每页长度 
     */
    public function getMyEquipments(){
        $this->checkUserToken();    //验证登录信息
        $parameterArray = array('pageno', 'pagesize');    //定义需要验证的参数
        $this->validateParams($parameterArray); //验证参数是否缺少  
        $User = new UserController();
        $data = $User->getMyEquipments($this->inputParams);
        $this->responseData($data);         
    }

    /**
     * 获取用户消费记录
     * @param userid: 用户userid
     * @param pageno: 页码
     * @param pagesize: 每页长度 
     */
    public function getBuyRecord(){
        $this->checkUserToken();    //验证登录信息
        $parameterArray = array('pageno', 'pagesize');    //定义需要验证的参数
        $this->validateParams($parameterArray); //验证参数是否缺少  
        $User = new UserController();
        $data = $User->getBuyRecord($this->inputParams);
        $this->responseData($data);
    }

    /**
     * 获取用户充值记录
     * @param userid: 用户userid
     * @param pageno: 页码
     * @param pagesize: 每页长度 
     */    
    public function getRechargeRecord(){
        $this->checkUserToken();    //验证登录信息
        $parameterArray = array('pageno', 'pagesize');    //定义需要验证的参数
        $this->validateParams($parameterArray); //验证参数是否缺少  
        $User = new UserController();
        $data = $User->getRechargeRecord($this->inputParams);
        $this->responseData($data);
    }

    /**
     * 修改用户的座驾
     * @param userid: 用户userid     
     * @param newequipid: 新座驾id
     * @param oldequipid: 原座驾id
     */
    public function modifyUseEquipment(){
        $this->checkUserToken();    //验证登录信息
        $parameterArray = array('newequipid', 'oldequipid');    //定义需要验证的参数
        $this->validateParams($parameterArray); //验证参数是否缺少  
        $User = new UserController();
        $data = $User->modifyUseEquipment($this->inputParams);
        $this->responseData($data);
    }

    /**
     * 修改用户信息
     * @param userid: 用户userid     
     * @param nickname: 昵称
     * @param sex: 性别
     * @param birthday: 生日 
     */
    public function modifyUserInfo(){
        $this->checkUserToken();    //验证登录信息
        $parameterArray = array('userid', 'nickname', 'sex', 'birthday');  //定义需要验证的参数
        $this->validateParams($parameterArray); //验证参数是否缺少  
        $User = new UserController();
        $data = $User->modifyUserInfo($this->inputParams);
        $this->responseData($data);
    }

    /**
     * 修改用户头像
     * @param userid: 用户userid
     * @param type：设1:表示修改大头像，其他值表示修改小头像
     */
    public function modifyHeadPic(){
        $this->checkUserToken(); //验证登录
        $parameterArray = array('userid', 'type');
        $this->validateParams($parameterArray); //验证参数是否缺少
        $User = new UserController();
        $data = $User->modifyHeadPic($this->inputParams);
        $this->responseData($data);
    }

    /**
     * 删除消息,或将消息设置为已读
     * @param userid: 用户userid     
     * @param messageids: 消息id
     * @param type: 操作类型 0 删除 1 设为已读     
     */
    public function delOrReadMessage(){
        $this->checkUserToken();    //验证登录信息
        $parameterArray = array('userid', 'messageids', 'type');  //定义需要验证的参数
        $this->validateParams($parameterArray); //验证参数是否缺少  
        $User = new UserController();
        $data = $User->delOrReadMessage($this->inputParams);
        $this->responseData($data);        
    }

    /**
     * 获取用户余额
     * @param userid: 用户userid     
     */
    public function getUserBalance(){
        $this->checkUserToken();    //验证登录信息
        $parameterArray = array('userid');  //定义需要验证的参数
        $this->validateParams($parameterArray); //验证参数是否缺少  
        $User = new UserController();
        $data = $User->getUserBalance($this->inputParams);
        $this->responseData($data);
    }

    /**
     * 购买VIP
     * @param userid: 用户userid     
     * @param vipid: vipid
     * @param duration: 时长 月
     */
    public function buyVip(){
        $this->checkUserToken();    //验证登录信息
        $parameterArray = array('userid', 'vipid', 'duration');  //定义需要验证的参数
        $this->validateParams($parameterArray); //验证参数是否缺少  
        $Spend = new SpendController();
        $data = $Spend->buyVip($this->inputParams);
        $this->responseData($data);         
    }

    /**
     * 购买座驾
     * @param userid: 用户userid     
     * @param comid: 座驾id
     * @param duration: 时长 月
     */
    public function buyEquipment(){
        $this->checkUserToken();    //验证登录信息
        $parameterArray = array('userid', 'comid', 'duration');  //定义需要验证的参数
        $this->validateParams($parameterArray); //验证参数是否缺少  
        $Spend = new SpendController();
        $data = $Spend->buyEquipment($this->inputParams);
        $this->responseData($data); 
    }

    /**
     * 购买靓号
     * @param userid: 用户userid     
     * @param niceno: 靓号
     * @param duration: 时长 月
     */
    public function buyNiceno(){
        $this->checkUserToken();    //验证登录信息
        $parameterArray = array('userid', 'niceno', 'duration');  //定义需要验证的参数
        $this->validateParams($parameterArray); //验证参数是否缺少  
        $Spend = new SpendController();
        $data = $Spend->buyNiceno($this->inputParams);
        $this->responseData($data); 
    }

    /**
     * 购买守护
     * @param userid: 用户userid     
     * @param emceeuserid: 主播userid     
     * @param guardid: 守护id
     * @param gdduration: 守护时长 月
     * @param price: 总金额 
     */
    public function buyGuard(){
        $this->checkUserToken();    //验证登录信息
        $parameterArray = array('userid', 'emceeuserid', 'guardid', 'gdduration', 'price');  //定义需要验证的参数
        $this->validateParams($parameterArray); //验证参数是否缺少  
        $Spend = new SpendController();
        $data = $Spend->buyGuard($this->inputParams);
        $this->responseData($data); 
    }

    /**
     * 购买守护
     * @param emceeuserid: 主播userid     
     * @param seatseqid: 座位序列ID   
     * @param userid: 坐沙发用户id
     * @param price: 总金额 
     */
    public function buySeat(){
        $this->checkUserToken();    //验证登录信息
        $parameterArray = array('emceeuserid', 'seatseqid', 'userid', 'price');  //定义需要验证的参数
        $this->validateParams($parameterArray); //验证参数是否缺少  
        $Spend = new SpendController();
        $data = $Spend->buySeat($this->inputParams);
        $this->responseData($data); 
    }

    /**
     * 赠送礼物
     * @param userid: 用户userid     
     * @param emceeuserid: 主播userid
     * @param giftid: 礼物id
     * @param giftcount: 礼物数量 
     */
    public function sendGift(){
        $this->checkUserToken();    //验证登录信息
        $parameterArray = array('userid', 'emceeuserid', 'giftid', 'giftcount');  //定义需要验证的参数
        $this->validateParams($parameterArray); //验证参数是否缺少  
        $Spend = new SpendController();
        $data = $Spend->sendGift($this->inputParams);
        $this->responseData($data); 
    }

    /**
     * 赠送免费礼物
     * @param userid: 用户userid
     * @param emceeuserid: 主播userid
     * @param giftid: 礼物id
     * @param giftcount: 礼物数量
     */
    public function sendFreeGift()
    {
        $this->checkUserToken();    //验证登录信息
        $parameterArray = array('userid', 'emceeuserid', 'giftid', 'giftcount');  //定义需要验证的参数
        $this->validateParams($parameterArray); //验证参数是否缺少
        $Spend = new SpendController();
        $data = $Spend->sendFreeGift($this->inputParams);
        $this->responseData($data);
    }

    /**
     * 发送弹幕
     * @param userid: 用户userid
     * @param emceeuserid: 主播userid
     */
    public function sendFlyScreen(){
        $this->checkUserToken();    //验证登录信息
        $parameterArray = array('userid', 'emceeuserid');  //定义需要验证的参数
        $this->validateParams($parameterArray); //验证参数是否缺少
        $Spend = new SpendController();
        $data = $Spend->sendFlyScreen($this->inputParams);
        $this->responseData($data);
    }

    /**
     * 获取过滤的脏话列表
     */
    public function getFilterWords(){
        $System = new SystemController();
        $data = $System->getFilterWords($this->inputParams);
        $this->responseData($data);
    }

    /**
     * 验证用户token
     * @param userid：登录用户的userid
     * @param token：登录用户的token值
     */
    private function checkUserToken(){
        $userid = $this->inputParams['userid'];
        $token = $this->inputParams['token'];
        $queryUserid = array(
            'userid' => $userid
        );
        $userInfo = M('Member')->where($queryUserid)->find();
        if (empty($token) || empty($userInfo) || $token != $userInfo['token']) {
            $data['status'] = 400001;
            $data['message'] = lan('400001', 'Api', $this->lanPackage);
            $this->responseData($data);
        }
    }

    /**
     * 数据解密，验证提交的参数
     * @param $inputParams:解密的用户信息，包含userid和token
     * @return $isLogin:是否登录，true/false
     */
    private function checkInputParam(){
        $inputParams = $this->decrypt(I('post.param', '', 'trim')); //数据解密
        $auth = $inputParams['auth'];
        $md5Str = md5($inputParams['key'] . $this->encryptDecryptSignKey);
        if ($auth && $md5Str && $auth == $md5Str){
            $this->inputParams = $inputParams;
        } else {
            $data['status'] = 500;
            $data['message'] = lan('500', 'Api', $this->lanPackage);
            $this->responseData($data);
        }
    }

    /**
     * 验证是否缺少参数
     * @param $parameterArray：需要验证的字段，一维数组
     */
    private function validateParams($parameterArray) {
        foreach ($parameterArray as $key) {
            if (!isset($this->inputParams[$key])) {
                $data['status'] = 400;
                $data['message'] = lan('400', 'Api', $this->lanPackage);
                $this->responseData($data);
            }
        }
    }

    /**
     * 接口返回数据，统一调用该函数
     * 响应接口请求，对返回的数据进行统一的加密处理
     * @param $data：响应请求，要返回会的数据
     * @return $responseData：返回数据进行加密处理后的值
     */
    private function responseData($data){
        $responseData = $this->encrypt($data);
        $this->ajaxReturn(array($responseData),'JSON');
    }

    /**
     * 数据加密
     * 对要返回的数据进行加密，并返回加密后的数据
     * @param $data：需要加密的数据,array 或 string
     * @return $string：返回加密后的数据，string类型
     */
    private  function encrypt($data){
        $data = json_encode($data);
        $size = mcrypt_get_block_size(MCRYPT_RIJNDAEL_128, MCRYPT_MODE_ECB);
        $pad = $size - (strlen($data) % $size);
        $data = $data . str_repeat(chr($pad), $pad);
        $td = mcrypt_module_open(MCRYPT_RIJNDAEL_128, '', MCRYPT_MODE_ECB, '');
        $iv = mcrypt_create_iv (mcrypt_enc_get_iv_size($td), MCRYPT_RAND);
        mcrypt_generic_init($td, $this->encryptDecryptKey, $iv);
        $string = mcrypt_generic($td, $data);
        mcrypt_generic_deinit($td);
        mcrypt_module_close($td);
        $string = base64_encode($string);
        return $string;
    }

    /**
     * 数据解密
     * 对接收的数据进行解密，并返回解密后的数据
     * @param $string：需要解密的数据，string类型
     * @return $data：返回解密后的数据，array 或 string
     */
    private  function decrypt($string){
        $data = mcrypt_decrypt(
            MCRYPT_RIJNDAEL_128,
            $this->encryptDecryptKey,
            base64_decode($string),
            MCRYPT_MODE_ECB
        );
        $dec_s = strlen($data);
        $padding = ord($data[$dec_s-1]);
        $data = substr($data, 0, -$padding);
        $data = json_decode($data,true);
        return $data;
    }
}