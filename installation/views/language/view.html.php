<?php
/**
 * @version		$Id: view.html.php 21376 2011-05-24 17:11:48Z dextercowley $
 * @package		Joomla.Installation
 * @copyright	Copyright (C) 2005 - 2011 Open Source Matters, Inc. All rights reserved.
 * @license		GNU General Public License version 2 or later; see LICENSE.txt
 */

defined('_JEXEC') or die;

/**
 * The HTML Joomla Core Language View
 *
 * @package		Joomla.Installation
 * @since		1.6
 */
class JInstallationViewLanguage extends JView
{
	/**
	 * Display the view
	 *
	 */
	public function display($tpl = null)
	{
		$state = $this->get('State');
		$form  = $this->get('Form');

		// Check for errors.
		if (count($errors = $this->get('Errors'))) {
			JError::raiseError(500, implode("\n", $errors));
			return false;
		}

		$this->assignRef('state',	$state);
		$this->assignRef('form',	$form);

		parent::display($tpl);
	}
}