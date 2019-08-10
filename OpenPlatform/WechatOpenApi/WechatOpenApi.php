<?php

/**
 * 类：WechatOpenApi
 * 说明：该类集合了微信第三方开放平台授权接口，只做授权
 * 处理，不做其他API方法封装调用处理。
 * 例如：
 * 1.获取第三方平台ComponentAccessToken 注意：2小时过期处理    api： getComponentAccessTokens
 * 2.获取预授权码pre_auth_code 注意：过期处理                  api：getPreAuthCodes
 * 3.获取代小程序/公众号AccessToken 注意：2小时过期处理         api：getAppletAccessTokens
 * 4.返回授权页url     api：getAuthUrls
 * 5.获取（刷新）授权公众号或小程序的接口调用凭据               api：refreshAccessTokens
 * 6.获取授权公众号或小程序基本信息                            api：getAuthAccountInfos
 * 7.获取授权方的选项设置信息                                api：getAccountOptionInfos
 * 8.设置授权方的选项信息                                   api：setAccountOptionInfos
 * 
 * 官方文档地址：https://open.weixin.qq.com/cgi-bin/showdocument?
 * action=dir_list&t=resource/res_list&verify=1&id=open1453779503&token=&lang=zh_CN
 * 
 * 特别注意，所有API调用需要验证调用者IP地址。只有在第三方平台申请时填写的白名单IP地址列表内
 * 的IP地址，才能合法调用，其他一律拒绝
 * 
 * 所有请求方式: POST
 * 
 * @author jinggz
 * @since 2019/7/12 13:00
 * @version V1.0
 */

header("Content-type: text/html; charset=utf-8"); //设置页面编码格式
class WechatOpenApi
{
    // 第三方平台上，开发者设置的token
    private $token;

    // 第三方平台上，开发者设置的EncodingAESKey
    private $encodingAesKey;

    // 第三方平台的appId
    private $appId;

    // 第三方平台的密钥
    private $appsecret;


    /**
     *  __construct 构造函数
     * 
     * 调用该类，初始化定义属性
     */
    public function __construct($token,$encodingAesKey,$appId,$appsecret)
    {
        $this->token = $token;  // 第三方平台上，开发者设置的token
        $this->encodingAesKey = $encodingAesKey; // 第三方平台上，开发者设置的EncodingAESKey
        $this->appId = $appId; // 第三方平台的appId
        $this->appsecret = $appsecret; // 第三方平台的appsecret密钥
    }


    /**
     * 函数：getComponentAccessTokens
     * 说明：获取第三方平台access_token并存储使用
     * 
     * 请注意:此处token是2小时刷新一次，开发者需要自行进行token的
     * 缓存，避免token的获取次数达到每日的限定额度
     * 
     * @access public
     * @param $ticket 微信服务器推送xml解密出来消息
     * @return 成功返回值  失败返回错误码 
     */

    public function getComponentAccessTokens($ticket)
    {
        $url = 'https://api.weixin.qq.com/cgi-bin/component/api_component_token';
        $data = array(
            'component_appid' => $this->appId,
            'component_appsecret' => $this->appsecret,
            'component_verify_ticket' => $ticket,
        );
        $json_arr = json_encode($data,JSON_UNESCAPED_UNICODE);
        $json_data = $this->curlPostHttps($url, $json_arr);
        $arr = json_decode($json_data, true);
        if (!isset($arr['component_access_token'])) {
            return WechatErrorCode::$component_access_token;
        }
        if (empty($arr['component_access_token'])) {
            return WechatErrorCode::$component_access_token;
        }
        return $arr;
    }


    /**
     * 函数：getPreAuthCodes
     * 说明：根据component_access_token获取预授权码PreAuthCode
     * 
     * @access public
     * @param $component_access_token 第三方平台的component_access_token
     * @return  成功返回值 失败返回错误码
     */

    public function getPreAuthCodes($component_access_token)
    {
        $url = 'https://api.weixin.qq.com/cgi-bin/component/api_create_preauthcode?component_access_token=' . $component_access_token;
        $arr = array(
            'component_appid' => $this->appId,
        );
        $json_arr = json_encode($arr,JSON_UNESCAPED_UNICODE);
        $json_data = $this->curlPostHttps($url, $json_arr);
        $data = json_decode($json_data, true);
        if (!isset($data['pre_auth_code'])) {
            return WechatErrorCode::$getPreAuthCode;
        }
        if (empty($data['pre_auth_code'])) {
            return WechatErrorCode::$getPreAuthCode;
        }
        return $data;
    }


    /**
     * 函数：getAuthUrls
     * 说明：拼接微信小程序/公众号授权地址 并返回给前端引入用户进入授权页
     * 根据业务需求此处采用方法一。
     * 
     * 方法一，网页扫码授权：https://mp.weixin.qq.com/cgi-bin/componentloginpage?component
     * \_appid=xxxx&pre\_auth\_code=xxxxx&redirect\_uri=xxxx&auth\_type=xxx。
     * 
     * 方法二，点击移动端链接快速授权：https://mp.weixin.qq.com/safe/bindcomponent?action=bindcomponent
     * &auth_type=3&no_scan=1&component_appid=xxxx&pre_auth_code=xxxxx&redirect_uri
     * =xxxx&auth_type=xxx&biz_appid=xxxx#wechat_redirect
     *
     * 特别注意：扫码授权，注意此URL必须放置在页面当中用户点击进行跳转，不能通过程序跳转，否则将
     * 出现“请确认授权入口页所在域名，与授权后回调页所在域名相同....”错误
     * 
     * @access public
     * @param $pre_code 预授权码
     * @param $type 授权类型 要授权的帐号类型， 1则商户扫码后，手机端仅展示公众号、
     *          2表示仅展示小程序，3表示公众号和小程序都展示。如果为未制定，则默认小程序和公众号都展示。
     *          第三方平台开发者可以使用本字段来控制授权的帐号类型。
     * @param $auth_appid 指定授权唯一的小程序或公众号的appid 
     * @return 成功返回授权 url  失败返回错误码 
     */

    public function getAuthUrls($url_goback,$pre_code, $type, $auth_appid)
    {
        $url_go_back = urlencode($url_goback);

        // 方法二 注：auth_type、biz_appid两个字段互斥。
        // $url = 'https://mp.weixin.qq.com/safe/bindcomponent?action=bindcomponent&auth_type=3&no_scan=1&component_appid=
        // ' . $this->appId . '&pre_auth_code=' . $pre_code . '&redirect_uri=' . $url_go_back . '&auth_type=2&biz_appid=' . $appId . '#wechat_redirect';

        // 方法一
        $url = 'https://mp.weixin.qq.com/cgi-bin/componentloginpage?component_appid=' . $this->appId . '&pre_auth_code=' . $pre_code . '&redirect_uri=' . $url_go_back .'&auth_type=' . $type . '&biz_appid=' . $auth_appid;
        return $url;
    }


    /**
     * 函数：getAppletAccessTokens
     * 说明：根据第三方的access_token令牌凭证和小程序授权code
     * 获取授权小程序的access_token令牌和相关信息
     * 
     * 请注意:此处token是2小时刷新一次，开发者需要自行进行token的
     * 缓存，避免token的获取次数达到每日的限定额度
     * 
     * @access public
     * @param $component_access_token 第三方令牌凭证
     * @param $code 授权小程序code
     * @return 成功返回值 失败返回错误码
     */

    public function getAppletAccessTokens($component_access_token, $code)
    {
        $url = 'https://api.weixin.qq.com/cgi-bin/component/api_query_auth?component_access_token=' . $component_access_token;
        $arr = array(
            'component_appid' => $this->appId,
            'authorization_code' => $code,
        );
        $json_arr = json_encode($arr,JSON_UNESCAPED_UNICODE);
        $json_data = $this->curlPostHttps($url, $json_arr);
        $data = json_decode($json_data, true);
        if (!isset($data['authorization_info'])) {
            return WechatErrorCode::$getAppletAccessToken;
        }
        if (empty($data['authorization_info'])) {
            return WechatErrorCode::$getAppletAccessToken;
        }
        return $data;
    }


    /**
     * 函数：refreshAccessTokens
     * 说明：根据刷新令牌凭证刷新授权小程序的接口调用凭据（令牌）
     * 
     * @access public
     * @param $component_access_token 第三方平台令牌
     * @param $authorizer_appid 授权方appid
     * @param $authorizer_refresh_token 授权方的刷新令牌
     * @return 成功返回值  失败返回错误码
     */

    public function refreshAccessTokens($component_access_token, $authorizer_appid, $authorizer_refresh_token)
    {
        $url = 'https://api.weixin.qq.com/cgi-bin/component/api_authorizer_token?component_access_token=' . $component_access_token;
        $arr = array(
            'component_appid' => $this->appId,
            'authorizer_appid' => $authorizer_appid,
            'authorizer_refresh_token' => $authorizer_refresh_token,
        );
        $json_arr = json_encode($arr,JSON_UNESCAPED_UNICODE);
        $json_data = $this->curlPostHttps($url, $json_arr);
        $data = json_decode($json_data, true);
        if (!isset($data['authorizer_access_token'])) {
            return WechatErrorCode::$flushAccessToken;
        }
        if (empty($data['authorizer_access_token'])) {
            return WechatErrorCode::$flushAccessToken;
        }
        return $data;
    }


    /**
     * 函数：getAuthAccountInfos
     * 说明：获取授权方的帐号基本信息,该API用于获取授权方的基本信息，
     * 包括头像、昵称、帐号类型、认证类型、微信号、原始ID和二维码图片URL。
     * 
     * @access public
     * @param $authorizer_appid 授权方appid
     * @param $component_access_token 第三方平台的令牌
     * @return 成功返回值  失败返回错误码
     */

    public function getAuthAccountInfos($component_access_token, $authorizer_appid)
    {
        $url = 'https://api.weixin.qq.com/cgi-bin/component/api_get_authorizer_info?component_access_token=' . $component_access_token;
        $arr = array(
            'component_appid' => $this->appId,
            'authorizer_appid' => $authorizer_appid,
        );
        $json_arr = json_encode($arr,JSON_UNESCAPED_UNICODE);
        $json_data = $this->curlPostHttps($url, $json_arr);
        $data = json_decode($json_data, true);
        if (!isset($data['authorizer_info'])) {
            return WechatErrorCode::$getAppletInfoError;
        }
        if (empty($data['authorizer_info'])) {
            return WechatErrorCode::$getAppletInfoError;
        }
        return $data;
    }


    /**
     * 函数：getAccountOptionInfos
     * 说明：获取授权方的选项设置信息,该API用于获取授权方的公众号或小程序的选项设置信息，
     * 如：地理位置上报，语音识别开关，多客服开关。注意，获取各项选项设置信息，需要有授权方的授权，详见权限集说明
     * 
     * @access public
     * @param $authorizer_appid 授权方appid
     * @param $option_name 选项名称 
     * @param $component_access_token 第三方平台的令牌
     * @return 成功返回值  失败返回错误码
     */

    public function getAccountOptionInfos($component_access_token, $authorizer_appid, $option_name)
    {
        $url = 'https://api.weixin.qq.com/cgi-bin/component/api_get_authorizer_option?component_access_token=' . $component_access_token;
        $arr = array(
            'component_appid' => $this->appId,
            'authorizer_appid' => $authorizer_appid,
            'option_name' => $option_name,
        );
        $json_arr = json_encode($arr,JSON_UNESCAPED_UNICODE);
        $json_data = $this->curlPostHttps($url, $json_arr);
        $data = json_decode($json_data, true);
        if (empty($data)) {
            return WechatErrorCode::$getAppletOptionError;
        }
        return $data;
    }


    /**
     * 函数：setAccountOptionInfos
     * 说明：设置授权方的选项信息,该API用于设置授权方的公众号或小程序的选项信息，
     * 如：地理位置上报，语音识别开关，多客服开关。注意，设置各项选项设置信息，
     * 需要有授权方的授权，详见权限集说明。
     * 
     * @access public
     * @param $authorizer_appid 授权方appid
     * @param $component_access_token 第三方平台的令牌
     * @param $option_name 选项名称
     * @param $option_value 选项值
     * @return 成功返回值  失败返回错误码
     */

    public function setAccountOptionInfos($component_access_token, $authorizer_appid, $option_name, $option_value)
    {
        $url = 'https://api.weixin.qq.com/cgi-bin/component/api_set_authorizer_option?component_access_token=' . $component_access_token;
        $arr = array(
            'component_appid' => $this->appId,
            'authorizer_appid' => $authorizer_appid,
            'option_name' => $option_name,
            'option_value' => $option_value,
        );
        $json_arr = json_encode($arr,JSON_UNESCAPED_UNICODE);
        $json_data = $this->curlPostHttps($url, $json_arr);
        $data = json_decode($json_data, true);
        if (!isset($data['errcode'])) {
            return WechatErrorCode::$setAppletOptionError;
        }
        if ($data['errcode'] != 0) {
            return $data['errcode'];
        }
        return WechatErrorCode::$OK;
    }


    /**-----------------------------------------------HTTTP CURL PHP GET POST---------------------------------------------------- */

    /**
     * 函数：curlPostHttps
     * 说明：PHP CURL HTTPS POST
     *  使用php的curl post请求 设置https
     */

    public function curlPostHttps($url, $data)
    { // 模拟提交数据函数
        $curl = curl_init(); // 启动一个CURL会话
        curl_setopt($curl, CURLOPT_URL, $url); // 要访问的地址
        curl_setopt($curl, CURLOPT_SSL_VERIFYPEER, 0); // 对认证证书来源的检查
        curl_setopt($curl, CURLOPT_SSL_VERIFYHOST, 2); // 从证书中检查SSL加密算法是否存在
        // curl_setopt($curl, CURLOPT_USERAGENT, $_SERVER['HTTP_USER_AGENT']); // 模拟用户使用的浏览器
        curl_setopt($curl, CURLOPT_FOLLOWLOCATION, 1); // 使用自动跳转
        curl_setopt($curl, CURLOPT_AUTOREFERER, 1); // 自动设置Referer
        curl_setopt($curl, CURLOPT_POST, 1); // 发送一个常规的Post请求
        curl_setopt($curl, CURLOPT_POSTFIELDS, $data); // Post提交的数据包
        curl_setopt($curl, CURLOPT_TIMEOUT, 30); // 设置超时限制防止死循环
        curl_setopt($curl, CURLOPT_HEADER, 0); // 显示返回的Header区域内容
        curl_setopt($curl, CURLOPT_RETURNTRANSFER, 1); // 获取的信息以文件流的形式返回
        $tmpInfo = curl_exec($curl); // 执行操作
        if (curl_errno($curl)) {
            $msg = 'Errno' . curl_error($curl); //捕抓异常
            $msg = $msg .'---'. date('Y-m-d H:i:s') . "\r\n";
            $fp = fopen('error.txt', 'ab+');
            fwrite($fp, $msg);
            fclose($fp);
        }
        curl_close($curl); // 关闭CURL会话
        return $tmpInfo; // 返回数据，json格式
    }
}
