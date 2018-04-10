<?php
/*
 * View for DB table stats for all modules
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
 * @copyright  2008-2018, PhreeSoft
 * @license    http://opensource.org/licenses/OSL-3.0 Open Software License (OSL 3.0)
 * @version    2.x Last Update: 2017-02-14

 * @filesource /lib/view/module/bizuno/tabAdminStats.php
 */

namespace bizuno;

$output['body'] .= "
<fieldset> <!-- db table stats --><legend>".$data['lang']['table_stats'].'</legend>
    <table style="border-style:none;width:100%">
    <thead class="panel-header">
        <tr><th>'.lang('table')."</th>
        <th>".$data['lang']['db_engine']."</th>
        <th>".$data['lang']['db_rows']."</th>
        <th>".$data['lang']['db_collation']."</th>
        <th>".lang('size')."</th>
        <th>".$data['lang']['db_next_id'].'</th></tr>
    </thead>
    <tbody>';
foreach ($data['fields']['stats'] as $table) {
	$output['body'] .= "
    <tr>
        <td>". $table['Name']."</td>
        <td>". $table['Engine']."</td>
        <td>". $table['Rows']."</td>
        <td>". $table['Collation']."</td>
        <td>".($table['Data_length']+$table['Index_length'])."</td>
        <td>". $table['Auto_increment']."</td>
    </tr>";
}
$output['body'] .= "
    </tbody>
    </table>
</fieldset>\n";
if (isset($data['statsSrc'])) {	include($data['statsSrc']); } // include any extra stats content
