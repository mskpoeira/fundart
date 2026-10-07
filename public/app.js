(() => {
  const root = document.documentElement;
  const body = document.body;
  const menuButton = document.querySelector('[data-menu-toggle]');
  const nav = document.querySelector('[data-nav]');
  const contrastButton = document.querySelector('[data-contrast]');
  const year = document.querySelector('[data-year]');
  let scale = Number(localStorage.getItem('fundart-font-scale') || '1');
  const contrast = localStorage.getItem('fundart-contrast') === '1';

  const applyScale = () => root.style.setProperty('--font-scale', String(scale));
  applyScale();

  if (contrast) {
    body.classList.add('high-contrast');
    contrastButton?.setAttribute('aria-pressed', 'true');
  }

  document.querySelectorAll('[data-font]').forEach((button) => {
    button.addEventListener('click', () => {
      scale += button.dataset.font === 'up' ? 0.08 : -0.08;
      scale = Math.max(0.9, Math.min(1.25, Number(scale.toFixed(2))));
      localStorage.setItem('fundart-font-scale', String(scale));
      applyScale();
    });
  });

  contrastButton?.addEventListener('click', () => {
    const enabled = body.classList.toggle('high-contrast');
    contrastButton.setAttribute('aria-pressed', String(enabled));
    localStorage.setItem('fundart-contrast', enabled ? '1' : '0');
  });

  menuButton?.addEventListener('click', () => {
    const open = nav?.classList.toggle('open') || false;
    menuButton.setAttribute('aria-expanded', String(open));
    menuButton.setAttribute('aria-label', open ? 'Fechar menu' : 'Abrir menu');
  });

  nav?.querySelectorAll('a').forEach((link) => link.addEventListener('click', () => {
    nav.classList.remove('open');
    menuButton?.setAttribute('aria-expanded', 'false');
  }));

  document.querySelectorAll('.pill').forEach((button) => {
    button.addEventListener('click', () => {
      button.parentElement?.querySelectorAll('.pill').forEach((item) => item.classList.remove('is-active'));
      button.classList.add('is-active');
    });
  });

  if (year) year.textContent = String(new Date().getFullYear());
})();
