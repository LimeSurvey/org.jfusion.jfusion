<?php

/**
 * This is the jfusion user plugin file
 *
 * PHP version 5
 *
 * @category   JFusion
 * @package    Plugins
 * @subpackage Authentication
 * @author     JFusion Team <webmaster@jfusion.org>
 * @copyright  2008 JFusion. All rights reserved.
 * @license    http://www.gnu.org/copyleft/gpl.html GNU/GPL
 * @link       http://www.jfusion.org
 */

// no direct access
defined('_JEXEC') or die('Restricted access');
/**
 * Load the JFusion framework
 */
jimport('joomla.event.plugin');
require_once JPATH_ADMINISTRATOR . DIRECTORY_SEPARATOR . 'components' . DIRECTORY_SEPARATOR . 'com_jfusion' . DIRECTORY_SEPARATOR . 'import.php';
/**
 * JFusion Authentication class
 *
 * @category   JFusion
 * @package    Plugins
 * @subpackage Authentication
 * @author     JFusion Team <webmaster@jfusion.org>
 * @copyright  2008 JFusion. All rights reserved.
 * @license    http://www.gnu.org/copyleft/gpl.html GNU/GPL
 * @link       http://www.jfusion.org
 */
class plgAuthenticationjfusion extends JPlugin
{
	var $name = 'jfusion';
    /**
     * Constructor
     *
     * For php4 compatibility we must not use the __constructor as a constructor for plugins
     * because func_get_args ( void ) returns a copy of all passed arguments NOT references.
     * This causes problems with cross-referencing necessary for the observer design pattern.
     *
     * @param object &$subject The object to observe
     * @param array  $config   An array that holds the plugin configuration
     *
     * @since 1.5
     * @return void
     */
    function plgAuthenticationjfusion(&$subject, $config)
    {
        parent::__construct($subject, $config);
        //load the language
        $this->loadLanguage('com_jfusion', JPATH_BASE);
    }

	/**
	 * @param $credentials
	 * @param $options
	 * @param $response
	 *
	 * @return void
	 */
    function onUserAuthenticate($credentials, $options, &$response){
	    jimport('joomla.user.helper');
	    global $JFusionLoginCheckActive;
	    $mainframe = JFactory::getApplication();
	    // Initialize variables
	    $debugger = JFusionFactory::getDebugger('jfusion-authentication');
	    $debugger->set(null, array());

	    $db = JFactory::getDBO();
	    //get the JFusion master
	    $master = JFusionFunction::getMaster();
	    if (!empty($master)) {
		    $JFusionMaster = JFusionFactory::getUser($master->name);
		    try {
			    $userinfo = $JFusionMaster->getUser($credentials['username']);
		    } catch (Exception $e) {
			    $userinfo = null;
		    }
		    if (!empty($userinfo)) {
			    //check to see if the login checker wanted a skip password
			    $debug = JFusionFunction::isAdministrator();
			    if (!empty($options['skip_password_check']) && $debug === true) {
				    $debugger->add('debug', JText::_('SKIPPED') . ' ' . JText::_('PASSWORD') . ' ' . JText::_('ENCRYPTION') . ' ' . JText::_('CHECK'));
				    $response->status = JAuthentication::STATUS_SUCCESS;
				    $response->email = $userinfo->email;
				    $response->fullname = $userinfo->name;
				    $response->error_message = '';
				    $response->userinfo = $userinfo;
			    } else {
				    // Joomla does not like blank passwords
				    if (empty($credentials['password'])) {
					    $response->status = JAuthentication::STATUS_FAILURE;
					    $response->error_message = JText::_('EMPTY_PASSWORD_NO_ALLOWED');
				    } else {
						//store this to be stored jfusion_user table by the joomla_int createUser function
					    $userinfo->credentialed_username = $credentials['username'];
					    //apply the clear text password to the user object
					    $userinfo->password_clear = $credentials['password'];
					    //check the master plugin for a valid password
					    $model = JFusionFactory::getAuth($master->name);

					    try {
						    $check = $model->checkPassword($userinfo);
					    } catch (Exception $e) {
						    JFusionFunction::raiseError($e, $model->getJname());
						    $check = false;
					    }
					    if ($check === true) {
						    //found a match
						    $debugger->add('debug', $master->name . ' ' . JText::_('PASSWORD') . ' ' . JText::_('ENCRYPTION') . ' ' . JText::_('CHECK') . ': ' . JText::_('SUCCESS'));
						    $response->status = JAuthentication::STATUS_SUCCESS;
						    $response->email = $userinfo->email;
						    $response->fullname = $userinfo->name;
						    $response->error_message = '';
						    $response->userinfo = $userinfo;
                            //Synchronize password to slaves
                            $query = $db->getQuery(true)
                                ->select('name')
                                ->from('#__jfusion')
                                ->where('master = 0');
                            $db->setQuery($query);
                            $auth_models = $db->loadObjectList();
                            //loop through the different models
                            foreach ($auth_models as $auth_model) {
                                $model = JFusionFactory::getAuth($auth_model->name);
                                $JFusionSlave = JFusionFactory::getUser($auth_model->name);
                                $existingUserinfo = $JFusionSlave->getUser($userinfo);
                                $status = array('error' => array(), 'debug' => array());
                                try {
                                    $JFusionSlave->updatePassword($userinfo, $existingUserinfo, $status);
                                    // var_dump($auth_model->name); 
                                } catch (Exception $e) {
                                    $JFusionSlave->debugger->add('error', JText::_('PASSWORD_UPDATE_ERROR') . ' ' . $e->getMessage());
                                }                                
                            }
                            //die();
					    } else {
						    $testcrypt = $model->generateEncryptedPassword($userinfo);
						    if (isset($options['show_unsensored'])) {
							    $debugger->add('debug', $master->name . ' ' . JText::_('PASSWORD') . ' ' . JText::_('ENCRYPTION') . ' ' . JText::_('CHECK') . ': ' . $testcrypt . ' vs ' . $userinfo->password);
						    } else {
							    $debugger->add('debug', $master->name . ' ' . JText::_('PASSWORD') . ' ' . JText::_('ENCRYPTION') . ' ' . JText::_('CHECK') . ': ' .  substr($testcrypt, 0, 6) . '******** vs ' . substr($userinfo->password, 0, 6) . '********');
						    }

						    //otherwise check the other authentication models
						    $query = $db->getQuery(true)
							    ->select('name')
							    ->from('#__jfusion')
							    ->where('master = 0')
							    ->where('check_encryption = 1');

						    $db->setQuery($query);
						    $auth_models = $db->loadObjectList();
						    //loop through the different models
						    foreach ($auth_models as $auth_model) {
							    try {
								    //Generate an encrypted password for comparison
								    $model = JFusionFactory::getAuth($auth_model->name);
								    $JFusionSlave = JFusionFactory::getUser($auth_model->name);
								    $slaveuserinfo = $JFusionSlave->getUser($userinfo);
								    // add in the clear password to be able to generate the hash
								    if (!empty($slaveuserinfo)) {
									    $slaveuserinfo->password_clear = $userinfo->password_clear;
									    $testcrypt = $model->generateEncryptedPassword($slaveuserinfo);
									    $check = $model->checkPassword($slaveuserinfo);
								    } else {
									    $testcrypt = $model->generateEncryptedPassword($userinfo);
									    $check = $model->checkPassword($userinfo);
								    }

								    if ($check === true) {
									    //found a match
									    $debugger->add('debug', $auth_model->name . ' ' . JText::_('PASSWORD') . ' ' . JText::_('ENCRYPTION') . ' ' . JText::_('CHECK') . ': ' . JText::_('SUCCESS'));
									    $response->status = JAuthentication::STATUS_SUCCESS;
									    $response->email = $userinfo->email;
									    $response->fullname = $userinfo->name;
									    $response->error_message = '';
									    $response->userinfo = $userinfo;
									    //update the password format to what the master expects
									    $JFusionMaster = JFusionFactory::getUser($master->name);
									    //make sure that the password_clear is not already hashed which may be the case for some dual login plugins

									    if (strlen($userinfo->password_clear) != 32) {
										    $status = array('error' => array(), 'debug' => array());
										    try {
											    $JFusionMaster->updatePassword($userinfo, $slaveuserinfo, $status);
										    } catch (Exception $e) {
											    $JFusionMaster->debugger->add('error', JText::_('PASSWORD_UPDATE_ERROR') . ' ' . $e->getMessage());
										    }
										    $JFusionMaster->mergeStatus($status);
										    $status = $JFusionMaster->debugger->get();
										    if (!empty($status['error'])) {
											    foreach($status['error'] as $error) {
												    $debugger->add('debug', $auth_model->name . ' ' . JText::_('PASSWORD') . ' ' . JText::_('UPDATE') . ' ' . JText::_('ERROR') . ': ' . $error);
											    }
											    JFusionFunction::raise('error', $status['error'], $master->name. ' ' .JText::_('PASSWORD') . ' ' . JText::_('UPDATE'));
										    } else {
											    $debugger->add('debug', $auth_model->name . ' ' . JText::_('PASSWORD') . ' ' . JText::_('UPDATE') . ' ' . JText::_('SUCCESS'));
										    }
									    } else {
										    $debugger->add('debug', $auth_model->name . ' ' . JText::_('SKIPPED_PASSWORD_UPDATE') . ': ' . JText::_('PASSWORD_UNAVAILABLE'));
									    }
									    return;
								    } else {
									    if (isset($options['show_unsensored'])) {
										    $debugger->add('debug', $auth_model->name . ' ' . JText::_('PASSWORD') . ' ' . JText::_('ENCRYPTION') . ' ' . JText::_('CHECK') . ': ' .  $testcrypt . ' vs ' . $userinfo->password);
									    } else {
										    $debugger->add('debug', $auth_model->name . ' ' . JText::_('PASSWORD') . ' ' . JText::_('ENCRYPTION') . ' ' . JText::_('CHECK') . ': ' .  substr($testcrypt, 0, 6) . '******** vs ' . substr($userinfo->password, 0, 6) . '********');
									    }
								    }
							    } catch (Exception $e) {
								    JFusionFunction::raiseError($e);
							    }
						    }

						    if (empty($JFusionLoginCheckActive) && $mainframe->isAdmin()) {
							    //Logging in via Joomla admin but JFusion failed so attempt the normal joomla behaviour
							    JFusionFunction::getJoomlaAuth()->onUserAuthenticate($credentials, $options, $response);
							    $debugger->add('debug', JText::_('JOOMLA_AUTH_PLUGIN_USED_JFUSION_FAILED'));
						    }

						    if (isset($response->status) && $response->status != JAuthentication::STATUS_SUCCESS) {
							    //no matching password found
							    $response->status = JAuthentication::STATUS_FAILURE;
							    $response->error_message = JText::_('FUSION_INVALID_PASSWORD');
						    }
					    }
				    }
			    }
		    } else {
			    if (empty($JFusionLoginCheckActive) && $mainframe->isAdmin()) {
				    //Logging in via Joomla admin but JFusion failed so attempt the normal joomla behaviour
				    JFusionFunction::getJoomlaAuth()->onUserAuthenticate($credentials, $options, $response);
				    $debugger->add('debug', JText::_('JOOMLA_AUTH_PLUGIN_USED_JFUSION_FAILED'));
			    }

			    if (isset($response->status) && $response->status != JAuthentication::STATUS_SUCCESS) {
				    $response->status = JAuthentication::STATUS_FAILURE;
				    $response->error_message = JText::_('USER_NOT_EXIST');
			    }
		    }

		    if (empty($JFusionLoginCheckActive)) {
			    // Check the two factor authentication
			    if ($response->status == JAuthentication::STATUS_SUCCESS)
			    {
				    $joomla = JFusionFactory::getUser('joomla_int');

				    $joomlauser = $joomla->getUser($credentials['username']);

				    require_once JPATH_ADMINISTRATOR . '/components/com_users/helpers/users.php';

				    if (method_exists('UsersHelper', 'getTwoFactorMethods')) {
					    $methods = UsersHelper::getTwoFactorMethods();

					    if (count($methods) <= 1)
					    {
						    // No two factor authentication method is enabled
						    return;
					    }

					    require_once JPATH_ADMINISTRATOR . '/components/com_users/models/user.php';

					    $model = new UsersModelUser;

					    // Load the user's OTP (one time password, a.k.a. two factor auth) configuration
					    if (!array_key_exists('otp_config', $options))
					    {
						    $otpConfig = $model->getOtpConfig($joomlauser->userid);
						    $options['otp_config'] = $otpConfig;
					    }
					    else
					    {
						    $otpConfig = $options['otp_config'];
					    }

					    // Check if the user has enabled two factor authentication
					    if (empty($otpConfig->method) || ($otpConfig->method == 'none'))
					    {
						    // Warn the user if he's using a secret code but he has not
						    // enabed two factor auth in his account.
						    if (!empty($credentials['secretkey']))
						    {
							    try
							    {
								    $app = JFactory::getApplication();

								    $this->loadLanguage();

								    $app->enqueueMessage(JText::_('PLG_AUTH_JOOMLA_ERR_SECRET_CODE_WITHOUT_TFA'), 'warning');
							    }
							    catch (Exception $exc)
							    {
								    // This happens when we are in CLI mode. In this case
								    // no warning is issued
								    return;
							    }
						    }

						    return;
					    }

					    // Load the Joomla! RAD layer
					    if (!defined('FOF_INCLUDED'))
					    {
						    include_once JPATH_LIBRARIES . '/fof/include.php';
					    }

					    // Try to validate the OTP
					    FOFPlatform::getInstance()->importPlugin('twofactorauth');

					    $otpAuthReplies = FOFPlatform::getInstance()->runPlugins('onUserTwofactorAuthenticate', array($credentials, $options));

					    $check = false;

					    /**
					     * This looks like noob code but DO NOT TOUCH IT and do not convert
					     * to in_array(). During testing in_array() inexplicably returned
					     * null when the OTEP begins with a zero! o_O
					     */
					    if (!empty($otpAuthReplies))
					    {
						    foreach ($otpAuthReplies as $authReply)
						    {
							    $check = $check || $authReply;
						    }
					    }

					    // Fall back to one time emergency passwords
					    if (!$check)
					    {
						    // Did the user use an OTEP instead?
						    if (empty($otpConfig->otep))
						    {
							    if (empty($otpConfig->method) || ($otpConfig->method == 'none'))
							    {
								    // Two factor authentication is not enabled on this account.
								    // Any string is assumed to be a valid OTEP.

								    return;
							    }
							    else
							    {
								    /**
								     * Two factor authentication enabled and no OTEPs defined. The
								     * user has used them all up. Therefore anything he enters is
								     * an invalid OTEP.
								     */
								    return;
							    }
						    }

						    // Clean up the OTEP (remove dashes, spaces and other funny stuff
						    // our beloved users may have unwittingly stuffed in it)
						    $otep = $credentials['secretkey'];
						    $otep = filter_var($otep, FILTER_SANITIZE_NUMBER_INT);
						    $otep = str_replace('-', '', $otep);

						    $check = false;

						    // Did we find a valid OTEP?
						    if (in_array($otep, $otpConfig->otep))
						    {
							    // Remove the OTEP from the array
							    $otpConfig->otep = array_diff($otpConfig->otep, array($otep));

							    $model->setOtpConfig($joomlauser->userid, $otpConfig);

							    // Return true; the OTEP was a valid one
							    $check = true;
						    }
					    }

					    if (!$check)
					    {
						    $response->status = JAuthentication::STATUS_FAILURE;
						    $response->error_message = JText::_('JGLOBAL_AUTH_INVALID_SECRETKEY');
					    }
				    }
			    }
		    }
	    } else {
		    //we have to call the main Joomla plugin as we have no master
		    JFusionFunction::getJoomlaAuth()->onUserAuthenticate($credentials, $options, $response);
		    $debugger->add('debug', JText::_('JOOMLA_AUTH_PLUGIN_USED_NO_MASTER'));
	    }
    }
}
