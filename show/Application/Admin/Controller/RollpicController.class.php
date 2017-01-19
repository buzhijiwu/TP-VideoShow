<?php
/**
 * 轮显设置
 */
namespace Admin\Controller;
use Think\Controller;
use Think\Upload;

class RollpicController extends CommonController {
	/*
	** 方法作用：首页轮播图显示 (PC端)
	** 参数1：[无]
	** 返回值：[无]
	** 备注：[无]
	 */
	public function admin_rollpic() {
		$this->lanType = getLanguage();
		$Db_rollpic = D("Rollpic");
			$result = $this->_rollpic($Db_rollpic,'PC_INDEX','Rollpic/admin_rollpic',$this->lanType);
		if($result=='ajax' || $result=='post') die;
		$assign = array(
			'rollpics' => $result,
		);
		$this->assign($assign);
		$this->display();
	}

	/*
	** 方法作用：PC端秀场轮播图设置
	** 参数1：[无]
	** 返回值：[无]
	** 备注：[无]
	 */
	public function show_rollpic() {
		$this->lanType = getLanguage();
		$Db_rollpic = D("Rollpic");
			$result = $this->_rollpic($Db_rollpic,2,'Rollpic/show_rollpic',$this->lanType);
		if($result=='ajax' || $result=='post') die;
		$assign = array(
			'rollpics' => $result,
		);
		$this->assign($assign);
		$this->display();
	}
	
	/*
	** 方法作用：手机端轮播图设置
	** 参数1：[无]
	** 返回值：[无]
	** 备注：[无]
	 */
	public function phoneRollpic() {
		$assign = $this->_publicRollpic('PHONE_INDEX');
		$this->assign($assign);
		$this->display();
	}
	
	/*
	** 方法作用：PC端轮播图设置
	** 参数1：[无]
	** 返回值：[无]
	** 备注：[无]
	 */
	public function pcRollpic() {
		$assign = $this->_publicRollpic('PC_INDEX');
		$this->assign($assign);
		$this->display();
	}
	
	/*
	** 方法作用：PC端轮播图和手机端轮播图公用代码
	** 参数1：[无]
	** 返回值：[无]
	** 备注：[无]
	 */
	private function _rollpic($Db_rollpic,$type='PC_INDEX',$url='Rollpic/admin_rollpic',$lantype='en') {
		if(IS_AJAX) {
			if(I('action')=='del') {
				if($Db_rollpic->delete(I('id','0','intval'))) echo 'ok';
				else echo 'error';
				return 'ajax';
			}
			if(I('action')=='edit') {
				$data = array(
					'rollpicid' => I('id','0','intval'),
					'picpath' => I('picpath','','trim'),
				);
				if($Db_rollpic->save($data)!==false) echo 'ok';
				else echo 'error';
				return 'ajax';
			}
			return 'ajax';
		}
		if(IS_POST) {
			if(!in_array($lantype,C('LAN_TYPE'))) {
				$this->error(lan('PARAM_ERROR', 'Admin'));
				die;
			}
			if(!$Db_rollpic->addDate($lantype,$type)) {
				$this->error(lan('PLZ_TRY_AGAIN', 'Admin'));
				return 'post';
			}
			$this->success('',$_SERVER['HTTP_REFERER']);
			return 'post';
		}
		if(!in_array($lantype,C('LAN_TYPE'))) $lantype = 'en';
		$where = array('type'=>$type,'lantype'=>$lantype);
		$rollpics = $Db_rollpic->where($where)->order('sort')->select();
		return $rollpics;
	}
	
	private function _publicRollpic($type,$url) {
		$Db_rollpic = D("Rollpic");
		$lantype = isset($_GET['lantype']) ? I('GET.lantype','en','trim') : 'en';
		$result = $this->_rollpic($Db_rollpic,$type,$url,$lantype);
		if($result=='ajax' || $result=='post') die;
		$assign = array (
			'rollpics' => $result,
			'lantype' => $lantype,
		);
		return $assign;
	}


	/*
	** 方法作用：上传图片
	** 参数1：[无]
	** 返回值：[无]
	** 备注：[无]
	 */
	public function upimg() {
		$upload = new \Think\Upload();// 实例化上传类

		$upload->maxSize = 1024*420;// 设置附件上传大小 [最大420KB]
		$upload->exts = array('jpg', 'gif', 'png', 'jpeg');// 设置附件上传类型
		$upload->autoSub = false;
		$upload->savePath = date('Y_m').'/';
		$info = $upload->upload();
		if(!$info) {// 上传错误提示错误信息
            switch ($upload->getError()) {
                case '没有上传的文件！':
                    die(lan('NO_UPLOAD_FILES', 'Admin'));
                    break;
                case '非法上传文件！':
                    die(lan('ILLEGAL_UPLOAD_FILES', 'Admin'));
                    break;
                case '上传文件大小不符！':
                    die(lan('FILES_SIZES_NOT_MATCH', 'Admin'));
                    break;   
                case '上传文件MIME类型不允许！':
                    die(lan('MIME_TYPE_NOT_ALLOWED', 'Admin'));
                    break;   
                case '上传文件后缀不允许':
                    die(lan('SUFFIX_NOT_ALLOWED', 'Admin'));
                    break;   
                case '非法图像文件！':
                    die(lan('ILLEGAL_IMAGE_FILES', 'Admin'));
                    break;                                                                            
                default:
                    die(lan('UNKNOWN_UPLOAD_ERROR', 'Admin'));	
            }			
		} else { // 上传成功 获取上传文件信息
			$data = array (
					'url' => '/Uploads/'.$info['imgFile']['savepath'].$info['imgFile']['savename'],
					'error' => 0
			);
			echo json_encode($data);
		}
	}

	/*
	** 方法作用：在修改的时候的公共处理函数
	** 参数1：[无]
	** 返回值：[无]
	** 备注：[无]
	 */
	protected function yz($str) {
		echo '<script>alert(\''.$str.'\');window.top.right.location.reload();window.top.art.dialog({id:"edit"}).close();</script>';
		die;
	}


	/*
	** 方法作用：增加表单时候的公共函数
	** 参数1：[无]
	** 返回值：[无]
	** 备注：insertField   addValidate
	 */
	protected function addForm($model,$successUrl) {
		$Db_model = $model;
		$field = $Db_model->insertField;
		$rule = $Db_model->addValidate();

		$result = $Db_model->field($field)->validate($rule)->create();
		if(!$result) {
			$this->error($Db_model->getError());
		} else {
			if(!$Db_model->add()) {
				$this->error(lan('PLZ_TRY_AGAIN', 'Admin'));
			} else {
				$this->success(lan('LAN_DO_SUCCESS', 'Admin'),$successUrl);
			}
		}
	}


	/*
	** 方法作用：清除所有缓存
	** 参数1：[无]
	** 返回值：[无]
	** 备注：[无]
	 */
	public function delCache() {
		if(IS_AJAX) {
			if($_POST['action']=='delCache') {
				if(del_rs()) echo 'ok';
				else echo 'error';
				die;
			}
			die;
		}
	}

    public function edit_rollpic(){
        $rollpicid = I('get.rollpicid');
        if ($rollpicid>0) {
        	$rollinfo = M('Rollpic')->find($rollpicid);
        	$this->assign('data',$rollinfo);
        }
        $this->assign('type',I('get.type',0,'trim'));
        $this->display();
    }

    public function do_edit_rollpic()
    {
    	$this->lanType = getLanguage();
        $rollpic=D("Rollpic");
        if(!empty($_POST)){
            //设置文件上传位置
            if ($_POST['type'] == 2) {
                $savePath = "/Uploads/Market/pc/show/";
            }else{
                $savePath = "/Uploads/Market/pc/";
            }
            //文件上传远程服务器
            $ftpFile = ftpFile('picpath',$savePath);
            $picpath = '';
            if($ftpFile['code'] == 200){
                $picpath = $ftpFile['msg'];
            }
            $data = $_POST;
            $vo = $rollpic->create($data);
            $data['picpath'] = $picpath;
            $data['createtime'] = date("Y-m-d H:i:s" ,time());

            if(!$vo){
                $this->error($rollpic->getError());
            }else{
            	$rollpicid = I('POST.rollpicid');
            	$rollinfo = M('Rollpic')->find($rollpicid);
            	if ($rollinfo['picpath'] != '' && $picpath == '') {
            		$data['picpath'] = $rollinfo['picpath'];
            	}

            	if ($rollinfo) {
            		$rollpic->save($data);
            	}else{
            		$data['lantype'] = getLanguage();
            		// dump($data);die;
            		$rollpic->add($data);
            	}
            }
        }
        if ($data['type'] == 0) {
        	$url = U('/Admin/Rollpic/admin_rollpic');
        }
        elseif($data['type'] == 2) {
            $url = U('/Admin/Rollpic/show_rollpic');
        }
        $this->success('',$url);
    }

    public function del_rollpic()
    {
        $rollpicId = I('get.rollpicid');

        if('' == $rollpicId)
        {
            $this->error(lan('PARAM_ERROR', 'Admin'));
        }
        else
        {
            $dao = D("Rollpic");
            $rollinfo = $dao->find($rollpicId);

            if($rollinfo)
            {
                $dao->where('rollpicid='.$rollpicId)->delete();
                $this->success();
            }
            else
            {
                $this->error(lan('CAN_NOT_FIND_THIS_RECORD', 'Admin'));
            }
        }
    }

    public function del_multi_rollpic()
    {
        $dao = D("Rollpic");

        if(is_array(I('request.ids')))
        {
              $array = I('request.ids');
              $num = count($array);
              for($i=0;$i<$num;$i++)
              {
                    $rollinfo = $dao->getById($array[$i]);

                    if($rollinfo){
                        $dao->where('rollpicid='.$array[$i])->delete();
                    }
              }
        }

        $this->success();
    }    	
}