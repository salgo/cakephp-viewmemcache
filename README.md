# ViewMemcache

Store your view(s) to memcache.  Allows easy distributed view invalidation and serving pages right from memcache via nginx (for example)

## Configuration

clone into APP/Plugin/ViewMemcache

Make an entry in APP/bootstrap.php for the 'view_memcache' engine:

```php
 Cache::config('view_memcache', array(
 		'engine' => 'Memcache', //[required]
 		'duration'=> 1209600, //2 weeks [optional]  You can conditionally set this in your controller and or action, see below
 		'probability'=> 100, //[optional]
  		'prefix' => 'VM_', //[optional]  prefix every cache file with this string. If you change, make sure to update your nginx conf
  		'servers' => array(
  			'127.0.0.1:11211' // localhost, default port 11211
  		), //[optional]
  		'persistent' => true, // [optional] set this to false for non-persistent connections
  		'compress' => true, // [optional] compress data in Memcache (slower, but uses less memory)
 ));
 ```
 
Put this in APP/Controller/AppController.php or just in the controller you want to do caching in.  

```php
var $helpers = array('ViewMemcache.ViewMemcache');  
```

From controller (or beforeFilter(), or AppController::beforeFilter()) simply do:

```php
 $viewMemcacheTimeout = <seconds> | <cache engine readable range, ex: '+30 days'>;	//This is optional. If not set, will use
 $enableViewMemcache = true;
 $this->set(compact('viewMemcacheTimeout','enableViewMemcache'));
```

### nginx sample config

location / {
	set $memcached_key "VM_:$request_uri";;
	memcached_connect_timeout 2000;
	memcached_read_timeout 2000;
	memcached_pass 127.0.0.1:11211;
	default_type text/html;
	# if memcache throws one of the following errors fallback to PHP
	error_page 404 502 504 = @fallback;
}

location @fallback{
	root /var/www/demo.*.com/app/webroot;

	try_files $uri $uri/ /index.php?$uri&$args;
}

location ~ \.php$ {
	fastcgi_pass 127.0.0.1:9000;
	fastcgi_index index.php;
	include fastcgi_params;
	fastcgi_intercept_errors on;
}
