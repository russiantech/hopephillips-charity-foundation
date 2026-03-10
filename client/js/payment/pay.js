const amountButtons = document.querySelectorAll(".amount-btn")

amountButtons.forEach(btn => {

btn.addEventListener("click", () => {

document.getElementById("amount").value = btn.dataset.amount

amountButtons.forEach(b => b.classList.remove("active"))
btn.classList.add("active")

})

})