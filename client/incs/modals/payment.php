<div class="modal fade" id="paymentModal">
<div class="modal-dialog modal-dialog-centered">
<div class="modal-content">

<div class="modal-header">
<h5 class="modal-title">Make Payment</h5>
<button class="btn-close" data-bs-dismiss="modal"></button>
</div>

<div class="modal-body">

<form id="paymentForm">

<div class="mb-3">
<label>Email</label>
<input type="email" class="form-control" id="email" required>
</div>

<div class="mb-3">
<label>Description</label>
<input type="text" class="form-control" id="description">
</div>

<div class="mb-3">

<label>Choose Amount</label>

<div class="d-flex flex-wrap gap-2 mb-2">

<button type="button" class="btn btn-outline-primary amount-btn" data-amount="2000">₦2,000</button>
<button type="button" class="btn btn-outline-primary amount-btn" data-amount="5000">₦5,000</button>
<button type="button" class="btn btn-outline-primary amount-btn" data-amount="10000">₦10,000</button>

</div>

<input
type="number"
class="form-control"
id="amount"
placeholder="Enter custom amount"
required
>

</div>

<div class="mb-3">

<label>Subscription Duration (Optional)</label>

<div class="form-check">
<input class="form-check-input" type="radio" name="interval" value="">
<label class="form-check-label">One-time payment</label>
</div>

<div class="form-check">
<input class="form-check-input" type="radio" name="interval" value="monthly">
<label class="form-check-label">Monthly</label>
</div>

<div class="form-check">
<input class="form-check-input" type="radio" name="interval" value="yearly">
<label class="form-check-label">Yearly</label>
</div>

</div>

<button class="btn btn-primary w-100">
Pay Now
</button>

</form>

</div>
</div>
</div>
</div>
