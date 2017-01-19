<?php
/**
 * @author 狼一
 * @brief 秀豆秀币兑换相关
 */
namespace Home\Controller;

class CurrencyController extends CommonController{
    private $exchangeType;
    private $lantype;
    public function _initialize(){
        parent::_initialize();
        $this->exchangeType = array(1,2);    //兑换类型：1.秀币换秀豆、2.秀豆换秀币
        $this->lantype = I('post.lantype') ? I('post.lantype') : getLanguage();
    }

    //获取用户余额信息
    public function getUserInfo(){
        $userInfo = $this->checkUserToken();
        $result = array(
            'code' => 200,
            'data' => array(
                'userid' => $userInfo['userid'],
                'show_money' => $userInfo['balance'],
                'show_bean' => $userInfo['show_bean'],
            )
        );
        $this->ajaxReturn($result);
    }

    //获取秀豆秀币兑换规则
    public function getCurrencyExchangeRule(){
        //验证参数
        $type = I('get.type');
        if(!in_array($type,$this->exchangeType)){
            $result = array(
                'code' => 400,
                'msg' => lan('PARAMETER_ERROR','Home',$this->lantype),
            );
            $this->ajaxReturn($result);
        }
        //获取规则列表
        $dbExchangerule = M('exchangerule');
        $time = date('Y-m-d H:i:s');
        $where['starttime'] = array('lt',$time);
        $where['endtime'] = array('egt',$time);
        $where['type'] = array('eq',$type);
        $field = 'type,showmoney as show_money,showbean as show_bean';
        $list = $dbExchangerule->field($field)->where($where)->order('sort desc')->select();
        $result = array(
            'code' => 200,
            'data' => $list
            );
        $this->ajaxReturn($result);
    }

    //秀豆秀币兑换
    public function currencyExchange(){
        $userInfo = $this->checkUserToken();
        //运动会游戏阶段，参与游戏的用户不能进行货币兑换
        $SportGame = new SportGameController();
        if($SportGame->checkUserInSportGame($userInfo['userid'])){
            $result = array(
                'code' => -1,
                'msg' => lan('-1','Home',$this->lantype),
            );
            $this->ajaxReturn($result);
        }
        //验证参数
        $type = I('post.type');
        $value = (int)I('post.value',0);
        $devicetype = I('post.devicetype');
        if(!in_array($type,$this->exchangeType)){
            $result = array(
                'code' => 400,
                'msg' => lan('PARAMETER_ERROR','Home',$this->lantype),
            );
            $this->ajaxReturn($result);
        }
        //验证余额
        if($value <= 0 || ($type==1 && $userInfo['balance'] < $value) || ($type==2 && $userInfo['show_bean'] < $value)){
            $result = array(
                'code' => 400,
                'msg' => lan('BALANCE_NOT_ENOUGH','Home',$this->lantype),
            );
            $this->ajaxReturn($result);
        }
        //获取兑换金额能够满足的规则
        $ruleInfo = $this->getExchangeRuleByValue($type,$value);
        if(empty($ruleInfo)){
            $result = array(
                'code' => 400,
                'msg' => lan('PARAMETER_ERROR','Home',$this->lantype),
            );
            $this->ajaxReturn($result);
        }
        //秀豆秀币兑换
        switch($type){
            case '1' :  //秀币兑换秀豆
                $show_money = $value;
                $show_bean = ($show_money*$ruleInfo['showbean'])/$ruleInfo['showmoney'];
                $data['balance'] = $userInfo['balance'] - $show_money;
                $data['show_bean'] = $userInfo['show_bean'] + $show_bean;
                $res = M('balance')->where(array('userid'=>$userInfo['userid']))->save($data);
                break;
            case '2' :  //秀豆兑换秀币
                $show_bean = $value;
                $show_money = ($show_bean*$ruleInfo['showmoney'])/$ruleInfo['showbean'];
                $data['show_bean'] = $userInfo['show_bean'] - $show_bean;
                $data['balance'] = $userInfo['balance'] + $show_money;
                $res = M('balance')->where(array('userid'=>$userInfo['userid']))->save($data);
                break;
            default :
                $show_money = 0;
                $show_bean = 0;
                $data['show_bean'] = 0;
                $data['balance'] = 0;
                $res = false;
        }
        //判断是否保存成功
        if($res === false){
            $result = array(
                'code' => -1,
                'msg' => lan('-1','Home',$this->lantype),
            );
            $this->ajaxReturn($result);
        }
        //兑换成功，添加兑换记录
        $data_exchangerecord = array(
            'userid' => $userInfo['userid'],
            'type' => $type,
            'showmoney' => $show_money,
            'showbean' => $show_bean,
            'status' => 1,
            'addtime' => date('Y-m-d H:i:s'),
            'devicetype' => $devicetype
        );
        M('exchangerecord')->add($data_exchangerecord);
        //返回结果
        $result = array(
            'code' => 200,
            'data' => array(
                'userid' => $userInfo['userid'],
                'type' => $type,
                'show_money' => $data['balance'],
                'show_bean' => $data['show_bean'],
            )
        );
        $this->ajaxReturn($result);
    }

    //根据兑换金额获取兑换规则
    private function getExchangeRuleByValue($type,$value){
        $dbExchangerule = M('exchangerule');
        $time = date('Y-m-d H:i:s');
        $where['starttime'] = array('lt',$time);
        $where['endtime'] = array('egt',$time);
        $where['type'] = array('eq',$type);
        $where['minvalue'] = array('elt',$value);
        $ruleInfo = $dbExchangerule->where($where)->order('sort desc')->find();
        return $ruleInfo;
    }

    //验证用户Token
    private function checkUserToken(){
        //验证用户
        $userid = I('post.userid',0);
        if($userid <= 0){
            $result = array(
                'code' => 100,
                'msg' => lan('YOU_NOT_LOGIN_RETRY','Home',$this->lantype),
            );
            $this->ajaxReturn($result);
        }
        //验证参数
        $token = I('post.token','');
        if(!$token){
            $result = array(
                'code' => 400,  //参数有误
                'msg' => lan('PARAMETER_ERROR','Home',$this->lantype),
            );
            $this->ajaxReturn($result);
        }
        //验证登录token
        $userInfo = M('member m')->join('ws_balance b ON b.userid = m.userid')->where( array('m.userid' => $userid))->find();
        if($token != $userInfo['token']){
            $result = array(
                'code' => 100,  //用户未登录
                'msg' => lan('YOU_NOT_LOGIN_RETRY','Home',$this->lantype),
            );
            $this->ajaxReturn($result);
        }
        return $userInfo;
    }
}