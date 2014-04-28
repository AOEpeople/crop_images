<?php
namespace Aijko\CropImages\Service;

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

use \Aijko\CropImages\Utility\ExtConfiguration;
use \Aijko\CropImages\Exception\ProcessingException;

/**
 * @package crop_images
 * @license http://www.gnu.org/licenses/gpl.html GNU General Public License, version 3 or later
 */
class DeviceService {

	/**
	 * @var \Aijko\CropImages\Observer\ImageProcessing
	 * @inject
	 */
	protected $imageObserver;

	/**
	 * Determines the device we are currently rendering for
	 *
	 * @return int
	 * @throws ProcessingException
	 */
	public function getDevice() {
		if (!isset($GLOBALS['TSFE'])) {
			throw new ProcessingException('Can only get source collection in Frontend context.', 1398673667);
		}
		$sourceCollection = array_keys($GLOBALS['TSFE']->tmpl->setup['tt_content.']['image.']['20.']['1.']['sourceCollection.']);
		$currentSourceCollection = NULL;
		$currentIndex = $this->imageObserver->getCurrentIndex();
		if (0 !== $currentIndex) {
			$currentSourceCollection = $sourceCollection[$currentIndex - 1];
			// Remove trailing .
			$currentSourceCollection = substr($currentSourceCollection, 0, -1);
		}
		// Identify which device this source collection item belongs to
		$device = ExtConfiguration::getResponsiveTypeBySourceCollection($currentSourceCollection);
		return $device;
	}
}