<?php

$key = 'e1b105b924f91990bc415444fe7ca348';
$cityId = 3100796;

$url = "http://api.openweathermap.org/data/2.5/weather?id=" . $cityId . "&lang=pl&units=metric&APPID=353e958b71e5f8b5e61e57ddcafb983c";
$contents = file_get_contents($url);

$clima=json_decode($contents);

$temp_max=$clima->main->temp_max;
$temp_min=$clima->main->temp_min;
$icon=$clima->weather[0]->icon.".png";
$today = date("F j, Y, G:i a");
$cityname = $clima->name;


echo $cityname .  "<br>";
echo  $today . "<br>";
echo "Temp Max: " . $temp_max ."&deg;C<br>";
echo "Temp Min: " . $temp_min ."&deg;C<br>";
echo "<img src='http://openweathermap.org/img/w/" . $icon ."'/ >";