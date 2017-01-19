<?php
/**
 * 修改密码
 */
namespace Admin\Controller;
use Think\Controller;
class EditPwdController extends CommonController{
    public function _initialize(){
        parent::_initialize();
        $this->assign('userName', lan('USER_NAME_LABEL', 'Admin'));
        $this->assign("oldPassword", lan("OLD_PASSWORD_LABEL", "Admin"));
        $this->assign("newPassword", lan("NEW_PASSWORD_LABEL", "Admin"));
        $this->assign('repeatNewPWD', lan('REPEATE_NEW_PWD_LABEL', 'Admin'));
        $this->assign("submit", lan("SUBMIT_LABEL", "Admin"));
        $this->assign("confirmpassword", lan("FAMILY_CONFIRMPASSWORD", "Admin"));
        $this->assign("donotchangepwd", lan("DONOT_CHANGEPWD", "Admin"));
        $this->assign("pwdissixtotwenty", lan("PWD_ISSIX_TO_TWENTY", "Admin"));
        $this->assign("oldpwdisright", lan("OLD_PWD_ISRIGHT", "Admin"));
        $this->assign("oldpwdiswrong", lan("OLD_PWD_ISWRONT", "Admin"));
        $this->assign("twicepwdnotsame", lan("TWICE_PWD_NOTSAME", "Admin"));
        $this->assign("twicepwdissame", lan("TWICE_PWD_ISSAME", "Admin"));
        $this->assign("inputoldpassword", lan("INPUT_OLD_PASSWORD", "Admin"));
        $this->assign("inputnewpassword", lan("INPUT_NEW_PASSWORD", "Admin"));
        $this->assign("inputconfirmpassword", lan("INPUT_CONFIRM_PASSWORD", "Admin"));
        $this->assign("newconfirmnotsame", lan("NEW_CONFIRM_NOT_SAME", "Admin"));
    }


    public function edit_pwd()
    {
        $admin = D("Admin")->find($_SESSION["adminid"]);
        $this->assign('adminname', $admin['adminname']);
        $this->display();
    }


    public function do_edit_pwd()
    {

        $oldpassword = md5($_POST["old_password"]);
        $adminDao = D('Admin');

        $admininfo = $adminDao->where("adminid='".$_SESSION["adminid"]."' and password='".$oldpassword."'")->select();
        if($admininfo)
        {
            // $rule = $adminDao->addValidate();
            $vo = $adminDao->create();
            if(!$vo)
            {
                $this->error($adminDao->getError());
            }
            else
            {
                $adminDao->password = md5($_POST['new_password']);
                if($adminDao->save()!==false) $this->success(lan("CHANGE_PWD_SUCCESSFUL", "Admin"));
                else $this->error(lan("OPERATION_FAILED", "Admin"));
                die;
            }
        }
        else{
            $this->error(lan("OLD_PWD_ISWRONT", "Admin"));
            die;
        }
    }

}