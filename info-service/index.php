<?php
header('Content-Type: application/json');
echo json_encode([
    "service" => "Info Service",
    "status" => "Active",
    "timestamp" => date("Y-m-d H:i:s"),
    "container_id" => gethostname()
]);
?>