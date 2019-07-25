<?php

/**
 * 类：WechatErrorCode
 * 说明：微信第三方平台接口调用返回错误码
 * 
 * @author jinggz
 * @since 2019/7/11 14:00
 * @version V1.0
 */

class WechatErrorCode
{

    /**
     * error code 说明.
     * <ul>
     *    <li>-30001: 获取第三方平台access_token凭证失败或者返回信息为空</li>
     *    <li>-30002: 获取PreAuthCode预授权码失败或者返回信息为空</li>
     *    <li>-30003: 获取授权方小程序access_token失败或者返回信息为空</li>
     *    <li>-30004: 刷新获取授权方小程序access_token失败或者返回信息为空</li>
     *    <li>-30005: 获取小程序账号信息失败或者返回信息为空</li>
     *    <li>-30006: 获取小程序选项信息失败或者返回信息为空</li>
     *    <li>-30007: 设置小程序选项信息失败或者返回信息为空</li>
     *    <li>-30008: 设置小程序服务器域名失败或者返回信息为空</li>
     *    <li>-30009: 设置小程序业务域名失败或者返回信息为空</li>
     *    <li>-30010: 申请使用插件失败或者返回信息为空</li>
     *    <li>-30011: 查询插件失败或者返回信息为空</li>
     *    <li>-30012: 删除插件失败或者返回信息为空</li>
     *    <li>-30013: 更新插件失败或者返回信息为空</li>
     *    <li>-30014: 上传小程序代码失败或者返回信息为空</li>
     *    <li>-30015: 获取体验小程序二维码失败或者返回信息为空</li>
     *    <li>-30016: 获取小程序设置的类目失败或者返回信息为空</li>
     *    <li>-30017: 获取小程序页面配置失败或者返回信息为空</li>
     *    <li>-30018: 提交小程序失败或者返回信息为空</li>
     *    <li>-30019: 查询指定版本小程序失败或者返回信息为空</li>
     *    <li>-30020: 查询最后一次提交审核小程序失败或者返回信息为空</li>
     *    <li>-30021: 发布小程序失败或者返回信息为空</li>
     *    <li>-30022: 小程序审核撤回失败或者返回信息为空</li>
     *    <li>-30023: 模板获取失败或者返回信息为空</li>
     * </ul>
     */

    public static $OK = 0;
    public static $getComponentAccessToken = -30001;
    public static $getPreAuthCode = -30002;
    public static $getAppletAccessToken = -30003;
    public static $flushAccessToken = -30004;
    public static $getAppletInfoError = -30005;
    public static $getAppletOptionError = -30006;
    public static $setAppletOptionError = -30007;
    public static $setAppletUrlError = -30008;
    public static $setVoipDomainNameError = -30009;
    public static $applyPlugInUseError = -30010;
    public static $selectPlugInError = -30011;
    public static $delAddPlugInError = -30012;
    public static $updatePlugInError = -30013;
    public static $appletUploadError = -30014;
    public static $getQrcodeError = -30015;
    public static $getAppletClassError = -30016;
    public static $getPageConfigError = -30017;
    public static $submitAppletError = -30018;
    public static $getAssignVersionError = -30019;
    public static $lastCheckStatusError = -30020;
    public static $issueError = -30021;
    public static $checkRecallError = -30022;
    public static $getTemplateError = -30023;
}
