<?php
/**
 * @package     Molajo
 * @subpackage  Application
 * @copyright   Copyright (C) 2005 - 2012 Open Source Matters, Inc. All rights reserved.
 * @copyright   Copyright (C) 2012 Individual Molajo Contributors. All rights reserved.
 * @license     GNU General Public License Version 2, or later http://www.gnu.org/licenses/gpl.html
 */
defined('MOLAJO') or die;

/**
 * Collection Update Adapter Class
 *
 * @package     Joomla.Platform
 * @subpackage  Updater
 * @since       11.1
 * */

class MolajoUpdaterCollection extends MolajoUpdateAdapter
{
    /**
     * Root of the tree
     *
     * @var    object
     * @since  11.1
     */
    protected $base;

    /**
     * Tree of objects
     *
     * @var    array
     * @since  11.1
     */
    protected $parent = array(0);

    /**
     * Used to control if an item has a child or not
     *
     * @var    boolean
     * @since  11.1
     */
    protected $pop_parent = 0;

    /**
     * @var array A list of discovered update sites
     */
    protected $extension_sites;

    /**
     * A list of discovered updates
     *
     * @var array
     */
    protected $updates;

    /**
     * Gets the reference to the current direct parent
     *
     * @return  object
     *
     * @since   1.0
     */
    protected function _getStackLocation()
    {

        return implode('->', $this->_stack);
    }

    /**
     * Get the parent tag
     *
     * @return  string   parent
     *
     * @since   1.0
     */
    protected function _getParent()
    {
        return end($this->parent);
    }

    /**
     * Opening an XML element
     *
     * @param   object  $parser  Parser object
     * @param   string  $name    Name of element that is opened
     * @param   array   $attrs   Array of attributes for the element
     *
     * @return  void
     *
     * @since   1.0
     */
    public function _startElement($parser, $name, $attrs = array())
    {
        array_push($this->_stack, $name);
        $tag = $this->_getStackLocation();
        // Reset the data
        eval('$this->' . $tag . '->_data = "";');
        switch ($name)
        {
            case 'CATEGORY':
                if (isset($attrs['REF'])) {
                    $this->extension_sites[] = array('type' => 'collection', 'location' => $attrs['REF'], 'extension_site_id' => $this->_extension_site_id);
                }
                else
                {
                    // This item will have children, so prepare to attach them
                    $this->pop_parent = 1;
                }
                break;
            case 'EXTENSION':
                $update = MolajoModel::getInstance('update');
                $update->set('extension_site_id', $this->_extension_site_id);
                foreach ($this->_updatecols as $col)
                {
                    // Reset the values if it doesn't exist
                    if (!array_key_exists($col, $attrs)) {
                        $attrs[$col] = '';
                        if ($col == 'CLIENT_ID') {
                            $attrs[$col] = 'site';
                        }
                    }
                }
                $client = MolajoApplicationHelper::getApplicationInfo($attrs['CLIENT_ID'], 1);
                $attrs['CLIENT_ID'] = $client->id;
                // Lower case all of the fields
                foreach ($attrs as $key => $attr)
                {
                    $values[strtolower($key)] = $attr;
                }

                // Only add the update if it is on the same platform and release as we are
                $ver = new JVersion;
                $product = strtolower(JFilterInput::getInstance()->clean($ver->PRODUCT, 'cmd')); // lower case and remove the exclamation mark
                // Set defaults, the extension file should clarify in case but it may be only available in one version
                // This allows an update site to specify a targetplatform
                // targetplatformversion can be a regexp, so 1.[56] would be valid for an extension that supports 1.5 and 1.6
                // Note: Whilst the version is a regexp here, the targetplatform is not (new extension per platform)
                //		Additionally, the version is a regexp here and it may also be in an extension file if the extension is
                //		compatible against multiple versions of the same platform (e.g. a library)
                if (!isset($values['targetplatform'])) {
                    $values['targetplatform'] = $product;
                }
                // set this to ourself as a default
                if (!isset($values['targetplatformversion'])) {
                    $values['targetplatformversion'] = $ver->RELEASE;
                }
                // set this to ourself as a default
                // validate that we can install the extension
                if ($product == $values['targetplatform'] && preg_match('/' . $values['targetplatformversion'] . '/', $ver->RELEASE)) {
                    $update->bind($values);
                    $this->updates[] = $update;
                }
                break;
        }
    }

    /**
     * Closing an XML element
     * Note: This is a protected function though has to be exposed externally as a callback
     *
     * @param   object  $parser  Parser object
     * @param   string  $name    Name of the element closing
     *
     * @return  void
     *
     * @since   1.0
     */
    protected function _endElement($parser, $name)
    {
        $lastcell = array_pop($this->_stack);
        switch ($name)
        {
            case 'CATEGORY':
                if ($this->pop_parent) {
                    $this->pop_parent = 0;
                    array_pop($this->parent);
                }
                break;
        }
    }

    // Note: we don't care about char data in collection because there should be none

    /**
     * Finds an update
     *
     * @param   array  $options  Options to use: extension_site_id: the unique ID of the update site to look at
     *
     * @return  array  Update_sites and updates discovered
     *
     * @since   1.0
     */
    public function findUpdate($options)
    {
        $url = $options['location'];
        $this->_extension_site_id = $options['extension_site_id'];
        if (substr($url, -4) != '.xml') {
            if (substr($url, -1) != '/') {
                $url .= '/';
            }
            $url .= 'update.xml';
        }

        $this->base = new stdClass;
        $this->extension_sites = array();
        $this->updates = array();
        $dbo = $this->parent->getDbo();

        if (!($fp = @fopen($url, "r"))) {
            $query = $dbo->getQuery(true);
            $query->update('#__extension_sites');
            $query->set('enabled = 0');
            $query->where('extension_site_id = ' . $this->_extension_site_id);
            $dbo->setQuery($query->__toString());
            $dbo->Query();

            JLog::add("Error parsing url: " . $url, JLog::WARNING, 'updater');

            Molajo::App()->setMessage(MolajoTextHelper::sprintf('JLIB_UPDATER_ERROR_COLLECTION_OPEN_URL', $url), 'warning');
            return false;
        }

        $this->xml_parser = xml_parser_create('');
        xml_set_object($this->xml_parser, $this);
        xml_set_element_handler($this->xml_parser, '_startElement', '_endElement');

        while ($data = fread($fp, 8192))
        {
            if (!xml_parse($this->xml_parser, $data, feof($fp))) {
                JLog::add("Error parsing url: " . $url, JLog::WARNING, 'updater');

                Molajo::App()->setMessage(MolajoTextHelper::sprintf('JLIB_UPDATER_ERROR_COLLECTION_PARSE_URL', $url), 'warning');
                return false;
            }
        }
        // TODO: Decrement the bad counter if non-zero
        return array('extension_sites' => $this->extension_sites, 'updates' => $this->updates);
    }
}
