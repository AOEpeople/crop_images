<?php
namespace Aijko\CropImages\ViewHelpers\Be;

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
 * View helper which allows you to create extbase based modules in the style of TYPO3 default modules.
 * Note: This feature is experimental!
 *
 * = Examples =
 *
 * <code title="Simple">
 * <f:be.container>your module content</f:be.container>
 * </code>
 * <output>
 * "your module content" wrapped with proper head & body tags.
 * Default backend CSS styles and JavaScript will be included
 * </output>
 *
 * <code title="All options">
 * <f:be.container pageTitle="foo" enableJumpToUrl="false">your module content</f:be.container>
 * </code>
 * <output>
 * "your module content" wrapped with proper head & body tags.
 * Custom CSS file EXT:your_extension/Resources/Public/styles/backend.css and JavaScript file EXT:your_extension/Resources/Public/scripts/main.js will be loaded
 * </output>
 */
class ContainerViewHelper extends \TYPO3\CMS\Fluid\ViewHelpers\Be\AbstractBackendViewHelper {

	/**
	 * Render start page with template.php and pageTitle
	 *
	 * @param string  $pageTitle title tag of the module. Not required by default, as BE modules are shown in a frame
	 * @param boolean $enableJumpToUrl If TRUE, includes "jumpTpUrl" javascript function required by ActionMenu. Defaults to TRUE
	 * @param array $addCssFiles Custom CSS files to be loaded
	 * @param array $addJsFiles Custom JavaScript file to be loaded
	 * @return string
	 * @see template
	 */
	public function render($pageTitle = '', $enableJumpToUrl = TRUE, $addCssFiles = NULL, $addJsFiles = NULL) {
		$doc = $this->getDocInstance();
		$pageRenderer = $doc->getPageRenderer();
		if ($enableJumpToUrl) {
			$doc->JScode .= '
				<script language="javascript" type="text/javascript">
					script_ended = 0;
					function jumpToUrl(URL)	{
						document.location = URL;
					}
					' . $doc->redirectUrls() . '
				</script>
			';
		}
		if ($addCssFiles !== NULL) {
			foreach ($addCssFiles as $cssFile) {
				$pageRenderer->addCssFile($cssFile);
			}
		}
		if ($addJsFiles !== NULL) {
			foreach ($addJsFiles as $jsFile) {
				$pageRenderer->addJsFile($jsFile);
			}
		}
		$output = $this->renderChildren();
		$output = $doc->startPage($pageTitle) . $output;
		$output .= $doc->endPage();
		return $output;
	}
}

