$(document).ready(function () {
    $('#verificationForm').on('submit', function (e) {
      e.preventDefault();

      $.ajax({
        url: '/verify_email.php',    // Путь к вашему PHP обработчику
        type: 'POST',
        data: {
          email: $('#email').val(),
          token: $('#token').val()
        },
        success: function (response) {
          $('#result').html(response);
        },
        error: function (jqXHR, textStatus, errorThrown) {
          $('#result').html('<p>Произошла ошибка при отправке запроса.</p>');
        }
      });
    });
  });