<?php
namespace Api\Modapi;
use Think\Model;

class GuardModapi extends Model {

    public $fields = array(
        'userid', 'guardid', 'effectivetime' , 'expiretime', 'emceeuserid'
    );

    /**
     * 获取用户对某个主播的最高守护
     * 根据用户ID，主播ID，筛选所有未失效的守护购买记录，按照guardid倒序查询
     */
    public function getMyTopGuardid($userid,$emceeuserid){
        $guardCond = array(
            'userid' => $userid,
            'emceeuserid' => $emceeuserid,
            'expiretime' => array('gt', date('Y-m-d H:i:s')),
        );
        $guardid = M('Guard')->where($guardCond)->field($this->fields)->order('guardid DESC')->getField('guardid');
        if (!$guardid) {
            $guardid = 0;
        }
        return $guardid;
    }

    /**
     * 获取用户所有的守护信息
     * 根据用户ID，查询所有未失效的守护购买记录，按照失效时间正序查询，并进行分页
     */
    public function getAllGuardByUserid($userid, $pageno, $pagesize){
        //查询为失效的守护列表
        $nowTime = date("Y-m-d H:i:s");
        $guardCond = array(
            'userid' => $userid,
            'expiretime' => array('gt', $nowTime)
        );
        $guardEmcees = M('Guard')
            ->where($guardCond)
            ->field($this->fields)
            ->limit($pageno*$pagesize . ',' . $pagesize)
            ->order('expiretime ASC')->select();

        //过滤已经不存在的主播守护
        $dbMember = M('Member');
        $dbEmceeproperty = M('Emceeproperty');
        $delcount = 0;  //计数器
        foreach ($guardEmcees as $k => $v) {
            //验证主播Member信息是否存在
            $whereEmceeUserid = array(
                'userid' => $v['emceeuserid'],
                'status' => array('neq', 1)
            );
            $emceeMember = $dbMember->where($whereEmceeUserid)->field('userlevel, niceno, nickname, smallheadpic')->find();
            if (!$emceeMember) {
                array_splice($guardEmcees, $k-$delcount, 1);    //从列表中删除该条记录
                $delcount++;
                continue;
            }

            //验证主播信息是否存在
            $emceePropertyInfo = $dbEmceeproperty->where($whereEmceeUserid)->find();
            if (!$emceePropertyInfo) {
                array_splice($guardEmcees, $k-$delcount, 1);    //从列表中删除该条记录
                $delcount++;
                continue;
            }

            $new_k = $k-$delcount;
            $guardEmcees[$new_k]['alreadydays'] = round ((time() - strtotime($v['effectivetime']))/3600/24);
            $guardEmcees[$new_k] = array_merge($emceeMember, $emceePropertyInfo, $guardEmcees[$new_k]);
        }
        return $guardEmcees;
    }

    /**
     * 获取主播的守护
     * @param emceeuserid：主播userid
     */
    public function getGuardByEmceeid($emceeuserid){
        //获取主播未过期的守护列表
        $guardCond = array (
            'emceeuserid' => $emceeuserid,
            'expiretime' => array('gt', date('Y-m-d H:i:s')),
        );
        $order = 'guardid DESC, expiretime DESC';
        $guards = M('Guard')->where($guardCond)->field($this->fields)->order($order)->select();

        //过滤已经删除的用户
        $dbMember = M('Member');
        $dbViprecord = D('Viprecord','Modapi');
        $delcount = 0;  //计数器
        foreach ($guards as $k => $v) {
            //验证用户是否存在
            $whereUserid = array(
                'userid' => $v['userid'],
                'status' => array('neq', 1)
            );
            $userInfo = $dbMember->where($whereUserid)->field('userlevel, niceno, roomno, nickname, smallheadpic')->find();
            if (!$userInfo) {
                array_splice($guards, $k-$delcount, 1);
                $delcount++;
                continue;
            }

            //获取用户等级
            $userInfo['vipid'] = $dbViprecord->getMyTopVipid($v['userid']);
            $guards[$k-$delcount] = array_merge($guards[$k-$delcount], $userInfo);
        }
        return $guards;
    }
}