<?php

// Capturar logs do ToastLog
$comandoToastLog = 'adb logcat -d -b all | grep "ToastLog" 2>/dev/null';
$toastOutput = shell_exec($comandoToastLog);

// Definir caminho da pasta Downloads
$downloadPath = getenv('HOME') . '/storage/downloads/';

// Verificar se a pasta existe (para Termux Android)
if (!is_dir($downloadPath)) {
    // Tentar caminho alternativo para Termux
    $downloadPath = getenv('HOME') . '/downloads/';
    
    // Se ainda não existir, usar pasta atual
    if (!is_dir($downloadPath)) {
        $downloadPath = './';
    }
}

// Salvar em arquivo com timestamp
if (!empty($toastOutput)) {
    $nomeArquivo = 'toastlog_' . date('Y-m-d_H-i-s') . '.txt';
    $caminhoCompleto = $downloadPath . $nomeArquivo;
    
    file_put_contents($caminhoCompleto, $toastOutput);
    
    echo "\n[✓] ToastLog capturado com sucesso!\n";
    echo "[✓] Arquivo salvo em: $caminhoCompleto\n";
    echo "[✓] Total de linhas: " . substr_count($toastOutput, "\n") . "\n\n";
} else {
    echo "\n[!] Nenhum log ToastLog encontrado no dispositivo.\n\n";
}

?>