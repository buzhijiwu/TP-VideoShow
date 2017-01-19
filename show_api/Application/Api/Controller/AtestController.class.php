<?php
namespace Api\Controller;
use Think\Controller;

class AtestController extends Controller {

    public function _initialize(){
        $this->encryptDecryptKey = 'waashow-ShanRuoC';
        $this->encryptDecryptSignKey = 'ShanRuoCom';
    }

    public function index(){
        echo date('Y-m-d H:i:s');
    }

    public function test(){
        if (IS_POST) {
            $test_str = I('post.test', '');
            $data = $this->decrypt($test_str);
            $data = json_encode($data);
        }   

        header("Content-type: text/html; charset=utf-8");
        $html = '<form action="/api.php/Atest/test" method="post">
            输入加密字符串：<br><br>
            <textarea name="test" rows="4" cols="200">'.$test_str.'</textarea><br><br>
            <input type="submit">
            </form>
            结果：<a href="http://www.bejson.com/jsonviewernew/" target="_blank">JSON视图</a><br><br>
            <button onclick="copy();">复制</button><br>
            <script type="text/javascript"> 
                function copy(){ 
                    var content=document.getElementById("contents");//对象是多行文本框contents 
                    content.select(); //选择对象 
                    document.execCommand("Copy"); //执行浏览器复制命令 
                } 
            </script> ';

        echo $html;
        echo '<textarea name="contents" id="contents" rows="4" cols="200">'.$data.'</textarea>';
    }

    private function test_encrypt(){
        //测试时输出的字符串需转义 “+”==>“%2B”
        $auth = md5($this->encryptDecryptKey . $this->encryptDecryptSignKey);
        $data = array(
            'devicetype' => 1,
            'auth' => $auth,
            'key' => $this->encryptDecryptKey,
        );
        $str = $this->encrypt($data);
        echo $str;exit;
    }

    /**
     * 数据加密
     * 对要返回的数据进行加密，并返回加密后的数据
     * @param $data：需要加密的数据,array 或 string
     * @return $string：返回加密后的数据，string类型
     */
    private  function encrypt($data){
        $data = json_encode($data);
        $size = mcrypt_get_block_size(MCRYPT_RIJNDAEL_128, MCRYPT_MODE_ECB);
        $pad = $size - (strlen($data) % $size);
        $data = $data . str_repeat(chr($pad), $pad);
        $td = mcrypt_module_open(MCRYPT_RIJNDAEL_128, '', MCRYPT_MODE_ECB, '');
        $iv = mcrypt_create_iv (mcrypt_enc_get_iv_size($td), MCRYPT_RAND);
        mcrypt_generic_init($td, $this->encryptDecryptKey, $iv);
        $string = mcrypt_generic($td, $data);
        mcrypt_generic_deinit($td);
        mcrypt_module_close($td);
        $string = base64_encode($string);
        return $string;
    }

    /**
     * 数据解密
     * 对接收的数据进行解密，并返回解密后的数据
     * @param $string：需要解密的数据，string类型
     * @return $data：返回解密后的数据，array 或 string
     */
    private  function decrypt($string){
        $data = mcrypt_decrypt(
            MCRYPT_RIJNDAEL_128,
            $this->encryptDecryptKey,
            base64_decode($string),
            MCRYPT_MODE_ECB
        );
        $dec_s = strlen($data);
        $padding = ord($data[$dec_s-1]);
        $data = substr($data, 0, -$padding);
        $data = json_decode($data,true);
        return $data;
    }    	
}

