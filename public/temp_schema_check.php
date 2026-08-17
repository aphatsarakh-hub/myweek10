<?php
$mysqli = new mysqli('localhost', 'root', '', 'bambam_cat_hotel');
if ($mysqli->connect_error) {
    echo 'CONNECT FAIL: ' . $mysqli->connect_error;
    exit(1);
}
$res = $mysqli->query('SHOW TABLES LIKE "staff"');
if (!$res) {
    echo 'TABLE QUERY FAIL: ' . $mysqli->error;
    exit(1);
}
if ($res->num_rows === 0) {
    echo 'NO STAFF TABLE\n';
    exit(0);
}
$res2 = $mysqli->query('SHOW COLUMNS FROM staff');
if (!$res2) {
    echo 'COLUMNS QUERY FAIL: ' . $mysqli->error;
    exit(1);
}
while ($row = $res2->fetch_assoc()) {
    echo $row['Field'] . ' ' . $row['Type'] . ' ' . ($row['Null'] ?? '') . ' ' . ($row['Key'] ?? '') . ' ' . ($row['Extra'] ?? '') . "\n";
}
