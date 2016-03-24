<?php

namespace libAllure;

class QueryBuilder {
	private $_fields = array();
	private $verb;
	private $from;
	private $orderBy = array();

	public function __construct($verb = 'SELECT') {
		$verb = strtoupper($verb);
		
		$this->verb = $verb;
	}

	public function orderBy() {
		foreach (func_get_args() as $arg) {
			array_push($this->orderBy, $arg);
		}

		return $this;
	}

	public function from($from) {
		$this->from = $from;

		return $this;
	}

	public function fields() {
		foreach (func_get_args() as $arg) {
			array_push($this->_fields, $arg);
		}

		return $this;
	}

	private function buildFields() {
		return implode(', ', $this->_fields);
	}

	private function buildOrderBy() {
		if (empty($this->orderBy)) {
			return $this->_fields[0];
		} else {
			return implode(', ', $this->orderBy);
		}
	}

	public function build() {
		return $this->verb . ' ' . $this->buildFields() . ' FROM ' . $this->from . ' ORDER BY ' . $this->buildOrderBy();
	}
}

?>
