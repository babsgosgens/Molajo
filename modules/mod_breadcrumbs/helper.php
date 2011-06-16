<?php
/**
 * @version		$Id: helper.php 19069 2010-10-09 13:30:40Z chdemko $
 * @package		Joomla.Site
 * @subpackage	mod_breadcrumbs
 * @copyright	Copyright (C) 2005 - 2010 Open Source Matters, Inc. All rights reserved.
 * @license		GNU General Public License version 2 or later; see LICENSE.txt
 */

// no direct access
defined('JPATH_PLATFORM') or die;

class modBreadCrumbsHelper
{
	public static function getList(&$params)
	{
		// Get the PathWay object from the application
		$app		= JFactory::getApplication();
		$pathway	= $app->getPathway();
		$items		= $pathway->getPathWay();

        var_dump($app->getMenu()->getDefault());

		$count = count($items);
		for ($i = 0; $i < $count; $i ++)
		{
			$items[$i]->name = stripslashes(htmlspecialchars($items[$i]->name, ENT_COMPAT, 'UTF-8'));
			$items[$i]->link = JRoute::_($items[$i]->link);
		}

		if ($params->get('showHome', 1))
		{
			$item = new stdClass();
			$item->name = $params->get('homeText', JText::_('MOD_BREADCRUMBS_HOME'));
			$item->link = JRoute::_('index.php?Itemid='.$app->getMenu()->getDefault()->id);
			array_unshift($items, $item);
		}

		return $items;
	}

	/**
	 * Set the breadcrumbs separator for the breadcrumbs display.
	 *
	 * @param	string	$custom	Custom xhtml complient string to separate the
	 * items of the breadcrumbs
	 * @return	string	Separator string
	 * @since	1.5
	 */
	public static function setSeparator($custom = null)
	{
		$lang = JFactory::getLanguage();

		// If a custom separator has not been provided we try to load a template
		// specific one first, and if that is not present we load the default separator
		if ($custom == null) {
			if ($lang->isRTL()){
				$_separator = JHTML::_('image','system/arrow_rtl.png', NULL, NULL, true);
			}
			else{
				$_separator = JHTML::_('image','system/arrow.png', NULL, NULL, true);
			}
		} else {
			$_separator = $custom;
		}

		return $_separator;
	}
}
