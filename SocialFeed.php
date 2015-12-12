<?php
/*

~ SocialFeed ~

This class generates a cached json feed with content provided from any
(public) Facebook account and from any (public) Instagram account.
Both feeds are merged into an uniform json:

{
    "status":"success",
    "message":"",
    "data":[
        {
            "source":"facebook",
            "link":"http://facebook.com/ubuntulinux/posts/10153694239668592",
            "created_time":"10 Dec 2015",
            "message":"GitHub Director of Community (and former Ubuntu Community Manager) Jono Bacon explains why you should go to #UbuCon in January. We can't help but agree.",
            "image":"https://fbexternal-a.akamaihd.net/safe_image.php?d=AQBonXQFibLBUdJ7&url=http%3A%2F%2Fubucon.org%2Fmedia%2Fcms_page_media%2F1%2Fubucon-community.jpg"
        },
        {
            "source":"instagram",
            "link":"https://www.instagram.com/p/_KQgGjPMyV/",
            "created_time":"11 Dec 2015",
            "message":"The "bath" process. #arduino #arduinoorg #arduinoteam #onthego #strambino #production #pcb #board #quality #madeinitaly #withlove",
            "image":"https://scontent-mia1-1.cdninstagram.com/hphotos-xft1/t51.2885-15/s640x640/sh0.08/e35/12356440_765574983571153_147890575_n.jpg"
        },


A valid pair client_id/app_secret must be provided.
These credentials can be acquired upon registration of any app in this page:
https://developers.facebook.com/quickstarts/?platform=web

Guilherme <guilhermecruz@gmail.com>

*/


class SocialFeed{


	/** 
	* @var string Facebook content provider. 
	*/
	private $facebook_username = '';

	/** 
	* @var string Instagram content provider. 
	*/
	private $instagram_username = '';


	/** 
	* @var string Facebook client_id - From any app registered on Facebook Developers.
	*/
	private $client_id = '';

	/** 
	* @var string Facebook app_secret - The app secret from the previous client id.
	*/
	private $app_secret = '';

	/** 
	* @var string Facebook Token
	*/
	private $token = '';


	/**
	* @var int Minimum interval between api requests. I'd say 10 minutes (600 secs) are a pretty good bet.
	*/
	private $checking_throttle = 600;

	/** 
	* @var array Used to log the time at the last request.
	*/
	private $date = NULL;	

	/** 
	* @var array The file to log these times.
	*/
	private $log_file = "SocialFeed.log";

	/** 
	* @var array File to cache the feed's content.
	*/
	private $json_file = "SocialFeed.json";



	/** 
	* Constructor
	*/
	public function __construct($args){
		date_default_timezone_set('America/Sao_Paulo');
		setlocale(LC_ALL, 'pt_BR');
		$this->date = getdate();
		$this->client_id = $args['client_id'];
		$this->app_secret = $args['app_secret'];
		$this->facebook_username = $args['facebook_username'];
		$this->instagram_username = $args['instagram_username'];
	}



	/**
	* 
	* Just gets the merged content. 
	* Content comes from cache or from new requests,
	* depending on $this->checking_throttle.
	*
	* @return String
	*/
	public function getFeed(){

		if( $this->cacheHasExpired() ){
			// Reloads content from the APIs
			$feed = $this->loadContentFromAPIs();
			$this->writeLogAndCache($feed);
			return $feed;
		}else{
			// Returns local feed cache
			return file_get_contents($this->json_file);
		}

	}



	/**
	* 
	* Load and merge content from the apis.
	*
	* @return String
	*/
	private function loadContentFromAPIs(){

		$facebook_posts = "";
		$instagram_posts = "";
		$combined_posts = array();

		/* ------------------------- FACEBOOK POSTS --------------------------*/
		
		// Get TOKEN
		$token_request = curl_init(); 
		curl_setopt($token_request, CURLOPT_URL, 'https://graph.facebook.com/v2.5/oauth/access_token?client_id='.$this->client_id.'&client_secret='.$this->app_secret.'&grant_type=client_credentials');
		curl_setopt($token_request, CURLOPT_RETURNTRANSFER, true);
		curl_setopt($token_request, CURLOPT_HEADER, false); 
		curl_setopt($token_request, CURLOPT_VERBOSE, true);
		$token_payload=curl_exec($token_request);
		curl_close($token_request);
		$token_payload = json_decode($token_payload, false);
		$token = $token_payload->access_token;
		// Erro
		if($token == NULL){
			return json_encode(array( 'status'=>'error','message'=>'Token inválido','data'=>'' ), JSON_UNESCAPED_UNICODE);
			exit;
		}

		// Get POSTS https://developers.facebook.com/docs/graph-api/reference/v2.5/post
		$posts_request = curl_init(); 
		curl_setopt($posts_request, CURLOPT_URL, 'https://graph.facebook.com/v2.5/'.$this->facebook_username.'/posts/?fields=id,link,created_time,caption,description,message,full_picture&access_token='.$token );
		curl_setopt($posts_request, CURLOPT_RETURNTRANSFER, true);
		curl_setopt($posts_request, CURLOPT_HEADER, false); 
		curl_setopt($posts_request, CURLOPT_VERBOSE, true);
		$posts_payload = curl_exec($posts_request);
		curl_close($posts_request);
		$facebook_posts = json_decode($posts_payload, false);



		/* ------------------------- INSTAGRAM POSTS --------------------------*/

		$instagram_request = curl_init(); 
		curl_setopt($instagram_request, CURLOPT_URL, 'https://www.instagram.com/'.$this->instagram_username.'/media/');
		curl_setopt($instagram_request, CURLOPT_RETURNTRANSFER, true);
		curl_setopt($instagram_request, CURLOPT_HEADER, false); 
		curl_setopt($instagram_request, CURLOPT_VERBOSE, true);
		$instagram_posts = curl_exec($instagram_request);
		curl_close($instagram_request);
		$instagram_posts = json_decode($instagram_posts, false);



		/* ------------------------- Merge content from both sources into a single array --------------------------*/

		for( $a=0; $a < 15 && $a < max(count($facebook_posts->data),count($instagram_posts->items)); $a++){

			if($a<count($facebook_posts->data)){
				$post = $facebook_posts->data[$a];
					$short_id = explode('_',$post->id);
					$link = 'http://facebook.com/'.$this->facebook_username.'/posts/'.$short_id[1];
					$created_time = date("d M Y", strtotime($post->created_time));
					$message = $post->message;
					$image = $post->full_picture;
					array_push($combined_posts, array('source'=>'facebook', 'link'=>$link, 'created_time'=>$created_time, 'message'=>$message, 'image'=>$image));
			}

			if($a<count($instagram_posts->items)){
				$post = $instagram_posts->items[$a];
					$link = $post->link;
					$created_time = date("d M Y", $post->created_time);
					$message = $post->caption->text;
					$image = $post->images->standard_resolution->url;
					array_push($combined_posts, array('source'=>'instagram', 'link'=>$link, 'created_time'=>$created_time, 'message'=>$message, 'image'=>$image));
			}

		}

		$response = array( 'status'=>'success','message'=>'','data'=>$combined_posts );

		return json_encode( $response , JSON_UNESCAPED_UNICODE);


	}



	/**
	* Checks if already waited n seconds since the last requests
	*
	* @return boolean
	*/
	private function cacheHasExpired(){
		if(file_exists($this->log_file)){
			$logged_time = file_get_contents($this->log_file);
			//echo time()-$logged_time." seconds";
			if(time()-$logged_time > $this->checking_throttle){
				return true;
			}else{
				return false;
			}
		}
		return true;
	}



	/**
	* Updates the time of last request and rewrites the feed cache
	*/
	private function writeLogAndCache($content){
		$file = fopen($this->log_file, "w");
		fwrite($file, time());
		fclose($file);
		$file = fopen($this->json_file, "w");
		fwrite($file, $content);
		fclose($file);
	}







}
?>