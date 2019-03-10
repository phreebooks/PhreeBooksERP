<?php
/*
 * Curencies class to handle the cleaning and formatting for locales
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
 * @license    http://opensource.org/licenses/OSL-3.0  Open Software License (OSL 3.0)
 * @version    3.x Last Update: 2018-09-05
 * @filesource /locale/currency.php
 */

namespace bizuno;

class currency {
    function __construct()
    {
        $this->iso  = getUserCache('profile', 'currency', 'iso');
        $this->rate = 1;
    }

    /**
     * Formatted currency per the ISO code, if not the default currency then the prefix and suffix will be added
     * @param float $number - number to be formatted
     * @param string $iso - {default: getUserCache('profile', 'currency', false, 'USD')} Three character code for the ISO currency
     * @param float $xRate - exchange rate from the default ISO code
     * @return string - Converted number
     */
    public function format($number, $iso='', $xRate=1)
    {
        if (strlen($iso) <> 3) { $iso = getUserCache('profile', 'currency', false, 'USD'); }
        $values = getModuleCache('phreebooks', 'currency', 'iso', $iso);
        $format_number = number_format($number * $xRate, $values['dec_len'], $values['dec_pt'], $values['sep']);
        $zero = number_format(0, $values['dec_len']); // to handle -0.00
        if ($format_number == '-'.$zero) { $format_number = $zero; }
        if ($iso <> getUserCache('profile', 'currency', false, 'USD')) { // show prefix and sufix if not default
            if ($values['prefix']) { $format_number  = $values['prefix'].' '.$format_number; }
            if ($values['suffix']) { $format_number .= ' '.$values['suffix']; }
        }
        return $format_number;
    }
}
