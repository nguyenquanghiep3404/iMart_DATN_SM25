# setup-scheduler.ps1 (Final English Version)

# --- CONFIGURATION ---
$TaskName = "iMart Laravel Project Scheduler"
$TaskDescription = "Runs the Laravel scheduler every minute for the iMart project."
$ProjectDirectory = $PSScriptRoot
$PhpPath = "D:\laragon\bin\php\php-8.4.4-nts-Win32-vs17-x64\php.exe" # Your correct PHP path
# ---------------------

# Check if PHP path exists
if (-not (Test-Path $PhpPath)) {
    Write-Host "ERROR: php.exe not found at '$PhpPath'. Please update the path in this script." -ForegroundColor Red
    exit
}

Write-Host "Configuring scheduled task: '$TaskName'..."

# Define the action
$Action = New-ScheduledTaskAction -Execute $PhpPath -Argument "artisan schedule:run" -WorkingDirectory $ProjectDirectory

# Define the trigger to run every minute
$Trigger = New-ScheduledTaskTrigger -Once -At (Get-Date) -RepetitionInterval (New-TimeSpan -Minutes 1)

# Define other settings
$Settings = New-ScheduledTaskSettingsSet -AllowStartIfOnBatteries -DontStopIfGoingOnBatteries

# Register the task with error handling
try {
    Register-ScheduledTask -TaskName $TaskName -Action $Action -Trigger $Trigger -Settings $Settings -Description $TaskDescription -Force -ErrorAction Stop
    Write-Host "SUCCESS! Task '$TaskName' has been created/updated to run every minute." -ForegroundColor Green
    Write-Host "You can verify this in the Windows Task Scheduler."
}
catch {
    Write-Host "ERROR! Could not create the scheduled task. Details:" -ForegroundColor Red
    Write-Error $_
}