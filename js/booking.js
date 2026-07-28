/* ============================================================
   BOOKING MODULE JAVASCRIPT ENGINE
   ============================================================ */

document.addEventListener('DOMContentLoaded', function () {
    const roomSelect = document.getElementById('bookingRoomSelect');
    const checkInInput = document.getElementById('checkInDate');
    const checkOutInput = document.getElementById('checkOutDate');
    const totalDaysDisplay = document.getElementById('totalDaysDisplay');
    const pricePerNightInput = document.getElementById('pricePerNightInput');
    const bookingAmountInput = document.getElementById('bookingAmountInput');
    const advancePaymentInput = document.getElementById('advancePaymentInput');
    const balanceAmountInput = document.getElementById('balanceAmountInput');

    function calculateBookingDetails() {
        if (!checkInInput || !checkOutInput) return;

        const checkIn = new Date(checkInInput.value);
        const checkOut = new Date(checkOutInput.value);

        if (checkInInput.value && checkOutInput.value && checkOut > checkIn) {
            const timeDiff = checkOut.getTime() - checkIn.getTime();
            const days = Math.ceil(timeDiff / (1000 * 3600 * 24));
            
            if (totalDaysDisplay) {
                totalDaysDisplay.textContent = days + (days === 1 ? ' Night' : ' Nights');
            }

            // Get selected room price
            let price = 0;
            if (roomSelect && roomSelect.selectedIndex > 0) {
                const selectedOption = roomSelect.options[roomSelect.selectedIndex];
                price = parseFloat(selectedOption.getAttribute('data-price')) || 0;
            } else if (pricePerNightInput) {
                price = parseFloat(pricePerNightInput.value) || 0;
            }

            const totalAmount = days * price;
            if (bookingAmountInput) {
                bookingAmountInput.value = totalAmount.toFixed(2);
            }

            const advance = parseFloat(advancePaymentInput ? advancePaymentInput.value : 0) || 0;
            const balance = Math.max(0, totalAmount - advance);
            
            if (balanceAmountInput) {
                balanceAmountInput.value = balance.toFixed(2);
            }
        } else {
            if (totalDaysDisplay) totalDaysDisplay.textContent = '1 Night';
            if (bookingAmountInput) bookingAmountInput.value = '0.00';
            if (balanceAmountInput) balanceAmountInput.value = '0.00';
        }
    }

    if (roomSelect) {
        roomSelect.addEventListener('change', calculateBookingDetails);
    }
    if (checkInInput) {
        checkInInput.addEventListener('change', calculateBookingDetails);
    }
    if (checkOutInput) {
        checkOutInput.addEventListener('change', calculateBookingDetails);
    }
    if (advancePaymentInput) {
        advancePaymentInput.addEventListener('input', calculateBookingDetails);
    }

    // Initialize calculation on page load if values exist
    calculateBookingDetails();
});
