<?php
namespace Home\Controller;

class AgreementController extends CommonController {
	public function index() {
		$template = I('get.template');
		$this->display($template);
	}
}