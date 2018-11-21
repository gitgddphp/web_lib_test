<?php
namespace Jar\Services;

use Jar\Facades\Role;
use Jar\Utils\Utils;
class UsersService extends Service {

    public function __construct(){
        parent::__construct();
        $this->model = D('Jar/Users');
    }

    /**
     * 绑定自己的电话号码及真实姓名 (废弃)
     * @param $phone
     * @param $name
     * @param $code
     */
    public function bindPhoneAndName($phone,$name,$code){
        if (Utils::is_moblie($phone)&&!empty(trim($name))){
            if ($this->checkSmsCode($phone,$code)){
                $res = $this->update(['user_id'=>Role::getuser_id()],['realname'=>trim($name),'mobile'=>$phone]);
                Role::init(array_merge(Role::getAll(),['realname'=>trim($name),'mobile'=>$phone]));//重置用户的信息
                return $res;
            }else{
                self::$errors=L('_USER_INVALID_SMS_CODE_');
            }
        }else{
            self::$errors=L('_USER_INVALID_PHONE_');
        }
        return false;
    }

    /**
     * 分销-获取用户上级
     * @param Int id 用户id
     */

    public function getOnDistributUser($id){
        if ($id){
            $data = $this->get($id);
            if ($data){
                $users = array();
                $data['first_leader']?array_push($users,$data['first_leader']):0;
                $data['second_leader']?array_push($users,$data['second_leader']):0;
                $data['third_leader']?array_push($users,$data['third_leader']):0;
                if (!empty($users)){
                    $map['user_id'] = array('in',$users);
                    $return = array();
                    if ($userDatas = $this->getList($map)){
                        foreach ($userDatas as $item){
                            switch ($item['user_id']){
                                case $data['first_leader']:
                                    $return['first_leader'] = $item;
                                    break;
                                case $data['second_leader']:
                                    $return['second_leader'] = $item;
                                    break;
                                case $data['third_leader']:
                                    $return['third_leader'] = $item;
                                    break;
                                default:
                                    break;
                            }
                        }
                    }
                    return $return;
                }
                return [];
            }
        }
        self::$errors = L('_USER_INVALID_ID_');
    }

    /**
     * 检查手机验证码
     * @param $phone
     * @param $code
     * @return mixed
     */
    public function checkSmsCode($phone,$code){
        $map['mobile']=$phone;
        $map['code']=$code;
        $map['add_time']=array('gt',(time()-(60*60)));
        return M('sms_log')->where($map)->order('add_time desc')->find();
    }

    /**
     * 初始化用户密码
     * @param String pass 密码
     */
    public function initUserPass($pass){
        if ($pass = trim($pass)){
            if ($data = $this->get(Role::getuser_id())){
                if (empty($data['password'])){
                    return $this->update(['user_id'=>Role::getuser_id()],['password'=>\encrypt($pass)]);
                }else{
                    self::$errors = L('_USER_READY_EXIST_PASS_');
                    return false;
                }
            }
        }
        self::$errors = L('_USER_INVALID_PASS_');
    }

    /**
     * 修改用户密码
     * @param String newpass 密码
     * @param String oldpass 密码
     */
    public function changePass($newpass,$oldpass){
        if ($pass = trim($newpass)){
            if ($data = $this->get(Role::getuser_id())){
                if ($data['password'] == \encrypt($oldpass)){
                    return $this->update(['user_id'=>Role::getuser_id()],['password'=>\encrypt($newpass)]);
                }else{
                    self::$errors = L('_USER_ERROR_EXIST_PASS_');
                    return false;
                }
            }
        }
        self::$errors = L('_USER_INVALID_PASS_');
    }

    /**
     * 检查用户密码是否存在
     * @return mixed
     */
    public function checkUserPassExist(){
        return $this->get(Role::getuser_id())['password'];
    }

    /**
     * 检验用户密码
     * @param String pass 密码
     */
    public function checkPass($pass){
        if ($pass = trim($pass)){
            if ($data = $this->get(Role::getuser_id())){
                if ($data['password'] == \encrypt($pass)){
                    return true;
                }else{
                    self::$errors = L('_USER_ERROR_PASS_');
                    return false;
                }
            }
        }
        self::$errors = L('_USER_INVALID_PASS_');
        return false;
    }
}