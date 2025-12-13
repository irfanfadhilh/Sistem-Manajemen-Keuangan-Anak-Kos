<?php
session_start(); 
header("Content-Type: application/json");
header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Methods: GET, POST, DELETE, OPTIONS");

include 'koneksi.php';


if (!isset($_SESSION['user_id'])) {
    echo json_encode(["status" => "error", "message" => "Unauthorized"]);
    exit();
}

$user_id = $_SESSION['user_id']; 
$method = $_SERVER['REQUEST_METHOD'];

// GET: Ambil Data
if ($method == 'GET') {
    $sql = "SELECT * FROM transactions WHERE user_id = '$user_id' ORDER BY date DESC, id DESC";
    $result = mysqli_query($koneksi, $sql);
    
    $transactions = [];
    $income = 0;
    $expense = 0;

    while ($row = mysqli_fetch_assoc($result)) {
        $row['amount'] = (float) $row['amount'];
        $transactions[] = $row;

        if ($row['type'] == 'in') {
            $income += $row['amount'];
        } else {
            $expense += $row['amount'];
        }
    }

    $balance = $income - $expense;

    echo json_encode([
        "transactions" => $transactions,
        "summary" => [
            "income" => $income,
            "expense" => $expense,
            "balance" => $balance,
            "user" => $_SESSION['username'] 
        ]
    ]);
}

// POST: Simpan Data Baru 
if ($method == 'POST') {
    $input = json_decode(file_get_contents('php://input'), true);
    
    $description = $input['description'];
    $amount = $input['amount'];
    $type = $input['type'];
    $date = $input['date'];

    $sql = "INSERT INTO transactions (user_id, description, amount, type, date) VALUES ('$user_id', '$description', '$amount', '$type', '$date')";
    
    if (mysqli_query($koneksi, $sql)) {
        echo json_encode(["status" => "success", "message" => "Data berhasil disimpan"]);
    } else {
        echo json_encode(["status" => "error", "message" => "Gagal menyimpan"]);
    }
}

// DELETE: Hapus Data 
if ($method == 'DELETE') {
    $id = isset($_GET['id']) ? $_GET['id'] : null;

    if ($id) {
        $sql = "DELETE FROM transactions WHERE id = $id AND user_id = '$user_id'";
        
        if (mysqli_query($koneksi, $sql)) {
            echo json_encode(["status" => "success", "message" => "Data berhasil dihapus"]);
        } else {
            echo json_encode(["status" => "error", "message" => "Gagal menghapus data"]);
        }
    } else {
        echo json_encode(["status" => "error", "message" => "ID tidak ditemukan"]);
    }
}

// PUT: Update Data 
if ($method == 'PUT') {
    $input = json_decode(file_get_contents('php://input'), true);
    
    $id = $input['id'];
    $description = $input['description'];
    $amount = $input['amount'];
    $type = $input['type'];
    $date = $input['date'];

    $sql = "UPDATE transactions 
            SET description='$description', amount='$amount', type='$type', date='$date' 
            WHERE id=$id AND user_id='$user_id'";
    
    if (mysqli_query($koneksi, $sql)) {
        echo json_encode(["status" => "success", "message" => "Data berhasil diupdate"]);
    } else {
        echo json_encode(["status" => "error", "message" => "Gagal update data"]);
    }
}
?>