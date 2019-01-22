<?php
/*
 * Renders a report in PDF format using the TCPDF application
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
 * @version    3.x Last Update: 2019-01-14
 * @filesource /controller/module/phreeform/renderReport.php
 */

namespace bizuno;

require_once(BIZUNO_3P_TCPDF."config/bizuno_config.php");
require_once(BIZUNO_3P_TCPDF."tcpdf.php");

class PDF extends \TCPDF
{
    public $moduleID = 'phreeform';
    var $y0; // current y position
    var $x0; // current x position
    var $pageY; // y value of bottom of page less bottom margin

    function __construct()
    {
        global $report;
        $this->lang       = getLang($this->moduleID);
        $this->defaultFont= getModuleCache('phreeform','settings','general','default_font','helvetica');
        $PaperSize        = explode(':', $report->page->size);
        parent::__construct($report->page->orientation, 'mm', strtoupper($PaperSize[0]), true, 'UTF-8', false);
        $this->SetCellPadding(0);
        $this->fontHeading= $report->heading->font== 'default' ? $this->defaultFont : $report->heading->font;
        $this->fontTitle1 = $report->title1->font == 'default' ? $this->defaultFont : $report->title1->font;
        $this->fontTitle2 = $report->title2->font == 'default' ? $this->defaultFont : $report->title2->font;
        $this->fontFilter = $report->filter->font == 'default' ? $this->defaultFont : $report->filter->font;
        $this->fontData   = $report->data->font   == 'default' ? $this->defaultFont : $report->data->font;

        if ($report->page->orientation == 'P') { // Portrait - calculate max page height
            $this->pageY = $PaperSize[2] - $report->page->margin->bottom;
        } else { // Landscape
            $this->pageY = $PaperSize[1] - $report->page->margin->bottom;
        }
        // fetch the column widths and put into array to match the columns of data
        $CellXPos[0] = $report->page->margin->left;
        $col = 1;
        foreach ($report->fieldlist as $field) {
            if (!empty($field->visible)) {
                if (!isset($CellXPos[$col])) { $CellXPos[$col] = $CellXPos[$col-1]; }
                if (!isset($field->width))   { $field->width = getModuleCache('phreeform', 'settings', 'general', 'column_width'); }
                $CellXPos[$col] = max($CellXPos[$col], $CellXPos[$col-1] + $field->width);
                if (isset($field->break) && $field->break) { $col++; }
            }
        }
        $this->columnWidths = $CellXPos;
        // Fetch the column break array and alignment array
        foreach ($report->fieldlist as $value) {
            if (isset($value->visible) && $value->visible) {
                $this->ColBreak[] = (isset($value->break) && $value->break) ? true : false;
                $this->align[]    = $value->align;
            }
        }
        $this->SetMargins($report->page->margin->left, $report->page->margin->top, $report->page->margin->right);
        $this->SetAutoPageBreak(0, $report->page->margin->bottom);
        $this->SetFont($this->fontHeading);
        $this->SetDrawColor(128, 0, 0);
        $this->SetLineWidth(0.35); // 1 point
        $this->AddPage();
    }

    /**
     * Builds and places the header block on ALL pages
     * @global object $report - Report structure
     */
    function Header()
    {
        global $report;
        $this->SetX($report->page->margin->left);
        $this->SetY($report->page->margin->top);
        $this->SetFillColor(255);
        if (isset($report->heading->show)) { // Show the company name
            $this->SetFont($this->fontHeading, 'B', $report->heading->size);
            $Colors = $this->convertRGB($report->heading->color);
            $this->SetTextColor($Colors[0], $Colors[1], $Colors[2]);
            $CellHeight = ($report->heading->size) * 0.35;
            $this->Cell(0, $CellHeight, getModuleCache('bizuno', 'settings', 'company', 'primary_name'), 0, 1, $report->heading->align);
        }
        if (isset($report->title1->show)) { // Set title 1 heading
            $this->SetFont($this->fontTitle1, '', $report->title1->size);
            $Colors = $this->convertRGB($report->title1->color);
            $this->SetTextColor($Colors[0], $Colors[1], $Colors[2]);
            $CellHeight = ($report->title1->size) * 0.35;
            $this->Cell(0, $CellHeight, TextReplace($report->title1->text), 0, 1, $report->title1->align);
        }
        if (isset($report->title2->show)) { // Set Title 2 heading
            $this->SetFont($this->fontTitle2, '', $report->title2->size);
            $Colors = $this->convertRGB($report->title2->color);
            $this->SetTextColor($Colors[0], $Colors[1], $Colors[2]);
            $CellHeight = ($report->title2->size) * 0.35;
            $this->Cell(0, $CellHeight, TextReplace($report->title2->text), 0, 1, $report->title2->align);
        }
        // Set the filter heading
        $this->SetFont($this->fontFilter, '', $report->filter->size);
        $Colors = $this->convertRGB($report->filter->color);
        $this->SetTextColor($Colors[0], $Colors[1], $Colors[2]);
        $CellHeight = ($report->filter->size) * 0.35; // convert points to mm
        $this->MultiCell(0, $CellHeight, $report->filter->text, 'B', 1, $report->filter->align);
        $this->y0 = $this->GetY(); // set y position after report headings before column titles
        // Set the table header
        $this->SetFont($this->fontData, '', $report->data->size);
        $Colors = $this->convertRGB($report->data->color);
        $this->SetTextColor($Colors[0], $Colors[1], $Colors[2]);
        $this->SetDrawColor(128, 0, 0);
        $this->SetLineWidth(.35); // 1 point
        $CellHeight = ($report->data->size) * 0.35;
        // fetch the column widths
        $CellXPos = $this->columnWidths;
        // See if we need to truncate the data
        $trunc = (isset($report->truncate) && $report->truncate) ? true : false;
        // Ready to draw the column titles in the header
        $maxY = $this->y0; // set to track the tallest column
        $col = 1;
        $LastY = $this->y0;
        foreach ($report->fieldlist as $key => $value) {
            if (isset($value->visible) && $value->visible) {
                $this->SetLeftMargin($CellXPos[$col - 1]);
                $this->SetX($CellXPos[$col - 1]);
                $this->SetY($LastY);
                // truncate data if selected
                if ($trunc) $value->title = $this->TruncData($value->title, $CellXPos[$col] - $CellXPos[$col-1]);
                $this->MultiCell($CellXPos[$col] - $CellXPos[$col-1], $CellHeight, $value->title, 0, 'C');
                if ($value->break) {
                    $col++;
                    $LastY = $this->y0;
                } else $LastY = $this->GetY();
                if ($this->GetY() > $maxY) $maxY = $this->GetY(); // check for new col max height
            }
        }
        // Draw a bottom line for the end of the heading
        $this->SetLeftMargin($CellXPos[0]);
        $this->SetX($CellXPos[0]);
        $this->SetY($this->y0);
        $this->Cell(0, $maxY - $this->y0, ' ', 'B');
        $this->y0 = $maxY + 0.35;
    }

    /**
     * Builds the footer and places it on the bottom of the page
     */
    function Footer()
    {
        $this->SetY(-12); //Position at 1.5 cm from bottom
        $this->SetFont($this->fontData, '', '8');
        $this->SetTextColor(0);
        $total_pages = \TCPDF::getAliasNbPages();
        $this->Cell(0, 10, lang('page').' '.$this->PageNo().' / '.$total_pages, 0, 0, 'C');
    }

    /**
     * Generates and draws the report table to the TCPDF structure
     * @global object $report - Report structure
     * @param arary $Data - data to place in the report
     * @return null - Page data is added to the PDF file on the fly
     */
    function ReportTable($Data)
    {
        global $report;
        if (!is_array($Data)) { return; }
        $FillColor = [224, 235, 255];
        $this->SetFont($this->fontData, '', $report->data->size);
        $this->SetFillColor($FillColor[0], $FillColor[1], $FillColor[2]);
        $Colors    = $this->convertRGB($report->data->color);
        $this->SetTextColor($Colors[0], $Colors[1], $Colors[2]);
        $CellHeight= ($report->data->size) * 0.35;
        // fetch the column widths
        $CellXPos  = $this->columnWidths;
        // See if we need to truncate the data
        $trunc     = isset($report->truncate) && $report->truncate ? true: false;
        // Ready to draw the column data
        $fill      = false;
        $brdr      = 'T';
        $NeedTop   = 'No';
        $this->MaxRowHt = 0; //track the tallest row to estimate page breaks
        $group_break= false;
        foreach ($Data as $myrow) {
            $Action = array_shift($myrow);
            $todo = explode(':', $Action); // contains a letter of the date type and title/groupname
            switch ($todo[0]) {
                case "h": // Heading
                    $this->SetLeftMargin($CellXPos[0]);
                    $this->SetX($CellXPos[0]);
                    $this->SetY($this->y0);
                    $this->Cell(0, $CellHeight, $todo[1], 1, 1, 'L');
                    $this->y0 = $this->GetY() + 0.35;
                    $NeedTop = 'Next';
                    $fill = false;
                    break;
                case "r": // Report Total
                case "g": // Group Total
                    // Draw a fill box
                    if ($this->y0 + (2 * $this->MaxRowHt) > $this->pageY) $this->forcePageBreak($CellXPos[0]);
                    $this->SetLeftMargin($CellXPos[0]);
                    $this->SetX($CellXPos[0]);
                    $this->SetY($this->y0);
                    $this->SetFillColor(240);
                    $this->Cell(0, $this->pageY-$this->y0, '', $brdr, 0, 'L', 1);
                    // Add total heading
                    $this->SetLeftMargin($CellXPos[0]);
                    $this->SetX($CellXPos[0]);
                    $this->SetY($this->y0);
                    $Desc  = ($todo[0] == 'g') ? $this->lang['group_total'] : $this->lang['report_total'];
                    $this->Cell(0, $CellHeight, "$Desc ".$todo[1], 1, 1, 'C');
                    $this->y0 = $this->GetY() + 0.35;
                    $NeedTop = 'Next';
                    $fill = false; // set so totals data will not be filled
                    if ($todo[0] == 'g' && $report->grpbreak) $group_break = true;
                    // now fall into the 'd' case to show the data
                case "d": // data element
                default:
                    // figure out if a border needs to be drawn for total separation
                    // and fill color (draws an empty box over the row just written with the fill color)
                    $brdr = 0;
                    if ($NeedTop == 'Yes') {
                        $brdr = 'T';
                        $fill = false; // set so first data after total will not be filled
                        $NeedTop = 'No';
                    } elseif ($NeedTop == 'Next') {
                        $brdr = 'LR';
                        $NeedTop = 'Yes';
                    }
                    // Draw a fill box
                    if (($this->y0 + $this->MaxRowHt) > $this->pageY) $this->forcePageBreak($CellXPos[0]);
                    $this->SetLeftMargin($CellXPos[0]);
                    $this->SetX($CellXPos[0]);
                    $this->SetY($this->y0);
                    if ($fill) $this->SetFillColor($FillColor[0], $FillColor[1], $FillColor[2]); else $this->SetFillColor(255);
                    $this->Cell(0, $this->pageY-$this->y0, '', $brdr, 0, 'L', 1);
                    // fill in the data
                    $maxY  = $this->y0; // set to current top of row
                    $col   = 1;
                    $LastY = $this->y0;
                    foreach ($myrow as $key => $value) {
                        $this->SetLeftMargin($CellXPos[$col-1]);
                        $this->SetX($CellXPos[$col-1]);
                        $this->SetY($LastY);
                        // truncate data if necessary
                        if ($trunc) $value = $this->TruncData($value, $CellXPos[$col] - $CellXPos[$col-1]);
                        $this->MultiCell($CellXPos[$col] - $CellXPos[$col-1], $CellHeight, $value, 0, $this->align[$key]);
                        if ($this->ColBreak[$key]) {
                            $col++;
                            $LastY = $this->y0;
                        } else $LastY = $this->GetY();
                        if ($this->GetY() > $maxY) { $maxY = $this->GetY(); }
                    }
                    $this->SetLeftMargin($CellXPos[0]); // restore left margin
                    break;
            }
            $ThisRowHt = $maxY - $this->y0; // seee how tall this row was
            if ($ThisRowHt > $this->MaxRowHt) { $this->MaxRowHt = $ThisRowHt; } // keep that largest row so far to track pagination
            $this->y0 = $maxY; // set y position to largest value for next row
            $fill = !$fill;
            if ($group_break) {
                $this->forcePageBreak($CellXPos[0]);
                $group_break = false;
                continue;
            }
        }
        // Fill the end of the report with white space, temp increase margins to eliminate occasional edging problem
        $this->SetMargins($report->page->margin->left - 0.25, $report->page->margin->top, $report->page->margin->right - 0.25);
        $this->SetX($report->page->margin->left - 0.25);
        $this->SetY($this->y0);
        $this->SetFillColor(255);
        $this->Cell(0, $this->pageY - $this->y0 + 0.25, '', 'T', 0, 'L', 1);
        // restore the margins
        $this->SetMargins($report->page->margin->left, $report->page->margin->top, $report->page->margin->right);
        $this->SetLeftMargin($CellXPos[0]);
        $this->SetX($CellXPos[0]);
    }

    /**
     * Causes an immediate page break and resets pointers to top of next page
     * @global object $report - Report structure
     * @param float $CellXPos - Contains the left margin
     */
    function forcePageBreak($CellXPos)
    {
        global $report;
        // Fill the end of the report with white space
        $this->SetMargins($report->page->margin->left - 0.25, $report->page->margin->top, $report->page->margin->right - 0.25);
        $this->SetX($report->page->margin->left - 0.25);
        $this->SetY($this->y0);
        $this->SetFillColor(255);
        $this->Cell(0, $this->pageY - $this->y0 + 0.25, '', 'T', 0, 'L', 1);
        $this->AddPage();
        $this->MaxRowHt = 0;
        $this->SetMargins($report->page->margin->left, $report->page->margin->top, $report->page->margin->right);
        $this->SetX($CellXPos);
    }

    /**
     * Truncates long data strings to fit within column width
     * @param sting $strData - data string to measure and operate on
     * @param float $ColWidth - width of a column in ems
     * @return string - truncated string if longer than 90% of the width, original string if not
     */
    function TruncData($strData, $ColWidth)
    {
//        $percent = 0.90; //percent to truncate from max to account for proportional spacing
        $CurWidth = $this->GetStringWidth($strData);
        if (!$CurWidth) { $CurWidth = 20; } // prevent divide by zero errors
        if ($CurWidth > ($ColWidth * (0.90))) { // then it needs to be truncated
            // for now we'll do an approximation based on averages and scale to 90% of the width to allow for variance
            // A better aproach would be an recursive call to this function until the string just fits.
//            $NumChars = strlen($strData);
            // Reduce the string by 1-$percent and retest
// comment this out as it causes 500 errors when columns/data is small?
//            $strData = $this->TruncData(substr($strData, 0, ($ColWidth / $CurWidth) * $NumChars * $percent), $ColWidth);
        }
        return $strData;
    }

    /**
     * Converts a RGB color to hexadecimal format
     * @param string $value - value to convert
     * @return string - converted $value
     */
    private function convertRGB($value)
    {
        if (strpos($value, '#') === 0) {
            $output[] = hexdec(substr($value, 1, 2));
            $output[] = hexdec(substr($value, 3, 2));
            $output[] = hexdec(substr($value, 5, 2));
        } elseif (strpos($value, ':') !== false) {
            $output = explode(':', $value);
        } else { $output = [0, 0, 0]; }
        return $output; // black
    }
}
