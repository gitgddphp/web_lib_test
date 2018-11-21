<?php
namespace Jar\Services;

use Closure;
//use Think\Model;
class Service{
    public $model;
    public static $errors=null;

    public function __construct(){
        $this->model = M();
    }

    public function add(Array $data){
        if (!$this->model->create($data)){
            static::$errors = $this->model->getError();
            return false;
        }else{
            $id = $this->model->add();
            return $id;
        }
    }

    public function addAll($data){
        if(!$id = $this->model->addAll($data)){
            static::$errors = $this->model->getError();
            return false;
        }
        return $id;
    }

    public function getLastInsID(){
        return $this->model->getLastInsID();
    }

    /**
     * 获取酒罐列表
     * @param Array map 查询条件
     * @param Closure cb 回调函数,处理额外的查询
     */
    public function getList(Array $map,Closure $cb = null){
        $this->model->where($map);
        if ($cb){
            $cb($this->model);
        }
        if ($data = $this->model->select()){
            return $data;
        }else{
            return [];
        }
    }

    /**
     * 根据主键获取一条数据
     * @param $id
     */
    public function get($id){
        return $this->model->find($id);
    }

    public function setDec($map,$data,Closure $cb = null){
        $this->model->where($map);
        if ($cb){
            $cb($this->model);
        }
        if (!$this->model->setDec($data[0],$data[0])){
            static::$errors = $this->model->getError();
            return false;
        }
        return true;
    }

    /**
     * 根据条件查询一条数据
     * @param array $map
     * @param Closure|null $cb
     * @return array
     */
    public function find(Array $map,Closure $cb = null){
        $this->model->where($map);
        if ($cb){
            $cb($this->model);
        }
        return $this->model->find();
    }

    /**
     * 获取酒罐列表分页
     * @param Array $map 条件
     * @param Integer $p 当前页
     * @param Integer $pagesize 一页数量
     */
    public function getListWithPage(Array $map,Array $page=[],Closure $cb = null){
        return $this->getList($map,function ($model) use ($page,$cb){
            $model->page($page[0],$page[1]);
            $cb($model);
        });
    }

    public function getCount(Array $map){
        return $this->model->where($map)->count();
    }

    public function update(Array $map,Array $data){
        if (!$this->model->where($map)->save($data)){
            static::$errors = $this->model->getError();
            return false;
        }
        return true;
    }

    public function getLastSql(){
        return $this->model->getLastSql();
    }

    public function adm_update(Array $map,Array $data){
        if (!$this->model->create($data)){
            static::$errors = $this->model->getError();
            return false;
        }
        return $this->model->save();
    }

    public function getError(){
        return static::$errors;
    }

    public function emit($event,$param){
        if ($this->model){
            $this->model->emit($event,$param);
        }
    }

    /**
     * 开启事务
     */
    public function startTrans(){
        $this->model->startTrans();
    }

    /**
     * 提交事务
     */
    public function commit(){
        $this->model->commit();
    }

    /**
     * 事务回滚
     */
    public function rollback(){
        $this->model->rollback();
    }

    public function data(){
        $this->model->data;
    }
}
?>