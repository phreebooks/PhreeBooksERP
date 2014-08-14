/*
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
//  Path: /modules/phreedom/language/en_us/jquery_i18n.js
*/

// English (US) initialization for the jQuery, plugins and other javascript plugins

// This (English) is a sample file for translation and is loaded by default.
// Translated versions for other languages can be found at: 
// http://jquery-ui.googlecode.com/svn/trunk/ui/i18n/
jQuery(function($){
	$.datepicker.regional['en'] = {
		closeText: 'Close',
		prevText: 'Prev',
		nextText: 'Next',
		currentText: 'Today',
		monthNames: ['January','February','March','April','May','June','July','August','September','October','November','December'],
		monthNamesShort: ['Jan', 'Feb', 'Mar', 'Apr', 'May', 'Jun',	'Jul', 'Aug', 'Sep', 'Oct', 'Nov', 'Dec'],
		dayNames: ['Sunday', 'Monday', 'Tuesday', 'Wednesday', 'Thursday', 'Friday', 'Saturday'],
		dayNamesShort: ['Sun', 'Mon', 'Tue', 'Wed', 'Thu', 'Fri', 'Sat'],
		dayNamesMin: ['Su','Mo','Tu','We','Th','Fr','Sa'],
		weekHeader: 'Wk',
		dateFormat: js_cal_date_format ? js_cal_date_format : 'mm/dd/yy',
		firstDay: 1,
		isRTL: false,
		showMonthAfterYear: false,
		yearSuffix: ''
	};
	$.datepicker.setDefaults($.datepicker.regional['en']);
});

// jquery DataTables translation 
// More translations can be found at http://datatables.net/plug-ins/i18n
var dataTables_i18n = { "oLanguage": 
  {
    "sProcessing":   "Processing...",
    "sLengthMenu":   "Show _MENU_ entries",
    "sZeroRecords":  "No matching records found",
    "sInfo":         "Showing _START_ to _END_ of _TOTAL_ entries",
    "sInfoEmpty":    "Showing 0 to 0 of 0 entries",
    "sInfoFiltered": "(filtered from _MAX_ total entries)",
    "sInfoPostFix":  "",
    "sSearch":       "Search:",
    "sUrl":          "",
    "oPaginate": {
        "sFirst":    "First",
        "sPrevious": "Previous",
        "sNext":     "Next",
        "sLast":     "Last"
    }
  }
};

