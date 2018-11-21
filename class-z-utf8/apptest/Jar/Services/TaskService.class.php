<?php
namespace Jar\Services;

use Jar\Facades\Role;
class TaskService extends Service {

    public function __construct(){
        parent::__construct();
        $this->model = null;
    }

    public function run()
    {
    	$time = file_get_contents('runtime.txt');
    	if($time > time()-60*5){ return false; }
    	file_put_contents('runtime.txt',time());
    	$task = C('Task');
    	foreach($task as $item){ eval($item.'();'); }
    }
}