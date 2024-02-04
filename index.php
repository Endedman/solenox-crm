<?php
require_once $_SERVER['DOCUMENT_ROOT'] . "init.php";
if (isset($_SESSION["user_id"])) {
  $userId = $_SESSION["user_id"]; // Получите ID пользователя из сессии
  if (!empty($_GET['id'])) {
    $userRank = $user->getUserRank($_GET['id']);
  } else {
    $userRank = $user->getUserRank($userId);
  }
  if (isset($_COOKIE["username"])) {
    $username = $_COOKIE["username"];
  }
} else {
  $userId = 0;
}
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST["login"])) {
  $usernameOrEmail = $_POST["username_or_email"];
  $password = $_POST["password"];
  $totpCode = $_POST["totp_code"]; // Получаем TOTP-код из формы
  $loginResult = $user->login($usernameOrEmail, $password);
  if (is_array($loginResult) && $loginResult["success"]) {
    // Проверяем TOTP-код
    $userId = $loginResult["user"]["id"];
    $userSecret = $user->getSecretByUserId($userId);
    $authenticator = new PHPGangsta_GoogleAuthenticator();
    $checkResult = $authenticator->verifyCode($userSecret, $totpCode, 2); // "2" - допуск времени
    if ($checkResult) {
      $loginMessage = "Login successful!";
      setcookie("username", $loginResult["user"]["username"], time() + 3600, "/");
      $_SESSION["user_id"] = $loginResult["user"]["id"];
      // After successful login
      $token = Token::generateToken();
      $user->updateToken($userId, $token);
    } else {
      $loginError = "Invalid TOTP code.";
    }
  } else {
    $loginError = $loginResult["message"] ?? "Login failed.";
  }
}

if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST["register"])) {
  $username = $_POST["username"];
  $email = $_POST["email"];
  $password = $_POST["password"];
  $confirmPassword = $_POST["confirm_password"];
  $captcha = $_POST["captcha"];

  if ($password !== $confirmPassword) {
    $registerError = "Passwords do not match.";
  } else {
    $registrationResult = $user->register($username, $email, $password, $confirmPassword, 0);

    if ($registrationResult["success"]) {
      // Зарегистрирован успешно, обработка данных для генерации QR-кода
      $registerMessage = $registrationResult["message"];
      $secret = $registrationResult["secret"];
      // Создание экземпляра GoogleAuthenticator
      $authenticator = new PHPGangsta_GoogleAuthenticator();
      $qrCodeUrl = $authenticator->getQRCodeGoogleUrl(JSTORE_TOTP_ORGANISATION_KEY, $email, $secret);
    } else {
      // Ошибка регистрации, вывод сообщения об ошибке
      $registerError = $registrationResult["message"];
    }
  }
}
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST["change_password"])) {
  if (isset($_SESSION["user_id"])) {
    $userId = $_SESSION["user_id"];
    $oldPassword = $_POST["old_password"];
    $newPassword = $_POST["new_password"];
    $confirmNewPassword = $_POST["confirm_new_password"];

    // Call the changePassword method and process the result
    $changePasswordResult = $user->changePassword($userId, $oldPassword, $newPassword, $confirmNewPassword);

    if (strpos($changePasswordResult, "successful") !== false) {
      $changePasswordMessage = $changePasswordResult;
    } else {
      $changePasswordError = $changePasswordResult;
    }
  } else {
    $changePasswordError = "You must be logged in to change your password.";
  }
}
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST["change_avatar"])) {
  if (isset($_SESSION["user_id"])) {
    $userId = $_SESSION["user_id"];

    // Check if file was uploaded without errors
    if (isset($_FILES["avatar"]) && $_FILES["avatar"]["error"] == 0) {
      $file_name = $_FILES["avatar"]["name"];
      $file_type = $_FILES["avatar"]["type"];
      $file_temp = $_FILES["avatar"]["tmp_name"];
      $file_size = $_FILES["avatar"]["size"];

      // Verify file extension
      $allowed = array("jpg" => "image/jpg", "jpeg" => "image/jpeg", "gif" => "image/gif", "png" => "image/png");
      $ext = pathinfo($file_name, PATHINFO_EXTENSION);

      if (!array_key_exists($ext, $allowed)) {
        $changeAvatarError = "Error: Please select a valid file format.";
      } else {
        // image size validation
        $maxsize = 2 * 1024 * 1024;  // 2MB limit

        if ($file_size > $maxsize) {
          $changeAvatarError = "Image size is larger than the allowable limit.";
        } else {
          // move the file to the specified location
          $avatar_path = JSTORE_UPLOAD_DIR . JSTORE_UPLOAD_AVATAR_PREFIX . md5($file_name);
          $avatar_path_db = JSTORE_UPLOAD_AVATARDIR_PREFIX . md5($file_name);

          if (move_uploaded_file($file_temp, $avatar_path)) {
            // update the user's profile with the new avatar path
            $changeAvatarResult = $user->updateAvatar($userId, $avatar_path_db);

            if (strpos($changeAvatarResult, "successful") !== false) {
              $changeAvatarMessage = $changeAvatarResult;
            } else {
              $changeAvatarError = $changeAvatarResult;
            }
          } else {
            $changeAvatarError = "Failed to upload your avatar. Please try again.";
          }
        }
      }
    } else {
      $changeAvatarError = "Error: " . $_FILES["avatar"]["error"];
    }
  } else {
    $changeAvatarError = "You must be logged in to change your avatar.";
  }
}
// Handle login
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST["get_token"])) {
  $password = $_POST["password"];
  if (password_verify($password, $user->getPassById($userId))) {
    $tokenStr = file_get_contents(JSTORE_API_URL . "/getToken?username=" . $user->getUsernameById($userId) . "&password=" . $password);
  } else {
    $getTokenMsg = "EADB error.";
  }
}
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST["change_email"])) {
  // Check if user is authenticated (logged in)
  if (isset($_SESSION["user_id"])) {
    $userId = $_SESSION["user_id"];
    $newEmail = $_POST["new_email"];

    // Call the changeEmail method and process the result
    $changeEmailResult = $user->changeEmail($userId, $newEmail);

    if (strpos($changeEmailResult, "request sent") !== false) {
      $changeEmailMessage = $changeEmailResult;
    } else {
      $changeEmailError = $changeEmailResult;
    }
  } else {
    $changeEmailError = "You must be logged in to request an email change.";
  }
}
if ($_SERVER["REQUEST_METHOD"] == "POST" && (isset($_POST["increase_rank"]) || isset($_POST["decrease_rank"]))) {
  $newRank = $user->getUserRank($userId); // Get the current user rank
  $changedByUserId = $_SESSION["user_id"];

  if (isset($_POST["increase_rank"])) {
    $newRank += 1;
  } elseif (isset($_POST["decrease_rank"])) {
    $newRank -= 1;
  }

  $userIdToUpdate = isset($_GET['id']) ? $_GET['id'] : $userId;

  if ($user->updateUserRank($userIdToUpdate, $newRank, $changedByUserId)) {
    // Rank updated successfully
    $userRank = $newRank;
  } else {
    // Rank change already made or other error, show an error message
    $rankUpdateError = "ERR: Rank change already made or invalid request.";
  }
}


$profile = null; // Инициализируем переменную для хранения профиля

// Если параметр id или user передан и не пустой, пытаемся получить профиль по id или username
if (!empty($_GET['id'])) {
  $userId = $_GET['id'];
  $profile = $user->getUserProfileById($userId);
} elseif (isset($_SESSION['user_id'])) {
  // Если не переданы параметры id и user, и есть активная сессия, получаем профиль залогиненного пользователя
  $profile = $user->getUserProfileById($_SESSION['user_id']);
}
?>
<!DOCTYPE html>
<html>

<head>
  <title>JStore</title>
  <link rel="stylesheet" href="static/css/xp.css">
  <link rel="stylesheet" type="text/css" href="static/css/style.css">
  <link rel="stylesheet" type="text/css" href="static/css/auth-buttons.css">

  <script src="https://dir.by/example_lib/jquery/jquery-3.3.1.min.js"></script>
  <script src="https://dir.by/example_lib/jquery_ui/jquery-ui-1.12.1/jquery-ui.min.js"></script>
  <meta name="viewport" content="width=device-width, initial-scale=1.0">

  <!-- подключаем стили jQuery UI -->
  <link rel="stylesheet" href="https://dir.by/example_lib/jquery_ui/jquery-ui-1.12.1/jquery-ui.min.css">
</head>

<body>
  <div class="window" style="max-width: 75vw; margin: 0 auto;">
    <div class="title-bar">
      <div class="title-bar-text"><img src="static/img/png/channels-0.png" alt="off"
          style="width: 10px;" />&nbsp;&nbsp;Advertisement</div>
      <div class="title-bar-controls">
        <button aria-label="Minimize"></button>
        <button aria-label="Maximize"></button>
        <button aria-label="Close" class="closebtn"></button>
      </div>
    </div>
    <div class="window-body" style="max-height: 200px; overflow: hidden;">
      <img src="<?= ADVERTISEMENT_URL ?>" style="
    width: 100%;
">
    </div>
  </div>
  <div class="window" style="max-width: 75vw; margin: 0px auto 0;">
    <div class="title-bar">
      <div class="title-bar-text"><img src="static/img/png/msie1-3.png" alt=""
          style="width: 13px;" />&nbsp;&nbsp;Internet Explorer</div>
    </div>
    <div class="window-body">
      <?= JSTORE_SITE_DESC ?>
      <a href='<?= JSTORE_RADIO_POINT ?>' onclick='playMusic(event)'>Radio</a>
      <section class="tabs">
        <menu role="tablist" aria-label="Sample Tabs">
          <button role="tab" aria-controls="Main">Main</button>
          <button role="tab" aria-controls="Files" aria-selected="true">Files</button>
          <button role="tab" aria-controls="Links">Links</button>
          <button role="tab" aria-controls="Donation">Donation</button>
          <?php if (isset($_COOKIE['username'])) { ?>
            <button role="tab" aria-label="Profile" aria-controls="Profile">
              <?php echo $_COOKIE['username']; ?>
            </button>
          <?php } else { ?>
            <button aria-label="Login" id="openbtn">Login</button>
          <?php } ?>
        </menu>
        <!-- the tab content -->
        <article role="tabpanel" hidden id="Main">
          <?= JSTORE_MAIN_TAB_DESC ?>
        </article>
        <article role="tabpanel" id="Files">
          <h4>Files</h4>
          <?php include JSTORE_DIR . "/admin/space.php" ?>
          <ul style="display: flex; text-align: center" class="tree-view cats" id="Categories">
            <?php require_once JSTORE_DIR . "categories.php"; ?>
          </ul>
          <br>
          <ul class="tree-view" id="ContentWindow">
          </ul>
          <br>
          <ul class="tree-view" id="AppWindow"></ul>
          <div id="player" style="display: none;">
            <audio id="audio" controls></audio>
          </div>
        </article>
        <article role="tabpanel" hidden id="Links">
          <ul class="tree-view">
            <li>
              <?= JSTORE_BRAND_NAME ?> site
              <ul style="display: block">
                <li><a href="<?= JSTORE_URL ?>">
                    <?= JSTORE_BRAND_NAME ?>
                  </a></li>
                <li><a href="<?= JSTORE_FORUM_URL ?>">Community</a></li>
                <li><a href="<?= JSTORE_STAFF_MAIL_URL ?>">Mail</a></li>
              </ul>
              Social networks:
              <ul>
                <li><a href="<?= JSTORE_TELEGRAM_CHANNEL_URL ?>">Telegram</a></li>
              </ul>
            </li>
          </ul>
        </article>
        <article role="tabpanel" hidden id="Donation">
          <h4>Donation</h4>
          <p>Currently we supporting:</p>
          <iframe src="<?= DONATE_URL_IFRAME_1 ?>" width="728" height="200" allowTransparency="true" scrolling="no"
            frameBorder="0"></iframe>
          <iframe src="<?= DONATE_URL_IFRAME_2 ?>" width="510" height="220" frameBorder="0"></iframe>
        </article>
        <article role="tabpanel" hidden id="Profile">
          <menu role="tablist" aria-label="ProfileSection">
            <button role="tab" aria-selected="true" aria-controls="ProfileInfo">Profile</button>
            <button role="tab" aria-controls="API">API (?)<span class="tab-tooltip">Client-side requests via 3rd-party
                apps</span></button>
            <button role="tab" aria-controls="TOTPInfo">Manage Two-Step Auth</button>
            <button role="tab" aria-controls="ChangeAvatar">Change avatar</button>
            <button role="tab" aria-controls="ChangePassword">Change password</button>
            <button role="tab" aria-controls="ChangeEmail">Change email</button>
          </menu>
          <article role="tabpanel" id="ProfileInfo">
            <ul class="tree-view">
              <li>
                Profile
                <ul style="display: block;">
                  <?php if ($profile) { ?>
                    <li>Name:
                      <?php echo $profile['username']; ?>
                    </li>
                    <li>Rank:
                      <?php echo $user->mapRankToText($profile['rank']) . " (" . ($profile['rank']) . ")"; ?>
                      <?php if (empty($_GET['id'])) { ?><button id="recalculateRankButton">Recalculate Rank</button>
                      <?php } ?>
                      <p>Note that manual user ranking can work not as expected. But you still can up your rank by using
                        Activities (uploading files, creating news, chatting, etc).</p>
                      <form method="post" action="">
                        <button type="submit" name="increase_rank">↑</button>
                        <button type="submit" name="decrease_rank">↓</button>
                      </form>
                    <li>Role:
                      <?php echo $user->mapRoleToText($profile['role']) . " (" . ($profile['role']) . ")"; ?>
                    </li>
                </li>
              <?php } else { ?>
                <li>User not found.</li>
              <?php } ?>
            </ul>
            </li>
            </ul>
          </article>
          <article role="tabpanel" id="API">
            <h4>Your API token</h4>
            <fieldset style="display: block;">
              <legend>Fields</legend>
              <p>Do not share token with others or you will lose account!</p>
              <?php if (isset($tokenStr)) {
                echo $tokenStr;
              } else { ?>
                <form method="post" action="">
                  <?php if (isset($getTokenMsg)) {
                    echo $getTokenMsg;
                  } ?>
                  <input type="password" name="password" id="password" placeholder="password" required>
                  <button type="submit" name="get_token">Get token</button>
                </form>
              </fieldset>
              <?php
              }
              ?>
          </article>
          <article role="tabpanel" id="ChangeAvatar">
            <h4>Change Avatar</h4>
            <fieldset style="align-items: center;">
              <legend>Fields</legend>
              <?php if (isset($changeAvatarError)) {
                echo "<p style='color: red;'>$changeAvatarError</p>";
              } ?>
              <?php if (isset($changeAvatarMessage)) {
                echo "<p style='color: green;'>$changeAvatarMessage</p>";
              } ?>
              <form method="post" action="index.php" enctype="multipart/form-data" style="width: 30%;">
                <input type="file" name="avatar" required><br>
                <button type="submit" name="change_avatar">Change Avatar</button>
              </form>
              <?php
              $userProfile = $user->getUserProfileById($userId);
              if ($userProfile && !empty($userProfile['avatar'])) {
                $avatarUrl = $userProfile['avatar'];
                echo '<div class="image-wrapper" style="width: 100%;border: aliceblue 4px solid;height: 50px;display: flex;margin-left: 26px;align-items: center;"><img src="' . htmlspecialchars($avatarUrl) . '" alt="User Avatar" style="width: 50px; height: 50px;"><p>Yea, this is your ava (avatar) =)</p></div>';
              } ?>
            </fieldset>
          </article>
          <article role="tabpanel" id="TOTPInfo">
            <h4>Setup Two-Factor Authentication (2FA)</h4>
            <fieldset style="display:block">
              <legend>Information</legend>
              <p>To enhance your account's security, we recommend setting up two-factor authentication (2FA). You will
                need a TOTP app to generate one-time codes.</p>
              <?php
              $userSecret = $user->getSecretByUserId($userId);
              if ($userSecret) {
                // Генерируем URL для QR-кода
                $authenticator = new PHPGangsta_GoogleAuthenticator();
                $websiteTitle = 'My organisation'; // Используйте название вашего сайта или приложения
                $qrCodeUrl = $authenticator->getQRCodeGoogleUrl($websiteTitle, $userSecret, $userId);
                // Отображаем QR-код и секрет
                echo "<img src='" . htmlspecialchars($qrCodeUrl) . "' alt='TOTP QR Code'>";
                echo "<p>Your TOTP secret: <code>" . htmlspecialchars($userSecret) . "</code></p>";
              } else {
                echo "<p>There was an error retrieving your TOTP secret.</p>";
              } ?>
              <p><strong>Recommended TOTP Apps:</strong></p>
              <ul style="display:block">
                <li><a href="https://play.google.com/store/apps/details?id=com.google.android.apps.authenticator2"
                    target="_blank">Google Authenticator for Android</a></li>
                <li><a href="https://apps.apple.com/us/app/google-authenticator/id388497605" target="_blank">Google
                    Authenticator for iOS</a></li>
                <li><a href="https://sourceforge.net/projects/j2megoogleauth/" target="_blank">J2ME Google
                    Authenticator</a> (for devices supporting Java ME)</li>
                <li><a href="https://www.microsoft.com/en-us/p/microsoft-authenticator/9nblggh08h54"
                    target="_blank">Microsoft Authenticator for Windows</a></li>
              </ul>
              <p>Don't forget to save your backup codes. They will help you access your account if you lose your device.
              </p>
              <a href="#" class="action-link">Learn more about 2FA</a>
            </fieldset>
          </article>
          <article role="tabpanel" id="ChangePassword">
            <h4>Change Password</h4>
            <fieldset>
              <legend>Fields</legend>
              <?php if (isset($changePasswordError)) {
                echo "<p style='color: red;'>$changePasswordError</p>";
              } ?>
              <?php if (isset($changePasswordMessage)) {
                echo "<p style='color: green;'>$changePasswordMessage</p>";
              } ?>
              <form method="post" action="index.php" style="width: 30%;">
                <label for="old_password">Old password</label><br>
                <input type="password" name="old_password" placeholder="Old Password" required><br>
                <label for="new_password">New password</label><br>
                <input type="password" name="new_password" placeholder="New Password" required><br>
                <label for="confirm_new_password">Confirm new password</label><br>
                <input type="password" name="confirm_new_password" placeholder="Confirm New Password" required><br>
                <button type="submit" name="change_password">Change Password</button>
              </form>
            </fieldset>
          </article>
          <article role="tabpanel" id="ChangeEmail">
            <menu role="tablist" aria-label="ProfileSection">
              <button role="tab" aria-selected="true" aria-controls="RTForm">Request token [email]</button>
              <button role="tab" aria-controls="VEForm">Verify email [email+token]</button>
            </menu>
            <article role="tabpanel" id="RTForm">
              <h4>Request Email Change</h4>
              <fieldset>
                <legend>Fields</legend>
                <?php if (isset($changeEmailError)) {
                  echo "<p style='color: red;'>$changeEmailError</p>";
                } ?>
                <?php if (isset($changeEmailMessage)) {
                  echo "<p style='color: green;'>$changeEmailMessage</p>";
                } ?>
                <form method="post" action="index.php" style="width: 30%;">
                  <label for="email">Email:</label><br>
                  <input type="email" name="new_email" placeholder="New Email" required><br>
                  <button type="submit" name="change_email">Request Email Change</button>
                </form>
              </fieldset>
            </article>
            <article role="tabpanel" id="VEForm">

              <h4>Verify</h4>
              <fieldset>
                <legend>Fields</legend>
                <form id="verificationForm" style="width: 30%;">
                  <label for="email">Email:</label><br>
                  <input type="email" id="email" name="email" required><br>
                  <label for="token">Token</label><br>
                  <input type="text" id="token" name="token" required><br>
                  <button type="submit">Verify ></button>
                </form>
                <div id="result"></div>
              </fieldset>
            </article>
          </article>
        </article>
      </section>
    </div>
    <div class="status-bar">
      <p class="status-bar-field">http://j2me.xyz</p>
      <p class="status-bar-field">Main</p>
      <p class="status-bar-field"><img src="static/img/png/no2-1.png" alt="off" style="width: 10px;" />&nbsp;&nbsp;
        <?= JSTORE_ENGINE_VERSION ?>
      </p>
      <p class="status-bar-field"><progress></progress></p>
    </div>
  </div>
  <div class="window modal" draggable="true" id="modalwindow" style="width: 340px">
    <div class="title-bar" id="modalwindowheader">
      <div class="title-bar-text"><img src="static/img/png/console_prompt-0.png" alt="off"
          style="width: 10px;" />&nbsp;&nbsp;Alert</div>
      <div class="title-bar-controls">
        <button aria-label="Minimize"></button>
        <button aria-label="Maximize"></button>
        <button aria-label="Close" class="closebtn"></button>
      </div>
    </div>
    <div class="window-body" draggable="false">
      <section class="tabs">
        <menu role="tablist" aria-label="Sample Tabs">
          <button role="tab" aria-selected="true" aria-controls="Login">Login</button>
          <button role="tab" aria-controls="Register">Register</button>
        </menu>
        <article role="tabpanel" id="Login">
          <h4>Login</h4>
          <?php if (isset($loginError)): ?>
            <p style="color: red;">
              <?= $loginError ?>
            </p>
          <?php endif; ?>
          <?php if (isset($loginMessage)): ?>
            <p style="color: green;">
              <?= $loginMessage ?>
            </p>
          <?php endif; ?>
          <form action="index.php" method="POST">
            <div class="field-row-stacked" style="width: 100%">
              <label for="loginfield">Login/e-mail</label>
              <input id="loginfield" name="username_or_email" type="text" required>
            </div>
            <div class="field-row-stacked" style="width: 100%">
              <label for="passfield">Password</label>
              <input id="passfield" name="password" type="password" required>
            </div>
            <div class="field-row-stacked" style="width: 100%">
              <label for="totpfield">TOTP Code</label>
              <input id="totpfield" name="totp_code" type="text" required>
            </div>
            <section class="field-row" style="display: grid">
              <button type="submit" name="login" style="margin: 0;">Login ></button>
            </section>
          </form>
          <a href="/social_login.php?partner=google">
            <button class="gsi-material-button" style="width:295px">
              <div class="gsi-material-button-state"></div>
              <div class="gsi-material-button-content-wrapper">
                <div class="gsi-material-button-icon">
                  <svg version="1.1" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 48 48"
                    xmlns:xlink="http://www.w3.org/1999/xlink" style="display: block;">
                    <path fill="#EA4335"
                      d="M24 9.5c3.54 0 6.71 1.22 9.21 3.6l6.85-6.85C35.9 2.38 30.47 0 24 0 14.62 0 6.51 5.38 2.56 13.22l7.98 6.19C12.43 13.72 17.74 9.5 24 9.5z">
                    </path>
                    <path fill="#4285F4"
                      d="M46.98 24.55c0-1.57-.15-3.09-.38-4.55H24v9.02h12.94c-.58 2.96-2.26 5.48-4.78 7.18l7.73 6c4.51-4.18 7.09-10.36 7.09-17.65z">
                    </path>
                    <path fill="#FBBC05"
                      d="M10.53 28.59c-.48-1.45-.76-2.99-.76-4.59s.27-3.14.76-4.59l-7.98-6.19C.92 16.46 0 20.12 0 24c0 3.88.92 7.54 2.56 10.78l7.97-6.19z">
                    </path>
                    <path fill="#34A853"
                      d="M24 48c6.48 0 11.93-2.13 15.89-5.81l-7.73-6c-2.15 1.45-4.92 2.3-8.16 2.3-6.26 0-11.57-4.22-13.47-9.91l-7.98 6.19C6.51 42.62 14.62 48 24 48z">
                    </path>
                    <path fill="none" d="M0 0h48v48H0z"></path>
                  </svg>
                </div>
                <span class="gsi-material-button-contents">Sign in with Google</span>
                <span style="display: none;">Sign in with Google</span>
              </div>
            </button>
          </a>
          <a class="btn-auth btn-github large" style="width:253px" href="/social_login.php?partner=github">
            Continue with <b>GH</b>
          </a>
        </article>
        <article role="tabpanel" hidden id="Register">
          <h4>Register</h4>
          <?php if (isset($registerMessage)): ?>
            <p>
              <?= $registerMessage ?>
            </p>
            <p>Please scan this QR by any TOTP app. If you will lose TOTP token, you will lose your account. Using 2FA is
              mandatory.</p>
            <img src="<?= htmlspecialchars($qrCodeUrl) ?>">
            <p>Or, use Secret code:
              <?php echo $secret; ?>
            </p>
          <?php elseif (isset($registerError)): ?>
            <p>
              <?= $registerError ?>
            </p>
          <?php endif; ?>
          <form action="index.php" method="POST">
            <div class="field-row-stacked" style="width: 100%">
              <label for="loginfield">Login</label>
              <input id="loginfield" name="username" type="text" />
            </div>
            <div class="field-row-stacked" style="width: 100%">
              <label for="e-mail">E-Mail address</label>
              <input id="e-mail" name="email" type="email" />
            </div>
            <div class="field-row-stacked" style="width: 100%">
              <label for="passfield">Password</label>
              <input id="passfield" name="password" type="password" />
            </div>
            <div class="field-row-stacked" style="width: 100%">
              <label for="passfield">Password confirmation</label>
              <input id="passfield" name="confirm_password" type="password" />
            </div>
            <section class="field-row" style="display: grid">
              <button type="submit" name="login" style="margin: 0;">Login ></button>
            </section>
          </form>
          <a href="/social_login.php?partner=google">
            <button class="gsi-material-button" style="width:295px">
              <div class="gsi-material-button-state"></div>
              <div class="gsi-material-button-content-wrapper">
                <div class="gsi-material-button-icon">
                  <svg version="1.1" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 48 48"
                    xmlns:xlink="http://www.w3.org/1999/xlink" style="display: block;">
                    <path fill="#EA4335"
                      d="M24 9.5c3.54 0 6.71 1.22 9.21 3.6l6.85-6.85C35.9 2.38 30.47 0 24 0 14.62 0 6.51 5.38 2.56 13.22l7.98 6.19C12.43 13.72 17.74 9.5 24 9.5z">
                    </path>
                    <path fill="#4285F4"
                      d="M46.98 24.55c0-1.57-.15-3.09-.38-4.55H24v9.02h12.94c-.58 2.96-2.26 5.48-4.78 7.18l7.73 6c4.51-4.18 7.09-10.36 7.09-17.65z">
                    </path>
                    <path fill="#FBBC05"
                      d="M10.53 28.59c-.48-1.45-.76-2.99-.76-4.59s.27-3.14.76-4.59l-7.98-6.19C.92 16.46 0 20.12 0 24c0 3.88.92 7.54 2.56 10.78l7.97-6.19z">
                    </path>
                    <path fill="#34A853"
                      d="M24 48c6.48 0 11.93-2.13 15.89-5.81l-7.73-6c-2.15 1.45-4.92 2.3-8.16 2.3-6.26 0-11.57-4.22-13.47-9.91l-7.98 6.19C6.51 42.62 14.62 48 24 48z">
                    </path>
                    <path fill="none" d="M0 0h48v48H0z"></path>
                  </svg>
                </div>
                <span class="gsi-material-button-contents">Sign in with Google</span>
                <span style="display: none;">Sign in with Google</span>
              </div>
            </button>
          </a>
          <a class="btn-auth btn-github large" style="width:253px" href="/social_login.php?partner=github">
            Continue with <b>GH</b>
          </a>
        </article>
    </div>
  </div>
  <div class="window" style="max-width: 75vw; margin: 0 auto;">
    <div class="title-bar">
      <div class="title-bar-text"><img src="static/img/png/channels-0.png" alt="off"
          style="width: 10px;" />&nbsp;&nbsp;Chat</div>
      <div class="title-bar-controls">
        <button aria-label="Minimize"></button>
        <button aria-label="Maximize"></button>
        <button aria-label="Close" class="closebtn"></button>
      </div>
    </div>
    <div class="window-body" style="max-height: 200px; overflow: scroll;">
      <div id="chat">
        <?php if (isset($_COOKIE['username'])) { ?>
          <input type="text" id="message" placeholder="Type your message...">
          <button id="send">Send</button>
        <?php } else {
          echo "<h4>In order to write messages, please login. Guest access is disabled <i>27 January, 2024</i></h4>";
        } ?>
        <div id="chat-window">
          <?php foreach ($messages as $message) { ?>
            <div>
              <strong>
                <?php echo $message['username']; ?>:
              </strong>
              <?php echo $message['message']; ?>
              <?php if ($message['parent_message_id'] !== null) { ?>
                <button class="reply-btn" data-id="<?php echo $message['parent_message_id']; ?>">Reply</button>
              <?php } ?>
            </div>
          <?php } ?>
        </div>
      </div>
    </div>
  </div>
  <div class="window" style="max-width: 75vw; margin: 0px auto 40px;">
    <div class="title-bar">
      <div class="title-bar-text"><img src="static/img/png/channels-0.png" alt="off"
          style="width: 10px;" />&nbsp;&nbsp;RSS Feed</div>
      <div class="title-bar-controls">
        <button aria-label="Minimize"></button>
        <button aria-label="Maximize"></button>
        <button aria-label="Close" class="closebtn"></button>
      </div>
    </div>
    <div class="window-body" style="max-height: 100px; overflow: scroll;">
      <?php foreach ($posts as $post) { ?>
        <div class="news-post">
          <a href="news.php?news_id=<?php echo $post['id']; ?>" style="margin: 0;">
            <p>
              <?php echo $post['title']; ?> -
              Date:
              <?php echo $post['date']; ?> -
              Author:
              <?php echo $user->getUsernameById($post['author_id']); ?>
            </p>
          </a>
        </div>
      <?php } ?>
    </div>
  </div>
  <div class="footer" style="align-items: center; position: sticky;">
    <button style="
    display: flex;
    align-items: center;
    height: 25px;
    width: 40px;
    margin: 0 0 0 2px;
    text-align: left;
    font-weight: bold;
"><img src="static/img/png/windows-0.png" alt="off" style="width: 16px;" />&nbsp;&nbsp;Start</button>
  </div>
  <div id="contextMenu" class="context-menu" style="display:none">
    <ul>
      <li><a href="#">About</a></li>
      <li><a href="#">J2ME.xyz template based</a></li>
    </ul>
  </div>
  <script src="/static/js/chat.js" defer></script>
  <script src="/static/js/category_loader.js" defer></script>
  <script src="/static/js/email_verification.js" defer></script>
  <script src="/static/js/rank_recalculator.js" defer></script>
  <script src="/static/js/context_menu.js" defer></script>
  <script src="/static/js/tabs_control.js" defer></script>
  <script src="https://unpkg.com/webamp@1.4.2/built/webamp.bundle.min.js"></script>
  <script src="https://unpkg.com/butterchurn@2.6.7/lib/butterchurn.min.js"></script>
  <script src="https://unpkg.com/butterchurn-presets@2.4.7/lib/butterchurnPresets.min.js"></script>
  <script src="/static/js/webamp_setup.js" defer></script>
  <script src="static/js/modal.js"></script>
</body>

</html>