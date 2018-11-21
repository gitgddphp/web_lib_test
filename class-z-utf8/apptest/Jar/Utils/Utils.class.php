<?php
namespace Jar\Utils;

use Think\Upload;

class Utils{
    public static $errors;

    public static function upload($path='File',$ext=array('png','jpg','jpeg','gif'),$size='2097152'){
        $upload = new Upload(); // 实例化上传类
        $upload->maxSize   =    $size ;// 设置附件上传大小
        $upload->exts      =    $ext;// 设置附件上传类型
        $upload->rootPath  =    UPLOAD_PATH; // 设置附件上传根目录
        $upload->savePath  =    $path.'/'; // 设置附件上传（子）目录
        // 上传文件
        $info   =   $upload->upload();
        if(!$info) {// 上传错误提示错误信息
            self::$errors=$upload->getError();
            return false;
        }else{// 上传成功
            return $info;
        }
    }

    /**
     * 发送手机短信
     * @param $mobile
     * @param $code
     * @return bool
     */
    public static function sendSMS($mobile,$code)
    {
        $resp = Sms::sendSms($mobile,$code);
        if ($resp && $resp->Code=="OK"){
            // 从数据库中查询是否有验证码
            $data = M('sms_log')->where("code = '$code' and add_time > ".(time() - intval(tpCache('sms.sms_time_out'))))->find();
            // 没有就插入验证码,供验证用
            empty($data) && M('sms_log')->add(array('mobile' => $mobile, 'code' => $code, 'add_time' => time(), 'session_id' => SESSION_ID));
            return true;
        }else{
            return false;
        }
    }

    public static function is_moblie($moblie) {
        return preg_match("/^1[34578]\d{9}$/", $moblie);
    }

    public static function date($format,$time=null){
        $timezone = isset($_SESSION['timezone']) ? $_SESSION['timezone'] : $GLOBALS['_CFG']['timezone'];

        if ($time === NULL)
        {
            $time = self::gmtime();
        }
        elseif ($time <= 0)
        {
            return '';
        }

        $time += ($timezone * 3600);

        return date($format, $time);
    }

    public static function gmtime(){
        return (time() - date('Z'));
    }

    /**
     * 返回最近的配置规则整数倍整数
     * @param $num
     */
    public static function regNum($num){
        $b = tpCache('basic.min_get_jar');
        return ceil(intval($num)/$b)*$b;
    }
}
?>