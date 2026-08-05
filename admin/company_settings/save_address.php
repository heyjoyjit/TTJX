<?php
// admin/company_settings/save_address.php
require_once $_SERVER['DOCUMENT_ROOT'] . '/includes/db.php';
require_once $_SERVER['DOCUMENT_ROOT'] . '/includes/auth.php';

$pdo = Database::getInstance()->getConnection();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    try {
        $pdo->beginTransaction();

        $company_name = $_POST['company_name'] ?? 'Traveltara Pvt. Ltd.';
        $tagline = $_POST['tagline'] ?? '';
        $address_line_1 = $_POST['address_line_1'] ?? '';
        $city = $_POST['city'] ?? '';
        $state = $_POST['state'] ?? '';
        $state_code = $_POST['state_code'] ?? '';
        $pincode = $_POST['pincode'] ?? '';
        $phone = $_POST['phone'] ?? '';
        $email = $_POST['email'] ?? '';
        $gstin = $_POST['gstin'] ?? '';
        $pan = $_POST['pan'] ?? '';
        $bank_name = $_POST['bank_name'] ?? '';
        $account_name = $_POST['account_name'] ?? '';
        $account_number = $_POST['account_number'] ?? '';
        $ifsc_code = $_POST['ifsc_code'] ?? '';
        $upi_id = $_POST['upi_id'] ?? '';

        $is_primary = isset($_POST['is_primary']) ? 1 : 0;

        // If this address is set to primary, remove primary status from all other addresses
        if ($is_primary === 1) {
            $pdo->query("UPDATE company_addresses SET is_primary = 0");
        }

        $stmt = $pdo->prepare("
            INSERT INTO company_addresses (
                company_name, tagline, address_line_1, city, state, state_code, pincode, 
                phone, email, gstin, pan, bank_name, account_name, account_number, ifsc_code, upi_id, is_primary
            ) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)
        ");

        $stmt->execute([
            $company_name,
            $tagline,
            $address_line_1,
            $city,
            $state,
            $state_code,
            $pincode,
            $phone,
            $email,
            $gstin,
            $pan,
            $bank_name,
            $account_name,
            $account_number,
            $ifsc_code,
            $upi_id,
            $is_primary
        ]);

        $pdo->commit();
        header("Location: index.php?success=Address Added");
        exit;
    } catch (Exception $e) {
        $pdo->rollBack();
        die("Error saving company address: " . $e->getMessage());
    }
}
