<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);

require_once('../config.php');
Class Master extends DBConnection {
    private $settings;
    public function __construct(){
        global $_settings;
        $this->settings = $_settings;
        parent::__construct();
    }
    public function __destruct(){
        parent::__destruct();
    }

    function capture_err(){
        if(!$this->conn->error)
            return false;
        else{
            $resp['status'] = 'failed';
            $resp['error'] = $this->conn->error;
            return json_encode($resp);
            exit;
        }
    }

    function save_package(){
        extract($_POST);

        if(empty($name) || empty($description) || empty($training_duration)){
            $resp['status'] = 'failed';
            $resp['msg'] = 'Required fields are missing (name, description, training_duration)';
            return json_encode($resp);
        }

        // Check if package name already exists (excluding current id if editing)
        $check_sql = "SELECT * FROM `package_list` WHERE `name` = ? " . (!empty($id) ? "AND id != ?" : "");
        $check_stmt = $this->conn->prepare($check_sql);
        if(!empty($id)){
            $check_stmt->bind_param('si', $name, $id);
        } else {
            $check_stmt->bind_param('s', $name);
        }
        $check_stmt->execute();
        $check_result = $check_stmt->get_result();
        if($check_result->num_rows > 0){
            $resp['status'] = 'failed';
            $resp['msg'] = "Package Name Already Exists.";
            return json_encode($resp);
        }

        if(empty($id)){
           $partial1 = $partial2 = $cost / 2;
$stmt = $this->conn->prepare("INSERT INTO `package_list` (`name`, `description`, `training_duration`, `cost`, `partial1`, `partial2`, `status`) VALUES (?, ?, ?, ?, ?, ?, ?)");
$stmt->bind_param("sssdddi", $name, $description, $training_duration, $cost, $partial1, $partial2, $status);

        } else {
            $partial1 = $partial2 = $cost / 2;
$stmt = $this->conn->prepare("UPDATE `package_list` SET `name` = ?, `description` = ?, `training_duration` = ?, `cost` = ?, `partial1` = ?, `partial2` = ?, `status` = ? WHERE `id` = ?");
$stmt->bind_param("sssdddis", $name, $description, $training_duration, $cost, $partial1, $partial2, $status, $id);

        }

        if($stmt->execute()){
            $resp['status'] = 'success';
            if(empty($id))
                $resp['msg'] = "Package details successfully added.";
            else
                $resp['msg'] = "Package details have been updated successfully.";
        } else {
            $resp['status'] = 'failed';
            $resp['msg'] = "An error occurred.";
            $resp['err'] = $stmt->error;
        }

        $stmt->close();

        if($resp['status'] == 'success')
            $this->settings->set_flashdata('success', $resp['msg']);
        return json_encode($resp);
    }

    function delete_package(){
        extract($_POST);
        $del = $this->conn->query("DELETE FROM `package_list` WHERE id = '{$id}'");
        if($del){
            $resp['status'] = 'success';
            $this->settings->set_flashdata('success', "Package has been deleted successfully.");
        } else {
            $resp['status'] = 'failed';
            $resp['error'] = $this->conn->error;
        }
        return json_encode($resp);
    }

    function save_enrollment(){
        if(empty($_POST['id'])){
            $pref = date("Ym");
            $code = sprintf("%'.04d",1);
            while(true){
                $check = $this->conn->query("SELECT * FROM `enrollee_list` WHERE reg_no = '{$pref}{$code}'")->num_rows;
                if($check > 0){
                    $code = sprintf("%'.04d",abs($code)+1);
                } else {
                    break;
                }
            }
            $_POST['reg_no'] = $pref.$code;
        }
        extract($_POST);
        $data = "";
        foreach($_POST as $k =>$v){
            if(!in_array($k, array('id')) && !is_array($_POST[$k])){
                if(!is_numeric($v))
                    $v = $this->conn->real_escape_string($v);
                if(!empty($data)) $data .=",";
                $data .= " `{$k}`='{$v}' ";
            }
        }
        if(empty($id)){
            $sql = "INSERT INTO `enrollee_list` SET {$data} ";
        } else {
            $sql = "UPDATE `enrollee_list` SET {$data} WHERE id = '{$id}' ";
        }
        $save = $this->conn->query($sql);
        if($save){
            $aid = !empty($id) ? $id : $this->conn->insert_id;
            $resp['status'] = 'success';
            if(empty($id)){
                $resp['reg_no'] = $reg_no;
            }
            if(empty($id))
                $resp['msg'] = "Enrollment was successfully submitted";
            else
                $resp['msg'] = "Enrollee details were updated successfully.";
        } else {
            $resp['status'] = 'failed';
            $resp['msg'] = "An error occurred.";
            $resp['err'] = $this->conn->error . " [{$sql}]";
        }
        if($resp['status'] == 'success')
            $this->settings->set_flashdata('success', $resp['msg']);
        return json_encode($resp);
    }

    function delete_enrollment(){
        extract($_POST);
        $del = $this->conn->query("DELETE FROM `enrollee_list` WHERE id = '{$id}'");
        if($del){
            $resp['status'] = 'success';
            $this->settings->set_flashdata('success', "Enrollee record has been deleted successfully.");
        } else {
            $resp['status'] = 'failed';
            $resp['error'] = $this->conn->error;
        }
        return json_encode($resp);
    }

    function update_status(){
        extract($_POST);
        $update = $this->conn->query("UPDATE `enrollee_list` SET `status` = '$status' WHERE id = '{$id}'");
        if($update){
            $resp['status'] = 'success';
            $this->settings->set_flashdata('success', "Enrollment details have been successfully updated.");
        } else {
            $resp['status'] = 'failed';
            $resp['error'] = $this->conn->error;
        }
        return json_encode($resp);
    }

    function save_payment(){
        extract($_POST);
        $data = "";
        foreach($_POST as $k =>$v){
            if(!in_array($k, array('id'))){
                if(!is_numeric($v))
                    $v = $this->conn->real_escape_string($v);
                if(!empty($data)) $data .=",";
                $data .= " `{$k}`='{$v}' ";
            }
        }
        $sql = "INSERT INTO `payment_list` SET {$data} ";
        $save = $this->conn->query($sql);
        if($save){
            $rid = !empty($id) ? $id : $this->conn->insert_id;
            $resp['status'] = 'success';
            $resp['msg'] = "Payment details were added successfully.";
        } else {
            $resp['status'] = 'failed';
            $resp['msg'] = "An error occurred.";
            $resp['err'] = $this->conn->error . " [{$sql}]";
        }
        if($resp['status'] == 'success')
            $this->settings->set_flashdata('success', $resp['msg']);
        return json_encode($resp);
    }

    function delete_payment(){
        extract($_POST);
        $del = $this->conn->query("DELETE FROM `payment_list` WHERE id = '{$id}'");
        if($del){
            $resp['status'] = 'success';
            $this->settings->set_flashdata('success', "Payment has been deleted successfully.");
        } else {
            $resp['status'] = 'failed';
            $resp['error'] = $this->conn->error;
        }
        return json_encode($resp);
    }
}

$Master = new Master();
$action = !isset($_GET['f']) ? 'none' : strtolower($_GET['f']);
$sysset = new SystemSettings();
switch ($action) {
    case 'save_package':
        echo $Master->save_package();
        break;
    case 'delete_package':
        echo $Master->delete_package();
        break;
    case 'save_enrollment':
        echo $Master->save_enrollment();
        break;
    case 'delete_enrollment':
        echo $Master->delete_enrollment();
        break;
    case 'update_status':
        echo $Master->update_status();
        break;
    case 'save_payment':
        echo $Master->save_payment();
        break;
    case 'delete_payment':
        echo $Master->delete_payment();
        break;
    default:
        // echo $sysset->index();
        break;
}
