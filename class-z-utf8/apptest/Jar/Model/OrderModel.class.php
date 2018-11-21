<?php
namespace Jar\Model;

class OrderModel extends BaseModel{
    protected $_validate = array(
//        array('jar_sn','require','jar_sn::串码必须填写',1)
    );

    public function __construct(){
        parent::__construct();
    }
}
?>