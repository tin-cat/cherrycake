<?php

/**
 * UiComponentFormMultilevelSelectAjax
 *
 * @package Cherrycake
 */

namespace Cherrycake\Modules;

/**
 * A Ui component for a meta field formed by multiple selects that are dependent on each other
 *
 * @package Cherrycake
 * @category Classes
 */
class UiComponentFormMultilevelSelectAjax extends \Cherrycake\UiComponent {
	protected $style;
	protected $additionalCssClasses;
	protected $domId;
	protected $isCentered = false;
	protected $isDisabled = false;
	protected $onChange;
	protected $levels;
	protected $actionName; // The action name that will return the Json data to build the selects
	protected $isWrap = false;
	protected $isInnerGap = true;

	protected $saveAjaxUrl;

	protected $dependentCoreModules = [
		"UiComponentJquery",
		"UiComponentJqueryEventUe",
		"UiComponentTooltip",
		"UiComponentFormSelect",
		"UiComponentAjax",
		"UiComponentColumns"
	];

	function addCssAndJavascript() {
		global $e;
		$e->Javascript->addFileToSet("coreUiComponents", "UiComponentFormMultilevelSelectAjax.js");
		return true;
	}

	/**
	 * Builds the HTML of the input. Any setup keys can be given, which will overwrite the ones (if any) given when constructing the object.
	 *
	 * @param array $setup A hash array with the setup keys. Refer to constructor to see what keys are available.
	 */
	function buildHtml($setup = false) {
		global $e;

		$this->setProperties($setup);

		if (!$this->domId)
			$this->domId = uniqid();

		foreach ($this->levels as $levelName => $levelData) {
			$columns[] = ["html" =>
				$e->UiComponentFormSelect->buildHtml([
					"name" => $levelName,
					"title" => $levelData["title"],
					"style" => $levelData["style"]." fullWidth"
				])
			];
		}
		reset($this->levels);

		$r =
			$e->UiComponentColumns->buildHtml([
				"isWrap" => $this->isWrap,
				"domId" => $this->domId,
				"columns" => $columns,
				"isWrap" => $this->isWrap,
				"isInnerGap" => $this->isInnerGap
			]);
		
		$e->HtmlDocument->addInlineJavascript("
			$('#".$this->domId."').UiComponentFormMultilevelSelectAjax({
				levels: ".json_encode($this->levels).",
				getDataAjaxUrl: '".$e->Actions->getAction($this->actionName)->request->buildUrl()."',
				saveAjaxUrl: '".$this->saveAjaxUrl."'
			});
		");

		return $r;
	}
}