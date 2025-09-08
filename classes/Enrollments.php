
<?php
// Prevent unwanted output
ob_start();

ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);


// classes/Enrollments.php
require_once('../config.php');




class Enrollments extends DBConnection {
    private $settings;

    public function __construct() {
        global $_settings;
        $this->settings = $_settings;
        parent::__construct();
    }

    function __destruct() {
        parent::__destruct();
    }

    private function jsonResponse($data, $http_code = 200) {
        if (ob_get_length()) ob_clean();
        header('Content-Type: application/json');
        http_response_code($http_code);
        echo json_encode($data);
        exit;
    }

    public function get_available_instructors() {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            return $this->jsonResponse(['status' => 'failed', 'message' => 'Invalid request method'], 405);
        }

        $start_date = $_POST['start_date'] ?? null;
        $end_date   = $_POST['end_date'] ?? null;
        $time_slot  = $_POST['time_slot'] ?? null;

        if (!$start_date || !$end_date || !$time_slot) {
            return $this->jsonResponse(['status' => 'failed', 'message' => 'Missing required parameters'], 400);
        }

        // Validate date format
        if (!preg_match('/^\d{4}-\d{2}-\d{2}$/', $start_date) || !preg_match('/^\d{4}-\d{2}-\d{2}$/', $end_date)) {
            return $this->jsonResponse(['status' => 'failed', 'message' => 'Invalid date format. Use YYYY-MM-DD.'], 400);
        }

        $stmt = $this->conn->prepare("
            SELECT i.id, i.firstname, i.lastname
            FROM instructor i
            WHERE i.status = 1
              AND i.id NOT IN (
                SELECT e.assigned_instructor_id
                FROM enrollees e
                WHERE e.time_slot = ?
                  AND (
                    (e.start_date <= ? AND e.end_date >= ?) OR
                    (e.start_date <= ? AND e.end_date >= ?) OR
                    (e.start_date >= ? AND e.end_date <= ?)
                  )
              )
        ");
        
        if (!$stmt) {
            return $this->jsonResponse(['status' => 'failed', 'message' => 'Database preparation error'], 500);
        }

        $stmt->bind_param('sssssss', $time_slot, $end_date, $start_date, $end_date, $start_date, $start_date, $end_date);
        
        if (!$stmt->execute()) {
            return $this->jsonResponse(['status' => 'failed', 'message' => 'Database execution error'], 500);
        }

        $result = $stmt->get_result();
        $instructors = [];

        while ($row = $result->fetch_assoc()) {
            $instructors[] = [
                'id' => $row['id'],
                'name' => $row['firstname'] . ' ' . $row['lastname']
            ];
        }

        $stmt->close();
        return $this->jsonResponse(['status' => 'success', 'instructors' => $instructors]);
    }

   public function save() {
    if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
        return $this->jsonResponse(['status' => 'failed', 'message' => 'Invalid request method'], 405);
    }

    if (empty($_POST['id'])) {
        return $this->jsonResponse(['status' => 'failed', 'message' => 'Enrollment ID is required'], 400);
    }

    // Start transaction
    $this->conn->begin_transaction();

    try {
        $id = $this->conn->real_escape_string($_POST['id']);
        $enrollment_status = $_POST['enrollment_status'] ?? null;
        $is_cancelled = $enrollment_status === 'Cancelled';
        
        if ($is_cancelled) {
            // Delete all payments for this enrollment
            $delete_payments = $this->conn->query("DELETE FROM payment_list WHERE enrollees_id = '{$id}'");
            if (!$delete_payments) {
                throw new Exception('Error cancelling payments: ' . $this->conn->error);
            }

            // Set cancelled status
            $_POST['payment_status'] = 'Cancelled';
        }

        // Validate dates if not cancelled
        if (!$is_cancelled) {
            if (empty($_POST['start_date']) || empty($_POST['end_date'])) {
                throw new Exception('Please select both start and end dates');
            }

            $start_date = trim($_POST['start_date']);
            $end_date = trim($_POST['end_date']);
            
            if (!preg_match('/^\d{4}-\d{2}-\d{2}$/', $start_date) || 
                !preg_match('/^\d{4}-\d{2}-\d{2}$/', $end_date)) {
                throw new Exception('Invalid date format. Use YYYY-MM-DD.');
            }

            if (strtotime($start_date) > strtotime($end_date)) {
                throw new Exception('End date cannot be before start date');
            }
        }

        // Validate instructor if not cancelled
        if (!$is_cancelled && empty($_POST['assigned_instructor_id'])) {
            throw new Exception('Please assign an instructor');
        }

        // Prepare data for update
        $data = [];
        if ($is_cancelled) {
            $data['assigned_instructor_id'] = null;
            $data['start_date'] = null;
            $data['end_date'] = null;
            $data['payment_status'] = 'Cancelled';

            // Skip date validation fields when cancelled
    unset($_POST['start_date']);
    unset($_POST['end_date']);
        }

        $allowed_fields = [
            'assigned_instructor_id' => ['type' => 'int', 'nullable' => true],
            'enrollment_status' => ['type' => 'string', 'values' => ['Pending', 'Verified', 'In-Session', 'Completed', 'Cancelled']],
            'payment_status' => ['type' => 'string', 'values' => ['not set', 'Pending', 'Partially Paid', 'Paid', 'Cancelled'], 'nullable' => true],
            'start_date' => ['type' => 'date'],
            'end_date' => ['type' => 'date'],
            'remarks' => ['type' => 'string', 'max_length' => 500],
            'time_slot' => ['type' => 'string']
        ];

        foreach ($allowed_fields as $field => $rules) {
            if (isset($_POST[$field])) {
                $value = trim($_POST[$field]);

                if ($value === '' && ($rules['nullable'] ?? false)) {
                    $data[$field] = null;
                    continue;
                }

                switch ($rules['type']) {
                    case 'int':
                        if (!ctype_digit($value)) {
                            throw new Exception("Invalid value for $field");
                        }
                        $data[$field] = (int)$value;
                        break;

                    case 'date':
                        if (!preg_match('/^\d{4}-\d{2}-\d{2}$/', $value)) {
                            throw new Exception("Invalid date format for $field");
                        }
                        $data[$field] = $value;
                        break;

                    case 'string':
                        if (isset($rules['max_length']) && strlen($value) > $rules['max_length']) {
                            throw new Exception("$field too long (max {$rules['max_length']} chars)");
                        }
                        if (isset($rules['values']) && !in_array($value, $rules['values'])) {
                            throw new Exception("Invalid value for $field");
                        }
                        $data[$field] = $this->conn->real_escape_string($value);
                        break;
                }
            }
        }

        // Handle file upload
        if (isset($_FILES['copy_of_LLR']) && $_FILES['copy_of_LLR']['error'] === UPLOAD_ERR_OK) {
            $folder = '../uploads/LLR_copy/';
            if (!is_dir($folder) && !mkdir($folder, 0755, true)) {
                throw new Exception('Upload folder creation failed');
            }

            $allowed_mime = ['application/pdf', 'image/jpeg', 'image/png'];
            $file_info = finfo_open(FILEINFO_MIME_TYPE);
            $mime = finfo_file($file_info, $_FILES['copy_of_LLR']['tmp_name']);
            finfo_close($file_info);

            if (!in_array($mime, $allowed_mime)) {
                throw new Exception('Invalid file type. Only PDF, JPEG, and PNG allowed.');
            }

            $ext = pathinfo($_FILES['copy_of_LLR']['name'], PATHINFO_EXTENSION);
            $filename = "LLR_{$id}_" . time() . ".$ext";
            $target = $folder . $filename;

            if (!move_uploaded_file($_FILES['copy_of_LLR']['tmp_name'], $target)) {
                throw new Exception('Failed to upload file');
            }

            // Delete old file
            $old_file = $this->conn->query("SELECT copy_of_LLR FROM enrollees WHERE id = {$id}")->fetch_assoc()['copy_of_LLR'];
            if (!empty($old_file) && file_exists('../' . $old_file)) {
                @unlink('../' . $old_file);
            }

            $data['copy_of_LLR'] = 'uploads/LLR_copy/' . $filename;
        }

        // Handle payment processing
        $payment_saved = false;
        if (!$is_cancelled && isset($_POST['save_payment']) && $_POST['save_payment'] == '1') {
            if (empty($_POST['amount']) || floatval($_POST['amount']) <= 0) {
                throw new Exception('Payment amount must be positive');
            }

            $payment_data = [
                'enrollees_id' => $id,
                'amount_type' => $this->conn->real_escape_string($_POST['amount_type'] ?? ''),
                'amount' => floatval($_POST['amount']),
                'payment_mode' => $this->conn->real_escape_string($_POST['payment_mode'] ?? ''),
                'payment_status' => $this->conn->real_escape_string($_POST['payment_entry_status'] ?? ''),
                'upi_reference' => $this->conn->real_escape_string($_POST['upi_reference'] ?? '')
            ];

            $payment_stmt = $this->conn->prepare("
                INSERT INTO payment_list 
                (enrollees_id, amount_type, amount, payment_mode, payment_status, upi_reference, date_created) 
                VALUES (?, ?, ?, ?, ?, ?, NOW())
            ");

            if (!$payment_stmt) {
                throw new Exception('Payment DB error: ' . $this->conn->error);
            }

            $payment_stmt->bind_param(
                "ssdsss", 
                $payment_data['enrollees_id'],
                $payment_data['amount_type'],
                $payment_data['amount'],
                $payment_data['payment_mode'],
                $payment_data['payment_status'],
                $payment_data['upi_reference']
            );

            if (!$payment_stmt->execute()) {
                throw new Exception('Payment save failed: ' . $payment_stmt->error);
            }
            $payment_stmt->close();
            $payment_saved = true;
        }

        // Update enrollment record
    $fields = [];
    $params = [];
    $types = '';

    foreach ($data as $key => $value) {
        $fields[] = "$key = ?";
        $types .= is_int($value) ? 'i' : 's';
        $params[] = $value;
    }

    $types .= 'i';
    $params[] = $id;

    $sql = "UPDATE enrollees SET " . implode(', ', $fields) . " WHERE id = ?";
    $stmt = $this->conn->prepare($sql);

    if (!$stmt) {
        throw new Exception('DB error: ' . $this->conn->error);
    }

    $stmt->bind_param($types, ...$params);

    if (!$stmt->execute()) {
        throw new Exception('Update failed: ' . $stmt->error);
    }
    $stmt->close();

    // Update payment status if payment was processed
    if ($payment_saved) {
        $new_status = $this->updatePaymentStatus($id);
        $data['payment_status'] = $new_status;
        
        // Get updated payment totals immediately after updating status
        $payment_info = $this->conn->query("
            SELECT 
                (SELECT SUM(amount) FROM payment_list WHERE enrollees_id = '$id' AND payment_status = 'paid') as total_paid,
                (SELECT cost FROM package_list WHERE id = (SELECT package_id FROM enrollees WHERE id = '$id')) as package_cost
        ")->fetch_assoc();
        
        $total_paid = $payment_info['total_paid'] ? $payment_info['total_paid'] : 0;
        $balance = $payment_info['package_cost'] - $total_paid;
        
        // Include these in the response so frontend can use them for email
        $response = [
            'status' => 'success', 
            'message' => 'Enrollment updated successfully',
            'payment_status' => $data['payment_status'] ?? null,
            'total_paid' => $total_paid,
            'balance' => $balance
        ];
    } else {
        $response = [
            'status' => 'success', 
            'message' => 'Enrollment updated successfully',
            'payment_status' => $data['payment_status'] ?? null
        ];
    }

    $this->conn->commit();
    return $this->jsonResponse($response);

    } catch (Exception $e) {
        $this->conn->rollback();
        error_log('Save Error: ' . $e->getMessage());
        return $this->jsonResponse(['status' => 'failed', 'message' => $e->getMessage()], 500);
    }
}

public function get_payment_summary() {
    if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
        return $this->jsonResponse(['status' => 'failed', 'message' => 'Invalid request method'], 405);
    }

    if (empty($_POST['id'])) {
        return $this->jsonResponse(['status' => 'failed', 'message' => 'Enrollment ID required'], 400);
    }

    $id = $this->conn->real_escape_string($_POST['id']);
    
    try {
        $result = $this->conn->query("
            SELECT 
                (SELECT SUM(amount) FROM payment_list WHERE enrollees_id = '$id' AND payment_status = 'paid') as total_paid,
                (SELECT cost FROM package_list WHERE id = (SELECT package_id FROM enrollees WHERE id = '$id')) as package_cost
        ")->fetch_assoc();
        
        $total_paid = $result['total_paid'] ? $result['total_paid'] : 0;
        $balance = $result['package_cost'] - $total_paid;
        
        return $this->jsonResponse([
            'status' => 'success',
            'total_paid' => $total_paid,
            'balance' => $balance
        ]);
        
    } catch (Exception $e) {
        return $this->jsonResponse(['status' => 'failed', 'message' => $e->getMessage()], 500);
    }
}

    
   public function delete_payment() {
    if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
        return $this->jsonResponse(['status' => 'failed', 'message' => 'Invalid request method'], 405);
    }

    if (empty($_POST['id'])) {
        return $this->jsonResponse(['status' => 'failed', 'message' => 'Payment ID is required'], 400);
    }

    $id = $this->conn->real_escape_string($_POST['id']);

    try {
        // Start transaction
        $this->conn->begin_transaction();

        // First get the payment details to update the enrollment status later
        $payment = $this->conn->query("SELECT enrollees_id, amount FROM payment_list WHERE id = '$id'")->fetch_assoc();
        
        if (!$payment) {
            $this->conn->rollback();
            return $this->jsonResponse(['status' => 'failed', 'message' => 'Payment record not found'], 404);
        }

        $enrollment_id = $payment['enrollees_id'];

        // Delete the payment
        $delete = $this->conn->query("DELETE FROM payment_list WHERE id = '$id'");
        if (!$delete) {
            throw new Exception($this->conn->error);
        }

        // Update the enrollment payment status
        $this->updatePaymentStatus($enrollment_id);

        $this->conn->commit();
        return $this->jsonResponse(['status' => 'success', 'message' => 'Payment deleted successfully']);
    } catch (Exception $e) {
        $this->conn->rollback();
        error_log('Delete Payment Error: ' . $e->getMessage());
        return $this->jsonResponse(['status' => 'failed', 'message' => 'Failed to delete payment: ' . $e->getMessage()], 500);
    }
}

private function updatePaymentStatus($enrollment_id) {
    // Get total paid amount
    $total_paid = $this->conn->query("SELECT SUM(amount) as total FROM payment_list WHERE enrollees_id = '$enrollment_id' AND payment_status = 'paid'")->fetch_assoc()['total'];
    $total_paid = $total_paid ? $total_paid : 0;
    
    // Get package cost
    $package = $this->conn->query("SELECT p.cost FROM enrollees e JOIN package_list p ON e.package_id = p.id WHERE e.id = '$enrollment_id'")->fetch_assoc();
    $cost = $package['cost'];
    
    // Determine new payment status
    $new_status = 'Pending';
    if ($total_paid >= $cost) {
        $new_status = 'Paid';
    } elseif ($total_paid > 0) {
        $new_status = 'Partially Paid';
    }
    
    // Update enrollment
    $this->conn->query("UPDATE enrollees SET payment_status = '$new_status' WHERE id = '$enrollment_id'");
}
public function update_payment() {
    if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
        return $this->jsonResponse(['status' => 'failed', 'message' => 'Invalid request method'], 405);
    }

    if (empty($_POST['id'])) {
        return $this->jsonResponse(['status' => 'failed', 'message' => 'Payment ID is required'], 400);
    }

    $required = ['amount_type', 'amount', 'payment_mode', 'payment_status'];
    foreach ($required as $field) {
        if (empty($_POST[$field])) {
            return $this->jsonResponse(['status' => 'failed', 'message' => "$field is required"], 400);
        }
    }

    $id = $this->conn->real_escape_string($_POST['id']);
    $amount_type = $this->conn->real_escape_string($_POST['amount_type']);
    $amount = floatval($_POST['amount']);
    $payment_mode = $this->conn->real_escape_string($_POST['payment_mode']);
    $payment_status = $this->conn->real_escape_string($_POST['payment_status']);
    $upi_reference = $this->conn->real_escape_string($_POST['upi_reference'] ?? '');

    if ($amount <= 0) {
        return $this->jsonResponse(['status' => 'failed', 'message' => 'Amount must be positive'], 400);
    }

    if (!in_array($amount_type, ['partial', 'full'])) {
        return $this->jsonResponse(['status' => 'failed', 'message' => 'Invalid amount type'], 400);
    }

    if (!in_array($payment_mode, ['cash', 'upi'])) {
        return $this->jsonResponse(['status' => 'failed', 'message' => 'Invalid payment mode'], 400);
    }

    if (!in_array($payment_status, ['pending', 'paid'])) {
        return $this->jsonResponse(['status' => 'failed', 'message' => 'Invalid payment status'], 400);
    }

    $this->conn->begin_transaction();

    try {
        // Update payment record
        $stmt = $this->conn->prepare("
            UPDATE payment_list SET 
                amount_type = ?,
                amount = ?,
                payment_mode = ?,
                payment_status = ?,
                upi_reference = ?
            WHERE id = ?
        ");

        if (!$stmt) {
            throw new Exception($this->conn->error);
        }

        $stmt->bind_param(
            "sdsssi",
            $amount_type,
            $amount,
            $payment_mode,
            $payment_status,
            $upi_reference,
            $id
        );

        if (!$stmt->execute()) {
            throw new Exception($stmt->error);
        }

        // Get enrollment ID to update its status
        $enrollment_id = $this->conn->query("SELECT enrollees_id FROM payment_list WHERE id = '$id'")->fetch_assoc()['enrollees_id'];
        
        // Update enrollment payment status
        $this->updatePaymentStatus($enrollment_id);

        $this->conn->commit();
        return $this->jsonResponse(['status' => 'success', 'message' => 'Payment updated successfully']);
    } catch (Exception $e) {
        $this->conn->rollback();
        error_log('Update Payment Error: ' . $e->getMessage());
        return $this->jsonResponse(['status' => 'failed', 'message' => 'Failed to update payment: ' . $e->getMessage()], 500);
    }
}


    public function delete() {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            return $this->jsonResponse(['status' => 'failed', 'message' => 'Invalid request method'], 405);
        }

        if (empty($_POST['id'])) {
            return $this->jsonResponse(['status' => 'failed', 'message' => 'Enrollment ID required'], 400);
        }

        $id = $this->conn->real_escape_string($_POST['id']);
        $this->conn->begin_transaction();

        try {
            // Get file path
            $file_result = $this->conn->query("SELECT copy_of_LLR FROM enrollees WHERE id = {$id}");
            if ($file_result && $file_result->num_rows > 0) {
                $file = $file_result->fetch_assoc()['copy_of_LLR'];
                if (!empty($file) && file_exists('../' . $file)) {
                    @unlink('../' . $file);
                }
            }

            // Delete record
            $stmt = $this->conn->prepare("DELETE FROM enrollees WHERE id = ?");
            if (!$stmt) {
                throw new Exception($this->conn->error);
            }
            
            $stmt->bind_param("i", $id);
            if (!$stmt->execute()) {
                throw new Exception($stmt->error);
            }

            $this->conn->commit();
            $stmt->close();
            return $this->jsonResponse(['status' => 'success', 'message' => 'Enrollment deleted']);
        } catch (Exception $e) {
            $this->conn->rollback();
            error_log('Delete Error: ' . $e->getMessage());
            return $this->jsonResponse(['status' => 'failed', 'message' => 'Failed to delete enrollment: ' . $e->getMessage()], 500);
        }
    }
}

// Controller
try {
    if (!isset($_GET['action'])) {
        throw new Exception('Missing action', 400);
    }

    $mod = new Enrollments();
    switch ($_GET['action']) {
    case 'save':
        $mod->save();
        break;
    case 'update_payment':
        $mod->update_payment();
        break;
    case 'delete':
        $mod->delete();
        break;
    case 'get_available_instructors':
        $mod->get_available_instructors();
        break;
    case 'delete_payment':
        $mod->delete_payment();
        break;
    case 'get_payment_summary':
        $mod->get_payment_summary();
        break;
    default:
        throw new Exception('Unknown action', 400);
}
} catch (Exception $e) {
    if (ob_get_length()) ob_clean();
    http_response_code($e->getCode() >= 400 ? $e->getCode() : 500);
    header('Content-Type: application/json');
    echo json_encode(['status' => 'failed', 'message' => $e->getMessage()]);
    exit;
}