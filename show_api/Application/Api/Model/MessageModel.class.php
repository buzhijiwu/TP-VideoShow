<?php
namespace Api\Model;

class MessageModel extends BaseModel
{
    public function SendMessageToUser($MessageData)
    {
        $this->add($MessageData);
    }    
}

?>