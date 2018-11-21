<?php
namespace Jar\Services;

use Jar\Facades\Role;
class OrderGoodsService extends Service {

    public function __construct(){
        parent::__construct();
        $this->model = D('Jar/OrderGoods');
    }

    public function add(Array $data)
    {
        return parent::add($data);
    }


}