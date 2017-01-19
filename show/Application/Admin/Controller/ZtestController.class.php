<?php
/**
 * @author 狼一
 * @brief 测试控制器，整理菜单接口设置私有属性
 */
namespace Admin\Controller;
use Think\Controller;
class ZtestController extends Controller {
	public function index() {
        $url = 'http://'.$_SERVER['SERVER_NAME'].'/Admin/Ztest/test';
        $this->makeRequest($url);
        echo $url;exit;
    }
    public function makeRequest($url){
        $cmd = "curl '" . $url . "' > /dev/null 2>&1 &";    //让进程正确的执行 ，而把任何可能输出都丢弃掉。
        exec($cmd, $output, $exit);
        return $exit == 0;
    }
    public function test(){
        file_put_contents(__ROOT__."Data/DB_Backup/test-.log",'',FILE_APPEND);
    }

    /*
	** 功能：整理数据库菜单，更新ws_menu和ws_auth表
	** 步骤一：首先将ws_menu表名称修改成ws_menu_bak，ws_auth表修改成ws_auth_bak。
	** 步骤二：然后新增ws_menu表ws_auth表，表结构和之前一致，然后运行该方法。
	** 步骤三：测试菜单是否整理完成，如果整理完成，可删除ws_menu_bak和ws_auth_bak表（确保整理完成，才能删除）
	** 备注：如果整理失败，可以清空新增的ws_menu和ws_auth表，重新整理；也可以调整代码里的生成逻辑，重新整理。
	 */
    private function menu_update(){
        ini_set('max_execution_time', '0');
        ini_set('memory_limit','512M');

        $this->home_menu_update();
        echo date('Y-m-d H:i:s')."<br/>";

        $this->admin_menu_update();
        echo date('Y-m-d H:i:s')."<br/>";

        $this->menu_auth_update();
        echo date('Y-m-d H:i:s')."<br/>";exit;
    }

    //前台菜单整理
    private function home_menu_update(){
        $where['parentid'] = '-1';
        $list = M('menu_bak')->where($where)->order('sort asc ,lantype desc')->select();
        foreach($list as $key => $val){
            $menu_data['menuid'] = $val['menuid'];
            $menu_data['menukey'] = $val['menukey'];
            $menu_data['parentid'] = $val['parentid'];
            $menu_data['lantype'] = $val['lantype'];
            $menu_data['menuname'] = $val['menuname'];
            $menu_data['position'] = $val['position'];
            $menu_data['url'] = $val['url'];
            $menu_data['sort'] = $val['sort'];
            $menu_data['menutype'] = -1;
            $menu_data['createtime'] = date('Y-m-d H:i:s');
            M('menu')->add($menu_data);
        }
    }

    //后台菜单整理
    private function admin_menu_update(){
        //生成特定数组
        $where_1['parentid'] = 0;
        $result_1 = M('menu_bak')->where($where_1)->order('menuid asc,lantype desc,sort asc')->select();
        $result = array();
        foreach($result_1 as $key_1 => $val_1){
            $result[$val_1['menuid']][$val_1['lantype']] = $val_1;

            $where_2['parentid'] = $val_1['menuid'];
            $result_2 = M('menu_bak')->where($where_2)->order('menuid asc,lantype desc,sort asc')->select();
            foreach($result_2 as $key_2 => $val_2){
                $result[$val_1['menuid']]['list'][$val_2['menuid']][$val_2['lantype']] = $val_2;

                $where_3['parentid'] = $val_2['menuid'];
                $result_3 = M('menu_bak')->where($where_3)->order('menuid asc,lantype desc,sort asc')->select();
                foreach($result_3 as $key_3 => $val_3){
                    $result[$val_1['menuid']]['list'][$val_2['menuid']]['list'][$val_3['menuid']][$val_3['lantype']] = $val_3;
                }
            }
        }

        $type = array('zh','en','vi');
        //添加一级菜单
        $i = 0;
        foreach($result as $k1 => $v1){
            $menuid_1 = 1001+$i;
            foreach($type as $lantype){
                $data[$k1][$lantype]['menuid'] = $menuid_1;
                $data[$k1][$lantype]['parentid'] = 0;
                $data[$k1][$lantype]['menutype'] = 1;
                $data[$k1][$lantype]['menukey'] = $v1[$lantype]['menukey'];
                $data[$k1][$lantype]['lantype'] = $v1[$lantype]['lantype'];
                $data[$k1][$lantype]['menuname'] = $v1[$lantype]['menuname'];
                $data[$k1][$lantype]['position'] = $v1[$lantype]['position'];
                $data[$k1][$lantype]['url'] = $v1[$lantype]['url'];
                $data[$k1][$lantype]['sort'] = $v1[$lantype]['sort'];
                $data[$k1][$lantype]['createtime'] = date('Y-m-d H:i:s');
                M('menu')->add($data[$k1][$lantype]);
            }
            $i++;
        }
        //添加二级菜单
        $i = 0;
        $j = 0;
        foreach($result as $k1 => $v1){
            $menuid_1 = 1001+$i;
            foreach($v1['list'] as $k2 => $v2){
                $menuid_2 = 2001+$j;
                foreach($type as $lantype){
                    $data[$k2][$lantype]['menuid'] = $menuid_2;
                    $data[$k2][$lantype]['parentid'] = $menuid_1;
                    $data[$k2][$lantype]['menutype'] = 2;
                    $data[$k2][$lantype]['menukey'] = $v2[$lantype]['menukey'];
                    $data[$k2][$lantype]['lantype'] = $v2[$lantype]['lantype'];
                    $data[$k2][$lantype]['menuname'] = $v2[$lantype]['menuname'];
                    $data[$k2][$lantype]['position'] = $v2[$lantype]['position'];
                    $data[$k2][$lantype]['url'] = $v2[$lantype]['url'];
                    $data[$k2][$lantype]['sort'] = $v2[$lantype]['sort'];
                    $data[$k2][$lantype]['createtime'] = date('Y-m-d H:i:s');
                    M('menu')->add($data[$k2][$lantype]);
                }
                $j++;
            }
            $i++;
        }
        //添加三级菜单
        $i = 0;
        $j = 0;
        $k = 0;
        foreach($result as $k1 => $v1){
            $menuid_1 = 1001+$i;
            foreach($v1['list'] as $k2 => $v2){
                $menuid_2 = 2001+$j;
                foreach($v2['list'] as $k3 => $v3){
                    $menuid_3 = 3001+$k;
                    foreach($type as $lantype){
                        $data[$k3][$lantype]['menuid'] = $menuid_3;
                        $data[$k3][$lantype]['parentid'] = $menuid_2;
                        $data[$k3][$lantype]['menutype'] = 3;
                        $data[$k3][$lantype]['menukey'] = $v3[$lantype]['menukey'];
                        $data[$k3][$lantype]['lantype'] = $v3[$lantype]['lantype'];
                        $data[$k3][$lantype]['menuname'] = $v3[$lantype]['menuname'];
                        $data[$k3][$lantype]['position'] = $v3[$lantype]['position'];
                        $data[$k3][$lantype]['url'] = $v3[$lantype]['url'];
                        $data[$k3][$lantype]['sort'] = $v3[$lantype]['sort'];
                        $data[$k3][$lantype]['createtime'] = date('Y-m-d H:i:s');
                        M('menu')->add($data[$k3][$lantype]);
                    }
                    $k++;
                }
                $j++;
            }
            $i++;
        }
    }

    //更新权限表
    private function menu_auth_update(){
        $auth_menu_list = M('menu')->distinct(true)->field('menuid')->select();
        //添加管理员权限
        foreach($auth_menu_list as $k => $v){
            $auth_data['roleid'] = 0;
            $auth_data['menuid'] = $v['menuid'];
            $auth_data['createtime'] = date('Y-m-d H:i:s');
            M('auth')->add($auth_data);
        }
        //添加用户权限
        foreach($auth_menu_list as $k => $v){
            $auth_data['roleid'] = 1;
            $auth_data['menuid'] = $v['menuid'];
            $auth_data['createtime'] = date('Y-m-d H:i:s');
            M('auth')->add($auth_data);
        }
    }
}