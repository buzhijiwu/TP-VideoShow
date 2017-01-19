<?php
namespace Admin\Controller;

class SystemSetController extends CommonController {
    //获取系统配置信息
	public function index() {
		$db_systemset = M('systemset');
        $where['lantype'] = getLanguage();
		$result = $db_systemset->where($where)->select();
		$this->assign('list',$result);
		$this->display();
	}
    //修改配置信息
    public function update(){
        $key = I('get.key');
        $db_systemset = M('systemset');
        if(IS_POST){
            $value = I('post.value');
            $remark = I('post.remark');
            if($key){
                $data = array(
                    'value' => $value,
                    'remark' => $remark
                );
                $result = $db_systemset->where(array('key'=>$key))->save($data);
            }else{
                $language = array('zh','en','vi');
                $data = array();
                foreach($language as $k => $v){
                    $data[] = array(
                        'value' => $value,
                        'remark' => $remark,
                        'lantype' => $v
                    );
                }
                $result = $db_systemset->addAll($data);
            }
            if($result !== false){
                $this->success('',U('Admin/SystemSet/index'));exit;
            }else{
                $this->error();exit;
            }
        }
        $result = array();
        if($key){
            $where['key'] = $key;
            $where['lantype'] = getLanguage();
            $result = $db_systemset->where($where)->find();
        }
        $this->assign('info',$result);
        $this->display();
    }

    /**
     * 系统消息自定义
     */
    public function sys_message(){
        $lantype = getLanguage();
        $whereSys = array(
            'key' => 'NODEJS_PATH',
            'lantype' => $lantype
        );
        $chatNodePath = M('Systemset')->where($whereSys)->getField('value');

        $this->assign('chatNodePath',$chatNodePath);          
        $this->display();
    }

    /**
     * 友盟系统消息
     */
    public function umeng_sys_message(){
        require_once('./umeng/index.php');

        //通知内容定义
        $umeng_sysmessage = I('post.umeng_sysmessage');
        if(!$umeng_sysmessage){
            $this->error();exit;
        }

        //查询配置，定义正式模式还是测试模式
        $dbSystemset = M('Systemset');
        $production_mode = $dbSystemset->where(array('key' => 'UMENG_EMCEE_ONLINE_NOTICE_MODE','lantype' => 'vi'))->getField('value');
        if(!$production_mode || $production_mode !== 'true'){
            $production_mode = 'false';
        }

        //通知类型
        $type = I('get.type');

        switch($type){
            case '1':   //tag模式通知
                //安卓通知
                $AppKey_Android = $dbSystemset->where(array('key' => 'UMENG_APPKEY_ANDROID','lantype' => 'vi'))->getField('value');
                $AppMasterSecret_Android = $dbSystemset->where(array('key' => 'UMENG_APPMASTERSECRET_ANDROID','lantype' => 'vi'))->getField('value');
                $umeng_android = new \Demo($AppKey_Android,$AppMasterSecret_Android);
                $result_android = $umeng_android->sendAndroidGroupcastSystem($production_mode,'systemMessageTag',$umeng_sysmessage);

                //IOS通知
                $AppKey_IOS = $dbSystemset->where(array('key' => 'UMENG_APPKEY_IOS','lantype' => 'vi'))->getField('value');
                $AppMasterSecret_IOS = $dbSystemset->where(array('key' => 'UMENG_APPMASTERSECRET_IOS','lantype' => 'vi'))->getField('value');
                $umeng_ios = new \Demo($AppKey_IOS,$AppMasterSecret_IOS);
                $result_ios = $umeng_ios->sendIOSGroupcastSystem($production_mode,'systemMessageTag',$umeng_sysmessage);
                break;
            default:    //通知所有客户端
                //安卓通知
                $AppKey_Android = $dbSystemset->where(array('key' => 'UMENG_APPKEY_ANDROID','lantype' => 'vi'))->getField('value');
                $AppMasterSecret_Android = $dbSystemset->where(array('key' => 'UMENG_APPMASTERSECRET_ANDROID','lantype' => 'vi'))->getField('value');
                $umeng_android = new \Demo($AppKey_Android,$AppMasterSecret_Android);
                $result_android = $umeng_android->sendAndroidBroadcast($production_mode,$umeng_sysmessage);

                //IOS通知
                $AppKey_IOS = $dbSystemset->where(array('key' => 'UMENG_APPKEY_IOS','lantype' => 'vi'))->getField('value');
                $AppMasterSecret_IOS = $dbSystemset->where(array('key' => 'UMENG_APPMASTERSECRET_IOS','lantype' => 'vi'))->getField('value');
                $umeng_ios = new \Demo($AppKey_IOS,$AppMasterSecret_IOS);
                $result_ios = $umeng_ios->sendIOSBroadcast($production_mode,$umeng_sysmessage);
                break;
        }

        $this->success();exit;
    }
}