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

defined('_JEXEC') or die('Restricted access');

jimport('joomla.filesystem.folder');

class mod_ariimagesliderInstallerScript
{
	function preflight($type, $parent)
	{
		$type = strtolower($type);
		if ($type == 'install' || $type == 'update')
		{
            $this->createFolders();
		}
	
		if ($type == 'update')
			$this->removeOutdateFiles();
	}

    private function createFolders()
    {
        $thumbFolder = JPATH_ROOT . '/images/ariimageslider';

        if (!JFolder::exists($thumbFolder))
            JFolder::create($thumbFolder);
    }

	private function removeOutdateFiles()
	{
		jimport('joomla.filesystem.file');

		$colorFieldFile = JPATH_ROOT . '/modules/mod_ariimageslider/mod_ariimageslider/fields/color.php';
		if (file_exists($colorFieldFile))
			JFile::delete($colorFieldFile);

		$colorFolder = JPATH_ROOT . '/modules/mod_ariimageslider/mod_ariimageslider/fields/color';

		if (file_exists($colorFolder))
			JFolder::delete($colorFolder);
	}
}