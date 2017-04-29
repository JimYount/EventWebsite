<?php
/*
 * ARI Image Slider
 *
 * @package		ARI Image Slider
 * @version		1.0.0
 * @author		ARI Soft
 * @copyright	Copyright (c) 2010 www.ari-soft.com. All rights reserved
 * @license		GNU/GPL (http://www.gnu.org/copyleft/gpl.html)
 * 
 */

defined('ARI_FRAMEWORK_LOADED') or die('Direct Access to this location is not allowed.');

jimport('joomla.filter.filterinput');
jimport('joomla.filesystem.file');

AriKernel::import('Utils.Utils');
AriKernel::import('Utils.AppUtils');
AriKernel::import('Web.JSON.JSONHelper');
AriKernel::import('FileSystem.Folder');

class AriImageSliderHelper
{
	public static function includeAssets($params)
	{
		static $loaded;

		if ($loaded)
			return ;
			
		$theme = $params->get('theme', '');

		$doc = JFactory::getDocument();
		$baseUri = JURI::root(true) . '/modules/mod_ariimageslider/mod_ariimageslider/js/';

        JHtml::_('jquery.framework', true);

		$doc->addScript($baseUri . 'jquery.nivo.slider.js');

		$filter = JFilterInput::getInstance();
		$theme = $filter->clean($theme, 'WORD');
		if (empty($theme))
            $theme = 'default';

		$doc->addStyleSheet($baseUri . 'themes/nivo-slider.css');
		$doc->addCustomTag(
			sprintf('<!--[if IE]><link rel="stylesheet" href="%s" type="text/css" /><![endif]-->',
				$baseUri . 'themes/nivo-slider.ie.css')
		);

		$doc->addStyleSheet($baseUri . 'themes/' . $theme . '/style.css');
		if (@file_exists(JPATH_ROOT . '/modules/mod_ariimageslider/mod_ariimageslider/js/themes/' . $theme . '/style.ie6.css'))
		{
			$doc->addCustomTag(
				sprintf('<!--[if lt IE 7]><link rel="stylesheet" href="%s" type="text/css" /><![endif]-->',
					$baseUri . 'themes/' . $theme . '/style.ie6.css')
			);
		}
		
		if (@file_exists(JPATH_ROOT . '/modules/mod_ariimageslider/mod_ariimageslider/js/themes/' . $theme . '/style.ie.css'))
		{
			$doc->addCustomTag(
				sprintf('<!--[if IE]><link rel="stylesheet" href="%s" type="text/css" /><![endif]-->',
					$baseUri . 'themes/' . $theme . '/style.ie.css')
			);
		}

		$loaded = true;
	}

    public static function initSlider($id, $params)
	{
		self::includeAssets($params);

		$clientParams = self::getClientParams($params);
		$doc = JFactory::getDocument();

		$loadMethod = $params->get('loadMethod', 'load');
		$fixTransparentBg = (bool)$params->get('fixTransparentBg');

		$animSpeed = AriUtils::getParam($clientParams, 'animSpeed', 500);
		
		$doc->addScriptDeclaration(
			sprintf('%3$s(function() { var $ = window.jQueryNivoSlider || jQuery; var opts = %2$s;%4$s $("#%1$s").nivoSlider(opts); });',
				$id,
				$clientParams ? AriJSONHelper::encode($clientParams) : '{}',
				$loadMethod == 'load' ? 'jQuery(window).load' : 'jQuery(document).ready',
				$fixTransparentBg
					? sprintf(
						'opts["beforeChange"] = function() { setTimeout(function() { $("#%1$s .nivo-main-image").animate({opacity:0}, %2$d);}, 20); }; opts["afterChange"] = function() { setTimeout(function() { $("#%1$s .nivo-main-image").css({opacity:1}); }, 20); };',
						$id,
						$animSpeed
					  )
					: ''
			)
		);

		$responsive = (bool)$params->get('responsive');

		$width = intval($params->get('width', 300), 10);
		$height = intval($params->get('height'), 10);
		
		if (!$responsive)
		{
			$styleDec = sprintf('#%1$s_wrapper,#%1$s{width:%2$dpx;height:%3$dpx;}',
				$id,
				$width,
				$height);
		}
		else 
		{
			$maxWidth = $params->get('width');
			if (strpos($maxWidth, '%') === false)
				$maxWidth = $width . 'px'; 
			
			$styleDec = sprintf('#%1$s_wrapper{max-width:%2$s;}#%1$s{width:100%%;height:auto;}.ari-image-slider-loading #%1$s{height:%3$dpx;}',
				$id,
				$maxWidth,
				$height
			);
			
			$doc->addCustomTag(
				sprintf('<!--[if lt IE 8]><style type="text/css">BODY #%1$s_wrapper,BODY #%1$s{width:%2$dpx;height:%3$dpx;}</style><![endif]-->',
				$id,
				$width,
				$height)
			);
		}

		if ($params->get('customstyle'))
		{
			$extraStyles = trim($params->get('customstyle'));
			$extraStyles = str_replace('{$id}', '#' . $id, $extraStyles);
			if (!empty($extraStyles))
				$styleDec .= $extraStyles;
		}
		
		$doc->addStyleDeclaration($styleDec);
	}

    public static function getClientParams($params)
	{
		$clientParams = array(
			'effect' => 'random',
			'slices' => 15,
			'boxCols' => 8,
			'boxRows' => 4,
			'animSpeed' => 500,
			'pauseTime' => 3000,
			'startSlide' => 0,
			'directionNav' => true,
			'directionNavHide' => true,
			'controlNav' => true,
			'keyboardNav' => true,
			'pauseOnHover' => true,
			'manualAdvance' => false,
			'captionOpacity' => 0.8,
			'disableClick' => false,
			'controlNavThumbs' => false,
			'stopOnEnd' => false,
			'randomStart' => false,
			'responsive' => false
		);
		
		$sliderParams = array();
		foreach ($clientParams as $key => $value)
		{
			$param = $params->get('opt_' . $key, null);
			if (is_null($param))
				continue ;
				
			$param = AriUtils::parseValueBySample($param, $value);
			if ($value !== $param)
				$sliderParams[$key] = $param;
		}
		
		$showNav = $params->get('showNav');
		if (empty($showNav))
		{
			$sliderParams['directionNav'] = false;
			$sliderParams['directionNavHide'] = false;
		}
		else if ($showNav != 'onover')
		{
			$sliderParams['directionNavHide'] = false;
		}
		
		$responsive = (bool)$params->get('responsive');
		if ($responsive)
			$sliderParams['responsive'] = true;

		return $sliderParams;
	}

    public static function getSlides($params)
	{
		$slides = array();

		$pathList = self::getPathList($params->get('path'));
		if (count($pathList) == 0)
			return $slides;

		$extraFields = array();
		$i18n = (bool)$params->get('i18n', false);
		$descrFile = trim($params->get('descrFile', ''));
		$processDescrFile = !empty($descrFile);
		$processSubDir = (bool)$params->get('subdir');
		$subdirLevel = intval($params->get('subdirLevel', 0), 10);
		if ($subdirLevel < 1)
			$subdirLevel = true;
		else 
			--$subdirLevel;
		
		$imgNumber = intval($params->get('imgNumber', 0), 10);
		$images = array();
		$sort = self::getSortExpr($params);
		$cwd = getcwd();
		if ($cwd != JPATH_ROOT)
			chdir(JPATH_ROOT);
		else
			$cwd = null;

		foreach ($pathList as $path)
		{
			if (empty($path))
				continue ;
			
			if (@is_file($path))
			{
				$images[] = $path;
				$path = dirname($path);
			}
			else
			{
				$folderImages = AriFolder::files(
					$path,
					'.(jpg|gif|jpeg|png|bmp|JPG|GIF|JPEG|BMP)$', 
					$processSubDir ? $subdirLevel : false,
					true,
					$sort);

				if (is_array($folderImages) && count($folderImages) > 0)
				{
					if ($imgNumber > 0 && count($folderImages) > $imgNumber)
						$folderImages = array_slice($folderImages, 0, $imgNumber);

					$images = array_merge($images, $folderImages);
				}
			}
			
			if ($processDescrFile)
			{
				$folderExtraFields = AriAppUtils::getExtraFieldsFromINI($path, $descrFile, $processSubDir, true, $i18n);
				if (is_array($folderExtraFields) && count($folderExtraFields) > 0)
				{				
					$extraFields = array_merge($extraFields, $folderExtraFields);
				}
			}
		}
		
		if (!is_null($cwd))
			chdir($cwd);
		
		$useThumbs = (bool)$params->get('imglist_useThumbs');
		$cachePath = $useThumbs ? self::getCachePath() : null;
		$thumbPath = $params->get('imglist_thumbPath');
		$thumbWidth = intval($params->get('imglist_thumbWidth'), 10);
		$thumbHeight = intval($params->get('imglist_thumbHeight'), 10);
		$navThumbs = (bool)$params->get('opt_controlNavThumbs');
		$navThumbWidth = intval($params->get('imglist_navThumbWidth'), 10);
		$navThumbHeight = intval($params->get('imglist_navThumbHeight'), 10);
		$navThumbPath = $params->get('imglist_navThumbPath');
		$navCachePath = self::getCachePath();
		$defaultDescr = $params->get('defaultDescription');
		$defLink = $params->get('defaultLink');
		$processDefaultDescr = $defaultDescr && strpos($defaultDescr, '{$') !== false;
        $getImageSizeAvailable = function_exists('getimagesize');
		foreach ($images as $image)
		{
			$originalSlide = $slide = array(
				'src' => $image,
				'image' => str_replace('\\', '/', $image)
			);

			if ($processDescrFile)
			{
				if (isset($extraFields[$image]))
				{
					$slide = array_merge($extraFields[$image], $slide);
				}
				else
				{
					$cleanImage = str_replace('/', '\\', $image);
					if (isset($extraFields[$cleanImage]))
						$slide = array_merge($extraFields[$cleanImage], $slide);
				}
			}
				
			if (empty($slide['description']) && $defaultDescr)
				$slide['description'] = $processDefaultDescr
					? str_replace(
						array('{$fileName}', '{$baseFileName}'), 
						array(basename($image), JFile::stripExt(basename($image))), 
						$defaultDescr)	
					: $defaultDescr;


			if ($useThumbs)
				$slide = self::generateThumbnail($slide, $thumbWidth, $thumbHeight, $thumbPath, $cachePath, $defLink);
            else if ($getImageSizeAvailable)
            {
                $slideDimensions = @getimagesize(JPATH_ROOT . '/' . $image);
                if (!empty($slideDimensions) && count($slideDimensions) > 1)
                {
                    $slide['width'] = $slideDimensions[0];
                    $slide['height'] = $slideDimensions[1];
                }
            }

				
			if ($navThumbs)
			{
				$navThumb = self::generateThumbnail($originalSlide, $navThumbWidth, $navThumbHeight, $navThumbPath, $navCachePath);
				$navThumb['alt'] = !empty($slide['alt']) ? $slide['alt'] : '';
				$slide['nav'] = $navThumb;
			}
			
			
			$slides[] = $slide;
		}

		return $slides;
	}

    public static function prepareSlides($slides, $params)
	{
		$newSlides = array();
		
		$target = $params->get('customLinkTarget');
		if (empty($target))
			$target = $params->get('linkTarget', '_self');
			
		$defLink = $params->get('defaultLink');

		$baseUri = JURI::base(true);
		$lightboxEngine = self::getLightboxEngine($params);
		$lightboxGrouping = (bool)$params->get('lightbox_grouping', true);
		$lightboxGroup = $lightboxGrouping ? uniqid('cc_') : null;
		foreach ($slides as $slide)
		{
			$defSlideLink = str_replace('{$originalImage}', $slide['image'], $defLink);

			if (empty($slide['link']))
                if (!empty($defSlideLink))
				    $slide['link'] = $defSlideLink;
                else if ($target != '_self' || $lightboxEngine)
                    $slide['link'] = $baseUri . '/' . $slide['image'];

            $slideLink = AriUtils::getParam($slide, 'link');
			$isLink = !empty($slideLink);
			$description = isset($slide['description']) ? $slide['description'] : '';
			$altText = isset($slide['alt']) ? $slide['alt'] : '';
		
			$lnkAttrs = null;
			$imgAttrs = array('src' => $baseUri . '/' . $slide['image'], 'alt' => $altText, 'title' => $description, 'class' => 'imageslider-item');
			if (!empty($slide['width'])) $imgAttrs['width'] = $slide['width'];
			if (!empty($slide['height'])) $imgAttrs['height'] = $slide['height'];
			if ($isLink)
			{
				$lnkAttrs = array('href' => $slideLink, 'target' => $target);
				if ($description)
					$lnkAttrs['title'] = $description;

				$skip_lb = false;
				if (!is_null($lightboxEngine) && strpos($slideLink, 'skip_lb') !== false)
				{
					$uri = new JURI($slideLink);
					$skip_lb = (bool)$uri->getVar('skip_lb');
					$uri->delVar('skip_lb');
					$slideLink = $uri->toString();
					$lnkAttrs['href'] = $slideLink;
				}

				if (!$skip_lb && !is_null($lightboxEngine))
					list($lnkAttrs, $imgAttrs) = $lightboxEngine->modifyAttrs($lnkAttrs, $imgAttrs, $lightboxGroup, $params);
				else
				{
					$originalLink = $slideLink;
					if (strpos($originalLink, '_target') !== false)
					{
						$uri = new JURI($originalLink);
						$linkTarget = $uri->getVar('_target');
						if (!is_null($linkTarget))
						{
							$uri->delVar('_target');
							$lnkAttrs['target'] = $linkTarget;
							$lnkAttrs['href'] = $uri->toString();
						}
					}
				}
			}

			$slide['lnkAttrs'] = $lnkAttrs;
			$slide['imgAttrs'] = $imgAttrs;
			$newSlides[] = $slide;
		}

		return $newSlides;
	}

    public static function generateThumbnail($slide, $thumbWidth, $thumbHeight, $thumbPath, $cachePath, $defLink = null)
	{
		$img = $slide['src'];
		$imgUri = $slide['image'];
		$thumbFile = null;
		
		if ($thumbPath)
		{
			$pathInfo = pathinfo($img);
			$thumbImg = $pathInfo['dirname'] . '/' .  str_replace('{$fileName}', $pathInfo['basename'], $thumbPath);
			if (@file_exists(JPATH_ROOT . '/' . $thumbImg) && @is_file(JPATH_ROOT . '/' . $thumbImg))
			{
				$thumbFile = $thumbImg;
			}
		}

		if (is_null($thumbFile))
		{
			$thumbName = AriImageHelper::generateThumbnail(
				JPATH_ROOT . '/' . $img,
				JPATH_ROOT . '/' . $cachePath,
				'ais',
				$thumbWidth,
				$thumbHeight);
			if ($thumbName)
			{
				$thumbFile = $cachePath . '/' . $thumbName;
			}
			else
			{
				$thumbFile = $img;
			}
		}

		$slide['src'] = $thumbFile;
		$slide['image'] = str_replace('\\', '/', $thumbFile);

        if (empty($slide['link']))
			$slide['link'] = empty($defLink) ? $imgUri : $defLink;

		$thumbSize = AriImageHelper::getThumbnailDimension(JPATH_ROOT . '/' . $thumbFile, $thumbWidth, $thumbHeight);
		if (!empty($thumbSize['w'])) $slide['width'] = $thumbSize['w'];
		if (!empty($thumbSize['h'])) $slide['height'] = $thumbSize['h'];
		
		return $slide;
	}

    public static function getCachePath()
	{
        $cacheDir = 'images';
        $extCacheDir = $cacheDir . '/ariimageslider';
        if (@file_exists($extCacheDir) && is_dir($extCacheDir))
        {
            $cacheDir = $extCacheDir;
        }

        return $cacheDir;
	}

    public static function getSortExpr($params)
	{
		$sortBy = $params->get('sortBy');
		if (empty($sortBy) || !in_array($sortBy, array('filename', 'modified', 'random')))
			return null;
			
		return array(
			'sortBy' => $sortBy, 
			'sortDir' => ($params->get('sortDir') == 'desc' ? 'desc' : 'asc')
		);
	}

    public static function getPathList($path)
	{
		$pathList = array();
		if (empty($path))
			return $pathList;

		$pathList = explode("\n", $path);
		array_walk($pathList, array('AriFolder', 'clean'));
		$pathList = array_unique($pathList);
		
		return $pathList;
	}

    public static function getLightboxEngine($params)
	{
		$engine = null;
        $filter = JFilterInput::getInstance();
		$engineName = ucfirst($filter->clean($params->get('lightboxEngine'), 'WORD'));
		if (empty($engineName))
			return null;
		
		AriKernel::import('ImageSlider.Lightbox.' . $engineName);
		
		$className = $engineName . 'ImageSliderEngine';
		if (class_exists($className))
		{
			$engine = new $className();
			if (!$engine->preCheck())
			{
				$engine = null;
			}
		}
		
		return $engine;
	}
}