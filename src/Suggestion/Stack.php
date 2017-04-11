<?php namespace SRAG\ILIAS\Plugins\LearningObjectiveSuggestions\Suggestion;

/**
 * Class Stack
 * @author Stefan Wanzenried <sw@studer-raimann.ch>
 * @package SRAG\ILIAS\Plugins\LearningObjectiveSuggestions\Suggestion
 */
class Stack {

	protected $elements = array();

	/**
	 * @param mixed $element
	 */
	public function push($element) {
		$this->elements[] = $element;
	}

	/**
	 * @return mixed|null
	 */
	public function pop() {
		return array_pop($this->elements);
	}

	/**
	 * @return mixed|null
	 */
	public function peek() {
		return ($this->isEmpty()) ? null : $this->elements[$this->count() -1];
	}

	/**
	 * @return int
	 */
	public function count() {
		return count($this->elements);
	}

	/**
	 * @return int
	 */
	public function isEmpty() {
		return ($this->count() == 0);
	}
}