<?php
namespace Aijko\CropImages\Utility;

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

/**
 * @package crop_images
 * @license http://www.gnu.org/licenses/gpl.html GNU General Public License, version 3 or later
 */
class ResponsiveType {

	const TYPE_FIELD = 'tx_cropimages_responsivetype';
	const TYPE_TABLE = 'sys_file_reference';

	/**
	 * Adds a new responsive type
	 * Allows the new type to be selected by new responsive images and maps the type to the frontend
	 * by aligning it with a sourceCollection item, making sure the correct image can be selected.
	 *
	 * @param string $typeName Label for the Type. Can be an LLL-type path to a label
	 * @param int $index Index to be used as the key for the item in the <select> field
	 * @param string $correspondingSourceCollection Comma-separated list of entries in the sourceCollection, see
	 * 		  http://docs.typo3.org/typo3cms/TyposcriptReference/latest/ContentObjects/Image/Index.html?highlight=sourcecollection
	 * 		  Determines the mapping between the responsive type cropping and the FE rendering.
	 * @return void
	 */
	public static function addNewResponsiveType($typeName, $index, $correspondingSourceCollection = '') {
		// Add type name to TCA
		\TYPO3\CMS\Core\Utility\ExtensionManagementUtility::addTcaSelectItem(self::TYPE_TABLE, self::TYPE_FIELD, array($typeName, $index));

		// Modify max items of responsive images
		$itemCount = count($GLOBALS['TCA'][self::TYPE_TABLE]['columns'][self::TYPE_FIELD]['config']['items']);
		$GLOBALS['TCA'][self::TYPE_TABLE]['columns']['tx_cropimages_responsiveimages']['config']['maxitems'] = $itemCount - 1;

		$sourceCollectionItems = \TYPO3\CMS\Core\Utility\GeneralUtility::trimExplode(',', $correspondingSourceCollection);

		// Add source collection matching to the extconf
		foreach ($sourceCollectionItems as $item) {
			\Aijko\CropImages\Utility\ExtConfiguration::addResponsiveTypeToSourceCollection($index, $item);
		}
	}

	/**
	 * Get all responsive types
	 *
	 * @return array
	 */
	public static function getAllResponsiveTypes() {
		$items = $GLOBALS['TCA'][self::TYPE_TABLE]['columns'][self::TYPE_FIELD]['config']['items'];
		$types = array();
		foreach ($items as $itemConfig) {
			$types[$itemConfig[1]] = $itemConfig[0];
		}
		return $types;
	}
}
?>