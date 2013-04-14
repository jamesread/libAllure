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

if (defined(__FILE__)) { return; } else { define(__FILE__, true); }

class ErrorHandler {
	protected $css = "margin: auto; width: 75%; background-color: #FFBCBA; border: 2px solid black; line-height: 1.5; padding: 6px; font-family: Verdana, Sans-Serif; font-size: 9pt; text-align: left;";
	protected $cssErrorTitle = 'background-color: red; color: white; text-align: left; margin: 0; padding: .5em; font-size: 12pt;';
	public static $instance;

	/**
	 * Constructs the new class.
	 *
 	 * @param greedy Whether or not this class can be greedy: is allowed to
	 * capture all types of errors that it can bind to.
	 */
	public function __construct($greedy = true) {
		if ($greedy) {
			$this->beGreedy();
		}
	}

	/**
	 * @returns ErrorHandler
	 * @deprecated
	 */
	public static function getInstance() {
		if (self::$instance == null) {
			self::$instance = new self();
		}

		return self::$instance;
	}

	protected function clearOutputBuffers() {
		// Clear the output buffers so we can actually show a message;
		while (ob_get_level() > 0) {
			ob_end_clean();
		}
	}

	/**
	 * Print the error out.
	 *
	 * @param trigger What triggored this error.
	 * @param message The message for the error.
	 * @param code The code for the error.
	 * @param file The file that this error came from.
	 * @param line The line that this error came from.
	 * @param stacktrace A stacktrace leading up to this error ( should be a
 	 * array).
	 */
	protected function render($trigger, $message, $code = null, $file = null, $line = null, $stacktrace = null) {
		if (class_exists('Logger', false)) {
			$metadata = '';

			if (isset($_SERVER['REQUEST_URI'])) {
				$metadata .= ' Request URI: ' . $_SERVER['REQUEST_URI'];
			}

			if (isset($_SERVER['REMOTE_ADDR'])) {
				$metadata .= ' Remote addr: ' . $_SERVER['REMOTE_ADDR'];
			}

			if (class_exists('Session', false) && class_exists('User', false) && Session::isLoggedIn()) {
				$metadata .= ' User:' . Session::getUser()->getUsername();
			}

			Logger::messageWarning('Fatal error;' . $message . ' at ' . $file . ':' . $line . ' ' . $metadata);
		}

		if (isset($_SERVER['HTTP_USER_AGENT'])) {
			$this->renderHtml($trigger, $message, $code, $file, $line, $stacktrace);
		} else {
			echo 'Fatal error' . "\n";
			echo '-------------------------------' . "\n";
			echo 'Message: ' . $message . "\n";
			echo 'Source: ' . $file . ':' . $line . "\n";
			exit(1);
		}

		exit;
	}

	protected function renderHtml($trigger, $message, $code = null, $file = null, $line = null, $stacktrace = null) {
		$this->clearOutputBuffers();

		if (!ini_get('display_errors') == '1') {
			throw new RuntimeException('A serious error has occoured, which cannot be sent via the web browser due to the webserver security configuration.');
		}

		// Show the error.
		echo "<!--\n##\n## ERROR: {$message} \n##\n-->\n";
		echo '<div style = "' . $this->css . '">', "\n";
		echo '<h1 style = "' . $this->cssErrorTitle . '">' . "Error!" . '</h1>', "\n";

		echo '<p>PHP display_errors is turned on, this is the full error message;</p>', "\n";

		echo '<strong>Message: </strong>', $message, '<br />', "\n";

		if (isset($code))
			echo '<strong>Code: </strong>', $code, '<br />', "\n";

		if (isset($line))
			echo '<strong>Line: </strong>', $line, '<br />', "\n";

		if (isset($file))
			echo '<strong>File: </strong>', $file, '<br />', "\n";

		// If the message concerns the database, skip the stack trace for risk
		// of printing db details.
		if (strpos($message, 'database'))
			$stacktrace = null;

		if (isset($stacktrace))
			if (is_array($stacktrace) && !empty($stacktrace)) {
				echo '<strong>Stacktrace: </strong><br />';
				echo '<blockquote><table style = "font-size: 9pt; width: 100%; text-align: left;">';
				echo '<tr><th>ID</th><th>File</th><th>Line</th><th>Class</th><th>function call</th></tr>';
				foreach ($stacktrace as $id => $point) {
					$point['class'] = (isset($point['class']) ? $point['class'] : '(none)');
					echo '<tr><td>' . (sizeof($stacktrace) - $id) . '</td><td>' . $point['file'] . '</td><td>' . $point['line'] . '</td><td>' . $point['class'] . '</td><td>' . $point ['function'] . '(';

					if (isset($point['args'])) {
						foreach ($point['args'] as $id =>  $arg) {
							if (is_object($arg)) {
								echo get_class($arg);
							} else {
								echo $arg;
							}

							if (isset($point['args'][$id + 1])) {
								echo ', ';
							}
						}
					}

					echo ')</td>' . '</tr>';
				}

				echo '</table></blockquote>';
			} else {
				if (is_array($stacktrace)) {
					$stacktrace = 'Empty';
				}

				echo '<strong>Stacktrace: </strong>' . $stacktrace, '<br />';
			}

		echo '<strong>Trigger: </strong>', $trigger, '<br />';

		echo '</div></div>';

		exit;
	}

	public function handleHttpError($code) {
		switch ($code) {
			case '404': $message = 'Object not found.'; break;
			case '403': $message = 'Forbidden.'; break;
			case '401': $message = 'Unauthorized.'; break;
		}

		$this->render('HTTP Error', $message, $code);
	}

	public function handlePhpError($code, $message, $file, $line) {
		// Supressed with @?
		if (error_reporting() == 0) {
			return;
		}

		// mysqli_connect() errors.
		// Dont include "my" in the search because it would return 0, which would evaluate to false and thats a pain in the ass. So, instead, we use "sql_...". This is a long comment.
		if (strpos($message, 'sqli_connect')) {
			if (strpos($message, '1045')) {
				throw new RuntimeException("Access denied by the database server");
			}

			if (strpos($message, '10061')) {
				throw new RuntimeException("Could not connect to the database server. The server may not be responding.");
			}

			if (strpos($message, '1049')) {
				throw new RuntimeException("Connected to the database server, but the database does not exist!");
			}

			throw new RuntimeException('There was an unrecognized problem when connecting to the database: ' . $message);
		}

		$code = self::errorCodeToString($code);
		$trigger = 'PHP Triggered Error';

		$this->render($trigger, $message, $code, $file, $line);
	}

	public static function errorCodeToString($code) {
		switch ($code) {
			case E_ERROR: return 'PHP Error';
			case E_WARNING: return 'PHP Warning';
			case E_PARSE: return 'PHP Parse Error';
			case E_NOTICE: return 'PHP Notice';
			case E_CORE_ERROR: return 'PHP Core Error';
			case E_CORE_WARNING: return 'PHP Core Warning';
			case E_COMPILE_ERROR: return 'PHP Compile Error';
			case E_COMPILE_WARNING: return 'PHP Compile Warning';
			case E_USER_ERROR: return 'PHP User Error';
			case E_USER_WARNING: return 'PHP User Warning';
			case E_USER_NOTICE: return 'PHP User Notice';
			case E_STRICT: return 'PHP E_STRICT ';
			case E_RECOVERABLE_ERROR: return 'PHP Recoverable Error';
			case E_DEPRECATED: return 'PHP Deprecated';
			case E_USER_DEPRCATED: return 'PHP User Deprecated';
			default: return 'Unknown (' . $code . ')';
		}
	}

	public function handleException($obj) {
		$message = $obj->getMessage();
		$code = $obj->getCode();
		$file = $obj->getFile();
		$line = $obj->getLine();
		$stacktrace = $obj->getTrace();

		$this->render('Exception -> ' . get_class($obj), $message, $code, $file, $line, $stacktrace);

	}

	public function setCss($css) {
		$this->css = $css;
	}

	public function null() {}

	public function beLazy() {
		error_reporting(E_ERROR);
		set_error_handler(array(&$this, 'null'));
//		restore_error_handler();
		restore_exception_handler();
	}

	public function beGreedy() {
		error_reporting(E_ALL | E_STRICT);

		// check for http errors
		if (isset($_REQUEST['httpError'])) {
			$this->handleHttpError(intval($_REQUEST['httpError']));
		}
		
		set_error_handler(array(&$this, 'handlePhpError'), error_reporting());
		set_exception_handler(array(&$this, 'handleException'));
	}
}

?>
