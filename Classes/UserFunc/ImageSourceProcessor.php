<?php
namespace Aijko\CropImages\UserFunc;

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
class ImageSourceProcessor extends AbstractImageProcessor {

	/**
	 * Modifies the image source
	 * Called preferably as a postUserFunc
	 *
	 * @param string $content Current value of the params
	 * @param array $configuration Additional configuration. Not used here
	 * @return string
	 * @throws \Aijko\CropImages\Exception\CropProcessing
	 */
	public function process($content, $configuration) {

		$this->validateContext();
		$this->notifyObserver();

		// Get the cropping information for the device corresponding to the current source collection item
		$sysReferenceFile = $this->getCurrentReferenceFile();
		if (!$sysReferenceFile) {
			return $content;
		}
		$device = $this->getDevice();
		$responsiveReferenceFile = $this->getReferenceFileService()->getReferenceFileByDevice($sysReferenceFile, $device);
		$responsiveUid = $responsiveReferenceFile->getOriginalFile()->getUid();
		$content = $responsiveUid;
		return $content;
	}

}
?>