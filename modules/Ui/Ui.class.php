<?php

/**
 * Ui
 *
 * @package Cherrycake
 */

namespace Cherrycake\Modules;

/**
 * Ui
 *
 * Provides User interface creation tools.
 * Works with Ui components, which automatically include themselves the needed files in the "ui" Css and Javascript sets.
 *
 * @package Cherrycake
 * @category Modules
 */
class Ui extends \Cherrycake\Module {
	/**
	 * @var bool $isConfig Sets whether this module has its own configuration file. Defaults to false.
	 */
	protected $isConfigFile = true;
	
	/**
	 * @var array $config Holds the default configuration for this module
	 */
	protected $config = [
		"cssSetName" => "UiComponents", // The name of the Css set (as configured in css.config.php) to which each UiComponent required Css files and Css content will be added, except for those UiComponent classes using their own Css set.
		"cherrycakeCssSetName" => "CoreUiComponents", // The name of the Css set (as configured in css.config.php) to which each Cherrycake UiComponent required Css files and Css content will be added
		"javascriptSetName" => "UiComponents", // The name of the Javascript set (as configured in javascript.config.php) to which each UiComponent required Javascript files and Javascript content will be added, except for those UiComponent classes using their own Javascript set.
		"cherrycakeJavascriptSetName" => "CoreUiComponents" // The name of the Javascript set (as configured in javascript.config.php) to which each Cherrycake UiComponent required Javascript files and Javascript content will be added, except for those UiComponent classes using their own Javascript set.
	];

	/**
	 * @var array $dependentCoreModules Core module names that are required by this module
	 */
	var $dependentCoreModules = [
		"Errors",
		"Css",
		"Javascript"
	];

	/**
	 * var @array $UiComponents The array of UiComponent objects that have been added to Ui
	 */
	public $uiComponents;

	/**
	 * init
	 *
	 * Initializes the module and loads the Ui components
	 *
	 * @return boolean Whether the module has been initted ok
	 */
	function init() {
		if (!parent::init())
			return false;

		global $e;
		$e->loadCoreModuleClass("Ui", "UiComponent");

		// Adds cherrycake Css and Javascript sets for UiComponents
		$e->Css->addSet(
			"CoreUiComponents",
			[
				"directory" => ENGINE_DIR."/res/css/uicomponents"
			]
		);

		$e->Javascript->addSet(
			"CoreUiComponents",
			[
				"directory" => ENGINE_DIR."/res/javascript/uicomponents"
			]
		);

		// Sets up Ui components
		if (is_array($CoreUiComponents = $this->getConfig("CoreUiComponents")))
			foreach($CoreUiComponents as $CoreUiComponent)
				$this->addCoreUiComponent($CoreUiComponent);

		if (is_array($appUiComponents = $this->getConfig("appUiComponents")))
			foreach($appUiComponents as $appUiComponent)
				$this->addAppUiComponent($appUiComponent);

		return true;
	}

	/**
	 * addCoreUiComponent
	 *
	 * Adds a Cherrycake Ui component.
	 *
	 * @param string $UiComponentName The name of the class of the Cherrycake Ui component to add
	 */
	function addCoreUiComponent($uiComponentName) {
		global $e;

		if (!isset($this->uiComponents[$uiComponentName])) {
			$e->loadCoreModuleClass("Ui", $uiComponentName);
			eval("\$this->uiComponents[\"".$uiComponentName."\"] = new \\Cherrycake\\".$uiComponentName."();");
		}
	}

	/**
	 * addAppUiComponent
	 *
	 * Adds an App Ui component.
	 *
	 * @param string $UiComponentName The name of the class of the App Ui component to add
	 */
	function addAppUiComponent($uiComponentName) {
		global $e;

		if (!isset($this->uiComponents[$uiComponentName])) {
			$e->loadAppModuleClass("Ui", $uiComponentName);
			eval("\$this->uiComponents[\"".$uiComponentName."\"] = new \\".$e->getAppNamespace()."\\".$uiComponentName."();");
			$this->uiComponents[$uiComponentName]->init();
		}
	}

	/**
	 * @param string $uiComponentName The name of the UiComponent to check
	 * @return boolean Whether the specified UiComponent has been loaded or not
	 */
	function isUiComponentLoaded($uiComponentName) {
		return isset($this->uiComponents[$uiComponentName]);
	}

	/**
	 * @param string $uiComponentName The name of the UiComponent to get
	 * @return mixed The UiComponent or false if it wasn't loaded
	 */
	function getUiComponent($uiComponentName) {
		if (!$this->isUiComponentLoaded($uiComponentName)) {
			global $e;
			$e->Errors->trigger(\Cherrycake\Modules\ERROR_SYSTEM, [
				"errorDescription" => "The requested UiComponent was not loaded",
				"errorVariables" => [
					"uiComponentName" => $uiComponentName
				]
			]);
			return false;
		} else
			return $this->uiComponents[$uiComponentName];
	}
}