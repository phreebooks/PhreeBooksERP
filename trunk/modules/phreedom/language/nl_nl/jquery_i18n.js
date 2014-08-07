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
//  Path: /modules/phreedom/language/nl_nl/jquery_i18n.js
*/

// Dutch initialization for the jQuery, plugins and other javascript plugins

// This (English) is a sample file for translation and is loaded by default.
// Translated versions for other languages can be found at: 
// http://jquery-ui.googlecode.com/svn/trunk/ui/i18n/

jQuery(function($){
	$.datepicker.regional['nl'] = {
		closeText: 'Sluiten',
		prevText: 'Vorige',
		nextText: 'Volgende',
		currentText: 'Vandaag',
		monthNames: ['Januari','Februari','Maart','April','Mei','Juni','Juli','Augustus','September','Oktober','November','December'],
		monthNamesShort: ['Jan', 'Feb', 'Mrt', 'Apr', 'Mei', 'Jun', 'Jul', 'Aug', 'Sep', 'Oct', 'Nov', 'Dec'],
		dayNames: ['Zondag', 'Maandag', 'Dinsdag', 'Woensdag', 'Donderdag', 'Vrijdag', 'Zaterdag'],
		dayNamesShort: ['Zon', 'Maa', 'Din', 'Woe', 'Don', 'Vri', 'Zat'],
		dayNamesMin: ['Zo','Ma','Di','Wo','Do','Vr','Za'],
		weekHeader: 'Wk',
		dateFormat: js_cal_date_format ? js_cal_date_format : 'dd/mm/yy',
		firstDay: 1,
		isRTL: false,
		showMonthAfterYear: false,
		yearSuffix: ''};
	$.datepicker.setDefaults($.datepicker.regional['nl']);
});
// jquery DataTables translation 
// More translations can be found at http://datatables.net/plug-ins/i18n
var dataTables_i18n = { "oLanguage": 
  {
    "sProcessing":   "Verwerken...",
    "sLengthMenu":   "Toon _MENU_ regels",
    "sZeroRecords":  "Geen passende resultaten gevonden",
    "sInfo":         "Toon _START_ tot _END_ van _TOTAL_ regels",
    "sInfoEmpty":    "Toon 0 tot 0 van 0 regels",
    "sInfoFiltered": "(gefiltereerd van _MAX_ totaal regels)",
    "sInfoPostFix":  "",
    "sSearch":       "Zoek:",
    "sUrl":          "",
    "oPaginate": {
        "sFirst":    "Eerste",
        "sPrevious": "Vorige",
        "sNext":     "Volgende",
        "sLast":     "Laatste"
    }
  }
};

