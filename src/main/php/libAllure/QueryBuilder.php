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

    private $lastAliasUsed = null;
    private $lastJoinedTable = null;

    public function __construct($verb = 'SELECT')
    {
        $verb = strtoupper($verb);

        $this->verb = $verb;
    }

    public function orderBy(string ...$fields)
    {
        foreach ($fields as $arg) {
            $arg = $this->from['alias'] . '.' . $arg;

            array_push($this->orderBy, $arg);
        }

        return $this;
    }

    public function from(string $tableName, ?string $alias = null, ?string $database = null)
    {
        if ($this->from !== null) {
            throw new \Exception('QB from() already used');
        }

        $alias = $this->buildTableAlias($tableName, $alias, $database);

        $this->from = array(
            'alias' => $alias,
            'table' => $tableName,
            'database' => $database
        );

        $this->lastAliasUsed = $alias;

        return $this;
    }

    public function fields(string|array ...$fields)
    {
        foreach ($fields as $arg) {
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

    /**
    A field "prefix" is the table alias, plus a dot.

    If the field already contains an alias, it is not added again.

    If the alias param is null, the last alias is used.
    */
    protected function addFieldPrefix($field, $alias = null)
    {
        if (strpos($field, '.') === false) {
            if ($alias == null) {
                $alias = $this->lastAliasUsed;
            }

            return $alias . '.' . $field;
        }

        return $field;
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

    public function buildTableAlias($tbl, $alias, $database)
    {
        $existingAliases = array_column($this->joins, 'alias');

        if (isset($this->from)) {
            $existingAliases[] = $this->from['alias'];
        }

        if ($alias == null) {
            for ($i = 0; $i < strlen($tbl); $i++) {
                $alias = $tbl[$i];

                if (!in_array($alias, $existingAliases)) {
                    return $alias;
                }
            }

            throw new \Exception('Unique alias not possible, tbl: ' . $tbl);
        }


        return $alias;
    }

    public function join($tbl, $alias = null, $database = null)
    {
        return $this->leftJoin($tbl, $alias, $database);
    }

    public function leftJoin($tbl, $alias = null, $database = null)
    {
        return $this->joinImpl('LEFT', $tbl, $alias, $database);
    }

    public function joinImpl($direction, $tbl, $alias = null, $database = null)
    {
        $alias = $this->buildTableAlias($tbl, $alias, $database);

        $this->joins[$tbl] = array(
            'direction' => $direction,
            'database' => $database,
            'table' => $tbl,
            'alias' => $alias
        );

        $this->lastJoinedTable = $tbl;
        $this->lastAliasUsed = $alias;

        return $this;
    }

    public function joinedTable($tbl = null)
    {
        $this->lastJoinedTable = $tbl;
        $this->lastAliasUsed = $this->joins[$tbl]['alias'];

        return $this;
    }

    public function onEq($field, $value)
    {
        return $this->on($field, '=', $value);
    }

    public function onFromFieldsEq($fromField, $joinedField)
    {
        return $this->onEq($this->addFieldPrefix($fromField, $this->from['alias']), $this->addFieldPrefix($joinedField));
    }

    public function onGt($field, $value)
    {
        return $this->on($field, '>', $value);
    }

    public function on(string $field, string $operator, string $value)
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
        $this->group = $this->addFieldPrefix($field);

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
            $db = '';

            if (!empty($join['database'])) {
                $db = $join['database'] . '.';
            }


            $ret .= ' ' . $join['direction'] . ' JOIN ' . $db . $join['table'] . ' ' . $join['alias'] . ' ' . $this->buildJoinConditions($join['table']);
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

    /**
     * Builds the actual SQL.
     * @return string The SQL string.
     */
    public function build(): string
    {
        if (!is_array($this->from)) {
            throw new \Exception("From table not specified");
        }

        if (empty($this->fields)) {
            throw new \Exception("No fields specified");
        }

        $ret = $this->verb . ' ';

        $from = $this->from['database'] != null ? $this->from['database'] . '.' . $this->from['table'] : $this->from['table'];

        $ret .= $this->buildFields() . ' FROM ' . $from . ' ';
        $ret .= $this->from['alias'] . $this->buildJoins();
        $ret .= $this->buildWhere();
        $ret .= $this->buildGroup();
        $ret .= ' ORDER BY ' . $this->buildOrderBy();

        return $ret;
    }
}
