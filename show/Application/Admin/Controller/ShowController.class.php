<?php
/**
 * 直播控制器
 */
namespace Admin\Controller;
use Think\Page;
use Think\Controller;
use Think\Upload;

class ShowController extends CommonController {
    private $export_count_limit = 2000;   //每页最大导出记录数

    public function _initialize(){
        parent::_initialize();
        $this->redis = new \Org\Util\ThinkRedis();
    }
	
	// 用户列表
	function user_manager(){
		// 实例化模型
		$db = D('Member');
		$p = I('get.p',0);
		
		if(IS_POST){
			$ids = I('post.ids');
			if($ids==''){
				$this->error(lan('OPERATE_AFTER_CHOOSE', 'Admin'));
			}else{
				for($i=0;$i<count($ids);$i++){
					$userinfo = $db->where('userid='.$ids[$i].' and isemcee=1')->select();
					if ($userinfo) {
						$this->error(lan('EMCEE_USER_NOT_DELETE', 'Admin'));
					}
					$db-> where('userid='.$ids[$i])->setField('status',1);
				}
				$this->success();
			}
			
		}else{

			// 用户查询条件
			if($_GET['start_time'] != '') $map['registertime'] = array('gt', $_GET['start_time']);
			if($_GET['end_time'] != '') $map['registertime'] = array(array('gt',$_GET['start_time']),array('lt', $_GET['end_time']." 23:59:59")) ;

			if($_GET['keyword'] != ''){
                $keyword = $_GET['keyword'];
                $where['userid']  = array('eq', $keyword);
                $where['username']  = array('like','%'.$keyword.'%');
                $where['nickname'] = array('like','%'.$keyword.'%');
                $where['roomno'] = array('like','%'.$keyword.'%');
                $userInfo = D('Admin')->getuserInfoByRoomno($keyword);
                if ($userInfo) {
                    $where['roomno'] = array('like','%'.$userInfo['roomno'].'%');
                }
				$where['_logic'] = 'or';
				$map['_complex'] = $where;
			}
			if($_GET['status'] != ''){
                if($_GET['status'] == 1){
                    $map['status'] = array('eq', 1);
                }else{
                    $map['status'] = array('neq', 1);
                }
			}
            if($_GET['isemcee'] != ''){
                $map['isemcee'] = array('eq', $_GET['isemcee']);
            }
            if($_GET['usertype'] != ''){
                $map['usertype'] = array('eq', $_GET['usertype']);
            }            

			// 获取用户列表
			$count = $db->where($map)->count();
	        $row = 20;
			$page = new Page($count,$row);
			$data['page'] = $page->show();
			$data['list'] = $db->order(' registertime desc')->page($p,$row)->getList($map);

			foreach ($data['list'] as $k => $v) {
                //判断redis中是否有该主播禁播记录
                $key = 'BanLive';
                $hashKey = 'Emcee_'.$v['userid'];       
                $emceeBanLive = $this->redis->hGet($key,$hashKey);
                $emceeBanLiveValue = json_decode($emceeBanLive,true);
                $now = date('Y-m-d H:i:s');
                if($emceeBanLiveValue['failuretime'] > $now || $emceeBanLiveValue['failuretime'] == -1){
                    $data['list'][$k]['isbanlive'] = 1;
                }else{
                	$data['list'][$k]['isbanlive'] = 0;
                }

                $banrecordInfo = M('Banrecord')->field('banid')->where('userid='.$v['userid'])->order('banid DESC')->find();
                $data['list'][$k]['banid'] = $banrecordInfo['banid'];

        	    $field = array(
        	    	'roomno',
        	    	'niceno'
        	    );
                $baneduser = $this->getUserInfoById($v['userid'],$field);
                if ($baneduser['niceno']) {
                  	$data['list'][$k]['showroomno'] = $baneduser['niceno'];
                }else{
                	$data['list'][$k]['showroomno'] = $baneduser['roomno'];
                }                 
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

			// 模版赋值输出
			$this->assign('data',$data);
			$this->assign('baninfo',$baninfo);			
			$this->assign('keyword',$_GET['keyword']);	
			$this->assign('nickname',$_GET['nickname']);
            $this->assign('chatNodePath',$chatNodePath);            					
			$this->display();
		}	
	}
	
	//主播列表
	function user_sign(){
		// 实例化模型
		$db = D('EmceesView');
		$p = I('get.p',0);
		
		// 用户查询条件
		if($_GET['start_time'] != '') $map['registertime'] = array('gt', $_GET['start_time']);
		if($_GET['end_time'] != '') $map['registertime'] = array(array('gt',$_GET['start_time']),array('lt', $_GET['end_time']." 23:59:59")) ;
		
		if($_GET['keyword'] != ''){
            $keyword = $_GET['keyword'];
            $where['Member.userid']  = array('eq', $keyword);
            $where['Member.username']  = array('like','%'.$keyword.'%');
            $where['Member.nickname'] = array('like','%'.$keyword.'%');
            $where['Member.roomno'] = array('like','%'.$keyword.'%');
            $userInfo = D('Admin')->getuserInfoByRoomno($keyword);
            if ($userInfo) {
                $where['Member.roomno'] = array('like','%'.$userInfo['roomno'].'%');
            }
            $where['_logic'] = 'or';
            $map['_complex'] = $where;
		}

		if($_GET['signflag'] != ''){
            $map['signflag'] = array('eq', $_GET['signflag']);
//            if($_GET['signflag'] == 2){ //签约主播
//                $map['expiretime'] = array('gt', date('Y-m-d H:i:s'));
//            }
        }
		if($_GET['isliving'] != ''){
            $map['isliving'] = array('eq', $_GET['isliving']);
        }
        $emceeBanList = $this->emceeBanlive();
		if($_GET['isforbidden'] == '1'){
            // $map['isforbidden'] = array('eq', $_GET['isforbidden']);
            if ($emceeBanList) {
            	$map['userid'] = array('in', $emceeBanList); //不显示被禁播主播
            }           
        }elseif($_GET['isforbidden'] == '0'){
            if ($emceeBanList) {
            	$map['userid'] = array('not in', $emceeBanList); //不显示被禁播主播
            }            
        }

        if ($_GET['recommend'] == '1') {
            $map['recommend'] = array('gt', 0);
        } elseif ($_GET['recommend'] == '0') {
            $map['recommend'] = array('eq', 0);
        }
		
		$map['status'] = array('neq',1);
		$map['isemcee'] = array('eq',1);
		
		// 获取用户列表
		$count = $db->where($map)->count();
        $row = 20;
		$page = new Page($count,$row);
		$data['page'] = $page->show();	
		$data['list'] = $db->page($p,$row)->getList($map);

        foreach ($data['list'] as $k => $v) {
            //判断redis中是否有该主播禁播记录
            $key = 'BanLive';
            $hashKey = 'Emcee_'.$v['userid'];       
            $emceeBanLive = $this->redis->hGet($key,$hashKey);
            $emceeBanLiveValue = json_decode($emceeBanLive,true);
            $now = date('Y-m-d H:i:s');
            if($emceeBanLiveValue['failuretime'] > $now || $emceeBanLiveValue['failuretime'] == -1){
                $data['list'][$k]['isbanlive'] = 1;
            }else{
                $data['list'][$k]['isbanlive'] = 0;
            }

            $banrecordInfo = M('Banrecord')->field('banid')->where('userid='.$v['userid'])->order('banid DESC')->find();
            $data['list'][$k]['banid'] = $banrecordInfo['banid'];

            $field = array(
                'roomno',
                'niceno'
            );
            $baneduser = $this->getUserInfoById($v['userid'],$field);
            if ($baneduser['niceno']) {
                $data['list'][$k]['showroomno'] = $baneduser['niceno'];
            }else{
                $data['list'][$k]['showroomno'] = $baneduser['roomno'];
            }                 
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
				
		// 模版赋值输出
		$this->assign('data',$data);
        $this->assign('baninfo',$baninfo);          
		$this->assign('keyword',$_GET['keyword']);	
        $this->assign('chatNodePath',$chatNodePath);	
		$this->display();
	}

	// 待签约主播列表
	function user_sign_audit() {
        // 实例化模型
        $db = D('EmceesView');
        $p = I('get.p',0);

        // 用户查询条件
        if($_GET['start_time'] != ''){
            $map['registertime'] = array('gt', $_GET['start_time']);
        }
        if($_GET['end_time'] != ''){
            $map['registertime'] = array(array('gt',$_GET['start_time']),array('lt', $_GET['end_time']." 23:59:59")) ;
        }

        if($_GET['keyword'] != ''){
            $keyword = $_GET['keyword'];
            $where['Member.userid']  = array('eq', $keyword);
            $where['Member.username']  = array('like','%'.$keyword.'%');
            $where['Member.nickname'] = array('like','%'.$keyword.'%');
            $where['Member.roomno'] = array('like','%'.$keyword.'%');
            $userInfo = D('Admin')->getuserInfoByRoomno($keyword);
            if ($userInfo) {
                $where['Member.roomno'] = array('like','%'.$userInfo['roomno'].'%');
            }
            $where['_logic'] = 'or';
            $map['_complex'] = $where;
        }

        $map['status'] = array('neq',1);
//        $map['isemcee'] = array('eq',1);
        $map['signflag'] = array('eq',1);

        // 获取用户列表
        $count = $db->where($map)->count();
        $row = 20;
        $page = new Page($count,$row);
        $data['page'] = $page->show();
        $data['list'] = $db->page($p,$row)->getList($map);

        foreach ($data['list'] as $k => $v) {
            if ($v['niceno']) {
                $data['list'][$k]['showroomno'] = $v['niceno'];
            }else{
                $data['list'][$k]['showroomno'] = $v['roomno'];                
            }
        }

		// 模版赋值输出
		$this->assign('data',$data);
		$this->assign('keyword',$_GET['keyword']);		
		$this->display();
	}

	//主播签约审核
	function user_audit_edit() {
		$db_Member = M('Member');
		$db_Account = M('Account');
		$db_Contract = M('Contract');
		$db_Emceeproperty = M('Emceeproperty');
		$userid = I('get.id');

        $data['lan'] = getLanguage();
        $map['userid'] = $userid;
        $data['user'] = $db_Member->where($map)->find();// 获取用户信息
        $data['account'] = $db_Account->where($map)->find();// 获取签约主播申请资料
        $data['emcee'] =  $db_Emceeproperty->where('userid='.$userid)->find();  //获取主播表信息
        $data['contract'] = $db_Contract->where($map)->find();// 获取签约信息

        if(IS_POST){
            //更新account数据
            $data_account = I('post.account');
            $data_account['skill'] = implode(",",I('post.skill', '', 'trim'));

            if(I('post.accountid')){
                $update_account_result = $db_Account->where('userid='.$userid)->save($data_account);
            }else{
                $data_account['userid'] = $userid;
                $data_account['createtime'] = date('Y-m-d H:i:s');
                $update_account_result = $db_Account->add($data_account);
            }
            if($update_account_result === false){
                $this->error(lan('OPERATION_FAILED', 'Admin'));exit;
            }

            //更新contract数据
            $data_contract = I('post.contract');
            if(I('post.contractid')){
                $update_contract_result = $db_Contract->where('userid='.$userid)->save($data_contract);
            }else{
                $data_contract['userid'] = $userid;
                $update_contract_result = $db_Contract->add($data_contract);
            }

            if($update_contract_result === false){
                $this->error(lan('OPERATION_FAILED', 'Admin'));exit;
            }

            $is_through = I('post.is_through');
            //更新主播所属家族、所属运营
            $userInfo = $data['user'];
            $db_Changerelation = M('Changerelation_record');
            $familyid = I('post.emcee_family');
            if ($familyid && ($is_through == 1 || $is_through == 2)) {
                if ($userInfo['familyid'] != $familyid) {
                    if ($userInfo['familyid'] == 11 && $userInfo['operatorid'] == 0) {
                        $data_family = array(
                            'familyid' => $familyid,
                        );
                        $map_family = array(
                            'userid' => $userid,
                        ); 
                        $db_Member->where($map_family)->save($data_family); 
                    }else{
                        $data_family = array(
                            'type' => 1,
                            'objectid' => $userid,
                            'firstid' => $userInfo['familyid'],
                            'nowid' => $familyid,
                            'createtime' => date("Y-m-d H:i:s")
                        );
                        $map_family = array(
                            'type' => 1,
                            'objectid' => $userid,
                            'status' => 0
                        );
                        $change_family = $db_Changerelation->where($map_family)->find();
                        if ($change_family) {
                            $db_Changerelation->where('changerecordid='.$change_family['changerecordid'])->save($data_family);  
                        }else{
                            $db_Changerelation->add($data_family);                        
                        }                        
                    }
                }

                if ($familyid == 11) {
                    $operatorid = I('post.operatorid');
                    if ($operatorid && $userInfo['operatorid'] != $operatorid) {
                        if ($userInfo['familyid'] == 11 && $userInfo['operatorid'] == 0) {
                            $data_operator = array(
                                'operatorid' => $operatorid,
                            );
                            $map_operator = array(
                                'userid' => $userid,
                            ); 
                            $db_Member->where($map_operator)->save($data_operator);                              
                        }else{
                            $data_operator = array(
                                'type' => 2,
                                'objectid' => $userid,
                                'firstid' => $userInfo['operatorid'],
                                'nowid' => $operatorid,
                                'createtime' => date("Y-m-d H:i:s")
                            );
                            $map_operator = array(
                                'type' => 2,
                                'objectid' => $userid,
                                'status' => 0
                            );
                            $change_operator = $db_Changerelation->where($map_operator)->find(); 
                            if ($change_operator) {
                                $db_Changerelation->where('changerecordid='.$change_operator['changerecordid'])->save($data_operator);
                            }else{
                                $db_Changerelation->add($data_operator);     
                            }                                
                        }
                    }                    
                }
            }


            if($is_through == 1){   //签约审核通过
                //更新member数据
                $data_member = I('post.user');
                //$data_member['bigheadpic'] = $data_account['emceepic'];
                $data_member['isemcee'] = 1;
                // $data_member['familyid'] = 11;
                $db_Member->where('userid='.$userid)->save($data_member);

                //更新Emceeproperty信息
                $data_emceeproperty = array(
                    'categoryid' => I('post.categoryid'),//主播类型
                    'emceetype' => I('post.emceetype'), //全职兼职
                    'settlement_type' => I('post.settlement_type'), //结算类型
                    'expiretime' => $data_contract['expiretime'],
                    'signflag' => 2,
                    'time' => time(),
                );
                $update_emceeproperty_result = M("Emceeproperty")->where('userid='.$userid)->save($data_emceeproperty);
            }elseif($is_through == 2){  //已签约过的主播
                //更新Emceeproperty信息
                $data_emceeproperty = array(
                    'categoryid' => I('post.categoryid'),//主播类型
                    'emceetype' => I('post.emceetype'), //全职兼职
                    'settlement_type' => I('post.settlement_type'), //结算类型
                    'expiretime' => $data_contract['expiretime'],
                    'time' => time(),
                );
                $update_emceeproperty_result = M("Emceeproperty")->where('userid='.$userid)->save($data_emceeproperty);
            }else{
                //更新Emceeproperty信息
                $data_emceeproperty = array(
                    'categoryid' => I('post.categoryid'),//主播类型
                    'emceetype' => I('post.emceetype'), //全职兼职
                    'settlement_type' => I('post.settlement_type'), //结算类型
                    'signflag' => 0,
                    'time' => time(),
                );
                $update_emceeproperty_result = $db_Emceeproperty->where('userid='.$userid)->save($data_emceeproperty);

                //取消签约时解除主播与家族或运营的关系
                $data_relation = array(
                    'familyid' => 11,
                    'operatorid' => 0,   
                );
                $db_Member->where(array('userid'=>$userid))->save($data_relation);
            }

            if($update_emceeproperty_result === false){
                $this->error(lan('OPERATION_FAILED', 'Admin'));exit;
            }else{
                //给用户发送审核结果
                $MessageData = array(
                    'userid' => $userid,
                    'messagetype' => 0,
                    'title' => lan('SYSTEM_MESSAGE', 'Admin'),
                    'lantype' => getLanguage(),
                    'createtime' => date('Y-m-d H:i:s')                             
                );                
                if ($is_through == 1) {
                    $MessageData['content'] = lan('YOUR', 'Admin').lan('APPLY_SIGN', 'Admin').','.lan('APPROVE_PASS_USER', 'Admin');
                    D('Message')->SendMessageToUser($MessageData);
                }elseif ($is_through == 0){
                    $MessageData['content'] = lan('YOUR', 'Admin').lan('APPLY_SIGN', 'Admin').','.lan('NOT_PASS_APPROVE', 'Admin'); 
                    D('Message')->SendMessageToUser($MessageData);
                }
                
                $this->success();exit;
            }
        }

        // $data['lan'] = getLanguage();
        // $map['userid'] = $userid;
        // $data['user'] = $db_Member->where($map)->find();// 获取用户信息
        // $data['account'] = $db_Account->where($map)->find();// 获取签约主播申请资料
        // $data['emcee'] =  $db_Emceeproperty->where('userid='.$userid)->find();  //获取主播表信息
        // $data['contract'] = $db_Contract->where($map)->find();// 获取签约信息

        //获取主播类型列表
        $where['type']  = array('in','0,1,2');
        $where['mark'] = 1;
        $where['lantype'] = getLanguage();
        $emceecategory = M('emceecategory')->where($where)->select();

        $skill_list = array(
            array('MC'),
            array('DJ'),
            array('搞笑','Funny','Hài hước'),
            array('唱歌','Sing','Ca há'),
            array('跳舞','Dance','Múa'),
            array('游戏','Game','Khác'),            
        );

        $signflag = $db_Emceeproperty->where('userid='.$userid)->getField('signflag');
        
        // 家族列表
        $dbFamily = M('Family');
        $familylist = $dbFamily->field('familyname,familyid')->where(array('status'=>1))->select();
        foreach ($familylist as $k => $v) {
            if ($v['familyid'] == 11) {
                $familylist[$k]['familyname'] = lan('OFFICIAL_FAMILY', 'Admin');
            }else{
                $familylist[$k]['familyname'] = $v['familyname'].lan('FAMILY', 'Admin');
            }
        } 

        // 运营列表
        $operatorlist = $db_Member->field('userid,realname')->where('usertype=30')->select();

        // 模版赋值输出
        $this->assign('operatorlist',$operatorlist);         
        $this->assign('familylist',$familylist);        
        $this->assign('signflag',$signflag);
        $this->assign('skill_list',$skill_list);
        $this->assign('emceecategory',$emceecategory);
        $this->assign('categoryid',$data['emcee']['categoryid']);        
        $this->assign('data',$data);
        $this->assign('status',$data['user']['status']);        
        $this->display();
    }
	
	// 在线主播
	function user_add(){
		// 实例化模型
		$db = D('Member');

		if(IS_POST){
			// if(I('post.password')=='') $this->error(lan('LAN_PWD_NO_EMPTY', 'Admin'));
			if (I('post.username')==''||I('post.password')==''||I('post.repassword')==''||I('post.userno')==''||I('post.countrycode')=='') {
				$this->error(lan('PLZ_COMPLETE_FORM', 'Admin'));
			}
			if(I('post.password')!=I('post.repassword')) $this->error(lan('TWICE_PWD_NOTSAME', 'Admin'));
			if (!preg_match('/^[0-9a-zA-Z_]{6,16}$/', I('post.password'))) {
				$this->error(lan('PASSWORD_LENGTH_ERROR', 'Admin'));
			}
			$data = $db->userAdd(I('post.username'),I('post.password'),I('post.userno'),I('post.countrycode'));
			if($data==1){
				$this->success(lan('LAN_DO_SUCCESS', 'Admin'));
			}else{
				$this->error($data);
			}
		}else{
			$p = I('get.p',0);
			// 获取用户列表
			$count = $db->count();
	        $row = 20;
			$page = new Page($count,$row);
			$data['page'] = $page->show();	
			$data['list'] = $db->page($p,$row)->getList();
			
			$data['lan'] = getLanguage();		
			$data['country'] = M('country')->where(array('lantype'=>array('eq',$data['lan'] )))->order('countryid asc')->select();
			// 模版赋值输出
			$this->assign('data',$data);
			$this->display();			
		}	
	}
	
	// 编辑用户
	function user_edit(){
		if(IS_POST && IS_AJAX){
			$db_emcee = D('Emceeproperty');
			$db_user = D('Member');
			$userid = I('post.id');
			$contract = I('post.contract');
			if($contract!='') {
				if (!is_numeric($contract['contractno'])) {
			        $res = array(
                        'status' => 0,
                        'message' => lan("CONTRACT_NO_IS_NUMBER", "Admin"),
                    );
                    echo json_encode($res); 
                    die;					
				}				
				if (M('contract')->where('userid='.$userid)->save($contract) === false) {
			        $res = array(
                        'status' => 0,
                        'message' => lan("OPERATION_FAILED", "Admin"),
                    );
                    echo json_encode($res); 
                    die;					
				}
			}


            $user = I('post.user');

            $base64 = I('post.img');
            $base64_image = str_replace(' ', '+', $base64);
            //post的数据里面，加号会被替换为空格，需要重新替换回来，如果不是post的数据，则注释掉这一行
            if (preg_match('/^(data:\s*image\/(\w+);base64,)/', $base64_image, $result)){
                //匹配成功
                if($result[2] == 'jpeg'){
                    $image_name = date('YmdHis').'_'.$userid.'.jpg';
                }else{
                    $image_name = date('YmdHis').'_'.$userid.'.'.$result[2];
                }
                $image_file = '/Uploads/HeadImg/268200/'.$image_name;
                //服务器文件存储路径
                $uploadStatus = file_put_contents(".".$image_file, base64_decode(str_replace($result[1], '', $base64_image)));
                if (!$uploadStatus){    //上传失败
			        $res = array(
                        'status' => 0,
                        'message' => lan("PLZ_TRY_AGAIN", "Admin"),
                    );
                    echo json_encode($res); 
                    die;	                    
                }else{
                    //文件上传远程服务器
                    $ftpUpload = ftpUpload($image_file, $image_file);
                    if($ftpUpload['code'] != 200){
                        $res = array(
                            'status' => 0,
                            'message' => lan("PLZ_TRY_AGAIN", "Admin"),
                        );
                        echo json_encode($res);die;
                    }
                    $user['bigheadpic'] = $image_file;
                    $userInfo = $db_user->where('userid='.$userid)->find();
                    $oldbigheadpic = $userInfo['bigheadpic'];
                }
            }  
                      			
			if($user!=''){
				if(!$db_user->create($user)){
			        $res = array(
                        'status' => 0,
                        'message' => $db_user->getError(),
                    );
                    echo json_encode($res); 
                    die;					
				}else{
					if(!$n = $db_user->where('userid='.$userid)->save($user)){
			            $res = array(
                            'status' => 0,
                            'message' => lan("OPERATION_FAILED", "Admin"),
                        );
                        echo json_encode($res); 
                        die;
					}else{
						//删除原路径图片
                        if ($userInfo['bigheadpic'] != $oldbigheadpic) {
                            unlink('.'.$oldbigheadpic);
                        } 
					}
				}
			}
			
			$password = I('post.password',0);
			if($password!=0){
				$password = md5(md5($password).I('post.salt'));
				$db_user->where('userid='.$userid)->setField('password',$password);			
			}
			
			$pz = I('post.pz');
			if($pz!=''){
				if(!$db_emcee->create($pz)){
			        $res = array(
                        'status' => 0,
                        'message' => $db_emcee->getError(),
                    );
                    echo json_encode($res); 
                    die;					
				}else{
					if(!$db_emcee->where('userid='.$userid)->save()){
			            $res = array(
                            'status' => 0,
                            'message' => lan("OPERATION_FAILED", "Admin"),
                        );
                        echo json_encode($res); 
                        die;
					}
				}
			} ;

            //设置VIP
			$free_vip = I('post.free_vip');
            $free_vipid = (int)$free_vip['vipid'];
            $free_vip_validdays = (int)$free_vip['validdays'];//有效天数
            $free_vip_lantype = $free_vip['lantype'] ? $free_vip['lantype'] : 'vi';//语言类型
            if($free_vipid > 0 && $free_vip_validdays > 0){
                //获取用户未过期的该等级的VIP记录
                $where_vip = array(
                    'userid' => $userid,
                    'vipid' => $free_vipid,
                    'expiretime' => array('gt',date('Y-m-d H:i:s'))
                );
                $my_vip = M('viprecord')->where($where_vip)->order('expiretime DESC')->find();
                if($my_vip && $my_vip['expiretime']){
                    $free_vip_effectivetime = $my_vip['expiretime'];
                    $free_vip_expiretime = date('Y-m-d H:i:s',strtotime('+'.$free_vip_validdays.' day',strtotime($my_vip['expiretime'])));
                }else{
                    $free_vip_effectivetime = date('Y-m-d H:i:s');
                    $free_vip_expiretime = date('Y-m-d H:i:s',strtotime('+'.$free_vip_validdays.' day'));
                }
                //获取该等级的VIP定义值
                $where_vipdefinition = array(
                    'vipid' => $free_vipid,
                    'lantype' => $free_vip_lantype
                );
                $vipdefinition = M('vipdefinition')->where($where_vipdefinition)->find();
                //添加新VIP记录
                $free_vip_data = array(
                    'userid' => $userid,
                    'vipid' => $free_vipid,
                    'vipname' => $vipdefinition['vipname'],
                    'pcsmallvippic' => $vipdefinition['pcsmallviplogo'],
                    'appsmallvippic' => $vipdefinition['appsmallviplogo'],
                    'spendmoney' => 0,
                    'ispresent' => 1,
                    'effectivetime' => $free_vip_effectivetime,
                    'expiretime' => $free_vip_expiretime
                );
                $result = M('viprecord')->add($free_vip_data);
                if($result === false){
                    $res = array(
                        'status' => 0,
                        'message' => M('viprecord')->getError(),
                    );
                    $this->ajaxReturn($res);
                }
            }

            //设置座驾
            $dbeQuipment = M('equipment');
            $free_equipment = I('post.free_equipment');
            $free_commodityid = (int)$free_equipment['commodityid'];
            $free_equipment_validdays = (int)$free_equipment['validdays'];//有效天数
            $free_equipment_lantype = $free_equipment['lantype'] ? $free_equipment['lantype'] : 'vi';//语言类型
            if($free_commodityid > 0 && $free_equipment_validdays > 0){
                //获取用户未过期的该座驾的记录
                $where_equipment = array(
                    'userid' => $userid,
                    'commodityid' => $free_commodityid,
                    'expiretime' => array('gt',date('Y-m-d H:i:s'))
                );
                $my_equipment = $dbeQuipment->where($where_equipment)->order('expiretime DESC')->find();
                if($my_equipment && $my_equipment['expiretime']){
                    //只要之前有为过期的相同的座驾，是否使用和之前的保持一致
                    $my_equipment_isused = $my_equipment['isused'];
                    $free_equipment_effectivetime = $my_equipment['expiretime'];
                    $free_equipment_expiretime = date('Y-m-d H:i:s',strtotime('+'.$free_equipment_validdays.' day',strtotime($my_equipment['expiretime'])));
                }else{
                    //查看用户是否有未过期的正在使用的座驾
                    $where_equipment_isused = array(
                        'userid' => $userid,
                        'isused' => 1,
                        'expiretime' => array('gt',date('Y-m-d H:i:s'))
                    );
                    $equipment_isused = $dbeQuipment->where($where_equipment_isused)->find();
                    if($equipment_isused){
                        $my_equipment_isused = 0;
                    }else{
                        //更新所有失效的座驾为未使用
                        $notUsed['isused'] = 0;
                        $UsedCond = array(
                            'userid' => $userid,
                            'isused' => 1
                        );
                        $dbeQuipment->where($UsedCond)->save($notUsed);
                        //设置赠送的座驾为使用
                        $my_equipment_isused = 1;
                    }
                    $free_equipment_effectivetime = date('Y-m-d H:i:s');
                    $free_equipment_expiretime = date('Y-m-d H:i:s',strtotime('+'.$free_equipment_validdays.' day'));
                }
                //获取该座驾的定义值
                $where_commodity = array(
                    'commodityid' => $free_commodityid,
                    'lantype' => $free_equipment_lantype
                );
                $commodity = M('commodity')->where($where_commodity)->find();
                //添加新座驾记录
                $free_equipment_data = array(
                    'userid' => $userid,
                    'commodityid' => $free_commodityid,
                    'commodityname' => $commodity['commodityname'],
                    'commodityflashid' => $commodity['commodityflashid'],
                    'commodityswf' => $commodity['commodityswf'],
                    'pcbigpic' => $commodity['pcbigpic'],
                    'pcsmallpic' => $commodity['pcsmallpic'],
                    'appbigpic' => $commodity['appbigpic'],
                    'appsmallpic' => $commodity['appsmallpic'],
                    'spendmoney' => 0,
                    'isused' => $my_equipment_isused,
                    'ispresent' => 1,
                    'effectivetime' => $free_equipment_effectivetime,
                    'expiretime' => $free_equipment_expiretime,
                    'operatetime' => date('Y-m-d H:i:s'),
                );
                $result = $dbeQuipment->add($free_equipment_data);
                if($result === false){
                    $res = array(
                        'status' => 0,
                        'message' => M('equipment')->getError(),
                    );
                    $this->ajaxReturn($res);
                }
            }

            //赠送免费礼物
            $free_gift_count = I('post.free_gift_count',0);
            if($free_gift_count > 0){
                $dbFreegift = M('freegift');
                $notUsedFreeGiftCond = array(
                    'userid' => $userid,
                    'isused' => 0
                );
                $dbFreegift->where($notUsedFreeGiftCond)->save(array('isused'=>1));
                $free_gift_count = $free_gift_count > 10 ? 10 : $free_gift_count;
                $giftid = M('gift')->where(array('gifttype'=>1))->getField('giftid');
                $insertFreeGfit = array(
                    'userid' => $userid,
                    'giftid' => $giftid,
                    'giftcount' => $free_gift_count,
                    'isused' => 0,
                    'addtime' => date('Y-m-d H:i:s')
                );
                $dbFreegift->add($insertFreeGfit);
            }

			$emcee = I('post.emcee');
			if($emcee!=''){
				if(!$db_emcee->create($emcee)){
			        $res = array(
                        'status' => 0,
                        'message' => $db_emcee->getError(),
                    );
                    echo json_encode($res); 
                    die;					
				}else{
					if($db_emcee->where('userid='.$userid)->save() === false){
			            $res = array(
                            'status' => 0,
                            'message' => lan("OPERATION_FAILED", "Admin"),
                        );
                        echo json_encode($res); 
                        die;
					}
				}
			}
			
			$res = array(
                'status' => 1,
                'message' => lan("OPERATION_SUCCESSFUL", "Admin"),
            );
            echo json_encode($res); 
			
		}else{
            $userid = I('get.id');
			$db = D('Member');
			$db_cate = D('Emceecategory');	
			$db_contract = D('Contract');
			$data['lan'] = getLanguage();
			$data['cate'] = $db_cate->getEmceeCate(2,$data['lan']);

			// 获取用户信息
			$map['userid'] = array('eq',$userid);
			$data['user'] = $db->where($map)->find();
			
			// 获取主播信息
			$data['emcee'] = M('emceeproperty')->where($map)->find();

			// 获取签约信息		
			$data['contract'] = M('contract')->where($map)->find();
			if(!$data['contract']){
				$db_contract->create();
				$db_contract->userid = I('get.id');
				$db_contract->add();
			}

			// 获取审核信息
			$data['account'] = M('Account')->where($map)->find();

            //获取VIP定义列表
            $data['vipdefinition'] = M('vipdefinition')->where(array('lantype'=>array('eq',$data['lan'])))->order('vipid asc')->select();
            //获取用户未过期的VIP列表
            $where_vip = array(
                'vr.userid' => $userid,
                'vr.expiretime' => array('gt',date('Y-m-d H:i:s')),
                'vd.lantype' => $data['lan']
            );
            $my_vip = M('viprecord vr')
                ->join('LEFT JOIN ws_vipdefinition vd ON (vd.vipid = vr.vipid)')
                ->field('vr.vipid, vr.effectivetime, vr.expiretime, vd.vipname')
                ->where($where_vip)
                ->order('myvipid ASC')
                ->select();
            $data['vip'] = array();
            foreach($my_vip as $key => $val){
                $data['vip'][$val['vipid']]['vipid'] = $val['vipid'];
                $data['vip'][$val['vipid']]['vipname'] = $val['vipname'];
                $data['vip'][$val['vipid']]['expiretime'] = $val['expiretime'];
                if(!$data['vip'][$val['vipid']]['effectivetime']){
                    $data['vip'][$val['vipid']]['effectivetime'] = $val['effectivetime'];
                }
            }

            //获取所有座驾定义列表
            $data['commodities'] = M('commodity')->where(array('lantype'=>$data['lan']))->order('commodityid ASC')->select();
            //获取用户未过期的座驾列表
            $where_equipment = array(
                'e.userid' => $userid,
                'e.expiretime' => array('gt',date('Y-m-d H:i:s')),
                'c.lantype' => $data['lan']
            );
            $my_equipment = M('equipment e')
                ->join('LEFT JOIN ws_commodity c ON (e.commodityid = c.commodityid)')
                ->field('e.commodityid, e.effectivetime, e.expiretime, c.commodityname')
                ->where($where_equipment)
                ->order('equipid ASC')
                ->select();
            $data['equipment'] = array();
            foreach($my_equipment as $key => $val){
                $data['equipment'][$val['commodityid']]['commodityid'] = $val['commodityid'];
                $data['equipment'][$val['commodityid']]['commodityname'] = $val['commodityname'];
                $data['equipment'][$val['commodityid']]['expiretime'] = $val['expiretime'];
                if(!$data['equipment'][$val['commodityid']]['effectivetime']){
                    $data['equipment'][$val['commodityid']]['effectivetime'] = $val['effectivetime'];
                }
            }

			$data['server'] = M('server')->order('serverid asc')->select();
				
			// 模版赋值输出
			$this->assign('data',$data);
			$this->display();
		}	
	}
	
	// 删除用户
	function user_del(){
		// 实例化模型
		$db = D('Member');
		$p = I('get.p',0);
		// 获取用户列表
		$map['status'] = array('eq',1);
		$count = $db->where($map)->count();
        $row = 20;
		$page = new Page($count,$row);
		$data['page'] = $page->show();	
		$data['list'] = $db->page($p,$row)->getList($map);
				
		// 模版赋值输出
		$this->assign('data',$data);
		$this->display();
	}
	
	// 用户状态
	function user_sh(){
		$db = D('Member');
      	$userid = I('get.id');
		$on = I('get.on');
		switch ($on)
		{
			case 5: //主播审核通过
			  	$data['status'] = 5;
				$data['isemcee'] = 1;
				if($db->where('userid='.$userid)->save($data)){
					$this->success(lan('APPROVE_PASS_USER', 'Admin'));
				}else{
					$this->error();
				}
			  	break;
			case 4:     //主播审核未通过
			  	$data['status'] = 4;
				$data['isemcee'] = 0;
				if($db->where('userid='.$userid)->save($data)){
					$this->success(lan('REJECT_APPROVE', 'Admin'));
				}else{
					$this->error();
				}
			  	break;
//			case 3:     //删除用户(物理删除)
//				if($db->delete($userid) ){
//					$this->success();
//				}else{
//					$this->error();
//				}
//			  	break;
			case 2:     //恢复删除用户
			  	$data['status'] = 0;				
				if($db->where('userid='.$userid)->save($data)){
					$this->success(lan('USER_RECOVERY_SUCCESSFUL', 'Admin'));
				}else{
					$this->error();
				}
			  	break;
			case 1:     //删除用户(逻辑删除)
                $data['status'] = 1;
                if($db->where('userid='.$userid)->save($data)){
                    $this->success(lan('DELETE_SUCCESS', 'Admin'));
                }else{
                    $this->error();
                }
                break;
			case 6:     //签约
			  	$data['signflag'] = 2;
				$data['signtime'] = date('Y-m-d H:i:s');
				if(M('emceeproperty')->where('emceeid='.$userid)->save($data)){
					$this->success(lan('AUDIT_SIGN', 'Admin'));
				}else{
					$this->error();
				}
			  	break;
			case 7:     //取消签约
			  	$data['signflag'] = 0;
				$data['signtime'] = date('Y-m-d H:i:s');
				if(M('emceeproperty')->where('emceeid='.$userid)->save($data)){
					$this->success(lan('CANCEL_SIGN', 'Admin'));
				}else{
					$this->error();
				}
			  	break;
			case 8:     //禁播主播
			  	$data['isforbidden'] = 1;
				if(M('emceeproperty')->where('emceeid='.$userid)->save($data)){
					$this->success(lan('FORBID_EMCEE', 'Admin'));
				}else{
					$this->error();
				}
			  	break;
			case 9:     //恢复禁播
			  	$data['isforbidden'] = 0;
				if(M('emceeproperty')->where('emceeid='.$userid)->save($data)){
					$this->success(lan('ENABLE_EMCEE', 'Admin'));
				}else{
					$this->error();
				}
			  	break;
		}
	}
	
	// 主播分类
	function user_type(){
		// 实例化模型
		$db = D('Emceecategory');	
		if(IS_POST){
			$ids = I('post.id');
			$sort = I('post.sort');
			for($i=0;$i<count($ids);$i++){
				if ($ids[$i]>0) {
				    $db-> where('categoryid='.$ids[$i])->setField('sort',$sort[$i]);					
				}
			}
			$this->success(lan('SORT_UPDATE_SUCCESSFUL', 'Admin'));
		}else{
			$data['lan'] = getLanguage();	
			$data = $db->getEmceeCate(I('get.type',0),$data['lan']);
				
			// 模版赋值输出
			$this->assign('data',$data);
			$this->display();
		}	
	}
	// 编辑主播分类
	function user_typeUp(){
		// 实例化模型
		$db = D('Emceecategory');
		$id = I('get.id');
		$categoryid = I('post.categoryid');		
		
		if(IS_POST){
			if(!$db->create()){
				$this->error($db->getError());
			}else{
				if($id==''){
					$cateinfo = $db->where('categoryid='.$categoryid)->find();
					if (!$cateinfo) {
					    if($db->add()){
					    	$this->success('',U('Show/user_type','type='.$_POST['type']));
					    }else{
					    	$this->error();
					    }						
					}
					else{
						$this->error(lan('TYPENO_EXISTS', 'Admin'));
					}
				}else{
					if($db->where('ecateid='.$id)->save()){
						$this->success('',U('Show/user_type','type='.$_POST['type']));
					}else{
						$this->error();
					}				
				}			
			}
			
		}else{
			if($id!='') $data = $db->find($id);
			$data['lan'] = getLanguage();
			$data['cate'] = $db->getEmceeCate(2,$data['lan']);	
			// 模版赋值输出
			$this->assign('data',$data);
			$this->display();
		}		
	}
	
	// 主播等级
	function user_lvl(){
		// 实例化模型
		$db = D('Levelconfig');
		
		if(IS_POST){	
			$ids = I('post.ids');
			$levelid = I('post.levelid');
			$levelname = I('post.levelname');
			$levellow = I('post.levellow');
			$levelup = I('post.levelup');
			if($ids!=''){
				for($i=0;$i<count($levelid);$i++){
					if(in_array($levelid[$i],$ids)) {
					    // $d['levelid'] = $levelid[$i];
					    // $d['levelname'] = $levelname[$i];
					    $d['levellow'] = $levellow[$i];
					    $d['levelup'] = $levelup[$i];

					    if (strlen($d['levellow'])>11 || strlen($d['levelup'])>11) {
					    	$this->error(lan('BIT_OVER_LIMIT', 'Admin'));
					    }
					    
					    if(!$db->create($d)){
					    	$this->error($db->getError());
					    }else{
					    	$db->where('levelid='.$levelid[$i].' and leveltype=0')->save($d);	
					    }
					}
				}
			}	
			else{
				$this->error(lan('OPERATE_AFTER_CHOOSE', 'Admin'));
			}		
			$data['levelid'] = I('post.add_levelid');
			$data['levelname'] = I('post.add_levelname');
			$data['levellow'] = I('post.add_levellow');
			$data['levelup'] = I('post.add_levelup');
			$data['leveltype'] = I('post.add_leveltype');
			$data['lantype'] = I('post.add_lantype');
			//dump($data); exit();
			if($data['levelid']!=0){
				if(!$db->create($data)){
					$this->error($db->getError());
				}else{
					if($n = $db->add()){
						$this->success();
					}else{
						$this->error();
					}		
				}						
			}else{
				$this->success();
			}
			
		}else{
			// 获取主播等级列表
			$data['lan'] = getLanguage();
			$data['list'] = $db->getList(0,$data['lan']);
				
			// 模版赋值输出
			$this->assign('data',$data);
			$this->display();
		}	
	}
	
	// 富豪等级
	function user_rich(){
		// 实例化模型
		$db = D('Levelconfig');
		
		if(IS_POST){	
			$ids = I('post.ids');
			$levelid = I('post.levelid');
			$levelname = I('post.levelname');
			$levellow = I('post.levellow');
			$levelup = I('post.levelup');
			if($ids!=''){
				for($i=0;$i<count($levelid);$i++){
					if(in_array($levelid[$i],$ids)) {
					    // $d['levelid'] = $levelid[$i];
					    // $d['levelname'] = $levelname[$i];
					    $d['levellow'] = $levellow[$i];
					    $d['levelup'] = $levelup[$i];
					    
					    if (strlen($d['levellow'])>11 || strlen($d['levelup'])>11) {
					    	$this->error(lan('BIT_OVER_LIMIT', 'Admin'));
					    }

					    if(!$db->create($d)){
					    	$this->error($db->getError());
					    }else{
					    	$db->where('levelid='.$levelid[$i].' and leveltype=1')->save($d);	
					    }						
					}
				}
				
			}	
			else{
				$this->error(lan('OPERATE_AFTER_CHOOSE', 'Admin'));
			}					
			$data['levelid'] = I('post.add_levelid');
			$data['levelname'] = I('post.add_levelname');
			$data['levellow'] = I('post.add_levellow');
			$data['levelup'] = I('post.add_levelup');
			$data['leveltype'] = I('post.add_leveltype');
			$data['lantype'] = I('post.add_lantype');
			//dump($data); exit();
			if($data['levelid']!=0){
				if(!$db->create($data)){
					$this->error($db->getError());
				}else{
					if($n = $db->add()){
						$this->success();
					}else{
						$this->error();
					}		
				}						
			}else{
				$this->success();
			}
			
		}else{
			// 获取主播等级列表
			$data['lan'] = getLanguage();
			$data['list'] = $db->getList(1,$data['lan']);
				
			// 模版赋值输出
			$this->assign('data',$data);
			$this->display();
		}	
	}
	
	// 签约审核
	function user_examine(){
		// 实例化模型
		$db = D('Member');
		$p = I('get.p',0);
		// 获取用户列表
		$count = $db->count();
        $row = 20;
		$page = new Page($count,$row);
		$data['page'] = $page->show();	
		$data['list'] = $db->page($p,$row)->getList();
				
		// 模版赋值输出
		$this->assign('data',$data);
		$this->display();
	}

    // 直播记录
    function live_record(){
        $map = array();
        $searchform = I('get.searchform');
        //查询-用户名昵称
        $username = I('get.username');
        if($username){
            $where['m.roomno'] = array('like','%'.$username.'%');
            $where['m.username'] = array('like','%'.$username.'%');
            $where['m.nickname']  = array('like','%'.$username.'%');
            $where['m.niceno']  = array('like','%'.$username.'%');
            $where['_logic'] = 'or';
            $map['_complex'] = $where;
        }
        $search['username'] = $username;

        //查询-时间范围
        $start_time = I('get.start_time');
        $end_time = I('get.end_time');
        if($start_time && $end_time){
            $map['lr.starttime'] = array(array('egt',$start_time),array('elt',date('Y-m-d 23:59:59',strtotime($end_time))));
        }elseif($start_time && !$end_time){
            $map['lr.starttime'] = array('egt',$start_time);
        }elseif(!$start_time && $end_time){
            $map['lr.starttime'] = array('elt',date('Y-m-d 23:59:59',strtotime($end_time)));
        }elseif($searchform != 1){
            $start_time = date('Y-m-d',mktime(0,0,0,date("m"),1,date("Y")));  //默认显示当月
            $map['lr.starttime'] = array('egt',$start_time);
        }
        $search['start_time'] = $start_time;
        $search['end_time'] = $end_time;

        //分页
        $dbLiverecord = M('liverecord lr');
        $count = $dbLiverecord
            ->join('LEFT JOIN ws_member m ON m.userid = lr.userid')
            ->where($map)->count();

        $pagesize = 50;
        $page = getpage($count,$pagesize);

        //排序
        $orderby = 'lr.starttime desc';

        //获取字段
        $field = array(
            'lr.*','m.roomno','m.username','m.nickname','m.niceno'
        );
        $liverecords = $dbLiverecord
            ->join('LEFT JOIN ws_member m ON m.userid = lr.userid')
            ->field($field)->where($map)->order($orderby)->limit($page->firstRow.",".$page->listRows)->select();

        //获取默认配置参数
        $default_parameter = M('default_parameter')->where(array('id'=>1))->find();
        $settlement_trade_type = $default_parameter['settlement_trade_type'];  //可以结算的消费类型（0.获得礼物、1.送礼物、2.购买商品、3.结算、4.购买沙发、5.付费房间、6.购买靓号、7.vip、8.发飞屏、9.购买守护）
        $dbEarndetail = M('earndetail');
        foreach($liverecords as $key => $val){
            //收入秀币
            $where_ed = array(
                'userid' => array('eq',$val['userid']),
                'tradetime' => array(array('egt',$val['starttime']),array('elt',$val['endtime'])),
                'tradetype' => array('in',$settlement_trade_type),
            );
            $earn_money = $dbEarndetail->field('IFNULL(sum(earnamount),0) as earn_money')->where($where_ed)->find();
            $liverecords[$key]['earn_money'] = $earn_money['earn_money'];

            //直播时长
            $living_length = strtotime($val['endtime'])-strtotime($val['starttime']);
            if($val['starttime'] == '' || $val['endtime'] == '' || $living_length < 0) {
                $liverecords[$key]['livelength_m'] = '';
            }else{
                $liverecords[$key]['living_length'] = getTimeLength($living_length);
            }

            if ($val['niceno']) {
                $liverecords[$key]['showroomno'] = $val['niceno'];
            }else{
                $liverecords[$key]['showroomno'] = $val['roomno'];
            }
        }

        //总收入秀币
        $totalEarnMoney = $dbLiverecord
            ->join('ws_member m ON m.userid = lr.userid')
            ->join('ws_earndetail ed on ed.userid = lr.userid and (ed.tradetime >= lr.starttime and ed.tradetime < lr.endtime) and tradetype in ('.$settlement_trade_type.')')
            ->field('sum(ed.earnamount) as total_earn_money')
            ->where($map)->find();
        $total_earn_money = (int)$totalEarnMoney['total_earn_money'];

        //总时长
        $totalLivingLength = $dbLiverecord
            ->join('ws_member m ON m.userid = lr.userid')
            ->field('IFNULL(sum(lr.duration),0) as living_length')
            ->where($map)->find();
        $total_living_length = getTimeLength($totalLivingLength['living_length']);

        //导出总页数
        $export_page = ceil($count/$this->export_count_limit);

        $this->assign('page',$page->show());
        $this->assign('liverecords',$liverecords);
        $this->assign('total_earn_money',$total_earn_money);
        $this->assign('total_living_length',$total_living_length);
        $this->assign('search',$search);
        $this->assign('export_page',$export_page);
        $this->display();
    }

    //导出直播记录
    public function export_live_record(){
        $map = array();
        //查询-用户名昵称
        $username = I('get.username');
        if($username){
            $where['m.roomno'] = array('like','%'.$username.'%');
            $where['m.username'] = array('like','%'.$username.'%');
            $where['m.nickname']  = array('like','%'.$username.'%');
            $where['_logic'] = 'or';
            $map['_complex'] = $where;
        }

        //查询-时间范围
        $start_time = I('get.start_time');
        $end_time = I('get.end_time');
        if($start_time && $end_time){
            $map['lr.starttime'] = array(array('egt',$start_time),array('elt',date('Y-m-d 23:59:59',strtotime($end_time))));
        }elseif($start_time && !$end_time){
            $map['lr.starttime'] = array('egt',$start_time);
        }elseif(!$start_time && $end_time){
            $map['lr.starttime'] = array('elt',date('Y-m-d 23:59:59',strtotime($end_time)));
        }

        //排序
        $orderby = 'lr.starttime desc';

        //分页
        $export_page = (int)I('export_page');
        if($export_page < 1){
            $export_page = 1;
        }
        $start = ($export_page-1)*$this->export_count_limit;

        //获取字段
        $field = array(
            'lr.*','m.roomno','m.username','m.nickname'
        );
        $dbLiverecord = M('liverecord lr');
        $liverecords = $dbLiverecord
            ->join('LEFT JOIN ws_member m ON m.userid = lr.userid')
            ->field($field)->where($map)->order($orderby)->limit($start.",".$this->export_count_limit)->select();

        if(empty($liverecords)){
            $this->error();exit;
        }

        //导出数组表头定义
        $title = array(
            lan('ROOMNO','Admin'),  //房间号
            lan('SYS_USERNAME','Admin'),  //用户名
            lan('NICKNAME','Admin'),  //昵称
            lan('START_LIVE_TIME','Admin'),  //开始直播时间
            lan('END_LIVE_TIME','Admin'),  //结束直播时间
            lan('LIVE_LENGTH','Admin'),  //直播时长
            lan('EARN_SHOW_MONEY','Admin'),  //收入秀币
        );
        //导出数据列表
        $data = array();
        //获取默认配置参数
        $default_parameter = M('default_parameter')->where(array('id'=>1))->find();
        $settlement_trade_type = $default_parameter['settlement_trade_type'];  //可以结算的消费类型（0.获得礼物、1.送礼物、2.购买商品、3.结算、4.购买沙发、5.付费房间、6.购买靓号、7.vip、8.发飞屏、9.购买守护）
        $dbEarndetail = M('earndetail');
        foreach($liverecords as $key => $val){
            //收入秀币
            $where_ed = array(
                'userid' => array('eq',$val['userid']),
                'tradetime' => array(array('gt',$val['starttime']),array('lt',$val['endtime'])),
                'tradetype' => array('in',$settlement_trade_type),
            );
            $earnMoney = $dbEarndetail->field('IFNULL(sum(earnamount),0) as earn_money')->where($where_ed)->find();
            $earn_money = $earnMoney['earn_money'];

            //直播时长
            $livingLength = strtotime($val['endtime'])-strtotime($val['starttime']);
            if($val['starttime'] == '' || $val['endtime'] == '' || $livingLength < 0) {
                $living_length = '';
            }else{
                $living_length = getTimeLength($livingLength);
            }
            //过滤换行符，以完整字符类型导出到excel
            $data[$key] = array(
                ExcleString($val['roomno']),
                ExcleString($val['username']),
                ExcleString($val['nickname']),
                $val['starttime'],
                $val['endtime'],
                $living_length,
                $earn_money
            );
        }

        //导出excel
        $filename = 'EmceeLiveRecord-'.date('Ymd').'-'.$export_page;
        exportExcle($title,$data,$filename);
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
	
	// 礼物分类
	function gift_type(){
		// 实例化模型
		$db = D('Giftcategory');
		$data['lan'] = getLanguage();
		
		if(IS_POST){
			$ids = I('post.ids');
			$id = I('post.id');
			$name = I('post.name');
			$sort = I('post.sort');			
            $giftinfo = $db->where('categoryid='.I('post.add_id'))->find();
			if ($giftinfo) {
				$this->error(lan('TYPENO_EXISTS', 'Admin'));
			}
			if($id){
				for($i=0;$i<count($id);$i++){
					// $d['categoryid'] =  $id[$i];
					$lantype = getLanguage();
					$d['categoryname'] =  $name[$i];
					$d['sort'] =  $sort[$i];					
					$db->where('categoryid='.$id[$i].' and lantype="'.$lantype.'"')->save($d);
				}
			}
			if(I('post.add_name','')!=''){
				$d['categoryid'] =  I('post.add_id');
				$d['categoryname'] =  I('post.add_name');
				$d['sort'] =  I('post.add_sort');				
				if(!$db->create($d)){
					$this->error($db->getError());
				}else{
					if($db->add()){
						$this->success();
					}else{
						$this->error();
					}
				}
			}else{
				$this->success();
			}	
		}else{
			// 获取分类列表
			$data['list'] = $db->getCateList($data['lan']);
					
			// 模版赋值输出
			$this->assign('data',$data);
			$this->display();
		}	
	}
	
	// 礼物管理
	function gift_manager(){
		// 实例化模型
		$db = D('Gift');
		
		if(IS_POST){	
			$ids = I('post.ids');
			$giftid = I('post.giftid');
			$categoryid = I('post.categoryid');
			$giftname = I('post.giftname');
			$price = I('post.price');
			$smallimgsrc = I('post.smallimgsrc');
			$bigimgsrc = I('post.bigimgsrc');
			$giftflash = I('post.giftflash');
			if($ids!=''){
				for($i=0;$i<count($giftid);$i++){
					if (in_array($giftid[$i],$ids)) {
					    $d['giftid'] = $giftid[$i];
					    $d['categoryid'] = $categoryid[$i];
					    $d['giftname'] = $giftname[$i];
					    $d['price'] = $price[$i];
					    $d['smallimgsrc'] = $smallimgsrc[$i];
					    $d['bigimgsrc'] = $bigimgsrc[$i];
					    $d['giftflash'] = $giftflash[$i];
					    
					    if(!$db->create($d)){
					    	$this->error($db->getError());
					    }else{
					    	$db->where('giftid='.$giftid[$i].' and lantype="'.getLanguage().'"')->save($d);	
					    	// echo $db->getlastsql();die;
					    }						
					}
				}
				
			}			
			$data['giftid'] = I('post.add_giftid');
			$data['categoryid'] = I('post.add_categoryid');
			$data['giftname'] = I('post.add_giftname');
			$data['price'] = I('post.add_price');
			$data['smallimgsrc'] = I('post.add_smallimgsrc');
			$data['bigimgsrc'] = I('post.add_bigimgsrc');
			$data['giftflash'] = I('post.add_giftflash');

			if($data['giftid']!=0){
				$giftinfo = $db->where('giftid='.$data['giftid'].' and lantype="'.getLanguage().'"')->find();
				if ($giftinfo) {
					$this->error(lan('GIFTNO_EXISTS', 'Admin'));
				}
				if (!is_numeric(I('post.add_price'))) {
					$this->error(lan('PRICE_IS_NUMBER', 'Admin'));
				}
				if(!$db->create($data)){
					$this->error($db->getError());
				}else{
					if($n = $db->add()){
						$this->success();
					}else{
						$this->error();
					}		
				}						
			}else{
				$this->success();
			}
			
		}else{
			
			
			// 获取分类列表
			$data['lan'] = getLanguage();
			$data['list'] = $db->getList($data['lan']);
					
			// 模版赋值输出
			$this->assign('cate',D('Giftcategory')->getCateList($data['lan']));
			$this->assign('data',$data);
			$this->display();
		}	
		
		
	}
	
	// 靓号管理
	function code_manager(){
		// 实例化模型
		$db = D('Nicenumber');
		
		if($_POST){
			dump($_POST);
			
		}else{
			$p = I('get.p',0);
			
			// 用户查询条件
			if($_GET['start_time'] != '') $map['createtime'] = array('gt', $_GET['start_time']);
			if($_GET['end_time'] != '') $map['createtime'] = array(array('gt',$_GET['start_time']),array('lt', $_GET['end_time']." 23:59:59")) ;		
			if($_GET['userid'] != '') $map['userid'] = array('eq', $_GET['userid']);
			if($_GET['length'] != '') $map['length'] = array('eq', $_GET['length']);
			if($_GET['isused'] != '') $map['isused'] = array('eq', $_GET['isused']);
			if($_GET['keyword'] != '') $map['niceno'] = array('eq', $_GET['keyword']);
			
			$count = $db->where($map)->count();
	        $row = 20;
			$page = new Page($count,$row);
			$data['page'] = $page->show();	
			$data['list'] = $db->where($map)->page($p, $row)->getList();
			for($i=0;$i<count($data['list']);$i++){
				$user = $this->getUserInfo($data['list'][$i]['userid']);
				$data['list'][$i]['username'] = $user['username'];
			}
						
			// 模版赋值输出
			$this->assign('data',$data);
			$this->display();		
		}		
	}
	
	// 靓号单个添加
	function code_addone(){
		// 实例化模型	
		$db = D('Nicenumber');
		$id = I('get.id');	
		if(IS_POST){
			// 自动验证		
			$num = I('post.niceno');
			if(!$db->create()){
				$this->error($db->getError());
			}else{
				$db->length = strlen($num);
				if($id!=''){ // 保存
					if($db->where('nicenoid='.$id)->save()){
						$this->success('',U('Show/code_manager'));
					}else{
						$this->error();
					}				
				}else{ // 新增
					$map['niceno'] = array('eq',$num);
					if($db->where($map)->count()>0) $this->error(lan('NICENO_IS_EXIST', 'Admin'));
					if($db->add()){
						$this->success('',U('Show/code_manager'));
					}else{
						$this->error();
					}
				}
			}
		}else{
			$data = $db->find($id);
			// 模版赋值输出
			$this->assign('data',$data);
			$this->display();
		}		
	}
	
	// 靓号多个个添加
	function code_addmore(){
		// 实例化模型
		$db = D('Nicenumber');
		
		if(IS_POST){
			$num = I('post.niceno');
			$map['niceno'] = array('eq',$num);
			if($db->where($map)->count()>0) $this->error(lan('NICENO_IS_EXIST', 'Admin'));
			
			if(!$db->create()){
				$this->error($db->getError());
			}else{
				$db->length = strlen(I('post.niceno'));
				if($db->add()){
					$this->success();
				}else{
					$this->error();
				}
			}	
		}else{
			// 模版赋值输出
			$this->display();
		}		
	}
	
	// 靓号赠送  1：赠送  0：收回
	function code_give($userid,$id,$on=1){
		$db = D('Nicenumber');
		$user = $this->getUserInfo($userid);
		if($user){
			if($on==1){
				$data['userid'] = $userid;
				$data['isused'] = 1;
				$data['remark'] = lan('OPERATOR_GIVE_AWAY', 'Admin');
			}else{
				$data['userid'] = 0;
				$data['isused'] = 0;
				$data['remark'] = lan('OPERATOR_WITHDRAW', 'Admin');
			}	
			$data['operatetime'] = date('Y-m-d H:i:s');
			if($db->where('nicenoid='.$id)->save($data)){
				$this->success('ok');
			}else{
				$this->success('no');
			}		
		}else{
			$this->error(lan('USERNAME_ERROR', 'Admin'));
		}		
	}
	
	// 砸蛋游戏设置
	function game_set(){
		// 实例化模型
		$db_egg = D('Eggset');
		
		if(IS_POST){
			// 自动验证
			if(!$db_egg->create()){
				$this->error($db_egg->getError());
			}else{
				if($db_egg->where('eggsetid=1')->save()){
					$this->success();
				}else{
					$this->error();
				}
			}
		}else{
			// 获取设置信息
			$data = $db_egg->find(1);
		
			// 模版赋值输出
			$this->assign('data',$data);
			$this->display();			
		}		
	}
	
	// 中奖纪录
	function game_order(){
		$db_order = M('giveaway');
				
		// 搜索条件
		if($_GET['start_time'] != '') $map['createtime'] = array('gt', $_GET['start_time']);
		if($_GET['end_time'] != '') $map['createtime'] = array(array('gt',$_GET['start_time']),array('lt', $_GET['end_time']." 23:59:59")) ;		
		if($_GET['userid'] != '') $map['userid'] = array('eq', $_GET['userid']);
			 
		// 获取家族列表
		$count = $db_order->where($map)->order('createtime desc')->count();
        $row = 20;
		$page = new Page($count,$row);
		$data['page'] = $page->show();	
		$data['list'] = $db_order->where($map)->order('createtime desc')->page(I('get.p'),$row)->select(); 
		for($i=0;$i<count($data['list']);$i++){
			$user = $this->getUserInfo($data['list'][$i]['targetid']);
			$data['list'][$i]['username'] = $user['username'];
		}
				
		// 模版赋值输出
		$this->assign('data',$data);
		$this->display();
	}

	/**
	 * 更具用户编号获取用户行
	 */
	function getUserInfo($userid){
		return M('member')->find($userid);
	}
	
	// 批量删除
	function delAll(){
		$model['6031'] = 'Nicenumber';
		$ids = I('post.ids');
		$db = $model[I('post.db')];
        $id = '';
		for($i=0;$i<count($ids);$i++){
			$id = $id.','.$ids[$i];
		}
		$id = ltrim($id,',');
		if(M($db)->delete($id)){
			$this->success(lan('DELETE_SUCCESS', 'Admin'));
		}else{
			$this->error(lan('APPROVE_DELETE', 'Admin'));
		}
	}
	
	// 单个删除
	function delOne(){
		$model['6031'] = 'Nicenumber';
		$model['6015'] = 'Emceecategory';
		$id = I('get.id');
		$db = $model[I('get.db')];
		if(D($db)->delete($id)){
			$this->success(lan('DELETE_SUCCESS', 'Admin'));
		}else{
			$this->error(lan('APPROVE_DELETE', 'Admin'));
		}
	}

    //主播视频
    function user_video() {
    	$emceeuserid = I('get.emceeuserid');
    	$videoinfo = M('video')->where('userid='.$emceeuserid)->find();
    	if (!$videoinfo['userid']) {
    		$videoinfo['userid'] = $emceeuserid;
    	}
    	$this->assign('data',$videoinfo);
        $this->display();
    }

    //上传视频
    function upload_video() {
    	$dVideo = M('video');
    	$emceeuserid = I('post.userid');
    	$videoinfo = $dVideo->where('userid='.$emceeuserid)->find();
    	if (IS_POST && $emceeuserid>0) {
            $videourl = '';
            //文件上传远程服务器
            $file = 'videopath';
            $filePath = '/Uploads/Video/';
            $fileName = 'v_'.$emceeuserid;
            $ftpFile = ftpFile($file, $filePath, $fileName);
            if($ftpFile['code'] == 200){
                $videourl = $ftpFile['msg'];
            }

            $data = array(
            	'userid' => $emceeuserid,
            	'title' => I('post.title'),
            	'type' => I('post.type'),
            	'uploadtime' => date('Y-m-d H:i:s')
            );
            if ($videourl) {
            	$data['url'] = $videourl;
            }
            if ($videoinfo['userid']) {
                $dVideo->where('userid='.$emceeuserid)->save($data);            	
            }else{
                $dVideo->add($data);   
            }
            $this->success();
    	}
    }

    //删除视频
    function del_video() {
        $emceeuserid = I('get.emceeuserid');
        $videoinfo = M('video')->where('userid='.$emceeuserid)->find();
    	if ($videoinfo['userid']) {
    		if (!unlink('./Uploads/Video/v_'.$emceeuserid.'.mp4')){
                $this->error();
            }else{
            	M('video')->where('userid='.$emceeuserid)->delete();
                $this->success();
            }
    	}
    } 

    //用户踢人解禁
    function user_kick_rez() {
    	$db = M('kickrecord');
    	$p = I('get.p',0);
    	$now = date('Y-m-d');
        $searchform = I('get.searchform');
        $starttime = $_GET['start_time'];
        $endtime = $_GET['end_time'];        
		// 用户查询条件
		if($starttime != '') $map['createtime'] = array('egt', $starttime);
		if($endtime != '') $map['createtime'] = array(array('egt',$starttime),array('elt', $endtime.' 23:59:59')) ;
        if ($searchform != 1) {
            $starttime = $now;
            $endtime = $now;
        	$map['createtime'] = array(array('egt',$starttime.' 00:00:00'),array('elt', $starttime.' 23:59:59'));
        }
        $search['starttime'] = $starttime;
        $search['endtime'] = $endtime;        

		$userid = $this->getUserIdByName($_GET['keyword']);
		if (!$userid) {
            $userinfo_username = M('Member')->where('username="'.$_GET['keyword'].'"')->find();            
			$userinfo_nickname = M('Member')->where('nickname like "%'.$_GET['keyword'].'%"')->find();
            if ($userinfo_username) {
                $userid = $userinfo_username['userid'];
            }elseif ($userinfo_nickname) {
                $userid = $userinfo_nickname['userid'];
            }else{
                $userid = $_GET['keyword'];
            }
		}

		if($_GET['keyword'] != '' && $_GET['keyword'] != lan('USERNAME', 'Admin').'/'.lan('NICKNAME', 'Admin')){
			$where['kickeduserid']  = array('eq', $userid);
			$where['_logic'] = 'or';
			$map['_complex'] = $where;
		}   
        $map['kickeduserid']  = array('gt', 0);
        $search['keyword'] = $_GET['keyword'];

		$count = $db->where($map)->count();
	    $row = 20;
		$page = new Page($count,$row);
		$data['page'] = $page->show();
		$data['list'] = $db->where($map)->order('createtime desc')->page($p,$row)->select();

    	foreach ($data['list'] as $k => $v) {
    	    $kickeduserid = $v['kickeduserid'];
    	    $kickeduserinfo = $this->getUserInfo($kickeduserid);
    	    $data['list'][$k]['kickedusername'] = $kickeduserinfo['username'];
    	    $data['list'][$k]['kickedusernickname'] = $kickeduserinfo['nickname'];

    	    $kickinguserid = $v['userid'];
    	    $kickinguserinfo = $this->getUserInfo($kickinguserid);
    	    $data['list'][$k]['kickingusername'] = $kickinguserinfo['username'];   

    	    $emceeuserid = $v['emceeuserid'];
    	    $emceeuserinfo = $this->getUserInfo($emceeuserid);
    	    if ($emceeuserinfo['niceno']) {
    	     	$data['list'][$k]['roomno'] = $emceeuserinfo['niceno'];  
    	    }else{
    	        $data['list'][$k]['roomno'] = $emceeuserinfo['roomno'];     	    	
    	    } 	

            $key = 'KickRecord';
            $hashKey = 'User'.$kickeduserid.'_'.'Emcee'.$emceeuserid;       
            $userKickedRecord = $this->redis->hGet($key,$hashKey);
            $userKickedRecordValue = json_decode($userKickedRecord,true);
            $nowtime = date('Y-m-d H:i:s');
            if($userKickedRecordValue['failuretime'] > $nowtime){
            	$data['list'][$k]['status'] = 1;
            }    	     
    	}
    
    	$this->assign('keyword',$_GET['keyword']);    	
    	$this->assign('now',$now);
    	$this->assign('data',$data);
        $this->assign('search',$search);        
    	$this->display();
    }

    function do_user_kick_rez() {
    	if (IS_POST && IS_AJAX) {
    		$kickid = I('post.id');
    		$db = M('kickrecord');
    		$kickrecord = $db->find($kickid);
    		if ($kickrecord) {
            	//删除redis中该用户在该房间被踢记录
                $key = 'KickRecord';    
                $hashKey = 'User'.$kickrecord['kickeduserid'].'_'.'Emcee'.$kickrecord['emceeuserid'];
            	$this->redis->hDel($key,$hashKey);    			
    			$result = array(
    				'status' => 1,
    				'msg' => lan('RELIEVE_SUCCESSFULL', 'Admin')
    			);
    			echo json_encode($result);
    			die;
    		}else{
    			$result = array(
    				'status' => 0,
    				'msg' => lan('OPERATION_FAILED', 'Admin')    				
    			);
    			echo json_encode($result);
    			die;                
    		}
    	}
    }

    //用户禁言解禁
    function user_shutup_rez() {
    	$db = M('shutuprecord');
    	$p = I('get.p',0);
    	$now = date('Y-m-d');
        $searchform = I('get.searchform');
        $starttime = $_GET['start_time'];
        $endtime = $_GET['end_time'];        
		// 用户查询条件
		if($starttime != '') $map['createtime'] = array('gt',$starttime);
		if($endtime != '') $map['createtime'] = array(array('gt',$starttime),array('lt', $endtime.' 23:59:59')) ;
        if ($searchform != 1) {
            $starttime = $now;
            $endtime = $now;
            $map['createtime'] = array(array('egt',$starttime.' 00:00:00'),array('elt', $starttime.' 23:59:59'));
        }
        $search['starttime'] = $starttime;
        $search['endtime'] = $endtime;		

		$userid = $this->getUserIdByName($_GET['keyword']);
		if (!$userid) {
            $userinfo_username = M('Member')->where('username="'.$_GET['keyword'].'"')->find();            
            $userinfo_nickname = M('Member')->where('nickname like "%'.$_GET['keyword'].'%"')->find();
            if ($userinfo_username) {
                $userid = $userinfo_username['userid'];
            }elseif ($userinfo_nickname) {
                $userid = $userinfo_nickname['userid'];
            }else{
                $userid = $_GET['keyword'];
            }
		}

		if($_GET['keyword'] != '' && $_GET['keyword'] != lan('USERNAME', 'Admin').'/'.lan('NICKNAME', 'Admin')){
			$where['forbidenuserid']  = array('eq', $userid);
			$where['_logic'] = 'or';
			$map['_complex'] = $where;
		}   
        $map['forbidenuserid']  = array('gt', 0);

		$count = $db->where($map)->count();
	    $row = 20;
		$page = new Page($count,$row);
		$data['page'] = $page->show();
		$data['list'] = $db->where($map)->order('createtime desc')->page($p,$row)->select();

    	foreach ($data['list'] as $k => $v) {
    	    $forbidenuserid = $v['forbidenuserid'];
    	    $forbidenuserinfo = $this->getUserInfo($forbidenuserid);
    	    $data['list'][$k]['forbidenusername'] = $forbidenuserinfo['username'];
    	    $data['list'][$k]['forbidenusernickname'] = $forbidenuserinfo['nickname'];

    	    $forbiddinguserid = $v['userid'];
    	    $forbiddinguserinfo = $this->getUserInfo($forbiddinguserid);
    	    $data['list'][$k]['forbiddingusername'] = $forbiddinguserinfo['username'];   

    	    $emceeuserid = $v['emceeuserid'];
    	    $emceeuserinfo = $this->getUserInfo($emceeuserid);
    	    if ($emceeuserinfo['niceno']) {
    	     	$data['list'][$k]['roomno'] = $emceeuserinfo['niceno'];  
    	    }else{
    	        $data['list'][$k]['roomno'] = $emceeuserinfo['roomno'];     	    	
    	    } 

            $nowtime = date('Y-m-d H:i:s');
            if($v['expiretime'] > $nowtime){
            	$data['list'][$k]['status'] = 1;
            } 

    	}
    
    	$this->assign('keyword',$_GET['keyword']);    	
    	$this->assign('now',$now);
    	$this->assign('data',$data);
        $this->assign('search',$search);        
    	$this->display();
    }    

    function do_user_shutup_rez() {
    	if (IS_POST && IS_AJAX) {
    		$shutupid = I('post.id');
    		$db = M('shutuprecord');
    		$shutuprecord = $db->find($shutupid);
    		if ($shutuprecord) {
    			$updateData['expiretime'] = date("Y-m-d H:i:s",0); 
    			$db->where('shutupid='.$shutupid)->save($updateData);
    			$result = array(
    				'status' => 1,
    				'msg' => lan('RELIEVE_SUCCESSFULL', 'Admin')
    			);
    			echo json_encode($result);
    			die;
    		}else{
    			$result = array(
    				'status' => 0,
    				'msg' => lan('OPERATION_FAILED', 'Admin')    				
    			);
    			echo json_encode($result);
    			die;                
    		}
    	}
    } 

    //待处理举报
    function pending_report() {
    	$db_Report = M('Report');
		// 用户查询条件
		if($_GET['start_time'] != '') $map['createtime'] = array('gt', $_GET['start_time']);
		if($_GET['end_time'] != '') $map['createtime'] = array(array('gt',$_GET['start_time']),array('lt', $_GET['end_time'].' 23:59:59')) ;

		$userid = $this->getUserIdByName($_GET['keyword']);
		if (!$userid) {
            $userinfo_username = M('Member')->where('username="'.$_GET['keyword'].'"')->find();            
            $userinfo_nickname = M('Member')->where('nickname like "%'.$_GET['keyword'].'%"')->find();
            $userinfo_roomno = M('Member')->where('roomno="'.$_GET['keyword'].'"')->find();
            $userinfo_niceno = M('Member')->where('niceno="'.$_GET['keyword'].'"')->find();   
            if ($userinfo_username) {
                $userid = $userinfo_username['userid'];
            }elseif ($userinfo_nickname) {
                $userid = $userinfo_nickname['userid'];
            }elseif ($userinfo_roomno) {
                $userid = $userinfo_roomno['userid'];
            }elseif ($userinfo_niceno) {
                $userid = $userinfo_niceno['userid'];
            }else{
                $userid = $_GET['keyword'];
            }
		}		    
		if($_GET['keyword'] != '' && $_GET['keyword'] != lan('USERNAME', 'Admin').'/'.lan('NICKNAME', 'Admin').'/'.lan('ROOMNO', 'Admin')){
			$map['reporteduid']  = array('eq', $userid);
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
                	$this->success('',U('Admin/show/pending_report'));
                }            	
            }
    	}else{
    	    $reporteduid = I('get.reporteduid','');
    	    if ($reporteduid == '') die;
		    // 用户查询条件
		    $userid = $this->getUserIdByName($_GET['keyword']);
		    if (!$userid) {
                $userinfo_username = M('Member')->where('username="'.$_GET['keyword'].'"')->find();            
                $userinfo_nickname = M('Member')->where('nickname like "%'.$_GET['keyword'].'%"')->find();
                $userinfo_roomno = M('Member')->where('roomno="'.$_GET['keyword'].'"')->find();
                $userinfo_niceno = M('Member')->where('niceno="'.$_GET['keyword'].'"')->find();   
                if ($userinfo_username) {
                    $userid = $userinfo_username['userid'];
                }elseif ($userinfo_nickname) {
                    $userid = $userinfo_nickname['userid'];
                }elseif ($userinfo_roomno) {
                    $userid = $userinfo_roomno['userid'];
                }elseif ($userinfo_niceno) {
                    $userid = $userinfo_niceno['userid'];
                }else{
                    $userid = $_GET['keyword'];
                }
		    }		    
		    if($_GET['keyword'] != '' && $_GET['keyword'] != lan('USERNAME', 'Admin').'/'.lan('NICKNAME', 'Admin').'/'.lan('ROOMNO', 'Admin')){
		    	$map['userid']  = array('eq', $userid);
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
                $msgbantime = $ban['bantime'].lan('MINUTE', 'Admin');  
                $expiretime = date('Y-m-d H:i:s',strtotime('+'.$ban['bantime'].' minutes'));              
            }      
            $banmoney = I('POST.banmoney',0, 'intval');      
            $violationMoney = M('Violatedefinition')->where('type=4 AND `key`='.$banmoney.' AND lantype="'.$lantype.'"')->find();        
            $ban['punishmoney'] = $violationMoney['value'];  
            $ban['processadminid'] = session('adminid');  
            $ban['processtime'] = date('Y-m-d H:i:s');
            $ban['expiretime'] = $expiretime;
            $ban['isopen'] = 0;  
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
                    'title' => lan('SYSTEM_MESSAGE', 'Admin'),
                    'content' => lan('YOU_ILLEGAL_LIVE', 'Admin').lan('ALREADY_BAN', 'Admin').$msgbantime.','.lan('CONTACT_HOTLINE', 'Admin'),
                    'lantype' => $lantype,
                    'createtime' => date('Y-m-d H:i:s')           
                );
                D('Message')->SendMessageToUser($MessageData);

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
                            'title' => lan('SYSTEM_MESSAGE', 'Admin'),
                            'content' => $reporteduser['nickname'].' '.lan('ILLEGAL_LIVE', 'Admin').','.lan('ALREADY_BAN', 'Admin').$msgbantime.','.lan('THANK_REPORT', 'Admin'),
                            'lantype' => $lantype,
                            'createtime' => date('Y-m-d H:i:s')                 			
                		);
                		D('Message')->SendMessageToUser($MessageUserData);
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
            $ban['processadminid'] = session('adminid');  
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

    //未违规记录
    function no_violation() {
    	$db_Report = M('Report');
		// 用户查询条件
		if($_GET['start_time'] != '') $map['createtime'] = array('gt', $_GET['start_time']);
		if($_GET['end_time'] != '') $map['createtime'] = array(array('gt',$_GET['start_time']),array('lt', $_GET['end_time'].' 23:59:59')) ;

		$userid = $this->getUserIdByName($_GET['keyword']);
		if (!$userid) {
            $userinfo_username = M('Member')->where('username="'.$_GET['keyword'].'"')->find();            
            $userinfo_nickname = M('Member')->where('nickname like "%'.$_GET['keyword'].'%"')->find();
            $userinfo_roomno = M('Member')->where('roomno="'.$_GET['keyword'].'"')->find();
            $userinfo_niceno = M('Member')->where('niceno="'.$_GET['keyword'].'"')->find();   
            if ($userinfo_username) {
                $userid = $userinfo_username['userid'];
            }elseif ($userinfo_nickname) {
                $userid = $userinfo_nickname['userid'];
            }elseif ($userinfo_roomno) {
                $userid = $userinfo_roomno['userid'];
            }elseif ($userinfo_niceno) {
                $userid = $userinfo_niceno['userid'];
            }else{
                $userid = $_GET['keyword'];
            }
		}		    
		if($_GET['keyword'] != '' && $_GET['keyword'] != lan('USERNAME', 'Admin').'/'.lan('NICKNAME', 'Admin').'/'.lan('ROOMNO', 'Admin')){
			$map['reporteduid']  = array('eq', $userid);
		}

        $p = I('get.p',1);
        $map['isprocess'] = 1;
        $map['isviolate'] = 0;        
		$count = count($db_Report->where($map)->group('liveid,reporteduid')->select());
	    $row = 50;
        
        $subQuery = $db_Report->where($map)->order('createtime DESC')->buildSql(); 
        $data = $db_Report->table($subQuery.' a')->field('a.*,count(reportid) AS reportedcount')->group('a.processtime,a.reporteduid')->order('a.createtime DESC')->page($p,$row)->select(); 

        foreach ($data as $k => $v) {
        	$field = array(
        		'username',
        		'nickname',
                'roomno',
                'niceno'
        	);
            $reporteduser = $this->getUserInfoById($v['reporteduid'],$field);
            $operationinfo = M('Admin')->find($v['processor']);
            $data[$k]['operation'] = $operationinfo['adminname'];            
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

    //未违规记录详情
    function no_violation_detail() {
    	$db_Report = M('Report');
    	$reporteduid = I('get.reporteduid','',intval);
    	$processtime = I('get.processtime');
    	if ($reporteduid == '') die;
		// 用户查询条件
		$userid = $this->getUserIdByName($_GET['keyword']);
		if (!$userid) {
            $userinfo_username = M('Member')->where('username="'.$_GET['keyword'].'"')->find();            
            $userinfo_nickname = M('Member')->where('nickname like "%'.$_GET['keyword'].'%"')->find();
            $userinfo_roomno = M('Member')->where('roomno="'.$_GET['keyword'].'"')->find();
            $userinfo_niceno = M('Member')->where('niceno="'.$_GET['keyword'].'"')->find();   
            if ($userinfo_username) {
                $userid = $userinfo_username['userid'];
            }elseif ($userinfo_nickname) {
                $userid = $userinfo_nickname['userid'];
            }elseif ($userinfo_roomno) {
                $userid = $userinfo_roomno['userid'];
            }elseif ($userinfo_niceno) {
                $userid = $userinfo_niceno['userid'];
            }else{
                $userid = $_GET['keyword'];
            }
		}		    
		if($_GET['keyword'] != '' && $_GET['keyword'] != lan('USERNAME', 'Admin').'/'.lan('NICKNAME', 'Admin').'/'.lan('ROOMNO', 'Admin')){
			$map['userid']  = array('eq', $userid);
		}
    
        $p = I('get.p',1);
        $map['isprocess'] = 1;
        $map['isviolate'] = 0;        
        $map['reporteduid'] = $reporteduid;
        if ($processtime != '') {
        	$map['processtime'] = $processtime;     
        }
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
    	$this->assign('processtime',$processtime); 
    	$this->assign('roomno',$reporteduser['roomno']);  
    	$this->assign('showroomno',$showroomno);    	    	    	    
    	$this->display();    		
    }

    //违规记录
    function violation_record() {
    	$db_Ban = M('Banrecord');
		// 用户查询条件
		if($_GET['start_time'] != '') $map['ws_banrecord.processtime'] = array('gt', $_GET['start_time']);
		if($_GET['end_time'] != '') $map['ws_banrecord.processtime'] = array(array('gt',$_GET['start_time']),array('lt', $_GET['end_time'].' 23:59:59')) ;

		$userid = $this->getUserIdByName($_GET['keyword']);
		if (!$userid) {
            $userinfo_username = M('Member')->where('username="'.$_GET['keyword'].'"')->find();            
            $userinfo_nickname = M('Member')->where('nickname like "%'.$_GET['keyword'].'%"')->find();
            $userinfo_roomno = M('Member')->where('roomno="'.$_GET['keyword'].'"')->find();
            $userinfo_niceno = M('Member')->where('niceno="'.$_GET['keyword'].'"')->find();   
            if ($userinfo_username) {
                $userid = $userinfo_username['userid'];
            }elseif ($userinfo_nickname) {
                $userid = $userinfo_nickname['userid'];
            }elseif ($userinfo_roomno) {
                $userid = $userinfo_roomno['userid'];
            }elseif ($userinfo_niceno) {
                $userid = $userinfo_niceno['userid'];
            }else{
                $userid = $_GET['keyword'];
            }
		}		    
		if($_GET['keyword'] != '' && $_GET['keyword'] != lan('USERNAME', 'Admin').'/'.lan('NICKNAME', 'Admin').'/'.lan('ROOMNO', 'Admin')){
			$map['ws_banrecord.userid']  = array('eq', $userid);
		}

        $p = I('get.p',1);
		$count = count($db_Ban->where($map)->select());
	    $row = 50;
        
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
            $userinfo_username = M('Member')->where('username="'.$_GET['keyword'].'"')->find();            
            $userinfo_nickname = M('Member')->where('nickname like "%'.$_GET['keyword'].'%"')->find();
            $userinfo_roomno = M('Member')->where('roomno="'.$_GET['keyword'].'"')->find();
            $userinfo_niceno = M('Member')->where('niceno="'.$_GET['keyword'].'"')->find();   
            if ($userinfo_username) {
                $userid = $userinfo_username['userid'];
            }elseif ($userinfo_nickname) {
                $userid = $userinfo_nickname['userid'];
            }elseif ($userinfo_roomno) {
                $userid = $userinfo_roomno['userid'];
            }elseif ($userinfo_niceno) {
                $userid = $userinfo_niceno['userid'];
            }else{
                $userid = $_GET['keyword'];
            }
		}		    
		if($_GET['keyword'] != '' && $_GET['keyword'] != lan('USERNAME', 'Admin').'/'.lan('NICKNAME', 'Admin').'/'.lan('ROOMNO', 'Admin')){
			$map['userid']  = array('eq', $userid);
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

    //根据用户id获得用户信息
    function getUserInfoById($userid,$field = '') {
		$userCond = array(
			'userid' => $userid,
		);
        return M('Member')->where($userCond)->field($field)->find();
    }

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

		$this->success();
	}  

    //禁播redis管理
    public function banlive_manage(){
        $key = 'BanLive';
        $value = $this->redis->hGetAll($key);
        $i = 0;
        $now = date('Y-m-d H:i:s');
        foreach ($value as $k => $v) {
            $time = json_decode($v,true);
            if ($time['failuretime'] > $now || $time['failuretime'] == -1) {
                $data[$i] = $time;
                $data[$i]['hkey'] = $k;
                $i++;                
            }
        } 
        $data = array_reverse($data);
        $this->assign('data',$data); 
        $this->display();
    }

    //禁播redis更新
    public function banlive_update(){
        $key = 'BanLive';
        $db_Ban = M('Banrecord');

        $forbiddenList = M('Emceeproperty')->field('userid')->where('isforbidden=1')->select();
        foreach ($forbiddenList as $k => $v) {
            $addData['userid'] = $v['userid'];
            $addData['violatelevel'] = 2;
            $addData['bantime'] = -1;
            $addData['processadminid'] = session('adminid'); 
            $addData['processtime'] = date('Y-m-d H:i:s');
            $addData['expiretime'] = -1; 
            $banInfo = $db_Ban->where('userid='.$v['userid'])->order('banid DESC')->find();
            if ($banInfo && $banInfo['expiretime'] == -1) {
                $db_Ban->where('banid='.$banInfo['banid'])->save($addData);                                                            
            }else{
                $db_Ban->add($addData);                
            }                                                           
        }

        $subQuery = $db_Ban->order('banid DESC')->buildSql();
        $where['IFNULL(b.expiretime,0)'] = array(array('gt',date('Y-m-d H:i:s')),array('eq',-1),'OR');
        $banList = $db_Ban->table($subQuery.' b')->where($where)->group('b.userid')->order('b.processtime DESC')->select();
        foreach ($banList as $k => $v) {
            $hashKey = 'Emcee_'.$v['userid'];
            $value = array(
                'failuretime' => $v['expiretime']
            );
            $value = json_encode($value);
            $this->redis->hSet($key,$hashKey,$value);            
        }
        
        $this->success('',U('Admin/Show/banlive_manage'));
    }

	/**
	 * 获取当前所有被禁播的主播userid
	 */
	public function emceeBanlive() {
        $key = 'BanLive';
        $BanliveList = $this->redis->hKeys($key);
        $list = array();
        foreach ($BanliveList as $k => $v) {
            $hashKey = $v;       
            $emceeBanLive = $this->redis->hGet($key,$hashKey);
            $emceeBanLiveValue = json_decode($emceeBanLive,true);
            $now = date('Y-m-d H:i:s');
            if($emceeBanLiveValue['failuretime'] > $now || $emceeBanLiveValue['failuretime'] == -1){
            	$list[$k] = substr($v,6);
            }        	
        }
		return $list;
	}	

    /**
     * 设置、取消巡查员
     */
    public function set_inspector(){
        if (IS_POST && IS_AJAX) {
            $db_Member = M('Member');
            $userid = I('post.userid','intval');
            $type = I('post.type','intval');
            switch ($type) {
                case '1':  //设为巡查员
                    $save_data = array(
                        'usertype' => 10
                    );
                    $result = $db_Member->where(array('userid'=>$userid))->save($save_data);
                    if ($result) {
                        $data = array(
                            'status' => 1,
                            'message' => lan('OPERATION_SUCCESSFUL', 'Admin')
                        );                        
                    }
                    break;
                case '2':  //取消巡查员
                    $save_data = array(
                        'usertype' => 0
                    );
                    $result = $db_Member->where(array('userid'=>$userid))->save($save_data);
                    if ($result) {
                        $data = array(
                            'status' => 1,
                            'message' => lan('OPERATION_SUCCESSFUL', 'Admin')
                        );                        
                    }                    
                    break;                    
                default:   //判断当前身份
                    $userInfo = $db_Member->where(array('userid'=>$userid))->find();
                    switch ($userInfo['usertype']) {
                        case '10':
                            $data = array(
                                'status' => 0,
                                'message' => lan('CONFIRM_CANCEL_INSPECTOR', 'Admin')
                            );
                            break;
                        case '20':
                            $data = array(
                                'status' => 1,
                                'message' => lan('USER_TYPE_IS', 'Admin').lan('FAMILY_MANAGER', 'Admin').', '.lan('CONFIRM_SET_AS_INSPECTOR', 'Admin')
                            );
                            break;
                        case '30':
                            $data = array(
                                'status' => 1,
                                'message' => lan('USER_TYPE_IS', 'Admin').lan('OPERATOR_ASSISTANT', 'Admin').', '.lan('CONFIRM_SET_AS_INSPECTOR', 'Admin')
                            );
                            break;                                        
                        default:
                            $data = array(
                                'status' => 1,
                                'message' => lan('CONFIRM_SET_AS_INSPECTOR', 'Admin')
                            );
                    }                    
            }

            echo json_encode($data);   
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
        $result = array(
            'status' => 1,
            'msg' => lan('OPERATION_SUCCESSFUL','Home'),
            'src' => $save_src
        );
        $this->ajaxReturn($result);
    }
}