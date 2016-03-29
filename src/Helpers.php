<?php namespace Enchance\Helpers;

use Session;
use DB;
use Carbon;

class Helpers {

	/**
	 * Given a file, i.e. /css/base.css, replaces it with a string containing the
	 * file's mtime, i.e. /css/base.1221534296.css.
	 * @param  string  $file The file to be loaded.  Must be an absolute path (i.e. starting with slash)
	 * @return string
	 */
	public static function auto_version($file) {
	  if(strpos($file, '/') !== 0 || !file_exists($_SERVER['DOCUMENT_ROOT'] . $file))
	    return $file;

	  $mtime = filemtime($_SERVER['DOCUMENT_ROOT'] . $file);
	  return preg_replace('{\\.([^./]+)$}', ".$mtime.\$1", $file);
	}

	/**
	 * In DB results which are arrays of objects ([{},{}]), this creates an associative array
	 * by collating the 1st and 2nd object element as key=>val pairs or an indexed value of only
	 * the 1st element of the {}.
	 * Works on indexed and assoc arrays; works on objects as well.
	 * @param  array  $results    DB result (an array of objects)
	 * @param  boolean $assoc      Assoc array of the 1st and 2nd or non-assoc using only the 1st
	 * @param  boolean $is_indexed Useful if $assoc = false
	 * @return array
	 */
	public static function array_collate($results, $assoc = true, $is_indexed = true) {

		if($assoc) {

			// Get first 2 values (assoc)
			foreach($results as $val){
				$val = array_values((array)$val);
				$val[1] = isset($val[1]) ? $val[1] : $val[0];
				$arr[trim($val[0])] = trim($val[1]);
			}
						
		} else {

			// Get first value only
			$target_key = (array)$results[0];
			$target_key = array_keys($target_key);
			$target_key = $target_key[0];

			if($is_indexed) {
				foreach($results as $val){
					$val = (array)$val;
					$arr[] = trim($val[$target_key]);
				}
			} else {
				foreach($results as $val){
					$val = (array)$val;
					$arr[trim($val[$target_key])] = trim($val[$target_key]);
				}
			}

		}

		return $arr;
	}

	/**
	 * Capitalize results from self::array_collate()
	 * @param  array  $results    Results from self::array_collate()
	 * @param  array   $param      allcaps|lower will force allcaps or lowercase to an array of words or a sting if it's just one
	 * @return array              Results from original array with modified values (keys are the same, only values are modified)
	 */
	public static function array_collate_cap($results, $param = []) {
		// Init
		extract($param);

		$arr = self::array_collate($results);

		// Default
		$allcaps = isset($allcaps) ? $allcaps : [];
		$lower   = isset($lower) ? $lower : [];

		// Capialize the value
		array_walk($arr, function(&$val) use ($allcaps, $lower) {
			$val = ucwords($val);

			if($allcaps) {
				$allcaps = is_array($allcaps) ? $allcaps : [$allcaps];
				foreach($allcaps as $v) {
					$val = preg_replace("/\b{$v}\b/i", strtoupper($v), $val);
				}
			}

			if($lower) {
				$lower = is_array($lower) ? $lower : [$lower];
				foreach($lower as $v) {
					$val = preg_replace("/\b{$v}\b/i", strtolower($v), $val);
				}
			}

		});

		return $arr;
	}

	/**
	 * Merges a multidimensional array based on a specified key
	 * @param  array $results Multidimensional array to merge
	 * @param  string $name     Must be string or int. This becomes the key for each associative element.
	 *                         The value will be used as the key, and you can't do this if it's an array or object.
	 * @param boolean $retain_element Retain or remove the element used as the key ($name param)
	 * @param boolean $show_all If there is an array in consolidation, show only the first element. Usefull for overwriting data. 
	 * @return array          A simplified multidimensional array
	 */
	public static function array_consolidate($results, $name, $retain_element = true, $show_all = true) {
		// Bouncer
		if(!$results) return [];

		foreach($results as $val){
			$item = is_array($val) ? $val[$name] : $val->$name;
			foreach((array)$val as $k=>$v){

				if(!$retain_element) {
					if($item == $v) continue;
				}

				if($show_all) {
					$arr[$item][$k][] = $v;
				}
				else {
					if( !isset($arr[$item][$k]) ) $arr[$item][$k][0] = $v;
				}
			}
		}

		// Combine unique entries into a single array
		// and non-unique entries into a single element
		foreach($arr as $key=>$val){
			foreach($val as $k=>$v){
				$field = array_unique($v);
				
				if(count($field) == 1){
					$field = array_values($field);
					$field = $field[0];
					$arr[$key][$k] = $field;
				} else {
					$arr[$key][$k] = $field;
				}
			}
		}
		
		return $arr;
	}

	/**
	 * If you want to display an array in a table with elements ordered vertically instead
	 * of horizontally, use this. The form of the table is retained and only the ordering of the 
	 * elements vary.
	 * @param  	array $results Results from the DB. You may use a chunked array just be sure to set $chunk_it to false
	 * @param 	int $cols Column count
	 * @param 	bool chunk_it To array_chunk() or not to array_chunk()? That is the question.
	 * @param 	int $minimum_first_col If count is less or equal than this integer, it will all be placed in the first column only
	 * @return 	array          A chunked array from array_chunk() ready for insert as a table
	 */
	public static function array_vertical($results, $cols = 5, $chunk_it = TRUE, $minimum_items = 10) {

		if($chunk_it) $results = array_chunk($results, $cols);

		$rows = count($results);
		$total = $cols * $rows;

		$new_data = array();
		$j = 0;
		for($i = 0; $i < $total; $i++) {
		  $old_x = floor($i / $cols); // integer division
		  $old_y = $i % $cols;        // modulo

		  do {
		    $new_x = $j % $rows;        // modulo
		    $new_y = floor($j / $rows); // integer division
		    $j++;
		  // move on to the next position if we have reached an index that isn't available in the old data structure
		  } while (!isset($results[$new_x][$new_y]) && $j < $total);

		  if (!isset($new_data[$new_x])) {
		    $new_data[$new_x] = array();
		  }
		  if (isset($results[$old_x][$old_y])) {
		    $new_data[$new_x][$new_y] = $results[$old_x][$old_y];
		  }
		}

		return $new_data;
	}

	/**
	 * Returns active list of countries for the <select> field
	 * Format: `code`=>`name`
	 * @return array
	 */
	public static function get_countrylist() {
		// Init
		$prefix    = DB::getTablePrefix();
		$dbcountry = config('helpers.tables.dbcountry');

		try {
			$results = DB::select("
				SELECT `code`, `name` FROM {$prefix}{$dbcountry} ORDER BY `name`");

			return $results ? self::array_collate($results) : [];
		}
		catch(Exception $e) {
			throw new Exception;
		}
	}

	/**
	 * Returns active list of currencies for the <select> field
	 * Format: `currency_code`=>`currency`
	 * @return array
	 */
	public static function get_currencylist($code = false) {
		// Init
		$prefix = app('prefix');

		try {
			$results = DB::select("
				SELECT currency_code, currency FROM {$prefix}dbcountry");

			if($code) {
				foreach($results as $key=>$val) {
					unset($results[$key]->currency);
				}
			}

			return $results ? self::array_collate($results) : [];
		}
		catch(Exception $e) {
			throw new Exception;
			// Session::flash('datafail', trans('generic.datafail') );
		}
	}

	/**
	 * Get an option value from a user. If a $user_id is supplier, it will return the
	 * user's option value and not the global value. You cna mix global and user options
	 * @param  string|array $option_name A single or multiple options
	 * @param  int $user_id User ID to get options from. These would overwrite the default settings.
	 * @param boolean $simplify Simplify result to show optname and optvalue only
	 * @return string              The value of that option
	 */
	public static function get_option($option_name, $user_id = null, $simplify = true) {
		// Init
		$prefix        = app('prefix');
		$options       = config('helpers.tables.options');
		$users_options = config('helpers.tables.users_options');
		$arr           = [];
		$param         = [];

		try {

			// Convert string to array for shorter code
			if(is_string($option_name)) $option_name = [$option_name];

			// Generate params
			foreach($option_name as $val) {
				$arr[]   = '?';
				$param[] = $val;
			}
			$q_str = implode(',', $arr);

			if($user_id) {

				$param = array_merge($param, [$user_id], $param);

				$results = DB::select("
					SELECT opt.optname, uo.optvalue, opt.full FROM {$prefix}{$users_options} uo
						JOIN {$prefix}{$options} `opt` ON uo.option_id = opt.id 
							WHERE opt.optname IN ({$q_str}) AND uo.user_id = ?
						UNION
					SELECT optname, optvalue, full FROM {$prefix}{$options} WHERE optname IN ({$q_str})", $param);

			} else {

				$results = DB::select("
					SELECT optname, optvalue, full FROM {$prefix}{$options} WHERE optname IN ({$q_str})", $param);

			}

			$results = $results ? self::array_consolidate($results, 'optname', true, false) : [];

			// Show results
			if($simplify) {
				if(!$results) return [];

				$arr = [];
				foreach($results as $key=>$val) {
					$arr[$val['optname']] = $val['optvalue'];
				}

				return $arr;
			} else {
				return $results;
			}
			
		} catch(Exception $e) {
			throw new Exception;
		}

	}

	/**
	 * Remove empty <p> tags from string
	 * @param  string $string String with HTML in it
	 * @return string
	 */
	public static function removeEmptyP($string) {
		return preg_replace('#<p>(\s+|&nbsp;|<br\s*/?>)*(</p>)(?=<p>)#', '', $string);
	}

	/**
	 * Truncates a string to a specified number of words.
	 * @param  string  $text  String to truncate
	 * @param  integer $limit Word count
	 * @param string $end Appended to any string longer than the limit
	 * @return string         
	 */
	public static function truncate($text, $limit = 3, $type = 'word', $end = '...') {

		if($type == 'word') {
		  if (str_word_count($text, 0) > $limit) {
		    $words = str_word_count($text, 2);
		    $pos = array_keys($words);
		    $text = substr($text, 0, $pos[$limit]) . $end;
		  }

		} elseif($type == 'char') {
			
			$charset = 'UTF-8';
			if(mb_strlen($text, $charset) > $limit) {
			  $text = mb_substr($text, 0, $limit, $charset) . $end;
			}
		}

	  return $text;
	}

	/**
	 * Manually create the <option> tags for <select> fields
	 * @param  array  $list         Array of options
	 * @param  string  $active      The active <option> to be selected
	 * @param  boolean $retain_keys Retains the keys instead of using the value as the key
	 * @param  string $chooseone    Text for 0 selection
	 * @return string               Complete <option> list
	 */
	public static function create_select_options($list, $active = '', $retain_keys = false, $chooseone = '- choose -'){
		// Init
		$str = '';
		// $choose_one_text = [$chooseone];

		foreach($list as $key=>$val) {
			// Skip if default option
			if($val == $chooseone) {
				$str .= "<option value='0'>{$val}</option>";
				continue;
			}

			$val = trim(ucwords($val));

			if(is_string($key) || $retain_keys) {
				$selected = strcasecmp($key, $active) === 0 ? ' selected' : '';
				$str .= "<option value='{$key}'{$selected}>{$val}</option>";
			} else {
				$selected = strcasecmp($val, $active) === 0 ? ' selected' : '';
				$str .= "<option value='{$val}'{$selected}>{$val}</option>";
			}
		}

		return $str;
	}

	/**
	 * NEEDS TO BE CHECKED
	 * Generate an HTML string of <option> tags for a <select> field
	 * from an array of country codes (not names)
	 * @param  array $countrycode_arr Array of country codes
	 * @return string                  HTML string of <option> tags for inserting into a <select> tag
	 */
	/*public static function countrycode_to_options($countrycode_arr) {
		// Init
		$prefix = app('prefix');
		$arr = [];

		// Cleaner
		foreach($countrycode_arr as $val) {
			$arr[] = "'{$val}'";
		}
		$str = implode(',', $arr);

		try {
			$results = DB::select("
				SELECT `code`, `name` FROM {$prefix}dbcountry
					WHERE `code` IN ({$str})");

			if($results) {
				$results = self::array_collate($results);
				$results = self::create_select_options($results);
			}
			return $results ?  : '';
		}
		catch(Exception $e) {
			throw new Exception;
		}
	}*/

	/**
	 * Splits mobile phones by prefix and number.
	 * @param  string $number   Number to split
	 * @param  string $split_by prefix|number
	 * @return string
	 */
	public static function split_phone($number, $return_number = true) {

		// Init
		$number = self::cleanup_number($number);
		preg_match('/[0-9]{7}$/', $number, $match);

		if($match) {
			$number_code = $match[0];
			$prefix_code = str_replace($number_code, '', $number);
			$prefix_code = empty($prefix_code) ? 2 : $prefix_code;
			$prefix_code = str_pad($prefix_code, 2, '0', STR_PAD_LEFT);
		} else {
			if($number == '02') {
				$number_code = '';
				$prefix_code = $number;
			} else {
				$number_code = $number;
				$prefix_code = '02';
			}
		}

		// e.g. 09178186659
		if($return_number) {
			// return '8186659';
			return $number_code;
		} else {
			// return '0917';
			return $prefix_code;
		}

	}

	/**
	 * Cleanup of phone numbers
	 * @param  string|array $number Number or an array of numbers to clean
	 * @return string         Cleaned number
	 */
	public static function cleanup_number($number) {
		// Init
		$replace_str = config('helpers.regex.cleanup_number');

		if(is_string($number)) {
			$number = preg_replace($replace_str, '', $number);
			$number = trim($number);
		} elseif(is_array($number)) {
			foreach($number as $key=>$val) {
				$val = preg_replace($replace_str, '', $val);
				$val = trim($val);
				$number[$key] = $val;
			}
		}
		return $number;
	}

	/**
	 * Clean a mobile number. Results in a number in the format +63xxxXXXXXXX
	 * Don't try on a landline number since it will only mess it up.
	 * @param  string $number Number to clean
	 * @param string $country_code Country code
	 * @return string         The cleaned number
	 */
	public static function clean_mobile($num, $country_code = '') {
		// Init
		$country_code = $country_code ? $country_code : '+63';
		$num          = self::cleanup_number($num);
		$len          = strlen($num);

		switch($len) {
			case 10:
				// If 10: Add CC
				$num = $country_code . $num;
				break;

			case 11:
				// If 11: Drop first char, add CC
				$num = substr($num, 1);
				$num = $country_code . $num;
				break;

			case 12:
				// If 12: Add +
				$num = '+' . $num;
				break;

			case 13:
				// If 13: Get first 2 + last 10
				$first = substr($num, 0, 2);
				$last = substr($num, -10);
				$num = $first . $last;
				break;

		}

		return $num;
	}

	/**
	 * Recreates each number string by scanning for any letters (if there are any) and replaces
	 * it with its numerical equivalent using self::touchtone_number()
	 * @param  string $num Number to scan through
	 * @return string
	 */
	public static function convert_touchtone($num) {
		// Init
		$str = '';

		$num = str_split($num, 1);
		foreach($num as $val) {
			if(is_numeric($val)) {
				$str .= $val;
			} else {
				$str .= self::touchtone_number($val);
			}
		}

		return $str;
	}

	/**
	 * Convert touchtone letters to numbers
	 * @param  string $num          String containing letters
	 * @param int $default Default number
	 * @return string
	 */
	public static function touchtone_number($letter, $default = 0){
		// Init
		$result = '';
		$letter = strtolower($letter);

		$keypad = [
			'a' => 2, 'b' => 2, 'c' => 2, 'd' => 3,
			'e' => 3, 'f' => 3, 'g' => 4, 'h' => 4,
			'i' => 4, 'j' => 5, 'k' => 5, 'l' => 5,
			'm' => 6, 'n' => 6, 'o' => 6, 'p' => 7,
			'q' => 7, 'r' => 7, 's' => 7, 't' => 8,
			'u' => 8, 'v' => 8, 'w' => 9, 'x' => 9,
			'y' => 9, 'z' => 9
		];

		return isset($keypad[$letter]) ? $keypad[$letter] : $default;
	}

	/**
	 * Convert multiple numbers into a string
	 * @param  array  $numbers Array of strings of numbers
	 * @param  boolean $cleanup Numbers are cleaned before return
	 * @return string
	 */
	public static function collate_numbers($numbers, $cleanup = TRUE, $glue = ', ') {
		foreach($numbers as $key=>$val) {
			if($val == '02') $numbers[$key] = '';
		}
		$numbers = array_filter($numbers);
		foreach($numbers as $key=>$val) {
			$val = str_replace(',', '/', $val);
			if(preg_match('/\//', $val)) {
				$more_arr = explode('/', $val);
				$numbers = array_merge($numbers, $more_arr);
			}
		}
		foreach($numbers as $key=>$val) {
			if(preg_match('/[,\/]/', $val)) unset($numbers[$key]);
		}
		if($cleanup) $numbers = self::cleanup_number($numbers);
		$number_str = implode($glue, $numbers);

		return $number_str;
	}

	/**
	 * UPDATE REQUIRED
	 * Separate numbers into the primary and seconday number
	 * The first number becomes the primary while all the rest become secondary
	 * regardless of how many there are.
	 * @param  string $number Collation of numbers.
	 * @return array         $arr[0]: primary, $arr[1]: secondary
	 */
	public static function extract_numbers($number) {
		// $number = self::cleanup_number($number);
		$number = str_replace('/', ',', $number);
		$number = explode(',', $number);

		$arr[0] = $number[0];
		// $arr[0] = $arr[0] ? $arr[0] : '';
		unset($number[0]);
		$arr[1] = self::collate_numbers($number);
		// $arr[1] = $arr[1] ? $arr[1] : '';

		return $arr;
	}

	/**
	 * UPGRADE REQUIRED
	 * Simple number checker
	 * @param  string  $number A sample phone number
	 * @return boolean
	 */
	public static function is_phone($number) {
		return !preg_match('/[0-9]{10,}/', $number);
	}

	/**
	 * Calculate the current age of the user
	 * @param  string $dob Date of birth
	 * @return int      Age of the person relative to today's date
	 */
	public static function get_age($dob) {
		// Init
		$dob = date('Y-m-d', strtotime($dob));
		$dob = new DateTime($dob);
		$today = new DateTime(date('Y-m-d'));

		// Calculate age
		$diff = $today->diff($dob);
		
		return $diff->y;
	}

	/**
	 * Choose a random Hex color
	 * @return string
	 */
	public static function randomcolor() {
    $possibilities = array(1, 2, 3, 4, 5, 6, 7, 8, 9, "A", "B", "C", "D", "E", "F" );
    shuffle($possibilities);
    $color = "#";
    for($i=1;$i<=6;$i++){
        $color .= $possibilities[rand(0,14)];
    }
    return $color;
	}

	/**
	 * Generate random dates
	 * @param  integer $count  How many random dates to create
	 * @param  string  $format Date format according to date()
	 * @return array
	 */
	public static function rand_date($count = 1, $format = 'Y-m-d') {
		// Init
		$arr = array();

		for($i = 1; $i <= $count; $i++) {
			$arr[] = date($format, rand(0, time()));
		}

		return $arr;
	}

	/**
	 * Convert time from one timezone to another
	 * @param  string $date The date to convert
	 * @param  string $from Current timezone of date
	 * @param  string $to   Timezone you want to convert to
	 * @return object       Carbon object
	 */
	public static function convertTimezone($date, $from = '', $to = '') {
		// Convert from one timezone to another
		return Carbon::parse($date, $from)->timezone($to);
	}

	/**
	 * Convert date from user's timezone to app's timezone before saving to db.
	 * This makes sure all dates in the db are of the same timezone.
	 * @see self::toUserTimezone() Opposite of this method
	 * @param  string $date    Date to convert
	 * @param  string $user_tz User's timezone
	 * @param  string $format  Format of the date string
	 * @return array
	 */
	public static function toAppTimezone($date, $user_tz = '', $format = 'Y-m-d H:i:s') {
		// Init
		$app_tz = config('app.timezone');
		$user_tz = $user_tz ? $user_tz : config('acctinfo')['timezone'];
		
		$carbon = self::convertTimezone($date, $user_tz, $app_tz);
		return $carbon->format($format);
	}

	/**
	 * Convert date from app's timezone to user's timezone before saving to db.
	 * This customizes all dates according to the user's set timezone in their settings.
	 * @see self::toAppTimezone() Opposite of this method
	 * @param  string $date    Date to convert
	 * @param  string $user_tz User's timezone
	 * @param  string $format  Format of the date string
	 * @return array
	 */
	public static function toUserTimezone($date, $user_tz = '', $format = 'Y-m-d H:i:s') {
		// Init
		$app_tz = config('app.timezone');
		$user_tz = $user_tz ? $user_tz : config('acctinfo')['timezone'];
		
		$carbon = self::convertTimezone($date, $app_tz, $user_tz);
		return $carbon->format($format);
	}

	/**
	 * Enclose field names in backticks for MySQL use
	 * @param  array $field_arr Field names
	 * @return array
	 */
	public static function backtick($field_arr, $backtick = true, $split = true) {

		// Bouncer
		if(is_array($field_arr) && $field_arr) {
			array_walk($field_arr, function(&$val) use ($backtick, $split) {
				if($backtick) {
					$arr = explode(' ', $val);
					if(count($arr) == 1) {
						$val = "`{$val}`";
					} else {
						$val = $split ? "`{$arr[0]}` " . $arr[1] : "`{$val}`";
					}
				} else {
					$val = addslashes($val);
					$arr = explode(' ', $val);
					if(count($arr) == 1) {
						$val = "'{$val}'";
					} else {
						$val = $split ? "'{$arr[0]}' " . $arr[1] : "'{$val}'";
					}
				}
			});

			return $field_arr;
		}
	}

	/**
	 * Separate numbers into the primary and seconday number
	 * The first number becomes the primary while all the rest become secondary
	 * regardless of how many there are.
	 * @param  string $number Collation of numbers.
	 * @return array         $arr[0]: primary, $arr[1]: secondary
	 */
	public static function get_primary_number($number) {
		$number = str_replace('/', ',', $number);
		$number = str_replace(' ', '', $number);
		$number = explode(',', $number);

		$arr[0] = $number[0];
		unset($number[0]);
		if(count($number)) $arr[1] = self::collate_numbers($number);

		return $arr;
	}

	/**
	 * Break a fullname in first and last name
	 * @param string $fullname Fullname that needs to be broken
	 * @return array
	 */
	public static function parse_name($fullname) {
		// Init
		$prefix = config('helpers.regex.name_prefix');
		$suffix = config('helpers.regex.name_suffix');
		$append = '';
		
		// Boom!
		$arr = explode(' ', $fullname);
		$arr = array_filter($arr);

		// Isolate appended names
		$reverse = array_reverse($arr);
		preg_match($suffix, $reverse[0], $matches);
		$matches = array_filter($matches);
		if($matches) {
			$append = $reverse[0];
			unset($reverse[0]);
		}
		$arr = array_reverse($reverse);

		if(count($arr) == 1) {

			$firstname = $arr[0];
			$lastname  = '';

		} elseif(count($arr) == 2) {

			$firstname = $arr[0];
			$lastname  = $arr[1];

		} elseif(count($arr) == 3) {

			// Check
			if( preg_match($prefix, $arr[1]) ) {
				$firstname = $arr[0];
				$lastname  = "{$arr[1]} {$arr[2]}";
			} else {
				$firstname = "{$arr[0]} {$arr[1]}";
				$lastname  = $arr[2];
			}

		} else {

			if( preg_match($prefix, $arr[1]) ) {
				$firstname = $arr[0];
				$lastname = "{$arr[1]} {$arr[2]} {$arr[3]}";
			} elseif( preg_match($prefix, $arr[2]) ) {
				$firstname = "{$arr[0]} {$arr[1]}";
				$lastname = "{$arr[2]} {$arr[3]}";
			} else {
				$firstname = "{$arr[0]} {$arr[1]} {$arr[2]}";
				$lastname = $arr[3];
			}

		}

		$firstname = trim($firstname);
		$lastname .= " {$append}";
		$lastname = trim($lastname);
		return compact('firstname', 'lastname');
	}

	/**
	 * Generates the string to be used when sorting.
	 * @param  string  $sort        Comma-separated list of fields to sort through
	 * @param  string  $dir         asc|desc
	 * @param  boolean $distribute  If $sort has more than one element, apply $dir to all or only the first element
	 * @param  string  $default_dir Default $dir if only the first element uses $dir but there are multiple elements
	 * @return string
	 */
	public static function sort_str($sort, $dir, $distribute = true, $default_dir = 'ASC') {
		// Init
		$sort_arr = explode(',', $sort);

		if($distribute) {
			// Apply $dir to all sorts
			array_walk($sort_arr, function(&$val) use ($dir) {
				$val = "{$val} {$dir}";
			});
			$sort_str = implode(', ', $sort_arr);
		}
		else {
			// Apply $dir to only the first sort
			foreach($sort_arr as $key=>$val) {
				$arr[] = $key == 0 ? "{$val} {$dir}" : "{$val} {$default_dir}";
			}
			$sort_str = implode(', ', $arr);
		}

		return $sort_str;
	}

}