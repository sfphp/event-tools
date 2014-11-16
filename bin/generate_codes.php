<?php

date_default_timezone_set('UTC');
define('DATA', dirname(__DIR__) . '/var');

$file = DATA . '/' . date('Y-m-d');
$move_file = $file;

while (file_exists($move_file)) {
    $move_file = DATA . '/' . date('Y-m-d') . '.' . uniqid();
}

if ($move_file != $file) {
    echo "$file exists, moving to $move_file\n";
    rename($file, $move_file);
}

if (!isset($_SERVER['argv'][1])) {
    $progname = $_SERVER['argv'][0];
    echo "$progname: expected argument for number of codes to generate.\n";
    exit(1);
}

if (isset($_SERVER['argv'][2])) {
    $length = $_SERVER['argv'][2];
} else {
    $length = 4;
}

$number_of_randoms = $_SERVER['argv'][1];

$codes = [];
$count = 0;

while ($count < $number_of_randoms) {
    $code = substr(md5(rand()), 0, $length);

    if (in_array($code, $codes)) {
        continue;
    }

    $codes[] = $code;
    $count++;
}

file_put_contents($file, implode("\n", $codes));

echo "Wrote $count codes to $file\n";