<?php
/*
 * Functions related to File input/output operations
 *
 * NOTICE OF LICENSE
 * This source file is subject to the Open Software License (OSL 3.0)
 * that is bundled with this package in the file LICENSE.TXT.
 * It is also available through the world-wide-web at this URL:
 * http://opensource.org/licenses/OSL-3.0
 *
 * DISCLAIMER
 * Do not edit or add to this file if you wish to upgrade Bizuno to newer
 * versions in the future. If you wish to customize Bizuno for your
 * needs please refer to http://www.phreesoft.com for more information.
 *
 * @name       Bizuno ERP
 * @author     Dave Premo, PhreeSoft <support@phreesoft.com>
 * @copyright  2008-2020, PhreeSoft, Inc.
 * @license    http://opensource.org/licenses/OSL-3.0  Open Software License (OSL 3.0)
 * @version    4.x Last Update: 2020-06-09
 * @filesource /lib/model/io.php
 */

namespace bizuno;

final class io
{
    private $ftp_con;
    private $sftp_con;
    private $sftp_sub;

    function __construct()
    {
        $this->myFolder    = defined('BIZUNO_DATA') ? BIZUNO_DATA : '';
        $this->max_count   = 200; // max 300 to work with BigDump based restore sript
        $this->db_filename = 'db-'.date('Ymd');
        $this->source_dir  = '';
        $this->source_file = 'filename.txt';
        $this->dest_dir    = 'backups/';
        $this->dest_file   = 'filename.bak';
        $this->mimeType    = '';
//      $this->useragent   = 'Mozilla/5.0 (Windows NT 5.1; rv:31.0) Gecko/20100101 Firefox/31.0'; // moved to portal
        $this->options     = ['upload_dir' => $this->myFolder.$this->dest_dir];
    }

    /**
     * Deletes a module attachment file and resets the attach flag if no more attachments are present
     * @param array $layout - page structure coming in
     * @param integer $mID - module ID
     * @param string $pfxID [default: rID_] - prefix for the filename followed by the record ID
     * @param boolean $dbID - table name
     * @return modified $structure
     */
    public function attachDelete(&$layout, $mID, $pfxID='rID_', $dbID=false)
    {
        $dgID = clean('rID', 'text', 'get');
        $file = clean('data','text', 'get');
        // get the rID
        $fID = str_replace(getModuleCache($mID, 'properties', 'attachPath'), '', $file);
        $tID = substr($fID, 4); // remove rID_
        $rID = substr($tID, 0, strpos($tID, '_'));
        msgDebug("\nExtracted rID = $rID");
        // delete the file
        $this->fileDelete($file);
        msgLog(lang('delete').' - '.$file);
        msgDebug("\n".lang('delete').' - '.$file);
        // check for more attachments, if no more, clear attachment flag
        if (!$dbID) { $dbID = $mID; }
        $rows = $this->fileReadGlob(getModuleCache($mID, 'properties', 'attachPath').$pfxID."{$rID}_");
        if (!sizeof($rows)) { dbWrite(BIZUNO_DB_PREFIX.$dbID, ['attach'=>'0'], 'update', "id=$rID"); }
        $layout = array_replace_recursive($layout, ['content'=>['action'=>'eval', 'actionData'=>"var row=jq('#$dgID').datagrid('getSelected');
            var idx=jq('#$dgID').datagrid('getRowIndex', row); jq('#$dgID').datagrid('deleteRow', idx);"]]);
    }

    /**
     * Sends a file/data to the browser
     * @param string $type - determines the type of data to download, choices are 'data' [default] or 'file'
     * @param string $src - contains either the file contents (for type data) or path (for type file)
     * @param string $fn - the filename to assign to the download
     * @param boolean $delete_source - determines if the source file should be deleted after the download, default is false
     * @return will not return if successful, if this script returns, the messageStack will contain the error.
     */
    public function download($type='data', $src='', $fn='download.txt', $delete_source=false)
    {
        switch ($type) {
            case 'file': // unzip the file to remove security encryption
                $realFN = $src . $fn;
                if (!realpath($this->myFolder.$realFN)) { return msgAdd("Invalid path!"); }
                if (!in_array(strtolower(pathinfo($fn, PATHINFO_EXTENSION)), $this->getValidExt())) { return msgAdd("Invalid file type!"); }
                if (!$this->validatePath($realFN)) { return; }
                if (!$output = $this->fileRead($realFN, 'rb')) { return; }
                $this->mimeType = $this->guessMimetype($realFN);
                if ($delete_source) {
                    msgDebug("\nUnlinking file: $realFN");
                    @unlink($this->myFolder.$realFN);
                }
                msgDebug("\n Downloading filename $realFN of size = ".$output['size']);
                break;
            default:
            case 'data':
                $this->mimeType = $this->guessMimetype($fn);
                $output = ['data'=>$src, 'size'=>strlen($src)];
                msgDebug("\n Downloading data of size = {$output['size']} to filename $fn");
        }
        if ($output['size'] == 0) { return msgAdd(lang('err_io_download_empty')); }
        $filename = clean($fn, 'filename');
        msgDebug("\n Detected mimetype = $this->mimeType and sending filename: $filename");
        msgDebugWrite();
        header('Set-Cookie: fileDownload=true; path=/');
        if ($this->mimeType) { header("Content-type: $this->mimeType"); }
        header("Content-disposition: attachment;filename=$filename; size=".$output['size']);
        header('Pragma: cache');
        header('Cache-Control: public, must-revalidate, max-age=0');
        header('Connection: close');
        header('Expires: '.date('r', time()+60*60));
        header('Last-Modified: '.date('r'));
        echo $output['data'];
        exit();
    }

    /**
     * Deletes file(s) matching the path specified, wildcards are allowed for glob operations
     * @param string $path - full path with filename (or file pattern)
     * @return null
     */
    public function fileDelete($path=false)
    {
        if (!$path) { return msgAdd("No file specified to delete!"); }
        msgDebug("\nDeleting files: BIZUNO_DATA/".print_r($path,true));
        $files = glob($this->myFolder.$path);
        if (is_array($files)) { foreach ($files as $filename) { @unlink($filename); } }
    }

    /**
     * Recursively moves the all files matching source pattern to destination pattern
     * Used in merging contacts, etc.
     * @param string $path - path to the source
     * @param string $srcID - filename at the source (can contain wildcards)
     * @param string $destID - path of where the files go
     */
    public function fileMove($path, $srcID, $destID)
    {
        $files = $this->fileReadGlob($path.$srcID);
        msgDebug("\nat fileMove read path: ".$path.$srcID." and returned with: ".print_r($files, true));
        foreach ($files as $file) {
            $newFile = str_replace($srcID, $destID, $file['name']);
            if (!file_exists($this->myFolder.$newFile)) {
                msgDebug("\nRenaming file in myFolder from: {$file['name']} to: $newFile");
                rename($this->myFolder.$file['name'], $this->myFolder.$newFile);
            } else { // file exists, create a new name
                msgAdd("The file ($newFile) already exists on the destination location. It will be ignored!");
            }
        }
    }

    /**
     * Read a file into an array
     * @param string $path - path and filename to the file of interest
     * @param string $mode - default 'rb', read only binary safe, see php fopen for other modes
     * @return array(data, size) - data is the file contents and size is the total length
     */
    public function fileRead($path, $mode='rb')
    {
        $myPath = $this->myFolder.$path;
        if (!$handle = @fopen($myPath, $mode)) {
            return msgAdd(sprintf(lang('err_io_file_open'), $path));
        }
        $size = filesize($myPath);
        $data = fread($handle, $size);
        msgDebug("\n Read file of size = $size");
        fclose($handle);
        return ['data'=>$data, 'size'=>$size];
    }

    /**
     * Reads a directory via the glob function
     * @param string $path - path relative to users myFolder to read
     * @param array $arrExt [default: empty] - list of extensions to skip
     * @return array - From empty to a list of files within the folder.
     */
    public function fileReadGlob($path, $arrExt=[])
    {
        $output= [];
        msgDebug("\nEntering fileReadGlob with path = $path");
        if (!$this->folderExists($path)) { return $output; }
        $files = glob($this->myFolder.$path."*");
        if (!is_array($files)) { return $output; }
        foreach ($files as $file) {
            $ext = strtolower(pathinfo($file, PATHINFO_EXTENSION));
            if (!empty($arrExt) && !in_array($ext, $arrExt)) { continue; }
            $fmTime = filemtime($file);
            $output[] = [
                'name' => str_replace($this->myFolder, "", $file), // everything less the myFolder path, used to delete and navigate to
                'title'=> str_replace($this->myFolder.$path, "", $file), // just the filename, part matching the *
                'fn'   => str_replace($this->myFolder.$path, "", $file), // duplicate of title to use in attach grid to avoid conflict with title of grid
                'size' => viewFilesize($file),
                'mtime'=> $fmTime,
                'date' => date(getModuleCache('bizuno', 'settings', 'locale', 'date_short'), $fmTime)];
        }
        msgDebug("\nReturned results from fileReadGlob = ".print_r($output, true));
        return $output;
    }

    /**
     * Writes a data string to a file location, if the path does not exist, it will be created.
     * @param string $data File contents
     * @param string $fn Full path to the file to be written from the myBiz folder
     * @param boolean $verbose [default true] adds error messages if any part of the write fails, false suppresses messages
     * @param boolean $append [default false] Causes the data to be appended to the file
     * @param boolean $replace True to overwrite file if one exists, false will not overwrite existing file
     * @return boolean
     */
    public function fileWrite($data, $fn, $verbose=true, $append=false, $replace=false)
    {
        if (strlen($data) < 1) { return; }
        if (!$append && $replace && file_exists($this->myFolder.$fn)) { $this->fileDelete($fn); }
        if (!$this->validatePath($fn, true)) { return $verbose ? msgAdd('Cannot write file, invalid path!') : false; }
//        header("Content-Type:text/html; charset=utf-8"); // make it UTF-8
        if (!$handle = @fopen($this->myFolder.$fn, $append?'a':'wb')) {
            flush();
            return $verbose ? msgAdd(sprintf(lang('err_io_file_open'), $fn)) : false;
        }
//        if (false === @fwrite($handle, "\xEF\xBB\xBF".$data)) {
        if (false === @fwrite($handle, $data)) {
            flush();
            return $verbose ? msgAdd(sprintf(lang('err_io_file_write'), $fn)) : false;
        }
        fclose($handle);
        msgDebug("\nSaved file to filename: BIZUNO_DATA/$fn");
    }

    /**
     * Recursively copies the contents of the source to the destination
     * @param string $dir_source - Source directory from the users root
     * @param string $dir_dest - Destination directory from the users root
     * @return null
     */
    public function folderCopy($dir_source, $dir_dest)
    {
        $dir_source = $this->myFolder.$dir_source;
        if (!is_dir($dir_source)) { return; }
        $files = scandir($dir_source);
        foreach ($files as $file) {
            if ($file == "." || $file == "..") { continue; }
            if (is_file($dir_source . $file)) {
                $mTime = filemtime($dir_source . $file);
                $aTime = fileatime($dir_source . $file); // preserve the file timestamps
                copy($dir_source . $file, $dir_dest . $file);
                touch($dir_dest . $file, $mTime, $aTime);
            } else {
                $this->validatePath($dir_dest."$file/index.php");
                $this->folderCopy($dir_source . "$file/", $dir_dest."$file/");
            }
        }
    }

    /**
     * Deletes a folder and all within it.
     * @param string $dir - Name of the directory to delete
     * @return boolean false
     */
    public function folderDelete($dir)
    {
        if (!is_dir($this->myFolder.$dir)) { return; }
        $files = scandir($this->myFolder.$dir);
        foreach ($files as $file) {
            if ($file == "." || $file == "..") { continue; }
            if (is_file($this->myFolder."$dir/$file")) {
                unlink($this->myFolder."$dir/$file");
            } else { // it's a directory
                $subdir = scandir($this->myFolder."$dir/$file");
                if (sizeof($subdir) > 2) { // directory is not empty, recurse
                    $subDir = str_replace($this->myFolder, '', $dir);
                    $this->folderDelete("$subDir/$file");
                }
                @rmdir($this->myFolder."$dir/$file");
            }
        }
        @rmdir($this->myFolder.$dir);
    }

    /**
     * Simple is_dir test to see if the folder exists
     * @param string $path - path without the path to the data space
     * @return true if path exists and is a folder, false otherwise
     */
    public function folderExists($path='')
    {
        msgDebug("\nEntering folderExists with path = $path");
        if (strpos($path, '/') === false) { return true; } // root folder
        if (substr($this->myFolder.$path, -1) == '/') { $path .= 'bizuno'; } // path is a dir, add a phony file so pathinfo works
        return is_dir(pathinfo($this->myFolder.$path, PATHINFO_DIRNAME)) ? true : false;
    }

    /**
     * Recursively moves the contents of a folder to another folder.
     * @param string $dir_source - source path
     * @param string $dir_dest - destination path
     * @param boolean $replace - [default false] whether to overwrite if the destination folder exists
     */
    public function folderMove($dir_source, $dir_dest, $replace=false)
    {
        $srcPath = $this->myFolder.$dir_source;
        if (!is_dir($srcPath)) { return; }
        $files = scandir($srcPath);
//      msgDebug("\nat folderMove read path: $srcPath and returned with: ".print_r($files, true));
        foreach ($files as $file) {
            if ($file == "." || $file == "..") { continue; }
            if ($replace && is_file($srcPath . $file)) {
                rename($srcPath . $file, $dir_dest . $file);
            } else { // folder
                if (!is_dir($dir_dest.$file)) { @mkdir($dir_dest.$file, 0755, true); }
                $this->folderMove($dir_source."$file/", $dir_dest."$file/", $replace);
                rmdir($dir_source."$file/");
            }
        }
    }

    /**
     * Reads the contents of a folder, cleans out the . and .. directories
     * @param string $path - [default ''] path from the users home folder
     * @param array $arrExt - [default {empty}]array of extensions to allow, leave empty for all extensions
     * @return array - List of files/directories within the $path
     */
    public function folderRead($path='', $arrExt=[])
    {
        $output = [];
        if (!$this->folderExists($path)) { return $output; }
        $temp = scandir($this->myFolder.$path);
        if (!is_array($temp)) { return $output; }
        foreach ($temp as $fn) {
            if ($fn=='.' || $fn=='..') { continue; }
            $ext = strtolower(pathinfo($fn, PATHINFO_EXTENSION));
            if (!empty($arrExt) && !in_array($ext, $arrExt)) { continue; }
            $output[] = $fn;
        }
        return $output;
    }

    /**
     * Returns the glob of a folder
     * @param string $path - File path to read, user folder will be prepended
     * @param array $arrExt - array of extensions to allow, leave empty for all extensions
     * @return array, empty for non-folder or no files
     */
    public function folderReadGlob($path='', $arrExt=[])
    {
        $output = [];
        msgDebug("\nTrying to read contents of myFolder/$path");
        if (!is_dir(pathinfo($this->myFolder.$path, PATHINFO_DIRNAME))) { return $output; }
        $temp = glob($this->myFolder.$path);
        foreach ($temp as $fn) {
            if ($fn == '.' || $fn == '..') { continue; }
            $ext = strtolower(pathinfo($fn, PATHINFO_EXTENSION));
            if (!empty($arrExt) && !in_array($ext, $arrExt)) { continue; }
            $output[] = str_replace($this->myFolder, '', $fn);
        }
        return $output;
    }

    /**
     * Establishes a FTP connection to a remote host.
     * @param string $host - FTP Host to connect to
     * @param string $user - username
     * @param string $pass - password
     * @param integer $port [default: 21] - FTP port
     * @return object - valid ftp connection
     */
    public function ftpConnect($host, $user='', $pass='', $port=21) {
        msgDebug("Ready to write to url $host to port $port with user $user");
        if (!$con = ftp_connect($host, $port)){ return msgAdd("Failed to connect to FTP server: $host through port $port"); }
        if (!ftp_login($con, $user, $pass))   { return msgAdd("Failed to log in to FTP server with user: $user"); }
        return $con;
    }

    /**
     * Uploads a file to the remote host though an established connection
     * @param object $con - valid FTP connection
     * @param string $local_file - path from myFiles including filename
     * @param string $remote_file [default: empty] - remote file to write, uses same name as source if left empty
     * @return boolean
     */
    public function ftpUploadFile($con, $local_file, $remote_file='') {
        $success = true;
        if (!$remote_file) { $remote_file = $local_file; }
        msgDebug("\nReady to open file $local_file and send to remote file name $remote_file");
        $fp = fopen(BIZUNO_DATA.$local_file, 'r');
        if (!ftp_fput($con, $remote_file, $fp, FTP_ASCII)) {
            return msgAdd("There was a problem while uploading $local_file through ftp to the remote server!");
        }
        ftp_close($con);
        fclose($fp);
        return $success;
    }

    /**
     * Pulls a list of valid extensions based on expectations
     * @param string $mime [default: file] - sets the type of extension to allow, file, zip, backup, or image
     */
    public function getValidExt($mime='file')
    {
        $extensions = [];
        switch ($mime) {
            case 'zip':    return ['gz','zip'];
            case 'backup': return ['sql','gz','zip'];
            default:
            case 'file' :  $extensions = array_merge($extensions, ['zip','gz','pdf','doc','docx','xls','xlsx','ods','txt']); // add valid file extensions, fall through
            case 'image':  $extensions = array_merge($extensions, ['jpg','jpeg','jpe','gif','png','tif','tiff']); // then add valid image extensions

        }
        return $extensions;
    }

    /**
     * Saves an uploaded file, validates first, creates path if not there
     * @param string $index - index of the $_FILES array where the file is located
     * @param string $dest - destination path/filename where the uploaded files are to be placed
     * @param string $prefix - File name prefix to prepend
     * @param string $mime - MIME types to allow
     * @return boolean true on success, false (with msg) on error
     */
    public function uploadSave($index, $dest, $prefix='', $mime='')
    {
        if (!isset($_FILES[$index])) { return msgDebug("\nTried to save uploaded file but nothing uploaded!"); }
        $extensions = $this->getValidExt($mime);
        if (!$this->validateUpload($index, '', $extensions, false)) { return; }
        if (empty($prefix) && substr($dest, -1)<>'/') {
            $prefix= pathinfo($dest, PATHINFO_BASENAME);
            $dest  = pathinfo($dest, PATHINFO_DIRNAME).'/';
        }
        $data = file_get_contents($_FILES[$index]['tmp_name']);
//      if (strpos($data, ['<'.'?'.'php', 'eval(']) !== false) { return msgAdd("Illegal file contents!"); }
        $filename = clean($_FILES[$index]['name'], 'filename');
        $path = $dest.str_replace(' ', '_', $prefix.$filename);
        $this->fileWrite($data, $path, false);
        return true;
    }

    /**
     * Validates path sent by user to be within the BIZUNO_DATA folder, i.e. stops ../../../../ hacking
     * @param string $srcPath - full path including filename
     * @param boolean $verbose [default: true] - false to suppress error messages or true to show them
     * @return true on valid path, false otherwise
     */
    public function validatePath($srcPath, $verbose=true) {
        msgDebug("\nEntering validatePath with srcPath = $srcPath");
        if (!defined('BIZUNO_DATA')) { return msgAdd("Error: Bizuno not initialized!"); }
        // cannot use empty() because it can be a string equating to "0"
        if ($srcPath === '' || $srcPath === null || $srcPath === false) { return false; }
        $path  = pathinfo(BIZUNO_DATA . $srcPath, PATHINFO_DIRNAME) . DIRECTORY_SEPARATOR; // pull the path from the full path and file
        if (!is_dir($path)) {
            @mkdir($path, 0775, true);
            $blnkDir = pathinfo($srcPath, PATHINFO_DIRNAME) . DIRECTORY_SEPARATOR; // need to remove BIZUNO_DATA before writing file
            $this->fileWrite('<'.'?'.'php', "{$blnkDir}index.php", false);
        }
        $error = false;
        $pPath = realpath($path) . DIRECTORY_SEPARATOR;
        $fPath = realpath(BIZUNO_DATA) . DIRECTORY_SEPARATOR;
        if ($pPath === false || $fPath === false) { $error = true; }
        $fPath = rtrim($fPath, DIRECTORY_SEPARATOR) . DIRECTORY_SEPARATOR;
        $pPath = rtrim($pPath, DIRECTORY_SEPARATOR) . DIRECTORY_SEPARATOR;
        if (strlen($pPath) < strlen($fPath)) { $error = true; }
        if (substr($pPath, 0, strlen($fPath)) !== $fPath) { $error = true; }
//      msgDebug("\nExiting validatePath with Path = $pPath and fPath = $fPath and error = ".($error?'true':'false'));
        msgDebug("\nExiting validatePath with error = ".($error?'true':'false'));
        return $error ? ($verbose ? msgAdd("Path validation error!") : false) : true; // passed all tests
    }

    /**
     * Recursive method to make sure all folders within the BIZUNO_DATA path have null index.php files
     * @param string $srcPath - path from myFiles to test
     * @return null - files are generated if the folder is empty
     */
    public function validateNullIndex($srcPath='/')
    {
        $path = rtrim(trim($srcPath, '/'), '/'); // remove leading and trailing slashes
        if (!is_dir(BIZUNO_DATA.$path)) { return; }
        $filename = trim("$path/index.php", '/');
        if (!file_exists(BIZUNO_DATA.$filename)) { $this->fileWrite('<'.'?'.'php', $filename, false); }
        $files = scandir(BIZUNO_DATA.$path);
        foreach ($files as $file) {
            if ($file == "." || $file == "..") { continue; }
            if (is_dir(BIZUNO_DATA."$path/$file")) { $this->validateNullIndex("$path/$file/"); }
        }
    }

    /**
     * This method tests an uploaded file for validity
     * @param string $index - Index of $_FILES array to find the uploaded file
     * @param string $type [default ''] validates the type of file updated
     * @param mixed $ext [default ''] restrict to specific extension(s)
     * @param string $verbose [default true] Suppress error messages for the upload operation
     * @return boolean - true on success, false if failure
     */
    public function validateUpload($index, $type='', $ext='', $verbose=true)
    {
        if (!isset($_FILES[$index])) { return; }
        if ($_FILES[$index]['error'] && $verbose) { // php error uploading file
            switch ($_FILES[$index]['error']) {
                case UPLOAD_ERR_INI_SIZE:   msgAdd("The uploaded file exceeds the upload_max_filesize directive in php.ini!"); break;
                case UPLOAD_ERR_FORM_SIZE:  msgAdd("The uploaded file exceeds the MAX_FILE_SIZE directive that was specified in the HTML form!"); break;
                case UPLOAD_ERR_PARTIAL:    msgAdd("The uploaded file was only partially uploaded!"); break;
                case UPLOAD_ERR_NO_FILE:    msgAdd("No file was uploaded!"); break;
                case UPLOAD_ERR_NO_TMP_DIR: msgAdd("Missing a temporary folder!"); break;
                case UPLOAD_ERR_CANT_WRITE: msgAdd("Cannot write file!"); break;
                case UPLOAD_ERR_EXTENSION:  msgAdd("Invalid upload extension!"); break;
                default:  msgAdd("Unknown upload error: ".$_FILES[$index]['error']);
            }
        } elseif ($_FILES[$index]['error']) {
            return;
        } elseif (!is_uploaded_file($_FILES[$index]['tmp_name'])) { // file not uploaded through HTTP POST
            return $verbose ? msgAdd("The upload file was not via HTTP POST!") : false;
        } elseif ($_FILES[$index]['size'] == 0) { // upload contains no data, error
            return $verbose ? msgAdd("The uploaded file was empty!") : false;
        }
        if (!empty($type)) {
            $type_match = strpos($_FILES[$index]['type'], $type) !== false ? true : false;
        } else { $type_match = true; }
        if (!empty($ext)) {
            if (!is_array($ext)) { $ext = [$ext]; }
            $fExt      = strtolower(pathinfo($_FILES[$index]['name'], PATHINFO_EXTENSION));
            $ext_match = in_array($fExt, $ext) ? true : false;
        } else { $ext_match = true; }
        if ($type_match && $ext_match) { return true; }
        return $verbose ? msgAdd("Unknown upload validation error. Make sure the file type is correct and it has one of the approved extensions.") : false;
    }

    /**
     * Wrapper to retrieve from a remote server using cURL, this method is portal dependent
     * @param string $url - URL to request data
     * @param string $data - data string, will be attached for get and through setopt as post or an array
     * @param string $type - [default 'get'] Choices are 'get' or 'post'
     * @param array $opts - cURL options
     * @return result if successful, false (plus messageStack error) if fails
     */
    public function cURLGet($url, $data='', $type='get', $opts=[])
    {
        return portalCurl($url, $data, $type, $opts);
    }

    /**
     * Attempts to retrieve information from the PhreeSoft server
     * @param string $method - method to use @PhreeSoft to gather user account info
     * @param array $myData [default: empty] - credentials to use as override to cache settings
     * @param type $type [default: post] - sets the type of transaction, get or post
     * @return array - response from PhreeSoft.com servers with messages
     */
    public function apiPhreeSoft($method='', $myData=[], $type='post')
    {
        $data = array_replace([
            'host'  => BIZUNO_HOST,
            'bizID' => getUserCache('profile', 'biz_id', false, 0),
            'UserID'=> getModuleCache('bizuno', 'settings', 'my_phreesoft_account', 'phreesoft_user'),
            'UserPW'=> getModuleCache('bizuno', 'settings', 'my_phreesoft_account', 'phreesoft_pass')], $myData);
        $result = $this->cURLGet("https://www.phreesoft.com/wp-admin/admin-ajax.php?action=bizuno_ajax&bizRt=myPortal/admin/$method", $data, $type);
        if (!$result) {
            msgAdd("I cannot reach the PhreeSoft.com server, please try again later.");
            return [];
        }
        if (!$output = json_decode($result, true)) {
            msgAdd("Im sorry, I received an unexpected response from the PhreeSoft.com server: ".print_r($result, true));
            return [];
        }
        msgDebug("\nReceived valid response back from PhreeSoft"); // ".print_r($output, true));
        if (!empty($output['message'])) { msgMerge($output['message']); }
        return $output;
    }

    /**
     * Creates a zip file folder,
     * @param string $type - choices are 'file' OR 'all'
     * @param string $localname - local filename
     * @param string $root_folder - where to store the zipped file
     * @return boolean true on success, false on error
     */
    public function zipCreate($type='file', $localname=NULL, $root_folder='/')
    {
        if (!class_exists('ZipArchive')) { return msgAdd(lang('err_io_no_zip_class')); }
        $zip = new \ZipArchive;
        $path = BIZUNO_DATA.$this->dest_dir.$this->dest_file;
        msgDebug("\nCreating Zip Archive in destination path = BIZUNO_DATA/$this->dest_dir$this->dest_file");
        $res = $zip->open($path, \ZipArchive::CREATE);
        if ($res !== true) {
            msgAdd(lang('GEN_BACKUP_FILE_ERROR') . $this->dest_dir);
            return false;
        }
        if ($type == 'folder') {
            msgDebug("\nAdding folder from Zip Archive source path = ".$this->source_dir);
            $this->zipAddFolder(BIZUNO_DATA.$this->source_dir, $zip, $root_folder);
        } else {
            $zip->addFile(BIZUNO_DATA.$this->source_dir . $this->source_file, $localname);
        }
        $zip->close();
        return true;
    }

    /**
     * Recursively adds a folder to an existing ZipArchive
     * @param string $dir - current working folder
     * @param class $zip - active ZIP class
     * @param string $dest_path - sets the destination path of the current folder
     * @return null
     */
    public function zipAddFolder($dir, $zip, $dest_path=NULL)
    {
        if (!is_dir($dir)) { return; }
        $files = scandir($dir);
        foreach ($files as $file) {
            if ($file == "." || $file == "..") { continue; }
            if (is_file($dir . $file)) {
//                msgDebug("\nAdding file = $dir$file to $dest_path$file");
                $zip->addFile($dir.$file, $dest_path.$file);
            } else { // If it's a folder, recurse!
//                msgDebug("\nAdding folder = $dir$file/ to $dest_path$file/");
                $this->zipAddFolder($dir."$file/", $zip, $dest_path."$file/");
            }
        }
    }

    /**
     * Unzips a file and puts it into a filename
     * @param string $file - Source path to zipped file
     * @param string $dest_path - Destination path where unzipped file will be placed
     * @return boolean true if error, false otherwise
     */
    public function zipUnzip($file, $dest_path='')
    {
        if (!class_exists('ZipArchive'))  { return msgAdd(lang('err_io_no_zip_class'));}
        if (!$dest_path) { $dest_path = $this->dest_dir; }
        if (!file_exists($file))          { return msgAdd("Cannot find file $file"); }
        msgDebug("\nUnzipping from: $file to $dest_path");
        $zip = new \ZipArchive;
        if (!$zip->open($file))           { return msgAdd("Problem opening the file $file"); }
        if (!$zip->extractTo($dest_path)) { return msgAdd("Problem extracting the file $file"); }
        $zip->close();
        return true;
    }

    /**
     * Attempts to guess the files mime type based on the extension
     * @param string $filename
     * @return string - mime guess
     */
    public function guessMimetype($filename)
    {
        $ext = strtolower(substr($filename, strrpos($filename, '.')+1));
        msgDebug("\nWorking with extension: $ext");
        switch ($ext) {
            case "aiff":
            case "aif":  return "audio/aiff";
            case "avi":  return "video/msvideo";
            case "bmp":
            case "gif":
            case "png":
            case "tiff": return "image/$ext";
            case "css":  return "text/css";
            case "csv":  return "text/csv";
            case "doc":
            case "dot":  return "application/msword";
            case "docx": return "application/vnd.openxmlformats-officedocument.wordprocessingml.document";
            case "dotx": return "application/vnd.openxmlformats-officedocument.wordprocessingml.template";
            case "docm": return "application/vnd.ms-word.document.macroEnabled.12";
            case "dotm": return "application/vnd.ms-word.template.macroEnabled.12";
            case "gz":
            case "gzip": return "application/x-gzip";
            case "html":
            case "htm":
            case "php":  return "text/html";
            case "jpg":
            case "jpeg":
            case "jpe":  return "image/jpg";
            case "js":   return "application/x-javascript";
            case "json": return "application/json";
            case "mp3":  return "audio/mpeg3";
            case "mov":  return "video/quicktime";
            case "mpeg":
            case "mpe":
            case "mpg":  return "video/mpeg";
            case "pdf":  return "application/pdf";
            case "pps":
            case "pot":
            case "ppa":
            case "ppt":  return "application/vnd.ms-powerpoint";
            case "pptx": return "application/vnd.openxmlformats-officedocument.presentationml.presentation";
            case "potx": return "application/vnd.openxmlformats-officedocument.presentationml.template";
            case "ppsx": return "application/vnd.openxmlformats-officedocument.presentationml.slideshow";
            case "ppam": return "application/vnd.ms-powerpoint.addin.macroEnabled.12";
            case "pptm": return "application/vnd.ms-powerpoint.presentation.macroEnabled.12";
            case "potm": return "application/vnd.ms-powerpoint.template.macroEnabled.12";
            case "ppsm": return "application/vnd.ms-powerpoint.slideshow.macroEnabled.12";
            case "rtf":  return "application/rtf";
            case "swf":  return "application/x-shockwave-flash";
            case "txt":  return "text/plain";
            case "tar":  return "application/x-tar";
            case "wav":  return "audio/wav";
            case "wmv":  return "video/x-ms-wmv";
            case "xla":
            case "xlc":
            case "xld":
            case "xll":
            case "xlm":
            case "xls":
            case "xlt":
            case "xlt":
            case "xlw":  return "application/vnd.ms-excel";
            case "xlsx": return "application/vnd.openxmlformats-officedocument.spreadsheetml.sheet";
            case "xltx": return "application/vnd.openxmlformats-officedocument.spreadsheetml.template";
            case "xlsm": return "application/vnd.ms-excel.sheet.macroEnabled.12";
            case "xltm": return "application/vnd.ms-excel.template.macroEnabled.12";
            case "xlam": return "application/vnd.ms-excel.addin.macroEnabled.12";
            case "xlsb": return "application/vnd.ms-excel.sheet.binary.macroEnabled.12";
            case "xml":  return "application/xml";
            case "zip":  return "application/zip";
            default:
                if (function_exists(__NAMESPACE__.'\mime_content_type')) { # if mime_content_type exists use it.
                    $m = mime_content_type($filename);
                } else {    # if nothing left try shell
                    if (strstr($_SERVER['HTTP_USER_AGENT'], "Windows")) { # Nothing to do on windows
                        return ""; # Blank mime display most files correctly especially images.
                    }
                    if (strstr($_SERVER['HTTP_USER_AGENT'], "Macintosh")) { $m = trim(exec('file -b --mime '.escapeshellarg($filename))); }
                    else { $m = trim(exec('file -bi '.escapeshellarg($filename))); }
                }
                $m = explode(";", $m);
                return trim($m[0]);
        }
    }
}

/**
 * Sends a cURL request to a server
 * @param type $data - array containing settings needed to perform cURL request
 * @return cURL Response, false if error
 */
function doCurlAction($data=[])
{
    if (!isset($data['url']) || !$data['url']) { msgAdd("Error in cURL, bad url"); }
    if (!isset($data['data'])|| !$data['data']){ msgAdd("Error in cURL, no data"); }
    $mode = isset($data['mode']) ? $data['mode'] : 'get';
    $opts = isset($data['opts']) ? $data['opts'] : [];
    $io = new \bizuno\io();
    msgDebug("\nSending data: ".print_r($data['data'], true));
    $cURLresp = $io->cURLGet($data['url'], $data['data'], $mode, $opts);
    msgDebug("\nReceived back from cURL: ".print_r($cURLresp, true));
    return $cURLresp;
}
