<?php

require_once('./mysqldump.php');

$dbhost = 'localhost'; //Server Hostname
$dbuser = 'username'; //Server User Name
$dbpass = 'password'; //Server Password
$dbname = 'database_name'; //DB Name

$drop_table_if_exists = false;
$version = '1.0';

$backup = new MySQLDump();
$backup->droptableifexists = $drop_table_if_exists;

$backup->connect($dbhost, $dbuser, $dbpass, $dbname);

if (!$backup->connected) {
    die('Error: ' . $backup->mysql_error);
}

$backup->list_tables();
$table_count = count($backup->tables);
$backup_output = "";

for ($i = 0; $i < $table_count; $i++) {
    $table_name = $backup->tables[$i];
    $backup->dump_table($table_name);
    $backup_output .= $backup->output;
}

$backup_filename = date("d-m-Y-DayzDB") . '.sql';

$file_handle = fopen($backup_filename, 'w') or die("Error: Can't open file");
fwrite($file_handle, $backup_output);
fclose($file_handle);

$zip_filename = $backup_filename . '.zip';
create_zip(array($backup_filename), $zip_filename);

unlink($backup_filename);

function create_zip($files = array(), $destination = '', $overwrite = true) {
    $zip = new ZipArchive();
    if ($zip->open($destination, $overwrite ? ZIPARCHIVE::OVERWRITE : ZIPARCHIVE::CREATE) !== true) {
        return false;
    }

    foreach ($files as $file) {
        $zip->addFile($file, basename($file));
    }

    $zip->close();
    return file_exists($destination);
}
?>
