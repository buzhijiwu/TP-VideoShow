<?php
namespace Admin\Controller;
use Think\Controller;

/*
** Login 控制器类的作用：登录、退出相关
*/
class LoginController extends Controller {
	
	/*
	** 方法作用：显示 '登录界面' 的模板 、 登录时逻辑判断
	** 参数1：[无]
	** 返回值：[无]
	** 备注：[无]
	 */
    public function index() {
        $this->display();
    }
 
     public function dologin()
    {
        $this->checkVerify();
        $adminname = I('POST.username', '', 'trim');
        $adminCond = array(
            'adminname' => $adminname,
        );
        $adminModel = D('Admin');
        $adminInfo = $adminModel->where($adminCond)->find();

        if ($adminInfo && $adminInfo['isdelete'] == 0)
        {
            $postpassword = I('password', '', 'md5');

            if ($postpassword == $adminInfo['password'])
            {
                $updatefeild = array(
                    'adminid' => $adminInfo['adminid'],
                    'lastlogintime' => date('Y-m-d H:i:s'),
                    'lastloginip' => get_client_ip()
                );
                //写入本次登录时间及IP
                $adminModel->save($updatefeild);
                //写入SESSION
                session('adminid', $adminInfo['adminid']);
                session('roleid', $adminInfo['roleid']);
                session('adminname', $adminname);
                redirect(U('Index/index'));
            }
            else
            {
                $this->error(lan("PASSWORD_ERROR", "Admin"));
            }
        }
        else
        {
            $this->error(lan("USERNAME_ERROR", "Admin"));
        }
    }

    private function checkVerify()
    {
        $code = I('post.code');

        if (!checkVerify($code))
        {
            $this->error(lan("VERIFY_CODE_ERROR", "Admin"));
        }
    }   
    
    /*
    ** 方法作用：生成验证码
    ** 参数1：[无]
    ** 返回值：[无]
    ** 备注：[无]
     */
    public function verify() {
    	verify();
    }
    
    
    /*
    ** 方法作用：后台管理员退出登录
    ** 参数1：[无]
    ** 返回值：[无]
    ** 备注：[无]
     */
    public function logout() {
    	session('adminid',null);
		session('adminname',null);
		echo '<meta charset=utf-8 />';
    	redirect(U('Login/index'),2,lan('LAN_LOGOUT_SUCCESS', 'Admin'));
    }
}