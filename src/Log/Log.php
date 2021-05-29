<?php

namespace Cherrycake\Log;

/**
 * Stores app-related events in a persistent log as they occur, usually generated by app modules.
 *
 * @package Cherrycake
 * @category AppModules
 */

class Log extends \Cherrycake\Module {
	/**
	 * @var array $config Holds the default configuration for this module
	 */
	protected $config = [
		"databaseProviderName" => "main", // The name of the database provider where the log table is found
		"cacheProviderName" => "engine", // The name of the cache provider that will be used to temporally store events as they happen, to be later added to the database by the JanitorTaskLog
		"cacheKeyUniqueId" => "QueuedLogEvents", // The unique cache key to use when storing events into cache.
		"isQueueInCache" => true // Whether to store events in a buffer using cache for improved performance instead of storing them in the database straightaway.
	];

	/**
	 * @var array $dependentCoreModules Core module names that are required by this module
	 */
	var $dependentCoreModules = [
		"Errors",
		"Database",
		"Cache"
	];

	/**
	 * logEvent
	 *
	 * Logs the given $logEvent
	 *
	 * @param LogEvent $logEvent The LogEvent object to log
	 * @return boolean Whether the event could be logged or not
	 */
	function logEvent($logEvent) {
		return
			$this->getConfig("isQueueInCache")
			?
			$this->queueEventInCache($logEvent)
			:
			$this->store($logEvent);
	}

	/**
	 * queueEventInCache
	 *
	 * Stores the given LogEvent into cache (queues it) in order to be later processed by JanitorTaskLog
	 *
	 * @param LogEvent $logEvent The event to queue
	 * @return boolean Whether the event could be queued or not
	 */
	function queueEventInCache($logEvent) {
		global $e;
		return $e->Cache->{$this->getConfig("cacheProviderName")}->queueRPush($this->getCacheKey(), $logEvent);
	}

	/**
	 * Stores the cached events into the database, should be called periodically, normally via a JanitorTask
	 * @return array A hash array with information items about the flushing
	 */
	function commit() {
		global $e;
		$count = 0;
		while (true) {
			if (!$logEvent = $e->Cache->{$this->getConfig("cacheProviderName")}->queueLPop($this->getCacheKey()))
				break;
			$this->store($logEvent);
			$count ++;
		}

		return [
			true,
			"numberOfFlushedItems" => $count
		];
	}

	/**
	 * Stores the given LogEvent on the database.
	 *
	 * @param $logEvent
	 * @return integer The log event id on the database, false if failed
	 */
	function store($logEvent) {
		return $logEvent->insert();
	}

	/**
	 * getCacheKey
	 *
	 * @return string The cache key to use when retrieveing and storing cache items
	 */
	function getCacheKey() {
		global $e;
		return $e->Cache->buildCacheKey([
			"uniqueId" => $this->getConfig("cacheKeyUniqueId")
		]);
	}
}
