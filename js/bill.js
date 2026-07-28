/* ============================================================
   BILLING & INVOICE CALCULATION ENGINE
   ============================================================ */

document.addEventListener('DOMContentLoaded', function () {
    const roomChargesInput = document.getElementById('billRoomCharges');
    const extraFoodInput = document.getElementById('billExtraFood');
    const extraLaundryInput = document.getElementById('billExtraLaundry');
    const extraMinibarInput = document.getElementById('billExtraMinibar');
    const discountInput = document.getElementById('billDiscount');
    const paidAmountInput = document.getElementById('billPaidAmount');

    const subtotalDisplay = document.getElementById('billSubtotal');
    const gstDisplay = document.getElementById('billGstAmount');
    const grandTotalDisplay = document.getElementById('billGrandTotal');
    const dueAmountDisplay = document.getElementById('billDueAmount');

    function calculateBill() {
        if (!roomChargesInput) return;

        const roomCharges = parseFloat(roomChargesInput.value) || 0;
        const extraFood = parseFloat(extraFoodInput ? extraFoodInput.value : 0) || 0;
        const extraLaundry = parseFloat(extraLaundryInput ? extraLaundryInput.value : 0) || 0;
        const extraMinibar = parseFloat(extraMinibarInput ? extraMinibarInput.value : 0) || 0;
        const discount = parseFloat(discountInput ? discountInput.value : 0) || 0;
        const paidAmount = parseFloat(paidAmountInput ? paidAmountInput.value : 0) || 0;

        const subtotal = roomCharges + extraFood + extraLaundry + extraMinibar;
        const gstRate = 0.12; // 12% GST
        const gstAmount = subtotal * gstRate;
        const grandTotal = Math.max(0, (subtotal + gstAmount) - discount);
        const dueAmount = Math.max(0, grandTotal - paidAmount);

        if (subtotalDisplay) subtotalDisplay.value = subtotal.toFixed(2);
        if (gstDisplay) gstDisplay.value = gstAmount.toFixed(2);
        if (grandTotalDisplay) grandTotalDisplay.value = grandTotal.toFixed(2);
        if (dueAmountDisplay) dueAmountDisplay.value = dueAmount.toFixed(2);
    }

    const billInputs = [roomChargesInput, extraFoodInput, extraLaundryInput, extraMinibarInput, discountInput, paidAmountInput];
    billInputs.forEach(input => {
        if (input) {
            input.addEventListener('input', calculateBill);
        }
    });

    calculateBill();

    // Print trigger button
    const printBtn = document.getElementById('printInvoiceBtn');
    if (printBtn) {
        printBtn.addEventListener('click', function () {
            window.print();
        });
    }
});
