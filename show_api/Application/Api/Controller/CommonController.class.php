<?php
namespace Api\Controller;
use Think\Controller;

/*
** 备注：接口传语言包参数的时候，会传：  lantype : en  cn  vi
 */
class CommonController extends Controller {

    public function _initialize() {
        //获取语言类型
        $this->lantype = I('post.lantype', '', 'trim') ? I('post.lantype', '', 'trim') : 'vi';
        $lanTypes = C('LAN_TYPE');
        if (!in_array($this->lantype, $lanTypes)) {
            $this->lantype = 'en';
        }
        $this->lanPackage = 'code_' . $this->lantype;
        date_default_timezone_set(C('DEFAULT_TIMEZONE'));
    }
    
    // , 'token' 查询用户表返回字段定义
    public $memberfield = array(
        'userid',
        'userno',
        'username',
        'roomno',
        'niceno',
        'salt',
        'familyid',
        'nickname',
        'userlevel',
        'province',
		'sex',
		'birthday',
        'city',
        'smallheadpic',
        'bigheadpic',
        'lastlogintime',
        'lastloginip',
        'isemcee',
        'isvirtual',
        'isvip',
        'usertype',
        'token'
    );
            
    public function getShowroomno($userinfo){
        if(!empty($userinfo['niceno'])){
            return  $userinfo['niceno'];
        }else{
            return  $userinfo['roomno'];
        }
    }
	
	/**
	 * 当用户有消费时，更新用户等级
	 */
	protected function updateUserlevel($userInfo, $balanceInfo)
	{
	    $newUserlevel = D('Levelconfig', 'Modapi')->getUserLevelBySpendMoney($balanceInfo['spendmoney'], $this->lantype);
	    if ($newUserlevel && $newUserlevel != $userInfo['userlevel'])
	    {
	        $db_Member = M('Member');
	        $userNewInfo['userlevel'] = $newUserlevel;
	        $db_Member->where(array('userid'=>$userInfo['userid']))->save($userNewInfo);
	    }
	}

    //生成订单号
    protected function createOrderNo($type,$id){
        $orderno = date('YmdHis').rand(1000,9999).$type.$id;
        return $orderno;
    }
	
	/**
	 * 当主播有收入时，更新用户等级
	 */
	protected function updateEmceelevel($emceeInfo, $balaneInfo)
	{
	    $newEmceelevel = D('Levelconfig', 'Modapi')->getEmceeLevelByEarnMoney($balaneInfo['earnmoney'], $this->lantype);
	    if ($newEmceelevel && $newEmceelevel != $emceeInfo['emceelevel'])
	    {
	        $db_Emceeproperty = M('Emceeproperty');
	        $newEmceeInfo['emceelevel'] = $newEmceelevel;
	        $db_Emceeproperty->where(array('userid'=>$emceeInfo['userid']))->save($newEmceeInfo);
	    }
	}

	/**
	 * @param $toplist_type、$virtualdatacount、$maxdata
	 * 该方法构造排行榜虚拟数据
	 * 根据$toplist_type排行榜类型，主播榜随机取1-100，用户榜随机取101-1000，根据$virtualdatacount和$maxdata确定构造数据的条数和最大值
	 */
	public function getTopListVirtualData($toplist_type,$virtualdatacount,$maxdata,$range)
	{
        switch ($toplist_type) {
        	case 'EmceeEarn': //主播收入榜
                $field = array(
                    'm.userid','m.nickname','m.smallheadpic','m.roomno','e.emceelevel'
                );
        	    $map['m.userid'] = array(array('elt',100),array('egt',1));
        		$virtual_toplist = M('Member m')
                    ->field($field)        		
                    ->join('ws_emceeproperty e on e.userid = m.userid')
                    ->where($map)
                    ->order('rand()')
                    ->limit('0,'.$virtualdatacount)->select();
                switch($range){
                    case 'd':
                        $maxdata_virtual = 65;
                        break;
                    case 'w':
                        $maxdata_virtual = 80;
                        break;
                    case 'm':
                        $maxdata_virtual = 90;
                        break;                
                    default :
                        $maxdata_virtual = 120;
                }
                if (!empty($maxdata)) {
                    $maxdata = $maxdata < $maxdata_virtual ? $maxdata : $maxdata_virtual;
                } else {
                    $maxdata = $maxdata_virtual;
                }    
                foreach ($virtual_toplist as $k => $v) {
                	$virtual_toplist[$k]['earnamount'] = mt_rand(1,$maxdata);
                    $virtual_toplist[$k]['value'] = $virtual_toplist[$k]['earnamount'];
                	$name[$k] = $virtual_toplist[$k]['earnamount'];
                }
                array_multisort($name,SORT_DESC,$virtual_toplist); //数组排序
        		break;
        	case 'UserRich': //用户消费榜
                $field = array(
                    'm.userid','m.nickname','m.smallheadpic','m.userlevel'
                );                
        	    $map['m.userid'] = array(array('elt',1000),array('egt',101));
        		$virtual_toplist = M('Member m')
                    ->field($field)        		
                    ->where($map)
                    ->order('rand()')
                    ->limit('0,'.$virtualdatacount)->select();
                switch($range){
                    case 'd':
                        $maxdata_virtual = 85;
                        break;
                    case 'w':
                        $maxdata_virtual = 95;
                        break;
                    case 'm':
                        $maxdata_virtual = 200;
                        break;                
                    default :
                        $maxdata_virtual = 300;
                }
                if (!empty($maxdata)) {
                    $maxdata = $maxdata < $maxdata_virtual ? $maxdata : $maxdata_virtual;
                } else {
                    $maxdata = $maxdata_virtual;
                }    
                foreach ($virtual_toplist as $k => $v) {
                	$virtual_toplist[$k]['spendamount'] = mt_rand(1,$maxdata);
                    $virtual_toplist[$k]['value'] = $virtual_toplist[$k]['spendamount'];
                	$name[$k] = $virtual_toplist[$k]['spendamount'];
                }
                array_multisort($name,SORT_DESC,$virtual_toplist); //数组排序        		
        		break; 
        	case 'NewFans': //新增用户关注榜
                $field = array(
                    'm.userid','m.nickname','m.smallheadpic','m.roomno','e.emceelevel'
                );
        	    $map['m.userid'] = array(array('elt',100),array('egt',1));
        		$virtual_toplist = M('Member m')
                    ->field($field)        		
                    ->join('ws_emceeproperty e on e.userid = m.userid')
                    ->where($map)
                    ->order('rand()')
                    ->limit('0,'.$virtualdatacount)->select();
                switch($range){
                    case 'd':
                        $maxdata_virtual = 5;
                        break;
                    case 'w':
                        $maxdata_virtual = 7;
                        break;
                    case 'm':
                        $maxdata_virtual = 15;
                        break;                
                    default :
                        $maxdata_virtual = 20;
                }
                if (!empty($maxdata)) {
                    $maxdata = $maxdata < $maxdata_virtual ? $maxdata : $maxdata_virtual;
                } else {
                    $maxdata = $maxdata_virtual;
                }    
                foreach ($virtual_toplist as $k => $v) {
                	$virtual_toplist[$k]['friendcount'] = mt_rand(1,$maxdata);
                    $virtual_toplist[$k]['value'] = $virtual_toplist[$k]['friendcount'];
                	$name[$k] = $virtual_toplist[$k]['friendcount'];
                }
                array_multisort($name,SORT_DESC,$virtual_toplist); //数组排序
        		break; 
        	case 'LiveTime': //主播直播时长榜
                $field = array(
                    'm.userid','m.nickname','m.smallheadpic','m.roomno','e.emceelevel'
                );
        	    $map['m.userid'] = array(array('elt',100),array('egt',1));
        		$virtual_toplist = M('Member m')
                    ->field($field)        		
                    ->join('ws_emceeproperty e on e.userid = m.userid')
                    ->where($map)
                    ->order('rand()')
                    ->limit('0,'.$virtualdatacount)->select();
                switch($range){
                    case 'd':
                        $maxdata_virtual = 10800;
                        break;
                    case 'w':
                        $maxdata_virtual = 12600;
                        break;
                    case 'm':
                        $maxdata_virtual = 14400;
                        break;                
                    default :
                        $maxdata_virtual = 18000;
                }
                if (!empty($maxdata)) {
                    $maxdata = $maxdata < $maxdata_virtual ? $maxdata : $maxdata_virtual;
                } else {
                    $maxdata = $maxdata_virtual;
                }    
                foreach ($virtual_toplist as $k => $v) {
                	$virtual_toplist[$k]['living_length'] = mt_rand(60,$maxdata);
                    $virtual_toplist[$k]['value'] = $virtual_toplist[$k]['living_length'];
                	$name[$k] = $virtual_toplist[$k]['living_length'];
                }
                array_multisort($name,SORT_DESC,$virtual_toplist); //数组排序
        		break;
        	case 'OnlineTime': //用户在线时长榜
                $field = array(
                    'm.userid','m.nickname','m.smallheadpic','m.userlevel'
                );                
        	    $map['m.userid'] = array(array('elt',1000),array('egt',101));
        		$virtual_toplist = M('Member m')
                    ->field($field)        		
                    ->where($map)
                    ->order('rand()')
                    ->limit('0,'.$virtualdatacount)->select();
                switch($range){
                    case 'd':
                        $maxdata_virtual = 8280;
                        break;
                    case 'w':
                        $maxdata_virtual = 10800;
                        break;
                    case 'm':
                        $maxdata_virtual = 12600;
                        break;                
                    default :
                        $maxdata_virtual = 16200;
                }
                if (!empty($maxdata)) {
                    $maxdata = $maxdata < $maxdata_virtual ? $maxdata : $maxdata_virtual;
                } else {
                    $maxdata = $maxdata_virtual;
                }    
                foreach ($virtual_toplist as $k => $v) {
                	$virtual_toplist[$k]['online_time'] = mt_rand(60,$maxdata);
                    $virtual_toplist[$k]['value'] = $virtual_toplist[$k]['online_time'];
                	$name[$k] = $virtual_toplist[$k]['online_time'];
                }
                array_multisort($name,SORT_DESC,$virtual_toplist); //数组排序        		
        		break;
            case 'SportMasters': //运动大师排行榜
                $field = array(
                    'm.userid','m.nickname','m.smallheadpic','m.userlevel'
                );                
                $map['m.userid'] = array(array('elt',1000),array('egt',101));
                $virtual_toplist = M('Member m')
                    ->field($field)             
                    ->where($map)
                    ->order('rand()')
                    ->limit('0,'.$virtualdatacount)->select();
                switch($range){
                    case 'd':
                        $maxdata_virtual = 50;
                        break;
                    case 'w':
                        $maxdata_virtual = 200;
                        break;
                    case 'm':
                        $maxdata_virtual = 1200;
                        break;                
                    default :
                        $maxdata_virtual = 3000;
                }
                if (!empty($maxdata)) {
                    $maxdata = $maxdata < $maxdata_virtual ? $maxdata : $maxdata_virtual;
                } else {
                    $maxdata = $maxdata_virtual;
                }    
                foreach ($virtual_toplist as $k => $v) {
                    $virtual_toplist[$k]['allearnmoney'] = (string)mt_rand(1,$maxdata);
                    $virtual_toplist[$k]['value'] = $virtual_toplist[$k]['allearnmoney'];
                    $name[$k] = $virtual_toplist[$k]['allearnmoney'];
                }
                array_multisort($name,SORT_DESC,$virtual_toplist); //数组排序               
                break;
            case 'EmceeFreeGift':   //主播免费礼物榜
                $field = array(
                    'm.userid','m.nickname','m.smallheadpic','m.roomno','e.emceelevel'
                );
                $map['m.userid'] = array(array('elt',100),array('egt',1));
                $virtual_toplist = M('Member m')
                    ->field($field)
                    ->join('ws_emceeproperty e on e.userid = m.userid')
                    ->where($map)
                    ->order('rand()')
                    ->limit('0,'.$virtualdatacount)->select();
                switch($range){
                    case 'd':
                        $maxdata_virtual = 65;
                        break;
                    case 'w':
                        $maxdata_virtual = 80;
                        break;
                    case 'm':
                        $maxdata_virtual = 90;
                        break;
                    default :
                        $maxdata_virtual = 120;
                }
                if (!empty($maxdata)) {
                    $maxdata = $maxdata < $maxdata_virtual ? $maxdata : $maxdata_virtual;
                } else {
                    $maxdata = $maxdata_virtual;
                }
                foreach ($virtual_toplist as $k => $v) {
                    $virtual_toplist[$k]['freegiftcount'] = mt_rand(1,$maxdata);
                    $virtual_toplist[$k]['value'] = $virtual_toplist[$k]['freegiftcount'];
                    $name[$k] = $virtual_toplist[$k]['freegiftcount'];
                }
                array_multisort($name,SORT_DESC,$virtual_toplist); //数组排序
                break;
        	default:
        		
        }
		return $virtual_toplist;
	}	

    /*
    ** 函数作用：把时间长度转换为小时分钟显示
    ** 参数：$length:时长，$type:时长类型（s秒、m分钟、h小时）
     */
    public function getTimeLength($length,$type='s') {
        $ShowLength = '';
        switch($type){
            case 'm':
                $day = floor($length/1440);
                $hour = floor($length/60)%24;
                $minute = $length%60;
                break;
            case 'h':
                $day = floor($length/24);
                $hour = $length%24;
                $minute = 0;
                break;
            default:
                $day = floor($length/86400);
                $hour = floor($length/3600)%24;
                $minute = floor($length/60)%60;
        }
    
        if($day > 0){
            $ShowLength .= $day.lan('DAY','Api', $this->lantype).$hour.lan('HOUR','Api', $this->lantype).$minute.lan('MINUTE','Api', $this->lantype);
        }elseif($hour > 0){
            $ShowLength .= $hour.lan('HOUR','Api', $this->lantype).$minute.lan('MINUTE','Api', $this->lantype);
        }else{
            $ShowLength .= $minute.lan('MINUTE', 'Api', $this->lantype);
        }
        return $ShowLength;
    }    

    function getCurWeekBegin()
    {
        //星期中的第几天，数字表示，N和w。N:1（表示星期一）到 7（表示星期天）;w:0（表示星期天）到 6（表示星期六）
    //    return date('Y-m-d H:i:s', mktime(0,0,0,date('m'), date('d')-date('w'), date('Y')));
        return date('Y-m-d H:i:s', mktime(0,0,0,date('m'), date('d')-date('N')+1, date('Y')));
    }    
}