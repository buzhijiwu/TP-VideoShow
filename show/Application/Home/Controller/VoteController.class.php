<?php
namespace Home\Controller;
use Think\Model;
class VoteController extends CommonController {
	//歌唱比赛活动投票
	public function SingActivityVote() {
		$limit_count =10;
		$data = I('post.');
		$data['votetype'] = 0;
        $starttime = '2016-05-18 09:00:00';//2016-05-18 09:00:00
        $endtime = '2018-05-20 23:00:00';  		
		$this->dovote($data,$limit_count,$starttime,$endtime);
	}

	public function dovote($data,$limit_count,$starttime,$endtime) {
		$nowtime = date("Y-m-d H:i:s" ,time());
        if ($starttime > $nowtime || $endtime < $nowtime) {
			$res = array(
				'status' => 0,
				'message' => lan('NOT_ACTIVE_TIME', 'Home'),
			);
			echo json_encode($res);
			die;        	
        }

		$userid = $data['userid'];
		$emceeuserid = $data['emceeuserid'];	
		if ($userid == $emceeuserid) {
			$res = array(
				'status' => 0,
				'message' => lan('NOT_VOTE_SELF', 'Home'),
			);
			echo json_encode($res);
			die;			
		}	

		$dVote_user = M('Voterecord_user');
		$dVote_emc = M('Voterecord_emc');
		$tran = new Model();
        $tran->startTrans();
		$votetype = $data['votetype'];
		$day = date("Y-m-d" ,time());
		$data_user = array(
				'userid' => $userid,
				'votetype' => $votetype,
				'day' => $day,
				'lastvotetime' => $nowtime,
		);
		$data_emc = array(
				'userid' => $emceeuserid,
				'votetype' => $votetype,
				'lastvotetime' => $nowtime,
		);

		$where = array(
				'userid' => $userid,
				'day' => $day,
				'votetype' => $votetype,
		);
		$userinfo = $dVote_user->where($where)->find();
		if($userinfo) {
            if($userinfo['votecount'] >= 10) {
				$res = array(
					'status' => 0,
					'message' => lan('ONLY_VOTE_TEN', 'Home'),
				);
				echo json_encode($res);
				die;
			}else{
				$data_user['votecount'] = $userinfo['votecount']+1;
				$uservote_res = $tran->table('ws_voterecord_user')->where($where)->save($data_user);
			}
		}else{
			$data_user['votecount'] = 1;
			$uservote_res = $tran->table('ws_voterecord_user')->add($data_user);
		}

		$where_emc = array(
				'userid' => $emceeuserid,
				'votetype' => $votetype,
		);		
        $emcinfo = $dVote_emc->where($where_emc)->find();
        if ($emcinfo) {
			$data_emc['votecount'] = $emcinfo['votecount']+1;
			$emcvote_res = $tran->table('ws_voterecord_emc')->where($where_emc)->save($data_emc);        	
        }
        else{
			$data_emc['votecount'] = 1;
			$emcvote_res = $tran->table('ws_voterecord_emc')->add($data_emc);        	
        }

        if($uservote_res && $emcvote_res){
            $tran->commit();
			$res = array(
				'status' => 1,
				'message' => lan('VOTE_SUCCESS', 'Home'),
			);
			echo json_encode($res);
			die;            
        }else{
            $tran->rollback();
        }        
	}
}