<!DOCTYPE html>
<head>
	<title>SeithiThal</title>
	<link rel="icon" type="image/ico" href="favicon.ico">
	<link rel="stylesheet" type="text/css" href="index.css">
	<link href="http://fonts.googleapis.com/icon?family=Material+Icons" rel="stylesheet"> 
    <link type="text/css" rel="stylesheet" href="materialize.min.css"  media="screen,projection"/>
    <meta name="viewport" content="width=device-width, initial-scale=1.0"/>
</head>
<body>
	      <div class="row">
        <div class="col s12 m7">
          <div class="card">
            <div class="card-image">
              <img src="images/sample-1.jpg">
              <span class="card-title">Card Title</span>
            </div>
            <div class="card-content">
              <p>I am a very simple card. I am good at containing small bits of information.
              I am convenient because I require little markup to use effectively.</p>
            </div>
            <div class="card-action">
              <a href="#">This is a link</a>
            </div>
          </div>
        </div>
      </div>
            
<?php
ini_set('display_errors', 1);
require_once('TwitterAPIExchange.php');
require_once('TextRazor.php');

$settings = array(
    'oauth_access_token' => "701172163357716480-qW6xBIZmaBoDMU1zHwE8qkYLZvR12FW",
    'oauth_access_token_secret' => "PAn2n1Yp85SEqiDXq4FD719zLlv56MBkNFSWf3CvqwQbc",
    'consumer_key' => "9euPRfBh3i4qamZkTA7WsJI28",
    'consumer_secret' => "NZsY3SS3b2u74AT7wKmkvS97aEe9VZRktIRhenBWkfjc0IrPeM"
);

$url = 'https://api.twitter.com/1.1/statuses/user_timeline.json';
$getfield = '?screen_name=abhijit_t9hacks';
$requestMethod = 'GET';

$twitter = new TwitterAPIExchange($settings);
$response = $twitter->setGetfield($getfield)
    ->buildOauth($url, $requestMethod)
    ->performRequest();

$tweets_data = json_decode($response);

foreach($tweets_data as $tweet){
		$text = $tweet->text;
		$textrazor = new TextRazor('614173a490965b6082469fff65a1e180c0b563c60a080ee2109a2ac3');

		$query_store = array();
		$textrazor->addExtractor('entities');

		$response = $textrazor->analyze($text);
		if (isset($response['response']['entities'])) {
		    foreach ($response['response']['entities'] as $entity) {
		        array_push($query_store,$entity['entityId']);
		        print(PHP_EOL);
		    }
		}
		else{
			$textrazor->addExtractor('phrases');
			$textrazor->addExtractor('words');
			$word_positions = array();
			$response = $textrazor->analyze($text);
			//var_dump($response);
			$counter = 0; 
			if (isset($response['response']['nounPhrases'])) {
			    foreach ($response['response']['nounPhrases'] as $phrase) {
			        foreach($phrase['wordPositions'] as $position){
			        	array_push($word_positions,$position);
			        }
			        print(PHP_EOL);
				    foreach($word_positions as $position){
				    	foreach($response['response']['sentences'][$counter]['words'] as $word){
				    		if($word['position'] == (int)$position ){
				    			array_push($query_store,$word['token']);
				    		}
				    	}
				    }
				    $word_positions = array();
				    $counter += 1;
			    }
			    
			}	
		}

		$query = '';
		$cnt = 1;
		foreach($query_store as $item){
			if( $cnt == sizeof($query_store)){
				$query .= $item;
			}
			else{
				$query .= $item." ";
			}
			$cnt += 1;
		}
		echo $query.'<br/>';
		$query = str_replace(' ', '+',$query);
		$curl = curl_init();
		$curl_url = 'http://content.guardianapis.com/search?q='.$query.'&api-key=e9419ba7-4eee-4859-82fc-3313ad8049a0';
		curl_setopt_array($curl, array(
		    CURLOPT_RETURNTRANSFER => 1,
		    CURLOPT_URL => $curl_url,
		    CURLOPT_USERAGENT => 'Guardian Co UK CuRL request'
		));

		$response = curl_exec($curl);
		$data = json_decode($response);
		$i = 1;
		foreach($data->response->results as $doc){
			if ($i > 2){
				break;
			} 
			echo "<a href='".$doc->webUrl."'>".$i.". ".$doc->webTitle."</a><br/>";
			$i += 1;
		}

		curl_close($curl);
		$query =str_replace(' ','+', $query);

		$curl = curl_init();
		$curl_url = 'http://api.nytimes.com/svc/search/v2/articlesearch.json?q='.$query.'&sort=newest&api-key=e78fc55203c3a90f51121f7d8b9e7c4a:1:74485816';
		curl_setopt_array($curl, array(
		    CURLOPT_RETURNTRANSFER => 1,
		    CURLOPT_URL => $curl_url,
		    CURLOPT_USERAGENT => 'New York Times CuRL request'
		));

		$response = curl_exec($curl);
		$data = json_decode($response);
		foreach($data->response->docs as $doc){
			if ($i > 3) break;
			echo "<a href='".$doc->web_url."'>".$i.". ".$doc->snippet."</a><br/>";
			$i += 1;
		}

		curl_close($curl);
}

?>
<script type="text/javascript" src="https://code.jquery.com/jquery-2.1.1.min.js"></script>
<script type="text/javascript" src="js/materialize.min.js"></script>
</body>
</html>
