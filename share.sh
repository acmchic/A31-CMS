$BasePath = "D:\backup"
$UserPassword = "123456"

$Units = @(
    @{ Folder="ban_giam_doc"; Username="bgd" },
    @{ Folder="phong_ke_hoach"; Username="pkh" },
    @{ Folder="ban_chinh_tri"; Username="bct" },
    @{ Folder="phong_ky_thuat"; Username="pkt" },
    @{ Folder="phong_co_dien"; Username="pcd" },
    @{ Folder="phong_vat_tu"; Username="pvt" },
    @{ Folder="phong_kiem_tra_chat_luong"; Username="pktcl" },
    @{ Folder="phong_tai_chinh"; Username="ptc" },
    @{ Folder="phong_hanh_chinh_hau_can"; Username="phchc" },
    @{ Folder="px1"; Username="px1" },
    @{ Folder="px2"; Username="px2" },
    @{ Folder="px3"; Username="px3" },
    @{ Folder="px4"; Username="px4" },
    @{ Folder="px5"; Username="px5" },
    @{ Folder="px6"; Username="px6" },
    @{ Folder="px7"; Username="px7" },
    @{ Folder="px8"; Username="px8" },
    @{ Folder="px9"; Username="px9" }
)

if (!(Test-Path $BasePath)) {
    New-Item -ItemType Directory -Path $BasePath | Out-Null
}

foreach ($u in $Units) {

    $FolderName = $u.Folder
    $UserName   = $u.Username
    $ShareName  = "share_$UserName"
    $FolderPath = Join-Path $BasePath $FolderName

    Write-Host "----- Processing: $UserName ($FolderPath) -----"

    $existingShare = Get-SmbShare -Name $ShareName -ErrorAction SilentlyContinue
    if ($existingShare) { Remove-SmbShare -Name $ShareName -Force }

    if (!(Test-Path $FolderPath)) {
        New-Item -ItemType Directory -Path $FolderPath | Out-Null
    }

    if (!(Get-LocalUser -Name $UserName -ErrorAction SilentlyContinue)) {
        $Pass = ConvertTo-SecureString $UserPassword -AsPlainText -Force
        $Params = @{ Name = $UserName; Password = $Pass; PasswordNeverExpires = $true }
        New-LocalUser @Params
    }

    Start-Sleep -Milliseconds 300

    icacls $FolderPath /inheritance:r | Out-Null
    icacls $FolderPath /grant "Administrators:(OI)(CI)F" | Out-Null
    icacls $FolderPath /grant "SYSTEM:(OI)(CI)F" | Out-Null
    icacls $FolderPath /grant "${UserName}:(OI)(CI)F" | Out-Null

    Start-Sleep -Milliseconds 200

    New-SmbShare -Name $ShareName -Path $FolderPath -FullAccess $UserName -ChangeAccess "Administrators" -ErrorAction Stop

    Write-Host "Share created: \\$env:COMPUTERNAME\$ShareName"
    Write-Host "----- DONE: $UserName -----`n"
}

Write-Host "=== ALL SHARES AND PERMISSIONS CREATED SUCCESSFULLY ==="
