# wechat-open-api
微信开放平台第三方sdk（PHP版本）

#### composer下载

`composer requ`

#### 微信官方文档：

地址：https://open.weixin.qq.com/cgi-bin/showdocument?action=dir_list&t=resource/res_list&verify=1&id=open1453779503&token=&lang=zh_CN

#### 注意：

1. 请注意调用时引入该类。
2. 请注意第三方token值和授权小程序token值及相关业务值过期时间，建议使用定时任务在两小时内（1小时50分）定时刷新token及相关值。
3. 建议查看微信授权流程，以便知晓刷新那些值和相关业务逻辑。
4. 获取微信推送的ticket值和小程序审核通知方式，请查看demo.php
5. 其他调用方法请查看文档。
6. 此包分为微信授权业务流程和代小程序实现业务。

#### 调用方法：

**请查看每个类中开始部分注释代码**