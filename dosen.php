<?php
header("Content-Type: application/json");
require __DIR__ . "/data-dosen.php";

function saveDosen(string $filePath, array $dosen): bool
{
    $content = "<?php\n\$dosen = " . var_export($dosen, true) . ";\n";
    return file_put_contents($filePath, $content, LOCK_EX) !== false;
}

$method = $_SERVER['REQUEST_METHOD'];
$dataFile = __DIR__ . "/data-dosen.php";

if (!isset($dosen) || !is_array($dosen)) {
    http_response_code(500);
    echo json_encode(["error" => "Data dosen tidak tersedia"]);
    exit;
}

if ($method === "GET") {
    if (isset($_GET['id'])) {
        $id = filter_input(INPUT_GET, 'id', FILTER_VALIDATE_INT);
        if ($id === false || $id === null) {
            http_response_code(400);
            echo json_encode(["error" => "Parameter id harus berupa angka"]);
            exit;
        }

        foreach ($dosen as $item) {
            if ($item['id'] === $id) {
                http_response_code(200);
                echo json_encode($item);
                exit;
            }
        }

        http_response_code(404);
        echo json_encode(["error" => "Dosen dengan ID $id tidak ditemukan"]);
        exit;
    }

    http_response_code(200);
    echo json_encode($dosen);
    exit;
}

if ($method === "POST") {
    $input = json_decode(file_get_contents("php://input"), true);
    if (!is_array($input) || json_last_error() !== JSON_ERROR_NONE) {
        http_response_code(400);
        echo json_encode(["error" => "Body harus berupa JSON yang valid"]);
        exit;
    }

    $requiredFields = ['nidn', 'name', 'email', 'department'];
    foreach ($requiredFields as $field) {
        if (!isset($input[$field]) || $input[$field] === '') {
            http_response_code(400);
            echo json_encode(["error" => "Field nidn, name, email, dan department wajib diisi"]);
            exit;
        }
    }

    $newId = count($dosen) > 0 ? max(array_column($dosen, 'id')) + 1 : 1;
    $newDosen = [
        "id" => $newId,
        "nidn" => $input['nidn'],
        "name" => $input['name'],
        "email" => $input['email'],
        "department" => $input['department']
    ];
    $dosen[] = $newDosen;

    if (!saveDosen($dataFile, $dosen)) {
        http_response_code(500);
        echo json_encode(["error" => "Data dosen gagal disimpan"]);
        exit;
    }

    http_response_code(201);
    header("Location: /dosen.php?id=$newId");
    echo json_encode($newDosen);
    exit;
}

if ($method === "PUT") {
    $id = filter_input(INPUT_GET, 'id', FILTER_VALIDATE_INT);
    if ($id === false || $id === null) {
        http_response_code(400);
        echo json_encode(["error" => "Parameter id harus berupa angka"]);
        exit;
    }

    $input = json_decode(file_get_contents("php://input"), true);
    if (!is_array($input) || json_last_error() !== JSON_ERROR_NONE) {
        http_response_code(400);
        echo json_encode(["error" => "Body harus berupa JSON yang valid"]);
        exit;
    }

    $requiredFields = ['nidn', 'name', 'email', 'department'];
    foreach ($requiredFields as $field) {
        if (!isset($input[$field]) || $input[$field] === '') {
            http_response_code(400);
            echo json_encode(["error" => "Field nidn, name, email, dan department wajib diisi"]);
            exit;
        }
    }

    $updatedDosen = null;
    foreach ($dosen as $index => $item) {
        if ($item['id'] === $id) {
            $updatedDosen = [
                "id" => $id,
                "nidn" => $input['nidn'],
                "name" => $input['name'],
                "email" => $input['email'],
                "department" => $input['department']
            ];
            $dosen[$index] = $updatedDosen;
            break;
        }
    }

    if ($updatedDosen === null) {
        http_response_code(404);
        echo json_encode(["error" => "Dosen dengan ID $id tidak ditemukan"]);
        exit;
    }

    if (!saveDosen($dataFile, $dosen)) {
        http_response_code(500);
        echo json_encode(["error" => "Data dosen gagal disimpan"]);
        exit;
    }

    http_response_code(200);
    echo json_encode($updatedDosen);
    exit;
}

if ($method === "DELETE") {
    $id = filter_input(INPUT_GET, 'id', FILTER_VALIDATE_INT);
    if ($id === false || $id === null) {
        http_response_code(400);
        echo json_encode(["error" => "Parameter id harus berupa angka"]);
        exit;
    }

    $deletedDosen = null;
    foreach ($dosen as $index => $item) {
        if ($item['id'] === $id) {
            $deletedDosen = $item;
            unset($dosen[$index]);
            $dosen = array_values($dosen);
            break;
        }
    }

    if ($deletedDosen === null) {
        http_response_code(404);
        echo json_encode(["error" => "Dosen dengan ID $id tidak ditemukan"]);
        exit;
    }

    if (!saveDosen($dataFile, $dosen)) {
        http_response_code(500);
        echo json_encode(["error" => "Data dosen gagal disimpan"]);
        exit;
    }

    http_response_code(200);
    echo json_encode($deletedDosen);
    exit;
}

http_response_code(405);
header("Allow: GET, POST, PUT, DELETE");
echo json_encode(["error" => "Method HTTP tidak didukung"]);
