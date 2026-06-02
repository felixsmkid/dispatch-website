<?php

function e(?string $value): string
{
    return htmlspecialchars((string) $value, ENT_QUOTES, 'UTF-8');
}

function redirect(string $url): void
{
    header('Location: ' . $url);
    exit;
}

function json_response(array $data, int $code = 200): void
{
    http_response_code($code);
    header('Content-Type: application/json; charset=utf-8');
    echo json_encode($data, JSON_UNESCAPED_UNICODE);
    exit;
}

function is_post(): bool
{
    return $_SERVER['REQUEST_METHOD'] === 'POST';
}

function input(string $key, string $default = ''): string
{
    $value = $_POST[$key] ?? $_GET[$key] ?? $default;
    return is_string($value) ? trim($value) : $default;
}

function validate_required(array $fields): ?string
{
    foreach ($fields as $label => $value) {
        if ($value === '' || (is_string($value) && trim($value) === '')) {
            return $label . ' wajib diisi.';
        }
    }
    return null;
}

function role_label(string $role): string
{
    $labels = [
        'developer' => 'Developer',
        'dispatch' => 'Dispatch',
        'lspd' => 'LSPD Officer',
        'bcso' => 'BCSO Deputy',
    ];
    return $labels[$role] ?? $role;
}

function department_for_role(string $role): ?string
{
    if ($role === 'lspd') {
        return 'LSPD';
    }
    if ($role === 'bcso') {
        return 'BCSO';
    }
    return null;
}

function can_manage_cad(string $role): bool
{
    return in_array($role, ['developer', 'dispatch'], true);
}

function can_access_dispatch(string $role): bool
{
    return in_array($role, ['developer', 'dispatch'], true);
}

function is_officer_role(string $role): bool
{
    return in_array($role, ['lspd', 'bcso'], true);
}

function unit_statuses(): array
{
    return [
        '10-7' => 'Not In Service',
        '10-8' => 'Available',
        '10-23' => 'On Scene',
        '10-38' => 'Traffic Stop',
        '10-57' => 'Pursuit',
        '10-95' => 'Suspect In Custody',
        '10-99' => 'Investigation Area',
    ];
}

function call_priorities(): array
{
    return ['low', 'medium', 'high', 'emergency'];
}

function generate_call_number(PDO $pdo): string
{
    $prefix = date('ymd');
    $stmt = $pdo->prepare("SELECT COUNT(*) FROM calls WHERE call_number LIKE ?");
    $stmt->execute([$prefix . '%']);
    $count = (int) $stmt->fetchColumn() + 1;
    return $prefix . '-' . str_pad((string) $count, 4, '0', STR_PAD_LEFT);
}

function generate_pursuit_code(PDO $pdo): string
{
    $prefix = 'P' . date('ymd');
    $stmt = $pdo->prepare("SELECT COUNT(*) FROM pursuits WHERE pursuit_code LIKE ?");
    $stmt->execute([$prefix . '%']);
    $count = (int) $stmt->fetchColumn() + 1;
    return $prefix . '-' . str_pad((string) $count, 3, '0', STR_PAD_LEFT);
}

function get_user_unit(PDO $pdo, int $userId): ?array
{
    $stmt = $pdo->prepare('SELECT * FROM units WHERE user_id = ? AND is_online = 1 LIMIT 1');
    $stmt->execute([$userId]);
    $unit = $stmt->fetch(PDO::FETCH_ASSOC);
    return $unit ?: null;
}

function format_datetime(?string $datetime): string
{
    if (!$datetime) {
        return '-';
    }
    return date('d M Y H:i:s', strtotime($datetime));
}

function priority_badge_class(string $priority): string
{
    return match ($priority) {
        'emergency' => 'bg-danger',
        'high' => 'bg-warning text-dark',
        'medium' => 'bg-info text-dark',
        default => 'bg-secondary',
    };
}

function status_badge_class(string $status): string
{
    return match ($status) {
        'active' => 'bg-success',
        'pending' => 'bg-warning text-dark',
        'closed', 'ended', 'inactive' => 'bg-secondary',
        default => 'bg-primary',
    };
}
