<?php
return array(
    'LOG_RECORD' => true,
    'LOG_LEVEL' =>'EMERG,ALERT,CRIT,ERR,SQL',
	//'配置项'=>'配置值'
    'LANG_SWITCH_ON' => true,// 开启语言包功能
    'LANG_AUTO_DETECT' => true,// 自动侦测语言 开启多语言功能后有效
    'DEFAULT_LANG' => 'zh-cn',// 默认语言包
    'LANG_LIST' => 'zh-cn',// 允许切换的语言列表 用逗号分隔
    'VAR_LANGUAGE' => 'l',// 默认语言切换变量


    'TOKEN_ON'=>false,
    'HOST_XCX'=>'https://xcx.yijiutiancheng.com/',
    'SMS'=>array(
        'sms_product'=>'',//短信内容：公司名/名牌名/产品名
        'sms_appkey'=>'',//App Key的值
        'sms_secretKey'=>'',//App Secret
        'sms_templateCode'=>'',//短信模板ID，
    ),
    'Jar'=>array(
        'NULL'      =>  0,      //未出售
        'HALF'      =>  10,     //管理员绑定电话号码和用户姓名
        'SALED'     =>  1,      //用户与酒坛已绑定
        'GET'       =>  2,      //用户已开坛取酒
        'GETALL'    =>  3,      //酒坛已取空
        'APPLY'     =>  4       //申请退出
    ),
    'Ticket'=>array(
        'NOTUSED'   =>  0,      //未使用
        'USED'      =>  1,      //已使用(最终状态)
        'ASKFOR'    =>  2,      //申请领酒
        'SENDING'   =>  3,      //已发酒
        'BACKING'   =>  5,      //退货中
        'INVALID'   =>  6       //无效
    ),
    'Distribut'=>array(
        'FREEZE'    =>  0,      //
        'NORMAL'    =>  1,
        'INVALID'   =>  2,
    ),
    'Ju'=>array(
        'INVALID'   =>  0,      //无效的
        'NORMAL'    =>  1,      //正常的
        'USERBACK'  =>  2,      //用户取消(没用)
        'ERROR'     =>  3       //错误
    ),
);