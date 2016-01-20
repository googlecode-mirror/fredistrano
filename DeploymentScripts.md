# Examples #

## Redirect to a specific page during the deployment process and modification of the content of some file ##

In this example with two very simple shell scripts I automatize some little tasks before and after the deployemnt process.

**Before the deployment (exactly between the export of files from Subverseion and the synchronisation with production files)**

  1. I change the .htaccess of my project to redirect the visitor to a specific page during the deployment process.
  1. I modify the value of a constant in a configuration file as needed on the production server.
  1. I move some temporaries "`302.*`" files to redirect my visitors on.

### Files in .fredistrano directory in my project ###
At the root of my project to deploy, I have created a specific folder contening some files :

![http://farm4.static.flickr.com/3118/2927999464_456e3e0002_o.png](http://farm4.static.flickr.com/3118/2927999464_456e3e0002_o.png)

**.htaccess**
```
<IfModule mod_rewrite.c>
   RewriteEngine on
   RewriteCond    %{REQUEST_FILENAME}  !302\..*$
   RewriteRule    (.*) /myProject/302.html	[L,R]
</IfModule>
```

**302.html**
```
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Strict//EN"
	"http://www.w3.org/TR/xhtml1/DTD/xhtml1-strict.dtd">
<html>
	<head>
		<meta http-equiv="Content-type" content="text/html; charset=utf-8">
		<title>Application in maintenance</title>
		
	</head>
	<body>
		<h1>Application in maintenance</h1>
		This application is in maintenance for very short time.<br>
		<a href="http://mydomain.com/myProject/">Please try again in few minutes.</a>
		<img src="302.png" alt="maintenance" title="maintenance" style="float:center;" />
	</body>
</html>
```

**beforeScript**
```
#!/bin/sh
PATHPRD=/cygdrive/d/www/html/myProject
PATHTMP=/cygdrive/d/www/html/fredistrano.1.0.RC1/files/tmp/myProject/tmpDir

# move the specific temporary .htaccess at the right place 
cp -vf ${PATHTMP}/.fredistrano/.htaccess ${PATHPRD}/.htaccess
#  move the specific temporary redirect page at the right place 
cp -vf ${PATHTMP}/.fredistrano/302.* ${PATHPRD}/
```

**afterScript**
```
#!/bin/sh
PATHPRD=/cygdrive/d/www/html/myProject
PATHTMP=/cygdrive/d/www/html/fredistrano.1.0.RC1/files/tmp/myProject/tmpDir

sed -i.bak "s/\('debug',\)[ ]*[12]/\1 0/gi" ${PATHPRD}/app/config/core.php

# replace the temporary .htaccess 
cp -vf ${PATHTMP}/.htaccess ${PATHPRD}/.htaccess

# remove the temorary redirection page 
rm -vf ${PATHPRD}/302.*
# remove the old version of app/config/core.bak
rm -vf ${PATHPRD}/app/config/core.php.bak
```