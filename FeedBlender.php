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

	// Facebook content provider
	private $facebook_username;

	// Instagram content provider
	private $instagram_username;

	// Facebook client_id - From any app registered on Facebook Developers
	private $client_id;

	// Facebook app_secret - The app secret for the client id above
	private $app_secret;

	// The minimum interval between api requests, in seconds
	private $checking_throttle = 300;

	// Used to log the time at the last request
	private $date = NULL;	

	// The file to log request timestamps
	private $log_file = "FeedBlender.log";

	// The file to cache the feed's content
	private $json_file = "FeedBlender.json";


	/** 
	* Let's construct something
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
	* Main gateway to the content.
	* The source used depends on cache expiration.
	*/
	public function getFeed(){
		if( $this->cacheHasExpired() ){
			// Reloads the content from the APIs
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
		$facebook_posts = $this->getFacebookPosts();
		$instagram_posts = $this->getInstagramPosts();
		$combined_posts = array();

		// Merge content from both sources into a single array
		for( $a=0; $a < max(count($facebook_posts->data),count($instagram_posts->items)); $a++){
			if($a<count($facebook_posts->data)){
				$post = $facebook_posts->data[$a];
					$short_id = explode('_',$post->id);
					$link = 'http://facebook.com/'.$this->facebook_username.'/posts/'.$short_id[1];
					$timestamp = (int) strtotime( $post->created_time );
					$created_time = date("d M Y", strtotime($post->created_time));
					$message = $post->message;
					$image = $post->full_picture;
					array_push($combined_posts, array('source'=>'facebook', 'username'=>$this->facebook_username, 'link'=>$link, 'timestamp'=>$timestamp, 'created_time'=>$created_time, 'message'=>$message, 'image'=>$image));
			}
			if($a<count($instagram_posts->items)){
				$post = $instagram_posts->items[$a];
					$link = $post->link;
					$timestamp = (int) $post->created_time;
					$created_time = date("d M Y", $post->created_time);
					$message = $post->caption->text;
					$image = $post->images->standard_resolution->url;
					array_push($combined_posts, array('source'=>'instagram', 'username'=>$this->instagram_username, 'link'=>$link, 'timestamp'=>$timestamp, 'created_time'=>$created_time, 'message'=>$message, 'image'=>$image));
			}
		}
		$response = array('status'=>'success','message'=>'','data'=>$combined_posts);
		return json_encode( $response , JSON_UNESCAPED_UNICODE);
		exit;
	}


	private function getFacebookPosts(){
		// Get a valid token
		$token_payload = $this->curlGet('https://graph.facebook.com/v2.5/oauth/access_token?client_id='.$this->client_id.'&client_secret='.$this->app_secret.'&grant_type=client_credentials');
		$token = $token_payload->access_token;
		if($token == NULL){
			return json_encode(array( 'status'=>'error','message'=>'Token inválido','data'=>'' ), JSON_UNESCAPED_UNICODE);
			exit;
		}
		// Get posts https://developers.facebook.com/docs/graph-api/reference/v2.5/post
		$facebook_posts = $this->curlGet('https://graph.facebook.com/v2.5/'.$this->facebook_username.'/posts/?fields=id,link,created_time,caption,description,message,full_picture&access_token='.$token );
		return $facebook_posts;
	}


	private function getInstagramPosts(){
		$instagram_posts = $this->curlGet('https://www.instagram.com/'.$this->instagram_username.'/media/');
		return $instagram_posts;
	}


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