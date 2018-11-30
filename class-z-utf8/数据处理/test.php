<?php
class DataModel{
	protected $fieldMap=[];
	public function __construct($fieldsMap = null){
		if($fieldsMap){ $this->fieldMap = $fieldsMap; }
	}
	//所有字段转换补全
	public function transformAll($datas){
		foreach($datas as $k=>$item){
			$tmp[$k] = array();
			foreach($this->fieldMap as $k1=>$item1){
				$tmp[$k][$k1] = $this->{$k1}($item);
			}
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
	//只转换已存在字段的映射
	public function getDataFields($data){
		$tmp = [];
		$fields = $this->reverseMapFields();
		foreach($fields as $k=>$v){
			isset($data[$k]) && $tmp[$v] = $this->fieldMap[$v];
		}
		return $tmp;
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
class YzyData extends DataModel{
	protected $fieldMap=[
			'out_order_id'=>['order_id',0],//字段名和默认值
			'out_form_code'=>['out_form_code','yz'],
			'open_id'=>['open_id',''],
			'open_type'=>['open_type',0],
			'out_user_id'=>['out_user_id',0],
			'delivery_address'=>['delivery_address',0],
			'receiver_name'=>['--',0],
			'delivery_province'=>['--',0],
			'delivery_city'=>['--',0],
			'delivery_district'=>['--',0],
			'receiver_tel'=>['',0],
			'out_transactions_no'=>['',0],
			'total_fee'=>['',0],
			'pay_fee'=>['',0],
			'goods_info'=>['',0],
			'is_activity'=>['',0],
			'activity_type'=>['',0],
			];
	public function open_id($data){
		return 12;
	}
}
//京东订单数据
class JdData extends DataModel{
	protected $fieldMap=[
			'out_order_id'=>['order_id',0],//字段名和默认值
			'out_form_code'=>['out_form_code','yz'],
			'open_id'=>['open_id',''],
			'open_type'=>['open_type',0],
			'out_user_id'=>['out_user_id',0],
			'delivery_address'=>['delivery_address',0],
			'receiver_name'=>['receiver_name',0],
			'delivery_province'=>['delivery_province',0],
			'delivery_city'=>['delivery_city',0],
			'delivery_district'=>['delivery_district',0],
			'receiver_tel'=>['receiver_tel',0],
			'out_transactions_no'=>['out_transactions_no',0],
			'total_fee'=>['total_fee',0],
			'pay_fee'=>['pay_fee',0],
			'goods_info'=>['goods_info',0],
			'is_activity'=>['is_activity',0],
			'activity_type'=>['activity_type',0],
			];
	public function open_id($data){
		return 12;
	}
}

$data = new YzyData();
$data = $data->transform([['order_id'=>['eq',1]]]);
var_dump($data);
exit;