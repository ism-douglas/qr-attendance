
<?php
require 'vendor/autoload.php';
require 'db.php';

use Endroid\QrCode\QrCode;
use Endroid\QrCode\Writer\PngWriter;

$stmt = $pdo->query("SELECT * FROM staff");

while ($row = $stmt->fetch()) {
    $qr = QrCode::create($row['staff_no']);
    $writer = new PngWriter();
    $result = $writer->write($qr);
    $path = __DIR__ . "/qrcodes/{$row['staff_no']}.png";
    $result->saveToFile($path);
    echo "Generated: $path<br>";
}
?>
