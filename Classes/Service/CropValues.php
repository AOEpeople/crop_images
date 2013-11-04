<?php
namespace Aijko\CropImages\Service;

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
class CropValues {

	/**
	 * Stores the crop values for given x-y values and a file reference
	 *
	 * @param \TYPO3\CMS\Core\Resource\FileReference $fileReference
	 * @param integer $x1
	 * @param integer$x2
	 * @param integer $y1
	 * @param integer $y2
	 * @param integer $deviceKey
	 * @return void
	 */
	public function storeCropValuesForFileReference(\TYPO3\CMS\Core\Resource\FileReference $fileReference, $x1, $x2, $y1, $y2, $deviceKey) {

		// Load data from SQL, as the file reference cropvalues could be outdated.
		$row = \TYPO3\CMS\Backend\Utility\BackendUtility::getRecord('sys_file_reference', $fileReference->getUid(), 'tx_cropimages_cropvalues');
		$xml = $row['tx_cropimages_cropvalues'];

		$blueprintXml = trim('
			<?xml version="1.0" encoding="UTF-8" ?>
			<images><image x1="0" y1="0" x2="0" y2="0" tstamp="0" device="' . $deviceKey . '">' . $fileReference->getName() . '</image></images>
		');

		if (empty($xml)) {
			$xml = $blueprintXml;
		}

		// Get image with the correct device key
		$cropXml = simplexml_load_string($xml);
		$cropData = $cropXml->xpath('//image[@device=' . $deviceKey . ']');

		// Add new element if the current device does not exist in the XML string
		if (empty($cropData)) {
			$values = $cropXml->addChild('image', $fileReference->getName());
		} else {
			$values = $cropData[0];
		}

		// Update cropping information
		$values['x1'] = $x1;
		$values['y1'] = $y1;
		$values['x2'] = $x2;
		$values['y2'] = $y2;
		$values['tstamp'] = time();
		$values['device'] = $deviceKey;

		// Store in database
		$fieldValues = array (
			'tx_cropimages_cropvalues' => $cropXml->asXML()
		);

		$GLOBALS['TYPO3_DB']->exec_UPDATEquery('sys_file_reference', 'uid = ' .$fileReference->getUid(), $fieldValues);
	}

	/**
	 * Resets the crop values for a fiven file reference
	 *
	 * @param \TYPO3\CMS\Core\Resource\FileReference $fileReference
	 * @return void
	 */
	public function resetCropValuesForFileReference(\TYPO3\CMS\Core\Resource\FileReference $fileReference) {

		$fieldValues = array (
			'tx_cropimages_cropvalues' => '',
		);

		$GLOBALS['TYPO3_DB']->exec_UPDATEquery('sys_file_reference', 'uid = ' .$fileReference->getUid(), $fieldValues);
	}

	/**
	 * Returns the crop values from a given file reference
	 *
	 * @param \TYPO3\CMS\Core\Resource\FileReference $fileReference
	 * @param integer $deviceKey
	 * @return array
	 */
	public function getCropValuesFromFileReference(\TYPO3\CMS\Core\Resource\FileReference $fileReference, $deviceKey) {
		$cropData = $fileReference->getProperty('tx_cropimages_cropvalues');
		$currentCropValues = array();

		$cropXml = @simplexml_load_string(html_entity_decode($cropData), 'SimpleXMLElement', LIBXML_NOCDATA);

		if (!$cropXml) {
			return $currentCropValues;
		}

		$cropData = $cropXml->xpath('//image[@device=' . $deviceKey . ']');
		$cropValues = $cropData[0];

		if (!$cropValues) {
			return $currentCropValues;
		}

		$currentCropValues['x1'] = (int) $cropValues['x1'];
		$currentCropValues['x2'] = (int) $cropValues['x2'];
		$currentCropValues['y1'] = (int) $cropValues['y1'];
		$currentCropValues['y2'] = (int) $cropValues['y2'];

		return $currentCropValues;
	}

	/**
	 * Gets the default crop values for an image, based on the desired ratio
	 *
	 * @see \Aijko\CropImages\Controller\ContentController
	 *
	 * @param \TYPO3\CMS\Core\Resource\FileReference $fileReference
	 * @return array
	 */
	public function getDefaultCropValuesFromFileReference(\TYPO3\CMS\Core\Resource\FileReference $fileReference) {
		$defaultCropValues = array();

		$aspectRatio = $fileReference->getProperty('tx_cropimages_aspectratio');
		$originalWidth = $fileReference->getOriginalFile()->getProperty('width');
		$originalHeight = $fileReference->getOriginalFile()->getProperty('height');

		// Determine a definitive aspect ratio, even if none is given
		$ratioParts = array();

		if (empty($aspectRatio)) {
			$ratioParts[0] = $originalWidth;
			$ratioParts[1] = $originalHeight;
		} else {
			$ratioParts = \t3lib_div::trimExplode(':', $aspectRatio, TRUE, 2);
		}

		// Orientation
		$orientation = ($originalWidth > $originalHeight) ? 'landscape' : 'portrait';

		if (intval($originalHeight * ($ratioParts[0] / $ratioParts[1])) > $originalWidth) {
			$orientation = 'portrait';
		}

		// Set defaults
		if ($orientation == 'landscape') {
			$cWidth = intval($originalHeight * ($ratioParts[0] / $ratioParts[1]));

			if ($cWidth == 0) {
				$cWidth = $originalWidth;
			}

			$defaultCropValues['x1'] = intval($originalWidth / 2 - $cWidth / 2);
			$defaultCropValues['y1'] = 0;
			$defaultCropValues['x2'] = $originalWidth - $defaultCropValues['x1'];
			$defaultCropValues['y2'] = $originalHeight;
		} else if ($orientation == 'portrait') {

			$cHeight = intval($originalWidth * ($ratioParts[1] / $ratioParts[0]));

			if ($cHeight == 0) {
				$cHeight = $originalHeight;
			}

			$defaultCropValues['x1'] = 0;
			$defaultCropValues['y1'] = intval($originalHeight / 2 - $cHeight / 2);
			$defaultCropValues['x2'] = $originalWidth;
			$defaultCropValues['y2'] = $originalHeight - $defaultCropValues['y1'];
		}

		return $defaultCropValues;
	}

}

?>