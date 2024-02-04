<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <title>
        <?php echo $pageTitle; ?> - JStore LTD
    </title>
    <link href="https://cdn.jsdelivr.net/bootstrap/2.3.2/css/bootstrap.min.css" rel="stylesheet">
    <style>
        body {
            padding-top: 60px;
        }

        .container {
            padding: 20px;
            background-color: #f5f5f5;
            border-radius: 5px;
        }

        .footer {
            margin-top: 20px;
            text-align: center;
        }
    </style>
</head>

<body>
    <div class="navbar navbar-inverse navbar-fixed-top">
        <div class="navbar-inner">
            <div class="container">
                <a class="brand" href="#">JStore LTD</a>
                <ul class="nav">
                    <li><a href="tos.php">Terms of Service</a></li>
                    <li><a href="privacy.php">Privacy Policy</a></li>
                </ul>
            </div>
        </div>
    </div>

    <div class="container">
        <h1>
            <?php echo $pageTitle; ?>
        </h1>
        <p>
            <?php echo $pageContent; ?>
        </p>
    </div>

    <div class="footer">
        <p>&copy;
            <?php echo date("Y"); ?> JStore LTD, All rights reserved.
        </p>
    </div>

    <script src="https://cdn.jsdelivr.net/bootstrap/2.3.2/js/bootstrap.min.js"></script>
</body>

</html>