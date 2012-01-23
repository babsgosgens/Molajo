<?php
/**
 * @package     Molajo
 * @subpackage  Request
 * @copyright   Copyright (C) 2012 Amy Stephen. All rights reserved.
 * @license     GNU General Public License Version 2, or later http://www.gnu.org/licenses/gpl.html
 */
defined('MOLAJO') or die;

/**
 * MolajoRequest
 *
 * Processes the Request
 *
 * Base class
 */
class MolajoRequest
{
    /**
     *  Request
     *
     * @var object
     * @since 1.0
     */
    public $request;

    /**
     * __construct
     *
     * Class constructor.
     *
     * @param   null    $override_request_url
     * @param   null    $asset_id
     *
     * @return boolean
     *
     * @since  1.0
     */
    public function __construct($override_request_url = null, $asset_id = null)
    {
        /** Request Object: Passed to Document, Renderers and MVC */
        $this->_initializeRequest();

        /** Specific asset */
        if ((int)$asset_id == 0) {
            $this->request->set('request_asset_id', 0);
        } else {
            $this->request->set('request_asset_id', $asset_id);
        }

        /**
         * Specific URL path
         *  Request is stripped of Host, Folder, and Application
         *  Path ex. index.php?option=login or access/groups
         */
        if ($override_request_url == null) {
            $path = MOLAJO_PAGE_REQUEST;
        } else {
            $path = $override_request_url;
        }

        /** duplicate content: URLs without the .html */
        if ((int)$this->request->get('application_sef_suffix') == 1
            && substr($path, -11) == '/index.html'
        ) {
            $path = substr($path, 0, (strlen($path) - 11));
        }
        if ((int)$this->request->get('application_sef_suffix') == 1
            && substr($path, -5) == '.html'
        ) {
            $path = substr($path, 0, (strlen($path) - 5));
        }

        /** populate value used in query  */
        $this->request->set('request_url_query', $path);

        /** home: duplicate content - redirect */
        if ($this->request->get('request_url_query', '') == 'index.php'
            || $this->request->get('request_url_query', '') == 'index.php/'
            || $this->request->get('request_url_query', '') == 'index.php?'
            || $this->request->get('request_url_query', '') == '/index.php/'
        ) {
            MolajoController::getApplication()->redirect('', 301);
            return $this->request;
        }

        /** Home */
        if ($this->request->get('request_url_query', '') == ''
            && (int)$this->request->get('request_asset_id', 0) == 0
        ) {
            $this->request->set('request_asset_id', MolajoController::getApplication()->get('home_asset_id', 0));
            $this->request->set('request_url_home', true);
        }

        return;
    }

    /**
     * _initializeRequest
     *
     * Create and Initialize the request
     *
     * Request Object which is passed on to Document, Renderers and the MVC classes
     *
     * @static
     * @return array
     * @since 1.0
     */
    private function _initializeRequest()
    {
        $this->request = new JObject();

        /**  site information */
        $this->request->set('site_id', MOLAJO_SITE_ID);
        $this->request->set('site_name', MOLAJO_SITE_NAME);
        $this->request->set('site_asset_type_id', MOLAJO_ASSET_TYPE_BASE_SITE);
        $this->request->set('site_asset_id', (int)
        MolajoAssetHelper::getAssetID($this->request->get('site_asset_type_id'),
            MOLAJO_SITE_ID));

        /**  application information */
        $this->request->set('application_id', MOLAJO_APPLICATION_ID);
        $this->request->set('application_name', MolajoController::getApplication()->get('application_name', MOLAJO_APPLICATION));
        $this->request->set('application_asset_type_id', MOLAJO_ASSET_TYPE_BASE_APPLICATION);
        $this->request->set('application_asset_id', (int)
        MolajoAssetHelper::getAssetID($this->request->get('application_asset_type_id'),
            MOLAJO_APPLICATION_ID));

        $this->request->set('application_sef', MolajoController::getApplication()->get('sef', 1));
        $this->request->set('application_sef_rewrite', (int)MolajoController::getApplication()->get('sef_rewrite', 0));
        $this->request->set('application_sef_suffix', (int)MolajoController::getApplication()->get('sef_suffix', 0));
        $this->request->set('application_unicode_slugs', (int)MolajoController::getApplication()->get('unicode_slugs', 0));
        $this->request->set('application_force_ssl', (int)MolajoController::getApplication()->get('force_ssl', 0));

        $this->request->set('application_media_priority_site', (int)
        MolajoController::getApplication()->get('media_priority_site', 100));
        $this->request->set('application_media_priority_application', (int)
        MolajoController::getApplication()->get('media_priority_application', 200));
        $this->request->set('application_media_priority_user', (int)
        MolajoController::getApplication()->get('media_priority_user', 300));
        $this->request->set('application_media_priority_other_extension', (int)
        MolajoController::getApplication()->get('media_priority_other_extension', 400));
        $this->request->set('application_media_priority_request_extension', (int)
        MolajoController::getApplication()->get('media_priority_request_extension', 500));
        $this->request->set('application_media_priority_template', (int)
        MolajoController::getApplication()->get('media_priority_template', 600));
        $this->request->set('application_media_priority_primary_category', (int)
        MolajoController::getApplication()->get('media_priority_primary_category', 700));
        $this->request->set('application_media_priority_menu_item', (int)
        MolajoController::getApplication()->get('media_priority_menu_item', 800));
        $this->request->set('application_media_priority_source_data', (int)
        MolajoController::getApplication()->get('media_priority_source_data', 900));

        $this->request->set('application_default_template_id',
            MolajoController::getApplication()->get('default_template_id', 'system'));
        $this->request->set('application_default_page_id',
            MolajoController::getApplication()->get('default_page_id', 'default'));

        $this->request->set('application_default_static_view_id',
            MolajoController::getApplication()->get('default_static_view_id', 'dummy'));
        $this->request->set('application_default_static_wrap_id',
            MolajoController::getApplication()->get('default_static_wrap_id', 'none'));
        $this->request->set('application_default_items_view_id',
            MolajoController::getApplication()->get('default_items_view_id', 'items'));
        $this->request->set('application_default_items_wrap_id',
            MolajoController::getApplication()->get('default_items_wrap_id', 'div'));
        $this->request->set('application_default_item_view_id',
            MolajoController::getApplication()->get('default_item_view_id', 'item'));
        $this->request->set('application_default_item_wrap_id',
            MolajoController::getApplication()->get('default_item_wrap_id', 'div'));
        $this->request->set('application_default_edit_view_id',
            MolajoController::getApplication()->get('default_edit_view_id', 'edit'));
        $this->request->set('application_default_edit_wrap_id',
            MolajoController::getApplication()->get('default_edit_wrap_id', 'div'));

        /** user */
        $this->request->set('user_id', (int)MolajoController::getUser()->get('id'), 0);
        $this->request->set('user_username', MolajoController::getUser()->get('username'), 'guest');
        $this->request->set('user_custom_fields', MolajoController::getUser()->custom_fields);
        $this->request->set('user_metadata', MolajoController::getUser()->metadata);
        $this->request->set('user_parameters', MolajoController::getUser()->parameters);
        $this->request->set('user_asset_type_id', MOLAJO_ASSET_TYPE_USER);
        $this->request->set('user_asset_id', (int)
        MolajoAssetHelper::getAssetID($this->request->get('user_asset_type_id'),
            $this->request->get('user_id')));
        $this->request->set('user_view_groups', MolajoController::getUser()->view_groups);
        $this->request->set('user_guest', (boolean)MolajoController::getUser()->get('guest'), true);

        /** request */
        $this->request->set('request_url_base', MOLAJO_BASE_URL);
        $this->request->set('request_url_query', '');
        $this->request->set('request_url', '');
        $this->request->set('request_url_sef', '');
        $this->request->set('request_url_redirect_to_id', 0);
        $this->request->set('request_url_home', 0);

        $this->request->set('request_mvc_option', '');
        $this->request->set('request_mvc_model', '');
        $this->request->set('request_mvc_task', '');
        $this->request->set('request_mvc_controller', '');
        $this->request->set('request_mvc_id', 0);
        $this->request->set('request_mvc_category_id', 0);

        $this->request->set('request_extension_instance_id', '');
        $this->request->set('request_extension_instance_name', '');
        $this->request->set('request_custom_fields', array());
        $this->request->set('request_metadata', array());
        $this->request->set('request_parameters', array());
        $this->request->set('request_path', '');
        $this->request->set('request_type', '');
        $this->request->set('request_folder', '');
        $this->request->set('request_plugin_type', '');
        $this->request->set('request_asset_type_id', 0);
        $this->request->set('request_asset_id', 0);
        $this->request->set('request_view_group_id', 0);
        $this->request->set('request_suppress_no_results', false);

        /** menu item data */
        $this->request->set('menu_item_id', 0);
        $this->request->set('menu_item_title', '');
        $this->request->set('menu_item_custom_fields', array());
        $this->request->set('menu_item_parameters', array());
        $this->request->set('menu_item_metadata', array());
        $this->request->set('menu_item_asset_type_id', MOLAJO_ASSET_TYPE_MENU_ITEM_COMPONENT);
        $this->request->set('menu_item_asset_id', 0);
        $this->request->set('menu_item_view_group_id', 0);
        $this->request->set('menu_item_language', '');
        $this->request->set('menu_item_translation_of_id', 0);

        /** source data */
        $this->request->set('source_id', 0);
        $this->request->set('source_title', '');
        $this->request->set('source_custom_fields', array());
        $this->request->set('source_parameters', array());
        $this->request->set('source_metadata', array());
        $this->request->set('source_asset_type_id', 0);
        $this->request->set('source_asset_id', 0);
        $this->request->set('source_view_group_id', 0);
        $this->request->set('source_language', '');
        $this->request->set('source_translation_of_id', 0);
        $this->request->set('source_table', '');
        $this->request->set('source_last_modified', getDate());

        /** primary category */
        $this->request->set('category_id', 0);
        $this->request->set('category_title', '');
        $this->request->set('category_custom_fields', array());
        $this->request->set('category_parameters', array());
        $this->request->set('category_metadata', array());
        $this->request->set('category_asset_type_id', MOLAJO_ASSET_TYPE_CATEGORY_LIST);
        $this->request->set('category_asset_id', 0);
        $this->request->set('category_view_group_id', 0);
        $this->request->set('category_language', '');
        $this->request->set('category_translation_of_id', 0);

        /** merged */
        $this->request->set('parameters', array());

        $this->request->set('metadata_title', '');
        $this->request->set('metadata_description', '');
        $this->request->set('metadata_generator', MolajoController::getApplication()->get('generator', 'Molajo'));
        $this->request->set('metadata_keywords', '');
        $this->request->set('metadata_author', '');
        $this->request->set('metadata_content_rights', '');
        $this->request->set('metadata_robots', '');
        $this->request->set('metadata_additional_array', array());

        /** template */
        $this->request->set('template_id', 0);
        $this->request->set('template_name', '');
        $this->request->set('template_custom_fields', array());
        $this->request->set('template_metadata', array());
        $this->request->set('template_parameters', array());
        $this->request->set('template_asset_type_id', MOLAJO_ASSET_TYPE_EXTENSION_TEMPLATE);
        $this->request->set('template_asset_id', 0);
        $this->request->set('template_view_group_id', 0);
        $this->request->set('template_path', '');
        $this->request->set('template_path_url', '');
        $this->request->set('template_include', '');
        $this->request->set('template_favicon', '');

        /** page */
        $this->request->set('page_id', 0);
        $this->request->set('page_name', '');
        $this->request->set('page_custom_fields', array());
        $this->request->set('page_metadata', array());
        $this->request->set('page_parameters', array());
        $this->request->set('page_asset_type_id', MOLAJO_ASSET_TYPE_EXTENSION_TEMPLATE);
        $this->request->set('page_asset_id', 0);
        $this->request->set('page_path', '');
        $this->request->set('page_path_url', '');
        $this->request->set('page_include', '');

        /** above this line does not change */
        $this->request->set('data_above_does_not_change_for_page_load', 'no delta');
        /**  */
        $this->request->set('data_below_changes_for_each_extension_renderer', 'delta');
        /** below this line changes for each extension / renderer */

        /** status */
        $this->request->set('status_error', false);
        $this->request->set('status_authorised', false);
        $this->request->set('status_found', false);

        /** mvc parameters */
        $this->request->set('mvc_extension_instance_id', '');
        $this->request->set('mvc_extension_instance_name', '');
        $this->request->set('mvc_extension_path', '');
        $this->request->set('mvc_extension_type', '');
        $this->request->set('mvc_extension_folder', '');
        $this->request->set('mvc_custom_fields', array());
        $this->request->set('mvc_metadata', array());
        $this->request->set('mvc_parameters', array());
        $this->request->set('mvc_controller', '');
        $this->request->set('mvc_model', '');
        $this->request->set('mvc_plugin_type', '');
        $this->request->set('mvc_task', '');
        $this->request->set('mvc_id', 0);
        $this->request->set('mvc_category_id', 0);
        $this->request->set('mvc_asset_type_id', MOLAJO_ASSET_TYPE_MENU_ITEM_COMPONENT);
        $this->request->set('mvc_asset_id', 0);
        $this->request->set('mvc_view_group_id', 0);
        $this->request->set('mvc_suppress_no_results', false);

        /** view */
        $this->request->set('view_id', 0);
        $this->request->set('view_name', '');
        $this->request->set('view_css_id', '');
        $this->request->set('view_css_class', '');
        $this->request->set('view_type', 'extensions');
        $this->request->set('view_path', '');
        $this->request->set('view_path_url', '');
        $this->request->set('view_asset_type_id', MOLAJO_ASSET_TYPE_EXTENSION_VIEW);
        $this->request->set('view_asset_id', 0);
        $this->request->set('view_group_id', 0);

        /** wrap */
        $this->request->set('wrap_id', 0);
        $this->request->set('wrap_name', '');
        $this->request->set('wrap_css_id', '');
        $this->request->set('wrap_css_class', '');
        $this->request->set('wrap_path', '');
        $this->request->set('wrap_path_url', '');
        $this->request->set('wrap_asset_type_id', MOLAJO_ASSET_TYPE_EXTENSION_VIEW);
        $this->request->set('wrap_asset_id', 0);
        $this->request->set('wrap_view_group_id', 0);

        /** results */
        $this->request->set('results', '');

        return $this->request;
    }

    /**
     * process
     *
     * Using the MOLAJO_PAGE_REQUEST value,
     *  retrieve the asset record,
     *  set the variables needed to render output,
     *  execute document renders and MVC
     *
     * @return bool|null
     * @since  1.0
     */
    public function process()
    {
        if (MolajoController::getApplication()->get('offline', 0) == 1) {
            $this->request->set('status_error', true);
            $this->request->set('mvc_task', 'display');
            MolajoController::getApplication()->setHeader('Status', '503 Service Temporarily Unavailable', 'true');
            $message = MolajoController::getApplication()->get('offline_message', 'This site is not available.<br /> Please check back again soon.');
            MolajoController::getApplication()->setMessage($message, MOLAJO_MESSAGE_TYPE_WARNING . 503);
            $this->request->set('template_id', MolajoController::getApplication()->get('offline_template_id', 'system'));
            $this->request->set('page_id', MolajoController::getApplication()->get('offline_page_id', 'offline'));

        } else {

            /** Retrieve the Asset Record for the Request */
            $results = $this->_getAsset();

            if ((int)$this->request->get('menu_item_id') > 0
                && (int)$this->request->get('source_asset_id') > 0
            ) {
                // AMY //
            }
            $this->_routeRequest();

            $this->_authoriseTask();
        }

        /** Display Controller */
        if ($this->request->get('mvc_task') == 'add'
            || $this->request->get('mvc_task') == 'edit'
            || $this->request->get('mvc_task') == 'display'
        ) {
            return $this->_renderDocument();

            /** Action Controller */
        } else {
            return $this->_processTask();
        }

        /** Return to Application */
        return;
    }

    /**
     * _getAsset
     *
     * Retrieve Asset and Asset Type data for a specific asset id or query request
     *
     * @results  null
     * @since    1.0
     */
    protected function _getAsset()
    {
        $row = MolajoAssetHelper::getAsset($this->request->get('request_asset_id'),
            $this->request->get('request_url_query'));

        echo '<pre>';
        var_dump($row);
        echo '</pre>';

        /** Not found: exit */
        if (count($row) == 0) {
            return $this->request->set('status_found', false);
        }
        if ((int)$row->routable == 0) {
            return $this->request->set('status_found', false);
        }

        /** match found */
        $this->request->set('status_found', true);

        /** request url */
        $this->request->set('request_url', $row->request);
        $this->request->set('request_url_sef', $row->sef_request);
        $this->request->set('request_url_redirect_to_id', $row->redirect_to_id);

        /** home */
        if ((int)$this->request->get('request_asset_id', 0)
            == MolajoController::getApplication()->get('home_extension_id')
        ) {
            $this->request->set('request_url_home', true);
        } else {
            $this->request->set('request_url_home', false);
        }

        /** mvc options and url parameters */
        $this->request->set('request_mvc_option', $row->request_option);
        $this->request->set('request_mvc_model', $row->request_model);

        if ($row->asset_type_id == MOLAJO_ASSET_TYPE_MENU_ITEM_COMPONENT) {
            $this->request->set('request_mvc_id', 0);
        } else {
            $this->request->set('request_mvc_id', (int)$row->request_id);
        }

        $parameterArray = array();
        $temp = substr($this->request->get('request_url'),
            10, (strlen($this->request->get('request_url')) - 10));
        $parameterArray = explode('&', $temp);
        $url_parameters = array();

        if (count($parameterArray) > 0) {
            foreach ($parameterArray as $q) {

                $pair = explode('=', $q);

                if ($pair[0] == 'task') {
                    $this->request->set('request_mvc_task', $pair[1]);

                } elseif ($pair[0] == 'view') {
                    $this->request->set('view_id', $pair[1]);

                } elseif ($pair[0] == 'wrap') {
                    $this->request->set('wrap_id', $pair[1]);

                } elseif ($pair[0] == 'template') {
                    $this->request->set('template_id', $pair[1]);

                } elseif ($pair[0] == 'page') {
                    $this->request->set('page_id', $pair[1]);

                } elseif ($pair[0] == 'category') {
                    $this->request->set('mvc_category_id', $pair[1]);

                }

                $url_parameters[$pair[0]] = $pair[1];
            }
        }
        $this->request->set('mvc_url_parameters', $url_parameters);

        /** Menu Item: Aggregate Component */
        if ($row->asset_type_id == MOLAJO_ASSET_TYPE_MENU_ITEM_COMPONENT) {
            $this->request->set('menu_item_id', $row->source_id);
            $this->request->set('menu_item_asset_type_id', $row->asset_type_id);
            $this->request->set('menu_item_asset_id', $row->asset_id);
            $this->request->set('menu_item_view_group_id', $row->view_group_id);

            $this->_getMenuItem();

        } else {
            /** Source Data */
            $this->request->set('source_table', $row->source_table);
            $this->request->set('source_id', $row->source_id);
            $this->request->set('source_asset_type_id', $row->asset_type_id);
            $this->request->set('source_asset_id', $row->asset_id);
            $this->request->set('source_view_group_id', $row->view_group_id);

            $this->_getSource();
        }

        $this->request->set('request_asset_type_id', $row->asset_type_id);
        $this->request->set('request_asset_id', $row->asset_id);
        $this->request->set('request_view_group_id', $row->view_group_id);

        $this->_getPrimaryCategory();
        $this->_getExtension;

        return;
    }

    /**
     * _getMenuItem
     *
     * Retrieve the Menu Item Data
     *
     * @return  boolean
     * @since   1.0
     */
    protected function _getMenuItem()
    {
        $row = MolajoExtensionHelper::getMenuItem((int)$this->request->get('menu_item_id'));

        /** todo: amy 500 error */
        if (count($row) > 0) {
        } else {
            return;
        }

        /** todo: amy 500 error */
        /** Not found: exit */
        if (count($row) == 0) {
            return $this->request->set('status_found', false);
        }

        $this->request->set('menu_item_title', $row->menu_item_title);

        $parameters = new JRegistry;
        $parameters->loadString($row->menu_item_parameters);
        $this->request->set('menu_item_parameters', $parameters);

        $this->request->set('menu_item_custom_fields', $row->menu_item_custom_fields);
        $this->request->set('menu_item_metadata', $row->menu_item_metadata);
        $this->request->set('menu_item_language', $row->menu_item_language);
        $this->request->set('menu_item_translation_of_id', $row->menu_item_translation_of_id);

        $this->request->set('request_mvc_model', $parameters->def('model', ''));
        $this->request->set('request_mvc_task', $parameters->def('task', 'display'));
        $this->request->set('request_mvc_controller', $parameters->def('model', 'display'));
        $this->request->set('request_mvc_category_id', $parameters->def('category', 0));

        $this->request->set('request_extension_instance_id', $parameters->def('extension_instance_id', 0));

        $table = $parameters->def('extension_source_table', '__content');
        $this->request->set('request_extension_source_table', $table);

        $this->request->set('request_path', MOLAJO_EXTENSIONS_COMPONENTS . '/' . $this->request->get('extension_title'));
        $this->request->set('request_type', 'component');
        $this->request->set('request_folder', '');

        $this->request->set('request_suppress_no_results', $parameters->def('suppress_no_results', 0));

        $this->_setPageValues($this->request->get('menu_item_parameters',
            $this->request->get('menu_item_metadata')));

        //$this->request->set('request_extension_instance_id', '');
        $this->request->set('request_extension_instance_name', '');
        //$this->request->set('request_custom_fields', array());
        //$this->request->set('request_metadata', array());
        //$this->request->set('request_parameters', array());
        //$this->request->set('request_path', '');
        //$this->request->set('request_type', '');
        //$this->request->set('request_folder', '');
        $this->request->set('request_plugin_type', '');
        //$this->request->set('request_asset_type_id', 0);
        //$this->request->set('request_asset_id', 0);
        //$this->request->set('request_view_group_id', 0);
        //$this->request->set('request_suppress_no_results', false);     

        return;
    }

    /**
     * _getSource
     *
     * Retrieve Parameters and Metadata for Source Detail
     *
     * @return  bool
     * @since   1.0
     */
    protected function _getSource()
    {
        $db = MolajoController::getDbo();
        $query = $db->getQuery(true);

        $query->select('a.' . $db->nameQuote('extension_instance_id'));
        $query->select('a.' . $db->nameQuote('title'));
        $query->select('a.' . $db->nameQuote('custom_fields'));
        $query->select('a.' . $db->nameQuote('metadata'));
        $query->select('a.' . $db->nameQuote('parameters'));
        $query->select('a.' . $db->nameQuote('language'));
        $query->select('a.' . $db->nameQuote('translation_of_id'));
        $query->select('a.' . $db->nameQuote('modified_datetime'));

        $query->from($db->nameQuote('#' . $this->request->get('source_table')) . ' as a');
        $query->where('a.' . $db->nameQuote('id') . ' = ' . (int)$this->request->get('source_id'));

        $db->setQuery($query->__toString());
        $rows = $db->loadObjectList();

        /** todo: amy 500 error */
        if (count($rows) == 0) {
            return $this->request->set('status_found', false);
        } else {
            /** retrieve single row */
            foreach ($rows as $row)
            {
            }
        }

        $this->request->set('extension_instance_id', $row->extension_instance_id);
        $this->request->set('source_title', $row->title);
        $this->request->set('source_custom_fields', $row->custom_fields);
        $this->request->set('source_metadata', $row->metadata);

        $parameters = new JRegistry;
        $parameters->loadString($row->menu_item_parameters);
        $this->request->set('source_parameters', $parameters);

        $this->request->set('source_language', $row->language);
        $this->request->set('source_translation_of_id', $row->translation_of_id);
        $this->request->set('source_last_modified', $row->modified_datetime);

        if ($this->request->get('request_mvc_model', '') == '') {
            $this->request->set('request_mvc_model', $parameters->def('model', ''));
        }
        if ($this->request->get('request_mvc_task', '') == '') {
            $this->request->set('request_mvc_task', $parameters->def('task', ''));
        }
        if ($this->request->get('request_mvc_controller', '') == '') {
            $this->request->set('request_mvc_controller', $parameters->def('controller', ''));
        }
        if ($this->request->get('request_mvc_category_id', '') == '') {
            $this->request->set('request_mvc_category_id', $parameters->def('primary_category', ''));
        }

        $this->request->set('request_extension_instance_id', $row->extension_instance_id);
        $this->request->set('request_extension_source_table', $this->request->get('source_table'));
        $this->request->set('request_suppress_no_results', $parameters->def('suppress_no_results', 0));

        $this->_setPageValues($this->request->get('source_parameters',
            $this->request->get('source_metadata')));

        return;
    }

    /**
     * _getPrimaryCategory
     *
     * Retrieve the Menu Item Parameters and Meta Data
     *
     * @return  boolean
     * @since   1.0
     */
    protected function _getPrimaryCategory()
    {
        if ((int)$this->request->get('category_id', 0) == 0) {
            return;
        }

        $db = MolajoController::getDbo();
        $query = $db->getQuery(true);

        $query->select('a.' . $db->nameQuote('title'));
        $query->select('a.' . $db->nameQuote('parameters'));
        $query->select('a.' . $db->nameQuote('metadata'));
        $query->from($db->nameQuote('#__content') . ' as a');
        $query->where('a.' . $db->nameQuote('id') . ' = ' . (int)$this->request->get('category_id'));

        $db->setQuery($query->__toString());

        $row = $db->loadObjectList();
        if (count($row) > 0) {
        } else {
            return;
        }

        foreach ($row as $result) {
            $this->request->set('category_title', $result->title);
            $this->request->set('category_parameters', $result->parameters);
            $this->request->set('category_metadata', $result->metadata);
        }

        $this->_setPageValues($this->request->get('category_parameters',
            $this->request->get('category_metadata')));

        return;
    }

    /**
     * _getExtension
     *
     * Retrieve Component information using either the ID
     *
     * @return bool
     * @since 1.0
     */
    protected function _getExtension()
    {
        //todo: amy 500 error
        if ((int)$this->request->get('request_extension_instance_id') == 0) {
            return;
        }

        $row = MolajoExtensionHelper::get(MOLAJO_ASSET_TYPE_EXTENSION_COMPONENT, (int)$this->request->get('request_extension_instance_id'));
        //todo: amy 500 error
        if (count($row) > 0) {
        } else {
            return;
        }

        foreach ($row as $result) {
            $this->request->set('extension_name', $result->extension_name);
            $this->request->set('extension_title', $result->title);

            $parameters = new JRegistry;
            $parameters->loadString($result->parameters);
            $this->request->set('extension_parameters', $parameters);
            $this->request->set('extension_metadata', $result->metadata);
            $this->request->set('custom_fields', $result->metadata);

            if (isset($parameters->static)
                && $parameters->static === true
            ) {
                $this->request->set('mvc_model_no_data', true);
            } else {
                $this->request->set('mvc_model_no_data', false);
            }
            $this->request->set('extension_path', MOLAJO_EXTENSIONS_COMPONENTS . '/' . $this->request->set('extension_title'));
            $this->request->set('extension_type', 'component');
            $this->request->set('extension_folder', '');
        }


        $this->_setPageValues($this->request->get('extension_parameters',
            $this->request->get('extension_metadata')));

        return;
    }

    /**
     * _routeRequest
     *
     * Route the application.
     *
     * Routing is the process of examining the request environment to determine which
     * component should receive the request. The component optional parameters
     * are then set in the request object to be processed when the application is being
     * dispatched.
     *
     * @return  void;
     * @since  1.0
     */
    protected function _routeRequest()
    {
        //        MolajoPluginHelper::importPlugin('system');
        //        MolajoController::getApplication()->triggerEvent('onAfterRoute');

        /** 404 Not Found */
        if ($this->request->get('status_found') === false) {
            $this->request->set('status_error', true);
            $this->request->set('mvc_task', 'display');
            MolajoController::getApplication()->setHeader('Status', '404 Not Found', 'true');
            $message = MolajoController::getApplication()->get('error_404_message', 'Page not found.');
            MolajoController::getApplication()->setMessage($message, MOLAJO_MESSAGE_TYPE_ERROR, 404);
            $this->request->set('template_name', MolajoController::getApplication()->get('error_template_id', 'system'));
            $this->request->set('page_name', MolajoController::getApplication()->get('error_page_id', 'error'));
        }

        /** redirect_to_id */
        if ($this->request->get('request_url_redirect_to_id', 0) == 0) {
        } else {
            MolajoController::getApplication()->redirect(MolajoAssetHelper::getURL($this->request->get('request_url_redirect_to_id')), 301);
        }

        /** Must be Logged on Requirement */
        if (MolajoController::getApplication()->get('logon_requirement', 0) > 0
            && MolajoController::getUser()->get('guest', true) === true
            && $this->request->get('request_asset_id') <> MolajoController::getApplication()->get('logon_requirement', 0)
        ) {
            $this->request->set('status_error', true);
            $this->request->set('mvc_task', 'display');
            MolajoController::getApplication()->redirect(MolajoController::getApplication()->get('logon_requirement', 0), 303);
        }

        return;
    }

    /**
     * _authoriseTask
     *
     * Test user is authorised to view page
     *
     * @return  boolean
     * @since   1.0
     */
    protected function _authoriseTask()
    {
        if (in_array($this->request->get('asset_view_group_id'), $this->request->get('user_view_groups'))) {
            $this->request->set('status_authorised', true);
        } else {
            $this->request->set('status_authorised', false);
        }

        if ($this->request->get('status_authorised') === false) {
            $this->request->set('status_error', true);
            $this->request->set('mvc_task', 'display');
            MolajoController::getApplication()->setHeader('Status', '403 Not Authorised', 'true');
            $message = MolajoController::getApplication()->get('error_403_message', 'Not Authorised.');
            MolajoController::getApplication()->setMessage($message, MOLAJO_MESSAGE_TYPE_ERROR, 403);
            $this->request->set('template_name', MolajoController::getApplication()->get('error_template_id', 'system'));
            $this->request->set('page_name', MolajoController::getApplication()->get('error_page_id', 'error'));
        }
    }

    /**
     *  _renderDocument
     *
     *  Retrieves and sets parameter values in order of priority
     *  Then, execute Document Class (which executes Renderers and MVC Classes)
     *
     * @return void
     * @since  1.0
     */
    protected function _renderDocument()
    {
        if ($this->request->get('status_error') === true) {
        } else {
            $this->_getSource();
            $this->_getPrimaryCategory();
            $this->_getExtension();
            $this->request = MolajoExtensionHelper::getExtensionOptions($this->request);
        }

        $this->_getUserParameters();

        $this->_getApplicationDefaults();

        $this->_getTemplateParameters();

        $this->_mergeParameters();

        $this->_setRenderingPaths();

        /**
        $temp = (array)$this->request;
        echo '<pre>';
        var_dump($temp);
        echo '</pre>';
        die;
         **/
        /** Render Document */
        new MolajoDocument ($this->request);
        return $this->request;
    }

    /**
     *  _processTask
     *
     * @return
     * @since  1.0
     */
    protected function _processTask()
    {
    }

    /**
     * _setPageValues
     *
     * Set the values needed to generate the page (template, page, view, wrap, and various metadata)
     *
     * @param null $sourceParameters
     * @param null $sourceMetadata
     *
     * @return bool
     * @since 1.0
     */
    protected function _setPageValues($parameters = null, $metadata = null)
    {
        /** rendering parameters */
        $params = new JRegistry;
        $params->loadString($parameters);

        if ($this->request->get('template_name', '') == '') {
            $this->request->set('template_name', $params->def('template_name', ''));
        }

        if ($this->request->get('page_name', '') == '') {
            $this->request->set('page_name', $params->def('page_name', ''));
        }

        if ($this->request->get('view_name', '') == '') {
            $this->request->set('view_name', $params->def('view_name', ''));
        }

        if ($this->request->get('wrap_name', '') == '') {
            $this->request->set('wrap_name', $params->def('wrap_name', ''));
        }

        /** merge meta data */
        $meta = new JRegistry;
        $meta->loadString($metadata);

        if ($this->request->get('metadata_title', '') == '') {
            $this->request->set('metadata_title', $meta->def('metadata_title', ''));
        }
        if ($this->request->get('metadata_description', '') == '') {
            $this->request->set('metadata_description', $meta->def('metadata_description', ''));
        }
        if ($this->request->get('metadata_keywords', '') == '') {
            $this->request->set('metadata_keywords', $meta->def('metadata_keywords', ''));
        }
        if ($this->request->get('metadata_author', '') == '') {
            $this->request->set('metadata_author', $meta->def('metadata_author', ''));
        }
        if ($this->request->get('metadata_content_rights', '') == '') {
            $this->request->set('metadata_content_rights', $meta->def('metadata_content_rights', ''));
        }
        if ($this->request->get('metadata_robots', '') == '') {
            $this->request->set('metadata_robots', $meta->def('metadata_robots', ''));
        }

        return;
    }

    /**
     * _getUserParameters
     *
     * Get Template Name using either the Template ID or the Template Name
     *
     * @return bool
     * @since 1.0
     */
    protected function _getUserParameters()
    {
        $params = new JRegistry;
        $params->loadString($this->request->get('user_parameters'));

        $this->request->set('user_template_name', $params->def('template_name', ''));

        $this->request->set('user_page_name', $params->def('page_name', ''));

        if ($this->request->get('template_name', '') == '') {
            $this->request->set('template_name', $this->request->get('user_template_name'));
        }

        if ($this->request->get('page_name', '') == '') {
            $this->request->set('page_name', $this->request->get('user_page_name'));
        }

        return;
    }

    /**
     * _getTemplateParameters
     *
     * Get Template Name using either the Template ID or the Template Name
     *
     * @return bool
     * @since 1.0
     */
    protected function _getTemplateParameters()
    {
        if ((int)$this->request->set('template_id') == 0) {
            $template = $this->request->get('template_name');
        } else {
            $template = $this->request->get('template_id');
        }

        $row = MolajoTemplateHelper::getTemplate($template);

        if (count($row) > 0) {
            if ($this->request->get('template_name') == 'system') {
                // error
            } else {
                $this->request->set('template_name', 'system');
                $row = MolajoTemplateHelper::getTemplate('system');
                if (count($row) > 0) {
                    // error
                }
            }
        }

        foreach ($row as $result) {
            $parameters = new JRegistry;
            $parameters->loadString($result->parameters);
            $this->request->set('template_id', $result->extension_id);
            $this->request->set('template_name', $result->title);
            $this->request->set('template_parameters', $parameters);
            $this->request->set('template_asset_id', $result->extension_instance_asset_id);
        }

        if ($this->request->get('page_name', '') == '') {
            $this->request->set('page_name', $parameters->get('page', ''));
        }

        return;
    }

    /**
     *  _getApplicationDefaults
     *
     *  Retrieve Template and Page from the final level of default values, if needed
     *
     * @return bool
     * @since 1.0
     */
    protected function _getApplicationDefaults()
    {
        /** template/page */
        if ($this->request->get('template_name', '') == '') {
            $this->request->set('template_name', MolajoController::getApplication()->get('default_template_id', ''));
        }
        if ($this->request->get('page_name', '') == '') {
            $this->request->set('page_name', MolajoController::getApplication()->get('default_page_id', ''));
        }

        /** view */
        if ($this->request->get('view_name', '') == '') {

            if ($this->request->get('mvc_model_no_data', true)) {
                $this->request->set('view_name', MolajoController::getApplication()->get('default_static_view_id', ''));

            } else if ($this->request->get('mvc_task', '') == 'add'
                || $this->request->get('mvc_task', '') == 'edit'
            ) {
                $this->request->set('mvc_task', MolajoController::getApplication()->get('default_edit_view_id', ''));

            } else if ((int)$this->request->get('mvc_id', 0) == 0) {
                $this->request->set('view_name', MolajoController::getApplication()->get('default_items_view_id', ''));

            } else {
                $this->request->set('view_name', MolajoController::getApplication()->get('default_item_view_id', ''));
            }
        }

        /** wrap */
        if ($this->request->get('wrap_name', '') == '') {

            if ($this->request->get('mvc_model_no_data', false) === true) {
                $this->request->set('wrap_name', MolajoController::getApplication()->get('default_static_wrap_id', ''));

            } elseif ($this->request->get('mvc_task', '') == 'add'
                || $this->request->get('mvc_task', '') == 'edit'
            ) {
                $this->request->set('mvc_task', MolajoController::getApplication()->get('default_edit_wrap_id', ''));

            } else if ((int)$this->request->get('mvc_id', 0) == 0) {
                $this->request->set('wrap_name', MolajoController::getApplication()->get('default_items_wrap_id', ''));

            } else {
                $this->request->set('wrap_name', MolajoController::getApplication()->get('default_item_wrap_id', ''));
            }
        }

        /** metadata  */
        if ($this->request->get('metadata_title', '') == '') {
            $appname = MolajoController::getApplication()->get('application_name', '');
            $sitename = MolajoController::getApplication()->get('site_name', '');
            if (trim($appname) == trim($sitename)) {
                $temp = $appname;
            } else {
                $temp = $appname . ' - ' . $sitename;
            }
            $this->request->set('metadata_title', $temp);
        }

        if ($this->request->get('metadata_description', '') == '') {
            $this->request->set('metadata_description', MolajoController::getApplication()->get('metadata_description', ''));
        }

        if ($this->request->get('metadata_keywords', '') == '') {
            $this->request->set('metadata_keywords', MolajoController::getApplication()->get('metadata_keywords', ''));
        }

        if ($this->request->get('metadata_author', '') == '') {
            $this->request->set('metadata_author', MolajoController::getApplication()->get('metadata_author', ''));
        }

        if ($this->request->get('metadata_content_rights', '') == '') {
            $this->request->set('metadata_content_rights', MolajoController::getApplication()->get('metadata_content_rights', ''));
        }

        if ($this->request->get('metadata_robots', '') == '') {
            $this->request->set('metadata_robots', MolajoController::getApplication()->get('metadata_robots', ''));
        }
    }

    /**
     * getRedirectURL
     *
     * Function to retrieve asset information for the Request or Asset ID
     *
     * @return  string url
     * @since   1.0
     */
    public static function getRedirectURL($asset_id)
    {
        $db = MolajoController::getDbo();
        $query = $db->getQuery(true);

        if ((int)$asset_id == MolajoController::getApplication()->get('home_asset_id', 0)) {
            return '';
        }

        if (MolajoController::getApplication()->get('sef', 1) == 0) {
            $query->select('a.' . $db->nameQuote('sef_request'));
        } else {
            $query->select('a.' . $db->nameQuote('request'));
        }

        $query->from($db->nameQuote('#__assets') . ' as a');
        $query->where('a.' . $db->nameQuote('id') . ' = ' . (int)$asset_id);

        $db->setQuery($query->__toString());

        return $db->loadResult();
    }

    /**
     * _setQueryParameters
     *
     * Retrieve Parameter overrides from URL
     *
     * @return bool
     * @since 1.0
     */
    protected function _setQueryParameters()
    {
        //  todo: amy add parameter to turn this off in the template manager
        //  todo: amy filter input
        $parameterArray = array();
        $temp = substr(MOLAJO_PAGE_REQUEST, 10, (strlen(MOLAJO_PAGE_REQUEST) - 10));
        $parameterArray = explode('&', $temp);

        foreach ($parameterArray as $parameter) {

            $pair = explode('=', $parameter);

            if ($pair[0] == 'view') {
                $this->request->set('view_name', (string)$pair[1]);

            } elseif ($pair[0] == 'wrap') {
                $this->request->set('wrap_name', (string)$pair[1]);

            } elseif ($pair[0] == 'template') {
                $this->request->set('template_name', (string)$pair[1]);

            } elseif ($pair[0] == 'page') {
                $this->request->set('page_name', (string)$pair[1]);
            }
        }
        return true;
    }

    /**
     *  _mergeParameters
     */
    protected function _mergeParameters()
    {
        return;
        /** initialize */
        $temp = array();
        $parameters = array();

        /** load request (without parameter fields) */
        //        $temp = $this->request;
        //        $parameters = $this->_merge($parameters, $temp);

        /** source parameters */
        $temp = array();
        $temp = json_decode($this->request->get('source_parameters'));
        $parameters = $this->_merge($parameters, $temp);

        /** category parameters */
        $temp = array();
        $temp = json_decode($this->request->get('category_parameters'));
        $parameters = $this->_merge($parameters, $temp);

        /** extension parameters */
        $temp = array();
        $temp = json_decode($this->request->get('extension_parameters'));

        $this->parameters = $this->_merge($parameters, $temp);

        echo '<pre>';
        var_dump($this->parameters);
        '</pre>';
        die();
    }

    /**
     *  _merge
     */
    protected function _merge($parameters, $temp)
    {
        if (count($temp) == 0) {
            return $parameters;
        }
        foreach ($temp as $name => $value) {
            if (strpos($name, 'parameter')) {
            } else {
                if (isset($parameters->$name)) {
                    if (trim($parameters->$name) == '') {
                        $parameters->$name = $value;
                    }
                } else {
                    $parameters->$name = $value;
                }
            }
        }
        return $parameters;
    }


    /**
     * _setRenderingPaths
     *
     * Set paths for Template, page, view, and wrap
     *
     * @return mixed
     */
    protected function _setRenderingPaths()
    {
        if ($this->request->get('status_error') === true) {
        } else {
            $this->request->set('view_type', 'extensions');
            $viewHelper = new MolajoViewHelper($this->request->get('view_name'),
                $this->request->get('view_type'),
                $this->request->get('extension_title'),
                $this->request->get('extension_type'),
                ' ',
                $this->request->get('template_name'));
            $this->request->set('view_path', $viewHelper->view_path);
            $this->request->set('view_path_url', $viewHelper->view_path_url);
        }

        if ($this->request->get('status_error') === true) {
        } else {
            $wrapHelper = new MolajoViewHelper($this->request->get('wrap_name'),
                'wraps',
                $this->request->get('extension_title'),
                $this->request->get('extension_type'),
                ' ',
                $this->request->get('template_name'));
            $this->request->set('wrap_path', $wrapHelper->view_path);
            $this->request->set('wrap_path_url', $wrapHelper->view_path_url);
        }

        /** Template Path */
        $path = MolajoTemplateHelper::getPath($this->request->get('template_name'));
        $this->request->set('template_path', $path);

        /** Page Path */
        $pageHelper = new MolajoViewHelper($this->request->get('page_name'),
            'pages',
            $this->request->get('extension_title'),
            $this->request->get('extension_type'),
            ' ',
            $this->request->get('template_name'));
        $this->request->set('page_path', $pageHelper->view_path);
        $this->request->set('page_path_url', $pageHelper->view_path_url);

        return;
    }
}
