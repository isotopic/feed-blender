<?php
require 'FeedBlender.php';
$feed_blender = new FeedBlender(
	array(
		'client_id'=>'646582008814751',
		'app_secret'=>'f41d88311674dff75df1b5113d587b0a',
		'facebook_username'=>'wired',
		'instagram_username'=>'arduinoorg'
	)
);
$response = json_decode( $feed_blender->getFeed() );
?>
<!doctype html>
<html>
    <head>
        <meta charset="utf-8">
        <meta http-equiv="X-UA-Compatible" content="IE=edge,chrome=1">
        <title>FeedBlender Example</title>
        <meta name="description" content="">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <link rel="stylesheet" href="//maxcdn.bootstrapcdn.com/bootstrap/3.3.4/css/bootstrap.min.css">
        <link href="https://file.myfontastic.com/n6vo44Re5QaWo8oCKShBs7/icons.css" rel="stylesheet">
        <link rel='stylesheet' id='prism-css'  href='https://cdnjs.cloudflare.com/ajax/libs/prism/0.0.1/prism.min.css' type='text/css' media='all' />
        <script src="https://cdnjs.cloudflare.com/ajax/libs/prism/0.0.1/prism.min.js"></script>
    </head>

    <body>


    <div class="container">
        <div class="row">


            <div class="col-sm-6 col-sm-offset-3 text-center"> 

                <h2>FeedBlender</h2>
                <p class="lead">The FeedBlender class generates a cached json feed containing timeline posts from Facebook and Instagram.</p>

                <p class="lead"> <a href="https://github.com/isotopic/feed-blender" style="color:#333;text-decoration:none"> <span class="socicon-github"></a> 

<a href="http://www.isotopic.com.br" target="_blank">
        <svg style="opacity:1" version="1.1" id="logo_isotopic_svg" xmlns="http://www.w3.org/2000/svg" xmlns:xlink="http://www.w3.org/1999/xlink" x="0px" y="0px" width="35px" viewBox="0 0 200 135" xml:space="preserve">
        <path fill-rule="evenodd" clip-rule="evenodd" fill="#333333" d="M186.7,33.05c0,0.034,0.066,0.067,0.2,0.1
          c1.8,1,3.316,2.117,4.55,3.35c6.666,5.033,6.666,12.65,0,22.85c-7.8,11.4-16.434,20.766-25.9,28.1c-0.533,1.1-1.1,2.25-1.7,3.45
          c-3.6,6.933-7.783,13.616-12.55,20.05c-2.399,3.267-4.983,6.467-7.75,9.6c-0.5,0.566-1,1.117-1.5,1.65
          c-8.866,9.8-21.2,11.35-37,4.649c-4.166-1.8-8.283-3.899-12.35-6.3c-4.6-2.8-9.117-6-13.55-9.6c-2.867-2.334-5.7-4.816-8.5-7.45
          c-1.533,3.3-3.383,5.783-5.55,7.45c-1.333,1.033-2.767,1.783-4.3,2.25c-0.033,0-0.1,0.017-0.2,0.05c-4.767,1.5-7.917,1.25-9.45-0.75
          c-2.667,0.767-5.35,1.066-8.05,0.9c-2.2-0.101-3.733-0.7-4.6-1.801c-0.267-0.199-0.5-0.416-0.7-0.649h-0.05
          c-2.533,0.1-4.983-0.267-7.35-1.101c-0.433-0.133-0.833-0.316-1.2-0.55c-2.2-1.366-3.567-2.833-4.1-4.399
          c-4.667-0.601-8.55-1.717-11.65-3.351c-8.534-5.6-11.833-11.166-9.9-16.7c0.367-1.133,1.05-2.166,2.05-3.1
          c1.267-1.8,4.417-2.866,9.45-3.2c-0.2-0.566-0.3-1.133-0.3-1.7c-0.667-2.733,0.45-5.25,3.35-7.55c0.733-0.666,1.767-1.283,3.1-1.85
          c-0.434-2,0.417-3.983,2.55-5.95c0.8-0.967,2.45-1.983,4.95-3.05c2.533-1.067,5.033-1.883,7.5-2.45
          c-0.467-8.967,1.6-16.483,6.2-22.55c4.667-6.367,13.817-12.483,27.45-18.35c13.6-5.934,28.117-9.733,43.55-11.4
          c15.466-1.633,25.733-2.1,30.8-1.4c17.733,1.6,27.899,10.067,30.5,25.4C178.634,29.233,182.634,31.017,186.7,33.05z M187.95,44.15
          c-0.601-2.333-2.367-4.316-5.3-5.95c-13.334-6.767-26.284-10.65-38.851-11.65c-1.967,0.333-2.684,1.35-2.149,3.05
          c3.433,7.933,6.267,16.133,8.5,24.6c2.3,8.4,3.983,16.883,5.05,25.45c0.333,1.866,1.416,2.416,3.25,1.649
          c10.399-7.166,19.7-17.017,27.899-29.55C188.05,48.917,188.583,46.383,187.95,44.15z M131.55,27.5
          c-15.533,0.667-30.5,5.917-44.9,15.75c-1.9,1.167-2.85,2.933-2.85,5.3c-0.2,7.9,0.717,15.583,2.75,23.05
          c2.067,7.334,5.15,14.483,9.25,21.45c1.1,2,2.8,3.084,5.101,3.25c17.267,1.3,32.85-1.649,46.75-8.85
          c1.366-0.9,1.933-2.033,1.699-3.4c-0.933-9.434-2.649-18.733-5.149-27.9c-2.434-9.2-5.584-18.117-9.45-26.75
          C134.15,28.133,133.083,27.5,131.55,27.5z M116.15,21.15c-6.267-2.467-12.017-3.683-17.25-3.65c-1.566,0-2.733,0.333-3.5,1
          c-5.467,3.567-9.566,7.333-12.3,11.3c-0.4,1.067-0.15,1.917,0.75,2.55l3.9,2.9c1,0.833,2,1,3,0.5c7.6-4.9,16.316-8.733,26.15-11.5
          C118.8,23.417,118.55,22.383,116.15,21.15z M83.85,87.15c1.033-1.334,1.25-2.717,0.65-4.15c-1.467-3.7-2.717-7.25-3.75-10.65
          c-0.8-3.466-1.483-7.183-2.05-11.149c-0.267-1.5-1.133-2.567-2.6-3.2c-1.567-0.633-3.067-1.1-4.5-1.4
          c-1.9-0.267-2.85,0.933-2.85,3.6C69.017,65.433,69.767,70.366,71,75c1.267,4.6,3.117,9.167,5.55,13.7
          c1.167,2.333,2.534,2.967,4.1,1.899C81.85,89.7,82.917,88.55,83.85,87.15z M34.75,70.9c-11.833,2.233-15.483,5.149-10.95,8.75
          c4.534,3.366,2.8,5.199-5.2,5.5c-8,0.233-10.05,2.233-6.15,6c4,3.733,10.833,5.583,20.5,5.55c-2.934,1.899-3.35,3.5-1.25,4.8
          c3.767,1.267,7.767,0.934,12-1c-2.434,2.733-2.717,4.167-0.85,4.3c4.233,0.2,8.383-1.05,12.45-3.75
          c-2.066,3.834-1.033,5.101,3.1,3.8c4.1-1.466,7.15-6.233,9.15-14.3c1.967-8.2,1.267-10-2.1-5.399
          c-3.333,4.699-6.767,7.433-10.3,8.199c-3.566,0.767-6.333,0.884-8.3,0.351c-1.9-0.566-1.167-2.05,2.2-4.45
          c3.5-2.333,3.95-3.7,1.35-4.1c-2.533-0.434-4.6-1.25-6.2-2.45c-1.633-1.167-0.083-2.65,4.65-4.45c4.633-1.833,5.667-3.366,3.1-4.6
          c-2.667-1.334-4.2-2.9-4.6-4.7c-0.333-2,2.933-2.117,9.8-0.351c6.833,1.801,8.317,1.017,4.45-2.35c-3.867-3.5-8.25-5.867-13.15-7.1
          c-4.8-1.333-6.233-0.133-4.3,3.6c-3.066-0.367-7.217,0.45-12.45,2.45C26.667,67.1,27.683,69,34.75,70.9z M129.4,110.95
          c2.566-2.2,5.1-4.917,7.6-8.15c1.566-2.066,1.333-3.033-0.7-2.899c-10,2.466-19.533,3.433-28.6,2.899
          c-1.134,0.167-1.9,0.851-2.3,2.05l-2,4.301c-0.267,0.666-0.284,1.267-0.051,1.8c0.167,0.366,0.45,0.7,0.851,1
          c4.267,2.033,9.684,3.217,16.25,3.55c1,0.267,2.184,0.017,3.55-0.75C125.8,113.717,127.6,112.45,129.4,110.95z"/>
        </svg></a>

                </p>
            </div>


            <div class="col-sm-6"> 
                <h3>Usage</h3>

                <pre><code class="language-php"><?php
$file = fopen("example.php","r");
$a = 0;
while($a<11){
  echo fgets($file)."<br>"; $a++;
}
fclose($file);
?></code></pre>

                <p>Outputs a json string like this:</p>

                <pre><code class="language-json">{
    "status":"success",
    "message":"",
    "data":[
        {
            "source":"facebook",
            "link":"http://facebook.com/ubuntulinux/posts/10153694239668592",
            "created_time":"11 Dec 2015",
            "message":"GitHub Director of Community (and former Ubuntu Community Manager) Jono Bacon explains why you should go to #UbuCon in January. We can't help but agree.",
            "image":"https://fbexternal-a.akamaihd.net/safe_image.php?d=AQBonXQFibLBUdJ7&url=http%3A%2F%2Fubucon.org%2Fmedia%2Fcms_page_media%2F1%2Fubucon-community.jpg"
        },
        {
            "source":"instagram",
            "link":"https://www.instagram.com/p/_KQgGjPMyV/",
            "created_time":"10 Dec 2015",
            "message":"The "bath" process. #arduino #arduinoorg #arduinoteam #onthego #strambino #production #pcb #board #quality #madeinitaly #withlove",
            "image":"https://scontent-mia1-1.cdninstagram.com/hphotos-xft1/t51.2885-15/s640x640/sh0.08/e35/12356440_765574983571153_147890575_n.jpg"
        },
        ...</code></pre>

                <p>If you need a php object, just decode it:</p>

                <pre><code class="language-php">$response = json_decode( $feed_blender->getFeed() );
echo $response->data[0]->message;
// Outputs "GitHub Director of Community..."</code></pre>



            <h3 class="">Note</h3>
            <p class="">The <code>client_id</code> and <code>app_secret</code> used in this example are for testing purposes and will be invalidated at any moment. 
            Your own credentials can be acquired upon registration of any app here:
            <a href="https://developers.facebook.com/quickstarts/?platform=web">https://developers.facebook.com/quickstarts/?platform=web</a></p>

            <hr><a rel="license" href="http://creativecommons.org/licenses/by/4.0/"><img alt="Creative Commons License" style="border-width:0" src="https://i.creativecommons.org/l/by/4.0/80x15.png" /></a><br /><span xmlns:dct="http://purl.org/dc/terms/" property="dct:title">FeedBlender</span> by <a xmlns:cc="http://creativecommons.org/ns#" href="http://www.isotopic.com.br" property="cc:attributionName" rel="cc:attributionURL">Guilherme Cruz</a> is licensed under a <a rel="license" href="http://creativecommons.org/licenses/by/4.0/">Creative Commons Attribution 4.0 International License</a>.

            

            </div>



            <div class="col-sm-6"> 
                <h3>Content</h3>
                <?php foreach($response->data as $item){ ?>

    		    <div class="thumbnail">
    		   		<a target="_blank" href="<?php echo $item->link; ?>">
    		    		<img src="<?php echo $item->image; ?>" class="img-responsive">
    		    	</a>
    		    	<div class="caption">
                        <h4><span class="socicon-<?php echo $item->source; ?>"></span> /<?php echo $item->username; ?></h4>
    		    		<p class="lead"><?php echo $item->message; ?></p>
    		    	</div>
    		    </div>

                <?php } ?>
            </div>



        </div><!-- end row -->
      </div><!-- end container -->


    </body>
</html>