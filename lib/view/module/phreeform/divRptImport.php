<?php
/*
 * View for importing Reports/forms
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
 * @version    2.x Last Update: 2017-05-14

 * @filesource /lib/view/module/phreeform/divRptImport.php
 */

namespace bizuno;

require_once(BIZUNO_LIB."controller/module/phreeform/functions.php");

$output['body'] .= html5('frmImport', $viewData['forms']['frmImport']);
$output['body'] .= html5('imp_name', array('attr'=>array('type'=>'hidden'))).'
 <table class="ui-widget" style="border-style:none;margin-left:auto;margin-right:auto;">
  <tbody>
   <tr>
    <td>'.html5('new_name', $viewData['fields']['new_name']).'</td>
    <td>'.html5('cbReplace', $viewData['fields']['cbReplace']).'</td>
   </tr>
   <tr class="panel-header"><th colspan="2">&nbsp;</th></tr>
   <tr>
    <td>'.html5('fileUpload',$viewData['fields']['fileUpload']).'</td>
    <td style="text-align:right;">'.html5('btnUpload', $viewData['fields']['btnUpload']).'</td>
   </tr>
   <tr class="panel-header"><th colspan="2">'.$viewData['lang']['phreeform_reports_available'].'</th></tr>
   <tr>
     <td>'.html5('selModule',$viewData['fields']['selModule']).html5('selLang',$viewData['fields']['selLang']).html5('btnSearch',$viewData['fields']['btnSearch']).'</td>
     <td style="text-align:right;">'.html5('btnImportAll',$viewData['fields']['btnImportAll']).'</td>
   </tr>
   <tr><td colspan="2">'.ReadDefReports('selReports').'</td></tr>
   <tr><td colspan="2" style="text-align:right;">'.html5('btnImport',$viewData['fields']['btnImport']).'</td></tr>
  </tbody>
 </table>
</form>';
$output['jsBody'][] = "ajaxForm('frmImport');";
