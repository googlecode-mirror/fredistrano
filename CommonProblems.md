This page sums up the main problems faced by Fredistrano's users. Don't forget that we also  provide a [dedicated forum](http://fbollon.net/forum/36).

# Installation #

**1. After installing Fredistrano according to the provided documentation, the application only displays an empty page (or maybe two small stribes)**

By default, the application doesn't output error messages. To enable error messages, you must turn a debug flag to 1. Therefore, go to your fredistrano's installation directory. Open the file _/app/config/core.php_ and change the following line:
```
define('DEBUG', 0);
```
To
```
define('DEBUG', 1);
```

**2. After installing Fredistrano according to the provided documentation, the application only displays the following error:**

```
Notice: Undefined variable: javascript in /var/www/fredistrano/app/views/layouts/default.thtml on line 24

Fatal error: Call to a member function link() on a non-object in /var/www/fredistrano/app/views/layouts/default.thtml on line 24
```

Some tables are missing in the database. Check if the database was correcly initialized by the SQL script located in _/app/config/sql/fredsitrano.sql_. Before running fredsitrano for the first time, your database has to contain 9 tables (aros, acos, aros\_acos, control\_objects, deployment\_logs, groups, groups\_users, projects, users) that already have initial data (default admin, authorization definitions...).

**3. Under Windows, even though Cygwin has been correctly installed, Fredistrano can't deploy any project. In the deployment logs, the result of the export command line is either empty or contains 'bash.exe ... not found'**

If you have just installed Cygwin, restart your computer first and try again.

# Usage #
**1. During a deployment, the following error message is triggered "Unable to find deploy.php"**

Fredsitrano is looking for a "deploy.php" file in the root directory of the deployed project. If the file is not found, Fredsitrano is unable to continue. Make also sure that you have committed this file in Subversion. Refer to the documentation for further details about the expected content of "deploy.php".

**2. During a deployment, the following error message is triggered "[Action: update] > Specified directory /xxxx/xxxx/fredistrano/files/tmp/myproject/tmpDir/ is not writeable"**

Delete the directories /xxxx/xxxx/fredistrano/files/tmp/myproject/tmpDir and /xxxx/xxxx/fredistrano/files/tmp/myproject and deploy again.

**3. During a deployment, the following error message is triggered "[Action: checkout] > An error has been detected in the SVN output"**

Check the error message in the console output, it's certainly a connection issue or an authentication issue to subversion server.