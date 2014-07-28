<?php
header("Content-Type: image/png");
require "class.lol.php";

$apiKey = ""; // You can get this from https://developer.riotgames.com/
$region = "na"; // [BR, EUNE, EUW, KR, LAN, LAS, NA, OCE, RU, TR]
$username = "Your Username";

$font = "./bebas.ttf";

$lol = new LoLSignature($apiKey, $region);
$lol->GenerateSignature($username, $font);
?>