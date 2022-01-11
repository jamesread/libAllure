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

class DatabaseStatement extends \PdoStatement
{
    public $dbh;
    private $numRows = null;

    protected function __construct($dbh)
    {
        $this->dbh = $dbh;
        $this->dbh->queryCount++;
        $this->setFetchMode(Database::FM_ASSOC);
    }

    public function fetchRow($fm = Database::FM_ASSOC)
    {
        return $this->fetch($fm);
    }

    public function fetchRowNotNull($fm = Database::FM_ASSOC)
    {
        $result = $this->fetchRow();

        if (empty($result)) {
            throw new \Exception('Row not found. Used query: ' . $this->queryString);
        } else {
            return $result;
        }
    }

    public function numRows()
    {
        if ($this->numRows == null) {
            $sql = 'SELECT found_rows()';
            $result = $this->dbh->query($sql);
            $row = $result->fetchRow();
            $row = current($row);

            $this->numRows = $row;
        }

        return $this->numRows;
    }

    public function execute($inputParams = null)
    {
        parent::execute($inputParams);

        return $this;
    }
}
