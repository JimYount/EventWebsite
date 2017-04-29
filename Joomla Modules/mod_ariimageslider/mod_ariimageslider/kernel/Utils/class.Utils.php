<?php
/*
 * ARI Framework
 *
 * @package		ARI Framework
 * @version		1.0.0
 * @author		ARI Soft
 * @copyright	Copyright (c) 2009 www.ari-soft.com. All rights reserved
 * @license		GNU/GPL (http://www.gnu.org/copyleft/gpl.html)
 * 
 */

(defined('_JEXEC') && defined('ARI_FRAMEWORK_LOADED')) or die('Direct Access to this location is not allowed.');

class AriSortUtils extends JObject
{
	var $_key;
	var $_dir;
	var $_cmp;
	
	function __construct($key, $dir = 'asc', $cmp = 'string')
	{
		$this->_key = $key;
		$this->_dir = strtolower($dir);
		$this->_cmp = strtolower($cmp);
	}
	
	function sort($a, $b)
	{
		$key = $this->_key;
		$aVal = is_array($a) ? $a[$key] : $a->$key;
		$bVal = is_array($b) ? $b[$key] : $b->$key;
		
		$res = 0;
		if ($this->_cmp == 'natural')
			$res = strnatcmp($aVal, $bVal);
		else
			$res = strcmp($aVal, $bVal);
		
		return $this->_dir == 'asc' 
			? $res
			: -$res;
	}
}

class AriUtils
{
	static function sortAssocArray($data, $key, $dir = 'asc', $cmp = 'string')
	{
		$sort = new AriSortUtils($key, $dir, $cmp);
		usort($data, array(&$sort, 'sort'));
		
		return $data;
	}

    static function parseValueBySample($str, $sample)
	{
		return self::parseValue($str, gettype($sample));
	}

    static function parseValue($str, $type)
	{
		$retVal = $str;
		switch ($type)
		{
			case 'boolean':
				if (is_null($str))
				{
					$retVal = false;
				}
				else
				{
					$str = strtolower(trim($str));
					if ($str == 'true' || $str == 'false')
					{
	                	$retVal = ($str == 'true');
					}
					else
					{
						$retVal = !empty($str);
					}
				}
                break;

            case 'NULL':
                $retVal = null;
                break;

            case 'integer':
                $retVal = intval($str, 10);
                break;

            case 'double':
            case 'float':
                $retVal = floatval($str);
                break;
		}
		
		return $retVal;
	}

    static function getValue($val, $emptyValue)
	{
		return !empty($val) ? $val : $emptyValue;
	}

    static function getParam($arr, $name, $defValue = null)
	{
		$retValue = $defValue;
		
		if (is_array($arr) && isset($arr[$name]))
		{
			$retValue = $arr[$name];
		}
		else if (is_object($arr) && isset($arr->{$name}))
		{
			$retValue = $arr->{$name};
		}

		return $retValue;
	}

    static function generateUniqueId()
	{
        mt_srand((float) microtime() * 1000000);
        $key = mt_rand();

        return md5($key);
	}

    static function isRemoteResource($link)
	{
		if (empty($link))
			return false;
			
		return preg_match('/(https?|ftp):\/\/.+/', $link);
	}

    static function resolvePath($path)
	{
		if (!preg_match('#^(\/|\\\\|[A-z]\:(\/|\\\\))#i', $path))
			$path = JPATH_ROOT . DIRECTORY_SEPARATOR . $path;

		return $path;
	}

    static function absPath2Url($path)
	{		
		$absPath = str_replace('\\', '/', JPATH_ROOT);
		$path = str_replace('\\', '/', $path);
		if ($absPath != '/')
		{
			$path = str_replace($absPath, JURI::root(true), $path);
		}
		else
		{
			$path = JURI::root(true) . $path;
		}
		
		return $path;
	}

    static function absPath2Relative($path)
	{
		$absPath = str_replace('\\', '/', JPATH_ROOT);
		$path = str_replace('\\', '/', $path);
		if ($absPath != '/')
		{
			$path = str_replace($absPath, '', $path);
		}
		
		if (strpos($path, '/') === 0) $path = substr($path, 1);
		
		return $path;
	}

    static function getFilteredParam($arr, $name, $defValue = null, $filterMask = 0)
	{
		$param = self::getParam($arr, $name, $defValue);
		
		return $param;
	}
}
