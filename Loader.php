<?php
$functionArg = 'example.function';
if (isset($argc) && $argc > 1) {
    $functionArg = $argv[1];
} elseif (isset($_GET['function'])) {
    $functionArg = $_GET['function'];
}
$functionArg = preg_replace('#\.\./|\.\.\\\\#', '', $functionArg);
$functionFile = __DIR__ . '/public/' . ltrim($functionArg, '/');
$title = '加载中……';
$icon = '';
$mountPoint = 'root';
$jsFiles = [];
$cssFiles = [];

if (file_exists($functionFile)) {
    $content = file_get_contents($functionFile);

    preg_match('/<title>(.*?)<\/title>/', $content, $m);
    if (!empty($m[1])) $title = $m[1];

    preg_match('/<icon>(.*?)<\/icon>/', $content, $m);
    if (!empty($m[1])) $icon = $m[1];

    preg_match('/<mount>(.*?)<\/mount>/', $content, $m);
    if (!empty($m[1])) $mountPoint = $m[1];

    preg_match_all('/<js>(.*?)<\/js>/', $content, $m);
    $jsFiles = $m[1];

    preg_match_all('/<css>(.*?)<\/css>/', $content, $m);
    $cssFiles = $m[1];
}
?><!DOCTYPE html>
<html lang="zh-CN">
<head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1" />
    <meta name="xfy-loader" content="true" />
<?php if ($icon): ?>
    <link rel="icon" href="<?= htmlspecialchars($icon) ?>" />
    <link rel="apple-touch-icon" href="<?= htmlspecialchars($icon) ?>" />
<?php endif; ?>
    <title><?= htmlspecialchars($title) ?></title>
<?php foreach ($cssFiles as $css): ?>
    <link rel="stylesheet" href="<?= htmlspecialchars($css) ?>" />
<?php endforeach; ?>
<style>
:root {
    color-scheme: light dark;
    --loader-bg: #E8E8E8;
    --loader-text: #666;
    --loader-mask: #E8E8E8;
}

@media (prefers-color-scheme: dark) {
    :root {
        --loader-bg: #1a1a2e;
        --loader-text: #888;
        --loader-mask: #1a1a2e;
    }
}

body {
    background-color: var(--loader-bg);
    margin: 0;
    padding: 0;
    min-height: 100vh;
    transition: background-color 0.3s ease;
}

.loader-container {
    position: fixed;
    top: 0;
    left: 0;
    width: 100%;
    height: 100%;
    display: flex;
    justify-content: center;
    align-items: center;
    background-color: var(--loader-bg);
    z-index: 9999;
    transition: background-color 0.3s ease;
}

.loader {
    --duration: 3s;
    --primary: rgb(0, 204, 255);
    --primary-light: #00a6ff;
    --primary-rgba: rgba(204, 0, 255, 0.04);
    width: 200px;
    height: 320px;
    position: relative;
    transform-style: preserve-3d;
}

@media (max-width: 480px) {
    .loader {
        zoom: 0.44;
    }
}

.loader:before, .loader:after {
    --r: 20.5deg;
    content: "";
    width: 320px;
    height: 140px;
    position: absolute;
    right: 32%;
    bottom: -11px;
    background: var(--loader-mask);
    transform: translateZ(200px) rotate(var(--r));
    animation: mask var(--duration) linear forwards infinite;
}

.loader:after {
    --r: -20.5deg;
    right: auto;
    left: 32%;
}

.loader .ground {
    position: absolute;
    left: -50px;
    bottom: -120px;
    transform-style: preserve-3d;
    transform: rotateY(-47deg) rotateX(-15deg) rotateZ(15deg) scale(1);
}

.loader .ground div {
    transform: rotateX(90deg) rotateY(0deg) translate(-48px, -120px) translateZ(100px) scale(0);
    width: 200px;
    height: 200px;
    background: var(--primary);
    background: linear-gradient(45deg, var(--primary) 0%, var(--primary) 50%, var(--primary-light) 50%, var(--primary-light) 100%);
    transform-style: preserve-3d;
    animation: ground var(--duration) linear forwards infinite;
}

.loader .ground div:before, .loader .ground div:after {
    --rx: 90deg;
    --ry: 0deg;
    --x: 44px;
    --y: 162px;
    --z: -50px;
    content: "";
    width: 156px;
    height: 300px;
    opacity: 0;
    background: linear-gradient(var(--primary), var(--primary-rgba));
    position: absolute;
    transform: rotateX(var(--rx)) rotateY(var(--ry)) translate(var(--x), var(--y)) translateZ(var(--z));
    animation: ground-shine var(--duration) linear forwards infinite;
}

.loader .ground div:after {
    --rx: 90deg;
    --ry: 90deg;
    --x: 0;
    --y: 177px;
    --z: 150px;
}

.loader .box {
    --x: 0;
    --y: 0;
    position: absolute;
    animation: var(--duration) linear forwards infinite;
    transform: translate(var(--x), var(--y));
}

.loader .box div {
    background-color: var(--primary);
    width: 48px;
    height: 48px;
    position: relative;
    transform-style: preserve-3d;
    animation: var(--duration) ease forwards infinite;
    transform: rotateY(-47deg) rotateX(-15deg) rotateZ(15deg) scale(0);
}

.loader .box div:before, .loader .box div:after {
    --rx: 90deg;
    --ry: 0deg;
    --z: 24px;
    --y: -24px;
    --x: 0;
    content: "";
    position: absolute;
    background-color: inherit;
    width: inherit;
    height: inherit;
    transform: rotateX(var(--rx)) rotateY(var(--ry)) translate(var(--x), var(--y)) translateZ(var(--z));
    filter: brightness(var(--b, 1.2));
}

.loader .box div:after {
    --rx: 0deg;
    --ry: 90deg;
    --x: 24px;
    --y: 0;
    --b: 1.4;
}

.loader .box.box0 {
    --x: -220px;
    --y: -120px;
    left: 58px;
    top: 108px;
}

.loader .box.box1 {
    --x: -260px;
    --y: 120px;
    left: 25px;
    top: 120px;
}

.loader .box.box2 {
    --x: 120px;
    --y: -190px;
    left: 58px;
    top: 64px;
}

.loader .box.box3 {
    --x: 280px;
    --y: -40px;
    left: 91px;
    top: 120px;
}

.loader .box.box4 {
    --x: 60px;
    --y: 200px;
    left: 58px;
    top: 132px;
}

.loader .box.box5 {
    --x: -220px;
    --y: -120px;
    left: 25px;
    top: 76px;
}

.loader .box.box6 {
    --x: -260px;
    --y: 120px;
    left: 91px;
    top: 76px;
}

.loader .box.box7 {
    --x: -240px;
    --y: 200px;
    left: 58px;
    top: 87px;
}

.loader .box0 { animation-name: box-move0; }
.loader .box0 div { animation-name: box-scale0; }
.loader .box1 { animation-name: box-move1; }
.loader .box1 div { animation-name: box-scale1; }
.loader .box2 { animation-name: box-move2; }
.loader .box2 div { animation-name: box-scale2; }
.loader .box3 { animation-name: box-move3; }
.loader .box3 div { animation-name: box-scale3; }
.loader .box4 { animation-name: box-move4; }
.loader .box4 div { animation-name: box-scale4; }
.loader .box5 { animation-name: box-move5; }
.loader .box5 div { animation-name: box-scale5; }
.loader .box6 { animation-name: box-move6; }
.loader .box6 div { animation-name: box-scale6; }
.loader .box7 { animation-name: box-move7; }
.loader .box7 div { animation-name: box-scale7; }

@keyframes box-move0 {
    12% { transform: translate(var(--x), var(--y)); }
    25%, 52% { transform: translate(0, 0); }
    80% { transform: translate(0, -32px); }
    90%, 100% { transform: translate(0, 188px); }
}

@keyframes box-scale0 {
    6% { transform: rotateY(-47deg) rotateX(-15deg) rotateZ(15deg) scale(0); }
    14%, 100% { transform: rotateY(-47deg) rotateX(-15deg) rotateZ(15deg) scale(1); }
}

@keyframes box-move1 {
    16% { transform: translate(var(--x), var(--y)); }
    29%, 52% { transform: translate(0, 0); }
    80% { transform: translate(0, -32px); }
    90%, 100% { transform: translate(0, 188px); }
}

@keyframes box-scale1 {
    10% { transform: rotateY(-47deg) rotateX(-15deg) rotateZ(15deg) scale(0); }
    18%, 100% { transform: rotateY(-47deg) rotateX(-15deg) rotateZ(15deg) scale(1); }
}

@keyframes box-move2 {
    20% { transform: translate(var(--x), var(--y)); }
    33%, 52% { transform: translate(0, 0); }
    80% { transform: translate(0, -32px); }
    90%, 100% { transform: translate(0, 188px); }
}

@keyframes box-scale2 {
    14% { transform: rotateY(-47deg) rotateX(-15deg) rotateZ(15deg) scale(0); }
    22%, 100% { transform: rotateY(-47deg) rotateX(-15deg) rotateZ(15deg) scale(1); }
}

@keyframes box-move3 {
    24% { transform: translate(var(--x), var(--y)); }
    37%, 52% { transform: translate(0, 0); }
    80% { transform: translate(0, -32px); }
    90%, 100% { transform: translate(0, 188px); }
}

@keyframes box-scale3 {
    18% { transform: rotateY(-47deg) rotateX(-15deg) rotateZ(15deg) scale(0); }
    26%, 100% { transform: rotateY(-47deg) rotateX(-15deg) rotateZ(15deg) scale(1); }
}

@keyframes box-move4 {
    28% { transform: translate(var(--x), var(--y)); }
    41%, 52% { transform: translate(0, 0); }
    80% { transform: translate(0, -32px); }
    90%, 100% { transform: translate(0, 188px); }
}

@keyframes box-scale4 {
    22% { transform: rotateY(-47deg) rotateX(-15deg) rotateZ(15deg) scale(0); }
    30%, 100% { transform: rotateY(-47deg) rotateX(-15deg) rotateZ(15deg) scale(1); }
}

@keyframes box-move5 {
    32% { transform: translate(var(--x), var(--y)); }
    45%, 52% { transform: translate(0, 0); }
    80% { transform: translate(0, -32px); }
    90%, 100% { transform: translate(0, 188px); }
}

@keyframes box-scale5 {
    26% { transform: rotateY(-47deg) rotateX(-15deg) rotateZ(15deg) scale(0); }
    34%, 100% { transform: rotateY(-47deg) rotateX(-15deg) rotateZ(15deg) scale(1); }
}

@keyframes box-move6 {
    36% { transform: translate(var(--x), var(--y)); }
    49%, 52% { transform: translate(0, 0); }
    80% { transform: translate(0, -32px); }
    90%, 100% { transform: translate(0, 188px); }
}

@keyframes box-scale6 {
    30% { transform: rotateY(-47deg) rotateX(-15deg) rotateZ(15deg) scale(0); }
    38%, 100% { transform: rotateY(-47deg) rotateX(-15deg) rotateZ(15deg) scale(1); }
}

@keyframes box-move7 {
    40% { transform: translate(var(--x), var(--y)); }
    53%, 52% { transform: translate(0, 0); }
    80% { transform: translate(0, -32px); }
    90%, 100% { transform: translate(0, 188px); }
}

@keyframes box-scale7 {
    34% { transform: rotateY(-47deg) rotateX(-15deg) rotateZ(15deg) scale(0); }
    42%, 100% { transform: rotateY(-47deg) rotateX(-15deg) rotateZ(15deg) scale(1); }
}

@keyframes ground {
    0%, 65% { transform: rotateX(90deg) rotateY(0deg) translate(-48px, -120px) translateZ(100px) scale(0); }
    75%, 90% { transform: rotateX(90deg) rotateY(0deg) translate(-48px, -120px) translateZ(100px) scale(1); }
    100% { transform: rotateX(90deg) rotateY(0deg) translate(-48px, -120px) translateZ(100px) scale(0); }
}

@keyframes ground-shine {
    0%, 70% { opacity: 0; }
    75%, 87% { opacity: 0.2; }
    100% { opacity: 0; }
}

@keyframes mask {
    0%, 65% { opacity: 0; }
    66%, 100% { opacity: 1; }
}
</style>
</head>
<body>
<div class="loader-container">
    <div class="loader">
        <div class="box box0"><div></div></div>
        <div class="box box1"><div></div></div>
        <div class="box box2"><div></div></div>
        <div class="box box3"><div></div></div>
        <div class="box box4"><div></div></div>
        <div class="box box5"><div></div></div>
        <div class="box box6"><div></div></div>
        <div class="box box7"><div></div></div>
        <div class="ground"><div></div></div>
    </div>
    <div style="position: absolute; bottom: 30%; left: 0; right: 0; text-align: center; font-size: 16px; color: var(--loader-text);">
        网页正在构建中……
    </div>
</div>

<div id="<?= htmlspecialchars($mountPoint) ?>" style="display: none; opacity: 0; transition: opacity 0.5s ease;"></div>
<div id="black-overlay" style="display: none; position: fixed; top: 0; left: 0; width: 100%; height: 100%; background-color: black; z-index: 100; transition: opacity 0.5s ease;"></div>

<?php foreach ($jsFiles as $js): ?>
<script src="<?= htmlspecialchars($js) ?>"></script>
<?php endforeach; ?>

<script>
(function () {
    var loader = document.querySelector('.loader-container');
    var overlay = document.getElementById('black-overlay');
    var mountPoint = document.getElementById('<?= htmlspecialchars($mountPoint) ?>');

    function fadeOutLoader() {
        if (!loader) return;
        loader.style.opacity = '0';
        setTimeout(function () {
            loader.style.display = 'none';
        }, 500);
    }

    function fadeInApp() {
        if (!mountPoint) return;
        mountPoint.style.display = 'block';
        mountPoint.style.position = 'relative';
        mountPoint.style.top = '0';
        mountPoint.style.left = '0';
        mountPoint.style.width = '100%';
        mountPoint.style.height = '100%';
        mountPoint.style.zIndex = '200';
        mountPoint.style.opacity = '0';
        mountPoint.style.transition = 'opacity 0.5s ease';
        setTimeout(function () {
            mountPoint.style.opacity = '1';
            fadeOutLoader();
            setTimeout(function () {
                if (overlay) {
                    overlay.style.display = 'block';
                    setTimeout(function () {
                        overlay.style.opacity = '1';
                    }, 100);
                }
            }, 500);
        }, 100);
    }

    if (document.readyState === 'complete') {
        fadeInApp();
    } else {
        window.addEventListener('load', fadeInApp);
    }
})();
</script>
</body>
</html>