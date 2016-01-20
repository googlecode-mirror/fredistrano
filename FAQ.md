

## What is Fredistrano? ##

**Fredistrano** is a deployment tool for web applications. It automates the export of your sources from a subversion repository and synchronizes them with the content of a target directory. Several manual tasks are also handled directly by the application during the deployment: renaming of configuration files, modifying permissions...

## Why would I choose Fredistrano? ##

Because it is a great application! Because it also runs on Windows systems. Because it is fast and reliable (make use of rsync). Because it provides a transparent (XML/HTML logs) and customizable (before/after scripts) deployment process. Because we really use it every day. Because... You get the message!

## Which applications may be deployed with Fredistrano? ##

Fredistrano is a PHP web application written with CakePHP. However, it may be used to deploy all kinds of web applications (perl, html files, ruby, etc... ). Whatever your project is, you just need to add one PHP configuration file to make it Fredistrano-compliant.

## Is there any support around Fredistrano? ##

Fredistrano is not a full time project. Nevertheless, we try to help our users in several ways:

  * A documentation is shipped with each release
  * If your are reading this page, you are already aware of the [wiki](http://code.google.com/p/fredistrano/w/list)
  * We keep track of all enhancement requests and bugs in a [dedicated website](http://code.google.com/p/fredistrano/issues/list)
  * We have even set up a [discussion group](http://groups.google.com/group/fredistrano-discuss) on google (ambitious you said?)

## Why should I not use Fredistrano? ##

Even though Fredistrano is a great app, it is not suited to all purposes. Indeed, Fredistrano:
  * cannot deploy an application to multiple hosts
  * does not support other version control systems than Subversion