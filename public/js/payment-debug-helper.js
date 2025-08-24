// Payment Debug Helper
// Các hàm tiện ích để debug thông tin thanh toán

/**
 * Hiển thị log debug thanh toán mới nhất
 */
function showLatestPaymentDebugLog() {
    const logs = JSON.parse(sessionStorage.getItem('payment_debug_logs') || '[]');
    
    if (logs.length === 0) {
        console.log('🔍 No payment debug logs found');
        return null;
    }
    
    const latestLog = logs[logs.length - 1];
    
    console.log('🔍 Latest Payment Debug Log');
    console.log('Timestamp:', latestLog.timestamp);
    console.log('Address Mode:', latestLog.addressMode);
    
    if (latestLog.addressMode === 'existing') {
        console.log('Selected Address ID:', latestLog.selectedAddressId);
        console.log('Address ID:', latestLog.addressId);
    } else {
        console.log('Save Address Checkbox:', latestLog.saveAddressCheckbox);
        console.log('Checked:', latestLog.checked);
        console.log('Address ID:', latestLog.addressId);
    }
    
    console.log('Is Logged In:', latestLog.isLoggedIn);
    console.log('Has Addresses:', latestLog.hasAddresses);
    console.log(latestLog);
    
    return latestLog;
}

/**
 * Hiển thị tất cả logs debug thanh toán
 */
function showAllPaymentDebugLogs() {
    const logs = JSON.parse(sessionStorage.getItem('payment_debug_logs') || '[]');
    
    if (logs.length === 0) {
        console.log('🔍 No payment debug logs found');
        return [];
    }
    
    console.log(`🔍 All Payment Debug Logs (${logs.length} entries):`);
    logs.forEach((log, index) => {
        console.log(`\n--- Log ${index + 1} ---`);
        console.log('Timestamp:', log.timestamp);
        console.log('Address Mode:', log.addressMode);
        console.log('Address ID:', log.addressId);
        console.log('Selected Address ID:', log.selectedAddressId);
        if (log.addressMode === 'new') {
            console.log('Save Address:', log.checked);
        }
        console.log('Is Logged In:', log.isLoggedIn);
        console.log('Has Addresses:', log.hasAddresses);
    });
    
    return logs;
}

/**
 * Xóa tất cả logs debug thanh toán
 */
function clearPaymentDebugLogs() {
    sessionStorage.removeItem('payment_debug_logs');
    console.log('🗑️ Payment debug logs cleared');
}

/**
 * Kiểm tra trạng thái hiện tại của trang thanh toán
 */
function checkCurrentPaymentState() {
    if (typeof CheckoutPage === 'undefined') {
        console.log('❌ CheckoutPage not found - not on payment page');
        return null;
    }
    
    const state = {
        selectedAddressId: CheckoutPage.state?.selectedAddressId,
        hasAddresses: CheckoutPage.state?.hasAddresses,
        isLoggedIn: CheckoutPage.state?.isLoggedIn,
        addresses: CheckoutPage.state?.addresses?.length || 0
    };
    
    console.log('🔍 Current Payment State:');
    console.log('Selected Address ID:', state.selectedAddressId || 'none');
    console.log('Has Addresses:', state.hasAddresses);
    console.log('Is Logged In:', state.isLoggedIn);
    console.log('Total Addresses:', state.addresses);
    
    return state;
}

// Đưa các hàm vào global scope để có thể gọi từ console
window.showLatestPaymentDebugLog = showLatestPaymentDebugLog;
window.showAllPaymentDebugLogs = showAllPaymentDebugLogs;
window.clearPaymentDebugLogs = clearPaymentDebugLogs;
window.checkCurrentPaymentState = checkCurrentPaymentState;

console.log('💡 Payment Debug Helper loaded. Available functions:');
console.log('- showLatestPaymentDebugLog()');
console.log('- showAllPaymentDebugLogs()');
console.log('- clearPaymentDebugLogs()');
console.log('- checkCurrentPaymentState()');