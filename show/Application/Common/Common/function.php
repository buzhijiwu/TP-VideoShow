<?php
require('./Application/Common/Common/cache.php');
require('./Application/Common/Common/language.php');
require './Application/Common/Common/verify.php';
require './Application/Common/Common/login.php';
require './Application/Common/Common/paging.php';
require './Application/Common/Common/waashowD.php';
require './Application/Common/Common/date.php';
require './Application/Common/Common/randomcode.php';
require './Application/Common/Common/ftpFile.php';


/**
 * 生成家族徽章方法
 * @param  string  $str 传入字符串
 * @param  integer $l   字符串长度
 * @return mixed      数组或false
 */
function splitstring($str, $l = 0) {
    if ($l > 0) {
        $ret = array();
        $len = mb_strlen($str, "UTF-8");
        for ($i = 0; $i < $len; $i += $l) {
            $ret[] = mb_substr($str, $i, $l, "UTF-8");
        }
        return $ret;
    }
    return preg_split("//u", $str, -1, PREG_SPLIT_NO_EMPTY);
}

function getFamilyBadge($badgecontent){
    $badgeArr = str_split($badgecontent);
    $familyBadge = "";
    foreach($badgeArr as $value){
         $familyBadge .= "<i class=\"guild-char-postion char-style1 char-". $value ."\"></i>";
    }
    return $familyBadge;
}

/*
** 函数作用：获得页面固定文字的语言包
** 参数1：[无]
** 返回值：[无]
** 备注：[无]
 */
function getSystemLan($key,$lan)
{
	if (!rs('SystemLan')) {
		$system_lan_array = M('language')->select();
		rs('SystemLan', json_encode($system_lan_array));
	}
	$system_lan_array = json_decode(rs('SystemLan'), true);
	foreach ($system_lan_array as $k => $v) {
		if ($v['k'] == $key) return $v[$lan];
	}
}

	function getLanType()
	{
		$lanTypes = C('LAN_TYPE');
		$lanType = strtolower(strstr($_SERVER['HTTP_ACCEPT_LANGUAGE'], ',', true));

		if (!in_array($lanType, $lanTypes))
		{
			$lanType = 'en';
		}

		return $lanType;
	}

function getDbIp($username)
{
	$routeCond = array(
		username => $username,
	);
	$route = D('route')->where($routeCond)->select();

	if (!$route)
	{
         return null;
	}

	$dbConfig = D('dbconfig')->where($route->dbnode)->select();
	return $dbConfig->dbip;
}

/*
** 获取所有RedisKeys
 */
function getRedisKeys() {
    $redisKeys = require('./Application/Common/Common/Redis/redisKeys.php');
    return $redisKeys;
}

/**
 * 导出Excel
 * @param  array   $title     [定义表头，一维数组]
 * @param  array   $data      [导出列表，必须和表头严格对应]
 * @param  string  $filename  [要保存的文件名]
 */
function exportExcle($title=array(),$data=array(),$filename=''){
    if(!$filename){
        $filename = time();
    }
    header("Content-type:application/octet-stream");
    header("Accept-Ranges:bytes");
    header("Content-type:application/vnd.ms-excel");
    header("Content-Disposition:attachment;filename=".$filename.".xls");
    header("Pragma: no-cache");
    header("Expires: 0");
    if (!empty($title)){
        $title= implode("\t", $title);
        echo "$title\n";
    }
    if (!empty($data)){
        foreach($data as $key=>$val){
            $data[$key]=implode("\t", $data[$key]);
        }
        echo implode("\n",$data);
    }
}

function ExcleString($str){
    return "=\"".str_replace("\r\n",'',$str)."\"";
}

/*
** 函数作用：根据URL获取远程文件保存到本地
** 参数：$url 远程文件地址
** 参数：$save_dir 保存本地路径
** 参数：$filename 文件名称及后缀
** 返回值：status状态(0成功1失败)；msg错误信息；file_name文件名称，save_path保存路径
 */
function getFileByUrl($url,$save_dir='',$filename=''){
    //远程文件URL地址
    if(trim($url)==''){
        return array('status'=>1,'msg'=>'URL is null');
    }
    //文件保存路径
    if(trim($save_dir) == '' || substr($save_dir,0,1) !== '/'){ //路径第一位
        $save_dir = '/';
    }
    if(substr($save_dir,-1,1) !== '/'){ //路径最后一位
        $save_dir .= '/';
    }
    //保存文件名,仅适用于URL上是文件资源的绝对定位
    if(trim($filename) == ''){
        $ext = strrchr($url,'.');//文件后缀
        $filename = time().$ext;
    }
    //获取远程文件所采用的方法
    ob_start();
    readfile($url);
    $content = ob_get_contents();
    ob_end_clean();
    if(!$content){
        return array('status'=>1,'msg'=>'File download failed');
    }

    //保存文件到本地
    $save_path = $save_dir.$filename;
    $fp2 = @fopen('.'.$save_path,'w');
    fwrite($fp2,$content);
    fclose($fp2);
    unset($content,$url);
    return array('status'=>0,'file_name'=>$filename,'save_path'=>$save_path);
}

/*
** 函数作用：图片裁剪
** 参数：$src 图片路径
** 参数：$width 裁剪宽度
** 参数：$height 裁剪高度
** 参数：$x $y 裁剪起点坐标
** 参数：$scale 缩放比例   0不缩放
** 参数：$rotate 旋转角度 0不旋转
** 参数：$fill 是否填充 0不填充
** 参数：$type 裁剪类型： 1、裁剪图片并预览 2、裁剪图片并保存
** 参数：$path 保存路径的目录
** 参数：$name 保存文件名称，不带后缀
 */
function imageCut($src,$width,$height,$x=0,$y=0,$scale=0,$rotate=0,$fill=1,$type=1,$path='',$name=''){
    if($width <= 0 ||  $height <= 0){
        return false;
    }

    //获取源图片信息
    $imageInfo = getimagesize($src);
    $ext = $imageInfo['mime'];  //后缀名

    //根据源图片创建一个image对象
    switch($ext){
        case 'image/gif':
            $source_image = imagecreatefromgif($src);
            $save_path = $path.$name.'.gif';
            break;
        case 'image/jpeg':
            $source_image = imagecreatefromjpeg($src);
            $save_path = $path.$name.'.jpg';
            break;
        case 'image/png':
            $source_image = imagecreatefrompng($src);
            $save_path = $path.$name.'.png';
            break;
        default:
            return false;
            break;
    }

    //图片旋转
    $source_image = imagerotate($source_image,$rotate,0);

    //根据传来的宽高参数创建真彩图像
    $target_image = imagecreatetruecolor($width,$height);

    //图片缩放
    if($scale){
        //获取旋转后的图片宽度和高度
        $source_width  = imagesx($source_image);
        $source_height = imagesy($source_image);

        if($fill){ //图片填充
            $source_ratio  = $source_height / $source_width;
            $target_ratio  = $height / $width;
            if($source_ratio > $target_ratio){  // 源图过高
                $cropped_width  = $source_width;
                $cropped_height = $source_width * $target_ratio;
                $x = 0;
                $y = ($source_height - $cropped_height) / 2;
            }elseif($source_ratio < $target_ratio){    // 源图过宽
                $cropped_width  = $source_height / $target_ratio;
                $cropped_height = $source_height;
                $x = ($source_width - $cropped_width) / 2;
                $y = 0;
            }else{  // 源图适中
                $cropped_width  = $source_width;
                $cropped_height = $source_height;
                $x = 0;
                $y = 0;
            }

            $cropped_image = imagecreatetruecolor($cropped_width,$cropped_height);  //根据缩放比例创建真彩图像
            imagecopy($cropped_image,$source_image,0,0,$x,$y,$cropped_width,$cropped_height);   //原图裁剪
            imagecopyresampled($target_image,$cropped_image,0,0,0,0,$width,$height,$cropped_width,$cropped_height); //剪后的图片填充
        }else{
            $cropped_width =  $source_width*$scale;
            $cropped_height =  $source_height*$scale;

            $cropped_image = imagecreatetruecolor($cropped_width,$cropped_height);  //根据缩放比例创建真彩图像
            imagecopyresized($cropped_image,$source_image,0,0,0,0,$cropped_width,$cropped_height,$source_width,$source_height); //原图缩放
            imagecopy($target_image,$cropped_image,0,0,$x,$y,$width,$height);   //缩放后的图片裁剪
        }
    }else{
        imagecopy($target_image,$source_image,0,0,$x,$y,$width,$height);
    }

    //输出结果
    switch($type){
        case 1:     //裁剪图片并预览
            header('Content-Type: image/jpeg');
            imagejpeg($target_image);
            break;
        case 2:     //裁剪图片并保存
            imagejpeg($target_image,$save_path,100);
            break;
        default:
            return false;
            break;
    }

    //销毁图像
    imagedestroy($source_image);
    imagedestroy($target_image);
    imagedestroy($cropped_image);

    return $save_path;
}

/*
** 函数作用：获取过滤的脏话列表
 */
function getFilterWords(){
    $filterWords = require_once('./Application/Common/Common/Language/filterWords.php');
    return $filterWords;
}

/**
 * 将图片从远程URL拿到本地，然后再ftp到图片服务器上
 * @param $remoteUrl
 * @param $userid
 * @return string
 */
function getSmallHeadpicUrl($remoteUrl, $userid)
{
    $ext = strrchr($remoteUrl,'.');//文件后缀
    $extArray = array('.png','.jpg','.gif');
    if(!in_array($ext,$extArray)){
        $ext = '.jpg';
    }
    $filename = date('YmdHis').'_'.$userid.$ext;    //文件名
    //$image_base_url = C('IMAGE_BASE_URL');
    $save_dir = "/Uploads/HeadImg/120120/"; //保存路径
    $getFileByUrl = getFileByUrl($remoteUrl,$save_dir,$filename);
    $image_file = $save_dir.$filename;
    if($getFileByUrl['status'] == 0){
        $ftpUpload = ftpUpload($image_file, $image_file);
        if($ftpUpload['code'] == 200){
            $smallheadpic = $image_file;
        }
        else
        {
            $smallheadpic = "/Public/Public/Images/HeadImg/default.png";
        }
    }
    else
    {
        $smallheadpic = "/Public/Public/Images/HeadImg/default.png";
    }

    return $smallheadpic;
}

/**
 * 执行一次异步请求
 * @param $url异步请求链接地址
 */
function makeRequest($url) {
    $cmd = "curl '" . $url . "' > /dev/null 2>&1 &";    //让进程正确的执行 ，而把任何可能输出都丢弃掉。
    exec($cmd, $output, $exit);
    return $exit == 0;
}