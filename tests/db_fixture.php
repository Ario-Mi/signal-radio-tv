<?php
if (PHP_SAPI !== 'cli') { http_response_code(404); exit; }
require_once dirname(__DIR__) . '/lib/config.php';
$name=$argv[2]??'';
if (!preg_match('/^signal_test_[a-f0-9]{16}$/D',$name)) { throw new InvalidArgumentException('Invalid database name'); }
$pdo=new PDO('mysql:host='.signal_config('DB_HOST','localhost').';port='.signal_config('DB_PORT','3306').';charset=utf8mb4',signal_config('DB_USER','root'),signal_config('DB_PASSWORD'),[PDO::ATTR_ERRMODE=>PDO::ERRMODE_EXCEPTION]);
if (($argv[1]??'')==='create') {
 $pdo->exec("CREATE DATABASE `$name` CHARACTER SET utf8mb4");
 foreach (['schema.sql','seed.sql'] as $file) {
  $sql=file_get_contents(dirname(__DIR__).'/database/'.$file);
  $sql=preg_replace('/^--.*$/m','',$sql);
  $sql=str_replace('myradio',$name,$sql);
  foreach(preg_split('/;\s*(?:\r?\n|$)/',$sql) as $statement) {
   if(trim($statement)!=='') { $pdo->query($statement)->closeCursor(); }
  }
 }
 echo 'Created';
} elseif (($argv[1]??'')==='drop') {
 $pdo->exec("DROP DATABASE `$name`"); echo 'Removed';
} elseif (($argv[1]??'')==='snapshot') {
 $pdo->exec("USE `$name`"); $out=[];
 foreach(['genres','stations','tv_stations'] as $table) { $out[$table]=$pdo->query("SELECT * FROM `$table` ORDER BY id")->fetchAll(PDO::FETCH_ASSOC); }
 echo json_encode($out);
} else { throw new InvalidArgumentException('Invalid fixture action'); }
