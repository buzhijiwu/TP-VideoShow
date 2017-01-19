<?php
namespace Admin\Model;
use Think\Model\ViewModel;

/**
 * Class MenuViewModel
 * @description 根据权限配置查询当前操作员可以展现的菜单
 * @package Admin\Model
 */
class MenuViewModel extends ViewModel {

//    protected $connection = array (
//        'db_type'  => 'mysql',
//        'db_user'  => 'root',
//        'db_pwd'   => 'xlingmao',
//        'db_host'  => '192.168.10.227',
//        'db_name'  => 'waashowdata',
//    );

    public $viewFields = array (
        'Menu' => array (
            'menuid',
            'parentid',
            'menuname',
            'url',
            'sort',
        ),
        'Auth' => array(
            'roleid',
            '_on' => 'Auth.menuid = Menu.menuid',
        ),
    );
}