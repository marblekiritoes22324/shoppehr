<?php
require 'db.php';
try {
  $pdo->exec("ALTER TABLE applications ADD COLUMN password VARCHAR(255) AFTER email");
  echo "Success";
} catch (Exception $e) {
  echo $e->getMessage();
}
?>
