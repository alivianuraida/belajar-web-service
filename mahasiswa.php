<?php
header("Content-Type: application/json");
require "data.php";

$method = $_SERVER['REQUEST_METHOD'];
$id = $_GET['id'] ?? null;

// Fungsi helper untuk menyimpan perubahan array kembali ke file data.php
function saveStudentsToFile($students) {
    $content = "<?php\n\$students = " . var_export($students, true) . ";\n";
    file_put_contents(__DIR__ . "/data.php", $content);
}

switch ($method) {
    case 'GET':
        if ($id !== null) {
            $found = null;
            foreach ($students as $s) {
                if ($s['id'] == $id) {
                    $found = $s;
                    break;
                }
            }
            if ($found) {
                http_response_code(200);
                echo json_encode($found);
            } else {
                http_response_code(404);
                echo json_encode(["error" => "Mahasiswa tidak ditemukan"]);
            }
        } else {
            http_response_code(200);
            echo json_encode($students);
        }
        break;

    case 'POST':
        $input = json_decode(file_get_contents("php://input"), true);
        
        if (!isset($input['nim']) || !isset($input['name']) || !isset($input['major'])) {
            http_response_code(400);
            echo json_encode(["error" => "Field nim, name, dan major wajib diisi"]);
            exit;
        }

        $newId = count($students) > 0 ? max(array_column($students, 'id')) + 1 : 1;
        $newStudent = [
            "id"    => $newId,
            "nim"   => $input['nim'],
            "name"  => $input['name'],
            "major" => $input['major'],
        ];

        $students[] = $newStudent;
        saveStudentsToFile($students); // Simpan permanen

        http_response_code(201);
        header("Location: /mahasiswa.php?id=$newId");
        echo json_encode($newStudent);
        break;

    case 'PATCH':
    case 'PUT':
        if ($id === null) {
            http_response_code(400);
            echo json_encode(["error" => "ID mahasiswa diperlukan untuk update"]);
            exit;
        }

        $input = json_decode(file_get_contents("php://input"), true);
        $studentIndex = null;
        
        foreach ($students as $index => $student) {
            if ($student['id'] == $id) {
                $studentIndex = $index;
                break;
            }
        }

        if ($studentIndex === null) {
            http_response_code(404);
            echo json_encode(["error" => "Mahasiswa tidak ditemukan"]);
            exit;
        }

        $students[$studentIndex] = array_merge($students[$studentIndex], $input);
        saveStudentsToFile($students); // Simpan permanen
        
        http_response_code(200);
        echo json_encode($students[$studentIndex]);
        break;

    case 'DELETE':
        if ($id === null) {
            http_response_code(400);
            echo json_encode(["error" => "ID mahasiswa diperlukan untuk menghapus"]);
            exit;
        }

        $studentIndex = null;
        foreach ($students as $index => $student) {
            if ($student['id'] == $id) {
                $studentIndex = $index;
                break;
            }
        }

        if ($studentIndex === null) {
            http_response_code(404);
            echo json_encode(["error" => "Mahasiswa tidak ditemukan"]);
            exit;
        }

        unset($students[$studentIndex]);
        // Re-index array agar rapi
        $students = array_values($students);
        saveStudentsToFile($students); // Simpan permanen ke file data.php

        http_response_code(204); 
        break;

    default:
        http_response_code(405);
        echo json_encode(["error" => "Method tidak didukung"]);
        break;
}