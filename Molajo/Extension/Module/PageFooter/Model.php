<?php
/**
 * @package     Molajo
 * @subpackage  Module
 * @copyright   Copyright (C) 2012 Amy Stephen. All rights reserved.
 * @license     GNU General Public License Version 2, or later http://www.gnu.org/licenses/gpl.html
 */
namespace Molajo\Application\MVC\Model;
namespace Molajo\Extension\Module;

defined('MOLAJO') or die;

/**
 * Footer
 *
 * @package     Molajo
 * @subpackage  Model
 * @since       1.0
 */
Class ModulePagefooterModel extends DisplayModel
{
    /**
     * __construct
     *
     * Constructor.
     *
     * @param  $config
     * @since  1.0
     */
    public function __construct($id = null)
    {
        $this->name = get_class($this);
        $this->table = '';
        $this->primary_key = '';

        return parent::__construct($id);
    }

    /**
     * getData
     *
     * @return    array    An empty array
     *
     * @since    1.0
     */
    public function getData()
    {
        $this->items = array();

        $date = Service::Date()
            ->getDate()
            ->format('Y-m-d-H-i-s');

        $row = new \stdClass();

        $row->current_year = Service::Date()
            ->getDate()
            ->format('Y');

        $row->site_name = Service::Configuration()
            ->get('site_name', 'Molajo');

        $row->link = 'http://molajo.org/';

        $row->linked_text = 'Molajo' . '&reg;';

        $row->remaining_text = ' ' . Service::Language()
            ->_('MOLAJO_IS_FREE_SOFTWARE');

        $row->version = Service::Language()
            ->_(MOLAJOVERSION);

        /** save recordset */
        $this->items[] = $row;

        return $this->items;
    }
}
