<?php
namespace Jar\Services;

use Jar\Facades\Role;
use Jar\Utils\Utils;

class JarTicketActionService extends Service{
    public function __construct(){
        parent::__construct();
        $this->model=D('Jar/JarTicketAction');
    }
    public function add(Array $data)
    {
        $data['ticket_act_id']=isset($data['ticket_act_id'])?$data['ticket_act_id']:Role::getuser_id();
        $data['ticket_act_name']=isset($data['ticket_act_name'])?$data['ticket_act_name']:Role::getrealname();
        $data['ticket_date']=Utils::date("Y-m-d H:i:s");
        return parent::add($data);
    }
}
?>