<?php

namespace Cherrycake\Log;

/**
 * Base class to represent log events for the Log module
 *
 * @package Cherrycake
 * @category Classes
 */
class LogEvent extends \Cherrycake\Item {
	protected $tableName = "log";
	protected $cacheSpecificPrefix = "Log";

	protected $fields = [
		"id" => ["type" => \Cherrycake\Database\DATABASE_FIELD_TYPE_INTEGER],
		"timestamp" => ["type" => \Cherrycake\Database\DATABASE_FIELD_TYPE_DATETIME],
		"type" => ["type" => \Cherrycake\Database\DATABASE_FIELD_TYPE_STRING],
		"subType" => ["type" => \Cherrycake\Database\DATABASE_FIELD_TYPE_STRING],
		"ip" => ["type" => \Cherrycake\Database\DATABASE_FIELD_TYPE_IP],
		"user_id" => ["type" => \Cherrycake\Database\DATABASE_FIELD_TYPE_INTEGER],
		"outher_id" => ["type" => \Cherrycake\Database\DATABASE_FIELD_TYPE_INTEGER],
		"secondaryOuther_id" => ["type" => \Cherrycake\Database\DATABASE_FIELD_TYPE_INTEGER],
		"additionalData" => ["type" => \Cherrycake\Database\DATABASE_FIELD_TYPE_SERIALIZED]
	];

	/**
	 * @var bool $isUseCurrentLoggedUserId Whether to use current logged user's id (if any) if no "userId" field had been given.
	 */
	protected $isUseCurrentLoggedUserId = false;

	/**
	 * @var bool $isUseCurrentClientIp Whether to use the client's ip if no other ip is specifiec. Defaults to true.
	 */
	protected $isUseCurrentClientIp = true;

	/**
	 * @var string $typeDescription The description of the log event type. Intended to be overloaded.
	 */
	protected $typeDescription;

	/**
	 * @var string $outherIdDescription The description of the outher_id field contents for this log event type. Intended to be overloaded when needed.
	 */
	protected $outherIdDescription;

	/**
	 * @var string $secondaryOutherIdDescription The description of the secondaryOuther_id field contents for this log event type. Intended to be overloaded when needed.
	 */
	protected $secondaryOutherIdDescription;

	/**
	 * Loads the item when no loadMethod has been provided on construction. This is the usual way of creating LogEvent objects for logging
	 *
	 * @param array $data A hash array with the data
	 * @return boolean True on success, false on error
	 */
	function loadInline($data = false) {
		$this->type = get_called_class();
		$this->subType = $data["subType"] ?? false;

		if ($data["ip"] ?? false)
			$this->ip = $data["ip"];
		else
		if ($this->isUseCurrentClientIp)
			$this->ip = $this->getClientIp();

		if ($data["userId"] ?? false)
			$this->user_id = $data["userId"];
		else
		if ($this->isUseCurrentLoggedUserId) {
			global $e;
			$e->loadCoreModule("Login");
			if ($e->Login && $e->Login->isLogged()) {
				$this->user_id = $e->Login->user->id;
			}
		}

		if ($data["timestamp"] ?? false)
			$this->timestamp = $data["timestamp"];
		else
			$this->timestamp = time();

		if (isset($data["outher_id"]))
			$this->outher_id = $data["outher_id"];

		if (isset($data["secondaryOuther_id"]))
			$this->secondaryOuther_id = $data["secondaryOuther_id"];

		if (isset($data["additionalData"]))
			$this->additionalData = $data["additionalData"];

		return parent::loadInline($data);
	}

	/**
	 * getClientIp
	 *
	 * @return string The client's IP
	 */
	function getClientIp() {
		if(isset($_SERVER["HTTP_X_FORWARDED_FOR"]))
			return $_SERVER["HTTP_X_FORWARDED_FOR"];
		else
			return $_SERVER["REMOTE_ADDR"];
	}

	/**
	 * getEventDescription
	 *
	 * Intended to be overloaded.
	 *
	 * @return string A detailed description of the currently loaded event
	 */
	function getEventDescription() {
	}

	/**
	 * debug
	 *
	 * @return array An array containing debug information about this log event
	 */
	function getDebugInfo() {
		return [
			"type" => $this->type,
			"timestamp" => $this->timestamp,
			"typeDescription" => $this->typeDescription,
			"isUseCurrentClientIp" => $this->isUseCurrentClientIp,
			"isUseCurrentLoggedUserId" => $this->isUseCurrentLoggedUserId,
			"outherIdDescription" => $this->outherIdDescription,
			"secondaryOutherIdDescription" => $this->secondaryOutherIdDescription,
			"ip" => $this->ip,
			"userId" => $this->user_id,
			"outherId" => $this->outher_id,
			"secondaryOutherId" => $this->secondaryOuther_id,
			"additional" => $this->additionalData,
			"eventDescription" => $this->eventDescription
		];
	}
}
