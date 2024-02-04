<?php
require_once $_SERVER['DOCUMENT_ROOT'] . "admin/post_handlers.php";
?>
<!DOCTYPE html>
<html>

<head>
  <title>JStore</title>
  <link rel="stylesheet" href="../static/css/xp.css">
  <link rel="stylesheet" type="text/css" href="/static/css/style.css">
  <link rel="stylesheet" type="text/css" href="/libs/ColorizePHPParser/MinecraftColors.css">
  <script src="https://dir.by/example_lib/jquery/jquery-3.3.1.min.js"></script>
  <script src="https://dir.by/example_lib/jquery_ui/jquery-ui-1.12.1/jquery-ui.min.js"></script>
  <!-- подключаем стили jQuery UI -->
  <link rel="stylesheet" href="https://dir.by/example_lib/jquery_ui/jquery-ui-1.12.1/jquery-ui.min.css">
  <link rel="stylesheet" href="/static/css/admin-styles.css">
  <script type="text/javascript" src="../static/js/jquery-1.12.0.min.js"></script>
  <script type="text/javascript" src="../static/js/jquery-migrate-1.2.1.min.js"></script>
  <script type="text/javascript" src="../static/js/jquery-ui-1.12.0.min.js"></script>
  <script src="../static/js/rcon.js"></script>
  <script type="text/javascript" src="/static/js/tinymce/tiny_mce.js"></script>
  <script type="text/javascript">
    tinyMCE.init({
      // General options
      language: "ru",
      mode: "textareas",
      theme: "advanced",
      plugins: "pagebreak,style,layer,table,save,advhr,advimage,advlink,emotions,iespell,insertdatetime,preview,media,searchreplace,print,contextmenu,paste,directionality,fullscreen,noneditable,visualchars,nonbreaking,xhtmlxtras,template,inlinepopups,autosave",

      // Theme options
      theme_advanced_buttons1: "bold,italic,underline,strikethrough,|,justifyleft,justifycenter,justifyright,justifyfull,|,styleselect,formatselect,fontselect,fontsizeselect",
      theme_advanced_buttons2: "cut,copy,paste,pastetext,pasteword,|,search,replace,|,bullist,numlist,|,outdent,indent,blockquote,|,undo,redo,|,link,unlink,anchor,image,cleanup,help,code,|,insertdate,inserttime,preview,|,forecolor,backcolor",
      theme_advanced_buttons3: "tablecontrols,|,hr,removeformat,visualaid,|,sub,sup,|,charmap,emotions,iespell,media,advhr,|,print,|,ltr,rtl,|,fullscreen",
      theme_advanced_buttons4: "insertlayer,moveforward,movebackward,absolute,|,styleprops,|,cite,abbr,acronym,del,ins,attribs,|,visualchars,nonbreaking,template,pagebreak,restoredraft",
      theme_advanced_toolbar_location: "top",
      theme_advanced_toolbar_align: "left",
      theme_advanced_statusbar_location: "bottom",
      theme_advanced_resizing: true,

      // Example word content CSS (should be your site CSS) this one removes paragraph margins
      // content_css : "css/word.css",

      // Drop lists for link/image/media/template dialogs
      template_external_list_url: "lists/template_list.js",
      external_link_list_url: "lists/link_list.js",
      external_image_list_url: "lists/image_list.js",
      media_external_list_url: "lists/media_list.js",

      // Replace values for the template plugin
      template_replace_values: {
        username: "Some User",
        staffid: "991234"
      }
    });
  </script>
  <!-- /TinyMCE -->

</head>

<body>
  <div style="height:100vh">
    <div class="window" style="max-width: 75vw; margin: 0px auto 0;">
      <div class="title-bar">
        <div class="title-bar-text"><img src="../static/img/png/msie1-3.png" alt=""
            style="width: 13px;" />&nbsp;&nbsp;Internet Explorer</div>
      </div>
      <div class="window-body">
        <p>Welcome to J2ME.xyz (formerly LibreShare).</p>
        <p>This is admin panel</p>
        <section class="tabs">
          <menu role="tablist" aria-label="Sample Tabs">
            <button role="tab" aria-selected="true" aria-controls="UserControl">User Control</button>
            <button role="tab" aria-controls="AddNews">AddNews</button>
            <button role="tab" aria-controls="CreateCategory">Create Category</button>
            <button role="tab" aria-controls="CreateRedirect">Create Redirect</button>
            <button role="tab" aria-controls="UserActivity">Statist</button>

            <button role="tab" aria-controls="UploadFiles">Upload Files</button>
            <button role="tab" aria-controls="SRVManage">MC Server management</button>

          </menu>
          <!-- the tab content -->
          <article role="tabpanel" id="UserControl">
            <?php
            $requiredRole = 3;
            if (!$user->userHasPermission($userId, $requiredRole)) {
              echo "You do not have permission to use this feature. Please <a href='/index.php'>re-login</a> to administrator's account in order to use this tab";
            } else {
              ?>
              <?php
              if (isset($_SESSION['tk_success_message'])) {
                echo '<div class="success">' . $_SESSION['tk_success_message'] . '</div>';
                unset($_SESSION['tk_success_message']); // Очистка сообщения
              }

              if (isset($_SESSION['tk_error_message'])) {
                echo '<div class="error">' . $_SESSION['tk_error_message'] . '</div>';
                unset($_SESSION['tk_error_message']); // Очистка сообщения
              }
              ?>
              <table>
                <tr>
                  <th>ID</th>
                  <th>Username</th>
                  <th>Email</th>
                  <th>Rank</th>
                  <th>TOTP</th>

                  <th>Role</th>
                  <th>Action</th>
                </tr>
                <?php foreach ($userList as $userOne): ?>
                  <tr>
                    <td>
                      <?php echo $userOne['id']; ?>
                    </td>
                    <td>
                      <?php echo $userOne['username']; ?>
                    </td>
                    <td>
                      <?php echo $userOne['email']; ?>
                    </td>
                    <td>
                      <?php echo $userOne['rank']; ?>
                    </td>
                    <td>
                      <?php echo $userOne['user_totp_secret']; ?>
                    </td>
                    <td>
                      <?php echo $userOne['role']; ?>
                    </td>
                    <td style="display:flex">
                      <button class="deleteUserButton" data-user-id="<?php echo $userOne['id']; ?>">Delete User</button>
                      <form method="post" action="index.php">
                        <input hidden name="changeToken" value=<?php echo $userOne['id']; ?>"></input>
                    <button class="regenerateTOTPButton" data-user-id="<?php echo $userOne['id']; ?>" type="submit"
                          name="regen_token">Regenerate TOTP Secret</button>
                      </form>
                    </td>
                  </tr>
                <?php endforeach; ?>
              </table>
              <!-- Modal -->
              <div id="modal" style="display: none;">
                <p>Are you sure you want to delete this user?</p>
                <button id="confirmDelete">Yes</button>
                <button id="cancelDelete">No</button>
              </div>

              <!-- Progress Bar -->
              <div id="progressBar" style="display: none; width: 0;"></div>

            <?php } ?>
          </article>
          <article role="tabpanel" hidden id="AddNews">
            <?php
            $requiredRole = 2;
            if (!$user->userHasPermission($userId, $requiredRole)) {
              echo "You do not have permission to use this feature. Please <a href='/index.php'>re-login</a> as a moderator to use this tab.";
            } else {
              // Show success/error messages
              if (isset($_SESSION['nw_success_message'])) {
                echo '<div class="success">' . $_SESSION['nw_success_message'] . '</div>';
                unset($_SESSION['nw_success_message']);
              }

              if (isset($_SESSION['nw_error_message'])) {
                echo '<div class="error">' . $_SESSION['nw_error_message'] . '</div>';
                unset($_SESSION['nw_error_message']);
              }

              // The form with TinyMCE editor for the content field
              ?>
              <h4>Create News</h4>
              <form method="post" action="index.php">
                <label for="title">Title:</label>
                <input type="text" id="title" name="title" required><br>

                <label for="content">Content:</label>
                <textarea id="content" name="content"></textarea><br>

                <input type="hidden" id="author" name="author" value="<?php echo $_SESSION['user_id']; ?>" readonly><br>

                <button type="submit" name="add_news">Add News</button>
              </form>
            <?php } ?>
          </article>

          <article role="tabpanel" hidden id="CreateCategory">
            <?php
            $requiredRole = 1;
            if (!$user->userHasPermission($userId, $requiredRole)) {
              echo "You do not have permission to use this feature. Please <a href='/index.php'>re-login</a> to curator's account in order to use this tab";
            } else {

              ?>
              <?php
              if (isset($_SESSION['ct_success_message'])) {
                echo '<div class="success">' . $_SESSION['ct_success_message'] . '</div>';
                unset($_SESSION['ct_success_message']); // Очистка сообщения
              }

              if (isset($_SESSION['ct_error_message'])) {
                echo '<div class="error">' . $_SESSION['ct_error_message'] . '</div>';
                unset($_SESSION['ct_error_message']); // Очистка сообщения
              }
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
                <input type="text" id="created_by" name="created_by" value="<?php echo $_SESSION['user_id']; ?>"
                  readonly><br>

                <button type="submit" name="create_category">Create Category</button>
              </form>
            <?php } ?>
          </article>
          <article role="tabpanel" hidden id="CreateRedirect">
            <?php
            $requiredRole = 2;
            if (!$user->userHasPermission($userId, $requiredRole)) {
              echo "You do not have permission to use this feature. Please <a href='/index.php'>re-login</a> to administrator's account in order to use this tab";
            } else {
              ?>
              <?php
              if (isset($_SESSION['redir_success_message'])) {
                echo '<div class="success">' . $_SESSION['redir_success_message'] . '</div>';
                unset($_SESSION['redir_success_message']); // Очистка сообщения
              }

              if (isset($_SESSION['redir_error_message'])) {
                echo '<div class="error">' . $_SESSION['redir_error_message'] . '</div>';
                unset($_SESSION['redir_error_message']); // Очистка сообщения
              }
              ?>

              <h4>Create Redirect</h4>
              <form method="post" action="index.php">
                <label for="name">Name:</label>
                <input type="text" id="name" name="name" required><br>

                <label for="link">Redirect Link:</label>
                <input type="text" id="link" name="link" required><br>

                <button type="submit" name="create_redirect">Create Redirect</button>
              </form>
            <?php } ?>
          </article>
          <article role="tabpanel" hidden id="UploadFiles">
            <?php
            $requiredRole = 0;
            if (!$user->userHasPermission($userId, $requiredRole)) {
              echo "You do not have permission to use this feature. Please <a href='/index.php'>re-login</a> to any user's account in order to use this tab";
            } else {

              ?>
              <?php
              if (isset($_SESSION['fl_success_message'])) {
                echo '<div class="success">' . $_SESSION['fl_success_message'] . '</div>';
                unset($_SESSION['fl_success_message']); // Очистка сообщения
              }

              if (isset($_SESSION['fl_error_message'])) {
                echo '<div class="error">' . $_SESSION['fl_error_message'] . '</div>';
                unset($_SESSION['fl_error_message']); // Очистка сообщения
              }
              ?>

              <h4>Upload File</h4>
              <div class="window modal modalW" draggable="true" id="modalW" id="modalwindow"
                style="width: 300px; position: absolute;">
                <div class="title-bar" id="modalwindowheader">
                  <div class="title-bar-text"><img src="../static/img/png/console_prompt-0.png" alt="off"
                      style="width: 10px;" />&nbsp;&nbsp;Alert</div>
                  <div class="title-bar-controls">
                    <button aria-label="Minimize"></button>
                    <button aria-label="Maximize"></button>
                    <button aria-label="Close" class="closebtn"></button>
                  </div>
                </div>
                <div class="window-body" draggable="false">
                  <h4>Loading...</h4>
                  <progress></progress>
                </div>
              </div>
              <form method="post" action="index.php" id="fileUploadForm" enctype="multipart/form-data"
                onsubmit="uploadFile(event)">
                <label for="file">File:</label><br>
                <!-- Атрибуты id и style модального окна будут использоваться в JavaScript -->

                <label class="input-file">
                  <span class="input-file-text" type="text"></span>
                  <input type="file" name="file">
                  <span class="input-file-btn">Выберите файл</span>
                </label><br>
                <label for="category">Category</label><br>
                <select name="category">
                  <?php foreach ($categories as $category) { ?>
                    <option value="<?php echo $category['id']; ?>">
                      <?php echo $category['name']; ?>
                    </option>
                  <?php } ?>
                </select><br>
                <label for="filenamehuman">Human-readable filename</label><br>
                <input type="text" id="filenamehuman" name="filenamehuman" required></textarea><br>

                <label for="description">Description:</label><br>
                <textarea id="description" name="description" required></textarea><br>

                <label for="qualitymark">Quality Mark:</label><br>
                <select id="qualitymark" name="qualitymark" required>
                  <option value="1">Yes</option>
                  <option value="0">No</option>
                </select>


                <label for="uniquenessmark">Uniqueness Mark:</label><br>
                <select id="uniquenessmark" name="uniquenessmark" required>
                  <option value="1">Yes</option>
                  <option value="0">No</option>
                </select>

                <label for="interfacelanguage">Interface Language:</label><br>
                <select id="interfacelanguage" name="interfacelanguage" required>
                  <?php foreach ($languages as $language) { ?>
                    <option value="<?php echo $language['language']; ?>">
                      <?php echo $language['language']; ?>
                    </option>
                  <?php } ?>
                </select><br>
                <label for="screenshots">Screenshots:</label>
                <input type="file" id="screenshots" name="screenshots[]" multiple ondrop="dropHandler(event);"
                  ondragover="dragOverHandler(event);">
                <input type="hidden" name="uploadedby" value="<?php echo $_SESSION['user_id']; ?>">

                <button type="submit" name="upload">Upload File</button>
              </form>

            <?php } ?>
            <script>
              function dropHandler(ev) {
                console.log('File(s) dropped');
                ev.preventDefault();
                if (ev.dataTransfer.items) {
                  for (var i = 0; i < ev.dataTransfer.items.length; i++) {
                    if (ev.dataTransfer.items[i].kind === 'file') {
                      var file = ev.dataTransfer.items[i].getAsFile();
                      console.log('... file[' + i + '].name = ' + file.name);
                    }
                  }
                } else {
                  for (var i = 0; i < ev.dataTransfer.files.length; i++) {
                    console.log('... file[' + i + '].name = ' + ev.dataTransfer.files[i].name);
                  }
                }
              }

              function dragOverHandler(ev) {
                console.log('File(s) in drop zone');
                ev.preventDefault();
              }
            </script>

          </article>
          <article role="tabpanel" hidden id="UserActivity">
            <?php
            // Обрабатываем журнал активности
            foreach ($activityLog as $log) {
              $date = substr($log['timestamp'], 0, 10);  // Получаем только дату, без времени
              $action = $log['action'];

              if (!isset($activityData[$action])) {
                $activityData[$action] = [
                  'dates' => [],
                  'counts' => []
                ];
              }

              $index = array_search($date, $activityData[$action]['dates']);
              if ($index !== false) {
                $activityData[$action]['counts'][$index]++;
              } else {
                $activityData[$action]['dates'][] = $date;
                $activityData[$action]['counts'][] = 1;
              }
            }
            ?>

            <h4>User Activity</h4>

            <!-- Здесь будет график активности пользователей -->
            <canvas id="activityChart"></canvas>

            <!-- Подключаем Chart.js и плагин chartjs-adapter-date-fns -->
            <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
            <script src="https://cdn.jsdelivr.net/npm/chartjs-adapter-date-fns"></script>

            <!-- Задаем функцию для генерации случайных цветов -->
            <script>
              function getRandomColor() {
                var characters = '0123456789ABCDEF';
                var color = "#";
                for (var i = 0; i < 6; i++) {
                  color += characters[Math.floor(Math.random() * 12)];
                }
                return color;
              }
            </script>

            <!-- Создаем график с помощью Chart.js -->
            <script>
              var activityData = <?php echo json_encode($activityData); ?>;
              var activityChart = document.getElementById('activityChart').getContext('2d');

              var datasets = [];
              for (var action in activityData) {
                var dataset = {
                  label: action,
                  data: activityData[action]['counts'].map(function (count, i) {
                    // Map the dates and counts to an object that Chart.js understands
                    return {
                      x: new Date(activityData[action]['dates'][i]),
                      y: count
                    };
                  }),
                  fill: false,
                  borderColor: getRandomColor()
                };
                datasets.push(dataset);
              }

              new Chart(activityChart, {
                type: 'line',
                data: {
                  datasets: datasets
                },
                options: {
                  title: {
                    display: true,
                    text: 'User Activity'
                  },
                  scales: {
                    x: {
                      type: 'time',
                      time: {
                        unit: 'day',
                        tooltipFormat: 'MMMM DD YYYY'
                      },
                      displayFormats: {
                        day: 'MMMM DD YYYY',
                      },
                    },
                    y: {
                      beginAtZero: true
                    }
                  }
                }
              });
            </script>

          </article>
          <article role="tabpanel" hidden id="SRVManage">
            <?php
            $requiredRole = 3;
            if (!$user->userHasPermission($userId, $requiredRole)) {
              echo "You do not have permission to use this feature. Please <a href='/index.php'>re-login</a> to administrator's account in order to use this tab";
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
                      <input id="chkAutoScroll" type="checkbox" checked="true" autocomplete="off" /><span
                        class="glyphicon glyphicon-arrow-down"></span>
                    </span>
                    <div id="txtCommandResults"></div>
                    <input type="text" class="form-control" id="txtCommand" />
                    <div class="input-group-btn">
                      <button type="button" class="btn btn-primary" id="btnSend"><span
                          class="glyphicon glyphicon-send"></span><span class="hidden-xs"> Send</span></button>
                      <button type="button" class="btn btn-warning" id="btnClearLog"><span
                          class="glyphicon glyphicon-erase"></span><span class="hidden-xs"> Clear</span></button>
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
        <p class="status-bar-field"><img src="../static/img/png/no2-1.png" alt="off"
            style="width: 10px;" />&nbsp;&nbsp;SmartScreen is off</p>
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
    <div id="contextMenu" class="context-menu" style="display:none">
      <ul>
        <li><a href="#">About</a></li>
        <li><a href="#" disabled>J2ME.xyz template based</a></li>
      </ul>
    </div>
    <script src="https://ajax.googleapis.com/ajax/libs/jquery/3.5.1/jquery.min.js"></script>
    <script>
      $(document).ready(function () {
        var userIdToDelete;

        $('.deleteUserButton').click(function () {
          userIdToDelete = $(this).data('userId');
          $('#modal').show();
        });

        $('#confirmDelete').click(function () {
          $('#modal').hide();
          $('#progressBar').show();
          $.ajax({
            url: 'user_management.php',
            type: 'POST',
            data: { userId: userIdToDelete, action: 'delete' },
            success: function (response) {
              alert(response);
              $('#progressBar').hide();
            }
          });
        });

        $('#cancelDelete').click(function () {
          $('#modal').hide();
        });
      });
    </script>
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
    <script src="https://snipp.ru/cdn/jquery/2.1.1/jquery.min.js"></script>
    <script>
      $('.input-file input[type=file]').on('change', function () {
        let file = this.files[0];
        $(this).closest('.input-file').find('.input-file-text').html(file.name);
      });
    </script>
    <script>
      function uploadFile(event) {
        var modalW = document.getElementById('modalW');
        modalW.style.display = 'block';
      }
    </script>

    <script src="../static/js/modal.js"></script>
    <script src="../libs/ColorizePHPParser/MinecraftObfuscated.js"></script>
  </div>
</body>

</html>