<?php
namespace Home\Controller;

use Think\Exception;
use Think\Model;

class LoginController extends CommonController {
    
	/*
	** 方法作用：登录
	** 参数1：[无]
	** 返回值：[无]
	** 备注：[无]
	 */
	public function index() {
		if (IS_POST) {
            
            $paraArray = array(
                'username', 'password' , 'countryno'
            );
            
            $this->checkParameter($paraArray);
            
            $db_member = D('Member');
            $username = I('username', '', 'trim');
            $countryno = I('countryno', '', 'trim');            
            
            $field = array(
                'userid', 'userno', 'username', 'roomno', 'niceno', 'familyid', 'nickname','userlevel', 'province', 
                'city', 'smallheadpic', 'bigheadpic','lastlogintime', 'lastloginip','isemcee', 'isvirtual', 
                'isvip', 'usertype', 'password', 'salt','token','countrycode','status'
            );
            
            // if($countryno == '84' && substr($username, 0, 1) != "0"){
            //     $username = "0" . $username;
            // }
            if (empty($username)) {
                $errorInfo = array(
                    'status' => 0,
                    'message' => lan("INPUT_YOUR_USERNAME", "Family"),
                );
                echo json_encode($errorInfo);  
                die;
            }
            
            $where = array('username' => $username);
            $userinfo = $db_member->where($where)->field($field)->find();
            
            //用户名是否存在且国家码是否匹配
            if ($userinfo && $countryno == $userinfo['countrycode']) {

                if ($userinfo['status'] == 1) {
                    $errorInfo = array(
                        'status' => 0,
                        'message' => lan("YOUR_ACCOUNT_HAVE_FORBIDDEN", "Home"),
                    );
                    echo json_encode($errorInfo);
                } else {
                    $postpassword = I('password', '', 'md5');
                    $password = md5($postpassword . $userinfo['salt']);

                    if ($password == $userinfo['password']) {
                        $rememberpwd = I('POST.rememberpwd');
                        $token = 'PC'.date('YmdHis').$userinfo['roomno'];
                        $updatefeild = array(
                            'userid' => $userinfo['userid'],
                            'lastlogintime' => date('Y-m-d H:i:s'),
                            'token' => $token,
                            'lastloginip' => get_client_ip(0, true)
                        );
                        // 写入本次登录时间及IP
                        $db_member->save($updatefeild);

                        $userinfo['lastlogintime']=$updatefeild['lastlogintime'];
                        $userinfo['lastloginip']=$updatefeild['lastloginip'];

                        $db_Balance = D('Balance');
                        $userCond = array(
                            'userid' => $userinfo['userid']
                        );
                        $balance = $db_Balance->where($userCond)->find();
                        if (!$balance)
                        {
                            $balance['balance'] = 0;
                        }

                        $userAllInfo = $db_member->getMemberGrade($userinfo);
                        $userAllInfo['postpwd'] = I('POST.password');
                        $userAllInfo['token'] = $token;
                        $userAllInfo['vipid'] = D('Viprecord')->getMyVipID($userinfo['userid']);
                        //获取用户免费礼物数量
                        $user_free_gift_count = $this->getFreeGiftcount($userinfo['userid']);
                        cookie('free_gift_count', $user_free_gift_count);

                        // 写入SESSION
                        $this->setSessionCookie($userAllInfo, $balance, $rememberpwd);
                        $userAllInfo['balance'] = $balance['balance'];
                        $userAllInfo['password']='';
                        $userAllInfo['postpwd']='';
                        $userAllInfo['salt'] = '';
                        $data['status'] = 1;
                        $data['message'] = lan('1', 'Home');
                        $data['datalist'] = $userAllInfo;
                        echo json_encode($data);
                        
                    } else {
                        $errorInfo = array(
                            'status' => 0,
                            'message' => lan("PASSWORD_ERROR", "Common"),
                        );
                        echo json_encode($errorInfo);
                    }
                    //redirect(session("currenturl"));
                }
            } else {
                $errorInfo = array(
                    'status' => 0,
                    'message' => lan("USERNAME_ERROR", "Common"),
                );
                echo json_encode($errorInfo);
            }
		}
	}
	
	/**
	 * facebook 登陆
	 */
	public function facebookLogin(){
	    $this->thirdPartyLogin("Facebook", 1, 'Index/index');
	}
	
	/**
	 * twitter 登陆
	 */
	public function twitterLogin(){
	    $this->thirdPartyLogin("Twitter", 3, 'Index/index');
	}
	
	/**
	 * google 登陆
	 */
	public function googleLogin(){
	    $this->thirdPartyLogin("Google", 2, 'Index/index');
	}

    /**
     * 招聘主播facebook 登陆
     */
    public function facebookLogin4Recruit(){
        $this->thirdPartyLogin("Facebook", 1, 'Index/recruit_index');
    }

    /**
     * 招聘主播twitter 登陆
     */
    public function twitterLogin4Recruit(){
        $this->thirdPartyLogin("Twitter", 3, 'Index/recruit_index');
    }

    /**
     * 招聘主播google 登陆
     */
    public function googleLogin4Recruit(){
        $this->thirdPartyLogin("Google", 2, 'Index/recruit_index');
    }


    /**
	 * FACEBOOK TWITTER GOOLGE登陆公共方法
	 */
	private function thirdPartyLogin($provider, $tpType, $redirect){
	    try {
	        require_once(dirname(__FILE__) . '/hybridauth/config.php');
	        require_once(dirname(__FILE__) . '/hybridauth/Hybrid/Auth.php');
            //include('../../Common/Common/hybridauth/config.php');
            //include('../../Common/Common/hybridauth/Hybrid/Hybrid_Auth.php');

            $hybridauth = new \Hybrid_Auth($config);
            $authProvider = $hybridauth->authenticate($provider);
            /**
             * Facebook:email, user_about_me, user_birthday, user_hometown, user_location, user_website, read_stream, publish_actions, read_custom_friendlists
             * email, user_about_me, user_birthday, user_hometown, user_website, offline_access, read_stream, publish_stream, read_friendlists
             * 
             * 
             */
            $user_profile = $authProvider->getUserProfile();

            //第三方登录成功添加日志记录
            $path = __ROOT__."Data/third_party_login/FacebookUserProfile.txt";
            $content = date('Y-m-d H:i:s')."\r\n".json_encode($user_profile)."\r\n=============================\r\n";
            file_put_contents($path,$content,FILE_APPEND);
            if ($user_profile && isset($user_profile->identifier)) {
                $field = array(
                    'userid', 'identifier', 'username', 'roomno', 'niceno', 'familyid', 'nickname','userlevel', 'province',
                    'city', 'smallheadpic', 'bigheadpic','lastlogintime', 'lastloginip','isemcee', 'isvirtual',
                    'isvip', 'usertype', 'password', 'salt','token','countrycode','status'
                );
                
                $where = array('identifier' => $user_profile->identifier);
                $db_member = D('Member');
                $userinfo = $db_member->where($where)->field($field)->find();
                if($userinfo){
                    if ($userinfo['status'] == 1) {
                        $errorInfo = array(
                            'status' => 0,
                            'message' => lan("YOUR_ACCOUNT_HAVE_FORBIDDEN", "Home"),
                        );
                        echo json_encode($errorInfo);
                    }else{
                        $token = 'PC'.date('YmdHis').$userinfo['roomno'];
                        $updatefeild = array(
                            'lastlogintime' => date('Y-m-d H:i:s'),
                            'token' => $token,
                            'lastloginip' => get_client_ip(0, true)
                        );
                        //第三方登录，判断用户头像是否是http开头，如果是，则取到本地
                        if(!$userinfo['smallheadpic'] || substr($userinfo['smallheadpic'],0,4) == 'http')
                        {
                            $smallHeadpic = getSmallHeadpicUrl($user_profile->photoURL, $userinfo['userid']);
                            $updatefeild['smallheadpic'] = $smallHeadpic;
                            $userinfo['smallheadpic'] = $updatefeild['smallheadpic'];
                        }

                        // 写入本次登录时间及IP
                        $db_member->where($where)->save($updatefeild);
                        session('UserLoginToken',$updatefeild['token']);

                        $userinfo['lastlogintime']=$updatefeild['lastlogintime'];
                        $userinfo['lastloginip']=$updatefeild['lastloginip'];

                        $db_Balance = D('Balance');
                        $userCond = array(
                            'userid' => $userinfo['userid']
                        );
                        $balance = $db_Balance->where($userCond)->find();
                        if (!$balance)
                        {
                            $balance['balance'] = 0;
                        }

                        $userAllInfo = $db_member->getMemberGrade($userinfo);
                        $userAllInfo['postpwd'] = I('POST.password');
                        $userAllInfo['token'] = $token;
                        $userAllInfo['vipid'] = D('Viprecord')->getMyVipID($userinfo['userid']);
                        //获取用户免费礼物数量
                        $user_free_gift_count = $this->getFreeGiftcount($userinfo['userid']);
                        cookie('free_gift_count', $user_free_gift_count);

                        // 写入SESSION
                        $this->setSessionCookie($userAllInfo, $balance, null);
                        $userAllInfo['balance'] = $balance['balance'];
                        $userAllInfo['password']='';
                        $userAllInfo['postpwd']='';
                        $userAllInfo['salt'] = '';
                        $data['status'] = 1;
                        $data['message'] = lan('1', 'Home');
                        $data['datalist'] = $userAllInfo;
                        //echo json_encode($data);

                        redirect(U($redirect));
                    }
                }
                else{
                    $this->registerThirdparty($user_profile, $tpType);
                    redirect(U($redirect));
                }
                
                
                
                /**echo "<b>displayName</b> :" . $user_profile->displayName . "<br>";
                echo "<b>Profile URL</b> :" . $user_profile->profileURL . "<br>";
                echo "<b>webSite URL</b> :" . $user_profile->webSiteURL . "<br>";
                echo "<b>coverInfoURL URL</b> :" . $user_profile->coverInfoURL . "<br>";
                echo "<b>Image</b> :" . $user_profile->photoURL . "<br> ";
                //echo "<img src='" . $user_profile->photoURL . "'/><br>";
                echo "<b>identifier</b> :" . $user_profile->identifier . "<br>";
                echo "<b>username</b> :" . $user_profile->username . "<br>";
                echo "<b>firstName</b> :" . $user_profile->firstName . "<br>";
                echo "<b>lastName</b> :" . $user_profile->lastName . "<br>";
                echo "<b>gender</b> :" . $user_profile->gender . "<br>";
                echo "<b>language</b> :" . $user_profile->language . "<br>";
                echo "<b>phone</b> :" . $user_profile->phone . "<br>";
                echo "<b>zip</b> :" . $user_profile->zip . "<br>";
                echo "<b>address</b> :" . $user_profile->address . "<br>";
                echo "<b>age</b> :" . $user_profile->age . "<br>";
                echo "<b>description</b> :" . $user_profile->description . "<br>";
                echo "<b>Email</b> :" . $user_profile->email . "<br>";
                echo "<b>emailVerified</b> :" . $user_profile->emailVerified . "<br>";
                echo "<b>region</b> :" . $user_profile->region . "<br>";
                echo "<b>country</b> :" . $user_profile->country . "<br>";
                echo "<b>city</b> :" . $user_profile->city . "<br>";
                echo "<b>birthYear</b> :" . $user_profile->birthYear . "<br>";
                echo "<b>birthMonth</b> :" . $user_profile->birthMonth . "<br>";
                echo "<b>birthDay</b> :" . $user_profile->birthDay . "<br>";
                echo "<br> <a href='logout.php'>Logout</a>";*/
            }
        } catch (Exception $e) {
            $data['status'] = 0;
            
            /* switch ($e->getCode()) {
                case 0:
                    echo "Unspecified error.";
                    break;
                case 1:
                    echo "Hybridauth configuration error.";
                    break;
                case 2:
                    echo "Provider not properly configured.";
                    break;
                case 3:
                    echo "Unknown or disabled provider.";
                    break;
                case 4:
                    echo "Missing provider application credentials.";
                    break;
                case 5:
                    echo "Authentication failed. " . "The user has canceled the authentication or the provider refused the connection.";
                    break;
                case 6:
                    echo "User profile request failed. Most likely the user is not connected " . "to the provider and he should to authenticate again.";
                    $authProvider->logout();
                    break;
                case 7:
                    echo "User not connected to the provider.";
                    $authProvider->logout();
                    break;
                case 8:
                    echo "Provider does not support this feature.";
                    break;
            } */
            $data['message'] = $e->getMessage();
            echo json_encode($data);
            //well, basically your should not display this to the end user, just give him a hint and move on..
            //echo "<br /><br /><b>Original error message:</b> " . $e->getMessage();
            //echo "<hr /><h3>Trace</h3> <pre>" . $e->getTraceAsString() . "</pre>";
        }
	}
	
	public function registerThirdparty($user_profile, $tptype){
        $sex = $user_profile->gender;
        if ($sex == 'male') {
            $sex = 0;
        }elseif($sex == 'female'){
            $sex = 1;
        }else {
            $sex = 0;
        }
        $insertArr = array(
            'identifier' => $user_profile->identifier,
            'thirdparty' => $tptype,
            'userno' => $user_profile->phone,
            'roomno' => getRoomno(),
            'username' => $user_profile->username,
            'nickname' => $user_profile->displayName,
            'userinfo' => $user_profile->firstName.'-'.$user_profile->lastName.'-'. $user_profile->gender
                . '-'. $user_profile->age. '-'. $user_profile->emailVerified
                . '-'. $user_profile->region. '-'. $user_profile->country. '-'. $user_profile->city
                . '-'. $user_profile->birthYear. '-'. $user_profile->birthMonth. '-'. $user_profile->birthDay
                . '-'. $user_profile->address,
            'salt' => '',
            'userlevel' => 0,
            'smallheadpic' => $user_profile->photoURL,
            'sex' => $sex,
            'usertype' => 0,
            'countrycode' => $user_profile->language,
            'city' => $user_profile->city,
            'email' => $user_profile->email,
            'registertime' => date('Y-m-d H:i:s'),
            'lastlogintime' => date('Y-m-d H:i:s'),
            'lastloginip' => get_client_ip(0, true),
            'isemcee' => 0,
            'token' => 'PC' . date('YmdHis') . $user_profile->identifier
        );
        
        // 事务
        $tran = new Model();
        $tran->startTrans();
        
        $userInfoResult = $tran->table('ws_member')->add($insertArr);
        $userCond = array('userid' => $userInfoResult);
        $roomno = getUserRoomno($userInfoResult);
        //第三方第一次登陆的时候将第三图片获取到本地，然后再上传到图片服务器上
        $smallHeadpicUrl = getSmallHeadpicUrl($user_profile->photoURL, $userInfoResult);
        $newUserInfo['roomno'] = $roomno;
        $newUserInfo['smallheadpic'] = $smallHeadpicUrl;
        $tran->table('ws_member')->where($userCond)->save($newUserInfo);

        $insertRoomArr = array(
            'roomno' => $roomno,
            'roomname' => $insertArr['nickname'],
            'createtime' => $insertArr['registertime']
        );
        
        $roomResult = $tran->table('ws_room')->add($insertRoomArr);
        
        $selectfield = array(
            'userid',
            'userno',
            'username',
            'roomno',
            'niceno',
            'familyid',
            'nickname',
            'userlevel',
            'province',
            'city',
            'smallheadpic',
            'bigheadpic',
            'lastlogintime',
            'lastloginip',
            'isemcee',
            'isvirtual',
            'isvip',
            'usertype',
            'token'
        );
        
        $db_member = D('Member');
        $userinfo = $db_member->where(array('identifier' => $insertArr['identifier']))->field($selectfield)->find();
        
        $insertBalArr = array(
            'userid' => $userinfo['userid'],
            'spendmoney' => 0,
            'earnmoney' => 0,
            'balance' => 0,
            'point' => 0,
            'totalrecharge' => 0,
            'createtime' => date('Y-m-d H:i:s'),
            'effectivetime' => date('Y-m-d H:i:s'),
            'expiretime' => date('Y-m-d H:i:s', mktime(0, 0, 0, 1, 1, 2037))
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
            $this->setSessionCookie($userinfo, $insertBalArr);
            
            $data['status'] = 1;
            $data['message'] = lan('1', 'Home');
            $data['datalist'] = $userinfo;
            return $data;
        } else {
            $tran->rollback();
            $data['status'] = 3;
            $data['message'] = lan('OPERATION_FAILED', 'Home');
            return $data;
        }
    }
	
    public function logout()
    {
        $_SESSION = array(); //清除SESSION值.
        if (isset($_COOKIE[session_name()])) {  //判断客户端的cookie文件是否存在,存在的话将其设置为过期.
            setcookie(session_name(), '', time() - 1, '/');
        }
        session_destroy();  //清除服务器的sesion文件

        //清除cookie
        foreach ($_COOKIE as $key => $val) {
            setcookie($key, '', time() - 3600, '/');
            unset($_COOKIE[$key]);
        }

        $data['status'] = 1;
        $data['message'] = lan('1', 'Home');
        echo json_encode($data);
    }

    /**
     * 根据用户ID获取免费礼物数量
     * @param userId：用户ID
     */
    private function getFreeGiftcount($userId){
        //获取免费礼物最大配置
        $where = array(
            'key' => 'FREE_GIFT_MAX_COUNT',
            'lantype' => $this->lan
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
        return $freegiftcount;
    }
}