<?php
require('./Application/Api/Common/createdata.php');
require('./Application/Api/Common/jsonapi.php');
require './Application/Api/Common/ftpFile.php';

function getLanType_api() {
	$lanTypes = C('LAN_TYPE');
	$lanType = strtolower(strstr($_SERVER['HTTP_ACCEPT_LANGUAGE'], ',', true));
	
	if (!in_array($lanType, $lanTypes)) {
	    $lanType = 'en';
	}
	
	return $lanType;
}

/**
 *
 * @desc  从一个数字区间内随机获取N个数的用户ID
 * @param (number)$start #数字区间开始值，默认为101
 * @param (number)$end #数字区间结束值，默认为1000
 * @param (number)$length #获取随机数数量，默认为10
 * return 返回：$n个数字组成的一维数组
 */
function getRandUserId($start=101,$end=1000, $length=10){
    //range 是将1到42 列成一个数组
    $numbers = range($start,$end);
    //shuffle 将数组顺序随即打乱
    shuffle($numbers);
    //array_slice 取该数组中的某一段
    return array_slice($numbers,0,$length);
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