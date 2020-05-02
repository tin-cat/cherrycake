<?php

/**
 * UiComponentUploadImage
 *
 * @package Cherrycake
 */

namespace Cherrycake;

/**
 * UiComponentUploadImage
 *
 * A Ui component to upload single images
 *
 * @package Cherrycake
 * @category Classes
 */
class UiComponentUploadImage extends \Cherrycake\UiComponent {
	protected $domId;
	protected $style;
	protected $additionalCssClasses;
	protected $ajaxUrl;
	protected $buttonUploadTitle = false;
	protected $buttonUploadIconName = "upload";
	protected $defaultImageUrl;

	/**
	 * @var array $dependentCoreModules Cherrycake UiComponent names that are required by this module
	 */
	protected $dependentCoreModules = [
		"UiComponentButton",
		"UiComponentAjaxUpload"
	];

	function addCssAndJavascript() {
		global $e;
		$e->Css->addFileToSet("coreUiComponents", "UiComponentUploadImage.css");
		$e->Javascript->addFileToSet("coreUiComponents", "UiComponentUploadImage.js");
	}

	/**
	 * Setup keys:
	 *
	 * * domId: The Dom id for the form element
	 * * style: The additional style for the form
	 * * additionalCssClasses: An array of any additional css classes
	 *
	 * @param array $setup A hash array with the specs
	 * @return string The Html
	 */
	function buildHtml($setup = false) {
		$this->treatParameters($setup, [
			"domId" => ["default" => uniqid()]
		]);

		$this->setProperties($setup);

		global $e;

		$r =
            "<div".
                " id=\"".$this->domId."\"".
				" class=\"".
					"UiComponentUploadImage".
					($this->style ? " ".$this->style : null).
					($this->additionalCssClasses ? " ".(is_array($this->additionalCssClasses) ? implode($this->additionalCssClasses, " ") : $this->additionalCssClasses) : null).
				"\"".
			">".
				"<div class=\"content\">".
					$e->UiComponentButton->buildHtml([
						"additionalCssClasses" => "uploadButton",
						"iconName" => $this->buttonUploadIconName,
						"title" => $this->buttonUploadTitle
					]).
				"</div>".
			"</div>";

		$e->HtmlDocument->addInlineJavascript("$('#".$this->domId."').UiComponentUploadImage(".json_encode([
			"ajaxUrl" => $this->ajaxUrl,
			"defaultImageUrl" => $this->defaultImageUrl
		]).");");

		return $r;
	}
}