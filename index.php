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
    try{

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
    echo "document.getElementById('user').innerHTML='".str_replace("'", "", $username)."';";
    echo "document.getElementById('head".($count +1)."').innerHTML='".str_replace("'", "", $query)."';";
    //echo $query.'<br/>';
    $query = str_replace(' ', '+',$query);
    $curl = curl_init();
    $curl_url = 'http://content.guardianapis.com/search?q='.str_replace("#", "", $query).'&api-key=e9419ba7-4eee-4859-82fc-3313ad8049a0';
    curl_setopt_array($curl, array(
        CURLOPT_RETURNTRANSFER => 1,
        CURLOPT_URL => $curl_url,
        CURLOPT_USERAGENT => 'Guardian Co UK CuRL request'
    ));

    $response = curl_exec($curl);
    $data = json_decode($response);
    $i = 1;
    if($data->response->status != "error"){
    foreach($data->response->results as $doc){
      if ($i > 1){
        break;
      } 
      echo "document.getElementById('icon".$i.$count."').innerHTML='<p>".str_replace("'", "", $doc->webTitle)."</p>';";
      echo "document.getElementById('link".$i.$count."').href='".str_replace("'", "", $doc->webUrl)."';";
      $i += 1;
    }

  }
    curl_close($curl);

    $curl = curl_init();
    $curl_url = 'http://api.nytimes.com/svc/search/v2/articlesearch.json?q='.str_replace("#", "", $query).'&sort=newest&api-key=e78fc55203c3a90f51121f7d8b9e7c4a:1:74485816';
    curl_setopt_array($curl, array(
        CURLOPT_RETURNTRANSFER => 1,
        CURLOPT_URL => $curl_url,
        CURLOPT_USERAGENT => 'New York Times CuRL request'
    ));

    $response = curl_exec($curl);
    $data = json_decode($response);
     if($data != NULL){

    foreach($data->response->docs as $doc){
      if ($i > 2) break;
      echo "document.getElementById('icon".$i.$count."').innerHTML='<p>".str_replace("'", "", $doc->snippet)."</p>';";
      echo "document.getElementById('link".$i.$count."').href='".str_replace("'", "", $doc->web_url)."';";
      $i += 1;
    }
    }
    curl_close($curl);
  
    $curl = curl_init();
    $curl_url = 'https://ajax.googleapis.com/ajax/services/search/news?v=1.0&q='.str_replace("#", "", $query);
    curl_setopt_array($curl, array(
        CURLOPT_RETURNTRANSFER => 1,
        CURLOPT_URL => $curl_url,
        CURLOPT_USERAGENT => 'Google News API'
    ));

    $response = curl_exec($curl);
    $data = json_decode($response);
    if($data != NULL){

    foreach($data->responseData->results as $doc){
      if ($i > 3){
        break;
      } 
      echo "document.getElementById('icon".$i.$count."').innerHTML='<p>".str_replace("'", "", $doc->content)."</p>';";
      echo "document.getElementById('link".$i.$count."').href='".str_replace("'", "", $doc->url)."';";
      $i += 1;
    }
    echo "document.getElementById('head".($count +1)."').innerHTML='".str_replace("'", "", $query)."';";
  }
    curl_close($curl);
    $count += 1;
  }
  }
}
  catch(Exception $e){
    echo "alert('".$e."');";
  }


    ?>
  }
  </script>
  <!-- CSS  -->
  <link href="https://fonts.googleapis.com/icon?family=Material+Icons" rel="stylesheet">
  <link href="css/materialize.css" type="text/css" rel="stylesheet" media="screen,projection"/>
  <link href="css/style.css" type="text/css" rel="stylesheet" media="screen,projection"/>
</head>
<body onload="init()" style="background-color: #a89aff;">

  <div id="index-banner" class="parallax-container">
    <div class="section no-pad-bot">
      <div class="container">
        <br><br>
        <h1 class="header center text-lighten-2" style="color: white; font-family: monospace;">SeithiThal</h1>
        <div class="row center">
          <h3 class="header col s12 light" id="user"></h5>
          <h5 class="header col s12 light" style="color: white; font-weight: bold;" id="head1">A modern day smart and intelligent personalized news provider</h5>
        </div>
        <div class="row center" id="form">
            <i class="material-icons prefix" style="position: relative; top :5px; left: 410px;">mode_edit</i>
            <input placeholder="Your Twitter UserName..." id="twitter_username" name="name" type="text" class="validate" style="width:400px; background-color: rgba(128, 128, 128, 0.52); border-radius:10px; padding-left: 10px; padding-right:10px; color: white;"><br/>
            <a href="#" id="download-button" class="btn-large waves-effect waves-light purple lighten-1" onclick="window.location.href='/index.php?name='+document.getElementById('twitter_username').value">Enter</a>
        </div>
        <br><br>

      </div>
    </div>
    <div class="parallax"><img src="/img/bg1.jpg" alt="Unsplashed background img 1" style="background-color:white;"></div>
  </div>

<br/><br/><br/>
  <div class="container" id ="container1">
    <div class="section">

      <!--   Icon Section   -->
      <div class="row" >
        <div class="col s12 m4" style=" " >
             <div class="card" style="background-color: #d3b9fc; border:5px solid #7440c7;">
            <div class="card-content" id="icon10">
            </div>
            <div class="card-action">
              <a href="#" id="link10" style="color:#7440c7">More Details...</a>
            </div>
          </div>
        </div>

        <div class="col s12 m4" style="">
   <div class="card" style="background-color: #d3b9fc; border:5px solid #7440c7;">
            <div class="card-content" id ="icon20">
            </div>
            <div class="card-action">
              <a href="#" id="link20" style="color:#7440c7">More Details...</a>
            </div>
          </div>
        </div>

        <div class="col s12 m4" style="">
   <div class="card" style="background-color: #d3b9fc; border:5px solid #7440c7;">
            <div class="card-content" id="icon30">
            </div>
            <div class="card-action">
              <a href="#" id="link30" style="color:#7440c7">More Details...</a>
            </div>
          </div>
      </div>

    </div>
  </div>


  <div class="parallax-container valign-wrapper">
    <div class="section no-pad-bot">
      <div class="container">
        <div class="row center">
          <h5 class="header col s12 light" id="head2"  style="color: white; font-weight: bold;"></h5>
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
        <div class="col s12 m4" style="">
             <div class="card" style="background-color: #d3b9fc; border:5px solid #7440c7;">
            <div class="card-content" id="icon11" style="color:black;">
            </div>
            <div class="card-action">
              <a href="#" id="link11" style="color:#7440c7">More Details...</a>
            </div>
          </div>
        </div>

        <div class="col s12 m4" style="">
   <div class="card" style="background-color: #d3b9fc; border:5px solid #7440c7;">
            <div class="card-content" id ="icon21" style="color:black;">
            </div>
            <div class="card-action">
              <a href="#" id="link21" style="color:#7440c7">More Details...</a>
            </div>
          </div>
        </div>

        <div class="col s12 m4" style="">
   <div class="card" style="background-color: #d3b9fc; border:5px solid #7440c7;">
            <div class="card-content" id="icon31" style="color:black;">
            </div>
            <div class="card-action">
              <a href="#" id="link31" style="color:#7440c7">More Details...</a>
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
        <div class="col s12 m4" style="">
             <div class="card" style="background-color: #d3b9fc;border:5px solid #7440c7;">
            <div class="card-content" id="icon12" style="color:black;">
            </div>
            <div class="card-action">
              <a href="#" id="link12" style="color:#7440c7">More Details...</a>
            </div>
          </div>
        </div>

        <div class="col s12 m4" style="">
   <div class="card" style="background-color: #d3b9fc; border:5px solid #7440c7;">
            <div class="card-content" id ="icon22" style="color:black;">
            </div>
            <div class="card-action">
              <a href="#" id="link22" style="color:#7440c7">More Details...</a>
            </div>
          </div>
        </div>

        <div class="col s12 m4" style="">
   <div class="card" style="background-color: #d3b9fc; border:5px solid #7440c7;">
            <div class="card-content" id="icon32" style="color:black;">
            </div>
            <div class="card-action">
              <a href="#" id="link32" style="color:#7440c7">More Details...</a>
            </div>
          </div>
      </div>

    </div>
  </div>

  <div class="parallax-container valign-wrapper" style="color:black;">
    <div class="section no-pad-bot">
      <div class="container">
        <div class="row center">
          <h5 class="header col s12 light" id="head3"  style="color: white; font-weight: bold;"></h5>
        </div>
      </div>
    </div>
    <div class="parallax"><img src="/img/background3.jpg" alt="Unsplashed background img 3"></div>
  </div>

  <footer class="page-footer" style="background-color: #7440c7;">
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
