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

class IncludePath
{
    public static function add($path)
    {
        self::addIncludePath($path);
    }

    public static function addIncludePath($path)
    {
        set_include_path(get_include_path() . PATH_SEPARATOR . $path);
    }

    public static function addLibAllure()
    {
        self::add(dirname(realpath(dirname(__FILE__) . '/')));
    }

    public static function get()
    {
        return get_include_path();
    }
}
