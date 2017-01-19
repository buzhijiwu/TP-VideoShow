<?php
namespace Org\Util;
/**
 * FTP基本操作：
 * 1) 登陆;    connect
 * 2) 当前目录文件列表;  filelist
 * 4) 重命名/移动;  rename
 * 6) 删除;    delete_dir/delete_file
 * 7) 上传;    upload
 * 8) 下载    download
 */

class ThinkFtp {
    private $hostname = '';
    private $username = '';
    private $password = '';
    private $port   = 21;
    private $passive  = true;
    private $conn_id  = false;
    private $error  = '';

    /**
     * 初始化配置
     *
     * @param  hostname：FTP服务器HOST
     * @param  port：FTP服务器端口
     * @param  username：FTP服务器用户
     * @param  password：FTP服务器用户密码
     */
    public function __construct($config = array()) {
        if(empty($config)) {
            $config = array(
                'hostname' => C('TP_FTP_HOST'),
                'port' => C('TP_FTP_PORT'),
                'username' => C('TP_FTP_USER'),
                'password' => C('TP_FTP_PWD'),
            );
        }
        $this->_init($config);
    }

    /**
     * FTP连接登录
     */
    public function connect() {
        //FTP连接
        if(false === ($this->conn_id = @ftp_connect($this->hostname,$this->port))) {
            $this->error = "FTP connect fail";
            return false;
        }

        //FTP登陆
        if(!@ftp_login($this->conn_id, $this->username, $this->password)) {
            $this->error = "FTP login fail";
            return false;
        }

        //把被动模式设置为打开或关闭 在被动模式中，数据连接是由客户机来初始化的，而不是服务器。这在客户机位于防火墙之后时比较有用
        if($this->passive === true) {
            ftp_pasv($this->conn_id, true);
        }

        return true;
    }

    /**
     * 上传
     *
     * @access  public
     * @param  $localpath：string  本地目录标识
     * @param $remotepath：string 远程目录标识(ftp)
     * @param $mode：string 上传模式 auto || ascii
     * @param $permissions：int  上传后的文件权限列表
     * @return boolean
     */
    public function upload($localpath, $remotepath, $mode = 'auto', $permissions = null) {
        if(!$this->_isconn()){
            return false;
        }

        //验证本地路径
        if( !file_exists($localpath)) {
            $this->error = "FTP no source file:".$localpath;
            return false;
        }

        //根据扩展名定义FTP传输模式  ascii 或 binary
        if($mode == 'auto') {
            $ext = $this->_getext($localpath);
            $mode = $this->_settype($ext);
        }
        $mode = ($mode == 'ascii') ? FTP_ASCII : FTP_BINARY;

        //文件上传
        $result = @ftp_put($this->conn_id, $remotepath, $localpath, $mode);
        if($result === false) {
            $this->error = "FTP unable to upload:localpath[".$localpath."]/remotepath[".$remotepath."]";
            return false;
        }

        //设置文件权限
        if( !is_null($permissions)) {
            $this->chmod($remotepath,(int)$permissions);
        }

        return true;
    }

    /**
     * 下载
     *
     * @access  public
     * @param  $remotepath：string  远程目录标识(ftp)
     * @param $localpath：string 本地目录标识
     * @param $mode：string 下载模式 auto || ascii
     * @return boolean
     */
    public function download($remotepath, $localpath, $mode = 'auto') {
        if(!$this->_isconn()){
            return false;
        }

        //根据扩展名定义FTP传输模式  ascii 或 binary
        if($mode == 'auto') {
            $ext = $this->_getext($remotepath);
            $mode = $this->_settype($ext);
        }
        $mode = ($mode == 'ascii') ? FTP_ASCII : FTP_BINARY;

        //文件下载
        $result = @ftp_get($this->conn_id, $localpath, $remotepath, $mode);
        if($result === false) {
            $this->error = "FTP unable to download:localpath[".$localpath."]-remotepath[".$remotepath."]";
            return false;
        }

        return true;
    }

    /**
     * 重命名/移动
     *
     * @access  public
     * @param  $oldname：string  远程目录标识(ftp)
     * @param $newname：string 新目录标识
     * @param $move：boolean 判断是重命名(FALSE)还是移动(TRUE)
     * @return boolean
     */
    public function rename($oldname, $newname, $move = false) {
        if(!$this->_isconn()){
            return false;
        }

        // 重命名/移动文件
        $result = @ftp_rename($this->conn_id, $oldname, $newname);
        if($result === false) {
            if($move == false){
                $this->error = "FTP unable to rename";
            }else{
                $this->error = "FTP unable to move";
            }
            return false;
        }

        return true;
    }

    /**
     * 删除文件
     *
     * @access  public
     * @param  $file：string  文件标识(ftp)
     * @return boolean
     */
    public function delete_file($file) {
        if(!$this->_isconn()){
            return false;
        }

        //删除文件
        $result = @ftp_delete($this->conn_id, $file);
        if($result === FALSE) {
            $this->error = "FTP unable to delete file:file[".$file."]";
            return false;
        }

        return true;
    }

    /**
     * 修改文件权限
     *
     * @access  public
     * @param  $path：string  目录标识(ftp)
     * @return boolean
     */
    public function chmod($path, $perm) {
        if(!$this->_isconn()){
            return false;
        }

        //只有在PHP5中才定义了修改权限的函数(ftp)
        if( !function_exists('ftp_chmod')){
            $this->error = 'FTP unable to chmod(function)';
            return false;
        }

        //修改文件权限
        $result = @ftp_chmod($this->conn_id, $perm, $path);
        if($result === false) {
            $this->error = "FTP unable to chmod:path[".$path."]-chmod[".$perm."]";
            return false;
        }

        return true;
    }

    /**
     * 获取目录文件列表
     *
     * @access  public
     * @param  $path：string  目录标识(ftp)
     * @return array
     */
    public function filelist($path = '.') {
        if(!$this->_isconn()){
            return false;
        }

        return ftp_nlist($this->conn_id, $path);
    }

    /**
     * 关闭FTP
     *
     * @access  public
     * @return boolean
     */
    public function close() {
        if( ! $this->_isconn()) {
            return false;
        }

        return @ftp_close($this->conn_id);
    }

    /**
     * 获取最后一次上传错误信息
     * @return string 错误信息
     */
    public function getError(){
        return $this->error;
    }

    /**
     * FTP成员变量初始化
     *
     * @access private
     * @param $config：array 配置数组
     * @return void
     */
    private function _init($config = array()) {
        foreach($config as $key => $val) {
            if(isset($this->$key)) {
                $this->$key = $val;
            }
        }
        //特殊字符过滤
        $this->hostname = preg_replace('|.+?://|','',$this->hostname);
    }

    /**
     * 判断con_id is_resource()检测变量是否为资源类型
     *
     * @access  private
     * @return boolean
     */
    private function _isconn() {
        if(!is_resource($this->conn_id)){
            $this->error = 'FTP no connection';
            return false;
        }
        return true;
    }

    /**
     * 从文件名中获取后缀扩展
     *
     * @access  private
     * @param  $filename：string  目录标识
     * @return string
     */
    private function _getext($filename) {
        if(false === strpos($filename, '.')) {
            return 'jpg';
        }

        $extarr = explode('.', $filename);
        return end($extarr);
    }

    /**
     * 从后缀扩展定义FTP传输模式  ascii 或 binary
     *
     * @access  private
     * @param  $ext：string  后缀扩展
     * @return string
     */
    private function _settype($ext) {
        $text_type = array (
            'txt',
            'text',
            'php',
            'phps',
            'php4',
            'js',
            'css',
            'htm',
            'html',
            'phtml',
            'shtml',
            'log',
            'xml'
        );

        return (in_array($ext, $text_type)) ? 'ascii' : 'binary';
    }
}