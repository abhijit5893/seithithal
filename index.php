<?php
ini_set('display_errors', 1);
require_once('TwitterAPIExchange.php');

$settings = array(
    'oauth_access_token' => "701172163357716480-qW6xBIZmaBoDMU1zHwE8qkYLZvR12FW",
    'oauth_access_token_secret' => "PAn2n1Yp85SEqiDXq4FD719zLlv56MBkNFSWf3CvqwQbc",
    'consumer_key' => "9euPRfBh3i4qamZkTA7WsJI28",
    'consumer_secret' => "NZsY3SS3b2u74AT7wKmkvS97aEe9VZRktIRhenBWkfjc0IrPeM"
);

$url = 'https://api.twitter.com/1.1/followers/ids.json';
$getfield = '?screen_name=J7mbo';
$requestMethod = 'GET';
$twitter = new TwitterAPIExchange($settings);
echo $twitter->setGetfield($getfield)
             ->buildOauth($url, $requestMethod)
             ->performRequest();
?>