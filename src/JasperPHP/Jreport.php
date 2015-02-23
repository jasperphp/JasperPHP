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
  and refactored by FraGoTe (fgonzalestello91@gmail.com)
 */
namespace JasperPHP;

require_once("http://localhost:8080/JavaBridge/java/Java.inc");

class Jreport
{
    var $query;
    var $jrxmlName;
    var $jasperPrint;
    var $filename;
    var $parametros;

	function setReport($query, $jrxmlName, $parameters, $filename = "report") 
	{
		if (!empty($query)) {
            $this->query = $query;
        }
        
        if (!empty($jrxmlName)) {
            $this->jrxmlName = $jrxmlName;
        }
        
        if (!empty($filename)) {
            $this->filename = $filename;
        }
        
        if (!empty($parameters)) {
            $this->parametros = $parameters;
        }
    
		$this->connect();
	}
	
    function compileReporte() 
    {
        $ruta = "reports/" . $this->jrxmlName;
        $consulta = $this->query;
        try {
            $jasperxml = new \java("net.sf.jasperreports.engine.xml.JRXmlLoader");
            $jasperDesign = $jasperxml->load(realpath($ruta));
            $query = new \java("net.sf.jasperreports.engine.design.JRDesignQuery");
            if (!empty($consulta)) {
                $consulta = str_replace("\"", "", $consulta);
                $query->setText($consulta);
                $jasperDesign->setQuery($query);
            }
            $compileManager = new \JavaClass("net.sf.jasperreports.engine.JasperCompileManager");
            $report = $compileManager->compileReport($jasperDesign);
        } catch (\JavaException $ex) {
            echo $ex;
        }
        
        return $report;
    }

    function connect() 
    {
        //db username and password
        $host = 'localhost:3306';
        $dbname = 'zakila';
        $username = 'root';
        $password = '';
        $report = $this->compileReporte();
        $fillManager = new \JavaClass("net.sf.jasperreports.engine.JasperFillManager");
        $params = new \Java("java.util.HashMap");
        $params->put("REPORT_LOCALE", $this->convertValue("en_US", "java.util.Locale"));

        if (!empty($this->parametros)) {
            foreach ($this->parametros as $key => $value) {
                $params->put($key, $value);
            }
        }

        $class = new \JavaClass("java.lang.Class");
        $class->forName("com.mysql.jdbc.Driver");
        $driverManager = new \JavaClass("java.sql.DriverManager");
		
        try{
			$conn = $driverManager->getConnection("jdbc:mysql://$host/$dbname?zeroDateTimeBehavior=convertToNull", $username, $password);
			$jasperPrint = $fillManager->fillReport($report, $params, $conn);
        } catch (\JavaException $ex) {
			echo $ex->getCause();
        }
        
        $this->jasperPrint = $jasperPrint;
    }

    function export($typeoffile) 
    {
        $jasperPrint = $this->jasperPrint;
        $exporter = new \java("net.sf.jasperreports.engine.JRExporter");
        set_time_limit(0);
		
        switch ($typeoffile) {
            case 'xls':
                $outputPath = tempnam(realpath("tmp"), $this->filename);  //generate unique temp file name    
                chmod($outputPath, 0766);       //allow tomcat to write permission
                try {
                    $exporter = new \java("net.sf.jasperreports.engine.export.JRXlsExporter");
                    $exporter->setParameter(\java("net.sf.jasperreports.engine.export.JRXlsExporterParameter")->IS_ONE_PAGE_PER_SHEET, java("java.lang.Boolean")->FALSE);
                    $exporter->setParameter(\java("net.sf.jasperreports.engine.export.JRXlsExporterParameter")->IS_WHITE_PAGE_BACKGROUND, java("java.lang.Boolean")->FALSE);
                    $exporter->setParameter(\java("net.sf.jasperreports.engine.export.JRXlsExporterParameter")->IS_REMOVE_EMPTY_SPACE_BETWEEN_ROWS, java("java.lang.Boolean")->TRUE);
                    $exporter->setParameter(\java("net.sf.jasperreports.engine.export.JRXlsExporterParameter")->IS_DETECT_CELL_TYPE, java("java.lang.Boolean")->TRUE);
                    $exporter->setParameter(\java("net.sf.jasperreports.engine.JRExporterParameter")->JASPER_PRINT, $jasperPrint);
                    $exporter->setParameter(\java("net.sf.jasperreports.engine.JRExporterParameter")->OUTPUT_FILE_NAME, $outputPath);
                } catch (\JavaException $ex) {
                    echo $ex;
                }

                header("Content-type: application/vnd.ms-excel;");
                header("Content-Disposition: attachment; filename={$this->filename}.xls");
                break;
            case 'csv':
                $outputPath = tempnam(realpath("tmp"), $this->filename);  //generate unique temp file name    
                chmod($outputPath, 0766);
                try {
                    $exporter = new \java("net.sf.jasperreports.engine.export.JRCsvExporter");
                    $exporter->setParameter(\java("net.sf.jasperreports.engine.export.JRCsvExporterParameter")->FIELD_DELIMITER, ",");
                    $exporter->setParameter(\java("net.sf.jasperreports.engine.export.JRCsvExporterParameter")->RECORD_DELIMITER, "\n");
                    $exporter->setParameter(\java("net.sf.jasperreports.engine.export.JRCsvExporterParameter")->CHARACTER_ENCODING, "UTF-8");
                    $exporter->setParameter(\java("net.sf.jasperreports.engine.JRExporterParameter")->JASPER_PRINT, $jasperPrint);
                    $exporter->setParameter(\java("net.sf.jasperreports.engine.JRExporterParameter")->OUTPUT_FILE_NAME, $outputPath);
                } catch (\JavaException $ex) {
                    echo $ex;
                }

                header("Content-type: application/csv");
                header("Content-Disposition: attachment; filename={$this->filename}.csv");
                break;
            case 'docx':
                $outputPath = tempnam(realpath("tmp"), $this->filename);  //generate unique temp file name    
                chmod($outputPath, 0766);
                try {
                    $exporter = new \java("net.sf.jasperreports.engine.export.ooxml.JRDocxExporter");
                    $exporter->setParameter(\java("net.sf.jasperreports.engine.JRExporterParameter")->JASPER_PRINT, $jasperPrint);
                    $exporter->setParameter(\java("net.sf.jasperreports.engine.JRExporterParameter")->OUTPUT_FILE_NAME, $outputPath);
                } catch (\JavaException $ex) {
                    echo $ex;
                }

                header("Content-type: application/vnd.ms-word");
                header("Content-Disposition: attachment; filename={$this->filename}.docx");
                break;
            case 'html':
                $outputPath = tempnam(realpath("tmp"), $this->filename);  //generate unique temp file name    
                chmod($outputPath, 0766);

                try {
                    $exporter = new \java("net.sf.jasperreports.engine.export.JRHtmlExporter");
                    $exporter->setParameter(\java("net.sf.jasperreports.engine.JRExporterParameter")->JASPER_PRINT, $jasperPrint);
                    $exporter->setParameter(\java("net.sf.jasperreports.engine.JRExporterParameter")->OUTPUT_FILE_NAME, $outputPath);
                } catch (\JavaException $ex) {
                    echo $ex;
                }
                break;
            case 'pdf':
                $outputPath = tempnam(realpath("tmp"), $this->filename);  //generate unique temp file name    
                chmod($outputPath, 0766);
                
				try {
					$exporter = new \java("net.sf.jasperreports.engine.export.JRPdfExporter");
					$exporter->setParameter(\java("net.sf.jasperreports.engine.JRExporterParameter")->JASPER_PRINT, $jasperPrint);
					$exporter->setParameter(\java("net.sf.jasperreports.engine.JRExporterParameter")->OUTPUT_FILE_NAME, $outputPath);
                } catch (\JavaException $ex) {
                    echo $ex;
                }
                header("Content-type: application/pdf");
                header("Content-Disposition: inline; filename={$this->filename}.pdf");
                header("Cache-Control: no-cache, must-revalidate"); // HTTP/1.1
                header('Last-Modified: '.gmdate('D, d M Y H:i:s').' GMT', true, 200);
                header("Expires: -1");
                break;
            case 'ods':
                $outputPath = tempnam(realpath("tmp"), $this->filename);  //generate unique temp file name    
                chmod($outputPath, 0766);
                try {
                    $exporter = new \java("net.sf.jasperreports.engine.export.oasis.JROdsExporter");
                    $exporter->setParameter(\java("net.sf.jasperreports.engine.JRExporterParameter")->JASPER_PRINT, $jasperPrint);
                    $exporter->setParameter(\java("net.sf.jasperreports.engine.JRExporterParameter")->OUTPUT_FILE_NAME, $outputPath);
                } catch (\JavaException $ex) {
                    echo $ex;
                }

                header("Content-type: application/vnd.oasis.opendocument.spreadsheet");
                header("Content-Disposition: attachment; filename={$this->filename}.ods");
                break;
            case 'odt':
                $outputPath = tempnam(realpath("tmp"), $this->filename);  //generate unique temp file name    
                chmod($outputPath, 0766);
                try {
                    $exporter = new \java("net.sf.jasperreports.engine.export.oasis.JROdtExporter");
                    $exporter->setParameter(\java("net.sf.jasperreports.engine.JRExporterParameter")->JASPER_PRINT, $jasperPrint);
                    $exporter->setParameter(\java("net.sf.jasperreports.engine.JRExporterParameter")->OUTPUT_FILE_NAME, $outputPath);
                } catch (\JavaException $ex) {
                    echo $ex;
                }

                header("Content-type: application/vnd.oasis.opendocument.text");
                header("Content-Disposition: attachment; filename={$this->filename}.odt");
                break;
            case 'txt':
                $outputPath = tempnam(realpath("tmp"), $this->filename);  //generate unique temp file name    
                chmod($outputPath, 0766);
                try {
                    $exporter = new \java("net.sf.jasperreports.engine.export.JRTextExporter");
                    $exporter->setParameter(\java("net.sf.jasperreports.engine.export.JRTextExporterParameter")->PAGE_WIDTH, 120);
                    $exporter->setParameter(\java("net.sf.jasperreports.engine.export.JRTextExporterParameter")->PAGE_HEIGHT, 60);
                    $exporter->setParameter(\java("net.sf.jasperreports.engine.JRExporterParameter")->JASPER_PRINT, $jasperPrint);
                    $exporter->setParameter(\java("net.sf.jasperreports.engine.JRExporterParameter")->OUTPUT_FILE_NAME, $outputPath);
                } catch (\JavaException $ex) {
                    echo $ex;
                }

                header("Content-type: text/plain");
                break;
            case 'rtf':
                $outputPath = tempnam(realpath("tmp"), $this->filename);  //generate unique temp file name    
                chmod($outputPath, 0766);

                try {
                    $exporter = new \java("net.sf.jasperreports.engine.export.JRRtfExporter");
                    $exporter->setParameter(\java("net.sf.jasperreports.engine.JRExporterParameter")->JASPER_PRINT, $jasperPrint);
                    $exporter->setParameter(\java("net.sf.jasperreports.engine.JRExporterParameter")->OUTPUT_FILE_NAME, $outputPath);
                } catch (\JavaException $ex) {
                    echo $ex;
                }

                header("Content-type: application/rtf");
                header("Content-Disposition: attachment; filename={$this->filename}.rtf");
                break;
            case 'pptx':
                $outputPath = tempnam(realpath("tmp"), $this->filename);  //generate unique temp file name    
                chmod($outputPath, 0766);
                try {
                    $exporter = new \java("net.sf.jasperreports.engine.export.ooxml.JRPptxExporter");
                    $exporter->setParameter(\java("net.sf.jasperreports.engine.JRExporterParameter")->JASPER_PRINT, $jasperPrint);
                    $exporter->setParameter(\java("net.sf.jasperreports.engine.JRExporterParameter")->OUTPUT_FILE_NAME, $outputPath);
                } catch (\JavaException $ex) {
                    echo $ex;
                }

                header("Content-type: application/vnd.ms-powerpoint");
                header("Content-Disposition: attachment; filename={$this->filename}.pptx");
                break;
            /* 	It exists
              case 'swf':
              $outputPath = tempnam(realpath("tmp"), $this->filename);  //generate unique temp file name    

              try {
              $exporter = new \java("net.sf.jasperreports.engine.export.JRXml4SwfExporter");
              $exporter->setParameter(\java("net.sf.jasperreports.engine.JRExporterParameter")->JASPER_PRINT, $jasperPrint);
              $exporter->setParameter(\java("net.sf.jasperreports.engine.JRExporterParameter")->OUTPUT_FILE_NAME, $outputPath);
              } catch (\JavaException $ex) {
              echo $ex;
              }

              header("Content-type: application/swf");
              header("Content-Disposition: attachment; filename={$this->filename}.swf");
              $exporter->exportReport();
              break;
             */
        }
        
        $exporter->exportReport();

        readfile($outputPath);
        unlink($outputPath);
    }

    static function convertValue($value, $className) 
    {
        // if we are a string, just use the normal conversion  
        // methods from the java extension...  
        //Revisar http://byte-consult.be/2008/08/16/phpjava-bridge-jasperreports/
        try {
            if ($className == 'java.lang.String') {
                $temp = new \java('java.lang.String', $value);
                return $temp;
            } else if ($className == 'java.lang.Boolean' ||
                    $className == 'java.lang.Integer' ||
                    $className == 'java.lang.Long' ||
                    $className == 'java.lang.Short' ||
                    $className == 'java.lang.Double' ||
                    $className == 'java.math.BigDecimal') {
                $temp = new \java($className, $value);
                return $temp;
            } else if ($className == 'java.sql.Timestamp' || $className == 'java.sql.Time') {
                $temp = new \java($className);
                $javaObject = $temp->valueOf($value);
                return $javaObject;
            } else if ($className == 'java.util.Locale') {
                $value_arr = explode("_", $value);
                $temp = new \java($className, $value_arr[0], $value_arr[1]);
                return $temp;
            } else if ($className == "java.util.Date") {
                $temp = new \java('java.text.DateFormat');
                $javaObject = $temp->parse($value);
                return $javaObject;
            }
        } catch (Exception $err) {
            echo ( 'unable to convert value, ' . $value .
            ' could not be converted to ' . $className . ' ');
            return false;
        }

        echo ( 'unable to convert value, class name ' . $className .
        ' not recognised');
        
        return false;
    }

    static function Jdate($dia, $mes, $ano) 
    {
        $date = new \java("java.util.Date", abs($ano - 1900), abs($mes - 1), abs($dia));
        return $date;
    }
    
    static function ArrayList($ar_data)
    {
        $arrayList = new \java( 'java.util.ArrayList' );
        foreach( $ar_data as $value ) {
          $arrayList->add( $value );
        }
        
        return $arrayList;
    }
}