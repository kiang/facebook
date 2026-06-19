<?php
$dataFile = __DIR__ . '/data/media_reports.json';

if (!file_exists($dataFile)) {
    die('media_reports.json not found');
}

$reports = json_decode(file_get_contents($dataFile), true);

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $updates = $_POST['titles'] ?? [];
    $changed = 0;
    foreach ($reports as &$r) {
        $url = $r['url'];
        if (isset($updates[$url]) && $updates[$url] !== $r['title']) {
            $r['title'] = $updates[$url];
            $changed++;
        }
    }
    unset($r);
    if ($changed > 0) {
        file_put_contents($dataFile, json_encode($reports, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES));
    }
    header('Location: media_admin.php?saved=' . $changed);
    exit;
}

$saved = isset($_GET['saved']) ? (int)$_GET['saved'] : -1;
$showAll = isset($_GET['all']);
$empty = array_filter($reports, fn($r) => empty($r['title']));
$display = $showAll ? $reports : $empty;
?>
<!DOCTYPE html>
<html lang="zh-TW">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>媒體報導管理</title>
<style>
*{box-sizing:border-box;margin:0;padding:0}
body{font-family:-apple-system,BlinkMacSystemFont,'Segoe UI',Roboto,sans-serif;background:#f0f2f5;color:#1c1e21;padding:20px}
h1{font-size:20px;margin-bottom:16px}
.info{background:#e8f5e9;border:1px solid #a5d6a7;border-radius:6px;padding:10px 14px;margin-bottom:16px;font-size:14px}
.stats{font-size:14px;color:#65676b;margin-bottom:16px}
.toggle{font-size:13px;margin-bottom:16px}
.toggle a{color:#1877f2}
table{width:100%;border-collapse:collapse;background:white;border-radius:8px;overflow:hidden;box-shadow:0 1px 2px rgba(0,0,0,.1)}
th{background:#1877f2;color:white;text-align:left;padding:10px 12px;font-size:13px}
td{padding:8px 12px;border-top:1px solid #e4e6eb;font-size:13px;vertical-align:top}
tr:hover{background:#f5f6f7}
.url{max-width:300px;word-break:break-all}
.url a{color:#1877f2;font-size:12px}
.date{white-space:nowrap;color:#65676b}
input[type=text]{width:100%;padding:6px 8px;border:1px solid #ddd;border-radius:4px;font-size:13px}
input[type=text]:focus{outline:none;border-color:#1877f2}
.actions{margin-top:16px;text-align:right}
button{background:#1877f2;color:white;border:none;padding:8px 24px;border-radius:6px;font-size:14px;cursor:pointer}
button:hover{background:#1669d4}
.empty{color:#e65100;font-size:12px}
</style>
</head>
<body>

<h1>媒體報導管理</h1>

<?php if ($saved >= 0): ?>
<div class="info">已更新 <?= $saved ?> 筆標題。</div>
<?php endif; ?>

<div class="stats">
    共 <?= count($reports) ?> 筆，其中 <?= count($empty) ?> 筆缺少標題。
</div>

<div class="toggle">
    <?php if ($showAll): ?>
        <a href="media_admin.php">只顯示缺少標題</a>
    <?php else: ?>
        <a href="media_admin.php?all">顯示全部</a>
    <?php endif; ?>
</div>

<?php if (empty($display)): ?>
<div class="info">所有報導都已有標題！</div>
<?php else: ?>
<form method="post">
<table>
<tr>
    <th>日期</th>
    <th>連結</th>
    <th>標題</th>
</tr>
<?php foreach ($display as $r): ?>
<tr>
    <td class="date"><?= date('Y-m-d', $r['ts']) ?></td>
    <td class="url"><a href="<?= htmlspecialchars($r['url']) ?>" target="_blank" rel="noopener"><?= htmlspecialchars($r['url']) ?></a></td>
    <td>
        <input type="text" name="titles[<?= htmlspecialchars($r['url']) ?>]" value="<?= htmlspecialchars($r['title'] ?? '') ?>"
            <?php if (empty($r['title'])): ?>placeholder="輸入標題..."<?php endif; ?>>
    </td>
</tr>
<?php endforeach; ?>
</table>
<div class="actions">
    <button type="submit">儲存變更</button>
</div>
</form>
<?php endif; ?>

</body>
</html>
