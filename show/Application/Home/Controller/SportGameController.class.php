<?php
/**
 * @author 狼一
 * @brief 运动会游戏
 */
namespace Home\Controller;

class SportGameController extends CommonController{
    private $redis;
    private $StakeTime;
    private $GameOverTime;
    private $MinShowBeanGrabBanker;
    private $MaxGamePlayer;

    public function _initialize(){
        parent::_initialize();
        $this->redis = new \Org\Util\ThinkRedis();
        $this->StakeTime = 40;  //押注时间
        $this->GameOverTime = 50;  //游戏结束时间
        $this->MinShowBeanGrabBanker = 10000;  //抢庄最少秀豆数
        $this->MaxGamePlayer = 100;  //每局游戏最多参与者
    }

    //APP加载运动会游戏
    public function AppSoprtGame(){
        $assign['userid'] = I('get.userid',0);
        $assign['roomno'] = I('get.roomno');
        $assign['language'] = I('get.language');
        $assign['devicetype'] = I('get.devicetype');
        if(I('get.token')){
            setcookie('UserLoginToken',I('get.token'));
        }
        $this->assign($assign);
        $this->display('./Application/Game/sports_game.html');
    }

    //打开游戏界面
    public function openGame(){
        $CloseSportGame = $this->CloseSportGame();
        if($CloseSportGame == 1){
            $result = array(
                'code' => 500    //游戏已关闭
            );
            $this->ajaxReturn($result);
        }
        //获取用户秀豆余额
        $userid = I('post.userid',0);
        $show_bean = 0;
        if($userid > 0){
            $show_bean = M('balance')->where(array('userid'=>$userid))->getField('show_bean');
        }

        //验证语言
        $lantype = I('post.lantype');
        if(!$lantype){
            $result = array(
                'code' => 400,  //参数有误
                'msg' => lan('PARAMETER_ERROR','Home'),
            );
            $this->ajaxReturn($result);
        }
        //获取游戏选项
        $game_option = $this->getGameOption($lantype);
        //获取游戏选项排列
        $game_option_list = $this->getGameOptionList($lantype);
        //获取当前游戏庄家信息
        $SportGameBanker = $this->getSportGameBanker();
        //游戏状态
        $game_status = 0;
        $now_countdown = $this->GameOverTime;
        if($SportGameBanker['game_status']){
            //验证是否需要强制关闭游戏
            $now_countdown = time() - strtotime($SportGameBanker['start_time']);
            if($now_countdown > $this->GameOverTime){
                $SportGameBanker = $this->gameOver($SportGameBanker);
            }
            $game_status = $SportGameBanker['game_status'];
        }
        //是否参与押注
        $is_stake = 0;
        if($game_status && $this->redis->hGet('SportGamePlayerStake',$userid)){
            $is_stake = 1;
        }
        //押注倒计时
        $countdown = 0;
        if($now_countdown < $this->StakeTime){
            $countdown = $this->StakeTime - $now_countdown;
        }
        //游戏结束倒计时
        $game_over_countdown = 0;
        if($now_countdown < $this->GameOverTime){
            $game_over_countdown = $this->GameOverTime - $now_countdown;
        }
        $result = array(
            'code' => 200,
            'data' => array(
                'game_info' => array(
                    'userid' => $userid,
                    'show_bean' => $show_bean,
                    'min_show_bean' => $this->MinShowBeanGrabBanker,
                    'game_status' => $game_status,
                    'is_stake' => $is_stake,
                    'countdown' => $countdown,
                    'game_over_countdown' => $game_over_countdown,
                ),
                'game_banker_info' => $SportGameBanker,
                'game_option' => $game_option,
                'game_option_list' => $game_option_list,
            )
        );
        $this->ajaxReturn($result);
    }

    //用户抢庄
    public function grabBanker(){
        $userid = $this->checkUserToken();
        //抢庄房间号
        $roomno = I('post.roomno');
        if(!$roomno){
            $result = array(
                'code' => 400   //参数有误
            );
            $this->ajaxReturn($result);
        }
        //获取当前游戏庄家信息
        $SportGameBanker = $this->getSportGameBanker();
        if($SportGameBanker['game_status']){
            $result = array(
                'code' => 102   //慢了一步，抢庄失败
            );
            $this->ajaxReturn($result);
        }

        //抢庄成功添加到redis
        $SportGameBanker = $this->addSportGameBanker($userid,$roomno);
        if(!$SportGameBanker){
            $result = array(
                'code' => 101   //秀豆不足
            );
            $this->ajaxReturn($result);
        }
        $SportGameBanker['countdown'] = $this->StakeTime;
        $result = array(
            'code' => 200,
            'data' => $SportGameBanker
        );
        $this->ajaxReturn($result);
    }

    //用户押注
    public function userStake(){
        $userid = $this->checkUserToken();
        $data = I('post.data');
        if(!$data){
            $result = array(
                'code' => 400   //参数有误
            );
            $this->ajaxReturn($result);
        }
        //获取当前游戏庄家信息
        $SportGameBanker = $this->getSportGameBanker();
        if(!$SportGameBanker){
            $result = array(
                'code' => -1    //系统繁忙
            );
            $this->ajaxReturn($result);
        }
        //验证是否押注阶段
        if($SportGameBanker['game_status'] != 1){
            $result = array(
                'code' => 111   //已经开始开奖，押注失败
            );
            $this->ajaxReturn($result);
        }
        //验证是否庄家自己押注
        if($SportGameBanker['bankerid'] == $userid){
            $result = array(
                'code' => 114   //庄家自己不能押注
            );
            $this->ajaxReturn($result);
        }
        //验证是否已经押注
        $userStakeRecord = $this->redis->hGet('SportGamePlayerStake',$userid);
        if($userStakeRecord){
            $result = array(
                'code' => 400   //已经押注过，参数错误
            );
            $this->ajaxReturn($result);
        }
        //验证本局游戏参与人数
        $SportGamePlayer = $this->getSportGamePlayer();
        if(count($SportGamePlayer) >= $this->MaxGamePlayer){
            $result = array(
                'code' => 113   //本局游戏参与人数已满，押注失败
            );
            $this->ajaxReturn($result);
        }
        //验证秀豆余额
        $show_bean = M('balance')->where(array('userid'=>$userid))->getField('show_bean');
        $stake_show_bean = 0;
        $stakeRecord = array();
        $GameOptionOdds = $this->getGameOptionOdds();
        foreach($data as $key => $val){
            if($val['show_bean'] > 0){
                $stake_show_bean += $val['show_bean'];
                $stakeRecord[] = array(
                    'optionid' => $val['optionid'],
                    'show_bean' => $val['show_bean'],
                    'odds' => $GameOptionOdds[$val['optionid']]
                );
            }
        }
        if($show_bean<=0 || $stake_show_bean<=0 || $show_bean<$stake_show_bean){
            $result = array(
                'code' => 112   //押注数据异常，押注失败
            );
            $this->ajaxReturn($result);
        }
        //押注成功，添加redis记录
        $this->redis->hSet('SportGamePlayerStake',$userid,json_encode($stakeRecord));
        $result = array(
            'code' => 200
        );
        $this->ajaxReturn($result);
    }

    //等待开奖倒计时
    public function waitingSettlement(){
        $userid = $this->checkUserToken();
        //获取当前游戏庄家信息
        $SportGameBanker = $this->getSportGameBanker();
        $game_status = $SportGameBanker['game_status'];
        switch($game_status){
            case  '1':  //押注阶段
                //获取游戏中奖选项信息
                $SportGameSettlementOption = $this->getSportGameSettlementOption();
                //根据中奖奖项，统计数据保存数据库中
                $res = $this->SportGameSettlement();
                if($res === false){
                    $result = array(
                        'code' => -1    //系统繁忙
                    );
                    $this->ajaxReturn($result);
                }
                //返回中奖奖项
                $result = array(
                    'code' => 200,
                    'data' => $SportGameSettlementOption
                );
                $this->ajaxReturn($result);
                break;
            default :
                if(!($SportGameBanker['optionid'] && $SportGameBanker['number'])){
                    $result = array(
                        'code' => -1    //系统繁忙
                    );
                    $this->ajaxReturn($result);
                }
                $SportGameSettlementOption = array(
                    'optionid' => $SportGameBanker['optionid'],
                    'number' => $SportGameBanker['number']
                );
                $result = array(
                    'code' => 200,
                    'data' => $SportGameSettlementOption
                );
                $this->ajaxReturn($result);
        }
    }

    //结束游戏，更改游戏状态
    public function gameOverExchangeStatus(){
        $SportGameBanker = $this->getSportGameBanker();
        $SportGameBanker = $this->gameOver($SportGameBanker);
        $code = 200;
        if($SportGameBanker['game_status']){
            $code = -1;
        }
        $this->ajaxReturn(array('code'=>$code));
    }

    //游戏结算
    public function gameSettlement(){
        $userid = $this->checkUserToken();
        $settlement_bean  = $this->getUserSettlementBean($userid);
        //验证游戏是否已强制关闭
        $CloseSportGame = $this->CloseSportGame();
        $user_settlement_info = array(
            'userid' => $userid,
            'settlement_bean' => $settlement_bean,
            'CloseSportGame' => $CloseSportGame,
        );
        $result = array(
            'code' => 200,
            'data' => $user_settlement_info
        );
        $this->ajaxReturn($result);
    }

    //添加庄家信息到redis
    private function addSportGameBanker($userid,$roomno){
        $SportGameBanker = array();
        //验证用户抢庄条件
        $where = array('userid'=>$userid);
        $show_bean = M('balance')->where($where)->getField('show_bean');
        if($show_bean >= $this->MinShowBeanGrabBanker){
            $userInfo = M('member')->where($where)->find();
            $SportGameBanker = array(
                'bankerid'  =>  $userid,
                'bankerName'  =>  $userInfo['nickname'],
                'bankerHeadpic'  => $userInfo['smallheadpic'],
                'show_bean'  =>  $show_bean,
                'start_time'  =>  date('Y-m-d H:i:s'),
                'roomno'    => $roomno,
                'game_status'  =>  1
            );
            $Key = 'SportGameBanker';
            $this->redis->set($Key,json_encode($SportGameBanker));
        }
        return $SportGameBanker;
    }

    //获取当前庄家信息
    private function getSportGameBanker(){
        $SportGameBanker = array();
        $Key = 'SportGameBanker';
        $SportGameBanker_value = $this->redis->get($Key);
        if($SportGameBanker_value){
            $SportGameBanker = json_decode($SportGameBanker_value,true);
        }
        return $SportGameBanker;
    }

    //获取运动会游戏参与人员
    private function getSportGamePlayer(){
        $Key = 'SportGamePlayerStake';
        $SportGamePlayer = $this->redis->hKeys($Key);
        return $SportGamePlayer;
    }

    //获取运动会游戏押注记录
    private function getUserStakeRecord(){
        $UserStakeRecord = array();
        $Key = 'SportGamePlayerStake';
        $UserStakeRecord_value = $this->redis->hGetAll($Key);
        if($UserStakeRecord_value){
            foreach($UserStakeRecord_value as $k => $v){
                $UserStakeRecord[$k] = json_decode($v,true);
            }
        }
        return $UserStakeRecord;
    }

    //获取开奖选项信息
    private function getSportGameSettlementOption(){
        $SportGameSettlementOption = array();
        $Key = 'SportGameBanker';
        $SportGameBanker_value = $this->redis->get($Key);
        if($SportGameBanker_value){
            $SportGameBanker = json_decode($SportGameBanker_value,true);
            if(!($SportGameBanker['optionid'] && $SportGameBanker['number'] && $SportGameBanker['game_status'] == 2)){
                //生成游戏中奖奖项
                $SportGameSettlementOption = $this->createSportGameSettlementOption();
                if($SportGameSettlementOption){
                    $SportGameBanker = array_merge($SportGameBanker,$SportGameSettlementOption);
                    //更改游戏状态添加缓存
                    $SportGameBanker['game_status'] = 2;
                    $this->redis->set($Key,json_encode($SportGameBanker));
                }
            }
            $SportGameSettlementOption = array(
                'optionid' => $SportGameBanker['optionid'],
                'number' => $SportGameBanker['number']
            );
        }
        return $SportGameSettlementOption;
    }

    //生成游戏中奖奖项
    private function createSportGameSettlementOption(){
        $SportGameSettlementOption = array();
        $optionid = 0;
        //获取游戏选项生成选项概率
        $GameOption = $this->getGameOption($this->lan,0);
        $proArr = array();
        foreach($GameOption as $k => $v){
            $proArr[$v['optionid']] = (int)((1/$v['odds'])*10000);   //概率基数扩大
        }
        //概率数组的总概率精度（数组中所有值的和）
        $proSum = array_sum($proArr);
        //概率数组循环
        foreach($proArr as $key => $proCur){
            $randNum = mt_rand(1,$proSum);
            if($randNum <= $proCur){
                $optionid = $key;
                break;
            }else{
                $proSum -= $proCur;
            }
        }
        //根据游戏选项ID，随机获取选项停留位置
        if($optionid){
            $where = array(
                'type' => 0,
                'optionid' => $optionid,
            );
            $OptionList = M('gameoptionlist')->where($where)->select();
            //列表数组乱序
            shuffle($OptionList);
            $SportGameSettlementOption = array(
                'optionid' => $optionid,
                'number' => $OptionList[0]['number']
            );
        }
        return $SportGameSettlementOption;
    }

    //运动会游戏结算
    private function SportGameSettlement(){
        $result = false;
        //获取当前庄家信息
        $SportGameBanker = $this->getSportGameBanker();
        if($SportGameBanker['game_status'] == 2 && $SportGameBanker['bankerid'] && $SportGameBanker['optionid'] && $SportGameBanker['show_bean']){
            $this->redis->expire('SportGamePlayerSettlement',0);//清除之前的结算记录
            $dbGamesport = M('gamesport');
            $bankerid = $SportGameBanker['bankerid'];
            $banker_show_bean = $SportGameBanker['show_bean'];
            $optionid = $SportGameBanker['optionid'];
            $data_gamesport = array(
                'bankerid' => $bankerid,
                'showbean' => $banker_show_bean,
                'roomno' => $SportGameBanker['roomno'],
                'addtime' => date('Y-m-d H:i:s'),
                'status' => 2,
            );
            //添加、更新运动会游戏
            if($SportGameBanker['gameid']){
                $gameid = $SportGameBanker['gameid'];
                $dbGamesport->where(array('gameid'=>$gameid,'bankerid'=>$bankerid))->save($data_gamesport);
            }else{
                $gameid = $dbGamesport->add($data_gamesport);
            }
            $Key = 'SportGameBanker';
            $SportGameBanker['gameid'] = $gameid;
            $this->redis->set($Key,json_encode($SportGameBanker));
            //获取用户押注记录
            $where_gamesport = array(
                'gameid'=>$gameid,
                'bankerid'=>$bankerid
            );
            $UserStakeRecord = $this->getUserStakeRecord();
            //没人押注直接更新
            if(empty($UserStakeRecord)){
                $update_gamesport = array(
                    'optionid' => $optionid,
                    'totalstakebean' => 0,
                    'totalearnbean' => 0,
                    'settlementbean' => 0,
                    'updatetime' => date('Y-m-d H:i:s'),
                    'status' => 2,
                );
                $result = $dbGamesport->where($where_gamesport)->save($update_gamesport);
                if($result !== false){
                    $this->addUserSettlementBean($bankerid,0);
                }
                return $result;
            }
            //添加用户押注记录到数据库
            $total_earn_bean = 0;
            $total_stake_bean = 0;
            $user_settlement = array();
            $time = date('Y-m-d H:i:s');
            $dbGamerecord = M('gamerecord');
            foreach($UserStakeRecord as $key => $val){
                $user_settlement[$key]['earn_bean'] = 0;
                $user_settlement[$key]['stake_bean'] = 0;
                $UserStakeRecord[$key] = json_decode($val,true);
                foreach($val as $k => $v){
                    $settlement_show_bean = $v['show_bean']*$v['odds'];
                    if($v['optionid'] == $optionid){
                        $total_earn_bean += $settlement_show_bean;
                        $user_settlement[$key]['earn_bean'] += $settlement_show_bean;
                    }
                    $total_stake_bean += $v['show_bean'];
                    $user_settlement[$key]['stake_bean'] += $v['show_bean'];
                    $StakeRecord = array(
                        'gameid' => $gameid,
                        'userid' => $key,
                        'optionid' => $v['optionid'],
                        'odds' => $v['odds'],
                        'showbean' => $v['show_bean'],
                        'addtime' => $time
                    );
                    $dbGamerecord->add($StakeRecord);
                }
            }

            //判断总秀豆是否足够分摊
            $ratio = 1;
            $total_show_bean = $banker_show_bean + $total_stake_bean;
            if($total_show_bean < $total_earn_bean){
                $ratio = ((int)(($total_show_bean/$total_earn_bean)*100))/100;
            }
            //统计每个用户的结算金额，并更新用户余额
            $dbGameplayer = M('gameplayer');
            $dbBalance = M('balance');
            $banker_selltement_show_bean = 0;
            foreach($user_settlement as $key => $val){
                $user_total_win = (int)($val['earn_bean']*$ratio);
                $user_selletment_show_bean = $user_total_win - $val['stake_bean'];
                $this->addUserSettlementBean($key,$user_selletment_show_bean);
                $banker_selltement_show_bean -= $user_selletment_show_bean;
                $GamePlayer = array(
                    'gameid' => $gameid,
                    'userid' => $key,
                    'totalstakebean' => $val['stake_bean'],
                    'totalearnbean' => $val['earn_bean'],
                    'settlementbean' => $user_selletment_show_bean,
                    'ratio' => $ratio,
                    'addtime' => $time,
                    'updatetime' => $time,
                );
                $dbGameplayer->add($GamePlayer);
                $dbBalance->where(array('userid'=>$key))->save(array('show_bean'=>array('exp','show_bean+'.$user_selletment_show_bean)));
            }
            //更新庄家信息
            $Gamesport = array(
                'optionid' => $optionid,
                'totalstakebean' => $total_stake_bean,
                'totalearnbean' => $total_earn_bean,
                'ratio' => $ratio,
                'settlementbean' => $banker_selltement_show_bean,
                'updatetime' => date('Y-m-d H:i:s'),
                'status' => 2,
            );
            $dbGamesport->where(array('gameid'=>$gameid))->save($Gamesport);
            //更新庄家秀豆余额
            $result = $dbBalance->where(array('userid'=>$bankerid))->save(array('show_bean'=>array('exp','show_bean+'.$banker_selltement_show_bean)));
            $this->addUserSettlementBean($bankerid,$banker_selltement_show_bean);
        }
        return $result;
    }

    //添加用户结算结果
    private function addUserSettlementBean($userid,$selltement_show_bean){
        $this->redis->hSet('SportGamePlayerSettlement',$userid,$selltement_show_bean);
    }

    //获取用户结算结果
    private function getUserSettlementBean($userid){
        $Key = 'SportGamePlayerSettlement';
        $UserSettlementBean = (int)$this->redis->hGet($Key,$userid);
        return $UserSettlementBean;
    }

    //结束游戏
    private function gameOver($SportGameBanker){
        $game_status = $SportGameBanker['game_status'];
        $gameid = $SportGameBanker['gameid'];
        $bankerid = $SportGameBanker['bankerid'];
        $optionid = $SportGameBanker['optionid'];
        $now_countdown = time() - strtotime($SportGameBanker['start_time']);
        //更新游戏记录
        if($now_countdown >= $this->StakeTime && $game_status == 2 && $gameid && $bankerid && $optionid){
            $where = array(
                'gameid' => $gameid,
                'bankerid' => $bankerid,
            );
            $data = array(
                'updatetime' => date('Y-m-d H:i:s'),
                'status' => 3,
            );
            M('gamesport')->where($where)->save($data);
        }
        //更新redis
        if(($game_status == 1 && $now_countdown > $this->GameOverTime) || ($game_status == 2 && $now_countdown > $this->StakeTime)){
            $Key = 'SportGameBanker';
            $SportGameBanker['game_status'] = 0;
            $this->redis->set($Key,json_encode($SportGameBanker));
            $this->redis->expire('SportGamePlayerStake',0);
        }
        return $SportGameBanker;
    }

    //获取游戏选项
    private function getGameOption($lantype,$type=0){
        $Key = 'SportGameOption';
        $GameOption_value = $this->redis->hGet($Key,$lantype);
        if(!$GameOption_value){
            $where = array(
                'type' => $type,
                'lantype' => $lantype
            );
            $GameOption = M('gameoption')->where($where)->select();
            if($GameOption){
                $this->redis->hSet($Key,$lantype,json_encode($GameOption));
            }
        }else{
            $GameOption = json_decode($GameOption_value,true);
        }
        return $GameOption;
    }

    //获取游戏选项赔率、
    private function getGameOptionOdds(){
        $Key = 'SportGameOptionOdds';
        $SportGameOptionOdds = $this->redis->hGetAll($Key);
        if(!$SportGameOptionOdds){
            $GameOption = $this->getGameOption($this->lan,0);
            foreach($GameOption as $k => $v){
                $SportGameOptionOdds[$v['optionid']] = $v['odds'];
                $this->redis->hSet($Key,$v['optionid'],$v['odds']);
            }
        }
        return $SportGameOptionOdds;
    }

    //获取游戏选项排列
    private function getGameOptionList($lantype,$type=0){
        $Key = 'SportGameOptionList';
        $GameOptionList_value = $this->redis->hGet($Key,$lantype);
        if(!$GameOptionList_value){
            $where = array(
                'gol.type' => $type
            );
            $GameOptionList = M('gameoptionlist gol')
                ->join('ws_gameoption go ON go.optionid=gol.optionid AND go.type="'.$type.'" AND go.lantype="'.$lantype.'"')
                ->where($where)->order('gol.number asc')->select();
            if($GameOptionList){
                $this->redis->hSet($Key,$lantype,json_encode($GameOptionList));
            }
        }else{
            $GameOptionList = json_decode($GameOptionList_value,true);
        }
        return $GameOptionList;
    }

    //验证用户Token
    private function checkUserToken(){
        //验证用户
        $userid = I('post.userid',0);
        if($userid <= 0){
            $result = array(
                'code' => 100,  //用户未登录
                'msg' => lan('YOU_NOT_LOGIN_RETRY','Home'),
            );
            $this->ajaxReturn($result);
        }
        //验证参数
        $token = I('post.token','');
        if(!$token){
            $result = array(
                'code' => 400,  //参数有误
                'msg' => lan('PARAMETER_ERROR','Home'),
            );
            $this->ajaxReturn($result);
        }
        //验证登录token
        $UserLoginToken = M('member')->where(array('userid' => $userid))->getField('token');
        if($token != $UserLoginToken){
            $result = array(
                'code' => 100,  //用户未登录
                'msg' => lan('YOU_NOT_LOGIN_RETRY','Home'),
            );
            $this->ajaxReturn($result);
        }
        return $userid;
    }

    //验证是否强制关闭运动会游戏
    private function CloseSportGame(){
        $where = array(
            'key' => 'CLOSE_SOPRT_GAME',
            'lantype' => $this->lan,
        );
        $CloseSportGame = M('systemset')->where($where)->getField('value');
        return $CloseSportGame;
    }

    //验证用户是否正在参与运动会游戏
    public function checkUserInSportGame($userid){
        $result = false;
        $SportGameBanker = $this->getSportGameBanker();
        $game_status = $SportGameBanker['game_status'];
        $bankerid = $SportGameBanker['bankerid'];
        $is_stake = $this->redis->hGet('SportGamePlayerStake',$userid);
        if($game_status && ($bankerid == $userid || $is_stake)){
            $result = true;
        }
        return $result;
    }
}