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
//  Path: /modules/phreeform/language/en_us/admin.php
//
// Module information
define('MODULE_PHREEFORM_TITLE','PhreeForm Module');
define('MODULE_PHREEFORM_DESCRIPTION','The phreeform module contains all the report and form tools needed to print reports in PDF or HTML format.');
// title
define('BOX_PHREEFORM_MODULE_ADM', 'PhreeForm Admin');
// Headings and Helpers
define('PB_CONVERT_REPORTS','Convert .txt Reports to PhreeForm');
// admin defines
define('PB_CONVERT_SAVE_ERROR','There was and error saving the converted report: %s');
define('PB_CONVERT_SUCCESS','Successfully converted %s reports and forms. If any had errors during the conversion, they will appear in a prior message.');
// Module configuration defaults
define('PF_DEFAULT_COLUMN_WIDTH_TEXT','Sets the default width to use for column widths of reports in mm (default: 25)');
define('PF_DEFAULT_MARGIN_TEXT','Sets the default page margin to use for reports and forms in mm (default: 8)');
define('PF_DEFAULT_TITLE1_TEXT','Sets the default title text to print as heading 1 for reports (default: %reportname%)');
define('PF_DEFAULT_TITLE2_TEXT','Sets the default title text to print as heading 2 for reports (default: Report Generated %date%)');
define('PF_DEFAULT_PAPERSIZE_TEXT','Sets the default papersize to use for reports and forms (default: Letter)');
define('PF_DEFAULT_ORIENTATION_TEXT','Sets the default page orientation for reports and forms (default: Portrait)');
define('PF_DEFAULT_TRIM_LENGTH_TEXT','Sets the trim length of report and form names when listing in directory format (default: 25)');
define('PF_DEFAULT_ROWSPACE_TEXT','Sets the separation between the heading rows for reports (default: 2)');
define('PDF_APP_TEXT','Sets the default PDF generator application. Note: TCPDF is required for UTF-8 and Bar Code generation.');
// Tools
define('PHREEFORM_TOOLS_REBUILD_TITLE','PhreeForm Stucture Verification / Rebuild');
define('PHREEFORM_TOOLS_REBUILD_DESC','This tool verifies, and rebuilds the report and form structure. It will re-load the folder structure, make sure there are no orphaned reports and clean out any phreefom table entries that don\'t have a report/form file associated with it');
define('PHREEFORM_TOOLS_REBUILD_SUBMIT','Start Structure Verify/Rebuild');
define('PHREEFORM_TOOLS_REBUILD_SUCCESS','Successfully rebuilt report table. The number of reports rebuilt was %s. %s orphaned reports were placed in the Miscellaneous folder.');
?>