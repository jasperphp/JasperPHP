JasperPHP
========
This a PHP library based on JasperReports library that let you create reports with a graphical tool (We recommend to use iReport).

Once you finish your report with iReport you'll have a file with extension jrxml, using this library you will export this jrxml to PDF, XLS, DOC, DOCX, PPTX, CSV, HTML, ODS, ODT, TXT, RTF and we are working to let the library export to SWF.


How to configure JasperPHP
=====================================

1. Download PHP/Java Bridge(http://php-java-bridge.sourceforge.net/).
2. Deploy the Java Bridge on tomcat Server.
3. Copy the jar jasper lib, ireport lib and mysql connector j lib in tomcat lib folder
4. Restart tomcat server
5. Edit php.ini,turn on setting -> allow_url_include = On;
6. Restart apache httpd server
7. Copy whole folder jasper-report-php-integration into htdocs  
8. Include path of the library in php (require_once("http://localhost:8080/JavaBridge/java/Java.inc")), make sure path is correct and tomcat server is running.
9. run index.php

FYI
=======
There is a bug when you use Tomcat 8.

License
=======
This program is free software, under the GNU/GPLv3 license terms.
See LICENSE.txt for the complete license.
