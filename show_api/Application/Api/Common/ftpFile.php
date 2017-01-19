<?php
/*
** 函数作用：file表单远程上传
** 参数：$file file表单名称
** 参数：$filePath 上传服务器上的路径
** 参数：$fileName 上传服务器上保存的文件名称
 */
function ftpFile($file, $filePath, $fileName=''){
    $ext = pathinfo($_FILES[$file]['name'], PATHINFO_EXTENSION); //文件后缀

    if(!$fileName){
        $fileName = basename($_FILES[$file]['name'],'.'.$ext); //文件名称
    }

    //文件上传远程服务器
    $ftp = new \Org\Util\ThinkFtp();
    $ftp->connect();

    if(!$ext){
        $ext = 'jpg';
    }
    $local_path = $_FILES[$file]['tmp_name']; //本地路径
    $save_path = $filePath . $fileName . '.'. $ext; //上传服务器路径

    //上传
    $file_result = $ftp->upload($local_path, $save_path);
    if($file_result){
        $result = array(
            'code' => 200,
            'msg' => $save_path
        );
    }else{
        $result = array(
            'code' => -1,
            'msg' => $ftp->getError()
        );
    }

    return $result;
}

/*
** 函数作用：本地文件远程上传
** 参数：$localPath 本地文件路径
** 参数：$filePath 上传服务器上的路径
** 参数：$isDelete 是否删除原文件
 */
function ftpUpload($localPath, $filePath, $isDelete=1){
    //如果没有保存路径，默认和上传路径一致
    if(!$filePath){
        $filePath = $localPath;
    }
    //文件上传远程服务器
    $ftp = new \Org\Util\ThinkFtp();
    $ftp->connect();

    //上传
    $file_result = $ftp->upload(realpath(__ROOT__).$localPath, $filePath);
    if($file_result){
        //删除原文件
        if($isDelete == 1){
            unlink(realpath(__ROOT__).$localPath);
        }
        $result = array(
            'code' => 200,
            'msg' => $filePath
        );
    }else{
        $result = array(
            'code' => -1,
            'msg' => $ftp->getError()
        );
    }

    return $result;
}

/*
** 函数作用：删除远程服务器上的文件
** 参数：$file 文件路径
 */
function ftpDelete($file){
    if(!$file){
        $result = array(
            'code' => -1,
            'msg' => ''
        );
        return $result;
    }

    //从远程服务器删除文件
    $ftp = new \Org\Util\ThinkFtp();
    $ftp->connect();
    $file_result = $ftp->delete_file($file);

    if($file_result){
        $result = array(
            'code' => 200,
            'msg' => ''
        );
    }else{
        $result = array(
            'code' => -1,
            'msg' => $ftp->getError()
        );
    }
    return $result;
}