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

abstract class LogEventType
{
    public const TESTING = 1000;
    public const USER_LOGIN = 1001;
    public const USER_LOGOUT = 1002;
    public const USER_REGISTER = 1003;
    public const LOGIN_FAILURE = 1060;
    public const LOGIN_FAILURE_USERNAME = 1061;
    public const LOGIN_FAILURE_PASSWORD = 1062;
}
