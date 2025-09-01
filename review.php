<?php
// Simple JSON-based inbox for reviews.
// Save messages to reviews.json and serve them back.
// Place this file beside index.html. Ensure the directory is writable.

header('Access-Control-Allow-Origin: *'); // same-site usage; adjust if needed
header('Content-Type: application/json; charset=utf-8');

$FILE = __DIR__ . '/reviews.json';

function load_all($file){
  if(!file_exists($file)){ return []; }
  $raw = file_get_contents($file);
  $data = json_decode($raw, true);
  return is_array($data) ? $data : [];
}
function save_all($file, $arr){
  // Write atomically
  $tmp = $file . '.tmp';
  file_put_contents($tmp, json_encode($arr, JSON_UNESCAPED_UNICODE|JSON_PRETTY_PRINT));
  rename($tmp, $file);
}

if($_SERVER['REQUEST_METHOD'] === 'POST'){
  // Basic validation & sanitization
  $name = isset($_POST['name']) ? trim($_POST['name']) : '';
  $msg  = isset($_POST['message']) ? trim($_POST['message']) : '';
  if($msg === '' || mb_strlen($msg) > 500){
    echo json_encode(['ok'=>false, 'error'=>'Message required (max 500 chars).']);
    exit;
  }
  if(mb_strlen($name) > 60){ $name = mb_substr($name, 0, 60); }

  // Strip tags (defense)
  $name = strip_tags($name);
  $msg  = strip_tags($msg);

  // Build entry
  $entry = [
    'id' => bin2hex(random_bytes(6)),
    'name' => $name,
    'message' => $msg,
    'ts' => round(microtime(true)*1000) // ms
  ];

  $all = load_all($FILE);
  // newest first
  array_unshift($all, $entry);

  // Ensure file/dir writable; create if missing
  if(!is_dir(dirname($FILE))){
    @mkdir(dirname($FILE), 0775, true);
  }
  save_all($FILE, $all);

  echo json_encode(['ok'=>true, 'entry'=>$entry]);
  exit;
}

// GET -> list messages
$all = load_all($FILE);
echo json_encode($all);
