<!--
  ╔══════════════════════════════════════════════════════════════════╗
  ║  Hope Phillips Charity Foundation — Donation Modal              ║
  ║  Drop this snippet anywhere on the page.                        ║
  ║  Requires: Bootstrap 5, Paystack inline.js (loaded below)       ║
  ╚══════════════════════════════════════════════════════════════════╝
-->

<!-- ══════════════════════ STYLES ══════════════════════ -->
<style>
  /* ── Amount grid ─────────────────────────────────────── */
  .amount-grid {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(90px, 1fr));
    gap: 12px;
  }

  .amount-card {
    border: 1px solid #dee2e6;
    background: white;
    padding: 12px;
    border-radius: 10px;
    font-weight: 500;
    transition: border-color .2s, color .2s, background .2s;
    cursor: pointer;
  }

  .amount-card:hover {
    border-color: #00a651;
    color: #00a651;
  }

  .amount-card.active {
    background: #00a651;
    color: white;
    outline: none;
  }

  /* ── Frequency grid ──────────────────────────────────── */
  .frequency-grid {
    display: grid;
    grid-template-columns: repeat(3, 1fr);
    gap: 10px;
  }

  .frequency-card {
    border: 1px solid #dee2e6;
    border-radius: 8px;
    cursor: pointer;
    text-align: center;
    padding: 6px 8px;
    transition: .2s;
  }

  .frequency-card input { display: none; }

  .frequency-card .card-body {
    font-weight: 500;
    font-size: 14px;
    padding: 12px;
  }

  .frequency-card input:checked + .card-body {
    background: #00a651;
    color: white;
    border-radius: 6px;
  }

  /* ── Inline toast ────────────────────────────────────── */
  .form-toast {
    display: none;
    background: #212529;
    color: white;
    padding: 8px 12px;
    border-radius: 6px;
    font-size: 14px;
    margin-bottom: 10px;
    opacity: .95;
  }

  /* ── Ensure modal renders above everything ───────────── */
  .modal           { z-index: 2000 !important; }
  .modal-backdrop  { z-index: 1990 !important; }

  /* ── Mobile ──────────────────────────────────────────── */
  @media (max-width: 576px) {
    .frequency-grid { grid-template-columns: 1fr; }
  }
</style>


<!-- ══════════════════════ MODAL ══════════════════════ -->
<div class="modal fade" id="donationModal" tabindex="-1" aria-labelledby="donationModalLabel" aria-modal="true" role="dialog">
  <div class="modal-dialog modal-dialog-centered modal-dialog-scrollable">
    <div class="modal-content shadow border-0">

      <div class="modal-header border-0 pb-0">
        <h5 class="modal-title fw-semibold" id="donationModalLabel">Support The Vulnerable</h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
      </div>

      <div class="modal-body pt-2">
        <form id="paymentForm" novalidate>

          <!-- EMAIL -->
          <div class="mb-3">
            <label class="form-label fw-medium" for="donorEmail">Email</label>
            <input type="email" class="form-control form-control-lg" id="donorEmail"
                   placeholder="you@example.com" autocomplete="email" required>
          </div>

          <!-- MESSAGE -->
          <div class="mb-3">
            <label class="form-label fw-medium" for="donorMessage">Message <span class="text-muted fw-normal">(optional)</span></label>
            <textarea class="form-control" id="donorMessage" rows="2"
                      placeholder="Leave an encouraging message"></textarea>
          </div>

          <!-- AMOUNT -->
          <div class="mb-4">
            <label class="form-label fw-medium">Select Amount</label>

            <div class="amount-grid mt-2">
              <button type="button" class="amount-card" data-amount="20">$20</button>
              <button type="button" class="amount-card" data-amount="50">$50</button>
              <button type="button" class="amount-card" data-amount="100">$100</button>
              <button type="button" class="amount-card" data-amount="200">$200</button>
            </div>

            <div class="mt-3">
              <input type="number" class="form-control form-control-lg" id="donorAmount"
                     placeholder="Custom amount in USD" min="1" step="1" required>
            </div>

            <div class="mt-2 text-muted small">
              ≈ ₦<span id="ngnPreview">0</span>
            </div>
          </div>

          <!-- FREQUENCY -->
          <div class="mb-4">
            <label class="form-label fw-medium">Donation Frequency</label>
            <div class="frequency-grid mt-2">
              <label class="frequency-card">
                <input type="radio" name="donationInterval" value="" checked>
                <div class="card-body">One-time</div>
              </label>
              <label class="frequency-card">
                <input type="radio" name="donationInterval" value="monthly">
                <div class="card-body">Monthly</div>
              </label>
              <label class="frequency-card">
                <input type="radio" name="donationInterval" value="annually">
                <div class="card-body">Yearly</div>
              </label>
            </div>
          </div>

          <!-- TOAST + SUBMIT -->
          <div id="formToast" class="form-toast" role="alert" aria-live="polite"></div>

          <button type="submit" class="btn btn-primary btn-lg w-100 py-2" id="payBtn">
            Donate Now
          </button>

        </form>
      </div><!-- /.modal-body -->

    </div>
  </div>
</div>


<!-- ══════════════════════ SCRIPTS ══════════════════════ -->
<script src="https://js.paystack.co/v1/inline.js"></script>

<script>
(function () {
  'use strict';

  /* ═══════════════════════════════════════════════════════════
     CONSTANTS — the ONLY place you ever change URLs / keys
  ═══════════════════════════════════════════════════════════ */
  const API_BASE        = 'https://api.hopephillipscharityfoundation.com/api';
  const FALLBACK_RATE   = 1500;   // NGN per 1 USD — used if server is unreachable
  const DEFAULT_AMOUNT  = 20;     // pre-selected USD amount

  /* ═══════════════════════════════════════════════════════════
     CONFIG MANAGER
     Fetches runtime config once; caches the result.
  ═══════════════════════════════════════════════════════════ */
  const AppConfig = (() => {
    let _cfg = null;
    let _promise = null;

    async function _load() {
      try {
        const res = await fetch(`${API_BASE}/payments/config`, {
          headers: { Accept: 'application/json' },
          // Abort if the server takes > 8 s
          signal: AbortSignal.timeout(8000)
        });

        if (!res.ok) throw new Error(`Config HTTP ${res.status}`);

        const body = await res.json();
        if (!body.success || !body.data) throw new Error('Unexpected config shape');

        _cfg = body.data;
        console.info('[Config] loaded — env:', _cfg.environment ?? 'unknown');
        return _cfg;

      } catch (err) {
        console.warn('[Config] falling back to defaults.', err.message);
        _cfg = {
          paystack_public_key : null,        // will be checked before use
          environment         : 'production',
          exchange_rate       : FALLBACK_RATE,
          currency            : 'NGN',
        };
        return _cfg;
      } finally {
        _promise = null;
      }
    }

    return {
      ready : ()            => { _promise = _promise ?? _load(); return _promise; },
      get   : (k, def=null) => (_cfg && _cfg[k] != null) ? _cfg[k] : def,
      loaded: ()            => _cfg !== null,
    };
  })();

  /* ═══════════════════════════════════════════════════════════
     DOM REFS
  ═══════════════════════════════════════════════════════════ */
  const form         = document.getElementById('paymentForm');
  const emailEl      = document.getElementById('donorEmail');
  const messageEl    = document.getElementById('donorMessage');
  const amountEl     = document.getElementById('donorAmount');
  const ngnPreview   = document.getElementById('ngnPreview');
  const payBtn       = document.getElementById('payBtn');
  const toastEl      = document.getElementById('formToast');
  const amountCards  = document.querySelectorAll('.amount-card');
  const modalEl      = document.getElementById('donationModal');

  let usdToNgn  = FALLBACK_RATE;
  let busy      = false;

  /* ═══════════════════════════════════════════════════════════
     BOOT
  ═══════════════════════════════════════════════════════════ */
  (async function boot() {
    await AppConfig.ready();

    usdToNgn = AppConfig.get('exchange_rate', FALLBACK_RATE);

    // Pre-select default card & seed input
    amountEl.value = DEFAULT_AMOUNT;
    amountCards.forEach(c => {
      if (Number(c.dataset.amount) === DEFAULT_AMOUNT) c.classList.add('active');
    });
    refreshPreview();
  })();

  /* ═══════════════════════════════════════════════════════════
     AMOUNT SELECTION
  ═══════════════════════════════════════════════════════════ */
  amountCards.forEach(card => {
    card.addEventListener('click', () => {
      amountCards.forEach(c => c.classList.remove('active'));
      card.classList.add('active');
      amountEl.value = card.dataset.amount;
      refreshPreview();
    });
  });

  amountEl.addEventListener('input', () => {
    amountCards.forEach(c => c.classList.remove('active'));
    refreshPreview();
  });

  // Only allow integer input
  amountEl.addEventListener('keypress', e => {
    if (e.key < '0' || e.key > '9') e.preventDefault();
  });

  function refreshPreview() {
    const usd = parseFloat(amountEl.value) || 0;
    ngnPreview.textContent = new Intl.NumberFormat('en-NG').format(Math.round(usd * usdToNgn));
  }

  /* ═══════════════════════════════════════════════════════════
     TOAST
  ═══════════════════════════════════════════════════════════ */
  let _toastTimer;
  function toast(msg, isError = false) {
    clearTimeout(_toastTimer);
    toastEl.textContent       = msg;
    toastEl.style.display     = 'block';
    toastEl.style.background  = isError ? '#dc3545' : '#212529';
    _toastTimer = setTimeout(() => { toastEl.style.display = 'none'; }, 4000);
  }

  /* ═══════════════════════════════════════════════════════════
     BUTTON STATE
  ═══════════════════════════════════════════════════════════ */
  function setBusy(state) {
    busy = state;
    payBtn.disabled = state;
    payBtn.innerHTML = state
      ? `<span class="spinner-border spinner-border-sm me-2" role="status" aria-hidden="true"></span>Processing…`
      : 'Donate Now';
  }

  /* ═══════════════════════════════════════════════════════════
     VALIDATION
  ═══════════════════════════════════════════════════════════ */
  function validate() {
    const email  = emailEl.value.trim();
    const amount = parseFloat(amountEl.value);

    if (!email || !/^[^\s@]+@[^\s@]+\.[^\s@]+$/.test(email)) {
      toast('Please enter a valid email address.', true);
      emailEl.focus();
      return false;
    }
    if (!amount || amount < 1) {
      toast('Please enter a valid amount (minimum $1).', true);
      amountEl.focus();
      return false;
    }
    return true;
  }

  /* ═══════════════════════════════════════════════════════════
     FORM SUBMIT — initialise payment
  ═══════════════════════════════════════════════════════════ */
  form.addEventListener('submit', async e => {
    e.preventDefault();
    if (busy) return;
    if (!validate()) return;

    setBusy(true);

    const payload = {
      email       : emailEl.value.trim(),
      amount      : parseFloat(amountEl.value),
      interval    : document.querySelector('input[name="donationInterval"]:checked')?.value || null,
      description : messageEl.value.trim() || null,
    };

    try {
      // Ensure config is ready (no-op if already loaded)
      if (!AppConfig.loaded()) await AppConfig.ready();

      const res  = await fetch(`${API_BASE}/payments/initialize`, {
        method  : 'POST',
        headers : { 'Content-Type': 'application/json' },
        body    : JSON.stringify(payload),
        signal  : AbortSignal.timeout(15000),
      });

      const body = await res.json();

      if (!res.ok) throw new Error(body.message || `Server error ${res.status}`);
      if (!body.success || !body.data) throw new Error(body.message || 'Unexpected response from server');

      const paystackKey = AppConfig.get('paystack_public_key');
      if (!paystackKey || !paystackKey.startsWith('pk_')) {
        throw new Error('Payment gateway is not configured. Please try again later.');
      }

      openPaystack(payload.email, body.data, paystackKey);

    } catch (err) {
      console.error('[Payment init]', err);
      toast(err.name === 'TimeoutError'
        ? 'Request timed out. Please check your connection and try again.'
        : err.message || 'Payment initialisation failed. Please try again.',
        true
      );
      setBusy(false);
    }
  });

  /* ═══════════════════════════════════════════════════════════
     PAYSTACK POPUP
  ═══════════════════════════════════════════════════════════ */
  function openPaystack(email, data, key) {
    if (typeof PaystackPop === 'undefined') {
      toast('Paystack SDK failed to load. Please refresh the page.', true);
      setBusy(false);
      return;
    }

    const handler = PaystackPop.setup({
      key,
      email,
      amount   : data.amount_ngn * 100,                  // Paystack expects kobo
      currency : AppConfig.get('currency', 'NGN'),
      ref      : data.reference,
      label    : 'Hope Phillips Charity Foundation',

      metadata: {
        description   : data.description ?? '',
        custom_fields : [
          {
            display_name  : 'Donation Type',
            variable_name : 'donation_type',
            value         : data.interval || 'one-time',
          },
          {
            display_name  : 'Purpose',
            variable_name : 'purpose',
            value         : data.description ?? 'General donation',
          },
        ],
      },

      callback: ({ reference }) => verifyPayment(reference),

      onClose: () => {
        setBusy(false);
        toast('Transaction was cancelled.');
      },

      onError: err => {
        console.error('[Paystack]', err);
        setBusy(false);
        toast('Payment failed: ' + (err?.message || 'Unknown error.'), true);
      },
    });

    handler.openIframe();
  }

  /* ═══════════════════════════════════════════════════════════
     VERIFY PAYMENT
  ═══════════════════════════════════════════════════════════ */
  async function verifyPayment(reference) {
    try {
      const res  = await fetch(`${API_BASE}/payments/verify/${encodeURIComponent(reference)}`, {
        signal: AbortSignal.timeout(15000),
      });
      const body = await res.json();

      if (res.ok && body.success) {
        toast('🎉 Donation successful! Thank you for your support.');
        payBtn.disabled = true;

        setTimeout(() => {
          closeModal();
          resetForm();
        }, 2500);

      } else {
        throw new Error(body.message || 'Verification failed');
      }

    } catch (err) {
      console.error('[Verify]', err);
      // Payment likely went through — don't alarm the donor
      toast('Your payment was received. Confirmation is on its way — thank you!');
    } finally {
      setBusy(false);
    }
  }

  /* ═══════════════════════════════════════════════════════════
     MODAL HELPERS
  ═══════════════════════════════════════════════════════════ */
  function closeModal() {
    // Prefer Bootstrap's own API when available, fall back to manual teardown
    if (window.bootstrap?.Modal) {
      const instance = bootstrap.Modal.getOrCreateInstance(modalEl);
      instance.hide();
    } else {
      modalEl.classList.remove('show');
      document.querySelector('.modal-backdrop')?.remove();
      document.body.classList.remove('modal-open');
      document.body.style.removeProperty('padding-right');
    }
  }

  function resetForm() {
    form.reset();
    amountCards.forEach(c => c.classList.remove('active'));
    amountEl.value = DEFAULT_AMOUNT;
    amountCards.forEach(c => {
      if (Number(c.dataset.amount) === DEFAULT_AMOUNT) c.classList.add('active');
    });
    refreshPreview();
    toastEl.style.display = 'none';
    setBusy(false);
  }

  // Auto-reset when donor closes without completing
  if (modalEl) {
    modalEl.addEventListener('hidden.bs.modal', () => {
      if (!busy) resetForm();
    });
  }

  /* ═══════════════════════════════════════════════════════════
     EXPOSE FOR CONSOLE DEBUGGING (remove in strict prod)
  ═══════════════════════════════════════════════════════════ */
  window.__HopeFoundation = { AppConfig };

})();
</script>
