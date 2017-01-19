<?php
/**
 * 公告管理
 */
namespace Admin\Controller;
use Think\Controller;
use Think\Upload;
class AnnounceController extends CommonController
{
    public function admin_announce()
    {
        $condition = array(
            'lantype' => getLanguage()
        );
        $orderby = 'createtime desc';
        $announce = D("Announce");
        $count = $announce->where($condition)->count();
        $pagesize =20;

        if($_POST['pageno'] != ''){
            $pageno = $_POST['pageno'];
        }
        if($_POST['pagesize'] != ''){
            $pagesize = $_POST['pagesize'];
        };

        $page = getpage($count,$pagesize);
        $announces = $announce->limit($page->firstRow.",".$page->listRows)->where($condition)->order($orderby)->select();
        $this->assign('page',$page->show());
        $this->assign('announces',$announces);

        $this->display();
    }

    public function add_announce(){
        //查询出当前所有的分类
        $this->display();
    }

    public function do_add_announce()
    {
        $announce=D("Announce");
        if(!empty($_POST)){
            $data = $_POST;
            $vo = $announce->create($data);
            //$announce->imagesrc = $info['imagesrc']['savepath'].$info['imagesrc']['savename'];
            $data['lantype'] = getLanguage();
            $data['createtime'] = date("Y-m-d H:i:s" ,time());

            if(!$vo)
            {
                $this->error($announce->getError());
            }
            else
            {
                $announceid = $announce->add($data);
                $announceCond = array(
                    'announceid' => $announceid
                );
                $announceurl['url'] = '/Announce/index/announceid/' . $announceid;
                $announce->where($announceCond)->save($announceurl);

            }
        }

        $this->success();
    }

    public function edit_announce()
    {
        $announceId = I('get.announceid');

        if($announceId == '')
        {
            $this->error(lan('PARAM_ERROR', 'Admin'));
        }
        else
        {
            $anninfo = D("Announce")->find($announceId);

            if($anninfo)
            {
                $this->assign('anninfo', $anninfo);
            }
            else
            {
                $this->error(lan('CAN_NOT_FIND_THIS_RECORD', 'Admin'));
            }
        }

        $this->display();
    }

    public function do_edit_announce()
    {
        $announceId = I('post.announceid');
        if('' == $announceId)
        {
            $this->error(lan('PARAM_ERROR', 'Admin'));
        }
        else{
            $anninfo = D("Announce")->find($announceId);
            if(!$anninfo)
            {
                $this->error(lan('CAN_NOT_FIND_THIS_RECORD', 'Admin'));
            }
        }

        $announce=D("Announce");
        $data = $_POST;
        $vo = $announce->create($data);

        if(!$vo)
        {
            $this->error($announce->getError());
        }
        else
        {
            $announce->save($data);
        }
        $this->assign('jumpUrl', '/Admin/Announce/admin_announce/');
        $this->success();
    }

    public function del_announce()
    {
        $announceId = I('get.announceid');

        if('' == $announceId)
        {
            $this->error(lan('PARAM_ERROR', 'Admin'));
        }
        else
        {
            $dao = D("Announce");
            $anninfo = $dao->find($announceId);

            if($anninfo)
            {
                $dao->where('announceid='.$announceId)->delete();
                $this->success();
            }
            else
            {
                $this->error(lan('CAN_NOT_FIND_THIS_RECORD', 'Admin'));
            }
        }
    }

    public function del_multi_announce()
    {
        $dao = D("Announce");

        if(is_array(I('request.ids')))
        {
              $array = I('request.ids');
              $num = count($array);
              for($i=0;$i<$num;$i++)
              {
                    $anninfo = $dao->getById($array[$i]);

                    if($anninfo){
                        $dao->where('announceid='.$array[$i])->delete();
                    }
              }
        }

        $this->success();
    }
}

