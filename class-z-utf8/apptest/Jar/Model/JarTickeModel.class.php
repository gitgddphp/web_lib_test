<?php
namespace Jar\Model;

use Jar\Observers\JarTicket;
class JarTicketModel extends BaseModel{
    protected $tableName = 'jar_ticket';
    protected $_validate = array(
        array('ticket_name','require','领酒券名称必须填写',1), //默认情况下用正则进行验证
        array('ticket_jar_id','require','领酒券的酒罐id必须填写',0,"unique"), //默认情况下用正则进行验证
        array('ticket_act_id','require','领酒券的操作者id必须填写',), // 在新增的时候验证name字段是否唯一
    );

    public function __construct(){
        parent::__construct();
    }
}
?>