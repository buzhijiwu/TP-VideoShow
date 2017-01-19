<?php
/**
 * 直播服务器设置
 */
namespace Admin\Controller;
use Think\Controller;
class PlayServerConfigController extends CommonController
{
    public function playserver_config()
    {
        $servers = D("server")->where("")->order('createtime')->select();

        $this->assign("servers",$servers);

        $this->display();
    }

    public function do_add_server()
    {
        if($_POST['servername'] == '')
        {
            $this->error('服务器名称不能为空');
        }

        if($_POST['serverip'] == '')
        {
            $this->error('访问域名或IP不能为空');
        }

        if($_POST['fmsport'] == '')
        {
            $this->error('端口号不能为空');
        }


        $server = D('Server');
        $vo = $server->create();
        if(!$vo)
        {
            $this->error($server->getError());
        }else
        {
            $server->createtime = date("Y-m-d H:i:s" ,time());
            $server->add();
            //$this->assign('jumpUrl',__URL__.'/playserver_config/');
            $this->success('添加成功');
        }
    }

    public function edit_server()
    {
        header("Content-type: text/html; charset=utf-8");
        if($_GET['serverid'] == ''){
            echo '<script>alert(\'参数错误\');window.top.right.location.reload();window.top.art.dialog({id:"edit"}).close();</script>';
        }
        else{
            $serverinfo = D("Server")->find($_GET["serverid"]);
            if($serverinfo){
                $this->assign('serverinfo',$serverinfo);
            }
            else{
                echo '<script>alert(\'找不到该服务器\');window.top.right.location.reload();window.top.art.dialog({id:"edit"}).close();</script>';
            }
        }
        $this->display();
    }

    public function do_edit_server(){
        header("Content-type: text/html; charset=utf-8");

        $server = D('Server');
        $vo = $server->create();
        if(!$vo) {
            echo '<script>alert(\''.$server->getError().'\');window.top.art.dialog({id:"edit"}).close();</script>';
        }else{
            if($_POST['updteAll']=="y")
            {
                D("Emceeproperty")->where("1=1")->save(array("serverip"=>$_POST['serverip'],"fmsport"=>$_POST['fmsport']));
            }
            else
            {
                $old_serverip =  $_POST["old_serverip"];
                D("Emceeproperty")->where("serverip='$old_serverip'")->save(array("serverip"=>$_POST['serverip'],"fmsport"=>$_POST['fmsport']));
            }

            $server->save();
            echo '<script>alert(\'修改成功\');window.top.right.location.reload();window.top.art.dialog({id:"edit"}).close();</script>';
        }
    }

    public function del_server(){
        if($_GET["serverid"] == '')
        {
            $this->error('缺少参数或参数不正确');
        }
        else{
            $dao = D("Server");
            $serverinfo = $dao->find($_GET["serverid"]);
            if($serverinfo){
                $dao->where('serverid='.$_GET["serverid"])->delete();
                //$this->assign('jumpUrl',__URL__.'/admin_rtmpserver/');
                $this->success('成功删除');
            }
            else{
                $this->error('找不到该服务器');
            }
        }
    }
}