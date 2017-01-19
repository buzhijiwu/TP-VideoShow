<?php
namespace Agent\Model;
use Think\Model;

class AgentModel extends Model
{
//    protected $_validate = array(
//        array('password','require', ''),
//        array('password','6,20', ''),
//    );
    /**
     * 数据校验项
     * @return array
     */
    public function addValidate()
    {
        return array (
            array('agentname','require',lan("MUST_INPUT_USERNAME", "Agent")),
            array('password','require',lan("MUST_INPUT_PASSWORD", "Agent")),
            array('password','6,20',lan("PWD_ISSIX_TO_TWENTY", "Agent")),
        );
    }
}