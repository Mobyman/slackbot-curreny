<?php

const USD = 9;
const EUR = 10;

const SERVER = "http://www.cbr.ru/scripts/XML_daily.asp?date_req=";

$yesterday = date('d/m/Y', strtotime('-1 day'));
$today = date('d/m/Y');

function getValues($date) {

    $url = SERVER. $date;
    $data = file_get_contents($url);

    $xml = new SimpleXMLElement($data);
    if($xml) {
        $result = [
            'usd' => (float)str_replace(',', '.', $xml->Valute[USD]->Value),
            'eur' => (float)str_replace(',', '.', $xml->Valute[EUR]->Value)
        ];

        return $result;

    } else {
        return null;
    }

}

$data['yesterday'] = getValues($yesterday);
$data['today']     = getValues($today);

$data['diff']['usd'] = $data['today']['usd'] - $data['yesterday']['usd'];
$data['diff']['eur'] = $data['today']['eur'] - $data['yesterday']['eur'];


$output = [];

$output[] = 'USD: ' . $data['today']['usd'] . '  ' . ($data['diff']['usd'] > 0 ? '+' : '-') . $data['diff']['usd'];
$output[] = 'EUR: ' . $data['today']['eur'] . '  ' . ($data['diff']['eur'] > 0 ? '+' : '-') . $data['diff']['eur'];

$image = new Imagick();
$draw = new ImagickDraw();
$pixel = new ImagickPixel( 'transparent' );

/* Новое изображение */
$image->newImage(170, 45, $pixel);

/* Черный текст */
$draw->setFillColor('black');

/* Настройки шрифта */
$draw->setFont( __DIR__ . '/fonts/Aller_Bd.ttf');
$draw->setFontSize( 14 );

/* Создаем текст */
$image->annotateImage($draw, 10, 15, 0, $output[0]);
$image->annotateImage($draw, 10, 35, 0, $output[1]);
$draw->setFillColor( 'red' );

$image->setImageFormat('png');

header('Content-type: image/png');
file_put_contents( __DIR__ . '/currency.png', $image);
