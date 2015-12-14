# FeedBlender
##### This class generates a cached json containing timeline posts from Facebook, Twitter and Instagram profiles. 



### Usage:

```php
<?php
require 'FeedBlender.php';
$feed_blender = new FeedBlender(
  array(
    'facebook'=>array(
      'client_id'=>'FACEBOOK_CLIENT_ID',
      'app_secret'=>'FACEBOOK_APP_SECRET',
      'users'=>array('wired', 'ubuntulinux', 'instructables')
    ),
    'instagram'=>array(
      'users'=>array('arduinoorg', 'unsplash')
    ),
    'twitter'=>array(
      'client_id'=>'TWITTER_CLIENT_ID',
      'app_secret'=>'TWITTER_APP_SECRET',
      'users'=>array('MongoDB', 'npmjs')
    )
  )
);
$response_json = $feed_blender->getFeed();
?>
```


Should produce something like this:
 ```
{
  "status": "success",
  "message": "",
  "data": [
    {
      "source": "facebook",
      "username": "WIRED",
      "link": "http://facebook.com/19440638720/posts/10153365470328721",
      "timestamp": 1450053416,
      "created_time": "13 Dec 2015",
      "text": "These apps will give you stellar advice at home, and help you make tastier choices when you’re out shopping.",
      "image": "https://fbexternal-a.akamaihd.net/safe_image.php?d=AQDTtzqZqO9OWO3b&url=http%3A%2F%2Fwww.wired.com%2Fwp-content%2Fuploads%2F2015%2F12%2Fapp-pack-food-1200x630-e1449874890918.jpg"
    },
    {
      "source": "facebook",
      "username": "Ubuntu",
      "link": "http://facebook.com/6723083591/posts/10153694239668592",
      "timestamp": 1449794784,
      "created_time": "10 Dec 2015",
      "text": "GitHub Director of Community (and former Ubuntu Community Manager) Jono Bacon explains why you should go to #UbuCon in January. We can't help but agree.",
      "image": "https://fbexternal-a.akamaihd.net/safe_image.php?d=AQBonXQFibLBUdJ7&url=http%3A%2F%2Fubucon.org%2Fmedia%2Fcms_page_media%2F1%2Fubucon-community.jpg"
    }
      ...   
```


In order to request the Facebook and Twitter apis you must provide the corresponding pair client_id/app_secret. You can get them upon registration of your app: [Facebook developers](https://developers.facebook.com/quickstarts/?platform=web) / [Twitter developers](https://apps.twitter.com/app/new)

Instagram is the Good Guy Greg and doesn't require anything for something so simple as reading public posts.