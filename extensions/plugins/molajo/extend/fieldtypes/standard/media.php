<?php
/**
 * @version		$Id: media.php 20196 2011-01-09 02:40:25Z ian $
 * @package		Joomla.Framework
 * @subpackage	Form
 * @copyright	Copyright (C) 2005 - 2011 Open Source Matters, Inc. All rights reserved.
 * @license		GNU General Public License version 2 or later; see LICENSE.txt
 */

defined('JPATH_BASE') or die;

jimport('joomla.form.formfield');

/**
 * Form Field class for the Joomla Framework.
 *
 * @package		Joomla.Framework
 * @subpackage	Form
 * @since		1.6
 */
class JFormFieldMedia extends JFormField
{
	/**
	 * The form field type.
	 *
	 * @var		string
	 * @since	1.6
	 */
	protected $type = 'Media';

	/**
	 * The initialised state of the document object.
	 *
	 * @var		boolean
	 * @since	1.6
	 */
	protected static $initialised = false;

	/**
	 * Method to get the field input markup.
	 *
	 * @return	string	The field input markup.
	 * @since	1.6
	 */
	protected function getInput()
	{
		$assetField	= $this->element['asset_field'] ? (string) $this->element['asset_field'] : 'asset_id';
		$authorField= $this->element['created_by_field'] ? (string) $this->element['created_by_field'] : 'created_by';
		$asset		= $this->form->getValue($assetField) ? $this->form->getValue($assetField) : (string) $this->element['asset_id'] ;

		$link = (string) $this->element['link'];
		if (!self::$initialised) {

			// Load the modal behavior script.
			JHtml::_('behavior.modal');

			// Build the script.
			$script = array();
			$script[] = '	function jInsertFieldValue(value,id) {';
			$script[] = '		var old_id = document.getElementById(id).value;';
			$script[] = '		if (old_id != id) {';
			$script[] = '			document.getElementById(id).value = value;';
			$script[] = '		}';
			$script[] = '	}';

			// Add the script to the document head.
			MolajoFactory::getDocument()->addScriptDeclaration(implode("\n", $script));

			self::$initialised = true;
		}

		// Initialize variables.
		$html = array();
		$attr = '';

		// Initialize some field attributes.
		$attr .= $this->element['class'] ? ' class="'.(string) $this->element['class'].'"' : '';
		$attr .= $this->element['size'] ? ' size="'.(int) $this->element['size'].'"' : '';

		// Initialize JavaScript field attributes.
		$attr .= $this->element['onchange'] ? ' onchange="'.(string) $this->element['onchange'].'"' : '';

		// The text field.
		$html[] = '<div class="fltlft">';
		$html[] = '	<input type="text" name="'.$this->name.'" id="'.$this->id.'"' .
					' value="'.htmlspecialchars($this->value, ENT_COMPAT, 'UTF-8').'"' .
					' readonly="readonly"'.$attr.' />';
		$html[] = '</div>';

		$directory = (string)$this->element['directory'];
		if ($this->value && file_exists(JPATH_ROOT.'/'.$this->value)) {
			$folder = explode ('/',$this->value);
			array_shift($folder);
			array_pop($folder);
			$folder = implode('/',$folder);
		}
		elseif (file_exists(JPATH_ROOT.'/images/'.$directory)) {
			$folder = $directory;
		}
		else {
			$folder='';
		}
		// The button.
		$html[] = '<div class="button2-left">';
		$html[] = '	<div class="blank">';
		$html[] = '		<a class="modal" title="'.MolajoText::_('JSELECT').'"' .
					' href="'.($this->element['readonly'] ? '' : ($link ? $link : 'index.php?option=com_media&amp;view=images&amp;tmpl=component&amp;asset='.$asset.'&amp;author='.$this->form->getValue($authorField)).'&amp;fieldid='.$this->id.'&amp;folder='.$folder).'"' .
					' rel="{handler: \'iframe\', size: {x: 800, y: 500}}">';
		$html[] = '			'.MolajoText::_('JSELECT').'</a>';
		$html[] = '	</div>';
		$html[] = '</div>';
		
		$html[] = '<div class="button2-left">';
		$html[] = '	<div class="blank">';
		$html[] = '		<a title="'.MolajoText::_('JCLEAR').'"' .
					' href="#"'.
					' onclick="javascript:document.getElementById(\''.$this->id.'\').value=\'\';">';
		$html[] = '			'.MolajoText::_('JCLEAR').'</a>';
		$html[] = '	</div>';
		$html[] = '</div>';

		return implode("\n", $html);
	}
}
