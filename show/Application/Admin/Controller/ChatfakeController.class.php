<?php
namespace Admin\Controller;
use Think\Controller;

class ChatfakeController extends Controller 
{
    public function index() {
        $enterroomno = $_POST["enterroomno"];
        if(!$enterroomno){
            $enterroomno = rand(100000001, 100030001);
        }
        $assign['enterroomno'] = $enterroomno;
        $this->assign($assign);
        
        $this->display();
    }
    
    public function getRandomMember(){
        
        $enterroomno = $_POST["enterroomno"];
        $limitno = $_POST["limitno"];
        
        if(!$limitno){
            $limitno = rand(30, 80);
        }
        
        $data['members'] = D('Member')->getRandomMembers($enterroomno, $limitno);
        //echo count($data['members']);
        echo json_encode($data);
    }
}

?>