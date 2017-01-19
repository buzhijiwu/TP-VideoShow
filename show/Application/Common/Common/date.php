<?php

/*
 ** 方法作用：获取今天是今年的第几个自然周
 ** 参数1：[无]
 ** 返回值：[无]
 ** 备注：[无]
 */
function getCurrentWeek() {
    // $datearr = getdate();
    // $year = strtotime($datearr['year'].'-1-1');
    // $startdate = getdate($year);
    // $firstweekday = 7-$startdate['wday'];//获得第一周几天
    // $yday = $datearr['yday']+1-$firstweekday;//今年的第几天
    // return ceil($yday/7)+1;//取到第几周
    return date('W');  
}

//获取上一周是第几周
function getLastWeek() {
    return date("W",mktime(0,0,0,date("m"),date("d")-7,date("Y")));
}

//获取上一周是哪一年
function getLastWeekYear() {
    return date("o",mktime(0,0,0,date("m"),date("d")-7,date("Y")));    
}

//获取上个月是第几个月
function getLastMonth() {
    return date("m",mktime(0,0,0,date("m")-1,date("d"),date("Y")));
}

//获取上个月是哪一年
function getLastMonthYear() {
    return date("o",mktime(0,0,0,date("m")-1,date("d"),date("Y")));
}

function getYesterdayBegin()
{
    return date('Y-m-d H:i:s', mktime(0,0,0,date('m'), date('d')-1 ,date('Y')));
}

function getYesterdayEnd()
{
    return date('Y-m-d H:i:s', mktime(23,59,59,date('m'), date('d')-1 ,date('Y')));
}

function getLastWeekBegin()
{
    return date('Y-m-d H:i:s', mktime(0,0,0,date('m'), date('d')-date('N')+1-7 ,date('Y'))); //每周从周一开始
}

function getLastWeekEnd()
{
    return date('Y-m-d H:i:s', mktime(23,59,59,date('m'), date('d')-date('N')+7-7,date('Y')));
}

function getLastMonthBegin()
{
    return date('Y-m-d H:i:s', mktime(0,0,0,date('m')-1, 1, date('Y')));
}

function getLastMonthEnd()
{
    return date('Y-m-d H:i:s', mktime(23,59,59,date('m'), 0, date('Y')));
}


function getCurWeekBegin()
{
    //星期中的第几天，数字表示，N和w。N:1（表示星期一）到 7（表示星期天）;w:0（表示星期天）到 6（表示星期六）
//    return date('Y-m-d H:i:s', mktime(0,0,0,date('m'), date('d')-date('w'), date('Y')));
    return date('Y-m-d H:i:s', mktime(0,0,0,date('m'), date('d')-date('N')+1, date('Y')));
}

/*
** 函数作用：把时间长度转换为小时分钟显示
** 参数：$length:时长，$type:时长类型（s秒、m分钟、h小时）
 */
function getTimeLength($length,$type='s') {
    $ShowLength = '';
    switch($type){
        case 'm':
            $day = floor($length/1440);
            $hour = floor($length/60)%24;
            $minute = $length%60;
            break;
        case 'h':
            $day = floor($length/24);
            $hour = $length%24;
            $minute = 0;
            break;
        default:
            $day = floor($length/86400);
            $hour = floor($length/3600)%24;
            $minute = floor($length/60)%60;
    }

    if($day > 0){
        $ShowLength .= $day.lan('DAY','Admin').$hour.lan('HOUR','Admin').$minute.lan('MINUTE','Admin');
    }elseif($hour > 0){
        $ShowLength .= $hour.lan('HOUR','Admin').$minute.lan('MINUTE','Admin');
    }else{
        $ShowLength .= $minute.lan('MINUTE','Admin');
    }
    return $ShowLength;
}
