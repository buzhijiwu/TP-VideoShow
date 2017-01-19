<?php
/* *
 * 配置文件
 * 版本：1.0
 * 日期：2016-06-06
 * 说明：
 * 以下代码只是为了方便商户测试而提供的样例代码，商户可以根据自己网站的需要，按照技术文档编写,并非一定要使用该代码。
 * 该代码仅供学习和研究支付宝接口使用，只是提供一个参考。
*/

//↓↓↓↓↓↓↓↓↓↓请在这里配置您的基本信息↓↓↓↓↓↓↓↓↓↓↓↓↓↓↓
$alipay_config['seller_id'] = 'xlmcaiwu1@sina.com';

//合作身份者ID，签约账号，以2088开头由16位纯数字组成的字符串，查看地址：https://openhome.alipay.com/platform/keyManage.htm?keyType=partner
$alipay_config['partner'] = '2088421207321684';

//商户的私钥,此处填写原始私钥去头去尾，RSA公私钥生成：https://doc.open.alipay.com/doc2/detail.htm?spm=a219a.7629140.0.0.nBDxfy&treeId=58&articleId=103242&docType=1
$alipay_config['private_key']	= 'MIICXgIBAAKBgQDCPJhv/aBLMXeKAsSmxnH+AqRpwIK/UtBYGgT096UBAw98WPI572uw5fbiMt0bljtJohom/thu8ys5V4UT+DJt16Jsx/fktVyqEzvCO4Q3XRU9m4n72rIRTj8ar1vmsn+g5bECKqkZKay0M7lj/LVFKeb68lgbako5TOojW00euQIDAQABAoGAQFo1Iv2DqwXzlez+3EZpJAAaGtNmPh8g2d+c/tBBgclSyx7o+drh8hTuq9iwOuCWOfoT8hGDAZddHb1qYMhdZEVHQFY21Ve99Rh0pZugyLCSpvjPA8H++xApFJuqzgDX+GE59pkVpv/T9Tfs5ILeDvbcBfvlSvJwFTY7/cWkPZECQQDf/PZ0scyLlIhP4v/9MYaME7FUkHgF0Kojj3d9arvhwb0Ulz2OYTuyO+fVrNPZn6KpG2S6IKcns5WfpCK4lo+FAkEA3f8ehLU+3RJFORkjZCcuY6v37YjaayAvWbQe13KXg1z42SAvFkJGi/S91+An9kBmU7i5cBM/lbVzgqBBlIeGpQJBAMXhSzIo6ZXecNZyqsjaeg1CUIVu5Dnu8IBd/KhWJQn7CLoqKv2gNQbvGY+SEc7O0vsm8kPlGQdgBi106h+9E6kCQQCL8CXswdO+x6WbOJ12pLw5WE4RDhOhM8ilY0WNyk54IEM9m2wTO/P8hWqmikamlDHs1KUQYRHT7W3DCgJM/qGBAkEA3M0WmHDPdPjEaZqYPyfl9SO8oxjLSFXb24fyqoQkjEPFQCwa9YyZP/Gz+eiY5CVMxD2IpsRDUQVtPJ9SDucQsA==';

//支付宝的公钥，查看地址：https://openhome.alipay.com/platform/keyManage.htm?keyType=partner
$alipay_config['alipay_public_key'] = 'MIGfMA0GCSqGSIb3DQEBAQUAA4GNADCBiQKBgQCnxj/9qwVfgoUh/y2W89L6BkRAFljhNhgPdyPuBV64bfQNN1PjbCzkIM6qRdKBoLPXmKKMiFYnkd6rAoprih3/PrQEB/VsW8OoM8fxn67UDYuyBTqA23MML9q1+ilIZwBC2AQ2UBVOrFXfFl75p6/B5KsiNG9zpgmLCUYuLkxpLQIDAQAB';

//异步通知接口
$alipay_config['notify_url'] = 'http://srapp.waashow.cn/Application/Api/Controller/alipay/notify_url.php';
//↑↑↑↑↑↑↑↑↑↑请在这里配置您的基本信息↑↑↑↑↑↑↑↑↑↑↑↑↑↑↑

//签名方式 不需修改
$alipay_config['sign_type'] = strtoupper('RSA');

//字符编码格式 目前支持 gbk 或 utf-8
$alipay_config['input_charset'] = strtolower('utf-8');

//ca证书路径地址，用于curl中ssl校验
//请保证cacert.pem文件在当前文件夹目录中
$alipay_config['cacert'] = getcwd().'/cacert.pem';

//访问模式,根据自己的服务器是否支持ssl访问，若支持请选择https；若不支持请选择http
$alipay_config['transport'] = 'http';
?>