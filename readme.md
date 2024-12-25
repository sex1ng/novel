#### **部署步骤**：

```bash
composer install
php artisan config:clear && php artisan route:clear && php artisan view:clear
chown www:www ./ -R && chmod 755 ./
```

##### 修改配置文件

```bash
- config/app.php
  1. app_name
  2. app_name_en
  3. url
  4. asset_url
- config/database.php
  1. mysql
  2. redis
- config/server.php
  1. single
```

##### 数据表结构导入

`mysql.sql`

##### 数据导入

`.xlsx 或 .csv`

##### 运行目录

`public/`

#### **Nginx伪静态**

```nginx
location / {
	if (!-e $request_filename){
		rewrite  ^(.*)$  /index.php?s=$1  last;   break;
	}
}
```

