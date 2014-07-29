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
//  Path: /modules/phreedom/pages/admin/template_tab_company.php
//

?>
<div title="<?php echo TEXT_MY_COMPANY;?>" id="tab_company">
<table class="ui-widget" style="border-style:none;width:100%">
 <thead class="ui-widget-header">
  <tr><th colspan="2"><?php echo TEXT_MY_COMPANY; ?></th></tr>
 </thead>
 <tbody class="ui-widget-content">
  <tr>
    <td><?php echo CD_01_16_DESC; ?></td>
    <td><?php echo html_input_field('company_id', $_POST['company_id'] ? $_POST['company_id'] : COMPANY_ID, ''); ?></td>
  </tr>
  <tr>
    <td><?php echo TEXT_THE_NAME_OF_MY_COMPANY; ?></td>
    <td><?php echo html_input_field('company_name', $_POST['company_name'] ? $_POST['company_name'] : COMPANY_NAME, ''); ?></td>
  </tr>
  <tr>
    <td><?php echo TEXT_THE_DEFAULT_NAME_OR_IDENTIFIER_TO_USE_FOR_ALL_RECEIVABLE_OPERATIONS; ?></td>
    <td><?php echo html_input_field('ar_contact_name', $_POST['ar_contact_name'] ? $_POST['ar_contact_name'] : AR_CONTACT_NAME, ''); ?></td>
  </tr>
  <tr>
    <td><?php echo TEXT_THE_DEFAULT_NAME_OR_IDENTIFIER_TO_USE_FOR_ALL_PAYABLE_OPERATIONS; ?></td>
    <td><?php echo html_input_field('ap_contact_name', $_POST['ap_contact_name'] ? $_POST['ap_contact_name'] : AP_CONTACT_NAME, ''); ?></td>
  </tr>
  <tr>
    <td><?php echo TEXT_FIRST_ADDRESS_LINE; ?></td>
    <td><?php echo html_input_field('company_address1', $_POST['company_address1'] ? $_POST['company_address1'] : COMPANY_ADDRESS1, ''); ?></td>
  </tr>
  <tr>
    <td><?php echo TEXT_SECOND_ADDRESS_LINE; ?></td>
    <td><?php echo html_input_field('company_address2', $_POST['company_address2'] ? $_POST['company_address2'] : COMPANY_ADDRESS2, ''); ?></td>
  </tr>
  <tr>
    <td><?php echo TEXT_THE_CITY_OR_TOWN_WHERE_THIS_COMPANY_IS_LOCATED; ?></td>
    <td><?php echo html_input_field('company_city_town', $_POST['company_city_town'] ? $_POST['company_city_town'] : COMPANY_CITY_TOWN, ''); ?></td>
  </tr>
  <tr>
    <td><?php echo TEXT_THE_STATE_OR_REGION_WHERE_THIS_COMPANY_IS_LOCATED; ?></td>
    <td><?php echo html_input_field('company_zone', $_POST['company_zone'] ? $_POST['company_zone'] : COMPANY_ZONE, ''); ?></td>
  </tr>
  <tr>
    <td><?php echo TEXT_POSTAL_OR_ZIP_CODE_WHERE_THIS_COMPANY_IS_LOCATED; ?></td>
    <td><?php echo html_input_field('company_postal_code', $_POST['company_postal_code'] ? $_POST['company_postal_code'] : COMPANY_POSTAL_CODE, ''); ?></td>
  </tr>
  <tr>
    <td><?php echo TEXT_THE_COUNTRY_THIS_COMPANY_IS_LOCATED; ?></td>
    <td><?php echo html_pull_down_menu('company_country', gen_get_countries(), $_POST['company_country'] ? $_POST['company_country'] : COMPANY_COUNTRY, ''); ?></td>
  </tr>
  <tr>
    <td><?php echo TEXT_ENTER_THE_COMPANYS_PRIMARY_TELEPHONE_NUMBER; ?></td>
    <td><?php echo html_input_field('company_telephone1', $_POST['company_telephone1'] ? $_POST['company_telephone1'] : COMPANY_TELEPHONE1, ''); ?></td>
  </tr>
  <tr>
    <td><?php echo TEXT_SECONDARY_TELEPHONE_NUMBER; ?></td>
    <td><?php echo html_input_field('company_telephone2', $_POST['company_telephone2'] ? $_POST['company_telephone2'] : COMPANY_TELEPHONE2, ''); ?></td>
  </tr>
  <tr>
    <td><?php echo TEXT_ENTER_THE_COMPANYS_FAX_NUMBER; ?></td>
    <td><?php echo html_input_field('company_fax', $_POST['company_fax'] ? $_POST['company_fax'] : COMPANY_FAX, ''); ?></td>
  </tr>
  <tr>
    <td><?php echo TEXT_ENTER_THE_GENERAL_COMPANY_EMAIL_ADDRESS; ?></td>
    <td><?php echo html_input_field('company_email', $_POST['company_email'] ? $_POST['company_email'] : COMPANY_EMAIL, ''); ?></td>
  </tr>
  <tr>
    <td><?php echo TEXT_THE_COMPANY_WEBSITE_WITHOUT_THE_HTTP; ?></td>
    <td><?php echo html_input_field('company_website', $_POST['company_website'] ? $_POST['company_website'] : COMPANY_WEBSITE, ''); ?></td>
  </tr>
  <tr>
    <td><?php echo TEXT_ENTER_THE_COMPANYS_FEDERAL_TAX_ID_NUMBER; ?></td>
    <td><?php echo html_input_field('tax_id', $_POST['tax_id'] ? $_POST['tax_id'] : TAX_ID, ''); ?></td>
  </tr>
 </tbody>
</table>
</div>
