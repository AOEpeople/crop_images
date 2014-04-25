<?php
namespace Aijko\CropImages\Aspect;

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

/**
 * @package crop_images
 * @license http://www.gnu.org/licenses/gpl.html GNU General Public License, version 3 or later
 */
class CroppingAspect implements \TYPO3\CMS\Core\SingletonInterface {

	/**
	 * @var \Aijko\CropImages\Service\CropParameterService
	 * @inject
	 */
	protected $cropParameterService;

	/**
	 * Adjusts the image processing configuration for the cropper
	 *
	 * @param \TYPO3\CMS\Frontend\ContentObject\ContentObjectRenderer $pObj
	 * @param \TYPO3\CMS\Core\Resource\FileInterface $originalFile
	 * @param string $file
	 * @param array $fileArray
	 * @param array $references
	 * @return void
	 */
	public function processCropping(\TYPO3\CMS\Frontend\ContentObject\ContentObjectRenderer $pObj,
									\TYPO3\CMS\Core\Resource\FileInterface $originalFile,
									$file,
									array $fileArray,
									array $references) {

		// Do not adjust cropping for non-FAL images
		if (!\TYPO3\CMS\Core\Utility\MathUtility::canBeInterpretedAsInteger($file)) {
			return;
		}

		// Only treat file references. It's the only file that holds cropping information
		if (empty($fileArray['treatIdAsReference'])) {
			return;
		}

		// Set cropping parameters
		$additionalParameters = $this->cropParameterService->getCropParameter($file, $references['processingConfiguration']);
		if (empty($additionalParameters)) {
			return;
		}

		$references['processingConfiguration']['additionalParameters'] = $additionalParameters;

		// Reset all other sizing values
		$dimensionKeys = array('width', 'height', 'maxWidth', 'maxHeight', 'minWidth', 'minHeight');
		foreach ($dimensionKeys as $key) {
			$references['processingConfiguration'][$key] = '';
		}
	}

}