<?php
require_once('../config.php');

if ($_GET['f'] == 'save_admin') {
    $id = $_POST['id'];
    $firstname = $_POST['firstname'];
    $lastname = $_POST['lastname'];
    $username = $_POST['username'];
    $password = !empty($_POST['password']) ? md5($_POST['password']) : null;

    $data = [
        "firstname" => $firstname,
        "lastname" => $lastname,
        "username" => $username
    ];

    if ($password !== null) {
        $data["password"] = $password;
    }

    // Prepare SQL
    $update_parts = [];
    foreach ($data as $key => $val) {
        $update_parts[] = "`$key` = '" . $conn->real_escape_string($val) . "'";
    }
    $update_sql = implode(", ", $update_parts);
    $conn->query("UPDATE admin SET $update_sql WHERE id = '{$id}'");

    // Handle avatar upload
    if (!empty($_FILES['avatar']['tmp_name'])) {
        $upload_path = 'uploads/Adminprofile/';
        $extension = pathinfo($_FILES['avatar']['name'], PATHINFO_EXTENSION);
        $filename = 'avatar-admin-' . $id . '.' . $extension;
        $full_path = base_app . $upload_path . $filename;

        if (!is_dir(base_app . $upload_path)) {
            mkdir(base_app . $upload_path, 0777, true);
        }

        if (move_uploaded_file($_FILES['avatar']['tmp_name'], $full_path)) {
            $conn->query("UPDATE admin SET avatar = '{$upload_path}{$filename}' WHERE id = '{$id}'");
            $_SESSION['userdata']['avatar'] = $upload_path . $filename;
        }
    }

    // Refresh session data
    $qry = $conn->query("SELECT * FROM admin WHERE id = '{$id}'");
    foreach ($qry->fetch_array() as $k => $v) {
        if (!is_numeric($k)) {
            $_SESSION['userdata'][$k] = $v;
        }
    }

    echo "<script>alert('Profile updated successfully'); location.href = '../admin/index.php';</script>";
}
