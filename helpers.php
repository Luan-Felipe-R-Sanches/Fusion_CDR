<?php
// Arquivo: helpers.php

function format_seconds($seconds) {
    if ($seconds < 0) return '00:00:00';
    $h = floor($seconds / 3600);
    $m = floor(($seconds % 3600) / 60);
    $s = $seconds % 60;
    return sprintf('%02d:%02d:%02d', $h, $m, $s);
}

function get_call_status($row) {
    $status = strtolower($row['status'] ?? '');
    switch ($status) {
        case 'answered': return ['text' => 'Atendida', 'class' => 'bg-success'];
        case 'missed': case 'no_answer': return ['text' => 'Não Atendida', 'class' => 'bg-danger'];
        case 'voicemail': return ['text' => 'Voicemail', 'class' => 'bg-info text-dark'];
        case 'busy': return ['text' => 'Ocupado', 'class' => 'bg-warning text-dark'];
        case 'failed': return ['text' => 'Falhou', 'class' => 'bg-dark'];
        default:
            if (($row['billsec'] ?? 0) > 0) return ['text' => 'Atendida', 'class' => 'bg-success'];
            return ['text' => 'Outro', 'class' => 'bg-secondary'];
    }
}