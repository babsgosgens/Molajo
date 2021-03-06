<?php
use Molajo\Service\Services;
/**
 * @package    Molajo
 * @copyright  2012 Amy Stephen. All rights reserved.
 * @license    GNU GPL v 2, or later and MIT, see License folder
 */
defined('MOLAJO') or die;

$nowrap = '';
$checked = '';
$rowCount = Services::Registry()->get('Triggerdata', 'AdminGridTableRows'); ?>
<table class="responsive">
    <thead>
    <tr>
        <?php
        $count = 1;
        $columnArray = Services::Registry()->get('Triggerdata', 'AdminGridTableColumns');
        foreach ($columnArray as $column) {
            $extraClass = '';
            if ($count == 1) {
                $extraClass .= 'first';
                $nowrap = ' nowrap ';
            }
            if ($count == count($columnArray)) {
                $extraClass .= 'last';
            }
            if ($extraClass == '') {
            } else {
                $extraClass = ' class="' . trim($extraClass) . '"';
            }
            ?>
            <th<?php echo $extraClass . ' ' .  $nowrap; ?>><?php echo Services::Language()->translate('GRID_' . strtoupper($column) . '_COLUMN_HEADING'); ?></th>
            <?php
            $count++;
        } ?>
        <th width="1%">
            <input type="checkbox" class="checkall"><?php echo Services::Language()->translate('GRID_CHECK_ALL'); ?>
        </th>
    </tr>
    </thead>
    <tbody>
