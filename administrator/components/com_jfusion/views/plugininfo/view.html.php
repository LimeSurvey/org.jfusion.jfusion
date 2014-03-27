<?php

/**
 * This is view file for wizard
 *
 * PHP version 5
 *
 * @category   JFusion
 * @package    ViewsAdmin
 * @subpackage Plugindisplay
 * @author     JFusion Team <webmaster@jfusion.org>
 * @copyright  2008 JFusion. All rights reserved.
 * @license    http://www.gnu.org/copyleft/gpl.html GNU/GPL
 * @link       http://www.jfusion.org
 */

// no direct access
use JFusion\Framework;

defined('_JEXEC') or die('Restricted access');

/**
 * Renders the main admin screen that shows the configuration overview of all integrations
 *
 * @category   JFusion
 * @package    ViewsAdmin
 * @subpackage Plugindisplay
 * @author     JFusion Team <webmaster@jfusion.org>
 * @copyright  2008 JFusion. All rights reserved.
 * @license    http://www.gnu.org/copyleft/gpl.html GNU/GPL
 * @link       http://www.jfusion.org
 */
class jfusionViewplugininfo extends JViewLegacy
{
	/**
	 * @var array $features
	 */
	var $features = array();

	/**
	 * @var string $jname
	 */
	var $jname;

	/**
	 * displays the view
	 *
	 * @param string $tpl template name
	 *
	 * @throws RuntimeException
	 * @return mixed html output of view
	 */
    function display($tpl = null)
    {
        //set jname as a global variable in order for elements to access it.
        global $jname;
        //find out the submitted name of the JFusion module
        $jname = JFactory::getApplication()->input->get('jname');
        if ($jname) {
			$admin = \JFusion\Factory::getAdmin($jname);

	        $this->features['ADMIN']['FEATURE_WIZARD'] = $this->outputFeature(Framework::hasFeature($jname, 'wizard'));
	        $this->features['ADMIN']['FEATURE_REQUIRE_FILE_ACCESS'] = $this->outputFeature($admin->requireFileAccess());
	        $this->features['ADMIN']['FEATURE_MULTI_USERGROUP'] = $this->outputFeature($admin->isMultiGroup());
	        $this->features['ADMIN']['FEATURE_MULTI_INSTANCE'] = $this->outputFeature($admin->multiInstance());

            $frameless = Framework::hasFeature($jname, 'frameless');
	        if ($jname == 'joomla_int') {
				$frameless = 'JNO';
	        } else {
		        $frameless = $frameless ? 'NATIVE_FRAMELESS' : 'CURL_FRAMELESS';
	        }

	        $this->features['PUBLIC']['FEATURE_FRAMELESS'] = $this->outputFeature($frameless);
	        $this->features['PUBLIC']['FEATURE_BREADCRUMB'] = $this->outputFeature(Framework::hasFeature($jname, 'breadcrumb'));
	        $this->features['PUBLIC']['FEATURE_SEARCH'] = $this->outputFeature(Framework::hasFeature($jname, 'search'));
	        $this->features['PUBLIC']['FEATURE_ONLINE_STATUS'] = $this->outputFeature(Framework::hasFeature($jname, 'whosonline'));
	        $this->features['PUBLIC']['FEATURE_FRONT_END_LANGUAGE'] = $this->outputFeature(Framework::hasFeature($jname, 'frontendlanguage'));

	        $this->features['FORUM']['FEATURE_THREAD_URL'] = $this->outputFeature(Framework::hasFeature($jname, 'threadurl'));
	        $this->features['FORUM']['FEATURE_POST_URL'] = $this->outputFeature(Framework::hasFeature($jname, 'posturl'));
	        $this->features['FORUM']['FEATURE_PROFILE_URL'] = $this->outputFeature(Framework::hasFeature($jname, 'profileurl'));
	        $this->features['FORUM']['FEATURE_AVATAR_URL'] = $this->outputFeature(Framework::hasFeature($jname, 'avatarurl'));
	        $this->features['FORUM']['FEATURE_PRIVATE_MESSAGE_URL'] = $this->outputFeature(Framework::hasFeature($jname, 'privatemessageurl'));
	        $this->features['FORUM']['FEATURE_VIEW_NEW_MESSAGES_URL'] = $this->outputFeature(Framework::hasFeature($jname, 'viewnewmessagesurl'));
	        $this->features['FORUM']['FEATURE_PRIVATE_MESSAGE_COUNT'] = $this->outputFeature(Framework::hasFeature($jname, 'privatemessagecounts'));
	        $this->features['FORUM']['FEATURE_ACTIVITY'] = $this->outputFeature(Framework::hasFeature($jname, 'activity'));
	        $this->features['FORUM']['FEATURE_DISCUSSION_BOT'] = $this->outputFeature(Framework::hasFeature($jname, 'discussion'));

	        $this->features['USER']['FEATURE_DUAL_LOGIN'] = $this->outputFeature(Framework::hasFeature($jname, 'duallogin'));
	        $this->features['USER']['FEATURE_DUAL_LOGOUT'] = $this->outputFeature(Framework::hasFeature($jname, 'duallogout'));
	        $this->features['USER']['FEATURE_UPDATE_PASSWORD'] = $this->outputFeature(Framework::hasFeature($jname, 'updatepassword'));
	        $this->features['USER']['FEATURE_UPDATE_USERNAME'] = $this->outputFeature(Framework::hasFeature($jname, 'updateusername'));
	        $this->features['USER']['FEATURE_UPDATE_EMAIL'] = $this->outputFeature(Framework::hasFeature($jname, 'updateemail'));
	        $this->features['USER']['FEATURE_UPDATE_USERGROUP'] = $this->outputFeature(Framework::hasFeature($jname, 'updateusergroup'));
	        $this->features['USER']['FEATURE_UPDATE_LANGUAGE'] = $this->outputFeature(Framework::hasFeature($jname, 'updateuserlanguage'));
	        $this->features['USER']['FEATURE_SESSION_SYNC'] = $this->outputFeature(Framework::hasFeature($jname, 'syncsessions'));
	        $this->features['USER']['FEATURE_BLOCK_USER'] = $this->outputFeature(Framework::hasFeature($jname, 'blockuser'));
	        $this->features['USER']['FEATURE_ACTIVATE_USER'] = $this->outputFeature(Framework::hasFeature($jname, 'activateuser'));
	        $this->features['USER']['FEATURE_DELETE_USER'] = $this->outputFeature(Framework::hasFeature($jname, 'deleteuser'));

	        $this->jname = $jname;
            //render view
            parent::display($tpl);
        } else {
	        throw new RuntimeException(JText::_('NONE_SELECTED'));
        }
    }

    /**
     * @param $feature
     * @return string
     */
    function outputFeature($feature)
    {
    	if ($feature===true) {
    		$feature = 'JYES';
    	} else if ($feature===false) {
    		$feature = 'JNO';
    	}
	    switch ($feature) {
		    case 'JNO':
		    	$images = 'cross.png';
		        break;
		    case 'JYES':
		    	$images = 'tick.png';
		        break;
		    default:
		    	$images = 'system-help.png';
		        break;
		}
		return '<img src="components/com_jfusion/images/' . $images . '"/> ' . JText::_($feature);
    }
}
