(() => {
  'use strict';

  const root = document.documentElement;
  const reduceQuery = window.matchMedia('(prefers-reduced-motion: reduce)');
  const state = {
    sound: localStorage.getItem('lucky_visual_sound') === 'on',
    reduced: localStorage.getItem('lucky_visual_motion') === 'reduced' || (!localStorage.getItem('lucky_visual_motion') && reduceQuery.matches),
    audio: null,
  };

  const applyPreferences = () => {
    root.dataset.sound = state.sound ? 'on' : 'off';
    root.dataset.motion = state.reduced ? 'reduced' : 'full';
    document.querySelectorAll('[data-sound-toggle]').forEach((button) => {
      button.setAttribute('aria-pressed', state.sound ? 'true' : 'false');
      button.title = state.sound ? 'Mute game sounds' : 'Enable game sounds';
      const icon = button.querySelector('[data-sound-icon]');
      if (icon) icon.textContent = state.sound ? '♫' : '♪';
    });
    document.querySelectorAll('[data-motion-toggle]').forEach((button) => {
      button.setAttribute('aria-pressed', state.reduced ? 'true' : 'false');
      button.title = state.reduced ? 'Enable full animation' : 'Reduce animation';
      const icon = button.querySelector('[data-motion-icon]');
      if (icon) icon.textContent = state.reduced ? '●' : '◌';
    });
  };

  const audioContext = () => {
    if (!state.sound) return null;
    if (!state.audio) {
      const Context = window.AudioContext || window.webkitAudioContext;
      if (!Context) return null;
      state.audio = new Context();
    }
    if (state.audio.state === 'suspended') state.audio.resume().catch(() => {});
    return state.audio;
  };

  const tone = (frequency = 440, duration = 0.12, type = 'sine', volume = 0.035, delay = 0) => {
    const context = audioContext();
    if (!context) return;
    const oscillator = context.createOscillator();
    const gain = context.createGain();
    oscillator.type = type;
    oscillator.frequency.setValueAtTime(frequency, context.currentTime + delay);
    gain.gain.setValueAtTime(0.0001, context.currentTime + delay);
    gain.gain.exponentialRampToValueAtTime(volume, context.currentTime + delay + 0.015);
    gain.gain.exponentialRampToValueAtTime(0.0001, context.currentTime + delay + duration);
    oscillator.connect(gain).connect(context.destination);
    oscillator.start(context.currentTime + delay);
    oscillator.stop(context.currentTime + delay + duration + 0.03);
  };

  const playResultSound = (won) => {
    if (!state.sound) return;
    if (won) {
      tone(523, 0.16, 'sine', 0.035, 0);
      tone(659, 0.18, 'sine', 0.035, 0.12);
      tone(784, 0.25, 'sine', 0.04, 0.25);
    } else {
      tone(240, 0.2, 'triangle', 0.025, 0);
      tone(196, 0.24, 'triangle', 0.02, 0.13);
    }
  };

  const setupPreferences = () => {
    applyPreferences();
    document.querySelectorAll('[data-sound-toggle]').forEach((button) => {
      button.addEventListener('click', () => {
        state.sound = !state.sound;
        localStorage.setItem('lucky_visual_sound', state.sound ? 'on' : 'off');
        applyPreferences();
        if (state.sound) tone(660, 0.12, 'sine', 0.035);
      });
    });
    document.querySelectorAll('[data-motion-toggle]').forEach((button) => {
      button.addEventListener('click', () => {
        state.reduced = !state.reduced;
        localStorage.setItem('lucky_visual_motion', state.reduced ? 'reduced' : 'full');
        applyPreferences();
      });
    });
  };

  const setupNavigation = () => {
    const toggle = document.querySelector('[data-nav-toggle]');
    const nav = document.querySelector('[data-nav]');
    toggle?.addEventListener('click', () => {
      const open = nav?.classList.toggle('open') ?? false;
      toggle.setAttribute('aria-expanded', open ? 'true' : 'false');
    });
  };

  const setupForms = () => {
    document.querySelectorAll('.js-play-form').forEach((form) => {
      form.addEventListener('submit', () => {
        const button = form.querySelector('button[type="submit"]');
        if (button) {
          button.disabled = true;
          button.textContent = button.dataset.loadingText || 'Processing…';
        }
        document.body.classList.add('is-playing');
        const game = form.dataset.gameSubmit;
        const frequency = game === 'roulette' ? 300 : game === 'slots' ? 420 : game === 'coinflip' ? 540 : 360;
        tone(frequency, 0.15, 'triangle', 0.028);
      });
    });

    document.querySelectorAll('[data-range-output]').forEach((input) => {
      const output = input.parentElement?.querySelector('output');
      const sync = () => { if (output) output.value = input.value; };
      input.addEventListener('input', sync);
      sync();
    });

    document.querySelectorAll('[data-quick-stakes]').forEach((row) => {
      const input = row.closest('form')?.querySelector('input[name="stake"]');
      row.querySelectorAll('[data-stake]').forEach((button) => {
        button.addEventListener('click', () => {
          if (!input) return;
          input.value = button.dataset.stake || input.value;
          tone(520, 0.06, 'sine', 0.018);
        });
      });
    });
  };

  const setupRouletteInput = () => {
    const type = document.querySelector('.js-roulette-type');
    const selection = document.querySelector('.js-roulette-selection');
    const hint = document.querySelector('.js-roulette-hint');
    const update = () => {
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
    type?.addEventListener('change', update);
    update();
  };

  const setupParticles = () => {
    const layer = document.querySelector('[data-particles]');
    if (!layer || state.reduced) return;
    for (let index = 0; index < 34; index += 1) {
      const particle = document.createElement('span');
      const x = (index * 37 + 11) % 100;
      const duration = 10 + (index % 9) * 1.4;
      const delay = -((index * 1.7) % 14);
      const opacity = 0.15 + (index % 5) * 0.09;
      particle.style.left = `${x}%`;
      particle.style.setProperty('--duration', `${duration}s`);
      particle.style.setProperty('--delay', `${delay}s`);
      particle.style.setProperty('--opacity', opacity.toFixed(2));
      particle.style.color = index % 3 === 0 ? '#73f8e1' : index % 3 === 1 ? '#a38cff' : '#ff80b7';
      layer.appendChild(particle);
    }
  };

  const setupTilt = () => {
    document.querySelectorAll('[data-tilt-card]').forEach((card) => {
      card.addEventListener('pointermove', (event) => {
        if (state.reduced || event.pointerType === 'touch') return;
        const rect = card.getBoundingClientRect();
        const x = (event.clientX - rect.left) / rect.width;
        const y = (event.clientY - rect.top) / rect.height;
        card.style.setProperty('--card-ry', `${(x - 0.5) * 7}deg`);
        card.style.setProperty('--card-rx', `${(0.5 - y) * 7}deg`);
        card.style.setProperty('--pointer-x', `${x * 100}%`);
        card.style.setProperty('--pointer-y', `${y * 100}%`);
      });
      card.addEventListener('pointerleave', () => {
        card.style.setProperty('--card-ry', '0deg');
        card.style.setProperty('--card-rx', '0deg');
      });
    });
  };

  const setupRouletteTrack = () => {
    const track = document.querySelector('[data-roulette-track]');
    if (!track) return;
    const order = [0, 32, 15, 19, 4, 21, 2, 25, 17, 34, 6, 27, 13, 36, 11, 30, 8, 23, 10, 5, 24, 16, 33, 1, 20, 14, 31, 9, 22, 18, 29, 7, 28, 12, 35, 3, 26];
    order.forEach((number, index) => {
      const label = document.createElement('span');
      label.textContent = String(number);
      label.style.setProperty('--angle', `${index * (360 / order.length)}deg`);
      if (number === 0) label.dataset.zero = '1';
      track.appendChild(label);
    });
    track.dataset.order = JSON.stringify(order);
  };

  const animateDice = (stage) => {
    const cube = stage.querySelector('[data-dice-cube]');
    const readout = stage.querySelector('[data-dice-readout]');
    if (!cube || !readout) return Promise.resolve();
    const final = cube.dataset.finalValue || '--.--';
    if (state.reduced) { readout.textContent = final; return Promise.resolve(); }
    readout.textContent = '••••';
    cube.classList.add('is-rolling');
    return new Promise((resolve) => {
      window.setTimeout(() => {
        cube.classList.remove('is-rolling');
        cube.style.transform = 'rotateX(708deg) rotateY(748deg)';
        readout.textContent = final;
        resolve();
      }, 1620);
    });
  };

  const animateRoulette = (stage) => {
    const wheel = stage.querySelector('[data-roulette-wheel]');
    const readout = stage.querySelector('[data-roulette-readout]');
    if (!wheel || !readout) return Promise.resolve();
    const final = Number(wheel.dataset.finalValue || 0);
    const order = [0, 32, 15, 19, 4, 21, 2, 25, 17, 34, 6, 27, 13, 36, 11, 30, 8, 23, 10, 5, 24, 16, 33, 1, 20, 14, 31, 9, 22, 18, 29, 7, 28, 12, 35, 3, 26];
    const index = Math.max(0, order.indexOf(final));
    const target = (360 * 6) - (index * (360 / order.length));
    if (state.reduced) { readout.textContent = String(final); wheel.style.transform = `rotateX(12deg) rotateZ(${target % 360}deg)`; return Promise.resolve(); }
    readout.textContent = '•';
    wheel.classList.add('is-spinning');
    requestAnimationFrame(() => { wheel.style.transform = `rotateX(12deg) rotateZ(${target}deg)`; });
    return new Promise((resolve) => {
      window.setTimeout(() => {
        wheel.classList.remove('is-spinning');
        readout.textContent = String(final);
        resolve();
      }, 3450);
    });
  };

  const animateCoin = (stage) => {
    const coin = stage.querySelector('[data-visual-coin]');
    if (!coin) return Promise.resolve();
    const side = coin.dataset.side || 'heads';
    if (state.reduced) { coin.classList.toggle('show-tails', side === 'tails'); return Promise.resolve(); }
    coin.classList.remove('show-tails');
    coin.classList.add('is-flipping');
    return new Promise((resolve) => {
      window.setTimeout(() => {
        coin.classList.remove('is-flipping');
        coin.classList.toggle('show-tails', side === 'tails');
        resolve();
      }, 1840);
    });
  };

  const animateSlots = (stage) => {
    const reels = [...stage.querySelectorAll('.visual-reel')];
    if (!reels.length) return Promise.resolve();
    const icons = { cherry: '🍒', lemon: '🍋', bell: '🔔', star: '⭐', seven: '7' };
    const names = Object.keys(icons);
    if (state.reduced) return Promise.resolve();
    return new Promise((resolve) => {
      let settled = 0;
      reels.forEach((reel, index) => {
        const symbol = reel.querySelector('[data-reel-symbol]');
        const label = reel.querySelector('[data-reel-label]');
        const final = reel.dataset.finalSymbol || 'cherry';
        reel.classList.add('is-spinning');
        let tick = 0;
        const interval = window.setInterval(() => {
          const randomName = names[(tick + index * 2) % names.length];
          if (symbol) symbol.textContent = icons[randomName];
          if (label) label.textContent = randomName.toUpperCase();
          tick += 1;
        }, 82);
        window.setTimeout(() => {
          clearInterval(interval);
          reel.classList.remove('is-spinning');
          if (symbol) symbol.textContent = icons[final] || '◆';
          if (label) label.textContent = final.toUpperCase();
          settled += 1;
          tone(380 + index * 90, 0.08, 'triangle', 0.02);
          if (settled === reels.length) resolve();
        }, 850 + index * 430);
      });
    });
  };

  const celebrate = () => {
    if (state.reduced) return;
    const canvas = document.querySelector('[data-celebration]');
    if (!(canvas instanceof HTMLCanvasElement)) return;
    const context = canvas.getContext('2d');
    if (!context) return;
    const ratio = Math.min(window.devicePixelRatio || 1, 2);
    canvas.width = window.innerWidth * ratio;
    canvas.height = window.innerHeight * ratio;
    context.scale(ratio, ratio);
    const colors = ['#73f8e1', '#8a72ff', '#ff6daa', '#f7ca68', '#ffffff'];
    const pieces = Array.from({ length: 75 }, (_, index) => ({
      x: window.innerWidth * (0.35 + (index % 31) / 100),
      y: window.innerHeight * 0.32,
      vx: ((index * 29) % 90 - 45) / 9,
      vy: -4 - ((index * 13) % 45) / 10,
      rotation: (index * 17) % 360,
      vr: ((index % 7) - 3) * 0.08,
      size: 4 + (index % 5),
      color: colors[index % colors.length],
      life: 1,
    }));
    let previous = performance.now();
    const frame = (now) => {
      const delta = Math.min((now - previous) / 16.7, 2);
      previous = now;
      context.clearRect(0, 0, window.innerWidth, window.innerHeight);
      pieces.forEach((piece) => {
        piece.x += piece.vx * delta;
        piece.vy += 0.14 * delta;
        piece.y += piece.vy * delta;
        piece.rotation += piece.vr * delta;
        piece.life -= 0.008 * delta;
        context.save();
        context.globalAlpha = Math.max(0, piece.life);
        context.translate(piece.x, piece.y);
        context.rotate(piece.rotation);
        context.fillStyle = piece.color;
        context.fillRect(-piece.size / 2, -piece.size / 2, piece.size, piece.size * 1.7);
        context.restore();
      });
      if (pieces.some((piece) => piece.life > 0 && piece.y < window.innerHeight + 40)) requestAnimationFrame(frame);
      else context.clearRect(0, 0, window.innerWidth, window.innerHeight);
    };
    requestAnimationFrame(frame);
  };

  const setupGameResult = async () => {
    const stage = document.querySelector('[data-visual-game]');
    if (!stage || stage.dataset.resultReady !== '1') return;
    const game = stage.dataset.visualGame;
    if (game === 'dice') await animateDice(stage);
    if (game === 'roulette') await animateRoulette(stage);
    if (game === 'coinflip') await animateCoin(stage);
    if (game === 'slots') await animateSlots(stage);
    const won = stage.dataset.won === '1';
    playResultSound(won);
    if (won) celebrate();
  };

  document.addEventListener('DOMContentLoaded', () => {
    setupPreferences();
    setupNavigation();
    setupForms();
    setupRouletteInput();
    setupParticles();
    setupTilt();
    setupRouletteTrack();
    setupGameResult().catch(() => {});
  });
})();
