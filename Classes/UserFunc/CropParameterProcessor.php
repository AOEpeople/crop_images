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
class CropParameterProcessor extends AbstractImageProcessor {

	/**
	 * @var int
	 */
	protected static $lastModifiedUid = 0;

	/**
	 * @var int
	 */
	protected static $currentIndex = 0;

	/**
	 * @var \Aijko\CropImages\Service\CropValues
	 */
	protected $cropValuesService;

	/**
	 * Modifies the crop parameters for an image/textpic
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

		$device = $this->getDevice();

		// Get the cropping information for the device corresponding to the current source collection item
		$sysReferenceFile = $this->getCurrentReferenceFile();
		if (!$sysReferenceFile) {
			return $content;
		}
		$device = $this->getDevice();
		$responsiveReferenceFile = $this->getReferenceFileService()->getReferenceFileByDevice($sysReferenceFile, $device);

		// At this point we have the reference file, so we now access possible crop values
		$cropValues = $this->getCropValuesService()->getCropValuesFromFileReference($responsiveReferenceFile, $device);
		if (empty($cropValues)) {
			return $content;
		}

		$cropWidth = intval($cropValues['x2'] - $cropValues['x1']);
		$cropHeight = intval($cropValues['y2'] - $cropValues['y1']);

		$x1 = $cropValues['x1'];
		$y1 = $cropValues['y1'];

		/** @var  $gifBuilder \TYPO3\CMS\Core\Imaging\GraphicalFunctions */
		$gifBuilder = \TYPO3\CMS\Core\Utility\GeneralUtility::makeInstance('TYPO3\\CMS\\Core\\Imaging\\GraphicalFunctions');

		// Get original image dimensions and adjust them to the target crop dimensions
		$info = $gifBuilder->getImageDimensions($responsiveReferenceFile->getOriginalFile()->getPublicUrl());
		$info[0] = $cropWidth;
		$info[1] = $cropHeight;

		// Take into account the user configuration
		$data = $gifBuilder->getImageScale($info, 0, 0, array());

		$targetWidth = $data[0];
		$targetHeight = $data[1];

		// TODO: cropping is not as it used to be. This is probably a problem. We might have to unset width/height and everything elsewhere, and add it here.
		$cropCommand = ' -crop ' . $cropWidth . 'x' . $cropHeight . '+' . $x1 . '+' . $y1 . ' -geometry ' . $targetWidth . 'x' . $targetHeight;
		$content = $cropCommand;

		return $content;
	}

	/**
	 * Gets the crop values service
	 *
	 * @return \Aijko\CropImages\Service\CropValues
	 */
	protected function getCropValuesService() {
		if (empty($this->cropValuesService)) {
			$this->cropValuesService = \TYPO3\CMS\Core\Utility\GeneralUtility::makeInstance('Aijko\\CropImages\\Service\\CropValues');
		}
		return $this->cropValuesService;
	}

}

?>