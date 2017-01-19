<?php
$html = "<h1>APP返回码</h1>";
$html .= "<span style='color:red;'>注：<br/>";
$html .= "<span style='color:red;'>-1:   一般是接口这边业务处理时，保存数据或更新数据失败<br/>";
$html .= "<span style='color:red;'>400: 一般是请求端传输的加密数据不正确，或者是缺少必须的参数<br/>";
$html .= "<span style='color:red;'>500: 一般是业务处理时，出现了不该出现的逻辑，比如使用伪造的数据非法请求接口之类，也有可能是服务器不稳定执行出错</span><br/><br/>";

$codeArray = array(
    '-1' => '系统繁忙',
    '200' => '成功',
    '400' => '参数有误',
    '500' => '请求异常',

    '400001' => '您还未登陆，请登陆',
    '400002' => '该号码已注册',
    '400003' => '验证码错误',
    '400004' => '该号码不存在,请注册',
    '400005' => '您的账户已被禁用或被删除',
    '400006' => '密码错误',
    '400007' => '超过当天注册短信最大发送次数',
    '400008' => '旧密码输入错误',
    '400009' => '两次密码不一致',
    '400010' => '不符合密码规则',

    '401001' => '您输入的内容含有非法字符',
    '401002' => '昵称应该为1-50位之间',
    '401003' => '头像上传失败',
    '401004' => '昵称已存在',
    '401005' => '您输入的昵称含有违禁字符',

    '403001' => '您涉嫌违规直播，有疑问请联系客服08.38.635287',
    '403101' => '你已拉黑',
    '403102' => '拉黑成功',
    '403103' => '权限不够',
    '403104' => '次数已用完',
    '403105' => '你不能禁言你自己',
    '403106' => '对方是房间管理员,不能被禁言',
    '403107' => '对方是房间守护者，您不能禁言',
    '403108' => '你不能踢出你自己',
    '403109' => '对方是房间管理员,不能被踢出',
    '403110' => '对方是房间守护者，您不能踢',
    '403201' => '你已经关注该主播',
    '403202' => '一个主播每小时只能分享一次',

    '404001' => '充值失败，请联系客服',
    '404002' => '充值失败',
    '404003' => '一个用户一小时内只有30次充值请求机会',

    '405001' => '余额不足',
    '405002' => '该靓号已被购买',
    '405003' => '该沙发已被其他用户更高价格购买',
);

$html .= "<table>";
foreach ($codeArray as $code => $msg) {
    $html .= "<tr><td>".$code."</td><td>&nbsp;</td><td>".$msg."</td></tr>";
}
$html .= "</table>";

echo $html;