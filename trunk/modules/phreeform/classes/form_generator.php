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
//  Path: /modules/phreeform/classes/form_generator.php
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
	var $y0;             // current y position
	var $x0;             // current x position
	var $pageY;          // y value of bottom of page less bottom margin
	var $PageCnt;        // tracks the page count for correct page numbering for multipage and multiform printouts
    var $NewPageGroup;   // variable indicating whether a new group was requested
    var $PageGroups;     // variable containing the number of pages of the groups
    var $CurrPageGroup;  // variable containing the alias of the current page group

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
	  $this->SetMargins($report->page->margin->left, $report->page->margin->top, $report->page->margin->right);
	  $this->SetAutoPageBreak(0, $report->page->margin->bottom);
	  $this->SetFont(PDF_DEFAULT_FONT);
	  $this->SetDrawColor(128, 0, 0);
	  $this->SetLineWidth(0.35); // 1 point
//	  $this->AliasNbPages(); // deprecated
	}
	
	function Header() { // prints all static information on the page
	  global $report, $FieldValues;
	  $tempValues = $FieldValues;
	  foreach ($report->fieldlist as $key => $field) {
//if ($field->type == 'ImgLink') { echo 'key = ' . $key . ' and field = '; print_r($field); echo '<br>'; }
		switch ($field->type) {
		  case "Data": 
			$field->text = array_shift($tempValues); // fill the data to display
			$field->processing = $field->boxfield[0]->processing;
			// some special processing if display on first or last page only
			$report->fieldlist[$key]->texttemp = $field->text;
			if (($this->PageNo() <> 1 && $field->display == 1) || $field->display == 2) $field->text = '';
		  case "TBlk": // same operation as page number 
		  case "Text": 
		  case "CDta": 
		  case "CBlk": 
		  case "LtrTpl":
		  case "PgNum":   $this->FormText($field);    break;
		  case "Img":     $this->FormImage($field);   break;
		  case "ImgLink": $this->FormImgLink($field, array_shift($tempValues)); break;
		  case "Line":    $this->FormLine($field);    break;
		  case "Rect":    $this->FormRect($field);    break;
		  case "BarCode": $this->FormBarCode($field, array_shift($tempValues)); break;
		  default: // do nothing
		}
	  }
	}

	function Footer() { // Prints totals at end of last page
	  global $report;
	  foreach ($report->fieldlist as $key => $field) {
		if ($field->type == 'Ttl' || ($field->type == 'Data' && $field->display == '2')) {
		  $this->FormText($field);
		}
	  }
	}

    function StartPageGroup() { // create a new page group; call this before calling AddPage()
      $this->NewPageGroup = true;
    }

    function GroupPageNo() { // current page in the group
      return $this->PageGroups[$this->CurrPageGroup];
    }

    function PageGroupAlias() { // alias of the current page group -- will be replaced by the total number of pages in this group
      return $this->CurrPageGroup;
    }

    function _beginpage($orientation, $format) {
	  parent::_beginpage($orientation, $format);
	  if ($this->NewPageGroup) { // start a new group
		$n = sizeof($this->PageGroups)+1;
		$alias = "{nb$n}";
		$this->PageGroups[$alias] = 1;
		$this->CurrPageGroup = $alias;
		$this->NewPageGroup = false;
	  } else if ($this->CurrPageGroup) {
		$this->PageGroups[$this->CurrPageGroup]++;
	  }
    }

    function _putpages() {
	  $nb = $this->page;
	  if (!empty($this->PageGroups)) { // do page number replacement
		foreach ($this->PageGroups as $k => $v) {
		  for ($n = 1; $n <= $nb; $n++) $this->pages[$n] = str_replace($k, $v, $this->pages[$n]);
		}
	  }
	  parent::_putpages();
	}

	function FormImage($Params) {
	  if (is_file   (PF_DIR_MY_REPORTS . 'images/' . $Params->filename)) {
		$this->Image(PF_DIR_MY_REPORTS . 'images/' . $Params->filename, $Params->abscissa, $Params->ordinate, $Params->width, $Params->height);
	  } else { // no image was found at the specified path, draw a box
		// check for any data entered
		if (!isset($Params->abscissa)) { // then no information was entered for this entry, set some defaults
		  $Params->abscissa = '10';
		  $Params->ordinate = '10';
		  $Params->width    = '50';
		  $Params->height   = '20';
		}
		$this->SetXY($Params->abscissa, $Params->ordinate);
		$this->SetFont(PDF_DEFAULT_FONT, '', '10');
		$this->SetTextColor(255, 0, 0);
		$this->SetDrawColor(255, 0, 0);
		$this->SetLineWidth(0.35);
		$this->SetFillColor(255);
		$this->Cell($Params->width, $Params->height, TEXT_NO_IMAGE, 1, 0, 'C');
	  }
	}

	function FormImgLink($Params, $data) {
	  $path = PF_DIR_DEF_IMAGE_LINK . $Params->text . $data;
	  if (isset($Params->boxfield[0]->processing)) $path = ProcessData($path, $Params->boxfield[0]->processing);
	  $ext = strtolower(substr($path, -3));
	  if (is_file($path) && ($ext == 'jpg' || $ext == 'jpeg')) { // TBD - Fails for png images on prod server, restrict to jpg
	  	$this->Image($path, $Params->abscissa, $Params->ordinate, $Params->width, $Params->height);
	  } else { // no image was found at the specified path, draw a box
		// check for any data entered
		if (!isset($Params->abscissa)) { // then no information was entered for this entry, set some defaults
		  $Params->abscissa = '10';
		  $Params->ordinate = '10';
		  $Params->width    = '50';
		  $Params->height   = '20';
		}
		$this->SetXY($Params->abscissa, $Params->ordinate);
		$this->SetFont(PDF_DEFAULT_FONT, '', '10');
		$this->SetTextColor(255, 0, 0);
		$this->SetDrawColor(255, 0, 0);
		$this->SetLineWidth(0.35);
		$this->SetFillColor(255);
		$this->Cell('30', '20', TEXT_NO_IMAGE, 1, 0, 'C');
	  }
	}

	function FormLine($Params) {
	  if (!isset($Params->abscissa)) return;	// don't do anything if data array has not been set
	  $FC = explode(':', $Params->bordercolor);
	  $this->SetDrawColor($FC[0], $FC[1], $FC[2]);
	  $this->SetLineWidth($Params->size * 0.35);
	  if       ($Params->linetype == 'H') { // Horizontal
		$XEnd = $Params->abscissa + $Params->length;
		$YEnd = $Params->ordinate;
	  } elseif ($Params->linetype == 'V') { // Vertical
		$XEnd = $Params->abscissa;
		$YEnd = $Params->ordinate + $Params->length;
	  } elseif ($Params->linetype == 'C') { // Custom
		$XEnd = $Params->endabscissa;
		$YEnd = $Params->endordinate;
	  } 
	  $this->Line($Params->abscissa, $Params->ordinate, $XEnd, $YEnd);
	}

	function FormRect($Params) {
	  if (!isset($Params->abscissa)) return; // don't do anything if data array has not been set
	  $DrawFill = '';
	  if ($Params->bordershow == '1') { // Border
		$DrawFill = 'D';
	    $FC = explode(':', $Params->bordercolor);
		$this->SetDrawColor($FC[0], $FC[1], $FC[2]);
		$this->SetLineWidth($Params->bordersize * 0.35);
	  } else { 
		$this->SetDrawColor(255);
		$this->SetLineWidth(0);
	  }
	  if ($Params->fillshow == '1') { // Fill
		$DrawFill .= 'F';
	    $FC = explode(':', $Params->fillcolor);
		$this->SetFillColor($FC[0], $FC[1], $FC[2]);
	  } else {
		$this->SetFillColor(255);
	  }
	  $this->Rect($Params->abscissa, $Params->ordinate, $Params->width, $Params->height, $DrawFill);
	}

	function FormBarCode($Params, $data) {
	  if (!isset($Params->abscissa)) return;	// don't do anything if data array has not been set
	  if (PDF_APP  <> 'TCPDF') {  // need to use TCPDF to generate bar codes.
		$Params->text = 'Barcodes Require TCPDF';
		$this->FormText($Params);
		return;
	  }
	  $style = array(
	    'position'    => '', 
	    'border'      => $Params->bordershow ? true : false, 
//	    'padding'     => 2, // in user units
	    'text'        => true, // print text below barcode
	    'font'        => $Params->font,
	    'fontsize'    => $Params->size,
	    'stretchtext' => 1, // 0 = disabled; 1 = horizontal scaling only if necessary; 2 = forced horizontal scaling; 3 = character spacing only if necessary; 4 = forced character spacing
	    'fgcolor'     => explode(':', $Params->color),
	    'stretch' => false,
	    'fitwidth' => true,
//	    'cellfitalign' => '',
//	    'hpadding' => 'auto',
//	    'vpadding' => 'auto',
	  );
	  switch ($Params->fillshow) {
		default:
		case '0': $style['bgcolor'] = false; break;
		case '1': $style['bgcolor'] = explode(':', $Params->fillcolor); break;
	  }
	  $this->write1DBarcode($data, $Params->boxfield[0]->processing, $Params->abscissa, $Params->ordinate, $Params->width, $Params->height, 0.4, $style, 'N');
	}

	function FormText($Params) {
	  global $report;
	  if (!isset($Params->abscissa)) return;	// don't do anything if data array has not been set
	  $this->SetXY($Params->abscissa, $Params->ordinate);
	  $this->SetFont($Params->font, '', $Params->size);
	  $FC = explode(':', $Params->color);
	  $this->SetTextColor($FC[0], $FC[1], $FC[2]);
	  if ($Params->bordershow == '1') { // Border
		$Border = '1';
	    $FC = explode(':', $Params->bordercolor);
		$this->SetDrawColor($FC[0], $FC[1], $FC[2]);
		$this->SetLineWidth($Params->bordersize * 0.35);
	  } else { 
		$Border = '0';
	  }
	  if ($Params->fillshow == '1') { // Fill
		$Fill = '1';
	    $FC = explode(':', $Params->fillcolor);
		$this->SetFillColor($FC[0], $FC[1], $FC[2]);
	  } else { 
		$Fill = '0';
	  }
	  switch ($Params->type) {
	  	case 'LtrTpl': $TextField = str_replace(array_keys($report->LetterData), array_values($report->LetterData), $Params->text); break;
	  	case 'PgNum':  $TextField = $this->GroupPageNo() . ' ' . TEXT_OF . ' ' . $this->PageGroupAlias(); break;
	  	default: $TextField = $Params->text; break;
	  }
	  if (isset($Params->processing)) $TextField = ProcessData($TextField, $Params->processing);
	  $this->MultiCell($Params->width, $Params->height, $TextField, $Border, $Params->align, $Fill);
	}

	function FormTable($Params) {
	  // set up some variables
	  $hRGB = $Params->hfillcolor;
	  $FC = explode(':', $Params->fillcolor);
	  $hFC = (!$hRGB) ? $FC : explode(':', $hRGB);
	  $MaxBoxY = $Params->ordinate + $Params->height; // figure the max y position on page

	  $FillThisRow = false;
	  $MaxRowHt = 0; //track the tallest row to estimate page breaks
	  $this->y0 = $Params->ordinate;
	  foreach ($Params->data as $index => $myrow) {
		// See if we are at or near the end of the table box size
		if (($this->y0 + $MaxRowHt) > $MaxBoxY) { // need a new page
		  $this->DrawTableLines($Params, $HeadingHt); // draw the box and lines around the table
		  $this->AddPage();
		  $this->y0 = $Params->ordinate;
		  $this->SetLeftMargin($Params->abscissa);
		  $this->SetXY($Params->abscissa, $Params->ordinate);
		  $MaxRowHt  = $this->ShowTableRow($Params, $Params->data[0], true, $hFC, true); // new page heading
		  $HeadingHt = $MaxRowHt;
		}
		$this->SetLeftMargin($Params->abscissa);
		$this->SetXY($Params->abscissa, $this->y0);
		// fill in the data
		if ($index == 0) { // its a heading line
		  $MaxRowHt  = $this->ShowTableRow($Params, $myrow, true, $hFC, true);
		  $HeadingHt = $MaxRowHt;
		} else {
		  $MaxRowHt = $this->ShowTableRow($Params, $myrow, $FillThisRow, $FC);
		}
		$FillThisRow = !$FillThisRow;
	  }
	  $this->DrawTableLines($Params, $HeadingHt); // draw the box and lines around the table
	}

	function ShowTableRow($Params, $myrow, $FillThisRow, $FC, $Heading = false) {
	  $MaxBoxY = $Params->ordinate + $Params->height; // figure the max y position on page
	  $fillReq = $Heading ? $Params->hfillshow : $Params->fillshow;
	  if ($FillThisRow && $fillReq) {
	    $this->SetFillColor($FC[0], $FC[1], $FC[2]); 
	  } else {
	    $this->SetFillColor(255);
	  }
	  $this->Cell($Params->width, $MaxBoxY - $this->y0, '', 0, 0, 'L', 1); // sets background proper color
	  $maxY = $this->y0; // set to current top of row
	  $Col  = 0;
	  $NextXPos = $Params->abscissa;
	  foreach ($myrow as $key => $value) {
	    if (substr($key, 0, 1) == 'r') $key = substr($key, 1);  
		$font  = ($Heading && $Params->hfont  <> '') ? $Params->hfont  : $Params->boxfield[$key]->font;
		$size  = ($Heading && $Params->hsize  <> '') ? $Params->hsize  : $Params->boxfield[$key]->size;
		$color = ($Heading && $Params->hcolor <> '') ? $Params->hcolor : $Params->boxfield[$key]->color;
		$align = ($Heading && $Params->halign <> '') ? $Params->halign : $Params->boxfield[$key]->align;
		$this->SetLeftMargin($NextXPos);
		$this->SetXY($NextXPos, $this->y0);
		$this->SetFont($font, '', $size);
		$TC = explode(':', $color);
		$this->SetTextColor($TC[0], $TC[1], $TC[2]);
		$CellHeight = ($size + PF_DEFAULT_ROWSPACE) * 0.35;
//		if ($trunc) $value=$this->TruncData($value, $value->width);
		// special code for heading and data
		if ($Heading) {
		  if ($align == 'A') $align = $Params->boxfield[$key]->align; // auto align
		} else {
		  if (isset($Params->boxfield[$key]->processing)) $value = ProcessData($value, $Params->boxfield[$key]->processing);
		}
		$this->MultiCell($Params->boxfield[$key]->width, $CellHeight, $value, 0, $align, $fillReq?true:false);
		if ($this->GetY() > $maxY) $maxY = $this->GetY();
		$NextXPos += $Params->boxfield[$key]->width;
		$Col++;
	  }
	  $ThisRowHt = $maxY - $this->y0; // seee how tall this row was
	  if ($ThisRowHt > $MaxRowHt) $MaxRowHt = $ThisRowHt; // keep that largest row so far to track pagination
	  $this->y0 = $maxY; // set y position to largest value for next row
	  if ($Heading && $Params->hbordershow) { // then it's the heading draw a line after if fill is set
		$this->Line($Params->abscissa, $maxY, $Params->abscissa + $Params->width, $maxY);
		$this->y0 = $this->y0 + ($Params->hsize * 0.35);
	  }
	  return $MaxRowHt;
	}

	function DrawTableLines($Params, $HeadingHt) {
	  if ($Params->hbordershow == '') $Params->hbordershow = $Params->bordershow;
	  if ($Params->hbordersize == '') $Params->hbordersize = $Params->bordersize;
	  $hRGB = $Params->hbordercolor;
	  $DC = explode(':', $Params->bordercolor);
	  $hDC = (!$hRGB) ? $DC : explode(':', $hRGB);
	  $MaxBoxY = $Params->ordinate + $Params->height; // figure the max y position on page
	  // draw the heading 
	  $this->SetDrawColor($hDC[0], $hDC[1], $hDC[2]);
	  $this->SetLineWidth($Params->hbordersize * 0.35);
	  if ($Params->hbordershow) {
	    $this->Rect($Params->abscissa, $Params->ordinate, $Params->width, $HeadingHt);
		$NextXPos = $Params->abscissa;
		foreach ($Params->boxfield as $value) { // Draw the vertical lines
		  $this->Line($NextXPos, $Params->ordinate, $NextXPos, $Params->ordinate + $HeadingHt);
		  $NextXPos += $value->width;
		}
	  }
	  // draw the table lines
	  $this->SetDrawColor($DC[0], $DC[1], $DC[2]);
	  $this->SetLineWidth($Params->bordersize * 0.35);
	  // Fill the remaining part of the table with white
	  if ($this->y0 < $MaxBoxY) {
	  	$this->SetLeftMargin($Params->abscissa);
		$this->SetXY($Params->abscissa, $this->y0);
		$this->SetFillColor(255);
		if ($Params->fillshow) $this->Cell($Params->width, $MaxBoxY - $this->y0, '', 0, 0, 'L', 1);
	  }
	  if ($Params->bordershow) {
		$this->Rect($Params->abscissa, $Params->ordinate + $HeadingHt, $Params->width, $Params->height - $HeadingHt);
		$NextXPos = $Params->abscissa;
		foreach ($Params->boxfield as $value) { // Draw the vertical lines
		  $this->Line($NextXPos, $Params->ordinate + $HeadingHt, $NextXPos, $Params->ordinate + $Params->height);
		  $NextXPos += $value->width;
		}
	  }
	  return;
	}

	function TruncData($strData, $ColWidth) {
	  $percent = 0.90; //percent to truncate from max to account for proportional spacing
	  $CurWidth = $this->GetStringWidth($strData);
	  if ($CurWidth > ($ColWidth * $percent)) { // then it needs to be truncated
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
