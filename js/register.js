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

document.getElementById('registerForm').addEventListener('submit', async (e) => {
    e.preventDefault();

    const password = document.getElementById('password').value;
    const confirm_password = document.getElementById('confirm_password').value;

    if (password !== confirm_password) {
        UI.showToast("Security keys do not match!", "error");
        return;
    }

    // Data package matching backend requirements
    const data = {
        username: document.getElementById('username').value,
        email: document.getElementById('email').value,
        password: password,
        name: document.getElementById('name').value,
        age: document.getElementById('age').value,
        dob: document.getElementById('dob').value,
        mobile: document.getElementById('mobile').value
    };

    UI.setLoading('submitBtn', true, 'Creating Identity...');

    try {
        // Send registration as JSON via fetch
        const response = await fetch('php/register.php', {
            method: 'POST',
            headers: { 
                'Content-Type': 'application/json',
                'Accept': 'application/json'
            },
            body: JSON.stringify(data)
        });

        const result = await response.json();

        if (response.ok && result.status === "success") {
            UI.showToast(result.message || "Membership approved! Proceed to Login.", "success");
            setTimeout(() => window.location.href = 'login.html', 1500);
        } else {
            UI.showToast(result.message || "Application denied.", "error");
        }
    } catch (error) {
        UI.showToast("Network failure. Connection lost.", "error");
        console.error("Register error:", error);
    } finally {
        UI.setLoading('submitBtn', false);
    }
});
