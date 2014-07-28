<?php
class LoLSignature
{
	private $region;
	private $base;
	private $suffix;

	private $userData;

	public function __construct($key, $region) {
		$region = strtolower($region);
		$this->region = $region;
		$this->base = "https://" . $region . ".api.pvp.net/api/lol/" . $region;
		$this->suffix = "?api_key=" . $key;
	}

	private function LoadSummonerData($user) {
		$endpoint = $this->base . "/v1.4/summoner/by-name/" . $user . $this->suffix;
		$response = $this->_fetch($endpoint);
		$lolJSON = json_decode($response, true);

		if (isset($lolJSON[strtolower($user)])) {
			$this->userData = $lolJSON[strtolower($user)];
		}
		else {
			throw new Exception("Invalid summoner username.");
		}
	}

	private function GetProfilePicture($id) {
		$endpoint = "http://ddragon.leagueoflegends.com/realms/" . $this->region . ".json";
		$response = $this->_fetch($endpoint);
		$lolJSON = json_decode($response, true);
		$currentVer = $lolJSON["v"];
		
		$profileUrl = "http://ddragon.leagueoflegends.com/cdn/" . $currentVer . "/img/profileicon/" . $id . ".png";
		return imagecreatefrompng($profileUrl);
	}

	public function GenerateSignature($user, $font) {
		$this->LoadSummonerData($user);
		$origProfilePic = $this->GetProfilePicture($this->userData["profileIconId"]);
		$resizeTo = 60;
		$profilePic = imagecreatetruecolor($resizeTo, $resizeTo);
		imagecopyresized($profilePic, $origProfilePic, 0, 0, 0, 0, $resizeTo, $resizeTo, 128, 128);

		$sig = imagecreatetruecolor(400, 150);
		$bg = imagecolorallocate($sig, 36, 33, 33);
		imagefill($sig, 0, 0, $bg);

		imagecopy($sig, $profilePic, 20, 20, 0, 0, $resizeTo, $resizeTo);
		$userColor = imagecolorallocate($sig, 196, 208, 232);
		$center = ((400 + $resizeTo) - $this->GetWidthOfText(37, $font, $this->userData["name"])) / 2;
		imagettftext($sig, 37, 0, $center, 69, $userColor, $font, $this->userData["name"]);

		$otherColor = imagecolorallocate($sig, 195, 199, 208);
		imagettftext($sig, 23, 0, 20, 111, $otherColor, $font, "Level: " . $this->userData["summonerLevel"]);
		imagettftext($sig, 23, 0, 20, 142, $otherColor, $font, "Region: " . $this->GetFullRegionName());

		imagepng($sig);
		imagedestroy($sig);
	}

	private function GetFullRegionName() {
		return strtr(strtoupper($this->region), array(
			"BR" => "Brazil",
			"EUNE" => "EU Nordic & East",
			"EUW" => "EU West",
			"KR" => "Korea",
			"LAN" => "Latin America North",
			"LAS" => "Latin America South",
			"NA" => "North America",
			"OCE" => "Oceania",
			"RU" => "Russia",
			"TR" => "Turkey"
		));
	}

	private function GetWidthOfText($fontSize, $font, $str) {
		$imgArr = imagettfbbox($fontSize, 0, $font, $str);
		return abs($imgArr[2] - $imgArr[0]);
	}

	private function _fetch($url) {
		$ch = curl_init();
		curl_setopt($ch, CURLOPT_URL, $url);
		curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
		curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
		curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, false);
		curl_setopt($ch, CURLOPT_USERAGENT, "LoL-Signature/1.0 GitHub/ebildude123");
		curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, 5);
		$data = curl_exec($ch);
		curl_close($ch);
		return $data;
	}
}
?>