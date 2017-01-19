<?php
/*!
* HybridAuth
* http://hybridauth.sourceforge.net | http://github.com/hybridauth/hybridauth
* (c) 2009-2012, HybridAuth authors | http://hybridauth.sourceforge.net/licenses.html
*/
//117039169631-jqfq5460f9t8t0p2qpjrv9lo1a0j7kuf.apps.googleusercontent.com", "secret" => "AWSdthopZ_4-xKaRYrz11RRS"
// ----------------------------------------------------------------------------------------
//	HybridAuth Config file: http://hybridauth.sourceforge.net/userguide/Configuration.html
//  facebook 1541809749448877 43c7b65ca74ae85c7d22466d142a13f4
// ----------------------------------------------------------------------------------------
$config =array(
		//"base_url" => "http://svn.xlingmao.com/Application/Home/Controller/hybridauth/index.php", 
		"base_url" => "http://".$_SERVER['HTTP_HOST']."/Application/Home/Controller/hybridauth/index.php",
		"providers" => array ( 
			"Google" => array ( 
				"enabled" => true,
				"keys"    => array ( "id" => "194846768480-56pah1m1475uoacfn94s4m63ml71kkbt.apps.googleusercontent.com", "secret" => "NT1e-LzpwOqxpa_xyZtV7DwP" ), 
			),

			"Facebook" => array ( 
				"enabled" => true,
				//"keys"    => array ( "id" => "553346311488994", "secret" => "a27f950494c023bf041d16ba33216766" ), //九尾账号
				//	"keys"    => array ( "id" => "448241242038183", "secret" => "7a6b10711a03584f6c6f4457cbe1b68e" ),//黑羊账号
			    "keys"    => array ( "id" => "1541809749448877", "secret" => "43c7b65ca74ae85c7d22466d142a13f4" ),//waashow账号
			    "trustForwarded" => true
			),

			"Twitter" => array ( 
				"enabled" => true,
				"keys"    => array ( "key" => "HJinWNa0wgG86iUy2RJ7vlGN0", "secret" => "EGLZnZ1bupMCImqPKMwriXVwafuhHkr0Vn2fJGhHYTcxRek6UU" ) 
			),
		),
		// if you want to enable logging, set 'debug_mode' to true  then provide a writable file by the web server on "debug_file"
		"debug_mode" => false,
		"debug_file" => "thirdpartylogin.log",
	);
