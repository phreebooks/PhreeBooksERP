<?php
/*
 * Language translation for PhreeForm module
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
 * @copyright  2008-2019, PhreeSoft, Inc.
 * @license    http://opensource.org/licenses/OSL-3.0  Open Software License (OSL 3.0)
 * @version    3.x Last Update: 2019-02-20
 * @filesource /locale/en_US/module/phreeform/language.php
 */

$lang = [
    'title' => 'PhreeForm',
    'description' => 'The phreeform module contains all the tools needed to create and generate reports and forms. <b>NOTE: This is a core module and cannot be removed!</b>',
    // Settings
    'default_font_lbl' => 'Font',
    'default_font_tip' => '[Default: Helvetica] Sets the default font to use for standard reports. This setting will need to be changed to use UTF-8 character sets and other fonts using special characters.',
    'column_width_lbl' => 'Receipt Width',
    'column_width_tip' => 'Sets the default width of a column on reports. This setting will truncate long data strings to better format reports.',
    'margin_lbl' => 'Page Margin',
    'margin_tip' => 'Sets the default margin when creating new reports. ',
    'title1_lbl' => 'Title 1 Text',
    'title1_tip' => 'Sets the first report heading line. See substitution table for dynamically replacable fields. Default: %reportname%',
    'title2_lbl' => 'Title 2 Text',
    'title2_tip' => 'Sets the second report heading line. See substitution table for dynamically replacable fields. Default: Report Generated %date%',
    'paper_size_lbl' => 'Paper Size',
    'paper_size_tip' => 'Sets the default paper size to use when creating new reports and forms.',
    'orientation_lbl' => 'Paper Orientation',
    'orientation_tip' => 'Sets the default paper orientation when creating new reports and forms.',
    'truncate_len_lbl' => 'Truncate Length',
    'truncate_len_tip' => 'Sets the maximum number of characters to display in a column on reports. This setting will truncate long data strings to better format reports.',
    // Labels
    'lbl_serial_form' => 'Check if this is a serial form (i.e. receipt)',
    'lbl_set_printed_flag' => 'Set Printed Flag',
    'lbl_phreeform_contact' => 'Enter log on email',
    'lbl_phreeform_email' => 'Default email address',
    'lbl_skip_null' => 'Skip if No Data Field',
    'date_default_selected' => 'Default Date Selected',
    'import_upload_report' => 'Select report to upload and import',
    'group_total' => 'Group Total:',
    'report_total' => 'Report Total:',
    'new_form' => 'New Form',
    'new_report' => 'New Report',
    // Messages
    'msg_printed_set' => 'Sets the field selected to 1 after each form has been generated. The field must be in the same table as the form page break field.',
    'msg_download_filename' => 'Download Filename Source:',
    'msg_replace_existing' => 'Replace existing file, if present',
    // Error Messages
    'err_pf_field_empty' => 'The Field has no information, this is a report build problem! Please edit the report and verify all fields are valid. The field that failed is : ',
    'err_rename_fail' => 'The report was not renamed, the proper id and/or title was not passed!',
    'err_copy_fail' => 'The report was not copied, the proper id and/or title was not passed!',
    'err_group_empty' => 'No reports could be found in group: %s',
    // Buttons
    'btn_import_all' => 'Import All From This List',
    'btn_import_selected' => 'Import Selected Report',
    // General
    'my_reports' => 'My Reports',
    'my_favorites' => 'My Favorites',
    'recent_reports' => 'Recent Reports/Forms',
    'name_business' => 'Business Name',
    'filter_list' => 'Filter List',
    'use_periods' => 'Use Accounting Periods',
    'align' => 'Align',
    'mail_out' => 'eMail Sent',
    'show_total_only' => 'Show only group totals',
    'sort_list' => 'Sorting List',
    'encoded_table_title' => 'For table data encoded in a single db field, the field below is required',
    // Form element types
    'fld_type_barcode' => 'Bar Code Image',
    'fld_type_data_block' => 'Data Block',
    'fld_type_data_line' => 'Data Line',
    'fld_type_data_table' => 'Data Table',
    'fld_type_data_table_dup' => 'Copy of Data Table',
    'fld_type_data_total' => 'Data Total',
    'fld_type_letter_tpl' => 'Letter Template',
    'fld_type_letter_data' => 'Letter Data',
    'fld_type_fixed_txt' => 'Fixed Text Field',
    'fld_type_image' => 'Image - JPG or PNG',
    'fld_type_image_link' => 'Image Link',
    'fld_type_line' => 'Line',
    'fld_type_page_num' => 'Page Number',
    'fld_type_rectangle' => 'Rectangle',
    'fld_type_biz_data' => 'Business Data',
    'fld_type_biz_block' => 'Business Block',
    'color' => 'Color',
    'color_red' => 'Red',
    'color_green' => 'Green',
    'color_blue' => 'Blue',
    'color_black' => 'Black',
    'color_orange' => 'Orange',
    'color_yellow' => 'Yellow',
    'color_white' => 'White',
    'abscissa' => 'Abscissa',
    'ordinate' => 'Ordinate',
    'border' => 'Border',
    'column_break' => 'Column Break',
    'display_on' => 'Display On',
    'end_position' => 'Custom End Position',
    'fill_area' => 'Fill Area',
    'formatting' => 'Formatting',
    'horizontal' => 'Horizontal',
    'join_type' => 'Join Type',
    'table_name' => 'Table Name',
    'page_break' => 'Page Break',
    'page_break_field' => 'Form Page Break Field',
    'page_all' => 'All Pages',
    'page_first' => 'First Page',
    'page_last' => 'Last Page',
    'points' => 'Points',
    'processing' => 'Processing',
    'total_width' => 'Total Width',
    'truncate_fit' => 'Truncate to fit',
    'vertical' => 'Vertical',
    // Report General
    'phreeform_import' => 'Report/Form Import Tool',
    'phreeform_title_edit' => 'Phreeform Editor',
    'phreeform_title_db' => 'Database',
    'phreeform_title_field' => 'Fields',
    'phreeform_title_page' => 'Page',
    'phreeform_paper_size' => 'Paper Size',
    'phreeform_orientation' => 'Orientation',
    'orient_portrait' => 'Portrait',
    'orient_landscape' => 'Landscape',
    'paper_legal' => 'Legal',
    'paper_letter' => 'Letter',
    'paper_tabloid' => 'Tabloid',
    // PhreeForm Page setup
    'phreeform_barcode_type' => 'Select Barcode Type',
    'phreeform_heading_2' => 'Report Generated %date%',
    'phreeform_groups' => 'Grouped by',
    'phreeform_sorts' => 'Sorted by',
    'phreeform_filter' => 'Filters',
    'phreeform_criteria_type' => 'Type of Criteria',
    'phreeform_current_user' => 'Current User',
    'phreeform_date_field' => 'Date Fieldname',
    'phreeform_date_info' => 'Report Date Information',
    'phreeform_field_break' => 'Field to page break forms',
    'phreeform_date_list' => 'Date Field List (check all that apply) For date independent reports select Date Field List and ONLY check the All box.',
    'phreeform_line_end' => 'or Select Line End Position (mm)',
    'phreeform_line_type' => 'Select Line Layout',
    'phreeform_filter_desc' => 'Report Filter Description',
    'phreeform_header_info' => 'Header Information / Formatting',
    'phreeform_page_layout' => 'Page Layout',
    'phreeform_margin_top' => 'Top Margin',
    'phreeform_margin_bottom' => 'Bottom Margin',
    'phreeform_margin_left' => 'Left Margin',
    'phreeform_margin_right' => 'Right Margin',
    'phreeform_margin_page' => 'Page Margins',
    'phreeform_page_title1' => 'Report Title 1',
    'phreeform_page_title2' => 'Report Title 2',
    'phreeform_heading' => 'Report Data Heading',
    'phreeform_text_disp' => 'Text to Display',
    'phreeform_reports_available' => 'Reports/Forms Available to Import',
    'phreeform_special_class' => 'Special Report (Programmers Only)',
    'phreeform_page_break' => 'Insert page break after each group',
    'phreeform_form_select' => 'Select Form to Output:',
    'phreeform_email_subject'=> "%s from %s",
    'in_list' => 'In List (comma delim)',
    // Tips
    'tip_phreeform_contact_log' => 'When a contact ID field is present, a log entry will be made in the database record of the ID selected. The field must be a valid ID (integer) and exist in the contacts table.',
    'tip_phreeform_database_syntax' => 'Notes: The link equations must be in SQL snytax.<br />Tables may be identified by the table name from the db as they appear in the drop-down menu without prefixes.<br />Press the Save icon after altering the table list to update the availalbe fields in the Fields tab.',
    'tip_phreeform_field_settings' => "Notes: Double click on a row to edit.\nIf multiple fields are displayed in the same column, the field with the largest column width will determine the width of column.",
];
