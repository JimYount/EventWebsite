<?php
/*
 * ARI Framework Lite
 *
 * @package		ARI Framework Lite
 * @version		1.0.0
 * @author		ARI Soft
 * @copyright	Copyright (c) 2009 www.ari-soft.com. All rights reserved
 * @license		GNU/GPL (http://www.gnu.org/copyleft/gpl.html)
 * 
 */

defined('_JEXEC') or die ('Restricted access');

require_once dirname(__FILE__) . '/../kernel/class.AriKernel.php';

AriKernel::import('Xml.XmlHelper');

jimport('joomla.html.html');
jimport('joomla.form.formfield');

class JFormFieldAriheader extends JFormField
{
	protected $type = 'Ariheader';

	function getInput()
	{
		$this->_includeAssets();
		
		return $this->fetchElement($this->element['name'], $this->value, $this->element, $this->name);
	}
	
	function fetchElement($name, $value, &$node, $control_name)
	{
		$options = array(JText::_($value));
		foreach ($node->children() as $option)
		{
			$options[] = AriXmlHelper::getData($option);
		}

		return sprintf('<div class="ari-el-header">%s</div>', call_user_func_array('sprintf', $options));
	}
	
	function _includeAssets()
	{
		static $loaded;
		
		if ($loaded)
			return ;

		$filePath = str_replace(DS == '\\' ? '/' : '\\', DS, dirname(__FILE__));
		if (strlen(JPATH_ROOT) > 1)
			$filePath = str_replace(JPATH_ROOT, '', $filePath);
			
		$uri = JURI::root(true) . str_replace(DS, '/', $filePath) . '/';
			
		$document = JFactory::getDocument();
		$document->addStyleSheet('http://fonts.googleapis.com/css?family=Muli', 'text/css');
		$document->addStyleSheet($uri . 'header.css', 'text/css', null, array());
			
		$loaded = true;
	}
}