<?php
/**
 * This is the jfusion Discussionbot element file
 *
 * PHP version 5
 *
 * @category  JFusion
 * @package   Elements
 * @author    JFusion Team <webmaster@jfusion.org>
 * @copyright 2008 JFusion. All rights reserved.
 * @license   http://www.gnu.org/copyleft/gpl.html GNU/GPL
 * @link      http://www.jfusion.org
 */
// Check to ensure this file is included in Joomla!
defined('_JEXEC') or die();
/**
 * Require the Jfusion plugin factory
 */
require_once JPATH_ADMINISTRATOR . DIRECTORY_SEPARATOR . 'components' . DIRECTORY_SEPARATOR . 'com_jfusion' . DIRECTORY_SEPARATOR . 'import.php';
/**
 * JFusion Element class Discussionbot
 *
 * @category  JFusion
 * @package   Elements
 * @author    JFusion Team <webmaster@jfusion.org>
 * @copyright 2008 JFusion. All rights reserved.
 * @license   http://www.gnu.org/copyleft/gpl.html GNU/GPL
 * @link      http://www.jfusion.org
 */
class JFormFieldForumUserList extends JFormField
{
    public $type = 'ForumUserList';
    /**
     * Get an element
     *
     * @return string html
     */
    protected function getInput()
    {
        global $jname;
	    try {
		    if ($jname) {
			    $JFusionForum = JFusionFactory::getAdmin($jname);
			    if ($JFusionForum->isConfigured()) {
				    $users = $JFusionForum->getUserList();
				    if (!empty($users)) {
					    $output = JHTML::_('select.genericlist', $users, $this->name, '', 'id', 'name', $this->value);
				    } else {
					    throw new RuntimeException(JText::_('NO_LIST'));
				    }
			    } else {
				    throw new RuntimeException(JText::_('SAVE_CONFIG_FIRST'));
			    }
		    } else {
			    throw new RuntimeException('Programming error: You must define global $jname before the JParam object can be rendered.');
		    }
	    } catch (Exception $e) {
		    $output = '<span style="float:left; margin: 5px 0; font-weight: bold;">' . $e->getMessage() . '</span>';
	    }
	    return $output;
    }
}
