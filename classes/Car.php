<?php
require_once('../config.php');

class Car extends DBConnection {
    private $settings;

    public function __construct() {
        global $_settings;
        $this->settings = $_settings;
        parent::__construct();
    }

    function __destruct() {
        parent::__destruct();
    }

    public function save() {
        header('Content-Type: application/json');
        try {
            $data = [];
            $allowed_fields = [
                'car_code', 'car_model', 'manufacturer', 'year_of_purchase',
                'fuel_type', 'car_colour', 'status'
            ];

            foreach ($allowed_fields as $field) {
                if (isset($_POST[$field])) {
                    $data[$field] = $this->conn->real_escape_string(trim($_POST[$field]));
                }
            }

            // Handle file uploads
            $file_fields = [
                'car_image' => 'cars/',
                'rc_copy' => 'cars/',
                'insurance_copy' => 'cars/',
                'fitness_certificate_copy' => 'cars/'
            ];

            foreach ($file_fields as $field => $sub_folder) {
                $upload_dir = '../uploads/' . $sub_folder;

                if (!is_dir($upload_dir)) {
                    mkdir($upload_dir, 0777, true);
                }

                if (isset($_FILES[$field]) && $_FILES[$field]['error'] == UPLOAD_ERR_OK) {
                    $file_name = time() . '_' . basename($_FILES[$field]['name']);
                    $target_file = $upload_dir . $file_name;

                    $allowed_types = ['image/jpeg', 'image/png', 'image/gif', 'application/pdf'];
                    $max_size = 5 * 1024 * 1024; // 5MB

                    if (in_array($_FILES[$field]['type'], $allowed_types) && $_FILES[$field]['size'] <= $max_size) {
                        if (move_uploaded_file($_FILES[$field]['tmp_name'], $target_file)) {
                            $data[$field] = 'uploads/' . $sub_folder . $file_name;

                            // Delete old file
                            if (isset($_POST['id']) && !empty($_POST['id'])) {
                                $old_file_qry = $this->conn->query("SELECT `{$field}` FROM `cars` WHERE id = '{$_POST['id']}'");
                                if ($old_file_qry->num_rows > 0) {
                                    $old_file = $old_file_qry->fetch_assoc()[$field];
                                    if (!empty($old_file) && file_exists('../' . $old_file)) {
                                        unlink('../' . $old_file);
                                    }
                                }
                            }
                        } else {
                            throw new Exception("Failed to upload $field.");
                        }
                    } else {
                        throw new Exception("Invalid file type or size for $field.");
                    }
                } elseif (isset($_POST['id']) && isset($_POST['current_' . $field])) {
                    $data[$field] = $_POST['current_' . $field];
                }
            }

            // INSERT or UPDATE
            if (empty($_POST['id'])) {
                // Generate next car_code
                $code_res = $this->conn->query("SELECT MAX(CAST(SUBSTRING(car_code, 4) AS UNSIGNED)) as max_code FROM `cars`");
$next_code = $code_res->fetch_assoc()['max_code'] + 1;
$data['car_code'] = 'CAR' . str_pad($next_code, 3, '0', STR_PAD_LEFT);
                
                if (!isset($data['status'])) {
    $data['status'] = 1; // default to Active
}


                $fields = implode(', ', array_keys($data));
                $values = "'" . implode("', '", array_values($data)) . "'";
                $sql = "INSERT INTO `cars` ($fields) VALUES ($values)";
                $msg = "New car added successfully.";
            } else {
                $id = $this->conn->real_escape_string($_POST['id']);
                $updates = [];
                foreach ($data as $key => $value) {
                    $updates[] = "`$key` = '{$value}'";
                }
                if (empty($updates)) throw new Exception("No fields to update.");

                $sql = "UPDATE `cars` SET " . implode(', ', $updates) . " WHERE id = '{$id}'";
                $msg = "Car details updated successfully.";
            }

            $save = $this->conn->query($sql);
            if (!$save) throw new Exception("Database error: " . $this->conn->error);

            echo json_encode(['status' => 'success', 'message' => $msg]);
            exit;

        } catch (Exception $e) {
            echo json_encode(['status' => 'failed', 'message' => $e->getMessage()]);
            exit;
        }
    }

    public function delete() {
        header('Content-Type: application/json');
        try {
            if (!isset($_POST['id']) || empty($_POST['id'])) {
                throw new Exception("Invalid car ID.");
            }

            $id = $this->conn->real_escape_string($_POST['id']);

            $get = $this->conn->query("SELECT car_image, rc_copy, insurance_copy, fitness_certificate_copy FROM `cars` WHERE id = '{$id}'");
            if ($get->num_rows > 0) {
                $row = $get->fetch_assoc();
                $delete = $this->conn->query("DELETE FROM `cars` WHERE id = '{$id}'");

                if (!$delete) throw new Exception("DB error: " . $this->conn->error);

                foreach ($row as $file) {
                    if (!empty($file) && file_exists('../' . $file)) {
                        unlink('../' . $file);
                    }
                }

                echo json_encode(['status' => 'success', 'message' => 'Car deleted successfully.']);
            } else {
                throw new Exception("Car not found.");
            }
            exit;

        } catch (Exception $e) {
            echo json_encode(['status' => 'failed', 'message' => $e->getMessage()]);
            exit;
        }
    }
}

$action = $_GET['f'] ?? '';
$car = new Car();

switch ($action) {
    case 'save':
        $car->save();
        break;
    case 'delete':
        $car->delete();
        break;
    default:
        echo json_encode(['status' => 'failed', 'message' => 'Invalid action']);
        break;
}
