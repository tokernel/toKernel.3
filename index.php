<?php
/**
 * toKernel by David A. and contributors
 * Application main callable file.
 *
 * This file is part of toKernel.
 *
 * toKernel is free software: you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation, either version 3 of the License, or
 * (at your option) any later version.
 *
 * toKernel is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the
 * GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License
 * along with toKernel. If not, see <http://www.gnu.org/licenses/>.
 *
 * @category   application
 * @package    toKernel
 * @subpackage main
 * @author     David A. <framework@tokernel.com>
 * @copyright  Copyright (c) 2017 toKernel
 * @license    http://www.gnu.org/copyleft/gpl.html GNU Public License
 * @version    1.0.0
 * @link       http://www.tokernel.com
 * @since      File available since Release 3.0.0
 */

/* Define project root path. */
define('TK_ROOT_PATH', dirname(__FILE__) . DIRECTORY_SEPARATOR);

/* Define application directory name (without directory separator). */
define('TK_APP_DIR', 'application');

/* Define Application path */
define('TK_APP_PATH', TK_ROOT_PATH . TK_APP_DIR . DIRECTORY_SEPARATOR);

/* Define Kernel Directory name */
define('TK_DIR', 'tokernel.framework');

/* Define Kernel path */
define('TK_PATH', TK_ROOT_PATH . TK_DIR . DIRECTORY_SEPARATOR);

/* Change current directory path. */
chdir(TK_ROOT_PATH);

/* Set ini to display errors */
ini_set('log_errors', 0);
ini_set('display_errors', 1);

/* Define default timezone before application initialization. */
ini_set('date.timezone', 'America/Los_Angeles');

/*
 * To restrict direct access to files excepts index.php, we have to define constant for future check.
 *
 * defined('TK_EXEC') or die('Restricted area.');
 *
 * This defined constant allows to run files included bellow.
 */
define('TK_EXEC', true);

/* Include framework configuration constants file */
require_once(TK_PATH . 'Config' . DIRECTORY_SEPARATOR . 'constants.php');

/* Include application constants file */
require_once(TK_APP_PATH . 'Config' . DIRECTORY_SEPARATOR . 'constants.php');

/* Include Autoloader */
require_once (TK_ROOT_PATH . 'vendor' . DIRECTORY_SEPARATOR . 'autoload.php');

/*
 * Detect the mode of application runtime - HTTP or CLI.
 * Note: There are differences between CLI and HTTP kernel Libraries and functionality.
 */
if(!empty($argc) and php_sapi_name() == 'cli') {
	require_once (TK_PATH . 'Kernel' . DIRECTORY_SEPARATOR . 'CLI' . DIRECTORY_SEPARATOR . 'Loader.inc.php');
} else {
	require_once (TK_PATH . 'Kernel' . DIRECTORY_SEPARATOR . 'HTTP' . DIRECTORY_SEPARATOR . 'Loader.inc.php');
}

/* End of file */
?>