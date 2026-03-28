// FinPilot AI — Shared JS Utilities

// ── Number formatting ─────────────────────────────────────────────────────────
function formatINR(amount) {
  if (amount >= 10000000) return '₹' + (amount / 10000000).toFixed(2) + ' Cr';
  if (amount >= 100000)   return '₹' + (amount / 100000).toFixed(2) + ' L';
  if (amount >= 1000)     return '₹' + (amount / 1000).toFixed(1) + 'K';
  return '₹' + amount;
}

// ── Animate counter ───────────────────────────────────────────────────────────
function animateCounter(el, from, to, duration = 1200, suffix = '') {
  const start = performance.now();
  const update = (now) => {
    const elapsed = now - start;
    const progress = Math.min(elapsed / duration, 1);
    const ease = 1 - Math.pow(1 - progress, 3);
    el.textContent = Math.round(from + (to - from) * ease) + suffix;
    if (progress < 1) requestAnimationFrame(update);
  };
  requestAnimationFrame(update);
}

// ── Animate progress bar ──────────────────────────────────────────────────────
function animateBar(fillEl, targetPct, delay = 0) {
  setTimeout(() => {
    fillEl.style.width = targetPct + '%';
  }, delay);
}

// ── Toast notification ────────────────────────────────────────────────────────
function showToast(msg, type = 'success') {
  const existing = document.querySelector('.fp-toast');
  if (existing) existing.remove();

  const toast = document.createElement('div');
  toast.className = 'fp-toast';
  toast.style.cssText = `
    position:fixed; bottom:24px; right:24px; z-index:9999;
    background:${type === 'success' ? 'rgba(52,211,153,0.15)' : 'rgba(248,113,113,0.15)'};
    border:1px solid ${type === 'success' ? 'rgba(52,211,153,0.3)' : 'rgba(248,113,113,0.3)'};
    color:${type === 'success' ? '#34d399' : '#f87171'};
    padding:12px 20px; border-radius:10px; font-size:0.88rem;
    backdrop-filter:blur(10px); animation:toastIn .3s ease-out;
    font-family:'DM Sans',sans-serif;
  `;
  toast.textContent = msg;
  document.body.appendChild(toast);
  setTimeout(() => toast.remove(), 3500);
}

// ── Inject toast keyframes once ───────────────────────────────────────────────
if (!document.getElementById('fpToastStyle')) {
  const s = document.createElement('style');
  s.id = 'fpToastStyle';
  s.textContent = `@keyframes toastIn{from{opacity:0;transform:translateY(12px)}to{opacity:1;transform:translateY(0)}}`;
  document.head.appendChild(s);
}
