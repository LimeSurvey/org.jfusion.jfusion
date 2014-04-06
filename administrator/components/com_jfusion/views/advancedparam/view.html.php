<?php

/**
 * This is view file for advancedparam
 *
 * PHP version 5
 *
 * @category   JFusion
 * @package    ViewsAdmin
 * @subpackage Advancedparam
 * @author     JFusion Team <webmaster@jfusion.org>
 * @copyright  2008 JFusion. All rights reserved.
 * @license    http://www.gnu.org/copyleft/gpl.html GNU/GPL
 * @link       http://www.jfusion.org
 */
// no direct access
defined('_JEXEC') or die('Restricted access');
jimport('joomla.application.component.view');
/**
 * Renders the JFusion Advanced Param view
 *
 * @category   JFusion
 * @package    ViewsAdmin
 * @subpackage Advancedparam
 * @author     JFusion Team <webmaster@jfusion.org>
 * @copyright  2008 JFusion. All rights reserved.
 * @license    http://www.gnu.org/copyleft/gpl.html GNU/GPL
 * @link       http://www.jfusion.org
 */
class jfusionViewadvancedparam extends JViewLegacy
{
	var $featureArray = array('config' => 'config.xml',
		'activity' => 'activity.xml',
		'search' => 'search.xml',
		'whosonline' => 'whosonline.xml',
		'useractivity' => 'useractivity.xml');

	/**
	 * @var $toolbar string
	 */
	var $toolbar;

	/**
	 * @var $comp array
	 */
	var $comp;

	/**
	 * @var $output string
	 */
	var $output;

	/**
	 * displays the view
	 *
	 * @param string $tpl template name
	 *
	 * @return mixed html output of view
	 */
	function display($tpl = null)
	{
		jimport('joomla.form.form');
		jimport('joomla.form.formfield');
		jimport('joomla.html.pane');

		JHtml::_('Formbehavior.chosen');

		$document = JFactory::getDocument();
		$document->addScript('components/com_jfusion/views/' . $this->getName() . '/tmpl/default.js');

		$mainframe = JFactory::getApplication();

		$lang = JFactory::getLanguage();
		$lang->load('com_jfusion');

		//Load Current feature
		$feature = JFactory::getApplication()->input->get('feature');
		if (empty($feature)) {
			$feature = 'any';
		}
		switch ($feature) {
			case 'whosonline':
				$lang->load('mod_jfusion_whosonline', JPATH_SITE);
				break;
			case 'search':
				$lang->load('plg_search_jfusion', JPATH_ADMINISTRATOR);
				break;
			case 'activity':
				$lang->load('mod_jfusion_activity', JPATH_SITE);
				break;
			case 'useractivity':
				$lang->load('mod_jfusion_user_activity', JPATH_SITE);
				break;
		}

		//Load multiselect
		$multiselect = JFactory::getApplication()->input->get('multiselect');
		if ($multiselect) {
			$multiselect = true;
			//Load Plugin XML Parameter
			$params = $this->loadXMLParamMulti($feature);
			//Load enabled Plugin List
			$this->output = $this->loadElementMulti($params, $feature);
		} else {
			$multiselect = false;
			//Load Plugin XML Parameter
			$params = $this->loadXMLParamSingle($feature);
			//Load enabled Plugin List
			$this->output = $this->loadElementSingle($params, $feature);
		}

		//Add Document dependent things like javascript, css
		$document = JFactory::getDocument();
		$document->setTitle('Plugin Selection');
		$template = $mainframe->getTemplate();
		$document->addStyleSheet('templates/' . $template. ' /css/general.css');

		$css = '.jfusion table.jfusionlist, table.jfusiontable{ font-size:11px; }';
		$document->addStyleDeclaration($css);

		JFusionFunction::initJavaScript();

		$apply = JText::_('APPLY');
		$close = JText::_('CLOSE');
		$toolbar = <<<HTML
	    <div class="btn-toolbar" id="toolbar">
			<div class="btn-group" id="toolbar-apply">
				<a href="#" onclick="$('adminForm').submit()" class="btn btn-small btn-success">
					<i class="icon-apply icon-white">
					</i>
					{$apply}
				</a>
			</div>

			<div class="btn-group" id="toolbar-cancel">
				<a href="#" onclick="window.parent.SqueezeBox.close();" class="btn btn-small">
					<i class="icon-cancel ">
					</i>
				    {$close}
				</a>
			</div>
		</div>
HTML;
		$this->toolbar = $toolbar;

		//for J1.6+ single select modes, params is an array
		$this->comp = is_array($params) ? $params : array();
		parent::display($multiselect ? 'multi' : 'single');
	}

	/**
	 * Loads a single element
	 *
	 * @param JRegistry $params parameters
	 * @param string $feature feature
	 *
	 * @return string html
	 */
	function loadElementSingle($params, $feature)
	{
		$JPlugin = (!empty($params['jfusionplugin'])) ? $params['jfusionplugin'] : '';

		$db = JFactory::getDBO();

		$query = $db->getQuery(true)
			->select('name as id, name as name')
			->from('#__jfusion')
			->where('status = 1');

		$db->setQuery($query);
		$rows = $db->loadObjectList();

		foreach ($rows as $key => &$row) {
			if (!JFusionFunction::hasFeature($row->name, $feature)) {
				unset($rows[$key]);
			}
		}

		$noSelected = new stdClass();
		$noSelected->id = null;
		$noSelected->name = JText::_('SELECT_ONE');
		$rows = array_merge(array($noSelected), $rows);
		$attributes = array('size' => '1', 'class' => 'inputbox');
		$output = JHTML::_('select.genericlist', $rows, 'params[jfusionplugin]', $attributes, 'id', 'name', $JPlugin);

		return $output;
	}

	/**
	 * Loads a single xml param
	 *
	 * @param string $feature feature
	 *
	 * @return array|JRegistry html
	 */
	function loadXMLParamSingle($feature)
	{
		$option = JFactory::getApplication()->input->getCmd('option');
		//Load current Parameter
		$value = $this->getParam();

		global $jname;
		$jname = (!empty($value['jfusionplugin'])) ? $value['jfusionplugin'] : '';
		if (isset($this->featureArray[$feature]) && !empty($jname)) {
			$path = JFUSION_PLUGIN_PATH . DIRECTORY_SEPARATOR . $jname . DIRECTORY_SEPARATOR . $this->featureArray[$feature];
			$defaultPath = JPATH_ADMINISTRATOR . DIRECTORY_SEPARATOR . 'components' . DIRECTORY_SEPARATOR . $option . DIRECTORY_SEPARATOR . 'views' . DIRECTORY_SEPARATOR . 'advancedparam' . DIRECTORY_SEPARATOR . 'paramfiles' . DIRECTORY_SEPARATOR . $this->featureArray[$feature];
			$xml_path = (JFile::exists($path)) ? $path : $defaultPath;
			$form = false;
			if (JFile::exists($xml_path)) {
				$xml = \JFusion\Framework::getXml($xml_path);
				if ($xml) {
					if ($xml->form) {
						/**
						 * @ignore
						 * @var $form JForm
						 */
						$form = JForm::getInstance($jname, $xml->form->asXML(), array('control' => "params[$jname]"));
						//add JFusion's fields
						$form->addFieldPath(JPATH_COMPONENT . DIRECTORY_SEPARATOR . 'fields');
						if (isset($value[$jname])) {
							$form->bind($value[$jname]);
						}
					}
				}
			}
			$value['form'] = $form;
		}
		return $value;
	}

	/**
	 * Loads a multi element
	 *
	 * @param array $params parameters
	 * @param string $feature feature
	 *
	 * @return string html
	 */
	function loadElementMulti($params, $feature)
	{
		$db = JFactory::getDBO();

		$query = $db->getQuery(true)
			->select('name as id, name as name')
			->from('#__jfusion')
			->where('status = 1');

		$db->setQuery($query);
		$rows = $db->loadObjectList();

		foreach ($rows as $key => &$row) {
			if (!JFusionFunction::hasFeature($row->name, $feature)) {
				unset($rows[$key]);
			}
		}

		//remove plugins that have already been selected
		foreach ($rows AS $k => $v) {
			if (array_key_exists($v->name, $params)) {
				unset($rows[$k]);
			}
		}
		$noSelected = new stdClass();
		$noSelected->id = null;
		$noSelected->name = JText::_('SELECT_ONE');
		$rows = array_merge(array($noSelected), $rows);
		$attributes = array('size' => '1', 'class' => 'inputbox');
		$output = JHTML::_('select.genericlist', $rows, 'jfusionplugin', $attributes, 'id', 'name');
		$output.= ' <input type="button" value="add" name="add" onclick="JFusion.addPlugin(this);" />';

		return $output;
	}

	/**
	 * @return array
	 */
	function getParam()
	{
		$hash = JFactory::getApplication()->input->get(JFactory::getApplication()->input->get('ename'));
		$session = JFactory::getSession();
		$encoded_pairs = $session->get($hash);

		$value = @unserialize(base64_decode($encoded_pairs));
		if (!is_array($value)) {
			$value = array();
		}
		return $value;
	}

	/**
	 * @param array $data
	 */
	function saveParam($data)
	{
		$hash = JFactory::getApplication()->input->get(JFactory::getApplication()->input->get('ename'));
		$session = JFactory::getSession();

		$data = base64_encode(serialize($data));
		$session->set($hash, $data);
	}

	/**
	 * Loads a multi XML param
	 *
	 * @param string $feature feature
	 *
	 * @return array html
	 */
	function loadXMLParamMulti($feature)
	{
		global $jname;
		$option = JFactory::getApplication()->input->getCmd('option');
		//Load current Parameter
		$value = $this->getParam();

		$task = JFactory::getApplication()->input->get('jfusion_task');
		if ($task == 'add') {
			$newPlugin = JFactory::getApplication()->input->get('jfusionplugin');
			if ($newPlugin) {
				if (!array_key_exists($newPlugin, $value)) {
					$value[$newPlugin] = array('jfusionplugin' => $newPlugin);
				} else {
					\JFusion\Framework::raiseError(JText::_('NOT_ADDED_TWICE'), $newPlugin);
				}
			} else {
				\JFusion\Framework::raiseError(JText::_('MUST_SELLECT_PLUGIN'));
			}
			$this->saveParam($value);
		} else if ($task == 'remove') {
			$rmPlugin = JFactory::getApplication()->input->get('jfusion_value');
			if (array_key_exists($rmPlugin, $value)) {
				unset($value[$rmPlugin]);
			} else {
				\JFusion\Framework::raiseError(JText::_('NOT_PLUGIN_REMOVE'));
			}
			$this->saveParam($value);
		}

		foreach (array_keys($value) as $key) {
			$jname = $value[$key]['jfusionplugin'];

			if (isset($this->featureArray[$feature]) && !empty($jname)) {
				$path = JFUSION_PLUGIN_PATH . DIRECTORY_SEPARATOR . $jname . DIRECTORY_SEPARATOR . $this->featureArray[$feature];
				$defaultPath = JPATH_ADMINISTRATOR . DIRECTORY_SEPARATOR . 'components' . DIRECTORY_SEPARATOR . $option . DIRECTORY_SEPARATOR . 'views' . DIRECTORY_SEPARATOR . 'advancedparam' . DIRECTORY_SEPARATOR . 'paramfiles' . DIRECTORY_SEPARATOR . $this->featureArray[$feature];
				$xml_path = (file_exists($path)) ? $path : $defaultPath;
				$xml = \JFusion\Framework::getXml($xml_path);
				if ($xml) {
					if ($xml->form) {
						/**
						 * @ignore
						 * @var $form JForm
						 */
						$form = JForm::getInstance($jname, $xml->form->asXML(), array('control' => "params[$jname]"));
						//add JFusion's fields
						$form->addFieldPath(JPATH_COMPONENT . DIRECTORY_SEPARATOR . 'fields');
						//bind values
						$form->bind($value[$key]);
						$value[$key]['form'] = $form;
					}
				}
			}
		}
		return $value;
	}
}