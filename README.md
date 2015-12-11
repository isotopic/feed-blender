# social-feed
Generates a mixed feed from facebook and instagram for specified profiles.

# 

Exemplo de uso:

```
 require 'SocialFeed.php';
 $social_feed = new SocialFeed(
 	array(
 		'client_id'=>'152123453345544',
 		'app_secret'=>'60asdgfac110d7b1234234241234a0d12e1a0a3420',
 		'facebook_username'=>'johndoe',
 		'instagram_username'=>'johnnydoe'
 	)
 );
 echo $social_feed->getFeed();
 ```


Retorna um json no formato:
 ```
{

    "status":"success",
    "message":"",
    "data":[
        {
            "source":"facebook",
            "link":"https:\/\/www.facebook.com\/cafe3coracoes\/photos\/a.113377965404697.17647.106469786095515\/885711318171354\/?type=3",
            "created_time":"2015-12-11T12:00:01+0000",
            "message":"Melhor sensação! Primeiro gole do dia é sagrado <3",
            "image":"https:\/\/fbcdn-photos-h-a.akamaihd.net\/hphotos-ak-xtp1\/v\/t1.0-0\/p180x540\/12294811_885711318171354_3897351323731946768_n.jpg?oh=591de9f86bf766c7c6eb4e36065d8096&oe=56E6516A&__gda__=1461297405_2cb8808c704f8ef45fe9edd1ec7349ff"
        },
        {
            "source":"instagram",
            "link":"https:\/\/www.instagram.com\/p\/_JykUcDz9R\/",
            "created_time":"1449839993",
            "message":"É quase a mesma coisa que ouvir “eu te amo”! ♥️ #saborqueapaixona #3coracoes #cafeparavoce",
            "image":"https:\/\/scontent-dfw1-1.cdninstagram.com\/hphotos-xap1\/t51.2885-15\/e35\/12357808_952064864873447_565035088_n.jpg"
        },
        (...)
        
```
