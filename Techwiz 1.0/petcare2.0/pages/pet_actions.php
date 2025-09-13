<?php
session_start();
if (!isset($_SESSION['role']) || $_SESSION['role'] != "owner") { exit("unauthorized"); }
include("../config/db.php");

$owner_id = $_SESSION['id'];
$action = $_POST['action'] ?? '';

if ($action == "add") {
    $name = mysqli_real_escape_string($conn, $_POST['name']);
    $species = mysqli_real_escape_string($conn, $_POST['species']);
    $breed = mysqli_real_escape_string($conn, $_POST['breed']);
    $age = (int) $_POST['age'];

    $sql = "INSERT INTO pets (owner_id, name, species, breed, age) VALUES (?,?,?,?,?)";
    $stmt = mysqli_prepare($conn, $sql);
    mysqli_stmt_bind_param($stmt, "isssi", $owner_id, $name, $species, $breed, $age);
    mysqli_stmt_execute($stmt);

    $id = mysqli_insert_id($conn);
    echo "<div class='pet-item mb-2' data-id='{$id}'>
        <strong>{$name}</strong> ({$species} - {$breed}, Age: {$age})
        <button class='btn btn-sm btn-warning edit-pet' data-id='{$id}'>Edit</button>
        <button class='btn btn-sm btn-danger delete-pet' data-id='{$id}'>Delete</button>
      </div>";
}

if ($action == "delete") {
    $pet_id = (int) $_POST['id'];
    $sql = "DELETE FROM pets WHERE pet_id=? AND owner_id=?";
    $stmt = mysqli_prepare($conn, $sql);
    mysqli_stmt_bind_param($stmt, "ii", $pet_id, $owner_id);
    mysqli_stmt_execute($stmt);
    echo "deleted";
}

if ($action == "edit") {
    $pet_id = (int) $_POST['id'];
    $name = mysqli_real_escape_string($conn, $_POST['name']);
    $species = mysqli_real_escape_string($conn, $_POST['species']);
    $breed = mysqli_real_escape_string($conn, $_POST['breed']);
    $age = (int) $_POST['age'];

    $sql = "UPDATE pets SET name=?, species=?, breed=?, age=? WHERE pet_id=? AND owner_id=?";
    $stmt = mysqli_prepare($conn, $sql);
    mysqli_stmt_bind_param($stmt, "sssiii", $name, $species, $breed, $age, $pet_id, $owner_id);
    mysqli_stmt_execute($stmt);

    echo "<strong>{$name}</strong> ({$species} - {$breed}, Age: {$age})
          <button class='btn btn-sm btn-warning edit-pet' data-id='{$pet_id}'>Edit</button>
          <button class='btn btn-sm btn-danger delete-pet' data-id='{$pet_id}'>Delete</button>";
}
