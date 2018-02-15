<?php
namespace O10n;

/**
 * Cache Directory Controller
 *
 * @package    optimization
 * @subpackage optimization/controllers
 * @author     PageSpeed.pro <info@pagespeed.pro>
 */
if (!defined('ABSPATH')) {
    exit;
}

class Cache extends Controller implements Controller_Interface
{
    // cache stores
    private $stores = array(
        'config:index' => array(
            'index' => 20,
            'hash_dir' => 'config/',
            'file_ext' => '',
            'expire' => 86400
        ),
        'css:src' => array(
            'index' => 1,
            'hash_dir' => 'css/src/',
            'file_ext' => '.css',
            'alt_exts' => array('.css.map'),
            'expire' => 259200 // expire after 3 days
        ),
        'css:concat' => array(
            'index' => 2,
            'hash_id' => true, // store data by database index id
            'hash_dir' => 'css/concat/',
            'id_dir' => 'css/',
            'file_ext' => '.css',
            'alt_exts' => array('.css.map'),
            'expire' => 86400 // expire after 1 day
        ),
        'css:critical' => array(
            'index' => 3,
            'hash_dir' => 'css/concat/',
            'file_ext' => '.css',
            'expire' => 86400 // expire after 1 day
        ),

        'js:src' => array(
            'index' => 4,
            'hash_dir' => 'js/src/',
            'file_ext' => '.js',
            'alt_exts' => array('.js.map'),
            'expire' => 259200 // expire after 3 days
        ),
        'js:concat' => array(
            'index' => 6,
            'hash_id' => true, // store data by database index id
            'hash_dir' => 'js/concat/',
            'id_dir' => 'js/',
            'file_ext' => '.js',
            'alt_exts' => array('.js.map'),
            'expire' => 86400 // expire after 1 day
        ),


        'proxy:css' => array(
            'index' => 5,
            'hash_dir' => 'css/proxy/',
            'file_ext' => '.css',
            'expire' => 86400 // expire after 1 day
        ),
        'proxy:js' => array(
            'index' => 7,
            'hash_dir' => 'js/proxy/',
            'file_ext' => '.js',
            'expire' => 86400 // expire after 1 day
        ),

        'html:fragments' => array(
            'index' => 9,
            'hash_dir' => 'html/',
            'file_ext' => '',
            'alt_exts' => 'index_count',
            'expire' => 86400 // expire after 1 day
        ),

        /** API */
        'api:css' => array(
            'index' => 10,
            'hash_dir' => 'api/css/',
            'file_ext' => '',
            'expire' => 300 // expire after 5 minutes
        ),

        'api:html' => array(
            'index' => 11,
            'hash_dir' => 'api/html/',
            'file_ext' => '',
            'expire' => 300 // expire after 5 minutes
        ),

        'fonts:google_webfonts_api' => array(
            'index' => 20,
            'hash_dir' => 'fonts/google/',
            'file_ext' => '.json',
            'expire' => 86400 // expire after 1 day
        )
    );

    private $cachedir; // cache directory root
    private $cachedirs = array(); // cache directories

    private $table; // database table

    private $index_id_cache = array();

    private $cache_db = array(); // cache database connections
    private $close_db_initiated = false; // close database on shutdown initiated

    /**
     * Load controller
     *
     * @param  Core       $Core Core controller instance.
     * @return Controller Controller instance.
     */
    public static function &load(Core $Core)
    {
        // instantiate controller
        return parent::construct($Core, array(
            // controllers to bind
            'file',
            'options',
            'shutdown'
        ));
    }

    /**
     * Setup controller
     */
    protected function setup()
    {
        // verify cache directory
        if (!is_dir(O10N_CACHE_DIR)) {
            throw new Exception('Cache directory not available.', 'cache');
        }

        // setup cache directory path
        $this->cachedir = $this->file->directory_path('', 'cache');

        // SQLite config
        $this->cache_db = array(
          'file' => $this->cachedir . 'cache.db'
        );

        // cache database table
        $this->table = $this->wpdb->prefix . 'o10n_cache';
    }

    /**
     * Get data from cache
     *
     * @param  string $store_key Cache store key.
     * @param  string $hash      MD5 hash key.
     * @param  bool   $gzip      Gzip compress data.
     * @param  bool   $opcache   Load data using PHP Opcache memory cache
     * @param  string $alt_ext   Alternative file extension
     * @return mixed  Cache data
     */
    final public function get($store_key, $hash, $gzip = false, $opcache = false, $alt_ext = false)
    {

        // store info
        $store = $this->store($store_key);

        // hash file
        $hash_file = substr($hash, 6);

        // hash path
        $hash_path = $this->file->directory_path($store['hash_dir'] . $this->hash_path($hash), 'cache', false);

        // file extension
        $file_ext = ($alt_ext) ? $alt_ext : $store['file_ext'];

        // verify if file exists
        if (!$hash_path) {
            return false;
        }

        // convert hash to numeric ID
        if (isset($store['hash_id']) && $store['hash_id']) {

            // verify if hash file exists
            if (!file_exists($hash_path . $hash_file)) {
                return false;
            }

            // get index ID
            $index_id = $this->index_id($store_key, $hash);

            // dynamic index ID
            if ($index_id && is_array($index_id)) {
                $suffix = $index_id[1];
                $index_id = $index_id[0];
            }

            // verify ID
            if (!$index_id || !is_numeric($index_id)) {
                return false;
            }
            
            // get index data path
            $path = $this->file->directory_path($store['id_dir'] . $this->index_path($index_id), 'cache', false);

            // index directory does not exist
            if (!$path) {
                return false;
            }

            // index file
            $index_file = $index_id . (($suffix) ? ':' . $suffix : '') . $file_ext;

            // set data file path
            $path = $path . $index_file;
        } else {
            $path = $hash_path . $hash_file . $file_ext;
        }

        // check if file exists
        if (!file_exists($path)) {
            return false;
        }

        // return opcache
        if ($opcache) {
            return $this->file->get_opcache($path);
        } elseif ($gzip) {
            return gzinflate(file_get_contents($path));
        } else {
            return file_get_contents($path);
        }
    }
    /**
     * Return cache file path
     *
     * @param  string $store_key Cache store key.
     * @param  string $hash      MD5 hash key.
     * @param  string $alt_ext   Alternative file extension
     * @return string Cache file path
     */
    final public function path($store_key, $hash, $alt_ext = false)
    {

        // store info
        $store = $this->store($store_key);

        // hash file
        $hash_file = substr($hash, 6);

        // hash path
        $hash_path = $this->file->directory_path($store['hash_dir'] . $this->hash_path($hash), 'cache', false);

        if (!$hash_path) {
            return false;
        }

        // file extension
        $file_ext = ($alt_ext) ? $alt_ext : $store['file_ext'];

        // convert hash to numeric ID
        if (isset($store['hash_id']) && $store['hash_id']) {

            // verify if hash file exists
            if (!file_exists($hash_path . $hash_file)) {
                return false;
            }

            // get index ID
            $index_id = $this->index_id($store_key, $hash);

            // dynamic index ID
            if ($index_id && is_array($index_id)) {
                $suffix = $index_id[1];
                $index_id = $index_id[0];
            }

            // verify ID
            if (!$index_id || !is_numeric($index_id)) {
                return false;
            }

            // get index data path
            $index_path = $this->file->directory_path($store['id_dir'] . $this->index_path($index_id), 'cache', false);

            // index file
            $index_file = $index_id . (($suffix) ? ':' . $suffix : '') . $file_ext;

            // set data file path
            return $index_path . $index_file;
        } else {
            return $hash_path . $hash_file . $file_ext;
        }
    }

    /**
     * Return cache url
     *
     * @param  string $store_key Cache store key.
     * @param  string $hash      MD5 hash key.
     * @param  string $alt_ext   Alternative file extension
     * @return string URL to cache file.
     */
    final public function url($store_key, $hash, $alt_ext = false)
    {

        // store info
        $store = $this->store($store_key);

        // hash file
        $hash_file = substr($hash, 6);

        // file extension
        $file_ext = ($alt_ext) ? $alt_ext : $store['file_ext'];

        // convert hash to numeric ID
        if (isset($store['hash_id']) && $store['hash_id']) {

            // get index ID
            $index_id = $this->index_id($store_key, $hash);

            // dynamic index ID
            if ($index_id && is_array($index_id)) {
                $suffix = $index_id[1];
                $index_id = $index_id[0];
            }

            // verify ID
            if (!$index_id || !is_numeric($index_id)) {
                return false;
            }

            // get index data path
            $path = $store['id_dir'] . $this->index_path($index_id);

            // index file
            $index_file = $index_id . (($suffix) ? ':' . $suffix : '') . $file_ext;

            // set data file path
            return $this->file->directory_url($path, 'cache') . $index_file;
        } else {

            // hash path
            $path = $store['hash_dir'] . $this->hash_path($hash);
        
            return $this->file->directory_url($path, 'cache') . $hash_file . $file_ext;
        }
    }

    /**
     * Save data in cache
     *
     * @param  string $store_key         Cache store key.
     * @param  string $hash              MD5 hash key.
     * @param  mixed  $data              Cache data to store.
     * @param  int    $suffix            Cache file name suffix.
     * @param  bool   $gzip              Gzip compress data.
     * @param  bool   $opcache           PHP opcache storage
     * @param  string $file_meta         File meta to store.
     * @param  string $file_meta_opcache PHP opcache meta storage.
     * @return bool   Status true or false.
     */
    final public function put($store_key, $hash, $data, $suffix = false, $gzip = false, $opcache = false, $file_meta = false, $file_meta_opcache = false)
    {
        // verify hash
        if (!is_string($hash) || strlen($hash) !== 32) {
            throw new Exception('Invalid cache file hash.', 'cache');
        }

        // store info
        $store = $this->store($store_key);

        // hash file
        $hash_file = substr($hash, 6);

        // cache file path
        $hash_path = $this->file->directory_path($store['hash_dir'] . $this->hash_path($hash), 'cache');

        // file extension
        $file_ext = $store['file_ext'];

        // hash ID index based file path
        if (isset($store['hash_id']) && $store['hash_id']) {

            // index id
            $index_id = false;

            // query id in cache table
            $exists_id = $this->cachedb_get_hash_id($store_key, $hash);
            $exists_suffix = false;
            if ($exists_id && is_array($exists_id)) {
                $exists_suffix = $exists_id[1];
                $exists_id = $exists_id[0];
            }

            // verify if file exists
            if ($exists_id && $suffix === $exists_suffix && file_exists($hash_path . $hash_file)) {

                // verify index ID
                try {
                    // PHP opcache
                    $index_id = $this->file->get_opcache($hash_path . $hash_file);
                } catch (\Exception $err) {
                    $index_id = false;
                }

                // index ID exists and is valid
                if ($index_id) {
                    if (
                        $index_id === $exists_id
                        || ($suffix && is_array($index_id) && $index_id['id'] === $exists_id && $index_id['suffix'] === $suffix)
                    ) {

                        // update file modified time
                        try {
                            $this->file->touch($hash_path . $hash_file);
                        } catch (\Exception $err) {
                        }

                        // index id
                        $index_id = (is_array($index_id)) ? $index_id['id'] : $index_id;
                    } else {
                        $index_id = false;
                    }
                }
            }

            // create index id in cache table
            if (!$index_id) {
                $index_id = $this->cachedb_create_hash_id($store_key, $hash, $suffix);
            }

            // hash index data
            $hash_index_data = ($suffix) ? array('id' => $index_id, 'suffix' => $suffix) : $index_id;

            // store hash index file
            try {
                $this->file->put_opcache($hash_path . $hash_file, $hash_index_data);
            } catch (\Exception $err) {
                throw new Exception('Failed to store cache file ' . $this->file->safe_path($hash_path . $hash_file), 'cache');
            }

            // get index data path
            $index_path = $this->file->directory_path($store['id_dir'] . $this->index_path($index_id), 'cache');

            // index file
            $index_file = $index_id . (($suffix) ? ':' . $suffix : '') . $file_ext;

            // set data file path
            $path = $index_path . $index_file;

            $hash_file_path = $hash_path . $hash_file;
        } else {
            $path = $hash_path . $hash_file . $file_ext;
            $hash_file_path = $path;
        }

        // regular data
        if (!$opcache) {

            // verify data
            if (!is_string($data)) {
                throw new Exception('Cache data not string.', 'cache');
            }

            // gzip compress data
            if ($gzip === true) {
                $data = gzdeflate($data, 9);
            }
        }

        // write cache data to file
        try {
            // PHP opcache
            if ($opcache) {
                $this->file->put_opcache($path, $data);
            } else {
                $this->file->put_contents($path, $data);
            }
        } catch (\Exception $err) {
            throw new Exception('Failed to store cache file ' . $this->file->safe_path($path) . '<pre>'.$err->getMessage().'</pre>', 'cache');
        }

        // file time
        $time = filemtime($path);
        
        // write file meta
        if ($file_meta) {

            // verify meta data
            if (!$file_meta_opcache && !is_string($file_meta)) {
                throw new Exception('File meta should be a string.', 'cache');
            }

            try {
                // write meta file
                if ($file_meta_opcache) {
                    $this->file->put_opcache($hash_file_path . '.meta', $file_meta);
                } else {
                    $this->file->put_contents($hash_file_path . '.meta', $file_meta);
                }
            } catch (\Exception $err) {
                throw new Exception('Failed to store cache file meta ' . $this->file->safe_path($hash_file_path . '.meta') . ' <pre>'.$err->getMessage().'</pre>', 'cache');
            }
        } elseif (file_exists($hash_file_path . '.meta')) {

            // remove existing meta file
            @unlink($hash_file_path . '.meta');
        }

        if ($opcache) {
            $size = strlen(serialize($data));
        } else {
            // data size
            $size = strlen($data);
        }

        // static gzip for nginx gzip module
        // @link http://nginx.org/en/docs/http/ngx_http_gzip_static_module.html
        if ($gzip === 'static') {

            // gzip cache file path
            $gzpath = $path . '.gz';

            // gzip compress
            $gzdata = gzdeflate($data, 9);

            // size
            $gzsize = strlen($gzdata);

            try {
                // write gzip file
                $this->file->put_contents($gzpath, $gzdata);
            } catch (\Exception $err) {
                throw new Exception('Failed to store gzip cache file ' . $this->file->safe_path($gzpath) . ' <pre>'.$err->getMessage().'</pre>', 'cache');
            }

            $size += $gzsize;
        }

        // open SQLite3 connection
        $db = & $this->open_db('write');

        // store in database
        try {
            if (isset($store['hash_id']) && $store['hash_id']) {
                $result = $this->put_db($db, $store_key, $store, $hash, $time, $size, $suffix, $index_id);
            } else {
                $result = $this->put_db($db, $store_key, $store, $hash, $time, $size);
            }
        } catch (\Exception $err) {
            if (!$this->verify_db()) {
                throw new Exception('Failed to update cache table. ' . $db->lastErrorMsg(), 'config');
            }

            // re-open SQLite3 connection
            $db = & $this->open_db('write');

            if (isset($store['hash_id']) && $store['hash_id']) {
                $result = $this->put_db($db, $store_key, $store, $hash, $time, $size, $suffix, $index_id);
            } else {
                $result = $this->put_db($db, $store_key, $store, $hash, $time, $size);
            }
        }

        return $path;
    }

    /**
     * Save cache file in database
     *
     * @param  object $db        Cache database connection.
     * @param  string $store_key Cache store key.
     * @param  array  $store     Cache store details.
     * @param  string $hash      MD5 hash key.
     * @param  int    $time      Timestamp of file.
     * @param  int    $size      Size of file.
     * @param  int    $suffix    Cache file name suffix.
     * @param  int    $index_id  Index ID
     * @return bool   Status true or false.
     */
    final private function put_db(&$db, $store_key, $store, $hash, $time, $size, $suffix = false, $index_id = false)
    {
        // hash ID index based file path
        if (isset($store['hash_id']) && $store['hash_id']) {

                    // update file data
            $sql = $db->prepare("UPDATE `{$store_key}` SET `d`=?, `z`=?,`x`=? WHERE `i`=?");
            $sql->bindValue(1, $time, SQLITE3_TEXT);
            $sql->bindValue(2, $size, SQLITE3_INTEGER);
            $sql->bindValue(3, $suffix, SQLITE3_TEXT);
            $sql->bindValue(4, $index_id, SQLITE3_INTEGER);

            // exec query
            $sql->execute();
        } else {

                    // prepare query
            $sql = $db->prepare("INSERT OR IGNORE INTO `{$store_key}` (`s`,`h`,`d`,`z`) VALUES (?,?,?,?)");
            $sql->bindValue(1, $store['index'], SQLITE3_INTEGER);
            $sql->bindValue(2, $hash, SQLITE3_TEXT);
            $sql->bindValue(3, $time, SQLITE3_INTEGER);
            $sql->bindValue(4, $size, SQLITE3_INTEGER);

            $sql->execute();
        }
    }

    /**
     * Preserve cache entry by updating expire time
     *
     * @param  string $store_key Cache store key.
     * @param  string $hash      MD5 hash key.
     * @param  bool   $minAge    Minimum age to update time stamp of file.
     * @return bool   Status true or false.
     */
    final public function preserve($store_key, $hash, $minAge = false)
    {
        // verify hash
        if (!is_string($hash) || strlen($hash) !== 32) {
            throw new Exception('Invalid cache file hash.', 'cache');
        }

        // store info
        $store = $this->store($store_key);

        // hash file
        $hash_file = substr($hash, 6);

        // cache file path
        $hash_path = $this->file->directory_path($store['hash_dir'] . $this->hash_path($hash), 'cache');

        // file extension
        $file_ext = $store['file_ext'];

        // get hash index path
        if (isset($store['hash_id']) && $store['hash_id']) {
            $hash_file_path = $hash_path . $hash_file;
        } else {
            $hash_file_path = $hash_path . $hash_file . $file_ext;
        }

        // verify if file exists
        if (!file_exists($hash_file_path)) {
            return false;
        }

        // last modified time
        $filemtime = filemtime($hash_file_path);

        // preserve when older than minimum age
        if ($minAge && $filemtime > $minAge) {

            // file is withing minimum age
            return false;
        }

        // update last modified time
        try {
            $this->file->touch($hash_file_path);
        } catch (\Exception $err) {
        }

        // open SQLite3 connection
        $db = & $this->open_db('write');

        // prepare query
        $sql = $db->prepare("UPDATE `{$store_key}` SET `d`=? WHERE `s`=? AND `h`=?");
        $sql->bindValue(1, filemtime($hash_file_path), SQLITE3_INTEGER);
        $sql->bindValue(2, $store['index'], SQLITE3_INTEGER);
        $sql->bindValue(3, $hash, SQLITE3_TEXT);

        // exec query
        $sql->execute();

        return true;
    }

    /**
     * Verify if cache file exists
     *
     * @param  string $store_key    Cache store key.
     * @param  string $hash         MD5 hash key.
     * @param  int    $expire_check Verify expire date
     * @param  string $alt_ext      Alternative file extension
     * @return bool   Exists true or false.
     */
    final public function exists($store_key, $hash, $expire_check = true, $alt_ext = false)
    {
        // verify hash
        if (!is_string($hash) || strlen($hash) !== 32) {
            throw new Exception('Invalid cache file hash.', 'cache');
        }

        // store info
        $store = $this->store($store_key);

        // hash file
        $hash_file = substr($hash, 6);

        // cache file path
        $hash_path = $this->file->directory_path($store['hash_dir'] . $this->hash_path($hash), 'cache');

        // file extension
        $file_ext = ($alt_ext) ? $alt_ext : $store['file_ext'];

        // get hash index path
        if (isset($store['hash_id']) && $store['hash_id']) {
            $hash_file_path = $hash_path . $hash_file;
        } else {
            $hash_file_path = $hash_path . $hash_file . $file_ext;
        }

        // verify if file exists
        if (!file_exists($hash_file_path)) {
            return false;
        }

        // no expire check
        if ($expire_check && $store['expire']) {
            
            // last modified time
            $filemtime = filemtime($hash_file_path);

            // expired
            if ($filemtime + $store['expire'] < time()) {
                return false;
            }
        }

        // get hash index path
        if (isset($store['hash_id']) && $store['hash_id']) {
            $index_path = $this->path($store_key, $hash, $alt_ext);

            // verify if index file exists
            if (!file_exists($index_path)) {
                return false;
            }
        }

        return true;
    }

    /**
     * Return meta for cache file
     *
     * @param  string $store_key Cache store key.
     * @param  string $hash      MD5 hash key.
     * @param  bool   $opcache   Load meta using PHP Opcache memory cache
     * @return bool   Status true or false.
     */
    final public function meta($store_key, $hash, $opcache = false)
    {
        // verify hash
        if (!is_string($hash) || strlen($hash) !== 32) {
            throw new Exception('Invalid cache file hash.', 'cache');
        }

        // store info
        $store = $this->store($store_key);

        // hash file
        $hash_file = substr($hash, 6);

        // cache file path
        $hash_path = $this->file->directory_path($store['hash_dir'] . $this->hash_path($hash), 'cache');

        // file extension
        $file_ext = $store['file_ext'];

        // file extension
        $file_ext = $store['file_ext'];

        // get hash index path
        if (isset($store['hash_id']) && $store['hash_id']) {
            $hash_file_path = $hash_path . $hash_file;
        } else {
            $hash_file_path = $hash_path . $hash_file . $file_ext;
        }

        // get hash cache file path
        $meta_file_path = $hash_file_path . '.meta';
        
        // get file meta
        if (file_exists($hash_file_path . '.meta')) {
            try {
                // PHP opcache
                if ($opcache) {
                    $meta = $this->file->get_opcache($hash_file_path . '.meta');
                } else {
                    $meta = file_get_contents($hash_file_path . '.meta');
                }
            } catch (\Exception $err) {
                $meta = false;
            }

            return $meta;
        }

        return false;
    }

    /**
     * Delete cache entry
     *
     * @param  string $store_key Cache store key.
     * @param  string $hash      MD5 hash key.
     * @param  bool   $deleteDB  Delete entry from database
     * @return bool   Status true or false.
     */
    final public function delete($store_key, $hash, $deleteDB = true)
    {
        // verify hash
        if (!is_string($hash) || strlen($hash) !== 32) {
            throw new Exception('Invalid cache file hash.', 'cache');
        }

        // store info
        $store = $this->store($store_key);

        // hash file
        $hash_file = substr($hash, 6);

        // cache file path
        $hash_path = $this->file->directory_path($store['hash_dir'] . $this->hash_path($hash), 'cache', false);

        // verify if file exists
        if ($hash_path && file_exists($hash_path . $hash_file)) {

            // hash ID index based file path
            if (isset($store['hash_id']) && $store['hash_id']) {
                $hash_file_path = $hash_path . $hash_file;

                // elete index file
                $this->delete_index_file($store_key, $hash);
            } else {
                $hash_file_path = $hash_path . $hash_file . $store['file_ext'];
            }

            // delete cache file
            $this->delete_hash_file($store_key, $hash_file_path);
        }

        // delete file from database
        if ($deleteDB) {

            // open SQLite3 connection
            $db = & $this->open_db('write');

            // prepare query
            $sql = $db->prepare("DELETE FROM `{$store_key}` WHERE `s`=? AND `h`=?");
            $sql->bindValue(2, $store['index'], SQLITE3_INTEGER);
            $sql->bindValue(3, $hash, SQLITE3_TEXT);

            // exec query
            $sql->execute();
        }

        return true;
    }

    /**
     * Delete hash cache files
     *
     * @param  string $store_key Cache store key.
     * @param  string $file      Cache file to delete.
     * @return bool   Status true or false.
     */
    final public function delete_hash_file($store_key, $file)
    {

        // store info
        $store = $this->store($store_key);

        // delete file
        if (file_exists($file)) {
            @unlink($file);
        }

        // delete static gzip
        if (file_exists($file . '.gz')) {
            @unlink($file . '.gz');
        }

        // delete meta
        if (file_exists($file . '.meta')) {
            @unlink($file . '.meta');
        }

        // alternative extensions
        if (isset($store['alt_exts'])) {
            if (is_string($store['alt_exts'])) {
                switch ($store['alt_exts']) {
                    case "index_count":
                        $i = 1;
                        while (file_exists($file . '.' . $i)) {
                            @unlink($file . '.' . $i);
                            $i++;
                        }
                    break;
                    default:
                        throw new Exception('Invalid alt exts.', 'cache');
                    break;
                }
            } elseif (!empty($store['alt_exts'])) {
                foreach ($store['alt_exts'] as $ext) {

                // delete ext file
                    if (file_exists($file . $ext)) {
                        @unlink($file . $ext);
                    }
                }
            }
        }

        return true;
    }

    /**
     * Delete index cache file
     *
     * @param  string $store_key Cache store key.
     * @param  string $hash      MD5 hash key.
     * @return bool   Status true or false.
     */
    final public function delete_index_file($store_key, $hash)
    {
        // store info
        $store = $this->store($store_key);

        // not an index store
        if (!isset($store['hash_id']) || !$store['hash_id']) {
            return;
        }

        // query id in cache table
        $exists_id = $this->cachedb_get_hash_id($store_key, $hash);
        if ($exists_id) {
            if (is_array($exists_id)) {
                $exists_suffix = $exists_id[1];
                $exists_id = $exists_id[0];
            }

            if (is_numeric($exists_id)) {

                // get index data path
                $index_path = $this->file->directory_path($store['id_dir'] . $this->index_path($index_id), 'cache', false);

                if ($index_path) {

                    // index file
                    $index_file = $index_id . (($suffix) ? ':' . $suffix : '') . $store['file_ext'];

                    // set data file path
                    if (file_exists($index_path . $index_file)) {
                        @unlink($index_path . $index_file);
                    }
                }
            }
        }

        return true;
    }

    /**
     * Return database id for hash
     *
     * @param  string $store_key Cache store key.
     * @param  string $hash      MD5 hash key.
     * @return bool   Status true or false.
     */
    final public function cachedb_get_hash_id($store_key, $hash)
    {
        // verify store
        $store = $this->store($store_key);

        if (!isset($store['hash_id']) || !$store['hash_id']) {
            throw new Exception('Cache store does not support hash index ID.', 'config');
        }

        // open SQLite3 connection
        $db = & $this->open_db('write');

        // verify if hash file exists
        try {
            $sql = $db->prepare("SELECT `i`,`x` FROM `{$store_key}` WHERE `s`=? AND `h`=? LIMIT 1");
        } catch (\Exception $err) {
            if (!$this->verify_db()) {
                throw new Exception('Failed to query cache table. ' . $db->lastErrorMsg(), 'config');
            }

            return $this->cachedb_get_hash_id($store_key, $hash);
        }
        $sql->bindValue(1, $store['index'], SQLITE3_INTEGER);
        $sql->bindValue(2, $hash, SQLITE3_TEXT);

        // exec query
        try {
            $result = $sql->execute();
        } catch (\Exception $err) {
            $result = false;
        }
        if (!$result) {
            if (!$this->verify_db()) {
                throw new Exception('Failed to query cache table. ' . $db->lastErrorMsg(), 'config');
            }

            return $this->cachedb_get_hash_id($store_key, $hash);
        }

        // fetch result
        $row = $result->fetchArray(SQLITE3_NUM);
        if ($row && $row[0]) {
            if (isset($row[1]) && !empty($row[1])) {
                return array($row[0],$row[1]);
            }

            return $row[0];
        }

        return false;
    }

    /**
     * Create database id for hash
     *
     * @param  string $store_key Cache store key.
     * @param  string $hash      MD5 hash key.
     * @return int    Hash id
     */
    final public function cachedb_create_hash_id($store_key, $hash, $suffix = '')
    {
        // verify store
        $store = $this->store($store_key);

        if (!isset($store['hash_id']) || !$store['hash_id']) {
            throw new Exception('Cache store does not support hash index ID.', 'config');
        }

        // open SQLite3 connection
        $db = & $this->open_db('write');

        // create index row
        try {
            $sql = $db->prepare("INSERT INTO `{$store_key}` (`s`,`h`,`x`) VALUES (?,?,?)");
            $sql->bindValue(1, $store['index'], SQLITE3_INTEGER);
            $sql->bindValue(2, $hash, SQLITE3_TEXT);
            $sql->bindValue(3, $suffix, SQLITE3_TEXT);
            $result = $sql->execute();
        } catch (\Exception $err) {
            throw new Exception('Failed to insert row in cache table. ' . $db->lastErrorMsg(), 'config');
        }

        // index ID
        $index_id = $db->lastInsertRowID();

        if (!$index_id) {
            throw new Exception('Failed create index ID. ' . $db->lastErrorMsg(), 'config');
        }
        
        return $index_id;
    }

    /**
     * Return store info
     *
     * @param  string $store_key Store key.
     * @return array  Store details.
     */
    final public function store($store_key)
    {
        // verify if store key is valid
        if (!isset($this->stores[$store_key])) {
            throw new Exception('Invalid store key: ' . $store_key, 'cache');
        }

        return $this->stores[$store_key];
    }
    
    /**
     * Return hash directory path
     *
     * @param  string $hash Cache file hash.
     * @return string Cache file path.
     */
    final public function hash_path($hash)
    {
        // verify hash
        if (!is_string($hash) || strlen($hash) !== 32) {
            throw new Exception('Invalid cache file hash ' . esc_html($hash), 'cache');
        }

        // the path to return
        $path = '';

        // lowercase
        $hash = strtolower($hash);

        // create 3 levels of 2-char subdirectories, [a-z0-9]
        $dir_blocks = array_slice(str_split($hash, 2), 0, 3);
        foreach ($dir_blocks as $block) {
            $path .= $block  . '/';
        }

        return $path;
    }
    
    /**
     * Return index ID
     *
     * @param  string $store_key Cache store key.
     * @param  string $hash      MD5 hash key.
     * @return string Cache file path.
     */
    final public function index_id($store_key, $hash)
    {

        // try local cache
        $cache_key = $store_key . ':' . $hash;
        if (isset($this->index_id_cache[$cache_key])) {
            return $this->index_id_cache[$cache_key];
        }

        // verify store
        $store = $this->store($store_key);

        if (!isset($store['hash_id']) || !$store['hash_id']) {
            throw new Exception('Cache store does not support hash index ID.', 'config');
        }
        
        // hash file
        $hash_file = substr($hash, 6);

        // hash path
        $hash_path = $this->file->directory_path($store['hash_dir'] . $this->hash_path($hash), 'cache', false);

        // query index ID from cache file
        try {
            // PHP opcache
            $index_id = $this->file->get_opcache($hash_path . $hash_file);
        } catch (\Exception $err) {
            return $this->index_id_cache[$cache_key] = false;
        }

        $suffix = false;

        // dynamic ID
        if ($index_id && is_array($index_id)) {
            if (!isset($index_id['id'])) {
                throw new Exception('Invalid cache index ID data (array).', 'cache');
            }
            if (isset($index_id['suffix'])) {
                $suffix = $index_id['suffix'];
            }

            $index_id = $index_id['id'];
        }

        // verify ID
        if (!$index_id || !is_numeric($index_id)) {
            return $this->index_id_cache[$cache_key] = false;
        }

        // verify hash
        if (!is_numeric($index_id)) {
            throw new Exception('Invalid cache file index ID.', 'cache');
        }

        if ($suffix) {
            return $this->index_id_cache[$cache_key] = array($index_id,$suffix);
        } else {
            return $this->index_id_cache[$cache_key] = $index_id;
        }
    }
    
    /**
     * Return index ID directory path
     *
     * @param  int    $index_id Index ID
     * @return string Cache file path.
     */
    final public function index_path($index_id)
    {

        // verify hash
        if (!is_numeric($index_id)) {
            throw new Exception('Invalid cache file index ID.', 'cache');
        }
        // the path to return
        $path = '';

        // 1m index
        $m_index = floor($index_id / 1000000);

        // if ($m_index > 0) {

        // 1m increments
        $path .= $m_index . '/';
        // }

        // 1k index
        $k_index = ceil(($index_id - ($m_index * 100000)) / 1000);

        // 1k increments
        $path .= $k_index . '/';

        return $path;
    }

    /**
     * Get cache stats
     *
     * @param  mixed $locations Cache location keys or indexes.
     * @return array Stats
     */
    final public function stats($locations = false)
    {

        // all locations
        if (!$locations) {
            $location_indexes = false;
        } else {

            // convert key to type
            if (is_numeric($locations) || is_string($locations)) {
                $locations = array($locations);
            }
            if (!is_array($locations)) {
                throw new Exception('Invalid cache locations.', 'cache');
            }
            $locations = array_unique($locations);

            $location_indexes = array();

            foreach ($locations as $key) {
                $location = $this->location($key);
                $location_indexes[] = $location['index'];
            }
        }

        // query database
        $result = $this->wpdb->get_results("SELECT `location`,count(*) as `count`,SUM(`size`) as `size` FROM {$this->table}".(($location_indexes) ? ' WHERE `location` IN (' . implode(', ', $location_indexes) . ')' : '')." GROUP BY `location`", 'ARRAY_A');

        // stats results
        $stats = array();
        foreach ($result as $row) {
            $stats[$this->type_key($row['location'])] = array(
                'count' => $row['count'],
                'size' => $row['size']
            );
        }

        return $stats;
    }

    /**
     * Delete empty cache directory
     *
     * @param string $dir Directory to clean.
     */
    final private function delete_empty_directory($dir)
    {

        // hash cache directory
        if (preg_match('|^((.*/[a-fA-F0-9]{2}/)[a-fA-F0-9]{2}/)[a-fA-F0-9]{2}/$|', $dir, $out)) {
            foreach ($out as $n => $hashdir) {
                if (empty(array_diff(scandir($hashdir), array('.','..')))) { // empty
                    // try {}
                    $this->file->rmdir($hashdir);
                } else {
                    break;
                }
            }
        } else {
            if (empty(array_diff(scandir($dir), array('.','..')))) { // empty
                // try {}
                $this->file->rmdir($dir);
            }
        }
    }

    /**
     * Clear cache
     *
     * @param  mixed $location Cache location key or index.
     * @return array Stats.
     */
    final public function clear($location = false)
    {

        // start time
        $time = time();

        // delete specific cache location
        if ($location) {

            // cache location
            $location = $this->location($location);

            // get cache path for type
            $path = $this->file->directory_path($location['dir']);

            // delete cache directory
            // try {}
            $this->file->rmdir($path);

            // delete query
            $this->wpdb->query($this->wpdb->prepare("DELETE FROM {$this->table} WHERE `location`='%d'", $location['index']));
        } else {

            // delete all

            // delete cache directory
            // try {}
            $this->file->rmdir(O10N_CACHE_DIR, true);


            // delete query
            $this->wpdb->query("TRUNCATE {$this->table}");
        }
    }

    /**
     * Prune expired cache objects
     *
     * @param mixed $location Location key or index.
     */
    final public function prune_expired($location = false)
    {

        // start time
        $time = time();

        // cache location
        if ($location) {

            // type index
            $location = $this->location($location);

            // build query
            $query = $this->wpdb->prepare("SELECT HEX(`hash`) as `hash`,`ext` FROM {$this->table} WHERE `location`='%d' AND `expire`!=0 AND DATE_ADD(`date`, INTERVAL `expire` SECOND) < FROM_UNIXTIME(%d)", $location['index'], $time);
            
            // delete query
            $delete_query = $this->wpdb->prepare("DELETE FROM {$this->table} WHERE `location`='%d' AND `expire`!=0 AND DATE_ADD(`date`, INTERVAL `expire` SECOND) < FROM_UNIXTIME(%d)", $location['index'], $time);
        } else {

            // all types
            $location = false;

            // build query
            $query = $this->wpdb->prepare("SELECT HEX(`hash`) as `hash`,`location`,`ext` FROM {$this->table} WHERE `expire`!=0 AND DATE_ADD(`date`, INTERVAL `expire` SECOND) < FROM_UNIXTIME(%d)", $time);
            
            // delete query
            $delete_query = $this->wpdb->prepare("DELETE FROM {$this->table} WHERE `expire`!=0 AND DATE_ADD(`date`, INTERVAL `expire` SECOND) < FROM_UNIXTIME(%d)", $time);
        }

        $result = $this->db->query($query);
        if ($result) {

            // count entries
            $num = $this->db->num_rows($result);

            // process entries
            while (($expired_entry = $this->db->fetch_assoc($result))) {
                $this->delete(
                    $expired_entry['hash'],
                    (($location) ? $location['index'] : $expired_entry['location']),
                    $expired_entry['ext'],
                    false // do not delete from DB
                );
            }
            $this->db->free_result($result);

            // delete entries from database
            $this->wpdb->query($delete_query . ' LIMIT ' . $num);
        }
    }

    /**
     * Prune cache
     *
     * - clear expired entries
     * - clear cache entries with missing files
     * - clear stale cache files
     * - clear empty cache directory structure
     */
    final public function prune()
    {

        // prune status file
        $prune_status_file = trailingslashit(O10N_CACHE_DIR) . 'prune-status.log';

        // verify status
        if (file_exists($prune_status_file)) {
            $status = file_get_contents($prune_status_file);

            // started less then 1 hour ago, ignore
            if ($status !== 'idle' && is_numeric($status) && $status > (time() - 3600)) {
                return;
            }
        }

        // set status time
        // try {}
        $this->file->put_contents($prune_status_file, time());

        // clear expired cache objects
        $this->clear_expired(false);

        // clear cache objects with missing files
        $result = $this->db->query("SELECT HEX(`hash`) as `hash`,`location`,`ext` FROM {$this->table} WHERE `date`<DATE_SUB(NOW(), INTERVAL 6 HOUR) ORDER BY `date` ASC");
        if ($result) {

            // process entries
            while (($entry = $this->db->fetch_assoc($result))) {

                // get file path
                $file = $this->file_path($entry['hash'], $entry['location'], $entry['ext'], false);
                
                // file does not exist
                if (!$file || !file_exists($file)) {
                    if ($file) {
                        
                        // delete cache file + static / meta files
                        $this->delete_file($file);
                        
                        // clear empty cache directory
                        $this->delete_empty_directory(pathinfo($file, PATHINFO_DIRNAME));
                    }

                    // delete entry from database
                    $this->wpdb->query($this->wpdb->prepare("DELETE FROM {$this->table} WHERE `hash`=UNHEX('%s') AND `location`='%d' LIMIT 1", $entry['hash'], $this->type_index($entry['location'])));
                }
            }
            $this->db->free_result($result);
        }

        // delete stale cache files
        $cachedirs = array();
        foreach ($this->locations as $location) {
            $cachepath = $this->file->directory_path($location['dir'], 'cache', false);
            if (!$cachepath) {
                continue;
            }

            // scan cache directory for stale files
            $this->prune_stale_cache_files($cachepath);
        }

        // set idle status
        $this->file->put_contents($prune_status_file, 'idle');
    }

    /**
     * Scan cache direcotory to check for stale files
     *
     * @param string $dir   Cache directory to scan.
     * @param int    $level Hash directory depth.
     */
    final private function prune_stale_cache_files($dir, $level = 0)
    {

        // scan directory contents
        $dir_contents = array_diff(scandir($dir), array('.','..'));
        if (empty($dir_contents)) {
            return false;
        }

        $cachefiles = array();

        foreach ($dir_contents as $file) {
            if ($level < 3) {

                // verify content
                if (strlen($file) !== 2 || !is_dir($dir . $file)) {
                    continue;
                }

                $this->prune_stale_cache_files($dir . $file, ++$level);
                continue;
            }

            $cachefile = $dir . $file;
            if (!is_file($cachefile)) {
                continue;
            }

            // skip static / meta files
            $static = false;
            if (substr($file, -3) === '.gz') {
                $static = '.gz';
            } elseif (substr($file, -5) === '.meta') {
                $static = '.meta';
            }
            if ($static !== false) {

                // verify if corresponding cache file is missing
                $parentfile = substr($cachefile, 0, (strlen($static) * -1));
                if (!file_exists($parentfile)) {

                    // remove stale static file
                    @unlink($cachefile);
                }

                continue;
            }

            // extract hash from cache file
            $cachehash = (strpos($file, '.') !== false) ? explode('.', $file, 1)[0] : $file;

            // not a cache file, ignore
            if (strlen($cachehash) !== 32) {
                continue;
            }

            $cachefiles[$cachehash] = $cachefile;
        }

        // file level
        if ($level === 3) {

            // verify if database entries exists
            if (!empty($cachefiles)) {

                // build query to check if cache entries exist in database and/or is expired
                $query = "SELECT HEX(`hash`) as `hash`, `ext`, IF (`expire`!='0' AND DATE_ADD(`date`, INTERVAL `expire` SECOND) < FROM_UNIXTIME(NOW()),1,0) as `expired` FROM {$this->table} WHERE `location`='%d' AND ( ";
                $n = 0;
                foreach ($cachefiles as $hash => $file) {
                    if ($n > 0) {
                        $query .= " OR ";
                    }
                    $query .= "`hash`=UNHEX('".$this->db->escape($hash)."')";
                    $n++;
                }
                $query .= " ) LIMIT " . count($cachefiles);

                // query results
                $result = $this->db->query($this->wpdb->prepare($query, $location['index']));
                if ($result) {

                    // process entries
                    while (($entry = $this->db->fetch_assoc($result))) {

                        // remove from cache file list
                        unset($cachefiles[$entry['hash']]);

                        // file is expired, delete
                        if (intval($entry['expired']) === 1) {
                            $this->delete($entry['hash'], $location['index'], $entry['ext']);
                        }
                    }
                    $this->db->free_result($result);
                }

                // delete stale cache files
                if (!empty($cachefiles)) {
                    foreach ($cachefiles as $hash => $cachefile) {
                        $this->delete_file($cachefile);
                    }
                }
            }

            // delete empty cache directory
            $this->delete_empty_directory($dir);
        }
    }

    /**
     * Return cached path
     */
    final private function cached_path($key, $path = -1)
    {

        // set path
        if ($path !== -1) {
            $this->cache_paths[$key] = $path;
        } else {
            return (isset($this->cache_paths[$key])) ? $this->cache_paths[$key] : false;
        }
    }

    /**
     * Return cache database table
     */
    final public function db_table()
    {
        return $this->table;
    }

    /**
     * Open cache database connection
     *
     * @param  string $write Write mode.
     * @return object SQLite3 database controller.
     */
    final private function &open_db($type = 'read')
    {

        // create database
        if (!file_exists($this->cache_db['file'])) {
            $this->create_db();
        }

        // close connections on shutdown
        $this->init_close_db();

        if (!isset($this->cache_db[$type])) {
            try {
                if ($type === 'write') {
                    $this->cache_db[$type] = new \SQLite3($this->cache_db['file'], SQLITE3_OPEN_READWRITE);
                    $this->cache_db[$type]->enableExceptions(true);
                    $this->cache_db[$type]->busyTimeout(5000);
                    $this->cache_db[$type]->exec('PRAGMA journal_mode = wal;PRAGMA foreign_keys = 1;');
                } else {
                    $this->cache_db[$type] = new \SQLite3($this->cache_db['file'], SQLITE3_OPEN_READONLY);
                    $this->cache_db[$type]->enableExceptions(true);
                }
            } catch (\Exception $err) {
                $this->cache_db[$type] = false;
            }

            // failed to connect to database
            if (!$this->cache_db[$type]) {

                // verify database integrity
                if ($type === 'write') {
                    $this->verify_db();
                }

                throw new Exception('Failed to open SQLite3 database. ' . $this->cache_db[$type]->lastErrorMsg(), 'config');
            }
        }

        return $this->cache_db[$type];
    }

    /**
     * Close cache database connection
     *
     * @param mixed $write Connection type to close
     */
    final public function close_db($type = false)
    {
        // close all
        if ($type) {
            $types = array($type);
        } else {
            $types = array('write', 'read');
        }

        foreach ($types as $type) {
            if (isset($this->cache_db[$type])) {
                $this->cache_db[$type]->close();
                unset($this->cache_db[$type]);
            }
        }
    }

    /**
     * Verify cache database
     */
    final public function verify_db()
    {
        // open database write connection
        try {
            $db = & $this->open_db('read');
        } catch (Exception $err) {
            $db = false;
        }
        if (!$db) {
            return $this->create_db();
        }
        
        // verify tables
        foreach ($this->stores as $store_key => $store) {
            if (isset($store['hash_id']) && $store['hash_id']) {
                try {
                    $result = $db->query("SELECT `i`,`s`,`h`,`d`,`z` FROM `{$store_key}` LIMIT 1");
                } catch (\Exception $err) {
                    $result = false;
                }
            } else {
                try {
                    $result = $db->query("SELECT `s`,`h`,`d`,`z` FROM `{$store_key}` LIMIT 1");
                } catch (\Exception $err) {
                    $result = false;
                }
            }
            if (!$result) {
                return $this->create_db();
            }
        }
    
        
        return false;
    }

    /**
     * Create cache database
     */
    final public function create_db()
    {
        // close any db connection
        $this->close_db();

        // delete existing database file
        if (file_exists($this->cache_db['file'])) {
            @unlink($this->cache_db['file']);
            @unlink($this->cache_db['file'].'-shm');
            @unlink($this->cache_db['file'].'-wal');
        }

        // initiate database file
        try {
            $this->file->put_contents($this->cache_db['file'], '');
        } catch (\Exception $err) {
            throw new Exception('Failed to create cache database. ' . $err->getMessage(), 'config');
        }

        // open database write connection
        $db = & $this->open_db('write');

        foreach ($this->stores as $store_key => $store) {
            if (isset($store['hash_id']) && $store['hash_id']) {

                /**
                 * Create hash ID index table
                 *
                 * i = id
                 * s = store
                 * h = hash
                 * d = date
                 * z = size
                 * x = suffix
                 */
                $sql = $db->prepare("CREATE TABLE `{$store_key}` (
                  `i`    INTEGER PRIMARY KEY AUTOINCREMENT NOT NULL,
                  `s`    INTEGER NOT NULL,
                  `h`    VARCHAR(32) NOT NULL,
                  `d`,  INTEGER,
                  `z`  INTEGER,
                  `x`    VARCHAR(100) NOT NULL
                );
                CREATE  INDEX `{$store_key}_store` ON `{$store_key}` (`s`);
                CREATE UNIQUE INDEX `{$store_key}_hash` ON `{$store_key}` (`s`,`h`);
                CREATE INDEX `{$store_key}_date` ON `{$store_key}` (`d`);");
            } else {

                /**
                 * Create hash table
                 *
                 * s = store
                 * h = hash
                 * d = date
                 * z = size
                 */
                $sql = $db->prepare("CREATE TABLE `{$store_key}` (
                  `s`    INTEGER NOT NULL,
                  `h`    VARCHAR(32) NOT NULL,
                  `d`,  INTEGER,
                  `z`  INTEGER,
                  PRIMARY KEY (`s`,`h`)
                );
                CREATE INDEX `{$store_key}_store` ON `{$store_key}` (`s`);
                CREATE INDEX `{$store_key}_date` ON `{$store_key}` (`d`);");
            }
        
            // execute query
            $result = $sql->execute();
            if (!$result) {
                throw new Exception('Failed to create cache table. ' . $db->lastErrorMsg(), 'config');
            }
        }

        return true;
    }

    /**
     * Initiate close database backup process
     */
    final private function init_close_db()
    {

        // close connections on shutdown
        if (!$this->close_db_initiated) {
            $this->shutdown->add(array($this,'close_db'));
            $this->close_db_initiated = true;
        }
    }
}
