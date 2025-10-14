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

// Floating label support for autofill / prefilled values across all login forms
(function(){
    const forms = ['student-login-form','prof-login-form','admin-login-form']
        .map(id=>document.getElementById(id))
        .filter(Boolean);
    if(!forms.length) return;
    const processInput = (el)=>{
        if(el.value) el.classList.add('filled'); else el.classList.remove('filled');
    };
    forms.forEach(f=>{
        const inputs = f.querySelectorAll('.float-stack input');
        inputs.forEach(i=>{
            processInput(i);
            ['input','change'].forEach(ev=> i.addEventListener(ev, ()=>processInput(i)) );
        });
    });
    // Re-run after short delay for late autofill
    setTimeout(()=>forms.forEach(f=>f.querySelectorAll('.float-stack input').forEach(processInput)), 120);
    document.addEventListener('visibilitychange', ()=>{ if(!document.hidden) forms.forEach(f=>f.querySelectorAll('.float-stack input').forEach(processInput)); });
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

// Numeric-only enforcement (for ID fields). Applies to any input with class 'numeric-only'.
(function(){
    const inputs = document.querySelectorAll('input.numeric-only');
    if(!inputs.length) return;
    const allowedControl = ['Backspace','Delete','ArrowLeft','ArrowRight','Tab','Home','End'];
    inputs.forEach(inp=>{
        // Block non-digit key presses (except control keys)
        inp.addEventListener('keydown', e=>{
            if(allowedControl.includes(e.key) || (e.ctrlKey||e.metaKey)) return;
            if(e.key === 'Enter') return; // allow submit
            if(/^[0-9]$/.test(e.key)) return;
            e.preventDefault();
        });
        // Sanitize on input (covers paste, drag-drop, autofill anomalies)
        inp.addEventListener('input', ()=>{
            const max = inp.getAttribute('maxlength') ? parseInt(inp.getAttribute('maxlength'),10) : null;
            let v = inp.value.replace(/\D+/g,'');
            if(max) v = v.slice(0,max);
            if(inp.value !== v) inp.value = v;
            // Maintain filled class for floating label consistency
            if(v) inp.classList.add('filled'); else inp.classList.remove('filled');
        });
        // Initial clean (in case old stored value has stray chars)
        inp.value = inp.value.replace(/\D+/g,'');
    });
})();

// Auto-dismiss non-lockout login error messages after 5 seconds.
// Criteria: target elements with .login-error that contain text not including 'Too many attempts'.
// We don't remove lockout messages because they are replaced dynamically by countdown scripts.
(function(){
    const DISMISS_MS = 5000;
    // Use a slight delay so server-rendered errors are in DOM, and countdown scripts (if any) can attach.
    window.addEventListener('DOMContentLoaded', ()=>{
        const nodes = document.querySelectorAll('.login-error');
        if(!nodes.length) return;
        nodes.forEach(node=>{
            // If this element will be updated by a lock countdown (contains substring or data-lock present in parent) skip
            const parent = node.closest('.options-row');
            const isLocked = parent && (
                parent.hasAttribute('data-lock-student') && parent.getAttribute('data-lock-student') ||
                parent.hasAttribute('data-lock-prof') && parent.getAttribute('data-lock-prof') ||
                parent.hasAttribute('data-lock-admin') && parent.getAttribute('data-lock-admin')
            );
            const text = (node.textContent||'').toLowerCase();
            if(isLocked || text.includes('too many attempts')) return; // don't auto-hide lockout countdown
            if(!text.trim()) return; // nothing to hide
            setTimeout(()=>{
                // Add fading class; keep element height so layout (Remember me position) doesn't shift.
                node.classList.add('fade-out-login-error');
                // After fade completes, clear text but keep an invisible placeholder character to preserve height precisely
                setTimeout(()=>{
                    if(node.classList.contains('fade-out-login-error')){
                        node.textContent = '\u200B'; // zero-width space retains height
                    }
                }, 400);
            }, DISMISS_MS);
        });
    });
})();

