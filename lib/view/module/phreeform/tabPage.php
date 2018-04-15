<?php
/*
 * View for page setup in PhreeForm Designer
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
 * @version    2.x Last Update: 2017-06-01

 * @filesource /lib/view/module/phreeform/tabPage.php
 */

namespace bizuno;

$output['body'] .= '
<table style="border-style:none;margin-left:auto;margin-right:auto;">
    <tbody>
        <tr><td colspan="3">'.html5('title', $data['fields']['Title']).'</td></tr>
        <tr class="panel-header"><th>'.lang('description').'</th><th colspan="2">'.$data['lang']['phreeform_page_layout'].'</th></tr>
        <tr>
            <td rowspan="2">'.html5('description', $data['fields']['Description']).'</td>
            <td>'            .html5('page[size]',  $data['fields']['PageSize']).'</td>
        </tr>
        <tr><td>'.html5('page[orientation]',     $data['fields']['PageOrient']).'</td></tr>
        <tr class="panel-header"><th>'.lang('email_body')."</th><th>".$data['lang']['phreeform_margin_page'].'</th></tr>
        <tr>
            <td rowspan="4">'.html5('emailmessage',     $data['fields']['EmailBody']).'</td>
            <td>'            .html5('page[margin][top]',$data['fields']['MarginTop']).' '.lang('mm').'</td>
        </tr>
        <tr><td>'.html5('page[margin][bottom]',$data['fields']['MarginBottom']).' '.lang('mm').'</td></tr>
        <tr><td>'.html5('page[margin][left]',  $data['fields']['MarginLeft'])  .' '.lang('mm').'</td></tr>
        <tr><td>'.html5('page[margin][right]', $data['fields']['MarginRight']) .' '.lang('mm').'</td></tr>
    </tbody>
</table>';
if ($data['reportType'] == 'rpt') {
	$output['body'] .= '
<table style="border-collapse:collapse;margin-left:auto;margin-right:auto;">
    <thead class="panel-header">
        <tr><th colspan="8">'.$data['lang']['phreeform_header_info'].'</th></tr>
        <tr><th>&nbsp;</th><th>'.lang('show') ."</th><th>".lang('font') ."</th><th>".lang('size') ."</th><th>".lang('color')."</th><th>".lang('align').'</th></tr>
	</thead>
    <tbody>
        <tr>
            <td>'.$data['lang']['name_business'].'</td>
            <td>'.html5('heading[show]', $data['fields']['HeadingShow']) .'</td>
            <td>'.html5('heading[font]', $data['fields']['HeadingFont']) .'</td>
            <td>'.html5('heading[size]', $data['fields']['HeadingSize']) .'</td>
            <td>'.html5('heading[color]',$data['fields']['HeadingColor']).'</td>
            <td>'.html5('heading[align]',$data['fields']['HeadingAlign']).'</td>
        </tr>
        <tr>
            <td>'.$data['lang']['phreeform_page_title1'].' '.html5('title1[text]', $data['fields']['Title1Text']).'</td>
            <td>'.html5('title1[show]', $data['fields']['Title1Show']) .'</td>
            <td>'.html5('title1[font]', $data['fields']['Title1Font']) .'</td>
            <td>'.html5('title1[size]', $data['fields']['Title1Size']) .'</td>
            <td>'.html5('title1[color]',$data['fields']['Title1Color']).'</td>
            <td>'.html5('title1[align]',$data['fields']['Title1Align']).'</td>
        </tr>
        <tr>
            <td>'.$data['lang']['phreeform_page_title2'].' '.html5('title2[text]', $data['fields']['Title2Text']).'</td>
            <td>'.html5('title2[show]', $data['fields']['Title2Show']) .'</td>
            <td>'.html5('title2[font]', $data['fields']['Title2Font']) .'</td>
            <td>'.html5('title2[size]', $data['fields']['Title2Size']) .'</td>
            <td>'.html5('title2[color]',$data['fields']['Title2Color']).'</td>
            <td>'.html5('title2[align]',$data['fields']['Title2Align']).'</td>
        </tr>
        <tr>
            <td colspan="2">'.$data['lang']['phreeform_filter_desc'].'</td>
            <td>'.html5('filter[font]', $data['fields']['FilterFont']) .'</td>
            <td>'.html5('filter[size]', $data['fields']['FilterSize']) .'</td>
            <td>'.html5('filter[color]',$data['fields']['FilterColor']).'</td>
            <td>'.html5('filter[align]',$data['fields']['FilterAlign']).'</td>
        </tr>
        <tr>
            <td colspan="2">'.$data['lang']['phreeform_heading'].'</td>
            <td>'.html5('data[font]', $data['fields']['DataFont']) .'</td>
            <td>'.html5('data[size]', $data['fields']['DataSize']) .'</td>
            <td>'.html5('data[color]',$data['fields']['DataColor']).'</td>
            <td>'.html5('data[align]',$data['fields']['DataAlign']).'</td>
        </tr>
        <tr>
            <td colspan="2">'.lang('totals').'</td>
            <td>'.html5('totals[font]', $data['fields']['TotalFont']) .'</td>
            <td>'.html5('totals[size]', $data['fields']['TotalSize']) .'</td>
            <td>'.html5('totals[color]',$data['fields']['TotalColor']).'</td>
            <td>'.html5('totals[align]',$data['fields']['TotalAlign']).'</td>
        </tr>
    </tbody>
</table>';
}
