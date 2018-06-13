<?php
/*
 * View for database restore entry page
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
 * @version    2.x Last Update: 2017-04-18
 * @filesource /lib/view/module/bizuno/divRestore.php
 */

namespace bizuno;

$output['head'] .= '
<script type="text/javascript" src="'.BIZUNO_SRVR.'apps/jquery-file-upload/js/vendor/jquery.ui.widget.js"></script>
<script type="text/javascript" src="'.BIZUNO_SRVR.'apps/jquery-file-upload/js/jquery.iframe-transport.js"></script>
<script type="text/javascript" src="'.BIZUNO_SRVR.'apps/jquery-file-upload/js/jquery.fileupload.js"></script>';

$output['body'] .= '
<table style="width:600px;border-collapse:collapse;margin-left:auto;margin-right:auto;">
 <tbody>
  <tr>
   <td colspan="2">'."\n";
htmlDatagrid($output, $viewData, 'restore');
$output['body'] .= '
   </td>
  </tr>
 </tbody>
 <tfoot class="panel-header">
  <tr>
   <td>'.lang('msg_io_upload_select').html5('file_upload', $viewData['content']['file_upload']).'</td>
   <td style="text-align:right"><progress style="display:none"></progress>'.html5('btn_upload', $viewData['content']['btn_upload'])."</td>
  </tr>
 </tfoot>
</table>";
$output['jsBody'][] = "
jq(function () {
    jq('#file_upload').fileupload({
    url:        '".BIZUNO_AJAX."&p=bizuno/backup/uploadRestore',
    dataType:   'json',
    maxChunkSize:500000,
    multipart:   false,
    add:         function (e, data) { data.context = jq('#btn_upload').show().click(function () { jq('#btn_upload').hide(); jq('progress').show(); data.submit(); }); },
    progressall: function (e, data) { var progress = parseInt(data.loaded / data.total * 100, 10); jq('progress').attr({value:progress,max:100}); },
    done:        function (e, data) { alert('done!'); return; window.location = '".BIZUNO_HOME."&p=bizuno/backup/managerRestore'; }
    });
});";
    
