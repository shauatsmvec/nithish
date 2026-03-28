/**
 * Profile Management (JS + Fetch)
 * Updated to include full registration data (DOB, etc.)
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

document.addEventListener('DOMContentLoaded', async () => {
    const token = localStorage.getItem('token');
    const user_id = localStorage.getItem('user_id');
    
    if (!token || !user_id) {
        window.location.href = 'login.html';
        return;
    }

    const profileView = document.getElementById('profileView');
    const profileEdit = document.getElementById('profileEdit');

    const fetchProfile = async () => {
        try {
            const response = await fetch('php/profile.php', {
                method: 'GET',
                headers: { 
                    'Authorization': `Bearer ${token}`,
                    'X-User-ID': user_id,
                    'Accept': 'application/json'
                }
            });

            const result = await response.json();

            if (response.ok && result.status === "success") {
                // View Mode Population
                document.getElementById('display_name').textContent = result.profile.name || 'N/A';
                document.getElementById('display_username').textContent = result.user.username;
                document.getElementById('display_email').textContent = result.user.email;
                document.getElementById('display_age').textContent = result.profile.age || 'N/A';
                document.getElementById('display_dob').textContent = result.profile.dob || 'N/A';
                document.getElementById('display_mobile').textContent = result.profile.mobile || 'N/A';
                document.getElementById('display_id').textContent = `#${user_id}`;
                
                const avatar = document.getElementById('display_pic');
                if (avatar) {
                    avatar.src = `https://api.dicebear.com/7.x/identicon/svg?seed=${result.user.username}&backgroundColor=CD1C18`;
                }

                // Edit Mode Population
                document.getElementById('edit_name').value = result.profile.name || '';
                document.getElementById('edit_age').value = result.profile.age || '';
                document.getElementById('edit_dob').value = result.profile.dob || '';
                document.getElementById('edit_mobile').value = result.profile.mobile || '';
            } else {
                if (response.status === 401 || response.status === 404) {
                    localStorage.clear();
                    window.location.href = 'login.html';
                } else {
                    UI.showToast(result.message || "Failed to sync profile.", "error");
                }
            }
        } catch (error) {
            UI.showToast("Network failure. Could not sync vault.", "error");
            console.error(error);
        }
    };

    await fetchProfile();

    document.getElementById('editBtn').addEventListener('click', () => {
        profileView.style.display = 'none';
        profileEdit.style.display = 'block';
    });

    document.getElementById('cancelEditBtn').addEventListener('click', () => {
        profileEdit.style.display = 'none';
        profileView.style.display = 'block';
    });

    document.getElementById('logoutBtn').addEventListener('click', () => {
        UI.showToast("Session Terminated.", "info");
        localStorage.clear();
        setTimeout(() => window.location.href = 'login.html', 800);
    });

    document.getElementById('profileForm').addEventListener('submit', async (e) => {
        e.preventDefault();
        
        const updateData = {
            name: document.getElementById('edit_name').value,
            age: document.getElementById('edit_age').value,
            dob: document.getElementById('edit_dob').value,
            mobile: document.getElementById('edit_mobile').value
        };

        const newPassword = document.getElementById('edit_password').value;
        if (newPassword) updateData.password = newPassword;

        UI.setLoading('saveBtn', true, 'Pushing Data...');

        try {
            const response = await fetch('php/profile.php', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'Authorization': `Bearer ${token}`,
                    'X-User-ID': user_id
                },
                body: JSON.stringify(updateData)
            });

            const result = await response.json();

            if (response.ok && result.status === "success") {
                UI.showToast("Vault Updated Successfully.", "success");
                setTimeout(async () => {
                    profileEdit.style.display = 'none';
                    profileView.style.display = 'block';
                    await fetchProfile();
                }, 1000);
            } else {
                UI.showToast(result.message || "Update rejected.", "error");
            }
        } catch (error) {
            UI.showToast("Push failed. Database connection error.", "error");
        } finally {
            UI.setLoading('saveBtn', false);
        }
    });
});
