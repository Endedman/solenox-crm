# JStore.SRVManager

> Use assets for your webserver in [WEBSERVER-CONFIG] folder
> 
> *"We just wanted to make the life of the owners of clean minecraft servers a little easier"* - JStore development department

## What is a JStore server manager?
JStore.SRVManager can be used in small communities for contacting between server *owners* and *users*. Using SRVManager, you can implement:
 - [x] Users' registration
 - [x] News feed
 - [x] File management system (raw implementation)
 - [x] Users' chat
 - [x] Ranking system depends on users activity (uploading files, writing news or simple chatting)
 - [x] AJAX (fast content reloading)
 - [x] Awesome admin panel with TinyMCE 3.x
### What is unimplemented now?
So, we have have a lot of problem depending on User.php class (due to incident in code documentation with ChatGPT some class were silently broken at 2 days before deadline =)
#### Now it is NOT implemented:
 - [ ] Manual user ranking system (don't work as expected)
 - [ ] Full files' pages (Description, License, etc)
 - [ ] Themes (only 98.css and it's family applicable

## Requirements
Please carefully read this. All apps which not listed in this section are not supported. If you are experiencing issues, please chech `Issues` section - maybe problem is listed before.

You need:
 - [X] Apache2 / nginx webserver
 - [X] PHP 8.2 (older/newer versions is not tested)
 - [X] Full root rights (are you want to install modules in future via PHAR modules? xD)
 - [X] MySQL 8+ database (MariaDB supported too)
 - [X] Opened ports for mailing

 
## Installation
Now you need a lot of packages (if you don't have any webserver/etc)
If you have a compatible webserver, skip this step.
**READ CAREFULLY: provided below product is not opensource. All modifications are strictly prohibited.**

First, go to your Terminal (we recommend using SSH) and write this command:
`wget  https://install.keyhelp.de/get_keyhelp.php -O install_keyhelp.sh; bash install_keyhelp.sh;`
The installer can request additional data such as 
- email
- domain name
- custom username

After CP installation, go to `<your ip>` and enter credentials.
Now, we need to configure webserver and database.
1. Configure DB 
Go to Settings>Configuration>DB settings and set `'Allow remote access'` and `Free choice of names`
2. Create user.
Go to Account>Users Administration and create user. Note: don't use Default template.
3. Login as USER and create database
4. Add domain name
5. Login to your FTP/SFTP server and place all site's data to `/home/users/<USERNAME>/www/<DOMAINNAME>/`
6. Go to PHPMyAdmin/your favourite CP and import SQL database (named `snowbear.sql`)
7. Configure your config.php (Not all values are mandatory, but you should remove mentions if you don't use directive)
8. Launch `composer install`
9. Now, you are ready to use SRVManager! Please be sure that all folders and files have right attributes.

## For developers
We have small API for contacting some DB fiels with no usage of Mysql protocol in computer programs.
Endpoint:
`api.example.com`

`getToken` (GET, params: username, password, return: token)

`getAllUsers` (POST, params: X-HTTP-API-TOKEN, returns: JSON Array)

`getAllNews` (POST, params: X-HTTP-API-TOKEN, returns: JSON Array)

`getAllMessages` (POST, params: X-HTTP-API-TOKEN, returns: JSON Array)
