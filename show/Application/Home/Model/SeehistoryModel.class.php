<?php
namespace Home\Model;

class SeehistoryModel extends BaseModel
{
    public function getSeeHisEmceesByPage($queryCond, $page){
        $seehisemcees = $this->where($queryCond)->order('lastseetime desc')->limit($page->firstRow.",".$page->listRows)->group('liveid')->select();
        $dMember = D('Member');
        $db_Emceeproperty = D('Emceeproperty');
        foreach ($seehisemcees as $k=>$v) {
            $memberinfor = $dMember->getSimpleMemberInfoByUserId($v['emceeuserid']);
            $emceeCond = array(
                'userid' => $v['emceeuserid']
            );
            $emceeinfor = $db_Emceeproperty->getEmceeProInfo($emceeCond);
            $seehisemcees[$k] = array_merge($seehisemcees[$k], $memberinfor);

            if ($emceeinfor)
            {
                $seehisemcees[$k] = array_merge($seehisemcees[$k], $emceeinfor);
            }
        }
        return $seehisemcees;
    }
}

?>