<?php
/* ========== CONFIG ========== */
$TELEGRAM_BOT_TOKEN = '7997163573:AAEOUg_dhbntM3josVk3ODD86f5AtAlvJ4s';   // 🔑 Bot token
$TELEGRAM_CHAT_ID   = '-1002701560268';                                   // Channel / group ID (bot must be admin)
$SAVE_DEBUG_LOG     = true;                                               // true = write tg_log.txt for every send

/* ========== CORE ========== */
$responseBox = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['clickid'])) {

    /* ---- 1.  Input ---- */
    $clickid = trim($_POST['clickid']);
    $goal    = isset($_POST['goal']) ? trim($_POST['goal']) : '';

    /* ---- 2.  External request to Adjust endpoint ---- */
    $url  = 'https://api-touch.clclubs.com/app/task/cb/adjust/event?sourceId=ad&touchId=staylong';
    $url .= '&clickid=' . urlencode($clickid);
    if ($goal !== '') {
        $url .= '&eventName=' . urlencode($goal);
    }

    $headers = [
        'sec-ch-ua: "Google Chrome";v="137", "Chromium";v="137", "Not/A)Brand";v="24"',
        'sec-ch-ua-mobile: ?1',
        'sec-ch-ua-platform: "Android"',
        'upgrade-insecure-requests: 1',
        'user-agent: Mozilla/5.0 (Linux; Android 10) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/137.0.0.0 Mobile Safari/537.36',
        'accept: */*',
        'sec-fetch-site: none',
        'sec-fetch-mode: navigate',
        'sec-fetch-dest: document',
        'X-Forwarded-For: 86.48.47.169',
        'accept-language: en-US,en;q=0.9,hi;q=0.8',
        'priority: u=0, i'
    ];

    $ch = curl_init($url);
    curl_setopt_array($ch, [
        CURLOPT_HTTPHEADER     => $headers,
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_TIMEOUT        => 20,
    ]);
    $result       = curl_exec($ch);
    $contentType  = curl_getinfo($ch, CURLINFO_CONTENT_TYPE) ?: '';
    $curlErr      = curl_error($ch);
    curl_close($ch);

    /* ---- 3.  Display response in browser ---- */
    if ($curlErr) {
        $responseBox = '<pre style="color:#ff8080">cURL Error: ' . htmlspecialchars($curlErr) . '</pre>';
    } else {
        if (stripos($contentType, 'application/json') !== false) {
            $responseBox = '<pre>'.json_encode(json_decode($result), JSON_PRETTY_PRINT|JSON_UNESCAPED_SLASHES).'</pre>';
        } else {
            $responseBox = '<pre>'.htmlspecialchars($result).'</pre>';
        }
    }

    /* ---- 4.  Send log to Telegram ---- */
    $msg  = "<b>🎯 Goal Fired</b>\nClickID: <code>$clickid</code>";
    if ($goal !== '') {
        $msg .= "\nGoal: <b>$goal</b>";
    }
    sendToTelegram($msg);
}


/* =========  FUNCTIONS  ========= */
function sendToTelegram(string $message): void {
    global $TELEGRAM_BOT_TOKEN, $TELEGRAM_CHAT_ID, $SAVE_DEBUG_LOG;

    if (!$TELEGRAM_BOT_TOKEN || !$TELEGRAM_CHAT_ID) return;

    $payload = [
        'chat_id'                  => $TELEGRAM_CHAT_ID,
        'text'                     => $message,
        'parse_mode'               => 'HTML',
        'disable_web_page_preview' => true
    ];

    $ch = curl_init("https://api.telegram.org/bot{$TELEGRAM_BOT_TOKEN}/sendMessage");
    curl_setopt_array($ch, [
        CURLOPT_POST           => true,
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_POSTFIELDS     => http_build_query($payload),
        CURLOPT_TIMEOUT        => 10
    ]);
    $resp = curl_exec($ch);
    $err  = curl_error($ch);
    curl_close($ch);

    if ($SAVE_DEBUG_LOG) {
        $line = date('[d-m H:i] ') . ($resp ?: 'ERR: '.$err) . PHP_EOL;
        file_put_contents(__DIR__.'/tg_log.txt', $line, FILE_APPEND);
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<title>Earnbay Bypass</title>
<meta name="viewport" content="width=device-width,initial-scale=1.0">
<style>
*{margin:0;padding:0;box-sizing:border-box;font-family:Poppins,arial,sans-serif}
body{min-height:100vh;display:flex;justify-content:center;align-items:center;background:linear-gradient(120deg,#2C5364,#203A43,#0F2027);padding:20px}
.card{width:100%;max-width:420px;background:rgba(255,255,255,.15);border-radius:20px;backdrop-filter:blur(14px);border:1px solid rgba(255,255,255,.2);padding:32px 28px;color:#fff;box-shadow:0 8px 24px rgba(0,0,0,.35);animation:fadeIn .8s ease}
@keyframes fadeIn{from{transform:scale(.9);opacity:0}to{transform:scale(1);opacity:1}}
h1{text-align:center;margin-bottom:24px;font-size:1.6rem}
.inputBox{position:relative;margin-bottom:26px}
.inputBox input{width:100%;background:rgba(255,255,255,.25);border:none;outline:none;padding:14px 16px;border-radius:12px;font-size:1rem;color:#fff}
.inputBox label{position:absolute;left:16px;top:50%;transform:translateY(-50%);font-size:.9rem;color:#e0e0e0;pointer-events:none;transition:.2s}
.inputBox input:focus+label,.inputBox input:not(:placeholder-shown)+label{top:-9px;background:rgba(0,0,0,.5);padding:0 6px;border-radius:6px;font-size:.7rem}
button{width:100%;padding:14px 0;border:none;border-radius:12px;background:linear-gradient(135deg,#12c2e9,#c471ed,#f64f59);color:#fff;font-size:1rem;font-weight:600;cursor:pointer;transition:.15s}
button:active{transform:scale(.97)}
.response-box{margin-top:30px;background:rgba(0,0,0,.55);padding:20px;border-radius:12px;max-height:320px;overflow:auto;font-family:monospace;font-size:.9rem;line-height:1.35;white-space:pre-wrap;color:#e5e5e5}
</style>
</head>
<body>
<div class="card">
  <h1>Earnbay Bypass Script</h1>
  <form method="post" autocomplete="off">
    <div class="inputBox">
      <input type="text" name="clickid" id="clickid" placeholder=" " required value="<?= isset($_POST['clickid']) ? htmlspecialchars($_POST['clickid']) : '' ?>">
      <label for="clickid">ClickID</label>
    </div>
    <div class="inputBox">
      <input type="text" name="goal" id="goal" placeholder=" " value="<?= isset($_POST['goal']) ? htmlspecialchars($_POST['goal']) : '' ?>">
      <label for="goal">Goal (optional)</label>
    </div>
    <button type="submit">Send Request 🚀</button>
  </form>

  <?php if ($responseBox): ?>
    <div class="response-box" id="respBox"><?= $responseBox ?></div>
    <script>document.getElementById("respBox").scrollIntoView({behavior:"smooth"});</script>
  <?php endif; ?>
</div>
</body>
</html>