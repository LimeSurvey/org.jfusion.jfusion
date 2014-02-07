<?php

/**
 * This is view file for logoutcheckerresult
 *
 * PHP version 5
 *
 * @category   JFusion
 * @package    ViewsAdmin
 * @subpackage Logoutcheckerresults
 * @author     JFusion Team <webmaster@jfusion.org>
 * @copyright  2008 JFusion. All rights reserved.
 * @license    http://www.gnu.org/copyleft/gpl.html GNU/GPL
 * @link       http://www.jfusion.org
 */
// no direct access
defined('_JEXEC') or die('Restricted access');
echo JFusionFunctionAdmin::getDonationBanner();

/**
 * Output information about the server for future support queries
 */
?>
<div class="jfusion">
	<form method="post" action="index.php?option=com_jfusion" name="adminForm" id="adminForm">
		<input type="hidden" name="task" value="logoutcheckerresult" />
	</form>
	<div style="border:0 none ; margin:0; padding:0 5px; width: 800px; float: left;">
		<?php
		$title = JText::_('LOGOUT') . ' ' . JText::_('DEBUG');
	    debug::show($this->debug, $title);
		?>
		<label for="debug"><?php echo JText::_('JFUSION') . ' ' . JText::_('DEBUG'); ?></label>
		<textarea id="debug" rows="25" class="dumparea"><?php echo debug::getText($this->debug, $title) ?></textarea>
	</div>
</div>