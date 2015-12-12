# FeedBlender
This class generates a cached json feed containing data provided from Facebook and Instagram feeds.


### Usage:

```php
<?php
 require 'FeedBlender.php';
 $feed_blender = new FeedBlender(
    array(
        'client_id'=>'152123453345544',
        'app_secret'=>'60asdgfac110d7b1234234241234a0d12e1a0a3420',
        'facebook_username'=>'johndoe',
        'instagram_username'=>'johnnydoe'
    )
 );
 echo $feed_blender->getFeed();
?>
```


Should produce something like this:
 ```
{
    "status":"success",
    "message":"",
    "data":[
        {
            "source":"facebook",
            "link":"http://facebook.com/ubuntulinux/posts/10153694239668592",
            "created_time":"11 Dec 2015",
            "message":"GitHub Director of Community (and former Ubuntu Community Manager) Jono Bacon explains why you should go to #UbuCon in January. We can't help but agree.",
            "image":"https://fbexternal-a.akamaihd.net/safe_image.php?d=AQBonXQFibLBUdJ7&url=http%3A%2F%2Fubucon.org%2Fmedia%2Fcms_page_media%2F1%2Fubucon-community.jpg"
        },
        {
            "source":"instagram",
            "link":"https://www.instagram.com/p/_KQgGjPMyV/",
            "created_time":"10 Dec 2015",
            "message":"The "bath" process. #arduino #arduinoorg #arduinoteam #onthego #strambino #production #pcb #board #quality #madeinitaly #withlove",
            "image":"https://scontent-mia1-1.cdninstagram.com/hphotos-xft1/t51.2885-15/s640x640/sh0.08/e35/12356440_765574983571153_147890575_n.jpg"
        },
        (...)
        
```



In order to access the Facebook Graph API, a valid pair client_id/app_secret must be provided.
These credentials can be acquired upon registration of any app here:
https://developers.facebook.com/quickstarts/?platform=web

The instagram feed does not require any credentials besides the username.
