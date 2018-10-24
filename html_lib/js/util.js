//js基础库 工具包
//浏览器信息
var Browser = new Object();

Browser.isMozilla = (typeof document.implementation != 'undefined') && (typeof document.implementation.createDocument != 'undefined') && (typeof HTMLDocument != 'undefined');
Browser.isIE = window.ActiveXObject ? true : false;
Browser.isFirefox = (navigator.userAgent.toLowerCase().indexOf("firefox") != - 1);
Browser.isSafari = (navigator.userAgent.toLowerCase().indexOf("safari") != - 1);
Browser.isOpera = (navigator.userAgent.toLowerCase().indexOf("opera") != - 1);

var Utils = new Object();

Utils.htmlEncode = function(text)
{
  return text.replace(/&/g, '&amp;').replace(/"/g, '&quot;').replace(/</g, '&lt;').replace(/>/g, '&gt;');
}

Utils.trim = function( text )
{
  if (typeof(text) == "string")
  {
    return text.replace(/^\s*|\s*$/g, "");
  }
  else
  {
    return text;
  }
}

Utils.isEmpty = function( val )
{
  switch (typeof(val))
  {
    case 'string':
      return Utils.trim(val).length == 0 ? true : false;
      break;
    case 'number':
      return val == 0;
      break;
    case 'object':
      return val == null;
      break;
    case 'array':
      return val.length == 0;
      break;
    default:
      return true;
  }
}

Utils.isNumber = function(val)
{
  var reg = /^[\d|\.|,]+$/;
  return reg.test(val);
}

Utils.isInt = function(val)
{
  if (val == "")
  {
    return false;
  }
  var reg = /\D+/;
  return !reg.test(val);
}

Utils.isEmail = function( email )
{
  var reg1 = /([\w-\.]+)@((\[[0-9]{1,3}\.[0-9]{1,3}\.[0-9]{1,3}\.)|(([\w-]+\.)+))([a-zA-Z]{2,4}|[0-9]{1,3})(\]?)/;

  return reg1.test( email );
}

Utils.isTel = function ( tel )
{
  var reg = /^[\d|\-|\s|\_]+$/; //只允许使用数字-空格等

  return reg.test( tel );
}

/*是否为字母和数字（字符集）*/
Utils.IsLetters = function( text )
{
    var reg = /^(?=.*[a-zA-Z]+)(?=.*[0-9]+)[a-zA-Z0-9]+$/ ;
    return reg.test( text );
    
}
Utils.fixEvent = function(e)
{
  var evt = (typeof e == "undefined") ? window.event : e;
  return evt;
}

Utils.srcElement = function(e)
{
  if (typeof e == "undefined") e = window.event;
  var src = document.all ? e.srcElement : e.target;

  return src;
}

Utils.isTime = function(val)
{
  var reg = /^\d{4}-\d{2}-\d{2}\s\d{2}:\d{2}:\d{2}$/;
  return reg.test(val);
}

Utils.x = function(e)
{ //当前鼠标X坐标
    return Browser.isIE?event.x + document.documentElement.scrollLeft - 2:e.pageX;
}

Utils.y = function(e)
{ //当前鼠标Y坐标
    return Browser.isIE?event.y + document.documentElement.scrollTop - 2:e.pageY;
}

Utils.request = function(url, item)
{
	var sValue=url.match(new RegExp("[\?\&]"+item+"=([^\&]*)(\&?)","i"));
	return sValue?sValue[1]:sValue;
}

Utils.$ = function(name)
{
    return document.getElementById(name);
}

Utils.isPhone = function ( mobile_phone )
{
  var reg = /^(((13[0-9]{1})|(14[0-9]{1})|(15[0-9]{1})|(16[0-9]{1})|(17[0-9]{1})|(18[0-9]{1})|(19[0-9]{1}))+\d{8})$/;

  return reg.test( mobile_phone );
}

//获取行数
function rowindex(tr)
{
  if (Browser.isIE)
  {
    return tr.rowIndex;
  }
  else
  {
    table = tr.parentNode.parentNode;
    for (i = 0; i < table.rows.length; i ++ )
    {
      if (table.rows[i] == tr)
      {
        return i;
      }
    }
  }
}

document.getCookie = function(sName)
{
  // cookies are separated by semicolons
  var aCookie = document.cookie.split("; ");
  for (var i=0; i < aCookie.length; i++)
  {
    // a name/value pair (a crumb) is separated by an equal sign
    var aCrumb = aCookie[i].split("=");
    if (sName == aCrumb[0])
      return decodeURIComponent(aCrumb[1]);
  }

  // a cookie with the requested name does not exist
  return null;
}

document.setCookie = function(sName, sValue, sExpires)
{
  var sCookie = sName + "=" + encodeURIComponent(sValue);
  if (sExpires != null)
  {
    sCookie += "; expires=" + sExpires;
  }

  document.cookie = sCookie;
}

document.removeCookie = function(sName,sValue)
{
  document.cookie = sName + "=; expires=Fri, 31 Dec 1999 23:59:59 GMT;";
}

function getPosition(o)
{
    var t = o.offsetTop;
    var l = o.offsetLeft;
    while(o = o.offsetParent)
    {
        t += o.offsetTop;
        l += o.offsetLeft;
    }
    var pos = {top:t,left:l};
    return pos;
}

function cleanWhitespace(element)
{
  var element = element;
  for (var i = 0; i < element.childNodes.length; i++) {
   var node = element.childNodes[i];
   if (node.nodeType == 3 && !/\S/.test(node.nodeValue))
     element.removeChild(node);
   }
}

//js 节点添加  可用于jsonp 进行跨域请求数据
function addScript(src, func){
    var script=document.createElement("script");
    script.setAttribute("type","text/javascript");
    script.setAttribute("id","json_js");
    script.src=src;

    if (script.readyState){//IE
        script.onreadystatechange = function(){
            if (script.readyState == 'loaded' || script.readyState == 'complete'){
                script.onreadystatechange = null;
                try{
                //    console.log('rturn:',jsonp()); 
                    if(typeof func == 'function'){
                        var json = jsonp();
                        json.status = 1;
                        func(json);
                    }  
                }catch(e){
                    func({status:0,error:'no!'});
                }
                
            }
        }
    }else{
        script.onload = function(){
            try{
            //    console.log('rturn:',jsonp()); 
                if(typeof func == 'function'){
                    var json = jsonp();
                    json.status = 1;
                    func(json);
                }  
            }catch(e){
                if(typeof func == 'function'){
                    func({status:0,error:'no!'});
                }  
            }
            
        }
    }
    document.head.appendChild(script);
}
//更新js 节点 路径
function changeScript(id,src){
    $('#'+id).remove();
    var script=document.createElement("script");
    script.setAttribute("type","text/javascript");
    script.setAttribute("id","json_js");
    script.setAttribute("src",src);
    
    if (script.readyState){//IE
        script.onreadystatechange = function(){
            if (script.readyState == 'loaded' || script.readyState == 'complete'){
                script.onreadystatechange = null;
                try{
                    console.log(jsonp());   
                }catch(e){
                    console.log('no!');
                }
                
            }
        }
    }else{
        script.onload = function(){
            try{
                console.log(jsonp());   
            }catch(e){
                console.log('no!');
            }
        }
    }
    document.head.appendChild(script);
}

//记录用户的浏览记录 依赖jquery.cookie.js
function GHistory(){
    var second = 0;
    this.init= function(cfg){
        window.setInterval(function () {
            second ++;
        }, 1000);
        if(cfg.submitUrl){
            this.submitUrl = cfg.submitUrl;
        }
        var tjArr = localStorage.getItem("jsArr") ? localStorage.getItem("jsArr") : '[{}]';
        $.cookie('tjRefer', getReferrer() ,{expires:1,path:'/'});
    }
    this.getUrlList = function(){
        var tjArr = localStorage.getItem("jsArr") ? localStorage.getItem("jsArr") : '[{}]';    
        return tjArr;
    }
    window.onbeforeunload = function() {
        if($.cookie('tjRefer') == ''){
            var tjT = eval('(' + localStorage.getItem("jsArr") + ')');
            if(tjT){
                tjT[tjT.length-1].time += second;
                var jsArr= JSON.stringify(tjT);
                localStorage.setItem("jsArr", jsArr);
            }
        } else {
            var tjArr = localStorage.getItem("jsArr") ? localStorage.getItem("jsArr") : '[{}]';
            var dataArr = {
                'url' : location.href,
                'time' : second,
                'refer' : getReferrer(),
                'timeIn' : Date.parse(new Date()),
                'timeOut' : Date.parse(new Date()) + (second * 1000)
            };
            tjArr = eval('(' + tjArr + ')');
            tjArr.push(dataArr);
            tjArr= JSON.stringify(tjArr);
            localStorage.setItem("jsArr", tjArr);
        }
    };
    function getReferrer() {
        var referrer = '';
        try {
            referrer = window.top.document.referrer;
        } catch(e) {
            if(window.parent) {
                try {
                    referrer = window.parent.document.referrer;
                } catch(e2) {
                    referrer = '';
                }
            }
        }
        if(referrer === '') {
            referrer = document.referrer;
        }
        return referrer;
    }
}
//var his = new GHistory();
//his.init({submitUrl:'http://my.kf1.com/operator/updateurllist'});