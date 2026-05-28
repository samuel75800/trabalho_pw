(() => {
  'use strict';

  const THEME_KEY = 'puppyco_theme';

  const applyTheme = (theme) => {
    document.documentElement.setAttribute('data-theme', theme);
    localStorage.setItem(THEME_KEY, theme);
  };

  const getTheme = () =>
    localStorage.getItem(THEME_KEY) ||
    (window.matchMedia('(prefers-color-scheme: dark)').matches ? 'dark' : 'light');

  applyTheme(getTheme());

  const toggleTheme = () =>
    applyTheme(getTheme() === 'dark' ? 'light' : 'dark');

  window.puppyTheme = { toggle: toggleTheme, get: getTheme };

  const initCursor = () => {
    if (window.matchMedia('(pointer: coarse)').matches) return;
    if (window.matchMedia('(prefers-reduced-motion: reduce)').matches) return;

    const dot  = document.getElementById('cursor-dot');
    const ring = document.getElementById('cursor-ring');
    if (!dot || !ring) return;

    let mouseX = 0, mouseY = 0;
    let ringX  = 0, ringY  = 0;
    let rafId  = null;

    document.addEventListener('mousemove', (e) => {
      mouseX = e.clientX;
      mouseY = e.clientY;

      dot.style.left = mouseX + 'px';
      dot.style.top  = mouseY + 'px';
    });

    const animateRing = () => {
      ringX += (mouseX - ringX) * 0.18;
      ringY += (mouseY - ringY) * 0.18;

      ring.style.left = ringX + 'px';
      ring.style.top  = ringY + 'px';

      rafId = requestAnimationFrame(animateRing);
    };
    animateRing();

    document.addEventListener('mousedown', () => dot.classList.add('clicking'));
    document.addEventListener('mouseup',   () => dot.classList.remove('clicking'));

    document.addEventListener('mouseleave', () => {
      dot.style.opacity  = '0';
      ring.style.opacity = '0';
    });
    document.addEventListener('mouseenter', () => {
      dot.style.opacity  = '1';
      ring.style.opacity = '1';
    });
  };

  const toastIcons = {
    success: '✓',
    error:   '✕',
    info:    'i',
  };

  window.showToast = (message, type = 'info', duration = 3000) => {
    const container = document.getElementById('toast-container');
    if (!container) return;

    const toast = document.createElement('div');
    toast.className = `toast toast-${type}`;
    toast.style.position = 'relative';
    toast.innerHTML = `
      <span class="toast-icon">${toastIcons[type] ?? 'i'}</span>
      <span>${message}</span>
      <div class="toast-bar" style="animation-duration: ${duration}ms"></div>
    `;

    container.appendChild(toast);

    requestAnimationFrame(() => {
      requestAnimationFrame(() => toast.classList.add('show'));
    });

    setTimeout(() => {
      toast.classList.remove('show');
      setTimeout(() => toast.remove(), 400);
    }, duration);
  };

  window.openModal = (id) => {
    const overlay = document.getElementById(id);
    if (!overlay) return;
    overlay.classList.add('open');
    document.body.style.overflow = 'hidden';

    const firstInput = overlay.querySelector('input, select, textarea');
    if (firstInput) setTimeout(() => firstInput.focus(), 250);
  };

  window.closeModal = (id) => {
    const overlay = document.getElementById(id);
    if (!overlay) return;
    overlay.classList.remove('open');
    document.body.style.overflow = '';
  };

  document.addEventListener('click', (e) => {
    if (e.target.classList.contains('modal-overlay')) {
      e.target.classList.remove('open');
      document.body.style.overflow = '';
    }
  });

  document.addEventListener('keydown', (e) => {
    if (e.key === 'Escape') {
      document.querySelectorAll('.modal-overlay.open').forEach((m) => {
        m.classList.remove('open');
        document.body.style.overflow = '';
      });
    }
  });

  window.confirmAction = (message, onConfirm) => {
    const existing = document.getElementById('confirm-modal');
    if (existing) existing.remove();

    const overlay = document.createElement('div');
    overlay.id = 'confirm-modal';
    overlay.className = 'modal-overlay';
    overlay.innerHTML = `
      <div class="modal" style="max-width:380px; text-align:center;">
        <div style="font-size:2rem; margin-bottom:12px;">⚠️</div>
        <h3 style="margin-bottom:10px; font-size:1rem;">${message}</h3>
        <div class="flex gap-sm" style="justify-content:center; margin-top:20px;">
          <button class="btn btn-ghost btn-sm" onclick="closeModal('confirm-modal')">Cancelar</button>
          <button class="btn btn-danger btn-sm" id="confirm-yes">Confirmar</button>
        </div>
      </div>
    `;

    document.body.appendChild(overlay);
    requestAnimationFrame(() => {
      requestAnimationFrame(() => openModal('confirm-modal'));
    });

    document.getElementById('confirm-yes').addEventListener('click', () => {
      closeModal('confirm-modal');
      setTimeout(onConfirm, 200);
    });
  };

  const markActiveNav = () => {
    const path = window.location.pathname.split('/').pop();
    document.querySelectorAll('.navbar-nav a').forEach((a) => {
      const href = a.getAttribute('href')?.split('/').pop();
      if (href === path) a.classList.add('active');
    });
  };

  window.validateForm = (formEl) => {
    let valid = true;
    formEl.querySelectorAll('[required]').forEach((field) => {
      const err = field.parentElement.querySelector('.field-error');
      if (!field.value.trim()) {
        field.style.borderColor = 'var(--danger)';
        if (err) err.style.display = 'block';
        valid = false;
      } else {
        field.style.borderColor = '';
        if (err) err.style.display = 'none';
      }
    });
    return valid;
  };

  document.addEventListener('input', (e) => {
    if (e.target.matches('.input')) {
      e.target.style.borderColor = '';
      const err = e.target.parentElement.querySelector('.field-error');
      if (err) err.style.display = 'none';
    }
  });

  window.initTableSearch = (inputId, tableId) => {
    const input = document.getElementById(inputId);
    const table = document.getElementById(tableId);
    if (!input || !table) return;

    input.addEventListener('input', () => {
      const q = input.value.toLowerCase();
      table.querySelectorAll('tbody tr').forEach((row) => {
        row.style.display = row.textContent.toLowerCase().includes(q) ? '' : 'none';
      });
    });
  };

  const animatePage = () => {
    const main = document.querySelector('main');
    if (main) main.classList.add('page-enter');
  };

  document.addEventListener('DOMContentLoaded', () => {
    initCursor();
    markActiveNav();
    animatePage();

    document.querySelectorAll('[data-theme-toggle]').forEach((btn) => {
      btn.addEventListener('click', toggleTheme);
    });
  });

})();