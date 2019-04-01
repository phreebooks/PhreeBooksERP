<?php
/*
 * Renders a report in html format for screen display
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
 * @license    http://opensource.org/licenses/OSL-3.0 Open Software License (OSL 3.0)
 * @version    3.x Last Update: 2019-03-27
 * @filesource /controller/module/phreeform/renderHTML.php
 */

namespace bizuno;

require_once(BIZUNO_3P_TCPDF."config/bizuno_config.php");

class HTML
{
    public $moduleID = 'phreeform';

    function __construct($data, $report)
    {
        $this->lang       = getLang($this->moduleID);
        $this->defaultFont= getModuleCache('phreeform','settings','general','default_font','helvetica');
        $this->FillColor  = '#E0EBFF';
        $this->HdColor    = '#00BFFF';
        $this->ttlColor   = '#CCCCCC';
        // set some more deaults if not specified in $report
        if (!isset($report->filter->color)){ $report->filter->color = '0'; } // black
        if (!isset($report->filter->size)) { $report->filter->size  = '10'; }
        if (!isset($report->filter->align)){ $report->filter->align = 'L'; }
        if (!isset($report->data))         { $report->data = new \stdClass(); }
        if (!isset($report->data->color))  { $report->data->color   = '0'; } // black
        if (!isset($report->data->size))   { $report->data->size    = '10'; }
        if (!isset($report->data->align))  { $report->data->align   = 'L'; }
        $this->fontHeading= $report->heading->font== 'default' ? $this->defaultFont : $report->heading->font;
        $this->fontTitle1 = $report->title1->font == 'default' ? $this->defaultFont : $report->title1->font;
        $this->fontTitle2 = $report->title2->font == 'default' ? $this->defaultFont : $report->title2->font;
        $this->fontFilter = $report->filter->font == 'default' ? $this->defaultFont : $report->filter->font;
        $this->fontData   = $report->data->font   == 'default' ? $this->defaultFont : $report->data->font;
        $this->output     = '<table width="95%">';
        $this->addHeading($report);
        $this->addTableHead($report);
        $this->addTable($data, $report);
        $this->output    .= "</table>";
    }

    /**
     * Creates and adds a heading to the HTML report
     * @param object $report - report structure
     */
    private function addHeading($report)
    {
        $this->tableHead = [];
        $data = NULL;
        $align="C";
        foreach ($report->fieldlist as $value) {
            if (isset($value->visible) && $value->visible) {
                $data .= ($value->title);
                if (isset($value->columnbreak) && $value->columnbreak) {
                    $data .= '<br />';
                    continue;
                }
                $this->tableHead[] = ['align' => $align, 'value' => $data];
                $data = NULL;
            }
        }
        if ($data !== NULL) { $this->tableHead[] = ['align'=>$align, 'value'=>$data]; }
        $this->numColumns = sizeof($this->tableHead);
        $rStyle = '';
        if (isset($report->heading->show) && $report->heading->show) { // Show the company name
            $color  = convertHex($report->heading->color);
            $dStyle = 'style="font-family:'.$this->fontHeading.'; color:'.$color.'; font-size:'.$report->heading->size.'pt; font-weight:bold;"';
            $this->writeRow([['align' => $report->heading->align, 'value' => getModuleCache('bizuno', 'settings', 'company', 'primary_name')]], $rStyle, $dStyle, $heading = true);
        }
        if (isset($report->title1->show) && $report->title1->show) { // Set title 1 heading
            $color  = convertHex($report->title1->color);
            $dStyle = 'style="font-family:'.$this->fontTitle1.'; color:'.$color.'; font-size:'.$report->title1->size.'pt;"';
            $this->writeRow([['align' => $report->title1->align, 'value' => TextReplace($report->title1->text)]], $rStyle, $dStyle, $heading = true);
        }
        if (isset($report->title2->show) && $report->title2->show) { // Set Title 2 heading
            $color  = convertHex($report->title2->color);
            $dStyle = 'style="font-family:'.$this->fontTitle2.'; color:'.$color.'; font-size:'.$report->title2->size.'pt;"';
            $this->writeRow([['align' => $report->title2->align, 'value' => TextReplace($report->title2->text)]], $rStyle, $dStyle, $heading = true);
        }
        $color  = convertHex($report->filter->color);
        $dStyle = 'style="font-family:'.$this->fontFilter.'; color:'.$color.'; font-size:'.$report->filter->size.'pt;"';
        $this->writeRow([['align' => $report->filter->align, 'value' => TextReplace($report->filter->text)]], $rStyle, $dStyle, $heading = true);
    }

    /**
     * Sets the table header
     * @param object $report - report structure
     */
    private function addTableHead($report)
    {
        $color  = convertHex($report->data->color);
        $rStyle = 'style="background-color:'.$this->HdColor.'"';
        $dStyle = 'style="font-family:'.$this->fontData.'; color:'.$color.'; font-size:'.$report->data->size.'pt;"';
        $this->writeRow($this->tableHead, $rStyle, $dStyle);
    }

    /**
     * Fill in all the data lines and add pages as needed
     * @param array $data - report data from the SQL
     * @param object $report - Report structure
     * @return null - data is added to TCPDF output file
     */
    private function addTable($data, $report)
    {
        if (!is_array($data)) {
            $this->output .= "<tr><td>".lang('phreeform_output_none')."</td></tr>";
            $this->output .= '</table>';
            return;
        }
        $color0 = convertHex($this->FillColor);
        $bgStyle= 'style="background-color:'.$color0.'"';
        $color  = str_replace(':', '', $report->data->color);
        $dStyle = 'style="font-family:'.$this->fontData.';color:'.$color.';font-size:'.$report->data->size.'pt;"';
        // Fetch the column break array and alignment array
        foreach ($report->fieldlist as $value) {
            if (isset($value->visible) && $value->visible) {
//              $ColBreak[] = !empty($value->break) ? true : false;
                $align[]    =  isset($value->align) ? $value->align : '';
            }
        }
        // Ready to draw the column data
        $rowCnt= 0;
        $showHd= false;
        $fill  = false;
        foreach ($data as $myrow) {
            $Action = array_shift($myrow);
            $todo = explode(':', $Action, 2); // contains a letter of the date type and title/groupname
            switch ($todo[0]) {
                case "h": // Heading
                    $this->writeRow([['align'=>$report->data->align, 'value'=>$todo[1]]], '', $dStyle);
                    break;
                case "r": // Report Total
                case "g": // Group Total
                    $Desc  = ($todo[0] == 'g') ? $this->lang['group_total'] : $this->lang['report_total'];
                    $rStyle = 'style="background-color:'.$this->ttlColor.'"';
                    $this->writeRow([['align' => 'C', 'value' => $Desc.' '.$todo[1]]], $rStyle, $dStyle, true);
                    if ($rowCnt > 25) { $showHd = true; $rowCnt = 0; }
                    // now fall into the 'd' case to show the data
                    $fill = false;
                case "d": // data element
                default:
                    $temp = [];
                    $data = NULL;
                    foreach ($myrow as $key => $value) {
                        $data .= ($value);
//                        if (!$ColBreak[$key]) { $data .= '</td><td>'; continue; } // not needed as column breaks are not supported for html
                        $temp[] = ['align' => $align[$key], 'value' => $data];
                        $data = NULL;
                    }
                    if ($data !== NULL) { // catches not checked column break at end of row
                        $temp[] = ['align' => $align[$key], 'value' => $data];
                    }
                    $rStyle = $fill ? $bgStyle : ($todo[0]=='r' || $todo[0]=='g' ? 'style="background-color:'.$this->ttlColor.'"' : '');
                    $this->writeRow($temp, $rStyle, $dStyle);
                    if ($showHd) { $this->addTableHead($report); $showHd = false; }
                    break;
            }
            $fill = !$fill;
            $rowCnt++;
        }
    }

    /**
     * Adds a row the the HTLM output string
     * @param array $aData - data to write on form page
     * @param string $rStyle - [default ''] style to add to the HTML tr element, if any
     * @param type $dStyle - [default ''] style to add to the HTLM td element, if any
     * @param boolean $heading - [default false] set to true if this row is a heading row.
     */
    private function writeRow($aData, $rStyle = '', $dStyle = '', $heading = false)
    {
        $output  = "  <tr";
        $output .= (!$rStyle ? '' : ' '.$rStyle).">";
        foreach ($aData as $value) {
            $params = NULL;
            if ($heading) { $params .= ' colspan="'.$this->numColumns.'"'; }
            $output .= '    <td';
            switch ($value['align']) {
                case 'C': $params .= ' align="center"'; break;
                case 'R': $params .= ' align="right"';  break;
                default:
                case 'L':
            }
            $output .= $params . (!$dStyle ? '' : ' '.$dStyle).'>';
            $output .= ($value['value'] == '') ? '&nbsp;' : htmlspecialchars($value['value']);
            $output .= '</td>';
        }
        $output .= '  </tr>';
        $this->output .= $output;
    }
}
