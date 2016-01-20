## Protect Fredistrano from search engines ##

If you want to block spiders for your fredistrano installation, create a "robots.txt" file with as content the following lines:

> `User-agent: *`

> `Disallow: /`

## Use a custom authentication method ##

You have the possibility to use your own authentication method.

  1. Modify the function authenticate in fredistrano/app/vendors/authentication.php according to your needs.
  1. Set the $config['Security']['authenticationType'] to 1

```
	$config['Security'] = array(
		'authenticationType'	=> 1,			//authentication type: 0 = accept all, 1 = custom, 2 = mysql 
		'authorizationsDisabled'=> false		//disable authorization
	);
```