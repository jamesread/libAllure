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

/**
 * Sanitizes and filters input.
 *
 * This class has an unusual history, and the first thing you will probably ask
 * is why on earth this class exists when PHP has filter_ functions.
 *
 * It looks like PHP's filter_ function  were added in PHP 5.2 (~2006), yet
 * this class was probably written around2005-2008 - and PHP 5.2 either probably
 * was not available on my server, or I just didn't know about the filter_
 * functions.
 *
 * However, the filter_ functions interface is incredibly goofy (array of
 * $options, etc), so this class has now been re-written as a wrapper around
 * the filter_ functions.
 */
class Sanitizer
{
    public $filterAllowUndefined = true;
    public $onUndefinedDoVariableHunt = false;

    public const INPUT_GET = 1;
    public const INPUT_POST = 2;
    public const INPUT_REQUEST = 3; // Not available in PHP's filter functions.
    public const INPUT_SERVER = 4;
    public const INPUT_COOKIE = 5;

    public const FORMAT_FOR_DB = 1;
    public const FORMAT_FOR_HTML = 2;
    public const FORMAT_FOR_ALL = 64;

    private $inputSource = self::INPUT_REQUEST;
    private $variableNamePrefixes = array ('form');

    private static $instance;

    /**
     * The constructor is still public as it's quite likely that users will want
     * to create instances of this class with different options. This singleton
     * method is useful for getting an instance with sane defaults.
     */
    public static function getInstance()
    {
        if (self::$instance == null) {
            self::$instance = new Sanitizer();
        }

        return self::$instance;
    }

    public function triggerFailFilter($message)
    {
        throw new \Exception($message);
    }

    public function setInputSource($inputSource)
    {
        $this->inputSource = $inputSource;
    }

    private function getInputSourceArray(): array
    {
        switch ($this->inputSource) {
            case self::INPUT_GET:
                return $_GET;
            case self::INPUT_POST:
                return $_POST;
            case self::INPUT_REQUEST:
                return $_REQUEST;
            case self::INPUT_SERVER:
                return $_SERVER;
            case self::INPUT_COOKIE:
                return $_COOKIE;
            default:
                throw new \Exception('Invalid input source');
        }
    }

    public function hasInput($name): bool
    {
        return $this->getInput($name) !== null;
    }

    private function getInput($name)
    {
        $source = $this->getInputSourceArray();

        if (isset($source[$name])) {
            return $source[$name];
        }

        if ($this->onUndefinedDoVariableHunt) {
            $val = $this->variableHunt($source, $name);
        }

        if (!$this->filterAllowUndefined) {
            throw new \Exception('Input variable not found: ' . $name);
        }

        return null;
    }

    private function variableHunt(array $source, $name)
    {
        foreach ($source as $key => $value) {
            if (strstr($key, $name) !== false) {
                return $source[$key];
            }
        }

        return false;
    }

    public function filterId()
    {
        return $this->filterUint('id');
    }

    public function filterUint($name, $min = 0, $max = PHP_INT_MAX)
    {
        $min = max($min, 0); // rectify sint

        return $this->filterInt($name, $min, $max);
    }

    public function filterInt($name, $min = null, $max = PHP_INT_MAX)
    {
        if ($min == null) {
            $min = -PHP_INT_MAX;
        }

        $value = intval($this->getInput($name));

        if ($value < $min) {
            $this->triggerFailFilter('The integer variable '  . $name . ' is below the minimum legal value of ' . $min);
        } elseif ($value > $max) {
            $this->triggerFailFilter('The integer variable '  . $name . ' is above the maximum legal value of ' . $max);
        }

        return $value;
    }

    public function filterSint($name, $min = PHP_INT_MIN, $max = PHP_INT_MAX)
    {
        return $this->filterInt($name, $min, $max);
    }

    public function filterIdentifier($name)
    {
        $c = $this->getInput($name);
        $c = (string) $c;

        if (preg_match('#^\w[\w\d]+$#', $c) === 0) {
            $this->triggerFailFilter('Content is not an identifier: ' . $name);
        }

        return $c;
    }

    public function filterAlphanumeric($name)
    {
        $c = $this->getInput($name);
        $c = (string) $c;

        if (preg_match('#^[\w\d ]+$#i', $c) === 0) {
            $this->triggerFailFilter('Content is not alphanumeric: ' . $c);
        }

        return $c;
    }

    public function filterNumeric($content)
    {
        if (!is_numeric($content)) {
            $this->triggerFailFilter('Content is not numeric');
        }

        return $content;
    }

    public function filterString($name, $default = null): mixed
    {
        return $this->filterInputString($name, $default);
    }

    public function filterInputString($name, $default = null): mixed
    {
        $v = $this->getInput($name);

        if (is_string($v)) {
            return filter_var($v, FILTER_UNSAFE_RAW);
        } else {
            return $default;
        }
    }

    public function escapeStringForClean($content)
    {
        if (is_string($content)) {
            $content = stripslashes($content);
        }

        return $content;
    }

    public function escapeStringForDatabase($content)
    {
        trigger_error('Method ' . __METHOD__ . ' is deprecated', E_USER_DEPRECATED);

        return null;
    }

    public function escapeStringForHtml($content)
    {
        $content = strip_tags($content);
        $content = htmlentities($content);

        return $content;
    }

    public function escapeStringForConsole($content)
    {
        return $content;
    }

    public function formatStringForDatabase($content)
    {
        return $this->escapeStringForDatabase($content);
    }

    public function formatStringForHtml($content)
    {
        return $this->escapeStringForHtml($content);
    }

    public function formatString($content, $destination = 3)
    {
        if ($destination & self::FORMAT_FOR_DB) {
            $content = $this->formatStringForDatabase($content);
        }

        if ($destination & self::FORMAT_FOR_HTML) {
            $content = $this->formatStringForHtml($content);
        }

        return $content;
    }

    public function formatNumericAsHex($num)
    {
        return dechex($num);
    }

    public function formatBool($content)
    {
        if ($content) {
            return true;
        } else {
            return false;
        }
    }

    public function filterEnum($name, array $accepted, $default = null)
    {
        return $this->filterInputEnum($name, $accepted, $default);
    }

    public function filterInputEnum($name, array $accepted, $default)
    {
        return $this->filterVariableEnum($this->filterInputString($name), $accepted, $default);
    }

    public function filterVariableEnum($value, array $accepted, $default = null)
    {
        if (in_array($value, $accepted)) {
            return $value;
        } else {
            return $default;
        }
    }
}
