<?php
namespace Admin\Model;

class MemberModel extends BaseModel
{
    // 自动验证
    protected $_validate = array(
        array('username','require','用户名不能为空！'),
        array('birthday','require','生日不能为空！',2),
        array('username','','用户名已经存在！',0,'unique',1),
        array('userno','number','手机号码为数字！'),
    );

    // 自动完成
    protected $_auto = array (
        array('online','0'),
        array('smallheadpic','/Public/Public/Images/HeadImg/default.png'),
        // array('bigheadpic','/Public/Public/Images/HeadImg/268200/big.png'),
        array('registertime','getTime',1,'callback'),
        array('lastlogintime','getTime',3,'callback'),
        array('lastloginip','get_client_ip',3,'function'),
        array('time','time',3,'function'),
    );

    public $memberfields = array(
        'userid',
        'roomno',
        'niceno',
        'nickname',
        'userlevel',
        'smallheadpic',
        'usertype'
    );

    // 获取当前时间
    public function getTime(){
        return date('Y-m-d H:i:s');
    }

    // 获取用户列表
    function getList($map){
        $data = $this->where($map)->select();
        for($i=0;$i<count($data);$i++){
            $data[$i]['balance'] = $this->getUserBalance($data[$i]['userid']);
        }
        return $data;
    }

    /**
     * 获取用户余额积分
     */
    function getUserBalance($userid){
        return M('balance')->where(array('userid'=>array('eq',$userid)))->find();
    }

    /**
     * 创建用户
     */
    function userAdd($username, $password, $userno, $countrycode){
        $data['userno'] = $userno;
        $data['roomno'] = getRoomno();
        $data['username'] = $username;
        $data['nickname'] = getWaashowNickname($userno);
        $data['salt'] = getRandomCode(4);
        $data['password'] =  md5(md5($password).$data['salt']);
        $data['countrycode'] = $countrycode;
        $data['token'] =  date('YmdHis').$username.$password;

        if(!$this->create($data)){
            return $this->getError();
        }else{
            $n = $this->add();
            $userCond = array('userid' => $n);
            $newUserInfo['roomno'] = getUserRoomno($n);
            $this->where($userCond)->save($newUserInfo);
            if($n){
                // 创建房间表
                $data_room['roomno'] = $newUserInfo['roomno'];
                $data_room['roomname'] = $data['nickname'];
                $db_room = D('Room');
                if(!$db_room->create($data_room)){
                    $this->delete($n);
                    return $db_room->getError();
                }else{
                    if(!$m = $db_room->add()){
                        $this->delete($n);
                        return '创建失败';
                    }
                }
                // 创建积分金额表
                $data_member['userid'] = $n;
                $db_balance = D('Balance');
                if(!$db_balance->create($data_member)){
                    $this->delete($n);
                    $db_room->delete($m);
                    return $db_balance->getError();
                }else{
                    if(!$m = $db_balance->add()){
                        $this->delete($n);
                        $db_room->delete($m);
                        return '创建失败';
                    }
                }
                // 创建余额积分表
                $user['userid'] = $n;
                M('emceeproperty')->add($user);
                // 创建合同表
                $db_contract = D('Contract');
                $db_contract->create();
                $db_contract->userid = $n;
                $db_contract->add();

                return 1;
            }else{
                return '创建失败';
            }
        }
    }

    /*
    ** 方法作用：用户富豪排行榜
    ** 参数1：[无]
    ** 返回值：[无]
    ** 备注：[无]
     */
    public function getRichList($limit,$time='d',$week='0') {
        $field = array(
            'userid' ,
        );
        switch($time) {
            case 'd' :
                $day = date('Y-m-d',time()-3600*24);  //前一天
                $result =  M('memstatistics_day')->where(array('day'=>$day))->field($field)->order('spendmoney DESC')->limit('0,'.$limit)->select();
                break;
            case 'w' :
                $result =  M('memstatistics_week')->where(array('week'=>$week,'year'=>getLastWeekYear()))->field($field)->order('spendmoney DESC')->limit('0,'.$limit)->select();
                break;

            case 'm' :
                $result =  M('memstatistics_month')->where(array('month'=>getLastMonth(),'year'=>getLastMonthYear()))->field($field)->order('spendmoney DESC')->limit('0,'.$limit)->select();
                break;
            case 'all' :
                $result =  D('balance')->where(1)->field($field)->order('spendmoney DESC')->limit('0,'.$limit)->select();
                break;
        }
        $db_member = M('member');
        $db_levelcon = D('Levelconfig');
        foreach ($result as $k=>$v) {
            $memberInfo = $db_member->where(array('userid'=>$v['userid']))->field('nickname,smallheadpic,bigheadpic,userlevel')->find();
            $userlevel = $db_levelcon->where("levelid=". $memberInfo[userlevel] . " and leveltype=1")->field("levelid,levelname,smalllevelpic")->find();
            $result[$k] = array_merge($result[$k],$memberInfo,$userlevel);
            $result[$k]['rankpic'] = "/Public/Public/Images/Ranklist/" . ($k+1) . ".png";
        }
        return $result;
    }

    /*
    ** 方法作用：用户富豪排行榜
    ** 参数1：[无]
    ** 返回值：[无]
    ** 备注：[无]
     */
    public function getRandomMembers($enterroomno, $limit=10) {
        $where = array(
            'thirdparty' => 0,
            'lastlogintime' => array('lt', date('Y-m-d H:i:s', strtotime("-10 day")))
        );
        $randbatch = rand(0, 10100); 
        
        $result = $this->where($where)->field($this->memberfields)->limit($randbatch .','. $limit)->select();
    	//var_dump($this->_sql());
    	$mergResult = array();
    	$totalNum = 0;
    	
    	foreach ($result as $k=>$v) {
    	    $result[$k]['enterroomno'] = $enterroomno;
    	    if(!empty($v['niceno'])){
    	        $result[$k]['showroomno'] = $v['niceno'];
    	    }else{
    	        $result[$k]['showroomno'] = $v['roomno'];
    	    }
    		$result[$k]['emceelevel'] = rand(1, 18);
    		$result[$k]['vipid'] = rand(0, 2);
    		$result[$k]['guardid'] = rand(0, 2);
    		$result[$k]['spendmoney'] = rand(0, 1000000);
    		$result[$k]['devicetype'] = rand(0, 2);
    		if(!$result[$k]['smallheadpic'] || $result[$k]['smallheadpic'] == "/Public/Public/Images/HeadImg/default.png"){
    		    $result[$k]['smallheadpic'] = "/Public/Public/Images/Virheadpic/120120/" . rand(1, 200)  . ".png";  //Public\Public\Images\Virheadpic\120120
    		}
    		
    		$mergResult[$totalNum] = $result[$k];
    		$randVist = rand(0, 3);
    		
    		for ($x=0; $x < $randVist; $x++) {
    		    $visitInfo['enterroomno'] = $enterroomno;
    		    $visitInfo['userid'] = -rand(1000, 1000000);
    		    $visitInfo['roomno'] = $visitInfo['userid'];
    		    //$visitInfo['niceno'] = $visitInfo['userid'];
    		    $visitInfo['nickname'] = "ws" . -rand(1000, 1000000);
    		    $visitInfo['userlevel'] = 0;
    		    $visitInfo['emceelevel'] = rand(1, 18);
    		    $visitInfo['smallheadpic'] = "/Public/Public/Images/Virheadpic/120120/" . rand(201, 1200)  . ".png";  //Public\Public\Images\Virheadpic\120120
    		    $visitInfo['usertype'] = 0;
    		    $visitInfo['showroomno'] = $visitInfo['userid'];
    		    $visitInfo['vipid'] = 0;
    		    $visitInfo['guardid'] = 0;
    		    $visitInfo['spendmoney'] = 0;
    		    $visitInfo['devicetype'] = rand(0, 2); 
    		    $mergResult[$totalNum + $x + 1] = $visitInfo;;
    		}
    		
    		$totalNum = $totalNum + $randVist;
    	}
    	//游客
    	return $mergResult;
    }
}

?>