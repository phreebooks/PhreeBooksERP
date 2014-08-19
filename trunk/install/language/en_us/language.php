<?php
// +-----------------------------------------------------------------+
// |                   PhreeBooks Open Source ERP                    |
// +-----------------------------------------------------------------+
// | Copyright(c) 2008-2014 PhreeSoft      (www.PhreeSoft.com)       |
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
//  Path: /modules/install/language/en_us/language.php
//

define('LANGUAGE','English (US)');
define('HTML_PARAMS','lang="en-US" xml:lang="en-US"');
define('CHARSET', 'UTF-8');
// template_welcome
define('LANGUAGE_TEXT','Available Installation Languages: ');
define('TITLE_WELCOME','Welcome - PhreeBooks Accounting');
define('TEXT_AGREE','Agree');
define('TEXT_DISAGREE','Disagree');
define('INTRO_WELCOME', '<h2>Welcome to PhreeBooks Accounting</h2>
<p>This script will assist in the installation of the application and verify your system meets the minimum requirements. You will need to following information to continue:</p>
<ul>
  <li>An existing mysql database table with access information</li>
  <li>Web server write access (777) to the directories: /includes and /my_files</li>
  <li>An administrator username, email address and password</li>
  <li>SSL Server path information (this is recommended as it can be modified later if necessary)</li>
  <li>The starting fiscal month and year to store journal entries</li>
</ul>
<p>Please confirm your acceptance of the license terms and press Continue to proceed.</p>');
define('DESC_AGREE', 'I have read and agree to the License terms as stated above.');
define('DESC_DISAGREE', 'I have read and do not agree to the License terms as stated above.');
// template_inspect
define('TITLE_INSPECT','Inspect - PhreeBooks Accounting');
define('MSG_INSPECT_ERRORS','The following installation errors have been found.
<ul>
  <li>Errors (in red) must be fixed before the installation script can proceed.</li>
  <li>Cautions (in yellow) will not prevent installation but may prevent modules from operating properly.</li>
</ul>');
define('INSTALL_ERROR_PHP_VERSION','Your php version needs to be 5.2 or greater.');
define('INSTALL_ERROR_REGISER_GLOBALS','Register globals needs to be turned off.');
define('INSTALL_ERROR_SAFE_MODE','Your php configuration is set to run in safe mode. Safe mode needs to be turned off to install PhreeBooks.');
define('INSTALL_ERROR_SESSION_SUPPORT','Your php configuration does not have session support installed. Session support is required to run PhreeBooks.');
define('INSTALL_ERROR_OPENSSL','Your server needs to have openssl installed.');
define('INSTALL_ERROR_CURL','Your servers php application was installed without cURL support. cURL support is required for secure communications to send/receive information to remore applications.');
define('INSTALL_ERROR_UPLOADS','Your servers php application was installed without file upload support. Upload support is required for importing files to PhreeBooks.');
define('INSTALL_ERROR_UPLOAD_DIR','I could not find a temporary upload directory on this server.');
define('INSTALL_ERROR_XML','Your servers php application was installed without XML support. Some modules may not operate with this feature not available.');
define('INSTALL_ERROR_FTP','Your servers php application was installed without FTP support. Some modules may not operate with this feature not available.');
define('INSTALL_ERROR_INCLUDES_DIR','The directory /includes is not writeable. The installer needs access to this directory to create the configuration file.');
define('INSTALL_ERROR_MY_FILES_DIR','The directory /my_files is not writeable. The installer needs access to this directory to store your company files.');
define('TEXT_RECHECK','Re-check');
define('TEXT_INSTALL','Install');
// template_install
define('TITLE_INSTALL','Install - PhreeBooks Accounting');
define('MSG_INSTALL_INTRO','Please enter some information about your company, adminstrator, web server and database.');
define('TEXT_COMPANY_INFO','Company Information');
define('TEXT_ADMIN_INFO','Administrator Information');
define('TEXT_SRVR_INFO','Server Information');
define('TEXT_DB_INFO','Database Information');
define('TEXT_FISCAL_INFO','Fiscal Information');
define('TEXT_COMPANY_NAME','Enter a short name for your company. This will show up in the pull down when logging in.');
define('TEXT_INSTALL_DEMO_DATA','Do you want to install demo data for each module if available? NOTE: If yes is selected, tables will be emptied before the demo data is written. Uses US Retail chart of accounts.');
define('TEXT_USER_NAME','Enter a username for the site administrator');
define('TEXT_USER_PASSWORD','Enter a password for the site administrator');
define('TEXT_UER_PW_CONFIRM','Re-enter a password for the site administrator');
define('TEXT_USER_EMAIL','Enter an email address for the site administrator');
define('TEXT_HTTP_SRVR','Enter the server http URL to the root directory (usually the default value is good as is)');
define('TEXT_USE_SSL','Use SSL for accessing your company (Note: a valid SSL certificate must be installed on the sever). This can be changed later if SSL is not needed at this time.');
define('TEXT_HTTPS_SRVR','Enter the server https URL to the root directory for secure transactions (usually the default value is good as is)');
define('TEXT_DB_HOST','Enter the database host name');
define('TEXT_DB_NAME','Enter the database name (the database must exist on the server)');
define('TEXT_DB_PREFIX','If this database is shared with another application, enter a prefix to use for this installation (use only alphabet characters and underscore):');
define('TEXT_DB_USER','Enter the database username');
define('TEXT_DB_PASSWORD','Enter the database password');
define('TEXT_FY_MONTH_INFO', 'Select a starting month to set as your first accounting period. PhreeBooks will initially set the start at the first day of the selected month as period 1.');
define('TEXT_FY_YEAR_INFO', 'Select a starting year to set as your first fiscal year. The month selected above and this year selection will be the earliest date that journal entries can be made.');

define('ERROR_TEXT_ADMIN_COMPANY_ISEMPTY','The company name is empty');
define('ERROR_TEXT_ADMIN_USERNAME_ISEMPTY', 'The administrator user name cannot be empty');
define('ERROR_TEXT_ADMIN_EMAIL_ISEMPTY', 'The administrator email cannot be empty');
define('ERROR_TEXT_LOGIN_PASS_ISEMPTY', 'The administrator password cannot be empty');
define('ERROR_TEXT_LOGIN_PASS_NOTEQUAL', 'The two passwords entered do not match');
define('ERROR_TEXT_DB_PREFIX_NODOTS','The database Table-Prefix may only contain the characters a-z and _ (underscore)');
define('ERROR_TEXT_DB_HOST_ISEMPTY', 'The database hostname is empty');
define('ERROR_TEXT_DB_NAME_ISEMPTY', 'The database name is empty');
define('ERROR_TEXT_DB_USERNAME_ISEMPTY', 'The database user name cannot empty');
define('ERROR_TEXT_DB_PASSWORD_ISEMPTY', 'The database password cannot be empty');
define('MSG_ERROR_MODULE_INSTALL','There were errors installing module: %s. See above messages for more details.');
define('MSG_ERROR_CANNOT_CONNECT_DB','I cannot connect to the database, please check your settings. error returned: ');
define('MSG_ERROR_INNODB_NOT_ENABLED','MYSQL InnoDB engine is not installed!. PhreeBooks requires the transaction capability of MySQL\'s InnoDB engine to operate properly.');
define('MSG_ERROR_CONFIGURE_EXISTS','The file includes/configure.php exists, this may indicate that PhreeBooks has already been installed or is being re-installed. This file must be removed for the installation to complete successfully!');
// template_finish
define('TITLE_FINISH','Finish - PhreeBooks&trade; Accounting');
define('TEXT_GO_TO_COMPANY','Go To Your Company');
define('INTRO_FINISHED','<h2>Congratulations!</h2>
<h3>You have successfully installed PhreeBooks&trade; Accounting on your system!</h3>
<h2>Next Steps</h2>
<p>A ToDo list has been generated identifying the key actions necessary to operated the installed modules. This list will appear on the homepage dashboard of the administrator. Additional module configuration, preferences and settings can be made through the Company -&gt; Module Admin menu.</p>
<p>For security reasons, you should change your configuration settings file to read-only. This file can be found at <strong>/includes/configure.php</strong>. If you enabled full access the the includes folder for installation, it can be changed back to the previous settings. It is also required that the folder <strong>/install</strong> be removed or renamed to prevent re-installation of the application.</p>
<h2>Documentation and Support</h2>
<p>PhreeBooks includes a built in context sensitive help file system. As with most open source applications, this is a continual work in process but provides general guidance and support.</p>
<p>There is also online documentation available on the PhreeBooks website (<a target="_blank" href="http://www.phreebooks.com">www.PhreeBooks.com</a>). The most current module documentation is available as well as other FAQs and customization/developer support.</p>
<p>Finally, the user forum located on the PhreeBooks website provides community support and allows posting questions, bugs, and suggestions. If you are stumped, feel free to post a question! We have a helpful, friendly, knowledgeable community who welcomes you.</p>');

?>