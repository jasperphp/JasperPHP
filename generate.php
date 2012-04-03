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

require_once("http://localhost:8080/JavaBridge/java/Java.inc");

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

//db username and password
$conn = $driverManager->getConnection("jdbc:mysql://localhost:3306/sakila?zeroDateTimeBehavior=convertToNull", "username", "password");
$jasperPrint = $fillManager->fillReport($report, $params, $conn);

$exporter = new java("net.sf.jasperreports.engine.JRExporter");

switch ($_POST['format']) {
    case 'xls':
        $outputPath = realpath(".") . "\\" . "output.xls";

        try {
            $exporter = new java("net.sf.jasperreports.engine.export.JRXlsExporter");
            $exporter->setParameter(java("net.sf.jasperreports.engine.export.JRXlsExporterParameter")->IS_ONE_PAGE_PER_SHEET, java("java.lang.Boolean")->TRUE);
            $exporter->setParameter(java("net.sf.jasperreports.engine.export.JRXlsExporterParameter")->IS_WHITE_PAGE_BACKGROUND, java("java.lang.Boolean")->FALSE);
            $exporter->setParameter(java("net.sf.jasperreports.engine.export.JRXlsExporterParameter")->IS_REMOVE_EMPTY_SPACE_BETWEEN_ROWS, java("java.lang.Boolean")->TRUE);
            $exporter->setParameter(java("net.sf.jasperreports.engine.JRExporterParameter")->JASPER_PRINT, $jasperPrint);
            $exporter->setParameter(java("net.sf.jasperreports.engine.JRExporterParameter")->OUTPUT_FILE_NAME, $outputPath);
        } catch (JavaException $ex) {
            echo $ex;
        }

        header("Content-type: application/vnd.ms-excel");
        header("Content-Disposition: attachment; filename=output.xls");
        break;
    case 'csv':
        $outputPath = realpath(".") . "\\" . "output.csv";

        try {
            $exporter = new java("net.sf.jasperreports.engine.export.JRCsvExporter");
            $exporter->setParameter(java("net.sf.jasperreports.engine.export.JRCsvExporterParameter")->FIELD_DELIMITER, ",");
            $exporter->setParameter(java("net.sf.jasperreports.engine.export.JRCsvExporterParameter")->RECORD_DELIMITER, "\n");
            $exporter->setParameter(java("net.sf.jasperreports.engine.export.JRCsvExporterParameter")->CHARACTER_ENCODING, "UTF-8");
            $exporter->setParameter(java("net.sf.jasperreports.engine.JRExporterParameter")->JASPER_PRINT, $jasperPrint);
            $exporter->setParameter(java("net.sf.jasperreports.engine.JRExporterParameter")->OUTPUT_FILE_NAME, $outputPath);
         } catch (JavaException $ex) {
            echo $ex;
        }

        header("Content-type: application/csv");
        header("Content-Disposition: attachment; filename=output.csv");
        break;
    case 'docx':
        $outputPath = realpath(".") . "\\" . "output.docx";

        try {
            $exporter = new java("net.sf.jasperreports.engine.export.ooxml.JRDocxExporter");
            $exporter->setParameter(java("net.sf.jasperreports.engine.JRExporterParameter")->JASPER_PRINT, $jasperPrint);
            $exporter->setParameter(java("net.sf.jasperreports.engine.JRExporterParameter")->OUTPUT_FILE_NAME, $outputPath);
        
            
        } catch (JavaException $ex) {
            echo $ex;
        }

        header("Content-type: application/vnd.ms-word");
        header("Content-Disposition: attachment; filename=output.docx");
        break;
    case 'html':
        $outputPath = realpath(".") . "\\" . "output.html";

        try {
            $exporter = new java("net.sf.jasperreports.engine.export.JRHtmlExporter");
            $exporter->setParameter(java("net.sf.jasperreports.engine.JRExporterParameter")->JASPER_PRINT, $jasperPrint);
            $exporter->setParameter(java("net.sf.jasperreports.engine.JRExporterParameter")->OUTPUT_FILE_NAME, $outputPath);
        
            
        } catch (JavaException $ex) {
            echo $ex;
        }
        break;
    case 'pdf':
        $outputPath = realpath(".") . "\\" . "output.pdf";

        $exporter = new java("net.sf.jasperreports.engine.export.JRPdfExporter");
        $exporter->setParameter(java("net.sf.jasperreports.engine.JRExporterParameter")->JASPER_PRINT, $jasperPrint);
        $exporter->setParameter(java("net.sf.jasperreports.engine.JRExporterParameter")->OUTPUT_FILE_NAME, $outputPath);
        
        header("Content-type: application/pdf");
        header("Content-Disposition: attachment; filename=output.pdf");
        break;
    case 'ods':
        $outputPath = realpath(".") . "\\" . "output.ods";

        try {
            $exporter = new java("net.sf.jasperreports.engine.export.oasis.JROdsExporter");
            $exporter->setParameter(java("net.sf.jasperreports.engine.JRExporterParameter")->JASPER_PRINT, $jasperPrint);
            $exporter->setParameter(java("net.sf.jasperreports.engine.JRExporterParameter")->OUTPUT_FILE_NAME, $outputPath);
         } catch (JavaException $ex) {
            echo $ex;
        }

        header("Content-type: application/vnd.oasis.opendocument.spreadsheet");
        header("Content-Disposition: attachment; filename=output.ods");
        break;
    case 'odt':
        $outputPath = realpath(".") . "\\" . "output.odt";

        try {
            $exporter = new java("net.sf.jasperreports.engine.export.oasis.JROdtExporter");
            $exporter->setParameter(java("net.sf.jasperreports.engine.JRExporterParameter")->JASPER_PRINT, $jasperPrint);
            $exporter->setParameter(java("net.sf.jasperreports.engine.JRExporterParameter")->OUTPUT_FILE_NAME, $outputPath);
         } catch (JavaException $ex) {
            echo $ex;
        }

        header("Content-type: application/vnd.oasis.opendocument.text");
        header("Content-Disposition: attachment; filename=output.odt");
        break;
    case 'txt':
        $outputPath = realpath(".") . "\\" . "output.txt";

        try {
            $exporter = new java("net.sf.jasperreports.engine.export.JRTextExporter");
            $exporter->setParameter(java("net.sf.jasperreports.engine.export.JRTextExporterParameter")->PAGE_WIDTH, 120);
            $exporter->setParameter(java("net.sf.jasperreports.engine.export.JRTextExporterParameter")->PAGE_HEIGHT, 60);
            $exporter->setParameter(java("net.sf.jasperreports.engine.JRExporterParameter")->JASPER_PRINT, $jasperPrint);
            $exporter->setParameter(java("net.sf.jasperreports.engine.JRExporterParameter")->OUTPUT_FILE_NAME, $outputPath);
        } catch (JavaException $ex) {
            echo $ex;
        }

        header("Content-type: text/plain");
        break;
    case 'rtf':
        $outputPath = realpath(".") . "\\" . "output.rtf";

        try {
            $exporter = new java("net.sf.jasperreports.engine.export.JRRtfExporter");
            $exporter->setParameter(java("net.sf.jasperreports.engine.JRExporterParameter")->JASPER_PRINT, $jasperPrint);
            $exporter->setParameter(java("net.sf.jasperreports.engine.JRExporterParameter")->OUTPUT_FILE_NAME, $outputPath);
        } catch (JavaException $ex) {
            echo $ex;
        }

        header("Content-type: application/rtf");
        header("Content-Disposition: attachment; filename=output.rtf");
        break;
    case 'pptx':
         $outputPath = realpath(".") . "\\" . "output.rtf";
        try {
            $exporter = new java("net.sf.jasperreports.engine.export.ooxml.JRPptxExporter");
            $exporter->setParameter(java("net.sf.jasperreports.engine.JRExporterParameter")->JASPER_PRINT, $jasperPrint);
            $exporter->setParameter(java("net.sf.jasperreports.engine.JRExporterParameter")->OUTPUT_FILE_NAME, $outputPath);
        } catch (JavaException $ex) {
            echo $ex;
        }

        header("Content-type: aapplication/vnd.ms-powerpoint");
        header("Content-Disposition: attachment; filename={$this->filename}.pptx");
      break;
}
$exporter->exportReport();
        
readfile($outputPath);
unlink($outputPath);
?>