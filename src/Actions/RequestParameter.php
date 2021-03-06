<?php

namespace Cherrycake\Actions;

/**
 * RequestParameter
 *
 * A class that represents a parameter passed to a Request via Get or Post
 *
 * @package Cherrycake
 * @category Classes
 */
class RequestParameter {
	private $type;
	public $name = false;
	private $value = null;
	private $securityRules = false;
	private $filters = false;

	/**
	 * RequestParameter
	 *
	 * Constructor
	 */
	function __construct($setup) {
		$this->type = $setup["type"];
		$this->name = $setup["name"];

		if (isset($setup["value"]))
			$this->setValue($setup["value"]);

		if (isset($setup["securityRules"]))
			$this->securityRules = $setup["securityRules"];

		if (isset($setup["filters"]))
			$this->filters = $setup["filters"];
	}

	/**
	 * retrieveValue
	 */
	function retrieveValue() {
		global $e;
		switch ($this->type) {
			case \Cherrycake\REQUEST_PARAMETER_TYPE_GET:
			case \Cherrycake\REQUEST_PARAMETER_TYPE_CLI:
				if (isset($_GET[$this->name]))
					$this->setValue($_GET[$this->name]);
				break;
			case \Cherrycake\REQUEST_PARAMETER_TYPE_POST:
				if (isset($_POST[$this->name]))
					$this->setValue($_POST[$this->name]);
				break;
			case \Cherrycake\REQUEST_PARAMETER_TYPE_FILE:
				if (isset($_FILES[$this->name]))
					$this->setValue($_FILES[$this->name]);
				break;
		}
	}

	/**
	 * Should be called only after calling retrieveValue
	 * @return Boolean Whether this parameter has been received or not
	 */
	function isReceived() {
		return !is_null($this->value);
	}

	/**
	 * @return mixed Returns the value received for this parameter after applying the proper filters
	 */
	function getValue() {
		global $e;
		if (!$this->isReceived())
			return null;
		return
			$this->type == \Cherrycake\REQUEST_PARAMETER_TYPE_FILE
			?
			$this->value
			:
			$e->Security->filterValue($this->value, $this->filters);
	}

	/**
	 * Sets the value for this parameter
	 * @param mixed $value The value
	 */
	function setValue($value) {
		$this->value = $value;
	}

	/**
	 * checkValueSecurity
	 *
	 * Checks this parameter's value against its configured security rules (and/or the Security defaulted rules)
	 *
	 * @return Result A Result object, like Security::checkValue
	 */
	function checkValueSecurity() {
		global $e;
		return
			$this->type == \Cherrycake\REQUEST_PARAMETER_TYPE_FILE
			?
			$e->Security->checkFile($this->value, $this->securityRules)
			:
			$e->Security->checkValue($this->value, $this->securityRules);
	}

	/**
	 * @return array Status information
	 */
	function getStatus() {
		$r["brief"] = $this->name."=".($this->value ?? "none");
		$r["name"] = $this->name ?? "unnamed";
		$r["value"] = $this->value ?? "none";
		if ($this->securityRules) {
			foreach ($this->securityRules as $securityRule)
				$r["securityRules"][] = $securityRule;
			reset($this->securityRules);
		}
		if ($this->filters) {
			foreach ($this->filters as $filter)
				$r["filters"][] = $filter;
			reset($this->filters);
		}
		return $r;
	}
}
