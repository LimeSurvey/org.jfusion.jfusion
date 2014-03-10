<?php

/**
 *
 * PHP version 5
 *
 * @category   JFusion
 * @package    JFusionPlugins
 * @subpackage Gallery2
 * @author     JFusion Team <webmaster@jfusion.org>
 * @copyright  2008 JFusion. All rights reserved.
 * @license    http://www.gnu.org/copyleft/gpl.html GNU/GPL
 * @link       http://www.jfusion.org
 */

// no direct access
defined('_JEXEC') or die('Restricted access');

/**
 * JFusion plugin class for Gallery2
 *
 * @category   JFusion
 * @package    JFusionPlugins
 * @subpackage Gallery2
 * @author     JFusion Team <webmaster@jfusion.org>
 * @copyright  2008 JFusion. All rights reserved.
 * @license    http://www.gnu.org/copyleft/gpl.html GNU/GPL
 * @link       http://www.jfusion.org
 */

class JFusionHelper_gallery2 extends JFusionPlugin
{
    var $loadedGallery = false;
    var $registry = array();

    /**
     * Returns the name for this plugin
     *
     * @return string
     */
    function getJname() {
        return 'gallery2';
    }

    /**
     * @param $fullInit
     * @param null $itemId
     * @return bool
     */
    function loadGallery2Api($fullInit, $itemId = null) {
        if (!$this->loadedGallery) {
            $source_url = $this->params->get('source_url');
            $source_path = $this->params->get('source_path');
	        $index_file = $source_path . 'embed.php';
            if (substr($source_url, 0, 1) == '/') {
                $uri = JURI::getInstance();
                $base = $uri->toString(array('scheme', 'host', 'port'));
                $source_url = $base . $source_url;
            }
            $initParams['g2Uri'] = $source_url;
            $initParams['embedUri'] = $this->getEmbedUri($itemId);
            $initParams['loginRedirect'] = JRoute::_('index.php?option=com_user&view=login');
            $initParams['fullInit'] = $fullInit;
            if (!is_file($index_file)) {
                JFusionFunction::raiseWarning('The path to the Gallery2(path: ' . $index_file . ') embed file set in the component preferences does not exist', $this->getJname());
            } else {
                if (!class_exists('GalleryEmbed')) {
                    require_once $index_file;
                } else {
                    global $gallery;
	                $config_file = $source_path . 'config.php';
                    require $config_file;
                }
                $ret = GalleryEmbed::init($initParams);
                if ($ret) {
                    JFusionFunction::raiseWarning('Error while initialising Gallery2 API', $this->getJname());
                } else {
                    $ret = GalleryCoreApi::setPluginParameter('module', 'core', 'cookie.path', '/');
                    if ($ret) {
                        JFusionFunction::raiseWarning('Error while setting cookie path', $this->getJname());
                    } else {
                        if ($fullInit) {
                            $user = JFactory::getUser();
                            if ($user->id != 0) {
	                            try {
		                            $userPlugin = JFusionFactory::getUser($this->getJname());
		                            $g2_user = $userPlugin->getUser($user);
		                            $options = array();
		                            $options['noframework'] = true;
		                            $userPlugin->createSession($g2_user, $options);
	                            } catch (Exception $e) {
									JfusionFunction::raiseError($e, $this->getJname());
	                            }
                            } else {
                                // commented out we will need to keep an eye on if this will cause problems..
                                //GalleryEmbed::logout();
                            }
                            $cookie_domain = $this->params->get('cookie_domain');
                            if (!empty($cookie_domain)) {
                                $ret = GalleryCoreApi::setPluginParameter('module', 'core', 'cookie.domain', $cookie_domain);
                                if ($ret) {
                                    return false;
                                }
                            }
                            $cookie_path = $this->params->get('cookie_path');
                            if (!empty($cookie_path)) {
                                $ret = GalleryCoreApi::setPluginParameter('module', 'core', 'cookie.path', $cookie_path);
                                if ($ret) {
                                    return false;
                                }
                            }
                        }
                        $this->loadedGallery = true;
                    }
                }
            }
        }
        return $this->loadedGallery;
    }

    /**
     * @param null $itemId
     * @return string
     */
    function getEmbedUri($itemId = null) {
        $mainframe = JFactory::getApplication();
        $router = $mainframe->getRouter();
        $id = JFactory::getApplication()->input->get('Itemid', -1);
        if ($itemId !== null) {
            $id = $itemId;
        }
        //Create Gallery Embed Path
        $path = 'index.php?option=com_jfusion';
        if ($id > 0) {
            $path .= '&Itemid=' . $id;
        } else if ($this->getJname() == $itemId) {
            $source_url = $this->params->get('source_url');
            return $source_url;
        } else {
            $path .= '&view=frameless&jname=' . $this->getJname();
        }

        //added check to prevent fatal error when creating session from outside joomla
        if (class_exists('JRoute')) {
            $uri = JRoute::_($path, false);
        } else {
            $uri = $path;
        }
        if ($router->getMode() == JROUTER_MODE_SEF) {
            if (JFactory::getConfig()->get('sef_suffix')) {
                $uri = str_replace('.html', '', $uri);
            }
            if (!strpos($uri, '?')) {
                $uri .= '/';
            }
        }
        return $uri;
    }

    /**
     * @param $key
     * @param $value
     */
    function setVar($key, $value) {
        $this->registry[$key] = $value;
    }

    /**
     * @param $key
     * @param mixed $default
     *
     * @return mixed
     */
    function getVar($key, $default = null) {
        if (isset($this->registry[$key])) {
            return $this->registry[$key];
        }
        return $default;
    }

    /**
     * @return null
     */
    function setPathway() {
        global $gallery;
        $session = $gallery->getSession();
        if ($session) {
            $session->doNotUseTempId();
        }
	    /**
	     * @ignore
	     * @var $entities GalleryItem[]
	     * @var $it GalleryItem
	     */
        $entities = array();
        $mainframe = JFactory::getApplication();
        $urlGenerator = $gallery->getUrlGenerator();
        $itemId = (int)GalleryUtilities::getRequestVariables('itemId');
        $userId = $gallery->getActiveUserId();
        /* fetch parent sequence for current itemId or Root */
        if ($itemId) {
            list($ret, $parentSequence) = GalleryCoreApi::fetchParentSequence($itemId);
            if ($ret) {
                return $ret;
            }
        } else {
            list($ret, $rootId) = GalleryCoreApi::getPluginParameter('module', 'core', 'id.rootAlbum');
            if ($ret) {
                return $ret;
            }
            $parentSequence = array($rootId);
        }
        /* Add current item at the end */
        $parentSequence[] = $itemId;
        /* shift first parent off, as Joomla adds menu name already.*/
        array_shift($parentSequence);
        /* study permissions */
        if (sizeof($parentSequence) > 0 && $parentSequence[0] != 0) {
	        GalleryCoreApi::requireOnce('modules/core/classes/helpers/GalleryPermissionHelper_simple.class');
	        $ret = GalleryPermissionHelper_simple::studyPermissions($parentSequence);
            if ($ret) {
                return $ret;
            } else {
                /* load the Entities */
                list($ret, $list) = GalleryCoreApi::loadEntitiesById($parentSequence);
                if ($ret) {
                    return $ret;
                } else {
                    foreach ($list as $it) {
                        $entities[$it->getId() ] = $it;
                    }
                }
            }
        }
        $breadcrumbs = $mainframe->getPathWay();
        $document = JFactory::getDocument();
        /* check permissions and push */
        $i = 1;
        $limit = count($parentSequence);
        foreach ($parentSequence as $id) {
            list($ret, $canSee) = GalleryCoreApi::hasItemPermission($id, 'core.view', $userId);
            if ($ret) {
                return $ret;
            } else {
                if ($canSee) {
                    /* push them into pathway */
                    $urlParams = array('view' => 'core.ShowItem', 'itemId' => $id);
                    $title = $entities[$id]->getTitle() ? $entities[$id]->getTitle() : $entities[$id]->getPathComponent();
                    $title = preg_replace('/\r\n/', ' ', $title);
                    $url = $urlGenerator->generateUrl($urlParams);
                    if ($i < $limit) {
                        $breadcrumbs->addItem($title, $url);
                    } else {
                        $breadcrumbs->addItem($title, '');
                        /* description */
                        $document->setMetaData('description', $entities[$id]->getSummary());
                        /* keywords */
                        $document->setMetaData('keywords', $entities[$id]->getKeywords());
                    }
                }
                $i++;
            }
        }
        return null;
    }
}
