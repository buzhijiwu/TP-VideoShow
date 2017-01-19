<?php
namespace Appchannel\Controller;

class IndexController extends CommonController {
    
    function _initialize(){
        C('HTML_CACHE_ON',false);
    
        $curUrl = base64_encode($_SERVER["REQUEST_URI"]);
        
        if(!strpos($_SERVER["REQUEST_URI"],'login') && !strpos($_SERVER["REQUEST_URI"],'verify') && !strpos($_SERVER["REQUEST_URI"],'logout') && !$_SESSION['distributeid'])
        {
            redirect(U('login'));            
        }
    }
        
    public function login()
    {
        $this->display();
    }
    
    public function verify(){
        verify();
    }
    
    public function dologin()
    {
        if(!checkVerify($_POST["code"])){
            $this->error(lan("VERIFY_CODE_ERROR", "Appchannel"));
        }
        
        $username = I('username','','trim');
        
        $dMember = new \Common\Model\MemberModel();
        $userinfo = $dMember->where("username='" .$username . "'")->find();

                    
        if($userinfo) {
            if($userinfo['usertype'] == 40 && $userinfo['distributeidentity'] != ''){
                if($userinfo['status'] == 1){
                    $this->error(lan("YOUR_ACCOUNT_HAVE_FORBIDDEN", "Appchannel"));
                }
                else{
                    $postpassword = I('password','','md5');
                    $password = md5($postpassword . $userinfo['salt']);
                    
                    if($password == $userinfo['password']){
                        $updatefeild = array(
                            'userid'  =>$userinfo['userid'],
                            'lastlogintime' => date('Y-m-d H:i:s'),
                            'lastloginip' => get_client_ip()
                        );
                        
                        //写入本次登录时间及IP
                        $dMember->save($updatefeild);

                        //写入SESSION
                        session('duserid', $userinfo['userid']);
                        session('distributeid', $userinfo['distributeidentity']);
                        session('disusername', $userinfo['username']); 
                        $map = array(
                            'distributeid' => $userinfo['distributeidentity']
                        );
                        $distributename = M('distribute')->where($map)->getField('distributename'); 
                        session('distributename', $distributename);                       

                        redirect(U('index'));
                    }else{
                        $this->error(lan("PASSWORD_ERROR", "Appchannel"));
                    }
                }
            }else{
                $this->error(lan("YOU_NOT_CHANNEL_ADMIN", "Appchannel"));
            }
        }
        else{
            $this->error(lan("USERNAME_ERROR", "Appchannel"));
        }
        
    }
    
    function logout()
    {
        session('duserid',null);
        session('distributeid',null);
        session('disusername',null); 
        session('distributename', null);               
        redirect(U('login'));
    }
    
    public function index()
    {
        $this->assign("hello", lan("HELLO", "Appchannel"));
        $this->assign("quitsystem", lan("QUIT_SYSTEM", "Appchannel"));
        $this->assign("sitehomepage", lan("SITE_HOMEPAGE", "Appchannel"));
        $this->assign("waashowplatform", lan("WAASHOW_PLATFORM", "Appchannel"));
        $this->assign("spreadandshrink", lan("SPREAD_AND_SHRINK", "Appchannel"));
        $this->assign("spread", lan("SPREAD", "Appchannel"));
        
        $AppchannelInfo = M('Member')->field('lastlogintime')->where('distributeidentity="'.session('distributeid').'"')->find();
        $this->assign("lastlogintime", $AppchannelInfo['lastlogintime']);        

        $this->display();
    }
    
    public function leftFrame()
    {        
        $this->display();
    }
    
}