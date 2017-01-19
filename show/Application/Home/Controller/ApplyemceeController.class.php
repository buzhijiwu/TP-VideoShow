<?php
namespace Home\Controller;
use Think\Controller;

class ApplyemceeController extends CommonController {
    //申请签约，填写基本信息
	public function index() {
        $userinfo = array();
        $userid = session('userid');
        if($userid > 0) {
            $memberInfo = M("Member")->field('status, sex')->where('userid='.$userid)->find();
            if(session('signflag') == 2){
                redirect(U('/'.session('showroomno')));
            }elseif(session('signflag') == 1) {
                redirect(U('Home/Applyemcee/apply'));
            }
            $userinfo = M("Account")->where('userid='.$userid)->find();
            $userinfo['sex'] = $memberInfo['sex'];
        }

        if($userid > 0 &&IS_AJAX && IS_POST){
            $sex = I('post.sex', '', 'trim');
            $base64 = I('post.credentialspicurl');
            $Account_data['realname'] = I('post.realname', '', 'trim');
            $Account_data['mobileno'] = I('post.mobileno', '', 'trim');
            $Account_data['zalo'] = I('post.zalo', '', 'trim');
            $Account_data['facebook'] = I('post.facebook', '', 'trim');
            $Account_data['email'] = I('post.email', '', 'trim');
            $Account_data['address'] = I('post.address', '', 'trim');
            $Account_data['cardid'] = I('post.cardid', '', 'trim');
            if(!$Account_data['realname'] || !$Account_data['mobileno'] || !$Account_data['email'] || !$Account_data['address'] || !$Account_data['cardid'] || !$base64){
                $result_json = array(
                    'code' => 1,
                    'msg' => lan('PLZ_COMPLETE_FORM', 'Home'),
                );
                $this->ajaxReturn($result_json);
            }

            $base64_image = str_replace(' ', '+', $base64);
            //post的数据里面，加号会被替换为空格，需要重新替换回来，如果不是post的数据，则注释掉这一行
            if (preg_match('/^(data:\s*image\/(\w+);base64,)/', $base64_image, $result)){
                //匹配成功
                if($result[2] == 'jpeg'){
                    $image_name = 'idimg_'.$userid.'.jpg';
                }else{
                    $image_name = 'idimg_'.$userid.'.'.$result[2];
                }
                $image_file = '/Uploads/emcee/IDimg/'.$image_name;
                //服务器文件存储路径
                $uploadStatus = file_put_contents(".".$image_file, base64_decode(str_replace($result[1], '', $base64_image)));
                if (!$uploadStatus){    //上传失败
                    $result_json = array(
                        'code' => 1,
                        'msg' => lan('OPERATION_FAILED', 'Home').','.lan('PLZ_TRY_AGAIN', 'Home'),
                    );
                    $this->ajaxReturn($result_json);
                }else{
                    //文件上传远程服务器
                    $ftpUpload = ftpUpload($image_file, $image_file);
                    if($ftpUpload['code'] != 200){
                        $result_json = array(
                            'code' => 1,
                            'msg' => lan('OPERATION_FAILED', 'Home').','.lan('PLZ_TRY_AGAIN', 'Home'),
                        );
                        $this->ajaxReturn($result_json);
                    }
                }

                $Account_data['credentialspicurl'] = $image_file;
            }

            $Account_data['userid'] = $userid;
            $Account_data['createtime'] = date("Y-m-d H:i:s");
            if ($userinfo['accountid']){  //修改记录
                $result_sign = M("Account")->where('userid='.$userid)->save($Account_data);
            }else{  //新增记录
                $result_sign = M("Account")->add($Account_data);
            }

            if($result_sign === false){ //记录更新失败
                $result_json = array(
                    'code' => 1,
                    'msg' => lan('OPERATION_FAILED', 'Home').','.lan('PLZ_TRY_AGAIN', 'Home'),
                );
                $this->ajaxReturn($result_json);
            }

            $data_sex = array(
                'sex' => $sex,
            );
            M("Member")->where('userid='.$userid)->save($data_sex);

            $result_json = array(
                'code' => 0,
                'msg' => '',
            );
            $this->ajaxReturn($result_json);
        }

        // 模版赋值输出
        $this->assign('data',$userinfo);
        $this->assign('lantype',$this->lan);
        $this->display();
	}

    //申请签约信息
    public function apply(){
        $userinfo = array();
        $userid = session('userid');
        if ($userid > 0) {
            if(session('signflag') == 2){
                redirect(U('/'.session('showroomno')));
            }
            $userinfo = M("Account")->where('userid='.$userid)->find();
            $bigheadpic = M('member')->where('userid='.$userid)->getField('bigheadpic');
            if($bigheadpic){
                $userinfo['emceepic'] = $bigheadpic;
            }
        }

        if($userid > 0 && IS_AJAX && IS_POST){
            $base64 = I('post.emceepic');
            $Account_data['skill'] = implode(",",I('post.skill', '', 'trim'));
            $Account_data['livetime'] = I('post.livetime', '', 'trim');
            $Account_data['bankname'] = I('post.bankname', '', 'trim');
            $Account_data['bankaddress'] = I('post.bankaddress', '', 'trim');
            $Account_data['subbankname'] = I('post.subbankname', '', 'trim');
            $Account_data['bankno'] = I('post.bankno', '', 'trim');
            $Account_data['accountname'] = I('post.accountname', '', 'trim');

            if(!$Account_data['skill'] || !$Account_data['livetime'] || !$base64){
                $result_json = array(
                    'code' => 1,
                    'msg' => lan('PLZ_COMPLETE_FORM', 'Home'),
                );
                $this->ajaxReturn($result_json);
            }

            $base64_image = str_replace(' ', '+', $base64);
            //post的数据里面，加号会被替换为空格，需要重新替换回来，如果不是post的数据，则注释掉这一行
            if (preg_match('/^(data:\s*image\/(\w+);base64,)/', $base64_image, $result)){
                //匹配成功
                if($result[2] == 'jpeg'){
                    $image_name =  date('YmdHis').'_'.$userid.'.jpg';
                }else{
                    $image_name =  date('YmdHis').'_'.$userid.'.'.$result[2];
                }
                $image_file = '/Uploads/emcee/LivePoster/'.$image_name;
                //服务器文件存储路径
                $uploadStatus = file_put_contents(".".$image_file, base64_decode(str_replace($result[1], '', $base64_image)));
                if (!$uploadStatus){    //上传失败
                    $result_json = array(
                        'code' => 1,
                        'msg' => lan('OPERATION_FAILED', 'Home').','.lan('PLZ_TRY_AGAIN', 'Home'),
                    );
                    $this->ajaxReturn($result_json);
                }else{
                    //文件上传远程服务器
                    $ftpUpload = ftpUpload($image_file, $image_file);
                    if($ftpUpload['code'] != 200){
                        $result_json = array(
                            'code' => 1,
                            'msg' => lan('OPERATION_FAILED', 'Home').','.lan('PLZ_TRY_AGAIN', 'Home'),
                        );
                        $this->ajaxReturn($result_json);
                    }
                }
                $Account_data['emceepic'] = $image_file;
            }

            //添加申请资料
            $apply_time = date('Y-m-d H:i:s');
            $Account_data['createtime'] = $apply_time;
            $result_apply = M("Account")->where('userid='.$userid)->save($Account_data);
            if ($result_apply === false) {
                $result_json = array(
                    'code' => 1,
                    'msg' => lan('OPERATION_FAILED', 'Home').','.lan('PLZ_TRY_AGAIN', 'Home'),
                );
                $this->ajaxReturn($result_json);
            }
            $M_emceeproperty = M("emceeproperty");
            $Emceeproperty = $M_emceeproperty->where('userid='.$userid)->find();
            if(empty($Emceeproperty)){
                $server = M('Server')->where('isdefault=1')->find();
                $data_emceeproperty = array(
                    'serverip' => $server['serverip'], //取默认服务器
                    'fmsport' => $server['fmsport'],
                    'emceelevel' => 0,
                    'emceetype' => I('post.emceetype'),
                    'longitude' => rand(103, 110),//越南的经度范围
                    'latitude' => rand(10, 23),//越南的纬度范围
                    'audiencecount' => 0,//当前观看人数
                    'totalaudicount' => 0,//累计观看人数
                    'categoryid' => I('post.categoryid'),//主播类型
                    'applytime' => $apply_time,
                    'signflag' => 1,
                    'userid' => $userid,
                );
                $result_emceeproperty = $M_emceeproperty->add($data_emceeproperty);
                D('Seat')->updateEmceeSeat($userid);
                $member_data = array(
                    'isemcee' => 1,
                    'familyid' => 11,
                );
                M('Member')->where(array('userid'=>$userid))->save($member_data);
            }else{
                $data_emceeproperty = array(
                    'signflag' => 1,
                );
                $result_emceeproperty = $M_emceeproperty->where('userid='.$userid)->save($data_emceeproperty);
            }

            if($result_emceeproperty === false){    //申请资料更新失败
                $result_json = array(
                    'code' => 1,
                    'msg' => lan('OPERATION_FAILED', 'Home').','.lan('PLZ_TRY_AGAIN', 'Home'),
                );
                $this->ajaxReturn($result_json);
            }

            session('signflag',1);
            $result_json = array(
                'code' => 0,
                'msg' => '',
            );
            $this->ajaxReturn($result_json);
        }

        $skill_list = array(
            array('MC'),
            array('DJ'),
            array('搞笑','Funny','Hài hước'),
            array('唱歌','Sing','Ca há'),
            array('跳舞','Dance','Múa'),
            array('游戏','Game','Khác'),            
        );

        // 模版赋值输出
        $this->assign('skill_list',$skill_list);        
        $this->assign('data',$userinfo);
        $this->assign('lantype',$this->lan);
        $this->display();
    }
}