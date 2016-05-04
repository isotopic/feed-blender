<?php
/**
 * Social media aggregator.
 *
 *
 * @version 1.6
 *
 * @author  Guilherme Cruz <guilhermecruz@gmail.com>
 *
 * @link https://github.com/isotopic/feed-blender
*/
class FeedBlender{
    // Facebook accounts + app/secret credentials
    private $facebook_sources = null;

    // Twitter accounts + app/secret credentials
    private $twitter_sources = null;

    // Instagram accounts - credentials are NOT required
    private $instagram_sources = null;

    // Youtube accounts + app/secret credentials
    private $youtube_sources = null;

    // The minimum interval between api requests, in seconds. 600 = 10 minutes.
    private $cache_minimum_time = 600;

    // Used to log the time at the last request
    private $date = null;

    // The file to log request timestamps
    private $log_file = 'FeedBlender.log';

    // The file to cache the feed's content
    private $json_file = 'FeedBlender.json';

    // Adicional output info
    private $message = '';

    // Limit for the resulting feed. Defaults to 100.
    private $global_limit = 100;

    // Type of sorting. Defaults to 'date'. Other option is 'interlaced'.
    private $sorting = 'date';

    /** 
     * Options example:
     * <pre>
     * array(
     *  'facebook'=>array(
     *    'client_id'=>'FACEBOOK_CLIENT_ID',
     *    'app_secret'=>'FACEBOOK_APP_SECRET',
     *    'users'=>array('mitmedialab')
     *  ),
     *  'instagram'=>array(
     *    'users'=>array('unsplash','iss')
     *  ),
     *  'twitter'=>array(
     *    'client_id'=>'TWITTER_CLIENT_ID',
     *    'app_secret'=>'TWITTER_APP_SECRET',
     *    'users'=>array('nasa')
     *  ),
     *  'youtube'=>array(
     *    'app_secret'=>'YOUTUBE_APP_SECRET',
     *    'users'=>array('TEDEducation','Computerphile')
     *  )
     * </pre>
     *
     * @param args Array of options
     */
    public function __construct($args){
        date_default_timezone_set('America/Sao_Paulo');
        setlocale(LC_ALL, 'pt_BR');
        $this->date = getdate();
        if (isset($args['facebook'])
        && isset($args['facebook']['client_id'])
        && isset($args['facebook']['app_secret'])
        && isset($args['facebook']['users'])) {
            $this->facebook_sources = $args['facebook'];
        }
        if (isset($args['twitter'])
        && isset($args['twitter']['client_id'])
        && isset($args['twitter']['app_secret'])
        && isset($args['twitter']['users'])) {
            $this->twitter_sources = $args['twitter'];
        }
        if (isset($args['instagram'])
        && isset($args['instagram']['users'])) {
            $this->instagram_sources = $args['instagram'];
        }
        if (isset($args['youtube'])
        && isset($args['youtube']['app_secret'])
        && isset($args['youtube']['users'])) {
            $this->youtube_sources = $args['youtube'];
        }
        $this->checkRequirements();
    }

    /**
     * Main gateway to the content.
     * The source used depends on cache expiration.
     *
     * @param limit Number of itens to be fetched
     * @param sorting Type of sorting to be used. Can be 'date' or 'interlaced'.
     */
    public function getFeed($limit = 100, $sorting = 'date'){
        $this->global_limit = $limit;
        $this->sorting = $sorting;
        if ($this->cacheHasExpired()) {
            // Reloads content from the APIs
            $feed = $this->loadContentFromAPIs();
            $this->writeLogAndCache($feed);

            return $feed;
        } else {
            // Returns the local feed cache
            return file_get_contents($this->json_file);
        }
    }

    /**
     * Pre check all what is needed.
     */
    private function checkRequirements(){
        if (!function_exists('curl_version')) {
            throw new Exception('This program requires the php curl extension.');
            exit;
        }
    }

    /**
     * Checks for cache validity.
     */
    private function cacheHasExpired(){
        if (file_exists($this->log_file) && file_exists($this->json_file)) {
            $log_content = json_decode(file_get_contents($this->log_file));
            if ((time() - $log_content->time < $this->cache_minimum_time) && ($log_content->users == $this->hashUsers())) {
                return false;
            }
        }

        return true;
    }

    /**
     * .log file contains the time of last api requests, and feeds used at the time.
     * .json file contains the cached content.
     */
    private function writeLogAndCache($content){
        //Log file
        $log_file = fopen($this->log_file, 'w');
        $log_content = array('time' => time(), 'users' => $this->hashUsers());
        fwrite($log_file, json_encode($log_content, JSON_UNESCAPED_UNICODE));
        fclose($log_file);
        //Feed file
        $json_file = fopen($this->json_file, 'w');
        fwrite($json_file, $content);
        fclose($json_file);
    }

    /**
     * Generates a hashed tag from used accounts. If any of it changes, the cache must be invalidated.
     */
    private function hashUsers(){
        $facebook_users = implode(',', isset($this->facebook_sources['users']) ? $this->facebook_sources['users'] : array());
        $instagram_users = implode(',', isset($this->instagram_sources['users']) ? $this->instagram_sources['users'] : array());
        $twitter_users = implode(',', isset($this->twitter_sources['users']) ? $this->twitter_sources['users'] : array());
        $youtube_users = implode(',', isset($this->youtube_sources['users']) ? $this->youtube_sources['users'] : array());

        return $facebook_users.','.$instagram_users.','.$twitter_users.','.$youtube_users;
    }

    /**
     * Load and merge content from the apis.
     */
    private function loadContentFromAPIs(){
        //Verify what we'll need
        $facebook_timelines = isset($this->facebook_sources) ? $this->getFacebookPosts() : array();
        $instagram_timelines = isset($this->instagram_sources) ? $this->getInstagramPosts() : array();
        $twitter_timelines = isset($this->twitter_sources) ? $this->getTwitterPosts() : array();
        $youtube_timelines = isset($this->youtube_sources) ? $this->getYoutubePosts() : array();

        $blended_timelines = array();

        //Find the size of the biggest timeline
        $biggest = 0;
        for ($a = 0; $a < count($facebook_timelines); ++$a) {
            if (count($facebook_timelines[$a]) > $biggest) {
                $biggest = count($facebook_timelines[$a]);
            }
        }
        for ($a = 0; $a < count($instagram_timelines); ++$a) {
            if (count($instagram_timelines[$a]) > $biggest) {
                $biggest = count($instagram_timelines[$a]);
            }
        }
        for ($a = 0; $a < count($twitter_timelines); ++$a) {
            if (count($twitter_timelines[$a]) > $biggest) {
                $biggest = count($twitter_timelines[$a]);
            }
        }
        for ($a = 0; $a < count($youtube_timelines); ++$a) {
            if (count($youtube_timelines[$a]) > $biggest) {
                $biggest = count($youtube_timelines[$a]);
            }
        }

        // Merge content from all sources into a single array
        for ($a = 0; $a < $biggest; ++$a) {

            // FACEBOOK specific parameters
            for ($i = 0; $i < count($facebook_timelines); ++$i) {
                $timeline = $facebook_timelines[$i];
                if ($a < count($timeline)) {
                    $post = $timeline[$a];
                    $id = explode('_', $post->id);
                    array_push($blended_timelines, array(
                            'source' => 'facebook',
                            'username' => $post->from->name,
                            'id' => $post->id,
                            'link' => 'http://facebook.com/'.$post->from->id.'/posts/'.$id[1],
                            'timestamp' => (int) strtotime($post->created_time),
                            'created_time' => date('d M Y', strtotime($post->created_time)),
                            'text' => $post->message,
                            'image' => isset($post->full_picture) ? $post->full_picture : '',
                            )
                        );
                }
            }
            // INSTAGRAM specific parameters
            for ($i = 0; $i < count($instagram_timelines); ++$i) {
                $timeline = $instagram_timelines[$i];
                if ($a < count($timeline)) {
                    $post = $timeline[$a];
                    array_push($blended_timelines, array(
                            'source' => 'instagram',
                            'username' => $post->caption->from->username,
                            'id' => $post->code,
                            'link' => $post->link,
                            'timestamp' => (int) $post->created_time,
                            'created_time' => date('d M Y', $post->created_time),
                            'text' => $post->caption->text,
                            'image' => $post->images->standard_resolution->url,
                            )
                        );
                }
            }
            // TWITTER specific parameters
            for ($i = 0; $i < count($twitter_timelines); ++$i) {
                $timeline = $twitter_timelines[$i];
                if ($a < count($timeline)) {
                    $post = $timeline[$a];
                    array_push($blended_timelines, array(
                            'source' => 'twitter',
                            'username' => $post->user->screen_name,
                            'id' => $post->id,
                            'link' => 'https://twitter.com/'.$post->user->name.'/status/'.$post->id,
                            'timestamp' => (int) strtotime($post->created_at),
                            'created_time' => date('d M Y', strtotime($post->created_at)),
                            'text' => $post->text,
                            'image' => isset($post->entities->media[0]->media_url_https) ? $post->entities->media[0]->media_url_https : '',
                            )
                        );
                }
            }
            // YOUTUBE specific parameters
            for ($i = 0; $i < count($youtube_timelines); ++$i) {
                $timeline = $youtube_timelines[$i];
                if ($a < count($timeline)) {
                    $post = $timeline[$a];
                    array_push($blended_timelines, array(
                            'source' => 'youtube',
                            'username' => $post->snippet->channelTitle,
                            'id' => $post->id->videoId,
                            'link' => 'https://www.youtube.com/watch?v='.$post->id->videoId,
                            'timestamp' => (int) strtotime($post->snippet->publishedAt),
                            'created_time' => date('d M Y', strtotime($post->snippet->publishedAt)),
                            'text' => $post->snippet->description,
                            'image' => $post->snippet->thumbnails->high->url,
                            )
                        );
                }
            }
        }

        // Content is already interlaced. If date is defined, sort all by date.
        if ($this->sorting == 'date') {
            usort($blended_timelines, function ($a, $b) {
                return  $b['timestamp'] - $a['timestamp'];
            });
        }

        // Slice to limit
        $blended_timelines = array_slice($blended_timelines, 0, $this->global_limit);

        $response = array('status' => 'success', 'message' => $this->message, 'data' => $blended_timelines);

        return json_encode($response, JSON_UNESCAPED_UNICODE);
        exit;
    }

    //Sorting functions
    private function dateSort($array){
        return usort($array, array($this, 'sort_fn'));
    }
    private function sort_fn($a, $b){
        if ($a->timestamp == $b->timestamp) {
            return 0;
        }

        return ($a->timestamp < $b->timestamp) ? -1 : 1;
    }

    // FACEBOOK posts
    private function getFacebookPosts(){
        $timelines = array();
        // Get a valid token
        $token_payload = $this->curlCall('https://graph.facebook.com/v2.5/oauth/access_token?client_id='.$this->facebook_sources['client_id'].'&client_secret='.$this->facebook_sources['app_secret'].'&grant_type=client_credentials');
        $token = $token_payload->access_token;
        if ($token == null) {
            $this->message .= 'Invalid facebook token. ';

            return array();
        }
        // Load all timelines
        for ($a = 0; $a < count($this->facebook_sources['users']); ++$a) {
            $user_posts = $this->curlCall('https://graph.facebook.com/v2.5/'.$this->facebook_sources['users'][$a].'/posts/?fields=id,from,link,created_time,caption,description,message,full_picture&access_token='.$token);
            if (isset($user_posts->data)) {
                array_push($timelines, $user_posts->data);
            }
        }

        return $timelines;
    }

    // TWITTER posts - requires Basic Authentication in all calls (along with a secure connection)
    private function getTwitterPosts(){
        $timelines = array();
        // Get a valid token. This one is different because it has to be a POST and it has to send a basic auth header.
        $pair64 = base64_encode($this->twitter_sources['client_id'].':'.$this->twitter_sources['app_secret']);
        $token_payload = $this->curlCall('https://api.twitter.com/oauth2/token', 'grant_type=client_credentials', 'Basic '.$pair64);
        $token = $token_payload->access_token;
        if ($token == null) {
            $this->message .= 'Invalid twitter token. ';

            return array();
        }
        // Load all timelines
        for ($a = 0; $a < count($this->twitter_sources['users']); ++$a) {
            $user_posts = $this->curlCall('https://api.twitter.com/1.1/statuses/user_timeline.json?screen_name='.$this->twitter_sources['users'][$a].'&count=20&trim_user=false&include_rts=false&exclude_replies=true', false, 'Bearer '.$token);
            if (isset($user_posts->errors)) {
                $this->message .= 'Twitter api said: '.$user_posts->errors->message;
            } else {
                array_push($timelines, $user_posts);
            }
        }

        return $timelines;
    }

    // INSTAGRAM posts - at the moment, instagram feeds doesn't require anything
    private function getInstagramPosts(){
        $timelines = array();
        // Load all timelines
        for ($a = 0; $a < count($this->instagram_sources['users']); ++$a) {
            $user_posts = $this->curlCall('https://www.instagram.com/'.$this->instagram_sources['users'][$a].'/media/');
            if (isset($user_posts->items)) {
                array_push($timelines, $user_posts->items);
            }
        }

        return $timelines;
    }

    // YOUTUBE posts - requires static app token
    private function getYoutubePosts(){
        $timelines = array();

        for ($a = 0; $a < count($this->youtube_sources['users']); ++$a) {

            //First we need the channelId. 
            $channelId = false;
            if (strlen($this->youtube_sources['users'][$a]) == '24') {
                //Looks like it is the channelId already. (Usernames are limited to 20 characters)
                $channelId = $this->youtube_sources['users'][$a];
            } else {
                //Get the channelId from the given username
                $channel_info = $this->curlCall('https://www.googleapis.com/youtube/v3/channels?part=snippet&forUsername='.$this->youtube_sources['users'][$a].'&maxResults=1&fields=items%2Fid&key='.$this->youtube_sources['app_secret']);
                $channelId = $channel_info->items[0]->id;
            }
            //Finally, get the videos
            if ($channelId) {
                $user_posts = $this->curlCall('https://www.googleapis.com/youtube/v3/search?part=snippet&type=video&order=date&channelId='.$channelId.'&key='.$this->youtube_sources['app_secret']);
                if (isset($user_posts->items)) {
                    array_push($timelines, $user_posts->items);
                }
            }
        }

        return $timelines;
    }

    // Must also handle twitter's post calls, and basic and bearer auths.
    private function curlCall($url, $post_fields = false, $authorization = false){
        $curl = curl_init();
        curl_setopt($curl, CURLOPT_URL, $url);
        curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($curl, CURLOPT_VERBOSE, true);
        if ($post_fields) {
            curl_setopt($curl, CURLOPT_POST, 1);
            curl_setopt($curl, CURLOPT_POSTFIELDS, $post_fields);
        }
        if ($authorization) {
            curl_setopt($curl, CURLOPT_HTTPHEADER, array('Content-Type: application/x-www-form-urlencoded;charset=UTF-8;', 'Authorization: '.$authorization));
        }
        $curl_response = curl_exec($curl);
        curl_close($curl);

        return json_decode($curl_response, false);
    }
}
