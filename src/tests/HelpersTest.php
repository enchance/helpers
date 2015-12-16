<?php

use Enchance\Helpers\Helpers;

class HelpersTest extends TestCase {

	public function testCountryList() {
		$country_list = Helpers::get_countrylist();
		$this->assertNotEmpty($country_list);
	}

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
		$this->assertEquals($keys[0], 1);
		$this->assertEquals($keys[1], 2);
		$this->assertEquals($vals[0], 'John');
		$this->assertEquals($vals[1], 'Paul');


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

	public function testSplitPhone() {
		$this->assertEquals(Helpers::split_phone('09178186659'), '8186659');
		$this->assertEquals(Helpers::split_phone('9178186659'), '8186659');
		$this->assertEquals(Helpers::split_phone('639178186659'), '8186659');
		$this->assertEquals(Helpers::split_phone('+63 917 8186659'), '8186659');
		$this->assertEquals(Helpers::split_phone('+63 (917) 8186659'), '8186659');
		$this->assertEquals(Helpers::split_phone('+63 (917) 818-6659'), '8186659');
		$this->assertEquals(Helpers::split_phone('+63 (917) 818 6659'), '8186659');

		$this->assertEquals(Helpers::split_phone('09178186659', false), '0917');
		$this->assertEquals(Helpers::split_phone('9178186659', false), '0917');
		$this->assertEquals(Helpers::split_phone('639178186659', false), '63917');
		$this->assertEquals(Helpers::split_phone('+63 917 8186659', false), '63917');
		$this->assertEquals(Helpers::split_phone('+63 (917) 8186659', false), '63917');
		$this->assertEquals(Helpers::split_phone('+63 (917) 818-6659', false), '63917');
		$this->assertEquals(Helpers::split_phone('+63 (917) 818 6659', false), '63917');
	}

	public function testCleanupNumber() {
		$this->assertEquals(Helpers::cleanup_number('09178186659'), '09178186659');
		$this->assertEquals(Helpers::cleanup_number('(0917)8186659'), '09178186659');
		$this->assertEquals(Helpers::cleanup_number('0917-8186659'), '09178186659');
		$this->assertEquals(Helpers::cleanup_number('+63.917.818-6659'), '639178186659');
		$this->assertEquals(Helpers::cleanup_number('+63 (917) 818.6659'), '639178186659');
	}

	public function testConvertTouchtone() {
		$this->assertEquals(Helpers::convert_touchtone('ABC'), '222');
		$this->assertEquals(Helpers::convert_touchtone('DEF'), '333');
		$this->assertEquals(Helpers::convert_touchtone('GHI'), '444');
		$this->assertEquals(Helpers::convert_touchtone('JKL'), '555');
		$this->assertEquals(Helpers::convert_touchtone('MNO'), '666');
		$this->assertEquals(Helpers::convert_touchtone('PQRS'), '7777');
		$this->assertEquals(Helpers::convert_touchtone('TUV'), '888');
		$this->assertEquals(Helpers::convert_touchtone('WXYZ'), '9999');
	}

	public function testCleanMobile() {
		$this->assertEquals(Helpers::clean_mobile('09178186659'), '+639178186659');
		$this->assertEquals(Helpers::clean_mobile('(0917)8186659'), '+639178186659');
		$this->assertEquals(Helpers::clean_mobile('0917-8186659'), '+639178186659');
		$this->assertEquals(Helpers::clean_mobile('+63.917.818-6659'), '+639178186659');
		$this->assertEquals(Helpers::clean_mobile('+63 (917) 818.6659'), '+639178186659');
	}

	public function testCollateNumbers() {
		$this->assertEquals(Helpers::collate_numbers(['123']), '123');
		$this->assertEquals(Helpers::collate_numbers(['123', '']), '123');
		$this->assertEquals(Helpers::collate_numbers(['123', '456']), '123, 456');
		$this->assertEquals(Helpers::collate_numbers(['123', '456'], true, ' / '), '123 / 456');
	}

	public function testGetPrimaryNumber() {
		$arr1 = Helpers::get_primary_number('123/456');
		$arr2 = Helpers::get_primary_number('123/456/789');

		$this->assertEquals($arr1[0], '123');
		$this->assertEquals($arr1[1], '456');
		$this->assertEquals($arr2[0], '123');
		$this->assertEquals($arr2[1], '456, 789');
	}

	public function testBacktick() {
		$arr1 = Helpers::backtick(['a', 'b']);
		$arr2 = Helpers::backtick(['a', 'b'], false);

		$this->assertEquals($arr1[0], "`a`");
		$this->assertEquals($arr1[1], "`b`");
		$this->assertEquals($arr2[0], "'a'");
		$this->assertEquals($arr2[1], "'b'");
	}

}