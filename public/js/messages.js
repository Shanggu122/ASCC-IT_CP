function toggleChat() {
    const chat = document.getElementById("chatOverlay");
    chat.classList.toggle("open");
}

function sendMessage() {
    const input = document.getElementById("userInput");
    const message = input.value.trim();
    if (message === "") return;

    const chatBody = document.getElementById("chatBody");

    // Add user's message
    const userMsg = document.createElement("div");
    userMsg.className = "message user";
    userMsg.textContent = message;
    chatBody.appendChild(userMsg);

    // Fake bot reply
    const botMsg = document.createElement("div");
    botMsg.className = "message bot";
    botMsg.textContent =
        "Thank you for your message. I’ll get back to you soon!";
    setTimeout(() => {
        chatBody.appendChild(botMsg);
        chatBody.scrollTop = chatBody.scrollHeight;
    }, 600);

    input.value = "";
    chatBody.scrollTop = chatBody.scrollHeight;
}

function handleKey(e) {
    if (e.key === "Enter") {
        sendMessage();
    }
}

// Update existing click handlers to trigger overlays
document.querySelectorAll('a[href="#"]').forEach((anchor) => {
    anchor.addEventListener("click", function (e) {
        e.preventDefault();
        if (this.textContent.includes("Change Password")) {
            toggleOverlay("passwordOverlay");
        } else if (this.textContent.includes("Email Notification")) {
            toggleOverlay("notificationOverlay");
        }
    });
});
function togglePanel(panelId) {
    const panel = document.getElementById(panelId);
    panel.classList.toggle("open");
}

function togglePasswordVisibility(inputId, icon) {
    const input = document.getElementById(inputId);
    if (input.type === "password") {
        input.type = "text";
        icon.classList.remove("bx-show");
        icon.classList.add("bx-hide");
    } else {
        input.type = "password";
        icon.classList.remove("bx-hide");
        icon.classList.add("bx-show");
    }
}
function loadChat(name) {
    document.getElementById("chat-person").innerText = name;
    document.getElementById("chat-body").innerHTML = `
      <div class="message received">Hi ${name}</div>
      <div class="message sent">Good Morning!</div>
    `;
}

function startVideoCall() {
    alert("Video call started!");
}

function sendMessage() {
    const input = document.getElementById("message-input");
    const text = input.value.trim();
    if (text !== "") {
        const chatBody = document.getElementById("chat-body");
        const message = document.createElement("div");
        message.className = "message sent";
        message.textContent = text;
        chatBody.appendChild(message);
        input.value = "";
        chatBody.scrollTop = chatBody.scrollHeight;
    }
}
// Add event listener to handle "Enter" key for sending messages
document.getElementById("message").addEventListener("keydown", function (e) {
    if (e.key === "Enter" && !e.shiftKey) {
        e.preventDefault();
        sendMessage();
    }
});

// Function to send message
function sendMessage() {
    const input = document.getElementById("message");
    const text = input.value.trim();
    if (text !== "") {
        const chatBody = document.getElementById("chat-body");
        const message = document.createElement("div");
        message.className = "message sent";
        message.textContent = text;
        chatBody.appendChild(message);
        input.value = ""; // Clear input field after sending message
        chatBody.scrollTop = chatBody.scrollHeight; // Scroll to bottom of chat body
    }
}

// Add this function to ensure you can enter backspace and edit messages
function handleKey(e) {
    if (e.key === "Enter" && !e.shiftKey) {
        // Only send message on Enter (not Shift+Enter)
        sendMessage();
    }
}

// Add to your messages.js
document.getElementById("attach-btn").addEventListener("click", function () {
    document.getElementById("file-input").click();
});

document.getElementById("file-input").addEventListener("change", function (e) {
    const file = e.target.files[0];
    if (!file) return;

    const formData = new FormData();
    formData.append("file", file);
    formData.append("bookingId", bookingId); // set this variable as needed
    formData.append("recipient", currentChatPerson); // set this variable as needed
    formData.append("_token", "{{ csrf_token() }}");

    fetch("/send-file", {
        method: "POST",
        body: formData,
    })
        .then((response) => response.json())
        .then((data) => {
            if (data.status === "File sent!") {
                const chatBody = document.getElementById("chat-body");
                let msgDiv = document.createElement("div");
                msgDiv.className = "message sent";
                if (file.type.startsWith("image/")) {
                    let img = document.createElement("img");
                    img.src = data.file_url;
                    img.style.maxWidth = "150px";
                    img.style.borderRadius = "8px";
                    msgDiv.appendChild(img);
                } else {
                    let link = document.createElement("a");
                    link.href = data.file_url;
                    link.textContent = file.name;
                    link.target = "_blank";
                    msgDiv.appendChild(link);
                }
                chatBody.appendChild(msgDiv);
                chatBody.scrollTop = chatBody.scrollHeight;
            } else {
                alert("Error sending file");
            }
        })
        .catch(() => alert("Error sending file"));
});
