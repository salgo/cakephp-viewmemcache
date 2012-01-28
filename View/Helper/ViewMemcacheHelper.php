<?php
App::uses('AppHelper', 'View/Helper');

/**
 * 
 * Store view to 'view_memcache' memcache storage engine config
 * 
 * From controller simply do:
 * $viewMemcacheTimeout = <seconds> | <cache engine readable range, ex: '+30 days'>
 * $enableViewMemcache = true;
 * $this->set(compact('viewMemcacheTimeout','enableViewMemcache'));
 * 
 * You can also disable storing view to memcache by setting Configure::write('ViewMemcache.disable',true);
 * @author rynop
 * 
 * Based on https://github.com/salgo/cakephp-viewmemcache by salgo
 *
 */
class ViewMemcacheHelper extends AppHelper {
	function afterLayout() {
		if (Configure::read('Cache.disable') || Configure::read('ViewMemcache.disable')) {
			return true;
		}
	
		if (!empty($this->_View->viewVars['enableViewMemcache'])) {
// 			debug('enabled');
			if (isset($this->_View->viewVars['viewMemcacheDuration'])) {
// 				debug('duration: '.$this->_View->viewVars['viewMemcacheDuration']);
// 				Cache::set(array('duration' => $this->_View->viewVars['viewMemcacheTimeout'],null,'view_memcache'));	//'+30 days' or seconds
			}

// 			Cache::write($this->request->here, $this->_View->output, 'view_memcache');
		}
	
		return true;
	}
}