<?php
namespace Jar\Utils;

use Endroid\QrCode\QrCode as Qr;

class Qrcode{
    //生成qr文件
    public static function wrtieFile($string,$path){
        if(!empty($string)){
            $qrCode = new Qr();
            $qrCode->setText(trim($string))
                ->setSize(300)
                ->setPadding(10)
                ->setErrorCorrection('high')
                ->setForegroundColor(['r' => 0, 'g' => 0, 'b' => 0, 'a' => 0])
                ->setBackgroundColor(['r' => 255, 'g' => 255, 'b' => 255, 'a' => 0])
                ->setLabel('Scan the code')
                ->setLabelFontSize(16)
                ->setImageType(Qr::IMAGE_TYPE_PNG);
            if(self::checkDir($path)){
                $filePath = rtrim($path,'/').'/'.self::randomName();
                $qrCode->save($filePath);
                return $filePath;
            }
        }
        return '';
    }
    //生成qr文件
    public static function wrtieString($string,$path){
        if(!empty($string)){
            $qrCode = new Qr();
            $qrCode->setText(trim($string))
                ->setSize(300)
                ->setPadding(10)
                ->setErrorCorrection('high')
                ->setForegroundColor(['r' => 0, 'g' => 0, 'b' => 0, 'a' => 0])
                ->setBackgroundColor(['r' => 255, 'g' => 255, 'b' => 255, 'a' => 0])
                ->setLabel('Scan the code')
                ->setLabelFontSize(16)
                ->setImageType(Qr::IMAGE_TYPE_PNG);
            return $qrCode->render();
        }else{
            return '';
        }
    }

    public static function randomName(){
        return md5(uniqid().mt_rand(1000000,9999999)).'.png';
    }

    public static function checkDir($path){
        if (!is_dir($path)){
            if(mkdir($path,0775,true)){
                return true;
            }else{
                throw new \Exception('目录创建失败');
            }
        }
        return true;
    }
}


?>