<?php
/*
 * Include file to generate EAN-13 barcodes as modified from fpdf.org
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
 * @version    3.x Last Update: 2019-12-20
 * @filesource /apps/FPDF/barcodes/EAN13.php
 */

class EAN13
{
    public function Barcode(&$PDF, $code, $x, $y, $w=40, $h=15) //($x, $y, $barcode, $h, $w, $len)
    {
        $len = 13; // for EAN13 (13 digits)
        $barcode = str_pad($code,$len-1,'0',STR_PAD_LEFT);
        if ($len==12) { $barcode='0'.$barcode; }
        if (strlen($barcode)==12)                 { $barcode.=$this->GetCheckDigit($barcode); }
        elseif (!$this->TestCheckDigit($barcode)) { $PDF->Error('Incorrect check digit'); }
        $codes= [
            'A'=> ['0'=>'0001101','1'=>'0011001','2'=>'0010011','3'=>'0111101','4'=>'0100011','5'=>'0110001','6'=>'0101111','7'=>'0111011','8'=>'0110111','9'=>'0001011'],
            'B'=> ['0'=>'0100111','1'=>'0110011','2'=>'0011011','3'=>'0100001','4'=>'0011101','5'=>'0111001','6'=>'0000101','7'=>'0010001','8'=>'0001001','9'=>'0010111'],
            'C'=> ['0'=>'1110010','1'=>'1100110','2'=>'1101100','3'=>'1000010','4'=>'1011100','5'=>'1001110','6'=>'1010000','7'=>'1000100','8'=>'1001000','9'=>'1110100']];
        $parities= [
            '0'=> ['A','A','A','A','A','A'],
            '1'=> ['A','A','B','A','B','B'],
            '2'=> ['A','A','B','B','A','B'],
            '3'=> ['A','A','B','B','B','A'],
            '4'=> ['A','B','A','A','B','B'],
            '5'=> ['A','B','B','A','A','B'],
            '6'=> ['A','B','B','B','A','A'],
            '7'=> ['A','B','A','B','A','B'],
            '8'=> ['A','B','A','B','B','A'],
            '9'=> ['A','B','B','A','B','A']];
        $bCode = '101';
        $p=$parities[$barcode[0]];
        for($i=1;$i<=6;$i++) { $bCode.=$codes[$p[$i-1]][$barcode[$i]]; }
        $bCode.='01010';
        for($i=7;$i<=12;$i++) { $bCode.=$codes['C'][$barcode[$i]]; }
        $bCode.='101';
        //Draw bars
        for($i=0;$i<strlen($bCode);$i++)
        {
            if($bCode[$i]=='1') { $PDF->Rect($x+$i*$w, $y, $w, $h, 'F'); }
        }
        //Print text uder barcode
        $PDF->SetFont('Arial','',12);
        $PDF->Text($x,$y+$h+11/$PDF->k,substr($barcode,-$len));
    }

        private function GetCheckDigit($barcode)
    {
        $sum = 0;
        for ($i=1;$i<=11;$i+=2) { $sum+=3*$barcode[$i]; }
        for ($i=0;$i<=10;$i+=2) { $sum+=$barcode[$i]; }
        $r = $sum%10;
        if ($r>0) { $r=10-$r; }
        return $r;
    }

    private function TestCheckDigit($barcode)
    {
        $sum = 0;
        for ($i=1;$i<=11;$i+=2) { $sum+=3*$barcode[$i]; }
        for ($i=0;$i<=10;$i+=2) { $sum+=$barcode[$i]; }
        return ($sum+$barcode[12])%10==0;
    }
}