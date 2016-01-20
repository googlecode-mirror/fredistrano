# Introduction #
For Windows users, fredistrano requires the installation of Cygwin in order to run several shell commands (rsync,...). Because Cygwin can be tricky (especially if you are not familiar with it), this page details a basic Cygwin setup for fredistrano.

This configuration has been fully tested and actually works great. We are using in production mode for Intranet applications.

# Details #
## Installation ##
The first step is to install cygwin. Simply go to the [cygwin homepage](http://www.cygwin.com/) and execute **setup.exe**. In addition to the basic components, one must also select the following packages for installation:
  * _rsync_, command required for synchronizing the currently with updates
  * _perl_, required by a custom script
  * _svn_, client required to export source code from SVN repository

## Post-install activities ##
### Update path ###
To benefit from all cygwin executables everywhere in your system, add the cygwin binary directory in the PATH:
  * Right click on "my computer"
  * Select "Properties"
  * Select the "Advanced" tab
  * Clik on the "Environment Variables" button
  * Update the PATH variable with the binary directory (eg. "C:\cygwin\bin")
To validate the modifcation
  * Open a new shell and type one of cygwin executables (eg. svn)
  * If found the PATH has been correctly set up

### Create apache service user in Cygwin (if required) ###
  * Open a cygwin shell
  * Take a loook at the content of the _/etc/passwd_ file
If your user is already in the file (eg. local user) then you are OK with this step otherwise do as follow:
  * Backup the file _/etc/passwd_
```
cp /etc/passwd /etc/passwd.bak
```
  * Retrieve a user from the domain controller and add it to file _/etc/passwd_ (don't forget to append and not recreate the file). Here we add the domain user _test_ (with a domain name being _my.domain_).
```
mkpasswd -d my.domain -u test >> /etc/passwd
```
  * (optional) You may also update manually his group membership
```
less /etc/group
```
Content eg. > Administrators:S-1-5-32-544:**544**:
```
vim /etc/passwd
```
Content before > service:unused\_by\_nt/2000/xp:501:**513**:Domain service user, U-MY.DOMAIN\service,S-1-5-2 ...[.md](.md)

Content after > service:unused\_by\_nt/2000/xp:501:**544**:Domain service user, U-MY.DOMAIN\service,S-1-5-2 ...[.md](.md)vim /etc/passwd
}}}  ```