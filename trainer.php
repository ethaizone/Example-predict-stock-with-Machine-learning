<?php

include 'vendor/autoload.php';

// This is stock data - ADVANC in Thailand
// This data have 6 month old data.
$data = json_decode(file_get_contents('data.json'), true);

use Phpml\Regression\SVR;
use Phpml\SupportVectorMachine\Kernel;

// We train AI with SVR
$samples = [];
$targets = [];


$initDate = new DateTime('04/01/2017');
$today = new DateTime();

$fields = ['open', 'high', 'low', 'close', 'volumn'];
$models = [];

foreach($fields as $field) {
    foreach ($data as $row) {
        $datetime2 = new DateTime($row['date']);
        $samples[] = [intval($initDate->diff($datetime2)->format('%a'))];
        $targets[] = floatval($row[$field]);
    }

    $regression = new SVR(Kernel::LINEAR);
    $regression->train($samples, $targets);

    $models[$field] = $regression;
}

// Note: You can save modal with ModelManager

//Try predict close value in next ten day.
// $datetime2 = clone $today;
// for ($i=0; $i < 10; $i++) {
//     echo $datetime2->format('m/d/Y').": ";
//     echo $regression->predict([intval($initDate->diff($datetime2)->format('%a'))]);
//     echo "\n";
//     $datetime2->add(new DateInterval('P1D'));
// }

// Create predict.csv
$txt = "";
foreach ($data as $row) {
    $datetime2 = new DateTime($row['date']);
    $txt .= $datetime2->format('j-M-y')
        . "," . $row['open']
        . "," . $row['high']
        . "," . $row['low']
        . "," . $row['close']
        . "," . $row['volumn']
        . "\n";
}

$datetime2 = clone $today;
for ($i=0; $i < 10; $i++) {
    $line = $datetime2->format('j-M-y');
    foreach($fields as $field) {
        $data = $models[$field]->predict([intval($initDate->diff($datetime2)->format('%a'))]);
        $line .= "," . $data;
    }
    $txt = $line . "\n" . $txt;
    $datetime2->add(new DateInterval('P1D'));
}

$txt = "Date,Open,High,Low,Close,Volume\n" . $txt;

file_put_contents('predict.csv', $txt);