# social-feed
Generates a mixed feed from facebook and instagram for specified profiles.

# 

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
