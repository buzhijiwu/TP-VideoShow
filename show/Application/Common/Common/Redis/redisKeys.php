<?php
//普通类型：$key --> $value
$redisKeys['Keys'] = array(
    'RichRankingDay'  =>  '每日富豪排行榜',
    'RichRankingWeek'  =>  '每周富豪排行榜',
    'RichRankingMonth'  =>  '每月富豪排行榜',

    'RankingDay_EmceeEarn'  =>  '主播收入日榜', 
    'RankingWeek_EmceeEarn'  =>  '主播收入周榜',     
    'RankingMonth_EmceeEarn'  =>  '主播收入月榜', 
    'RankingAll_EmceeEarn'  =>  '主播收入总榜',  

    'RankingDay_UserRich'  =>  '用户消费日榜', 
    'RankingWeek_UserRich'  =>  '用户消费周榜',     
    'RankingMonth_UserRich'  =>  '用户消费月榜', 
    'RankingAll_UserRich'  =>  '用户消费总榜', 

    'RankingDay_NewFans'  =>  '新增用户关注日榜', 
    'RankingWeek_NewFans'  =>  '新增用户关注周榜',     
    'RankingMonth_NewFans'  =>  '新增用户关注月榜', 
    'RankingAll_NewFans'  =>  '新增用户关注总榜',     

    'RankingDay_LiveTime'  =>  '主播直播时长日榜', 
    'RankingWeek_LiveTime'  =>  '主播直播时长周榜',     
    'RankingMonth_LiveTime'  =>  '主播直播时长月榜', 
    'RankingAll_LiveTime'  =>  '主播直播时长总榜',

    'RankingDay_OnlineTime'  =>  '用户在线时长日榜', 
    'RankingWeek_OnlineTime'  =>  '用户在线时长周榜',     
    'RankingMonth_OnlineTime'  =>  '用户在线时长月榜', 
    'RankingAll_OnlineTime'  =>  '用户在线时长总榜',

    'RankingDay_SportMasters'  =>  '运动大师日榜', 
    'RankingWeek_SportMasters'  =>  '运动大师周榜',     
    'RankingMonth_SportMasters'  =>  '运动大师月榜', 
    'RankingAll_SportMasters'  =>  '运动大师总榜',

    'RankingDay_EmceeFreeGift'  =>  '主播免费礼物日榜',
    'RankingWeek_EmceeFreeGift'  =>  '主播免费礼物周榜',
    'RankingMonth_EmceeFreeGift'  =>  '主播免费礼物月榜',
    'RankingAll_EmceeFreeGift'  =>  '主播免费礼物总榜',

    'SportGameBanker'  =>  '运动会游戏庄家信息',
);

//hash类型：$key --> $hashKey --> $value
$redisKeys['hKeys'] = array(
    'GiftCategory' => array(
        'remark' => '礼物分类',
        'HashKey' => array(
            '$lantype'  =>  '语言类型'
        )
    ),
    'AllGifts' => array(
        'remark' => '礼物列表',
        'HashKey' => array(
            '$lantype'  =>  '语言类型'
        )
    ),
    'LevelConfig' => array(
        'remark' => '主播、富豪系统等级',
        'HashKey' => array(
            '$lantype_$leveltype_$levelid'  =>  '语言类型/等级类型/级别ID'
        )
    ),
    'GuardDefinition' => array(
        'remark' => '守护定义',
        'HashKey' => array(
            '$lantype'  =>  '语言类型'
        )
    ),
    'SeatDefinition' => array(
        'remark' => '沙发定义',
        'HashKey' => array(
            '$lantype'  =>  '语言类型'
        )
    ),
    'Emcee_$userid' => array(
        'remark' => '主播相关信息',
        'HashKey' => array(
            'Member'  =>  '主播基本信息',
            'EmceeVipid'  =>  '主播VIP等级',
            'EmceeProperty'  =>  '主播直播间信息',
            'FamilyInfo'  =>  '主播家族信息',
            'EmceeLevel'  =>  '主播等级信息',
            'EmceeGuards'  =>  '主播守护',
            'EmceeSeats'  =>  '主播沙发',
        )
    ),
    'Activity_TopEmceeTotal' => array(
        'remark' => '活动-总榜',
        'HashKey' => array(
            '$start_$limit'  =>  '分页'
        )
    ),
    'Activity_TopGifts' => array(
        'remark' => '活动-礼物榜',
        'HashKey' => array(
            '$start_$limit'  =>  '分页'
        )
    ),
    'Activity_TopNewFans' => array(
        'remark' => '活动-新增粉丝榜',
        'HashKey' => array(
            '$start_$limit'  =>  '分页'
        )
    ),
    'Activity_TopEmceeEarn' => array(
        'remark' => '活动-主播收入榜',
        'HashKey' => array(
            '$start_$limit'  =>  '分页'
        )
    ),
    'Activity_TopEmceeShare' => array(
        'remark' => '活动-主播分享榜',
        'HashKey' => array(
            '$start_$limit'  =>  '分页'
        )
    ),
    'Activity_TopUserSentGift' => array(
        'remark' => '活动-用户礼物榜',
        'HashKey' => array(
            '$start_$limit'  =>  '分页'
        )
    ),
    'BanLive' => array(
        'remark' => '禁播记录',
        'HashKey' => array(
            'Emcee_$userid'  =>  '主播userid'
        )
    ), 
    'KickRecord' => array(
        'remark' => '踢人记录',
        'HashKey' => array(
            'User$kickeduserid_Emcee$emceeuserid'  =>  '被踢用户和踢人用户'
        )
    ),
    'SportGameOption' => array(
        'remark' => '运动会游戏选项',
        'HashKey' => array(
            '$lantype'  =>  '语言类型',
        )
    ),
    'SportGameOptionList' => array(
        'remark' => '运动会游戏选项排列',
        'HashKey' => array(
            '$lantype'  =>  '语言类型',
        )
    ),
    'SportGamePlayerStake' => array(
        'remark' => '运动会游戏参与用户押注记录',
        'HashKey' => array(
            '$userid'  =>  '参与者用户ID',
        )
    ),
    'SportGamePlayerSettlement' => array(
        'remark' => '运动会游戏参与用户结算结果',
        'HashKey' => array(
            '$userid'  =>  '参与者用户ID',
        )
    ),
);

return $redisKeys;