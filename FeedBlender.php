<?php
/**
 * The amazing Feed Blender mix up and serve your favorite facebook and instagram feeds.
 *
 * @todo exception handling
 * @todo limit itens
 * @todo interlaced or sort_by_date
 *
 * @version 1.0
 * @author  Guilherme Cruz <guilhermecruz@gmail.com>
*/

class FeedBlender{

	// Facebook accounts + app/secret credentials
	private $facebook_sources = NULL;

	// Instagram accounts - it's timeline api ("/media") does not require credentials
	private $instagram_sources = NULL;

	// The minimum interval between api requests, in seconds
	private $checking_throttle = 300;

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
		if(isset($args['instagram'])  
		&& isset($args['instagram']['users'])){
			$this->instagram_sources = $args['instagram'];
		}
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

		$facebook_timelines = $this->getFacebookPosts();
		$instagram_timelines = $this->getInstagramPosts();
		$combined_posts = array();

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

		// Merge content from all sources into a single array
		for( $a=0; $a < $biggest; $a++){

			//All facebook timelines
			for( $i=0; $i<count($facebook_timelines); $i++){
				$timeline = $facebook_timelines[$i];
				if($a<count($timeline)){
					$post = $timeline[$a];
						$id = explode('_',$post->id);
						array_push($combined_posts, array(
							'source'=>'facebook', 
							'username'=>$post->from->name, 
							'link'=>'http://facebook.com/'.$post->from->id.'/posts/'.$id[1],
							'timestamp'=>(int) strtotime( $post->created_time ), 
							'created_time'=>date("d M Y", strtotime($post->created_time)), 
							'text'=>$post->message, 
							'image'=>$post->full_picture
							)
						);
				}
			}
			//All instagram timelines
			for( $i=0; $i<count($instagram_timelines); $i++){
				$timeline = $instagram_timelines[$i];
				if($a<count($timeline)){
					$post = $timeline[$a];
						array_push($combined_posts, array(
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

		}
		$response = array('status'=>'success','message'=>$this->message,'data'=>$combined_posts);
		return json_encode( $response , JSON_UNESCAPED_UNICODE);
		exit;
	}





	// Returns an 2d array of facebook timelines for each user specified
	private function getFacebookPosts(){
		$timelines = array();
		// Get a valid token
		$token_payload = $this->curlGet('https://graph.facebook.com/v2.5/oauth/access_token?client_id='.$this->facebook_sources['client_id'].'&client_secret='.$this->facebook_sources['app_secret'].'&grant_type=client_credentials');
		$token = $token_payload->access_token;
		if($token == NULL){
			$this->message = "Invalid facebook token.";
			return array();
		}
		// Load all timelines
		for($a=0; $a<count($this->facebook_sources['users']); $a++){
			$user_posts = $this->curlGet('https://graph.facebook.com/v2.5/'.$this->facebook_sources['users'][$a].'/posts/?fields=id,from,link,created_time,caption,description,message,full_picture&access_token='.$token );
			if(isset($user_posts->data)){
				array_push($timelines, $user_posts->data);
			}
		}
		return $timelines;
	}





	// Returns an 2d array of instagram timelines for each user specified
	private function getInstagramPosts(){
		$timelines = array();
		// Load all timelines
		for($a=0; $a<count($this->instagram_sources['users']); $a++){
			$user_posts = $this->curlGet('https://www.instagram.com/'.$this->instagram_sources['users'][$a].'/media/');
			if(isset($user_posts->items)){
				array_push($timelines, $user_posts->items);
			}
		}
		return $timelines;
	}






	// As simple as it GETs!
	private function curlGet($url){
		$curl = curl_init(); 
		curl_setopt($curl, CURLOPT_URL, $url);
		curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);
		curl_setopt($curl, CURLOPT_HEADER, false); 
		curl_setopt($curl, CURLOPT_VERBOSE, true);
		$curl_response = curl_exec($curl);
		curl_close($curl);
		return json_decode($curl_response, false);
	}


}













?>