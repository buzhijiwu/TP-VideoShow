<?php
require_once(dirname(__FILE__) . '/' . 'notification/android/AndroidBroadcast.php');
require_once(dirname(__FILE__) . '/' . 'notification/android/AndroidFilecast.php');
require_once(dirname(__FILE__) . '/' . 'notification/android/AndroidGroupcast.php');
require_once(dirname(__FILE__) . '/' . 'notification/android/AndroidUnicast.php');
require_once(dirname(__FILE__) . '/' . 'notification/android/AndroidCustomizedcast.php');
require_once(dirname(__FILE__) . '/' . 'notification/ios/IOSBroadcast.php');
require_once(dirname(__FILE__) . '/' . 'notification/ios/IOSFilecast.php');
require_once(dirname(__FILE__) . '/' . 'notification/ios/IOSGroupcast.php');
require_once(dirname(__FILE__) . '/' . 'notification/ios/IOSUnicast.php');
require_once(dirname(__FILE__) . '/' . 'notification/ios/IOSCustomizedcast.php');

class Demo {
	protected $appkey           = NULL;
	protected $appMasterSecret     = NULL;
	protected $timestamp        = NULL;
	protected $validation_token = NULL;

	function __construct($key, $secret) {
		$this->appkey = $key;
		$this->appMasterSecret = $secret;
		$this->timestamp = strval(time());
	}

    /*
    ** 系统消息安卓通知
    */
    function sendAndroidBroadcast($production_mode,$umeng_sysmessage) {
        try {
            $brocast = new AndroidBroadcast();
            $brocast->setAppMasterSecret($this->appMasterSecret);
            $brocast->setPredefinedKeyValue("appkey", $this->appkey);
            $brocast->setPredefinedKeyValue("timestamp", $this->timestamp);
            $brocast->setPredefinedKeyValue("ticker", $umeng_sysmessage);
            $brocast->setPredefinedKeyValue("title", "Waashow");
            $brocast->setPredefinedKeyValue("text", $umeng_sysmessage);
            $brocast->setPredefinedKeyValue("after_open", "go_app");
            // Set 'production_mode' to 'false' if it's a test device.
            // For how to register a test device, please see the developer doc.
            $brocast->setPredefinedKeyValue("production_mode", $production_mode);
            $result = $brocast->send();
        } catch (Exception $e) {
            $result = $e->getMessage();
        }
        return $result;
    }

   /*
   ** 系统消息安卓通知（Tag模式）
   */
    function sendAndroidGroupcastSystem($production_mode,$tag,$content) {
        try {
            $filter = array(
                "where" => 	array(
                    "and" 	=>  array(
                        array(
                            "tag" => $tag
                        )
                    )
                )
            );

            $groupcast = new AndroidGroupcast();
            $groupcast->setAppMasterSecret($this->appMasterSecret);
            $groupcast->setPredefinedKeyValue("appkey", $this->appkey);
            $groupcast->setPredefinedKeyValue("timestamp", $this->timestamp);
            // Set the filter condition
            $groupcast->setPredefinedKeyValue("filter", $filter);
            $groupcast->setPredefinedKeyValue("ticker", $content);
            $groupcast->setPredefinedKeyValue("title", 'Waashow');
            $groupcast->setPredefinedKeyValue("text", $content);
            $groupcast->setPredefinedKeyValue("after_open", "go_app");
            $groupcast->setPredefinedKeyValue("production_mode", $production_mode);
            $result = $groupcast->send();
        } catch (Exception $e) {
            $result = $e->getMessage();
        }
        return $result;
    }

    /*
    ** 主播开播安卓通知
    */
	function sendAndroidGroupcast($production_mode,$tag,$title,$content,$emceeid,$roomno,$bigheadpic) {
		try {
			$filter = array(
                "where" => 	array(
                    "and" 	=>  array(
                        array(
                            "tag" => $tag
                        )
                    )
                )
            );

			$groupcast = new AndroidGroupcast();
			$groupcast->setAppMasterSecret($this->appMasterSecret);
			$groupcast->setPredefinedKeyValue("appkey", $this->appkey);
			$groupcast->setPredefinedKeyValue("timestamp", $this->timestamp);
			// Set the filter condition
			$groupcast->setPredefinedKeyValue("filter", $filter);
			$groupcast->setPredefinedKeyValue("ticker", $content);
			$groupcast->setPredefinedKeyValue("title", $title);
			$groupcast->setPredefinedKeyValue("text", $content);
			$groupcast->setPredefinedKeyValue("after_open", "go_activity");
			$groupcast->setPredefinedKeyValue("activity", "com.xlingmao.jiuwei.ui.activity.UserLiveRoomActivity");
			// Set 'production_mode' to 'false' if it's a test device.
			// For how to register a test device, please see the developer doc.
			$groupcast->setExtraField("emcee_uid", $emceeid); //主播userid
			$groupcast->setExtraField("emcee_roomno", $roomno); //主播房间号
			$groupcast->setExtraField("emcee_cover", $bigheadpic);   //主播封面图片
			$groupcast->setPredefinedKeyValue("production_mode", $production_mode);
			$result = $groupcast->send();
		} catch (Exception $e) {
            $result = $e->getMessage();
		}
        return $result;
	}

    /*
    ** 系统消息IOS通知
    */
    function sendIOSBroadcast($production_mode,$umeng_sysmessage) {
        try {
            $brocast = new IOSBroadcast();
            $brocast->setAppMasterSecret($this->appMasterSecret);
            $brocast->setPredefinedKeyValue("appkey", $this->appkey);
            $brocast->setPredefinedKeyValue("timestamp", $this->timestamp);
            $brocast->setPredefinedKeyValue("alert", $umeng_sysmessage);
            $brocast->setPredefinedKeyValue("badge", 0);
            $brocast->setPredefinedKeyValue("sound", "chime");
            // Set 'production_mode' to 'true' if your app is under production mode
            $brocast->setPredefinedKeyValue("production_mode", $production_mode);
            $result = $brocast->send();
        } catch (Exception $e) {
            $result = $e->getMessage();
        }
        return $result;
    }

    /*
    ** 系统消息IOS通知（Tag模式）
    */
    function sendIOSGroupcastSystem($production_mode,$tag,$alert) {
        try {
            $filter = array(
                "where" => 	array(
                    "and" 	=>  array(
                        array(
                            "tag" => $tag
                        )
                    )
                )
            );
            $groupcast = new IOSGroupcast();
            $groupcast->setAppMasterSecret($this->appMasterSecret);
            $groupcast->setPredefinedKeyValue("appkey", $this->appkey);
            $groupcast->setPredefinedKeyValue("timestamp", $this->timestamp);
            $groupcast->setPredefinedKeyValue("filter", $filter);
            $groupcast->setPredefinedKeyValue("alert", $alert);
            $groupcast->setPredefinedKeyValue("badge", 0);
            $groupcast->setPredefinedKeyValue("sound", "chime");
            $groupcast->setPredefinedKeyValue("production_mode", $production_mode);
            $result = $groupcast->send();
        } catch (Exception $e) {
            $result = $e->getMessage();
        }
        return $result;
    }

    /*
    ** 主播开播IOS通知
    */
	function sendIOSGroupcast($production_mode,$tag,$alert,$emceeid) {
		try {
            $filter = array(
                "where" => 	array(
                    "and" 	=>  array(
                        array(
                            "tag" => $tag
                        )
                    )
                )
            );
            $extraParame = array(
                'emceeuserid' => $emceeid
            );
			$groupcast = new IOSGroupcast();
			$groupcast->setAppMasterSecret($this->appMasterSecret);
			$groupcast->setPredefinedKeyValue("appkey", $this->appkey);
			$groupcast->setPredefinedKeyValue("timestamp", $this->timestamp);
			// Set the filter conditions
			$groupcast->setPredefinedKeyValue("filter", $filter);
			$groupcast->setPredefinedKeyValue("alert", $alert);
			$groupcast->setPredefinedKeyValue("badge", 0);
			$groupcast->setPredefinedKeyValue("sound", "chime");
			// Set 'production_mode' to 'true' if your app is under production mode
			$groupcast->setCustomizedField("pageName", "appShowRoom");
			$groupcast->setCustomizedField("extraParame", $extraParame);
            $groupcast->setPredefinedKeyValue("production_mode", $production_mode);
            $result = $groupcast->send();
		} catch (Exception $e) {
            $result = $e->getMessage();
		}
        return $result;
	}
}