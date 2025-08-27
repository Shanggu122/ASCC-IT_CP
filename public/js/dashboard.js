// === Chatbot ===
function toggleChat() {
    const overlay = document.getElementById('chatOverlay');
    overlay.classList.toggle('open');
    const bell = document.getElementById('mobileNotificationBell');
    const isOpen = overlay.classList.contains('open');
    document.body.classList.toggle('chat-open', isOpen);
    if (bell) {
        if (isOpen) {
            bell.style.zIndex = '0';
            bell.style.pointerEvents = 'none';
            bell.style.opacity = '0'; /* visually hide without layout shift */
        } else {
            bell.style.zIndex = '';
            bell.style.pointerEvents = '';
            bell.style.opacity = '';
        }
    }
}

const csrfToken = document.querySelector('meta[name="csrf-token"]').getAttribute('content');
const chatForm = document.getElementById('chatForm');
const input = document.getElementById('message');
const chatBody = document.getElementById('chatBody');

chatForm.addEventListener('submit', async function (e) {
    e.preventDefault();
    const text = input.value.trim();
    if (!text) return;

    const um = document.createElement('div');
    um.classList.add('message', 'user');
    um.innerText = text;
    chatBody.appendChild(um);
    chatBody.scrollTop = chatBody.scrollHeight;
    input.value = '';

    const res = await fetch('/chat', {
        method: 'POST',
        credentials: 'same-origin',
        headers: {
            Accept: 'application/json',
            'Content-Type': 'application/json',
            'X-CSRF-TOKEN': csrfToken,
        },
        body: JSON.stringify({ message: text }),
    });

    if (!res.ok) {
        const err = await res.json();
        const bm = document.createElement('div');
        bm.classList.add('message', 'bot');
        bm.innerText = err.message || 'Server error.';
        chatBody.appendChild(bm);
        return;
    }

    const { reply } = await res.json();
    const bm = document.createElement('div');
    bm.classList.add('message', 'bot');
    bm.innerText = reply;
    chatBody.appendChild(bm);
    chatBody.scrollTop = chatBody.scrollHeight;
});
