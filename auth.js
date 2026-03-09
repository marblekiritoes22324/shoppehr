/**
 * Shopee HR - Auth helper (client-side only, no backend)
 */
const Auth = {
  KEY: 'shoppehr_session',

  getSession() {
    try {
      const data = localStorage.getItem(this.KEY);
      return data ? JSON.parse(data) : null;
    } catch {
      return null;
    }
  },

  async login(credentials) {
    console.log('Auth.login called with:', credentials.email);
    if (window.location.protocol === 'file:') {
      throw new Error('CRITICAL: You are opening the file directly (file://). PHP will not work.\n\nPlease open the server URL: http://localhost:8080/Login.html');
    }
    let response;
    try {
      response = await fetch('login_backend.php', {
        method: 'POST',
        headers: { 'Content-Type': 'application/json' },
        body: JSON.stringify(credentials)
      });
    } catch (networkErr) {
      console.error('Network error:', networkErr);
      throw new Error('Cannot connect to server. Make sure Apache/Laragon is running and you are accessing via http://localhost:8080/');
    }
    if (!response.ok) {
      const text = await response.text();
      console.error('Server error:', response.status, text);
      throw new Error('Server error (' + response.status + '). Check that PHP and MySQL are running.');
    }
    let result;
    try {
      result = await response.json();
    } catch (jsonErr) {
      const text = await response.text();
      console.error('Invalid JSON response:', text);
      throw new Error('Server returned invalid response. Check PHP error logs.');
    }
    console.log('Auth.login response:', result);
    if (result.success) {
      if (result.require_otp) {
        return result; // Login.html handles the redirect to allow UI feedback if needed, but we can also redirect here
      }
      localStorage.setItem(this.KEY, JSON.stringify(result.user));
      return result;
    }
    throw new Error(result.message || 'Login failed');
  },


  async fetchOnlineAdmins() {
    try {
      const response = await fetch('get_online_admins.php');
      const result = await response.json();
      return result.admins || [];
    } catch (e) {
      console.error('Error fetching online admins:', e);
      return [];
    }
  },

  logout() {
    localStorage.removeItem(this.KEY);
    window.location.href = 'Login.html';
  },

  isAdmin() {
    const s = this.getSession();
    return s && s.role === 'admin';
  },

  isEmployee() {
    const s = this.getSession();
    return s && s.role === 'employee';
  },

  requireAuth() {
    const session = this.getSession();
    if (!session) {
      window.location.href = 'Login.html';
      return null;
    }
    return session;
  },

  requireAdmin() {
    const session = this.requireAuth();
    if (!session) return null;
    if (session.role !== 'admin') {
      window.location.href = 'Dashboard.html';
      return null;
    }
    return session;
  },

  applyRoleNav() {
    const session = this.requireAuth();
    if (!session) return;

    const adminOnly = document.querySelectorAll('[data-admin-only]');
    const employeeOnly = document.querySelectorAll('[data-employee-only]');

    if (session.role === 'admin') {
      adminOnly.forEach(el => el.style.display = '');
      employeeOnly.forEach(el => el.style.display = 'none');
    } else {
      adminOnly.forEach(el => el.style.display = 'none');
      employeeOnly.forEach(el => el.style.display = '');
    }
  },

  updateUserDisplay() {
    const session = this.getSession();
    if (!session) return;

    // Split name into first and last
    const nameParts = session.name.trim().split(' ');
    const firstname = nameParts[0] || '';
    const lastname = nameParts.length > 1 ? nameParts.slice(1).join(' ') : '';

    document.querySelectorAll('[data-user-display]').forEach(el => {
      const type = el.getAttribute('data-user-display');
      if (type === 'name') {
        el.textContent = session.name;
      } else if (type === 'firstname') {
        el.textContent = firstname;
      } else if (type === 'lastname') {
        el.textContent = lastname;
      } else if (type === 'email') {
        el.textContent = session.email;
      } else {
        el.textContent = session.name + ' | ' + session.email;
      }
    });

    document.querySelectorAll('[data-role-display]').forEach(el => {
      el.textContent = session.role === 'admin' ? 'Administrator' : 'Employee';
    });
  }
};
