# ViewMemcache for CakePHP 2.X

Store your view(s) to memcache.  Allows easy distributed view invalidation and serving pages right from memcache via nginx (for example)

## Configuration

clone into APP/Plugin/ViewMemcache

Make an entry in APP/bootstrap.php for the 'view_memcache' engine and load the plugin and the VieMemcache engine:

```php
CakePlugin::load(array(
	'ViewMemcache'
));

//Use custom ViewMemcache engine because Cake core MemcacheEngine converts '/' to '_'
Cache::config('view_memcache', array(
		'engine' => 'ViewMemcache.ViewMemcache', //[required]
		'duration'=> 1209600, //2 weeks [optional]  You can conditionally set this in your controller and or action, see below
		'probability'=> 100, //[optional]
		'prefix' => 'VM_', //[optional]  prefix every cache file with this string. If you change, make sure to update your nginx conf
		'servers' => array(
			'127.0.0.1:11211' // localhost, default port 11211
		), //[optional]
		'persistent' => false, // [optional] set this to false for non-persistent connections
		'compress' => false, // Don't set this to true. Enable gzip in the helper
));
 ```
 
Put this in APP/Controller/AppController.php or just in the controller you want to do caching in.  

```php
var $helpers = array('ViewMemcache.ViewMemcache' => array(
	'gzipContent'=>true,	//Default is false. This will gzip content before pushing into memcache. Allows nginx to serve gzipped directly ;)
	'gzipCompressLevel'=>6	//Default is 7
));  
```

From controller action or beforeFilter() or AppController::beforeFilter() simply do:

```php
$viewMemcacheDuration = 600; //<seconds> | <cache engine readable range, ex: '+30 days'>;	//This is optional. If not set, will use
$enableViewMemcache = true;
//You can also diable gzip compression on an action by action basis by doing $this->set('viewMemcacheDisableGzip',true);
$this->set(compact('viewMemcacheDuration','enableViewMemcache'));
 //$this->set('viewMemcacheNoFooter',true);	//If you set this, it will omit the HTML comment
```

### nginx sample config using PHP-FPM

```
	gzip on;    
   	gzip_types text/plain text/css application/x-javascript text/javascript application/javascript application/json application/xml text/x-component application/rss+xml text/xml;
		
	#match exactly /
    location = / { 
   		default_type text/html;
   		
   		#if you set 'compress' => true in your 'view_cache' ViewMemcache engine config, make sure you disable gzip (cuz its already gzipped ;) 
   		gzip off;
   		#need to add the Content-Encoding: gzip header to tell clients to gunzip
   		add_header Content-Encoding gzip;
   		
		#VM_ prefix here matches the 'view_memcache' cache engine config prefix
		set $memcached_key VM_$request_uri;
		memcached_connect_timeout 2000;
		memcached_read_timeout 2000;
		memcached_pass 127.0.0.1:11211;
		# if memcache throws one of the following errors fallback to PHP
		error_page 404 502 504 = @fallback;
	}

	#If you arent storing all URLs in memcache, then there is no sense in checking if key exists
    location ~* ^/someUrlYouWantToSeeIfViewIsInMemcache { 
   		default_type text/html;
   		
   		#if you set 'compress' => true in your 'view_cache' ViewMemcache engine config, make sure you disable gzip (cuz its already gzipped ;) 
   		gzip off;
   		#need to add the Content-Encoding: gzip header to tell clients to gunzip
   		add_header Content-Encoding gzip;
   		
		#VM_ prefix here matches the 'view_memcache' cache engine config prefix
		set $memcached_key VM_$request_uri;
		memcached_connect_timeout 2000;
		memcached_read_timeout 2000;
		memcached_pass 127.0.0.1:11211;
		# if memcache throws one of the following errors fallback to PHP
		error_page 404 502 504 = @fallback;
	}
			
	# rewrite rules for cakephp
    location / {        
        index  index.php index.html;
        try_files $uri $uri/ /index.php?$uri&$args;
    }

	location @fallback{
		try_files $uri $uri/ /index.php?$uri&$args;
	}
	
	location ~ \.php$ {
		fastcgi_pass 127.0.0.1:9000;
		fastcgi_index index.php;
		include fastcgi_params;
		fastcgi_intercept_errors on;
	}
	
	#cake test takes long time
	location /test.php {
		fastcgi_read_timeout 1200;
    	fastcgi_send_timeout 1200;
	}
```
