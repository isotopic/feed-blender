<?php
/**
 * The amazing Feed Blender mix up and serve your favorite facebook, instagram and twitter feeds.
 *
 *
 * @version 1.5
 * @author  Guilherme Cruz <guilhermecruz@gmail.com>
*/

class FeedBlender{

	// Facebook accounts + app/secret credentials
	private $facebook_sources = NULL;

	// Twitter accounts + app/secret credentials
	private $twitter_sources = NULL;

	// Instagram accounts - credentials are NOT required
	private $instagram_sources = NULL;

	// The minimum interval between api requests, in seconds.
	private $checking_throttle = 600;

	// Used to log the time at the last request
	private $date = NULL;	

	// The file to log request timestamps
	private $log_file = "FeedBlender.log";

	// The file to cache the feed's content
	private $json_file = "FeedBlender.json";

	// Adicional output info
	private $message = '';


	/** 
	* Let's construct something
	*/
	public function __construct($args){
		date_default_timezone_set('America/Sao_Paulo');
		setlocale(LC_ALL, 'pt_BR');
		$this->date = getdate();
		if(isset($args['facebook'])  
		&& isset($args['facebook']['client_id']) 
		&& isset($args['facebook']['app_secret'])
		&& isset($args['facebook']['users'])){
			$this->facebook_sources = $args['facebook'];
		}
		if(isset($args['twitter'])  
		&& isset($args['twitter']['client_id']) 
		&& isset($args['twitter']['app_secret'])
		&& isset($args['twitter']['users'])){
			$this->twitter_sources = $args['twitter'];
		}
		if(isset($args['instagram'])  
		&& isset($args['instagram']['users'])){
			$this->instagram_sources = $args['instagram'];
		}
		$this->checkRequirements();
	}




	/**
	* Main gateway to the content.
	* The source used depends on cache expiration.
	*/
	public function getFeed(){
		if( $this->cacheHasExpired() ){
			// Reloads content from the APIs
			$feed = $this->loadContentFromAPIs();
			$this->writeLogAndCache($feed);
			return $feed;
		}else{
			// Returns the local feed cache
			return file_get_contents($this->json_file);
		}
	}


	/**
	* Pre check all what is needed
	*/
	private function checkRequirements(){
		if(!function_exists('curl_version')){
			throw new Exception("This program requires curl enabled.");
			exit;
		}
	}

	/**
	* Checks if already waited n seconds since the last api requests
	*/
	private function cacheHasExpired(){
		if(file_exists($this->log_file)){
			$logged_time = file_get_contents($this->log_file);
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


 

	/**
	* Load and merge content from the apis
	*/
	private function loadContentFromAPIs(){
		//Verify what we'll need
		$facebook_timelines = isset( $this->facebook_sources) ? $this->getFacebookPosts() : array() ;
		$instagram_timelines = isset( $this->instagram_sources) ? $this->getInstagramPosts() : array() ;
		$twitter_timelines = isset( $this->twitter_sources) ? $this->getTwitterPosts() : array() ;

		$blended_timelines = array();

		//Find the size of the biggest timeline
		$biggest = 0;
		for( $a=0; $a < count($facebook_timelines); $a++){
			if( count($facebook_timelines[$a]) > $biggest){
				$biggest = count($facebook_timelines[$a]);
			}
		}
		for( $a=0; $a < count($instagram_timelines); $a++){
			if( count($instagram_timelines[$a]) > $biggest){
				$biggest = count($instagram_timelines[$a]);
			}
		}
		for( $a=0; $a < count($twitter_timelines); $a++){
			if( count($twitter_timelines[$a]) > $biggest){
				$biggest = count($twitter_timelines[$a]);
			}
		}

		// Merge content from all sources into a single array
		for( $a=0; $a < $biggest; $a++){

			// All facebook timelines
			for( $i=0; $i<count($facebook_timelines); $i++){
				$timeline = $facebook_timelines[$i];
				if($a<count($timeline)){
					$post = $timeline[$a];
						$id = explode('_',$post->id);
						array_push($blended_timelines, array(
							'source'=>'facebook', 
							'username'=>$post->from->name, 
							'link'=>'http://facebook.com/'.$post->from->id.'/posts/'.$id[1],
							'timestamp'=>(int) strtotime( $post->created_time ), 
							'created_time'=>date("d M Y", strtotime($post->created_time)), 
							'text'=>$post->message, 
							'image'=>isset($post->full_picture)?$post->full_picture:''
							)
						);
				}
			}
			// All instagram timelines
			for( $i=0; $i<count($instagram_timelines); $i++){
				$timeline = $instagram_timelines[$i];
				if($a<count($timeline)){
					$post = $timeline[$a];
						array_push($blended_timelines, array(
							'source'=>'instagram', 
							'username'=>$post->caption->from->username, 
							'link'=>$post->link, 
							'timestamp'=>(int) $post->created_time, 
							'created_time'=>date("d M Y", $post->created_time), 
							'text'=>$post->caption->text, 
							'image'=>$post->images->standard_resolution->url
							)
						);
				}
			}	
			// All twitter timelines
			for( $i=0; $i<count($twitter_timelines); $i++){
				$timeline = $twitter_timelines[$i];
				if($a<count($timeline)){
					$post = $timeline[$a];
						array_push($blended_timelines, array(
							'source'=>'twitter', 
							'username'=>$post->user->screen_name, 
							'link'=>$post->id, 
							'timestamp'=>(int) strtotime( $post->created_at ), 
							'created_time'=>date("d M Y", strtotime($post->created_at)), 
							'text'=>$post->text, 
							'image'=>isset($post->entities->media[0]->media_url_https) ? $post->entities->media[0]->media_url_https : ''
							)
						);
				}
			}	

		}
		$response = array('status'=>'success','message'=>$this->message,'data'=>$blended_timelines);
		return json_encode( $response , JSON_UNESCAPED_UNICODE);
		exit;
	}





	// FACEBOOK posts
	private function getFacebookPosts(){
		$timelines = array();
		// Get a valid token
		$token_payload = $this->curlCall('https://graph.facebook.com/v2.5/oauth/access_token?client_id='.$this->facebook_sources['client_id'].'&client_secret='.$this->facebook_sources['app_secret'].'&grant_type=client_credentials');
		$token = $token_payload->access_token;
		if($token == NULL){
			$this->message .= "Invalid facebook token. ";
			return array();
		}
		// Load all timelines
		for($a=0; $a<count($this->facebook_sources['users']); $a++){
			$user_posts = $this->curlCall('https://graph.facebook.com/v2.5/'.$this->facebook_sources['users'][$a].'/posts/?fields=id,from,link,created_time,caption,description,message,full_picture&access_token='.$token );
			if(isset($user_posts->data)){
				array_push($timelines, $user_posts->data);
			}
		}
		return $timelines;
	}





	// TWITTER posts - require Basic Authentication in all calls (along with a secure connection)
	private function getTwitterPosts(){
		$timelines = array();
		// Get a valid token. This one is different because it has to be a POST + has to send the basic auth header.
        $pair64 = base64_encode($this->twitter_sources['client_id'].":".$this->twitter_sources['app_secret']);
        $token_payload = $this->curlCall("https://api.twitter.com/oauth2/token", "grant_type=client_credentials", "Basic ".$pair64);
		$token = $token_payload->access_token;
		if($token == NULL){
			$this->message .= "Invalid twitter token. ";
			return array();
		}
		// Load all timelines
		for($a=0; $a<count($this->twitter_sources['users']); $a++){
			$user_posts = $this->curlCall('https://api.twitter.com/1.1/statuses/user_timeline.json?screen_name='.$this->twitter_sources['users'][$a].'&count=20&exclude_replies=true', false, "Bearer ".$token);
			if(isset($user_posts->errors)){
				$this->message .= "Twitter api said: ".$user_posts->errors->message;
			}else{
				array_push($timelines, $user_posts);
			}
		}
		return $timelines;
	}



	// INSTAGRAM posts - this is the good guy greg, doesn't require anything
	private function getInstagramPosts(){
		$timelines = array();
		// Load all timelines
		for($a=0; $a<count($this->instagram_sources['users']); $a++){
			$user_posts = $this->curlCall('https://www.instagram.com/'.$this->instagram_sources['users'][$a].'/media/');
			if(isset($user_posts->items)){
				array_push($timelines, $user_posts->items);
			}
		}
		return $timelines;
	}






	// Must also handle twitter's post calls, and basic and bearer auths.
	private function curlCall($url, $post_fields=false, $authorization=false){
		$curl = curl_init(); 
		curl_setopt($curl, CURLOPT_URL, $url);
		curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);
		curl_setopt($curl, CURLOPT_VERBOSE, true);
		if($post_fields){
			curl_setopt($curl, CURLOPT_POST, 1);
			curl_setopt($curl, CURLOPT_POSTFIELDS, $post_fields);
		}
		if($authorization){
			curl_setopt($curl, CURLOPT_HTTPHEADER, array("Content-Type: application/x-www-form-urlencoded;charset=UTF-8;","Authorization: ".$authorization));
		}
		$curl_response = curl_exec($curl);
		curl_close($curl);
		return json_decode($curl_response, false);
	}




}













?>