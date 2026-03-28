/**
 * Hedgehog UI Helper Utilities
 */
const UI = {
    showToast: (message, type = 'error') => {
        const container = document.getElementById('toast-container');
        if (!container) return;
        
        const toast = document.createElement('div');
        toast.className = `toast ${type}`;
        toast.innerHTML = `<span>${message}</span>`;
        container.appendChild(toast);
        
        setTimeout(() => {
            toast.style.opacity = '0';
            toast.style.transform = 'translateX(50px)';
            setTimeout(() => toast.remove(), 400);
        }, 3500);
    },

    setLoading: (btnId, isLoading, text = 'Processing...') => {
        const btn = document.getElementById(btnId);
        if (!btn) return;
        
        if (isLoading) {
            btn.disabled = true;
            btn._originalHTML = btn.innerHTML;
            btn.innerHTML = `<div class="spinner"></div> <span>${text}</span>`;
        } else {
            btn.disabled = false;
            btn.innerHTML = btn._originalHTML || btn.innerHTML;
        }
    }
};

document.getElementById('loginForm').addEventListener('submit', async (e) => {
    e.preventDefault();

    const identifier = document.getElementById('identifier').value;
    const password = document.getElementById('password').value;
    const form = document.getElementById('loginForm');

    UI.setLoading('submitBtn', true, 'Authenticating...');

    try {
        // Send login data as JSON using fetch
        const response = await fetch('php/login.php', {
            method: 'POST',
            headers: { 
                'Content-Type': 'application/json',
                'Accept': 'application/json'
            },
            body: JSON.stringify({ identifier, password })
        });

        const result = await response.json();

        if (response.ok && result.status === "success") {
            UI.showToast(result.message || "Identity Verified. Redirecting...", "success");
            localStorage.setItem('token', result.token);
            localStorage.setItem('user_id', result.user_id);
            setTimeout(() => window.location.href = 'profile.html', 1200);
        } else {
            form.classList.add('shake');
            setTimeout(() => form.classList.remove('shake'), 400);
            UI.showToast(result.message || "Authentication failed.", "error");
        }
    } catch (error) {
        UI.showToast("Network link interrupted. Try again.", "error");
        console.error("Login error:", error);
    } finally {
        UI.setLoading('submitBtn', false);
    }
});
