<?php 
if(isset($_GET['name'])){
  $username = $_GET['name'];
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
$getfield = '?screen_name='.$username;
$requestMethod = 'GET';

$twitter = new TwitterAPIExchange($settings);
$response = $twitter->setGetfield($getfield)
    ->buildOauth($url, $requestMethod)
    ->performRequest();

$tweets_data = json_decode($response);
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
  <meta http-equiv="Content-Type" content="text/html; charset=UTF-8"/>
  <link rel="shortcut icon" type="image/ico" href="/img/favicon.ico"/>
  <meta name="viewport" content="width=device-width, initial-scale=1, maximum-scale=1.0, user-scalable=no"/>
  <title>SeithiThal</title>
  <script>
  function init(){
    document.getElementById('form').style.display='<?php if(isset($_GET['name'])){ echo 'none';} ?>';
    <?php
    if(isset($_GET['name'])){
    $count =0 ;
    foreach($tweets_data as $tweet){
      if($count > 3 ) break;
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
    //echo $query.'<br/>';
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
      echo "document.getElementById('icon".$i.$count."').innerHTML='<p>".str_replace("'", "", $doc->webTitle)."</p>';";
      echo "document.getElementById('link".$i.$count."').href='".str_replace("'", "", $doc->webUrl)."';";
      $i += 1;
    }
    echo "document.getElementById('head".($count +1)."').innerHTML='".str_replace("'", "", $query)."';";
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
      echo "document.getElementById('icon".$i.$count."').innerHTML='<p>".str_replace("'", "", $doc->snippet)."</p>';";
      echo "document.getElementById('link".$i.$count."').href='".str_replace("'", "", $doc->web_url)."';";
      $i += 1;
    }

    curl_close($curl);
    $count += 1;
  }
}

    ?>
  }
  </script>
  <!-- CSS  -->
  <link href="https://fonts.googleapis.com/icon?family=Material+Icons" rel="stylesheet">
  <link href="css/materialize.css" type="text/css" rel="stylesheet" media="screen,projection"/>
  <link href="css/style.css" type="text/css" rel="stylesheet" media="screen,projection"/>
</head>
<body onload="init()">
  <nav class="white" role="navigation">
    <div class="nav-wrapper container">
      <a id="logo-container" href="#" class="brand-logo"><img src="/img/logo.jpg" width="120" height="100"/></a>
      <ul class="right hide-on-med-and-down">
        <li><a href="#"></a></li>
      </ul>

      <ul id="nav-mobile" class="side-nav">
        <li><a href="#"></a></li>
      </ul>
      <a href="#" data-activates="nav-mobile" class="button-collapse"><i class="material-icons">menu</i></a>
    </div>
  </nav>

  <div id="index-banner" class="parallax-container">
    <div class="section no-pad-bot">
      <div class="container">
        <br><br>
        <h1 class="header center teal-text text-lighten-2">SeithiThal</h1>
        <div class="row center">
          <h5 class="header col s12 light" id="head1">A modern day smart and intelligent personalized news provider</h5>
        </div>
        <div class="row center" id="form">
            <i class="material-icons prefix">mode_edit</i>
            <input placeholder="Your Twitter UserName" id="twitter_username" name="name" type="text" class="validate" style="width:400px"><br/>
            <a href="#" id="download-button" class="btn-large waves-effect waves-light teal lighten-1" onclick="window.location.href='/index.php?name='+document.getElementById('twitter_username').value">Enter</a>
        </div>
        <br><br>

      </div>
    </div>
    <div class="parallax"><img src="/img/background1.jpg" alt="Unsplashed background img 1"></div>
  </div>

<br/><br/><br/>
  <div class="container" id ="container1">
    <div class="section">

      <!--   Icon Section   -->
      <div class="row">
        <div class="col s12 m4">
             <div class="card">
            <div class="card-content" id="icon10">
            </div>
            <div class="card-action">
              <a href="#" id="link10">More Details...</a>
            </div>
          </div>
        </div>

        <div class="col s12 m4">
   <div class="card">
            <div class="card-content" id ="icon20">
            </div>
            <div class="card-action">
              <a href="#" id="link20">More Details...</a>
            </div>
          </div>
        </div>

        <div class="col s12 m4">
   <div class="card">
            <div class="card-content" id="icon30">
            </div>
            <div class="card-action">
              <a href="#" id="link30">More Details...</a>
            </div>
          </div>
      </div>

    </div>
  </div>


  <div class="parallax-container valign-wrapper">
    <div class="section no-pad-bot">
      <div class="container">
        <div class="row center">
          <h5 class="header col s12 light" id="head2"></h5>
        </div>
      </div>
    </div>
    <div class="parallax"><img src="/img/background2.jpg" alt="Unsplashed background img 2"></div>
  </div>

  <div class="parallax-container">
    <div class="section">


<div class="row">
       <!--   Icon Section   -->
      <div class="row">
        <div class="col s12 m4">
             <div class="card">
            <div class="card-content" id="icon11" style="color:black;">
            </div>
            <div class="card-action">
              <a href="#" id="link11">More Details...</a>
            </div>
          </div>
        </div>

        <div class="col s12 m4">
   <div class="card">
            <div class="card-content" id ="icon21" style="color:black;">
            </div>
            <div class="card-action">
              <a href="#" id="link21">More Details...</a>
            </div>
          </div>
        </div>

        <div class="col s12 m4">
   <div class="card">
            <div class="card-content" id="icon31" style="color:black;">
            </div>
            <div class="card-action">
              <a href="#" id="link31">More Details...</a>
            </div>
          </div>
      </div>



    </div>
  </div>

<div class="parallax-container">
    <div class="section">


<div class="row">
        <!--   Icon Section   -->
      <div class="row">
        <div class="col s12 m4">
             <div class="card">
            <div class="card-content" id="icon12" style="color:black;">
            </div>
            <div class="card-action">
              <a href="#" id="link12">More Details...</a>
            </div>
          </div>
        </div>

        <div class="col s12 m4">
   <div class="card">
            <div class="card-content" id ="icon22" style="color:black;">
            </div>
            <div class="card-action">
              <a href="#" id="link22">More Details...</a>
            </div>
          </div>
        </div>

        <div class="col s12 m4">
   <div class="card">
            <div class="card-content" id="icon32" style="color:black;">
            </div>
            <div class="card-action">
              <a href="#" id="link32">More Details...</a>
            </div>
          </div>
      </div>

    </div>
  </div>

  <div class="parallax-container valign-wrapper" style="color:black;">
    <div class="section no-pad-bot">
      <div class="container">
        <div class="row center">
          <h5 class="header col s12 light" id="head3"></h5>
        </div>
      </div>
    </div>
    <div class="parallax"><img src="/img/background3.jpg" alt="Unsplashed background img 3"></div>
  </div>

  <footer class="page-footer teal">
    <div class="container">
      <div class="row">
        <div class="col l6 s12">
          <h5 class="white-text">Company Bio</h5>
          <p class="grey-text text-lighten-4">We as a team are working towards empowering the future with powerful products which are cognitively demanding and intelligent.</p>


        </div>
        <div class="col l3 s12">
          <h5 class="white-text">Contributors</h5>
          <ul>
            <li><a class="white-text" href="mailto:abhijit.t9hacks@gmail.com">Abhijit Suresh</a></li>
          </ul>
        </div>
        <div class="col l3 s12">
          <h5 class="white-text">Connect</h5>
          <ul>
            <li><a class="white-text" href="https://twitter.com/abhijit_t9hacks">Twitter</a></li>
          </ul>
        </div>
      </div>
    </div>
    <div class="footer-copyright">
      <div class="container">
      Made by <a class="brown-text text-lighten-3" href="http://ltslab.blogspot.com">Abhijit Suresh @Echostomp</a>
      </div>
    </div>
  </footer>


  <!--  Scripts-->
  <script src="https://code.jquery.com/jquery-2.1.1.min.js"></script>
  <script src="js/materialize.js"></script>
  <script src="js/init.js"></script>

  </body>
</html>
