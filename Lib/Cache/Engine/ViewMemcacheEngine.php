<?php
App::uses('MemcacheEngine', 'Cache/Engine');
/**
 * ViewMemcache storage engine for cache.  This cache keeps '/'s in the key.
 * Memcache has some limitations in the amount of
 * control you have over expire times far in the future.  See MemcacheEngine::write() for
 * more information.
 *
 * @author rynop.com
 */
class ViewMemcacheEngine extends MemcacheEngine {
	/**
	 * Don't convert / to _
	 *
	 * @param string $key key to use in memcache
	 * @return mixed string $key or false
	 */
	public function key($key) {
		if (empty($key)) {
			return false;
		}
		CakeLog::write('debug', "ViewMemCacheEngine: key (minus cache prefix): {$key}");
		return $key;
	}
}
