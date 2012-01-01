<?php
/**
 * @package     Molajo
 * @subpackage  Component
 * @copyright   Copyright (C) 2012 Amy Stephen. All rights reserved.
 * @license     GNU General Public License Version 2, or later http://www.gnu.org/licenses/gpl.html
 */
defined('MOLAJO') or die;

$fileHelper = new MolajoFileHelper();

/** Controllers */
if (file_exists($request['component_path'] . '/controller.php')) {
    $fileHelper->requireClassFile($request['component_path'] . '/controller.php', ucfirst($request['option']) . 'Controller');
}
$files = JFolder::files($request['component_path'] . '/controllers', '\.php$', false, false);
if ($files) {
    foreach ($files as $file) {
        echo $file . '<br />';
        $fileHelper->requireClassFile($request['component_path'] . '/controllers/' . $file, ucfirst($request['option']) . 'Controller' . ucfirst(substr($file, 0, strpos($file, '.'))));
    }
}
/** Helpers */
$files = JFolder::files($request['component_path'] . '/helpers', '\.php$', false, false);
if ($files) {
    foreach ($files as $file) {
        $fileHelper->requireClassFile($request['component_path'] . '/helpers/' . $file, ucfirst($request['option']) . ucfirst(substr($file, 0, strpos($file, '.'))));
    }
}

/** Models */
$files = JFolder::files($request['component_path'] . '/models', '\.php$', false, false);
if ($files) {
    foreach ($files as $file) {
        $fileHelper->requireClassFile($request['component_path'] . '/models/' . $file, ucfirst($request['option']) . 'Model' . ucfirst(substr($file, 0, strpos($file, '.'))));
    }
}

/** Tables */
$files = JFolder::files($request['component_path'] . '/tables', '\.php$', false, false);
if ($files) {
    foreach ($files as $file) {
        $fileHelper->requireClassFile($request['component_path'] . '/tables/' . $file, ucfirst($request['option']) . 'Table' . ucfirst(substr($file, 0, strpos($file, '.'))));
    }
}

/** Views */
$folders = JFolder::folders($request['component_path'] . '/views', false, false);
if ($folders) {
    foreach ($folders as $folder) {
        $files = JFolder::files($request['component_path'] . '/views/' . $folder, false, false);
        if ($files) {
            foreach ($files as $file) {
                $fileHelper->requireClassFile($request['component_path'] . '/views/' . $folder . '/' . $file, ucfirst($request['option']) . 'View' . ucfirst($folder));
            }
        }
    }
}
