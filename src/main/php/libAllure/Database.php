<?php

/*******************************************************************************

  This program is free software; you can redistribute it and/or modify
  it under the terms of the GNU General Public License as published by
  the Free Software Foundation; either version 2 of the License, or
  (at your option) any later version.

  This program is distributed in the hope that it will be useful,
  but WITHOUT ANY WARRANTY; without even the implied warranty of
  MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
  GNU General Public License for more details.

  You should have received a copy of the GNU General Public License
  along with this program; if not, write to the Free Software
  Foundation, Inc., 51 Franklin St, Fifth Floor, Boston, MA  02110-1301  USA

*******************************************************************************/

namespace libAllure;

class Database extends \PDO
{
    public const FM_ORDER = \PDO::FETCH_NUM;
    public const FM_ASSOC = \PDO::FETCH_ASSOC;
    public const FM_OBJECT = \PDO::FETCH_OBJ;

    public const DB_MYSQL_ERR_CONSTRAINT = 23000;

    public $queryCount = 0;

    /**
    @throws PDOException if it cannot connect
    */
    public function __construct($dsn, $username, $password)
    {
        parent::__construct($dsn, $username, $password, array(\PDO::ATTR_PERSISTENT => false));

        $this->setAttribute(\PDO::ATTR_STATEMENT_CLASS, array('\libAllure\DatabaseStatement', array($this)));
        $this->setAttribute(\PDO::ATTR_ERRMODE, \PDO::ERRMODE_EXCEPTION);
        $this->setAttribute(\PDO::MYSQL_ATTR_USE_BUFFERED_QUERY, true);
        //$this->setAttribute(PDO::MYSQL_ATTR_DIRECT_QUERY, true);
    }

    public function prepareSelectById($table, $id)
    {
        $args = func_get_args();
        $table = array_shift($args);
        $id = intval(array_shift($args));

        $fields = implode(array_merge(array('id'), $args), ', ');
        $sql = "SELECT {$fields} FROM {$table} WHERE id = :id";
        $stmt = $this->prepare($sql);
        $stmt->bindValue(':id', $id);

        return $stmt;
    }

    public function fetchById($table, $id)
    {
        $stmt = call_user_func_array(array($this, 'prepareSelectById'), func_get_args());
        $stmt->execute();

        return $stmt->fetchRowNotNull();
    }

    public function escape($s)
    {
        return $s;
    }
}
