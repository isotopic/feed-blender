# FeedBlender
This class generates a cached json feed containing timeline posts from Facebook and Instagram profiles.


### Usage:

```php
<?php
 $feed_blender = new FeedBlender( array(
  'facebook'=>array(
    'client_id'=>'CLIENT_ID',
    'app_secret'=>'APP_SECRET',
    'users'=>array('wired', 'ubuntulinux', 'instructables')
  ),
  'instagram'=>array(
    'users'=>array('arduinoorg', 'unsplash')
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
            "link": "http://facebook.com/19440638720/posts/10153364287623721",
            "timestamp": 1450000238,
            "created_time": "13 Dec 2015",
            "text": "The war against crypto rages on in the wake of terrorist attacks in Paris and San Bernardino.",
            "image": "https://fbexternal-a.akamaihd.net/safe_image.php?d=AQBosIIe-cITr5c-&url=http%3A%2F%2Fwww.wired.com%2Fwp-content%2Fuploads%2F2015%2F09%2FGettyImages-499420689-1200x630.jpg"
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



In order to access the Facebook Graph API, a valid pair client_id/app_secret must be provided.
These credentials can be acquired upon registration of any app here:
https://developers.facebook.com/quickstarts/?platform=web

The instagram feed does not require any credentials besides the username.
