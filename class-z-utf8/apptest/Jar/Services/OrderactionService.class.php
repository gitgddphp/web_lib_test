<?php
namespace Jar\Services;

use Jar\Facades\Role;
class OrderactionService extends Service {

    public function __construct(){
        parent::__construct();
        $this->model = D('Jar/Orderaction');
    }

}