<?php
namespace Supervise\Controller;
use Think\Page;

class IndexController extends CommonController {
            
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
            $this->error(lan("VERIFY_CODE_ERROR", "Supervise"));
        }
        
        $username = I('username','','trim');
        
        $dMember = new \Common\Model\MemberModel();
        $userinfo = $dMember->where("username='" .$username . "'")->find();

                    
        if($userinfo) {
            if($userinfo['usertype'] == 10){
                if($userinfo['status'] == 1){
                    $this->error(lan("YOUR_ACCOUNT_HAVE_FORBIDDEN", "Supervise"));
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
                        session('superviseid', $userinfo['userid']);
                        session('supervisename', $userinfo['username']);

                        redirect(U('index'));
                    }else{
                        $this->error(lan("PASSWORD_ERROR", "Supervise"));
                    }
                }
            }else{
                $this->error(lan("YOU_NOT_INSPECTOR_ADMIN", "Supervise"));
            }
        }
        else{
            $this->error(lan("USERNAME_ERROR", "Supervise"));
        }
        
    }
    
    function logout()
    {
        session('superviseid',null);
        session('supervisename',null);
        
        redirect(U('login'));
    }
    
    public function index()
    {
        $map['isprocess'] = 0;
        $pending_report_count = count(M('Report')->where($map)->group('reporteduid')->select()); 
        $this->assign("pending_report_count", $pending_report_count);               
        $this->assign("hello", lan("HELLO", "Operator"));
        $this->assign('username',session('supervisename'));
        $this->display();
    }   
}