<?php
header("Content-Type: application/json");
require "data_buku.php";

$method = $_SERVER['REQUEST_METHOD'];
$id = $_GET['id'] ?? null;

// Fungsi helper untuk menyimpan perubahan array kembali ke file data_buku.php secara permanen
function saveBooksToFile($books) {
    $content = "<?php\n\$books = " . var_export($books, true) . ";\n";
    file_put_contents(__DIR__ . "/data_buku.php", $content);
}

switch ($method) {
    case 'GET':
        if ($id !== null) {
            $found = null;
            foreach ($books as $b) {
                if ($b['id'] == $id) {
                    $found = $b;
                    break;
                }
            }
            if ($found) {
                http_response_code(200);
                echo json_encode($found);
            } else {
                http_response_code(404);
                echo json_encode(["error" => "Buku tidak ditemukan"]);
            }
        } else {
            http_response_code(200);
            echo json_encode($books);
        }
        break;

    case 'POST':
        $input = json_decode(file_get_contents("php://input"), true);
        
        // Validasi field wajib
        if (!isset($input['isbn']) || !isset($input['title']) || !isset($input['author'])) {
            http_response_code(400);
            echo json_encode(["error" => "Field isbn, title, dan author wajib diisi"]);
            exit;
        }

        $newId = count($books) > 0 ? max(array_column($books, 'id')) + 1 : 1;
        $newBook = [
            "id"     => $newId,
            "isbn"   => $input['isbn'],
            "title"  => $input['title'],
            "author" => $input['author'],
        ];

        $books[] = $newBook;
        saveBooksToFile($books);

        http_response_code(201);
        header("Location: /buku.php?id=$newId");
        echo json_encode($newBook);
        break;

    case 'PATCH':
    case 'PUT':
        if ($id === null) {
            http_response_code(400);
            echo json_encode(["error" => "ID buku diperlukan untuk update"]);
            exit;
        }

        $input = json_decode(file_get_contents("php://input"), true);
        $bookIndex = null;
        
        foreach ($books as $index => $book) {
            if ($book['id'] == $id) {
                $bookIndex = $index;
                break;
            }
        }

        if ($bookIndex === null) {
            http_response_code(404);
            echo json_encode(["error" => "Buku tidak ditemukan"]);
            exit;
        }

        $books[$bookIndex] = array_merge($books[$bookIndex], $input);
        saveBooksToFile($books);
        
        http_response_code(200);
        echo json_encode($books[$bookIndex]);
        break;

    case 'DELETE':
        if ($id === null) {
            http_response_code(400);
            echo json_encode(["error" => "ID buku diperlukan untuk menghapus"]);
            exit;
        }

        $bookIndex = null;
        foreach ($books as $index => $book) {
            if ($book['id'] == $id) {
                $bookIndex = $index;
                break;
            }
        }

        if ($bookIndex === null) {
            http_response_code(404);
            echo json_encode(["error" => "Buku tidak ditemukan"]);
            exit;
        }

        unset($books[$bookIndex]);
        $books = array_values($books);
        saveBooksToFile($books);

        http_response_code(204); 
        break;

    default:
        http_response_code(405);
        echo json_encode(["error" => "Method tidak didukung"]);
        break;
}