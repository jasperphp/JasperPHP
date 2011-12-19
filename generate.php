<?php

/*
  This file is free software: you can redistribute it and/or modify
  it under the terms of the GNU General Public License as published by
  the Free Software Foundation, either version 3 of the License, or
  (at your option) any later version.

  This file is distributed in the hope that it will be useful,
  but WITHOUT ANY WARRANTY; without even the implied warranty of
  MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
  GNU General Public License for more details.

  You should have received a copy of the GNU General Public License
  along with This file.  If not, see <http://www.gnu.org/licenses/>.

  tsuyu

 */

require_once("http://localhost:8081/JavaBridge/java/Java.inc");

try {

	$jasperxml = new java("net.sf.jasperreports.engine.xml.JRXmlLoader");
	$jasperDesign = $jasperxml->load(realpath("customer.jrxml"));
	$query = new java("net.sf.jasperreports.engine.design.JRDesignQuery");
	$query->setText("SELECT customer.`first_name` AS customer_first_name,
                                customer.`last_name` AS customer_last_name,
                                customer.`email` AS customer_email
                         FROM  `customer` customer");
	$jasperDesign->setQuery($query);
	$compileManager = new JavaClass("net.sf.jasperreports.engine.JasperCompileManager");
	$report = $compileManager->compileReport($jasperDesign);
	
} catch (JavaException $ex) {
	echo $ex;
}

$fillManager = new JavaClass("net.sf.jasperreports.engine.JasperFillManager");

$params = new Java("java.util.HashMap");
$params->put("title", "Customer");

$class = new JavaClass("java.lang.Class");
$class->forName("com.mysql.jdbc.Driver");
$driverManager = new JavaClass("java.sql.DriverManager");
$conn = $driverManager->getConnection("jdbc:mysql://localhost:3306/sakila?zeroDateTimeBehavior=convertToNull","username","password");
$jasperPrint = $fillManager->fillReport($report, $params, $conn);

switch($_POST['format']) {
case 'xls':
    $outputPath = realpath(".")."\\"."output.xls";

    try {
        $exporterXLS = new java("net.sf.jasperreports.engine.export.JRXlsExporter");
        $exporterXLS->setParameter(java("net.sf.jasperreports.engine.export.JRXlsExporterParameter")->IS_ONE_PAGE_PER_SHEET, java("java.lang.Boolean")->TRUE);
        $exporterXLS->setParameter(java("net.sf.jasperreports.engine.export.JRXlsExporterParameter")->IS_WHITE_PAGE_BACKGROUND, java("java.lang.Boolean")->FALSE);
        $exporterXLS->setParameter(java("net.sf.jasperreports.engine.export.JRXlsExporterParameter")->IS_REMOVE_EMPTY_SPACE_BETWEEN_ROWS, java("java.lang.Boolean")->TRUE);
        $exporterXLS->setParameter(java("net.sf.jasperreports.engine.JRExporterParameter")->JASPER_PRINT, $jasperPrint );
        $exporterXLS->setParameter(java("net.sf.jasperreports.engine.JRExporterParameter")->OUTPUT_FILE_NAME, $outputPath);
        $exporterXLS->exportReport();

    } catch (JavaException $ex) {
      echo $ex;
    }
    
    header("Content-type: application/vnd.ms-excel");
    header("Content-Disposition: attachment; filename=output.xls");
break;
case 'csv':
    $outputPath = realpath(".")."\\"."output.csv";

    try {
        $exporterCSV = new java("net.sf.jasperreports.engine.export.JRCsvExporter");
        $exporterCSV->setParameter(java("net.sf.jasperreports.engine.export.JRCsvExporterParameter")->FIELD_DELIMITER,",");
        $exporterCSV->setParameter(java("net.sf.jasperreports.engine.export.JRCsvExporterParameter")->RECORD_DELIMITER,"\n");
        $exporterCSV->setParameter(java("net.sf.jasperreports.engine.export.JRCsvExporterParameter")->CHARACTER_ENCODING,"UTF-8");
        $exporterCSV->setParameter(java("net.sf.jasperreports.engine.JRExporterParameter")->JASPER_PRINT, $jasperPrint );
        $exporterCSV->setParameter(java("net.sf.jasperreports.engine.JRExporterParameter")->OUTPUT_FILE_NAME, $outputPath);
        $exporterCSV->exportReport();
    } catch (JavaException $ex) {
      echo $ex;
    }

    header("Content-type: application/csv");
    header("Content-Disposition: attachment; filename=output.csv");
break;
case 'docx':
    $outputPath = realpath(".")."\\"."output.docx";

    try {
        $exporterDOCX = new java("net.sf.jasperreports.engine.export.ooxml.JRDocxExporter");
        $exporterDOCX->setParameter(java("net.sf.jasperreports.engine.JRExporterParameter")->JASPER_PRINT, $jasperPrint );
        $exporterDOCX->setParameter(java("net.sf.jasperreports.engine.JRExporterParameter")->OUTPUT_FILE_NAME, $outputPath);
        $exporterDOCX->exportReport();

    } catch (JavaException $ex) {
      echo $ex;
    }

    header("Content-type: application/vnd.ms-word");
    header("Content-Disposition: attachment; filename=output.docx");
break;
case 'html':
    $outputPath = realpath(".")."\\"."output.html";

    try {
        $exporterHTML = new java("net.sf.jasperreports.engine.export.JRHtmlExporter");
        $exporterHTML->setParameter(java("net.sf.jasperreports.engine.JRExporterParameter")->JASPER_PRINT, $jasperPrint );
        $exporterHTML->setParameter(java("net.sf.jasperreports.engine.JRExporterParameter")->OUTPUT_FILE_NAME, $outputPath);
        $exporterHTML->exportReport();

    } catch (JavaException $ex) {
      echo $ex;
    }
break;
case 'pdf':
    $outputPath = realpath(".")."\\"."output.pdf";

    $exportManagerPDF = new JavaClass("net.sf.jasperreports.engine.JasperExportManager");
    $exportManagerPDF->exportReportToPdfFile($jasperPrint, $outputPath);

    header("Content-type: application/pdf");
break;
case 'ods':
    $outputPath = realpath(".")."\\"."output.ods";

    try {
        $exporterODS = new java("net.sf.jasperreports.engine.export.oasis.JROdsExporter");
        $exporterODS->setParameter(java("net.sf.jasperreports.engine.JRExporterParameter")->JASPER_PRINT, $jasperPrint );
        $exporterODS->setParameter(java("net.sf.jasperreports.engine.JRExporterParameter")->OUTPUT_FILE_NAME, $outputPath);
        $exporterODS->exportReport();

    } catch (JavaException $ex) {
      echo $ex;
    }

    header("Content-type: application/vnd.oasis.opendocument.spreadsheet");
    header("Content-Disposition: attachment; filename=output.ods");
break;
case 'odt':
    $outputPath = realpath(".")."\\"."output.odt";

    try {
        $exporterODT = new java("net.sf.jasperreports.engine.export.oasis.JROdtExporter");
        $exporterODT->setParameter(java("net.sf.jasperreports.engine.JRExporterParameter")->JASPER_PRINT, $jasperPrint );
        $exporterODT->setParameter(java("net.sf.jasperreports.engine.JRExporterParameter")->OUTPUT_FILE_NAME, $outputPath);
        $exporterODT->exportReport();

    } catch (JavaException $ex) {
      echo $ex;
    }

    header("Content-type: application/vnd.oasis.opendocument.text");
    header("Content-Disposition: attachment; filename=output.odt");
break;
case 'txt':
    $outputPath = realpath(".")."\\"."output.txt";

    try {
        $exporterTXT = new java("net.sf.jasperreports.engine.export.JRTextExporter");
        $exporterTXT->setParameter(java("net.sf.jasperreports.engine.export.JRTextExporterParameter")->PAGE_WIDTH,120);
        $exporterTXT->setParameter(java("net.sf.jasperreports.engine.export.JRTextExporterParameter")->PAGE_HEIGHT,60);
        $exporterTXT->setParameter(java("net.sf.jasperreports.engine.JRExporterParameter")->JASPER_PRINT, $jasperPrint );
        $exporterTXT->setParameter(java("net.sf.jasperreports.engine.JRExporterParameter")->OUTPUT_FILE_NAME, $outputPath);
        $exporter->exportReport();

    } catch (JavaException $ex) {
      echo $ex;
    }

    header("Content-type: text/plain");
break;
}

readfile($outputPath);
unlink($outputPath);
?>