infuse v0.1.15.1
=====

PHP MVC framework built with infuse/libs

## What is Infuse?

Infuse Framework is an incredibly powerful tool for building a modern web application that is built on top of [infuse/libs](https://github.com/jaredtking/infuse-libs). The goal of Infuse is to bring scalable simplicity to web development. The framework follows MVC conventions and scaffolds a REST API + administrator dashboard for models.

## Features

- MVC pattern
- Modular
- Built-in authentication
- Optional modules for [OAuth](https://github.com/jaredtking/infuse-oauth), [Facebook](https://github.com/jaredtking/infuse-facebook), and [Twitter](https://github.com/jaredtking/infuse-twitter) authentication
- Robust permissions system
- Database agnostic with PDO
- Flexible URL routing
- Automatic REST API for models
- Dashboard to view, create, edit, and delete models
- Schema generation
- Templating with [Smarty](http://smarty.net)
- CSS asset minification with LESS
- Javascript minification
- Logging with [monolog](https://github.com/Seldaek/monolog)

## Requirements

- PHP >= 5.3
- [Composer](http://getcomposer.org)
- PDO supported data store
- mod_rewrite (if using apache)

### Optional

- memcached (for built-in model caching)
- redis (for sessions)

## Demo

A demo has been setup at [infuse.jaredtking.com](http://infuse.jaredtking.com).

## Getting Started

### 1. Install with composer

```
composer create-project infuse/infuse ./path/to/dir
```

Infuse Framework is served through the `/app` directory to prevent the framework files from being publicly accessible. This requires a small amount of configuration for the web server to work properly.

### 2. nginx

Here is a sample configuration:

```
server {
	listen 80;
 	server_name example.com;
	root /var/www/example.com/app;
	access_log /var/www/log/example.com-access.log;
	error_log /var/www/log/example.com-error.log;
	
	location ~ \.php$ {
		fastcgi_pass   127.0.0.1:9000;
		fastcgi_index  index.php;
		fastcgi_param  SCRIPT_FILENAME  $document_root$fastcgi_script_name;
		include        fastcgi_params;
	}

	location / {
	   	index  index.html index.htm index.php;
		try_files $uri $uri/ /index.php?q=$uri&$args;
	}

	location ~ ^/index.php
	{
	  	fastcgi_pass   127.0.0.1:9000;
	 	fastcgi_index  index.php;
	 	fastcgi_param  SCRIPT_FILENAME  $document_root$fastcgi_script_name;
		fastcgi_param  PATH_INFO $fastcgi_script_name;	
		include        fastcgi_params;
	}
}
```

### 2. apache

A .htaccess file is already included in the `/app` directory for url rewrites. You must also make sure that `DocumentRoot` points to `{FRAMEWORK_PATH}/app`.

### 3. Installer

Fire up the url where the framework is installed and you will be redirected to an installer to setup the database and `config.yml`.

## Why another MVC framework?

Infuse is the culmination of best practices that I learned through developing PHP web applications. My aim is to pass on what I learned for other's benefit. This framework has been built to my own taste. If something does not look right or feels wrong, I would love to hear about it in the issues.

## Documentation

Learn more about Infuse in the [wiki](https://github.com/jaredtking/infuse/wiki).

## Contributing

Please feel free to contribute by participating in the issues or by submitting a pull request. :-)

## License

The MIT License (MIT)

Copyright © 2013 Jared King

Permission is hereby granted, free of charge, to any person obtaining a copy of this software and associated documentation files (the “Software”), to deal in the Software without restriction, including without limitation the rights to use, copy, modify, merge, publish, distribute, sublicense, and/or sell copies of the Software, and to permit persons to whom the Software is furnished to do so, subject to the following conditions:

The above copyright notice and this permission notice shall be included in all copies or substantial portions of the Software.

THE SOFTWARE IS PROVIDED “AS IS”, WITHOUT WARRANTY OF ANY KIND, EXPRESS OR IMPLIED, INCLUDING BUT NOT LIMITED TO THE WARRANTIES OF MERCHANTABILITY, FITNESS FOR A PARTICULAR PURPOSE AND NONINFRINGEMENT. IN NO EVENT SHALL THE AUTHORS OR COPYRIGHT HOLDERS BE LIABLE FOR ANY CLAIM, DAMAGES OR OTHER LIABILITY, WHETHER IN AN ACTION OF CONTRACT, TORT OR OTHERWISE, ARISING FROM, OUT OF OR IN CONNECTION WITH THE SOFTWARE OR THE USE OR OTHER DEALINGS IN THE SOFTWARE.