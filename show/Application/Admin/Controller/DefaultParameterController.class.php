<?php
/**
 * 默认参数配置
 */
namespace Admin\Controller;
use Think\Controller;
class DefaultParameterController extends CommonController{

    public function _initialize() {
        parent::_initialize();
        if (session('roleid') != 0) {
            $this->error(lan('NO_AUTHORITY', 'Admin'));exit;
        }
    }

    public function index(){
        $default_parameter = M('default_parameter')->where(array('id'=>1))->find();
        $this->assign('default_parameter',$default_parameter);
        $this->display();
    }

    public function save(){
        $default_parameter = M('default_parameter');
        $data = array(
            'signflag_emcee_ratio'  => I('post.signflag_emcee_ratio'),
            'emcee_base_ratio'  => I('post.emcee_base_ratio'),
            'vnd_ratio'  => I('post.vnd_ratio'),
            'settlement_trade_type'  => I('post.settlement_trade_type'),
            'family_first_month_ratio'  => I('post.family_first_month_ratio'),
            'operator_ratio'  => I('post.operator_ratio'),
        );
        $result = $default_parameter->where(array('id'=>1))->save($data);
        if($result === false) {
            $this->error();
        }else{
            $this->success();
        }
    }
}