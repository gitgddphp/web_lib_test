<?php
namespace Jar\Controller;

use Jar\Facades\Goods;
use Jar\Facades\Jar;
use Jar\Facades\Ju;
use Jar\Facades\JarTicket;
use Jar\Facades\Role;
use Jar\Facades\Users;
use Jar\Facades\Jaraction;
use Jar\Facades\JarDistribut;
use Jar\Facades\Order;
use Jar\Facades\Pay;
use Jar\Facades\TransPort;
use Jar\Facades\Task;

class IndexController extends BaseController{
    public function __construct()
    {
        parent::__construct();
        $this->taskRun();
    }

    public function index(){
        prs($_SERVER);
    }

    /**
     * 添加罐子
     * @param String jar_sn        串号
     * @param String jar_name       酒名字
     * @param String jar_capacity   总容量
     * @param String jar_unit       单位
     * @param String jar_cellar_age 窖龄
     * @param String jar_cost       价值
     * @param String jar_material   原料
     * @param String jar_quality    质量等级
     * @param String jar_intro      简介
     * @param Int user_id 用户id
     * @param String user_name 用户姓名
     * @param Int ju_timelimit 期限
     */
    public function ajaxAddJar(){
        $id = Jar::addJar(I('post.'));
        if ($id){
            $this->ajaxReturn(['info'=>'ok','status'=>1,'data'=>$id]);
        }else{
            $this->ajaxReturn(['info'=>Jar::getError(),'status'=>0,'data'=>[]]);
        }
    }

    public function ajaxUpload(){
        $res = Jar::Upload();
        if ($res){
            $this->ajaxReturn(['info'=>'ok','status'=>1,'data'=>$res['img']['urlpath']]);
        }else{
            $this->ajaxReturn(['info'=>Jar::getError(),'status'=>0,'data'=>[]]);
        }
    }

    /**
     * 获取酒罐-分页
     * @param Int pagenow 当前页
     * @param Int pagesize 一页大小
     */
    public function ajaxGetJarList(){
        $list = Jar::getListWithPage([],[I('post.pagenow'),I('post.pagesize')]);
        $this->ajaxReturn(['info'=>'ok','status'=>1,'data'=>$list]);
    }

    /**
     * 获取根据串号酒罐信息
     * @param String jar_sn 串号
     */
    public function ajaxGetJarBySn(){
        $this->ajaxReturn(['info'=>'ok','status'=>1,'data'=>Jar::getJarDetailBySn(I('post.jar_sn',0))]);
    }

    /**
     * 获取id串号酒罐信息
     * @param String id 串号
     */
    public function ajaxGetJarById(){
        $this->ajaxReturn(['info'=>'ok','status'=>1,'data'=>Jar::getJarDetailById(I('post.id',0))]);
    }

    /**
     * 买酒,绑定酒罐到人
     * @param Int u_id 用户id
     * @param Int jar_id 酒罐id
     */
    public function ajaxJarBuy(){
        $id = Ju::add(I('post.'));
        if ($id){
            $this->ajaxReturn(['info'=>'ok','status'=>1,'data'=>$id]);
        }else{
            $this->ajaxReturn(['info'=>Ju::getError(),'status'=>0,'data'=>[]]);
        }
    }

    /**
     * 管理员
     * @param Int jar_id 酒罐id
     * @param String phone 电话号码
     */
    public function ajaxBuyJarBySeller(){
        if($res = Jar::buyBySeller(I('post.jar_id',0),I('post.phone'),I('post.name'))){
            $this->ajaxReturn(['info'=>'ok','status'=>1,'data'=>$res]);
        }else{
            $this->ajaxReturn(['info'=>Jar::getError(),'status'=>0,'data'=>[]]);
        }
    }

    /**
     * 用户确认绑定封坛酒
     * @param Int jar_id    封坛酒id
     * @param Int phone     电话号码
     * @param Int name      姓名
     */
    public function ajaxConfirmJarByUser(){
        Users::bindPhoneAndName(I('post.phone'),I('post.name'));
        $res = Jar::confirmByUser(I('post.jar_id',0));
        if ($res){
            $this->ajaxReturn(['info'=>'ok','status'=>1,'data'=>[]]);
        }else{
            $this->ajaxReturn(['info'=>Jar::getError(),'status'=>0,'data'=>[]]);
        }
    }

    /**
     * 获取个人自己的酒罐列表
     */
    public function ajaxGetOwnJarList(){
        $res = Ju::getOwnJarList(I('post.jar_id',0));
        if ($res){
            $this->ajaxReturn(['info'=>'ok','status'=>1,'data'=>$res]);
        }else{
            $this->ajaxReturn(['info'=>Ju::getError(),'status'=>0,'data'=>[]]);
        }
    }

    /**
     * 绑定用户的手机号和真实姓名
     */
    public function ajaxBindPhoneName(){
        if (Users::bindPhoneAndName(I('post.phone'),I('post.name'))){
            $this->ajaxReturn(['info'=>'ok','status'=>1,'data'=>[]]);
        }else{
            $this->ajaxReturn(['info'=>Users::getError(),'status'=>0,'data'=>[]]);
        }
    }

    /**
     * 获取酒坛操作记录
     */
    public function getJarLog(){
        $map=array();
//        I('post.jar_status') !== 'all'?$map['jar.jar_status']=I('jar_status'):0;
//        I('post.jar_sn')?$map['jar.jar_sn']=I('jar_sn'):0;
//        I('post.ju_date')?$map['ju.ju_date']=array(I('post.ju_date_order'),I('post.ju_date')):0;
//        I('post.ju_deadline')?$map['ju.ju_deadline']=array(I('post.ju_deadline_order'),I('post.ju_deadline')):0;
//        I('post.user_name')?$map['u.realname']=array('like','%'.I('post.user_name').'%'):0;
//        I('post.mobile')?$map['u.mobile']=I('post.mobile'):0;
        $map['u.mobile'] = Role::getmobile();
        $data = Jaraction::getList($map,function($model){
            $model->alias('jac')
                ->field('jar.jar_sn,jar.jar_status,u.realname,jac.jaraction_date,jac.jaraction_comment,jac.jaraction_action,ju.ju_status')
                ->join('ty_jar jar on jar.id = jac.jaraction_jar_id')
                ->join('ty_ju ju on ju.id = jac.jaraction_ju_id')
                ->join('ty_users u on u.user_id = jac.jaraction_u_id');
        });
        $this->ajaxReturn(['info'=>'ok','status'=>1,'data'=>$data]);
    }

    /**
     * 获取领酒券
     * @param Int id 酒罐id
     * @param Int num   取酒量
     * @param String package 包装信息
     */
    public function ajaxGetTicket(){
        if($data = JarTicket::getTicket(I('post.id',0),I('post.num'),json_decode($_POST['package'],true))){
            $this->ajaxReturn(['info'=>'ok','status'=>1,'data'=>$data]);
        }else{
            $this->ajaxReturn(['info'=>JarTicket::getError(),'status'=>0,'data'=>[]]);
        }
    }

    /**
     * 获取分销提成的领酒券
     * @param Int id 酒罐id
     * @param Int num   取酒量
     */
    public function ajaxGetDistributTicket(){
        if($data = JarTicket::getDistributTicket(I('post.id',0),I('post.num'))){
            $this->ajaxReturn(['info'=>'ok','status'=>1,'data'=>$data]);
        }else{
            $this->ajaxReturn(['info'=>JarTicket::getError(),'status'=>0,'data'=>[]]);
        }
    }

    /**
     * 自己取酒
     * @param Int id 酒罐id
     * @param Int num   取酒量
     * @param Int addressId   地址id
     * @param String package 包装信息
     */
    public function ajaxGetOwnJar(){
        $package = json_decode($_POST['package'],true);
        if($data = JarTicket::getTicket(I('post.id',0),I('post.num'))){
            if($id = JarTicket::useTicket($data['id'],I('post.addressId',0),$package)){
                $this->ajaxReturn(['info'=>'ok','status'=>1,'data'=>Pay::getWXPayData($id)]);
            }
        }
        $this->ajaxReturn(['info'=>JarTicket::getError(),'status'=>0,'data'=>[]]);
    }

    /**
     * 使用领酒券
     * @param Int id 领酒券id
     * @param Int addressId   地址id
     * @param String package 包装信息
     */
    public function ajaxUseTicket(){
        $package = json_decode($_POST['package'],true);
        if($id = JarTicket::useTicket(I('post.id',0),I('post.addressId',0),$package)){
            $this->ajaxReturn(['info'=>'ok','status'=>1,'data'=>Pay::getWXPayData($id)]);
        }else{
            $this->ajaxReturn(['info'=>JarTicket::getError(),'status'=>0,'data'=>[]]);
        }
    }

    /**
     * 使用已支付包装费的领酒券
     * @param Int id 领酒券id
     * @param Int id 收货地址id
     */
    public function ajaxUsePaidTicket(){
        if($id = JarTicket::usePaidTicket(I('post.id',0),I('post.addressId',0))){
            $this->ajaxReturn(['info'=>'ok','status'=>1,'data'=>[]]);
        }else{
            $this->ajaxReturn(['info'=>JarTicket::getError(),'status'=>0,'data'=>[]]);
        }
    }

    /**
     * 使用领酒券
     * @param Int id 领酒券id
     * @param Int addressId   地址id
     * @param String password 密码
     */
    public function ajaxUserDistributTicket(){
        if($data = JarTicket::useDistributTicket(I('post.id',0),I('post.addressId',0),I('post.password',''))){
            $this->ajaxReturn(['info'=>'ok','status'=>1,'data'=>[]]);
        }else{
            $this->ajaxReturn(['info'=>JarTicket::getError(),'status'=>0,'data'=>[]]);
        }
    }

    /**
     * 领酒券详情
     * @param Int id 领酒券id
     */
    public function ajaxGetTicketInfoById(){
        if($data = JarTicket::getTicketInfoById(I('post.id',0))){
            $this->ajaxReturn(['info'=>'ok','status'=>1,'data'=>$data]);
        }else{
            $this->ajaxReturn(['info'=>JarTicket::getError(),'status'=>0,'data'=>[]]);
        }
    }

    /**
     * 用户取消领酒券
     * @param Int id 领酒券id
     */
    public function ajaxSetInvalidByUser(){
        if ($id = I('post.id',0,'intval')){
            if(JarTicket::setInvalidByUser($id)){
                $this->ajaxReturn(['info'=>'ok','status'=>1,'data'=>[]]);
            }else{
                $this->ajaxReturn(['info'=>JarTicket::getError(),'status'=>0,'data'=>[]]);
            }
        }
    }

    /**
     * 获取包装列表
     */
    public function ajaxGetBzList(){
        if ($data = Goods::getGoodsList()){
            $this->ajaxReturn(['info'=>'ok','status'=>1,'data'=>$data]);
        }else{
            $this->ajaxReturn(['info'=>Goods::getError(),'status'=>1,'data'=>[]]);
        }
    }

    /**
     * 重置已付邮费的领酒券的收货人地址
     * @param Int id 领酒券id
     * @param Int addressId 领酒券id
     */
    public function ajaxResetOrderConsignee(){
        if($id = JarTicket::resetOrderConsignee(I('post.id',0),I('post.addressId',0))){
            $this->ajaxReturn(['info'=>'ok','status'=>1,'data'=>[]]);
        }else{
            $this->ajaxReturn(['info'=>JarTicket::getError(),'status'=>0,'data'=>[]]);
        }
    }

    /**
     * 领酒券超时失效 最好是定时任务,不会抛异常,
     */
    public function ajaxSetTicketsInvalidByTimeOut(){
        if($data = JarTicket::setInvalidByTimeout()){
            $this->ajaxReturn(['info'=>'ok','status'=>1,'data'=>[]]);
        }else{
            $this->ajaxReturn(['info'=>JarTicket::getError(),'status'=>0,'data'=>[]]);
        }
    }

    public function ajaxgetInfo(){
        prs(Role::getAll());
    }

    public function test(){
        Order::say();
        //prs(JarTicket::setInvalidByTimeout());
    }
    public function ajaxgetOrderlist(){
        //订单列表
        $this->ajaxReturn([
            'info'=>'ok',
            'status'=>1,
            'data'=>Order::getOwnList(I('post.status',0),[I('post.p',0),10])
            ]);
    }
    public function ajaxgetOrder(){
        //订单详情
        $this->ajaxReturn([
            'info'=>'ok',
            'status'=>1,
            'data'=>Order::getOwnOrder(I('post.id'))
            ]);
    }
    public function ajaxgetPay(){
        //获取支付配置信息
        if($data = Pay::getWXPayData(I('post.id',0))){
            $this->ajaxReturn([
            'info'=>'ok',
            'status'=>1,
            'data'=>$data
            ]);
        }else{
            $this->ajaxReturn(['info'=>Pay::getError(),'status'=>0,'data'=>[]]);
        }
    }
    public function ajaxgetTransmoney(){
        if($data = TransPort::getTransMoney(I('post.weight',0), I('post.addressId',0))){
            $this->ajaxReturn([
            'info'=>'ok',
            'status'=>1,
            'data'=>$data[1]
            ]);
        }else{
            $this->ajaxReturn(['info'=>TransPort::getError(),'status'=>0,'data'=>[]]);
        }
    }
    public function ajaxgetTranslist(){
        $this->ajaxReturn([
            'info'=>'ok',
            'status'=>1,
            'data'=>D('plugin')->where(['type'=>'shipping','status'=>1])->select()
            ]);
    }
    public function taskRun(){
        Task::run();
    }
}