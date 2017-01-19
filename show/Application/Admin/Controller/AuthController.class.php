<?php
namespace Admin\Controller;
//二次开发的控制器类
class AuthController extends CommonController {
	/**
	 * 用户管理
	 */
	function user(){
		// 实例化模型
		$db = D('Admin');
		
		$data = $db->getList();
		$this->assign('data',$data);
		$this->display();
	}
	
	function user_up(){
		// 实例化模型
		$db = D('Admin');
		$id = I('get.id');
		if(IS_POST){
			$pass = I('post.pass');
			if($pass!='') {
				if (!preg_match('/^[0-9a-zA-Z_]{6,16}$/is',$pass)) {
					$this->error(lan('PASSWORD_LENGTH_ERROR', 'Admin'));
				}
				$_POST['password'] = md5($pass);
			}
			// 自动验证		
			if(!$db->create()){
				$this->error($db->getError());
			}else{
				if($id!=''){ // 保存
					if($db->where('adminid='.$id)->save()){
						$this->success('',U('Auth/user'));
					}else{
						$this->error();
					}				
				}else{ // 新增
					if($pass == ''){
                        $this->error(lan('PLEASE_INPUT_PASSWORD', 'Admin'));
					}
					if($db->add()){
						$this->success('',U('Auth/user'));
					}else{
						$this->error();
					}
				}
			}
		}else{
			$data = $db->find($id);
			// 模版赋值输出
			$this->assign('data',$data);
			$this->assign('cate',D('Role')->getList());
			$this->display();
		}				
	}
	
	function auth(){
		if(IS_POST){
			$db = M('auth');
			$roleid = I('get.id',0);
			$menuid = I('post.menuid');
			if($menuid!=''){
				for($i=0;$i<count($menuid);$i++){
					$map['roleid'] = array('eq', $roleid);
					$map['menuid'] = array('eq', $menuid[$i]);
					$data['roleid'] = $roleid;
					$data['menuid'] = $menuid[$i];
					$data['createtime'] = date('Y-m-d');
					if($auth = $db->where($map)->find()){
						$db->where('authid='.$auth['authid'])->save($data);
					}else{
						$db->add($data);
					}
				}
				$this->success('',U('Auth/role'));
			}else{
				$this->error();
			}
		}else{

			$db_menu = D('Menu');			
			$data['auth'] = M('auth')->where('roleid='.I('get.id'))->select();
			$map_menu['menutype'] = array('gt',0);		
			$menu = $db_menu->getList($map_menu,getLanguage());
			// $data['menu'] = list_to_tree($menu,'menuid','parentid');
		
			$this->assign('data',$data);
			$this->display();
		}	
	}
	
	/**
	 * 角色管理
	 */
	function role(){
		// 实例化模型
		$db = D('Role');
		
		$data = $db->getList();		
		$this->assign('data',$data);
		$this->display();
	}
	
	function role_up(){
		// 实例化模型
		$db = D('Role');
		$id = I('get.id');
		if(IS_POST){
			// 自动验证		
			if(!$db->create()){
				$this->error($db->getError());
			}else{
				if($id!=''){ // 保存
					if($db->where('rid='.$id)->save()){
						$this->success('',U('Auth/role'));
					}else{
						$this->error();
					}				
				}else{ // 新增
					$roleinfo = $db->where('roleid='.I('post.roleid'))->find();
					if ($roleinfo) {
						$this->error(lan('ROLE_NUMBER', 'Admin').lan('EXISTS', 'Admin'));
					}else{
					    if($db->add()){
					    	$this->success('',U('Auth/role'));
					    }else{
					    	$this->error();
					    }						
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
	
	/**
	 * 菜单管理
	 */
	function menu(){
		// 实例化模型
		$db = D('Menu');
		
		$type = I('get.type');
		if($type=='') $map['menutype'] = array('gt',0);		
		if($type==1) $map['menutype'] = array('lt',1);	
		$data = $db->getList($map,getLanguage());	
		// if($type=='') $data = list_to_tree($data,'menuid','parentid');
		
		$this->assign('data',$data);
		$this->display();
	}
	
	function menu_up(){
		// 实例化模型
		$db = D('Menu');
		$id = I('get.id');
		if(IS_POST){
			// 自动验证		
			if(!$db->create()){
				$this->error($db->getError());
			}else{
				if($id!=''){ // 保存
					if($db->where('mid='.$id)->save()){
						$this->success('',U('Auth/menu'));
					}else{
						$this->error();
					}				
				}else{ // 新增
					if($db->add()){
						$this->success('',U('Auth/menu'));
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
	
	// 批量删除
	function delAll(){
		$model['4003'] = 'Admin';
		$model['4004'] = 'Role';
		$model['4005'] = 'Menu';
		$ids = I('post.ids');
		$db = $model[I('post.db')];

        $id = implode(",",$ids);
		if(M($db)->delete($id)){
			$this->success(lan('DELETE_SUCCESS', 'Admin'));
		}else{
			$this->error(lan('APPROVE_DELETE', 'Admin'));
		}
	}
	
	// 单个删除
	function delOne(){
		$model['4003'] = 'Admin';
		$model['4004'] = 'Role';
		$model['4005'] = 'Menu';
		$model['4006'] = 'Agent';
		$id = I('get.id');

		if (I('get.db') == '4004') {
			$roleinfo = M('Role')->where('rid='.$id)->find();
            $admininfo = M('Admin')->where('roleid='.$roleinfo['roleid'])->find();
            if ($admininfo) {
            	$this->error(lan('NO_AUTHORITY', 'Admin'));
            }
		}

		$db = $model[I('get.db')];
        $data['isdelete'] = 1;
		if (I('get.db') == '4003') {
			$vo = M($db)->where('adminid='.$id)->save($data);
		}
		elseif (I('get.db') == '4006') {
			$vo = M($db)->where('agentid='.$id)->save($data);
		}
		else {
			$vo = M($db)->delete($id);            
		}
		if($vo){
			$this->success(lan('DELETE_SUCCESS', 'Admin'));
		}else{
			$this->error(lan('APPROVE_DELETE', 'Admin'));
		}
	}

    //代理管理
    public function agent(){
        $where = array();
        //代理商名称
        $agentname = I('get.agentname');
        if($agentname){
            $where['agentname'] = array('like','%'.$agentname.'%');;
        }
        //代理商真实姓名
        $realname = I('get.realname');
        if($realname){
            $where['realname'] = array('like','%'.$realname.'%');;
        }
        //代理商电话
        $mobileno = I('get.mobileno');
        if($realname){
            $where['mobileno'] = array('like','%'.$mobileno.'%');;
        }
        $where['isdelete'] = 0;
        $order = 'agentid asc';
        $data = M('agent')->where($where)->order($order)->select();

        if($data){
            $agenttype[0] = lan('AGENT_BEFORE_PAY','Admin');
            $agenttype[1] = lan('AGENT_AFTER_PAY','Admin');
            foreach($data as $key => $val){
                $data[$key]['agenttype'] = $agenttype[$data[$key]['agenttype']];
            }
        }
        $this->assign('data',$data);
        $this->display();
    }

    //更新代理
    public function agent_update(){
        if(IS_POST){    //提交
            $db = M('agent');
            $data = I('post.');
            $agentid = I('post.agentid');
            $data['limitamount'] = I('post.limitamount') !='' ? I('post.limitamount') : 0;
            $result = false;

            if($agentid){   //编辑
                if(I('post.password')){
				    if (!preg_match('/^[0-9a-zA-Z_]{6,16}$/is',I('post.password'))) {
				    	$this->error(lan('PASSWORD_LENGTH_ERROR', 'Admin'));
				    }
                    $data['password'] = md5(I('post.password'));
                }else{
                    $data['password'] = $db->where("agentid='".$agentid."'")->getField('password');
                }
                if(I('agentname')){
                    $result = $db->where("agentid='".$agentid."'")->save($data);
                }
            }else{  //添加
                if(I('agentname') && I('post.password') && is_numeric($data['limitamount'])){
				    if (!preg_match('/^[0-9a-zA-Z_]{6,16}$/is',I('post.password'))) {
				    	$this->error(lan('PASSWORD_LENGTH_ERROR', 'Admin'));
				    }                	
                    $data['createtime'] = date('Y-m-d H:i:s');
                    $result = $db->add($data);
                }
            }

            if($result === false){
                $this->error();
            }else{
                $this->success('',U('Auth/agent'));
            }
        }else{  //读取
            $agent_info = array();
            $agentid = I('get.id');
            if($agentid){
                $agent_info = M('Agent')->where("agentid='".$agentid."'")->find();
            }
            $agenttype = array(
                array('id'=>'0','name'=>lan('AGENT_BEFORE_PAY','Admin')),
                array('id'=>'1','name'=>lan('AGENT_AFTER_PAY','Admin')),
            );
            $this->assign('agenttype',$agenttype);
            $this->assign('data',$agent_info);
            $this->display();
        }
    }

    //代理详情
    public function agent_detail(){
        $agent_info = array();
        $agentid = I('get.id');
        if($agentid){
            $agent_info = M('Agent')->where("agentid='".$agentid."'")->find();
            $agenttype[0] = lan('AGENT_BEFORE_PAY','Admin');
            $agenttype[1] = lan('AGENT_AFTER_PAY','Admin');
            $agent_info['agenttype'] = $agenttype[$agent_info['agenttype']];
        }

        $this->assign('data',$agent_info);
        $this->display();
    }
}