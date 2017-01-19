<?php
namespace Home\Controller;



class AnnounceController extends CommonController
{
    public function index()
    {
        $this->lanType = getLanguage();
        $Db_announce = D('Announce');
        $announceid = I('get.announceid',0);
        $annwhere = array(
            'announceid'  => $announceid,
        );        
        $announceinfo = $Db_announce->where($annwhere)->find();
        if($announceinfo)
        {
            $this->assign('announceinfo', $announceinfo);
        }
        else
        {
            $this->error(lan('CAN_NOT_FIND_THIS_RECORD', 'Home'));
        }

        $this->display();
    }

}

