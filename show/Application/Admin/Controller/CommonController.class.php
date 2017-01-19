<?php
namespace Admin\Controller;
use Think\Controller;

class CommonController extends Controller {
	public function _initialize() {
		if(!session('adminid')) {
			redirect(U('Login/index'));
			die;
		}
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
		//p($info);
		if(!$info) {// 上传错误提示错误信息
			//$this->error($upload->getError());
			die($upload->getError());
		} else { // 上传成功 获取上传文件信息
			$data = array (
	  			'url' => '/Uploads/'.$info['imgFile']['savepath'].$info['imgFile']['savename'],
	  			'error' => 0
 			);
 			//$data['url'] = trim($data['url'],'.');
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
				$this->error('添加失败，请重试');
			} else {
				$this->success('添加成功',$successUrl);
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

	public function delRecordByIdForWeb()
	{
		$recordId = I('get.id');
		$name = I('get.name');
		$TabelNames = C('MYDB_TABEL_NAMES');
		$modelName = $TabelNames[$name];

		$this->delRecordById($recordId, $modelName);
	}

	/**
	 * @param $delIds
	 * @param $ModelName
	 */
	protected function delMultiRecord($delIds, $modelName)
	{
		$num = count($delIds);
		$model = D($modelName);

		for ($i = 0; $i < $num; $i++)
		{
			$recordInfo = $model->find($delIds[$i]);

			if ($recordInfo)
			{
				$model->delete($delIds[$i]);
			}
		}

		$this->success(lan('OPERATION_SUCCESSFUL', 'Admin'));
	}

	/**
	 * @param $recordId
	 * @param $modelName
	 */
	protected function delRecordById($recordId, $modelName)
	{
		if ($recordId == '') {
			$this->error(lan('PARAM_ERROR', 'Admin'));
		} else {
			$dao = D($modelName);
			$delInfo = $dao->find($recordId);

			if ($delInfo) {
				$dao->delete($recordId);
				$this->success(lan('OPERATION_SUCCESSFUL', 'Admin'));
			} else {
				$this->error(lan('CAN_NOT_FIND_THIS_RECORD', 'Admin'));
			}
		}
	}
}