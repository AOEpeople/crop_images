<?php

/***************************************************************
 *  Copyright notice
 *
 *  (c) 2014 AIJKO GmbH <info@aijko.com>
 *
 *  All rights reserved
 *
 *  This script is part of the TYPO3 project. The TYPO3 project is
 *  free software; you can redistribute it and/or modify
 *  it under the terms of the GNU General Public License as published by
 *  the Free Software Foundation; either version 3 of the License, or
 *  (at your option) any later version.
 *
 *  The GNU General Public License can be found at
 *  http://www.gnu.org/copyleft/gpl.html.
 *
 *  This script is distributed in the hope that it will be useful,
 *  but WITHOUT ANY WARRANTY; without even the implied warranty of
 *  MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 *  GNU General Public License for more details.
 *
 *  This copyright notice MUST APPEAR in all copies of the script!
 ***************************************************************/

if (!defined ('TYPO3_MODE')) die ('Access denied.');

// Hooks
// $GLOBALS['TYPO3_CONF_VARS']['SC_OPTIONS']['tslib/class.tslib_content.php']['getImgResource'][$_EXTKEY] = 'EXT:' . $_EXTKEY . '/Classes/Hooks/ContentObjectRenderer.php:Aijko\CropImages\Hooks\ContentObjectRenderer';

if (TYPO3_MODE == 'BE') {
	// Hide Module
	\TYPO3\CMS\Core\Utility\ExtensionManagementUtility::addUserTSConfig('options.hideModules := addToList(CropImagesCropmainmodule)');
}

// Add the two default responsive types
if (\Aijko\CropImages\Utility\EmConfiguration::getSetting(\Aijko\CropImages\Utility\EmConfiguration::ENABLE_DEFAULT_RESPONSIVE_TYPES)) {
	\Aijko\CropImages\Utility\ResponsiveType::addNewResponsiveTypeToExtconf(1, 'src-tablet,src-tablet-highres');
	\Aijko\CropImages\Utility\ResponsiveType::addNewResponsiveTypeToExtconf(2, 'src-phone,src-phone-highres');
}

// SignalSlot
$signalSlotDispatcher = \TYPO3\CMS\Core\Utility\GeneralUtility::makeInstance('TYPO3\\CMS\\Extbase\\SignalSlot\\Dispatcher');
$signalSlotDispatcher->connect(
	'TYPO3\\CMS\\Frontend\\ContentObject\\ContentObjectRenderer',
	\TYPO3\CMS\Frontend\ContentObject\ContentObjectRenderer::SIGNAL_PREIMAGEPROCESS,
	'Aijko\\CropImages\\Aspect\\CroppingAspect',
	'processCropping');
