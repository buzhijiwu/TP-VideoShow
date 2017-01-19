<?php
namespace Home\Model;

class SellerModel extends BaseModel
{
    public $sellerfields = array('sellerid', 'sellername','pclogopath', 'applogopath','sellerdesc');
    
    public function getSellers($chuniqueid){
        $sellerCond = array(
            'chuniqueid' => $chuniqueid,
        );

        $sellers = $this->where($sellerCond)->field($this->sellerfields)->order('sort')->select();
        return $sellers;
    }

}

?>