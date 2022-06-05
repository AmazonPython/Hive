<p align="center"><a href="https://laravel.com" target="_blank"><img src="https://raw.githubusercontent.com/laravel/art/master/logo-lockup/5%20SVG/2%20CMYK/1%20Full%20Color/laravel-logolockup-cmyk-red.svg" width="400"></a></p>

<p align="center">
<a href="https://travis-ci.org/laravel/framework"><img src="https://travis-ci.org/laravel/framework.svg" alt="Build Status"></a>
<a href="https://packagist.org/packages/laravel/framework"><img src="https://img.shields.io/packagist/dt/laravel/framework" alt="Total Downloads"></a>
<a href="https://packagist.org/packages/laravel/framework"><img src="https://img.shields.io/packagist/v/laravel/framework" alt="Latest Stable Version"></a>
<a href="https://packagist.org/packages/laravel/framework"><img src="https://img.shields.io/packagist/l/laravel/framework" alt="License"></a>
</p>

## Hive Store: 一个基于 Laravel 开发的开源商城

### 功能简介

- 用户登录、注册功能
- 邮件发送功能
- 用户地址管理功能
- 用户角色权限管理功能
- 商品 SKU 分类、属性、属性值管理
- 购物车功能
- 订单管理功能
- 订单流水号的生成功能
- 订单发货功能
- 订单退款功能
- 分期付款、支付宝、微信支付功能
- 商品分类、商品管理功能
- 商品评论功能
- 商品收藏功能
- 商品搜索功能
- 高并发下减商品库存功能
- 商品销量统计功能
- 商品评分统计功能
- 优惠券功能
- 众筹功能
- 异步队列任务

### 使用流程

克隆项目
```
git clone git@github.com:AmazonPython/Hive.git
```
转到项目目录
```
cd Hive
```
安装 PHP 依赖
```
composer install
```
安装 Nodejs 依赖
```
npm install && npm run dev
```
复制配置文件：
```
cp .env.example .env
```
生成密钥
```
php artisan key:generate
```
生成图片软连接
```
php artisan storage:link
```
运行数据库迁移
```
php artisan migrate
```
导入后台管理数据
```
php artisan db:seed --class=AdminTablesSeeder
```
创建后台管理员账号
```
php artisan admin:create-user
后台地址：http://your_domain/admin
```
.env 文件配置：
```
# 邮件配置 (如果环境支持 mailhog 则使用 .env 默认配置，否则需要自行配置)
MAIL_DRIVER=smtp
MAIL_HOST=smtp.{example qq.com}
MAIL_PORT={example 465}
MAIL_USERNAME={example Root}
MAIL_PASSWORD={example 123456}
MAIL_ENCRYPTION=tls
MAIL_FROM_ADDRESS={example 123@qq.com}
MAIL_FROM_NAME={example "Hive Store"}

# 支付宝配置信息
ALIPAY_APP_ID=202100011760...
ALIPAY_PUBLIC_KEY="MIIBIjANB..."
ALIPAY_PRIVATE_KEY="MIIEpAIBAA..."

# 微信支付配置信息
# 公众号 app id
WECHAT_PAY_APP_ID=wx7f8f8f8f8f8f8...
# 商户号
WECHAT_PAY_MCH_ID=1419000...
# 商户密钥
WECHAT_PAY_KEY="e10adc3949ba59abbe56e057f..."
# API 证书路径 resource/wechat文件夹下
WECHAT_CERT=cert文件完整文件名
WECHAT_CERT_KEY=文件完整文件名

# 内网穿透配置信息
NGROK_URL=http://{分配给你的域名}.ngrok.io
```
队列与定时任务配置
```
# 队列配置 如果运行环境不支持 Redis 则在 .env 文件中将 QUEUE_CONNECTION= 中的 redis 改为 database
php artisan queue:work 
# 定时任务配置
cron:calculate-installment-fine
cron:finish-crowdfunding
```

### 提示

配置微信支付需要商户号，如果没有资质可以仅配置支付宝沙箱账号。本项目为展示项目，为避免不必要的麻烦，线上地址不支持真实支付，以免资金纠纷。若对本项目感兴趣可将其克隆到本地，配置相关文件并浏览效果。也可以直接上线服务器环境。

如果您发现该项目中存在安全漏洞，请将问题提交至此项目。我会尽快解决所有安全漏洞。

## License
The Laravel framework is open-sourced software licensed under the [MIT license](https://opensource.org/licenses/MIT).
