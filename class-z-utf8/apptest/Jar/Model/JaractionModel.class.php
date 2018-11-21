<?php
namespace Jar\Model;

class JaractionModel extends BaseModel{
    protected $_validate = array(
        array('jaraction_u_id','require','用户id必须填写',1), //默认情况下用正则进行验证
        array('jaraction_ju_id','require','用户关联表id必须填写',1), //默认情况下用正则进行验证
        array('jaraction_act_id','require','操作者id必须填写',1), //默认情况下用正则进行验证
        array('jaraction_jar_id','require','酒坛id必须填写！',), // 在新增的时候验证name字段是否唯一
        array('jaraction_action','require','操作名称必须填写！',1,'',1), // 当值不为空的时候判断是否在一个范围内
        array('jaraction_comment','require','操作详情必须填写',1,'',1), // 验证确认密码是否和密码一致
    );

    public function __construct(){
        parent::__construct();
    }
}

?>