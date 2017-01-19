<?php
namespace Home\Model;

class CountryModel extends BaseModel
{
    public function getCountryByLan($language, $lantype='en'){
        
        $where = array(
            'language' => $language,
            'lantype' => $lantype
        );
        
        $field = array (
            'countryid', 'countryno', 'countrycode', 'countryname'
        );
        
        return $this->where($where)->field($field)->find();
    }
}

?>