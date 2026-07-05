document.addEventListener('DOMContentLoaded', () => {
  const toggle = document.querySelector('[data-nav-toggle]');
  const nav = document.querySelector('[data-nav]');
  toggle?.addEventListener('click', () => nav?.classList.toggle('open'));

  document.querySelectorAll('.js-play-form').forEach((form) => {
    form.addEventListener('submit', () => {
      const button = form.querySelector('button[type="submit"]');
      if (!button) return;
      button.disabled = true;
      button.textContent = button.dataset.loadingText || 'Processing…';
    });
  });

  const type = document.querySelector('.js-roulette-type');
  const selection = document.querySelector('.js-roulette-selection');
  const hint = document.querySelector('.js-roulette-hint');
  const updateRoulette = () => {
    if (!type || !selection || !hint) return;
    const map = {
      straight: ['17', 'Enter a number from 0 to 36.'],
      color: ['red', 'Enter red or black.'],
      parity: ['odd', 'Enter odd or even.'],
      range: ['low', 'Enter low or high.'],
      dozen: ['1', 'Enter dozen 1, 2 or 3.'],
    };
    const [placeholder, text] = map[type.value] || map.straight;
    selection.placeholder = placeholder;
    hint.textContent = text;
  };
  type?.addEventListener('change', updateRoulette);
  updateRoulette();
});
