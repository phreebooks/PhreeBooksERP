<?php
// +-----------------------------------------------------------------+
// |                   PhreeBooks Open Source ERP                    |
// +-----------------------------------------------------------------+
// | Copyright(c) 2008-2015 PhreeSoft      (www.PhreeSoft.com)       |
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
//  Path: /install/defaults.php
//

$config_contents = "<" . "?php\n" .
"// +-----------------------------------------------------------------+\n" .
"// |                    Phreedom Open Source ERP                     |\n" .
"// +-----------------------------------------------------------------+\n" .
"// | Copyright(c) 2008-2015 PhreeSoft      (www.PhreeSoft.com)       |\n" .
"// +-----------------------------------------------------------------+\n" .
"// | This program is free software: you can redistribute it and/or   |\n" . 
"// | modify it under the terms of the GNU General Public License as  |\n" .
"// | published by the Free Software Foundation, either version 3 of  |\n" .
"// | the License, or any later version.                              |\n" .
"// |                                                                 |\n" .
"// | This program is distributed in the hope that it will be useful, |\n" .
"// | but WITHOUT ANY WARRANTY; without even the implied warranty of  |\n" .
"// | MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the   |\n" .
"// | GNU General Public License for more details.                    |\n" .
"// +-----------------------------------------------------------------+\n" .
"// Path: /includes/configure.php\n" .
"//\n\n" .
"// Set the webserver and path parameters\n" .
"define('HTTP_SERVER',  'DEFAULT_HTTP_SERVER'); // Main webserver: eg, http://localhost\n" .
"define('HTTPS_SERVER', 'DEFAULT_HTTPS_SERVER'); // Secure webserver: eg, https://localhost\n" .
"// Enable secure webserver for admin areas?\n" .
"define('ENABLE_SSL_ADMIN', 'DEFAULT_ENABLE_SSL_ADMIN'); // valid values are true and false\n\n" .
"// NOTE: be sure to leave the trailing '/' at the end of these lines if you make changes!\n" .
"// Relative (virtual) path from top of your webspace (ie: under the public_html or httpdocs folder)\n" .
"define('DIR_WS_ADMIN', 'DEFAULT_DIR_WS_ADMIN');\n" .
"// Complete physical path to your Phreedom root directory.\n" .
"define('DIR_FS_ADMIN', 'DEFAULT_DIR_FS_ADMIN'); // Physical path eg: /var/www/public_html/app_dir/\n\n" .
"// Set the default language, this can be changed at login or through the url with GET parameters.\n" .
"define('DEFAULT_LANGUAGE','DEFAULT_DEFAULT_LANGUAGE');\n\n" .
"// Set the default company, this can be changed at login or through the url with GET parameters.\n" .
"define('DEFAULT_COMPANY','');\n\n" .
"// Set the database information, the login, passwrod and db name are stored in the company directory\n" .
"define('DB_TYPE', 'DEFAULT_DB_TYPE');\n" .
"define('DB_PREFIX', 'DEFAULT_DB_PREFIX');\n" .
"?" . ">\n";

?>