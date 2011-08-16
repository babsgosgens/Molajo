<?php
/**
 * @version		$Id: languages.php 21320 2011-05-11 01:01:37Z dextercowley $
 * @copyright	Copyright (C) 2005 - 2011 Open Source Matters, Inc. All rights reserved.
 * @license		GNU General Public License version 2 or later; see LICENSE.txt
 */

// Check to ensure this file is included in Molajo
defined('_JEXEC') or die;

jimport('joomla.application.component.modellist');

/**
 * Languages Model Class
 *
 * @package		Joomla.Administrator
 * @subpackage	com_languages
 * * * @since		1.0
 */
class LanguagesModelLanguages extends JModelList
{
	/**
	 * Constructor.
	 *
	 * @param	array	An optional associative array of configuration settings.
	 * @see		JController
	 * @since	1.0
	 */
	public function __construct($config = array())
	{
		if (empty($config['filter_fields'])) {
			$config['filter_fields'] = array(
				'lang_id', 'a.lang_id',
				'lang_code', 'a.lang_code',
				'title', 'a.title',
				'title_native', 'a.title_native',
				'sef', 'a.sef',
				'image', 'a.image',
				'published', 'a.published',
				'home','l.home',
			);
		}

		parent::__construct($config);
	}

	/**
	 * Method to auto-populate the model state.
	 *
	 * Note. Calling getState in this method will result in recursion.
	 *
	 * @return	void
	 * @since	1.0
	 */
	protected function populateState($ordering = null, $direction = null)
	{
		// Initialise variables.
		$app = MolajoFactory::getApplication('administrator');

		// Load the filter state.
		$search = $this->getUserStateFromRequest($this->context.'.search', 'filter_search');
		$this->setState('filter.search', $search);

		$published = $this->getUserStateFromRequest($this->context.'.published', 'filter_published', '');
		$this->setState('filter.published', $published);

		// Load the parameters.
		$params = JComponentHelper::getParams('com_languages');
		$this->setState('params', $params);

		// List state information.
		parent::populateState('a.title', 'asc');
	}

	/**
	 * Method to get a store id based on model configuration state.
	 *
	 * This is necessary because the model is used by the component and
	 * different modules that might need different sets of data or different
	 * ordering requirements.
	 *
	 * @param	string		$id	A prefix for the store id.
	 *
	 * @return	string		A store id.
	 * @since	1.0
	 */
	protected function getStoreId($id = '')
	{
		// Compile the store id.
		$id	.= ':'.$this->getState('filter.search');
		$id	.= ':'.$this->getState('filter.published');

		return parent::getStoreId($id);
	}

	/**
	 * Method to build an SQL query to load the list data.
	 *
	 * @return	string	An SQL query
	 * @since	1.0
	 */
	protected function getListQuery()
	{
		// Create a new query object.
		$db = $this->getDbo();
		$query = $db->getQuery(true);

		// Select all fields from the languages table.
		$query->select($this->getState('list.select', 'a.*', 'l.home'));
		$query->from('`#__languages` AS a');

		// Select the language home pages
		$query->select('l.home AS home');
		$query->join('LEFT', '`#__menu_items`  AS l  ON  l.language = a.lang_code AND l.home=1  AND l.language <> \'*\'' );

		// Filter on the published state.
		$published = $this->getState('filter.published');
		if (is_numeric($published)) {
			$query->where('a.published = '.(int) $published);
		}
		else if ($published === '') {
			$query->where('(a.published IN (0, 1))');
		}

		// Filter by search in title
		$search = $this->getState('filter.search');
		if (!empty($search)) {
			$search = $db->Quote('%'.$db->getEscaped($search, true).'%', false);
			$query->where('(a.title LIKE '.$search.')');
		}

		// Add the list ordering clause.
		$query->order($db->getEscaped($this->getState('list.ordering', 'a.ordering')).' '.$db->getEscaped($this->getState('list.direction', 'ASC')));

		return $query;
	}

	/**
	 * Set the published language(s)
	 *
	 * @param	array	$cid	An array of language IDs.
	 * @param	int		$value	The value of the published state.
	 *
	 * @return	boolean	True on success, false otherwise.
	 * @since	1.0
	 */
	public function setPublished($cid, $value = 0)
	{
		return JTable::getInstance('Language')->publish($cid, $value);
	}

	/**
	 * Method to delete records.
	 *
	 * @param	array	An array of item primary keys.
	 *
	 * @return	boolean	Returns true on success, false on failure.
	 * @since	1.0
	 */
	public function delete($pks)
	{
		// Sanitize the array.
		$pks = (array) $pks;

		// Get a row instance.
		$table = JTable::getInstance('Language');

		// Iterate the items to delete each one.
		foreach ($pks as $itemId)
		{
			if (!$table->delete((int) $itemId)) {
				$this->setError($table->getError());

				return false;
			}
		}

		// Clean the cache.
		$this->cleanCache();

		return true;
	}

	/**
	 * Custom clean cache method, 2 places for 2 applications
	 *
	 * @since	1.0
	 */
	function cleanCache() {
		parent::cleanCache('_system', 0);
		parent::cleanCache('_system', 1);
		parent::cleanCache('com_languages', 0);
		parent::cleanCache('com_languages', 1);
	}

}