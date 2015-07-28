<?php
/**
 * DokuWiki Plugin authdrupal7 (Auth Component)
 *
 * @license GPL 2 http://www.gnu.org/licenses/gpl-2.0.html
 * @author  Matthias Jung <matzekuh@web.de>
 */

// must be run within Dokuwiki
if(!defined('DOKU_INC')) die();

class auth_plugin_authdrupal7 extends DokuWiki_Auth_Plugin {


    /**
     * Constructor.
     */
    public function __construct() {
        parent::__construct(); // for compatibility
        
        if(!function_exists('mysql_connect')) {
            $this->_debug("MySQL err: PHP MySQL extension not found.", -1, __LINE__, __FILE__);
            $this->success = false;
            return;
        }
        
        // set capabilities based upon config strings set
        if(!$this->getConf('server') || !$this->getConf('user') || !$this->getConf('database')) {
            $this->_debug("MySQL err: insufficient configuration.", -1, __LINE__, __FILE__);
            $this->success = false;
            return;
        }

        // FIXME set capabilities accordingly
        $this->cando['addUser']     = false; // can Users be created?
        $this->cando['delUser']     = false; // can Users be deleted?
        $this->cando['modLogin']    = false; // can login names be changed?
        $this->cando['modPass']     = false; // can passwords be changed?
        $this->cando['modName']     = false; // can real names be changed?
        $this->cando['modMail']     = false; // can emails be changed?
        $this->cando['modGroups']   = false; // can groups be changed?
        $this->cando['getUsers']    = true; // can a (filtered) list of users be retrieved?
        $this->cando['getUserCount']= true; // can the number of users be retrieved?
        $this->cando['getGroups']   = true; // can a list of available groups be retrieved?
        $this->cando['external']    = false; // does the module do external auth checking?
        $this->cando['logout']      = true; // can the user logout again? (eg. not possible with HTTP auth)

        // FIXME intialize your auth system and set success to true, if successful
        $this->success = true;
    }


    /**
     * Log off the current user [ OPTIONAL ]
     */
    //public function logOff() {
    //}

    /**
     * Do all authentication [ OPTIONAL ]
     *
     * @param   string  $user    Username
     * @param   string  $pass    Cleartext Password
     * @param   bool    $sticky  Cookie should not expire
     * @return  bool             true on successful auth
     */
    //public function trustExternal($user, $pass, $sticky = false) {
        /* some example:

        global $USERINFO;
        global $conf;
        $sticky ? $sticky = true : $sticky = false; //sanity check

        // do the checking here

        // set the globals if authed
        $USERINFO['name'] = 'FIXME';
        $USERINFO['mail'] = 'FIXME';
        $USERINFO['grps'] = array('FIXME');
        $_SERVER['REMOTE_USER'] = $user;
        $_SESSION[DOKU_COOKIE]['auth']['user'] = $user;
        $_SESSION[DOKU_COOKIE]['auth']['pass'] = $pass;
        $_SESSION[DOKU_COOKIE]['auth']['info'] = $USERINFO;
        return true;

        */
    //}

    /**
     * Checks if the given user exists and the given plaintext password
     * is correct. Furtheron it might be checked wether the user is
     * member of the right group
     *
     * Depending on which SQL string is defined in the config, password
     * checking is done here (getpass) or by the database (passcheck)
     *
     * @param  string $user user who would like access
     * @param  string $pass user's clear text password to check
     * @return bool
     *
     * @author  Andreas Gohr <andi@splitbrain.org>
     * @author  Matthias Grimm <matthiasgrimm@users.sourceforge.net>
     */
    public function checkPass($user, $pass) {
        global $conf;
        $rc = false;
        if($this->_openDB()) {
            $sql    = str_replace('%{user}', $this->_escape($user), $this->getConf('checkPass'));
            $sql    = str_replace('%{drupal_prefix}', $this->getConf('drupalPrefix'), $sql);
            //$sql    = str_replace('%{dgroup}', $this->_escape($conf['defaultgroup']), $sql);
            $result = $this->_queryDB($sql);
            if($result !== false && count($result) == 1) {
                $rc = $this->hash_password($pass, $result[0]['pass']) == $result[0]['pass'];
            }
            $this->_closeDB();
        }
        return $rc;
    }
    
    public function hash_password($pass, $hashedpw) {
        $drupalroot = $this->getConf('drupalRoot');
        require_once($drupalroot.'includes/password.inc');
        if(!function_exists(_password_crypt)) {
            msg("Drupal installation not found. Please check your configuration.",-1,__LINE__,__FILE__);
            $this->success = false;
        }
        $hash = _password_crypt('sha512', $pass, $hashedpw);
        return $hash;
    }

    /**
     * Return user info
     *
     * @author  Andreas Gohr <andi@splitbrain.org>
     * @author  Matthias Grimm <matthiasgrimm@users.sourceforge.net>
     *
     * @param string $user user login to get data for
     * @param bool $requireGroups  when true, group membership information should be included in the returned array;
     *                             when false, it maybe included, but is not required by the caller
     * @return array|bool
     */
    public function getUserData($user, $requireGroups=true) {
        if($this->_cacheExists($user, $requireGroups)) {
            return $this->cacheUserInfo[$user];
        }
        if($this->_openDB()) {
            $this->_lockTables("READ");
            $info = $this->_getUserInfo($user, $requireGroups);
            $this->_unlockTables();
            $this->_closeDB();
        } else {
            $info = false;
        }
        return $info;
    }

    /**
     * Bulk retrieval of user data [implement only where required/possible]
     *
     * Set getUsers capability when implemented
     *
     * @param   int   $start     index of first user to be returned
     * @param   int   $limit     max number of users to be returned
     * @param   array $filter    array of field/pattern pairs, null for no filter
     * @return  array list of userinfo (refer getUserData for internal userinfo details)
     */
    public function retrieveUsers($start = 0, $limit = -1, $filter = null) {
        // FIXME implement
        return array();
    }

    /**
     * Return a count of the number of user which meet $filter criteria
     * [should be implemented whenever retrieveUsers is implemented]
     *
     * Set getUserCount capability when implemented
     *
     * @param  array $filter array of field/pattern pairs, empty array for no filter
     * @return int
     */
    public function getUserCount($filter = array()) {
        // FIXME implement
        return 0;
    }

    /**
     * Retrieve groups [implement only where required/possible]
     *
     * Set getGroups capability when implemented
     *
     * @param   int $start
     * @param   int $limit
     * @return  array
     */
    public function retrieveGroups($start = 0, $limit = 0) {
        // FIXME implement
        return array();
    }

    /**
     * Return case sensitivity of the backend
     *
     * MYSQL is case-insensitive
     *
     * @return false
     */
    public function isCaseSensitive() {
        return false;
    }

    /**
     * Sanitize a given username
     *
     * This function is applied to any user name that is given to
     * the backend and should also be applied to any user name within
     * the backend before returning it somewhere.
     *
     * This should be used to enforce username restrictions.
     *
     * @param string $user username
     * @return string the cleaned username
     */
    public function cleanUser($user) {
        return $user;
    }

    /**
     * Check Session Cache validity [implement only where required/possible]
     *
     * DokuWiki caches user info in the user's session for the timespan defined
     * in $conf['auth_security_timeout'].
     *
     * This makes sure slow authentication backends do not slow down DokuWiki.
     * This also means that changes to the user database will not be reflected
     * on currently logged in users.
     *
     * To accommodate for this, the user manager plugin will touch a reference
     * file whenever a change is submitted. This function compares the filetime
     * of this reference file with the time stored in the session.
     *
     * This reference file mechanism does not reflect changes done directly in
     * the backend's database through other means than the user manager plugin.
     *
     * Fast backends might want to return always false, to force rechecks on
     * each page load. Others might want to use their own checking here. If
     * unsure, do not override.
     *
     * @param  string $user - The username
     * @return bool
     */
    //public function useSessionCache($user) {
      // FIXME implement
    //}
    
    
    /**
     * Opens a connection to a database and saves the handle for further
     * usage in the object. The successful call to this functions is
     * essential for most functions in this object.
     *
     * @author Matthias Grimm <matthiasgrimm@users.sourceforge.net>
     *
     * @return bool
     */
    protected function _openDB() {
        if(!$this->dbcon) {
            $con = @mysql_connect($this->getConf('server'), $this->getConf('user'), $this->getConf('password'));
            if($con) {
                if((mysql_select_db($this->getConf('database'), $con))) {
                    if((preg_match('/^(\d+)\.(\d+)\.(\d+).*/', mysql_get_server_info($con), $result)) == 1) {
                        $this->dbver = $result[1];
                        $this->dbrev = $result[2];
                        $this->dbsub = $result[3];
                    }
                    $this->dbcon = $con;
                    if($this->getConf('charset')) {
                        mysql_query('SET CHARACTER SET "'.$this->getConf('charset').'"', $con);
                    }
                    return true; // connection and database successfully opened
                } else {
                    mysql_close($con);
                    $this->_debug("MySQL err: No access to database {$this->getConf('database')}.", -1, __LINE__, __FILE__);
                }
            } else {
                $this->_debug(
                    "MySQL err: Connection to {$this->getConf('user')}@{$this->getConf('server')} not possible.",
                    -1, __LINE__, __FILE__
                );
            }
            return false; // connection failed
        }
        return true; // connection already open
    }
    
    /**
     * Closes a database connection.
     *
     * @author Matthias Grimm <matthiasgrimm@users.sourceforge.net>
     */
    protected function _closeDB() {
        if($this->dbcon) {
            mysql_close($this->dbcon);
            $this->dbcon = 0;
        }
    }
    
        /**
     * Sends a SQL query to the database and transforms the result into
     * an associative array.
     *
     * This function is only able to handle queries that returns a
     * table such as SELECT.
     *
     * @author Matthias Grimm <matthiasgrimm@users.sourceforge.net>
     *
     * @param string $query  SQL string that contains the query
     * @return array|false with the result table
     */
    protected function _queryDB($query) {
        if($this->getConf('debug') >= 2) {
            msg('MySQL query: '.hsc($query), 0, __LINE__, __FILE__);
        }
        $resultarray = array();
        if($this->dbcon) {
            $result = @mysql_query($query, $this->dbcon);
            if($result) {
                while(($t = mysql_fetch_assoc($result)) !== false)
                    $resultarray[] = $t;
                mysql_free_result($result);
                return $resultarray;
            }
            $this->_debug('MySQL err: '.mysql_error($this->dbcon), -1, __LINE__, __FILE__);
        }
        return false;
    }
    
    /**
     * Escape a string for insertion into the database
     *
     * @author Andreas Gohr <andi@splitbrain.org>
     *
     * @param  string  $string The string to escape
     * @param  boolean $like   Escape wildcard chars as well?
     * @return string
     */
    protected function _escape($string, $like = false) {
        if($this->dbcon) {
            $string = mysql_real_escape_string($string, $this->dbcon);
        } else {
            $string = addslashes($string);
        }
        if($like) {
            $string = addcslashes($string, '%_');
        }
        return $string;
    }
    
    /**
     * Wrapper around msg() but outputs only when debug is enabled
     *
     * @param string $message
     * @param int    $err
     * @param int    $line
     * @param string $file
     * @return void
     */
    protected function _debug($message, $err, $line, $file) {
        if(!$this->getConf('debug')) return;
        msg($message, $err, $line, $file);
    }
}

// vim:ts=4:sw=4:et:
