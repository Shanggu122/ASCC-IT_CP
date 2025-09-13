// Password visibility toggle (supports old/new IDs)
// Generic password visibility toggle supporting multiple login pages.
// Logic: find the nearest password input within the same .password-group (so pages
// can have ids like password / prof-password / admin-password). Update icon
// classes and ARIA attributes for accessibility.
(function(){
    const btn = document.getElementById('toggle-password-btn') || document.getElementById('toggle-password');
    if(!btn) return;
    btn.addEventListener('click', function(){
        // Prefer closest password input in the same group
        let pwd = btn.closest('.password-group')?.querySelector('input[type="password"], input[data-type="password"], input') || null;
        // If it was already toggled to text previously, the selector above may not match type=password, so fallback by id heuristics
        if(!pwd){
            const candidateIds = ['password','prof-password','admin-password'];
            for(const id of candidateIds){ const el = document.getElementById(id); if(el){ pwd = el; break; } }
        }
        if(!pwd) return;
        const showing = pwd.type === 'text';
        pwd.type = showing ? 'password' : 'text';
        const icon = btn.querySelector('i');
        if(icon){
            icon.classList.remove('bx-hide','bx-show');
            icon.classList.add(showing ? 'bx-hide' : 'bx-show');
        }
        btn.setAttribute('aria-pressed', String(!showing));
        btn.setAttribute('aria-label', showing ? 'Show password' : 'Hide password');
    });
})();

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

function showError(message) {
    let errorDiv = document.createElement("div");
    errorDiv.className = "alert alert-danger";
    errorDiv.textContent = message;
    document.body.prepend(errorDiv);
    setTimeout(() => errorDiv.remove(), 3000);
}
// Usage: showError('Incorrect Student ID or Password.');
