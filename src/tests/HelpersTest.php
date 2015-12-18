<?php

use Enchance\Helpers\Helpers;

class HelpersTest extends TestCase {

	/**
	 * @group query
	 */
	public function testCountryList() {
		$country_list = Helpers::get_countrylist();
		$this->assertNotEmpty($country_list);
	}

	/**
	 * @group array
	 */
	public function testArrayCollate() {
		// 1
		$arr = [
			['id' => 1, 'name' => 'John'],
			['id' => 2, 'name' => 'Paul'],
		];
		$new_arr = Helpers::array_collate($arr);
		$keys = array_keys($new_arr);
		$vals = array_values($new_arr);

		// Tests
		$this->assertEquals(1, $keys[0]);
		$this->assertEquals(2, $keys[1]);
		$this->assertEquals('John', $vals[0]);
		$this->assertEquals('Paul', $vals[1]);


		// 2
		$arr = [
			['id' => 'Hey'],
			['id' => 'You'],
		];
		$new_arr = Helpers::array_collate($arr);
		$keys = array_keys($new_arr);
		$vals = array_values($new_arr);

		// Tests
		$this->assertEquals($keys[0], 'Hey');
		$this->assertEquals($keys[1], 'You');
		$this->assertEquals($vals[0], 'Hey');
		$this->assertEquals($vals[1], 'You');

		$new_arr = Helpers::array_collate($arr, false);
		$keys = array_keys($new_arr);
		$vals = array_values($new_arr);

		// Tests
		$this->assertEquals($keys[0], 0);
		$this->assertEquals($keys[1], 1);
		$this->assertEquals($vals[0], 'Hey');
		$this->assertEquals($vals[1], 'You');
	}

	/**
	 * @group array
	 */
	public function testArrayConsolidate() {
		$arr = [
			['id' =>1, 'name'=>'John', 'color' => 'yellow', 'os'=>'mint'],
			['id' =>3, 'name'=>'Paul', 'color' => 'yellow', 'os'=>'mint'],
			['id' =>2, 'name'=>'James', 'color' => 'yellow', 'os'=>'mint'],
			['id' =>5, 'name'=>'Paul', 'color' => '', 'os'=>'mint'],
		];
		$arr = Helpers::array_consolidate($arr, 'name');

		$this->assertCount(3, $arr);
		$this->assertEquals($arr['Paul']['os'], 'mint');
		$this->assertEquals($arr['Paul']['id'][0], 3);
		$this->assertEquals($arr['Paul']['id'][1], 5);
		$this->assertEquals($arr['Paul']['color'][0], 'yellow');
		$this->assertEquals($arr['Paul']['color'][1], '');
	}

	/**
	 * @dataProvider cleanup_number_data
	 * @group phone
	 */
	public function testCleanupNumber($data, $expected) {
		$this->assertEquals($expected, Helpers::cleanup_number($data));
	}

	public function cleanup_number_data() {
		return [
			['09178186659', '09178186659'],
			['(0917)8186659', '09178186659'],
			['0917-8186659', '09178186659'],
			['+63.917.818-6659', '639178186659'],
			['+63 (917) 818.6659', '639178186659'],
		];
	}

	/**
	 * @dataProvider touchtone_data
	 * @group phone
	 */
	public function testConvertTouchtone($data, $expected) {
		$this->assertEquals(Helpers::convert_touchtone($data), $expected);
	}

	public function touchtone_data() {
		return [
			['ABC','222'],
			['DEF','333'],
			['GHI','444'],
			['JKL','555'],
			['MNO','666'],
			['PQRS','7777'],
			['TUV','888'],
			['WXYZ','9999'],
		];
	}

	/**
	 * @group phone
	 */
	public function testCleanMobile() {
		$this->assertEquals('+639178186659', Helpers::clean_mobile('09178186659'));
		$this->assertEquals('+639178186659', Helpers::clean_mobile('(0917)8186659'));
		$this->assertEquals('+639178186659', Helpers::clean_mobile('0917-8186659'));
		$this->assertEquals('+639178186659', Helpers::clean_mobile('+63.917.818-6659'));
		$this->assertEquals('+639178186659', Helpers::clean_mobile('+63 (917) 818.6659'));
	}

	/**
	 * @group phone
	 */
	public function testCollateNumbers() {
		$this->assertEquals('123', Helpers::collate_numbers(['123']));
		$this->assertEquals('123', Helpers::collate_numbers(['123', '']));
		$this->assertEquals('123, 456', Helpers::collate_numbers(['123', '456']));
		$this->assertEquals('123 / 456', Helpers::collate_numbers(['123', '456'], true, ' / '));
	}

	/**
	 * @group phone
	 */
	public function testGetPrimaryNumber() {
		$arr1 = Helpers::get_primary_number('123/456');
		$arr2 = Helpers::get_primary_number('123/456/789');

		$this->assertEquals('123', $arr1[0]);
		$this->assertEquals('456', $arr1[1]);
		$this->assertEquals('123', $arr2[0]);
		$this->assertEquals('456, 789', $arr2[1]);
	}

	/**
	 * @group strings
	 */
	public function testBacktick() {
		$arr1 = Helpers::backtick(['a', 'b']);
		$arr2 = Helpers::backtick(['a', 'b'], false);

		$this->assertEquals("`a`", $arr1[0]);
		$this->assertEquals("`b`", $arr1[1]);
		$this->assertEquals("'a'", $arr2[0]);
		$this->assertEquals("'b'", $arr2[1]);
	}

	/**
	 * Parse a full name string
	 * @dataProvider	fullnames_data
	 * @group strings
	 */
	public function testParseName($data, $expected) {
		$this->assertEquals($expected, Helpers::parse_name($data));
	}

	/**
	 * INCOMPLETE
	 */
	public function fullnames_data() {
		return [
			['Ani Nubian', ['firstname'=>'Ani', 'lastname'=>'Nubian']],
			['Ani Nubian II', ['firstname'=>'Ani', 'lastname'=>'Nubian II']],
			['Ani Nubian Ph.d', ['firstname'=>'Ani', 'lastname'=>'Nubian Ph.d']],
			['Ani de los Santos', ['firstname'=>'Ani', 'lastname'=>'de los Santos']],
			['Ani delos Santos', ['firstname'=>'Ani', 'lastname'=>'delos Santos']],
			['Ani Nubian, Jr.', ['firstname'=>'Ani', 'lastname'=>'Nubian, Jr.']],
			['Ani Nubian-Santos', ['firstname'=>'Ani', 'lastname'=>'Nubian-Santos']],
			['Ani Nubian-Santos III', ['firstname'=>'Ani', 'lastname'=>'Nubian-Santos III']],
			['Ani Nubian-Santos, Jr.', ['firstname'=>'Ani', 'lastname'=>'Nubian-Santos, Jr.']],
			['Ani Mation Nubian', ['firstname'=>'Ani Mation', 'lastname'=>'Nubian']],
			['Ani Mation Nubian II', ['firstname'=>'Ani Mation', 'lastname'=>'Nubian II']],
			['Ani Mation Woah Nubian', ['firstname'=>'Ani Mation Woah', 'lastname'=>'Nubian']],
			['Ani M. Nubian', ['firstname'=>'Ani M.', 'lastname'=>'Nubian']],
			['Ani Mation M. Nubian', ['firstname'=>'Ani Mation M.', 'lastname'=>'Nubian']],
			['Ani de Nubian-Santos', ['firstname'=>'Ani', 'lastname'=>'de Nubian-Santos']],
			['Ani M. de Nubian-Santos', ['firstname'=>'Ani M.', 'lastname'=>'de Nubian-Santos']],

			// Fails
			// ['Ani Mation M. de Nubian-Santos', ['firstname'=>'Ani Mation M.', 'lastname'=>'de Nubian-Santos']],
			// ['Ani Mation Saunders Nubian', ['firstname'=>'Ani Mation Saunders', 'lastname'=>'Nubian']],
		];
	}

	/**
	 * @dataProvider removeEmptyP_data
	 * @group strings
	 */
	public function testRemoveEmptyP($expected, $data) {
		$this->assertEquals($expected, Helpers::removeEmptyP($data));
	}

	public function removeEmptyP_data() {
		return [
			['<p>xxx</p>', '<p></p><p>xxx</p>'],
			['<p>xxx</p>', '<p> </p><p>xxx</p>'],
			['<p>xxx</p>', '<p> </p><p>xxx</p>'],
			['<p>xxx</p>', '<p>&nbsp;</p><p>xxx</p>'],
			['<p>xxx</p>', '<p><br /></p><p>xxx</p>'],
			['<p>xxx</p>', '<p><br /><br />&nbsp;</p><p>xxx</p>'],
		];
	}

	/**
	 * @dataProvider truncateword_data
	 * @group strings
	 */
	public function testTruncateWord($data, $expected) {
		$this->assertEquals($expected, Helpers::truncate($data));
	}

	public function truncateword_data() {
		return [
			['This is how we roll', 'This is how ...'],
			["This isn't how it's suppose dto be", "This isn't how ..."]
		];
	}

	/**
	 * @dataProvider truncatechar_data
	 * @group strings
	 */
	public function testTruncateChar($data, $expected) {
		$this->assertEquals($expected, Helpers::truncate($data, 5, 'char'));
	}

	public function truncatechar_data() {
		return [
			['This is how we roll', 'This ...'],
			["This isn't how it's suppose dto be", "This ..."]
		];
	}

	/**
	 * @dataProvider create_select_options_data
	 * @group strings
	 */
	public function testCreateSelectOptions($data, $expected) {
		$this->assertEquals($expected, Helpers::create_select_options($data));
	}

	public function create_select_options_data() {
		return [
			[['xxx'=>'Hey', 'yyy'=>'You'], "<option value='xxx'>Hey</option><option value='yyy'>You</option>"],
		];
	}

}