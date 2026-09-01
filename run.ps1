Set-Location $PSScriptRoot
if (Get-Command php -ErrorAction SilentlyContinue) {
    php main.php
} else {
    & "C:\PHP\php.exe" main.php
}
