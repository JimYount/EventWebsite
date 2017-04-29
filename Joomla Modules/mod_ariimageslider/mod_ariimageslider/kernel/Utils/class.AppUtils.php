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

(defined('_JEXEC') && defined('ARI_FRAMEWORK_LOADED')) or die;

jimport('joomla.filesystem.folder');

class AriAppUtils
{
    static private $isParseIniFileEnabled = null;

    static public function parseIniFile($filePath)
    {
        if (is_null(self::$isParseIniFileEnabled))
        {
            $disabledFunctions = explode(',', ini_get('disable_functions'));

            self::$isParseIniFileEnabled = !in_array('parse_ini_file', $disabledFunctions);
        }

        $data = null;
        if (self::$isParseIniFileEnabled)
        {
            $data = parse_ini_file($filePath, true);
        }
        else
        {
            $data = self::parseIniFileImpl($filePath);
        }

        return $data;
    }

    static private function parseIniFileImpl($filePath)
    {
        return self::parseIniStrImpl(file_get_contents($filePath));
    }

    function parseIniStrImpl($str)
    {
        if (empty($str))
            return false;

        $lines = explode("\n", $str);
        $ret = array();
        $inside_section = false;

        foreach($lines as $line)
        {
            $line = trim($line);

            if (!$line || $line[0] == '#' || $line[0] == ';')
                continue;

            if ($line[0] == '[' && $endIdx = strpos($line, ']'))
            {
                $inside_section = substr($line, 1, $endIdx - 1);
                continue;
            }

            if (!strpos($line, '='))
                continue;

            $tmp = explode('=', $line, 2);

            if ($inside_section)
            {
                $key = rtrim($tmp[0]);
                $value = ltrim($tmp[1]);

                if (preg_match("/^\".*\"$/", $value) || preg_match("/^'.*'$/", $value))
                {
                    $value = mb_substr($value, 1, mb_strlen($value) - 2);
                }

                if (!empty($matches) && isset($matches[0]))
                {
                    $arr_name = preg_replace('#\[(.*?)\]#is', '', $key);

                    if (!isset($ret[$inside_section][$arr_name]) || !is_array($ret[$inside_section][$arr_name]))
                    {
                        $ret[$inside_section][$arr_name] = array();
                    }

                    if (isset($matches[1]) && !empty($matches[1]))
                    {
                        $ret[$inside_section][$arr_name][$matches[1]] = $value;
                    }
                    else
                    {
                        $ret[$inside_section][$arr_name][] = $value;
                    }
                }
                else
                {
                    $ret[$inside_section][trim($tmp[0])] = $value;
                }

            }
            else
            {
                $ret[trim($tmp[0])] = ltrim($tmp[1]);
            }
        }

        return $ret;
    }

	static public function getExtraFieldsFromINI($path, $iniFileName, $recurse = false, $fullPath = false, $i18n = false)
	{
		$fields = array();

		$iniFileName = basename($iniFileName);
		if (empty($iniFileName))
			return $fields;
		
		$filePath = JPATH_ROOT . DS . $path . DS . $iniFileName;
		if ($i18n)
			$filePath = self::getLocalizedFileName($filePath);

		if (!@file_exists($filePath) || !is_file($filePath) || !is_readable($filePath))
			return $fields;

		$iniFields = self::parseIniFile($filePath);

		if (empty($iniFields))
			return $fields;
			
		foreach ($iniFields as $secName => $secItems)
		{
			$prop = strtolower($secName);
			foreach ($secItems as $itemKey => $itemValue)
			{
				$key = $itemKey;
				if ($fullPath)
					$key = ($path && $path != '.') ? $path . DS . $key : $key;
				if (!isset($fields[$key]))
					$fields[$key] = array();
					
				$fields[$key][$prop] = $itemValue;
			}
		}
		
		if ($recurse)
		{
			$subFolders = JFolder::folders($path);
			foreach ($subFolders as $subFolder)
			{
				$subFolderFields = self::getExtraFieldsFromINI($path . DS . $subFolder, $iniFileName, $recurse, $fullPath);
				if (count($subFolderFields) > 0)
					$fields = array_merge($fields, $subFolderFields);
			}
		}
	
		return $fields;
	}
	
	static public function getLocalizedFileName($filePath)
	{
		if (empty($filePath))
			return $filePath;
		
		$lang =& JFactory::getLanguage(); 
		$langTag = $lang->get('tag');

		if (empty($langTag))
			return $filePath;

		$defLang = $lang->getDefault();
		$prefLangs = array($langTag);
		if ($defLang != $langTag)
			$prefLangs[] = $defLang;
		
		$pathInfo = pathinfo($filePath);
		$baseName = !empty($pathInfo['extension']) ? basename($filePath, '.' . $pathInfo['extension']) : $pathInfo['basename'];
		foreach ($prefLangs as $prefLang)
		{
			$langFile = $pathInfo['dirname'] . DS . $baseName . '.' . $prefLang;
			if (!empty($pathInfo['extension']))
				$langFile .= '.' . $pathInfo['extension'];

			if (@file_exists($langFile) && is_file($langFile))
			{
				$filePath = $langFile;
				break;
			}
		}

		return $filePath;
	}
}