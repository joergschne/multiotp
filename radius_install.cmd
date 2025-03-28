@ECHO OFF
REM ************************************************************
REM @file  radius_install.cmd
REM @brief Script to install the radius service.
REM
REM multiOTP - Strong two-factor authentication PHP class package
REM https://www\.multiOTP.net
REM 
REM Windows batch file for Windows 2K/XP/2003/7/2008/8/2012/10
REM
REM @author    Andre Liechti, SysCo systemes de communication sa, <info@multiotp.net>
REM @version   5.9.9.1
REM @date      2025-01-20
REM @since     2013-08-20
REM @copyright (c) 2013-2025 SysCo systemes de communication sa
REM @copyright GNU Lesser General Public License
REM
REM
REM Description
REM
REM   radius_install is a small script that will install the
REM   radius server of multiOTP under Windows using freeradius.
REM   (http://sourceforge.net/projects/freeradius/)
REM
REM
REM Usage
REM  
REM   The script must be launched in the top folder of multiOTP.
REM   Default ports are 1812 and 1813
REM
REM
REM Licence
REM
REM   Copyright (c) 2013-2025 SysCo systemes de communication sa
REM   SysCo (tm) is a trademark of SysCo systemes de communication sa
REM   (http://www.sysco.ch/)
REM   All rights reserved.
REM
REM   This file is part of the multiOTP project.
REM
REM
REM Users feedbacks and comments
REM
REM  2013-08-21 Henk van der Helm (again ;-)
REM    Thanks Henk for his first valuable feedback with some bugs reports
REM
REM
REM Change Log
REM
REM   2024-05-03 5.9.7.2 SysCo/al -debug option removed from the Windows radius module launcher
REM   2023-11-23 5.9.7.0 SysCo/al Space in installation path supported
REM   2020-12-11 5.8.0.6 SysCo/al Do an automatic "Run as administrator" if needed
REM                               Don't delete the _radius_multiotp_folder_alternate environment variable
REM   2018-09-14 5.4.0.1 SysCo/al Compatibility mode to Windows 7 automatically added for radiusd.exe
REM   2017-05-29 5.0.4.5 SysCo/al Unified script with some bug fixes
REM                               Alternate authentication executable support
REM   2016-11-04 5.0.2.7 SysCo/al Unified file header
REM   2016-10-16 5.0.2.5 SysCo/al Version synchronisation
REM   2015-07-15 4.3.2.5 SysCo/al Replace the localhost configuration by 222.222.222.222
REM   2014-03-27 4.2.4   SysCo/al More generic usage
REM   2013-08-25 4.0.6   SysCo/al Service can also be set in the command line
REM                               (radius_install [auth_port [account_port [service_tag [service_name]]]])
REM   2013-08-21 4.0.5   SysCo/al Fix the mix between _port and _auth_port in the script
REM                               The service is now *really* using the defined ports
REM                               Ports can be set in the command line
REM   2013-08-20 4.0.4   SysCo/al Initial release
REM
REM ************************************************************

NET SESSION >NUL 2>&1
IF NOT %ERRORLEVEL% == 0 (
    ECHO WARNING! Please run this script as an administrator, otherwise it will fail.
    ECHO Elevating privileges...
    REM PING 127.0.0.1 > NUL 2>&1
    CD /d %~dp0
    MSHTA "javascript: var shell = new ActiveXObject('shell.application'); shell.ShellExecute('%~nx0', '', '', 'runas', 1);close();"
    EXIT
    REM PAUSE
    REM EXIT /B 1
)
:NoWarning

SET _radius_secret=multiotpsecret

REM Ports variables are not overwritten if already defined
IF "%_auth_port%"=="" SET _auth_port=1812
IF "%_account_port%"=="" SET _account_port=1813

REM Define the service tag and the service name
SET _service_tag=multiOTPradius
SET _service_name=multiOTP Radius server

REM Define the executable
SET _radius_multiotp=../../multiotp.exe
IF NOT "%_radius_multiotp_alternate%"=="" SET _radius_multiotp=%_radius_multiotp_alternate%

REM Ports and service information can be overwritten if passing parameters
IF NOT "%1"=="" SET _auth_port=%1
IF NOT "%2"=="" SET _account_port=%2
IF NOT "%3"=="" SET _service_tag=%3
IF NOT "%4"=="" SET _service_name=%4
IF NOT "%5"=="" SET _service_name=%_service_name% %5
IF NOT "%6"=="" SET _service_name=%_service_name% %6
IF NOT "%7"=="" SET _service_name=%_service_name% %7
IF NOT "%8"=="" SET _service_name=%_service_name% %8
IF NOT "%9"=="" SET _service_name=%_service_name% %9

REM Define the current folder IN SHORT PATH NOTATION
SET _radius_multiotp_folder=%~d0%~sp0
IF NOT "%_radius_multiotp_folder_alternate%"=="" SET _radius_multiotp_folder=%_radius_multiotp_folder_alternate%
SET _radius_folder=%~d0%~p0
SET _tools_folder=%~d0%~p0tools\
IF NOT EXIST "%_radius_folder%radius" SET _radius_folder=%~d0%~p0..\
IF NOT EXIST "%_tools_folder%FART.exe" SET _tools_folder=%~d0%~p0..\tools\

REM Stop and delete the service (if already existing)
SC stop %_service_tag% >NUL
SC delete %_service_tag% >NUL

REM Create the multiotp module for the radius server
ECHO # Exec module instance for multiOTP (https://www\.multiOTP.net/).>"%_radius_folder%radius\etc\raddb\modules\multiotp"
ECHO exec multiotp {>>"%_radius_folder%radius\etc\raddb\modules\multiotp"
ECHO         wait = yes>>"%_radius_folder%radius\etc\raddb\modules\multiotp"
ECHO         input_pairs = request>>"%_radius_folder%radius\etc\raddb\modules\multiotp"
ECHO         output_pairs = reply>>"%_radius_folder%radius\etc\raddb\modules\multiotp"
ECHO         program = "%_radius_multiotp% **"-base-dir=%_radius_multiotp_folder%**" -keep-local -log **"%%{User-Name}**" **"%%{User-Password}**" -src=%%{Packet-Src-IP-Address} -chap-challenge=%%{CHAP-Challenge} -chap-password=%%{CHAP-Password} -ms-chap-challenge=%%{MS-CHAP-Challenge} -ms-chap-response=%%{MS-CHAP-Response} -ms-chap2-response=%%{MS-CHAP2-Response}">>"%_radius_folder%radius\etc\raddb\modules\multiotp"
ECHO         shell_escape = yes>>"%_radius_folder%radius\etc\raddb\modules\multiotp"
ECHO }>>"%_radius_folder%radius\etc\raddb\modules\multiotp"

REM Sorry, this is an *ugly* trick to change "\" to "/" with the FART tool
"%_tools_folder%FART.exe" "%_radius_folder%radius\etc\raddb\modules\multiotp" "\\" "!!!/!!!" >NUL
"%_tools_folder%FART.exe" --remove "%_radius_folder%radius\etc\raddb\modules\multiotp" "!!!" >NUL
"%_tools_folder%FART.exe" "%_radius_folder%radius\etc\raddb\modules\multiotp" "**" "\\" >NUL

REM Customize the etc/raddb/radiusd.conf configuration file
COPY "%_radius_folder%radius\etc\raddb\radiusd.template.conf" "%_radius_folder%radius\etc\raddb\radiusd.conf" /Y >NUL
"%_tools_folder%FART.exe" "%_radius_folder%radius\etc\raddb\radiusd.conf" "_auth_port" "%_auth_port%" >NUL
"%_tools_folder%FART.exe" "%_radius_folder%radius\etc\raddb\radiusd.conf" "_account_port" "%_account_port%" >NUL

REM Customize the etc/raddb/clients.conf configuration file
COPY "%_radius_folder%radius\etc\raddb\clients.template.conf" "%_radius_folder%radius\etc\raddb\clients.conf" /Y >NUL

"%_tools_folder%FART.exe" "%_radius_folder%radius\etc\raddb\clients.conf" "_radius_secret" "%_radius_secret%" >NUL
"%_tools_folder%FART.exe" "%_radius_folder%radius\etc\raddb\clients.conf" "ipaddr = 127.0.0.1" "ipaddr = 222.222.222.222" >NUL
"%_tools_folder%FART.exe" "%_radius_folder%radius\etc\raddb\clients.conf" "client localhost" "client 222.222.222.222" >NUL

REM Create the service
"%_radius_folder%radius\nssm" install "%_service_tag%" "%_radius_folder%radius\sbin\radiusd.exe" >NUL
"%_radius_folder%radius\nssm" set "%_service_tag%" Description "Runs the %_service_name% on ports %_auth_port%/%_account_port%." >NUL

REM Define the parameters of the service (launched by nssm
"%_radius_folder%radius\nssm" set "%_service_tag%" DisplayName "%_service_name%" >NUL
"%_radius_folder%radius\nssm" set "%_service_tag%" AppParameters "-X -d ""%_radius_folder%radius\etc\raddb""" >NUL
"%_radius_folder%radius\nssm" set "%_service_tag%" AppDirectory "%_radius_folder%radius\sbin" >NUL

REM Define the compatibility mode to Windows 7 for radiusd
REG ADD "HKLM\Software\Microsoft\Windows NT\CurrentVersion\AppCompatFlags\Layers" /f /v "%_radius_folder%radius\sbin\radiusd.exe" /t REG_SZ /d "WIN7RTM" >NUL

REM Basic firewall rules for the service
netsh firewall delete allowedprogram "%_radius_folder%radius\sbin\radiusd.exe" >NUL
netsh firewall add allowedprogram "%_radius_folder%radius\sbin\radiusd.exe" "%_service_name%" ENABLE >NUL

REM Enhanced firewall rules for the service
netsh advfirewall firewall delete rule name="%_service_name%" >NUL
netsh advfirewall firewall add rule name="%_service_name%" dir=in action=allow program="%_radius_folder%radius\sbin\radiusd.exe" enable=yes >NUL

REM Start the service
SC start %_service_tag% >NUL

REM Clean the environment variables
SET _account_port=
SET _auth_port=
SET _radius_multiotp_folder=
SET _radius_multiotp=
SET _radius_folder=
SET _radius_secret=
SET _service_name=
SET _service_tag=
SET _tools_folder=
