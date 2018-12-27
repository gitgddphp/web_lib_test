<?php
/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 2018/12/27
 * Time: 13:11
 */

namespace Datahelper;

class Datahelper
{
    protected $fieldMap=[];
    protected static $helper=[];
    public function __construct($fieldsMap = null){
        if($fieldsMap){ $this->fieldMap = $fieldsMap; }
    }
    public static function getHelper(){
        $class = static::class;
        if(!isset(static::$helper[$class])){
            static::$helper[$class] = new $class();
        }
        return static::$helper[$class];
    }
    //所有字段转换补全 适配成数据库所需字段
    public function transformAll($datas){
        foreach($datas as $k=>$item){
            $tmp[$k] = array();
            foreach($this->fieldMap as $k1=>$item1){
                $tmp[$k][$k1] = $this->{$k1}($item);
            }
            $tmp[$k]['open_id'] = $_SESSION['open_id'];
        }
        return $tmp;//转换后的数据
    }
    public function __call($func, $args){
        $item = $args[0];
        $name = $this->fieldMap[$func][0];
        if(isset($item[$name]))
            return $item[$name];
        else
            return $this->fieldMap[$func][1];
    }
    //正向转换 适配到数据库
    public function transform($datas){
        $fieldMap = $this->getDataFields($datas[0]);
        foreach($datas as $k=>$item){
            $tmp[$k] = array();
            foreach($fieldMap as $k1=>$item1){
                $tmp[$k][$k1] = $this->{$k1}($item);
            }
        }
        return $tmp;//转换后的数据
    }
    //反向转换 由数据库字段适配到逻辑固定字段
    public function reverseTransform($datas){
        $fieldMap = $this->MapFields();
        var_dump($fieldMap);
        foreach($datas as $k=>$item){
            $tmp[$k] = array();
            var_dump($item);
            foreach($item as $k1=>$item1){
                $name = $fieldMap[$k1];
                $tmp[$k][$name] = $item1;
            }
        }
        return $tmp;//转换后的数据
    }
    //只转换已存在字段的映射
    public function getDataFields($data){
        $tmp = [];
        $fields = $this->reverseMapFields();
        foreach($fields as $k=>$v){
            isset($data[$k]) && $tmp[$v] = $this->fieldMap[$v];
        }
        return $tmp;
    }
    //正向字段映射
    public function MapFields(){
        $fields = [];
        foreach($this->fieldMap as $k=>$item){
            $fields[$k] = $item[0];
        }
        return $fields;
    }
    //映射数组反向
    public function reverseMapFields(){
        $fields = [];
        foreach($this->fieldMap as $k=>$item){
            $fields[$item[0]] = $k;
        }
        return $fields;
    }
}

//有赞云数据
class YzyData extends Datahelper{
    protected $fieldMap=[
            'user_name'=>['receiver_name',0],//字段名和默认值
            'shop_name'=>['shop_name','易酒购'],
            'order_status'=>['order_status',''],
            'order_url'=>['order_url',0],
            'order_sn'=>['tid',0],
            'team_id'=>['team_id',0],
            'extension_code'=>['extension_code',0],
            'order_time'=>['created',0],
            'order_goods'=>['order_goods',0],
            'order_goods_num'=>['order_goods_num',0],
            'total_fee'=>['total_fee',0],
            'delay'=>['delay',0],//交易单号
        //  'handler_return'=>['handler_return',0],
            'pay_status'=>['payment',0],
            'delete_yes'=>['delete_yes',0],
        //  'order_del'=>['order_del',0],
        //  'online_pay'=>['online_pay',0],
            'open_id'=>['open_id',0],
            'sku_id'=>['sku_id1',0],
            'os'=>['os','有赞']
            ];
    public function order_goods($data){
        $tmp = array();
        foreach($data['orders'] as $k=>$v){
            $tmp[] = ['goods_name'=>$v['title'], 'goods_thumb'=>$v['pic_path']];
        }
        return json_encode($tmp);
    }
    public function order_url($data){
        return "https://h5.youzan.com/wsctrade/order/detail?order_no={$data['order_info']['tid']}&kdt_id=41198500";
    }
    public function order_goods_num($data){
        return count($data['orders']);
    }
    public function sku_id($data){
        return $data['orders'][0]['sku_id'];
    }
    public function total_fee($data){
        return $data['pay_info']['payment'];
    }
    public function order_time($data){
    //  var_dump($data);
        return $data['order_info']['created'];
    }
    public function order_status($data){
        return $data['order_info']['status_str'];
    }
}
$dataHelper = YzyData::getHelper();

$data = $dataHelper->reverseTransform([['sku_id'=>2]]);
var_dump($data);