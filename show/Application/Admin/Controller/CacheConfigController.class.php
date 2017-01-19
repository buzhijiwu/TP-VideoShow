<?php
/**
 * 缓存机制管理
 */
namespace Admin\Controller;
use Think\Controller;
class CacheConfigController extends CommonController
{
    public function cache_config()
    {
        $this->display();
    }

    public function save_cacheconfig()
    {
        $para = $_POST['para'];
        if (is_array($para)) {
            foreach ($para as $key=>$val) {

                $filepath = './Application/Admin/Conf/config.php';
                if (file_exists($filepath)) {
                    $arr = include $filepath;
                    $arr[$key] = $val;
                } else {
                    $arr = array($key=>$val,'disable'=>0, 'dirname'=>$key);
                }

                $res = file_put_contents($filepath, '<?php return '.var_export($arr, true).';?>');
            }
            $this->success();
        } else {
            $this->error();
        }

    }
}