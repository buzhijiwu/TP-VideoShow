<?php
namespace Home\Controller;

class UsercenterController extends CommonController
{

    private function checkCurUserId()
    {
        if (!(session('userid') > 0))
        {
            redirect(U('/Index/index'));
        }
    }

    /**
     * 基本资料
     */
    public function index()
    {
        $this->checkCurUserId();
        //基本资料
        $db_Member = D('Member');
        $userInfo = $db_Member->getMemberInfoByUserid(session('userid'));
        $userGrade = $db_Member->getUserGrade(session('userid'));
        $userInfo = array_merge($userInfo,$userGrade);

        $this->assign('userinfo', $userInfo);
        /* 左侧菜单 */
        $this->assign('menu',1);
        $this->display();
    }

    /**
     * 我的财富
     */
    public function mywealth()
    {
        $this->checkCurUserId();
        $userCond = array(
            'userid' => session('userid'),
        );
        $db_Balance = D('Balance');
        $balanceInfo = $db_Balance->getBalanceByUserid($userCond);
        $this->assign('balanceInfo', $balanceInfo);
        /* 左侧菜单 */
        $this->assign('menu',1);
        $this->display();
    }

    /**
     * 我的特权
     */
    public function myprivilege(){
        $this->checkCurUserId();
        //获取用户未过期的VIP列表
        $where_vip = array(
            'vr.userid' => session('userid'),
            'vr.expiretime' => array('gt',date('Y-m-d H:i:s')),
            'vd.lantype' => $this->lan
        );
        $vip_record = M('viprecord vr')
            ->join('LEFT JOIN ws_vipdefinition vd ON (vd.vipid = vr.vipid)')
            ->field('vr.vipid, vr.effectivetime, vr.expiretime, vd.vipname, vd.pcsmallviplogo')
            ->where($where_vip)
            ->order('expiretime DESC')
            ->select();
        $my_vip = array();
        foreach($vip_record as $key => $val){
            $my_vip[$val['vipid']]['vipid'] = $val['vipid'];
            $my_vip[$val['vipid']]['vipname'] = $val['vipname'];
            $my_vip[$val['vipid']]['pcsmallviplogo'] = $val['pcsmallviplogo'];
            $my_vip[$val['vipid']]['effectivetime'] = $val['effectivetime'];
            if(!$my_vip[$val['vipid']]['expiretime']){
                $my_vip[$val['vipid']]['expiretime'] = $val['expiretime'];
            }
        }

        //获取特权列表
        $db_Privilege = D('Privilege');
        $privileges = $db_Privilege->getMyVipPrivileges(session('userid'), $this->lan);
        if (!$privileges){
            $vipDefinPprivileges = $db_Privilege->getVipDefinePrivileges(2, $this->lan);//查询最高级别vip特权
            $this->assign('vipDefinPprivileges', $vipDefinPprivileges);
        }

        $this->assign('my_vip', $my_vip);
        $this->assign('privileges', $privileges);
        /* 左侧菜单 */
        $this->assign('menu',1);
        $this->display();
    }

    /**
     * 我的座驾
     */
    public function mycar()
    {
        $this->checkCurUserId();
        //获取用户未过期的座驾列表
        $where_equipment = array(
            'e.userid' => session('userid'),
            'e.expiretime' => array('gt',date('Y-m-d H:i:s')),
            'c.lantype' => $this->lan
        );
        $my_equipment = M('equipment e')
            ->join('LEFT JOIN ws_commodity c ON (e.commodityid = c.commodityid)')
            ->field('e.commodityid, e.effectivetime, e.expiretime, e.isused, c.commodityname, c.pcbigpic')
            ->where($where_equipment)
            ->order('equipid ASC')
            ->select();
        $equipments = array();
        foreach($my_equipment as $key => $val){
            $equipments[$val['commodityid']]['commodityid'] = $val['commodityid'];
            $equipments[$val['commodityid']]['commodityname'] = $val['commodityname'];
            $equipments[$val['commodityid']]['pcbigpic'] = $val['pcbigpic'];
            $equipments[$val['commodityid']]['expiretime'] = $val['expiretime'];
            if(!$equipments[$val['commodityid']]['effectivetime']){
                $equipments[$val['commodityid']]['effectivetime'] = $val['effectivetime'];
            }
            if(!$equipments[$val['commodityid']]['isused']){
                $equipments[$val['commodityid']]['isused'] = $val['isused'];
            }
        }

        if (!$equipments){
            $db_Commodity = D('Commodity');
            $randcars = $db_Commodity->getCommodityByType(1, $this->lan);//查询汽车的数据
            $this->assign('randcars', $randcars);
        }

        $this->assign('equipments', $equipments);
        /* 左侧菜单 */
        $this->assign('menu',1);
        $this->display();
    }

    /**
     * 我的家族
     */
    public function myfamily()
    {
        $this->checkCurUserId();
        $userCond = array(
            'userid' => session('userid')
        );
        $db_Member = D('Member');
        $db_Family = D('Family');
        $userInfo = $db_Member->getMemberInfo($userCond);
        $myFamily = $db_Family->getFamilyInfo($userInfo['familyid']);
        if (!$myFamily)
        {
            $randFamily = $db_Family->getRandFamily();
            $this->assign('randfamily', $randFamily);
        }

        $this->assign('myFamily', $myFamily);

        /* 左侧菜单 */
        $this->assign('menu',1);
        $this->display();
    }

    /**
     * 收到礼物
     */
    public function recivegift()
    {
        $this->checkCurUserId();
        $queryCond = $this->getTimeCond('tradetime');
        $queryCond['userid'] = session('userid');
        $queryCond['tradetype'] = array('in', '0,9');//0表示收到礼物,9表示守护

        $db_Earndetail = D('Earndetail');
        $count = $db_Earndetail->where($queryCond)->count();
        $page = getConfigPage($count,10);
        $reciveGifts = $db_Earndetail->getReciveGifts($queryCond, $page);
        $this->assign('reciveGifts', $reciveGifts);
        $this->assign('page',$page->show());
        /* 左侧菜单 */
        $this->assign('menu',2);
        $this->display();
    }

    private function getTimeCond($timeFieldName )
    {
        $timeCond = array();
        $start = I('get.start');
        $end = I('get.end');

        if ('' == $start && '' != $end) {
          $timeCond[$timeFieldName][0] = array('egt', '1970-01-01 00:00:00');
          $timeCond[$timeFieldName][1] = array('lt', $end.' 23:59:59');   
        }
        if ('' != $start && '' == $end) {
          $timeCond[$timeFieldName][0] = array('egt', $start.' 00:00:00');  
          $timeCond[$timeFieldName][1] = array('lt', date('Y-m-d H:i:s'));           
        }        
        if ('' != $start && '' != $end) {
          $timeCond[$timeFieldName][0] = array('egt', $start.' 00:00:00');
          $timeCond[$timeFieldName][1] = array('lt', $end.' 23:59:59');     
        }
        $this->assign('starttime', $start);
        $this->assign('endtime', $end);
        return $timeCond;
    }

    /**
     * 送出礼物
     */
    public function sendgift()
    {
        $this->checkCurUserId();
        $queryCond = $this->getTimeCond('tradetime');
        $queryCond['userid'] = session('userid');
        $queryCond['tradetype'] = 1;//tradetype为1表示送礼物
        $db_Spenddetail = D('Spenddetail');
        $count = $db_Spenddetail->where($queryCond)->count();
        $page = getConfigPage($count,10);
        $spendGifts = $db_Spenddetail->getSendGifts($queryCond, $page);
        $this->assign('spendGifts', $spendGifts);
        $this->assign('page',$page->show());
        /* 左侧菜单 */
        $this->assign('menu',2);
        $this->display();
    }

    /**
     * 充值记录
     */
    public function rechargelist()
    {
        $this->checkCurUserId();
        $queryCond = $this->getTimeCond('rechargetime');
        $queryCond['userid'] = session('userid');
        $db_Rechargedetail = D('Rechargedetail');
        $count = $db_Rechargedetail->where($queryCond)->count();
        $page = getConfigPage($count,10);
        $rechargeList = $db_Rechargedetail->getRechargeList($queryCond, $page);
        $this->assign('rechargeList', $rechargeList);
        $this->assign('page',$page->show());
        /* 左侧菜单 */
        $this->assign('menu',2);
        $this->display();
    }

    /**
     * 消费记录
     */
    public function consumelist()
    {
        $this->checkCurUserId();
        $queryCond = $this->getTimeCond('tradetime');
        $queryCond['userid'] = session('userid');
        $queryCond['tradetype'] = array('in', '2,4,6,7,8,9');//tradetype为1表示送礼物
        $db_Spenddetail = D('Spenddetail');
        $count = $db_Spenddetail->where($queryCond)->count();
        $page = getConfigPage($count,10);
        $consumeList = $db_Spenddetail->getConsumeList($queryCond, $page);

        $this->assign('consumeList', $consumeList);
        $this->assign('page',$page->show());
        /* 左侧菜单 */
        $this->assign('menu',2);
        $this->display();
    }

    /**
     * 历史足迹
     */
    public function seehistory(){
        $this->checkCurUserId();

        $queryCond['userid'] = session('userid');
        $count = count(M('Seehistory')->where($queryCond)->group('liveid')->select());
        $page = getConfigPage($count,8);
        $seehistory = D('Seehistory')->getSeeHisEmceesByPage($queryCond, $page);

        $this->assign('seehistory', $seehistory);
        $this->assign('page',$page->show());

        /* 左侧菜单 */
        $this->assign('menu',3);
        $this->display();
    }

    /**
     * 我的关注
     */
    public function attention(){
        $this->checkCurUserId();

        $queryCond['userid'] = session('userid');
        $queryCond['status'] = 0;
        $count = M('Friend')->where($queryCond)->count();
        $page = getConfigPage($count,8);
        $attentEmcees = D('Friend')->getFriendEmceesByPage($queryCond, $page);

        $this->assign('attentEmcees', $attentEmcees);
        $this->assign('page',$page->show());

        /* 左侧菜单 */
        $this->assign('menu',3);
        $this->display();
    }

    /**
     * 我的守护
     */
    public function guard(){
        $this->checkCurUserId();

        $db_Guard = D('Guard');
        $db_Guarddefinition = D('Guarddefinition');
        $guardCond = array(
            'userid' => session('userid'),
            'expiretime' => array('gt', date("Y-m-d H:i:s" ,time()))
        );
        $count = $db_Guard->where($guardCond)->count();
        $page = getConfigPage($count,6);
        $guardEmcees = $db_Guard->getGuardByPage($guardCond, $page);
        $guardDefs = $db_Guarddefinition->getAllGuards($this->lan);

        $this->assign('guardEmcees', $guardEmcees);
        $this->assign('guarddefs', $guardDefs);
        $this->assign('page',$page->show());
        /* 左侧菜单 */
        $this->assign('menu',3);
        $this->display();
    }

    /**
     * 消息
     */
    public function message()
    {
        $this->checkCurUserId();
        $db_Message = D('Message');

        $messageCond = array(
            'userid' => session('userid'),
            'lantype' => $this->lan
        );
        $count = $db_Message->where($messageCond)->count();
        $page = getConfigPage($count,10);
        $messages = $db_Message->getAllMessagesByPage($messageCond, $page);
        $this->assign('messages', $messages);
        $this->assign('page',$page->show());
        /* 左侧菜单 */
        $this->assign('menu',4);
        $this->display();
    }

    /**
     * 消息查看
     */
    public function message_read()
    {
        $this->checkCurUserId();
        $db_Message = D('Message');
        $messageid = I('POST.messageid');
        $type = I('POST.type');
        if (1 == $type) {
            $editData['read'] = 1;
            $mesgCond = array(
                'messageid' => $messageid
            );
            $db_Message->where($mesgCond)->save($editData);  
            if ($db_Message) {
                echo 1;
            }          
        }        
    }

    /**
     * 消息删除
     */
    public function message_del()
    {
        $this->checkCurUserId();
        $db_Message = D('Message');
        $messageid = I('POST.messageid');
        $type = I('POST.type');
        if (0 == $type)
        {
            $mesgCond = array(
                'messageid' => $messageid
            );
            $db_Message->where($mesgCond)->delete();
            if ($db_Message) {
                echo 1;
            }             
        }        
    }    

    /**
     * 账号设置
     */
    public function setting()
    {
        $this->checkCurUserId();
        //基本资料
        $db_Member = D('Member');
        $userInfo = $db_Member->getMemberInfoByUserid(session('userid'));
        $this->assign('userinfo', $userInfo);

        /* 左侧菜单 */
        $this->assign('menu', 5);
        $this->display();
    }

    public function modifyUserInfo()
    {
        $this->checkCurUserId();
        $db_Member = M('Member');
        $editData['nickname'] = I('POST.nickname');
        //验证用户昵称是否包含脏话
        $filterWords = getFilterWords();
        $arr = array();
        foreach ($filterWords as $k => $val) {
            $arr = explode(' ', $val);
            $str = '';
            foreach ($arr as $key => $value) {
                $str = $str.'('.$value.')|';
            }
            $str = substr($str,0,strlen($str)-1);
            if (preg_match('/^'.$str.'$/',$editData['nickname'])) {
                $result['status'] = 0;
                $result['msg'] = lan('NICKNAME_ILLEGAL','Home');
                echo json_encode($result);exit;
            }
        }
        //验证用户昵称是否有特殊字符，目前只过滤"<"、">"、"/"、"\"、"'"、"""、"?"
        if (preg_match("/<|>|\/|\\\\|\'|\"|\?/",$editData['nickname'])) {
            $res['status'] = 0;
            $res['message'] = lan('CHAR_ILLEGAL','Home');
            echo json_encode($res);exit;
        }
        //验证用户昵称长度
        if (strlen($editData['nickname'])>50 || empty($editData['nickname'])) {
            $result['status'] = 0;
            $result['msg'] = lan('NICKNAME_ISONE_TO_FIFTY', 'Home');
            echo json_encode($result);exit;
        }
        //验证用户昵称是否存在
        $nickname = $editData['nickname'];
        $userid = session('userid');
        $nicknameCond['_string'] = 'BINARY nickname = "'.$nickname.
            '" AND userid != '.$userid;
        $userNickInfo = $db_Member
            ->field('userid')
            ->where($nicknameCond)
            ->find();
        if ($userNickInfo) {
            $result['status'] = 301;
            echo json_encode($result);exit;            
        }
        $editData['sex'] = I('POST.sex');
//        $editData['province'] = I('POST.province', '', 'tirm');
//        $editData['city'] = I('POST.city', '', 'tirm');
//        $editData['county'] = I('POST.county', '', 'tirm');
        $editData['birthday'] = I('POST.birthday');
        $editData['email'] = I('POST.email');
        $userCond = array(
            'userid' => session('userid')
        );

        $db_Member->where($userCond)->save($editData);

        //替换session中的昵称
        session('nickname',$editData['nickname']);

        $result['status'] = 1;
        $result['msg'] = lan('OPERATION_SUCCESSFUL', 'Home');
        echo json_encode($result);
    }

    /**
     * 修改头像
     */
    public function smallheadpic(){
        $this->checkCurUserId();
        $smallheadpic = M('Member')->where("userid='".session('userid')."'")->getField('smallheadpic');
        $this->assign('smallheadpic', $smallheadpic);

        /* 左侧菜单 */
        $this->assign('menu',5);
        $this->display();
    }

    public function modSmallHeadpic()
    {
        $this->checkCurUserId();
        $db_Member = D("Member");
        if(!empty($_POST)){
            $base64 = I('POST.headpic');
            if($base64){
                $base64_image = str_replace(' ', '+', $base64);
                //post的数据里面，加号会被替换为空格，需要重新替换回来，如果不是post的数据，则注释掉这一行
                if (preg_match('/^(data:\s*image\/(\w+);base64,)/', $base64_image, $result)){
                    //匹配成功
                    if($result[2] == 'jpeg'){
                        $image_name = date('YmdHis').'_'.session('userid').'.jpg';
                    }else{
                        $image_name = date('YmdHis').'_'.session('userid').'.'.$result[2];
                    }
                    $image_file = "/Uploads/HeadImg/120120/".$image_name;
                    //服务器文件存储路径
                    $uploadStatus = file_put_contents(".".$image_file, base64_decode(str_replace($result[1], '', $base64_image)));
                    if (!$uploadStatus){    //上传失败
                        $result['status'] = 1;
                        $result['msg'] = lan('OPERATION_FAILED', 'Home');
                        $this->ajaxReturn($result);
                    }else{
                        //文件上传远程服务器
                        $ftpUpload = ftpUpload($image_file, $image_file);
                        if($ftpUpload['code'] != 200){
                            $result['status'] = 1;
                            $result['msg'] = lan('OPERATION_FAILED', 'Home');
                            $this->ajaxReturn($result);
                        }
                    }
                    $userCond = array(
                        'userid' => session('userid')
                    );
                    $userInfo['smallheadpic'] = $image_file;
                    $userInformation = $db_Member->where($userCond)->find();
                    $oldsmallheadpic = $userInformation['smallheadpic'];
                    $res = $db_Member->where($userCond)->save($userInfo);
                    //删除原路径图片
                    if ($res && $userInfo['smallheadpic'] != $oldsmallheadpic && $oldsmallheadpic != '/Public/Public/Images/HeadImg/default.png') {
                        ftpDelete($oldsmallheadpic);
                    }                    
                    //替换session中的头像
                    session('smallheadpic',$userInfo['smallheadpic']);
                }
            }

            $result['status'] = 0;
            $result['msg'] = lan('OPERATION_SUCCESSFUL', 'Home');
            $this->ajaxReturn($result);
        }
    }

    public function bigheadpic(){
        $this->checkCurUserId();
        $bigheadpic = M('Member')->where("userid='".session('userid')."'")->getField('bigheadpic');
        $this->assign('bigheadpic', $bigheadpic);

        /* 左侧菜单 */
        $this->assign('menu',5);
        $this->display();
    }

    public function modBigHeadpic()
    {
        $this->checkCurUserId();
        $db_Member = D("Member");
        if(!empty($_POST)){
            $base64 = I('POST.headpic');
            $base64_image = str_replace(' ', '+', $base64);
            //post的数据里面，加号会被替换为空格，需要重新替换回来，如果不是post的数据，则注释掉这一行
            if (preg_match('/^(data:\s*image\/(\w+);base64,)/', $base64_image, $result)){
                //匹配成功
                if($result[2] == 'jpeg'){
                    $image_name = date('YmdHis').'_'.session('userid').'.jpg';
                }else{
                    $image_name = date('YmdHis').'_'.session('userid').'.'.$result[2];
                }
                $image_file = "/Uploads/HeadImg/268200/".$image_name;
                //服务器文件存储路径
                $uploadStatus = file_put_contents(".".$image_file, base64_decode(str_replace($result[1], '', $base64_image)));
                if ($uploadStatus){
                    //文件上传远程服务器
                    $ftpUpload = ftpUpload($image_file, $image_file);
                    if($ftpUpload['code'] != 200){
                        $result['status'] = 1;
                        $result['msg'] = lan('OPERATION_FAILED', 'Home');
                        echo json_encode($result);exit;
                    }
                    $userCond = array(
                        'userid' => session('userid')
                    );
                    $userInfo['bigheadpic'] = $image_file;
                    $userInformation = $db_Member->where($userCond)->find();
                    $oldbigheadpic = $userInformation['bigheadpic'];
                    $res = $db_Member->where($userCond)->save($userInfo);
                    //删除原路径图片
                    if ($res && $userInfo['bigheadpic'] != $oldbigheadpic) {
                        unlink('.'.$oldbigheadpic);
                    }                     
                    
                    session('bigheadpic',$image_file);
                    cookie('bigheadpic',$image_file,604800);

                    $result['status'] = 0;
                    $result['msg'] = lan('OPERATION_SUCCESSFUL', 'Home');
                    echo json_encode($result);
                }else{
                    $result['status'] = 1;
                    $result['msg'] = lan('OPERATION_FAILED', 'Home');
                    echo json_encode($result);
                }
            }else{
                $result['status'] = 0;
                $result['msg'] = lan('OPERATION_SUCCESSFUL', 'Home');
                echo json_encode($result);                
            }
        }
    }

    //更新封面头像
    public function updateBigheadpic(){
        //验证用户
        $userid = I('post.userid',0);
        if($userid < 0){
            $result = array(
                'status' => 0,
                'msg' => lan('YOU_NOT_LOGIN_RETRY','Home')
            );
            $this->ajaxReturn($result);
        }

        //验证参数
        $x = I('post.x',0); //原图裁剪左上角，x坐标
        $y = I('post.y',0);//原图裁剪左上角，y坐标
        $width = I('post.width',0); //裁剪保存宽度
        $height = I('post.height',0); //裁剪保存高度
        $rotate = I('post.rotate',0); //旋转角度
        if($width <= 0 ||  $height <= 0){
            $result = array(
                'status' => 0,
                'msg' => lan('PARAMETER_ERROR','Home')
            );
            $this->ajaxReturn($result);
        }

        $image_file = "/HeadImg/268200/";   //保存目录
        // 实例化上传类
        $upload = new \Think\Upload();
        $upload->autoSub = false;   //自动使用子目录保存上传文件 默认为true
        $upload->exts = array('jpg', 'gif', 'png', 'jpeg'); // 设置附件上传类型
        $upload->savePath = $image_file;     // 设置附件上传目录
        $info = $upload->upload();  // 上传文件

        // 上传失败
        if(!$info){
            $result = array(
                'status' => 0,
                'msg' => lan('HEAD_PIC_UPLOAD_FAILED','Home')
            );
            $this->ajaxReturn($result);
        }

        //图片裁剪
        $src = 'Uploads'.$info['bigheadpic']['savepath'].$info['bigheadpic']['savename'];
        $savePath = 'Uploads'.$image_file;
        $imageName = date('YmdHis').'_'.$userid;
        $res = imageCut($src,$width,$height,$x,$y,0,$rotate,0,2,$savePath,$imageName);//裁剪图片获取保存路径
        if(!$res){
            $result = array(
                'status' => 0,
                'msg' => lan('PICTURE_CUT_FAIL','Home')
            );
            $this->ajaxReturn($result);
        }
        unlink($src);   //删除上传的图片

        //文件上传远程服务器
        $save_src = '/'.$res;
        $ftpUpload = ftpUpload($save_src,$save_src);
        if($ftpUpload['code'] != 200){
            $result = array(
                'status' => 0,
                'msg' => lan('PICTURE_SAVE_FAIL','Home')
            );
            $this->ajaxReturn($result);
        }
        //更新数据
        $res = M('member')->where(array('userid'=>$userid))->save(array('bigheadpic'=>$save_src));
        if($res === false){
            $result = array(
                'status' => 0,
                'msg' => lan('PICTURE_SAVE_FAIL','Home')
            );
            $this->ajaxReturn($result);
        }
        $this->updateSessionCookie($userid);
        $result = array(
            'status' => 1,
            'msg' => lan('OPERATION_SUCCESSFUL','Home'),
            'src' => $save_src
        );
        $this->ajaxReturn($result);
    }

    /**
     * 修改密码
     */
    public function modifypwd()
    {
        $this->checkCurUserId();

        /* 左侧菜单 */
        $this->assign('menu',5);
        $this->display();
    }

    /**
     * 修改密码
     */
    public function doModifyPwd()
    {
        $this->checkCurUserId();
        $db_Member = D('Member');
        // $userInfo = $db_Member->getMemberInfoByUserid(session('userid'));
        $field = array(
            'userid', 'userno', 'username', 'password', 'salt'
        );
        $userInfo = $db_Member->where(array('userid' => session('userid')))->field($field)->find();  

        //校验密码长度
        $field_psw = array (
                'oldpwd', 'newpwd', 'confirmpwd'
        );
        $rule = array (
            // 新密码长度校验
            array('newpwd', '/^[0-9a-zA-Z_]{6,16}$/is', lan("PASSWORD_LENGTH_ERROR","Common")),
        );
        $res_psw = $db_Member->field($field_psw)->validate($rule)->create();
        if (!$res_psw) {
            $errorInfo = array(
                'status' => 4,
                'msg' => $db_Member->getError(),
            );
            echo json_encode($errorInfo);
            die();
        }
              
        $inputParams = array(
            'oldpwd' => trim(I("post.oldpwd")),
            'newpwd' => trim(I("post.newpwd")),
            'confirmpwd' => trim(I("post.confirmpwd"))
        );
        $mdOldpwd = md5(md5($inputParams['oldpwd']) . $userInfo['salt']);
        
        if ($mdOldpwd != $userInfo['password']) {
            //$this->error(lan('OLD_PASSWORD_ERROR', 'Home'));
            $result['status'] = 2;
            $result['msg'] = lan('OLD_PASSWORD_ERROR', 'Home');
            echo json_encode($result);
            die;
        }

        if ($inputParams['newpwd'] != $inputParams['confirmpwd']) {
            //$this->error(lan('CONFIRM_PWD_ERROR', 'Home'));
            $result['status'] = 3;
            $result['msg'] = lan('CONFIRM_PWD_ERROR', 'Home');
            echo json_encode($result);
            die;
        }

        $rule = array('password', '/^[0-9a-zA-Z_]{6,16}$/is', lan("PASSWORD_LENGTH_ERROR", "Common"));
        $editData['password'] = md5(md5($inputParams['newpwd']) . $userInfo['salt']);
        $result = $db_Member->where(array('userid' => session('userid')))->validate($rule)->save($editData);

        if ($result !== false) {
            //$this->success(lan('OPERATION_SUCCESSFUL', 'Home'));
            $res['status'] = 0;
            $res['msg'] = lan('OPERATION_SUCCESSFUL', 'Home');
            echo json_encode($res);
        } else {
            $res['status'] = 1;
            $res['msg'] = lan('OPERATION_FAILED', 'Home');
            echo json_encode($res);
            //$this->error(lan('OPERATION_FAILED', 'Home'));
        }

    }

    public function readMessage()
    {
        $this->checkCurUserId();
        $db_Message = D('Message');
        $messageid = I('POST.messageid');
        $editData['read'] = 1;
        $mesgCond = array(
            'messageid' => $messageid
        );

        $db_Message->where($mesgCond)->save($editData);
        $result['status'] = 0;
        $result['msg'] = lan('OPERATION_SUCCESSFUL', 'Home');
        echo json_encode($result);
    }

    public function modEquipment(){
        $this->checkCurUserId();
        $db_Equipment = D('Equipment');
        $commodityid = I('POST.commodityid');

        $notUsed['isused'] = 0;
        $notUsedCond = array(
            'userid' => session('userid'),
            'isused' => 1
        );
        $db_Equipment->where($notUsedCond)->save($notUsed);
        $isUsed['isused'] = 1;
        $usedCond = array(
            'userid' => session('userid'),
            'commodityid' => $commodityid,
            'expiretime' => array('gt',date('Y-m-d H:i:s'))
        );
        $db_Equipment->where($usedCond)->save($isUsed);

        $result['status'] = 1;
        $result['message'] = lan('SELECT_NEW_CAR_SUCCESSFUL', 'Home');
        echo json_encode($result);
    }

    /**
     * 秀币兑换秀豆
     */
    public function showbeanexchange(){
        $this->checkCurUserId();
        $queryCond = $this->getTimeCond('addtime');
        $queryCond['userid'] = session('userid');
        $queryCond['type'] = array('eq', 1); //兑换类型：1.秀币换秀豆、2.秀豆换秀币        
        $db_Exchangerecord = D('Exchangerecord');
        $count = $db_Exchangerecord->where($queryCond)->count();
        $page = getConfigPage($count,10);
        $exchangeRecord = $db_Exchangerecord->getExchangeRecord($queryCond, $page);
        $this->assign('exchangeRecord', $exchangeRecord);
        $this->assign('page',$page->show());
        /* 左侧菜单 */
        $this->assign('menu',2);
        $this->display();        
    }

    /**
     * 秀豆兑换秀币
     */
    public function showmoneyexchange(){
        $this->checkCurUserId();
        $queryCond = $this->getTimeCond('addtime');
        $queryCond['userid'] = session('userid');
        $queryCond['type'] = array('eq', 2); //兑换类型：1.秀币换秀豆、2.秀豆换秀币       
        $db_Exchangerecord = D('Exchangerecord');
        $count = $db_Exchangerecord->where($queryCond)->count();
        $page = getConfigPage($count,10);
        $exchangeRecord = $db_Exchangerecord->getExchangeRecord($queryCond, $page);
        $this->assign('exchangeRecord', $exchangeRecord);
        $this->assign('page',$page->show());
        /* 左侧菜单 */
        $this->assign('menu',2);
        $this->display();         
    }  

    /**
     * 绑定手机
     */
    public function boundphone(){
        $this->checkCurUserId();
        if (IS_POST) {
            $db_Member = M('Member');
            $ischeck = I('POST.ischeck', '0');
            $userid = I('POST.userid','', 'trim');
            $userno = I('POST.userno','', 'trim');
            $verifycode = I('POST.verifycode','', 'trim');
            $countryno = I('POST.countryno','', 'trim');  
            switch ($ischeck) {
                case '1':  //验证手机号是否被绑定
                    $where['userno'] = array('eq', $userno);
                    $userinfo = $db_Member->where($where)->find();
                    if ($userinfo) {
                        $data = array(
                            'status' => 2,  //此手机号已被绑定
                            'msg'   => lan('PHONE_ALREADY_EXISTS', 'Home')
                        );
                    } else {
                        $data = array(
                            'status' => 200,
                            'msg' => lan("OPERATION_SUCCESSFUL", "Home")
                        );
                    }
                    $this->ajaxReturn($data);                    
                    break;
                default:
                    //验证验证码
                    switch ($countryno) {
                        case '84':  //越南
                            $querySmsArr = array(
                                'phoneno' => $userno,
                                'smstype' => 0,
                                'verifycode' =>  $verifycode,
                                'senddate' => date('Y-m-d'),
                            );
                            $smsrecord = M('Smsrecord')->where($querySmsArr)->find();
                            if (!$smsrecord) {
                                $data = array(
                                    'status' => 0,  //验证码错误，请重试
                                    'msg' => lan('LAN_VERIFY_ERROR', 'Home'),
                                );
                                $this->ajaxReturn($data);
                            }
                            break;
                        default:  //其他国家
                            $response = checkSmsCode($userno, $countryno, $verifycode);
                            if ($response['status'] != 200) {
                                $data = array(
                                    'status' => 0,  //验证码错误，请重试
                                    'msg' => lan('LAN_VERIFY_ERROR', 'Home'),
                                );
                                $this->ajaxReturn($data);
                            }
                            break;
                    }
                    //验证手机号
                    $where['userno'] = array('eq', $userno);
                    $userinfo = $db_Member->where($where)->find();
                    if ($userinfo) {
                        $data = array(
                            'status' => 2,  //此手机号已被绑定
                            'msg'   => lan('PHONE_ALREADY_EXISTS', 'Home')
                        );
                        $this->ajaxReturn($data);
                    }
                    //更新手机号
                    $updateArr = array(
                        'userno' => $userno
                    );
                    $map = array(
                        'userid' => $userid
                    );
                    $result = $db_Member->where($map)->save($updateArr);
                    if ($result) {
                        $this->updateSessionCookie($userid);
                        $data = array(
                            'status' => 200,
                            'msg' => lan("OPERATION_SUCCESSFUL", "Home")
                        );
                    } else {
                        $data = array(
                            'status' => -1,
                            'msg' => lan("-1", "Home")
                        );                
                    }
                    $this->ajaxReturn($data);                    
                    break;
            }                        
        }
    }      
}

?>