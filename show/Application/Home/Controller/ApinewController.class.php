<?php
namespace Home\Controller;
use Think\Controller;
use Think\Upload;
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

    public function _initialize(){
        $this->encryptDecryptKey = 'waashow-ShanRuoC';
        $this->encryptDecryptSignKey = 'ShanRuoCom';

        $this->lantype = I('post.lantype', '', 'trim') ? I('post.lantype', '', 'trim') : 'vi';
        $lanTypes = C('LAN_TYPE');
        if (!in_array($this->lantype, $lanTypes)) {
            $this->lantype = 'en';
        }
        
        $this->checkInputParam();   //数据解密，验证提交的参数
    }

    /**
     * APP入口，获取系统信息
     * @param devicetype：设备类型 0 安卓 1 iOS 2PC
     */
    public function getSystemInfo(){
        $devicetype = $this->inputParams['devicetype'];

        $data['status'] = 200;
        $data['message'] = lan('200', 'Home', $this->lantype);
        $data['datalist'] = array(
            'domainname'  => $this->getSystemInfoList('DOMAIN_NAME' , $this->lantype),
            'rtmppath'    => $this->getSystemInfoList('RTMP_PATH' , $this->lantype),
            'nodejspath'  => $this->getSystemInfoList('NODEJS_NEW_PATH' , $this->lantype),
            'loadimgpath' => $this->getSystemInfoList('LOAD_IMG_PATH' , $this->lantype),
            'editimgpath' => $this->getSystemInfoList('EDIT_IMG_PATH' , $this->lantype),
            'shutuptime'  => $this->getSystemInfoList('SHUTUP_TIME' , $this->lantype),
            'apppushpath'    => $this->getSystemInfoList('APP_PUSH_PATH' , $this->lantype),
            'showtopten'    => $this->getSystemInfoList('SHOW_TOP_TEN' , $this->lantype),
            'registeragreement'  => $this->getSystemInfoList('REGISTER_AGREEMNET_PATH' , $this->lantype),
            'rechargeagreement'  => $this->getSystemInfoList('RECHARGE_AGREEMNET_PATH' , $this->lantype),
            'emceesignagreement'  => $this->getSystemInfoList('EMCEE_SIGN_AGREEMNET_PATH' , $this->lantype),
            'allowshowuserlevel' => $this->getSystemInfoList('ALLOW_SHOW_USERLEVEL' , 'vi'),
            'allowshowtime' => $this->getSystemInfoList('ALLOW_SHOW_TIME' , 'vi'),
            'exchangeagreement' => $this->getSystemInfoList('EXCHANGE_AGREEMENT' , $this->lantype),
            'screenshotslimit' => $this->getSystemInfoList('SCREENSHOTS_LIMIT' , $this->lantype),
        );

        if ($devicetype == 0) { //安卓
            $data['datalist']['audiobitrate'] = $this->getSystemInfoList('ANDROID_AUDIO_BITRATE' , 'vi');
            $data['datalist']['videowidth'] = $this->getSystemInfoList('ANDROID_VIDEO_WIDTH' , 'vi');
            $data['datalist']['videoheight'] = $this->getSystemInfoList('ANDROID_VIDEO_HEIGHT' , 'vi');
            $data['datalist']['videofps'] = $this->getSystemInfoList('ANDROID_VIDEO_FPS' , 'vi');
            $data['datalist']['videobitrate'] = $this->getSystemInfoList('ANDROID_VIDEO_BITRATE' , 'vi');
            $data['datalist']['imgprocparam'] = $this->getSystemInfoList('ANDROID_IMG_PROC_PARAM' , 'vi');
            $data['datalist']['hidesportgame'] = $this->getSystemInfoList('ANDROID_HIDE_SOPRT_GAME' , 'vi');
            $data['datalist']['newsdkdistribute'] = $this->getSystemInfoList('ANDROID_NEW_SDK_DISTRIBUTE' , 'vi');
        } else {    //iOS
            $data['datalist']['audiobitrate'] = $this->getSystemInfoList('IOS_AUDIO_BITRATE' , 'vi');
            $data['datalist']['videowidth'] = $this->getSystemInfoList('IOS_VIDEO_WIDTH' , 'vi');
            $data['datalist']['videoheight'] = $this->getSystemInfoList('IOS_VIDEO_HEIGHT' , 'vi');
            $data['datalist']['videofps'] = $this->getSystemInfoList('IOS_VIDEO_FPS' , 'vi');
            $data['datalist']['videobitrate'] = $this->getSystemInfoList('IOS_VIDEO_BITRATE' , 'vi');
            $data['datalist']['imgprocparam'] = $this->getSystemInfoList('IOS_IMG_PROC_PARAM' , 'vi');
            $data['datalist']['hidesportgame'] = $this->getSystemInfoList('IOS_HIDE_SOPRT_GAME' , 'vi');
        }

        //获取版本相关信息
        $versioninfo = M('versioninfo')->where(array('lantype' => $this->lantype))->order('id DESC')->find();
        if ($devicetype == 0) {
            //安卓apk下载地址
            $android_download_link = '';
            $distributeid = $this->inputParams['distributeid'];   //应用市场渠道
            if ($distributeid) {
                $where_versionapk = array(
                    'versioninfoid' => $versioninfo['versioninfoid'],
                    'distributeid' => $distributeid,
                );
                $android_download_link = M('versionapk')->where($where_versionapk)->getField('download_link');
            }
            if (!$android_download_link) {
                $android_download_link = $versioninfo['android_download_link'];
            }
            $data['versioninfo'] = array(
                'android_new_version'  =>  $versioninfo['android_new_version'], //安卓最新版本
                'android_download_link'  =>  $android_download_link, //安卓下载链接
                'android_apk_size'  =>  $versioninfo['android_apk_size'], //安卓最新apk大小
                'android_new_code'  =>  (int)$versioninfo['android_new_code'], //安卓最新code
                'android_forced_upgrade_code'  =>  (int)$versioninfo['android_forced_upgrade_code'], //安卓强制升级code
                'android_released_time'  =>  $versioninfo['android_released_time'], //安卓发布时间
                'android_note'  =>  $versioninfo['android_note'], //安卓升级说明
            );
        } else {
            $data['versioninfo'] = array(
                'ios_new_version'  =>  (int)$versioninfo['ios_new_version'], //iOS最新版本
                'ios_forced_upgrade_version'  =>  (int)$versioninfo['ios_forced_upgrade_version'], //iOS强制升级版本
                'ios_download_link'  =>  $versioninfo['ios_download_link'], //iOS下载链接
                'ios_released_time'  =>  $versioninfo['ios_released_time'], //iOS发布时间
                'ios_note'  =>  $versioninfo['ios_note'], //iOS升级说明
            );
        }
        $this->responseData($data);
    }

    /**
     * 获取系统信息方法
     */
    private function getSystemInfoList($key,$lantype){
        $where = array(
            'key' => $key,
            'lantype' => $lantype
        );
        $dbSystemset = M('Systemset');
        $value = $dbSystemset->where($where)->getField('value');
        return $value;
    }

    /**
     * 修改头像
     * @param type：设1:表示修改大头像，其他值表示修改小头像
     */
   public function modifyHeadPic(){
       $this->checkUserToken(); //验证登录
       $parameterArray = array('userid', 'type');
       $this->validateParams($parameterArray); //验证参数是否缺少
       $type = $this->inputParams['type'] ? $this->inputParams['type'] : 0;
       $userid = $this->inputParams['userid'] ? $this->inputParams['userid'] : 0;

       if ($type == 1){
           $filePath = '/Uploads/HeadImg/268200/';
       }else{
           $filePath = '/Uploads/HeadImg/120120/';
       }
       //文件上传远程服务器
       $file = 'file';
       $fileName = date('YmdHis').'_'.$userid;
       $ftpFile = ftpFile($file, $filePath, $fileName);
       if($ftpFile['code'] != 200){
           $data['status'] = 16;
           $data['message'] = lan('HEAD_PIC_UPLOAD_FAILED', 'Home', $this->lantype);
           echo json_encode($data);exit;
       }
       $fileurl = $ftpFile['msg'];

       //上传成功保存数据
       $dbMember = M('Member');
       $userInfo = $dbMember->where(array('userid' => $userid))->find();
       if ($type == 1) {
           $editData['bigheadpic'] = $fileurl;
           $result = $dbMember->where(array('userid' => $userid))->save($editData);

           //保存成功删除老图片
           $oldbigheadpic = $userInfo['bigheadpic'];
           if ($result && $editData['bigheadpic'] != $oldbigheadpic) {
               ftpDelete($oldbigheadpic);  //删除老图片
           }
       } else {
           $editData['smallheadpic'] = $fileurl;
           $result = $dbMember->where(array('userid' => $userid))->save($editData);

           //保存成功删除老图片
           $oldsmallheadpic = $userInfo['smallheadpic'];
           if ($result && $editData['smallheadpic'] != $oldsmallheadpic && $oldsmallheadpic != '/Public/Public/Images/HeadImg/default.png') {
               ftpDelete($oldsmallheadpic);    //删除老图片
           }
       }

       //返回数据
       $data['status'] = 200;
       $data['message'] = lan('200', 'Home', $this->lantype);
       $data['headpicpath'] = $editData['smallheadpic'];
       $data['bigheadpic'] = $editData['bigheadpic'];
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

        $report['userid'] = $this->inputParams['userid'];
        $report['reporteduid'] = $this->inputParams['reporteduid'];
        $report['type'] = $this->inputParams['type'];
        $report['content'] = $this->inputParams['content'];
        $report['devicetype'] = $this->inputParams['devicetype'];

        $syswhere = array(
            'key' => 'SCREENSHOTS_TIME_INVERTAL',
            'lantype' => $this->lantype
        );
        $sysInfo = M('Systemset')->field('value')->where($syswhere)->find();
        $timeInvertal = $sysInfo['value'];

        //被举报主播直播设备类型
        $livetype = M('Emceeproperty')
            ->where(array('userid'=>$report['reporteduid']))
            ->getField('livetype');

        $db_Report = M('Report');
        if (!empty($_FILES) && $livetype != 2) {
            //判断是否上传截屏
            $ispic = $db_Report->where('reporteduid='.$report['reporteduid'].' AND pic!="" AND isprocess=0 AND TIMESTAMPDIFF(MINUTE,createtime,now())<'.$timeInvertal)->find();
            if (!$ispic) {
                //文件上传远程服务器
                $filePath = '/Uploads/Report/Pic/';
                $pic = array();
                foreach($_FILES as $k => $v){
                    $ftpFile = ftpFile($k, $filePath);
                    if($ftpFile['code'] == 200){
                        $pic[] = $v['msg'];
                    }
                }
                $report['pic'] = implode(",", $pic);
            }
        }else{
            //判断是否通知pc录屏
            $isvideo = $db_Report->where('reporteduid='.$report['reporteduid'].' AND video!="" AND isprocess=0 AND TIMESTAMPDIFF(MINUTE,createtime,now())<'.$timeInvertal)->find();
            if (!$isvideo) {
                $video = 'stream'.$report['reporteduid'].'_'.date('YmdHis');
                $sysmap = array(
                    'key' => 'RECORD_PATH',
                    'lantype' => $this->lantype
                );
                $systemset = M('Systemset')->field('value')->where($sysmap)->find();
                $report['video'] = 'rtmp://'.$systemset['value'].'/live/'.$video;
            }
        }


        $report['isprocess'] = 0;
        $liveinfo = M('Liverecord')->where('userid='.$report['reporteduid'])->order('liveid DESC')->find();
        if (empty($liveinfo['endtime']) || $liveinfo['laststarttime'] > $liveinfo['endtime']) {
            $report['liveid'] = $liveinfo['liveid'];
        }
        $report['createtime'] = date('Y-m-d H:i:s');

        //判断用户是否恶意举报
        $sql = 'SELECT TIMESTAMPDIFF(MINUTE,min(a.createtime),max(a.createtime)) as reporttime FROM (SELECT * FROM ws_report WHERE userid='.$report['userid'].' AND isprocess=1 AND isviolate=0 ORDER BY createtime LIMIT 10) AS a';
        $userreport = $db_Report->query($sql);
        $sqlreportcount = 'SELECT * FROM (SELECT *,count(reportid) AS reportcount FROM ws_report WHERE userid='.$report['userid'].' AND isprocess=1 AND isviolate=0 GROUP BY reporteduid) AS a WHERE a.reportcount>=10';
        $reportcount = $db_Report->query($sqlreportcount);
        if (($userreport['reporttime']<=10 && $userreport['reporttime']>0) || $reportcount) {
            $data['status'] = 200;//恶意举报,只提示成功,不记录
            $data['message'] = lan('200', 'Home', $this->lantype);
        }else{
            if (!empty($report['liveid'])) {
                $result = $db_Report->add($report);
            }

            if ($result) {
                $data['video'] = $video;
                $data['status'] = 200;
                $data['message'] = lan('200', 'Home', $this->lantype);
            } else {
                $data['status'] = -1;
                $data['message'] = lan('-1', 'Home', $this->lantype);
            }
        }
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
            $data['message'] = lan('400001', 'Home', $this->lantype);
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
            $data['message'] = lan('500', 'Home', $this->lantype);
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
                $data['message'] = lan('400', 'Home', $this->lantype);
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