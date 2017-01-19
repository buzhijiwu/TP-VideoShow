<?php
/**
 * 系统设置
 */
namespace Admin\Controller;
use Think\Controller;

class SysConfigController extends CommonController
{
//设置
    public function sys_config()
    {
        $sysConfigDB = D("Siteconfig");
        $siteconfig = $sysConfigDB->find();

        if ($siteconfig)
        {
            $this->assign('siteconfig', $siteconfig);
        }
        else
        {
            //$this->assign('jumpUrl',__URL__.'/mainFrame');
            $this->error(lan('PARAM_ERROR', 'Admin'));
        }
        $this->display();
    }

    public function save_sysconfig()
    {

        $siteconfig = D('Siteconfig');
        $vo = $siteconfig->create();

        if (!$vo)
        {
            // $this->assign('jumpUrl', __URL__ . '/sys_config/');
            $this->error($siteconfig->getError());
        }
        else
        {
            $siteconfig->save();
            $cdn = $_POST['cdn'];
            $fps = $_POST['fps'];
            $zddk = $_POST['zddk'];
            $pz = $_POST['pz'];
            $zjg = $_POST['zjg'];
            $cdnl = $_POST['cdnl'];
            $height = $_POST['height'];
            $width = $_POST['width'];
            //$fmsPort = $_POST['fmsPort'];
            $sql = "update ws_siteconfig set cdn='{$cdn}',fps='{$fps}',zddk='{$zddk}',pz='{$pz}',zjg='{$zjg}',cdnl='{$cdnl}',height='{$height}',width='{$width}' where sconfigid=1";
            D('Siteconfig')->execute($sql);
           // $this->assign('jumpUrl', __URL__ . '/sys_config/');
            $this->success();
        }
    }
}