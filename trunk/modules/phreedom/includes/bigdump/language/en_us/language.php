<?php
// +-----------------------------------------------------------------+
// |                   PhreeBooks Open Source ERP                    |
// +-----------------------------------------------------------------+
// | Copyright(c) 2008-2013 PhreeSoft, LLC (www.PhreeSoft.com)       |
// +-----------------------------------------------------------------+
// | This program is free software: you can redistribute it and/or   |
// | modify it under the terms of the GNU General Public License as  |
// | published by the Free Software Foundation, either version 3 of  |
// | the License, or any later version.                              |
// |                                                                 |
// | This program is distributed in the hope that it will be useful, |
// | but WITHOUT ANY WARRANTY; without even the implied warranty of  |
// | MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the   |
// | GNU General Public License for more details.                    |
// +-----------------------------------------------------------------+
//  Path: /modules/phreedom/includes/bigdump/language/en_us/language.php
//

define('TEXT_FILE_NAME','Filename');
define('TEXT_MISC','Misc');
define('TEXT_QUERY','Query: ');
define('TEXT_MYSQL','MYSQL: ');
define('TEXT_PROCESSING_FILE','Processing file: ');
define('TEXT_STARTING_LINE','Starting from line: ');
define('TEXT_TO_GO','To go');
define('TEXT_DONE','Done');
define('TEXT_SESSION','Session');
define('TEXT_LINES','Lines');
define('TEXT_QUERIES','Queries');
define('TEXT_BYTES','Bytes');
define('TEXT_KB','KB');
define('TEXT_MB','MB');
define('TEXT_PERCENT','%');
define('TEXT_PERCENT_BAR','% bar');
define('TEXT_STOP','STOP');
define('TEXT_PRESS','Press ');

define('BIGDUMP_INTRO','This script is a modified version of the BigDump script developed by <a href="mailto:alexey@ozerov.de">Alexey Ozerov</a> - <a href="http://www.ozerov.de/bigdump.php" target="_blank">BigDump Home</a>');
define('BIGDUMP_FILE_EXISTS','File %s already exists! Delete and upload again!');
define('BIGDUMP_UPLOAD_TYPES','You may only upload .sql .gz or .csv files.');
define('BIGDUMP_ERROR_MOVE','Error moving uploaded file %s to the %s');
define('BIGDUMP_ERROR_PERM','Check the directory permissions for %s (must be 777)!');
define('BIGDUMP_FILE_SAVED','Uploaded file saved as %s');
define('BIGDUMP_ERROR_UPLOAD','Error uploading file ');
define('BIGDUMP_REMOVED',' was removed successfully');
define('BIGDUMP_FAIL_REMOVE','Can not remove ');
define('BIGDUMP_START_IMP','Start Import');
define('BIGDUMP_START_LOC',"into %s at %s");
define('BIGDUMP_DEL_FILE','Delete file');
define('BIGDUMP_NO_FILES','No uploaded files found in the working directory');
define('BIGDUMP_ERROR_DIR','Error listing directory %s');
define('BIGDUMP_FROM_LOC','from %s into %s at %s');
define('BIGDUMP_UPLOAD_A','Upload form disabled. Permissions for the working directory <i>%s</i> <b>must be set to 777</b> in order ');
define('BIGDUMP_UPLOAD_B',"to upload files from here. Alternatively you can upload your dump files via FTP to directory: ");
define('BIGDUMP_UPLOAD_C','You can now upload your dump file up to %s bytes (%s Mbytes) ');
define('BIGDUMP_UPLOAD_D',"directly from your browser to the server. Alternatively you can upload your dump files of any size via FTP to directory: ");
define('BIGDUMP_OPEN_FAIL','Can\'t open %s for import');
define('BIGDUMP_BAD_NAME','Please, check that your dump file name contains only alphanumerical characters, and rename it accordingly, for example: %s.<br />Or, specify \%s in bigdump.php with the full filename. <br />Or, you have to upload the %s to the server first.');
define('BIGDUMP_NO_SEEK','I can\'t seek into %s');
define('BIGDUMP_IMPORT_MSG_1','UNEXPECTED: Non-numeric values for start and offset');
define('BIGDUMP_IMPORT_MSG_2','Error when deleting entries from %s.');
define('BIGDUMP_IMPORT_MSG_3','UNEXPECTED: Can\'t set file pointer behind the end of file');
define('BIGDUMP_IMPORT_MSG_4','UNEXPECTED: Can\'t set file pointer to offset: ');
define('BIGDUMP_IMPORT_MSG_5','Stopped at the line %s.');
define('BIGDUMP_IMPORT_MSG_6','At this place the current query is from csv file, but %s was not set.');
define('BIGDUMP_IMPORT_MSG_7','You have to tell where you want to send your data.');
define('BIGDUMP_IMPORT_MSG_8','At this place the current query includes more than %s dump lines. That can happen if your dump file was created by some tool which doesn\'t place a semicolon followed by a linebreak at the end of each query, or if your dump contains extended inserts. Please read the BigDump FAQs for more infos.');
define('BIGDUMP_IMPORT_MSG_9','Error at the line %s: ');
define('BIGDUMP_IMPORT_MSG_10','UNEXPECTED: Can\'t read the file pointer offset');
define('BIGDUMP_IMPORT_MSG_11','Now I\'m <b>waiting %s milliseconds</b> before starting next session...');
define('BIGDUMP_IMPORT_MSG_12','Continue from the line %s (Enable JavaScript to do it automatically)');
define('BIGDUMP_IMPORT_MSG_13'," to abort the import <b>OR WAIT!</b>");
define('BIGDUMP_IMPORT_MSG_14','Stopped on error');
define('BIGDUMP_IMPORT_MSG_15','Start from the beginning');
define('BIGDUMP_IMPORT_MSG_16',' (DROP the old tables before restarting)');
define('BIGDUMP_IMPORT_MSG_17','DATABASE IMPORT SUCCESSFULL - PRESS HERE TO LOGOUT/LOGIN TO COMPLETE THE RESTORE');

?>