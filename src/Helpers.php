<?php namespace Enchance\Helpers;

use Session;

class Helpers {

	/**
	 * Classes for the <body> tag
	 * @param string|array $append Append to the list
	 * @return string
	 */
	public static function body_class($append = '') {

		if(Sentry::check()) {
			// Init
			$user     = Sentry::getUser();
			$provider = Sentry::getThrottleProvider();
			$role     = Session::get('account')['role'];
			$arr[]    = 'registered';

			// Throttle
			if($provider->isEnabled()) {
				$throttle = Sentry::findThrottlerByUserId($user->id);
				$arr[] = Sentry::isActivated() ? 'activated' : '';
				$arr[] = $throttle->isBanned() ? 'banned' : '';
			}
			
			// Role
			if($role) {
				if(is_array($role)) {
					foreach($role as $val) {
						$roles[] = 'role-' . $val;
					}
					$arr[] = implode(' ', $roles);
				} else {
					$arr[] = 'role-' . $role;
				}
			}

		} else {
			$arr[] = 'guest';
		}

		// Append
		if($append) {
			$arr[] = is_array($append) ? implode(' ', $append) : trim($append);
		}

		$arr = array_filter($arr);
		return implode(' ', $arr);
	}

}