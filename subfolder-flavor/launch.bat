@echo off
SET OUTPUT_FOLDER=pexadmin
SET XCOPY_OPTIONS=/E /H /C /R /Q /Y /I

echo Making the output folder (%OUTPUT_FOLDER%)...
mkdir %OUTPUT_FOLDER%

echo Copying Application and Framework folders...
xcopy ..\Application %OUTPUT_FOLDER%\Application %XCOPY_OPTIONS%
xcopy ..\Oxygen_Framework %OUTPUT_FOLDER%\Oxygen_Framework %XCOPY_OPTIONS%

echo Protecting them with .htaccess files to prevent public access...
xcopy .htaccess %OUTPUT_FOLDER%\Application
xcopy .htaccess %OUTPUT_FOLDER%\Oxygen_Framework

echo Moving public content...
xcopy ..\public %OUTPUT_FOLDER% %XCOPY_OPTIONS%

echo Copying specific bootstrap file...
xcopy index.php %OUTPUT_FOLDER% /Y

echo "Subfolder flavor done!"