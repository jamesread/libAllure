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

class User
{
    private $privs = array();
    private $usergroups = array();
    private $data = array();
    private $username;

    public static $uniqueField = 'username';

    private function __construct($username)
    {
        $this->username = $username;
        $this->getData('username', false);
        $this->updateUsergroups();
        $this->updatePrivileges();
    }

    public function requirePriv($priv)
    {
        if (!$this->hasPriv($priv)) {
            throw new \Exception($priv);
        }
    }

    public static function getUserById($id)
    {
        $id = intval($id);

        $sql = 'SELECT u.* FROM users u WHERE id = :id LIMIT 1';
        $stmt = DatabaseFactory::getInstance()->prepare($sql);
        $stmt->bindValue(':id', $id);
        $stmt->execute();

        if ($stmt->numRows() == 0) {
            throw new \libAllure\exceptions\UserNotFoundException();
        } else {
            $result = $stmt->fetchRow();

            return new User($result['username']);
        }
    }

    public function getManager()
    {
        if (isset($this->data['manager'])) {
            return $this->data['manager'];
        } else {
            throw new \Exception('Manager not set.');
        }
    }

    public static function getUser($username)
    {
        return new User($username);
    }

    private static function grantPermissionToUid(string $permissionName, int $uid): bool
    {
        $sql = 'SELECT `id` FROM permissions WHERE `key` = := :permissionName LIMIT 1';
        $stmt = DatabaseFactory::getInstance()->prepare($sql);
        $stmt->execute([
            'permissionName' => $permissionName
        ]);

        $permission = $stmt->fetchRow();

        if (!$permission) {
            return false;
        } else {
            $sql = 'INSERT INTO privileges_u (user, permission) VALUES (:user, :permission)';
            $stmt = DatabaseFactory::getInstance()->prepare($sql);
            $stmt->execute([
                ':user' => $uid,
                ':permission' => $permission['id'],
            ]);

            return true;
        }
    }

    private function getPrivilegesFromSupplimentaryGroups(): array
    {
        $ret = [];

        $sql = <<<SQL
SELECT
   u.id,
   u.username,
   gm.user,
   g.id AS groupId,
   g.title AS groupTitle,
   p.key,
   p.description
FROM
   permissions p,
   privileges_g gp,
   groups g,
   group_memberships gm,
   users u

WHERE
   gm.`user` = u.id AND
   gm.`group` = gp.`group` AND
   gp.`group` = g.id AND
   gp.permission = p.id AND
   u.id = :uid
SQL;

        $stmt = DatabaseFactory::getInstance()->prepare($sql);
        $stmt->execute([
            'uid' => $this->getId(),
        ]);

        foreach ($stmt->fetchAll() as $priv) {
            if ($priv['description'] == '') {
                $priv['description'] = '???';
            }

            $priv['source'] = 'Group';
            $priv['sourceTitle'] = $priv['groupTitle'];
            $priv['sourceId'] = $priv['groupId'];

            $ret[$priv['key']] = $priv;
        }

        return $ret;
    }

    private function getPrivilegesFromPrimaryGroup(): array
    {
        $ret = [];

        $sql = 'SELECT distinct p.key, p.description, u.username as userUsername, u.id as userId, g.id groupId, g.title groupTitle FROM permissions p, users u, groups g, privileges_g gp WHERE u.group = g.id AND gp.`group` = g.id AND gp.permission = p.id AND u.id = :uid ';
        $stmt = DatabaseFactory::getInstance()->prepare($sql);
        $stmt->execute([
            'uid' => $this->getId(),
        ]);

        foreach ($stmt->fetchAll() as $priv) {
            if ($priv['description'] == '') {
                $priv['description'] = '???';
            }

            $priv['source'] = 'Group';
            $priv['sourceTitle'] = $priv['groupTitle'];
            $priv['sourceId'] = $priv['groupId'];

            $ret[$priv['key']] = $priv;
        }

        return $ret;
    }

    private function getPrivilegesFromUser(): array
    {
        $ret = [];

        $sql = 'SELECT distinct p.key, p.description, u.username as userUsername, u.id as userId FROM permissions p, privileges_u up, users u WHERE up.user = u.id AND up.permission = p.id AND u.id = :uid ';
        $stmt = DatabaseFactory::getInstance()->prepare($sql);
        $stmt->execute([
            'uid' => $this->getId(),
        ]);

        foreach ($stmt->fetchAll() as $priv) {
            if ($priv['description'] == '') {
                $priv['description'] = '???';
            }

            $priv['source'] = 'User';
            $priv['sourceTitle'] = $priv['userUsername'];
            $priv['sourceId'] = $priv['userId'];

            $ret[$priv['key']] = $priv;
        }

        return $ret;
    }

    public function updatePrivileges()
    {
        $this->privs = array_merge(
            $this->getPrivilegesFromSupplimentaryGroups(),
            $this->getPrivilegesFromPrimaryGroup(),
            $this->getPrivilegesFromUser(),
        );
    }

    public function getPrivs()
    {
        return $this->privs;
    }

    public function hasPriv($ident)
    {
        if (!is_string($ident)) {
            throw new \Exception('Priv ident must be a string, passed to User::hasPriv');
        }

        return (array_key_exists($ident, $this->privs) || array_key_exists('SUPERUSER', $this->privs)) !== false;
    }

    public function getPriv($ident)
    {
        if ($this->hasPriv($ident)) {
            return $ident;
        } else {
            throw new \Exception('Trying to get a priv that the user does not have (' . $ident . ')');
        }
    }

    public function getUsergroups()
    {
        if (sizeof($this->usergroups) == 0) {
            $this->updateUsergroups();
        }

        return $this->usergroups;
    }

    public function updateUsergroups()
    {
        $this->usergroups = array();

        $sql = 'SELECT g.*, "primary" AS type FROM groups g, users u WHERE u.group = g.id AND u.id = :id LIMIT 1';
        $stmt = DatabaseFactory::getInstance()->prepare($sql);
        $stmt->bindValue(':id', $this->getId());
        $stmt->execute();

        $this->usergroups['primary'] = $stmt->fetchRow();

        $sql = 'SELECT g.*, "supplimentary" AS type FROM group_memberships gm, groups g WHERE gm.group = g.id AND gm.user = :id ';
        $stmt = DatabaseFactory::getInstance()->prepare($sql);
        $stmt->bindValue(':id', $this->getId());
        $stmt->execute();

        foreach ($stmt->fetchAll() as $group) {
            if ($group['title'] != $this->usergroups['primary']['title']) {
                $this->usergroups[$group['title']] = $group;
            }
        }

        return true;
    }

    public function getId(): int
    {
        return intval($this->getAttribute('id'));
    }

    public function getUsername()
    {
        return $this->username;
    }

    public function getDataAll()
    {
        return $this->data;
    }

    /**
     * @Deprecated Use getAttribute() instead.
     */
    public function getData($field, $useCache = true)
    {
        return $this->getAttribute($field, $useCache);
    }

    public function getAttribute($field, $useCache = true)
    {
        if (!$useCache) {
            $this->updateAttributeCache();
        }

        return $this->data[$field];
    }

    public function setAttribute($key, $value)
    {
        return AuthBackend::getBackend()->setUserAttribute($this->username, $key, $value);
    }

    /**
     * @Deprecated Use setAttribute() instead.
     */
    public function setData($key, $value)
    {
        $this->setAttribute($key, $value);
    }

    private function updateAttributeCache()
    {
        $this->data = AuthBackend::getBackend()->getUserAttributes($this->username, self::$uniqueField);
    }

    public function __toString()
    {
        return 'User class for (' . $this->getUsername() . ')';
    }

    public static function getAllLocalUsers()
    {
        $sql = 'SELECT u.*, g.id as groupId, g.title as groupTitle, g.css FROM users u, groups g WHERE u.group = g.id ORDER BY u.id ';
        $stmt = DatabaseFactory::getInstance()->prepare($sql);
        $stmt->execute();

        return $stmt->fetchAll();
    }

    public static function getCountLocalUsers(): int
    {
        $sql = 'SELECT count(id) AS count FROM users';
        $stmt = DatabaseFactory::getInstance()->prepare($sql);
        $stmt->execute();

        $row = $stmt->fetchRow();

        return intval($row['count']);
    }
}
