document.addEventListener("DOMContentLoaded", function () {
    // ✅ Automatically select the first user when the page loads
    let firstUser = document.querySelector(".user-item");
    if (firstUser) {
        firstUser.click();
    }

    // ✅ Add click event to all user items
    document.querySelectorAll(".user-item").forEach(user => {
        user.addEventListener("click", function () {
            let chatWindow = document.getElementById("chat-window");
            chatWindow.style.display = "block"; // ✅ Show chat window

            document.querySelectorAll(".user-item").forEach(u => u.classList.remove("active"));
            this.classList.add("active");

            let receiverId = this.getAttribute("data-id");
            loadMessages(receiverId);
            markMessagesAsRead(receiverId);
        });
    });

    // ✅ Fix Send Button Event Listener
    document.getElementById("send-message").addEventListener("click", sendMessage);

    // ✅ Refresh unread message badges every 5 seconds
    updateUnreadCounts();
    setInterval(updateUnreadCounts, 5000);
});

// ✅ Function to Fetch & Update Unread Message Counts
function updateUnreadCounts() {
    fetch('/get-users-with-unread-count')
        .then(response => response.json())
        .then(users => {
            users.forEach(user => {
                let badge = document.getElementById(`unread-${user.id}`);
                if (!badge) return;

                if (user.unread_count > 0) {
                    badge.textContent = user.unread_count;
                    badge.classList.remove("hidden"); // ✅ Show badge
                } else {
                    badge.classList.add("hidden"); // ✅ Hide badge if no unread messages
                }
            });
        })
        .catch(error => console.error("Error fetching unread messages:", error));
}

// ✅ Function to Load Messages
// ✅ Load Messages & Refresh Every 3 Seconds
function loadMessages(receiverId) {
    document.getElementById('chat-window').classList.remove("hidden");

    fetch(`/messages/${receiverId}`)
        .then(response => response.json())
        .then(messages => {
            let messageContainer = document.getElementById('message-container');
            messageContainer.innerHTML = ''; // Clear previous messages

            messages.forEach(message => {
                let messageElement = document.createElement('div');
                let userId = document.querySelector('meta[name="auth-user-id"]').content;

                let timestamp = new Date(message.created_at);
                let formattedTime = timestamp.toLocaleTimeString([], { hour: '2-digit', minute: '2-digit', hour12: true });

                if (message.sender_id == userId) {
                    messageElement.classList.add('sent-message');
                } else {
                    messageElement.classList.add('received-message');
                }

                messageElement.innerHTML = `
                    <div class="message-bubble">
                        <p class="message-text">${message.message}</p>
                        <span class="message-time">${formattedTime}</span>
                    </div>
                `;

                messageContainer.appendChild(messageElement);
            });

            // Scroll to the latest message
            scrollToBottom();
        })
        .catch(error => console.error('Error loading messages:', error));
}

// ✅ Poll for New Messages Every 3 Seconds
setInterval(() => {
    let activeUser = document.querySelector(".user-item.active");
    if (activeUser) {
        let receiverId = activeUser.getAttribute("data-id");
        loadMessages(receiverId);
    }
}, 3000); // ✅ Fetch new messages every 3 seconds




// ✅ Function to Mark Messages as Read
function markMessagesAsRead(receiverId) {
    fetch(`/mark-messages-as-read/${receiverId}`, {
        method: "POST",
        headers: {
            "X-CSRF-TOKEN": document.querySelector('meta[name="csrf-token"]').getAttribute("content")
        }
    })
    .then(response => response.json())
    .then(() => {
        let badge = document.getElementById(`unread-${receiverId}`);
        if (badge) {
            badge.classList.add("hidden"); // ✅ Hide badge after reading
        }
        updateUnreadCounts(); // ✅ Ensure badge updates immediately
    })
    .catch(error => console.error("Error marking messages as read:", error));
}
// ✅ Send Message & Reload Messages Immediately
function sendMessage() {
    let messageInput = document.getElementById("message-input");
    let messageContent = messageInput.value.trim();
    let receiverElement = document.querySelector(".user-item.active");
    let receiverId = receiverElement ? receiverElement.getAttribute("data-id") : null;

    if (!messageContent || !receiverId) {
        alert("Please select a user and enter a message.");
        return;
    }

    fetch("/messages", {
        method: "POST",
        headers: {
            "Content-Type": "application/json",
            "X-CSRF-TOKEN": document.querySelector('meta[name="csrf-token"]').getAttribute("content")
        },
        body: JSON.stringify({ receiver_id: receiverId, message: messageContent })
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            messageInput.value = "";
            loadMessages(receiverId); // ✅ Reload messages after sending
        } else {
            alert("Failed to send message.");
        }
    })
    .catch(error => console.error("Error sending message:", error));
}


// ✅ Scroll to Bottom Automatically
function scrollToBottom() {
    let messageContainer = document.getElementById('message-container');
    setTimeout(() => {
        messageContainer.scrollTop = messageContainer.scrollHeight;
    }, 300);
}

// ✅ Export Functions for Global Use
window.loadMessages = loadMessages;
window.sendMessage = sendMessage;
window.markMessagesAsRead = markMessagesAsRead;
