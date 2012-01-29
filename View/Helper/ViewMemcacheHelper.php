<?php
App::uses('AppHelper', 'View/Helper');

/**
 * 
 * Store view to 'view_memcache' memcache storage engine config
 * 
 * In controller load the helper:
 * 
 * Put this in APP/Controller/AppController.php or just in the controller you want to do caching in.  
 * 
 * ```php
 * var $helpers = array('ViewMemcache.ViewMemcache' => array(
 * 	'gzipContent'=>true,	//Default is false
 * 	'gzipCompressLevel'=>6	//Default is 7
 * ));  
 * ```
 * 
 * From controller action or beforeFilter() or AppController::beforeFilter() simply do:
 * 
 * ```php
 * $viewMemcacheDuration = 600; //<seconds> | <cache engine readable range, ex: '+30 days'>;	//This is optional. If not set, will use
 * $enableViewMemcache = true;
 * //You can also diable gzip compression on an action by action basis by doing $this->set('viewMemcacheDisableGzip',true);
 * $this->set(compact('viewMemcacheDuration','enableViewMemcache'));
 *  //$this->set('viewMemcacheNoFooter',true);	//If you set this, it will omit the HTML comment
 * ```
 * 
 * You can also disable storing view to memcache by setting Configure::write('ViewMemcache.disable',true);
 * 
 * @author rynop.com
 * 
 * Based on https://github.com/salgo/cakephp-viewmemcache by salgo
 *
 */
class ViewMemcacheHelper extends AppHelper {
	private $cacheFooter 	= null;
	private $compressLevel 	= 7;
	private $gzipContent 	= false;
	
	function __construct(View $view, $settings = array()) {
		parent::__construct($view, $settings);		
		if(!empty($settings['gzipCompressLevel'])){
// 			CakeLog::write('debug', "ViewMemCache: gzip level: {$settings['gzipCompressLevel']}");
			$this->compressLevel = intval($settings['gzipCompressLevel']);
		}
		if(!empty($settings['gzipContent'])){
// 			CakeLog::write('debug', "ViewMemCache: gzip on");
			$this->gzipContent = true;
		}
	}
	
	function afterLayout() {
		if (Configure::read('Cache.disable') || Configure::read('ViewMemcache.disable')) {
			return true;
		}
	
		if (!empty($this->_View->viewVars['enableViewMemcache'])) {
			if (isset($this->_View->viewVars['viewMemcacheDuration'])) {
// 				CakeLog::write('debug', "ViewMemCache: duration override: {$this->_View->viewVars['viewMemcacheDuration']}");
				Cache::set(array('duration' => $this->_View->viewVars['viewMemcacheDuration'],null,'view_memcache'));	//'+30 days' or seconds
			}

			if (!isset($this->_View->viewVars['viewMemcacheNoFooter'])) {
// 				CakeLog::write('debug', "ViewMemCache: footer disabled");
				$this->cacheFooter = "\n<!-- ViewCached";
				if($this->gzipContent) $this->cacheFooter .= ' gzipped';
				$this->cacheFooter .= ' '.date('r').' -->';
			}
						
			if ( $this->gzipContent && empty($this->_View->viewVars['viewMemcacheDisableGzip']) ) {
// 				CakeLog::write('debug', "ViewMemCache: gzipping");
				Cache::write($this->request->here, gzencode($this->_View->output . $this->cacheFooter, $this->compressLevel), 'view_memcache');
			}
			else {
				Cache::write($this->request->here, $this->_View->output . $this->cacheFooter, 'view_memcache');
			}					
		}
	
		return true;
	}
}