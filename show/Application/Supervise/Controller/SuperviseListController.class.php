<?php
namespace Supervise\Controller;
use Think\Page;

class SuperviseListController extends CommonController {
    //监控列表
    public function supervise_list(){
        if (IS_POST && IS_AJAX) {
            $db_Member = M('Member m');
            $p = I('post.p',0);
            $field = array('m.userid,IF(m.niceno>0,m.niceno,m.roomno) AS roomno,m.nickname,e.livetype,m.bigheadpic');
            $livetype = I('post.livetype');
            switch ($livetype) {
                case 'app':
                    $map['e.livetype'] = array(array('neq', 2),array('EXP', 'is null'),'OR');
                    break;
                default:
                    $map['e.livetype'] = 2;
            }
            $map['e.isliving'] = 1;

            $keyword = I('post.keyword');
            if (!empty($keyword)) {
                $where['username'] = array('eq',$keyword);                
            	$where['nickname'] = array('like','%'.$keyword.'%');
            	$where['roomno'] = array('eq',$keyword);
            	$where['niceno'] = array('eq',$keyword);   
            	$where['_logic'] = 'or';
            	$map['_complex'] = $where;        	
            }

            $count = $db_Member->join('ws_emceeproperty e ON e.userid=m.userid')->where($map)->count();
            $row = I('post.row');

            $livelist = $db_Member
                ->field($field)
                ->join('ws_emceeproperty e ON e.userid=m.userid')
                ->where($map)
                ->order('e.livetime DESC')
                ->page($p,$row)
                ->select();

            $result['data'] = $livelist;            
            $result['p'] = $p;
            $result['count'] = $count;
            echo json_encode($result); 
        }
    } 

    public function app_list() {
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
        $this->assign('baninfo',$baninfo);       
    	$this->display();
    }

    public function pc_list() {
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
        $this->assign('baninfo',$baninfo); 
    	$this->display();
    } 
}