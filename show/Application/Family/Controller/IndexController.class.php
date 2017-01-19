<?php
namespace Family\Controller;
use Think\Controller;

class IndexController extends Controller {
    
    function _initialize(){
        C('HTML_CACHE_ON',false);
    
        $curUrl = base64_encode($_SERVER["REQUEST_URI"]);
        
        if(!strpos($_SERVER["REQUEST_URI"],'login') && !strpos($_SERVER["REQUEST_URI"],'verify') && !strpos($_SERVER["REQUEST_URI"],'logout') && !$_SESSION['familyuserid'])
        {
            redirect(U('login'));            
        }
    }
    
    // 空操作定义
    public function _empty() {
        $this->assign('jumpUrl', U('mainFrame'));
        $this->error(lan("OPERATION_NOT_EXIST", "Family"));
    }
    
    public function pswencode($txt,$key='youst'){
        $chars = "ABCDEFGHIJKLMNOPQRSTUVWXYZabcdefghijklmnopqrstuvwxyz0123456789-=+_)(*&^%$#@!~";
        $nh = rand(0,64);
        $ch = $chars[$nh];
        $mdKey = md5($key.$ch);
        $mdKey = substr($mdKey,$nh%8, $nh%8+7);
        $txt = base64_encode($txt);
        $tmp = '';
        $i=0;$j=0;$k = 0;
        for ($i=0; $i<strlen($txt); $i++) {
            $k = $k == strlen($mdKey) ? 0 : $k;
            $j = ($nh+strpos($chars,$txt[$i])+ord($mdKey[$k++]))%64;
            $tmp .= $chars[$j];
        }
        return $ch.$tmp;
    }
    
    public function login()
    {
        $this->assign("familylogintitle", lan("FAMILY_LOGIN_TITLE", "Family"));
        $this->assign("familyusername", lan("FAMILY_USER_NAME", "Family"));
        $this->assign("familypassword", lan("FAMILY_PASSWORD", "Family"));
        $this->assign("familyverifycode", lan("FAMILY_VERIFYCODE", "Family"));
        $this->assign("familylogin", lan("FAMILY_LOGIN", "Family"));
        $this->assign("familychangecode", lan("FAMILY_CHANGECODE", "Family"));
        $this->assign('admin_login_verify', M('systemset')->where(array('key'=>'admin_login_verify'))->getField('value'));
        
        if($_GET['return']!=''){
            $this->assign('returnurl', $_GET['return']);
        }
        $this->display();
    }
    
    public function verify(){
        verify();
    }
    
    public function dologin()
    {
        if(!checkVerify($_POST["code"])){
            $this->error(lan("VERIFY_CODE_ERROR", "Family"));
        }
        
        $username = I('username','','trim');
        
        $dMember = new \Common\Model\MemberModel();
        $userinfo = $dMember->where("username='" .$username . "'")->find();

                    
        if($userinfo) {
            if($userinfo['usertype'] == 20){
                if($userinfo['status'] == 1){
                    $this->error(lan("YOUR_ACCOUNT_HAVE_FORBIDDEN", "Family"));
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

                        $db_Family = M('Family');
                        $familyfield = array(
                            'lastlogintime' => date('Y-m-d H:i:s'),                            
                        );
                        $db_Family->where('userid='.$userinfo['userid'])->save($familyfield);
                        $familyCond = array(
                            'userid' => $userinfo['userid']
                        );
                        $familyInfo = $db_Family->where($familyCond)->find();

                        //写入SESSION
                        session('familyuserid', $userinfo['userid']);
                        session('familyid', $familyInfo['familyid']);
                        session('familyname', $familyInfo['familyname']);

                        redirect(U('index'));
                    }else{
                        $this->error(lan("PASSWORD_ERROR", "Family"));
                    }
                }
            }else{
                $this->error(lan("YOU_NOT_FAMILY_ADMIN", "Family"));
            }
        }
        else{
            $this->error(lan("USERNAME_ERROR", "Family"));
        }
        
    }
    
    function logout()
    {
        session('familyuserid',null);
        session('familyid',null);
        session('familyname',null);
        
        redirect(U('login'));
    }
    
    public function index()
    {
        $this->assign("familymangetitle", lan("FAMILY_MANAGE_CENTER", "Family"));
        $this->assign("hello", lan("HELLO", "Family"));
        $this->assign("familymangement", lan("FAMILY_MANAGEMENT", "Family"));
        $this->assign("quitsystem", lan("QUIT_SYSTEM", "Family"));
        $this->assign("sitehomepage", lan("SITE_HOMEPAGE", "Family"));
        $this->assign("familymangetitle", lan("FAMILY_MANAGE_CENTER", "Family"));
        $this->assign("waashowplatform", lan("WAASHOW_PLATFORM", "Family"));
        $this->assign("spreadandshrink", lan("SPREAD_AND_SHRINK", "Family"));
        $this->assign("spread", lan("SPREAD", "Family"));
        
        $this->display();
    }
    
    public function leftFrame()
    {
        $this->assign("familymangement", lan("FAMILY_MANAGEMENT", "Family"));
        $this->assign("managehomepage", lan("MANAGEMENT_HOMEPAGE", "Family"));
        $this->assign("spreadandshrink", lan("SPREAD_AND_SHRINK", "Family"));
        $this->assign("personalinformaion", lan("PERSON_INFORMATION", "Family"));
        $this->assign("changepassword", lan("CHANGE_PASSWORD", "Family"));
        $this->assign("emceelist", lan("EMCEE_LIST", "Family"));
        $this->assign("incomeexpensesummary", lan("INCOME_EXPENSE_SUMMARY", "Family"));
        $this->assign("incomeexpensedetail", lan("INCOME_EXPENSE_DETAIL", "Family"));
        
        $this->display();
    }
    
    public function mainFrame()
    {
        $this->assign("userieeight", lan("USE_ABOVE_IEEIGHT", "Family"));
        $this->assign("myinformation", lan("MY_INFORMATION", "Family"));
        $this->assign("accountbalance", lan("ACCOUNT_BALANCE", "Family"));
        $this->assign("hello", lan("HELLO", "Family"));
        $this->assign("rolefamilymanager", lan("ROLE_FMAILY_MANAGER", "Family"));
        $this->assign("lastlogintime", lan("LAST_LOGIN_TIME", "Family"));
        $this->assign("lastloginip", lan("LAST_LOGIN_IP", "Family"));
        $this->assign("prompting", lan("PROMPTING", "Family"));
        $this->assign("promptingcontent", lan("PROMPTING_CONTENT", "Family"));
        $this->assign("moneyunit", lan("MONEY_UNIT", "Family"));
        
        $dMember = new \Common\Model\MemberModel();
        $userinfo = $dMember->where("userid=" .session('familyuserid'))->find();
        $dBalance = new \Common\Model\BalanceModel();
        $userinfo['balance'] = $dBalance->where("userid=" .session('familyuserid'))->getField("balance");
        $this->assign('userinfo',$userinfo);
    
        $this->display();
    }
    
    
    public function edit_pwd()
    {
        $this->assign("familyusername", lan("FAMILY_USER_NAME", "Family"));
        $this->assign("oldpassword", lan("FAMILY_OLDPASSWORD", "Family"));
        $this->assign("newpassword", lan("FAMILY_NEWPASSWORD", "Family"));
        $this->assign("confirmpassword", lan("FAMILY_CONFIRMPASSWORD", "Family"));
        $this->assign("submitbuttion", lan("SUBMIT_BUTTION", "Family"));
        $this->assign("donotchangepwd", lan("DONOT_CHANGEPWD", "Family"));
        $this->assign("pwdissixtotwenty", lan("PWD_ISSIX_TO_TWENTY", "Family"));
        $this->assign("pwdissixtotwentyv", lan("PWD_ISSIX_TO_TWENTYV", "Family"));
        $this->assign("oldpwdisright", lan("OLD_PWD_ISRIGHT", "Family"));
        $this->assign("oldpwdiswrong", lan("OLD_PWD_ISWRONT", "Family"));
        $this->assign("pleaswait", lan("PLEASE_WAIT", "Family"));
        $this->assign("twicepwdnotsame", lan("TWICE_PWD_NOTSAME", "Family"));
        $this->assign("twicepwdissame", lan("TWICE_PWD_ISSAME", "Family"));
        $this->assign("inputoldpassword", lan("INPUT_OLD_PASSWORD", "Family"));
        $this->assign("inputnewpassword", lan("INPUT_NEW_PASSWORD", "Family"));
        $this->assign("inputconfirmpassword", lan("INPUT_CONFIRM_PASSWORD", "Family"));
        $this->assign("newconfirmnotsame", lan("NEW_CONFIRM_NOT_SAME", "Family"));
        
        
        if($_GET['action'] == 'edit_pwd_ajax'){
            
            $dMember = new \Common\Model\MemberModel();
            $userinfo = $dMember->where("userid='".session("familyuserid")."'")->find();
            $postpassword = I('get.old_password','','md5');
            $password = md5($postpassword . $userinfo['salt']);
            
            if($password == $userinfo['password']){
                echo '1';
            }
            else{
                echo '0';
            }
            exit;
        }
    
        $this->display();
    }
    
    public function do_edit_pwd()
    {
        $dMember = new \Common\Model\MemberModel();
        
        if ($_POST['new_password'] != '') {
            if ($_POST['old_password'] == '') {
                $this->error(lan("INPUT_OLD_PASSWORD", "Family"));
            }
            if ($_POST['new_password'] != $_POST['new_pwdconfirm']) {
                $this->error(lan("NEW_CONFIRM_NOT_SAME", "Family"));
            }
        }
        $userinfo = $dMember->where("userid='".session("familyuserid")."'")->find();
        $newpassword = I('new_password','','md5');
        $password = md5($newpassword . $userinfo['salt']);
  
        $updatefeild = array(
            'userid'  =>$userinfo['userid'],
            'password' => $password
        );
        
        //更新密码
        $dMember->save($updatefeild);
        
        session('familyuserid',null);
        session('familyusename',null);
        
        //redirect(U('login'));
        
        $this->assign('jumpUrl', U('login'));
        $this->success(lan("CHANGE_PWD_SUCCESSFUL", "Family"));
    }

    public function add_emcee()
    {
        $keyword = I('keyword','','trim');
        $condition = array(
            'isemcee' => 1,
        );
        $dMember = new \Common\Model\MemberModel();

        if($keyword != '' && $keyword != lan("INPUT_USERID_USERNMAE", "Family")){
            if(is_numeric($keyword)){
                $condition['userid'] = $keyword;
            }
            else{
                $condition['nickname'] = array('like', '%'.$keyword.'%');
            }
            $emcee = $dMember->where($condition)->find();
        }

        if ($emcee['familyid'] > 0)
        {
            $this->error(lan('JOIN_OTHER_FAMILY_ERROR', 'Family'));
        }
        else{
            $this->assign("emcee", $emcee);
        }

        $this->display();
    }

    public function do_addEmcee()
    {
        $userid = I('POST.userid');
        $newUserInfo['familyid'] = session('familyid');
        $userCond = array(
            'userid' =>  $userid
        );
        $db_Member = M('Member');
        $db_Member->where($userCond)->save($newUserInfo);
        $db_Family = M('Family');
        $familyCond = array(
            'familyid' =>  session('familyid')
        );
        $db_Family->where($familyCond)->setInc('emceecount', 1);
        $db_Family->where($familyCond)->setInc('totalcount', 1);
        $this->success(lan('OPERATION_SUCCESSFUL', 'Family'));
    }

    public function view_myemcee()
    {      
        $this->assign("pleasechoose", lan("PLEASE_CHOOSE", "Family"));
        
        $condition = 'familyid='.session('familyid') . ' and isemcee = 1';
        
        if($_POST['start_time'] != ''){
            /* $timeArr = explode("-", $_POST['start_time']);
            $unixtime = mktime(0,0,0,$timeArr[1],$timeArr[2],$timeArr[0]); //$timeArr[0] 年  $timeArr[1] 月  $timeArr[2] 日 */
            $condition .= " and registertime>='".$_POST['start_time']."'";
        }
        if($_POST['end_time'] != ''){
            $condition .= " and registertime<='".$_POST['end_time']."'";
        }
        
        $keyword = I('keyword','','trim');
        
        if($keyword != '' && $keyword != lan("INPUT_USERID_USERNMAE", "Family")){
            if(is_numeric($keyword)){
                $condition .= ' and userid='.$keyword;
            }
            else{
                $condition .= ' and nickname like \'%'.$keyword.'%\'';
            }
        }
        
        
        $pageno = 0;
        $pagesize =20;
        if($_POST['pageno'] != ''){
            $pageno = $_POST['pageno'];
        }
        if($_POST['pagesize'] != ''){
            $pagesize = $_POST['pagesize'];
        }
        
        $dMember = new \Common\Model\MemberModel();
        //$orderby = 'userid desc';
        $count = $dMember->where($condition)->count();
        
        $page = getpage($count,$pagesize);
        $members = $dMember->limit($page->firstRow, $page->listRows)->where($condition)->select();
        //$members = $dMember->limit($pageno*$pagesize.','.$pagesize)->where($condition)->order($orderby)->select();
        
        $dBalance = new \Common\Model\BalanceModel();
        foreach($members as $seq=>$userinfo) {
            $members[$seq]['earnmoney'] = $dBalance->where("userid=" .$userinfo['userid'])->getField("earnmoney");
        }
        $this->assign('members',$members);
        $this->assign('page',$page->show());
        $this->display();
    }
    
    public function view_showmoney(){
        $condition = 'familyid='.session('familyid') . ' and isemcee = 1';
        
        if($_POST['start_time'] != ''){
            $condition .= " and registertime>='".$_POST['start_time']."'";
        }
        if($_POST['end_time'] != ''){
            $condition .= " and registertime<='".$_POST['end_time']."'";
        }
        
        $pageno = 0;
        $pagesize =20;
        if($_POST['pageno'] != ''){
            $pageno = $_POST['pageno'];
        }
        if($_POST['pagesize'] != ''){
            $pagesize = $_POST['pagesize'];
        }
        
        $dMember = new \Common\Model\MemberModel();
        $orderby = 'userid desc';
        $count = $dMember->where($condition)->count();
        
        $page = getpage($count,$pagesize);
        
        $members = $dMember->limit($page->firstRow, $page->listRows)->where($condition)->order($orderby)->select();
        //$members = $dMember->limit($pageno*$pagesize.','.$pagesize)->where($condition)->order($orderby)->select();
        
        $dBalance = new \Common\Model\BalanceModel();
        
        foreach($members as $seq=>$userinfo) {
            $balinfor = $dBalance->where("userid=" .$userinfo['userid'])->find();
            $members[$seq]['balance'] = $balinfor['balance'];
            $members[$seq]['spendmoney'] = $balinfor['spendmoney'];
            $members[$seq]['earnmoney'] = $balinfor['earnmoney'];
            $members[$seq]['point'] = $balinfor['point'];
            $members[$seq]['createtime'] = $balinfor['createtime'];
        }
        $this->assign('balances',$members);
        $this->assign('page',$page->show());
        $this->display();
    }
    
    public function view_familyDetail()
    {
        $condition = 'familyid='.session('familyid');
        
        if($_POST['start_time'] != ''){
            $condition .= " and tradetime>='".$_POST['start_time']."'";
        }
        if($_POST['end_time'] != ''){
            $condition .= " and tradetime<='".$_POST['end_time']."'";
        }
        
        
        $keyword = I('keyword','','trim');
        
        if($keyword != '' && $keyword != lan("INPUT_USERID_USERNMAE", "Family")){
            if(is_numeric($keyword)){
                $condition .= ' and userid='.$_POST['keyword'];
            }
            else{

                $dMember = new \Common\Model\MemberModel();
                $userid = $dMember->where("nickname like '%". $keyword. "%'")->getField(userid);
                if($userid){
                    $condition .= ' and userid='.$userid;
                }
            }
        }
        
        $pageno = 0;
        $pagesize =20;
        if($_POST['pageno'] != ''){
            $pageno = $_POST['pageno'];
        }
        if($_POST['pagesize'] != ''){
            $pagesize = $_POST['pagesize'];
        }
        
        
        
        $dearnDetail = new \Common\Model\EarndetailModel();
        $orderby = 'userid asc';
        $count = $dearnDetail->where($condition)->count();
        
        $page = getpage($count,$pagesize);
    
        $earndetails = $dearnDetail->limit($page->firstRow, $page->listRows)->where($condition)->order($orderby)->select();
        //$members = $dMember->limit($pageno*$pagesize.','.$pagesize)->where($condition)->order($orderby)->select();
    
        $dMember = new \Common\Model\MemberModel();
        $field = array(
            'userid', 'nickname' , 'bigheadpic'
        );
        
        foreach($earndetails as $seq=>$earninfo) {
            $userinfo = $dMember->where("userid=" .$earninfo['userid'])->field($field)->find();
            $userinfofrom = $dMember->where("userid=" .$earninfo['fromid'])->field($field)->find();
            $earndetails[$seq]['nickname'] = $userinfo['nickname'];
            $earndetails[$seq]['fromnickname'] = $userinfofrom['nickname'];

        }
        
        $this->assign('earndetails',$earndetails);
        $this->assign('page',$page->show());
        $this->display();
    }
    
    public function userDetail()
    {
        $userid = $_GET['userid'];
                
        if(empty($userid)){
            $userid = $_POST['userid'];
        }

        $condition = 'userid='.$userid.' or fromid='. $userid;
    
        if($_POST['start_time'] != ''){
            $condition .= " and tradetime>='".$_POST['start_time']."'";
        }
        if($_POST['end_time'] != ''){
            $condition .= " and tradetime<='".$_POST['end_time']."'";
        }
    
        $pageno = 0;
        $pagesize =20;
        if($_POST['pageno'] != ''){
            $pageno = $_POST['pageno'];
        }
        if($_POST['pagesize'] != ''){
            $pagesize = $_POST['pagesize'];
        }
    
        $dearnDetail = new \Common\Model\EarndetailModel();
        $orderby = 'userid asc';
        $count = $dearnDetail->where($condition)->count();
        
        $page = getpage($count,$pagesize);
    
        $earndetails = $dearnDetail->limit($page->firstRow, $page->listRows)->where($condition)->order($orderby)->select();
        //$members = $dMember->limit($pageno*$pagesize.','.$pagesize)->where($condition)->order($orderby)->select();
    
        $dMember = new \Common\Model\MemberModel();
        $field = array(
            'userid', 'nickname' , 'bigheadpic'
        );
    
        foreach($earndetails as $seq=>$earninfo) {
            $userinfo = $dMember->where("userid=" .$earninfo['userid'])->field($field)->find();
            $userinfofrom = $dMember->where("userid=" .$earninfo['fromid'])->field($field)->find();
            $earndetails[$seq]['nickname'] = $userinfo['nickname'];
            $earndetails[$seq]['fromnickname'] = $userinfofrom['nickname'];
    
        }
        $this->assign('earndetails',$earndetails);
        $this->assign('page',$page->show());
        $this->display();
    }
}