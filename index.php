<?php
// --- CONFIGURATION ---
$PANEL_URL = "157.230.246.36:1198/wFG4RQySEiWtLUG/xui/inbounds"; // Panel Port ပါထည့်ပါ
$USERNAME  = "45nMOrB8wB";
$PASSWORD  = "7DjIrvjWP2";

// 1. Login Function
function login($url, $user, $pass) {
    $ch = curl_init();
    curl_setopt($ch, CURLOPT_URL, $url . "/login");
    curl_setopt($ch, CURLOPT_POST, true);
    curl_setopt($ch, CURLOPT_POSTFIELDS, http_build_query(['username' => $user, 'password' => $pass]));
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_COOKIEJAR, __DIR__ . '/cookie.txt'); // Cookie သိမ်းရန်
    curl_exec($ch);
    curl_close($ch);
}

// 2. Get Data Function
function getInbounds($url) {
    $ch = curl_init();
    curl_setopt($ch, CURLOPT_URL, $url . "/xui/API/inbounds/list");
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_COOKIEFILE, __DIR__ . '/cookie.txt');
    $response = curl_exec($ch);
    curl_close($ch);
    return json_decode($response, true);
}

// ပထမဦးဆုံး Login ဝင်မယ်
login($PANEL_URL, $USERNAME, $PASSWORD);

// Data တွေ ဆွဲယူမယ်
$data = getInbounds($PANEL_URL);
$inbounds = $data['obj'] ?? [];
?>

<!DOCTYPE html>
<html lang="my">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>X-UI Free Vless Keys</title>
    <style>
        body { font-family: 'Tahoma', sans-serif; background: #0f172a; color: #e2e8f0; padding: 20px; }
        .container { max-width: 800px; margin: auto; }
        .card { background: #1e293b; border: 1px solid #334155; border-radius: 12px; padding: 20px; margin-bottom: 15px; }
        .header { display: flex; justify-content: space-between; align-items: center; border-bottom: 1px solid #334155; padding-bottom: 10px; }
        .data-info { display: flex; gap: 20px; margin-top: 15px; }
        .data-box { background: #0f172a; padding: 10px; border-radius: 8px; flex: 1; text-align: center; }
        .label { font-size: 12px; color: #94a3b8; display: block; }
        .value { font-weight: bold; color: #38bdf8; font-size: 1.1rem; }
        .vless-key { background: #000; color: #94a3b8; padding: 10px; border-radius: 6px; font-size: 11px; word-break: break-all; margin-top: 15px; border: 1px dashed #3a7bd5; }
        .copy-btn { width: 100%; background: #3a7bd5; color: white; border: none; padding: 10px; border-radius: 6px; margin-top: 10px; cursor: pointer; }
    </style>
</head>
<body>

<div class="container">
    <h1 style="text-align:center; color:#38bdf8;">VLESS Keys Live Status</h1>
    
    <?php if(empty($inbounds)): ?>
        <p style="text-align:center;">No Inbounds Found or Login Failed.</p>
    <?php endif; ?>

    <?php foreach($inbounds as $item): 
        // Data Calculation
        $total = $item['total'] > 0 ? round($item['total'] / (1024**3), 2) . " GB" : "Unlimited";
        $used = round(($item['up'] + $item['down']) / (1024**3), 2);
        $remaining = ($item['total'] > 0) ? round(($item['total'] - ($item['up'] + $item['down'])) / (1024**3), 2) . " GB" : "Unlimited";
        
        // Expiry Date
        $expiry = ($item['expiryTime'] > 0) ? date("Y-m-d", $item['expiryTime']/1000) : "No Expiry";
        
        // Config JSON ထဲက Client ID ကိုထုတ်ယူခြင်း
        $settings = json_decode($item['settings'], true);
        $client_id = $settings['clients'][0]['id'] ?? '';
        $streamSettings = json_decode($item['streamSettings'], true);
        $net = $streamSettings['network'] ?? 'tcp';
        
        // Vless Link တည်ဆောက်ခြင်း (နမူနာ)
        $vless_link = "vless://$client_id@YOUR_DOMAIN:{$item['port']}?type=$net&security=none#{$item['remark']}";
    ?>

    <div class="card">
        <div class="header">
            <span style="font-size: 1.2rem; font-weight: bold;">📍 <?php echo $item['remark']; ?></span>
            <span style="color: #4ade80;">● Online</span>
        </div>

        <div class="data-info">
            <div class="data-box">
                <span class="label">ကျန်ရှိသော Data</span>
                <span class="value"><?php echo $remaining; ?></span>
            </div>
            <div class="data-box">
                <span class="label">ကုန်ဆုံးမည့်ရက်</span>
                <span class="value"><?php echo $expiry; ?></span>
            </div>
        </div>

        <div class="vless-key" id="key-<?php echo $item['id']; ?>"><?php echo $vless_link; ?></div>
        <button class="copy-btn" onclick="copyToClipboard('key-<?php echo $item['id']; ?>')">Copy Vless Key</button>
    </div>

    <?php endforeach; ?>
</div>

<script>
function copyToClipboard(id) {
    const text = document.getElementById(id).innerText;
    navigator.clipboard.writeText(text);
    alert("Copied!");
}
</script>

</body>
</html>
