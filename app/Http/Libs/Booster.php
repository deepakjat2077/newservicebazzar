<?php

namespace App\Http\Libs;

use App\Models\LoginHistory;

class Booster{

	public function balanceCheck($user,$amount,$route = null){
		if ($amount > $user->balance) {
			$alert = ['danger','You have no sufficient balance'];
			if ($route) {
				return redirect()->route($route)->withAlert($alert);
			}else{
				return back()->withAlert($alert);
			}
		}
	}

	public function userBalance($user,$amount,$type){
		if ($type == '+') {
			$user->balance += $amount;
		}else{
			$user->balance -= $amount;
		}
    	$user->save();
    	return $user->balance;
	}

	public function chargeCalculator($amount,$percent,$fixed,$type){
		$charge = ($amount * $percent / 100) + $fixed;
		if($type == 'include')$amoAfterCharge = $amount + $charge;else$amoAfterCharge = $amount - $charge;
		return [
			'charge'=>$charge,
			'after_charge'=>$amoAfterCharge,
		];
	}

	public function createLoginLog($user){
		$userInfo = $this->userInfo();
		LoginHistory::create([
			'user_id'=>$user->id,
			'browser'=>$userInfo['browser'],
			'os'=>$userInfo['os'],
			'ip'=>$userInfo['ip'],
			'country'=>$userInfo['country'],
			'country_code'=>$userInfo['code'],
		]);
	}

	protected function userInfo(){
		$ip = null;
	    $deep_detect = TRUE;

	    if (filter_var($ip, FILTER_VALIDATE_IP) === FALSE) {
	        $ip = $_SERVER["REMOTE_ADDR"];
	        if ($deep_detect) {
	            if (filter_var(@$_SERVER['HTTP_X_FORWARDED_FOR'], FILTER_VALIDATE_IP))
	                $ip = $_SERVER['HTTP_X_FORWARDED_FOR'];
	            if (filter_var(@$_SERVER['HTTP_CLIENT_IP'], FILTER_VALIDATE_IP))
	                $ip = $_SERVER['HTTP_CLIENT_IP'];
	        }
	    }


	    $xml = @simplexml_load_file("http://www.geoplugin.net/xml.gp?ip=" . $ip);


	    $country = @$xml->geoplugin_countryName;
	    $city = @$xml->geoplugin_city;
	    $area = @$xml->geoplugin_areaCode;
	    $code = @$xml->geoplugin_countryCode;
	    $long = @$xml->geoplugin_longitude;
	    $lat = @$xml->geoplugin_latitude;

	    $data['country'] = $country;
	    $data['code'] = $code;
	    $data['ip'] = request()->ip();


	    $user_agent = $_SERVER['HTTP_USER_AGENT'];
	    $os_platform = "Unknown OS Platform";
	    $os_array = array(
	        '/windows nt 10/i' => 'Windows 10',
	        '/windows nt 6.3/i' => 'Windows 8.1',
	        '/windows nt 6.2/i' => 'Windows 8',
	        '/windows nt 6.1/i' => 'Windows 7',
	        '/windows nt 6.0/i' => 'Windows Vista',
	        '/windows nt 5.2/i' => 'Windows Server 2003/XP x64',
	        '/windows nt 5.1/i' => 'Windows XP',
	        '/windows xp/i' => 'Windows XP',
	        '/windows nt 5.0/i' => 'Windows 2000',
	        '/windows me/i' => 'Windows ME',
	        '/win98/i' => 'Windows 98',
	        '/win95/i' => 'Windows 95',
	        '/win16/i' => 'Windows 3.11',
	        '/macintosh|mac os x/i' => 'Mac OS X',
	        '/mac_powerpc/i' => 'Mac OS 9',
	        '/linux/i' => 'Linux',
	        '/ubuntu/i' => 'Ubuntu',
	        '/iphone/i' => 'iPhone',
	        '/ipod/i' => 'iPod',
	        '/ipad/i' => 'iPad',
	        '/android/i' => 'Android',
	        '/blackberry/i' => 'BlackBerry',
	        '/webos/i' => 'Mobile'
	    );
	    foreach ($os_array as $regex => $value) {
	        if (preg_match($regex, $user_agent)) {
	            $os_platform = $value;
	        }
	    }
	    $browser = "Unknown Browser";
	    $browser_array = array(
	        '/msie/i' => 'Internet Explorer',
	        '/firefox/i' => 'Firefox',
	        '/safari/i' => 'Safari',
	        '/chrome/i' => 'Chrome',
	        '/edge/i' => 'Edge',
	        '/opera/i' => 'Opera',
	        '/netscape/i' => 'Netscape',
	        '/maxthon/i' => 'Maxthon',
	        '/konqueror/i' => 'Konqueror',
	        '/mobile/i' => 'Handheld Browser'
	    );
	    foreach ($browser_array as $regex => $value) {
	        if (preg_match($regex, $user_agent)) {
	            $browser = $value;
	        }
	    }

	    $data['os'] = $os_platform;
	    $data['browser'] = $browser;

	    return $data;
	}
}
