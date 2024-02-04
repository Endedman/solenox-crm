<?php
session_start();
session_destroy();
header('Location: /mobile/login'); // редирект на страницу входа
exit();