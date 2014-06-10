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
include_once 'generate.php';

if (!empty($_REQUEST) && count($_REQUEST) > 0) {
    $query = "SELECT customer.first_name AS customer_first_name,
                                customer.last_name AS customer_last_name,
                                customer.email AS customer_email
                         FROM  customer customer";
    
    $obJrepor = new Jreport($query, 'customer.jrxml', array('title'=>'Customer'), 'report');
    $obJrepor->exportar($_REQUEST['format']);
}

?>
<!DOCTYPE html>
<html>
<head>
<title>Jasper Report Example</title>
</head>
<body>
<form name="form_jasper" method="post" action="#">
<select name="format">
    <option value="">Select</option>
	<option value="xls">XLS</option>
	<option value="csv">CSV</option>
	<option value="docx">DOCX</option>
	<option value="html">HTML</option>
	<option value="pdf">PDF</option>
	<option value="ods">ODS</option>
	<option value="odt">ODT</option>
	<option value="txt">TXT</option>
        <option value="rtf">RTF</option>
        <option value="pptx">PPTX</option>
</select>
<br /><br />
	<input type="submit" value="Generate"/>
</body>
</html>
