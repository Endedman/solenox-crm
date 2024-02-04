
$(document).ready(function () {
    let currentCategoryId = null;
    const categoriesDiv = document.getElementById("Categories");
    const contentWindowDiv = document.getElementById("ContentWindow");

    categoriesDiv.addEventListener("click", function (event) {
      if (event.target.classList.contains("category-link")) {
        event.preventDefault();
        currentCategoryId = event.target.getAttribute("data-category-id");
        loadCategoryContent(currentCategoryId);
      }
    });
    const appWindowDiv = document.getElementById("AppWindow");

    contentWindowDiv.addEventListener("click", function (event) {
      if (event.target.classList.contains("file-link")) {
        event.preventDefault();
        const fileId = event.target.getAttribute("data-file-id");
        loadAppContent(fileId);
      }
    });

    function loadAppContent(fileId) {
      const xhr = new XMLHttpRequest();
      xhr.open("GET", "/app.php?id=" + fileId, true);

      xhr.onreadystatechange = function () {
        if (xhr.readyState === 4 && xhr.status === 200) {
          appWindowDiv.innerHTML = xhr.responseText;
        }
      };

      xhr.send();
    }

    function loadCategoryContent(categoryId, pageNum = 1) {
      const xhr = new XMLHttpRequest();
      xhr.open("GET", "/content.php?category_id=" + categoryId + "&page=" + pageNum, true);

      xhr.onreadystatechange = function () {
        if (xhr.readyState === 4 && xhr.status === 200) {
          contentWindowDiv.innerHTML = xhr.responseText;
        }
      };

      xhr.send();
    }

    $(document).on('click', '.page-link', function (e) {
      e.preventDefault();
      var pageNum = $(this).data('page-number');
      if (currentCategoryId) {
        loadCategoryContent(currentCategoryId, pageNum);
      }
    });
  });