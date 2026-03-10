<style>

.modal{
z-index:2000!important;
}

.modal-backdrop{
z-index:1990!important;
}


/* AMOUNT GRID */

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

<script>

const amountCards=document.querySelectorAll(".amount-card")
const amountInput=document.getElementById("amount")
const ngnPreview=document.getElementById("ngnPreview")
const payBtn=document.getElementById("payBtn")
const toastBox=document.getElementById("formToast")

let processing=false

let USD_NGN=1500



/* AMOUNT CARD SELECT */

amountCards.forEach(card=>{

card.addEventListener("click",()=>{

amountCards.forEach(c=>c.classList.remove("active"))

card.classList.add("active")

amountInput.value=card.dataset.amount

updatePreview()

})

})



/* CUSTOM AMOUNT INPUT */

amountInput.addEventListener("input",()=>{

amountCards.forEach(c=>c.classList.remove("active"))

updatePreview()

})



/* LIVE CONVERSION */

function updatePreview(){

let usd=amountInput.value||0

let ngn=usd*USD_NGN

ngnPreview.innerText=new Intl.NumberFormat().format(ngn)

}



/* TOAST */

function toast(msg){

toastBox.innerText=msg

toastBox.style.display="block"

setTimeout(()=>{
toastBox.style.display="none"
},3000)

}



/* BUTTON SPINNER */

function showSpinner(){

payBtn.disabled=true

payBtn.innerHTML=`
<span class="spinner-border spinner-border-sm me-2"></span>
Processing...
`

}


function hideSpinner(){

payBtn.disabled=false

payBtn.innerHTML="Donate Now"

processing=false

}



/* FORM SUBMIT */

document.getElementById("paymentForm")
.addEventListener("submit",async function(e){

e.preventDefault()

if(processing) return

processing=true

showSpinner()


const email=document.getElementById("email").value
const description=document.getElementById("description").value
const amount=amountInput.value

const interval=
document.querySelector("input[name='interval']:checked")?.value||null


try{

const res=await fetch("/api/payments/initialize",{

method:"POST",

headers:{
"Content-Type":"application/json"
},

body:JSON.stringify({
email,
amount,
interval,
description
})

})


const data=await res.json()

launchPaystack(email,data)

}catch{

toast("Payment initialization failed")

hideSpinner()

}

})



/* PAYSTACK INLINE */

function launchPaystack(email,data){

let handler=PaystackPop.setup({

key:PAYSTACK_PUBLIC_KEY,

email:email,

amount:data.amount_ngn*100,

currency:"NGN",

ref:data.reference,


callback:function(response){

verifyPayment(response.reference)

},

onClose:function(){

hideSpinner()

toast("Transaction cancelled")

}

})

handler.openIframe()

}



/* VERIFY PAYMENT */

async function verifyPayment(reference){

await fetch(`/api/payments/verify/${reference}`)

toast("Donation successful")

location.reload()

}

</script>
