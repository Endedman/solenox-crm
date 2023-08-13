<?php
session_start();
require_once $_SERVER['DOCUMENT_ROOT'] . "classes/Database.php";
$db = new Database();
require_once $_SERVER['DOCUMENT_ROOT'] . "classes/NetCat.php";
require_once $_SERVER['DOCUMENT_ROOT'] . "classes/News.php";
require_once $_SERVER['DOCUMENT_ROOT'] . "classes/User.php";
require_once $_SERVER['DOCUMENT_ROOT'] . "classes/Rcon.php";
require $_SERVER['DOCUMENT_ROOT'] . "libs/ColorizePHPParser/mccolors_helper.php";
    // Создаем объекты классов с передачей подключения к базе данных
    $netCat = new NetCat($db);
    $user = new User($db); 
	$news = new News($db);
	$host = 'd12.gamely.pro';
	$port = 20164;
	$password = 'Fsjxwrfvbjo1/';
	$timeout = 5;
	$rcon = new Rcon($host, $port, $password, $timeout);
    $userId = $_SESSION["user_id"]; // Получите ID пользователя из сессии
    $command = $_POST['command'];

        // Process the file upload if needed
        if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST["upload"])) {
            $categoryID = $_POST["category"];
            $file = $_FILES["file"]; // Обработка загруженного файла
            $fileNameHuman = $_POST["filenamehuman"];

            $description = $_POST["description"];
            $qualityMark = $_POST["qualitymark"];
            $uniquenessMark = $_POST["uniquenessmark"];
            $interfaceLanguage = $_POST["interfacelanguage"];
            $uploadedBy = $_POST["uploadedby"];
            if (isset($_FILES["file"])) {
                $uploadResult = $netCat->uploadFile($_FILES["file"], $categoryID, $description, $qualityMark, $uniquenessMark, $interfaceLanguage, $uploadedBy, $fileNameHuman);
                if ($uploadResult === true) {
                    echo "File uploaded successfully.";
                } else {
                    echo "File upload failed: $uploadResult";
                }
            }
        }
		if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST["create_category"])) {
	        $name = $_POST["name"];
	        $developer = $_POST["developer"];
	        $website = $_POST["website"];
	        $license = $_POST["license"];
	        $createdBy = $_POST["created_by"];

	        $createResult = $netCat->createCategory($name, $developer, $website, $license, $createdBy);
            if ($createResult === true) {
                echo "Category created successfully.";
            } else {
                echo "Category creation failed: $createResult";
            }
        }
        if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST["add_news"])) {
		    $title = $_POST["title"];
		    $content = $_POST["content"];
		    $author = $_POST["author"];

		    $createResult = $news->createNews($title, $content, $author);
		    if ($createResult) {
		        echo "News created successfully.";
		    } else {
		        echo "Failed to create news.";
		    }
		}

if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST["action"])) {
    $allowedActions = array("reboot", "reload"); // Массив разрешенных действий

    $action = $_POST["action"];

    if (in_array($action, $allowedActions)) {
        if ($rcon->connect()) {
            $command = "";

            // Создайте массив команд для каждого действия
            $commands = array(
                "reboot" => "restart",
                "reload" => "reload confirm"
            );

            if (array_key_exists($action, $commands)) {
                $command = $commands[$action];
            }

            if (!empty($command)) {
                if ($rcon->send_command($command)) {
				    $result = $rcon->get_response();
				    $_SESSION['success_message'] = "Команда успешно отправлена: $command";
				} else {
				    $_SESSION['error_message'] = "Ошибка отправки команды: $command";
                }
            } else {
                // Обработка ошибки: не удалось найти команду для действия
                $_SESSION['error_message'] = "Ошибка: неизвестное действие";
            }
        } else {
            // Обработка ошибки: не удалось подключиться к RCON серверу
            $_SESSION['error_message'] = "Ошибка подключения к RCON серверу";
        }
    } else {
        // Обработка ошибки: недопустимое действие
        $_SESSION['error_message'] =  "Ошибка: недопустимое действие";
    }
}


        // Display the upload form
        $categories = $netCat->getCategories();
?>
<!DOCTYPE html>
<html>
<head>
	<title>JStore</title>
		<link rel="stylesheet" href="../static/css/98.css">
	<link rel="stylesheet" type="text/css" href="../static/css/style.css">
	<link rel="stylesheet" type="text/css" href="../libs/ColorizePHPParser/MinecraftColors.css">
	<script src="https://dir.by/example_lib/jquery/jquery-3.3.1.min.js"></script>
    <script src="https://dir.by/example_lib/jquery_ui/jquery-ui-1.12.1/jquery-ui.min.js"></script> 
    <!-- подключаем стили jQuery UI -->
    <link rel="stylesheet" href="https://dir.by/example_lib/jquery_ui/jquery-ui-1.12.1/jquery-ui.min.css">
    <script type="text/javascript" src="../static/js/jquery-1.12.0.min.js"></script>
    <script type="text/javascript" src="../static/js/jquery-migrate-1.2.1.min.js"></script>
    <script type="text/javascript" src="../static/js/jquery-ui-1.12.0.min.js"></script>
    <script src="../static/js/rcon.js"></script>

</head>
<body>
<div class="window" style="max-width: 75vw; margin: 30px auto 0;">
  <div class="title-bar">
    <div class="title-bar-text"><img src="../static/img/png/msie1-3.png" alt="" style="width: 13px;" />&nbsp;&nbsp;Internet Explorer</div>
  </div>
  <div class="window-body">
<p>Welcome to J2ME.xyz (formerly LibreShare).</p>
<p>This is admin panel</p>
<section class="tabs">
  <menu role="tablist" aria-label="Sample Tabs">
    <button role="tab" aria-selected="true" aria-controls="UserControl">User Control</button>
    <button role="tab" aria-controls="AddNews">AddNews</button>
    <button role="tab" aria-controls="CreateCategory">Create Category</button>
    <button role="tab" aria-controls="UploadFiles">Upload Files</button>
    <button role="tab" aria-controls="SRVManage">MC Server management</button>

</menu>
  <!-- the tab content -->
  <article role="tabpanel" id="UserControl">
  	<?php 
  	$requiredRole = 0;
    if (!$user->userHasPermission($userId, $requiredRole)) {
        $permMessage = "You do not have permission to use this feature.";
    } else {
    ?>
  	UserControl
  <?php } ?>
  </article>
	<article role="tabpanel" hidden id="AddNews">
	<?php
	$requiredRole = 0;
    if (!$user->userHasPermission($userId, $requiredRole)) {
        $permMessage = "You do not have permission to use this feature.";
    } else {
    ?>
    <h4>Create News</h4>
    <form method="post" action="index.php">
        <label for="title">Title:</label>
        <input type="text" id="title" name="title" required><br>

        <label for="content">Content:</label>
        <textarea id="content" name="content" required></textarea><br>

        <input type="hidden" id="author" name="author" value="<?php echo $_SESSION['user_id']; ?>" readonly><br>

        <button type="submit" name="add_news">Add News</button>
    </form>
      <?php } ?>
	</article>

  <article role="tabpanel" hidden id="CreateCategory">
  	<?php
  	$requiredRole = 0;
    if (!$user->userHasPermission($userId, $requiredRole)) {
        $permMessage = "You do not have permission to use this feature.";
    } else {
    ?>
  	<h4>Create Category</h4>
    <form method="post" action="index.php">
        <label for="name">Name:</label>
        <input type="text" id="name" name="name" required><br>

        <label for="developer">Developer:</label>
        <input type="text" id="developer" name="developer" required><br>

        <label for="website">Website:</label>
        <input type="text" id="website" name="website"><br>

        <label for="license">License:</label>
        <input type="text" id="license" name="license"><br>

        <label for="created_by">Created By:</label>
        <input type="text" id="created_by" name="created_by" value="<?php echo $_SESSION['user_id']; ?>" readonly><br>

        <button type="submit" name="create_category">Create Category</button>
    </form>
<?php } ?>
  </article>
  <article role="tabpanel" hidden id="UploadFiles">
  	<?php
  	$requiredRole = 0;
    if (!$user->userHasPermission($userId, $requiredRole)) {
        $permMessage = "You do not have permission to use this feature.";
    } else {
    ?>
  	<h4>Upload File</h4>
        <form method="post" action="index.php" enctype="multipart/form-data">
        <label for="file">File:</label>
        <input type="file" name="file">
            <select name="category">
                <?php foreach ($categories as $category) { ?>
                    <option value="<?php echo $category['id']; ?>"><?php echo $category['name']; ?></option>
                <?php } ?>
            </select><br>
        <label for="filenamehuman">Human-readable filename</label>
        <input type="text" id="filenamehuman" name="filenamehuman" required></textarea><br>

        <label for="description">Description:</label>
        <textarea id="description" name="description" required></textarea><br>

        <label for="qualitymark">Quality Mark:</label>
        <input type="text" id="qualitymark" name="qualitymark" required><br>

        <label for="uniquenessmark">Uniqueness Mark:</label>
        <input type="text" id="uniquenessmark" name="uniquenessmark" required><br>

        <label for="interfacelanguage">Interface Language:</label>
        <input type="text" id="interfacelanguage" name="interfacelanguage" required><br>

        <input type="hidden" name="uploadedby" value="<?php echo $_SESSION['user_id']; ?>">

        <button type="submit" name="upload">Upload File</button>
    </form>
<?php } ?>
  </article>
  <article role="tabpanel" hidden id="SRVManage">
  	<?php 
  	$requiredRole = 1;
    if (!$user->userHasPermission($userId, $requiredRole)) {
        $permMessage = "You do not have permission to use this feature.";
    } else {
    ?>
  	<?php
  	if (isset($_SESSION['success_message'])) {
    echo '<div class="success">' . $_SESSION['success_message'] . '</div>';
    unset($_SESSION['success_message']); // Очистка сообщения
	}

if (isset($_SESSION['error_message'])) {
    echo '<div class="error">' . $_SESSION['error_message'] . '</div>';
    unset($_SESSION['error_message']); // Очистка сообщения
}
?>
  	<form method="post" action="">
    <button type="submit" name="action" value="reboot">Reboot</button>
    <button type="submit" name="action" value="reload">Reload plug-ins</button>
    <!-- Добавьте кнопки для других разрешенных действий -->
	</form>
	<div class="container-fluid" id="content">
    <div id="consoleRow">
      <div class="panel panel-default" id="consoleContent">
        <div class="panel-heading">
          <h4>Console</h4>
        </div>
        <div class="panel-body">
          <ul class="tree-view" id="groupConsole"></ul>
        </div>
      </div>
      <div class="input-group" id="consoleCommand">
        <span class="input-group-addon">
          <input id="chkAutoScroll" type="checkbox" checked="true" autocomplete="off" /><span class="glyphicon glyphicon-arrow-down"></span>
        </span>
        <div id="txtCommandResults"></div>
        <input type="text" class="form-control" id="txtCommand" />
        <div class="input-group-btn">
          <button type="button" class="btn btn-primary" id="btnSend"><span class="glyphicon glyphicon-send"></span><span class="hidden-xs"> Send</span></button>
          <button type="button" class="btn btn-warning" id="btnClearLog"><span class="glyphicon glyphicon-erase"></span><span class="hidden-xs"> Clear</span></button>
        </div>
      </div>
    </div>
  </div>
<?php } ?>
  </article>
</section>
  </div>
  <div class="status-bar">
    <p class="status-bar-field">http://j2me.xyz</p>
    <p class="status-bar-field">Admin</p>
    <p class="status-bar-field"><img src="../static/img/png/no2-1.png" alt="off" style="width: 10px;" />&nbsp;&nbsp;SmartScreen is off</p>
    <p class="status-bar-field"><progress></progress></p>
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
"><img src="../static/img/png/windows-0.png" alt="off" style="width: 16px;" />&nbsp;&nbsp;Start</button>
</div>
<div id="contextMenu" class="context-menu" 
        style="display:none">
        <ul>
            <li><a href="#">About</a></li>
            <li><a href="#" disabled>J2ME.xyz template based</a></li>
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
<script src="../static/js/modal.js"></script>
<script src="../libs/ColorizePHPParser/MinecraftObfuscated.js"></script>
</body>
</html>
