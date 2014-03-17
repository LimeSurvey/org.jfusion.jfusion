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
class JFormFieldJFusionPair extends JFormField
{
	public $type = 'JFusionPair';
	/**
	 * Get an element
	 *
	 * @return string html
	 */
	protected function getInput()
	{
		JFusionFunction::initJavaScript();

		$delete = JText::_('DELETE_PAIR');
		$add = JText::_('ADD_PAIR');
		$configure = JText::_('CONFIGURE');

		$att = $this->element->attributes();

		$col1 = isset($att['col1']) ? JText::_((string)$att['col1']) : JText::_('NAME');
		$col2 = isset($att['col2']) ? JText::_((string)$att['col2']) : JText::_('VALUE');

		$js = '';
		$values = '';
		if (!is_array($this->value) || !count($this->value)) {
			$this->value = array();
			$this->value['value'][] = '';
			$this->value['name'][] = '';
		}

		$i = 0;
		foreach ($this->value['value'] as $key => $value) {
			$v = htmlentities($value);
			$n = htmlentities($this->value['name'][$key]);

			$js .=<<<JS
				$('{$this->id}name{$i}').addEvent('change', function () {
					$('{$this->id}_save').set('src', 'components/com_jfusion/images/filesave.png');
				});
				$('{$this->id}value{$i}').addEvent('change', function () {
					$('{$this->id}_save').set('src', 'components/com_jfusion/images/filesave.png');
				});
JS;

			$values .=<<<HTML
				<tr id="{$this->id}{$i}">
					<td>
						<input type="text" name="{$this->name}[name][{$i}]" id="{$this->id}name{$i}" size="50" value="{$n}"/>
					</td>
					<td>
						<input type="text" name="{$this->name}[value][{$i}]" id="{$this->id}value{$i}" size="50" value="{$v}"/>
					</td>
					<td>
						<a href="javascript:JFusion.removePair('{$this->id}', '{$i}');">{$delete}</a>
					</td>
				</tr>
HTML;
			$i++;
		}

		$js =<<<JS
			window.addEvent('domready',function() {
				{$js}
				});
JS;
		$document = JFactory::getDocument();
		$document->addScriptDeclaration($js);

		$output = <<<HTML
			<div style="display:none;" id="{$this->id}">
				<div id="{$this->id}_target">
					<table>
						<thead>
							<tr>
								<th>
									{$col1}
								</th>
								<th>
									{$col2}
								</th>
								<th>
								</th>
							</tr>
						</thead>
						<tbody id="{$this->id}_params">
							{$values}
						</tbody>
					</table>
					<div>
						<a href="javascript:JFusion.addPair('{$this->name}', '{$this->id}', 50);">{$add}</a>
					</div>
	    		</div>
			</div>
			<a class="modal btn" title="{$configure}"  href="" rel="{target: '{$this->id}_target', handler: 'adopt', returnTo: '{$this->id}', onClose : JFusion.closeAdopt, size: {x: 650, y: 375}}">{$configure}</a>
HTML;

		if($this->value) {
			$src = 'components/com_jfusion/images/tick.png';
		} else {
			$src = 'components/com_jfusion/images/clear.png';
		}
		$output .= '<img id="' . $this->id . '_save" src="' . $src . '" alt="' . JText::_('SAVE') . '">';
		return $output;
	}
}
