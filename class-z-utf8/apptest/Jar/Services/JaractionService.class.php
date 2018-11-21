<?php
namespace Jar\Services;

use Jar\Model\JarModel;
use Jar\Facades\Role;
class JaractionService extends Service{
    public function __construct(){
        parent::__construct();
        $this->model=D('Jar/Jaraction');
    }
    public function add(Array $data)
    {
        $data['jaraction_act_id']=isset($data['jaraction_act_id'])?$data['jaraction_act_id']:Role::getuser_id();
        $data['jaraction_date']=date("Y-m-d H:I:s");
        return parent::add($data);
    }


}
?>