<?php
// Tambahkan debugging untuk melihat apa yang terjadi saat update status
if ($_SERVER['REQUEST_METHOD'] === 'POST' && $_POST['action'] === 'update_status') {
    // Log semua data yang diterima
    error_log("=== STATUS UPDATE DEBUG ===");
    error_log("POST data: " . print_r($_POST, true));
    
    $orderId = intval($_POST['order_id'] ?? 0);
    $status = $_POST['status'] ?? '';
    $notes = $_POST['notes'] ?? '';
    
    error_log("Parsed - Order ID: $orderId, Status: $status, Notes: $notes");
    
    // Validasi input
    if ($orderId <= 0) {
        error_log("ERROR: Invalid order ID");
        echo json_encode(['success' => false, 'error' => 'ID pesanan tidak valid']);
        exit;
    }
    
    // Cek koneksi database
    if (!$koneksi) {
        error_log("ERROR: Database connection failed");
        echo json_encode(['success' => false, 'error' => 'Koneksi database gagal']);
        exit;
    }
    
    try {
        // Cek pesanan saat ini
        $stmt = $koneksi->prepare("SELECT status_pesanan FROM pesanan WHERE id_pesanan = ?");
        if (!$stmt) {
            error_log("ERROR: Prepare statement failed: " . $koneksi->error);
            echo json_encode(['success' => false, 'error' => 'Database prepare error']);
            exit;
        }
        
        $stmt->bind_param("i", $orderId);
        $stmt->execute();
        $result = $stmt->get_result();
        $currentOrder = $result->fetch_assoc();
        
        if (!$currentOrder) {
            error_log("ERROR: Order not found - ID: $orderId");
            echo json_encode(['success' => false, 'error' => 'Pesanan tidak ditemukan']);
            exit;
        }
        
        error_log("Current status: " . $currentOrder['status_pesanan']);
        
        // Update status
        $updateStmt = $koneksi->prepare("UPDATE pesanan SET status_pesanan = ? WHERE id_pesanan = ?");
        if (!$updateStmt) {
            error_log("ERROR: Update prepare failed: " . $koneksi->error);
            echo json_encode(['success' => false, 'error' => 'Database update prepare error']);
            exit;
        }
        
        $updateStmt->bind_param("si", $status, $orderId);
        $updateResult = $updateStmt->execute();
        
        if ($updateResult) {
            $affectedRows = $koneksi->affected_rows;
            error_log("Update successful - Affected rows: $affectedRows");
            
            // Verifikasi update berhasil
            $verifyStmt = $koneksi->prepare("SELECT status_pesanan FROM pesanan WHERE id_pesanan = ?");
            $verifyStmt->bind_param("i", $orderId);
            $verifyStmt->execute();
            $verifyResult = $verifyStmt->get_result();
            $updatedOrder = $verifyResult->fetch_assoc();
            
            error_log("New status after update: " . $updatedOrder['status_pesanan']);
            
            echo json_encode([
                'success' => true, 
                'message' => 'Status pesanan berhasil diperbarui',
                'old_status' => $currentOrder['status_pesanan'],
                'new_status' => $updatedOrder['status_pesanan'],
                'affected_rows' => $affectedRows
            ]);
        } else {
            error_log("ERROR: Update failed: " . $koneksi->error);
            echo json_encode(['success' => false, 'error' => 'Gagal memperbarui status: ' . $koneksi->error]);
        }
        
    } catch (Exception $e) {
        error_log("EXCEPTION: " . $e->getMessage());
        echo json_encode(['success' => false, 'error' => $e->getMessage()]);
    }
    exit;
}
?>
