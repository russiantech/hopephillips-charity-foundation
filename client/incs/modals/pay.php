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

/* Make payment modal above nav/others */
.modal{
z-index:2000!important;
}

.modal-backdrop{
z-index:1990!important;
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

<input type="radio" name="interval" value="annually">

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
<!-- v2 - Complete corrected version -->
<script>
// ==================== CONFIGURATION MANAGER ====================
const AppConfig = (function() {
    let config = null;
    let loadingPromise = null;
    
    // Initialize config from server
    async function loadConfig() {
        if (config) return config;
        if (loadingPromise) return loadingPromise;
        
        loadingPromise = (async () => {
            try {
                const response = await fetch('/api/payments/config', {
                    headers: { 'Accept': 'application/json' }
                });
                
                if (!response.ok) throw new Error(`HTTP ${response.status}`);
                
                const result = await response.json();
                
                // Extract data from your backend response structure
                if (result.success && result.data) {
                    config = result.data;
                    console.log('✅ Config loaded:', config.environment || 'development');
                    
                    // Update exchange rate from config
                    if (config.exchange_rate) {
                        USD_NGN = config.exchange_rate;
                        updatePreview();
                    }
                    
                    return config;
                } else {
                    throw new Error(result.message || 'Invalid config response');
                }
            } catch (error) {
                console.error('❌ Failed to load config:', error);
                // Emergency fallback
                config = {
                    paystack_public_key: 'pk_test_942792a5adee387f3a4ebe5f9094b98f994bdd62',
                    environment: 'development',
                    exchange_rate: 1500,
                    currency: 'NGN',
                    api_base_url: 'http://localhost:5000/api'
                };
                return config;
            } finally {
                loadingPromise = null;
            }
        })();
        
        return loadingPromise;
    }
    
    // Public API
    return {
        // Initialize and ensure config is loaded
        ready: async function() {
            return await loadConfig();
        },
        
        // Get specific config value with fallback
        get: async function(key, defaultValue = null) {
            const cfg = await loadConfig();
            return cfg && cfg[key] !== undefined ? cfg[key] : defaultValue;
        },
        
        // Sync get - only if already loaded
        getSync: function(key, defaultValue = null) {
            return config && config[key] !== undefined ? config[key] : defaultValue;
        },
        
        // Check if config is loaded
        isReady: function() {
            return config !== null;
        },
        
        // Get full config
        getAll: function() {
            return config;
        }
    };
})();

// ==================== DOM ELEMENTS ====================
const amountCards = document.querySelectorAll(".amount-card");
const amountInput = document.getElementById("amount");
const ngnPreview = document.getElementById("ngnPreview");
const payBtn = document.getElementById("payBtn");
const toastBox = document.getElementById("formToast");
const paymentForm = document.getElementById("paymentForm");

let processing = false;
let USD_NGN = 1500; // Will be updated from config

// ==================== INITIALIZATION ====================
(async function initializeApp() {
    // Load config first
    await AppConfig.ready();
    
    // Update exchange rate from config
    USD_NGN = AppConfig.getSync('exchange_rate', 1500);
    
    // Set default amount
    if (!amountInput.value) {
        amountInput.value = 20;
        amountCards.forEach(card => {
            if (card.dataset.amount === '20') {
                card.classList.add('active');
            }
        });
    }
    updatePreview();
    
    console.log('🚀 App initialized with config:', {
        environment: AppConfig.getSync('environment'),
        currency: AppConfig.getSync('currency'),
        rate: USD_NGN
    });
})();

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
    let usd = parseFloat(amountInput.value) || 0;
    let ngn = usd * USD_NGN;
    ngnPreview.innerText = new Intl.NumberFormat().format(Math.round(ngn));
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

/* GET API BASE URL */
async function getApiBaseUrl() {
    const baseUrl = await AppConfig.get('api_base_url', 'http://localhost:5000/api');
    // Remove trailing slash if present
    return baseUrl.replace(/\/$/, '');
}

/* FORM SUBMIT */
paymentForm.addEventListener("submit", async function(e) {
    e.preventDefault();
    
    if (processing) return;
    
    if (!validateForm()) {
        return;
    }
    
    // Ensure config is loaded
    if (!AppConfig.isReady()) {
        await AppConfig.ready();
    }
    
    processing = true;
    showSpinner();

    const email = document.getElementById("email").value;
    const description = document.getElementById("description").value;
    const amount = amountInput.value;
    const interval = document.querySelector("input[name='interval']:checked")?.value || null;

    try {
        const apiBaseUrl = await getApiBaseUrl();
        const res = await fetch(`${apiBaseUrl}/payments/initialize`, {
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
        // if (!response.ok) {
        //     // Handle specific error messages
        //     if (response.message?.includes('gateway')) {
        //         toast('Payment gateway is temporarily unavailable. Please try again in a few minutes.', true);
        //     } else if (response.message?.includes('network')) {
        //         toast('Network error. Please check your internet connection.', true);
        //     } else {
        //         toast(response.message || 'Payment initialization failed', true);
        //     }
        //     return;
        // }

        if (response.success && response.data) {
            // Get Paystack key from config
            const paystackKey = AppConfig.getSync('paystack_public_key');
            if (!paystackKey) {
                throw new Error('Payment gateway not configured');
            }
            launchPaystack(email, response.data, paystackKey);
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

function launchPaystack(email, data, paystackKey) {
    try {
        // Validate Paystack is loaded
        if (typeof PaystackPop === 'undefined') {
            throw new Error('Paystack SDK not loaded');
        }
        
        // Validate key
        if (!paystackKey || !paystackKey.startsWith('pk_')) {
            throw new Error('Invalid Paystack public key');
        }
        
        let handler = PaystackPop.setup({
            key: paystackKey,
            email: email,
            amount: data.amount_ngn * 100, // Convert to kobo
            currency: AppConfig.getSync('currency', 'NGN'),
            ref: data.reference,
            // 
            label: "Hope Phillips Charity Foundation",

            metadata: {

                description: data.description,

                custom_fields: [

                    {
                        display_name: "Donation Type",
                        variable_name: "donation_type",
                        value: data.interval || "one-time"
                    },

                    {
                        display_name: "Purpose",
                        variable_name: "purpose",
                        value: data.description
                    }

                ]
            },
                
            callback: function(response) {
                verifyPayment(response.reference);
            },
            
            onClose: function() {
                hideSpinner();
                toast("Transaction cancelled");
            },
            
            onError: function(error) {
                console.error('Paystack error:', error);
                hideSpinner();
                toast("Payment failed: " + (error.message || 'Unknown error'), true);
            },
            
            // metadata: {
            //     custom_fields: [
            //         {
            //             display_name: "Donation Type",
            //             variable_name: "donation_type",
            //             value: data.interval || 'one-time'
            //         }
            //     ]
            // }
        });
        
        handler.openIframe();
        
    } catch (error) {
        console.error('Paystack initialization error:', error);
        toast(error.message || "Failed to initialize payment gateway", true);
        hideSpinner();
    }
}

// v2
/*
function launchPaystack(email, data, paystackKey) {

    const handler = PaystackPop.setup({

        key: paystackKey,

        email: email,

        amount: data.amount_kobo, // backend already converted

        currency: "NGN",

        ref: data.reference,

        metadata: {
            custom_fields: [
                {
                    display_name: "Donation Type",
                    variable_name: "donation_type",
                    value: data.interval || "one-time"
                }
            ]
        },

        callback: function(response){
            verifyPayment(response.reference);
        },

        onClose: function(){
            hideSpinner();
            toast("Transaction cancelled");
        }

    });

    handler.openIframe();
}
*/

// v3
/*
function launchPaystack(email, data, paystackKey){

    const handler = PaystackPop.setup({

        key: paystackKey,

        email: email,

        amount: data.amount_kobo,

        currency: "NGN",

        ref: data.reference,

        label: "Dunistech Foundation",

        metadata: {

            description: data.description,

            custom_fields: [

                {
                    display_name: "Donation Type",
                    variable_name: "donation_type",
                    value: data.interval || "one-time"
                },

                {
                    display_name: "Purpose",
                    variable_name: "purpose",
                    value: data.description
                }

            ]
        },

        callback: function(response){
            verifyPayment(response.reference);
        },

        onClose: function(){
            hideSpinner();
            toast("Transaction cancelled");
        }

    });

    handler.openIframe();
}
*/

// /* VERIFY PAYMENT */
/*
async function verifyPayment(reference) {
    try {
        const apiBaseUrl = await getApiBaseUrl();
        const response = await fetch(`${apiBaseUrl}/payments/verify/${reference}`);
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
*/
// handles closing modal after successful properly:
async function verifyPayment(reference) {

    try {

        const apiBaseUrl = await getApiBaseUrl();

        const response = await fetch(`${apiBaseUrl}/payments/verify/${reference}`);

        const result = await response.json();

        if (response.ok && result.success) {

            toast("Donation successful! Thank you for your support.");

            // Disable button so user doesn't click again
            payBtn.disabled = true;

            setTimeout(() => {

                // const modalEl = document.getElementById('donationModal');

                // let modal = bootstrap.Modal.getInstance(modalEl);

                // if (!modal) {
                //     modal = new bootstrap.Modal(modalEl);
                // }

                // modal.hide();

                // v2
                // const modalEl = document.getElementById('donationModal');

                // const modal = bootstrap.Modal.getOrCreateInstance(modalEl);

                // modal.hide();

                // v3
                document.getElementById("donationModal").classList.remove("show");
                document.querySelector(".modal-backdrop")?.remove();
                document.body.classList.remove("modal-open");

                resetForm();

            }, 2000);

        } else {

            toast(result.message || "Payment verified but confirmation failed", true);

        }

    } catch (error) {

        console.error('Verification error:', error);

        toast("Payment successful but verification failed", true);

    } finally {

        hideSpinner();

    }

}


// // v2
// /* VERIFY PAYMENT */
// async function verifyPayment(reference) {
//     try {
//         const apiBaseUrl = await getApiBaseUrl();
//         const response = await fetch(`${apiBaseUrl}/payments/verify/${reference}`);
//         const result = await response.json();
        
//         console.log('Verification result:', result);
        
//         if (response.ok && result.success) {
//             const status = result.data.status;
//             // const yourStatus = result.data.status;
            
//             // Handle different statuses
//             switch(status) {
//                 case 'success':
//                     toast("✅ Donation successful! Thank you for your support.");
//                     setTimeout(() => {
//                         const modal = bootstrap.Modal.getInstance(document.getElementById('donationModal'));
//                         if (modal) {
//                             modal.hide();
//                         }
//                         resetForm();
//                     }, 2000);
//                     break;
                    
//                 case 'failed':
//                     toast("❌ Payment failed. Please try again.", true);
//                     hideSpinner();
//                     break;
                    
//                 case 'abandoned':
//                     toast("⚠️ Payment was cancelled.", true);
//                     hideSpinner();
//                     break;
                    
//                 case 'pending':
//                 case 'processing':
//                     toast("⏳ Payment is processing. You'll receive a confirmation shortly.", false);
//                     // Retry verification after 5 seconds
//                     setTimeout(() => {
//                         verifyPayment(reference);
//                     }, 5000);
//                     break;
                    
//                 default:
//                     toast(`Payment status: ${status}`, false);
//                     hideSpinner();
//             }
//         } else {
//             toast(result.message || "Payment verification failed", true);
//             hideSpinner();
//         }
        
//     } catch (error) {
//         console.error('Verification error:', error);
//         toast("Payment verification failed. Please contact support.", true);
//         hideSpinner();
//     }
// }


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

/* EXPORT CONFIG FOR DEBUGGING (optional) */
window.AppConfig = AppConfig; // For console debugging
</script>
