<?php

namespace libAllure;

class QueryBuilder
{
    private $fields = array();
    private $verb;
    private $from;
    private $orderBy = array();
    private $where = array();
    private $group = null;
    private $joins = array();
    private $joinConditions = array();

    private $lastPrefix = null;
    private $lastJoinedTabke = null;

    public function __construct($verb = 'SELECT')
    {
        $verb = strtoupper($verb);

        $this->verb = $verb;
    }

    public function orderBy()
    {
        foreach (func_get_args() as $arg) {
            $arg = $this->addFieldPrefix($arg);

            array_push($this->orderBy, $arg);
        }

        return $this;
    }

    public function from($from, $prefix = null)
    {
        if ($prefix == null) {
            $prefix = substr($from, 0, 1);
        }

        $this->from = array(
            "prefix" => $prefix,
            "table" => $from
        );

        $this->lastPrefix = $prefix;

        return $this;
    }

    public function fields()
    {
        foreach (func_get_args() as $arg) {
            if (is_array($arg)) {
                $field = array(
                    'field' => $arg[0],
                    'alias' => $arg[1]
                );
            } else {
                $field = array(
                    'field' => $arg,
                    'alias' => null
                );
            }

            $field['field'] = $this->addFieldPrefix($field['field']);

            array_push($this->fields, $field);
        }

        return $this;
    }

    protected function addFieldPrefix($field)
    {
        if (strpos($field, '!') !== false) {
            return str_replace('!', '', $field);
        }

        if (strpos($field, '.') === false) {
            $prefix = $this->lastPrefix . '.';
        } else {
            $prefix = '';
        }

        return $prefix . $field;
    }

    public function where($field, $operator, $value)
    {
        $field = $this->addFieldPrefix($field);

        $this->where[] = array(
            'field' => $field,
            'operator' => $operator,
            'value' => $value
        );
    }

    public function whereGt($field, $value)
    {
        $this->where($field, '>', $this->quoteValue($value));
    }

    public function whereEquals($field, $value)
    {
        $this->whereEqualsParam($field, $value);
    }

    public function whereEqualsParam($field, $value)
    {
        $this->where($field, '=', $this->paramName($value));
    }

    public function whereEqualsValue($field, $value)
    {
        $this->where($field, '=', $this->quoteValue($value));
    }

    public function whereNotEquals($field, $value)
    {
        $this->where($field, '!=', $this->quoteValue($value));
    }

    public function whereNotNull($field)
    {
        $this->where($field, 'NOT', 'NULL');
    }

    public function whereLikeValue($field, $value)
    {
        $this->where($field, 'LIKE', $this->quoteValue('%' . $value . '%'));
    }

    public function whereLikeParam($field, $value)
    {
        $this->where($field, 'LIKE', $this->quoteValue('%' . $this->paramName($value) . '%'));
    }

    public function whereSubquery($field, $operator, QueryBuilder $subquery)
    {
        $this->where($field, $operator, '(' . $subquery->build() . ')');
    }

    private function paramName($name)
    {
        if ($name[0] != ':') {
            $name = ':' . $name;
        }

        return $name;
    }

    private function quoteValue($value)
    {
        if (is_numeric($value)) {
            return $value;
        } else {
            return '"' . $value . '"';
        }
    }

    public function join($tbl, $alias = null)
    {
        return $this->leftJoin($tbl, $alias);
    }

    public function leftJoin($tbl, $alias = null)
    {
        return $this->joinImpl('LEFT', $tbl, $alias);
    }

    public function joinImpl($direction, $tbl, $alias = null)
    {
        if ($alias == null) {
            $alias = substr($tbl, 0, 1);
        }

        $this->joins[$tbl] = array(
            'direction' => $direction,
            'table' => $tbl,
            'alias' => $alias
        );

        $this->lastJoinedTable = $tbl;

        return $this;
    }

    public function joinedTable($tbl)
    {
        $this->lastJoinedTable = $tbl;

        return $this;
    }

    public function on($field, $value)
    {
        return $this->onImpl($field, '=', $value);
    }

    public function onGt($field, $value)
    {
        return $this->onImpl($field, '>', $value);
    }

    public function onImpl($field, $operator, $value)
    {
        $this->joinConditions[$this->lastJoinedTable][] = array(
            'field' => $field,
            'operator' => $operator,
            'value' => $value
        );

        return $this;
    }

    public function groupBy($field)
    {
        return $this->group($field);
    }

    public function group($field)
    {
        $this->group = $field;

        return $this;
    }

    private function buildFields()
    {
        $clauses = array();

        foreach ($this->fields as $field) {
            if ($field['alias'] == null) {
                $clauses[] = $field['field'];
            } else {
                $clauses[] = $field['field'] . ' AS ' . $field['alias'];
            }
        }

        return implode(', ', $clauses);
    }

    private function buildOrderBy()
    {
        if (empty($this->orderBy)) {
            return $this->fields[0]['field'];
        } else {
            return implode(', ', $this->orderBy);
        }
    }

    private function buildWhere()
    {
        $ret = '';

        if (count($this->where) > 0) {
            $clauses = array();

            foreach ($this->where as $clause) {
                $clauses[] = $clause['field'] . ' ' . $clause['operator'] . ' ' . $clause['value'];
            }

            $ret = ' WHERE ' . implode(' AND ', $clauses);
        }

        return $ret;
    }

    public function buildJoins()
    {
        $ret = '';

        foreach ($this->joins as $join) {
            $ret .= ' ' . $join['direction'] . ' JOIN ' . $join['table'] . ' ' . $join['alias'] . ' ' . $this->buildJoinConditions($join['table']);
        }

        return $ret;
    }

    public function buildJoinConditions($joinedTable)
    {
        $clauses = array();

        foreach ($this->joinConditions[$joinedTable] as $condition) {
            $clauses[] = $condition['field'] . ' ' . $condition['operator'] . ' ' . $condition['value'];
        }

        return 'ON ' . implode(' AND ', $clauses);
    }

    public function buildGroup()
    {
        if ($this->group == null) {
            return '';
        } else {
            return ' GROUP BY ' . $this->group;
        }
    }

    public function build()
    {
        if (!is_array($this->from)) {
            throw new \Exception("From table not specified");
        }

        if (empty($this->fields)) {
            throw new \Exception("No fields specified");
        }

        $ret = $this->verb . ' ';
        $ret .= $this->buildFields() . ' FROM ' . $this->from['table'] . ' ';
        $ret .= $this->from['prefix'] . $this->buildJoins();
        $ret .= $this->buildWhere();
        $ret .= $this->buildGroup();
        $ret .= ' ORDER BY ' . $this->buildOrderBy();

        return $ret;
    }
}
