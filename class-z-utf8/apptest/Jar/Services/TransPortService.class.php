<?php
namespace Jar\Services;

use Jar\Facades\Role;
use Jar\Facades\UserAddress;
class TransPortService extends Service {

    public function __construct(){
        parent::__construct();
        $this->model = D('shipping_area');
    }
    public function getTransMoney($weight , $addressId, $shipping_code=0){
        if(!$addressId){
            return array(0, $this->noAddressMoney($weight));
        }
        $address = UserAddress::find(['id'=>$addressId,'user_id'=>Role::getuser_id()], function($model){
            $model->field('country,city,district');
        });
        if(!$address){ self::$errors = '地址找不到!'; return false; }
//        var_dump($address);
        if(!$shipping_code){//未选择快递我们提供最优快递方案
            return $this->getMoreLessMoney($address, $weight);
        }else{
        //    $this->searchAreaTmpByTrans($address, $shipping_code);
            if($transTmp = $this->searchAreaTmpByTrans($address, $shipping_code)){
                return array($shipping_code, $this->countMoney($weight, $transTmp['config']));
            }else{
                self::$errors = '没找到配送模板!';
                return false;
            }
        }

    }

    public function getMoreLessMoney($address, $weight=0){
        $shipping_list = D('plugin')->where(['type'=>'shipping','status'=>1])->select();
        $money = 0; $shipping_code = 0;
        foreach($shipping_list as $item){
            if($transTmp = $this->searchAreaTmpByTrans($address, $item['code'])){
                $tmp_money = $this->countMoney($weight, $transTmp['config']);
                $tmp_shipping_code = $item['code'];
                if($shipping_code!=0){
                    if($tmp_money < $money){
                        $money = $tmp_money;
                        $shipping_code = $tmp_shipping_code;
                    }
                }else{
                    $money = $tmp_money;
                    $shipping_code = $tmp_shipping_code;
                }
            }
        }
        return array($shipping_code, $money);
    }
    /**
     * [searchAreaTmp description]
     * @param  Array  $address ['country'=>,'city'=>,'district'=>,twon=>]
     * @return [type]          [description]
     */
    public function searchAreaTmp(Array $address){
        $areaInfo = $this->findArea($address['district']);
        $areaInfo ? false : $areaInfo = $this->findArea($address['city']);
        $areaInfo ? false : $areaInfo = $this->findArea($address['province']);
//        var_dump($areaInfo);echo '0';
        if($areaInfo){
            $transTmp = $this->model->find($areaInfo['shipping_area_id']);
//            var_dump($transTmp);echo 1;
        }else{
            $transTmp = $this->model
            ->where(['shipping_area_name'=>'全国其他地区', 'is_default'=>1, 'store_id'=>0])
            ->find();
//            var_dump($transTmp);echo 2;
        }

        $transTmp['config'] = unserialize($transTmp['config']);
//        var_dump($transTmp);echo 2;
        return $transTmp;
    }
    /**
     * [searchAreaTmpByTrans 根据物流查询模板]
     * @param  Array  $address [description]
     * @return [type]          [description]
     */
    public function searchAreaTmpByTrans(Array $address, $shipping_code){
        $areaInfo = $this->findArea($address['district']);
        $areaInfo ? false : $areaInfo = $this->findArea($address['city'], $shipping_code);
        $areaInfo ? false : $areaInfo = $this->findArea($address['province'], $shipping_code);
//        var_dump($areaInfo);echo '0';
        if($areaInfo){
            $transTmp = $this->model->find($areaInfo['shipping_area_id'], $shipping_code);
//            var_dump($transTmp);echo 1;
        }else{
            $transTmp = $this->model
            ->where([
                'shipping_area_name'=>'全国其他地区', 'is_default'=>1,
                'store_id'=>0, 'shipping_code'=>$shipping_code
                ])
            ->find();
//            var_dump($transTmp);echo 2;
        }

        $transTmp['config'] = unserialize($transTmp['config']);
//        var_dump($transTmp);echo 2;
        return $transTmp;
    }
    public function findArea($id, $shipping_code=0){
        if(!$shipping_code)
            return D('area_region')->where(['id'=>$id, 'store_id'=>0])->find();
        else
            return D('area_region')->alias('ar')
                ->join(C('db_prefix').'shipping_area sar on(sar.shipping_area_id=ar.shipping_area_id)')
                ->where(['ar.region_id'=>$id, 'ar.store_id'=>0, 'sar.shipping_code'=>$shipping_code])
                ->field('ar.*,sar.shipping_code')
                ->find();
    }
    public function getShippingCode($code){

    }
    /**
     * [countMoney description]
     * @param  Int    $weight [description]
     * @param  Array  $cfg    ['first_weight','money','second_weight','add_money'] 总量单位克
     * @return [type]         [description]
     */
    public function countMoney($weight,Array $cfg){
        $weight = $weight*500;
        if($weight-$cfg['first_weight'] < 0){
            return round($cfg['money'],2);
        }else{
            return ceil(($weight-$cfg['first_weight'])/$cfg['second_weight'])*$cfg['add_money']+$cfg['money'];
        }
    }
    public function noAddressMoney($weight){
        $cfg = [
            'first_weight'=>1000,//首重
            'second_weight'=>1000,//追加重量
            'add_money'=>1.5,//追加价
            'money'=>1.5,//起步价
        ];
        $weight = $weight*500;
        if($weight-$cfg['first_weight'] < 0){
            return round($cfg['money'],2);
        }else{
            return ceil(($weight-$cfg['first_weight'])/$cfg['second_weight'])*$cfg['add_money']+$cfg['money'];
        }
    }
}