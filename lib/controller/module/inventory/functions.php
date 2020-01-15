<?php
/*
 * Inventory module support functions
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
 * @copyright  2008-2020, PhreeSoft, Inc.
 * @license    http://opensource.org/licenses/OSL-3.0 Open Software License (OSL 3.0)
 * @version    3.x Last Update: 2020-01-10
 * @filesource /lib/controller/module/inventory/functions.php
 */

namespace bizuno;

/**
 * Processes a value by format, used in PhreeForm
 * @global array $report - report structure
 * @param mixed $value - value to process
 * @param type $format - what to do with the value
 * @return mixed, returns $value if no formats match otherwise the formatted value
 */
function inventoryProcess($value, $format='')
{
    global $report;
    switch ($format) {
        case 'image_sku':return dbGetValue(BIZUNO_DB_PREFIX."inventory", 'image_with_path', "sku='$value'");
        case 'inv_image':return dbGetValue(BIZUNO_DB_PREFIX."inventory", 'image_with_path', "id='$value'");
        case 'inv_sku':  return ($result = dbGetValue(BIZUNO_DB_PREFIX."inventory", 'sku',                 "id='$value'")) ? $result : $value;
        case 'inv_assy': return dbGetInvAssyCost($value);
        case 'inv_j06':  return ($result = dbGetValue(BIZUNO_DB_PREFIX."inventory", 'description_purchase',"sku='$value'"))? $result : $value;
        case 'inv_j12':  return ($result = dbGetValue(BIZUNO_DB_PREFIX."inventory", 'description_sales',   "sku='$value'"))? $result : $value;
        case 'inv_mv0':  $range = 'm0';
        case 'inv_mv1':  if (empty($range)) { $range = 'm1'; }
        case 'inv_mv3':  if (empty($range)) { $range = 'm3'; }
        case 'inv_mv6':  if (empty($range)) { $range = 'm6'; }
        case 'inv_mv12': if (empty($range)) { $range = 'm12';}
                         return viewInvSales($value, $range); // value passed should be the SKU
        case 'inv_stk':  return viewInvMinStk($value); // value passed should be the SKU
        default:
    }
    if (substr($format, 0, 5) == 'skuPS') { // get the sku price based on the price sheet passed
        if (!$value) { return ''; }
        $fld   = explode(':', $format);
        if (empty($report->currentValues['id']) || empty($report->currentValues['unit_price']) || empty($report->currentValues['full_price'])) { // need to get the sku details
            $inv = dbGetValue(BIZUNO_DB_PREFIX.'inventory', ['id','item_cost','full_price'], "sku='{$value}'");
        } else { $inv = $report->currentValues; }
        $values= ['iID'=>$inv['id'], 'iCost'=>$inv['item_cost'],'iList'=>$inv['full_price'],'iSheetc'=>$fld[1],'iSheetv'=>$fld[1],'cID'=>0,'cSheet'=>$fld[1],'cType'=>'c','qty'=>1];
        $prices= [];
        bizAutoLoad(BIZUNO_LIB."controller/module/inventory/prices.php", 'inventoryPrices');
        $mgr   = new inventoryPrices();
        $mgr->pricesLevels($prices, $values);
        return $prices['price'];
    }
    return $value;
}
