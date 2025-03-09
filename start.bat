@echo off
cls
echo LOCALHOST FORWARDER
echo.
echo [1] Ngrok
echo [2] Pinggy.io
echo [3] Localhost.run
echo.
echo [0] Exit
echo.
set /p "menuChoice=Choose a menu option [1,2,3,0] : "
if "%menuChoice%"=="1" goto NGROK
if "%menuChoice%"=="2" goto PINGGY
if "%menuChoice%"=="3" goto LOCALHOST
if "%menuChoice%"=="0" goto EXIT

:NGROK
cls
echo LOCALHOST FORWARDER
echo [Ngrok]
echo.
ngrok http 80
pause
exit

:PINGGY
cls
echo LOCALHOST FORWARDER
echo [Pinggy.io]
echo.
ssh -p 443 -o StrictHostKeyChecking=no -R0:127.0.0.1:80 ap.a.pinggy.io
pause
exit

:LOCALHOST
cls
echo LOCALHOST FORWARDER
echo [Localhost.run]
echo.
ssh -R 80:localhost:80 nokey@localhost.run
pause
exit

:EXIT
exit