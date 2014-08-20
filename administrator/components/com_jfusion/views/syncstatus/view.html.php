<?php

/**
 * This is view file for syncstatus
 *
 * PHP version 5
 *
 * @category   JFusion
 * @package    ViewsAdmin
 * @subpackage Syncstatus
 * @author     JFusion Team <webmaster@jfusion.org>
 * @copyright  2008 JFusion. All rights reserved.
 * @license    http://www.gnu.org/copyleft/gpl.html GNU/GPL
 * @link       http://www.jfusion.org
 */

// no direct access
defined('_JEXEC') or die('Restricted access');

/**
 * Renders the main admin screen that shows the configuration overview of all integrations
 *
 * @category   JFusion
 * @package    ViewsAdmin
 * @subpackage Syncstatus
 * @author     JFusion Team <webmaster@jfusion.org>
 * @copyright  2008 JFusion. All rights reserved.
 * @license    http://www.gnu.org/copyleft/gpl.html GNU/GPL
 * @link       http://www.jfusion.org
 */
class jfusionViewsyncstatus extends JViewLegacy
{
 	/**
	 * @var string $syncid
	 */
	var $syncid;

	/**
	 * @var int $errorCount
	 */
	var $errorCount;

	/**
	 * @var $pageNav JPagination
	 */
	var $pageNav;

	/**
	 * @var $filter array
	 */
	var $filter;

	/**
	 * @var $syncdata array
	 */
	var $syncdata;

    /**
     * displays the view
     *
     * @param string $tpl template name
     *
     * @return mixed html output of view
     */
    function display($tpl = null)
    {
        $mainframe = JFactory::getApplication();
        //add css
        $document = JFactory::getDocument();
        $template = $mainframe->getTemplate();
        $document->addStyleSheet('templates/' . $template . '/css/general.css');

	    $this->syncid = JFactory::getApplication()->input->get('syncid');
	    $syncdata = \JFusion\User\Sync::getSyncdata($this->syncid);

        //append log
        $mainframe = JFactory::getApplication();
        $client = JFactory::getApplication()->input->getWord('filter_client', 'site');
        $option = JFactory::getApplication()->input->getCmd('option');
	    $sort = $mainframe->getUserStateFromRequest($option . '.' . $client . '.filter_order', 'filter_order', 'id', 'cmd');
	    $dir = $mainframe->getUserStateFromRequest($option . '.' . $client . '.filter_order_Dir', 'filter_order_Dir', '', 'word');
        $limit = (int)$mainframe->getUserStateFromRequest('global.list.limit', 'limit', JFactory::getConfig()->get('list_limit'), 'int');
        $limitstart = (int)$mainframe->getUserStateFromRequest($option . '.limitstart', 'limitstart', 0, 'int');
        $syncdata['log'] = \JFusion\User\Sync::getLogData($this->syncid, 'all', $limitstart, $limit, $sort, $dir);

        $db = JFactory::getDBO();

	    $query = $db->getQuery(true)
		    ->select('COUNT(*)')
		    ->from('#__jfusion_sync_details')
		    ->where('syncid = ' . $db->quote($this->syncid));

        $db->setQuery($query);
        $total = $db->loadResult();
        jimport('joomla.html.pagination');

	    $this->pageNav = new JPagination($total, $limitstart, $limit);
	    $this->filter = array('order' => $sort, 'dir' => $dir, 'limit' => $limit, 'limitstart' => $limitstart, 'client' => $client);
	    $this->syncdata = $syncdata;

	    $this->errorCount = \JFusion\User\Sync::countLogData($this->syncid, 'error');
        parent::display($tpl);
    }
}
