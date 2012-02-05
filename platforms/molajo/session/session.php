<?php
/**
 * @package     Molajo
 * @subpackage  Session
 * @copyright   Copyright (C) 2012 Amy Stephen. All rights reserved.
 * @copyright   Copyright (C) 2005 - 2012 Open Source Matters, Inc. All rights reserved.
 * @license     GNU General Public License Version 2, or later http://www.gnu.org/licenses/gpl.html
 */
defined('MOLAJO') or die;

/**
 * Class for managing HTTP sessions
 *
 * Provides access to session-state values as well as session-level
 * settings and lifetime management methods.
 * Based on the standart PHP session handling mechanism it provides
 * more advanced features such as expire timeouts.
 *
 * @package     Joomla.Platform
 * @subpackage  Session
 * @since       11.1
 */
class MolajoSession extends JObject
{
    /**
     * Internal state
     * Values:  active|expired|destroyed|error
     *
     * @var     string $_state
     * @since   1.0
     */
    protected $_state = 'active';

    /**
     * Maximum age of unused session.
     *
     * @var     string
     * @since   1.0
     */
    protected $_expire = 15;

    /**
     * The session store object.
     *
     * @var     object
     * @since   1.0
     */
    protected $_store = null;

    /**
     * Security policy
     *
     * Default values:
     *  - fix_browser
     *  - fix_address
     *
     * @var array $_security list of checks that will be done
     */
    protected $_security = array('fix_browser');

    /**
     * Force cookies to be SSL only
     *
     * @default false
     * @var bool $force_ssl
     */
    protected $_force_ssl = false;

    /**
     * Constructor
     *
     * @param   string  $storage
     * @param   array   $options    optional parameters
     */
    public function __construct($store = 'none', $options = array())
    {
        /** Destroy session started with session.auto_start */
        if (session_id()) {
            session_unset();
            session_destroy();
        }

        /** php */
        ini_set('session.save_handler', 'files');
        ini_set('session.use_trans_sid', '0');

        $this->_store = MolajoSessionStorage::getInstance($store, $options);

        $this->_setOptions($options);

        $this->_setCookieParams();

        $this->_start();

        $this->_setCounter();

        $this->_setTimers();

        $this->_state = 'active';

        $this->_validate();
    }

    /**
     * Returns the global Session object, only creating it
     * if it doesn't already exist.
     *
     * @return  object  MolajoSession    The Session object.
     * @since   1.0
     */
    public static function getInstance($handler, $options)
    {
        static $instance;

        if (is_object($instance)) {
        } else {
            $instance = new MolajoSession($handler, $options);
        }

        return $instance;
    }

    /**
     * Get current state of session
     *
     * @return  string  The session state
     */
    public function getState()
    {
        return $this->_state;
    }

    /**
     * Get expiration time in minutes
     *
     * @return  integer  The session expiration time in minutes
     */
    public function getExpire()
    {
        return $this->_expire;
    }

    /**
     * Get a session token, if a token isn't set yet one will be generated.
     *
     * Tokens are used to secure forms from spamming attacks. Once a token
     * has been generated the system will check the post request to see if
     * it is present, if not it will invalidate the session.
     *
     * @param   boolean  If true, force a new token to be created
     *
     * @return  string    The session token
     */
    public function getToken($forceNew = false)
    {
        $token = $this->get('session.token');

        if ($token === null || $forceNew) {
            $token = $this->_createToken(12);
            $this->set('session.token', $token);
        }

        return $token;
    }

    /**
     * Method to determine if a token exists in the session. If not the
     * session will be set to expired
     *
     * @param   string    Hashed token to be verified
     * @param   boolean  If true, expires the session
     *
     * @return  boolean
     * @since   1.0
     */
    public function hasToken($tCheck, $forceExpire = true)
    {
        $tStored = $this->get('session.token');

        if (($tStored !== $tCheck)) {
            if ($forceExpire) {
                $this->_state = 'expired';
            }
            return false;
        }

        return true;
    }

    /**
     * Method to determine a hash for anti-spoofing variable names
     *
     * @return  string  Hashed var name
     * @since       11.1
     */
    public static function getFormToken($forceNew = false)
    {
        $user = Molajo::User();
        $session = Molajo::App()->getSession();
        $hash = Molajo::App()->getHash($user->get('id', 0) . $session->getToken($forceNew));

        return $hash;
    }

    /**
     * Get session name
     *
     * @return  string  The session name
     */
    public function getName()
    {
        if ($this->_state === 'destroyed') {
            // @TODO : raise error
            return null;
        }
        return session_name();
    }

    /**
     * Get session id
     *
     * @return  string  The session name
     */
    public function getId()
    {
        if ($this->_state === 'destroyed') {
            // @TODO : raise error
            return null;
        }
        return session_id();
    }

    /**
     * Get the session handlers
     *
     * @return  array  An array of available session handlers
     */
    public static function getStores()
    {
        $handlers = JFolder::files(__DIR__ . DS . 'storage', '.php$');

        $names = array();
        foreach ($handlers as $handler) {

            $name = substr($handler, 0, strrpos($handler, '.'));
            $class = 'MolajoSessionStorage' . ucfirst($name);

            //Load the class only if needed
            if (class_exists($class)) {
            } else {
                require_once __DIR__ . DS . 'storage' . DS . $name . '.php';
            }

            if (call_user_func_array(array(trim($class), 'test'), array())) {
                $names[] = $name;
            }
        }

        return $names;
    }

    /**
     * Check whether this session is currently created
     *
     * @return  boolean  True on success.
     */
    public function isNew()
    {
        $counter = $this->get('session.counter');
        if ($counter === 1) {
            return true;
        }
        return false;
    }

    /**
     * Get data from the session store
     *
     * @param   string  Name of a variable
     * @param   mixed   Default value of a variable if not set
     * @param   string  Namespace to use, default to 'default'
     *
     * @return  mixed    Value of a variable
     */
    public function get($name, $default = null, $namespace = 'default')
    {
        $namespace = '__' . $namespace; //add prefix to namespace to avoid collisions

        if ($this->_state !== 'active' && $this->_state !== 'expired') {
            // @TODO :: generated error here
            $error = null;
            return $error;
        }

        if (isset($_SESSION[$namespace][$name])) {
            return $_SESSION[$namespace][$name];
        }
        return $default;
    }

    /**
     * Set data into the session store.
     *
     * @param   string  Name of a variable.
     * @param   mixed   Value of a variable.
     * @param   string  Namespace to use, default to 'default'.
     *
     * @return  mixed    Old value of a variable.
     */
    public function set($name, $value = null, $namespace = 'default')
    {
        $namespace = '__' . $namespace; //add prefix to namespace to avoid collisions

        if ($this->_state !== 'active') {
            // @TODO :: generated error here
            return null;
        }

        $old = isset($_SESSION[$namespace][$name]) ? $_SESSION[$namespace][$name] : null;

        if (null === $value) {
            unset($_SESSION[$namespace][$name]);
        } else {
            $_SESSION[$namespace][$name] = $value;
        }

        return $old;
    }

    /**
     * Check whether data exists in the session store
     *
     * @param   string  Name of variable
     * @param   string  Namespace to use, default to 'default'
     * @return  boolean  True if the variable exists
     */
    public function has($name, $namespace = 'default')
    {
        $namespace = '__' . $namespace; //add prefix to namespace to avoid collisions

        if ($this->_state !== 'active') {
            // @TODO :: generated error here
            return null;
        }

        return isset($_SESSION[$namespace][$name]);
    }

    /**
     * Unset data from the session store
     *
     * @param  string  Name of variable
     * @param  string  Namespace to use, default to 'default'
     * @return  mixed  The value from session or NULL if not set
     */
    public function clear($name, $namespace = 'default')
    {
        // Add prefix to namespace to avoid collisions
        $namespace = '__' . $namespace;

        if ($this->_state !== 'active') {
            // @TODO :: generated error here
            return null;
        }

        $value = null;
        if (isset($_SESSION[$namespace][$name])) {
            $value = $_SESSION[$namespace][$name];
            unset($_SESSION[$namespace][$name]);
        }

        return $value;
    }

    /**
     * Start a session.
     *
     * Creates a session (or resumes the current one based on the state of the session)
     *
     * @return  boolean  true on success
     */
    protected function _start()
    {
        if ($this->_state == 'restart') {
            session_id($this->_createId());

        } else {
            $session_name = session_name();

            $input = Molajo::App()->getInput();
            $cookie = $input->get($session_name, false, 'COOKIE');

            if ($cookie === false) {
            } else {
                session_id($cookie);
                setcookie($cookie, '', time() - 3600);
            }
        }

        session_cache_limiter('none');
        session_start();

        return true;
    }

    /**
     * Session object destructor
     *
     * @since   1.0
     */
    public function __destruct()
    {
        $this->close();
    }

    /**
     * Frees all session variables and destroys all data registered to a session
     *
     * This method resets the $_SESSION variable and destroys all of the data associated
     * with the current session in its storage (file or DB). It forces new session to be
     * started after this method is called. It does not unset the session cookie.
     *
     * @return  boolean    true on success
     * @see    session_unset()
     * @see    session_destroy()
     */
    public function destroy()
    {
        // Session was already destroyed
        if ($this->_state === 'destroyed') {
            return true;
        }

        // In order to kill the session altogether, like to log the user out, the session id
        // must also be unset. If a cookie is used to propagate the session id (default behavior),
        // then the session cookie must be deleted.
        if (isset($_COOKIE[session_name()])) {
            $cookie_domain = Molajo::App()->get('cookie_domain', '');
            $cookie_path = Molajo::App()->get('cookie_path', '/');
            setcookie(session_name(), '', time() - 42000, $cookie_path, $cookie_domain);
        }

        session_unset();
        session_destroy();

        $this->_state = 'destroyed';
        return true;
    }

    /**
     * Restart an expired or locked session.
     *
     * @return  boolean  true on success
     * @see    destroy
     */
    public function restart()
    {
        $this->destroy();
        if ($this->_state !== 'destroyed') {
            // @TODO :: generated error here
            return false;
        }

        // Re-register the session handler after a session has been destroyed, to avoid PHP bug
        $this->_store->register();

        $this->_state = 'restart';
        //regenerate session id
        $id = $this->_createId(strlen($this->getId()));
        session_id($id);
        $this->_start();
        $this->_state = 'active';

        $this->_validate();
        $this->_setCounter();

        return true;
    }

    /**
     * Create a new session and copy variables from the old one
     *
     * @return boolean $result true on success
     */
    public function fork()
    {
        if ($this->_state !== 'active') {
            // @TODO :: generated error here
            return false;
        }

        // Save values
        $values = $_SESSION;

        // Keep session config
        $trans = ini_get('session.use_trans_sid');
        if ($trans) {
            ini_set('session.use_trans_sid', 0);
        }
        $cookie = session_get_cookie_params();

        // Create new session id
        $id = $this->_createId(strlen($this->getId()));

        // Kill session
        session_destroy();

        // Re-register the session store after a session has been destroyed, to avoid PHP bug
        $this->_store->register();

        // restore config
        ini_set('session.use_trans_sid', $trans);
        session_set_cookie_params($cookie['lifetime'], $cookie['path'], $cookie['domain'], $cookie['secure']);

        // restart session with new id
        session_id($id);
        session_start();

        return true;
    }

    /**
     * Writes session data and ends session
     *
     * Session data is usually stored after your script terminated without the need
     * to call MolajoSession::close(), but as session data is locked to prevent concurrent
     * writes only one script may operate on a session at any time. When using
     * framesets together with sessions you will experience the frames loading one
     * by one due to this locking. You can reduce the time needed to load all the
     * frames by ending the session as soon as all changes to session variables are
     * done.
     *
     * @see    session_write_close()
     */
    public function close()
    {
        session_write_close();
    }

    /**
     * Create a session id
     *
     * @return  string  Session ID
     */
    protected function _createId()
    {
        $id = 0;
        while (strlen($id) < 32) {
            $id .= mt_rand(0, mt_getrandmax());
        }

        return md5(uniqid($id, true));
    }

    /**
     * Set session cookie parameters
     */
    protected function _setCookieParams()
    {
        $cookie = session_get_cookie_params();

        if ($this->_force_ssl) {
            $cookie['secure'] = true;
        }

        if (Molajo::App()->get('cookie_domain', '') == '') {
        } else {
            $cookie['domain'] = Molajo::App()->get('cookie_domain');
        }

        if (Molajo::App()->get('cookie_path', '') == '') {
        } else {
            $cookie['path'] = Molajo::App()->get('cookie_path');
        }

        session_set_cookie_params($cookie['lifetime'],
                                    $cookie['path'],
                                    $cookie['domain'],
                                    $cookie['secure']);
    }

    /**
     * Create a token-string
     *
     * @param   integer  length of string
     *
     * @return  string  generated token
     */
    protected function _createToken($length = 32)
    {
        static $chars = '0123456789abcdef';
        $max = strlen($chars) - 1;
        $token = '';
        $name = session_name();
        for ($i = 0; $i < $length; ++$i) {
            $token .= $chars[(rand(0, $max))];
        }

        return md5($token . $name);
    }

    /**
     * Set counter of session usage
     *
     * @return  boolean  true on success
     */
    protected function _setCounter()
    {
        $counter = $this->get('session.counter', 0);
        ++$counter;

        $this->set('session.counter', $counter);

        return true;
    }

    /**
     * Set the session timers
     *
     * @return boolean $result true on success
     */
    protected function _setTimers()
    {
        if ($this->has('session.timer.start')) {
        } else {
            $start = time();

            $this->set('session.timer.start', $start);
            $this->set('session.timer.last', $start);
            $this->set('session.timer.now', $start);
        }

        $this->set('session.timer.last', $this->get('session.timer.now'));
        $this->set('session.timer.now', time());

        return true;
    }

    /**
     * Set additional session options
     *
     * @param   array  list of parameter
     *
     * @return  boolean  true on success
     */
    protected function _setOptions($options)
    {
        if (isset($options['name'])) {
            session_name(md5($options['name']));
        }
        if (isset($options['id'])) {
            session_id($options['id']);
        }
        if (isset($options['expire'])) {
            $this->_expire = $options['expire'];
        }
        if (isset($options['security'])) {
            $this->_security = explode(',', $options['security']);
        }
        if (isset($options['force_ssl'])) {
            $this->_force_ssl = (bool)$options['force_ssl'];
        }

        ini_set('session.gc_maxlifetime', $this->_expire);

        return true;
    }

    /**
     * Do some checks for security reason
     *
     * - timeout check (expire)
     * - ip-fixation
     * - browser-fixation
     *
     * If one check failed, session data has to be cleaned.
     *
     * @param   boolean  reactivate session
     *
     * @return  boolean  true on success
     * @see     http://shiflett.org/articles/the-truth-about-sessions
     */
    protected function _validate($restart = false)
    {
        if ($restart === true) {
            $this->_state = 'active';

            $this->set('session.client.address', null);
            $this->set('session.client.forwarded', null);
            $this->set('session.client.browser', null);
            $this->set('session.token', null);
        }

        if ($this->_expire === true) {
            $curTime = $this->get('session.timer.now', 0);
            $maxTime = $this->get('session.timer.last', 0) + $this->_expire;

            if ($maxTime < $curTime) {
                $this->_state = 'expired';
                return false;
            }
        }

        if (isset($_SERVER['HTTP_X_FORWARDED_FOR'])) {
            $this->set('session.client.forwarded', $_SERVER['HTTP_X_FORWARDED_FOR']);
        }

        if (in_array('fix_address', $this->_security)
            && isset($_SERVER['REMOTE_ADDR'])) {
            $ip = $this->get('session.client.address');

            if ($ip === null) {
                $this->set('session.client.address', $_SERVER['REMOTE_ADDR']);

            } else if ($_SERVER['REMOTE_ADDR'] == $ip) {
            } else {
                $this->_state = 'error';
                return false;
            }
        }

        if (in_array('fix_browser', $this->_security)
            && isset($_SERVER['HTTP_USER_AGENT'])) {
            $browser = $this->get('session.client.browser');

            if ($browser === null) {
                $this->set('session.client.browser', $_SERVER['HTTP_USER_AGENT']);

            } else if ($_SERVER['HTTP_USER_AGENT'] == $browser) {
            } else {
                /** todo: amy why where these two lines removed? */
                $this->_state	=	'error';
                return false;
            }
        }
        return true;
    }
}
