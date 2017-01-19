<?php
namespace Home\Controller;

class ShareController extends CommonController {
	public function index() {
		if (IS_POST && IS_AJAX) {
		    $dShare = M('Sharerecord');			
			$data['userid'] = I('post.userid');
			$data['emceeuserid'] = I('post.emceeuserid');
			$data['shareplat'] = I('post.shareplat');
			$data['sharetype'] = I('post.sharetype');
			$data['devicetype'] = I('post.devicetype');	
			$data['sharetime'] = date("Y-m-d H:i:s" ,time());	

            $this->share_judge($data['userid'],$data['emceeuserid'],$data['sharetype']);

			$dShare->create($data);	
            if(!$dShare->add($data)){
			    $res = array(
                    'status' => 0,
                    'message' => lan("OPERATION_FAILED", "Home"),
                );
                echo json_encode($res); 
                die;
			}else{
			    $res = array(
                    'status' => 1,
                    'message' => lan("OPERATION_SUCCESSFUL", "Home"),
                );
                echo json_encode($res); 
                die;				
			}			
        }		
	}

    public function share_judge() {
        $userid = I('post.userid');
        $emceeuserid = I('post.emceeuserid');
        $sharetype = I('post.sharetype');
        $is_judge = I('post.is_judge');

        $dShare = M('Sharerecord'); 
        $time = time()-3600; //当前时间减3600秒
        $queryCond = array(
            'userid' => $userid,
            'emceeuserid' => $emceeuserid,             
            'sharetime' => array('gt', date("Y-m-d H:i:s", $time)),
            'sharetype' => $sharetype,   
        );
        
        $hasrecord = $dShare->where($queryCond)->select();
        if ($hasrecord)
        {
            $res = array(
                'status' => 0,
                'message' => lan("ONE_VOTE_ONE_HOUR", "Home"),
            );                
            echo json_encode($res); 
            die;                
        }else{
            if ($is_judge == 1) {
                $emceeInfo = M('Member')->field('bigheadpic')->where('userid='.$emceeuserid)->find();
                $res = array(
                    'status' => 1,
                    'message' => 'can share',
                    'emceebigpic' => $emceeInfo['bigheadpic']
                );                
                echo json_encode($res); 
                die;                  
            }
        }        
    }

	public function share() {
		$url = $_SERVER['HTTP_HOST'].'/'.I('get.url');
		if ($this->ismobile()) {
            if(strpos($_SERVER['HTTP_USER_AGENT'], 'iPhone')||strpos($_SERVER['HTTP_USER_AGENT'], 'iPad')){ 
			    header("Location: https://itunes.apple.com/app/waashow-kenh-video-truc-tuyen/id1067265475?mt=8");
			    exit; 
            }else{ 
			    header("Location: http://waashow.vn/ApkDownload/Android/WaaShow_Idol.apk");
			    exit;                
            } 
		}else{
			header("Location: http://".$url);
			exit; 			
		}
	}

    function ismobile()
    {
        // 如果有HTTP_X_WAP_PROFILE则一定是移动设备
        if (isset($_SERVER['HTTP_X_WAP_PROFILE']))
            return true;
            // 此条摘自TPM智能切换模板引擎，适合TPM开发
        if (isset($_SERVER['HTTP_CLIENT']) && 'PhoneClient' == $_SERVER['HTTP_CLIENT'])
            return true;
            // 如果via信息含有wap则一定是移动设备,部分服务商会屏蔽该信息
        if (isset($_SERVER['HTTP_VIA']))
            // 找不到为flase,否则为true
            return stristr($_SERVER['HTTP_VIA'], 'wap') ? true : false;
            // 判断手机发送的客户端标志,兼容性有待提高
        if (isset($_SERVER['HTTP_USER_AGENT'])) {
            $clientkeywords = array(
                'nokia',
                'sony',
                'ericsson',
                'mot',
                'samsung',
                'htc',
                'sgh',
                'lg',
                'sharp',
                'sie-',
                'philips',
                'panasonic',
                'alcatel',
                'lenovo',
                'iphone',
                'ipod',
                'blackberry',
                'meizu',
                'android',
                'netfront',
                'symbian',
                'ucweb',
                'windowsce',
                'palm',
                'operamini',
                'operamobi',
                'openwave',
                'nexusone',
                'cldc',
                'midp',
                'wap',
                'mobile'
            ); // 从HTTP_USER_AGENT中查找手机浏览器的关键字
            if (preg_match("/(" . implode('|', $clientkeywords) . ")/i", strtolower($_SERVER['HTTP_USER_AGENT']))) {
                return true;
            }
        }
        // 协议法，因为有可能不准确，放到最后判断
        if (isset($_SERVER['HTTP_ACCEPT'])) {
            // 如果只支持wml并且不支持html那一定是移动设备
            // 如果支持wml和html但是wml在html之前则是移动设备
            if ((strpos($_SERVER['HTTP_ACCEPT'], 'vnd.wap.wml') !== false) && (strpos($_SERVER['HTTP_ACCEPT'], 'text/html') === false || (strpos($_SERVER['HTTP_ACCEPT'], 'vnd.wap.wml') < strpos($_SERVER['HTTP_ACCEPT'], 'text/html')))) {
                return true;
            }
        }
        return false;
    }		
}