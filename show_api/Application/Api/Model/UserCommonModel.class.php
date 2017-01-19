<?php
namespace Api\Model;
use Think\Model;

class UserCommonModel extends Model {
	protected $connection = array (
		'db_type'  => 'mysql',
		'db_user'  => 'root',
		'db_pwd'   => 'xlingmao',
		'db_host'  => '192.168.10.227',
		'db_name'  => 'waashowuser',
	);
}