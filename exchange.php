<?php

const USD = 9;
const EUR = 10;
const SERVER = "http://www.cbr.ru/scripts/XML_daily.asp?date_req=";

$yesterday = date('d/m/Y', strtotime('-1 day'));
$today = date('d/m/Y');
$tommorow = date('d/m/Y', strtotime('+1 day'));

function getValues($date) 
{
    $url = SERVER. $date;
    $data = file_get_contents($url);
    $xml = new SimpleXMLElement($data);
 
    if($xml) {

	    if($xml["Date"][0] != $date) { return null; }
	    
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
$data['tommorow']  = getValues($tommorow);

if(!$data['tommorow']) {
	$data['diff']['usd'] = $data['today']['usd'] - $data['yesterday']['usd'];
	$data['diff']['eur'] = $data['today']['eur'] - $data['yesterday']['eur'];
} else {
	$data['diff']['usd'] = $data['tommorow']['usd'] - $data['today']['usd'];
	$data['diff']['eur'] = $data['tommorow']['eur'] - $data['today']['eur'];
}

$output = [];
$output[] = 'USD: ' . $data['today']['usd'] . '  ' . ($data['diff']['usd'] > 0 ? '+' : '-') . abs($data['diff']['usd']);
$output[] = 'EUR: ' . $data['today']['eur'] . '  ' . ($data['diff']['eur'] > 0 ? '+' : '-') . abs($data['diff']['eur']);


$image = new Imagick();
$draw = new ImagickDraw();
$pixel = new ImagickPixel( 'transparent' );
$image->newImage(170, 45, $pixel);
$draw->setFillColor('black');
$draw->setFont( __DIR__ . '/fonts/Aller_Bd.ttf');
$draw->setFontSize( 14 );
$image->annotateImage($draw, 10, 15, 0, $output[0]);
$image->annotateImage($draw, 10, 35, 0, $output[1]);
$draw->setFillColor( 'red' );
$image->setImageFormat('png');
file_put_contents(__DIR__ . '/currency.png', $image);
