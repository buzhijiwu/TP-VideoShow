<?php

use Think\Page;
/**
 * TODO 基础分页的相同代码封装，使前台的代码更少
 * @param $count 要分页的总记录数
 * @param int $pagesize 每页查询条数
 * @return \Think\Page
 */
function getpage($count, $pagesize = 10) {
    $p = new Page($count, $pagesize);
    $p->setConfig('header', '<li class="rows">'.lan("PAGE_TOTAL", "Common").'<b>%TOTAL_ROW%</b>'.lan("TIAO", "Common").lan("RECORD", "Common").'&nbsp;'.lan("DI", "Common").'<b>%NOW_PAGE%</b>'.lan("PAGE", "Common").'/'.lan("PAGE_TOTAL", "Common").'<b>%TOTAL_PAGE%</b>'.lan("PAGE", "Common").'</li>');
    $p->setConfig('prev', lan("LAST_PAGE", "Common"));
    $p->setConfig('next', lan("NEXT_PAGE", "Common"));
    $p->setConfig('last', lan("END_PAGE", "Common"));
    $p->setConfig('first', lan("FIRST_PAGE", "Common"));
    $p->setConfig('theme', '%FIRST%%UP_PAGE%%LINK_PAGE%%DOWN_PAGE%%END%%HEADER%');
    $p->lastSuffix = false;//最后一页不显示为总页数
    return $p;
}

function getConfigPage($count, $pagesize = 10) {
    $p = new Page($count, $pagesize);
//    $p->setConfig('header', '<li class="rows">'.lan("PAGE_TOTAL", "Common").'<b>%TOTAL_ROW%</b>'.lan("TIAO", "Common").lan("RECORD", "Common").'&nbsp;'.lan("DI", "Common").'<b>%NOW_PAGE%</b>'.lan("PAGE", "Common").'/'.lan("PAGE_TOTAL", "Common").'<b>%TOTAL_PAGE%</b>'.lan("PAGE", "Common").'</li>');
    $p->setConfig('prev', '<img src="/Public/Public/Images/PersonalCenter/page_prev.png">');
    $p->setConfig('next', '<img src="/Public/Public/Images/PersonalCenter/page_next.png">');
    $p->setConfig('last', '<img src="/Public/Public/Images/PersonalCenter/page_last.png">');
    $p->setConfig('first', '<img src="/Public/Public/Images/PersonalCenter/page_first.png">');
    $p->rollPage = 8;
    $p->setConfig('theme', '%FIRST%%UP_PAGE%%LINK_PAGE%%DOWN_PAGE%%END%');
    $p->lastSuffix = false;//最后一页不显示为总页数
    return $p;
}