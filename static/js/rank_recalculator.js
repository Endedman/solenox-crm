document.addEventListener("DOMContentLoaded", function () {
    const recalculateRankButton = document.getElementById("recalculateRankButton");

    recalculateRankButton.addEventListener("click", function () {
      const xhr = new XMLHttpRequest();
      xhr.open("GET", "/recalculate_rank.php", true); // Путь к файлу на сервере для перерасчета ранга

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