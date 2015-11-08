<?php
// +-----------------------------------------------------------------+
// |                   PhreeBooks Open Source ERP                    |
// +-----------------------------------------------------------------+
// | Copyright(c) 2008-2015 PhreeSoft      (www.PhreeSoft.com)       |
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
//  Path: /includes/classes/DateTime.php
//
namespace core\classes;
class DateTime extends \DateTime {
	public $DateChoices = array(
			'a' => TEXT_ALL,
			'b' => TEXT_RANGE,
			'c' => TEXT_TODAY,
			'd' => TEXT_THIS_WEEK,
			'e' => TEXT_WEEK_TO_DATE,
			'f' => TEXT_CURRENT_PERIOD,
			'g' => TEXT_THIS_MONTH,
			'h' => TEXT_MONTH_TO_DATE,
			'i' => TEXT_THIS_QUARTER,							// This Quarter as Calander new in 4.0
			'j' => TEXT_THIS_QUARTER_IN_ACCOUNTING_PERIODS, 	// This Quarter in accountingperiods
			'k' => TEXT_QUARTER_TO_DATE,						// This Quarter to date as Calander new in 4.0
			'l' => TEXT_QUARTER_TO_DATE_IN_ACCOUNTING_PERIODS,	// Quarter to Date in accountingperiods
			'm' => TEXT_THIS_YEAR,								// This Year as Calander new in 4.0
			'n' => TEXT_THIS_YEAR_IN_ACCOUNTING_PERIODS,		// This Year in accountingperiods
			'o' => TEXT_YEAR_TO_DATE,							// This Year to date as Calander new in 4.0
			'p' => TEXT_YEAR_TO_DATE_IN_ACCOUNTING_PERIODS,		// This Year to Date in accountingperiods
			'z' => TEXT_DATE_BY_PERIOD,
	);

	/** builds sql date string and description string based on passed criteria
	 * function requires as input an associative array with two entries:
	 * @param date_prefs = imploded (:) string with three entries
	 *    entry 1 => date range specfication for switch statement
	 *    entry 2 => start date value db format
	 *    entry 3 => end date value db format
	 * @param df = database fieldname for the sql date search
	 */
	function sql_date_array($date_prefs, $fieldname) {
		$DateArray = explode(':', $date_prefs);
		$t = time();
		$start_date = '0000-00-00';
		$end_date = '2199-00-00';
		$raw_sql = '';
		$fildesc = '';
		switch ($DateArray[0]) {
			default:
			case "a": // All, skip the date addition to the where statement, all dates in db
				break;
			case "b": // Date Range
				$fildesc = TEXT_DATE_RANGE. ': ';
				if ($DateArray[1] <> '') {
			  		$start_date = $this->db_date_format($DateArray[1]);
			  		$raw_sql .=  "$fieldname >= '$start_date'";
			  		$fildesc .= ' ' . TEXT_FROM . ' ' . $DateArray[1];
				}
				if ($DateArray[2] <> '') { // a value entered, check
			  		if (strlen($raw_sql) > 0) $raw_sql .= ' and ';
			  		$end_date = $this->db_date_format($DateArray[2]);
			  		$raw_sql.= "$fieldname <= '$end_date'";
			  		$fildesc .= ' ' . TEXT_TO . ' ' . $DateArray[2];
				}
				$fildesc .= '; ';
				break;
			case "c": // Today (specify range for datetime type fields to match for time parts)
				$end_date = clone $this;
				$raw_sql = "$fieldname >= '{$this->format('Y-m-d')}' and $fieldname <= '{$end_date->format('Y-m-d')}'";
				$fildesc = TEXT_DATE_RANGE . ' = ' . $this->format(DATE_FORMAT) . '; ';
				break;
			case "d": // This Week
				$this->modify("-{$this->format('w')} day");
				$end_date = clone $this;
				$end_date->modify('+6 day');
				$raw_sql = "$fieldname >= '{$this->format('Y-m-d')}' and $fieldname <= '{$end_date->format('Y-m-d')}'";
				$fildesc = TEXT_DATE_RANGE . ' ' . TEXT_FROM . ' ' . $this->format(DATE_FORMAT) . ' ' . TEXT_TO . ' ' . $end_date->format(DATE_FORMAT) . '; ';
				break;
			case "e": // This Week to Date
				$end_date = clone $this;
				$this->modify("-{$this->format('w')} day");
				$raw_sql = "$fieldname >= '{$this->format('Y-m-d')}' and $fieldname <= '{$end_date->format('Y-m-d')}'";
				$fildesc = TEXT_DATE_RANGE . ' ' . TEXT_FROM . ' ' . $this->format(DATE_FORMAT) . ' ' . TEXT_TO . ' ' . $end_date->format(DATE_FORMAT) . '; ';
				break;
			case "g": // This Month
				$this->modify("-{$this->format('j')} day");
				$end_date = clone $this;
				$end_date->modify("+{$this->format('t')} day");
				$raw_sql = "$fieldname >= '{$this->format('Y-m-d')}' and $fieldname <= '{$end_date->format('Y-m-d')}'";
				$fildesc = TEXT_DATE_RANGE . ' ' . TEXT_FROM . ' ' . $this->format(DATE_FORMAT) . ' ' . TEXT_TO . ' ' . $end_date->format(DATE_FORMAT). '; ';
				break;
			case "h": // This Month to Date
				$end_date = clone $this;
				$this->modify("-{$this->format('j')} day");
				$raw_sql = "$fieldname >= '{$this->format('Y-m-d')}' and $fieldname <= '{$end_date->format('Y-m-d')}'";
				$fildesc = TEXT_DATE_RANGE . ' ' . TEXT_FROM . ' ' . $this->format(DATE_FORMAT) . ' ' . TEXT_TO . ' ' . $end_date->format(DATE_FORMAT). '; ';
				break;
			case "i": // This Quarter as Calander new in 4.0
				$QtrStrt = $this->format('m') - ($this->format('m') % 3);
				$this->modify("-{$this->format('j')} day -{$QtrStrt} month");
				$end_date = clone $this;
				$end_date->modify("+3 month");
				$raw_sql = "$fieldname >= '{$this->format('Y-m-d')}' and $fieldname <= '{$end_date->format('Y-m-d')}'";
				$fildesc = TEXT_DATE_RANGE . ' ' . TEXT_FROM . ' ' . $this->format(DATE_FORMAT) . ' ' . TEXT_TO . ' ' . $end_date->format(DATE_FORMAT) . '; ';
				break;
			case "j": // This Quarter in accountingperiods
				$QtrStrt = CURRENT_ACCOUNTING_PERIOD - ((CURRENT_ACCOUNTING_PERIOD - 1) % 3);
				$start_date = $this->get_fiscal_dates($QtrStrt);
				$end_date   = $this->get_fiscal_dates($QtrStrt + 2);
				$raw_sql = "$fieldname >= '{$start_date['start_date']}' and $fieldname <= '{$end_date['end_date']}'";
				$fildesc = TEXT_DATE_RANGE . ' ' . TEXT_FROM . ' ' . $this->locale_date($start_date['start_date']) . ' ' . TEXT_TO . ' ' . $this->locale_date($end_date['end_date']) . '; ';
				break;
			case "k": // Quarter to Date as Calander new in 4.0
				$end_date = clone $this;
				$QtrStrt = $this->format('m') - (floor($this->format('m') / 3) * 3);
				$this->modify("-{$this->format('j')} day -{$QtrStrt} month");
				$raw_sql = "$fieldname >= '{$this->format('Y-m-d')}' and $fieldname <= '{$end_date->format('Y-m-d')}'";
				$fildesc = TEXT_DATE_RANGE . ' ' . TEXT_FROM . ' ' . $this->format(DATE_FORMAT) . ' ' . TEXT_TO . ' ' . $end_date->format(DATE_FORMAT) . '; ';
				break;
			case "l": // Quarter to Date in accountingperiods
				$QtrStrt = CURRENT_ACCOUNTING_PERIOD - ((CURRENT_ACCOUNTING_PERIOD - 1) % 3);
				$start_date = $this->get_fiscal_dates($QtrStrt);
				$end_date = clone $this;
				$raw_sql = "$fieldname >= '{$start_date['start_date']}' and $fieldname <= '{$end_date->format('Y-m-d')}'";
				$fildesc = TEXT_DATE_RANGE . ' ' . TEXT_FROM . ' ' . $this->locale_date($start_date['start_date']) . ' ' . TEXT_TO . ' ' . $end_date->format(DATE_FORMAT) . '; ';
				break;
			case "m": // This Year as Calander new in 4.0
				$this->modify("-{$this->format('j')} -{$this->format('m')} month");
				$end_date = clone $this;
				$end_date->modify("+1 year");
				$raw_sql = "$fieldname >= '{$this->format('Y-m-d')}' and $fieldname < '{$end_date->format('Y-m-d')}'";
				$fildesc = TEXT_DATE_RANGE . ' ' . TEXT_FROM . ' ' . $this->format(DATE_FORMAT) . ' ' . TEXT_TO . ' ' . $end_date->format(DATE_FORMAT) . '; ';
				break;
			case "n": // This Year in accounting periods
				$YrStrt = CURRENT_ACCOUNTING_PERIOD - ((CURRENT_ACCOUNTING_PERIOD - 1) % 12);
				$start_date = $this->get_fiscal_dates($YrStrt);
				$end_date = $this->get_fiscal_dates($YrStrt + 11);
				$raw_sql = "$fieldname >= '{$start_date['start_date']}' and $fieldname <= '{$end_date['end_date']}'";
				$fildesc = TEXT_DATE_RANGE . ' ' . TEXT_FROM . ' ' . $this->locale_date($start_date['start_date']) . ' ' . TEXT_TO . ' ' . $this->locale_date($end_date['end_date']) . '; ';
				break;
			case "o": // Year to Date as Calander new in 4.0
				$end_date = clone $this;
				$this->modify("-{$this->format('j')} -{$this->format('m')} month");
				$raw_sql = "$fieldname >= '{$this->format('Y-m-d')}' and $fieldname < '{$end_date->format('Y-m-d')}'";
				$fildesc = TEXT_DATE_RANGE . ' ' . TEXT_FROM . ' ' . $this->format(DATE_FORMAT) . ' ' . TEXT_TO . ' ' . $end_date->format(DATE_FORMAT) . '; ';
				break;
			case "p": // Year to Date in accounting periods
				$YrStrt = CURRENT_ACCOUNTING_PERIOD - ((CURRENT_ACCOUNTING_PERIOD - 1) % 12);
				$start_date = $this->get_fiscal_dates($YrStrt);
				$end_date = clone $this;
				$raw_sql = "$fieldname >= '{$start_date['start_date']}' and $fieldname <= '{$end_date->format('Y-m-d')}'";
				$fildesc = TEXT_DATE_RANGE . ' ' . TEXT_FROM . ' ' . $this->locale_date($start_date['start_date']) . ' ' . TEXT_TO . ' ' . $end_date->format(DATE_FORMAT) . '; ';
				break;
			case "f": // This Period
				$temp = $this->get_fiscal_dates(CURRENT_ACCOUNTING_PERIOD);
				$start_date = $temp['start_date'];
				$end_date = $temp['end_date'];
				$raw_sql = 'period = ' . CURRENT_ACCOUNTING_PERIOD;
				$fildesc = TEXT_PERIOD . ' ' . CURRENT_ACCOUNTING_PERIOD . ' (' . $this->locale_date($start_date) . ' ' . TEXT_TO . ' ' . $this->locale_date($end_date) . '); ';
				break;
			case "z": // date by period
				$temp = $this->get_fiscal_dates($DateArray[1]);
				$start_date = $temp['start_date'];
				$end_date = $temp['end_date'];
				$raw_sql = 'period = ' . $DateArray[1];
				$fildesc = TEXT_PERIOD . ' ' . $DateArray[1] . ' (' . $this->locale_date($start_date) . ' ' . TEXT_TO . ' ' . $this->locale_date($end_date) . '); ';
				break;
		}
		$dates = array(
		  'sql'         => $raw_sql,
		  'description' => $fildesc,
		  'start_date'  => $start_date,
		  'end_date'    => $end_date,
		);
		return $dates;
	}

	/**
	 * returns a database date from when formated in DATE_FORMAT
	 * @param string $raw_date
	 * @return string
	 */
	static function db_date_format($raw_date = '') {
		$date = self::createFromFormat ( DATE_FORMAT , $raw_date);
		$errors = self::getLastErrors();
		if ($date->format('Y')  < 1900 || $date->format('Y')  > 2099) throw new \core\classes\userException("The year is lower than 1900 or higher than 2099 recieved: $date->format('Y') ", 'error');
		if ($errors['warning_count'] != 0)  throw new \core\classes\userException($errors['warnings'], 	'warning');
		if ($errors['error_count'] != 0)    throw new \core\classes\userException($errors['errors'],	'error');
		return $date->format('Y-m-d');
	}

	/**
	 * sets current date to local date.
	 * the date needs to be construced first.
	 * @param bool $long
	 * @return string
	 */
	function locale_date($long = false) { // from db to display format
		$errors = $this->getLastErrors();
		$year = $this->format('Y');
		if ($year  < 1900 || $year  > 2099) throw new \core\classes\userException("The year is lower than 1900 or higher than 2099 recieved: $year ", 'error');
		if ($errors['warning_count'] != 0)  throw new \core\classes\userException($errors['warnings'], 	'error');
		if ($errors['error_count'] != 0)    throw new \core\classes\userException($errors['errors'],	'error');
		if ($long) return $this->format(DATE_TIME_FORMAT);
		return $this->format(DATE_FORMAT);
	}

	/**
	 * gets fiscal dates from database.
	 * @param number $period in format YYYY/mm/dd
	 * @throws \core\classes\userException
	 * @return array (fiscal_year, start_date, end_date)
	 */
	static function get_fiscal_dates($period = 1) {
		global $admin;
		$result = $admin->DataBase->query("SELECT fiscal_year, start_date, end_date FROM " . TABLE_ACCOUNTING_PERIODS . " WHERE period = $period");
		// post_date is out of range of defined accounting periods
		if ($result->fetch(\PDO::FETCH_NUM) <> 1) throw new \core\classes\userException(ERROR_MSG_POST_DATE_NOT_IN_FISCAL_YEAR,'error');
		return $result;
	}
	/**
	 * returns the period of the date submitted.
	 * @param unknown $post_date
	 * @param string $hide_error
	 * @throws \core\classes\userException
	 * @return string|unknown
	 */
	static function period_of_date($post_date, $hide_error = false) {
		global $admin;
		if (is_object($post_date)) $post_date = $post_date->format('Y-m-d');
		$post_time_stamp = strtotime($post_date);
		$period_start_time_stamp = strtotime(CURRENT_ACCOUNTING_PERIOD_START);
		$period_end_time_stamp = strtotime(CURRENT_ACCOUNTING_PERIOD_END);
	
		if (($post_time_stamp >= $period_start_time_stamp) && ($post_time_stamp <= $period_end_time_stamp)) return CURRENT_ACCOUNTING_PERIOD;
		$sql = $admin->DataBase->prepare("SELECT period FROM " . TABLE_ACCOUNTING_PERIODS . " WHERE start_date <= '$post_date' and end_date >= '$post_date'");
		$sql->execute();
		if ($sql->fetch(\PDO::FETCH_NUM) <> 1) { // post_date is out of range of defined accounting periods
			if (!$hide_error) throw new \core\classes\userException(ERROR_MSG_POST_DATE_NOT_IN_FISCAL_YEAR);
		}
		$result = $sql->fetch(\PDO::FETCH_LAZY);
		if (!$hide_error) throw new \core\classes\userException(ERROR_MSG_BAD_POST_DATE);
		return $result['period'];
	}
}

?>