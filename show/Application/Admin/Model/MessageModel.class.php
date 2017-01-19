<?php
namespace Admin\Model;

class MessageModel extends AdminModel
{
    public function SendMessageToUser($MessageData)
    {
        $this->add($MessageData);
    }
}

?>