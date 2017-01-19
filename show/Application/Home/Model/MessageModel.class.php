<?php
namespace Home\Model;

class MessageModel extends BaseModel
{
    public $messagefields = array('messageid', 'content', 'title', 'createtime', 'read');
    
    public function getAllMessagesByPage($messageCond, $page)
    {
        $messages = $this->where($messageCond)->field($this->messagefields)->limit($page->firstRow.",".$page->listRows)->order('createtime desc')->select();

        return $messages;
    }
    
    public function SendMessageToUser($MessageData)
    {
        $this->add($MessageData);
    }
}

?>