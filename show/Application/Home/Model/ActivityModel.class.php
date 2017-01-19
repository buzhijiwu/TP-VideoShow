<?php
namespace Home\Model;

class ActivityModel extends BaseModel
{
    public function getActivitys($type, $lantype='en'){
        
        $where = array(
            'type' => $type,
            'lantype' => $lantype
        );
        
        $activitys = $this->where($where)->order('sort ASC')->select();

        foreach ($activitys as $k => $v) {
            if ($v['linkurl'] == '') {
                $activitys[$k]['linkurl'] = U('Home/Activity/activityinfo/activityid/'.$v['activityid']);
            }
        }
        return $activitys;
    }
}

?>