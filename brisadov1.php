<?php

// ========== CORES ==========
$branco = "\e[97m";
$amarelo = "\e[93m";
$laranja = "\e[38;5;208m";
$azul = "\e[34m";
$verde = "\e[92m";
$fverde = "\e[32m";
$vermelho = "\e[91m";
$cinza = "\e[37m";
$cln = "\e[0m";
$bold = "\e[1m";

// ========== BANNER ==========
system("clear");
echo $bold . $azul . "
╔════════════════════════════════════════════════════╗
║                                                    ║
║         🔍 TOASTLOG ANALYZER v1.0 🔍             ║
║                                                    ║
║         Detector de Apps Suspeitos via ADB         ║
║                                                    ║
╚════════════════════════════════════════════════════╝
" . $cln . "\n\n";

echo $bold . $cinza . "   Discord: discord.gg/allianceoficial\n";
echo $bold . $cinza . "   GitHub: github.com/nicollasbravo7/SCANNER\n\n" . $cln;

// ========== VERIFICAÇÕES INICIAIS ==========
echo $bold . $azul . "[i] Verificando dependências...\n" . $cln;

// Verificar ADB
$adbCheck = shell_exec('which adb 2>/dev/null');
if (empty($adbCheck)) {
    echo $bold . $vermelho . "[✗] ADB não encontrado!\n" . $cln;
    echo $bold . $amarelo . "[i] Instale: pkg install android-tools -y\n\n" . $cln;
    exit(1);
}
echo $bold . $verde . "[✓] ADB encontrado\n" . $cln;

// Verificar dispositivos
$devices = shell_exec('adb devices 2>&1');
if (strpos($devices, 'device') === false || strpos($devices, 'no devices') !== false) {
    echo $bold . $vermelho . "[✗] Nenhum dispositivo conectado!\n" . $cln;
    echo $bold . $amarelo . "[i] Execute: adb connect IP:PORTA\n\n" . $cln;
    exit(1);
}
echo $bold . $verde . "[✓] Dispositivo conectado\n\n" . $cln;

// ========== CAPTURAR TOASTLOG ==========
echo $bold . $azul . "[+] Capturando ToastLog...\n" . $cln;

$comandoToastLog = 'adb logcat -d -b all | grep "ToastLog" 2>/dev/null';
$toastOutput = shell_exec($comandoToastLog);

if (empty($toastOutput)) {
    echo $bold . $vermelho . "[!] Nenhum log ToastLog encontrado.\n" . $cln;
    echo $bold . $amarelo . "[i] Possíveis causas:\n" . $cln;
    echo $bold . $branco . "    • Buffer de log limpo\n" . $cln;
    echo $bold . $branco . "    • Dispositivo sem logs recentes\n\n" . $cln;
    exit(0);
}

$totalLinhas = substr_count($toastOutput, "\n");
echo $bold . $verde . "[✓] {$totalLinhas} linhas capturadas\n\n" . $cln;

// ========== LISTAS DE APPS SUSPEITOS ==========
$appsRoot = [
    'com.rifsxd.ksunext' => 'KernelSU Next',
    'me.weishu.kernelsu' => 'KernelSU',
    'com.topjohnwu.magisk' => 'Magisk Manager',
    'io.github.huskydg.magisk' => 'Magisk Delta',
    'com.kingroot.kinguser' => 'KingRoot',
    'eu.chainfire.supersu' => 'SuperSU',
    'com.koushikdutta.superuser' => 'Superuser',
    'com.dimonvideo.luckypatcher' => 'Lucky Patcher',
    'com.chelpus.lackypatch' => 'Lucky Patcher Alt',
    'com.forpda.lp' => 'Lucky Patcher Pro'
];

$appsModificacao = [
    'bin.mt.plus' => 'MT Manager',
    'bin.mt.plus.canary' => 'MT Manager Canary',
    'bin.mt.plus.dev' => 'MT Manager Dev',
    'com.lptiyu.tanki' => 'NP Manager',
    'com.gmail.heagoo.apkeditor' => 'APK Editor',
    'com.gmail.heagoo.apkeditor.pro' => 'APK Editor Pro',
    'com.speedsoftware.rootexplorer' => 'Root Explorer',
    'com.sb.gamehack' => 'GameGuardian',
    'com.sb.gsh' => 'GameGuardian Alt',
    'com.dw.gamekiller' => 'Game Killer',
    'org.creeplays.hack' => 'Creehack',
    'ru.zdevs.zarchiver' => 'ZArchiver (Root Mode)'
];

$appsADB = [
    'com.termux' => 'Termux',
    'com.termux.api' => 'Termux API',
    'com.draco.ladb' => 'LADB (Local ADB)',
    'com.cgutman.androidremotedebugger' => 'ADB Wireless',
    'stericson.busybox' => 'BusyBox',
    'com.jrummy.busybox.installer' => 'BusyBox Installer'
];

$appsXposed = [
    'de.robv.android.xposed.installer' => 'Xposed Framework',
    'org.meowcat.edxposed.manager' => 'EdXposed Manager',
    'top.canyie.dreamland.manager' => 'Dreamland Manager',
    'me.weishu.exp' => 'Tai Chi',
    'com.solohsu.android.edxp.manager' => 'EdXposed (SoloHsu)'
];

$palavrasSuspeitas = [
    'susfs' => 'SUSFS (Root Bypass)',
    '/data/adb/ksu' => 'KernelSU Path',
    'shamiko' => 'Shamiko (Magisk Hide)',
    'zygisk' => 'Zygisk Module',
    'riru' => 'Riru Framework',
    'hook' => 'Code Hook',
    'inject' => 'Code Injection',
    'bypass' => 'Bypass detectado'
];

// ========== ANÁLISE ==========
echo $bold . $azul . "[+] Analisando apps suspeitos...\n\n" . $cln;

$detectados = [];
$mensagensSuspeitas = [];

$linhas = explode("\n", trim($toastOutput));

foreach ($linhas as $linha) {
    if (empty($linha)) continue;
    
    preg_match('/(\d{2}-\d{2} \d{2}:\d{2}:\d{2}\.\d{3})/', $linha, $matchTime);
    preg_match('/\(([^,]+),/', $linha, $matchPackage);
    preg_match('/msg=\'([^\']+)\'/', $linha, $matchMsg);
    
    $horario = !empty($matchTime[1]) ? $matchTime[1] : 'Desconhecido';
    $package = !empty($matchPackage[1]) ? trim($matchPackage[1]) : '';
    $mensagem = !empty($matchMsg[1]) ? $matchMsg[1] : '';
    
    // Verificar ROOT
    foreach ($appsRoot as $pkg => $nome) {
        if (strpos($linha, $pkg) !== false && !isset($detectados[$pkg])) {
            $detectados[$pkg] = [
                'nome' => $nome,
                'tipo' => 'ROOT',
                'horario' => $horario,
                'package' => $pkg,
                'count' => 0
            ];
        }
        if (isset($detectados[$pkg])) {
            $detectados[$pkg]['count']++;
        }
    }
    
    // Verificar Modificação
    foreach ($appsModificacao as $pkg => $nome) {
        if (strpos($linha, $pkg) !== false && !isset($detectados[$pkg])) {
            $detectados[$pkg] = [
                'nome' => $nome,
                'tipo' => 'MODIFICAÇÃO',
                'horario' => $horario,
                'package' => $pkg,
                'count' => 0
            ];
        }
        if (isset($detectados[$pkg])) {
            $detectados[$pkg]['count']++;
        }
    }
    
    // Verificar ADB Tools
    foreach ($appsADB as $pkg => $nome) {
        if (strpos($linha, $pkg) !== false && !isset($detectados[$pkg])) {
            $detectados[$pkg] = [
                'nome' => $nome,
                'tipo' => 'ADB_TOOL',
                'horario' => $horario,
                'package' => $pkg,
                'count' => 0
            ];
        }
        if (isset($detectados[$pkg])) {
            $detectados[$pkg]['count']++;
        }
    }
    
    // Verificar Xposed
    foreach ($appsXposed as $pkg => $nome) {
        if (strpos($linha, $pkg) !== false && !isset($detectados[$pkg])) {
            $detectados[$pkg] = [
                'nome' => $nome,
                'tipo' => 'XPOSED',
                'horario' => $horario,
                'package' => $pkg,
                'count' => 0
            ];
        }
        if (isset($detectados[$pkg])) {
            $detectados[$pkg]['count']++;
        }
    }
    
    // Verificar mensagens suspeitas
    if (!empty($mensagem)) {
        foreach ($palavrasSuspeitas as $palavra => $descricao) {
            if (stripos($mensagem, $palavra) !== false) {
                $key = md5($linha);
                if (!isset($mensagensSuspeitas[$key])) {
                    $mensagensSuspeitas[$key] = [
                        'horario' => $horario,
                        'package' => $package,
                        'mensagem' => $mensagem,
                        'motivo' => $descricao
                    ];
                }
            }
        }
    }
}

// ========== EXIBIR RESULTADOS ==========
$encontrouCritico = false;

if (!empty($detectados)) {
    echo $bold . $vermelho . "╔════════════════════════════════════════════════════╗\n";
    echo $bold . $vermelho . "║         🚨 APLICATIVOS SUSPEITOS 🚨               ║\n";
    echo $bold . $vermelho . "╚════════════════════════════════════════════════════╝\n\n" . $cln;
    
    foreach ($detectados as $info) {
        switch($info['tipo']) {
            case 'ROOT':
            case 'XPOSED':
                $cor = $vermelho;
                $simbolo = '🚨';
                $acao = 'APLICAR W.O IMEDIATAMENTE';
                $encontrouCritico = true;
                break;
            case 'MODIFICAÇÃO':
                $cor = $amarelo;
                $simbolo = '⚠️';
                $acao = 'Verificar se foi usado durante/após partida';
                break;
            case 'ADB_TOOL':
                $cor = $laranja;
                $simbolo = '⚠️';
                $acao = 'Investigar uso suspeito';
                break;
            default:
                $cor = $amarelo;
                $simbolo = '⚠️';
                $acao = 'Investigar';
        }
        
        echo $bold . $cor . "{$simbolo} {$info['nome']} - [{$info['tipo']}]\n" . $cln;
        echo $bold . $branco . "   Package: {$info['package']}\n" . $cln;
        echo $bold . $branco . "   Primeiro uso: {$info['horario']}\n" . $cln;
        echo $bold . $branco . "   Detecções: {$info['count']}x\n" . $cln;
        echo $bold . $cor . "   ➜ {$acao}\n\n" . $cln;
    }
}

if (!empty($mensagensSuspeitas)) {
    echo $bold . $vermelho . "╔════════════════════════════════════════════════════╗\n";
    echo $bold . $vermelho . "║         🔍 MENSAGENS SUSPEITAS 🔍                 ║\n";
    echo $bold . $vermelho . "╚════════════════════════════════════════════════════╝\n\n" . $cln;
    
    foreach ($mensagensSuspeitas as $msg) {
        echo $bold . $amarelo . "⚠️  Toast Suspeito\n" . $cln;
        echo $bold . $branco . "   Horário: {$msg['horario']}\n" . $cln;
        echo $bold . $branco . "   App: {$msg['package']}\n" . $cln;
        echo $bold . $branco . "   Mensagem: \"{$msg['mensagem']}\"\n" . $cln;
        echo $bold . $amarelo . "   Motivo: {$msg['motivo']}\n\n" . $cln;
    }
    $encontrouCritico = true;
}

// ========== VEREDITO FINAL ==========
if ($encontrouCritico) {
    echo $bold . $vermelho . "\n╔════════════════════════════════════════════════════╗\n";
    echo $bold . $vermelho . "║                                                    ║\n";
    echo $bold . $vermelho . "║       🚨 ATIVIDADES CRÍTICAS DETECTADAS 🚨       ║\n";
    echo $bold . $vermelho . "║                                                    ║\n";
    echo $bold . $vermelho . "║         RECOMENDAÇÃO: APLICAR W.O                  ║\n";
    echo $bold . $vermelho . "║                                                    ║\n";
    echo $bold . $vermelho . "╚════════════════════════════════════════════════════╝\n\n" . $cln;
} else if (!empty($detectados)) {
    echo $bold . $amarelo . "\n⚠️  Aplicativos suspeitos encontrados - Investigar\n\n" . $cln;
} else {
    echo $bold . $verde . "╔════════════════════════════════════════════════════╗\n";
    echo $bold . $verde . "║                                                    ║\n";
    echo $bold . $verde . "║       ✅ NENHUM APP SUSPEITO DETECTADO ✅         ║\n";
    echo $bold . $verde . "║                                                    ║\n";
    echo $bold . $verde . "╚════════════════════════════════════════════════════╝\n\n" . $cln;
}

echo $bold . $cinza . "   Obrigado por compactuar por um cenário limpo.\n";
echo $bold . $cinza . "   Com carinho, Keller...\n\n" . $cln;

?>
