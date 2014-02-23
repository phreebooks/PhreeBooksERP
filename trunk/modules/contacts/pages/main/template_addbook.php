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
//  Path: /modules/contacts/pages/main/template_addbook.php
//
?>
<div title="<?php echo TEXT_ADDRESS_BOOK;?>" id="tab_addbook">
<?php // *********************** SHIPPING ADDRESSES  *************************************
  if (defined('MODULE_SHIPPING_STATUS')) { // show shipping address for customers and vendors
    echo '  <fieldset>';
    echo '    <legend>' . ACT_CATEGORY_S_ADDRESS . '</legend>';
    echo '    <table id="'.$type.'s_address_form" class="ui-widget" style="border-style:none;width:100%;">';
    echo '      <tr><td>' . ACT_SHIPPING_MESSAGE . '</td></tr>';
    echo draw_address_fields($cInfo, $type.'s', true);
    echo '    </table>';
    echo '  </fieldset>';
  }
  // *********************** BILLING/BRANCH ADDRESSES  *********************************
    echo '<fieldset>';
    echo '  <legend>' . ACT_CATEGORY_B_ADDRESS . '</legend>'; 
    echo '  <table id="'.$type.'b_address_form" class="ui-widget" style="border-collapse:collapse;width:100%;">';
    echo '    <tr><td>' . ACT_BILLING_MESSAGE . '</td></tr>';
    echo draw_address_fields($cInfo, $type.'b', true);
    echo '  </table>';
    echo '</fieldset>';
?>
</div>