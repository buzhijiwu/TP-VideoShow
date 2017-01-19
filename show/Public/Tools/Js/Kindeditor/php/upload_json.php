<?php
/**
 * KindEditor PHP
 *
 * 本PHP程序是演示程序，建议不要直接在实际项目中使用。
 * 如果您确定直接使用本程序，使用之前请仔细确认相关安全设置。
 *
 */
require_once('ThinkFtp.class.php');
require_once('JSON.php');
//文件上传远程服务器
$ext = pathinfo($_FILES['imgFile']['name'], PATHINFO_EXTENSION); //文件后缀
$fileName = date("YmdHis") . '_' . rand(10000, 99999); //文件名称
$filePath = '/Uploads/Market/pc/';  //保存路径
$ftp = new ThinkFtp();
$ftp->connect();
$local_path = $_FILES['imgFile']['tmp_name']; //本地路径
$save_path = $filePath . $fileName . '.'. $ext; //上传服务器路径
$file_result = $ftp->upload($local_path, $save_path);
if($file_result){
    $file_url = $ftp->image_base_url().$save_path;
    header('Content-type: text/html; charset=UTF-8');
    $json = new Services_JSON();
    echo $json->encode(array('error' => 0, 'url' => $file_url));exit;
}else{
    alert("上传失败。");
}

function alert($msg) {
	header('Content-type: text/html; charset=UTF-8');
	$json = new Services_JSON();
	echo $json->encode(array('error' => 1, 'message' => $msg));exit;
}
