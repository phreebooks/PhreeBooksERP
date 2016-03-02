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
//  Path: /modules/contacts/pages/main/template_addbook.php
//
?>
<div title="<?php echo TEXT_ADDRESS_BOOK;?>" >
<?php // *********************** SHIPPING ADDRESSES  *************************************
  if (defined('MODULE_SHIPPING_STATUS')) { // show shipping address for customers and vendors
    echo '  <fieldset>';
    echo '    <legend>' . TEXT_SHIPPING_ADDRESSES . '</legend>';
    echo "    <table id='{$basis->cInfo->contact->type}s_address_form' class='ui-widget' style='border-style:none;width:100%;'>";
    echo '      <tr><td>' . ACT_SHIPPING_MESSAGE . '</td></tr>';
    $basis->cInfo->contact->draw_address_fields($basis->cInfo->contact->type.'s', true, false, false, false);
    echo '    </table>';
    echo '  </fieldset>';
  }
  // *********************** BILLING/BRANCH ADDRESSES  *********************************
    echo '<fieldset>';
    echo '  <legend>' . TEXT_BILLING_ADDRESSES . '</legend>';
    echo "  <table id='{$basis->cInfo->contact->type}b_address_form' class='ui-widget' style='border-collapse:collapse;width:100%;'>";
    echo '    <tr><td>' . ACT_BILLING_MESSAGE . '</td></tr>';
    $basis->cInfo->contact->draw_address_fields($basis->cInfo->contact->type.'b', true, false, false, false);
    echo '  </table>';
    echo '</fieldset>';
?>
</div>