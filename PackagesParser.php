<?php
//  PackagesParser.php
//  Written by 1GamerDev in 2018
//  This script parses both "Packages" and "Release" files.
//  Beta 2
header("Content-Type: text/text");
set_time_limit(0);
error_reporting(0);
function get_file_contents($url) {
	$request = curl_init();
  	curl_setopt($request, CURLOPT_URL, $url);
	curl_setopt($request, CURLOPT_HTTPHEADER, array(
	    'X-Unique-ID: ABCDEF1234567890ABCDEF1234567890ABCDEF12',
   		'User-Agent: Telesphoreo APT-HTTP/1.0.592',
  	 	'X-Firmware: 12.0.0',
 		'X-Machine: iPhone10,3'
	));
	curl_setopt($request, CURLOPT_RETURNTRANSFER, 1);
	curl_setopt($request, CURLOPT_VERBOSE, 1);
	curl_setopt($request, CURLOPT_HEADER, 1);
	curl_setopt($request, CURLOPT_FOLLOWLOCATION, true);
	$response = curl_exec($request);
	$header_size = curl_getinfo($request, CURLINFO_HEADER_SIZE);
	$header = substr($response, 0, $header_size);
	$body = substr($response, $header_size);
	$retcode = curl_getinfo($request, CURLINFO_HTTP_CODE);
	curl_close($request);
	return $body;
}
function startsWith($haystack, $needle) {
    return (substr($haystack, 0, strlen($needle)) === $needle);
}
function endsWith($haystack, $needle) {
    return strlen($needle) === 0 || (substr($haystack, - strlen($needle)) === $needle);
}
function exists($url) {
	$request = curl_init();
  	curl_setopt($request, CURLOPT_URL, $url);
	curl_setopt($request, CURLOPT_HTTPHEADER, array(
	    'X-Unique-ID: ABCDEF1234567890ABCDEF1234567890ABCDEF12',
   		'User-Agent: Telesphoreo APT-HTTP/1.0.592',
  	 	'X-Firmware: 12.0.0',
 		'X-Machine: iPhone10,3'
	));
	curl_setopt($request, CURLOPT_RETURNTRANSFER, 1);
	curl_setopt($request, CURLOPT_VERBOSE, 1);
	curl_setopt($request, CURLOPT_HEADER, 1);
	curl_setopt($request, CURLOPT_FOLLOWLOCATION, true);
	$response = curl_exec($request);
	$header_size = curl_getinfo($request, CURLINFO_HEADER_SIZE);
	$header = substr($response, 0, $header_size);
	$body = substr($response, $header_size);
	$retcode = curl_getinfo($request, CURLINFO_HTTP_CODE);
	curl_close($request);
	if ($retcode >= 400) {
		return false;
	}
	if (preg_match('/^Location: (.+)$/im', $response, $matches))
        return trim($matches[1]);
	return $url;
}
function splitAt($str0, $str1) {
	if (strpos($str0, $str1) === false) {
		return false;
	}
	$arr = ["", ""];
	$s0 = false;
	for ($i = 0; $i < strlen($str0); $i++) {
		$index = 0;
		if ($s0) {
			$index = 1;
		}
		if ($str0[$i] === $str1 && !$s1) {
			$s0 = true;
		} else {
			$arr[$index] .= $str0[$i];
		}
	}
	return $arr;
}
function packagesToArray($packages) {
	$arr = explode("\n\n", $packages);
	for ($i = 0; $i < count($arr); $i++) {
		if ($arr[$i] === "") {
			unset($arr[$i]);
		}
	}
	return $arr;
}
function splitPackages($packages, $__url) {
	$fullURL = false;
	if (isset($_GET["packagesFullURL"])) {
		$fullURL = true;
	}
	$prev = "";
	for ($i = 0; $i < count($packages); $i++) {
		$a = explode("\n", $packages[$i]);
		$arr = [];
		for ($ii = 0; $ii < count($a); $ii++) {
			$str = $a[$ii];
			$arr_ = splitAt($str, ":");
			if ($arr_ == false && $prev != "" && $ii != 0 && $str !== "") {
				$arr[$prev] .= "\n" . $str;
			}
			if ($arr_ !== false) {
				$prev = $arr_[0];
				$keyName = strtolower(trim($arr_[0]));
				$keyValue = trim($arr_[1]);
				if ($fullURL && $keyName == "filename" && strpos($keyValue, "://") === false) {
					$keyValue = $__url . "/" . $keyValue;
				} else if ($fullURL && $keyName == "filename" && strpos($keyValue, "//") === 0) {
					$keyValue = "http:" . $keyValue;
				}
				$arr[$keyName] = $keyValue;
			}
		}
		$packages[$i] = $arr;
	}
	return $packages;
}
function splitAt_a($str0, $str1) {
	if (strpos($str0, $str1) === false) {
		return false;
	}
	$arr = [];
	$index = 0;
	for ($i = 0; $i < strlen($str0); $i++) {
		if ($str0[$i] === $str1) {
			$index++;
		} else {
			$arr[$index] .= $str0[$i];
		}
	}
	return $arr;
}
function splitReleaseToArray($release) {
	$arr = explode("\n", $release);
	/*for ($i = 0; $i < count($arr); $i++) {
		if ($arr[$i] === "") {
			unset($arr[$i]);
		}
	}*/
	return $arr;
}
function splitRelease($release) {
	$release_ = [];
	$prev = "";
	for ($i = 0; $i < count($release); $i++) {
		$add = $release[$i];
		$str = $add . "";
		$arr_ = splitAt($str, ":");
		if ($arr_ !== false) {
			$release_[trim($arr_[0])] = trim($arr_[1]);
			$prev = trim($arr_[0]);
		} else if ($str !== "") {
			$release_[$prev] .= "\n" . $str;
			$release_[$prev] = trim($release_[$prev]);
		}
	}
	foreach ($release_ as &$str) {
		if (strpos($str, "\n") !== false) {
			$str = splitAt_a($str, "\n");
			for ($i = 0; $i < count($str); $i++) {
				$str[$i] = trim($str[$i]);
			}
		}
	}
	return $release_;
}
$finalJSON = [];
function vp($str) {
	return $str !== false && (endsWith(strtolower($str), strtolower("/Packages")) || endsWith(strtolower($str), strtolower("/Packages.gz")) || endsWith(strtolower($str), strtolower("/Packages.bz2")));
}
function vr($str) {
	return $str !== false && endsWith(strtolower($str), strtolower("/Release"));
}
function packages($__packages) {
	$url = $__packages;
	$find = false;
	if (isset($_GET["packagesFind"])) {
		$find = true;
	}
	$lowerurl = strtolower($url);
	if (strpos($url, "..") !== false) {
		return "[ERROR] Possible path traversal attack detected.";
	}
	if (strpos($url, "/") == 0) {
		return "[ERROR] Possible path traversal attack detected.";
	}
	if (strpos($url, $_SERVER["HTTP_HOST"]) !== false) {
		return "[ERROR] Possible path traversal attack detected.";
	}
	if (strpos($url, $_SERVER["SERVER_ADDR"]) !== false) {
		return "[ERROR] Possible path traversal attack detected.";
	}
	if ($find) {
		$found = false;
		$f0 = "/Packages";
		$f1 = "/Packages.gz";
		$f2 = "/Packages.bz2";
		$f3 = "/dists/stable/main/binary-iphoneos-arm/Packages";
		$f4 = "/dists/stable/main/binary-iphoneos-arm/Packages.gz";
		$f5 = "/dists/stable/main/binary-iphoneos-arm/Packages.bz2";
		$e0 = "";
		$e1 = "";
		$e2 = "";
		$e3 = "";
		$e4 = "";
		$e5 = "";
		$e6 = "";
		if (vp($lowerurl)) {
			$str = $url;
			$e = $e0;

			$e = exists($str);
			if (vp($e)) {
				$found = true;
			}
		}
		if (!$found) {
			$str = $url . $f0;
			$e = $e1;

			$e = exists($str);
			if (vp($e)) {
				$found = true;
				$url = $str;
				$lowerurl = strtolower($url);
			}
		}
		if (!$found) {
			$str = $url . $f1;
			$e = $e2;

			$e = exists($str);
			if (vp($e)) {
				$found = true;
				$url = $str;
				$lowerurl = strtolower($url);
			}
		}
		if (!$found) {
			$str = $url . $f2;
			$e = $e3;

			$e = exists($str);
			if (vp($e)) {
				$found = true;
				$url = $str;
				$lowerurl = strtolower($url);
			}
		}
		if (!$found) {
			$str = $url . $f3;
			$e = $e4;

			$e = exists($str);
			if (vp($e)) {
				$found = true;
				$url = $str;
				$lowerurl = strtolower($url);
			}
		}
		if (!$found) {
			$str = $url . $f4;
			$e = $e5;

			$e = exists($str);
			if (vp($e)) {
				$found = true;
				$url = $str;
				$lowerurl = strtolower($url);
			}
		}
		if (!$found) {
			$str = $url . $f5;
			$e = $e6;

			$e = exists($str);
			if (vp($e)) {
				$found = true;
				$url = $str;
				$lowerurl = strtolower($url);
			}
		}
		if (!$found) {
			return "[ERROR] Unable to find the Packages file.";
		}
	} else if (!exists($url)) {
		return "[ERROR] Unable to find the Packages file.";
	}
	$file = "";
	if (endsWith($lowerurl, "/packages.bz2")) {
		$random = rand();
		$content = @get_file_contents($url);
		if ($content === false) {
			return "[ERROR] Unable to find the Packages file.";
		}
		file_put_contents("/tmp/$random", $content);
		$bz2 = bzopen("/tmp/$random", "r");
		unlink("/tmp/$random");
		while (!feof($bz2)) {
	  		$file .= bzread($bz2, 4096);
		}
		bzclose($bz2);
	} else if (endsWith($lowerurl, "/packages.gz")) {
		$random = rand();
		$content = @get_file_contents($url);
		if ($content === false) {
			return "[ERROR] Unable to find the Packages file.";
		}
		file_put_contents("/tmp/$random", $content);
		$gz = gzopen("/tmp/$random", "r");
		unlink("/tmp/$random");
		while (!feof($gz)) {
  			$file .= gzread($gz, 4096);
		}
		gzclose($gz);
	} else if (endsWith($lowerurl, "/packages")) {
		$content = @get_file_contents($url);
		if ($content === false) {
			return "[ERROR] Unable to find the Packages file.";
		}
		$file = $content;
	} else if (!$find) {
		return "[ERROR] The URL doesn't look like it points a Packages file.";
	}
	$file = preg_replace('/[\x00-\x08\x10\x0B\x0C\x0E-\x19\x7F]|[\x00-\x7F][\x80-\xBF]+|([\xC0\xC1]|[\xF0-\xFF])[\x80-\xBF]*|[\xC2-\xDF]((?![\x80-\xBF])|[\x80-\xBF]{2,})|[\xE0-\xEF](([\x80-\xBF](?![\x80-\xBF]))|(?![\x80-\xBF]{2})|[\x80-\xBF]{3,})/S', '?', $file);
	$file = preg_replace('/\xE0[\x80-\x9F][\x80-\xBF]|\xED[\xA0-\xBF][\x80-\xBF]/S','?', $file);
	$packages = packagesToArray($file);
	$packages = splitPackages($packages, dirname($url));
	return $packages;
}
function release($__release) {
	$url = $__release;
	$find = false;
	if (isset($_GET["releaseFind"])) {
		$find = true;
	}
	$lowerurl = strtolower($url);
	if (strpos($url, "..") !== false) {
		return "[ERROR] Possible path traversal attack detected.";
	}
	if (strpos($url, "/") == 0) {
		return "[ERROR] Possible path traversal attack detected.";
	}
	if (strpos($url, $_SERVER["HTTP_HOST"]) !== false) {
		return "[ERROR] Possible path traversal attack detected.";
	}
	if (strpos($lowerurl, strtolower($_SERVER["SERVER_ADDR"])) !== false) {
		return "[ERROR] Possible path traversal attack detected.";
	}
	if ($find) {
		$found = false;
		$f0 = "/Release";
		$f1 = "/dists/stable/Release";
		$e0 = "";
		$e1 = "";
		$e2 = "";
		if (vr($lowerurl)) {
			$str = $url;
			$e = $e0;

			$e = exists($str);
			if (vr($e)) {
				$found = true;
			}
		}
		if (!$found) {
			$str = $url . $f0;
			$e = $e1;

			$e = exists($str);
			if (vr($e)) {
				$found = true;
				$url = $str;
				$lowerurl = strtolower($url);
			}
		}
		if (!$found) {
			$str = $url . $f1;
			$e = $e2;

			$e = exists($str);
			if (vr($e)) {
				$found = true;
				$url = $str;
				$lowerurl = strtolower($url);
			}
		}
		if (!$found) {
			return "[ERROR] Unable to find the Release file.";
		}
	} else if (!exists($url)) {
		return "[ERROR] Unable to find the Release file.";
	}
	$file = "";
	if (endsWith($lowerurl, "/release")) {
		$content = @get_file_contents($url);
		if ($content === false) {
			return "[ERROR] Unable to find the Release file.";
		}
		$file = $content;
	} else if (!$find) {
		return "[ERROR] The URL doesn't look like it points a Release file.";
	}
	$file = preg_replace('/[\x00-\x08\x10\x0B\x0C\x0E-\x19\x7F]|[\x00-\x7F][\x80-\xBF]+|([\xC0\xC1]|[\xF0-\xFF])[\x80-\xBF]*|[\xC2-\xDF]((?![\x80-\xBF])|[\x80-\xBF]{2,})|[\xE0-\xEF](([\x80-\xBF](?![\x80-\xBF]))|(?![\x80-\xBF]{2})|[\x80-\xBF]{3,})/S', '?', $file);
	$file = preg_replace('/\xE0[\x80-\x9F][\x80-\xBF]|\xED[\xA0-\xBF][\x80-\xBF]/S','?', $file);
	$release = splitReleaseToArray($file);
	$release = splitRelease($release);
	return $release;
}
$pkgarr = [];
if (isset($_GET["packages"])) {
	if (gettype($_GET["packages"]) == "string") {
		$_p = [];
		$p = packages($_GET["packages"]);
		if (gettype($p) != "string") {
			$_p[0] = $p;
		} else {
			die($p);
		}
		$finalJSON["Packages"] = $_p;
		$pkgarr = $_p;
	} else {
		$_p = [];
		$cache = [];
		for ($i = 0; $i < count($_GET["packages"]); $i++) {
			$p = "";
			if (isset($cache[$_GET["packages"][$i]])) {
				$p = $_p[$cache[$_GET["packages"][$i]]];
			} else {
				$p = packages($_GET["packages"][$i]);
			}
			if (gettype($p) != "string") {
				$_p[count($_p)] = $p;
				$cache[$_GET["packages"][$i]] = $i;
			} else {
				die($p);
			}
		}
		$finalJSON["Packages"] = $_p;
		$pkgarr = $_p;
	}
}
if (isset($_GET["release"])) {
	if (gettype($_GET["release"]) == "string") {
		$_r = [];
		$r = release($_GET["release"]);
		if (gettype($r) != "string") {
			$_r[0] = $r;
			$finalJSON["Release"] = $_r;
		} else {
			die($r);
		}
	} else {
		$_r = [];
		$cache = [];
		for ($i = 0; $i < count($_GET["release"]); $i++) {
			$r = "";
			if (isset($cache[$_GET["release"][$i]])) {
				$r = $_r[$cache[$_GET["release"][$i]]];
			} else {
				$r = release($_GET["release"][$i]);
			}
			if (gettype($r) != "string") {
				$_r[count($_r)] = $r;
				$cache[$_GET["Release"][$i]] = $i;
			} else {
				die($r);
			}
		}
		$finalJSON["Release"] = $_r;
	}
}
$search = [];
if (isset($_GET["search"]) && isset($_GET["packages"])) {
	$searchArr = [];
	$searchFor = "Name";
	if (isset($_GET["searchFor"])) {
		$searchFor = $_GET["searchFor"];
	}
	$searchFor = strtolower($searchFor);
	if (gettype($_GET["search"]) == "string") {
		for ($i = 0; $i < count($pkgarr); $i++) {
			if (isset($pkgarr[$i])) {
				$p = $pkgarr[$i];
				for ($ii = 0; $ii < count($p); $ii++) {
					if (isset($p[$ii])) {
						$pk = $p[$ii];
						if (isset($pk[$searchFor])) {
							if (startsWith(strtolower($pk[$searchFor]), strtolower($_GET["search"]))) {
    							if (!in_array($pk, $search)) {
    								$searchArr[$_GET["search"]][count($searchArr[$_GET["search"]])] = $pk;
    							}
    						}
    					}
    				}
				}
			}
		}
		$search = $searchArr;
		$finalJSON["search"] = $search;
	} else {
		for ($i = 0; $i < count($pkgarr); $i++) {
			if (isset($pkgarr[$i])) {
				$p = $pkgarr[$i];
				for ($ii = 0; $ii < count($p); $ii++) {
					if (isset($p[$ii])) {
						$pk = $p[$ii];
						for ($iii = 0; $iii < count($_GET["search"]); $iii++) {
							if (isset($pk[$searchFor])) {
								if (startsWith(strtolower($pk[$searchFor]), strtolower($_GET["search"][$iii]))) {
    								if (!in_array($pk, $search)) {
    									$searchArr[$_GET["search"][$iii]][count($searchArr[$_GET["search"][$iii]])] = $pk;
    								}
    							}
    						}
						}
					}
				}
			}
		}
		$search = $searchArr;
		$finalJSON["search"] = $search;
	}
}
header("Content-Type: text/json");
$finalJSON = json_encode($finalJSON, JSON_PRETTY_PRINT);
echo $finalJSON;
?>
