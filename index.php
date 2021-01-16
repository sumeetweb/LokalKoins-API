<?php
require_once("vendor/autoload.php");
header("Content-Type: application/json");

use \BenMajor\ExchangeRatesAPI\ExchangeRatesAPI;
use \BenMajor\ExchangeRatesAPI\Response;
use \BenMajor\ExchangeRatesAPI\Exception;

$lookup = new ExchangeRatesAPI();
$rate  = $lookup->setBaseCurrency('usd')->fetch()->toJSON();
$rates = json_decode($rate, true);

$response = array();
$response['status'] = 200;
$response['message'] = null;

if(isset($_GET["currency"])){
    $currency = htmlentities($_GET["currency"]);
}
else{
    $currency = null;
}

// Check in currency
if($currency == null || !isset($rates['rates'][$currency])) {
    $response['message'] = 'Currency Value Invalid';
    $response['currencylist'] = array();
    foreach($rates['rates'] as $sym=>$val) {
        array_push($response['currencylist'], $sym);
    }
    $response['currencyCount'] = count($response['currencylist']);
    echo(json_encode($response));
    exit();
}


$client = new GuzzleHttp\Client();

//Save some exchange data to exchangeInfo.json
if(!file_exists('exchangeInfo.json')){
    $data = file_get_contents('https://api.binance.com/api/v3/exchangeInfo');
    $put = file_put_contents('exchangeInfo.json', $get);
}
$result = json_decode(file_get_contents('exchangeInfo.json'), true);

// Get price list.
$res = $client->request('GET', 'https://api.binance.com/api/v3/ticker/price');
$result2 = json_decode($res->getBody(), true);

//Most used quoteAssets in Binance
$btctousd = json_decode($client->request('GET', 'https://api.binance.com/api/v3/ticker/price?symbol=BTCUSDT')->getBody(), true);
$ethtousd = json_decode($client->request('GET', 'https://api.binance.com/api/v3/ticker/price?symbol=ETHUSDT')->getBody(), true);
$bnbtousd = json_decode($client->request('GET', 'https://api.binance.com/api/v3/ticker/price?symbol=BNBUSDT')->getBody(), true);
$paxtousd = json_decode($client->request('GET', 'https://api.binance.com/api/v3/ticker/price?symbol=PAXUSDT')->getBody(), true);
$xrptousd = json_decode($client->request('GET', 'https://api.binance.com/api/v3/ticker/price?symbol=XRPUSDT')->getBody(), true);

$pairs = array();

foreach($result['symbols'] as $symbol) {

    $multiplyby = 0;

    try 
    {
        if($symbol['quoteAsset'] == 'BTC') {
            $multiplyby = $btctousd['price'] * $rates['rates'][$currency]; 
        }
        else if($symbol['quoteAsset'] == 'ETH') {
            $multiplyby = $ethtousd['price'] * $rates['rates'][$currency];
        }        
        else if($symbol['quoteAsset'] == 'BNB') {
            $multiplyby = $bnbtousd['price'] * $rates['rates'][$currency];
        }  
        else if($symbol['quoteAsset'] == 'PAX') {
            $multiplyby = $paxtousd['price'] * $rates['rates'][$currency];
        }   
        else if($symbol['quoteAsset'] == 'XRP') {
            $multiplyby = $xrptousd['price'] * $rates['rates'][$currency];
        }                                
        else if($symbol['quoteAsset'] == 'USDT' || $symbol['quoteAsset'] == 'TUSD' || $symbol['quoteAsset'] == 'USDC' || $symbol['quoteAsset'] == 'BUSD' || $symbol['quoteAsset'] == 'USDS') {
            $multiplyby = $rates['rates'][$currency];
        }
        else {
            $multiplyby = 0;
        }
    }
    catch(Exception $e) 
    {
        error_log('QuoteAsset error...');
    }

    foreach($result2 as $res2) {
        if($symbol['symbol'] == $res2['symbol'] && $multiplyby !== 0) {
            $pairs[$symbol['baseAsset']] = array();
            $pairs[$symbol['baseAsset']]['asset'] = $symbol['baseAsset'];
            $pairs[$symbol['baseAsset']]['price'] = $multiplyby * $res2['price'];
            $multiplyby = 0;
            break;
        }
    }
}

$response['message'] = 'Success';
$response['baseCurrency'] = $currency;
$response['count'] = count($pairs);
$response['rates'] = $pairs;
echo json_encode($response);
exit();