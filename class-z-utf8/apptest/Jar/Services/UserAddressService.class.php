<?php
namespace Jar\Services;

use Jar\Facades\Role;
class UserAddressService extends Service {

    public function __construct(){
        parent::__construct();
        $this->model = D('Jar/UserAddress');
    }

    public function add(Array $data)
    {
        return parent::add($data);
    }
}