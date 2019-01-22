<?php
/*
 * Render functions for forms in PDF format
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
 * @version    3.x Last Update: 2018-06-06
 * @filesource /controller/module/phreeform/renderForm.php
 */

namespace bizuno;

require_once(BIZUNO_3P_TCPDF."config/bizuno_config.php");
require_once(BIZUNO_3P_TCPDF."tcpdf.php");

class PDF extends \TCPDF
{
    public $moduleID  = 'phreeform';
    var $defaultSize  = "10";
    var $defaultColor = "#000000";
    var $defaultAlign = "L";
    var $y0;             // current y position
    var $x0;             // current x position
    var $pageY;          // y value of bottom of page less bottom margin
    var $PageCnt;        // tracks the page count for correct page numbering for multipage and multiform printouts
    var $NewPageGroup;   // variable indicating whether a new group was requested
    var $PageGroups;     // variable containing the number of pages of the groups
    var $CurrPageGroup;  // variable containing the alias of the current page group
    protected $last_page_flag = false;

    function __construct() {
        global $report;
        $this->defaultFont = getModuleCache('phreeform','settings','general','default_font','helvetica');
        $PaperSize = explode(':', $report->page->size);
        parent::__construct($report->page->orientation, 'mm', strtoupper($PaperSize[0]), true, 'UTF-8', false); 
        $this->SetCellPadding(0);
        if ($report->page->orientation == 'P') { // Portrait - calculate max page height
            $this->pageY = $PaperSize[2] - $report->page->margin->bottom;
        } else { // Landscape
            $this->pageY = $PaperSize[1] - $report->page->margin->bottom;
        }
        $this->SetMargins(PDF_MARGIN_LEFT, PDF_MARGIN_TOP, PDF_MARGIN_RIGHT);
        $this->SetMargins($report->page->margin->left, $report->page->margin->top, $report->page->margin->right);
        $this->SetCellPaddings(1, 0, 1); // added to move text away from border in forms
        $this->SetAutoPageBreak(0, $report->page->margin->bottom);
        $this->SetFont($this->defaultFont);
        $this->SetDrawColor(128, 0, 0);
        $this->SetLineWidth(0.35); // 1 point
    }
    
    /**
     * Creates the header for every page of a PDF output file. Basically all page static data 
     * @global type $report - complete $report structure with data
     */
    function Header()
    { // prints all static information on the page
        global $report;
        $tempValues = $report->FieldValues;
        $this->tempValues = [];
        foreach ($report->fieldlist as $field) {
            if (!isset($field->settings)) { $field->settings = (object)[]; }
            // This flags an error for block types. Need to define as class for each level.
//            msgDebug("\nWorking with field ".print_r($field, true));
//            msgDebug("\nTesting for object settings ".(is_object($field->settings)?'true':'false'));
            if (!isset($field->settings->font))  {
                $field->settings->font  = isset($field->settings->hfont) ? $field->settings->hfont : $this->defaultFont;
            }
            if (!isset($field->settings->size))  { $field->settings->size  = $this->defaultSize; }
            if (!isset($field->settings->color)) { $field->settings->color = $this->defaultColor; }
            if (!isset($field->settings->align)) { $field->settings->align = $this->defaultAlign; }
            switch ($field->type) {
                case 'Data':
                    if (!isset($field->settings->display)) { $field->settings->display = 0; }
                    $cellValue = array_shift($tempValues);
                    $field->settings->text = $cellValue; // fill the data to display
                    if (!isset($field->settings->processing)) { $field->settings->processing = ''; }
                    if (!isset($field->settings->formatting)) { $field->settings->formatting = ''; }
                    // some special processing if display on first or last page only
                    if ($field->settings->display==2) {
                        $this->tempValues[] = $cellValue;
                        break; 
                    }
                case 'TBlk': // same operation as page number 
                case 'Text': 
                case 'CDta': 
                case 'CBlk':
                case 'LtrTpl':
                case 'PgNum':   $this->FormText($field);    break;
                case 'Img':     $this->FormImage($field);   break;
                case 'ImgLink': $this->FormImgLink($field, array_shift($tempValues)); break;
                case 'Line':    $this->FormLine($field);    break;
                case 'Rect':    $this->FormRect($field);    break;
                case 'BarCode': $this->FormBarCode($field, array_shift($tempValues)); break;
                case 'Tbl':     if (!empty($field->settings->fieldname)) { array_shift($tempValues); }  break; // scrap field as this is not static but is in the data array
                default: // do nothing
            }
        }
    }

    /**
     * Generates the footer data for forms, generally the totals
     * @global array $report - Complete $report structure with data
     */
    public function Footer()
    { // Prints totals at end of last page
        global $report;
        foreach ($report->fieldlist as $field) {
            if (!isset($field->settings->display)){ $field->settings->display = 0; }
            if (!isset($field->settings->font))   { $field->settings->font  = $this->defaultFont; }
            if (!isset($field->settings->size))   { $field->settings->size  = $this->defaultSize; }
            if (!isset($field->settings->color))  { $field->settings->color = $this->defaultColor; }
            if (!isset($field->settings->align))  { $field->settings->align = $this->defaultAlign; }
            if (!$this->last_page_flag && $field->settings->display=='2') {
                $field->settings->text = lang('continued');
                $this->FormText($field);
            } elseif ($field->type=='Data' && $field->settings->display=='2') {
                $field->settings->text = array_shift($this->tempValues);
                $this->FormText($field);
            } elseif ($field->type=='Ttl') { // all totals are printed as the footer
                $this->FormText($field);
            }
//            $this->FormText($field);
        }
    }

    public function Close() {
//        $this->last_page_flag = true;
        parent::Close();
    }
    /**
     * Create a new page group; call this before calling AddPage()
     */
    function StartPageGroup($page='')
    { // 
        $this->NewPageGroup = true;
    }

    /**
     * Returns the current page number being generated
     * @return integer
     */
    function GroupPageNo()
    { // current page in the group
        return $this->PageGroups[$this->CurrPageGroup];
    }

    /**
     * Alias of the current page group -- will be replaced by the total number of pages in this group
     * @return current page group 
     */
    public function PageGroupAlias()
    { // 
        return $this->CurrPageGroup;
    }

    protected function getPagePosition($display=0)
    {
        if (!$display) { return true; } // all pages
        msgDebug("\nSetting display value with display = $display");
        if ($display==1 && $this->GroupPageNo()==1) { return true; } // first page
        if ($display==2 && $this->GroupPageNo()==$this->PageGroupAlias()) { msgDebug("returning true last page"); return true; } // last page
        msgDebug("returning false, page ".$this->GroupPageNo()." of ".$this->PageGroupAlias());
        return false;
    }
    
    /**
     * Starts rending a page, static first then dynamic
     * @param char $orientation - page orientation
     * @param string $format - page size
     */
    public function _beginpage($orientation='', $format='')
    {
        parent::_beginpage($orientation, $format);
        if ($this->NewPageGroup) { // start a new group
            $n = sizeof((array)$this->PageGroups)+1;
            $alias = "{nb$n}";
            $this->PageGroups[$alias] = 1;
            $this->CurrPageGroup = $alias;
            $this->NewPageGroup = false;
        } else if ($this->CurrPageGroup) {
            $this->PageGroups[$this->CurrPageGroup]++;
        }
    }

    /**
     * Wrapper for the TCPDF method with the same name
     */
    function _putpages() {
        $nb = $this->page;
        if (!empty($this->PageGroups)) { // do page number replacement
            foreach ($this->PageGroups as $k => $v) {
                for ($n = 1; $n <= $nb; $n++) { $this->pages[$n] = str_replace($k, $v, $this->pages[$n]); }
            }
        }
        parent::_putpages();
    }

    /**
     * Creates an image and places it on the page
     * @param array $Params - attributes of the image element
     */
    function FormImage($Params)
    {
        if (!isset($Params->abscissa)) { $Params->abscissa = 10; }
        if (!isset($Params->ordinate)) { $Params->ordinate = 10; }
        if (!isset($Params->width))    { $Params->width    = 0; }
        if (!isset($Params->height))   { $Params->height   = 0; }
        if (is_file(BIZUNO_DATA."images/".$Params->settings->img_file)) {
            $this->Image(BIZUNO_DATA."images/".$Params->settings->img_file, $Params->abscissa, $Params->ordinate, $Params->width, $Params->height);
        } else { // no image was found at the specified path, draw a box
            $this->SetXY($Params->abscissa, $Params->ordinate);
            $this->SetFont($this->defaultFont, '', '10');
            $this->SetTextColor(255, 0, 0);
            $this->SetDrawColor(255, 0, 0);
            $this->SetLineWidth(0.35);
            $this->SetFillColor(255);
            $this->Cell($Params->width, $Params->height, lang('no_image'), 1, 0, 'C');
        }
    }

    /**
     * Creates an image link on the PDF output file
     * @param object $Params - typically the settings of a specific field
     * @param string $path - full path of where to find file
     * @return updated class variables ready to render PDF element
     */
    function FormImgLink($Params, $path)
    {
        if (!isset($Params->abscissa)) { return; } // don't do anything if data array has not been set
        if (!isset($Params->abscissa)){ $Params->abscissa = '8'; }
        if (!isset($Params->ordinate)){ $Params->ordinate = '8'; }
        if (!isset($Params->width))   { $Params->width    = ''; }
        if (!isset($Params->height))  { $Params->height   = ''; }
        if ( isset($Params->settings->processing)) { $path = viewProcess($path, $Params->settings->processing); }
        $ext = strtolower(substr($path, strrpos($path, '.')+1));
        if (is_file($path) && ($ext == 'jpg' || $ext == 'jpeg' || $ext == 'png')) {
            $this->Image($path, $Params->abscissa, $Params->ordinate, $Params->width, $Params->height);
        } else { // no image was found at the specified path, draw a box
            $this->SetXY($Params->abscissa, $Params->ordinate);
            $this->SetFont($this->defaultFont, '', '10');
            $this->SetTextColor(255, 0, 0);
            $this->SetDrawColor(255, 0, 0);
            $this->SetLineWidth(0.35);
            $this->SetFillColor(255);
            $this->Cell('30', '20', lang('no_image'), 1, 0, 'C');
        }
    }

    /**
     * Creates and places a single line on the PDF page.
     * @param array $Params - field settings
     * @return updated  class variables ready to render.
     */
    public function FormLine($Params) {
        if (!isset($Params->abscissa)) { return; } // don't do anything if data array has not been set
        if (!isset($Params->settings->bsize))   { $Params->settings->bsize    = '1'; }
        if (!isset($Params->settings->bcolor))  { $Params->settings->bcolor  = $this->defaultColor; }
        if (!isset($Params->settings->linetype)){ $Params->settings->linetype= 'H'; }
        if (!isset($Params->settings->length))  { $Params->settings->length  = 0; }
        $FC = $this->convertRGB($Params->settings->bcolor);
        $this->SetDrawColor($FC[0], $FC[1], $FC[2]);
        $this->SetLineWidth($Params->settings->bsize * 0.35);
        if       ($Params->settings->linetype == 'H') { // Horizontal
            $XEnd = $Params->abscissa + $Params->settings->length;
            $YEnd = $Params->ordinate;
        } elseif ($Params->settings->linetype == 'V') { // Vertical
            $XEnd = $Params->abscissa;
            $YEnd = $Params->ordinate + $Params->settings->length;
        } elseif ($Params->settings->linetype == 'C') { // Custom
            $XEnd = $Params->endabscissa;
            $YEnd = $Params->endordinate;
        } 
        $this->Line($Params->abscissa, $Params->ordinate, $XEnd, $YEnd);
    }

    /**
     * Places a rectangle in the page.
     * @param array $Params - fields attributes
     * @return updated session variables.
     */
    function FormRect($Params) {
        if (!isset($Params->abscissa)) { return; } // don't do anything if data array has not been set
        $DrawFill = '';
        if (isset($Params->settings->bshow) && $Params->settings->bshow == '1') { // Border
            $DrawFill = 'D';
            $FC = $this->convertRGB($Params->settings->bcolor);
            $this->SetDrawColor($FC[0], $FC[1], $FC[2]);
            $this->SetLineWidth($Params->settings->bsize * 0.35);
        } else { 
            $this->SetDrawColor(255);
            $this->SetLineWidth(0);
        }
        if (isset($Params->settings->fshow) && $Params->settings->fshow == '1') { // Fill
            $DrawFill .= 'F';
            $FC = $this->convertRGB($Params->settings->fcolor);
            $this->SetFillColor($FC[0], $FC[1], $FC[2]);
        } else {
            $this->SetFillColor(255);
        }
        $this->Rect($Params->abscissa, $Params->ordinate, $Params->width, $Params->height, $DrawFill);
    }

    /**
     * Creates and places a bar code image on the page
     * @param array $Params - field settings
     * @param string $data - data used to create new bar code image
     * @return updated variables
     */
    function FormBarCode($Params, $data) {
        if (!isset($Params->abscissa)) { return; } // don't do anything if data array has not been set
        if (!isset($Params->width))           { $Params->width  = ''; }
        if (!isset($Params->height))          { $Params->height = ''; }
        if (!isset($Params->settings->fshow)) { $Params->settings->fshow = '0'; }
        $style = [
            'position'    => '', 
            'border'      => isset($Params->settings->bshow) && $Params->settings->bshow ? true : false, 
//            'padding'     => 2, // in user units
            'text'        => true, // print text below barcode
            'font'        => $Params->settings->font == 'default' ? $this->defaultFont : $Params->settings->font,
            'fontsize'    => $Params->settings->size,
            'stretchtext' => 1, // 0 = disabled; 1 = horizontal scaling only if necessary; 2 = forced horizontal scaling; 3 = character spacing only if necessary; 4 = forced character spacing
            'fgcolor'     => $this->convertRGB($Params->settings->color),
            'stretch'     => false,
            'fitwidth'    => true,
//            'cellfitalign' => '',
//            'hpadding' => 'auto',
//            'vpadding' => 'auto',
        ];
        switch ($Params->settings->fshow) {
            default:
            case '0': $style['bgcolor'] = false; break;
            case '1': $style['bgcolor'] = $this->convertRGB($Params->settings->fcolor); break;
        }
        if (isset($Params->settings->processing)) { $data = viewProcess($data, $Params->settings->processing); }
        if (isset($Params->settings->formatting)) { $data = viewFormat ($data, $Params->settings->formatting); }
        $data = clean($data, 'alpha_num'); // need to remove all special characters
        $this->write1DBarcode($data, $Params->settings->barcode, $Params->abscissa, $Params->ordinate, $Params->width, $Params->height, 0.4, $style, 'N');
    }

    /**
     * Places a single text string on the page
     * @param array $Params - field settings
     * @return updated class variables
     */
    function FormText($Params) {
        if (!isset($Params->abscissa)) { return; } // don't do anything if data array has not been set
        $this->SetXY($Params->abscissa, $Params->ordinate);
        $this->SetFont($Params->settings->font == 'default' ? $this->defaultFont : $Params->settings->font, '', $Params->settings->size);
        $FC = $this->convertRGB($Params->settings->color);
        $this->SetTextColor($FC[0], $FC[1], $FC[2]);
        if (isset($Params->settings->bshow) && $Params->settings->bshow == '1') { // Border
            $Border = '1';
            $FC = $this->convertRGB($Params->settings->bcolor);
            $this->SetDrawColor($FC[0], $FC[1], $FC[2]);
            $this->SetLineWidth($Params->settings->bsize * 0.35);
        } else { 
            $Border = '0';
        }
        if (isset($Params->settings->fshow) && $Params->settings->fshow == '1') { // Fill
            $Fill = '1';
            $FC = $this->convertRGB($Params->settings->fcolor);
            $this->SetFillColor($FC[0], $FC[1], $FC[2]);
        } else { 
            $Fill = '0';
        }
        if ($Params->type <> 'PgNum') { $TextField = isset($Params->settings->text) ? $Params->settings->text : ''; }
            else { $TextField = $this->GroupPageNo().' '.lang('of').' '.$this->PageGroupAlias(); } // fix for multi-page multi-group forms
        if (isset($Params->settings->processing)) { $TextField = viewProcess($TextField, $Params->settings->processing); }
        if (isset($Params->settings->formatting)) { $TextField = viewFormat ($TextField, $Params->settings->formatting); }
        if ($TextField) { $this->MultiCell($Params->width, $Params->height, $TextField, $Border, $Params->settings->align, $Fill); }
    }

    /**
     * Builds a data table and places it on the form.
     * @param array $Params - field settings
     */
    function FormTable($Params) {
        msgDebug("\nParams = ".print_r($Params, true));
        if (!isset($Params->settings->hfcolor)) { $Params->settings->hfcolor = $this->defaultColor; }
        if (!isset($Params->settings->fcolor))  { $Params->settings->fcolor  = $this->defaultColor; }
        $hRGB = $Params->settings->hfcolor;
        $FC = $this->convertRGB($Params->settings->fcolor);
        $hFC = (!$hRGB) ? $FC : $this->convertRGB($hRGB);
        $MaxBoxY = $Params->ordinate + $Params->height; // figure the max y position on page

        $FillThisRow = false;
        $MaxRowHt = 0; //track the tallest row to estimate page breaks
        $HeadingHt= 0;
        $this->y0 = $Params->ordinate;
        $this->last_page_flag = false;
        foreach ($Params->data as $index => $myrow) {
            // See if we are at or near the end of the table box size
            if (($this->y0 + $MaxRowHt) > $MaxBoxY) { // need a new page
                $this->DrawTableLines($Params, $HeadingHt); // draw the box and lines around the table
                $this->AddPage();
                $this->y0 = $Params->ordinate;
                $this->SetLeftMargin($Params->abscissa);
                $this->SetXY($Params->abscissa, $Params->ordinate);
                $MaxRowHt = $this->ShowTableRow($Params, $Params->data[0], true, $hFC, true); // new page heading
                $HeadingHt= $MaxRowHt;
            }
            $this->SetLeftMargin($Params->abscissa);
            $this->SetXY($Params->abscissa, $this->y0);
            // fill in the data
            if ($index == 0) { // its a heading line
                $MaxRowHt = $this->ShowTableRow($Params, $myrow, true, $hFC, true);
                $HeadingHt= $MaxRowHt;
            } else {
                $MaxRowHt = $this->ShowTableRow($Params, $myrow, $FillThisRow, $FC);
            }
            $FillThisRow  = !$FillThisRow;
        }
        $this->DrawTableLines($Params, $HeadingHt); // draw the box and lines around the table
        $this->last_page_flag = true;
    }

    /**
     * Builds and places a single table row on the page.
     * @param array $Params - Field settings
     * @param array $myrow - a single row of data to place on page
     * @param boolean $FillThisRow - used for alternating background color to easier identify rows
     * @param integer $FC - fill color (in decimal 0 - 255)
     * @param boolean $Heading - [default false] set to true to generate a heading at top of table
     * @return integer - max row height used to control pagination and end of page position
     */
    public function ShowTableRow($Params, $myrow, $FillThisRow, $FC, $Heading = false) {
        if (!isset($Params->settings->hfshow)) { $Params->settings->hfshow = '0'; }
        if (!isset($Params->settings->fshow))  { $Params->settings->fshow  = '0'; }
        if (!isset($Params->settings->hbshow)) { $Params->settings->hbshow = '1'; }
        if (!isset($Params->settings->hfont))  { $Params->settings->hfont  = $this->defaultFont; }
        if (!isset($Params->settings->hsize))  { $Params->settings->hsize  = '12'; }
        if (!isset($Params->settings->halign)) { $Params->settings->halign = 'L'; }
        if (!isset($Params->settings->hcolor)) { $Params->settings->hcolor = $this->defaultColor; }
        $MaxBoxY = $Params->ordinate + $Params->height; // figure the max y position on page
        $fillReq = $Heading ? $Params->settings->hfshow : $Params->settings->fshow;
        if ($FillThisRow && $fillReq) {
            $this->SetFillColor($FC[0], $FC[1], $FC[2]); 
        } else {
            $this->SetFillColor(255);
        }
        if ($fillReq) { $this->Cell($Params->width, $MaxBoxY - $this->y0, '', 0, 0, 'L', 1); } // sets background to white
        $maxY     = $this->y0; // set to current top of row
        $Col      = 0;
        $MaxRowHt = 0;
        $NextXPos = $Params->abscissa;
        foreach ($myrow as $key => $value) {
            if (substr($key, 0, 1) == 'r') { $key = substr($key, 1); }
            $font  = ($Heading && $Params->settings->hfont  <> '') ? $Params->settings->hfont  : $Params->settings->boxfield[$key]->font;
            $size  = ($Heading && $Params->settings->hsize  <> '') ? $Params->settings->hsize  : $Params->settings->boxfield[$key]->size;
            $color = ($Heading && $Params->settings->hcolor <> '') ? $Params->settings->hcolor : $Params->settings->boxfield[$key]->color;
            $align = ($Heading && $Params->settings->halign <> '') ? $Params->settings->halign : $Params->settings->boxfield[$key]->align;
            $this->SetLeftMargin($NextXPos);
            $this->SetXY($NextXPos, $this->y0);
            $this->SetFont($font=='default' ? $this->defaultFont : $font, '', $size);
            $TC = $this->convertRGB($color);
            $this->SetTextColor($TC[0], $TC[1], $TC[2]);
            $CellHeight = ($size + 2) * 0.35;
//            if ($trunc) $value=$this->TruncData($value, $value->width);
            // special code for heading and data
            if ($Heading) {
                if ($align == 'A') { $align = $Params->settings->boxfield[$key]->align; } // auto align
            } else {
                if (isset($Params->settings->boxfield[$key]->processing)) $value = viewProcess($value, $Params->settings->boxfield[$key]->processing);
                if (isset($Params->settings->boxfield[$key]->formatting)) $value = viewFormat ($value, $Params->settings->boxfield[$key]->formatting);
            }
            $this->MultiCell($Params->settings->boxfield[$key]->width, $CellHeight, $value, 0, $align, $fillReq?true:false);
            if ($this->GetY() > $maxY) $maxY = $this->GetY();
            $NextXPos += $Params->settings->boxfield[$key]->width;
            $Col++;
        }
        $ThisRowHt = $maxY - $this->y0; // seee how tall this row was
        if ($ThisRowHt > $MaxRowHt) $MaxRowHt = $ThisRowHt; // keep that largest row so far to track pagination
        $this->y0 = $maxY; // set y position to largest value for next row
        if ($Heading && $Params->settings->hbshow) { // then it's the heading draw a line after if fill is set
            $this->Line($Params->abscissa, $maxY, $Params->abscissa + $Params->width, $maxY);
            $this->y0 = $this->y0 + ($Params->settings->hsize * 0.35);
        }
        return $MaxRowHt;
    }

    /**
     * Draws lines all the lines in a table 
     * @param array $Params - field settings
     * @param float $HeadingHt - height of the heading to determine ordinate starting point
     * @return null - data is added to PDF output string
     */
    function DrawTableLines($Params, $HeadingHt) {
        if (!isset($Params->settings->hbshow)) { $Params->settings->hbshow = $Params->settings->bshow; }
        if (!isset($Params->settings->hbsize)) { $Params->settings->hbsize = $Params->settings->bsize; }
        if (!isset($Params->settings->hbcolor)){ $Params->settings->hbcolor= '0:0:0'; }
        $hRGB = $Params->settings->hbcolor;
        $DC = $this->convertRGB($Params->settings->bcolor);
        $hDC = (!$hRGB) ? $DC : $this->convertRGB($hRGB);
        $MaxBoxY = $Params->ordinate + $Params->height; // figure the max y position on page
        // draw the heading 
        $this->SetDrawColor($hDC[0], $hDC[1], $hDC[2]);
        $this->SetLineWidth($Params->settings->hbsize * 0.35);
        if ($Params->settings->hbshow) {
            $this->Rect($Params->abscissa, $Params->ordinate, $Params->width, $HeadingHt);
            $NextXPos = $Params->abscissa;
            foreach ($Params->settings->boxfield as $value) { // Draw the vertical lines
                $this->Line($NextXPos, $Params->ordinate, $NextXPos, $Params->ordinate + $HeadingHt);
                $NextXPos += $value->width;
            }
        }
        // draw the table lines
        $this->SetDrawColor($DC[0], $DC[1], $DC[2]);
        $this->SetLineWidth($Params->settings->bsize * 0.35);
        // Fill the remaining part of the table with white
        if ($this->y0 < $MaxBoxY) {
            $this->SetLeftMargin($Params->abscissa);
            $this->SetXY($Params->abscissa, $this->y0);
            $this->SetFillColor(255);
            if ($Params->settings->fshow) { $this->Cell($Params->width, $MaxBoxY - $this->y0, '', 0, 0, 'L', 1); }
        }
        if (isset($Params->settings->bshow)) {
            $this->Rect($Params->abscissa, $Params->ordinate + $HeadingHt, $Params->width, $Params->height - $HeadingHt);
            $NextXPos = $Params->abscissa;
            foreach ($Params->settings->boxfield as $value) { // Draw the vertical lines
                $this->Line($NextXPos, $Params->ordinate + $HeadingHt, $NextXPos, $Params->ordinate + $Params->height);
                $NextXPos += $value->width;
            }
        }
    }

    /**
     * Truncates long data strings to fit within column width 
     * @param sting $strData - data string to measure and operate on
     * @param float $ColWidth - width of a column in ems
     * @return string - truncated string if longer than 90% of the width, original string if not
     */
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
