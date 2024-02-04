let mobileDir = '/mobile/';
$(document).on('click', '.language', function (e) {
    e.preventDefault();

    var lang = $(this).data('lang');
    $.ajax({
        url: mobileDir + 'set_language.php',
        data: { language: lang },
        type: 'POST',
        success: function () {
            location.reload();
        }
    });
});
$(document).ready(function () {
    // Получение изображения капчи
    $.get(mobileDir + 'captcha.php', function (data) {
        $('#captcha_img').html('<img src=' + mobileDir + 'captcha.php />');
    });

    // Перезагрузка капчи при клике
    $('#captcha_img').click(function () {
        $.get(mobileDir + 'captcha.php', function (data) {
            $('#captcha_img').html('<img src="' + mobileDir + '>captcha.php />');
        });
    });
});

$(document).on('pageinit', function () {
    $('#open-popup').on('click', function () {
        $('#myPopup').popup('open');
    });

    $('#close-popup').on('click', function () {
        $('#myPopup').popup('close');
    });
});
$(document).on("pagecreate", function (event) {
    // Тема по умолчанию
    var defaultTheme = 'a';

    // Загрузить тему из cookie при загрузке страницы
    var theme = $.cookie('theme') || defaultTheme;
    changeTheme(theme);

    $(".themeChanger").on("vclick", function () {
        var theme = $(this).data("theme");

        // Сохранить выбранную тему в cookie
        $.cookie('theme', theme, { expires: 7, path: mobileDir }); // cookie будет сохраняться 7 дней

        // Применить выбранную тему
        changeTheme(theme);

        // Перезагрузить страницу
        location.reload();
    });
});

function changeTheme(theme) {
    $('[data-role="page"]').attr("data-theme", theme);
    $('[data-role="header"]').attr("data-theme", theme);
    $('[data-role="footer"]').attr("data-theme", theme);
    $('[data-role="listview"]').attr("data-theme", theme);
    $('[data-role="content"]').attr("data-theme", theme);
    $('[data-role="panel"]').attr("data-theme", theme);

    $('[data="listview"]').listview('refresh');
    $('[data-role="panel"]').panel();
}


$(document).ready(function () {
    var options = {
        beforeSubmit: function () {
            $('#uploadResult').empty();
            $("#loading").show(); // показать индикатор загрузки
        },
        success: function (data) {
            $("#loading").hide(); // скрыть индикатор загрузки
            $('#uploadResult').html(data.message);
        },
        error: function (e) {
            $('#uploadResult').html('File upload failed.');
            $("#loading").hide(); // скрыть индикатор загрузки
        },
        url: mobileDir + 'addons/fileupl.php', // URL к обработчику на сервере который выполняет загрузку
        type: 'post',
        dataType: 'json'
    };

    // Меняем 'ajaxForm' на 'ajaxSubmit'
    $('#submit').click(function () {
        $("#uploadForm").ajaxSubmit(options);
    });
});
$(document).ready(function () {
    $('div[data-role="navbar"] a').on('click', function () {
        var target = $(this).attr('href');
        $('.ui-content div').hide();
        $(target).show();
        return false;
    });
});
$(document).ready(function () {
    $("#wishlist-button").click(function () {
        var itemId = $(this).data("item-id");
        var action = $(this).text() == "Добавить в список желаний" ? "add" : "remove";
        $.ajax({
            type: "POST",
            url: mobileDir + "addons/wishform.php",
            data: {
                action: action,
                itemId: itemId
            },
            success: function (response) {
                if (response == "added") {
                    $("#wishlist-button").text("Удалить из списка желаний");
                } else if (response == "removed") {
                    $("#wishlist-button").text("Добавить в список желаний");
                }
            }
        });
    });
});