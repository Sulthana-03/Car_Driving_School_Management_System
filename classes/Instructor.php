<?php
require_once('../config.php');

class Instructor extends DBConnection {
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
        header('Content-Type: application/json'); // Set proper content type
        
        try {
            // Sanitize and prepare data
            $data = [];
            // Removed 'date_of_birth' from allowed_fields
            $allowed_fields = [
                'instructor_code', 'firstname', 'lastname', 'gender', 'email', 'phone', 
                'address', 'username', 'license_number', 'assigned_vehicle_id', 
                'joined_date', 'education', 'age', 'experience', 'status'
            ];

            foreach ($allowed_fields as $field) {
                if(isset($_POST[$field])) {
                    $data[$field] = $this->conn->real_escape_string(trim($_POST[$field]));
                }
            }

            // Handle password. Only hash if it's new or being changed.
            if(!empty($_POST['password'])) {
                $data['password'] = password_hash(trim($_POST['password']), PASSWORD_DEFAULT);
            }

            // Handle file uploads
            $file_fields = [
                'avatar' => 'avatar/',
                'license_copy' => 'license_copy/',
                'id_proof' => 'id_proof/'
            ];

            foreach ($file_fields as $field => $sub_folder) {
                $upload_dir = '../uploads/' . $sub_folder;
                
                // Create directory if it doesn't exist
                if (!is_dir($upload_dir)) {
                    mkdir($upload_dir, 0777, true);
                }

                if(isset($_FILES[$field]) && $_FILES[$field]['error'] == UPLOAD_ERR_OK) {
                    $file_name = time() . '_' . basename($_FILES[$field]['name']);
                    $target_file = $upload_dir . $file_name;
                    
                    $allowed_types = ['image/jpeg', 'image/png', 'image/gif', 'application/pdf']; // PDF for license/id proof
                    $max_size = 5 * 1024 * 1024; // 5MB
                    
                    if(in_array($_FILES[$field]['type'], $allowed_types) && $_FILES[$field]['size'] <= $max_size) {
                        if(move_uploaded_file($_FILES[$field]['tmp_name'], $target_file)) {
                            $data[$field] = 'uploads/' . $sub_folder . $file_name;
                            
                            // Delete old file if updating and a new file was uploaded
                            if (isset($_POST['id']) && !empty($_POST['id'])) {
                                $old_file_qry = $this->conn->query("SELECT `{$field}` FROM `instructor` WHERE id = '{$_POST['id']}'");
                                if ($old_file_qry->num_rows > 0) {
                                    $old_file = $old_file_qry->fetch_assoc()[$field];
                                    if (!empty($old_file) && file_exists('../' . $old_file)) {
                                        unlink('../' . $old_file);
                                    }
                                }
                            }
                        } else {
                            throw new Exception("Failed to upload " . $field . ".");
                        }
                    } else {
                        throw new Exception("Invalid file type or size for " . $field . ".");
                    }
                } else if (isset($_POST['id']) && isset($_POST['current_' . $field])) {
                    // Keep the existing file path if no new file is uploaded and it's an update
                    $data[$field] = $_POST['current_' . $field];
                }
            }

            // Determine if it's an INSERT or UPDATE
            if (empty($_POST['id'])) { // This is a new instructor
                // Generate a simple instructor code (you might want a more robust generation)
                $data['instructor_code'] = 'INST-' . (time() + rand(100, 999)); 

                $fields = implode(', ', array_keys($data));
                $values = "'" . implode("', '", array_values($data)) . "'";
                $sql = "INSERT INTO `instructor` ($fields) VALUES ($values)";
                $action_message = "New instructor added successfully.";
            } else { // This is an existing instructor (UPDATE)
                $id = $this->conn->real_escape_string($_POST['id']);
                $updates = [];
                foreach($data as $key => $value) {
                    $updates[] = "`$key` = '{$value}'";
                }
                if (empty($updates)) {
                    throw new Exception('No data to update.');
                }
                $sql = "UPDATE `instructor` SET ".implode(', ', $updates)." WHERE id = '{$id}'";
                $action_message = "Instructor details updated successfully.";
            }

            $save_result = $this->conn->query($sql);

            if(!$save_result) {
                throw new Exception('Database error: ' . $this->conn->error);
            }

            echo json_encode([
                'status' => 'success', 
                'message' => $action_message
            ]);
            exit();

        } catch (Exception $e) {
            echo json_encode([
                'status' => 'failed', 
                'message' => $e->getMessage()
            ]);
            exit();
        }
    }

    public function delete() {
        header('Content-Type: application/json'); // Set proper content type
        try {
            if(!isset($_POST['id']) || empty($_POST['id'])) {
                throw new Exception('Invalid instructor ID for deletion.');
            }

            $id = $this->conn->real_escape_string($_POST['id']);

            // Get file paths before deleting the record to unlink files
            $qry = $this->conn->query("SELECT avatar, license_copy, id_proof FROM `instructor` WHERE id = '{$id}'");
            if($qry->num_rows > 0) {
                $row = $qry->fetch_assoc();
                $files_to_delete = [];
                if(!empty($row['avatar']) && file_exists('../' . $row['avatar'])) {
                    $files_to_delete[] = '../' . $row['avatar'];
                }
                if(!empty($row['license_copy']) && file_exists('../' . $row['license_copy'])) {
                    $files_to_delete[] = '../' . $row['license_copy'];
                }
                if(!empty($row['id_proof']) && file_exists('../' . $row['id_proof'])) {
                    $files_to_delete[] = '../' . $row['id_proof'];
                }

                $delete_sql = "DELETE FROM `instructor` WHERE id = '{$id}'";
                $result = $this->conn->query($delete_sql);

                if(!$result) {
                    throw new Exception('Database error: '.$this->conn->error);
                }

                // Unlink files after successful database deletion
                foreach($files_to_delete as $file) {
                    unlink($file);
                }

                echo json_encode([
                    'status' => 'success', 
                    'message' => 'Instructor deleted successfully.'
                ]);
            } else {
                echo json_encode([
                    'status' => 'failed', 
                    'message' => 'Instructor not found.'
                ]);
            }
            exit();

        } catch (Exception $e) {
            echo json_encode([
                'status' => 'failed', 
                'message' => $e->getMessage()
            ]);
            exit();
        }
    }
}

$action = $_GET['f'] ?? '';

$instructor = new Instructor();

switch ($action) {
    case 'save':
        $instructor->save();
        break;
    case 'delete':
        $instructor->delete();
        break;
    default:
        echo json_encode([
            'status' => 'failed', 
            'message' => 'Invalid action'
        ]);
        break;
}