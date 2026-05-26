<#!
  B4.7 — Smoke thủ công / semi-auto: BKS Stay JWT → cancel-request.

  Yêu cầu: BE chạy local, DB đã migrate + seed (StayPortalSeeder: user@gmail.com).
  Bật BCP: BCP_CANCELLATION_V1=1 (middleware bcp.cancellation cho reasons + cancel-request).

  Chạy mẫu (PowerShell):
    cd bks-system-be\business-script
    .\b4_stay_smoke.ps1
    .\b4_stay_smoke.ps1 -BookingId 42

  Biến môi trường (tuỳ chọn):
    BKS_API_BASE   — mặc định http://127.0.0.1:8000
    BKS_STAY_EMAIL — mặc định user@gmail.com
    BKS_STAY_PASS  — mặc định 123456a!
#>
[CmdletBinding()]
param(
    [string] $ApiBase = $(if ($env:BKS_API_BASE) { $env:BKS_API_BASE } else { "http://127.0.0.1:8000" }),
    [string] $Email = $(if ($env:BKS_STAY_EMAIL) { $env:BKS_STAY_EMAIL } else { "user@gmail.com" }),
    [string] $Password = $(if ($env:BKS_STAY_PASS) { $env:BKS_STAY_PASS } else { "123456a!" }),
    [int] $BookingId = 0
)

$ErrorActionPreference = "Stop"
$root = $ApiBase.TrimEnd("/")
$v1 = "$root/api/v1"

function Invoke-BksJson {
    param(
        [string] $Method,
        [string] $Url,
        [hashtable] $Headers = @{},
        $Body = $null
    )
    $params = @{ Uri = $Url; Method = $Method; Headers = $Headers; ContentType = "application/json" }
    if ($null -ne $Body) {
        $params.Body = ($Body | ConvertTo-Json -Depth 8 -Compress)
    }
    return Invoke-RestMethod @params
}

Write-Host "[1/3] POST admin/auth/login ..." -ForegroundColor Cyan
$login = Invoke-BksJson -Method POST -Url "$v1/admin/auth/login" -Body @{ email = $Email; password = $Password }
if (-not $login.data.token) {
    throw "Login failed: $($login | ConvertTo-Json -Compress)"
}
$token = [string] $login.data.token
$auth = @{ Authorization = "Bearer $token" }

Write-Host "[2/3] GET stay/cancellation-reasons ..." -ForegroundColor Cyan
$reasons = Invoke-BksJson -Method GET -Url "$v1/stay/cancellation-reasons" -Headers $auth
if ($reasons.status -ne "success" -or -not $reasons.data) {
    throw "Không đọc được lý do hủy. Kiểm tra BCP_CANCELLATION_V1 và route stay."
}
$reasonCode = [string] $reasons.data[0].code
Write-Host "  Dùng reason_code: $reasonCode" -ForegroundColor Gray

$bid = $BookingId
if ($bid -lt 1) {
    Write-Host "[3a] GET stay/bookings — tìm đơn status=1 (confirmed) ..." -ForegroundColor Cyan
    $list = Invoke-BksJson -Method GET -Url "$v1/stay/bookings?page=1&per_page=20" -Headers $auth
    $rows = @()
    if ($list.data.data) { $rows = $list.data.data }
    elseif ($list.data) { $rows = $list.data }
    $confirmed = $rows | Where-Object { $_.status -eq 1 } | Select-Object -First 1
    if (-not $confirmed) {
        Write-Warning "Không có booking confirmed (1) trong trang 1. Tạo đơn confirmed hoặc chạy: .\b4_stay_smoke.ps1 -BookingId <id>"
        exit 0
    }
    $bid = [int] $confirmed.id
}
Write-Host "[3b] POST stay/bookings/$bid/cancel-request ..." -ForegroundColor Cyan
$idempotency = [guid]::NewGuid().ToString("N")
$body = @{
    reason_code       = $reasonCode
    reason_text       = "Smoke B4.7 $(Get-Date -Format o)"
    idempotency_key   = $idempotency
}
try {
    $resp = Invoke-BksJson -Method POST -Url "$v1/stay/bookings/$bid/cancel-request" -Headers $auth -Body $body
    Write-Host "OK: $($resp.status) — $($resp.message)" -ForegroundColor Green
    Write-Host ($resp.data | ConvertTo-Json -Compress)
} catch {
    $err = $_.ErrorDetails.Message
    if (-not $err) { $err = $_.Exception.Message }
    Write-Warning "cancel-request thất bại: $err"
    exit 1
}

Write-Host "Hoàn tất smoke B4.7." -ForegroundColor Green
