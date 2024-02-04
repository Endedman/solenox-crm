document.addEventListener("DOMContentLoaded", function () {
    const chatWindow = document.getElementById("chat-window");
    const messageInput = document.getElementById("message");
    const sendButton = document.getElementById("send");

    // Append message to chat window and add reply button event
    function appendMessage(username, message, id) {
      const messageDiv = document.createElement("div");
      messageDiv.innerHTML = `<strong>${username}:</strong> ${message}
            <button class="reply-btn" data-parent="${id}">Reply</button>`;
      chatWindow.appendChild(messageDiv);

      // Add event listeners to all reply buttons
      document.querySelectorAll(".reply-btn").forEach(button => {
        button.addEventListener("click", () => {
          const parentMessageId = button.getAttribute("data-parent");
          messageInput.value = `@${username} `;
          messageInput.focus();
        });
      });
    }

    // Send message via AJAX
    function sendMessage() {
      const message = messageInput.value;
      const xhr = new XMLHttpRequest();
      xhr.open("POST", "ajax_chat.php", true);
      xhr.setRequestHeader("Content-type", "application/x-www-form-urlencoded");
      xhr.onreadystatechange = function () {
        if (xhr.readyState === 4 && xhr.status === 200) {
          const response = JSON.parse(xhr.responseText);
          if (response.success) {
            appendMessage("You", message, response.id); // Pass the new message ID
            messageInput.value = ""; // Clear input field
          }
        }
      };
      const data = "message=" + encodeURIComponent(message);
      xhr.send(data);
    }

    // Update chat by fetching messages from the server
    function updateChat() {
      const xhr = new XMLHttpRequest();
      xhr.open("GET", "ajax_chat.php", true);
      xhr.onreadystatechange = function () {
        if (xhr.readyState === 4 && xhr.status === 200) {
          const messages = JSON.parse(xhr.responseText);
          chatWindow.innerHTML = ""; // Clear chat window
          messages.forEach(msg => {
            appendMessage(msg.username, msg.message, msg.id);
          });
        }
      };
      xhr.send();
    }

    // Add event listener to send button
    sendButton.addEventListener("click", sendMessage);

    // Update chat every 5 seconds
    setInterval(updateChat, 5000);
  });