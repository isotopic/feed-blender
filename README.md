# FeedBlender

FeedBlender is a social network aggregator written in PHP.

### Key features

- Fetches content from Facebook, Twitter, Instagram and Youtube public accounts
- Organizes all different sources into a single and uniform structure
- Caches all requests within a defined timespam


### Usage:

```php
<?php
require 'FeedBlender.php';
$feed_blender = new FeedBlender(
  array(
    'facebook'=>array(
      'client_id'=>'FACEBOOK_CLIENT_ID',
      'app_secret'=>'FACEBOOK_APP_SECRET',
      'users'=>array('mitmedialab')
    ),
    'instagram'=>array(
      'users'=>array('unsplash','iss')
    ),
    'twitter'=>array(
      'client_id'=>'TWITTER_CLIENT_ID',
      'app_secret'=>'TWITTER_APP_SECRET',
      'users'=>array('nasa')
    ),
    'youtube'=>array(
      'app_secret'=>'YOUTUBE_APP_SECRET',
      'users'=>array('TEDEducation','Computerphile')
    )
  )
);
$response_json = $feed_blender->getFeed(30);
//Optional sorting parameter:
//$response_json = $feed_blender->getFeed(10, 'interlaced');
?>
```


Should produce something like this:
 ```
{
  "status": "success",
  "message": "",
  "data": [
    {
      "source": "twitter",
      "username": "NASA",
      "id": 706291714785452032,
      "link": "https://twitter.com/NASA/status/706291714785452032",
      "timestamp": 1457228041,
      "created_time": "05 Mar 2016",
      "text": "After yrs of tests & development, scientific balloon is set to break flight duration record: https://t.co/9IdbMG8KtW https://t.co/x1sh3oA060",
      "image": "https://pbs.twimg.com/media/Cc1AkoeWIAApEjx.jpg"
    },
    {
      "source": "facebook",
      "username": "MIT Media Lab",
      "id": "51320424738_10154093003709739",
      "link": "http://facebook.com/51320424738/posts/10154093003709739",
      "timestamp": 1457224511,
      "created_time": "05 Mar 2016",
      "text": "Celebrating the Invisible Cryptologists: The Digital Currency Initiative's Gina Vargas on the African-Americans who were instrumental to developing early cryptography from WWII through 1956."
      ...       
```

In order to consume the Facebook, Twitter and Youtube apis, you **must get your own credentials** (client_id, app_secret) for each one of them.
The ones used in this example are for testing purposes only and **may expire at any moment**. You MUST get your own credentials registering your app here:

[Facebook](https://developers.facebook.com/quickstarts/?platform=web">https://developers.facebook.com/quickstarts/?platform=web)
[Twitter developers](https://apps.twitter.com">https://apps.twitter.com)
[Youtube](https://console.developers.google.com">https://console.developers.google.com)