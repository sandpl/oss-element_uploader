<?php
function gmt_iso8601($time) {
    date_default_timezone_set('PRC');
    $dtStr = date("c", $time);
    $mydatetime = new DateTime($dtStr);
    $expiration = $mydatetime->format(DateTime::ISO8601);
    $pos = strpos($expiration, '+');
    $expiration = substr($expiration, 0, $pos);
    return $expiration."Z";
}

$ossParams = require "../../../../common/config/params-local.php";

$id = $ossParams['oss_access_id'];
$key = $ossParams['oss_access_key'];
$host = $ossParams['oss_upload_host'];
$dir = $ossParams['oss_path'];
$showHost = $ossParams['oss_cdn_endpoint'];

$now = time();
$expire = 30; //设置该policy超时时间是10s. 即这个policy过了这个有效时间，将不能访问
$end = $now + $expire;
$expiration = gmt_iso8601($end);


//最大文件大小.用户可以自己设置
$condition = array(0=>'content-length-range', 1=>0, 2=>1048576000);
$conditions[] = $condition;

//表示用户上传的数据,必须是以$dir开始, 不然上传会失败,这一步不是必须项,只是为了安全起见,防止用户通过policy上传到别人的目录
//$start = array(0=>'starts-with', 1=>'$key', 2=>$dir);
//$conditions[] = $start;


$arr = array('expiration'=>$expiration,'conditions'=>$conditions);

$policy = json_encode($arr);
$base64_policy = base64_encode($policy);
$string_to_sign = $base64_policy;
$signature = base64_encode(hash_hmac('sha1', $string_to_sign, $key, true));

$response = array();
$response['accessid'] = $id;
$response['host'] = $host;
$response['policy'] = $base64_policy;
$response['signature'] = $signature;
$response['expire'] = $end;
$response['showHost'] = $showHost;
//这个参数是设置用户上传指定的前缀
$response['dir'] = $dir;
echo json_encode($response);