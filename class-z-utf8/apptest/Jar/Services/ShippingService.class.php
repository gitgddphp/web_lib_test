<?php
namespace Jar\Services;

use Jar\Facades\Role;
class ShippingService extends Service {

    public function __construct(){
        parent::__construct();
        $this->model = D('Jar/Shipping');
    }

    public function add(Array $data)
    {
        return parent::add($data);
    }


}