<?php
namespace Jar\Services;

use Jar\Model\JarModel;
use Jar\Facades\Role;
use Jar\Facades\Users;
use Jar\Facades\Jar;

class JarDistributService extends Service{
    public function __construct(){
        parent::__construct();
        $this->model=D('Jar/JarDistribut');
    }
    public function add(Array $data)
    {
        $data['jaraction_act_id']=isset($data['jaraction_act_id'])?$data['jaraction_act_id']:Role::getuser_id();
        $data['jaraction_date']=date("Y-m-d H:I:s");
        return parent::add($data);
    }

    /**
     * 分销(注:这个函数没有捕捉TransException异常,调用的时候,捕捉一下)
     * @param Array juData 酒坛关联关系
     * @return true
     */
    public function distribut(Array $juData){
        $flag = true;
        if ($this->checkDistribut()){
            if($datas = $this->productDistributInfo($juData)){
                foreach ($datas as $data){
                    if (!$this->add($data)){
                        $flag = false;
                    }
                }
            }
        }
        return $flag;
    }

    /**
     * 获取酒坛信息
     * @param Int id 酒坛ID
     */
    public function getJarInfo($id){
        if ($id){
            return Jar::get($id);
        }
        return false;
    }

    /**
     * 分析分销配置文件
     * @return array
     */
    public function analyseConfig(){
        if($data = $this->getDistributConfig()){
            $config = array();
            foreach ($data['rate'] as $k=>$datum){
                if ($datum){
                    $config['rate'][$k] = $datum;
                }else{
                    break;
                }
            }
            if (empty($config['rate'])){
                return array();
            }
            $config['date'] = $data['jar_date'];
            return $config;
        }
    }

    /**
     * 生成分销数据
     * @param array $juData
     */
    public function productDistributInfo(Array $juData){
        if ($configs = $this->analyseConfig()){
            if (!$this->checkExist($juData)){
                $userDatas = Users::getOnDistributUser($juData['ju_u_id']);
                if ($userDatas){
                    if($jarData = $this->getJarInfo($juData['ju_jar_id'])){
                        $saveData = array();
                        foreach ($configs['rate'] as $k => $rate){
                            if ($userDatas[$k]){
                                $data['distribut_jar_id'] = $juData['ju_jar_id'];
                                $data['distribut_jar_name'] = $jarData['jar_name'];
                                $data['distribut_jar_u_id'] = $juData['ju_u_id'];
                                $data['distribut_u_id'] = $userDatas[$k]['user_id'];
                                $data['distribut_capacity'] = floor(($jarData['jar_capacity']*$rate)/100);
                                $data['distribut_freeze'] = $data['distribut_capacity'];
                                $data['distribut_image'] = $jarData['jar_image'];
                                $data['distribut_thumb_img'] = $jarData['jar_thumb_img'];
                                $data['distribut_date'] = date("Y-m-d H:i:s");
                                $data['distribut_valid_date'] = date('Y-m-d H:i:s',strtotime(date("Y-m-d H:i:s").' + '.$configs['date'].' month'));
                                $saveData[] = $data;
                            }
                        }
                        return $saveData;
                    }else{
                        self::$errors = L('_DISTRIBUT_JAR_NOT_EXIST_');
                    }
                }
            }else{
                self::$errors = L('_DISTRIBUT_READ_EXIST_');
            }
        }
    }

    /**
     * 获取分销配置
     */
    public function getDistributConfig(){
        $config = array();
        $confs = tpCache('distribut');
        if($confs){
            $config['rate']['first_leader'] = $confs['jar_first_rate'];
            $config['rate']['second_leader'] = $confs['jar_second_rate'];
            $config['rate']['third_leader'] = $confs['jar_third_rate'];
        }
        $config['jar_date'] = $confs['jar_date'];
        return $config;
    }

    /**
     * 检查该酒罐是否已经产生过分销
     * @param array $juData
     * @param $ids
     * @return mixed
     */
    public function checkExist(Array $juData){
        $map['distribut_jar_u_id'] = $juData['ju_u_id'];
        $map['distribut_jar_id'] = $juData['ju_jar_id'];
        return $this->getList($map);
    }

    /**
     * 检查是否分销
     */
    public function checkDistribut(){
        return tpCache('distribut.jar_switch');
    }

    /**
     * 根据用户的id 获取分销提成列表
     */
    public function getUserDistributById($id){
        return $this->getList(['distribut_u_id'=>$id]);
    }

    /**
     * 获取分销提成列表
     */
    public function getDistributList(){

    }

    /**
     * 使分销提成成为可用状态
     */
    public function completeDistribut(){
        $datas = $this->getList(['distribut_status'=>C('Distribut.FREEZE'),'distribut_valid_date'=>array('egt',date("Y-m-d H:i:s"))]);
        if ($datas){
            foreach ($datas as $data){              //数量不是很大,就循环更新, 数量打了就优化
                $saveData['distribut_status'] = C('Distribut.NORMAL');
                $saveData['distribut_residue'] = $data['distribut_residue'];
                $saveData['distribut_freeze'] = 0;
                $this->update(['id'=>$data['id']],$saveData);
            }
        }
        return true;
    }

    /**
     * 获取自己的一个分成
     */
    public function getOwnDistributById($id){
        if(intval($id)){
            return $this->find(['id'=>$id,'distribut_u_id'=>Role::getuser_id()]);
        }
    }

    /**
     * 根据领酒券还原酒坛数据
     * @param array $ticket
     */
    public function backTicket(Array $ticket){
        if ($ticket){
            if ($data = $this->get($ticket['ticket_distribute_id'])){
                if ($data['distribut_freeze']>=$ticket['ticket_capacity']){
                    $saveData['distribut_residue']= $data['distribut_residue'] + $ticket['ticket_capacity'];
                    $saveData['distribut_freeze']= $data['distribut_freeze'] - $ticket['ticket_capacity'];
                    if ($this->update(['id'=>$ticket['ticket_distribut_id']],$saveData)){
                        $this->emit('JarDistributsetSysBackLog',$ticket);   //日志
                    }
                }
            }

        }
    }

    /**
     * 根据领酒券还原酒坛数据
     * @param array $ticket
     */
    public function backTicketByUser(Array $ticket){
        if ($ticket){
            if ($data = $this->get($ticket['ticket_distribute_id'])){
                if ($data['distribut_freeze']>=$ticket['ticket_capacity']){
                    $saveData['distribut_residue']= $data['distribut_residue'] + $ticket['ticket_capacity'];
                    $saveData['distribut_freeze']= $data['distribut_freeze'] - $ticket['ticket_capacity'];
                    if ($this->update(['id'=>$ticket['ticket_distribut_id']],$saveData)){
                        $this->emit('JarDistributsetUserBackLog',$ticket);   //日志
                        return true;
                    }else{
                        return false;
                    }
                }
            }
        }
        self::$errors = L('_TICKET_INVALID_ID_');
    }
}
?>