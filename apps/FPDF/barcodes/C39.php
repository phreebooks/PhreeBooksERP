<?php
/*
 * Include file to generate Code 39 barcodes as modified from fpdf.org
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
 * @copyright  2008-2020, PhreeSoft, Inc.
 * @license    http://opensource.org/licenses/OSL-3.0 Open Software License (OSL 3.0)
 * @version    3.x Last Update: 2020-03-18
 * @filesource /apps/FPDF/barcodes/C39.php
 */

class C39 {

    protected $T128;
    protected $ABCset  = "";
    protected $Aset    = "";
    protected $Bset    = "";
    protected $Cset    = "";
    protected $SetFrom;
    protected $SetTo;
    protected $JStart  = ["A"=>103, "B"=>104, "C"=>105];
    protected $JSwap   = ["A"=>101, "B"=>100, "C"=>99];

    function Barcode(&$PDF, $code, $xpos, $ypos, $width=40, $height=15)
    {
        $height  -= 5; // correction to allow text to show
        $baseline = $width / 50; // correction factor to get a reasonably close dimension
        $wide     = $baseline;
        $narrow   = $baseline / 3;
        $gap      = $narrow;
        $barChar['0'] = 'nnnwwnwnn';
        $barChar['1'] = 'wnnwnnnnw';
        $barChar['2'] = 'nnwwnnnnw';
        $barChar['3'] = 'wnwwnnnnn';
        $barChar['4'] = 'nnnwwnnnw';
        $barChar['5'] = 'wnnwwnnnn';
        $barChar['6'] = 'nnwwwnnnn';
        $barChar['7'] = 'nnnwnnwnw';
        $barChar['8'] = 'wnnwnnwnn';
        $barChar['9'] = 'nnwwnnwnn';
        $barChar['A'] = 'wnnnnwnnw';
        $barChar['B'] = 'nnwnnwnnw';
        $barChar['C'] = 'wnwnnwnnn';
        $barChar['D'] = 'nnnnwwnnw';
        $barChar['E'] = 'wnnnwwnnn';
        $barChar['F'] = 'nnwnwwnnn';
        $barChar['G'] = 'nnnnnwwnw';
        $barChar['H'] = 'wnnnnwwnn';
        $barChar['I'] = 'nnwnnwwnn';
        $barChar['J'] = 'nnnnwwwnn';
        $barChar['K'] = 'wnnnnnnww';
        $barChar['L'] = 'nnwnnnnww';
        $barChar['M'] = 'wnwnnnnwn';
        $barChar['N'] = 'nnnnwnnww';
        $barChar['O'] = 'wnnnwnnwn';
        $barChar['P'] = 'nnwnwnnwn';
        $barChar['Q'] = 'nnnnnnwww';
        $barChar['R'] = 'wnnnnnwwn';
        $barChar['S'] = 'nnwnnnwwn';
        $barChar['T'] = 'nnnnwnwwn';
        $barChar['U'] = 'wwnnnnnnw';
        $barChar['V'] = 'nwwnnnnnw';
        $barChar['W'] = 'wwwnnnnnn';
        $barChar['X'] = 'nwnnwnnnw';
        $barChar['Y'] = 'wwnnwnnnn';
        $barChar['Z'] = 'nwwnwnnnn';
        $barChar['-'] = 'nwnnnnwnw';
        $barChar['.'] = 'wwnnnnwnn';
        $barChar[' '] = 'nwwnnnwnn';
        $barChar['*'] = 'nwnnwnwnn';
        $barChar['$'] = 'nwnwnwnnn';
        $barChar['/'] = 'nwnwnnnwn';
        $barChar['+'] = 'nwnnnwnwn';
        $barChar['%'] = 'nnnwnwnwn';

        $PDF->SetFont('Arial','',10);
        $PDF->Text($xpos, $ypos + $height + 4, $code);
        $PDF->SetFillColor(0);

        $code = '*'.strtoupper($code).'*';
        for ($i=0; $i<strlen($code); $i++){
            $char = $code[$i];
            if (!isset($barChar[$char])) { $PDF->Error('Invalid character in barcode: '.$char); }
            $seq = $barChar[$char];
            for ($bar=0; $bar<9; $bar++) {
                if ($seq[$bar] == 'n') { $lineWidth = $narrow; }
                else                   { $lineWidth = $wide; }
                if ($bar % 2 == 0) { $PDF->Rect($xpos, $ypos, $lineWidth, $height, 'F'); }
                $xpos += $lineWidth;
            }
            $xpos += $gap;
        }
    }
}