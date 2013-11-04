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
class ExtConfiguration {

	/**
	 * Gets the responsive type, based on the source collection
	 *
	 * @param string $sourceCollectionItem
	 * @return int
	 */
	public static function getResponsiveTypeBySourceCollection($sourceCollectionItem) {
		$responsiveType = 0;
		$configuration = self::parseSettings();

		// Find TCA index
		foreach ($configuration['source_collection'] as $tcaIndex => $mappedSourceCollections) {
			if (in_array($sourceCollectionItem, $mappedSourceCollections)) {
				$responsiveType = $tcaIndex;
				break;
			}
		}

		return $responsiveType;
	}

	/**
	 * Adds an item to the source collection
	 *
	 * @param int $responsiveKey
	 * @param string $sourceCollection
	 * @return void
	 */
	public static function addResponsiveTypeToSourceCollection($responsiveKey, $sourceCollection) {
		if (!is_array($GLOBALS['TYPO3_CONF_VARS']['EXTCONF']['crop_images']['source_collection'][$responsiveKey])) {
			$GLOBALS['TYPO3_CONF_VARS']['EXTCONF']['crop_images']['source_collection'][$responsiveKey] = array();
		}
		$GLOBALS['TYPO3_CONF_VARS']['EXTCONF']['crop_images']['source_collection'][$responsiveKey][] = $sourceCollection;
	}

	/**
	 * Parse settings and return it as array
	 *
	 * @return array unserialized extconf settings
	 */
	protected  static function parseSettings() {
		$settings = $GLOBALS['TYPO3_CONF_VARS']['EXTCONF']['crop_images'];

		if (!is_array($settings)) {
			$settings = array();
		}
		return $settings;
	}
}
?>