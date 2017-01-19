<?php
namespace Operator\Controller;
use Think\Controller;

class CommonController extends Controller {
	public function _initialize() {
		if(!session('operatorid')) {
			redirect(U('Index/login'));
			die;
		}
	}
	
}