<?php
header('Content-Type: application/json');

// Set timezone to IST
date_default_timezone_set('Asia/Kolkata');

// Get request parameters
$game = $_GET['game'] ?? $_POST['game'] ?? '';
$user_key = $_GET['user_key'] ?? $_POST['user_key'] ?? '';
$serial = $_GET['serial'] ?? $_POST['serial'] ?? '';

// Store file
$store_file = 'keys.json';

// Load existing keys
$keys = [];
if (file_exists($store_file)) {
    $keys = json_decode(file_get_contents($store_file), true) ?? [];
}

// --- CHECK LOGIC ---

// 1. Check game
if (empty($game)) {
    echo json_encode([
        "status" => false,
        "reason" => "Game parameter required"
    ], JSON_PRETTY_PRINT);
    exit;
}

// 2. Check user_key
if (empty($user_key)) {
    echo json_encode([
        "status" => false,
        "reason" => "Key not found"
    ], JSON_PRETTY_PRINT);
    exit;
}

// 3. Check serial
if (empty($serial)) {
    echo json_encode([
        "status" => false,
        "reason" => "Serial required"
    ], JSON_PRETTY_PRINT);
    exit;
}

// 4. Valid keys (add your own here)
$valid_keys = [
    'hexmods',
    'HEX-CIPHER-N6F8JG',
    'PIYUSH-HACKS',
    'XITEXE-KEY',
    'DRAGON-MODZ'
];

// Also load from keys.json
foreach ($keys as $k => $data) {
    $valid_keys[] = $k;
}

// 5. Check if key is valid
if (!in_array($user_key, $valid_keys)) {
    echo json_encode([
        "status" => false,
        "reason" => "Key not found"
    ], JSON_PRETTY_PRINT);
    exit;
}

// 6. Check expiry & device limit (if from keys.json)
if (isset($keys[$user_key])) {
    $key_data = $keys[$user_key];
    
    // Check expiry
    if ($key_data['expiry_timestamp'] < time() * 1000) {
        echo json_encode([
            "status" => false,
            "reason" => "Key expired"
        ], JSON_PRETTY_PRINT);
        exit;
    }
    
    // Check device limit
    if ($key_data['devices_used'] >= $key_data['max_devices']) {
        // Check if same device
        if ($key_data['device_id'] !== $serial) {
            echo json_encode([
                "status" => false,
                "reason" => "Device limit reached"
            ], JSON_PRETTY_PRINT);
            exit;
        }
    }
    
    // Assign device if new
    if ($key_data['device_id'] === null) {
        $key_data['device_id'] = $serial;
        $key_data['devices_used'] = 1;
        $keys[$user_key] = $key_data;
        file_put_contents($store_file, json_encode($keys, JSON_PRETTY_PRINT));
    } elseif ($key_data['device_id'] !== $serial) {
        // Check if limit allows more
        if ($key_data['devices_used'] < $key_data['max_devices']) {
            $key_data['device_id'] = $serial;
            $key_data['devices_used'] = $key_data['devices_used'] + 1;
            $keys[$user_key] = $key_data;
            file_put_contents($store_file, json_encode($keys, JSON_PRETTY_PRINT));
        } else {
            echo json_encode([
                "status" => false,
                "reason" => "Device mismatch"
            ], JSON_PRETTY_PRINT);
            exit;
        }
    }
}

// --- SUCCESS RESPONSE ---

$response = [
    "status" => true,
    "reason" => "Login successful",
    "data" => [
        "token" => md5(uniqid() . $serial),
        "rng" => time(),
        "EXP" => "9999999999",
        "modname" => "PLASMA CHEATS",
        "mod_status" => "Online",
        "credit" => "@ARPANMODX",
        "ESP" => "1",
        "Item" => "1",
        "AIM" => "1",
        "SilentAim" => "1",
        "BulletTrack" => "1",
        "Floating" => "1",
        "Memory" => "1",
        "Setting" => "1"
    ]
];

echo json_encode($response, JSON_PRETTY_PRINT);
?>
