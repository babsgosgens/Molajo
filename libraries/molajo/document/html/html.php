<?php
/**
 * @package     Molajo
 * @subpackage  Document
 * @copyright   Copyright (C) 2005 - 2011 Open Source Matters, Inc. All rights reserved.
 * @copyright   Copyright (C) 2011 Amy Stephen. All rights reserved.
 * @license     GNU General Public License Version 2, or later http://www.gnu.org/licenses/gpl.html
 */
defined('MOLAJO') or die;

/**
 * DocumentHTML class, provides an easy interface to parse and display an HTML document
 *
 * @package     Joomla.Platform
 * @subpackage  Document
 * @since       11.1
 */
class MolajoDocumentHTML extends MolajoDocument
{
    /**
     * Array of Header <link> tags
     *
     * @var    array
     */
    public $_links = array();

    /**
     * Array of custom tags
     *
     * @var    array
     */
    public $_custom = array();

    public $template = null;
    public $baseurl = null;
    public $parameters = null;
    public $_file = null;

    /**
     * String holding parsed template
     */
    protected $_template = '';

    /**
     * Array of parsed template MolajoDoc tags
     */
    protected $_template_tags = array();

    /**
     * Integer with caching setting
     */
    protected $_caching = null;

    /**
     * Class constructor
     *
     * @param   array  $options Associative array of options
     */
    public function __construct($options = array())
    {
        parent::__construct($options);

        // Set document type
        $this->_type = 'html';

        // Set default mime type and document metadata (meta data syncs with mime type by default)
        $this->setMetaData('Content-Type', 'text/html', true);
        $this->setMetaData('robots', 'index, follow');
    }

    /**
     * Get the HTML document head data
     *
     * @return  array  The document head data in array form
     */
    public function getHeadData()
    {
        $data = array();
        $data['title'] = $this->title;
        $data['description'] = $this->description;
        $data['link'] = $this->link;
        $data['metaTags'] = $this->_metaTags;
        $data['links'] = $this->_links;
        $data['styleSheets'] = $this->_styleSheets;
        $data['style'] = $this->_style;
        $data['scripts'] = $this->_scripts;
        $data['script'] = $this->_script;
        $data['custom'] = $this->_custom;
        return $data;
    }

    /**
     * Set the HTML document head data
     *
     * @param   array  $data    The document head data in array form
     */
    public function setHeadData($data)
    {
        if (empty($data) || !is_array($data)) {
            return;
        }

        $this->title = (isset($data['title']) && !empty($data['title'])) ? $data['title'] : $this->title;
        $this->description = (isset($data['description']) && !empty($data['description'])) ? $data['description']
                : $this->description;
        $this->link = (isset($data['link']) && !empty($data['link'])) ? $data['link'] : $this->link;
        $this->_metaTags = (isset($data['metaTags']) && !empty($data['metaTags'])) ? $data['metaTags']
                : $this->_metaTags;
        $this->_links = (isset($data['links']) && !empty($data['links'])) ? $data['links'] : $this->_links;
        $this->_styleSheets = (isset($data['styleSheets']) && !empty($data['styleSheets'])) ? $data['styleSheets']
                : $this->_styleSheets;
        $this->_style = (isset($data['style']) && !empty($data['style'])) ? $data['style'] : $this->_style;
        $this->_scripts = (isset($data['scripts']) && !empty($data['scripts'])) ? $data['scripts'] : $this->_scripts;
        $this->_script = (isset($data['script']) && !empty($data['script'])) ? $data['script'] : $this->_script;
        $this->_custom = (isset($data['custom']) && !empty($data['custom'])) ? $data['custom'] : $this->_custom;
    }

    /**
     * Merge the HTML document head data
     *
     * @param   array  $data    The document head data in array form
     */
    public function mergeHeadData($data)
    {

        if (empty($data) || !is_array($data)) {
            return;
        }

        $this->title = (isset($data['title']) && !empty($data['title']) && !stristr($this->title, $data['title']))
                ? $this->title . $data['title'] : $this->title;
        $this->description = (isset($data['description']) && !empty($data['description']) && !stristr($this->description, $data['description']))
                ? $this->description . $data['description'] : $this->description;
        $this->link = (isset($data['link'])) ? $data['link'] : $this->link;

        if (isset($data['metaTags'])) {
            foreach ($data['metaTags'] AS $type1 => $data1)
            {
                $booldog = $type1 == 'http-equiv' ? true : false;
                foreach ($data1 AS $name2 => $data2)
                {
                    $this->setMetaData($name2, $data2, $booldog);
                }
            }
        }

        $this->_links = (isset($data['links']) && !empty($data['links']) && is_array($data['links']))
                ? array_unique(array_merge($this->_links, $data['links'])) : $this->_links;
        $this->_styleSheets = (isset($data['styleSheets']) && !empty($data['styleSheets']) && is_array($data['styleSheets']))
                ? array_merge($this->_styleSheets, $data['styleSheets']) : $this->_styleSheets;

        if (isset($data['style'])) {
            foreach ($data['style'] AS $type => $stdata)
            {
                if (!isset($this->_style[strtolower($type)]) || !stristr($this->_style[strtolower($type)], $stdata)) {
                    $this->addStyleDeclaration($stdata, $type);
                }
            }
        }

        $this->_scripts = (isset($data['scripts']) && !empty($data['scripts']) && is_array($data['scripts']))
                ? array_merge($this->_scripts, $data['scripts']) : $this->_scripts;


        if (isset($data['script'])) {
            foreach ($data['script'] AS $type => $sdata)
            {
                if (!isset($this->_script[strtolower($type)]) || !stristr($this->_script[strtolower($type)], $sdata)) {
                    $this->addScriptDeclaration($sdata, $type);
                }
            }
        }

        $this->_custom = (isset($data['custom']) && !empty($data['custom']) && is_array($data['custom']))
                ? array_unique(array_merge($this->_custom, $data['custom'])) : $this->_custom;
    }

    /**
     * Adds <link> tags to the head of the document
     *
     * $relType defaults to 'rel' as it is the most common relation type used.
     * ('rev' refers to reverse relation, 'rel' indicates normal, forward relation.)
     * Typical tag: <link href="index.php" rel="Start">
     *
     * @param   string  $href        The link that is being related.
     * @param   string  $relation    Relation of link.
     * @param   string  $relType    Relation type attribute.  Either rel or rev (default: 'rel').
     * @param   array   $attributes Associative array of remaining attributes.
     *
     * @return  void
     */
    public function addHeadLink($href, $relation, $relType = 'rel', $attribs = array())
    {
        $attribs = JArrayHelper::toString($attribs);
        $generatedTag = '<link href="' . $href . '" ' . $relType . '="' . $relation . '" ' . $attribs;
        $this->_links[] = $generatedTag;
    }

    /**
     * Adds a shortcut icon (favicon)
     *
     * This adds a link to the icon shown in the favorites list or on
     * the left of the url in the address bar. Some browsers display
     * it on the tab, as well.
     *
     * @param   string  $href        The link that is being related.
     * @param   string  $type        File type
     * @param   string  $relation    Relation of link
     */
    public function addFavicon($href, $type = 'image/vnd.microsoft.icon', $relation = 'shortcut icon')
    {
        $href = str_replace('\\', '/', $href);
        $this->_links[] = '<link href="' . $href . '" rel="' . $relation . '" type="' . $type . '"';
    }

    /**
     * Adds a custom HTML string to the head block
     *
     * @param   string  $html  The HTML to add to the head
     * @return  void
     */

    public function addCustomTag($html)
    {
        $this->_custom[] = trim($html);
    }

    /**
     * Get the contents of a document include
     *
     * @param   string  $type    The type of renderer
     * @param   string  $name    The name of the element to render
     * @param   array   $attribs Associative array of remaining attributes.
     *
     * @return  The output of the renderer
     */
    public function getBuffer($type = null, $name = null, $attribs = array())
    {
        // If no type is specified, return the whole buffer
        if ($type === null) {
            return parent::$_buffer;
        }

        $result = null;
        if (isset(parent::$_buffer[$type][$name])) {
            return parent::$_buffer[$type][$name];
        }

        // If the buffer has been explicitly turned off don't display or attempt to render
        if ($result === false) {
            return null;
        }

        $renderer = $this->loadRenderer($type);
        if ($this->_caching == true && $type == 'modules') {
            $cache = MolajoFactory::getCache('com_modules', '');
            $hash = md5(serialize(array($name, $attribs, $result, $renderer)));
            $cbuffer = $cache->get('cbuffer_' . $type);

            if (isset($cbuffer[$hash])) {
                return JCache::getWorkarounds($cbuffer[$hash], array('mergehead' => 1));
            } else {

                $options = array();
                $options['nopathway'] = 1;
                $options['nomodules'] = 1;
                $options['modulemode'] = 1;

                $this->setBuffer($renderer->render($name, $attribs, $result), $type, $name);

                $data = parent::$_buffer[$type][$name];

                $tmpdata = JCache::setWorkarounds($data, $options);

                $cbuffer[$hash] = $tmpdata;

                $cache->store($cbuffer, 'cbuffer_' . $type);
            }

        } else {
            $this->setBuffer($renderer->render($name, $attribs, $result), $type, $name);
        }

        return parent::$_buffer[$type][$name];
    }

    /**
     * Set the contents a document includes
     *
     * @param   string  $content    The content to be set in the buffer.
     * @param   array   $options    Array of optional elements.
     */
    public function setBuffer($content, $options = array())
    {
        // The following code is just for backward compatibility.
        if (func_num_args() > 1
            && !is_array($options)) {
            $args = func_get_args();
            $options = array();
            $options['type'] = $args[1];
            $options['name'] = (isset($args[2])) ? $args[2] : null;
        }

        parent::$_buffer[$options['type']][$options['name']] = $content;
    }

    /**
     * Parses the template and populates the buffer
     *
     * @param   array  $parameters  parameters for fetching the template
     */
    public function parse($parameters = array())
    {
        $this->_fetchTemplate($parameters);
        $this->_parseTemplate();
    }

    /**
     * Outputs the template to the browser.
     *
     * @param   boolean  $cache        If true, cache the output
     * @param   array    $parameters        Associative array of attributes
     * @return  The rendered data
     */
    public function render($caching = false, $parameters = array())
    {
        $this->_caching = $caching;

        if (empty($this->_template)) {
            $this->parse($parameters);
        }

        $data = $this->_renderTemplate();

        parent::render();
        return $data;
    }

    /**
     * Count the modules based on the given condition
     *
     * @param   string  $condition  The condition to use
     *
     * @return  integer  Number of modules found
     */
    public function countModules($condition)
    {
        $result = '';

        $operators = '(\+|\-|\*|\/|==|\!=|\<\>|\<|\>|\<=|\>=|and|or|xor)';
        $words = preg_split('# ' . $operators . ' #', $condition, null, PREG_SPLIT_DELIM_CAPTURE);
        for ($i = 0, $n = count($words); $i < $n; $i += 2)
        {
            // odd parts (modules)
            $name = strtolower($words[$i]);
            $words[$i] = ((isset(parent::$_buffer['modules'][$name])) && (parent::$_buffer['modules'][$name] === false))
                    ? 0 : count(MolajoModuleHelper::getModules($name));
        }

        $str = 'return ' . implode(' ', $words) . ';';

        return eval($str);
    }

    /**
     * Count the number of child menu items
     *
     * @return  integer  Number of child menu items
     */
    public function countMenuChildren()
    {
        static $children;

        if (!isset($children)) {
        } else {
            $dbo = MolajoFactory::getDbo();
            $app = MolajoFactory::getApplication();
            $menu = $app->getMenu();
            $where = Array();
            $active = $menu->getActive();
            /** todo: amy acl */
            if ($active) {
                $where[] = 'parent = ' . $active->id;
                $where[] = 'published = 1';
                $dbo->setQuery('SELECT COUNT(*) FROM #__menu_items WHERE ' . implode(' AND ', $where));
                $children = $dbo->loadResult();
            } else {
                $children = 0;
            }
        }

        return $children;
    }

    /**
     * Load a template file
     *
     * @param string    $template    The name of the template
     * @param string    $filename    The actual filename
     * @return string The contents of the template
     */
    protected function _loadTemplate($directory, $filename)
    {
        $contents = '';

        $directory = '/users/amystephen/sites/molajo/extensions/templates/molajito/';
        $filename = 'index.php';

        // Check to see if we have a valid template file
        if (file_exists($directory.'/'.$filename)) {
            // Store the file path
            $this->_file = $directory.'/'.$filename;

            //get the file content
            ob_start();
            require $directory.'/'.$filename;
            $contents = ob_get_contents();
            ob_end_clean();
        }

        // Try to find a favicon by checking the template and root folder
        $path = $directory.'/';
        $dirs = array($path, MOLAJO_BASE_FOLDER.'/');
        foreach ($dirs as $dir)
        {
            $icon = $dir . 'favicon.ico';
            if (file_exists($icon)) {
                $path = str_replace(MOLAJO_BASE_FOLDER.'/', '', $dir);
                $path = str_replace('\\', '/', $path);
                $this->addFavicon(JURI::base(true).'/'.$path . 'favicon.ico');
                break;
            }
        }

        return $contents;
    }

    /**
     * Fetch the template, and initialise the params
     *
     * @param   array  $parameters  parameters to determine the template
     */
    protected function _fetchTemplate($parameters = array())
    {
        $directory = isset($parameters['directory']) ? $parameters['directory'] : 'templates';
        $filter = JFilterInput::getInstance();
        $template = $filter->clean($parameters['template'], 'cmd');
        $file = $filter->clean($parameters['file'], 'cmd');

        if (file_exists($directory.'/'.$template.'/'.$file)) {
        } else {
            $template = 'system';
        }

        // Load the language file for the template
        $lang = MolajoFactory::getLanguage();
        // 1.5 or core then 1.6
        // todo: amy go thru all the language loads and make certain paths are simplified and correct
        $lang->load('tpl_' . $template, MOLAJO_BASE_FOLDER, null, false, false)
        || $lang->load('tpl_' . $template, $directory.'/'.$template, null, false, false)
        || $lang->load('tpl_' . $template, MOLAJO_BASE_FOLDER, $lang->getDefault(), false, false)
        || $lang->load('tpl_' . $template, $directory.'/'.$template, $lang->getDefault(), false, false);


        // Assign the variables
        $this->template = $template;
        $this->baseurl = JURI::base(true);
        $this->params = isset($parameters['parameters']) ? $parameters['parameters'] : new JRegistry;

        // Load
        $this->_template = $this->_loadTemplate($directory.'/'.$template, $file);
    }

    /**
     * Parse a document template
     *
     * @return  The parsed contents of the template
     */
    protected function _parseTemplate()
    {
        $matches = array();

        if (preg_match_all('#<jdoc:include\ type="([^"]+)" (.*)\/>#iU', $this->_template, $matches)) {
            $template_tags_first = array();
            $template_tags_last = array();

            // Step through the jdocs in reverse order.
            for ($i = count($matches[0]) - 1; $i >= 0; $i--) {
                $type = $matches[1][$i];
                $attribs = empty($matches[2][$i]) ? array() : MolajoUtility::parseAttributes($matches[2][$i]);
                $name = isset($attribs['name']) ? $attribs['name'] : null;

                // Separate buffers to be executed first and last
                if ($type == 'module' || $type == 'modules') {
                    $template_tags_first[$matches[0][$i]] = array('type' => $type, 'name' => $name, 'attribs' => $attribs);
                } else {
                    $template_tags_last[$matches[0][$i]] = array('type' => $type, 'name' => $name, 'attribs' => $attribs);
                }
            }
            // Reverse the last array so the jdocs are in forward order.
            $template_tags_last = array_reverse($template_tags_last);

            $this->_template_tags = $template_tags_first + $template_tags_last;
        }
    }

    /**
     * _renderTemplate
     *
     * Render pre-parsed template
     *
     * @return string rendered template
     */
    protected function _renderTemplate()
    {
        $replace = array();
        $with = array();

        foreach ($this->_template_tags AS $jdoc => $args) {
            $replace[] = $jdoc;
            $with[] = $this->getBuffer($args['type'], $args['name'], $args['attribs']);
        }

        return str_replace($replace, $with, $this->_template);
    }
}