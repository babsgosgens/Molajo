<?php
/**
 * @version     $id: audio.php
 * @package     Molajo
 * @subpackage  Responses Component
 * @copyright   Copyright (C) 2012 Amy Stephen. All rights reserved.
 * @license     GNU General Public License Version 2, or later http://www.gnu.org/licenses/gpl.html
 */
defined('MOLAJO') or die;
$document =& MolajoFactory::getDocument();
$this->audio_file_loader .= ' });';
$document->addScriptDeclaration($this->audio_file_loader); ?>
