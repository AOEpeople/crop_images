<?php

/***************************************************************
 *  Copyright notice
 *
 *  (c) 2013 aijko GmbH <info@aijko.de>
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

use \TYPO3\CMS\Core\Utility\ExtensionManagementUtility;
use \TYPO3\CMS\Core\Utility\VersionNumberUtility;
use \Aijko\CropImages\Utility\EmConfiguration;

if (!defined('TYPO3_MODE')) die ('Access denied.');


if (TYPO3_MODE === 'BE') {

	/**
	 * Registers a Backend Module
	 */
	\TYPO3\CMS\Extbase\Utility\ExtensionUtility::registerModule(
		'Aijko.' . $_EXTKEY,
		'cropmainmodule',	 // Hide module by setting main module to something that does not exist
		'cropper',	// Submodule key
		'',						// Position
		array('Content' => 'list,save,reset,close'),
		array(
			'access' => 'user,group',
			'icon'   => 'EXT:' . $_EXTKEY . '/ext_icon.gif',
			'labels' => 'LLL:EXT:' . $_EXTKEY . '/Resources/Private/Language/locallang_cropper.xlf',
		)
	);
}


/**
 * Add static Typoscript template
 */
ExtensionManagementUtility::addStaticFile($_EXTKEY, 'Configuration/TypoScript', 'Image cropper');


/**
 * Add TCA
 */
$tempColumns = array (
	'tx_cropimages_aspectratio' => array (
		'exclude' => 1,
		'label' => 'LLL:EXT:' . $_EXTKEY . '/Resources/Private/Language/locallang_db.xlf:tx_cropimages_domain_model_content.aspectratio',
		'displayCond' => array(
			'AND' => array(
				'REC:NEW:false',
				'FIELD:tablenames:=:tt_content',
			),
		),
		'config' => array (
			'type' => 'select',
			'items' => array (
				array('-:-', ''),
				array('1:1', '1:1'),
				array('4:3', '4:3'),
				array('13:9', '13:9'),
				array('16:9', '16:9'),
			),
			'minitems' => 1,
			'maxitems' => 1,
		)
	),
	'tx_cropimages_cropvalues' => array(
		'exclude' => 1,
		'displayCond' => array(
			'AND' => array(
				'REC:NEW:false',
				'FIELD:tablenames:=:tt_content',
			),
		),
		'label' => 'LLL:EXT:' . $_EXTKEY . '/Resources/Private/Language/locallang_db.xlf:tx_cropimages_domain_model_content.cropvalues',
		'config' => array (
			'type' => 'user',
			'userFunc' => 'EXT:' . $_EXTKEY . '/Classes/Hooks/Field/Cropvalues.php:Aijko\CropImages\Hooks\Field\Cropvalues->generateField',
		)
	)
);

ExtensionManagementUtility::addTCAcolumns('sys_file_reference', $tempColumns);
ExtensionManagementUtility::addFieldsToPalette('sys_file_reference', 'imageoverlayPalette', '--linebreak--, tx_cropimages_aspectratio, tx_cropimages_cropvalues');
ExtensionManagementUtility::addFieldsToPalette('sys_file_reference', 'basicoverlayPalette', '--linebreak--, tx_cropimages_aspectratio, tx_cropimages_cropvalues');

// Responsive Functionality available in v6.2
$numericVersion = VersionNumberUtility::convertVersionNumberToInteger(VersionNumberUtility::getNumericTypo3Version());
if ($numericVersion >= 6002000) {

	$tempColumns = array (
		'tx_cropimages_responsiveimages' => array (
			'exclude' => 1,
			'label' => 'LLL:EXT:' . $_EXTKEY . '/Resources/Private/Language/locallang_db.xlf:tx_cropimages_domain_model_content.tx_cropimages_responsiveimages',
			'displayCond' => array(
				'AND' => array(
					'REC:NEW:false',
					'FIELD:tablenames:=:tt_content',
				),
			),
			'config' => ExtensionManagementUtility::getFileFieldTCAConfig(
				'image',
				array(
					'appearance' => array(
						'createNewRelationLinkTitle' => 'LLL:EXT:' . $_EXTKEY . '/Resources/Private/Language/locallang_db.xlf:tx_cropimages_domain_model_content.tx_cropimages_responsiveimages.createnew'
					),
					// maxitems is modified by \Aijko\CropImages\Utility\ResponsiveType::addNewResponsiveType
					'maxitems' => 0,
					'foreign_types' => array(
						'0' => array(
							'showitem' => '
							--palette--;;filePalette, tx_cropimages_responsivetype'
						),
						\TYPO3\CMS\Core\Resource\File::FILETYPE_TEXT => array(
							'showitem' => '
							--palette--;;filePalette, tx_cropimages_responsivetype'
						),
						\TYPO3\CMS\Core\Resource\File::FILETYPE_IMAGE => array(
							'showitem' => '
							--palette--;;filePalette, tx_cropimages_responsivetype'
						),
						\TYPO3\CMS\Core\Resource\File::FILETYPE_AUDIO => array(
							'showitem' => '
							--palette--;;filePalette, tx_cropimages_responsivetype'
						),
						\TYPO3\CMS\Core\Resource\File::FILETYPE_VIDEO => array(
							'showitem' => '
							--palette--;;filePalette, tx_cropimages_responsivetype'
						),
						\TYPO3\CMS\Core\Resource\File::FILETYPE_APPLICATION => array(
							'showitem' => '
							--palette--;;filePalette, tx_cropimages_responsivetype'
						)
					),
				),
				$GLOBALS['TYPO3_CONF_VARS']['GFX']['imagefile_ext']),
		),
		'tx_cropimages_responsivetype' => array (
			'exclude' => 1,
			'label' => 'LLL:EXT:' . $_EXTKEY . '/Resources/Private/Language/locallang_db.xlf:tx_cropimages_domain_model_content.tx_cropimages_responsivetype',
			'displayCond' => array(
				'AND' => array(
					'REC:NEW:false',
					'FIELD:tablenames:=:sys_file_reference',
				),
			),
			'config' => array (
				'type' => 'select',
				'items' => array (
					array('', 0),
				),
				'minitems' => 1,
				'maxitems' => 1,
			)
		),
	);

	ExtensionManagementUtility::addTCAcolumns('sys_file_reference', $tempColumns);
	ExtensionManagementUtility::addFieldsToPalette('sys_file_reference', 'imageoverlayPalette', '--linebreak--, tx_cropimages_responsiveimages');
	ExtensionManagementUtility::addFieldsToPalette('sys_file_reference', 'basicoverlayPalette', '--linebreak--, tx_cropimages_responsiveimages');

	// Add the two default responsive types
	if (EmConfiguration::getSetting(EmConfiguration::ENABLE_DEFAULT_RESPONSIVE_TYPES)) {
		\Aijko\CropImages\Utility\ResponsiveType::addNewResponsiveType('Tablet', 1, 'src-tablet,src-tablet-highres');
		\Aijko\CropImages\Utility\ResponsiveType::addNewResponsiveType('Mobile', 2, 'src-phone,src-phone-highres');
	}

}
unset($numericVersion);
?>