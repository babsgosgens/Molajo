<?php
/**
 * @package     Molajo
 * @subpackage  Application
 * @copyright   Copyright (C) 2012 Amy Stephen. All rights reserved.
 * @license     GNU General Public License Version 2, or later http://www.gnu.org/licenses/gpl.html
 */
defined('MOLAJO') or die;

/**
 * Molajo Application Class
 *
 * Base class
 */
class MolajoApplication
{
    /**
     * @var    JInput  The application input object.
     * @since  11.3
     */
    public $input;

    /**
     * @var    string  Character encoding string.
     * @since  11.3
     */
    public $charSet = 'utf-8';

    /**
     * @var    string  Response mime type.
     * @since  11.3
     */
    public $mimeType = 'text/html';

    /**
     * @var    JDate  The body modified date for response headers.
     * @since  11.3
     */
    public $modifiedDate;

    /**
     * @var    JWebClient  The application client object.
     * @since  11.3
     */
    public $client;

    /**
     * @var    JRegistry  The application configuration object.
     * @since  11.3
     */
    protected $config;

    /**
     * @var    JDispatcher  The application dispatcher object.
     * @since  11.3
     */
    protected $dispatcher;

    /**
     * @var    JDocument  The application document object.
     * @since  11.3
     */
    protected $document;

    /**
     * @var    MolajoLanguage  The application language object.
     * @since  11.3
     */
    protected $language;

    /**
     * @var    Session  The application session object.
     * @since  11.3
     */
    protected $session;

    /**
     * @var    object  The application response object.
     * @since  11.3
     */
    protected $response;

    /**
     * @var    MolajoApplication  The application instance.
     * @since  11.3
     */
    protected static $instance;

    /**
     * Class constructor.
     *
     * @param   mixed  $input   An optional argument to provide dependency injection for the application's
     *                          input object.  If the argument is a JInput object that object will become
     *                          the application's input object, otherwise a default input object is created.
     * @param   mixed  $config  An optional argument to provide dependency injection for the application's
     *                          config object.  If the argument is a JRegistry object that object will become
     *                          the application's config object, otherwise a default config object is created.
     * @param   mixed  $client  An optional argument to provide dependency injection for the application's
     *                          client object.  If the argument is a JWebClient object that object will become
     *                          the application's client object, otherwise a default client object is created.
     *
     * @since   11.3
     */
    public function __construct(JInput $input = null, JRegistry $config = null, JWebClient $client = null)
    {
        if ($input instanceof JInput) {
            $this->input = $input;
        } else {
            $this->input = new JInput;
        }

        if ($config instanceof JRegistry) {
            $this->config = $config;
        } else {
            $this->config = new JRegistry;
        }

        if ($client instanceof JWebClient) {
            $this->client = $client;
        } else {
            $this->client = new JWebClient;
        }

        $this->getConfig();

        /** now */
        $this->set('execution.datetime', gmdate('Y-m-d H:i:s'));
        $this->set('execution.timestamp', time());

        /** response */
        $this->response = new stdClass;
        $this->response->cachable = false;
        $this->response->headers = array();
        $this->response->body = array();

        /** URI */
        $this->loadSystemUris();
    }

    /**
     * Returns a reference to the global MolajoApplication object, only creating it if it doesn't already exist.
     *
     * This method must be invoked as: $web = MolajoApplication::getInstance();
     *
     * @param   string  $name  The name (optional) of the MolajoApplication class to instantiate.
     *
     * @return  MolajoApplication
     *
     * @since   11.3
     */
    public static function getInstance($prefix = 'Molajo')
    {
        if (self::$instance) {
        } else {

            $info = MolajoApplicationHelper::getApplicationInfo(MOLAJO_APPLICATION, true);
            if ($info === false) {
                return false;
            }

            if (defined('MOLAJO_APPLICATION_PATH')) {
            } else {
                define('MOLAJO_APPLICATION_PATH', MOLAJO_APPLICATION_CORE . '/applications/' . $info->path);
            }

            if (defined('MOLAJO_APPLICATION_ID')) {
            } else {
                define('MOLAJO_APPLICATION_ID', $info->id);
            }

            MolajoApplicationHelper::loadApplicationClasses();

            $classname = $prefix . ucfirst(MOLAJO_APPLICATION) . 'Application';
            if (class_exists($classname)) {
                self::$instance = new $classname ();
            } else {
                return MolajoError::raiseError(500, MolajoTextHelper::sprintf('MOLAJO_APPLICATION_INSTANTIATION_ERROR', $classname));
            }
        }

        return self::$instance;
    }

    /**
     * Initialise the application.
     *
     * @param   mixed  $session     An optional argument to provide dependency injection for the application's
     *                              session object.  If the argument is a Session object that object will become
     *                              the application's session object, if it is false then there will be no session
     *                              object, and if it is null then the default session object will be created based
     *                              on the application's loadSession() method.
     * @param   mixed  $document    An optional argument to provide dependency injection for the application's
     *                              document object.  If the argument is a JDocument object that object will become
     *                              the application's document object, if it is false then there will be no document
     *                              object, and if it is null then the default document object will be created based
     *                              on the application's loadDocument() method.
     * @param   mixed  $language    An optional argument to provide dependency injection for the application's
     *                              language object.  If the argument is a MolajoLanguage object that object will become
     *                              the application's language object, if it is false then there will be no language
     *                              object, and if it is null then the default language object will be created based
     *                              on the application's loadLanguage() method.
     * @param   mixed  $dispatcher  An optional argument to provide dependency injection for the application's
     *                              event dispatcher.  If the argument is a JDispatcher object that object will become
     *                              the application's event dispatcher, if it is null then the default event dispatcher
     *                              will be created based on the application's loadDispatcher() method.
     *
     * @return  MolajoApplication  Instance of $this to allow chaining.
     *
     * @see     loadSession()
     * @see     loadDocument()
     * @see     loadLanguage()
     * @see     loadDispatcher()
     * @since   11.3
     */
    public function initialise($session = null, $document = null, $language = null, $dispatcher = null)
    {
        if ($session instanceof MolajoSession) {
            $this->session = $session;

        } elseif ($session === false) {

        } else {
            $this->loadSession();
        }

        if ($document instanceof JDocument) {
            $this->document = $document;

        } elseif ($document === false) {

        } else {
            $this->loadDocument();
        }

        if ($language instanceof MolajoLanguage) {
            $this->language = $language;

        } elseif ($language === false) {

        } else {
            $this->loadLanguage();
        }

        if ($dispatcher instanceof JDispatcher) {
            $this->dispatcher = $dispatcher;
        } else {
            $this->loadDispatcher();
        }

        return $this;
    }

    /**
     * Method to send the application response to the client.  All headers will be sent prior to the main
     * application output data.
     *
     * @return  void
     *
     * @since   11.3
     */
    public function respond()
    {
        $this->triggerEvent('onBeforeRespond');

        // If gzip compression is enabled in configuration and the server is compliant, compress the output.
        if ($this->get('gzip')
            && !ini_get('zlib.output_compression')
            && (ini_get('output_handler') != 'ob_gzhandler')) {
            $this->compress();
        }

        // Send the content-type header.
        $this->setHeader('Content-Type', $this->mimeType . '; charset=' . $this->charSet);

        // If the response is set to uncachable, we need to set some appropriate headers so browsers don't cache the response.
        if ($this->response->cachable) {
            $this->setHeader('Expires', gmdate('D, d M Y H:i:s', time() + 900) . ' GMT');
            // Last modified.
            if ($this->modifiedDate instanceof JDate) {
                $this->setHeader('Last-Modified', $this->modifiedDate->format('D, d M Y H:i:s'));
            }

        } else {
            // Expires in the past.
            $this->setHeader('Expires', 'Mon, 1 Jan 2001 00:00:00 GMT', true);
            // Always modified.
            $this->setHeader('Last-Modified', gmdate('D, d M Y H:i:s') . ' GMT', true);
            $this->setHeader('Cache-Control', 'no-store, no-cache, must-revalidate, post-check=0, pre-check=0', false);
            // HTTP 1.0
            $this->setHeader('Pragma', 'no-cache');

        }

        $this->sendHeaders();

        echo $this->getBody();

        $this->triggerEvent('onAfterRespond');
    }

    /**
     * Checks the accept encoding of the browser and compresses the data before
     * sending it to the client if possible.
     *
     * @return  void
     *
     * @since   11.3
     */
    protected function compress()
    {
        // Supported compression encodings.
        $supported = array(
            'x-gzip' => 'gz',
            'gzip' => 'gz',
            'deflate' => 'deflate'
        );

        // Get the supported encoding.
        $encodings = array_intersect($this->client->encodings, array_keys($supported));

        // If no supported encoding is detected do nothing and return.
        if (empty($encodings)) {
            return;
        }

        // Verify that headers have not yet been sent, and that our connection is still alive.
        if ($this->checkHeadersSent() || !$this->checkConnectionAlive()) {
            return;
        }

        // Iterate through the encodings and attempt to compress the data using any found supported encodings.
        foreach ($encodings as $encoding)
        {
            if (($supported[$encoding] == 'gz') || ($supported[$encoding] == 'deflate')) {
                // Verify that the server supports gzip compression before we attempt to gzip encode the data.
                // @codeCoverageIgnoreStart
                if (!extension_loaded('zlib') || ini_get('zlib.output_compression')) {
                    continue;
                }
                // @codeCoverageIgnoreEnd

                // Attempt to gzip encode the data with an optimal level 4.
                $data = $this->getBody();
                $gzdata = gzencode($data, 4, ($supported[$encoding] == 'gz') ? FORCE_GZIP : FORCE_DEFLATE);

                // If there was a problem encoding the data just try the next encoding scheme.
                // @codeCoverageIgnoreStart
                if ($gzdata === false) {
                    continue;
                }
                // @codeCoverageIgnoreEnd

                // Set the encoding headers.
                $this->setHeader('Content-Encoding', $encoding);
                $this->setHeader('X-Content-Encoded-By', 'Joomla');

                // Replace the output with the encoded data.
                $this->setBody($gzdata);

                // Compression complete, let's break out of the loop.
                break;
            }
        }
    }

    /**
     * Redirect to another URL.
     *
     * If the headers have not been sent the redirect will be accomplished using a "301 Moved Permanently"
     * or "303 See Other" code in the header pointing to the new location. If the headers have already been
     * sent this will be accomplished using a JavaScript statement.
     *
     * @param   string   $url    The URL to redirect to. Can only be http/https URL
     * @param   boolean  $moved  True if the page is 301 Permanently Moved, otherwise 303 See Other is assumed.
     *
     * @return  void
     *
     * @since   11.3
     */
    public function redirect($url, $moved = false)
    {
        // Import library dependencies.
        jimport('phputf8.utils.ascii');

        // Check for relative internal links.
        if (preg_match('#^index\.php#', $url)) {
            $url = $this->get('uri.base.full') . $url;
        }

        // Perform a basic sanity check to make sure we don't have any CRLF garbage.
        $url = preg_split("/[\r\n]/", $url);
        $url = $url[0];

        /*
           * Here we need to check and see if the URL is relative or absolute.  Essentially, do we need to
           * prepend the URL with our base URL for a proper redirect.  The rudimentary way we are looking
           * at this is to simply check whether or not the URL string has a valid scheme or not.
           */
        if (!preg_match('#^[a-z]+\://#i', $url)) {
            // Get a JURI instance for the requested URI.
            $uri = JURI::getInstance($this->get('uri.request'));

            // Get a base URL to prepend from the requested URI.
            $prefix = $uri->toString(array('scheme', 'user', 'pass', 'host', 'port'));

            // We just need the prefix since we have a path relative to the root.
            if ($url[0] == '/') {
                $url = $prefix . $url;
            }
            // It's relative to where we are now, so lets add that.
            else
            {
                $parts = explode('/', $uri->toString(array('path')));
                array_pop($parts);
                $path = implode('/', $parts) . '/';
                $url = $prefix . $path . $url;
            }
        }

        // If the headers have already been sent we need to send the redirect statement via JavaScript.
        if ($this->checkHeadersSent()) {
            echo "<script>document.location.href='$url';</script>\n";
        } else {

            // We have to use a JavaScript redirect here because MSIE doesn't play nice with utf-8 URLs.
            if (($this->client->engine == JWebClient::TRIDENT) && !utf8_is_ascii($url)) {
                $html = '<html><head>';
                $html .= '<meta http-equiv="content-type" content="text/html; charset=' . $this->charSet . '" />';
                $html .= '<script>document.location.href=\'' . $url . '\';</script>';
                $html .= '</head><body></body></html>';

                echo $html;
            }
            /*
                * For WebKit based browsers do not send a 303, as it causes subresource reloading.  You can view the
                * bug report at: https://bugs.webkit.org/show_bug.cgi?id=38690
                */
            elseif (!$moved and ($this->client->engine == JWebClient::WEBKIT))
            {
                $html = '<html><head>';
                $html .= '<meta http-equiv="refresh" content="0; url=' . $url . '" />';
                $html .= '<meta http-equiv="content-type" content="text/html; charset=' . $this->charSet . '" />';
                $html .= '</head><body></body></html>';

                echo $html;
            }
            else
            {
                // All other cases use the more efficient HTTP header for redirection.
                $this->header($moved ? 'HTTP/1.1 301 Moved Permanently' : 'HTTP/1.1 303 See other');
                $this->header('Location: ' . $url);
                $this->header('Content-Type: text/html; charset=' . $this->charSet);
            }
        }

        // Close the application after the redirect.
        $this->close();
    }

    /**
     * Exit the application.
     *
     * @param   integer  $code  The exit code (optional; default is 0).
     *
     * @return  void
     *
     * @codeCoverageIgnore
     * @since   11.3
     */
    public function close($code = 0)
    {
        exit($code);
    }

    /**
     * getConfig
     *
     * Creates the Application configuration object.
     *
     * return   object  A config object
     *
     * @since  1.0
     */
    public function getConfig()
    {
        $configClass = new MolajoConfiguration();
        $this->config = $configClass->getConfig();

        return $this->config;
    }

    /**
     * get
     *
     * Returns a property of the Application object
     * or the default value if the property is not set.
     *
     * @param   string  $key      The name of the property.
     * @param   mixed   $default  The default value (optional) if none is set.
     *
     * @return  mixed   The value of the configuration.
     *
     * @since   11.3
     */
    public function get($key, $default = null)
    {
        return $this->config->get($key, $default);
    }

    /**
     * set
     *
     * Modifies a property of the Application object, creating it if it does not already exist.
     *
     * @param   string  $key    The name of the property.
     * @param   mixed   $value  The value of the property to set (optional).
     *
     * @return  mixed   Previous value of the property
     *
     * @since   11.3
     */
    public function set($key, $value = null)
    {
        $this->config->set($key, $value);
    }

    /**
     * Registers a handler to a particular event group.
     *
     * @param   string    $event    The event name.
     * @param   callback  $handler  The handler, a function or an instance of a event object.
     *
     * @return  MolajoApplication  Instance of $this to allow chaining.
     *
     * @since   11.3
     */
    public function registerEvent($event, $handler)
    {
        if ($this->dispatcher instanceof JDispatcher) {
            $this->dispatcher->register($event, $handler);
        }

        return $this;
    }

    /**
     * Calls all handlers associated with an event group.
     *
     * @param   string  $event  The event name.
     * @param   array   $args   An array of arguments (optional).
     *
     * @return  array   An array of results from each function call, or null if no dispatcher is defined.
     *
     * @since   11.3
     */
    public function triggerEvent($event, array $args = null)
    {
        if ($this->dispatcher instanceof JDispatcher) {
            return $this->dispatcher->trigger($event, $args);
        }

        return null;
    }

    /**
     * Set/get cachable state for the response.  If $allow is set, sets the cachable state of the
     * response.  Always returns the current state.
     *
     * @param   boolean  $allow  True to allow browser caching.
     *
     * @return  boolean
     *
     * @since   11.3
     */
    public function allowCache($allow = null)
    {
        if ($allow !== null) {
            $this->response->cachable = (bool)$allow;
        }

        return $this->response->cachable;
    }

    /**
     * Method to set a response header.  If the replace flag is set then all headers
     * with the given name will be replaced by the new one.  The headers are stored
     * in an internal array to be sent when the site is sent to the browser.
     *
     * @param   string   $name     The name of the header to set.
     * @param   string   $value    The value of the header to set.
     * @param   boolean  $replace  True to replace any headers with the same name.
     *
     * @return  MolajoApplication  Instance of $this to allow chaining.
     *
     * @since   11.3
     */
    public function setHeader($name, $value, $replace = false)
    {
        // Sanitize the input values.
        $name = (string)$name;
        $value = (string)$value;

        // If the replace flag is set, unset all known headers with the given name.
        if ($replace) {
            foreach ($this->response->headers as $key => $header)
            {
                if ($name == $header['name']) {
                    unset($this->response->headers[$key]);
                }
            }

            // Clean up the array as unsetting nested arrays leaves some junk.
            $this->response->headers = array_values($this->response->headers);
        }

        // Add the header to the internal array.
        $this->response->headers[] = array('name' => $name, 'value' => $value);

        return $this;
    }

    /**
     * Method to get the array of response headers to be sent when the response is sent
     * to the client.
     *
     * @return  array
     *
     * @since   11.3
     */
    public function getHeaders()
    {
        return $this->response->headers;
    }

    /**
     * Method to clear any set response headers.
     *
     * @return  MolajoApplication  Instance of $this to allow chaining.
     *
     * @since   11.3
     */
    public function clearHeaders()
    {
        $this->response->headers = array();

        return $this;
    }

    /**
     * Send the response headers.
     *
     * @return  MolajoApplication  Instance of $this to allow chaining.
     *
     * @since   11.3
     */
    public function sendHeaders()
    {
        if (!$this->checkHeadersSent()) {
            foreach ($this->response->headers as $header)
            {
                if ('status' == strtolower($header['name'])) {
                    // 'status' headers indicate an HTTP status, and need to be handled slightly differently
                    $this->header(ucfirst(strtolower($header['name'])) . ': ' . $header['value'], null, (int)$header['value']);
                }
                else
                {
                    $this->header($header['name'] . ': ' . $header['value']);
                }
            }
        }

        return $this;
    }

    /**
     * Set body content.  If body content already defined, this will replace it.
     *
     * @param   string  $content  The content to set as the response body.
     *
     * @return  MolajoApplication  Instance of $this to allow chaining.
     *
     * @since   11.3
     */
    public function setBody($content)
    {
        $this->response->body = array((string)$content);

        return $this;
    }

    /**
     * Prepend content to the body content
     *
     * @param   string  $content  The content to prepend to the response body.
     *
     * @return  MolajoApplication  Instance of $this to allow chaining.
     *
     * @since   11.3
     */
    public function prependBody($content)
    {
        array_unshift($this->response->body, (string)$content);

        return $this;
    }

    /**
     * Append content to the body content
     *
     * @param   string  $content  The content to append to the response body.
     *
     * @return  MolajoApplication  Instance of $this to allow chaining.
     *
     * @since   11.3
     */
    public function appendBody($content)
    {
        array_push($this->response->body, (string)$content);

        return $this;
    }

    /**
     * Return the body content
     *
     * @param   boolean  $asArray  True to return the body as an array of strings.
     *
     * @return  mixed  The response body either as an array or concatenated string.
     *
     * @since   11.3
     */
    public function getBody($asArray = false)
    {
        if ($asArray === true) {
            return $this->response->body;
        } else {
            return implode('', $this->response->body);
        }
    }

    /**
     * Method to get the application document object.
     *
     * @return  JDocument  The document object
     *
     * @since   11.3
     */
    public function getDocument()
    {
        return $this->document;
    }

    /**
     * Method to get the application language object.
     *
     * @return  MolajoLanguage  The language object
     *
     * @since   11.3
     */
    public function getLanguage()
    {
        return $this->language;
    }

    /**
     * Method to get the application session object.
     *
     * @return  Session  The session object
     *
     * @since   11.3
     */
    public function getSession()
    {
        return $this->session;
    }

    /**
     * Method to check the current client connnection status to ensure that it is alive.  We are
     * wrapping this to isolate the connection_status() function from our code base for testing reasons.
     *
     * @return  boolean  True if the connection is valid and normal.
     *
     * @codeCoverageIgnore
     * @see     connection_status()
     * @since   11.3
     */
    protected function checkConnectionAlive()
    {
        return (connection_status() === CONNECTION_NORMAL);
    }

    /**
     * Method to check to see if headers have already been sent.  We are wrapping this to isolate the
     * headers_sent() function from our code base for testing reasons.
     *
     * @return  boolean  True if the headers have already been sent.
     *
     * @codeCoverageIgnore
     * @see     headers_sent()
     * @since   11.3
     */
    protected function checkHeadersSent()
    {
        return headers_sent();
    }

    /**
     * Method to send a header to the client.  We are wrapping this to isolate the header() function
     * from our code base for testing reasons.
     *
     * @param   string   $string   The header string.
     * @param   boolean  $replace  The optional replace parameter indicates whether the header should
     *                             replace a previous similar header, or add a second header of the same type.
     * @param   integer  $code     Forces the HTTP response code to the specified value. Note that
     *                             this parameter only has an effect if the string is not empty.
     *
     * @return  void
     *
     * @codeCoverageIgnore
     * @see     header()
     * @since   11.3
     */
    protected function header($string, $replace = true, $code = null)
    {
        header($string, $replace, $code);
    }

    /**
     * Method to create an event dispatcher for the Web application.  The logic and options for creating
     * this object are adequately generic for default cases but for many applications it will make sense
     * to override this method and create event dispatchers based on more specific needs.
     *
     * @return  void
     *
     * @since   11.3
     */
    protected function loadDispatcher()
    {
        $this->dispatcher = JDispatcher::getInstance();
    }

    /**
     * Method to create a document for the Web application.  The logic and options for creating this
     * object are adequately generic for default cases but for many applications it will make sense
     * to override this method and create document objects based on more specific needs.
     *
     * @return  void
     *
     * @since   11.3
     */
    protected function loadDocument()
    {
        $this->document = JFactory::getDocument();
    }

    /**
     * Method to create a language for the Web application.  The logic and options for creating this
     * object are adequately generic for default cases but for many applications it will make sense
     * to override this method and create language objects based on more specific needs.
     *
     * @return  void
     *
     * @since   11.3
     */
    protected function loadLanguage()
    {
        $this->language = JFactory::getLanguage();
    }

    /**
     * Method to create a session for the Web application.  The logic and options for creating this
     * object are adequately generic for default cases but for many applications it will make sense
     * to override this method and create session objects based on more specific needs.
     *
     * @return  void
     *
     * @since   11.3
     */
    protected function loadSession()
    {
        // Generate a session name.
        $name = md5($this->get('secret') . $this->get('session_name', get_class($this)));

        // Calculate the session lifetime.
        $lifetime = (($this->get('session_lifetime')) ? $this->get('session_lifetime') * 60 : 900);

        // Get the session handler from the configuration.
        $handler = $this->get('session_handler', 'none');

        // Initialize the options for Session.
        $options = array(
            'name' => $name,
            'expire' => $lifetime,
            'force_ssl' => $this->get('force_ssl')
        );

        // Instantiate the session object.
        $session = MolajoSession::getInstance($handler, $options);
        if ($session->getState() == 'expired') {
            $session->restart();
        }

        // If the session is new, load the user and registry objects.
        if ($session->isNew()) {
            $session->set('registry', new JRegistry);
            $session->set('user', new JUser);
        }

        // Set the session object.
        $this->session = $session;
    }

    /**
     * Method to load the system URI strings for the application.
     *
     * @param   string  $requestUri  An optional request URI to use instead of detecting one from the
     *                               server environment variables.
     *
     * @return  void
     *
     * @since   11.3
     */
    protected function loadSystemUris($requestUri = null)
    {
        /** Request URL */
        if (empty($requestUri)) {
            $this->set('uri.request', $this->detectRequestUri());
        } else {
            $this->set('uri.request', $requestUri);
        }

        /** siteUri */
        $siteUri = trim($this->get('site_uri'));
        if ($siteUri == '') {
            $uri = JUri::getInstance($this->get('uri.request'));
        } else {
            $uri = JUri::getInstance($siteUri);
        }

        /** Host */
        $host = $uri->toString(array('scheme', 'user', 'pass', 'host', 'port'));
        $path = rtrim($uri->toString(array('path')), '/\\');

        /** Base */
        $this->set('uri.base.full', $host . $path . '/');
        $this->set('uri.base.host', $host);
        $this->set('uri.base.path', $path . '/');

        /** Media */
        $mediaURI = trim($this->get('media_uri'));
        if ($mediaURI) {
            if (strpos($mediaURI, '://') == false) {
                $this->set('uri.media.full', $this->get('uri.base.host') . $mediaURI);
                $this->set('uri.media.path', $mediaURI);
            } else {
                $this->set('uri.media.full', $mediaURI);
                $this->set('uri.media.path', $mediaURI);
            }
        } else {
            $this->set('uri.media.full', $this->get('uri.base.full') . 'media/');
            $this->set('uri.media.path', $this->get('uri.base.path') . 'media/');
        }
//        echo '<pre>';
//        var_dump($this);
//        '</pre>';
    }

    /**
     * Method to detect the requested URI from server environment variables.
     *
     * @return  string  The requested URI
     *
     * @since   11.3
     */
    protected function detectRequestUri()
    {
        // Initialise variables.
        $uri = '';

        // First we need to detect the URI scheme.
        if (isset($_SERVER['HTTPS'])
            && !empty($_SERVER['HTTPS'])
            && (strtolower($_SERVER['HTTPS']) != 'off')) {
            $scheme = 'https://';
        } else {
            $scheme = 'http://';
        }

        /*
        * There are some differences in the way that Apache and IIS populate server environment variables.  To
        * properly detect the requested URI we need to adjust our algorithm based on whether or not we are getting
        * information from Apache or IIS.
        */

        // If PHP_SELF and REQUEST_URI are both populated then we will assume "Apache Mode".
        if (!empty($_SERVER['PHP_SELF']) && !empty($_SERVER['REQUEST_URI'])) {
            // The URI is built from the HTTP_HOST and REQUEST_URI environment variables in an Apache environment.
            $uri = $scheme . $_SERVER['HTTP_HOST'] . $_SERVER['REQUEST_URI'];

        // If not in "Apache Mode" we will assume that we are in an IIS environment and proceed.
        } else {

            // IIS uses the SCRIPT_NAME variable instead of a REQUEST_URI variable... thanks, MS
            $uri = $scheme . $_SERVER['HTTP_HOST'] . $_SERVER['SCRIPT_NAME'];

            // If the QUERY_STRING variable exists append it to the URI string.
            if (isset($_SERVER['QUERY_STRING']) && !empty($_SERVER['QUERY_STRING'])) {
                $uri .= '?' . $_SERVER['QUERY_STRING'];
            }
        }

        return trim($uri);
    }

    /**
     * getHash
     *
     * Provides a secure hash based on a seed
     *
     * @param   string   $seed  Seed string.
     *
     * @return  string   A secure hash
     *
     * @since  1.0
     */
    public static function getHash($seed)
    {
        return md5(self::get('secret') . $seed);
    }
}
