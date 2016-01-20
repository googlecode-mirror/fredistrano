# Introduction #

If you don't want to rely on the password stored in the database, fredistrano lets you define a custom authentication mechanism. As an example, we are using in our company a SOAP web service for authenticating our users against a Windows Active Directory.

# Details #

First of all, you will have to tell fredistrano that you are willing to use a custom authentication. In the file _app/config/config.php_, change the value of the option named **Security>authenticationType** to **1**. See the ConfigurationGuide for further details about configuration.

Afterwards, you will need to implement yourself the **authenticate** method located in the file _app/vendors/authentication.php_. From a given login/password, the method has to return a boolean: **true** if the authentication is successful; **false** otherwise.

That's all, you're done! You should be able to authenticate with your own authentication method.