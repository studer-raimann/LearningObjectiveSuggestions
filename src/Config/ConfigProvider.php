<?php namespace SRAG\ILIAS\Plugins\LearningObjectiveSuggestions\Config;

/**
 * Class ConfigProvider
 *
 * Provides access to global config data
 *
 * @author  Stefan Wanzenried <sw@studer-raimann.ch>
 * @package SRAG\ILIAS\Plugins\LearningObjectiveSuggestions\Config
 */
class ConfigProvider {
	public function get(string $key): ?string {
		/** @var CourseConfig $config */
		$config = Config::where(array(
			'cfg_key' => $key,
		))->first();
		return ($config) ? $config->getValue() : NULL;
	}
	public function set(string $key, string $value): void {
		$config = Config::where(array(
			'cfg_key' => $key,
		))->first();
		if ($config === NULL) {
			$config = new Config();
			$config->setKey($key);
		}
		$config->setValue($value);
		$config->save();
	}
	public function getCourseRefIds(): array
    {
		return (array)json_decode($this->get('course_ref_ids'), true);
	}
}