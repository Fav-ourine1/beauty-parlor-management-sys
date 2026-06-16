/* ================================================================
   Mbagathi Beauty Parlour — App JS
   ================================================================ */

// ── API helpers ───────────────────────────────────────────────

const API = {
  async request(method, path, body = null) {
    const opts = {
      method,
      headers: { 'Content-Type': 'application/json', 'Accept': 'application/json' },
    };
    if (body) opts.body = JSON.stringify(body);
    const res  = await fetch(path, opts);
    const data = await res.json().catch(() => ({}));
    if (!res.ok || !data.success) {
      throw new Error(data.message || 'Request failed');
    }
    return data;
  },
  get:    (path)        => API.request('GET',    path),
  post:   (path, body)  => API.request('POST',   path, body),
  put:    (path, body)  => API.request('PUT',    path, body),
  delete: (path, body)  => API.request('DELETE', path, body),
};

// ── Toast notifications ───────────────────────────────────────

function toast(message, type = 'info') {
  let container = document.getElementById('toast-container');
  if (!container) {
    container = document.createElement('div');
    container.id = 'toast-container';
    document.body.appendChild(container);
  }
  const el = document.createElement('div');
  el.className = `toast ${type}`;
  el.textContent = message;
  container.appendChild(el);
  setTimeout(() => el.remove(), 4000);
}

// ── Sidebar mobile toggle ─────────────────────────────────────

document.addEventListener('DOMContentLoaded', () => {
  const hamburger = document.querySelector('.hamburger');
  const sidebar   = document.querySelector('.sidebar');
  const overlay   = document.getElementById('sidebar-overlay');

  if (hamburger && sidebar) {
    hamburger.addEventListener('click', () => {
      sidebar.classList.toggle('open');
      if (overlay) overlay.classList.toggle('active');
    });
  }
  if (overlay) {
    overlay.addEventListener('click', () => {
      sidebar.classList.remove('open');
      overlay.classList.remove('active');
    });
  }

  // Logout button
  const logoutBtn = document.getElementById('btn-logout');
  if (logoutBtn) {
    logoutBtn.addEventListener('click', async () => {
      try {
        await API.post('/api/auth/logout');
      } catch (_) { /* ignore */ }
      window.location.href = '/login';
    });
  }
});

// ── Appointment status update ─────────────────────────────────

async function updateAppointmentStatus(id, status, btn) {
  btn.disabled = true;
  try {
    await API.put(`/api/appointments/${id}`, { status });
    toast(`Appointment marked as ${status}`, 'success');
    setTimeout(() => location.reload(), 800);
  } catch (e) {
    toast(e.message, 'error');
    btn.disabled = false;
  }
}

// ── Login form ────────────────────────────────────────────────

function initLoginForm() {
  const form = document.getElementById('login-form');
  if (!form) return;

  form.addEventListener('submit', async (e) => {
    e.preventDefault();
    const btn = form.querySelector('[type=submit]');
    const err = document.getElementById('login-error');
    btn.disabled = true;
    btn.textContent = 'Signing in…';
    err.textContent = '';
    err.style.display = 'none';

    try {
      const data = await API.post('/api/auth/login', {
        email:    form.email.value,
        password: form.password.value,
      });
      const role = data.data?.role;
      if      (role === 'admin')  window.location.href = '/admin/dashboard';
      else if (role === 'staff')  window.location.href = '/staff/dashboard';
      else                        window.location.href = '/client/dashboard';
    } catch (e) {
      err.textContent    = e.message;
      err.style.display  = 'block';
      btn.disabled       = false;
      btn.textContent    = 'Sign In';
    }
  });
}

// ── Register form ─────────────────────────────────────────────

function initRegisterForm() {
  const form = document.getElementById('register-form');
  if (!form) return;

  form.addEventListener('submit', async (e) => {
    e.preventDefault();
    const btn = form.querySelector('[type=submit]');
    const err = document.getElementById('register-error');
    btn.disabled   = true;
    btn.textContent = 'Creating account…';
    err.textContent = '';
    err.style.display = 'none';

    if (form.password.value !== form.password_confirm.value) {
      err.textContent   = 'Passwords do not match.';
      err.style.display = 'block';
      btn.disabled      = false;
      btn.textContent   = 'Create Account';
      return;
    }

    try {
      await API.post('/api/auth/register', {
        full_name: form.full_name.value,
        email:     form.email.value,
        phone:     form.phone.value,
        password:  form.password.value,
      });
      window.location.href = '/client/dashboard';
    } catch (e) {
      err.textContent   = e.message;
      err.style.display = 'block';
      btn.disabled      = false;
      btn.textContent   = 'Create Account';
    }
  });
}

// ── Booking form ──────────────────────────────────────────────

function initBookingForm() {
  const form = document.getElementById('booking-form');
  if (!form) return;

  const serviceItems  = document.querySelectorAll('.service-item');
  const totalEl       = document.getElementById('booking-total');
  const summaryEl     = document.getElementById('booking-summary');
  const selectedIds   = new Set();

  function updateTotal() {
    let total = 0;
    document.querySelectorAll('.service-item.selected').forEach(item => {
      total += parseFloat(item.dataset.price);
    });
    if (totalEl) totalEl.textContent = `KES ${total.toLocaleString('en-KE', { minimumFractionDigits: 2 })}`;
    if (summaryEl) summaryEl.style.display = selectedIds.size > 0 ? 'block' : 'none';
  }

  serviceItems.forEach(item => {
    item.addEventListener('click', () => {
      const cb  = item.querySelector('input[type=checkbox]');
      const id  = item.dataset.id;
      cb.checked = !cb.checked;
      item.classList.toggle('selected', cb.checked);
      cb.checked ? selectedIds.add(id) : selectedIds.delete(id);
      updateTotal();
    });
  });

  form.addEventListener('submit', async (e) => {
    e.preventDefault();
    const btn = form.querySelector('[type=submit]');
    const err = document.getElementById('booking-error');
    err.style.display = 'none';

    if (selectedIds.size === 0) {
      err.textContent   = 'Please select at least one service.';
      err.style.display = 'block';
      return;
    }

    btn.disabled    = true;
    btn.textContent = 'Booking…';

    try {
      const res = await API.post('/api/appointments', {
        appointment_date: form.appointment_date.value,
        start_time:       form.start_time.value,
        end_time:         form.end_time.value,
        service_ids:      [...selectedIds].map(Number),
        notes:            form.notes?.value || '',
      });
      toast('Appointment booked!', 'success');
      setTimeout(() => window.location.href = '/client/dashboard', 1000);
    } catch (e) {
      err.textContent   = e.message;
      err.style.display = 'block';
      btn.disabled      = false;
      btn.textContent   = 'Book Appointment';
    }
  });
}

// ── Init ──────────────────────────────────────────────────────

document.addEventListener('DOMContentLoaded', () => {
  initLoginForm();
  initRegisterForm();
  initBookingForm();
});
