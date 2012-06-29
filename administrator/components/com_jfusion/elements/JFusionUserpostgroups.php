<?php

/**
 * This is the jfusion Userpostgroups element file
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
require_once JPATH_ADMINISTRATOR . DS . 'components' . DS . 'com_jfusion' . DS . 'models' . DS . 'model.factory.php';
require_once JPATH_ADMINISTRATOR . DS . 'components' . DS . 'com_jfusion' . DS . 'models' . DS . 'model.jfusion.php';

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
class JElementJFusionUserpostgroups extends JElement
{
    var $_name = 'JFusionUserpostgroups';

    /**
     * Get an element
     *
     * @param string $name         name of element
     * @param string $value        value of element
     * @param JSimpleXMLElement &$node        node of element
     * @param string $control_name name of controler
     *
     * @return string|void html
     */
    function fetchElement($name, $value, &$node, $control_name)
    {
        global $jname;
        if ($jname) {
            if (JFusionFunction::validPlugin($jname)) {
                /**
                 * @ignore
                 * @var $JFusionAdmin JFusionAdmin_smf
                 */
                $JFusionAdmin = & JFusionFactory::getAdmin($jname);
                $usergroups = $JFusionAdmin->getUserpostgroupList();
                if (!empty($usergroups)) {
                    return JHTML::_('select.genericlist', $usergroups, $control_name . '[' . $name . ']', '', 'id', 'name', $value);
                } else {
                    return '';
                }
            } else {
                return JText::_('SAVE_CONFIG_FIRST');
            }
        } else {
            return 'Programming error: You must define global $jname before the JParam object can be rendered';
        }
    }
}
