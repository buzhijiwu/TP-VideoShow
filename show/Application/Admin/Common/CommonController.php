<?php
/*
** 函数作用：根据交易类型获取交易名称
** 参数：$tradetype:交易类型的值
 */
function getTradeTypeName($tradetype){
    switch($tradetype){
        case 0:
            $tradetype_name = lan('EARN_GIFT', 'Admin');
            break;
        case 1:
            $tradetype_name = lan('GIVE_GIFT', 'Admin');
            break;
        case 2:
            $tradetype_name = lan('BUY_GOODS', 'Admin');
            break;
        case 3:
            $tradetype_name = lan('SETTLEMENT', 'Admin');
            break;
        case 4:
            $tradetype_name = lan('BUY_SOFA', 'Admin');
            break;
        case 5:
            $tradetype_name = lan('PAY_ROOM', 'Admin');
            break;
        case 6:
            $tradetype_name = lan('BUY_NICE_NO', 'Admin');
            break;
        case 7:
            $tradetype_name = lan('BUY_VIP', 'Admin');
            break;
        case 8:
            $tradetype_name = lan('SEND_FLY', 'Admin');
            break;
        case 9:
            $tradetype_name = lan('BUY_GUARD', 'Admin');
            break;
        default:
            $tradetype_name = lan('EARN_GIFT', 'Admin');
    }
    return $tradetype_name;
}

/*
** 函数作用：根据充值类型获取充值类型名称
** 参数：$type:交易类型的值
 */
function getRechargeTypeName($type){
    switch($type){
        case 0:
            $name = lan('RECHARGE_YOURSELF', 'Admin');
            break;
        case 1:
            $name = lan('AGENT_RECHARGE_USER', 'Admin');
            break;
        case 2:
            $name = lan('USER_RECHARGE_OTHERS', 'Admin');
            break;
        case 3:
            $name = lan('ADMIN_RECHARGE_AGENT', 'Admin');
            break;
        case 4:
            $name = lan('ADMIN_RECHARGE_USER', 'Admin');
            break;
        default:
            $name = lan('RECHARGE_YOURSELF', 'Admin');
    }
    return $name;
}

/*
** 函数作用：根据排行榜类型ID获取排行榜类型名称
** 参数：$type:排行榜类型ID
 */
function getRankingTypeName($type,$lantype=''){
    if (!empty($lantype)) {
        $lan = $lantype;
    }else{
        $lan = getLanguage();
    }
    switch($type){
        case 1: //主播收入榜
            $name = lan('EMCEE_EARN_TOP','Home',$lan);
            break;
        case 2: //主播直播时长榜
            $name = lan('TOP_EMLIVETIME_LIST', 'Home',$lan);
            break;
        case 3: //新增用户关注榜
            $name = lan('TOP_NEWATT_LIST', 'Home',$lan);
            break;
        case 4: //用户消费榜
            $name = lan('TOP_CONSUME_LIST', 'Home',$lan);
            break;
        case 5: //用户在线时长榜
            $name = lan('TOP_USONLINETIME_LIST', 'Home',$lan);
            break;
        case 6: //运动大师榜
            $name = lan('TOP_SPORTGAME_LIST', 'Home',$lan);
            break;
        case 7: //主播免费礼物榜
            $name = lan('TOP_EMCEE_FREE_GIFT_LIST', 'Home',$lan);
            break;
        default:
            $name = lan('EMCEE_EARN_TOP', 'Home',$lan);
    }
    return $name;
}