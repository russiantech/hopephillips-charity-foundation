<style>
.amount-grid{
display:grid;
grid-template-columns:repeat(auto-fit,minmax(90px,1fr));
gap:12px;
}


/* AMOUNT CARDS */

.amount-card{
border:1px solid #dee2e6;
background:white;
padding:12px;
border-radius:10px;
font-weight:500;
transition:.2s;
cursor:pointer;
}

.amount-card:hover{
border-color: #00a651;
color:#00a651;
}

.amount-card.active{
background:#00a651;
color:white;
/* border-color:#00a651; */
outline: none;

}


/* CUSTOM AMOUNT */

.custom-amount-wrapper{
margin-top:14px;
}


/* FREQUENCY GRID */

.frequency-grid{
display:grid;
grid-template-columns:repeat(3,1fr);
gap:10px;
}


/* FREQUENCY CARD */

.frequency-card{
border:1px solid #dee2e6;
border-radius:8px;
cursor:pointer;
text-align:center;
padding:6px 8px;
transition:.2s;
}

.frequency-card input{
display:none;
}

.frequency-card .card-body{
font-weight:500;
font-size:14px;
padding:12px;
}

.frequency-card input:checked + .card-body{
background:#00a651;
color:white;
border-radius:6px;
}


/* MOBILE */

@media (max-width:576px){

.frequency-grid{
grid-template-columns:1fr;
}

}


/* FORM TOAST (near button) */

.form-toast{
display:none;
background:#212529;
color:white;
padding:8px 12px;
border-radius:6px;
font-size:14px;
margin-bottom:10px;
opacity:.95;
}

</style>



<div class="modal fade" id="donationModal" tabindex="-1">
<div class="modal-dialog modal-dialog-centered modal-dialog-scrollable">
<div class="modal-content shadow border-0">

<div class="modal-header border-0 pb-0">

<h5 class="modal-title fw-semibold">
Support The Vulnerable
</h5>

<button type="button" class="btn-close" data-bs-dismiss="modal"></button>

</div>



<div class="modal-body pt-2">

<form id="paymentForm">


<!-- EMAIL -->

<div class="mb-3">

<label class="form-label fw-medium">
Email
</label>

<input
type="email"
class="form-control form-control-lg"
id="email"
required>

</div>



<!-- MESSAGE -->

<div class="mb-3">

<label class="form-label fw-medium">
Message (optional)
</label>

<textarea
class="form-control"
id="description"
rows="2"
placeholder="Leave a message"></textarea>

</div>




<!-- AMOUNT -->

<div class="mb-4">

<label class="form-label fw-medium">
Select Amount
</label>

<div class="amount-grid mt-2">

<button type="button" class="amount-card" data-amount="20">$20</button>
<button type="button" class="amount-card" data-amount="50">$50</button>
<button type="button" class="amount-card" data-amount="100">$100</button>
<button type="button" class="amount-card" data-amount="200">$200</button>

</div>



<div class="custom-amount-wrapper">

<input
type="number"
class="form-control form-control-lg"
id="amount"
placeholder="Custom amount in USD"
min="1"
step="1"
required>

</div>


<div class="conversion-preview mt-2 text-muted small">
≈ ₦<span id="ngnPreview">0</span>
</div>

</div>




<!-- FREQUENCY -->

<div class="mb-4">

<label class="form-label fw-medium">
Donation Frequency
</label>

<div class="frequency-grid mt-2">

<label class="frequency-card">

<input type="radio" name="interval" value="" checked>

<div class="card-body">
One-time
</div>

</label>


<label class="frequency-card">

<input type="radio" name="interval" value="monthly">

<div class="card-body">
Monthly
</div>

</label>


<label class="frequency-card">

<input type="radio" name="interval" value="yearly">

<div class="card-body">
Yearly
</div>

</label>

</div>

</div>



<!-- TOAST -->

<div id="formToast" class="form-toast"></div>



<button
class="btn btn-primary btn-lg w-100 py-2"
id="payBtn">

Donate Now

</button>


</form>

</div>

</div>
</div>
</div>



<script src="https://js.paystack.co/v1/inline.js"></script>
<!-- 
<script>
// ==================== CONFIGURATION ====================
// const API_BASE_URL = 'https://your-api-domain.com';  // CHANGE THIS TO YOUR API DOMAIN
// const PAYSTACK_PUBLIC_KEY = 'pk_test_xxxxxxxxxxxxx';  // CHANGE THIS TO YOUR PAYSTACK PUBLIC KEY
const API_BASE_URL = 'http://localhost:5000';  // CHANGE THIS TO YOUR API DOMAIN
const PAYSTACK_PUBLIC_KEY = 'pk_test_xxxxxxxxxxxxx';  // CHANGE THIS TO YOUR PAYSTACK PUBLIC KEY

// ==================== DOM ELEMENTS ====================
const amountCards = document.querySelectorAll(".amount-card");
const amountInput = document.getElementById("amount");
const ngnPreview = document.getElementById("ngnPreview");
const payBtn = document.getElementById("payBtn");
const toastBox = document.getElementById("formToast");
const paymentForm = document.getElementById("paymentForm");

let processing = false;
let USD_NGN = 1500; // Exchange rate - you might want to fetch this dynamically

// ==================== INITIALIZATION ====================
// Set a default amount if none selected
if (!amountInput.value) {
    amountInput.value = 20;
    // Auto-select the $20 card
    amountCards.forEach(card => {
        if (card.dataset.amount === '20') {
            card.classList.add('active');
        }
    });
}
updatePreview();

// ==================== EVENT LISTENERS ====================

/* AMOUNT CARD SELECT */
amountCards.forEach(card => {
    card.addEventListener("click", () => {
        amountCards.forEach(c => c.classList.remove("active"));
        card.classList.add("active");
        amountInput.value = card.dataset.amount;
        updatePreview();
    });
});

/* CUSTOM AMOUNT INPUT */
amountInput.addEventListener("input", () => {
    amountCards.forEach(c => c.classList.remove("active"));
    updatePreview();
});

/* LIVE CONVERSION */
function updatePreview() {
    let usd = amountInput.value || 0;
    let ngn = usd * USD_NGN;
    ngnPreview.innerText = new Intl.NumberFormat().format(ngn);
}

/* TOAST FUNCTION */
function toast(msg, isError = false) {
    toastBox.innerText = msg;
    toastBox.style.display = "block";
    toastBox.style.background = isError ? '#dc3545' : '#212529';
    
    setTimeout(() => {
        toastBox.style.display = "none";
    }, 3000);
}

/* BUTTON SPINNER */
function showSpinner() {
    payBtn.disabled = true;
    payBtn.innerHTML = `
        <span class="spinner-border spinner-border-sm me-2" role="status" aria-hidden="true"></span>
        Processing...
    `;
}

function hideSpinner() {
    payBtn.disabled = false;
    payBtn.innerHTML = "Donate Now";
    processing = false;
}

/* VALIDATE FORM */
function validateForm() {
    const email = document.getElementById("email").value;
    const amount = amountInput.value;
    
    if (!email || !email.includes('@')) {
        toast('Please enter a valid email address', true);
        return false;
    }
    
    if (!amount || amount < 1) {
        toast('Please enter a valid amount (minimum $1)', true);
        return false;
    }
    
    return true;
}

/* FORM SUBMIT */
paymentForm.addEventListener("submit", async function(e) {
    e.preventDefault();
    
    if (processing) return;
    
    if (!validateForm()) {
        return;
    }
    
    processing = true;
    showSpinner();

    const email = document.getElementById("email").value;
    const description = document.getElementById("description").value;
    const amount = amountInput.value;
    const interval = document.querySelector("input[name='interval']:checked")?.value || null;

    try {
        // Use full URL for API endpoint
        const res = await fetch(`${API_BASE_URL}/api/payments/initialize`, {
            method: "POST",
            headers: {
                "Content-Type": "application/json"
            },
            body: JSON.stringify({
                email,
                amount,
                interval,
                description
            })
        });

        if (!res.ok) {
            throw new Error(`HTTP error! status: ${res.status}`);
        }

        const data = await res.json();
        
        if (data.reference) {
            launchPaystack(email, data);
        } else {
            throw new Error('Invalid response from server');
        }
        
    } catch(error) {
        console.error('Payment initialization error:', error);
        toast("Payment initialization failed. Please try again.", true);
        hideSpinner();
    }
});

/* PAYSTACK INLINE */
function launchPaystack(email, data) {
    try {
        let handler = PaystackPop.setup({
            key: PAYSTACK_PUBLIC_KEY,
            email: email,
            amount: data.amount_ngn * 100, // Paystack expects amount in kobo
            currency: "NGN",
            ref: data.reference,
            
            callback: function(response) {
                verifyPayment(response.reference);
            },
            
            onClose: function() {
                hideSpinner();
                toast("Transaction cancelled");
            },
            
            // Optional: Add metadata
            metadata: {
                custom_fields: [
                    {
                        display_name: "Donation Type",
                        variable_name: "donation_type",
                        value: document.querySelector("input[name='interval']:checked")?.value || 'one-time'
                    }
                ]
            }
        });
        
        handler.openIframe();
        
    } catch (error) {
        console.error('Paystack initialization error:', error);
        toast("Failed to initialize payment gateway", true);
        hideSpinner();
    }
}

/* VERIFY PAYMENT */
async function verifyPayment(reference) {
    try {
        // Use full URL for API endpoint
        const response = await fetch(`${API_BASE_URL}/api/payments/verify/${reference}`);
        
        if (response.ok) {
            toast("Donation successful! Thank you for your support.");
            
            // Optional: Close modal after successful donation
            setTimeout(() => {
                const modal = bootstrap.Modal.getInstance(document.getElementById('donationModal'));
                if (modal) {
                    modal.hide();
                }
                // Reset form
                paymentForm.reset();
                amountCards.forEach(c => c.classList.remove('active'));
                amountInput.value = 20;
                // Re-select $20 card
                amountCards.forEach(card => {
                    if (card.dataset.amount === '20') {
                        card.classList.add('active');
                    }
                });
                updatePreview();
            }, 2000);
        } else {
            toast("Payment verified but server confirmation failed", true);
        }
        
        // Optionally reload or update UI instead of full reload
        // location.reload(); // Commented out for better UX
        
    } catch (error) {
        console.error('Verification error:', error);
        toast("Payment successful but verification failed", true);
    } finally {
        hideSpinner();
    }
}

// ==================== ADDITIONAL FEATURES ====================

/* FETCH LIVE EXCHANGE RATE (Optional) */
async function fetchExchangeRate() {
    try {
        // You can use a free API like exchangerate-api.com
        // For now, we'll keep the static rate
        // const response = await fetch('https://api.exchangerate-api.com/v4/latest/USD');
        // const data = await response.json();
        // USD_NGN = data.rates.NGN || 1500;
        // updatePreview();
    } catch (error) {
        console.log('Using default exchange rate');
    }
}

// Uncomment to fetch live rate
// fetchExchangeRate();

/* MODAL RESET ON CLOSE */
const donationModal = document.getElementById('donationModal');
if (donationModal) {
    donationModal.addEventListener('hidden.bs.modal', function () {
        if (!processing) {
            paymentForm.reset();
            amountCards.forEach(c => c.classList.remove('active'));
            amountInput.value = 20;
            amountCards.forEach(card => {
                if (card.dataset.amount === '20') {
                    card.classList.add('active');
                }
            });
            updatePreview();
            toastBox.style.display = 'none';
        }
    });
}

/* INPUT VALIDATION */
amountInput.addEventListener('keypress', function(e) {
    // Allow only numbers
    if (e.key < '0' || e.key > '9') {
        e.preventDefault();
    }
});

</script> -->

<!-- v2 -->
<!-- // Updated frontend JavaScript -->
<script>
// ==================== CONFIGURATION ====================
const API_BASE_URL = 'http://127.0.0.1:5000';  // Your Flask server URL
const PAYSTACK_PUBLIC_KEY = 'pk_test_xxxxxxxxxxxxx';

// ==================== DOM ELEMENTS ====================
const amountCards = document.querySelectorAll(".amount-card");
const amountInput = document.getElementById("amount");
const ngnPreview = document.getElementById("ngnPreview");
const payBtn = document.getElementById("payBtn");
const toastBox = document.getElementById("formToast");
const paymentForm = document.getElementById("paymentForm");

let processing = false;
let USD_NGN = 1500;

// ==================== INITIALIZATION ====================
if (!amountInput.value) {
    amountInput.value = 20;
    amountCards.forEach(card => {
        if (card.dataset.amount === '20') {
            card.classList.add('active');
        }
    });
}
updatePreview();

// ==================== EVENT LISTENERS ====================

/* AMOUNT CARD SELECT */
amountCards.forEach(card => {
    card.addEventListener("click", () => {
        amountCards.forEach(c => c.classList.remove("active"));
        card.classList.add("active");
        amountInput.value = card.dataset.amount;
        updatePreview();
    });
});

/* CUSTOM AMOUNT INPUT */
amountInput.addEventListener("input", () => {
    amountCards.forEach(c => c.classList.remove("active"));
    updatePreview();
});

/* LIVE CONVERSION */
function updatePreview() {
    let usd = amountInput.value || 0;
    let ngn = usd * USD_NGN;
    ngnPreview.innerText = new Intl.NumberFormat().format(ngn);
}

/* TOAST FUNCTION */
function toast(msg, isError = false) {
    toastBox.innerText = msg;
    toastBox.style.display = "block";
    toastBox.style.background = isError ? '#dc3545' : '#212529';
    
    setTimeout(() => {
        toastBox.style.display = "none";
    }, 3000);
}

/* BUTTON SPINNER */
function showSpinner() {
    payBtn.disabled = true;
    payBtn.innerHTML = `
        <span class="spinner-border spinner-border-sm me-2" role="status" aria-hidden="true"></span>
        Processing...
    `;
}

function hideSpinner() {
    payBtn.disabled = false;
    payBtn.innerHTML = "Donate Now";
    processing = false;
}

/* VALIDATE FORM */
function validateForm() {
    const email = document.getElementById("email").value;
    const amount = amountInput.value;
    
    if (!email || !email.includes('@')) {
        toast('Please enter a valid email address', true);
        return false;
    }
    
    if (!amount || amount < 1) {
        toast('Please enter a valid amount (minimum $1)', true);
        return false;
    }
    
    return true;
}

/* FORM SUBMIT */
paymentForm.addEventListener("submit", async function(e) {
    e.preventDefault();
    
    if (processing) return;
    
    if (!validateForm()) {
        return;
    }
    
    processing = true;
    showSpinner();

    const email = document.getElementById("email").value;
    const description = document.getElementById("description").value;
    const amount = amountInput.value;
    const interval = document.querySelector("input[name='interval']:checked")?.value || null;

    try {
        const res = await fetch(`${API_BASE_URL}/api/payments/initialize`, {
            method: "POST",
            headers: {
                "Content-Type": "application/json"
            },
            body: JSON.stringify({
                email,
                amount,
                interval,
                description
            })
        });

        const response = await res.json();
        
        if (!res.ok) {
            throw new Error(response.message || 'Payment initialization failed');
        }
        
        if (response.success && response.data) {
            launchPaystack(email, response.data);
        } else {
            throw new Error(response.message || 'Invalid response from server');
        }
        
    } catch(error) {
        console.error('Payment initialization error:', error);
        toast(error.message || "Payment initialization failed. Please try again.", true);
        hideSpinner();
    }
});

/* PAYSTACK INLINE */
function launchPaystack(email, data) {
    try {
        let handler = PaystackPop.setup({
            key: PAYSTACK_PUBLIC_KEY,
            email: email,
            amount: data.amount_ngn * 100,
            currency: "NGN",
            ref: data.reference,
            
            callback: function(response) {
                verifyPayment(response.reference);
            },
            
            onClose: function() {
                hideSpinner();
                toast("Transaction cancelled");
            },
            
            metadata: {
                custom_fields: [
                    {
                        display_name: "Donation Type",
                        variable_name: "donation_type",
                        value: data.interval || 'one-time'
                    }
                ]
            }
        });
        
        handler.openIframe();
        
    } catch (error) {
        console.error('Paystack initialization error:', error);
        toast("Failed to initialize payment gateway", true);
        hideSpinner();
    }
}

/* VERIFY PAYMENT */
async function verifyPayment(reference) {
    try {
        const response = await fetch(`${API_BASE_URL}/api/payments/verify/${reference}`);
        const result = await response.json();
        
        if (response.ok && result.success) {
            toast("Donation successful! Thank you for your support.");
            
            setTimeout(() => {
                const modal = bootstrap.Modal.getInstance(document.getElementById('donationModal'));
                if (modal) {
                    modal.hide();
                }
                resetForm();
            }, 2000);
        } else {
            toast(result.message || "Payment verified but server confirmation failed", true);
        }
        
    } catch (error) {
        console.error('Verification error:', error);
        toast("Payment successful but verification failed", true);
    } finally {
        hideSpinner();
    }
}

/* RESET FORM */
function resetForm() {
    paymentForm.reset();
    amountCards.forEach(c => c.classList.remove('active'));
    amountInput.value = 20;
    amountCards.forEach(card => {
        if (card.dataset.amount === '20') {
            card.classList.add('active');
        }
    });
    updatePreview();
    toastBox.style.display = 'none';
}

/* MODAL RESET ON CLOSE */
const donationModal = document.getElementById('donationModal');
if (donationModal) {
    donationModal.addEventListener('hidden.bs.modal', function () {
        if (!processing) {
            resetForm();
        }
    });
}

/* INPUT VALIDATION */
amountInput.addEventListener('keypress', function(e) {
    if (e.key < '0' || e.key > '9') {
        e.preventDefault();
    }
});

</script>
