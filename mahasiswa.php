<?php
header("Content-Type: application/json");
require __DIR__ . "/data.php";
$method = $_SERVER['REQUEST_METHOD'];
if ($method === "GET") {
 http_response_code(200);
 echo json_encode($students);
 exit;
}
if ($method === "POST") {
 $input = json_decode(file_get_contents("php://input"), true);
 if (!isset($input['nim']) || !isset($input['name']) ||
!isset($input['major'])) {
 http_response_code(400);
 echo json_encode(["error" => "Field nim, name, dan major wajib diisi"]);
 exit;
 }
 $newId = count($students) + 1;
 $newStudent = [
 "id" => $newId,
 "nim" => $input['nim'],
 "name" => $input['name'],
 "major" => $input['major'],
 ];
 http_response_code(201);
 header("Location: /mahasiswa.php?id=$newId");
 echo json_encode($newStudent);
 exit;
 }
if ($method === "PUT") {
 $id = filter_input(INPUT_GET, 'id', FILTER_VALIDATE_INT);
 $studentIndex = null;
 foreach ($students as $index => $student) {
	if ($student['id'] === $id) {
	 $studentIndex = $index;
	 break;
	}
 }

 if ($studentIndex === null) {
	http_response_code(404);
	echo json_encode(["error" => "Mahasiswa tidak ditemukan"]);
	exit;
 }

 $input = json_decode(file_get_contents("php://input"), true);
 if (!isset($input['nim']) || !isset($input['name']) ||
!isset($input['major'])) {
	http_response_code(400);
	echo json_encode(["error" => "Field nim, name, dan major wajib diisi"]);
	exit;
 }

 $students[$studentIndex] = [
	"id" => $id,
	"nim" => $input['nim'],
	"name" => $input['name'],
	"major" => $input['major'],
 ];
 http_response_code(200);
 echo json_encode($students[$studentIndex]);
 exit;
}
if ($method === "DELETE") {
 $id = filter_input(INPUT_GET, 'id', FILTER_VALIDATE_INT);
 $studentIndex = null;
 foreach ($students as $index => $student) {
  if ($student['id'] === $id) {
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
 http_response_code(204);
 exit;
}
http_response_code(405);
echo json_encode(["error" => "Method tidak didukung"]); 

