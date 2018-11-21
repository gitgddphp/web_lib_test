<?php
namespace Jar\Services;

use Jar\Facades\Role;
class JuService extends Service {

    public function __construct(){
        parent::__construct();
        $this->model = D('Jar/Ju');
    }
//
    public function add(Array $data)
    {
        $data['ju_date']=date('Y-m-d H:i:s');
        $data['ju_deadline']=date('Y-m-d H:i:s',strtotime($data['ju_date']." + ".$data['ju_timelimit']." month"));
        return parent::add($data);
    }

    /**
     * 获取自己的酒罐列表-分页
     * @return mixed
     */
    public function getOwnJarListWithPage(Array $page){
        return $this->getListWithPage(['ju_u_id'=>Role::getuser_id()],[$page[0],$page[1]]);
    }

    /**
     * 根据罐子id获取我自己的罐装酒
     * @param Int id 罐子id
     */
    public function getOwnJarByJarId($id){
        if (intval($id)){
            $data = $this->getList(['ju_u_id'=>Role::getuser_id(),'ju_jar_id'=>$id],function ($model){
                $model->alias('ju')
                    ->field('ju.*,jar.*,ju.id as ju_id,jar.id as jar_id')
                    ->join('ty_jar jar ON ju_jar_id = jar.id');
            });
            return $data[0];
        }
    }

    /**
     * 获取我自己的罐装酒
     */
    public function getOwnJarList(){
        $data = $this->getList(['ju_u_id'=>Role::getuser_id()],function ($model){
            $model->alias('ju')
                ->field('ju.*,jar.*,ju.id as ju_id,jar.id as jar_id ,count(1) as jar_ticket_num,jt.id as jar_ticket')
                ->join('ty_jar jar ON ju_jar_id = jar.id')
                ->join('ty_jar_ticket jt ON jar.id = jt.ticket_jar_id',"LEFT")
                ->group('jt.ticket_jar_id');;
        });
        foreach ($data as $k=>$datum){
            $data[$k]['jar_image']=$_SERVER['REQUEST_SCHEME']."://".$_SERVER['HTTP_HOST'].'/'.$datum['jar_image'];
            $data[$k]['jar_thumb_img']=$_SERVER['REQUEST_SCHEME']."://".$_SERVER['HTTP_HOST'].'/'.$datum['jar_thumb_img'];
            if (!$datum['jar_ticket']){
                $data[$k]['jar_ticket_num'] = 0;
            }
        }
        return $data;
    }

    /**
     * 根据jar_id 获取ju_id
     */
    public function getCurrentJuId($jar_id,$u_id){
        return $this->find(['ju_jar_id'=>$jar_id,'ju_u_id'=>$u_id],function ($model){
            $model->order('id desc');
        })['id'];
    }





}