<?php
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);
session_start();
require_once $_SERVER['DOCUMENT_ROOT'] . "classes/Database.php";
require_once $_SERVER['DOCUMENT_ROOT'] . "classes/User.php";
require_once $_SERVER['DOCUMENT_ROOT'] . "classes/News.php";
require_once $_SERVER['DOCUMENT_ROOT'] . "classes/Chat.php";
require_once $_SERVER['DOCUMENT_ROOT'] . "classes/Token.php";
require_once $_SERVER['DOCUMENT_ROOT'] . "classes/NetCat.php";

$db = new Database();
$user = new User($db);
$news = new News($db);
$chat = new Chat($db);
$token = new Token();
$netCat = new NetCat($db);
$messages = $chat->getMessages();
$posts = $news->getAllNews();

$categories = $netCat->getCategoriesAndFiles();

if(isset($_SESSION["user_id"])) {
  $userId = $_SESSION["user_id"]; // Получите ID пользователя из сессии
  if(!empty($_GET['id'])) {
    $userRank = $user->getUserRank($_GET['id']);
  } else {
    $userRank = $user->getUserRank($userId);
  }
  if(isset($_COOKIE["username"])) {
  $username = $_COOKIE["username"];
  }
} else {
  $userId = 0;
}

// Handle login
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST["login"])) {
    $usernameOrEmail = $_POST["username_or_email"];
    $password = $_POST["password"];

    $loginResult = $user->login($usernameOrEmail, $password);

    if (is_array($loginResult) && $loginResult["success"]) {
        $loginMessage = "Login successful!";
        // Set a cookie with the username
        setcookie("username", $loginResult["user"]["username"], time() + 3600, "/");    
        $_SESSION["user_id"] = $loginResult["user"]["id"];
        // After successful login
        $token = Token::generateToken();
        $user->updateToken($userId, $token); // Implement this method in your User class

        // You can store user information in sessions, cookies, etc.
        // For example: $_SESSION["user"] = $loginResult["user"];
    } else {
        $loginError = is_string($loginResult) ? $loginResult : "Login failed.";
    }
}

if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST["register"])) {
    $username = $_POST["username"];
    $email = $_POST["email"];
    $password = $_POST["password"];
    $confirmPassword = $_POST["confirm_password"];

    if ($password !== $confirmPassword) {
        $registerError = "Passwords do not match.";
    } else {
        $registrationResult = $user->register($username, $email, $password, $confirmPassword, 0);

        if ($registrationResult === "Registration successful!") {
            $registerMessage = "Registration successful!";
        } else {
            $registerError = $registrationResult;
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
// if ($_SERVER["REQUEST_METHOD"] == "POST" && (isset($_POST["increase_rank"]) || isset($_POST["decrease_rank"]))) {
//     $newRank = $user->getUserRank($userId); // Get the current user rank
//     $changedByUserId = $_SESSION["user_id"];

//     if (isset($_POST["increase_rank"])) {
//         $newRank += 1;
//     } elseif (isset($_POST["decrease_rank"])) {
//         $newRank -= 1;
//     }

//     $userIdToUpdate = isset($_GET['id']) ? $_GET['id'] : $userId;

//     if ($user->updateUserRank($userIdToUpdate, $newRank, $changedByUserId)) {
//         // Rank updated successfully
//         $userRank = $newRank;
//     } else {
//         // Rank change already made or other error, show an error message
//         $rankUpdateError = "ERR: Rank change already made or invalid request.";
//     }
// }


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
    <link rel="stylesheet" href="static/css/98.css">
	<link rel="stylesheet" type="text/css" href="static/css/style.css">

	<script src="https://dir.by/example_lib/jquery/jquery-3.3.1.min.js"></script>
    <script src="https://dir.by/example_lib/jquery_ui/jquery-ui-1.12.1/jquery-ui.min.js"></script>

    <!-- подключаем стили jQuery UI -->
    <link rel="stylesheet" href="https://dir.by/example_lib/jquery_ui/jquery-ui-1.12.1/jquery-ui.min.css">
</head>
<body>
<div class="window" style="max-width: 75vw; margin: 30px auto 0;">
  <div class="title-bar">
    <div class="title-bar-text"><img src="static/img/png/msie1-3.png" alt="" style="width: 13px;" />&nbsp;&nbsp;Internet Explorer</div>
  </div>
  <div class="window-body">
<p>Welcome to J2ME.xyz (formerly LibreShare).</p>
<p>Here you can find all necessary info about J2ME.xyz.</p>
<section class="tabs">
  <menu role="tablist" aria-label="Sample Tabs">
    <button role="tab" aria-selected="true" aria-controls="Main">Main</button>
    <button role="tab" aria-controls="Files">FIles</button>
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
  <article role="tabpanel" id="Main">
    <h4>Who are we?</h4>
    <p>
      We are small organisation which collects J2ME apps. All apps are marked as abandonware, we have all rights to redistribute it =)
    </p>
    <p>
      If you want to donate, check please Donation tab.</a>
    </p>
  </article>
  <article role="tabpanel" hidden id="Files">
    <h4>Files</h4>
      <ul class="tree-view" id="Categories">
            <?php require_once "categories.php"; ?>
      </ul>
    <br>
      <ul class="tree-view"  id="ContentWindow"> 
    </ul>
  </article>
  <article role="tabpanel" hidden id="Links">
    <ul class="tree-view">
    <li>
      J2ME.xyz site
      <ul>
        <li><a href="http://j2me.xyz">J2ME.xyz</a></li>
        <li><a href="http://community.j2me.xyz">Community</a></li>
        <li><a href="http://mail.j2me.xyz">Mail</a></li>
      </ul>
      Social networks:
      <ul>
        <li><a href="https://t.me/javame_ch">Telegram</a></li>
      </ul>
    </li>
  </ul>
  </article>
  <article role="tabpanel" hidden id="Donation">
    <h4>Donation</h4>
    <p>Currently we supporting:</p>
  </article>
  <article role="tabpanel" hidden id="Profile">
    <?php $userRank = $user->getUserRank($userId); ?>
    <?php if (isset($rankUpdateError)) { ?>
      <p style="color: red;"><?php echo $rankUpdateError; ?></p>
    <?php } ?>
    <ul class="tree-view">
    <li>
      Profile
      <ul>
        <?php if ($profile) { ?>
        <li>Name: <?php echo $profile['username'];  ?></li>
        <li>Rank: <?php echo mapRankToText($profile['rank']). " (". ($profile['rank']) .")"; ?><?php if(empty($_GET['id'])) {?><button id="recalculateRankButton">Recalculate Rank</button><?php } ?>
        <p>Note that manual user ranking is disabled. But you still can up your rank by using Activities.</p>
<!--           <form method="post" action="">
            <button type="submit" name="increase_rank">↑</button>
            <button type="submit" name="decrease_rank">↓</button>
          </form> -->
          <?php } else { ?>
          <li>User not found.</li>
          <?php } ?>
        </li>
      </ul>
    </li>
  </ul>
    <h4>Your API token</h4>
    <p>Sorry, but viewing token here is disabled due to security reasons.</p>
    <p>Use https://snowbear-beta.j2me.xyz/api/getToken?username=&lt;USERNAME&gt;&password=&lt;PASSWORD&gt; for this</p>
    <h4>Change Password</h4>
    <?php if (isset($changePasswordError)) { echo "<p style='color: red;'>$changePasswordError</p>"; } ?>
    <?php if (isset($changePasswordMessage)) { echo "<p style='color: green;'>$changePasswordMessage</p>"; } ?>
    <form method="post" action="index.php">
        <input type="password" name="old_password" placeholder="Old Password" required>
        <input type="password" name="new_password" placeholder="New Password" required>
        <input type="password" name="confirm_new_password" placeholder="Confirm New Password" required><br>
        <button type="submit" name="change_password">Change Password</button>
    </form>
    <h4>Request Email Change</h4>
    <?php if (isset($changeEmailError)) { echo "<p style='color: red;'>$changeEmailError</p>"; } ?>
    <?php if (isset($changeEmailMessage)) { echo "<p style='color: green;'>$changeEmailMessage</p>"; } ?>
    <form method="post" action="index.php">
        <input type="email" name="new_email" placeholder="New Email" required><br>
        <button type="submit" name="change_email">Request Email Change</button>
    </form>
  </article>
</section>
  </div>
  <div class="status-bar">
    <p class="status-bar-field">http://j2me.xyz</p>
    <p class="status-bar-field">Main</p>
    <p class="status-bar-field"><img src="static/img/png/no2-1.png" alt="off" style="width: 10px;" />&nbsp;&nbsp;SmartScreen is off</p>
    <p class="status-bar-field"><progress></progress></p>
 </div>
</div>
<div class="window modal" draggable="true" id="modalwindow" style="width: 300px">
  <div class="title-bar" id="modalwindowheader">
    <div class="title-bar-text"><img src="static/img/png/console_prompt-0.png" alt="off" style="width: 10px;" />&nbsp;&nbsp;Alert</div>
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
    <?php if (isset($loginError)) { echo "<p style='color: red;'>$loginError</p>"; } ?>
    <?php if (isset($loginMessage)) { echo "<p style='color: green;'>$loginMessage</p>"; header("Location: /");} ?>
      	<form action="index.php" method="POST">
  			<div class="field-row-stacked" style="width: 100%">
  			  <label for="loginfield">Login/e-mail</label>
  			  <input id="loginfield" name="username_or_email" type="text" />
  			</div>
  			<div class="field-row-stacked" style="width: 100%">
  			  <label for="passfield">Password</label>
  			  <input id="passfield" name="password" type="password" />
  			</div>
  			<section class="field-row" style="justify-content: flex-end">
  		      <input type="submit" name="login"></button>
  		    </section>
  		</form>
    </article>
    <article role="tabpanel" hidden id="Register">
      <h4>Register</h4>
    <?php if (isset($registerError)) { ?>
        <p><?php echo $registerError; ?></p>
    <?php } elseif (isset($registerMessage)) { ?>
        <p><?php echo $registerMessage; ?></p>
    <?php } ?>
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
      <section class="field-row" style="justify-content: flex-end">
        <input type="submit" name="register"></button>
      </section>
      </form>
    </article>
  </div>
</div>
<div class="window" style="max-width: 75vw; margin: 0 auto;">
  <div class="title-bar">
    <div class="title-bar-text"><img src="static/img/png/channels-0.png" alt="off" style="width: 10px;" />&nbsp;&nbsp;Chat</div>
    <div class="title-bar-controls">
      <button aria-label="Minimize"></button>
      <button aria-label="Maximize"></button>
      <button aria-label="Close" class="closebtn"></button>
    </div>
  </div>
  <div class="window-body" style="max-height: 200px; overflow: scroll;">
    <div id="chat">
        <input type="text" id="message" placeholder="Type your message...">
        <button id="send">Send</button>
        <div id="chat-window">
        <?php foreach ($messages as $message) { ?>
        <div>
            <strong><?php echo $message['username']; ?>:</strong> <?php echo $message['message']; ?>
            <?php if ($message['parent_message_id'] !== null) { ?>
                <button class="reply-btn" data-id="<?php echo $message['parent_message_id']; ?>">Reply</button>
            <?php } ?>
        </div>
        <?php } ?>
        </div>
    </div>
  </div>
</div>
    <script>
        document.addEventListener("DOMContentLoaded", function () {
            const chatWindow = document.getElementById("chat-window");
            const messageInput = document.getElementById("message");
            const sendButton = document.getElementById("send");

            // Функция для добавления сообщения в окно чата
            function appendMessage(username, message, id) {
            const messageDiv = document.createElement("div");
            messageDiv.innerHTML = `<strong>${username}:</strong> ${message}
                <button class="reply-btn" data-parent="${id}">Reply</button>`;
            chatWindow.appendChild(messageDiv);

            const replyButtons = document.querySelectorAll(".reply-btn");
            replyButtons.forEach(button => {
                button.addEventListener("click", () => {
                    const parentMessageId = button.getAttribute("data-parent");
                    messageInput.value = `@${username} `;
                    messageInput.focus();
                });
            });
        }

            // Функция для отправки сообщения через AJAX
            function sendMessage() {
                const message = messageInput.value;

                const xhr = new XMLHttpRequest();
                xhr.open("POST", "ajax_chat.php", true);
                xhr.setRequestHeader("Content-type", "application/x-www-form-urlencoded");
                xhr.onreadystatechange = function () {
                    if (xhr.readyState === 4 && xhr.status === 200) {
                        const response = JSON.parse(xhr.responseText);
                        if (response.success) {
                            appendMessage("You", message);
                            messageInput.value = ""; // Очистка поля ввода
                        }
                    }
                };
                const data = "message=" + encodeURIComponent(message);
                xhr.send(data);
            }

            // Функция для обновления чата
        function updateChat() {
            const xhr = new XMLHttpRequest();
            xhr.open("GET", "ajax_chat.php", true);
            xhr.onreadystatechange = function () {
                if (xhr.readyState === 4 && xhr.status === 200) {
                    const messages = JSON.parse(xhr.responseText);
                    chatWindow.innerHTML = ""; // Очистка окна чата
                    messages.forEach(message => {
                        appendMessage(message.username, message.message, message.id);
                    });
                }
            };
            xhr.send();
        }

        // Отправка сообщения по кнопке "Отправить"
        sendButton.addEventListener("click", sendMessage);

        // Автоматическое обновление чата каждые 5 секунд
        setInterval(updateChat, 5000);
        });
    document.addEventListener("DOMContentLoaded", function () {
    const categoriesDiv = document.getElementById("Categories");
    const contentWindowDiv = document.getElementById("ContentWindow");

    categoriesDiv.addEventListener("click", function (event) {
        if (event.target.classList.contains("category-link")) {
            event.preventDefault();
            const categoryId = event.target.getAttribute("data-category-id");
            loadCategoryContent(categoryId);
        }
    });

    function loadCategoryContent(categoryId) {
        const xhr = new XMLHttpRequest();
        xhr.open("GET", "content.php?category_id=" + categoryId, true);

        xhr.onreadystatechange = function () {
            if (xhr.readyState === 4 && xhr.status === 200) {
                contentWindowDiv.innerHTML = xhr.responseText;
            }
        };

        xhr.send();
    }
});


    </script>
    <script>
document.addEventListener("DOMContentLoaded", function () {
    const recalculateRankButton = document.getElementById("recalculateRankButton");

    recalculateRankButton.addEventListener("click", function () {
        const xhr = new XMLHttpRequest();
        xhr.open("GET", "recalculate_rank.php", true); // Путь к файлу на сервере для перерасчета ранга

        xhr.onreadystatechange = function () {
            if (xhr.readyState === 4 && xhr.status === 200) {
                // Обновление страницы или вывод сообщения об успешном перерасчете
                alert("Rank recalculated successfully!");
                location.reload(); // Если хотите обновить страницу после перерасчета
            }
        };

        xhr.send();
    });
});
</script>

<div class="window" style="max-width: 75vw; margin: 0px auto 40px;">
  <div class="title-bar">
    <div class="title-bar-text"><img src="static/img/png/channels-0.png" alt="off" style="width: 10px;" />&nbsp;&nbsp;RSS Feed</div>
    <div class="title-bar-controls">
      <button aria-label="Minimize"></button>
      <button aria-label="Maximize"></button>
      <button aria-label="Close" class="closebtn"></button>
    </div>
  </div>
  <div class="window-body" style="max-height: 100px; overflow: scroll;">
    <?php foreach ($posts as $post) { ?>
        <div class="news-post">
            <p><?php echo $post['title']; ?> - 
            Date: <?php echo $post['date']; ?> - 
            <?php echo $post['text']; ?> - 
            Author: <?php echo $user->getUsernameById($post['author_id']); ?></p>
        </div>
    <?php } ?>
  </div>
</div>
<div class="footer" style="align-items: center;">
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
<div id="contextMenu" class="context-menu" 
        style="display:none">
        <ul>
            <li><a href="#">About</a></li>
            <li><a href="#">J2ME.xyz template based</a></li>
        </ul>
    </div>
        <script>
        document.onclick = hideMenu;
        document.oncontextmenu = rightClick;
  
        function hideMenu() {
            document.getElementById(
                "contextMenu").style.display = "none"
        }
  
        function rightClick(e) {
            e.preventDefault();
  
            if (document.getElementById(
                "contextMenu").style.display == "block")
                hideMenu();
            else {
                var menu = document
                    .getElementById("contextMenu")
                      
                menu.style.display = 'block';
                menu.style.left = e.pageX + "px";
                menu.style.top = e.pageY + "px";
            }
        }
		const tabs = document.querySelectorAll("menu[role=tablist]");

		for (let i = 0; i < tabs.length; i++) {
		  const tab = tabs[i];

		  const tabButtons = tab.querySelectorAll("menu[role=tablist] > button[role=tab]");

		  tabButtons.forEach((btn) =>
		    btn.addEventListener("click", (e) => {
		      e.preventDefault();

		      tabButtons.forEach((button) => {
		        if (
		          button.getAttribute("aria-controls") ===
		          e.target.getAttribute("aria-controls")
		        ) {
		          button.setAttribute("aria-selected", true);
		          openTab(e, tab);
		        } else {
		          button.setAttribute("aria-selected", false);
		        }
		      });
		    })
		  );
		}

		function openTab(event, tab) {
		  const articles = tab.parentNode.querySelectorAll('[role="tabpanel"]');
		  articles.forEach((p) => {
		    p.setAttribute("hidden", true);
		  });
		  const article = tab.parentNode.querySelector(
		    `[role="tabpanel"]#${event.target.getAttribute("aria-controls")}`
		  );
		  article.removeAttribute("hidden");
		}
</script>
<script src="static/js/modal.js"></script>
</body>
</html>