<?php
namespace Jar\Services;

use Jar\Model\JarModel;
use Jar\Facades\Role;
class GoodsService extends Service{
    public function __construct(){
        parent::__construct();
        $this->model=D('Jar/Goods');
    }

    public function getGoodsList(){
       $datas = $this->getList(['is_on_sale'=>1],function ($model){
           $model->order('is_recommend desc');
       });
       foreach ($datas as $k=>$item){
           $datas[$k]['original_img']=$_SERVER['REQUEST_SCHEME']."://".$_SERVER['HTTP_HOST'].$item['original_img'];
       }
       return $datas;
    }

    public function countMoney(Array $list){
        $money = 0;
        $ids = [0];
        foreach($list as $v){
            $ids[] = $v['id'];
            $listTmp[$v['id']] = $v;
        }
        $goodsList = $this->model->where(['goods_id'=>['in',$ids]])->select();
        foreach($goodsList as $goods){
            $money+=$goods['shop_price']*$listTmp[$goods['goods_id']]['num'];
        }
        return $money;
    }
}
?>