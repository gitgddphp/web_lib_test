<?php
namespace Jar\Model;

use Jar\Observers\Jar;
class JarModel extends BaseModel{
    protected $_validate = array(
        array('jar_sn','require','jar_sn::串码必须填写',1), //默认情况下用正则进行验证
        array('jar_sn','require','jar_sn::串码不能重复',0,"unique"), //默认情况下用正则进行验证
        array('jar_name','require','jar_name::名称必须填写！',), // 在新增的时候验证name字段是否唯一
        array('jar_capacity','require','jar_capacity::容量必须填写！',1,'',1), // 当值不为空的时候判断是否在一个范围内
        array('jar_cellar_age','require','jar_cellar_age::窖龄必须填写',1,'',1), // 验证确认密码是否和密码一致
        array('jar_cost','require','jar_cost::价格必须填写',1,'',1), // 验证确认密码是否和密码一致
        array('jar_material','require','jar_material::原料必须填写',1,'',1), // 验证确认密码是否和密码一致
    );

    public function __construct(){
        parent::__construct();
    }
}
?>