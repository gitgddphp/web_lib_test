<?php
namespace Jar\Model;

use Jar\Observers\Ju;
class JuModel extends BaseModel{
    protected $_validate = array(
        array('ju_u_id','require','用户id必须填写',1), //默认情况下用正则进行验证
        array('ju_jar_id','require','酒罐必须填写',0), //默认情况下用正则进行验证
        array('ju_deadline','require','截止时间必须填写',0), //默认情况下用正则进行验证
        array('ju_timelimit','require','年限必须填写',0), //默认情况下用正则进行验证
    );

    public function __construct(){
        parent::__construct();
    }
}