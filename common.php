<?php
include_once 'debug.php';
function dprintf($format, $a='', $b='', $c='', $d='', $e='', $f='')
{
	global $debug;
	if($debug == 1)
		printf($format, $a, $b, $c, $d, $e, $f);
}
function dprint($format)
{
	global $debug;
	if($debug == 1)
		print($format);
}
function get_client_ip(){
	if (getenv("HTTP_CLIENT_IP") && strcasecmp(getenv("HTTP_CLIENT_IP"), "unknown"))
		$ip = getenv("HTTP_CLIENT_IP");
	else if (getenv("HTTP_X_FORWARDED_FOR") && strcasecmp(getenv("HTTP_X_FORWARDED_FOR"), "unknown"))
		$ip = getenv("HTTP_X_FORWARDED_FOR");
	else if (getenv("REMOTE_ADDR") && strcasecmp(getenv("REMOTE_ADDR"), "unknown"))
		$ip = getenv("REMOTE_ADDR");
	else if (isset($_SERVER['REMOTE_ADDR']) && $_SERVER['REMOTE_ADDR'] && strcasecmp($_SERVER['REMOTE_ADDR'], "unknown"))
		$ip = $_SERVER['REMOTE_ADDR'];
	else
		$ip = "unknown";
	return($ip);
}

function get_cur_php(){
	if(!isset($_SERVER['HTTP_HOST']))
		return '';
	$mail_url = "http://" . $_SERVER['HTTP_HOST'];
	$mail_url .= $_SERVER['PHP_SELF'];
	return $mail_url;
}

function get_cur_root(){
	return dirname(get_cur_php());
}

function get_cur_month(){
	$tm = time();
	$date = getdate($tm);
	return  $date['mon'];
}

function get_cur_year(){
	$tm = time();
	$date = getdate($tm);
	return  $date['year'];
}

function strip($str){
	$reg = "/^\s*([^\s][^\s\r\n]*)\s*$/";
	if(preg_match($reg, $str, $match))
		return $match[1];
	return $str;
}

function read_mysql_query($sql)
{
	($res = mysql_query($sql)) or die("Invalid read query:" . $sql ."<br>\n". mysql_error());
	return $res;
}

function update_mysql_query($sql)
{
	($res = mysql_query($sql)) or die("Invalid update query:" . $sql . "<br>\n" .mysql_error());
	return $res;
}

function update_mysql_query2($sql)
{

	$link=mysql_connect("localhost","bookweb","book2web");
	mysql_query("set character set 'utf8'");//..
	mysql_query("set names 'utf8'");//.. 
	$db=mysql_select_db("testbook",$link);
	$res = mysql_query($sql) or die("Invalid query:" . $sql . mysql_error());

	$link=mysql_connect("cedump-sh.ap.qualcomm.com","bookweb","book2web");
	$db=mysql_select_db("testbook",$link);
	mysql_query("set character set 'utf8'");//..
	mysql_query("set names 'utf8'");//.. 
}

function delay_back($url, $msec=1000)
{
	print("<script type=\"text/javascript\">setTimeout(\"window.location.href='$url'\",$msec);</script>");
}

function mail_html($to, $cc, $subject, $message)
{
	global $debug_mail, $debug;
	$headers = 'From: book@cedump-sh.ap.qualcomm.com' . "\r\n" .
	    'Reply-To: xling@qti.qualcomm.com' . "\r\n" .
	    'X-Mailer: PHP/' . phpversion();
	$headers  = 'MIME-Version: 1.0' . "\r\n";
	$headers .= 'Content-type: text/html; charset=utf-8' . "\r\n";
	if($debug == 1){
		$message .= "\r\n To:$to, CC:$cc";
		$cc = 'xling@qti.qualcomm.com';
		$to = 'xling@qti.qualcomm.com';
	}
	if($cc)
		$headers .= "Cc: $cc" . "\r\n";
	if(isset($debug_mail) && $debug_mail == 1)
		$headers .= "Bcc: xling@qti.qualcomm.com" . "\r\n";

	dprint("mail|to:$to|cc:$cc|". htmlentities($subject, ENT_COMPAT, 'utf-8') . "<br>\n");
//	print("$message\n");
	mail($to,$subject, $message, $headers);

}

function visit_record($table, $ver='')
{
	$ip = get_client_ip();
	if($ver != '')
		$ip = "$ver:$ip";
	$sql = "insert into $table set `ip` = '$ip', `ver` = '$ver', `times` = 1 on duplicate key update `times` = `times` + 1";
	$res = mysql_query($sql) or die("Invalid update query:" . $sql . mysql_error());
}

function get_url_var($name, $default)
{
	$var=isset($_GET[$name])?$_GET[$name]:$default;
	$var=isset($_POST[$name])?$_POST[$name]:$default;
	return $var;
}

function get_persist_var($name, $default)
{
	$var=isset($_SESSION[$name])?$_SESSION[$name]:$default;
	$var=isset($_GET[$name])?$_GET[$name]:$var;
	$var=isset($_POST[$name])?$_POST[$name]:$var;
	$_SESSION[$name] = $var;
	return $var;
}

function import_excel_file($import_file, $db, $tbname, $key, $trans_array, $more='', $time='')
{
	$tables = array();
	$col_names = array();

	$fields = mysql_list_fields($db, $tbname);
	$columns = mysql_num_fields($fields);
	$table_fields  = array();

	for ($i = 0; $i < $columns; $i++) {
		$table_fields[] = mysql_field_name($fields, $i);
	}	
	if(substr_count($import_file, '.xlsx') || substr_count($import_file, '.xlsm') ){
		$xlsx = true;
		print "  --  xlsx file<br>\n";
	}
	else
	{
		$xlsx = false;
		print " -- old xls file<br>\n";
	}

	/* Append the PHPExcel directory to the include path variable */
	set_include_path(get_include_path() . PATH_SEPARATOR . getcwd() . '/PHPExcel/');
	require_once 'PHPExcel/PHPExcel.php';
	if($xlsx){
		require_once 'PHPExcel/PHPExcel/Reader/Excel2007.php';
		$objReader = new PHPExcel_Reader_Excel2007();
	}else{
		require_once 'PHPExcel/PHPExcel/Reader/Excel5.php';
		$objReader = new PHPExcel_Reader_Excel5();
	}

	$objReader->setReadDataOnly(true);
	$objReader->setLoadAllSheets();
	$objPHPExcel = $objReader->load($import_file);
	
	$sheet_names = $objPHPExcel->getSheetNames();
	$num_sheets = count($sheet_names);
	$sheet_names[0] = $tbname;
	
	$num_sheets = 1;

	//for ($s = 0; $s < $num_sheets; ++$s) {
	$s = 0;

    $current_sheet = $objPHPExcel->getSheet($s);
    
    $num_rows = $current_sheet->getHighestRow();
    $num_cols = PMA_getColumnNumberFromName($current_sheet->getHighestColumn());
   	print "excel line x col : $num_rows x $num_cols<br>\n"; 
	$url = "";
	$begin_rol = 1;
	if($num_cols == 1 || $num_cols == 2){
		$num_cols = 50;
	}

    $cellobj = $current_sheet->getCellByColumnAndRow(0, 1);
    $cell = $current_sheet->getCellByColumnAndRow(0, 1)->getCalculatedValue();
	$update = 0;
	$duplicate = 0;
	$new = 0;
	$incount = 0;
	flush();
    if ($num_rows != 1 && $num_cols != 0) {
        for ($r = $begin_rol; $r <= $num_rows; ++$r) {
            $tempRow = array();
			$rows = array();
            for ($c = 0; $c < $num_cols; ++$c) {
                $cellobj = $current_sheet->getCellByColumnAndRow($c, $r);
                $cell = $current_sheet->getCellByColumnAndRow($c, $r)->getCalculatedValue();
                if (! strcmp($cell, '')) {
					if($r == 1){
						$num_cols = $c;
						break;
					}
                    $cell = 'NULL';
                }
                $tempRow[] = $cell;
            }
			if($r==$begin_rol){
				$colnames = $tempRow; 
				foreach($colnames as $colname){
					if(!in_array($colname, $table_fields)){
						if(!isset($trans_array[$colname]))
							print("Skip unknow database field: $colname<br>\n");
						else{
							$old = $colname;
							$colname = $trans_array[$colname];
							print(" transer $old to $colname<br>");
						}
					}else{
						#print("field:$colname<br>\n");
					}
				}

				flush();
			}else{
				$sql_cmd = "insert into $tbname set ";
				$sql = "";
				$i = 0;
				$emptyline = false;
				$owner = false;
				$onwer_email = false;
				#print "$tempRow[$i],$tempRow[3]<br>";
				$first = 0;
				foreach($colnames as $colname){
					$cell = $tempRow[$i];
					$i += 1;
					if(!in_array($colname, $table_fields)){
						if(!isset($trans_array[$colname]))
							continue;
						else{
							$colname = $trans_array[$colname];
						}
					}
					if($colname == $key)
						$keyvalue = $cell;
					$rows[$colname] = $cell;	
					dprint("$colname:$cell<br>");
					$cell = str_replace("'", "''", $cell);
					$cell = str_replace("\\", "\\\\", $cell);
					if($first == 0)
						$sql .= " `$colname` = '$cell' " ;
					else
						$sql .= " , `$colname` = '$cell' " ;
					$first++;
				}
				if($emptyline){
					print "skip empty line<br>\n";
					$emptyline = false;
				}else{
					dprint("$sql<br>\n");
					if($more != '')
						$sql .= ", $more ";
					if($time != '')
						$sql .= ", import_time = '$time' ";
					$sql_insert = $sql_cmd . $sql;
					$res1=mysql_query($sql_insert);
					if(!$res1)
					{
					//	print("Fail:$r:${rows['kba_id']}, ${rows['status']}, ${rows['author']} <br>");
					//	print("Failed query:" . $sql . mysql_error());
						$sql_replace = "update $tbname set " . $sql." where `$key` = '$keyvalue'";
						$res=update_mysql_query($sql_replace);
						$up = mysql_affected_rows();
						$update += $up/2;
						$duplicate++;
					}else{
						$new++;
					};
					$incount++;
					if(($r % 1000) == 0){
						print("Done $r line<br>\n");
						flush();
					}
				}
			}
            $rows[] = $tempRow;
        }
		print("New: $new  Duplicate: $duplicate  Update: $update");
        $tables[] = array($sheet_names[$s], $col_names, $rows);
 		$done = true;       
		unset($objPHPExcel);
		unset($objReader);
		unset($rows);
		unset($tempRow);

	}
	return $incount;
}

function export_excel_by_sql($sql, $filename, $title, $width=array()) 
{  
	$result = read_mysql_query($sql);
	$field_name = array();
	$cols = mysql_num_fields($result);
	for ($i = 0; $i < $cols;  ++$i) {
		$field = mysql_field_name($result, $i);
		$field_name[] = $field;
	}
	set_include_path(get_include_path() . PATH_SEPARATOR . getcwd() . '/PHPExcel/');
	require_once 'PHPExcel/PHPExcel.php';
	//require_once 'php/libraries/PHPExcel/PHPExcel/Reader/Excel5.php';
	//require_once 'php/libraries/PHPExcel/PHPExcel/Reader/Excel2007.php';

        // Create new PHPExcel object    
    $objPHPExcel = new PHPExcel();  
    // Set properties    
    $objPHPExcel->getProperties()->setCreator("sfrule")  
            ->setLastModifiedBy("cedump-sh")  
            ->setTitle($title)  
            ->setSubject("Office 2007 XLSX ")  
            ->setDescription("Test document for Office 2007 XLSX, generated using PHP classes.")  
            ->setKeywords("office 2007 openxml php")  
            ->setCategory("Test result file");  
    
    // set width    
    // .....  
    $objPHPExcel->getActiveSheet()->getDefaultStyle()->getFont()->setSize(10);  
    
    // ......    
	$wc = count($width);
	$sheet = $objPHPExcel->getActiveSheet(0);
	for($j = 0; $j < $cols; $j++){
		$cell0 = chr(ord('A')+$j);
		$cell = chr(ord('A')+$j) . "1";
		if($j < $wc)
			$w = $width[$j];
		else
			$w = 5;
    	$objPHPExcel->getActiveSheet()->getColumnDimension($cell0)->setWidth($w);  
        $sheet->setCellValue($cell, $field_name[$j]);  
    	$objPHPExcel->getActiveSheet()->getStyle($cell)->getAlignment()->setHorizontal(PHPExcel_Style_Alignment::HORIZONTAL_CENTER);  
	}
	$sheet->getStyle('A' . (1) . ':'.$cell )->getBorders()->getAllBorders()->setBorderStyle(PHPExcel_Style_Border::BORDER_THIN);  
	$sheet->getStyle('A' . (1) . ':'.$cell )->getFont()->setBold(true);  
    $sheet->getRowDimension('1')->setRowHeight(22);  
    
    //  ..  
//    $objPHPExcel->getActiveSheet()->mergeCells('A1:D1');  
    // ..  
	$i = 0;
	while(($rows = mysql_fetch_array($result))){
		for($j = 0; $j < $cols; $j++){
			$cell = chr(ord('A')+$j) . ($i+2);
			#print $cell;
			$objPHPExcel->getActiveSheet(0)->setCellValue($cell, $rows[$j]);  
		}
 	
		$sheet->getStyle('A' . ($i + 2) . ':'.$cell )->getAlignment()->setVertical(PHPExcel_Style_Alignment::HORIZONTAL_CENTER);  
		$sheet->getStyle('A' . ($i + 2) . ':'.$cell )->getAlignment()->setVertical(PHPExcel_Style_Alignment::VERTICAL_CENTER);  
		$sheet->getStyle('A' . ($i + 2) . ':'.$cell )->getBorders()->getAllBorders()->setBorderStyle(PHPExcel_Style_Border::BORDER_THIN); 
		$sheet->getRowDimension($i + 2)->setRowHeight(16);  

		$i++;
    }  
    
   //  Rename sheet    
    $sheet->setTitle($title);  
    
    // Set active sheet index to the first sheet, so Excel opens this as the first sheet    
    $objPHPExcel->setActiveSheetIndex(0);  
    
    // ..  
    header('Content-Type: application/vnd.ms-excel');  
    header('Content-Disposition: attachment;filename="' .$filename);  
    header('Cache-Control: max-age=0');  
    
    $objWriter = PHPExcel_IOFactory::createWriter($objPHPExcel, 'Excel5');  
    $objWriter->save('php://output');  
}

?>
