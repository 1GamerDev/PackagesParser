<?php
//  PackagesParser.php
//  Written by 1GamerDev in 2018
//  This script parses both "Packages" and "Release" files.
//  Beta 1
header("Content-Type: text/text");
function startsWith($haystack, $needle) {
    return (substr($haystack, 0, strlen($needle)) === $needle);
}
function endsWith($haystack, $needle) {
    return strlen($needle) === 0 || (substr($haystack, - strlen($needle)) === $needle);
}
function exists($url) {
	$ch = curl_init($url);
	curl_setopt($ch, CURLOPT_NOBODY, true);
	curl_exec($ch);
	$retcode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
	curl_close($ch);
	if ($retcode >= 400) {
		return false;
	}
	return true;
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
function splitPackages($packages) {
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
				$arr[trim($arr_[0])] = trim($arr_[1]);
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
		}
	}
	return $release_;
}
$finalJSON = [];
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
		$_url = false;
		if (endsWith($lowerurl, "/packages.bz2")) {
			$_url = true;
		} else if (endsWith($lowerurl, "/packages.gz")) {
			$_url = true;
		} else if (endsWith($lowerurl, "/packages")) {
			$_url = true;
		}
		if (!$_url) {
			$_url = exists($url . "/Packages");
		}
		if (!$_url) {
			$_url = exists($url . "/Packages.bz2");
		}
		if (!$_url) {
			$_url = exists($url . "/Packages.gz");
		}
		if (!$_url) {
			$_url = exists($url . "/dists/stable/main/binary-iphoneos-arm/Packages");
		}
		if (!$_url) {
			$_url = exists($url . "/dists/stable/main/binary-iphoneos-arm/Packages.bz2");
		}
		if (!$_url) {
			$_url = exists($url . "/dists/stable/main/binary-iphoneos-arm/Packages.gz");
		}
		if ($_url) {
			$url = $_url;
		} else {
			return "[ERROR] Unable to find the Packages file.";
		}
	} else {
		if (!exists($url)) {
			return "[ERROR] Unable to find the Packages file.";
		}
	}
	$file = "";
	if (endsWith($lowerurl, "/packages.bz2")) {
		$random = rand();
		$content = @file_get_contents($url);
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
		$content = @file_get_contents($url);
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
		$content = @file_get_contents($url);
		if ($content === false) {
			return "[ERROR] Unable to find the Packages file.";
		}
		$file = $content;
	} else {
		return "[ERROR] The URL doesn't look like it points a Packages file.";
	}
	$packages = packagesToArray($file);
	$packages = splitPackages($packages);
	return $packages;
}
function release($__release) {
	$url = $__release;
	$find = false;
	if (isset($_GET["releaseFind"])) {
		$find = true;
	}
	$lowerurl = strtolower($url);
	echo $url;
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
		$_url = false;
		if (endsWith($lowerurl, "/release")) {
			$_url = true;
		}
		if (!$_url) {
			$_url = exists($url . "/Release");
		}
		if ($_url) {
			$url = $_url;
		} else {
			return "[ERROR] Unable to find the Release file.";
		}
	} else {
		if (!exists($url)) {
			return "[ERROR] Unable to find the Release file.";
		}
	}
	$file = "";
	if (endsWith($lowerurl, "/release")) {
		$content = @file_get_contents($url);
		if ($content === false) {
			return "[ERROR] Unable to find the Packages file.";
		}
		$file = $content;
	} else {
		return "[ERROR] The URL doesn't look like it points a Release file.";
	}
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
	$searchFor = "Name";
	if (isset($_GET["searchFor"])) {
		$searchFor = $_GET["searchFor"];
	}
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
    								$search[count($search)] = $pk;
    							}
    						}
    					}
    				}
				}
			}
		}
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
    									$search[count($search)] = $pk;
    								}
    							}
    						}
						}
					}
				}
			}
		}
		$finalJSON["search"] = $search;
	}
}
header("Content-Type: text/json");
$finalJSON = json_encode($finalJSON, JSON_PRETTY_PRINT);
echo $finalJSON;
?>
