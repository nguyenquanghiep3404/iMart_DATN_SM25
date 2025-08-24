/**
 * Helper script để debug thông tin thanh toán
 * Sử dụng: Mở console trên bất kỳ trang nào và gọi showPaymentDebugLogs()
 */

// Hàm hiển thị tất cả debug logs từ sessionStorage
function showPaymentDebugLogs() {
    const logs = JSON.parse(sessionStorage.getItem('payment_debug_logs') || '[]');
    
    if (logs.length === 0) {
        console.log('Không có debug logs nào được tìm thấy.');
        return;
    }
    
    console.group('🔍 Payment Debug Logs');
    logs.forEach((log, index) => {
        console.group(`Log ${index + 1} - ${log.timestamp}`);
        console.log('Save Address Checkbox:', log.saveAddressCheckbox);
        console.log('Checked:', log.checked);
        console.log('Is Logged In:', log.isLoggedIn);
        console.log('Has Addresses:', log.hasAddresses);
        console.log('Address ID:', log.addressId);
        console.groupEnd();
    });
    console.groupEnd();
    
    return logs;
}

// Hàm xóa tất cả debug logs
function clearPaymentDebugLogs() {
    sessionStorage.removeItem('payment_debug_logs');
    console.log('✅ Đã xóa tất cả payment debug logs.');
}

// Hàm hiển thị log mới nhất
function showLatestPaymentDebugLog() {
    const logs = JSON.parse(sessionStorage.getItem('payment_debug_logs') || '[]');
    
    if (logs.length === 0) {
        console.log('Không có debug logs nào được tìm thấy.');
        return;
    }
    
    const latestLog = logs[logs.length - 1];
    console.group('🔍 Latest Payment Debug Log');
    console.log('Timestamp:', latestLog.timestamp);
    console.log('Save Address Checkbox:', latestLog.saveAddressCheckbox);
    console.log('Checked:', latestLog.checked);
    console.log('Is Logged In:', latestLog.isLoggedIn);
    console.log('Has Addresses:', latestLog.hasAddresses);
    console.log('Address ID:', latestLog.addressId);
    console.groupEnd();
    
    return latestLog;
}

// Tự động load script và hiển thị hướng dẫn
console.log('🚀 Payment Debug Helper đã được load!');
console.log('📋 Các lệnh có sẵn:');
console.log('  - showPaymentDebugLogs(): Hiển thị tất cả debug logs');
console.log('  - showLatestPaymentDebugLog(): Hiển thị log mới nhất');
console.log('  - clearPaymentDebugLogs(): Xóa tất cả debug logs');

// Tự động hiển thị log mới nhất nếu có
if (JSON.parse(sessionStorage.getItem('payment_debug_logs') || '[]').length > 0) {
    console.log('\n💡 Có debug logs từ trang thanh toán. Gọi showLatestPaymentDebugLog() để xem.');
}