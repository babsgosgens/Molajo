<?php
/**
 * @package    Molajo
 * @copyright  2012 Amy Stephen. All rights reserved.
 * @license    GNU General Public License Version 2, or later http://www.gnu.org/licenses/gpl.html
 */
use Molajo\Service\Services;
defined('MOLAJO') or die; ?>
<li<?php echo $this->row->css_class; ?>><a href="<?php echo $this->row->link; ?>"><?php echo $this->row->link_text; ?></a></li>
