<?php
namespace Supervise\Controller;
use Think\Upload;
use Think\Page;

class TreatedController extends CommonController {
    public function _initialize(){
        parent::_initialize();
        $this->redis = new \Org\Util\ThinkRedis();
    }

    //待处理举报
    function pending_report() {
        $db_Report = M('Report');
        // 用户查询条件
        if($_GET['start_time'] != '') $map['createtime'] = array('gt', $_GET['start_time']);
        if($_GET['end_time'] != '') $map['createtime'] = array(array('gt',$_GET['start_time']),array('lt', $_GET['end_time'].' 23:59:59')) ;

        $userid = $this->getUserIdByName($_GET['keyword']);
        if (!$userid) {
            $userinfo_username = M('Member')->field('userid')->where('username="'.$_GET['keyword'].'"')->find();            
            $userinfo_nickname = M('Member')->field('userid')->where('nickname like "%'.$_GET['keyword'].'%"')->select();
            $userinfo_roomno = M('Member')->field('userid')->where('roomno="'.$_GET['keyword'].'"')->find();
            $userinfo_niceno = M('Member')->field('userid')->where('niceno="'.$_GET['keyword'].'"')->find();   
            if ($userinfo_username) {
                $userid = $userinfo_username['userid'];
            }elseif ($userinfo_nickname) {
                $userinfo_n = array();
                foreach ($userinfo_nickname as $k => $v) {
                    $userinfo_n[$k] = $v['userid'];
                }
                $userid = implode(',', $userinfo_n);
            }elseif ($userinfo_roomno) {
                $userid = $userinfo_roomno['userid'];
            }elseif ($userinfo_niceno) {
                $userid = $userinfo_niceno['userid'];
            }else{
                $userid = $_GET['keyword'];
            }
        }           
        if($_GET['keyword'] != ''){
            $map['reporteduid']  = array('in', $userid);
        }

        $p = I('get.p',1);
        $map['isprocess'] = 0;
        $count = count($db_Report->where($map)->group('reporteduid')->select());
        $row = 50;
        
        $subQuery = $db_Report->where($map)->order('createtime DESC')->buildSql(); 
        $data = $db_Report->table($subQuery.' a')->field('a.*,count(reportid) AS reportedcount')->group('a.reporteduid')->order('a.createtime DESC')->page($p,$row)->select(); 

        foreach ($data as $k => $v) {
            $field = array(
                'username',
                'nickname',
                'roomno',
                'niceno'
            );
            $reporteduser = $this->getUserInfoById($v['reporteduid'],$field);
            $data[$k]['username'] = $reporteduser['username'];
            $data[$k]['nickname'] = $reporteduser['nickname']; 

            if ($reporteduser['niceno']) {
                $data[$k]['showroomno'] = $reporteduser['niceno'];
            }else{
                $data[$k]['showroomno'] = $reporteduser['roomno'];
            }                            
        }    

        $page = new Page($count,$row);
        $page = $page->show();

        $this->assign('data',$data);
        $this->assign('page',$page);
        $this->assign('keyword',$_GET['keyword']);          
        $this->display();           
    }  

    //待处理举报详情
    function pending_report_detail() {
        $db_Report = M('Report');
        if (IS_POST) {
            //没问题
            $reportidArr = I('post.reportid');
            if ($reportidArr) {
                $map['reportid'] = array('in', $reportidArr);
                $data['isprocess'] = 1;
                $data['isviolate'] = 0;
                $data['processor'] = session('adminid');
                $data['processtime'] = date('Y-m-d H:i:s');
                $result = $db_Report->where($map)->save($data);
                if ($result) {
                    $this->success('',U('Supervise/Treated/pending_report'));
                }               
            }
        }else{
            $reporteduid = I('get.reporteduid','');
            if ($reporteduid == '') die;
            // 用户查询条件
            $userid = $this->getUserIdByName($_GET['keyword']);
            if (!$userid) {
                $userinfo_username = M('Member')->field('userid')->where('username="'.$_GET['keyword'].'"')->find();            
                $userinfo_nickname = M('Member')->field('userid')->where('nickname like "%'.$_GET['keyword'].'%"')->select();
                $userinfo_roomno = M('Member')->field('userid')->where('roomno="'.$_GET['keyword'].'"')->find();
                $userinfo_niceno = M('Member')->field('userid')->where('niceno="'.$_GET['keyword'].'"')->find();   
                if ($userinfo_username) {
                    $userid = $userinfo_username['userid'];
                }elseif ($userinfo_nickname) {
                    $userinfo_n = array();
                    foreach ($userinfo_nickname as $k => $v) {
                        $userinfo_n[$k] = $v['userid'];
                    }
                    $userid = implode(',', $userinfo_n);
                }elseif ($userinfo_roomno) {
                    $userid = $userinfo_roomno['userid'];
                }elseif ($userinfo_niceno) {
                    $userid = $userinfo_niceno['userid'];
                }else{
                    $userid = $_GET['keyword'];
                }
            }           
            if($_GET['keyword'] != ''){
                $map['userid']  = array('in', $userid);
            }
    
            $p = I('get.p',1);
            $map['isprocess'] = 0;
            $map['reporteduid'] = $reporteduid;
            $count = count($db_Report->where($map)->select());
            $row = 50;
            
            $data = $db_Report->where($map)->order('createtime DESC')->page($p,$row)->select(); 
    
            foreach ($data as $k => $v) {
                $field = array(
                    'username',
                    'nickname',
                    'roomno',
                    'niceno'
                );
                $reportuser = $this->getUserInfoById($v['userid'],$field);
                $data[$k]['username'] = $reportuser['username'];
                $data[$k]['nickname'] = $reportuser['nickname'];   

                if ($reportuser['niceno']) {
                    $data[$k]['showroomno'] = $reportuser['niceno'];
                }else{
                    $data[$k]['showroomno'] = $reportuser['roomno'];
                }                              
            }    
    
            $page = new Page($count,$row);
            $page = $page->show();
    
            $fieldreported = array(
                'roomno',
                'niceno'
            );
            $reporteduser = $this->getUserInfoById($reporteduid,$fieldreported);
            if ($reporteduser['niceno']) {
                $showroomno = $reporteduser['niceno'];
            }else{
                $showroomno = $reporteduser['roomno'];
            }  

            //禁播操作
            $lantype = getLanguage();
            $baninfo['reason'] = M('Violatedefinition')->where('lantype="'.$lantype.'" AND type=1')->select();
            $baninfo['level'] = M('Violatedefinition')->where('lantype="'.$lantype.'" AND type=2')->select();
            $baninfo['time'] = M('Violatedefinition')->where('lantype="'.$lantype.'" AND type=3')->select(); 
            foreach ($baninfo['time'] as $k => $v) {
                if ($v['key'] != 9) {
                    $baninfo['time'][$k]['value'] = $v['value'].' '.lan('MINUTE', 'Home');
                }
            }
            $baninfo['money'] = M('Violatedefinition')->where('lantype="'.$lantype.'" AND type=4')->select(); 
            $whereSys = array(
                'key' => 'NODEJS_PATH',
                'lantype' => $lantype
            );
            $chatNodePath = M('Systemset')->where($whereSys)->getField('value');
            
            $this->assign('chatNodePath',$chatNodePath);     
            $this->assign('data',$data);
            $this->assign('page',$page);
            $this->assign('baninfo',$baninfo);          
            $this->assign('keyword',$_GET['keyword']);
            $this->assign('reporteduid',$reporteduid);
            $this->assign('adminid',session('adminid'));
            $this->assign('roomno',$reporteduser['roomno']);   
            $this->assign('showroomno',$showroomno);                            
            $this->display();           
        }
    }    

    //违规记录
    public function violation_record() {
    	$db_Ban = M('Banrecord');
		// 用户查询条件
		if($_GET['start_time'] != '') $map['ws_banrecord.processtime'] = array('gt', $_GET['start_time']);
		if($_GET['end_time'] != '') $map['ws_banrecord.processtime'] = array(array('gt',$_GET['start_time']),array('lt', $_GET['end_time'].' 23:59:59')) ;

		$userid = $this->getUserIdByName($_GET['keyword']);
		if (!$userid) {
            $userinfo_username = M('Member')->field('userid')->where('username="'.$_GET['keyword'].'"')->find();            
            $userinfo_nickname = M('Member')->field('userid')->where('nickname like "%'.$_GET['keyword'].'%"')->select();
            $userinfo_roomno = M('Member')->field('userid')->where('roomno="'.$_GET['keyword'].'"')->find();
            $userinfo_niceno = M('Member')->field('userid')->where('niceno="'.$_GET['keyword'].'"')->find();   
            if ($userinfo_username) {
                $userid = $userinfo_username['userid'];
            }elseif ($userinfo_nickname) {
                $userinfo_n = array();
                foreach ($userinfo_nickname as $k => $v) {
                    $userinfo_n[$k] = $v['userid'];
                }
                $userid = implode(',', $userinfo_n);
            }elseif ($userinfo_roomno) {
                $userid = $userinfo_roomno['userid'];
            }elseif ($userinfo_niceno) {
                $userid = $userinfo_niceno['userid'];
            }else{
                $userid = $_GET['keyword'];
            }
		}		    
		if($_GET['keyword'] != ''){
			$map['ws_banrecord.userid']  = array('in', $userid);
		}

        $p = I('get.p',1);
		$count = count($db_Ban->where($map)->select());
	    $row = 20;
        
        $data = $db_Ban->field('ws_banrecord.*,r.createtime,r.banid AS id')->join('LEFT JOIN ws_report r ON r.banid=ws_banrecord.banid')->where($map)->group('ws_banrecord.banid')->order('ws_banrecord.processtime DESC')->page($p,$row)->select(); 

        foreach ($data as $k => $v) {
        	$field = array(
        		'username',
        		'nickname',
        		'roomno',
        		'niceno'
        	);
            $reporteduser = $this->getUserInfoById($v['userid'],$field);
            if ($v['processadminid']) {
                $operationinfo = M('Admin')->find($v['processadminid']);
                $data[$k]['operation'] = $operationinfo['adminname'];             	
            }else{
                $operationinfo = $this->getUserInfoById($v['processuserid'],$field);;
                $data[$k]['operation'] = $operationinfo['nickname'];             	
            }
            $data[$k]['username'] = $reporteduser['username'];
            $data[$k]['nickname'] = $reporteduser['nickname']; 
            if ($reporteduser['niceno']) {
              	$data[$k]['showroomno'] = $reporteduser['niceno'];
            }else{
            	$data[$k]['showroomno'] = $reporteduser['roomno'];
            }             
        }    

		$page = new Page($count,$row);
		$page = $page->show();

        //禁播操作
        $lantype = getLanguage();
        $baninfo['reason'] = M('Violatedefinition')->where('lantype="'.$lantype.'" AND type=1')->select();
        $baninfo['level'] = M('Violatedefinition')->where('lantype="'.$lantype.'" AND type=2')->select();
        $baninfo['time'] = M('Violatedefinition')->where('lantype="'.$lantype.'" AND type=3')->select(); 
        foreach ($baninfo['time'] as $k => $v) {
            if ($v['key'] != 9) {
                $baninfo['time'][$k]['value'] = $v['value'].' '.lan('MINUTE', 'Home');
            }
        }
        $baninfo['money'] = M('Violatedefinition')->where('lantype="'.$lantype.'" AND type=4')->select(); 
        $whereSys = array(
            'key' => 'NODEJS_PATH',
            'lantype' => $lantype
        );
        $chatNodePath = M('Systemset')->where($whereSys)->getField('value');
        
        $this->assign('chatNodePath',$chatNodePath); 
    	$this->assign('data',$data);
    	$this->assign('page',$page);
    	$this->assign('baninfo',$baninfo); 
    	$this->assign('adminid',session('adminid'));    	   	
    	$this->assign('keyword',$_GET['keyword']);    	    
    	$this->display();    	
    }

    //违规记录详情
    function violation_record_detail() {
        $db_Report = M('Report');
        $banid = I('get.banid','',intval);
        if ($banid == '') die;
        // 用户查询条件
        $userid = $this->getUserIdByName($_GET['keyword']);
        if (!$userid) {
            $userinfo_username = M('Member')->field('userid')->where('username="'.$_GET['keyword'].'"')->find();            
            $userinfo_nickname = M('Member')->field('userid')->where('nickname like "%'.$_GET['keyword'].'%"')->select();
            $userinfo_roomno = M('Member')->field('userid')->where('roomno="'.$_GET['keyword'].'"')->find();
            $userinfo_niceno = M('Member')->field('userid')->where('niceno="'.$_GET['keyword'].'"')->find();   
            if ($userinfo_username) {
                $userid = $userinfo_username['userid'];
            }elseif ($userinfo_nickname) {
                $userinfo_n = array();
                foreach ($userinfo_nickname as $k => $v) {
                    $userinfo_n[$k] = $v['userid'];
                }
                $userid = implode(',', $userinfo_n);
            }elseif ($userinfo_roomno) {
                $userid = $userinfo_roomno['userid'];
            }elseif ($userinfo_niceno) {
                $userid = $userinfo_niceno['userid'];
            }else{
                $userid = $_GET['keyword'];
            }
        }           
        if($_GET['keyword'] != ''){
            $map['userid']  = array('in', $userid);
        }
    
        $p = I('get.p',1);
        $map['banid'] = $banid;
        $count = count($db_Report->where($map)->select());
        $row = 50;
        
        $data = $db_Report->where($map)->order('createtime DESC')->page($p,$row)->select(); 
    
        foreach ($data as $k => $v) {
            $field = array(
                'username',
                'nickname',
                'roomno',
                'niceno'                
            );
            $reportuser = $this->getUserInfoById($v['userid'],$field);
            $data[$k]['username'] = $reportuser['username'];
            $data[$k]['nickname'] = $reportuser['nickname'];

            if ($reportuser['niceno']) {
                $data[$k]['showroomno'] = $reportuser['niceno'];
            }else{
                $data[$k]['showroomno'] = $reportuser['roomno'];                
            }                
        }    
    
        $page = new Page($count,$row);
        $page = $page->show();
    
        $fieldreported = array(
            'roomno',
            'niceno'
        );
        $banInfo = M('Banrecord')->find($banid);
        $reporteduser = $this->getUserInfoById($banInfo['userid'],$fieldreported);
        if ($reporteduser['niceno']) {
            $showroomno = $reporteduser['niceno'];
        }else{
            $showroomno = $reporteduser['roomno'];
        }  
    
        $this->assign('data',$data);
        $this->assign('page',$page);
        $this->assign('keyword',$_GET['keyword']);
        $this->assign('banid',$banid);
        $this->assign('showroomno',$showroomno);                            
        $this->display();           
    }

    //举报证据
    function report_video() {
        $reportid = I('get.reportid','');
        if ($reportid == '') die;
        $db_Report = M('Report');
        $reporteduser = $db_Report->field('reporteduid,video,pic')->find($reportid);
        $data['video'] = $reporteduser['video'];
        $data['pic'] = explode(',',$reporteduser['pic']);
        $this->assign('data', $data);
        $this->display();
    }

    //禁止直播
    function doBan() {
        require_once('CommonRedisController.class.php');
        if (IS_AJAX && IS_POST) {
            $db_Ban = M('Banrecord');
            $db_Report = M('Report');
            $banid = I('POST.banid',0, 'intval');
            $ban['userid'] = I('POST.reporteduid',0, 'intval');
            $liveinfo = M('Liverecord')->where('userid='.$ban['userid'])->order('liveid DESC')->find();
            if (empty($liveinfo['endtime']) || $liveinfo['laststarttime'] > $liveinfo['endtime']) {
                $ban['liveid'] = $liveinfo['liveid'];                 
            }
            $ban['punishtype'] = 0;
            $ban['type'] = I('POST.type',0, 'intval');
            $ban['content'] = I('POST.content', '' , 'trim');
            $lantype = getLanguage();
            $violationType = M('Violatedefinition')->where('type=1 AND `key`='.$ban['type'].' AND lantype="'.$lantype.'"')->find();
            if ($ban['type'] != 7) {
                $ban['content'] = $violationType['value'];
            } 
            $ban['violatelevel'] = I('POST.violatelevel',0, 'intval');
            $bantime = I('POST.bantime',0, 'intval');
            if ($bantime == 9) {
                $ban['bantime'] = -1;
                $expiretime = $ban['bantime'];  
            }else{
                $violationTime = M('Violatedefinition')->where('type=3 AND `key`='.$bantime.' AND lantype="'.$lantype.'"')->find();
                $ban['bantime'] = $violationTime['value'];  
                $msgbantime = $ban['bantime'].lan('MINUTE', 'Supervise');  
                $expiretime = date('Y-m-d H:i:s',strtotime('+'.$ban['bantime'].' minutes'));              
            }      
            $banmoney = I('POST.banmoney',0, 'intval');      
            $violationMoney = M('Violatedefinition')->where('type=4 AND `key`='.$banmoney.' AND lantype="'.$lantype.'"')->find();        
            $ban['punishmoney'] = $violationMoney['value'];  
            $ban['processuserid'] = I('post.superviseid',0,'intval');
            $ban['processtime'] = date('Y-m-d H:i:s');
            $ban['expiretime'] = $expiretime;
            $ban['isopen'] = 0;

            //上传证据
            $imgFile = I('post.imgFile');
            if($imgFile){
                $base64_image = str_replace(' ', '+', $imgFile);
                //post的数据里面，加号会被替换为空格，需要重新替换回来，如果不是post的数据，则注释掉这一行
                if (preg_match('/^(data:\s*image\/(\w+);base64,)/', $base64_image, $result)){
                    //匹配成功
                    if($result[2] == 'jpeg'){
                        $image_name = date('YmdHis').'_'.$ban['userid'].'.jpg';
                    }else{
                        $image_name = date('YmdHis').'_'.$ban['userid'].'.'.$result[2];
                    }
                    $image_file = "/Uploads/Report/Pic/".$image_name;
                    //服务器文件存储路径
                    $uploadStatus = file_put_contents(".".$image_file, base64_decode(str_replace($result[1], '', $base64_image)));
                    if (!$uploadStatus){    //上传失败
                        $result['status'] = 0;
                        $result['msg'] = lan('OPERATION_FAILED', 'Supervise');
                    }else{
                        //文件上传远程服务器
                        $ftpUpload = ftpUpload($image_file, $image_file);
                        if($ftpUpload['code'] != 200){
                            $result['status'] = 0;
                            $result['msg'] = lan('OPERATION_FAILED', 'Supervise');
                        }
                    }
                    $ban['pic'] = $image_file;
                }
            }                

            if ($banid > 0) {
                $result = $db_Ban->where('banid='.$banid)->save($ban);  
            }else{
                $result = $db_Ban->add($ban);  
                $banid = $result;                   
            }
 
            if($result) {
                //修改举报记录状态
                $reportidArr = I('post.reportid');
                if ($reportidArr) {
                    $mapReport['reportid'] = array('in', $reportidArr);
                    $dataReport['isprocess'] = 1;
                    $dataReport['isviolate'] = 1;
                    $dataReport['banid'] = $banid;                    
                    $dataReport['processor'] = session('adminid');
                    $dataReport['processtime'] = date('Y-m-d H:i:s');
                    $db_Report->where($mapReport)->save($dataReport);

                    $reportInfo = $db_Report->field('userid')->where($mapReport)->group('userid')->select();
                }                

                //redis中设置禁播信息
                if ($ban['bantime'] > 0 || $ban['bantime'] == -1) {
                    $CommonRedis = new CommonRedisController();
                    $CommonRedis->setBanLive($banid);
                }

                //给主播个人中心发送消息
                $MessageData = array(
                    'userid' => $ban['userid'],
                    'messagetype' => 0,
                    'title' => lan('SYSTEM_MESSAGE', 'Supervise'),
                    'content' => lan('YOU_ILLEGAL_LIVE', 'Supervise').lan('ALREADY_BAN', 'Supervise').$msgbantime.','.lan('CONTACT_HOTLINE', 'Supervise'),
                    'lantype' => $lantype,
                    'createtime' => date('Y-m-d H:i:s')           
                );
                $this->SendMessageToUser($MessageData);

                //给举报用户个人中心发消息
                if ($reportInfo) {
                    $field = array(
                        'nickname'
                    );
                    $reporteduser = $this->getUserInfoById($ban['userid'],$field);
                    foreach ($reportInfo as $k => $v) {
                        $MessageUserData = array(
                            'userid' => $v['userid'],
                            'messagetype' => 0,
                            'title' => lan('SYSTEM_MESSAGE', 'Supervise'),
                            'content' => $reporteduser['nickname'].' '.lan('ILLEGAL_LIVE', 'Supervise').','.lan('ALREADY_BAN', 'Supervise').$msgbantime.','.lan('THANK_REPORT', 'Supervise'),
                            'lantype' => $lantype,
                            'createtime' => date('Y-m-d H:i:s')                             
                        );
                        $this->SendMessageToUser($MessageUserData);
                    }
                }

                $liveInfo = M('Emceeproperty')->field('livetype')->where('userid='.$ban['userid'])->find();
                $data['livetype'] = $liveInfo['livetype'];
                $data['status'] = 1;
                $data['message'] = lan('BAN_SUCCESSFUL', 'Home');
            }else{
                $data['status'] = 0;
                $data['message'] = lan('INSERT_DATA_FAILED', 'Home');                
            }  
            echo json_encode($data);                                                   
        }        
    }    

    //监控列表禁播
    function supervise_doban(){
        require_once('CommonRedisController.class.php');
        if (IS_POST) {
            $db_Ban = M('Banrecord');
            $db_Report = M('Report');
            $banid = I('post.banid',0, 'intval');   
            $ban['userid'] = I('POST.reporteduid',0, 'intval');  
            $liveinfo = M('Liverecord')->where('userid='.$ban['userid'])->order('liveid DESC')->find();
            if (empty($liveinfo['endtime']) || $liveinfo['laststarttime'] > $liveinfo['endtime']) {
                $ban['liveid'] = $liveinfo['liveid'];                 
            }
            $ban['punishtype'] = 0;
            $ban['type'] = I('POST.type',0, 'intval');
            $ban['content'] = I('POST.content', '' , 'trim');
            $lantype = getLanguage();      
            $violationType = M('Violatedefinition')->where('type=1 AND `key`='.$ban['type'].' AND lantype="'.$lantype.'"')->find();
            if ($ban['type'] != 7) {
                $ban['content'] = $violationType['value'];
            } 
            $ban['violatelevel'] = I('POST.violatelevel',0, 'intval');
            $bantime = I('POST.bantime',0, 'intval');
            if ($bantime == 9) {
                $ban['bantime'] = -1;
                $expiretime = $ban['bantime'];  
            }else{
                $violationTime = M('Violatedefinition')->where('type=3 AND `key`='.$bantime.' AND lantype="'.$lantype.'"')->find();
                $ban['bantime'] = $violationTime['value'];  
                $msgbantime = $ban['bantime'].lan('MINUTE', 'Supervise');  
                $expiretime = date('Y-m-d H:i:s',strtotime('+'.$ban['bantime'].' minutes'));              
            }      
            $banmoney = I('POST.banmoney',0, 'intval');      
            $violationMoney = M('Violatedefinition')->where('type=4 AND `key`='.$banmoney.' AND lantype="'.$lantype.'"')->find();        
            $ban['punishmoney'] = $violationMoney['value'];  
            $ban['processuserid'] = I('post.superviseid',0,'intval');
            $ban['processtime'] = date('Y-m-d H:i:s');
            $ban['expiretime'] = $expiretime;
            $ban['isopen'] = 0;

            //文件上传远程服务器
            $file = 'imgFile';
            $filePath = '/Uploads/Report/Pic/';
            $fileName = date('YmdHis').'_'.$ban['userid'];
            $ftpFile = ftpFile($file, $filePath, $fileName);
            $videourl = '';
            if($ftpFile['code'] == 200){
                $videourl = $ftpFile['msg'];
            }
            //上传证据
            $ban['pic'] = $videourl;

            if ($banid > 0) {
                $result = $db_Ban->where('banid='.$banid)->save($ban);  
            }else{
                $result = $db_Ban->add($ban);  
                $banid = $result;                   
            }  

            $liveInfo = M('Emceeproperty')->field('livetype')->where('userid='.$ban['userid'])->find();
            if ($liveInfo['livetype'] != 2) {
                $url = U('Supervise/SuperviseList/app_list');
            }else{
                $url = U('Supervise/SuperviseList/pc_list');
            }
            
            if($result) {
                //修改举报记录状态
                // $reportidArr = I('post.reportid');
                $ra_map['reporteduid'] = $ban['userid'];
                $ra_map['isprocess'] = 0;
                $reportidArr = $db_Report->field('reportid')->where($ra_map)->select();
                $reportidA = array();
                foreach ($reportidArr as $k => $v) {
                    $reportidA[$k] = $v['reportid'];
                }
                $reportidStr=implode(',',$reportidA);
                if ($reportidStr) {
                    $mapReport['reportid'] = array('in', $reportidStr);
                    $dataReport['isprocess'] = 1;
                    $dataReport['isviolate'] = 1;
                    $dataReport['banid'] = $banid;                    
                    $dataReport['processor'] = session('superviseid');
                    $dataReport['processtime'] = date('Y-m-d H:i:s');
                    $db_Report->where($mapReport)->save($dataReport);

                    $reportInfo = $db_Report->field('userid')->where($mapReport)->group('userid')->select();
                }                

                //redis中设置禁播信息
                if ($ban['bantime'] > 0 || $ban['bantime'] == -1) {
                    $CommonRedis = new CommonRedisController();
                    $CommonRedis->setBanLive($banid);
                }

                //给主播个人中心发送消息
                $MessageData = array(
                    'userid' => $ban['userid'],
                    'messagetype' => 0,
                    'title' => lan('SYSTEM_MESSAGE', 'Supervise'),
                    'content' => lan('YOU_ILLEGAL_LIVE', 'Supervise').lan('ALREADY_BAN', 'Supervise').$msgbantime.','.lan('CONTACT_HOTLINE', 'Supervise'),
                    'lantype' => $lantype,
                    'createtime' => date('Y-m-d H:i:s')           
                );
                $this->SendMessageToUser($MessageData);

                //给举报用户个人中心发消息
                if ($reportInfo) {
                    $field = array(
                        'nickname'
                    );
                    $reporteduser = $this->getUserInfoById($ban['userid'],$field);
                    foreach ($reportInfo as $k => $v) {
                        $MessageUserData = array(
                            'userid' => $v['userid'],
                            'messagetype' => 0,
                            'title' => lan('SYSTEM_MESSAGE', 'Supervise'),
                            'content' => $reporteduser['nickname'].' '.lan('ILLEGAL_LIVE', 'Supervise').','.lan('ALREADY_BAN', 'Supervise').$msgbantime.','.lan('THANK_REPORT', 'Supervise'),
                            'lantype' => $lantype,
                            'createtime' => date('Y-m-d H:i:s')                             
                        );
                        $this->SendMessageToUser($MessageUserData);
                    }
                }

                $liveInfo = M('Emceeproperty')->field('livetype')->where('userid='.$ban['userid'])->find();
                // $data['livetype'] = $liveInfo['livetype'];
                // $data['status'] = 1;
                // $data['message'] = lan('BAN_SUCCESSFUL', 'Home');
                $this->success(lan('BAN_SUCCESSFUL', 'Supervise'),$url);
            }else{
                // $data['status'] = 0;
                // $data['message'] = lan('INSERT_DATA_FAILED', 'Home');    
                $this->error(lan('INSERT_DATA_FAILED', 'Supervise'),$url);            
            }  
            // echo json_encode($data);                                                                     
        }        
    }

    //开启直播
    function open_live() {
        if (IS_AJAX && IS_POST) {
            $db_Ban = M('Banrecord');
            $banid = I('POST.banid',0, 'intval');
            $banInfo = $db_Ban->find($banid);
            //禁播失效时间更新
            $map = array(
                'userid' => $banInfo['userid'],
                'isopen' => 0
            );
            $ban['processuserid'] = session('superviseid');  
            $ban['processtime'] = date('Y-m-d H:i:s');
            $ban['expiretime'] = date("Y-m-d H:i:s",0);        
            $ban['isopen'] = 1;            
            $result = $db_Ban->where($map)->save($ban);
            if ($result) {
                //主播表isforbidden设为0
                $dataEmcee = array(
                    'isforbidden' => 0
                );
                M('Emceeproperty')->where('userid='.$banInfo['userid'])->save($dataEmcee);

                //删除redis中该主播禁播记录
                $key = 'BanLive';    
                $hashKey = 'Emcee_'.$banInfo['userid'];
                $this->redis->hDel($key,$hashKey);

                $data['status'] = 1;
                $data['message'] = lan('OPERATION_SUCCESSFUL', 'Home'); 
            }
            echo json_encode($data);  
        }           
    }

    //违规证据
    function violation_pic(){
        $banid = I('get.banid','');
        if ($banid == '') die;
        $db_Ban = M('Banrecord');
        $data = $db_Ban->field('userid,pic')->find($banid);
        $this->assign('data', $data);
        $this->display();
    }

    //改变直播状态
    public function stoplive()
    {
        $userid = I('get.id');
        $dLiverecord = D("Liverecord");
        $dHistory = D("Seehistory");
        $queryEmcCond = array(
            'isliving' => 1 ,
            'userid' => $userid
        );
        $emceeInfo = M('Emceeproperty')->where($queryEmcCond)->find();

        if ($emceeInfo)
        {
            $updateCondArr = array(
                    'userid' => $userid
            );
            $queryLiverArr = array(
                    'userid' => $userid,
            );
            $liverecord = $dLiverecord->where($queryLiverArr)->order('starttime DESC')->find();
            $updateliveArr = array('liveid' => $liverecord['liveid']);
            // 设置主播是否直播为0 livetime audiencecount
            D('Emceeproperty')->where($updateCondArr)->save(array('isliving' => 0, 'livetime' => date('Y-m-d H:i:s'), 'audiencecount' => $liverecord['audicount']));
            //设置直播间沙发所有座位为空
            //$seatdef = D('Seatdefinition')->getSeatdefine($this->lan);
            D('Seat')->where($updateCondArr)->save(array('seatuserid' => 0, 'seatcount' => 0, 'price' => 0));

            //设置当前直播记录结束时间
            if ($liverecord['liveid']) {
                $dLiverecord->where($updateliveArr)->save(array('endtime' => date('Y-m-d H:i:s')));
                $updSeeHis = array('liveid' => $liverecord['liveid'], 'endtime' => array('exp', 'is NULL'));
                $dHistory->where($updSeeHis)->save(array('endtime' => date('Y-m-d H:i:s')));
            }
        }

        $this->success(lan('OPERATION_SUCCESSFUL', 'Supervise'));
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

    //根据用户id获得用户信息
    private function getUserInfoById($userid,$field = '') {
        $userCond = array(
            'userid' => $userid,
        );
        return M('Member')->where($userCond)->field($field)->find();
    }  

    private function SendMessageToUser($MessageData)
    {
        M('Message')->add($MessageData);
    }         
}