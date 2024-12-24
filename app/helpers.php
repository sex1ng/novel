<?php
//获取js css的路径
use Carbon\Carbon;
use phpseclib\Crypt\AES;
use Curl\Curl;

function getCdnUrl($url) {
    $mixUrl = file_get_contents(public_path('mix-manifest.json'));
    if($mixUrl) {
        $mixUrl = json_decode($mixUrl, true);
        $url = @$mixUrl[$url] ?: $url;
    }
    return $url;
}


/**
 * AES加密
 * @param $data
 * @return string
 */
function aesEncrypt($data, $key = '832ujr2#$r2198jf')
{
    static $crypt = null;

    if (is_null($crypt)) {
        $crypt = new phpseclib\Crypt\AES(phpseclib\Crypt\AES::MODE_ECB);
        $crypt->setKey($key);
    }
    $temp = $crypt->encrypt($data);
    $temp = base64_encode($temp);
    $result = $temp;

    return $result;
}
function aesDecrypt($data, $key = '832ujr2#$r2198jf')
{
    static $crypt = null;

    $result = $data;
    // 判断是否是密文，通过Base64编码特征识别。
    if (strlen($data) % 4 == 0 && preg_match('/^([+\\/A-Za-z0-9]+)={0,3}$/', $data)) {
        // 对密文进行解密。
        if (is_null($crypt)) {
            $crypt = new phpseclib\Crypt\AES(phpseclib\Crypt\AES::MODE_ECB);
            $crypt->setKey($key);
        }
        try {
            $temp = base64_decode($data);
            // 检查密文长度。
            if (strlen($temp) % $crypt->block_size == 0) {
                $result = $crypt->decrypt($temp);
            }
        } catch (LengthException $e) {
            // noop.
        } catch (ErrorException $e) {
            // noop.
        } catch (Exception $e) {
            // noop.
        }
    }

    return $result;
}
function aesEncryptUrl($encrypt_data)
{
    $encrypt_url = aesEncrypt($encrypt_data);
    $encrypt_url = str_replace('+', '-', $encrypt_url);
    $encrypt_url = str_replace('/', '_', $encrypt_url);

    return $encrypt_url;
}

function aesDecryptUrl($decrypt_url)
{
    $decrypt_url = str_replace('-', '+', $decrypt_url);
    $decrypt_url = str_replace('_', '/', $decrypt_url);

    return aesDecrypt($decrypt_url);
}

function getDeviceType()
{
    //全部变成小写字母
    $agent = strtolower($_SERVER['HTTP_USER_AGENT']);
    $type ='';
    //分别进行判断
    if(strpos($agent,'iphone') || strpos($agent,'ipad'))
    {
        $type ='iOS';
    }

    if(strpos($agent,'android'))
    {
        $type ='Android';
    }
    return $type;
}


/**
 * 二进制转64进制
 * @param string $bin 待转的二进制字符串
 * @return string
 */
function bin2Base64($bin){
    $str = 'ABCDEFGHIJKLMNOPQRSTUVWXYZabcdefghijklmnopqrstuvwxyz0123456789+=';
    for($i = strlen($bin) % 6; $i < 6 && $i != 0; $i++) {
        $bin = '0' . $bin;
    }
    $splits = str_split($bin, 6);
    $sixtyFour = '';
    foreach ($splits as $split) {
        $sixtyFour .= $str[bindec($split)];
    }
    return $sixtyFour;
}

/**
 * 64进制转2进制
 * @param string $base64 待转的64进制字符串
 * @return string
 */
function base642bin($base64){
    $str = 'ABCDEFGHIJKLMNOPQRSTUVWXYZabcdefghijklmnopqrstuvwxyz0123456789+=';
    $length = strlen($base64);
    $bin = '';
    for($i = 0; $i < $length; $i++){
        $minBin = decbin(strpos($str, $base64[$i]));
        for($j = strlen($minBin) % 6; $j < 6 && $j != 0 && $i != 0; $j++) {
            $minBin = '0' . $minBin;
        }
        $bin .= $minBin;
    }
    return $bin;
}

/**
 * 判断是否是微信
 * @return bool
 */
function isWechat() {
    return isset($_SERVER['HTTP_USER_AGENT']) && strpos($_SERVER['HTTP_USER_AGENT'], 'MicroMessenger') !== false;
}

/**
 * 获取手机号归属地
 */
function getMobileInfo($mobile) {
    try {
        $curl = new Curl();
        $curl->get('http://mobsec-dianhua.baidu.com/dianhua_api/open/location?tel=' . $mobile);
        $rul = json_decode($curl->response, true);
        $curl->close();
        $res = @$rul['response'][$mobile];
        if($res){
            $province = $res['detail']['province'];
            $city = $res['detail']['area']['0']['city'];
            if($province == $city){
                $res['detail']['province_city'] = $province;
                $res['province_city'] = $province;
            }else{
                $res['detail']['province_city'] = $province.$city;
                $res['province_city'] = $province.$city;
            }
            $res['detail']['city'] = $city;

            $res['operator'] = @$res['detail']['operator']? : "";
        }else{
            //请求其他的
            $curl = new Curl();
            $curl->get('https://cx.shouji.360.cn/phonearea.php?number=' . $mobile);
            $rul = json_decode($curl->response,true);
            $curl->close();
            $res = [];
            if($rul['code'] == 0){
                $rul = $rul['data'];
                $res = [
                    'operator'  =>  $rul['sp'],
                    'location'  =>  $rul['province'] . $rul['city'] .$rul['sp'],
                    'province_city' =>  $rul['province'].$rul['city'],
                    'detail'  => [
                        'operator'  =>  $rul['sp'],
                        'province'  =>  $rul['province'],
                        'city'  =>  $rul['city'],
                        'province_city' => $rul['province'].$rul['city'],
                        'area'      =>  [
                            [
                                'city'  =>  $rul['city']
                            ]
                        ]
                    ],
                ];

            }

        }
        return $res;
    }catch (\Exception $e){
        \Log::info('获取手机号信息失败',[$e]);
        return '';
    }
}


/**
 * 二进制转32进制
 * @param string $bin 待转的二进制字符串
 * @return string
 */
function bin2Base32($bin){
    $str = 'WX23456789ABCDEFGHYJKLMNZPQRSTUV';
    for($i = strlen($bin) % 5; $i < 5 && $i != 0; $i++) {
        $bin = '0' . $bin;
    }
    $splits = str_split($bin, 5);
    $sixtyFour = '';
    foreach ($splits as $split) {
        $sixtyFour .= $str[bindec($split)];
    }
    return $sixtyFour;
}

/**
 * 32进制转2进制
 * @param string $base32 待转的64进制字符串
 * @return string
 */
function base322Bin($base32){
    $str = 'WX23456789ABCDEFGHYJKLMNZPQRSTUV';
    $length = strlen($base32);
    $bin = '';
    for($i = 0; $i < $length; $i++){
        $minBin = decbin(strpos($str, $base32[$i]));
        for($j = strlen($minBin) % 5; $j < 5 && $j != 0 && $i != 0; $j++) {
            $minBin = '0' . $minBin;
        }
        $bin .= $minBin;
    }
    return $bin;
}


/**
 * 十进制转32进制
 * @param $decimal
 * @return string
 */
function decimal2Base32($decimal) {
    return bin2Base32(decimal2Bin($decimal));
}

/**
 * 32进制转十进制
 * @param $base32
 * @return string
 */
function base322Decimal($base32) {
    $base32 = str_replace('0', 'W', $base32);
    $base32 = str_replace('1', 'X', $base32);
    $base32 = str_replace('I', 'Y', $base32);
    $base32 = str_replace('O', 'Z', $base32);
    return bin2Decimal(base322Bin($base32));
}
/**
 * 十进制转二进制
 * @param $decimal
 * @return string
 */
function decimal2Bin($decimal) {
    $decimal = floatval($decimal);
    $rul = '';
    while($decimal > 0) {
        $temp = ceil($decimal / 2);
        $decimal = floor($decimal / 2);
        $rul = ($temp - $decimal) . $rul;
    }
    return $rul;
}

/**
 * 二进制转十进制
 * @param $bin
 * @return string
 */
function bin2Decimal($bin) {
    $pow = 0;
    $rul = 0;
    while(strlen($bin) > 0) {
        $rul = $rul + substr($bin, -1) * pow(2, $pow++);
        $bin = substr($bin, 0, -1);
    }
    return (string)$rul;
}

function randStr($length = 6)
{
    $string = 'abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ1234567890';

    $code = '';
    $strlen = strlen($string) - 1;
    for ($i = 0; $i < $length; $i++) {
        $code .= $string[mt_rand(0, $strlen)];
    }
    return $code;
}

/**
 * 发送钉钉消息
 * @param $title
 * @param $message
 * @param $config
 * @param $at
 * @throws ErrorException
 */
function sendDingDing ($title, $message, $config, $at = []) {

    // 发送钉钉消息
    $msg['msgtype'] = "markdown";
    $msg['markdown']['title'] = $title;
    $msg['markdown']['text'] = $message;
    if(!empty($at)){
        $msg['at'] = [
            'atMobiles'     =>  $at,
            'isAtAll'       => false
        ];
    }
    $curl = new \Curl\Curl();
    $curl->setHeader('Content-type', 'application/json');
    $curl->setHeader(CURLOPT_SSL_VERIFYPEER, 0);
    $curl->setHeader(CURLOPT_SSL_VERIFYHOST, 0);
    $curl->post($config, json_encode($msg));
    $curl->close();
}

/**
 * 发送钉钉卡片消息
 * @param $title
 * @param $message
 * @param $config
 * @param $btns
 * @param int $btnOrientation
 * @param array $at
 */
function sendDingDingCardBtn ($title, $message, $config, $btns,$btnOrientation = 0, $at = []) {

    // 发送钉钉消息
    $msg['msgtype'] = "actionCard";
    $msg['actionCard']['title'] = $title;
    $msg['actionCard']['text'] = $message;
    $msg['actionCard']['btns'] = $btns;
    $msg['actionCard']['btnOrientation'] = $btnOrientation;
    if(!empty($at)){
        $msg['at'] = [
            'atMobiles'     =>  $at,
            'isAtAll'       => false
        ];
    }
    $curl = new \Curl\Curl();
    $curl->setHeader('Content-type', 'application/json');
    $curl->setHeader(CURLOPT_SSL_VERIFYPEER, 0);
    $curl->setHeader(CURLOPT_SSL_VERIFYHOST, 0);
    $curl->post($config, json_encode($msg));
    $curl->close();
}

/**
 * 广点通金额单位转(分)
 * @param $money
 * @return int
 */
function takeGDTMoney($money)
{
    return (int)($money * 100);
}

/**
 * 权重选择
 * @param $arr
 * @return mixed
 */
function chooseByRatio($arr)
{
    $sum   = collect($arr)->sum('ratio');
    $ratio = myRand(1, $sum, 0);
    foreach ($arr as $item) {
        $ratio -= $item['ratio'];
        if ($ratio <= 0) {
            return $item;
        }
    }
    return collect($arr)->first();
}

/**
 * 广点通金额单位转(元)
 * @param $money
 * @return float|int
 */
function divisionGDTMoney($money)
{
    return $money / 100;
}

/**
 * 除法运算
 * @param float $divisor 除数
 * @param float $dividend 被除数
 * @param int $precision
 * @return float 商
 */
function divide($divisor, $dividend = 1, $precision = 2){
    $divisor  = !is_string($divisor) ? $divisor : (float)$divisor;
    $dividend = !is_string($dividend) ? $dividend : (float)$dividend;
    return $dividend? round($divisor / $dividend, $precision): 0;
}

/**
 * 获取百分比
 * @param $divisor
 * @param $dividend
 * @param int $precision
 * @return float|int
 */
function getRate($divisor, $dividend = 1, $precision = 4){
    return round(divide($divisor, $dividend, $precision) * 100, 2);
}

/**
 * 获取随机数
 * @param $data
 * @param int $precision
 * @return int
 */
function getRand($data, $precision = 2) {
    $data = explode('-', $data);
    return myRand($data[0], $data[1], $precision);
}

/**
 * 获取随机数
 * @param $data1
 * @param $data2
 * @param int $precision
 * @return float|int
 */
function myRand($data1, $data2, $precision = 2) {
    $multiple = pow(10, $precision);
    return rand($data1 * $multiple, $data2 * $multiple) / $multiple;
}

/**
 * 环比同比
 * @param $previousData
 * @param $nowData
 * @return float|int
 */
function compareDivide($previousData, $nowData) {
    return getRate($nowData - $previousData,$previousData);
}

/**
 * 环比同比获取展示结果
 * @param $compare
 * @return string
 */
function getCompareDivideShow($compare) {
    if ($compare) {
        $compare = ($compare > 0? '+': '') . $compare . '%';
    } else {
        $compare = '- -';
    }
    return $compare;
}

/**
 * 获取环比颜色
 * @param $val
 * @return string
 */
function getCompareDivideColor($val) {
    if ($val == 0) {
        return '0,0,0';
    }
    if ($val < 0) {
        return '0,128,0';
    }

    return '255,60,48';
}

/**
 * 金额颜色(正黑,负红)
 * @param $val
 * @return string
 */
function getMoneyColor($val)
{
    if ($val >= 0) {
        return '0,0,0';
    }

    if ($val < 0) {
        return '255,60,48';
    }

    return '0,0,0';
}

function isMobile()
{
    // 如果有HTTP_X_WAP_PROFILE则一定是移动设备
    if (isset ($_SERVER['HTTP_X_WAP_PROFILE']))
    {
        return true;
    }
    // 如果via信息含有wap则一定是移动设备,部分服务商会屏蔽该信息
    if (isset ($_SERVER['HTTP_VIA']))
    {
        // 找不到为flase,否则为true
        return stristr($_SERVER['HTTP_VIA'], "wap") ? true : false;
    }
    // 脑残法，判断手机发送的客户端标志,兼容性有待提高
    if (isset ($_SERVER['HTTP_USER_AGENT']))
    {
        $clientkeywords = array ('nokia',
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
        );
        // 从HTTP_USER_AGENT中查找手机浏览器的关键字
        if (preg_match("/(" . implode('|', $clientkeywords) . ")/i", strtolower($_SERVER['HTTP_USER_AGENT'])))
        {
            return true;
        }
    }
    // 协议法，因为有可能不准确，放到最后判断
    if (isset ($_SERVER['HTTP_ACCEPT']))
    {
        // 如果只支持wml并且不支持html那一定是移动设备
        // 如果支持wml和html但是wml在html之前则是移动设备
        if ((strpos($_SERVER['HTTP_ACCEPT'], 'vnd.wap.wml') !== false) && (strpos($_SERVER['HTTP_ACCEPT'], 'text/html') === false || (strpos($_SERVER['HTTP_ACCEPT'], 'vnd.wap.wml') < strpos($_SERVER['HTTP_ACCEPT'], 'text/html'))))
        {
            return true;
        }
    }
    return false;
}

// 单位转换
function uniteConversion($val) {
    $val = divide($val,1024);

    if ($val < 1024) {
        return $val . 'KB';
    }

    $val = divide($val, 1024);
    if ($val < 1024) {
        return $val . 'MB';
    }

    $val = divide($val, 1024);
    return $val . 'GB';
}

/**
 * @param Illuminate\Database\Eloquent\Builder $sql
 * @return string
 */
function getSql(\Illuminate\Database\Eloquent\Builder $sql) : string {
    return sprintf(str_replace('?', '%s', $sql->toSql()), ...$sql->getBindings());
}

function fileFiltering($fileType) {
    switch ($fileType) {
        case 'png':
        case 'jpg':
        case 'jpeg':
        case 'mp3':
        case 'mp4':
        case 'mov':
            return true;
        default:
            return false;
    }
}

/**
 * web 传到后端的时间格式化
 * @param $time
 * @return Carbon
 */
function getWebTime($time) {
    return Carbon::parse($time)->setTimezone(config('app.timezone'));
}

//获取js css的路径
function getAssetUrl($url) {
    $mixUrl = file_get_contents(public_path('mix-manifest.json'));
    if($mixUrl) {
        $mixUrl = json_decode($mixUrl, true);
        $url = @$mixUrl[$url] ?: $url;
    }
    return $url;
}