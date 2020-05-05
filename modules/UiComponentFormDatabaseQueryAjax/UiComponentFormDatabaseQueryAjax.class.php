<?php

/**
 * UiComponentFormDatabaseQueryAjax
 *
 * @package Cherrycake
 */

namespace Cherrycake;

/**
 * UiComponentFormDatabaseQueryAjax
 *
 * A Ui component for form selects
 *
 * @package Cherrycake
 * @category Classes
 */
class UiComponentFormDatabaseQueryAjax extends \Cherrycake\UiComponent {
	protected $style;
	protected $additionalCssClasses;
	protected $domId;
	protected $title;
	protected $name;
	protected $value;
	protected $isDisabled = false;
	protected $isAutoFocus;
	protected $onChange;

	protected $selectionStyle = UICOMPONENTFORMDATABASEQUERY_SELECTION_STYLE_SELECT;
	protected $querySql;
	protected $queryFields = false;
	protected $queryCacheTtl;
	protected $queryCacheKeyNamingOptions;
	protected $valueFieldName;
	protected $titleFieldName;

	protected $saveAjaxUrl;
	protected $saveAjaxKey = false;

	protected $isConfigFile = true;

	protected $config = [
		"cacheProviderName" => "engine",
		"cacheDefaultTtl" => \Cherrycake\CACHE_TTL_NORMAL,
		"databaseProviderName" => false
	];

	protected $dependentCoreModules = [
		"Database",
		"UiComponentFormSelectAjax",
		"UiComponentFormRadiosAjax"
	];

	/**
	 * Builds the HTML of the input. Any setup keys can be given, which will overwrite the ones (if any) given when constructing the object.
	 *
	 * @param array $setup A hash array with the setup keys. Refer to constructor to see what keys are available.
	 */
	function buildHtml($setup = false) {
		if (is_array($setup))
			foreach ($setup as $key => $value)
				$this->$key = $value;
		
		global $e;
		if (!$result = $e->Database->{$this->getConfig("databaseProviderName")}->prepareAndExecuteCache(
			$this->querySql,
			$this->queryFields,
			$this->queryCacheTtl ? $this->queryCacheTtl : $this->getConfig("cacheDefaultTtl"),
			$this->queryCacheKeyNamingOptions,
			$this->getConfig("cacheProviderName"),
			false
		))
			return false;

		if ($result) {
			while ($row = $result->getRow()) {
				$this->items[$row->getField($this->valueFieldName)] = $row->getField($this->titleFieldName);
			}
		}
		
		switch ($this->selectionStyle) {
			case UICOMPONENTFORMDATABASEQUERY_SELECTION_STYLE_SELECT:
				return $e->UiComponentFormSelectAjax->buildHtml([
					"name" => $this->name,
					"title" => $this->title,
					"style" => $this->style,
					"additionalCssClasses" => $this->additionalCssClasses,
					"domId" => $this->domId,
					"items" => $this->items,
					"value" => $this->value,
					"saveAjaxUrl" => $this->saveAjaxUrl,
					"saveAjaxKey" => $this->saveAjaxKey
				]);
				break;
			case UICOMPONENTFORMDATABASEQUERY_SELECTION_STYLE_RADIOS:
				return $e->UiComponentFormRadiosAjax->buildHtml([
					"name" => $this->name,
					"title" => $this->title,
					"style" => $this->style,
					"additionalCssClasses" => $this->additionalCssClasses,
					"domId" => $this->domId,
					"items" => $this->items,
					"value" => $this->value,
					"saveAjaxUrl" => $this->saveAjaxUrl,
					"saveAjaxKey" => $this->saveAjaxKey
				]);
				break;
		}
	}
}