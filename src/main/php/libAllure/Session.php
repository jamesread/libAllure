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

use libAllure\Logger;
use libAllure\AuthBackend;
use libAllure\Database;
use libAllure\User;

class Session
{
    private static $sessionName = '';
    private static $cookieLifetime = 0; // 0 = until browser is closed
    public static $cookieDomain = null;
    public static $cookieSecure = false;
    public static $cookieHttpOnly = true;

    // static copy of $_SESSION['user']
    private static $user;

    protected function __construct()
    {
    }

    public static function hasPriv($p)
    {
        if (!self::isLoggedIn()) {
            return false;
        }

        return self::getUser()->hasPriv($p);
    }

    public static function requirePriv($ident)
    {
        if (!self::hasPriv($ident)) {
            throw new \Exception();
        }
    }

    /**
     * @returns User
     */
    public static function getUser()
    {
        if (!self::isLoggedIn()) {
            throw new \Exception('User is not yet logged in.');
        }

        if ($_SESSION['user'] instanceof \libAllure\User) {
            return $_SESSION['user'];
        } else {
            throw new \Exception('Your session is probably corrupted, could not unpack Session::user');
        }
    }

    public static function performLogin($username, $againstField = 'username')
    {
        session_regenerate_id();

        // Create account if it does not exist.
        self::checkLocalAccount($username, $againstField);

        // Construct the user object and store it in the session
        User::$uniqueField = $againstField;
        $user = \libAllure\User::getUser($username);
        $_SESSION['user'] = $user;
        $_SESSION['username'] = $username;

        $now = new \DateTime();
        $now = $now->format('Y-m-d H:s');

        $sql = 'UPDATE users SET lastLogin = :now WHERE id = :id LIMIT 1';
        $stmt = DatabaseFactory::getInstance()->prepare($sql);
        $stmt->bindValue(':now', $now);
        $stmt->bindValue(':id', $user->getId());
        $stmt->execute();
    }

    public static function checkCredentials($username, $password)
    {
        $credCheck = AuthBackend::getBackend()->checkCredentials($username, $password);

        if ($credCheck) {
            self::performLogin($username);

            Logger::messageDebug('Sucessful login for: ' . $username, 'USER_LOGIN');
            return true;
        } else {
            Logger::messageDebug('Login failed for: ' . $username, 'USER_LOGIN_FAILURE');
            return false;
        }
    }

    private static function checkLocalAccount($identifier, $field)
    {
        $sql = 'SELECT username FROM `users` WHERE `' . $field . '` = :identifier LIMIT 1';
        $stmt = DatabaseFactory::getInstance()->prepare($sql);
        $stmt->bindValue(':identifier', $identifier);
        $stmt->execute();

        if ($stmt->numRows() >= 1) {
            // This user has a local account
        } else {
            // Create a local account for this user.
            $sql = 'INSERT INTO users (' . $field . ', `group`) VALUES (:identifier, 1) ';
            $stmt = DatabaseFactory::getInstance()->prepare($sql);
            $stmt->bindValue(':identifier', $identifier);
            $stmt->execute();
        }
    }

    public static function setCookieLifetimeInSeconds($cookieLifetime)
    {
        self::$cookieLifetime = $cookieLifetime;
    }

    public static function start()
    {
        if (!(DatabaseFactory::getInstance() instanceof Database)) {
            throw new \Exception('Session cannot be started without a valid database instance registered in the DatabaseFactory.');
        }

        if (!empty(self::$sessionName)) {
            session_name(self::$sessionName);
        }

        session_set_cookie_params(self::$cookieLifetime, '/', self::$cookieDomain, self::$cookieSecure, self::$cookieHttpOnly);
        session_start();
    }

    public static function logout()
    {
        Logger::messageNormal('Logout: ' . self::getUser()->getUsername(), 'USER_LOGOUT');

        session_unset();
        session_regenerate_id(true);
    }

    public static function isLoggedIn()
    {
        return (isset($_SESSION['username']));
    }

    public static function setSessionName($s)
    {
        self::$sessionName = $s;
    }
}
