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
//  Path: /modules/phreeform/classes/report_generator.php
//
namespace phreeform\classes; 
if (PDF_APP == 'TCPDF') { 
  define ('K_PATH_MAIN', DIR_FS_MODULES . 'phreeform/includes/tcpdf/');
  define ('K_PATH_URL',  DIR_WS_MODULES . 'phreeform/includes/tcpdf/');
  require_once (DIR_FS_MODULES . 'phreeform/includes/tcpdf/tcpdf.php'); // TCPDF class to generate reports, default
} else {
  require_once (DIR_FS_MODULES . 'phreeform/includes/fpdf/fpdf.php'); // FPDF class to generate reports
}

class PDF extends TCPDF {
	var $y0; // current y position
	var $x0; // current x position
	var $pageY; // y value of bottom of page less bottom margin

	function __construct() {
		global $report;
		$PaperSize = explode(':', $report->page->size);
		if (PDF_APP == 'TCPDF') {
			parent::__construct($report->page->orientation, 'mm', $PaperSize[0], true, 'UTF-8', false); 
			$this->SetCellPadding(0);
		} else {
			$this->FPDF($report->page->orientation, 'mm', $PaperSize[0]);
		}
		if ($report->page->orientation == 'P') { // Portrait - calculate max page height
			$this->pageY = $PaperSize[2] - $report->page->margin->bottom;
		} else { // Landscape
			$this->pageY = $PaperSize[1] - $report->page->margin->bottom;
		}
		// fetch the column widths and put into array to match the columns of data
		$CellXPos[0] = $report->page->margin->left;
		$col = 1;
		foreach ($report->fieldlist as $field) {
		  if ($field->visible) {
			if (isset($field->columnwidth)) {
				$CellXPos[$col] = max($CellXPos[$col], $CellXPos[$col-1] + $field->columnwidth);
			} else {
				$CellXPos[$col] = $CellXPos[$col-1] + RW_DEFAULT_COLUMN_WIDTH;
			}
			if ($field->columnbreak) $col++;
		  }
		}
		$this->columnWidths = $CellXPos;

		$this->SetMargins($report->page->margin->left, $report->page->margin->top, $report->page->margin->right);
		$this->SetAutoPageBreak(0, $report->page->margin->bottom);
		$this->SetFont($report->page->heading->font);
		$this->SetDrawColor(128, 0, 0);
		$this->SetLineWidth(0.35); // 1 point
//	    $this->AliasNbPages(); // deprecated
		$this->AddPage();
	}

	function Header() {
		global $report, $Heading, $Seq;
		$this->SetX($report->page->margin->left);
		$this->SetY($report->page->margin->top);
		$this->SetFillColor(255);
		if ($report->page->heading->show) { // Show the company name
			$this->SetFont($report->page->heading->font, 'B', $report->page->heading->size);
			$Colors = explode(':', $report->page->heading->color);
			$this->SetTextColor($Colors[0], $Colors[1], $Colors[2]);
			$CellHeight = ($report->page->heading->size + REPORT_ROWSPACE) * 0.35;
			$this->Cell(0, $CellHeight, COMPANY_NAME, 0, 1, $report->page->heading->align);
		}
		if ($report->page->title1->show) { // Set title 1 heading
			$this->SetFont($report->page->title1->font, '', $report->page->title1->size);
			$Colors = explode(':', $report->page->title1->color);
			$this->SetTextColor($Colors[0], $Colors[1], $Colors[2]);
			$CellHeight = ($report->page->title1->size + REPORT_ROWSPACE) * 0.35;
			$this->Cell(0, $CellHeight, TextReplace($report->page->title1->text), 0, 1, $report->page->title1->align);
		}
		if ($report->page->title2->show) { // Set Title 2 heading
			$this->SetFont($report->page->title2->font, '', $report->page->title2->size);
			$Colors = explode(':', $report->page->title2->color);
			$this->SetTextColor($Colors[0], $Colors[1], $Colors[2]);
			$CellHeight = ($report->page->title2->size + REPORT_ROWSPACE) * 0.35;
			$this->Cell(0, $CellHeight, TextReplace($report->page->title2->text), 0, 1, $report->page->title2->align);
		}
		// Set the filter heading
		$this->SetFont($report->page->filter->font, '', $report->page->filter->size);
		$Colors = explode(':', $report->page->filter->color);
		$this->SetTextColor($Colors[0], $Colors[1], $Colors[2]);
		$CellHeight = ($report->page->filter->size + REPORT_ROWSPACE) * 0.35; // convert points to mm
		$this->MultiCell(0, $CellHeight, $report->page->filter->text, 'B', 1, $report->page->filter->align);
		$this->y0 = $this->GetY(); // set y position after report headings before column titles
		// Set the table header
		$this->SetFont($report->page->data->font, '', $report->page->data->size);
		$Colors = explode(':', $report->page->data->color);
		$this->SetTextColor($Colors[0], $Colors[1], $Colors[2]);
		$this->SetDrawColor(128, 0, 0);
		$this->SetLineWidth(.35); // 1 point
		$CellHeight = ($report->page->data->size + REPORT_ROWSPACE) * 0.35;
		// fetch the column widths
		$CellXPos = $this->columnWidths;
		// Fetch the column break array
		foreach ($Seq as $Temp) $ColBreak[] = ($Temp['break']) ? true : false;
		// See if we need to truncate the data
		$trunc = ($report->truncate == '1') ? true : false;
		// Ready to draw the column titles in the header
		$maxY = $this->y0; // set to track the tallest column
		$col = 1;
		$LastY = $this->y0;
		foreach ($Heading as $key => $value) {
			$this->SetLeftMargin($CellXPos[$col - 1]);
			$this->SetX($CellXPos[$col - 1]);
			$this->SetY($LastY);
			// truncate data if selected
			if ($trunc) $value = $this->TruncData($value, $CellXPos[$col] - $CellXPos[$col-1]);
			$this->MultiCell($CellXPos[$col] - $CellXPos[$col-1], $CellHeight, $value, 0, $report->page->data->align);
			if ($ColBreak[$key]) { 
				$col++;
				$LastY = $this->y0;
			} else $LastY = $this->GetY();
			if ($this->GetY() > $maxY) $maxY = $this->GetY(); // check for new col max height
		}
		// Draw a bottom line for the end of the heading
		$this->SetLeftMargin($CellXPos[0]);
		$this->SetX($CellXPos[0]);
		$this->SetY($this->y0);
		$this->Cell(0, $maxY - $this->y0, ' ', 'B');
		$this->y0 = $maxY + 0.35;
	}

	function Footer() {
	  //Position at 1.5 cm from bottom
	  $this->SetY(-8);
	  //Arial italic 8
	  $this->SetFont(PDF_DEFAULT_FONT, '', '8');	// fixed footer font type and size for page numbers
	  $this->SetTextColor(0);
	  //Page number
	  $total_pages = (PDF_APP == 'TCPDF') ? TCPDF::getAliasNbPages() : '{nb}';
	  $this->Cell(0, 10, TEXT_PAGE . ' ' . $this->PageNo() . ' / ' . $total_pages, 0, 0, 'C');
	}

	function ReportTable($Data) {
		global $report, $Seq;
		if (!is_array($Data)) return;
		$FillColor  = array(224, 235, 255);
		$this->SetFont($report->page->data->font, '', $report->page->data->size);
		$this->SetFillColor($FillColor[0], $FillColor[1], $FillColor[2]);
		$Colors     = explode(':', $report->page->data->color);
		$this->SetTextColor($Colors[0], $Colors[1], $Colors[2]);
		$CellHeight = ($report->page->data->size + REPORT_ROWSPACE) * 0.35;
		// fetch the column widths
		$CellXPos   = $this->columnWidths;
		// Fetch the column break array and alignment array
		foreach ($Seq as $Temp) {
			$ColBreak[] = ($Temp['break']) ? true : false;
			$align[]    = $Temp['align'];
		}
		// See if we need to truncate the data
		$trunc          = $report->truncate == '1' ? true: false;
		// Ready to draw the column data
		$fill           = false;
		$NeedTop        = 'No';
		$this->MaxRowHt = 0; //track the tallest row to estimate page breaks
		$group_break    = false;
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
					$Desc  = ($todo[0] == 'g') ? TEXT_GROUP_TOTAL_FOR : TEXT_REPORT_TOTAL_FOR;
					$this->Cell(0, $CellHeight, $Desc . $todo[1], 1, 1, 'C');
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
						$this->MultiCell($CellXPos[$col] - $CellXPos[$col-1], $CellHeight, $value, 0, $align[$key]);
						if ($ColBreak[$key]) { 
							$col++;
							$LastY = $this->y0;
						} else { 
							$LastY = $this->GetY();
						}
						if ($this->GetY() > $maxY) $maxY = $this->GetY();
					}
					$this->SetLeftMargin($CellXPos[0]); // restore left margin
					break;
			}
			$ThisRowHt = $maxY - $this->y0; // seee how tall this row was
			if ($ThisRowHt > $this->MaxRowHt) $this->MaxRowHt = $ThisRowHt; // keep that largest row so far to track pagination
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

	function forcePageBreak($CellXPos) {
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

	function TruncData($strData, $ColWidth) {
		$percent = 0.90; //percent to truncate from max to account for proportional spacing
		$CurWidth = $this->GetStringWidth($strData);
		if ($CurWidth > ($ColWidth * (0.90))) { // then it needs to be truncated
			// for now we'll do an approximation based on averages and scale to 90% of the width to allow for variance
			// A better aproach would be an recursive call to this function until the string just fits.
			$NumChars = strlen($strData);
			// Reduce the string by 1-$percent and retest
			$strData = $this->TruncData(substr($strData, 0, ($ColWidth / $CurWidth) * $NumChars * $percent), $ColWidth);
		}
		return $strData;
	}

} // end class
?>