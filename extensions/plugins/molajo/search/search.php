<?php
/**
 * @package     Molajo
 * @subpackage  Molajo Search Plugin
 * @copyright   Copyright (C) 2011 Amy Stephen. All rights reserved.
 * @license     GNU General Public License Version 2, or later http://www.gnu.org/licenses/gpl.html
 */
defined('MOLAJO') or die;


require_once JPATH_SITE.'/components/com_weblinks/helpers/route.php';

/**
 * Weblinks Search plugin
 *
 * @package		Joomla
 * @subpackage	Search
 * @since		1.6
 */
class plgSearchWeblinks extends MolajoPlugin
{
	/**
	 * @return array An array of search areas
	 */
	function onContentSearchAreas() {
		static $areas = array(
			'weblinks' => 'PLG_SEARCH_WEBLINKS_WEBLINKS'
			);
			return $areas;
	}

	/**
	 * Weblink Search method
	 *
	 * The sql must return the following fields that are used in a common display
	 * routine: href, title, section, created, text, browsernav
	 * @param string Target search string
	 * @param string matching option, exact|any|all
	 * @param string ordering option, newest|oldest|popular|alpha|category
	 * @param mixed An array if the search it to be restricted to areas, null if search all
	 */
	function onContentSearch($text, $phrase='', $ordering='', $areas=null)
	{
		$db		= MolajoFactory::getDbo();
		$app	= MolajoFactory::getApplication();
		$user	= MolajoFactory::getUser();

		$searchText = $text;

		if (is_array($areas)) {
			if (!array_intersect($areas, array_keys($this->onContentSearchAreas()))) {
				return array();
			}
		}

		$sContent		= $this->parameters->get('search_content',		1);
		$sArchived		= $this->parameters->get('search_archived',		1);
		$limit			= $this->parameters->def('search_limit',		50);
		$state = array();
		if ($sContent) {
			$state[]=1;
		}
		if ($sArchived) {
			$state[]=2;
		}

		$text = trim($text);
		if ($text == '') {
			return array();
		}
		$section	= MolajoText::_('PLG_SEARCH_WEBLINKS');

		$wheres	= array();
		switch ($phrase)
		{
			case 'exact':
				$text		= $db->Quote('%'.$db->getEscaped($text, true).'%', false);
				$wheres2	= array();
				$wheres2[]	= 'a.url LIKE '.$text;
				$wheres2[]	= 'a.description LIKE '.$text;
				$wheres2[]	= 'a.title LIKE '.$text;
				$where		= '('.implode(') OR (', $wheres2).')';
				break;

			case 'all':
			case 'any':
			default:
				$words	= explode(' ', $text);
				$wheres = array();
				foreach ($words as $word)
				{
					$word		= $db->Quote('%'.$db->getEscaped($word, true).'%', false);
					$wheres2	= array();
					$wheres2[]	= 'a.url LIKE '.$word;
					$wheres2[]	= 'a.description LIKE '.$word;
					$wheres2[]	= 'a.title LIKE '.$word;
					$wheres[]	= implode(' OR ', $wheres2);
				}
				$where	= '('.implode(($phrase == 'all' ? ') AND (' : ') OR ('), $wheres).')';
				break;
		}

		switch ($ordering)
		{
			case 'oldest':
				$order = 'a.created ASC';
				break;

			case 'popular':
				$order = 'a.hits DESC';
				break;

			case 'alpha':
				$order = 'a.title ASC';
				break;

			case 'category':
				$order = 'c.title ASC, a.title ASC';
				break;

			case 'newest':
			default:
				$order = 'a.created DESC';
		}

		$return = array();
		if (!empty($state)) {
			$query	= $db->getQuery(true);
			$query->select('a.title AS title, a.description AS text, a.created AS created, a.url, '
						.'CASE WHEN CHAR_LENGTH(a.alias) THEN CONCAT_WS(\':\', a.id, a.alias) ELSE a.id END as slug, '
						.'CASE WHEN CHAR_LENGTH(c.alias) THEN CONCAT_WS(\':\', c.id, c.alias) ELSE c.id END as catslug, '
						.'CONCAT_WS(" / ", '.$db->Quote($section).', c.title) AS section, "1" AS browsernav');
			$query->from('#__weblinks AS a');
			$query->innerJoin('#__categories AS c ON c.id = a.catid');
			$query->where('('.$where.')'.' AND a.state in ('.implode(',',$state).') AND  c.published=1 ');

            $acl = new MolajoACL ();
            $acl->getQueryInformation ('', $query, 'viewaccess', array('table_prefix'=>'a'));

			$query->order($order);

			// Filter by language
            $lang = MolajoFactory::getLanguage()->getTag();
            $query->where('a.language IN ('.$db->Quote($lang).','.$db->Quote('*').')');
			$query->where('c.language in ('.$db->Quote($lang).','.$db->Quote('*').')');

			$db->setQuery($query, 0, $limit);
			$rows = $db->loadObjectList();

			$return = array();
			if ($rows) {
				foreach($rows as $key => $row) {
					$rows[$key]->href = WeblinksHelperRoute::getWeblinkRoute($row->slug, $row->catslug);
				}

				foreach($rows AS $key => $weblink) {
					if (searchHelper::checkNoHTML($weblink, $searchText, array('url', 'text', 'title'))) {
						$return[] = $weblink;
					}
				}
			}
		}
		return $return;
	}
}
