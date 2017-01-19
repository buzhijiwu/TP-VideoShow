<?php
namespace Home\Controller;

class ForgetpwdController extends CommonController{
    public function index(){
        if (IS_POST) {
            $field = array(
                'username', 'password', 'confirmpwd','verifycode', 'countryno'
            );

            //用户名不能为空
            if(empty($_POST['username'])) {
                $result = array(
                    'status' => 2,
                    'msg'   => lan('INPUT_YOUR_USERNAME', 'Home')
                );
                $this->ajaxReturn($result);
            }

            //过滤字段
            $this->checkParameter($field);
            $username = I('POST.username','', 'trim');
            $password = I('POST.password','', 'trim');
            $confirmpwd = I('POST.confirmpwd','', 'trim');
            $verifycode = I('POST.verifycode','', 'trim');
            $countryno = I('POST.countryno','', 'trim');

            //验证用户
            $db_Member = M('Member');
            $userinfo = $db_Member->where(array('username' => $username))->find();
            if (!$userinfo) {
                $result = array(
                    'status' => 2,
                    'msg'   => lan('USERNAME_ERROR', 'Home')
                );
                $this->ajaxReturn($result);
            }

            //验证密码
            if(!preg_match('/^[0-9a-zA-Z_]{6,16}$/',$password)){
                $result = array(
                    'status' => 3,
                    'msg' => lan("PASSWORD_LENGTH_ERROR","Home")
                );
                $this->ajaxReturn($result);
            }

            //确认密码
            if ($password != $confirmpwd) {
                $result = array(
                    'status' => 4,
                    'msg' => lan("CONFIRM_PWD_ERROR","Home")
                );
                $this->ajaxReturn($result);
            }

            //验证短信验证码
            $isright = true;
            if($countryno == '84'){  //越南
                $dSmsrecord = D("Smsrecord");
                $querySmsArr = array(
                    'phoneno' => $username,
                    'smstype' => 0,
                    'verifycode' =>  $verifycode,
                    'senddate' => date('Y-m-d'),
                );
                $smsrecord = $dSmsrecord->where($querySmsArr)->find();
                if(!$smsrecord){
                    $isright = false;
                }
            }else{  //其他国家
                $response = checkSmsCode($username,$countryno,$verifycode);
                if($response['status'] != 200){
                    $isright = false;
                }
            }
            if(!$isright){
                $result = array(
                    'status' => 5,
                    'msg' => lan("LAN_VERIFY_ERROR","Home")
                );
                $this->ajaxReturn($result);
            }

            //更新密码
            $editData['password'] = md5(md5($password) . $userinfo['salt']);
            $editData['token'] = 'PC'.date('YmdHis').$username.$editData['password'];
            $db_Member->where(array('userid' => $userinfo['userid']))->save($editData);
            $result = array(
                'status' => 1,
                'msg' => lan("OPERATION_SUCCESSFUL","Home")
            );
            $this->ajaxReturn($result);
        }else{
            $this->display();
        }
    }

    /**
     * 校验用户是否已经注册
     */
    public function checkUserRegister()
    {
        $username = I('POST.username');
        $db_Member = D('Member');
        $userinfo = $db_Member->where(array('username' => $username))->find();

        if ($username == '') {
            $result['status'] = 2;
            $result['msg'] = lan('INPUT_YOUR_USERNAME', 'Home');
            echo json_encode($result);
            die;
        }

        if (!$userinfo) {
            $result['status'] = 2;
            $result['msg'] = lan('USERNAME_ERROR', 'Home');
            echo json_encode($result);
        }
        else
        {
            $result['status'] = 1;
            $result['msg'] = lan('OPERATION_SUCCESSFUL', 'Home');
            echo json_encode($result);
        }
    }
}

?>