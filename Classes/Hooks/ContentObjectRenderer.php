<?php
namespace Aijko\CropImages\Hooks;

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
class ContentObjectRenderer implements \TYPO3\CMS\Frontend\ContentObject\ContentObjectGetImageResourceHookInterface{

	/**
	 * @var \Aijko\CropImages\Service\CropValues
	 */
	protected $cropValuesService;

	/**
	 * Hooks into the image resource rendering
	 *
	 * @param string $file
	 * @param array $configuration
	 * @param array $imageResource
	 * @param \TYPO3\CMS\Frontend\ContentObject\ContentObjectRenderer $parent
	 * @return array|void
	 */
	public function getImgResourcePostProcess($file, array $configuration, array $imageResource, \TYPO3\CMS\Frontend\ContentObject\ContentObjectRenderer $parent) {

//		\t3lib_utility_Debug::debug($configuration, __LINE__.__FILE__);
//		\t3lib_utility_Debug::debug($parent->data, __LINE__.__FILE__);
//		\t3lib_utility_Debug::debug($file, __LINE__.__FILE__);
//		die;
		return $imageResource;

		$treatIdAsReference = (bool) $configuration['treatIdAsReference'];

		// Process calls only with filled data
		if (!$treatIdAsReference && empty($parent->data)) {
			return $imageResource;
		}

		// Skip records with no images
		if (!$treatIdAsReference && empty($parent->data['image'])) {
			return $imageResource;
		}

		$currentData = $parent->data;
		$table = $parent->getCurrentTable();

		/** @var $fileRepository \TYPO3\CMS\Core\Resource\FileRepository */
		$fileRepository = \TYPO3\CMS\Core\Utility\GeneralUtility::makeInstance('TYPO3\\CMS\\Core\\Resource\\FileRepository');

		/** @var  $sysReferenceFile \TYPO3\CMS\Core\Resource\FileReference */
		$sysReferenceFile = NULL;

		// Get sys_reference files to be able to access the crop values and aspect ration // TODO: improve this
		if ($treatIdAsReference) {

			$sysReferenceFile = $fileRepository->findFileReferenceByUid($file);

		} else if ('tt_content' == $table) {

			$files = $fileRepository->findByRelation($table, 'image', isset($currentData['_LOCALIZED_UID']) ? intval($currentData['_LOCALIZED_UID']) : intval($currentData['uid']));

			if (0 >= count($files)) {
				return $imageResource;
			}

			foreach ($files as $currentFile) {
				$originalFileUid = $currentFile->getOriginalFile()->getUid();

				if ($originalFileUid == $file) {
					$sysReferenceFile = $currentFile;
				}
			}
		}

		if (!$sysReferenceFile) {
			return $imageResource;
		}

		// At this point we have the reference file, so we now access possible crop values
		$cropValues = $this->getCropValuesService()->getCropValuesFromFileReference($sysReferenceFile);

		if (empty($cropValues)) {
			return $imageResource;
		}

		$cropWidth = intval($cropValues['x2'] - $cropValues['x1']);
		$cropHeight = intval($cropValues['y2'] - $cropValues['y1']);

		$x1 = $cropValues['x1'];
		$y1 = $cropValues['y1'];

		// Get processing configuration
		$processingConfiguration = array();
		$processingConfiguration['width'] = isset($configuration['width.']) ? $parent->stdWrap($configuration['width'], $configuration['width.']) : $configuration['width'];
		$processingConfiguration['height'] = isset($configuration['height.']) ? $parent->stdWrap($configuration['height'], $configuration['height.']) : $configuration['height'];
		$processingConfiguration['fileExtension'] = isset($configuration['ext.']) ? $parent->stdWrap($configuration['ext'], $configuration['ext.']) : $configuration['ext'];
		$processingConfiguration['maxWidth'] = isset($configuration['maxW.']) ? intval($parent->stdWrap($configuration['maxW'], $configuration['maxW.'])) : intval($configuration['maxW']);
		$processingConfiguration['maxHeight'] = isset($configuration['maxH.']) ? intval($parent->stdWrap($configuration['maxH'], $configuration['maxH.'])) : intval($configuration['maxH']);
		$processingConfiguration['minWidth'] = isset($configuration['minW.']) ? intval($parent->stdWrap($configuration['minW'], $configuration['minW.'])) : intval($configuration['minW']);
		$processingConfiguration['minHeight'] = isset($configuration['minH.']) ? intval($parent->stdWrap($configuration['minH'], $configuration['minH.'])) : intval($configuration['minH']);

		/** @var  $gifBuilder \TYPO3\CMS\Core\Imaging\GraphicalFunctions */
		$gifBuilder = \TYPO3\CMS\Core\Utility\GeneralUtility::makeInstance('TYPO3\\CMS\\Core\\Imaging\\GraphicalFunctions');

		// Get original image dimensions and adjust them to the target crop dimensions
		$info = $gifBuilder->getImageDimensions($sysReferenceFile->getOriginalFile()->getPublicUrl());
		$info[0] = $cropWidth;
		$info[1] = $cropHeight;

		// Take into account the user configuration
		$data = $gifBuilder->getImageScale($info, $processingConfiguration['width'], $processingConfiguration['height'], $processingConfiguration);

		$targetWidth = $data[0];
		$targetHeight = $data[1];

		// Build our own query and process it
		$cropConfiguration = array();
		$cropConfiguration['additionalParameters'] = ' -crop ' . $cropWidth . 'x' . $cropHeight . '+' . $x1 . '+' . $y1 . ' -geometry ' . $targetWidth . 'x' . $targetHeight;

		\t3lib_utility_Debug::debug($cropConfiguration, __LINE__.__FILE__);


		if ($GLOBALS['TSFE']->config['config']['meaningfulTempFilePrefix']) {
			$cropConfiguration['useTargetFileNameAsPrefix'] = 1;
		}

		$processedFileObject = $sysReferenceFile->getOriginalFile()->process(\TYPO3\CMS\Core\Resource\ProcessedFile::CONTEXT_IMAGECROPSCALEMASK, $cropConfiguration);

		$hash = $processedFileObject->calculateChecksum();

		// store info in the TSFE template cache (kept for backwards compatibility)
		if ($processedFileObject->isProcessed() && !isset($GLOBALS['TSFE']->tmpl->fileCache[$hash])) {
			$GLOBALS['TSFE']->tmpl->fileCache[$hash] = array(
				0 => $processedFileObject->getProperty('width'),
				1 => $processedFileObject->getProperty('height'),
				2 => $processedFileObject->getExtension(),
				3 => $processedFileObject->getPublicUrl(),
				'origFile' => $sysReferenceFile->getOriginalFile()->getPublicUrl(),
				'origFile_mtime' => $sysReferenceFile->getOriginalFile()->getModificationTime(),
				// This is needed by \TYPO3\CMS\Frontend\Imaging\GifBuilder,
				// in order for the setup-array to create a unique filename hash.
				'originalFile' => $sysReferenceFile->getOriginalFile(),
				'processedFile' => $processedFileObject,
				'fileCacheHash' => $hash
			);
		}
		$modifiedImageResource = $GLOBALS['TSFE']->tmpl->fileCache[$hash];

		return $modifiedImageResource;
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