<?php
namespace Operator\Controller;

class IndexController extends CommonController {
    
    function _initialize(){
        C('HTML_CACHE_ON',false);
    
        $curUrl = base64_encode($_SERVER["REQUEST_URI"]);
        
        if(!strpos($_SERVER["REQUEST_URI"],'login') && !strpos($_SERVER["REQUEST_URI"],'verify') && !strpos($_SERVER["REQUEST_URI"],'logout') && !$_SESSION['operatorid'])
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
            $this->error(lan("VERIFY_CODE_ERROR", "Operator"));
        }
        
        $username = I('username','','trim');
        
        $dMember = new \Common\Model\MemberModel();
        $userinfo = $dMember->where("username='" .$username . "'")->find();

                    
        if($userinfo) {
            if($userinfo['usertype'] == 30){
                if($userinfo['status'] == 1){
                    $this->error(lan("YOUR_ACCOUNT_HAVE_FORBIDDEN", "Operator"));
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
                        session('operatorid', $userinfo['userid']);
                        session('operatorname', $userinfo['realname']);

                        redirect(U('index'));
                    }else{
                        $this->error(lan("PASSWORD_ERROR", "Operator"));
                    }
                }
            }else{
                $this->error(lan("YOU_NOT_OPERATOR_ADMIN", "Operator"));
            }
        }
        else{
            $this->error(lan("USERNAME_ERROR", "Operator"));
        }
        
    }
    
    function logout()
    {
        session('operatorid',null);
        session('operatorname',null);
        
        redirect(U('login'));
    }
    
    public function index()
    {
        $this->assign("hello", lan("HELLO", "Operator"));
        $this->assign("quitsystem", lan("QUIT_SYSTEM", "Operator"));
        $this->assign("sitehomepage", lan("SITE_HOMEPAGE", "Operator"));
        $this->assign("waashowplatform", lan("WAASHOW_PLATFORM", "Operator"));
        $this->assign("spreadandshrink", lan("SPREAD_AND_SHRINK", "Operator"));
        $this->assign("spread", lan("SPREAD", "Operator"));
        
        $operatorInfo = M('Member')->field('lastlogintime')->where('userid='.session('operatorid'))->find();
        $this->assign("lastlogintime", $operatorInfo['lastlogintime']);        

        $this->display();
    }
    
    public function leftFrame()
    {        
        $this->display();
    }
    
}