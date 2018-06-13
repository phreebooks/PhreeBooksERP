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
        <tr><td colspan="3">'.html5('title', $viewData['fields']['Title']).'</td></tr>
        <tr class="panel-header"><th>'.lang('description').'</th><th colspan="2">'.$viewData['lang']['phreeform_page_layout'].'</th></tr>
        <tr>
            <td rowspan="2">'.html5('description', $viewData['fields']['Description']).'</td>
            <td>'            .html5('page[size]',  $viewData['fields']['PageSize']).'</td>
        </tr>
        <tr><td>'.html5('page[orientation]',     $viewData['fields']['PageOrient']).'</td></tr>
        <tr class="panel-header"><th>'.lang('email_body')."</th><th>".$viewData['lang']['phreeform_margin_page'].'</th></tr>
        <tr>
            <td rowspan="4">'.html5('emailmessage',     $viewData['fields']['EmailBody']).'</td>
            <td>'            .html5('page[margin][top]',$viewData['fields']['MarginTop']).' '.lang('mm').'</td>
        </tr>
        <tr><td>'.html5('page[margin][bottom]',$viewData['fields']['MarginBottom']).' '.lang('mm').'</td></tr>
        <tr><td>'.html5('page[margin][left]',  $viewData['fields']['MarginLeft'])  .' '.lang('mm').'</td></tr>
        <tr><td>'.html5('page[margin][right]', $viewData['fields']['MarginRight']) .' '.lang('mm').'</td></tr>
    </tbody>
</table>';
if ($viewData['reportType'] == 'rpt') {
	$output['body'] .= '
<table style="border-collapse:collapse;margin-left:auto;margin-right:auto;">
    <thead class="panel-header">
        <tr><th colspan="8">'.$viewData['lang']['phreeform_header_info'].'</th></tr>
        <tr><th>&nbsp;</th><th>'.lang('show') ."</th><th>".lang('font') ."</th><th>".lang('size') ."</th><th>".lang('color')."</th><th>".lang('align').'</th></tr>
	</thead>
    <tbody>
        <tr>
            <td>'.$viewData['lang']['name_business'].'</td>
            <td>'.html5('heading[show]', $viewData['fields']['HeadingShow']) .'</td>
            <td>'.html5('heading[font]', $viewData['fields']['HeadingFont']) .'</td>
            <td>'.html5('heading[size]', $viewData['fields']['HeadingSize']) .'</td>
            <td>'.html5('heading[color]',$viewData['fields']['HeadingColor']).'</td>
            <td>'.html5('heading[align]',$viewData['fields']['HeadingAlign']).'</td>
        </tr>
        <tr>
            <td>'.$viewData['lang']['phreeform_page_title1'].' '.html5('title1[text]', $viewData['fields']['Title1Text']).'</td>
            <td>'.html5('title1[show]', $viewData['fields']['Title1Show']) .'</td>
            <td>'.html5('title1[font]', $viewData['fields']['Title1Font']) .'</td>
            <td>'.html5('title1[size]', $viewData['fields']['Title1Size']) .'</td>
            <td>'.html5('title1[color]',$viewData['fields']['Title1Color']).'</td>
            <td>'.html5('title1[align]',$viewData['fields']['Title1Align']).'</td>
        </tr>
        <tr>
            <td>'.$viewData['lang']['phreeform_page_title2'].' '.html5('title2[text]', $viewData['fields']['Title2Text']).'</td>
            <td>'.html5('title2[show]', $viewData['fields']['Title2Show']) .'</td>
            <td>'.html5('title2[font]', $viewData['fields']['Title2Font']) .'</td>
            <td>'.html5('title2[size]', $viewData['fields']['Title2Size']) .'</td>
            <td>'.html5('title2[color]',$viewData['fields']['Title2Color']).'</td>
            <td>'.html5('title2[align]',$viewData['fields']['Title2Align']).'</td>
        </tr>
        <tr>
            <td colspan="2">'.$viewData['lang']['phreeform_filter_desc'].'</td>
            <td>'.html5('filter[font]', $viewData['fields']['FilterFont']) .'</td>
            <td>'.html5('filter[size]', $viewData['fields']['FilterSize']) .'</td>
            <td>'.html5('filter[color]',$viewData['fields']['FilterColor']).'</td>
            <td>'.html5('filter[align]',$viewData['fields']['FilterAlign']).'</td>
        </tr>
        <tr>
            <td colspan="2">'.$viewData['lang']['phreeform_heading'].'</td>
            <td>'.html5('data[font]', $viewData['fields']['DataFont']) .'</td>
            <td>'.html5('data[size]', $viewData['fields']['DataSize']) .'</td>
            <td>'.html5('data[color]',$viewData['fields']['DataColor']).'</td>
            <td>'.html5('data[align]',$viewData['fields']['DataAlign']).'</td>
        </tr>
        <tr>
            <td colspan="2">'.lang('totals').'</td>
            <td>'.html5('totals[font]', $viewData['fields']['TotalFont']) .'</td>
            <td>'.html5('totals[size]', $viewData['fields']['TotalSize']) .'</td>
            <td>'.html5('totals[color]',$viewData['fields']['TotalColor']).'</td>
            <td>'.html5('totals[align]',$viewData['fields']['TotalAlign']).'</td>
        </tr>
    </tbody>
</table>';
}
