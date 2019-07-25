<?php

/**
 * 示例：
 * 1.接收微信推送ticket消息
 * 2.接收微信推送小程序审核消息
 * 3.调用代码
 * 
 * @author jinggz
 * @since 2019-07-25 
 * @version V1.0
 */

// 官方文档：https://open.weixin.qq.com/cgi-bin/showdocument?action
// =dir_list&t=resource/res_list&verify=1&id=open1453779503&token=&lang=zh_CN
// 微信推送ticket消息  请注意在第三方平台填写的接收授权事件url
function Ticket()
 {
    $data = $request->all();  //接收所有参数
    //接收消息
    $timeStamp    = $data['timestamp'];
    $nonce        = $data['nonce'];
    $msg_sign     = $data['msg_signature'];
    $encryptMsg   = file_get_contents('php://input');
    //获取配置信息
    $token = '自己的配置信息';  // 公众平台上，开发者设置的token
    $encodingAesKey = '自己的配置信息'; // 公众平台上，开发者设置的EncodingAESKey
    $appId = '自己的配置信息'; // 公众平台的appId
    //解密消息
    $pc = new WxBizMsgCrypt($token, $encodingAesKey, $appId); //调用时请引入该类
    $xml_tree = new DOMDocument(); //引入该类  直接use即可
    $xml_tree->loadXML($encryptMsg);
    $array_e = $xml_tree->getElementsByTagName('Encrypt');
    $encrypt = $array_e->item(0)->nodeValue;
    //替换百分号
    $format = "<xml><ToUserName><![CDATA[toUser]]></ToUserName><Encrypt><![CDATA[%s]]></Encrypt></xml>";
    $from_xml = sprintf($format, $encrypt);
    // 解密
    $msg = '';
    $errCode = $pc->decryptMsg($msg_sign, $timeStamp, $nonce, $from_xml, $msg);
    if ($errCode == 0) {
        $xml = new DOMDocument();
        $xml->loadXML($msg);
        $array_p = $xml->getElementsByTagName('InfoType');
        $info_type = $array_p->item(0)->nodeValue;
        //这里$info_type值会有几种情况，请做相关处理
       //取消授权 unauthorized  更新授权 updateauthorized  授权成功  authorized  component_verify_ticket  推送ticket消息
    }
 }

// 官方文档：https://open.weixin.qq.com/cgi-bin/showdocument?action
// =dir_list&t=resource/res_list&verify=1&id=open1489140610_Uavc4&token=&lang=zh_CN
// 微信小程序审核通知   请注意第三方平台上填写的授权完接收事件url
 function Appletcheck()
 {
    $data = $request->all();
    $wx_appid = $request->route('param'); //接收路由中的wxappid参数
    //接收消息
    $timeStamp    = $data['timestamp'];
    $nonce        = $data['nonce'];
    $msg_sign     = $data['msg_signature'];
    $encryptMsg   = file_get_contents('php://input');
    //获取配置信息
    $token = '自己的配置信息';  // 公众平台上，开发者设置的token
    $encodingAesKey = '自己的配置信息'; // 公众平台上，开发者设置的EncodingAESKey
    $appId = '自己的配置信息'; // 公众平台的appId
    //解密消息
    $pc = new WxBizMsgCrypt($token, $encodingAesKey, $appId);  //引入该类
    $xml_tree = new DOMDocument(); //引入该类  直接use
    $xml_tree->loadXML($encryptMsg);
    $array_e = $xml_tree->getElementsByTagName('Encrypt');
    $encrypt = $array_e->item(0)->nodeValue;
    //替换百分号
    $format = "<xml><ToUserName><![CDATA[toUser]]></ToUserName><Encrypt><![CDATA[%s]]></Encrypt></xml>";
    $from_xml = sprintf($format, $encrypt);
    // 解密
    $msg = '';
    $errCode = $pc->decryptMsg($msg_sign, $timeStamp, $nonce, $from_xml, $msg);
    if ($errCode == 0) {
        $xml = new DOMDocument();
        $xml->loadXML($msg);
        $event = $xml->getElementsByTagName('Event');
        $msg_type = $event->item(0)->nodeValue;
        // $msg_type 值有两种情况  成功失败
        // weapp_audit_success  成功   weapp_audit_fail  失败  做相关处理   
    }
 }

 //此包中共两个使用类，一个是授权流程，一个是代小程序实现业务
 //调用代小程序实现业务的方法：每个类仅举例一个 ，详细请查看文档
//  1.授权流程
    $auth = new WechatOpenApi($token,$encodingAesKey,$appId,$appsecret); //传递的参数可直接写在类中的构造方法或定义全局变量
    $auth->getComponentAccessTokens($ticket) //获取第三方平台access_token  $ticket==上边微信推送获取的ticket值
//  2.代小程序业务
    $applet = new AppletOpenApi();
    $applet->applyPlugInUses($access_token, $plugin_appid); // access_token==获取到授权小程序的token plugin_appid==插件id





