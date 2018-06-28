<?php

namespace SRAG\ILIAS\Plugins\LearningObjectiveSuggestions\Config;

/**
 * Class Config
 *
 * @author  Stefan Wanzenried <sw@studer-raimann.ch>
 * @package SRAG\ILIAS\Plugins\LearningObjectiveSuggestions\Config
 */
class Config extends \ActiveRecord {

	const TABLE_NAME = "alo_config";


	/**
	 * @return string
	 */
	public function getConnectorContainerName() {
		return self::TABLE_NAME;
	}


	/**
	 * @return string
	 * @deprecated
	 */
	public static function returnDbTableName() {
		return self::TABLE_NAME;
	}


	/**
	 * @var int
	 *
	 * @db_has_field    true
	 * @db_fieldtype    integer
	 * @db_length       8
	 * @db_is_primary   true
	 * @db_sequence     true
	 */
	protected $id;
	/**
	 * @var string
	 *
	 * @db_has_field    true
	 * @db_fieldtype    text
	 * @db_length       64
	 */
	protected $cfg_key;
	/**
	 * @var string
	 *
	 * @db_has_field    true
	 * @db_fieldtype    clob
	 */
	protected $value;


	/**
	 * @return int
	 */
	public function getId() {
		return $this->id;
	}


	/**
	 * @return string
	 */
	public function getKey() {
		return $this->cfg_key;
	}


	/**
	 * @param string $key
	 */
	public function setKey($key) {
		$this->cfg_key = $key;
	}


	/**
	 * @return string
	 */
	public function getValue() {
		return $this->value;
	}


	/**
	 * @param string $value
	 */
	public function setValue($value) {
		$this->value = $value;
	}
}
