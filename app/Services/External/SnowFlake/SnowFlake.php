<?php
namespace App\Services\External\SnowFlake;
/**
 * 雪花模式
 * */
class SnowFlake
{
    const max41bit = 41;
    static $machineId = null;        // 0-31
    static $centerDataId = null;     // 0-31
    const TWEPOCH =  1650793282000; // 毫秒 // 时间起始标记点，作为基准，一般取系统的最近时间（一旦确定不能变动）

    public function __construct($mId = 1, $cId = 1)
    {
        self::$machineId = $mId;
        self::$centerDataId = $cId;
    }

    /**
     * 获取唯一uid
     * @return string
     */
    public function uid()
    {
        $nowtime = floor(microtime(true) * 1000);
        $time = $nowtime - self::TWEPOCH;
        $base = decbin(self::max41bit + $time);
        $machineid = self::$machineId;
        $centerDataId = self::$centerDataId;
//        $base = str_pad(decbin($time), 41, "0", STR_PAD_LEFT);
//        $machineid = str_pad(decbin(self::$machineId), 5, "0", STR_PAD_LEFT);
//        $centerDataId = str_pad(decbin(self::$centerDataId), 5, "0", STR_PAD_LEFT);
        $random = str_pad(decbin(mt_rand(0, 4095)), 12, "0", STR_PAD_LEFT);
        usleep(1);
        $id_str = $base . $centerDataId . $machineid . $random;
        return number_format(bindec($id_str), 0, '', '');
    }


}
