<?php
ini_set('display_errors', 0);
ini_set('display_startup_errors', 0);
error_reporting(0);
mysqli_report(MYSQLI_REPORT_ERROR | MYSQLI_REPORT_STRICT);
session_start();
define("JSTORE_ENGINE_VERSION", 'J2ME.xyz template (build #1421) v1.1.18');                             // Please, do not change. We are created this engine...                                     
//----------------------------------------------------------------------------------------------------------Main variables----------------------------------------------------------------------------------------------------------
define("JSTORE_URL", 'https://j2me.xyz');
define("JSTORE_MOBILE_URL", 'https://j2me.xyz/mobile/');
define("JSTORE_FORUM_URL", 'https://community.j2me.xyz');                                               // Not mandatory. Delete mention in index.php if you don't have forum.
define("JSTORE_STAFF_MAIL_URL", 'https://mail.j2me.xyz');                                               // Not mandatory. Delete mention in index.php if you don't have mail.
define("JSTORE_TELEGRAM_CHANNEL_URL", 'https://therealj2me.t.me');                                      // Not mandatory. Delete mention in index.php if you don't have telegram channel.
define("JSTORE_BRAND_NAME", 'J2ME.xyz');
define("JSTORE_SITE_DESC", '<p>Welcome to J2ME.xyz (formerly LibreShare).</p><p>Here you can find all necessary info about J2ME.xyz.</p>');
define("JSTORE_MAIN_TAB_DESC", '<h4>Who are we?</h4><p>We are small organisation which collects J2ME apps. All apps are marked as abandonware, we have all rights to redistribute it =)</p><p>If you want to donate, check please Donation tab.</a></p>');
define("JSTORE_RADIO_POINT", 'https://wave.j2me.xyz:8443/radiopoint');                                  // Not mandatory. Delete mention in index.php if you don't have radio.
define("JSTORE_DIR", '/var/www/html/jstore/');                                                          // Change it with YOUR dir.
define("JSTORE_MOBILE_DIR", '/var/www/html/jstore/mobile/');                                            // Change it with YOUR dir.
define("JSTORE_MOBILE_LANGUAGES_DIR", '/var/www/html/jstore/mobile/translations/');                     // Change it with YOUR dir.
define("JSTORE_UPLOAD_DIR", '/var/www/html/jstore/uploads/');                                           // This and const below must be the same if you don't have external FTP mounted in OS.
define("JSTORE_TEMP_UPLOAD_DIR", '/var/www/html/jstore/uploadsTemp/');                                  // This and const above must be the same if you don't have external FTP mounted in OS.
define("JSTORE_UPLOAD_BRANDFILEPREFIX", 's1_j2me.xyz_');                                                // leave last "_" synbol at the end, otherwise it will cause [JSTORE_UPLOAD_BRANDFILEPREFIX][HASH]... instead of space
define("JSTORE_UPLOAD_THUMBDIR_PREFIX", '/uploads/thumb_');                                             // Change it in the same time with constant below (Not in production if you have some data in DB)
define("JSTORE_UPLOAD_THUMB_PREFIX", 'thumb_');                                                         // Change it in the same time with constant above (Not in production if you have some data in DB)
define("JSTORE_UPLOAD_AVATARDIR_PREFIX", '/uploads/avatar_');                                           // Change it in the same time with constant below (Not in production if you have some data in DB)
define("JSTORE_UPLOAD_AVATAR_PREFIX", 'avatar_');                                                       // Change it in the same time with constant above (Not in production if you have some data in DB)
define("JSTORE_UPLOAD_SCREENSHOT_PREFIX", 'screenshot_');                                               // By default, screenshot_
define("JSTORE_WEB_UPLOAD_DIR", 'https://j2me.xyz/uploads/');                                           // Dependent on above constants (Not change in production if you have some data in DB)
define("JSTORE_WEB_UPLOAD_SHORT_DIR", 'uploads/');                                                      // Dependent on above constants (Not change in production if you have some data in DB)
define("JSTORE_WEB_MOBILE_SHORT_DIR", 'mobile/');                                                       // Dependent on above constants (Not change in production if you have some data in DB)
define("JSTORE_API_URL", 'https://api.j2me.xyz');                                                       // Alternative way is using JSTORE_URL/api
define("JSTORE_CAPTCHA_FONT_URL", '/var/www/html/jstore/mobile/captchacode.otf');                       // Change it with YOUR dir.
//---------------------------------------------------------------------------------------------------------DB variables-------------------------------------------------------------------------------------------------------------
define("JSTORE_DBA_HOST", 'localhost');                                                                 // Usually 'localhost'. If you have external MySQL DB server, use it's IP.
define("JSTORE_DBA_PORT", '3306');                                                                      // Usually '3306'. If you have external MySQL DB server, use it's port.
define("JSTORE_DBA_USER", 'root');                                                                      // Usually 'root'. If you have preconfigured MySQL DB server, use your credentials.
define("JSTORE_DBA_PASS", '');                                                                          // Usually '' (not recommended). If you have preconfigured MySQL DB server, use your credentials.
define("JSTORE_DBA_DBSE", 'snowbear_data');                                                             // Usually 'snowbear_data' (assigned by JStore GitHub repo). If you have preconfigured MySQL DB server, use your credentials.
//----------------------------------------------------------------------------------------------------------2FA variables-----------------------------------------------------------------------------------------------------------
define("JSTORE_TOTP_ORGANISATION_KEY", 'Your Organisation - TOTP');                                     // Do not change if you have already registered users. Otherwise, regenerate all TOTPs with users notification.
//----------------------------------------------------------------------------------------------------------Mail variables----------------------------------------------------------------------------------------------------------
define("MAIL_HOST", 'ssl://smtp.mailserver.ru');
define("MAIL_PORT", '465');
define("MAIL_PROTO", 'SSL');
define("MAIL_ENCODING", 'UTF-8');
define("ADMIN_MAIL", 'verify@example.com');
define("ADMIN_APPPASSWORD", 'PASSWORD');
//----------------------------------------------------------------------------------------------------------Other variables---------------------------------------------------------------------------------------------------------
define("TELEGRAM_BOT_SECRET", 'xxxxxxxxxx:xxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxx');                        // Your credentials from @BotFather
define("TELEGRAM_CHANNEL_ID", 'xxxxxxxxxxxxxx');                                                        // Data from @getidsbot
define("TELEGRAM_CHANNEL_RESERVE_ID", 'xxxxxxxxxxxxxx');                                                // Reserved data from @getidsbot

define("GITHUB_CLIENT_ID", 'xxxxxxxxxxxxxxxxxxxx');                                                     // Your credentials from Github Developer App profile
define("GITHUB_SECRET", 'xxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxx');                                    // Your credentials from Github Developer App profile
define("GITHUB_CALLBACK", 'https://j2me.xyz/github_callback.php');                                      // Change it cautiously

define("GOOGLE_CLIENT_ID", 'xxxxxxxxxxxx-yyyyyyyyyyyyyyyyyyyyyyyyyyyyyyyy.apps.googleusercontent.com'); // Your credentials from Google Console
define("GOOGLE_SECRET", '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');                                         // Your credentials from Google Console
define('GOOGLE_CALLBACK', 'https://j2me.xyz/callback.php');                                             // Change it cautiously

define("VIRUSTOTAL_API_KEY", 'xxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxx');       // Your credentials from Virustotal profile

define("MINECRAFT_SRVMANAGER_HOST", 'mc.example.com');                                                  // RCON HOST for SRVManager
define("MINECRAFT_SRVMANAGER_PORT", '25565');                                                           // RCON PORT for SRVManager
define("MINECRAFT_SRVMANAGER_PASS", 'PASSWORD');                                                        // RCON PASS for SRVManager

define("ADVERTISEMENT_URL", 'http://promo.old-web.com/uploads/a7d601af1480652a75834a3ee37bd0f1.gif');   // Advert on the top of the website. 

define("DONATE_URL_IFRAME_1", 'https://widget.qiwi.com/widgets/big-widget-728x200?publicKey=48e7qUxn9T7RyYE1MVZswX1FRSbE6iyCj2gCRwwF3Dnh5XrasNTx3BGPiMsyXQFNKQhvukniQG8RTVhYm3iP48fPcEvBXDF88sGdXMCy4UjbDF96rUgvh4be6awmug9mGhHz19wBiFHvW71byftUFAoxe2pjJ62zBDGaxv4rsrwRfTnH52ugjHAdf1jgP&noCache=true');
define("DONATE_URL_IFRAME_2", 'https://widget.donatepay.ru/widgets/page/9ea57adf4b149bea652c2d01ab2bc67049e2d2bf4445c7e6660d0fd83036131a?widget_id=5365664&sum=200');