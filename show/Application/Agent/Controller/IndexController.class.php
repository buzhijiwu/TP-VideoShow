<?php
namespace Agent\Controller;
use Think\Controller;

class IndexController extends Controller {
    
    function _initialize()
    {
        C('HTML_CACHE_ON',false);

        if(!strpos($_SERVER["REQUEST_URI"],'login') && !strpos($_SERVER["REQUEST_URI"],'verify') && !strpos($_SERVER["REQUEST_URI"],'logout') && !$_SESSION['agentid'])
        {
            redirect(U('login'));            
        }
    }
    
    public function login()
    {
        $this->assign('admin_login_verify', M('systemset')->where(array('key'=>'admin_login_verify'))->getField('value'));
        if($_GET['return']!=''){
            $this->assign('returnurl', $_GET['return']);
        }
        $this->display();
    }
    
    public function verify()
    {
        verify();
    }
    
    public function dologin()
    {
        $this->checkVerify();
        $agentname = I('POST.username', '', 'trim');
        $agentCond = array(
            'agentname' => $agentname,
        );
        $agentModel = D('Agent');
        $agentInfo = $agentModel->where($agentCond)->find();

        if ($agentInfo && $agentInfo['isdelete'] == 0)
        {
            $postpassword = I('password', '', 'md5');

            if ($postpassword == $agentInfo['password'])
            {
                $updatefeild = array(
                    'agentid' => $agentInfo['agentid'],
                    'lastlogintime' => date('Y-m-d H:i:s'),
                    'lastloginip' => get_client_ip()
                );
                //写入本次登录时间及IP
                $agentModel->save($updatefeild);
                //写入SESSION
                session('agentid', $agentInfo['agentid']);
                session('agentname', $agentname);
                session('agenttype', $agentInfo['agenttype']);
                redirect(U('index'));
            }
            else
            {
                $this->error(lan("PASSWORD_ERROR", "Agent"));
            }
        }
        else
        {
            $this->error(lan("USERNAME_ERROR", "Agent"));
        }
    }
    
    function logout()
    {
        session('agentid',null);
        session('agentname',null);
        session('agenttype',null);
        
        redirect(U('login'));
    }
    
    public function index()
    {
        $this->display();
    }
    
    public function leftFrame()
    {
        $this->display();
    }
    
    public function mainFrame()
    {
        $agentModel = D('Agent');
        $agentInfo = $agentModel->find(session('agentid'));
        $this->assign('agentinfo',$agentInfo);
        $this->display();
    }
    
    
    public function edit_pwd()
    {
        if($_GET['action'] == 'edit_pwd_ajax')
        {
            $agentModel = D('Agent');
            $agentInfo = $agentModel->find(session("agentid"));
            $postpassword = I('get.old_password','','md5');

            if($postpassword == $agentInfo['password'])
            {
                echo '1';
            }
            else
            {
                echo '0';
            }
            exit;
        }
    
        $this->display();
    }
    
    public function do_edit_pwd()
    {
        $agentModel = D('Agent');
        
        if ($_POST['new_password'] != '') {
            if ($_POST['old_password'] == '') {
                $this->error(lan("INPUT_OLD_PASSWORD", "Agent"));
            }
            if ($_POST['new_password'] != $_POST['new_pwdconfirm']) {
                $this->error(lan("NEW_CONFIRM_NOT_SAME", "Agent"));
            }
        }
        $agentInfo = $agentModel->find(session("agentid"));
        $newpassword = I('new_password','','md5');
        $updatefeild = array(
            'agentid'  => $agentInfo['agentid'],
            'password' => $newpassword
        );
        $agentModel->save($updatefeild);

        $this->success(lan("CHANGE_PWD_SUCCESSFUL", "Agent"));
    }
    
    public function recharge()
    {
        $agentInfo = D('Agent')->find(session('agentid'));
        $this->assign('agentInfo', $agentInfo);
        $this->display();
    }

    public function do_recharge()
    {
        $userName = I('post.username');
        $rechargeAmount = I('post.rechargeamount');
        $this->checkAmount($rechargeAmount);
        if($userName != '')
        {
            $ueserCond = array(
                'username' => $userName,
            );
            $userinfo = D("Member")->where($ueserCond)->select();
            if($userinfo)
            {
                $balanceCond = array(
                    'userid' => $userinfo[0]['userid'],
                );
                $balance = D("Balance");
                $userBalance = $balance->where($balanceCond)->find();
                //dump($userBalance);die;
                $newBalance['balance'] = $userBalance['balance'] + $rechargeAmount;
                $ratio = M('siteconfig')->where("sconfigid = '1'")->getField('ratio');  //货币与虚拟币兑换比例
                $newBalance['point'] = $userBalance['point'] + $rechargeAmount/$ratio;                
                $newBalance['totalrecharge'] = $userBalance['totalrecharge'] + $rechargeAmount;
                $balance->where($balanceCond)->save($newBalance);
                $this->addRechargeRecord($userinfo, $rechargeAmount, 1);
                $db_Agent = M('Agent');
                $agentInfo = $db_Agent->find(session('agentid'));
                $newUseAmount['useamount'] = $rechargeAmount + $agentInfo['useamount'];
                $agentCond = array(
                    'agentid' => session('agentid')
                );
                $db_Agent->where($agentCond)->save($newUseAmount);
                $this->success();
            }
            else{
                $this->error(lan('USERNAME_ERROR', 'Agent'));
            }
        }
        else{
            $this->error(lan('PARAM_ERROR', 'Agent'));
        }
    }

    public function postRecharge()
    {
        $agentInfo = D('Agent')->find(session('agentid'));
        $this->assign('agentInfo', $agentInfo);
        $this->display();
    }

    public function do_postRecharge()
    {
        $userName = I('post.username');
        $rechargeAmount = I('post.rechargeamount');
        //$this->checkAmount($rechargeAmount);
       //dump('ky');
        if($userName != '')
        {
            $ueserCond = array(
                'username' => $userName,
            );
            $userinfo = D("Member")->where($ueserCond)->select();
            if($userinfo)
            {
                $this->addRechargeRecord($userinfo, $rechargeAmount, 2);
                $this->success();
            }
            else{
                $this->error(lan('USERNAME_ERROR', 'Agent'));
            }
        }
        else{
            $this->error(lan('PARAM_ERROR', 'Agent'));
        }
    }

    public function recharge_record()
    {
        $condition = $this->buildQueryCond();
        $orderby = 'rechargetime desc';
        $rechargedetail = new \Admin\Model\RechargedetailModel;
        $count = $rechargedetail->where($condition)->count();
        $pagesize = 50;

        if($_POST['pagesize'] != '')
        {
            $pagesize = $_POST['pagesize'];
        };

        $page = getpage($count,$pagesize);
        $recharges = $rechargedetail->limit($page->firstRow.",".$page->listRows)->where($condition)->order($orderby)->select();

        foreach($recharges as $n=> $val)
        {
            $rechargeUser = D("Member")->find($val['targetid']);
            $recharges[$n]['username'] = $rechargeUser['username'];
            $agent = D('Agent')->find($val['agentid']);
            $recharges[$n]['agentname'] = $agent['agentname'];
        }

        $this->assign('page',$page->show());
        $this->assign('recharges',$recharges);
        $this->display();
    }

    private function checkVerify()
    {
        $code = I('post.code');

        if (!checkVerify($code))
        {
            $this->error(lan("VERIFY_CODE_ERROR", "Agent"));
        }
    }

    /**
     * @param $userinfo
     * @param $rechargeAmount
     */
    private function addRechargeRecord($userinfo, $rechargeAmount, $status)
    {
        $rechargedetailModel = new \Admin\Model\RechargedetailModel;
        $rechargedetailModel->userid = session('agentid');
        $rechargedetailModel->targetid = $userinfo[0]['userid'];
        $rechargedetailModel->type = 1;//代理充值
        $rechargedetailModel->orderno = date('YmdHis').rand(1000,9999).$userinfo[0]['userid'];
        $rechargedetailModel->showamount = $rechargeAmount;
        $rechargedetailModel->rechargetime = date("Y-m-d H:i:s", time());
        $rechargedetailModel->status = $status;
        //$rechargedetailModel->agentid = session('agentid');
        $rechargedetailModel->content = lan('AGENT_RECHARGE', 'Agent');
        $rechargedetailModel->devicetype = 2;

        $siteconfigModel = new \Admin\Model\SiteconfigModel();
        $siteconfig = $siteconfigModel->find();
        $rechargedetailModel->amount = $rechargeAmount/$siteconfig['ratio'];
        $rechargedetailModel->localunit = 'VND'; //暂时默认越南盾
        $rechargedetailModel->add();
    }

    private function buildQueryCond()
    {
        $condition = $this->getTimeCond('rechargetime');
        $orderno = I('get.orderno');

        if ('' != $orderno)
        {
            $condition['orderno'] = $orderno;
        }

        $username = I('get.username');
        if ('' != $username)
        {
            $condition['targetid'] = $this->getUserIdByName($username);
        }

        $agentid = session('agentid');
        if ('' != $agentid)
        {
            $condition['agentid'] = $agentid;
        }
        $condition['type'] = 1;//代理充值
        return $condition;
    }

    /**
     * @param $username
     * @return mixed
     */
    private function getUserIdByName($username){
        $userId = 0;
        if($username){
            $targetCond['username'] = $username;
            $userId = M('Member')->where($targetCond)->getField('userid');
        }
        return $userId;
    }

    private function getTimeCond($timeFildName )
    {
        $condition = array();
        $startTime = I('get.start_time');
        $endTime = I('get.end_time');

        if ('' == $startTime && '' != $endTime) {
          $condition[$timeFildName][0] = array('egt', '1970-01-01 00:00:00');
          $condition[$timeFildName][1] = array('lt', $endTime.' 23:59:59');   
        }
        if ('' != $startTime && '' == $endTime) {
          $condition[$timeFildName][0] = array('egt', $startTime.' 00:00:00');  
          $condition[$timeFildName][1] = array('lt', date('Y-m-d H:i:s'));           
        }        
        if ('' != $startTime && '' != $endTime) {
          $condition[$timeFildName][0] = array('egt', $startTime.' 00:00:00');
          $condition[$timeFildName][1] = array('lt', $endTime.' 23:59:59');     
        }          
        return $condition;
    }

    /**
     * @param $rechargeAmount
     */
    private function checkAmount($rechargeAmount)
    {
        $agentInfo = D('Agent')->find(session('agentid'));
        $newUseAmount = $rechargeAmount + $agentInfo['useamount'];

        if ($agentInfo['limitamount'] < $newUseAmount)
        {
            $this->error(lan('MONEY_NOT_ENOUGH', 'Agent'));
        }
    }
}