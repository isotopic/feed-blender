<?php
/**
*
* SocialFeed
* 
* Este programa gera um arquivo json alimentado regularmente por um feed do facebook e um do instagram.
* Para os dados do instagram é necessário apenas um username.
* Para a api do facebook são necessários um par api_secret e client_id, obtidos através de qualquer app registrado em:
* https://developers.facebook.com/quickstarts/?platform=web
*
* Exemplo de uso:
* 
* require 'SocialFeed.php';
* $social_feed = new SocialFeed(
* 	array(
* 		'client_id'=>'152123453345544',
* 		'app_secret'=>'60asdgfac110d7b1234234241234a0d12e1a0a3420',
* 		'facebook_username'=>'johndoe',
* 		'instagram_username'=>'johnnydoe'
* 	)
* );
* echo $social_feed->getFeed();
* 
*
* @author Guilherme <guilhermecruz@gmail.com>
*
*/


class SocialFeed{


	/** 
	* @var string FACEBOOK Profile de onde os posts devem ser adquiridos.
	*/
	private $facebook_username = '';
	/** 
	* @var string INSTAGRAM Profile de onde os posts devem ser adquiridos.
	*/
	private $instagram_username = '';


	/** 
	* @var string FACEBOOK client_id - Pode ser de qualquer app registrada no facebook developers.
	*/
	private $client_id = '';
	/** 
	* @var string FACEBOOK app_secret - Pode ser de qualquer app registrada no facebook developers.
	*/
	private $app_secret = '';
	/** 
	* @var string Facebook Token
	*/
	private $token = '';


	/**
	* @var int Intervalo mínimo entre requisições nas apis (segundos)
	*/
	private $checking_throttle = 30;
	/** 
	* @var array Objeto date para nomear os arquivo de log
	*/
	private $date = NULL;	
	/** 
	* @var array Nome do arquivo onde será logado o momento da última atualização do feed
	*/
	private $log_file = "SocialFeed.log";	
	/** 
	* @var array Nome do arquivo onde será cacheado o conteúdo do feed construído
	*/
	private $json_file = "SocialFeed.json";



	/** 
	* Instancia e define o fuso horário
	*/
	public function __construct($args){
		date_default_timezone_set('America/Sao_Paulo');
		$this->date = getdate();
		$this->client_id = $args['client_id'];
		$this->app_secret = $args['app_secret'];
		$this->facebook_username = $args['facebook_username'];
		$this->instagram_username = $args['instagram_username'];
	}



	/**
	* Chamada principal da classe
	* 
	* Retorna string json com os feeds combinados.
	*
	* @return String
	*/
	public function getFeed(){

		if( $this->hasWaited() ){
			//Atualizar feeds
			$feed = $this->requestAPIS();
			$this->writeLogAndCache($feed);
			return $feed;
		}else{
			//Retorna feed cacheado
			return file_get_contents($this->json_file);
		}

	}



	/**
	* 
	* Acessa as apis e retorna string json contendo os dados das duas fontes.
	*
	* @return String
	*/
	private function requestAPIS(){

		$facebook_posts = "";
		$instagram_posts = "";
		$combined_posts = array();

	/* ------------------------- FACEBOOK POSTS --------------------------*/
		
		// Obter o TOKEN
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

		// Obter POSTS https://developers.facebook.com/docs/graph-api/reference/v2.5/post
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



	/* ------------------------- Mistura os dois feeds em um só array, alternando --------------------------*/

		for( $a=0; $a < max(count($facebook_posts->data),count($instagram_posts->items)); $a++){

			if($a<count($facebook_posts->data)){
				$post = $facebook_posts->data[$a];
					$link = $post->link;
					$created_time = $post->created_time;
					$message = $post->message;
					$image = $post->full_picture;
					array_push($combined_posts, array('source'=>'facebook', 'link'=>$link, 'created_time'=>$created_time, 'message'=>$message, 'image'=>$image));
			}

			if($a<count($instagram_posts->items)){
				$post = $instagram_posts->items[$a];
					$link = $post->link;
					$created_time = $post->created_time;
					$message = $post->caption->text;
					$image = $post->images->standard_resolution->url;
					array_push($combined_posts, array('source'=>'instagram', 'link'=>$link, 'created_time'=>$created_time, 'message'=>$message, 'image'=>$image));
			}

		}

		$response = array( 'status'=>'success','message'=>'','data'=>$combined_posts );

		return json_encode( $response , JSON_UNESCAPED_UNICODE);


	}

















	/**
	* Verifica se a última chamada para as APIs foi feita há no mínimo n (checking_throttle) segundos
	*
	* @return boolean
	*/
	private function hasWaited(){
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
	* Atualiza horário da última requisição das apis e cacheia o conteúdo do feed
	*
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