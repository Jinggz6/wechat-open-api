<?php

/**
 * 类：AppletOpenApi
 * 说明：该类集合了微信第三方开放平台代小程序业务接口，只做小程序
 * 业务接口处理，不做其他API方法封装调用处理。
 * 例如：
 * 1.设置小程序服务器域名     api：setAppletDomainNames
 * 2.设置小程序业务域名（仅供第三方代小程序调用）  api：setVoipDomainNames
 * 3.申请使用插件接口   api：applyPlugInUses
 * 4.查询已添加的插件   api：selectAddPlugIns
 * 5.删除已添加的插件   api：delAddPlugIns
 * 6.快速更新插件版本   api：fastUpdatePlugIns
 * 7.为授权的小程序帐号上传小程序代码  api：appletUploadCodes
 * 8.获取体验小程序的体验二维码       api：getAppletQrcodes
 * 9.获取授权小程序帐号已设置的类目   api：getAppletClassifys
 * 10.获取小程序的第三方提交代码的页面配置（仅供第三方开发者代小程序调用）  api：getAppletPageConfigs
 * 11.将第三方提交的代码包提交审核（仅供第三方开发者代小程序调用）         api：submitAppletCheckCodes
 * 12.查询某个指定版本的审核状态（仅供第三方代小程序调用）                 api：getAssignVersionApplets
 * 13.发布已通过审核的小程序（仅供第三方代小程序调用）                    api：issuePassCheckApplets
 * 14.查询最新一次提交的审核状态（仅供第三方代小程序调用）                 api：selectLastCheckStatuss
 * 15.小程序审核撤回          api：appletCheckRecalls
 * 16.获取小程序模板列表       api：getTemplateInfos
 * 
 * 官方文档地址：https://open.weixin.qq.com/cgi-bin/showdocument?action=dir_list&t=
 * resource/res_list&verify=1&id=open1489140610_Uavc4&token=&lang=zh_CN
 * 
 * 特别注意，所有API调用需要验证调用者IP地址。只有在第三方平台申请时填写的白名单IP地址列表内
 * 的IP地址，才能合法调用，其他一律拒绝
 * 
 * 注：没有标明请求方法的均为POST请求。
 * 
 * @author jinggz
 * @since 2019/7/11 16:00
 * @version V1.0
 */

header("Content-type: text/html; charset=utf-8"); //设置页面编码格式
class AppletOpenApi
{

    /**
     * 函数：setAppletDomainNames
     * 说明：设置小程序服务器域名
     * 
     * 授权给第三方的小程序，其服务器域名只可以为第三方的服务器，当小程序通过第三方发布代码上线后，
     * 小程序原先自己配置的服务器域名将被删除，只保留第三方平台的域名，所以第三方平台在代替小程序发布代码之前，
     * 需要调用接口为小程序添加第三方自身的域名。提示：需要先将域名登记到第三方平台的小程序服务器域名中，才可以调用接口进行配置。
     * 
     * 请注意传的域名参数请在传递的时候拼接https或wss  这里不作处理
     * 
     * @access public
     * @param $access_token 第三方平台获取到的该小程序授权的authorizer_access_token
     * @param $action add添加, delete删除, set覆盖, get获取。当参数是get时不需要填四个域名字段
     * @param $requestdomain request合法域名，当action参数是get时不需要此字段
     * @param $wsrequestdomain  socket合法域名，当action参数是get时不需要此字段
     * @param $uploaddomain uploadFile合法域名，当action参数是get时不需要此字段
     * @param $downloaddomain  downloadFile合法域名，当action参数是get时不需要此字段
     * @return 成功返回值  失败返回错误码 
     */

    public function setAppletDomainNames($access_token, $action, $requestdomain = null, $wsrequestdomain = null, $uploaddomain = null, $downloaddomain = null)
    {
        $url = 'https://api.weixin.qq.com/wxa/modify_domain?access_token=' . $access_token;
        if ($action == 'get') {
            $data = array(
                'action' => $action,
            );
        } else {
            $data = array(
                'action' => $action,
                'requestdomain' => $requestdomain,
                'wsrequestdomain' => $wsrequestdomain,
                'uploaddomain' => $uploaddomain,
                'downloaddomain' => $downloaddomain,
            );
        }
        $json_arr = json_encode($data,JSON_UNESCAPED_UNICODE); //第二个参数解决中文问题
        $json_data = $this->curlPostHttps($url, $json_arr);
        if (empty($json_data)) {
            return WechatErrorCode::$setAppletUrlError;
        }
        $arr = json_decode($json_data, true);
        if (!isset($arr['errcode'])) {
            return WechatErrorCode::$setAppletUrlError;
        }
        if ($arr['errcode'] != 0) {
            return $arr['errcode'];
        }
        return WechatErrorCode::$OK;
    }


    /**
     * 函数：setVoipDomainNames
     * 说明：设置小程序业务域名（仅供第三方代小程序调用）
     * 
     * 授权给第三方的小程序，其业务域名只可以为第三方的服务器，当小程序通过第三方发布代码上线后，
     * 小程序原先自己配置的业务域名将被删除，只保留第三方平台的域名，所以第三方平台在代替小程序发布代码之前，
     * 需要调用接口为小程序添加业务域名。提示：1、需要先将域名登记到第三方平台的小程序业务域名中，才可以调用接口进行配置。
     * 2、为授权的小程序配置域名时支持配置子域名，例如第三方登记的业务域名如为qq.com，则可以直接将qq.com及其子域名（如xxx.qq.com）
     * 也配置到授权的小程序中。
     * 
     * @access public
     * @param $access_token 第三方平台获取到的该小程序授权的authorizer_access_token
     * @param $action add添加, delete删除, set覆盖, get获取。当参数是get时不需要填四个域名字段
     * @param $webviewdomain  小程序业务域名，当action参数是get时不需要此字段
     * @return 成功返回值  失败返回错误码 
     */

    public function setVoipDomainNames($access_token, $action, $webviewdomain = null)
    {
        $url = 'https://api.weixin.qq.com/wxa/setwebviewdomain?access_token=' . $access_token;
        if ($action == 'get') {
            $data = array(
                'action' => $action,
            );
        } else {
            $data = array(
                'action' => $action,
                'webviewdomain' => $webviewdomain,
            );
        }
        $json_arr = json_encode($data,JSON_UNESCAPED_UNICODE);
        $json_data = $this->curlPostHttps($url, $json_arr);
        if (empty($json_data)) {
            return WechatErrorCode::$setVoipDomainNameError;
        }
        $arr = json_decode($json_data, true);
        if (!isset($arr['errcode'])) {
            return WechatErrorCode::$setVoipDomainNameError;
        }
        if ($arr['errcode'] != 0) {
            return $arr['errcode'];
        }
        return WechatErrorCode::$OK;
    }


    /**
     * 函数：applyPlugInUses
     * 说明：申请使用插件接口
     * 此接口用于小程序向插件开发者发起使用插件的申请。
     * 
     * @access public
     * @param $access_token 第三方平台获取到的该小程序授权的authorizer_access_token
     * @param $plugin_appid  插件appid
     * @return  成功返回值 失败返回错误码
     */

    public function applyPlugInUses($access_token, $plugin_appid)
    {
        $url = 'https://api.weixin.qq.com/wxa/plugin?access_token=' . $access_token;
        $data = array(
            'action' => 'apply',
            'plugin_appid' => $plugin_appid,
        );
        $json_arr = json_encode($data,JSON_UNESCAPED_UNICODE);
        $json_data = $this->curlPostHttps($url, $json_arr);
        if (empty($json_data)) {
            return WechatErrorCode::$applyPlugInUseError;
        }
        $arr = json_decode($json_data, true);
        if (!isset($arr['errcode'])) {
            return WechatErrorCode::$applyPlugInUseError;
        }
        if ($arr['errcode'] != 0) {
            return $arr['errcode'];
        }
        return WechatErrorCode::$OK;
    }



    /**
     * 函数：selectAddPlugIns 
     * 说明：查询已添加的插件
     * 此接口用于查询小程序目前已添加的插件（包括确认中、已通过、已拒绝、已超时状态）
     * 
     * @access public
     * @param $access_token 第三方平台获取到的该小程序授权的authorizer_access_token
     * @return  成功返回值 失败返回错误码
     */

    public function selectAddPlugIns($access_token)
    {
        $url = 'https://api.weixin.qq.com/wxa/plugin?access_token=' . $access_token;
        $data = array(
            'action' => 'list',
        );
        $json_arr = json_encode($data,JSON_UNESCAPED_UNICODE);
        $json_data = $this->curlPostHttps($url, $json_arr);
        if (empty($json_data)) {
            return WechatErrorCode::$selectPlugInError;
        }
        $arr = json_decode($json_data, true);
        if (!isset($arr['errcode'])) {
            return WechatErrorCode::$selectPlugInError;
        }
        if ($arr['errcode'] != 0) {
            return $arr['errcode'];
        }
        return $arr;
    }



    /**
     * 函数：delAddPlugIns 
     * 说明：删除已添加的插件
     * 此接口用户小程序删除当前已添加的插件（包括已通过和已拒绝）
     * 
     * @access public
     * @param $access_token 第三方平台获取到的该小程序授权的authorizer_access_token
     * @param $plugin_appid  插件appid
     * @return  成功返回值 失败返回错误码
     */

    public function delAddPlugIns($access_token, $plugin_appid)
    {
        $url = 'https://api.weixin.qq.com/wxa/plugin?access_token=' . $access_token;
        $data = array(
            'action' => 'list',
            'plugin_appid' => $plugin_appid,
        );
        $json_arr = json_encode($data,JSON_UNESCAPED_UNICODE);
        $json_data = $this->curlPostHttps($url, $json_arr);
        if (empty($json_data)) {
            return WechatErrorCode::$delAddPlugInError;
        }
        $arr = json_decode($json_data, true);
        if (!isset($arr['errcode'])) {
            return WechatErrorCode::$delAddPlugInError;
        }
        if ($arr['errcode'] != 0) {
            return $arr['errcode'];
        }
        return WechatErrorCode::$OK;
    }



    /**
     * 函数：fastUpdatePlugIns
     * 说明：快速更新插件版本
     * 此接口用于快速更新插件的版本号，小程序不需要修改代码、不需要重新提交版本审核，
     * 即可快速更新当前小程序正在使用的插件版本号。
     * 
     * @access public
     * @param $access_token 第三方平台获取到的该小程序授权的authorizer_access_token
     * @param $plugin_appid  插件appid
     * @param $user_version  升级至版本号，要求此插件版本支持快速更新
     * @return  成功返回值 失败返回错误码
     */

    public function fastUpdatePlugIns($access_token, $plugin_appid, $user_version)
    {
        $url = 'https://api.weixin.qq.com/wxa/plugin?access_token=' . $access_token;
        $data = array(
            'action' => 'update',
            'user_version' => $user_version,
            'plugin_appid' => $plugin_appid,
        );
        $json_arr = json_encode($data,JSON_UNESCAPED_UNICODE);
        $json_data = $this->curlPostHttps($url, $json_arr);
        if (empty($json_data)) {
            return WechatErrorCode::$updatePlugInError;
        }
        $arr = json_decode($json_data, true);
        if (!isset($arr['errcode'])) {
            return WechatErrorCode::$updatePlugInError;
        }
        if ($arr['errcode'] != 0) {
            return $arr['errcode'];
        }
        return WechatErrorCode::$OK;
    }


    /**
     * 函数：appletUploadCodes
     * 说明：为授权的小程序帐号上传小程序代码
     * 
     * @access public
     * @param $access_token 请使用第三方平台获取到的该小程序授权的authorizer_access_token
     * @param $template_id 代码库中的代码模版ID
     * @param $ext_json 第三方自定义的配置  请直接传json格式
     * @param $user_version 代码版本号，开发者可自定义（长度不要超过64个字符）
     * @param $user_desc	代码描述，开发者可自定义
     * @return 成功返回值 失败返回错误码
     */

    public function appletUploadCodes($access_token, $template_id, $ext_json, $user_version, $user_desc)
    {
        $url = 'https://api.weixin.qq.com/wxa/commit?access_token=' . $access_token;
        $data = array(
            'template_id' => $template_id,
            'ext_json' => json_encode($ext_json,JSON_UNESCAPED_UNICODE),
            'user_version' => $user_version,
            'user_desc' => $user_desc,
        );
        $json_arr = json_encode($data,JSON_UNESCAPED_UNICODE);
        $json_data = $this->curlPostHttps($url, $json_arr);
        if (empty($json_data)) {
            return WechatErrorCode::$appletUploadError;
        }
        $arr = json_decode($json_data, true);
        if (!isset($arr['errcode'])) {
            return WechatErrorCode::$appletUploadError;
        }
        if ($arr['errcode'] != 0) {
            return $arr['errcode'];
        }
        return WechatErrorCode::$OK;
    }


    /**
     * 函数：getAppletQrcodes
     * 说明：获取体验小程序的体验二维码
     * 
     * @access public
     * @method GET
     * @param $access_token 请使用第三方平台获取到的该小程序授权的authorizer_access_token
     * @param $path 指定体验版二维码跳转到某个具体页面（如果不需要的话，则不需要填path参数，
     *          可在路径后以“参数”方式传入参数）具体的路径加参数需要urlencode，比如page/index?action=1
     *          编码后得到page%2Findex%3Faction%3D1
     * @return 成功返回值 失败返回错误码
     */

    public function getAppletQrcodes($access_token, $path = null)
    {
        if ($path == null) {
            $url = 'https://api.weixin.qq.com/wxa/get_qrcode?access_token=' . $access_token;
        } else {
            $path = urlencode($path);
            $url = 'https://api.weixin.qq.com/wxa/get_qrcode?access_token=' . $access_token . '&path=' . $path;
        }
        $json_data = $this->curlGetHttps($url);
        if (empty($json_data)) {
            return WechatErrorCode::$getQrcodeError;
        }
        return $json_data;
    }

    /**
     * 函数：getAppletClassifys
     * 说明：获取授权小程序帐号已设置的类目
     * 注意：该接口可获取已设置的二级类目及用于代码审核的可选三级类目。
     * 
     * @access public
     * @method GET
     * @param  $access_token 请使用第三方平台获取到的该小程序授权的authorizer_access_token
     * @return 成功返回值 失败返回错误码
     */

    public function getAppletClassifys($access_token)
    {
        $url = 'https://api.weixin.qq.com/wxa/get_category?access_token=' . $access_token;
        $json_data = $this->curlGetHttps($url);
        if (empty($json_data)) {
            return WechatErrorCode::$getAppletClassError;
        }
        $arr = json_decode($json_data, true);
        if (!isset($arr['errcode'])) {
            return WechatErrorCode::$getAppletClassError;
        }
        if ($arr['errcode'] != 0) {
            return $arr['errcode'];
        }
        return $arr;
    }


    /**
     * 函数：getAppletPageConfigs
     * 说明：获取小程序的第三方提交代码的页面配置（仅供第三方开发者代小程序调用）
     * 
     * @access public
     * @method GET
     * @param  $access_token 请使用第三方平台获取到的该小程序授权的authorizer_access_token
     * @return 成功返回值 失败返回错误码
     */

    public function getAppletPageConfigs($access_token)
    {
        $url = 'https://api.weixin.qq.com/wxa/get_page?access_token=' . $access_token;
        $json_data = $this->curlGetHttps($url);
        if (empty($json_data)) {
            return WechatErrorCode::$getPageConfigError;
        }
        $arr = json_decode($json_data, true);
        if (!isset($arr['errcode'])) {
            return WechatErrorCode::$getPageConfigError;
        }
        if ($arr['errcode'] != 0) {
            return $arr['errcode'];
        }
        return $arr;
    }


    /**
     * 函数：submitAppletCheckCodes
     * 说明：将第三方提交的代码包提交审核（仅供第三方开发者代小程序调用）
     * 注意：只有上个版本被驳回，才能使用“feedback_info”、“feedback_stuff”这两个字段，否则忽略处理。
     * 注意：需要先提交体验版后再提交代码包审核。
     * 
     * @access public
     * @param $access_token 请使用第三方平台获取到的该小程序授权的authorizer_access_token
     * @param $item_list 提交审核项的一个列表（至少填写1项，至多填写5项）
     * @param $feedback_info 反馈内容，不超过200字
     * @param $feedback_stuff  图片media_id列表，中间用“丨”分割，xx丨yy丨zz，不超过5张图片, 其中 media_id 可以通过新增临时素材接口上传而得到
     * @return 成功返回值 失败返回错误码
     */

    public function submitAppletCheckCodes($access_token, $item_list, $feedback_info = null, $feedback_stuff = null)
    {
        $url = 'https://api.weixin.qq.com/wxa/submit_audit?access_token=' . $access_token;
        $data = array(
            'item_list' => $item_list,
            'feedback_info' => $feedback_info,
            'feedback_stuff' => $feedback_stuff,
        );
        $data_json = json_encode($data,JSON_UNESCAPED_UNICODE);
        $json_data = $this->curlPostHttps($url, $data_json);
        if (empty($json_data)) {
            return WechatErrorCode::$submitAppletError;
        }
        $arr = json_decode($json_data, true);
        if (!isset($arr['errcode'])) {
            return WechatErrorCode::$submitAppletError;
        }
        if ($arr['errcode'] != 0) {
            return $arr['errcode'];
        }
        return $arr;
    }


    /**
     * 函数：getAssignVersionApplets
     * 说明：查询某个指定版本的审核状态（仅供第三方代小程序调用）
     * 
     * @access public
     * @param  $access_token 请使用第三方平台获取到的该小程序授权的authorizer_access_token
     * @param  $auditid  提交审核时获得的审核id
     * @return 成功返回值 失败返回错误码
     */

    public function getAssignVersionApplets($access_token, $auditid)
    {
        $url = 'https://api.weixin.qq.com/wxa/get_auditstatus?access_token=' . $access_token;
        $arr = array(
            'auditid' => $auditid,
        );
        $arr_json = json_encode($arr,JSON_UNESCAPED_UNICODE);
        $json_data = $this->curlPostHttps($url, $arr_json);
        if (empty($json_data)) {
            return WechatErrorCode::$getAssignVersionError;
        }
        $json_arr = json_decode($json_data, true);
        if (!isset($json_arr['errcode'])) {
            return WechatErrorCode::$getAssignVersionError;
        }
        if ($json_arr['errcode'] != 0) {
            return $json_arr['errcode'];
        }
        return $json_arr;
    }


    /**
     * 函数：selectLastCheckStatuss
     * 说明：查询最新一次提交的审核状态（仅供第三方代小程序调用）
     * 
     * @access public
     * @method GET
     * @param $access_token 请使用第三方平台获取到的该小程序授权的authorizer_access_token
     * @return  成功返回值 失败返回错误码
     */

    public function selectLastCheckStatuss($access_token)
    {
        $url = 'https://api.weixin.qq.com/wxa/get_latest_auditstatus?access_token=' . $access_token;
        $json_data = $this->curlGetHttps($url);
        if (empty($json_data)) {
            return WechatErrorCode::$lastCheckStatusError;
        }
        $arr = json_decode($json_data, true);
        if (!isset($arr['errcode'])) {
            return WechatErrorCode::$lastCheckStatusError;
        }
        if ($arr['errcode'] != 0) {
            return $arr['errcode'];
        }
        return $arr;
    }


    /**
     * 函数：issuePassCheckApplets
     * 说明：发布已通过审核的小程序（仅供第三方代小程序调用）
     * 
     * @access public
     * @param $access_token 第三方平台获取到的该小程序授权的authorizer_access_token
     * @param "{}" 请填写空的数据包，POST的json数据包为空即可。
     * @return  成功返回值 失败返回错误码
     */

    public function issuePassCheckApplets($access_token)
    {
        $url = 'https://api.weixin.qq.com/wxa/release?access_token=' . $access_token;
        $data = '{}';
        $json_data = $this->curlPostHttps($url, $data);
        if (empty($json_data)) {
            return WechatErrorCode::$issueError;
        }
        $arr = json_decode($json_data, true);
        if (!isset($arr['errcode'])) {
            return WechatErrorCode::$issueError;
        }
        if ($arr['errcode'] != 0) {
            return $arr['errcode'];
        }
        return WechatErrorCode::$OK;
    }


    /**
     * 函数：appletCheckRecalls
     * 说明：小程序审核撤回
     * 单个帐号每天审核撤回次数最多不超过1次，一个月不超过10次。
     * 
     * @access public
     * @method GET
     * @param $access_token 第三方平台获取到的该小程序授权的authorizer_access_token
     * @return 成功返回值 失败返回错误码
     */

    public function appletCheckRecalls($access_token)
    {
        $url = 'https://api.weixin.qq.com/wxa/undocodeaudit?access_token=' . $access_token;
        $json_data = $this->curlGetHttps($url);
        if (empty($json_data)) {
            return WechatErrorCode::$checkRecallError;
        }
        $arr = json_decode($json_data, true);
        if (!isset($arr['errcode'])) {
            return WechatErrorCode::$checkRecallError;
        }
        if ($arr['errcode'] != 0) {
            return $arr['errcode'];
        }
        return WechatErrorCode::$OK;
    }


    /**
     * 函数：getTemplateInfos
     * 说明：获取代码模版库中的所有小程序代码模版
     * 
     * @method GET
     * @access public
     * @param   $access_token 第三方平台的access_token
     * @return  成功返回值 失败返回错误码
     */

     public function getTemplateInfos($access_token)
     {
        $url = 'https://api.weixin.qq.com/wxa/gettemplatelist?access_token=' . $access_token;
        $json_data = $this->curlGetHttps($url);
        if (empty($json_data)) {
            return WechatErrorCode::$getTemplateError;
        }
        $arr = json_decode($json_data, true);
        if (!isset($arr['errcode'])) {
            return WechatErrorCode::$getTemplateError;
        }
        if ($arr['errcode'] != 0) {
            return $arr['errcode'];
        }
        return $arr;
     }


    /**------------------------------------------------HTTTP CURL PHP GET POST------------------------------------------------------------------------------------------------  */


    /**
     * 函数：curlPostHttps
     * 说明：PHP CURL HTTPS POST
     *  使用php的curl get请求 设置https
     */
    public static function curlGetHttps($url)
    {
        $curl = curl_init(); // 启动一个CURL会话
        curl_setopt($curl, CURLOPT_URL, $url);
        curl_setopt($curl, CURLOPT_HEADER, 0);
        curl_setopt($curl, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt($curl, CURLOPT_SSL_VERIFYPEER, 0); // 跳过证书检查
        curl_setopt($curl, CURLOPT_SSL_VERIFYHOST, 2);  // 从证书中检查SSL加密算法是否存在
        $data_info = curl_exec($curl);     //返回api的json对象
        if (curl_errno($curl)) {
            $msg = 'Errno' . curl_error($curl); //捕抓异常
            $msg = $msg .'---'. date('Y-m-d H:i:s') .  "\r\n";
            $fp = fopen('error.txt', 'ab+');
            fwrite($fp, $msg);
            fclose($fp);
        }
        //关闭URL请求
        curl_close($curl);
        return $data_info;    //返回json对象
    }



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
            $msg = $msg . '---'. date('Y-m-d H:i:s') . "\r\n";
            $fp = fopen('error.txt', 'ab+');
            fwrite($fp, $msg);
            fclose($fp);
        }
        curl_close($curl); // 关闭CURL会话
        return $tmpInfo; // 返回数据，json格式
    }
}
