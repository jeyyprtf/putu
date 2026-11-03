<?php

include 'koneksi.php';

$url = $SUPABASE_URL . "/rest/v1/tugas?select=*";

$ch = curl_init($url);

curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);

curl_setopt($ch, CURLOPT_HTTPHEADER, [
    "apikey: " . $SUPABASE_KEY,
    "Authorization: Bearer " . $SUPABASE_KEY
]);

$response = curl_exec($ch);

$data = json_decode($response, true);

curl_close($ch);

echo "<pre>";
print_r($data);
echo "</pre>";

?>