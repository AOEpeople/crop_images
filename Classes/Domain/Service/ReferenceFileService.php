<?php
namespace Aijko\CropImages\Domain\Service;

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
class ReferenceFileService implements \TYPO3\CMS\Core\SingletonInterface {

	/**
	 * @var \TYPO3\CMS\Core\Resource\FileRepository
	 */
	protected $fileRepository = NULL;

	/**
	 * @param $table
	 * @param $imageReference
	 * @param $data
	 * @return null|\TYPO3\CMS\Core\Resource\FileReference
	 */
	public function getReferenceFile($table, $imageReference, $data) {
		/** @var  $sysReferenceFile \TYPO3\CMS\Core\Resource\FileReference */
		$sysReferenceFile = NULL;
		// Try shortcut
		if (isset($data['image_fileReferenceUids']) && FALSE === stripos($data['image_fileReferenceUids'], ',')) {
			$referenceUid = (int) $data['image_fileReferenceUids'];
			$sysReferenceFile = $this->getFileRepository()->findFileReferenceByUid($referenceUid);
		} else {
			// Load all for the table
			$files = $this->getFileRepository()->findByRelation($table, 'image', isset($data['_LOCALIZED_UID']) ? intval($data['_LOCALIZED_UID']) : intval($data['uid']));
			if (0 >= count($files)) {
				return NULL;
			}
			foreach ($files as $currentFile) {
				$originalFileUid = $currentFile->getOriginalFile()->getUid();

				if ($originalFileUid == $imageReference) {
					$sysReferenceFile = $currentFile;
				}
			}
		}
		return $sysReferenceFile;
	}

	/**
	 * Gets the correct reference file, given a particular device
	 * Might return the same fileReference that was passed as a parameter, if there are
	 * no responsive images added to that file
	 *
	 * @param \TYPO3\CMS\Core\Resource\FileReference $file
	 * @param int $device
	 * @return \TYPO3\CMS\Core\Resource\FileReference
	 */
	public function getReferenceFileByDevice(\TYPO3\CMS\Core\Resource\FileReference $file, $device) {
		// Default (Desktop) can not be overwritten
		if (0 == $device) {
			return $file;
		}
		// Load all for the table
		$files = $this->getFileRepository()->findByRelation('sys_file_reference', 'image', $file->getUid());
		if (0 >= count($files)) {
			return $file;
		}
		$correctResponsiveFile = $file;
		foreach ($files as $responsiveFile) {
			if ($device == $responsiveFile->getProperty('tx_cropimages_responsivetype')) {
				$correctResponsiveFile = $responsiveFile;
				break;
			}
		}
		return $correctResponsiveFile;
	}

	/**
	 * Gets a file repository
	 *
	 * @return \TYPO3\CMS\Core\Resource\FileRepository
	 */
	protected function getFileRepository() {
		if (NULL === $this->fileRepository) {
			$this->fileRepository = \TYPO3\CMS\Core\Utility\GeneralUtility::makeInstance('TYPO3\\CMS\\Core\\Resource\\FileRepository');
		}
		return $this->fileRepository;
	}

}
?>