<?php namespace SRAG\ILIAS\Plugins\AutoLearningObjectives\Config;

/**
 * Class ConfigProvider
 *
 * Provides access to global config data
 *
 * @author Stefan Wanzenried <sw@studer-raimann.ch>
 * @package SRAG\ILIAS\Plugins\AutoLearningObjectives\Config
 */
class ConfigProvider {


	/**
	 * @param string $key
	 * @return string
	 */
	public function get($key) {
		/** @var CourseConfig $config */
		$config = Config::where(array(
			'cfg_key' => $key,
		))->first();
		return ($config) ? $config->getValue() : null;
	}

	/**
	 * @param string $key
	 * @param string $value
	 */
	public function set($key, $value) {
		$config = Config::where(array(
			'cfg_key' => $key,
			))->first();
		if ($config === null) {
			$config = new Config();
			$config->setKey($key);
		}
		$config->setValue($value);
		$config->save();
	}

	/**
	 * @return array
	 */
	public function getCourseRefIds() {
		return (array)json_decode($this->get('course_ref_ids'), true);
	}

}