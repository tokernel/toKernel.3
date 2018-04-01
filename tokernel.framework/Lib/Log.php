<?php
/**
 * toKernel by David A. and contributors.
 * Log files processor class library
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
 * @category    library
 * @package     framework
 * @subpackage  library
 * @author      toKernel development team <framework@tokernel.com>
 * @copyright   Copyright (c) 2017 toKernel
 * @license     http://www.gnu.org/copyleft/gpl.html GNU Public License
 * @version     1.0.0
 * @link        http://www.tokernel.com
 * @since       File available since Release 3.0.0
 */

namespace Lib;

/* Restrict direct access to this file */
defined('TK_EXEC') or die('Restricted area.');

/**
 * Log class
 *
 * @author David A. <tokernel@gmail.com>
 */
class Log {

    /**
     * Log file
     *
     * @access protected
     * @var string
     */
    protected $logFileName;

    /**
     * Log file size
     *
     * @access protected
     * @var integer
     */
    protected $logFileSize;

    /**
     * Log files path
     *
     * @access protected
     * @var string
     */
    protected $logPath;

    /**
     * Class constructor
     *
     * @access public
     * @param string $logFileName (i.e. error.log, warning.log, etc...)
     * @param int $logFileSize File size in bytes.
     * @param string $logPath (Directory path to log).
     */
    public function __construct($logFileName, $logFileSize = 524288, $logPath = NULL) {

        $this->logFileName = $logFileName;
        $this->logFileSize = $logFileSize;

        if(!is_null($logPath)) {

            if(substr($logPath, -1) != TK_DS) {
                $logPath .= TK_DS;
            }

            $this->logPath = $logPath;
        } else {
            $this->logPath = TK_APP_PATH . 'Log' . TK_DS;
        }

    }

    /**
     * Get current log file.
     * If file is larger than specified size, archive it, and create new file.
     *
     * @access protected
     * @throws \ErrorException
     * @return bool
     */
    protected function prepareLogFile() {

        // Check, if path is writeable
        if(!is_writeable($this->logPath)) {
            throw new \ErrorException('Logs directory is not writable!');
        }

        // Log file not exists, let's create it.
        if(!is_file($this->logPath . $this->logFileName)) {

            $this->createFile($this->logFileName);

            return true;

        }

        // Check the log file size, and archive it if size reached.
        if(filesize($this->logPath . $this->logFileName) > $this->logFileSize) {

            $this->compressFile($this->logFileName, true);
            $this->createFile($this->logFileName);

            return true;

        }

        return true;

    } // end func current_file

    /**
     * Create new log file
     *
     * @access public
     * @param string $logFileName
     * @return void
     */
    protected function createFile($logFileName) {

        touch($this->logPath . $logFileName);
        chmod($this->logPath . $logFileName, 0777);

    } // End func createFile

    /**
     * Write data to current log file
     *
     * @access public
     * @throws \ErrorException
     * @param string $logMessage
     * @param bool $dateMode
     * @return void
     */
    public function write($logMessage, $dateMode = true) {

        // Prepare the log file to write
        $this->prepareLogFile();

        $fileRes = fopen($this->logPath . $this->logFileName, 'a');

        if($fileRes) {

            if($dateMode == true) {
                $logMessage = '[' . date('Y-m-d H:i:s') . ' ' . TK_RUN_MODE.'] ' . $logMessage;
            }

            fwrite($fileRes, $logMessage . "\n");
            fclose($fileRes);

        } else {
            throw new \ErrorException('Unable to write log!');
        }

    } // end func write

    /**
     * Return a list of log files.
     * if extension not defined, then will return all files such as *.log, *.gz
     *
     * @access public
     * @param string $ext (this can be more then one extension separated by "|" )
     * @return array
     */
    public function getFilesList($ext = NULL) {

        if(is_null($ext)) {
            $globArr = glob($this->logPath . '*.*');
        } else {

            $ext = explode('|', $ext);

            $types = '';

            foreach($ext as $type) {
                $types .= $this->logPath.'*.'.$type.',';
            }

            $types = trim($types, ',');

            $globArr = glob("{".$types."}", GLOB_BRACE);

        }

        $filesArr = array();

        if(is_array($globArr) and !empty($globArr)) {
            foreach($globArr as $file) {
                $filesArr[] = basename($file);
            }
        }

        return $filesArr;

    } // end func getFilesList

    /**
     * Delete log file
     *
     * @access public
     * @throws \ErrorException
     * @param  string $file (base name without path)
     * @return bool
     */
    public function deleteFile($file) {

        $file = basename($file);

        $filePath = $this->logPath . $file;

        if(!is_file($filePath)) {
            throw new \ErrorException('File `'.$file.'` not found!');
        }

        if(!is_writeable($filePath)) {
            throw new \ErrorException('File `'.$file.'` is not writeable!');
        }

        return unlink($filePath);

    } // End func

    /**
     * Delete log files
     *
     * @access public
     * @param string $ext
     * @return bool
     */
    public function deleteFiles($ext = NULL) {

        $fileList = $this->getFilesList($ext);

        if(empty($fileList)) {
            return false;
        }

        foreach($fileList as $logFile) {
            unlink($this->logPath . $logFile);
        }

        return true;

    } // end func deleteFiles

    /**
     * Compress log file to *.gz
     * Delete source file if $removeSourceFile defined as true
     *
     * @access public
     * @throws \ErrorException
     * @param string $logFile
     * @param bool $removeSourceFile
     * @return bool
     */
    public function compressFile($logFile, $removeSourceFile = false) {

        if(!is_readable($this->logPath . $logFile)) {
            throw new \ErrorException('Unable to read file: ' . $logFile);
        }

        $fileContent = file_get_contents($this->logPath . $logFile);

        // make file base name and extension
        $fileData = pathinfo($this->logPath . $logFile);

        $ext = $fileData['extension'];
        $basename = $fileData['filename'];

        $gzLogFile = $this->logPath . $basename . '.' . date('Y-m-d_H.i.s') . '.' . $ext . '.gz';

        $gzFileContent = gzencode($fileContent, 9);

        $fileRes = fopen($gzLogFile, "a");

        if($fileRes) {

            fputs($fileRes, $gzFileContent);
            fclose($fileRes);

            if($removeSourceFile == true) {
                unlink($this->logPath . $logFile);
            }

        } else {
            throw new \ErrorException('Unable to compress log file!');
        }

        return true;

    } // end func compressFile

    /**
     * Uncompress archived log file (*.gz)
     * Delete source file if $removeArchive defined as true
     *
     * @access public
     * @throws \ErrorException
     * @param string $archivedFile
     * @param bool $removeArchive
     * @return bool
     */
    public function uncompressFile($archivedFile, $removeArchive = false) {

        if(!is_readable($this->logPath . $archivedFile)) {
            throw new \ErrorException('Unable to read file: ' . $archivedFile);
        }

        $fileRes = gzopen($this->logPath . $archivedFile, 'r');
        $gzFileContent = '';

        while(!feof($fileRes)) {
            $gzFileContent .= gzread($fileRes, 10000);
        }

        gzclose($fileRes);

        // put content
        $logFile = str_replace('.gz', '', $archivedFile);

        $fileRes = fopen($this->logPath . $logFile, "a");

        if($fileRes) {
            fputs($fileRes, $gzFileContent);
            fclose($fileRes);

            if($removeArchive == true) {
                unlink($this->logPath . $archivedFile);
            }

        } else {
            throw new \ErrorException('Unable to write file: ' . $logFile);
        }

        return true;

    } // end func uncompressFile

    /**
     * Return log file content as string or as an array
     * It is possible to read and return *.gz archived log file.
     *
     * @access public
     * @throws \ErrorException
     * @param string $file
     * @param bool $asArray
     * @return mixed array | string | bool
     */
    public function readFile($file, $asArray = false) {

        if(!is_readable($this->logPath . $file)) {
            throw new \ErrorException('Unable to read file:' . $file);
        }

        if(substr($file, -3) == '.gz') {
            $lines = gzfile($this->logPath . $file);
        } else {
            $lines = file($this->logPath . $file);
        }

        if($asArray == false) {
            return implode("", $lines);
        }

        return $lines;

    } // end func readFile

    /**
     * Return current log file name
     *
     * @access public
     * @return string
     */
    public function getFileName() {
        return $this->logFileName;
    }

    /**
     * Return current log files path
     *
     * @access public
     * @return string
     */
    public function logPath() {
        return $this->logPath;
    }

} // End class lib Log