<?php
namespace Home\Model;
use Think\Model\ViewModel;

/*
** 备注：主播+房间  视图模型
 */
class EmceepropertyRoomViewModel extends ViewModel {
	protected $viewFields = array (
		'emceeproperty' => array (
			'_table' => 'ws_emceeproperty',
			'isliving',
			'emceeid',
			'emceelevel',
			'emceepic',
			'categoryid' => 'cateid',
		),
		'member' => array(
			'_table' => 'ws_member',
			'username',
			'_on' => 'emceeproperty.userid = member.userid',
			'_type' => 'inner',
		),
		'room' => array(
			'_table' => 'ws_room',
			'roomno',
			'niceno',
			'_on' => 'emceeproperty.roomid = room.roomid',
			'_type' => 'inner',
		),
	);
	
	public function getPcIndexEmc($cateId) {
		$where = array(
			'emceeproperty.categoryid' => $cateId,
			'emceeproperty.isliving' => 1,   //正在直播
			'emceeproperty.emceepic' => array('NEQ',''),  //头像不为空
		);
		$result = $this->where($where)->order('emceeproperty.livetime DESC')->limit('0,10')->select();
		
		if(count($result) < 10){
		    $limit = 10-count($result);
		    $where = array (
		        'emceeproperty.categoryid' => $cateId,
		        'emceeproperty.isliving' => 0,
		        'emceeproperty.emceepic' => array('NEQ',''),
		    );
		    $result = array_merge($result,$this->where($where)->order('emceeproperty.emceelevel DESC')->limit('0,'.$limit)->select());
		}
		
		
		return $result;
	}
	
	/*
	** 方法作用：获得某个类别下面主播列表
	** 参数1：[无]
	** 返回值：[无]
	** 备注：此处算法稍有复杂  
	**	思路:先取出在线的、头像不为空的主播  按照在线时间从远到近排序
	**      再取出不在线、头像为空的主播   按照在线时间从远到近排序
	 */
	public function getOneCateEmec ($cateid,$pageno=1) {
		$where = array (
			'categoryid'=>$cateid,
			'isliving'=>1,
			'emceepic'=>array('NEQ',''),
		);
		if($pageno==1) {
			$result = $this->where($where)->order('emceeproperty.livetime ASC')->limit('0,'.C('SHOW_LIST_COUNT'))->select();
			if(count($result)<C('SHOW_LIST_COUNT')) {
				$where = array(
					'categoryid'=>$cateid,
					'isliving'=>0,
					'emceepic'=>array('NEQ',''),
				);
				$result = array_merge($result,$this->where($where)->order('emceeproperty.livetime ASC')->limit('0,'.(C('SHOW_LIST_COUNT')-count($result)))->select());
			}
		} else {
			$count_isliving = $this->where($where)->count();  //在线主播的总数
			$totalPageno = ceil($count_isliving/C('SHOW_LIST_COUNT')); //在线主播的总页数
			$lastLivingCount = $count_isliving%C('SHOW_LIST_COUNT');
			if(!$lastLivingCount) $lastLivingCount = C('SHOW_LIST_COUNT');
			//最后一页在线主播的个数
			
			$overplusPageno = $totalPageno - $pageno;

			if($overplusPageno>0) {  //在线主播还没取完
				$result = $this->where($where)->order('emceeproperty.livetime ASC')->limit((($pageno-1)*C('SHOW_LIST_COUNT')).','.C('SHOW_LIST_COUNT'))->select();
			} else if($overplusPageno==0) {
				$result = $this->where($where)->order('emceeproperty.livetime ASC')->limit((($pageno-1)*C('SHOW_LIST_COUNT')).',20')->select();
				if(count($result)>0 && count($result)<C('SHOW_LIST_COUNT')) {
					$limit = C('SHOW_LIST_COUNT')-count($result);
					$where = array(
						'categoryid'=>$cateid,
						'isliving'=>0,
						'emceepic'=>array('NEQ',''),
					);
					$result = array_merge($result,$this->where($where)->order('emceeproperty.livetime ASC')->limit('0,'.$limit)->select());
				}
			} else {
				$limit_start = C('SHOW_LIST_COUNT')-$lastLivingCount;
				$limit_start += (abs($overplusPageno)-1)*C('SHOW_LIST_COUNT');
				//此处需细心
				$where = array(
					'categoryid'=>$cateid,
					'isliving'=>0,
					'emceepic'=>array('NEQ',''),
				);
				$result = $this->where($where)->order('emceeproperty.livetime ASC')->limit($limit_start.','.C('SHOW_LIST_COUNT'))->select();
			}
		}
		$data['totalpageno'] = $this->where(array('categoryid'=>$cateid))->count();
		$data['totalpageno'] = ceil($data['totalpageno']/C('SHOW_LIST_COUNT'));
		$data['dataList'] = $result;
		return $data;
	}
}