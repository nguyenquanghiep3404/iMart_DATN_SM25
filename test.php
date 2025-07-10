<?php

// Dữ liệu lấy TỪ FILE LOG MỚI NHẤT của bạn
$signedPayload = "1751969961329.{\"error\":0,\"data\":{\"id\":0,\"reference\":\"MA_GIAO_DICH_THU_NGHIEM\",\"description\":\"giao dich thu nghiem\",\"amount\":599000,\"runningBalance\":0,\"transactionDateTime\":\"2025-07-08 17:19:21\",\"accountNumber\":\"88888888\",\"bankName\":\"VPBank\",\"bankAbbreviation\":\"VPB\",\"virtualAccountNumber\":\"\",\"virtualAccountName\":\"\",\"counterAccountName\":\"NGUYEN VAN A\",\"counterAccountNumber\":\"8888888888\",\"counterAccountBankId\":\"970415\",\"counterAccountBankName\":\"VietinBank\"}}";
$cassoSignature = "d37ac80a275f8001a12ba5cf2bbb277c28d18e2dddf4abf76e322985805944102dfb7f13ab654bf5833ff889de08d7040892bfeac0899a40395bdd7fda5a2d67";

// Webhook Secret LẤY TỪ CODE của bạn
$yourWebhookSecret = "AK_CS.af2577105be211f09efae5bad474ddda.xwrtuR3lasaWHsHo3hj6y368KKVPjXbRp3O67vCzSdBNQlHeseriLqq2KYZqfaR3pJgs6qKT"; // Thay bằng key bạn đang dùng

// Tính toán chữ ký
$expectedSignature = hash_hmac('sha512', $signedPayload, $yourWebhookSecret);

// So sánh
echo "Casso Signature   : " . $cassoSignature . "\n";
echo "My Calculated     : " . $expectedSignature . "\n";

if (hash_equals($cassoSignature, $expectedSignature)) {
    echo "\nSUCCESS: Chữ ký khớp! Secret Key của bạn ĐÚNG.\n";
} else {
    echo "\nERROR: Chữ ký KHÔNG khớp! Secret Key của bạn SAI.\n";
}