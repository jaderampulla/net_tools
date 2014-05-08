<?php
/**
 * PHPExcel
 *
 * Copyright (C) 2006 - 2010 PHPExcel
 *
 * This library is free software; you can redistribute it and/or
 * modify it under the terms of the GNU Lesser General Public
 * License as published by the Free Software Foundation; either
 * version 2.1 of the License, or (at your option) any later version.
 *
 * This library is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the GNU
 * Lesser General Public License for more details.
 *
 * You should have received a copy of the GNU Lesser General Public
 * License along with this library; if not, write to the Free Software
 * Foundation, Inc., 51 Franklin Street, Fifth Floor, Boston, MA  02110-1301  USA
 *
 * @category   PHPExcel
 * @package    PHPExcel
 * @copyright  Copyright (c) 2006 - 2010 PHPExcel (http://www.codeplex.com/PHPExcel)
 * @license    http://www.gnu.org/licenses/old-licenses/lgpl-2.1.txt	LGPL
 * @version    1.7.4, 2010-08-26
 */

/** Error reporting */
//error_reporting(0);
error_reporting(E_ALL);
date_default_timezone_set('America/New_York');

/** PHPExcel */
require_once 'Classes/PHPExcel.php';

session_start();

// Create new PHPExcel object
$objPHPExcel = new PHPExcel();

// Set properties
$objPHPExcel->getProperties()->setCreator("Jade Rampulla")
							 ->setLastModifiedBy("Jade Rampulla")
							 ->setTitle("Syslog Export")
							 ->setSubject("Syslog Export")
							 ->setDescription("Syslog Export")
							 ->setKeywords("Syslog Export")
							 ->setCategory("Syslog Export");

//Get info from session variables
$headerar=$_SESSION['headerar'];
$dataar=$_SESSION['dataar'];
//echo "<pre>";
//print_r($headerar);
//print_r($dataar);

$headernum=count($headerar);
$headeralph=array();
$tmpheadercnt=1;
foreach(range('A','Z') as $i) {
	if($tmpheadercnt<=$headernum){
		$headeralph[]=$i;
	}
	$tmpheadercnt+=1;
}
//Column headings
$tmpcnt=0;
foreach($headeralph as $alph){
	$objPHPExcel->setActiveSheetIndex(0)->setCellValue("$alph" . "1", $headerar[$tmpcnt]);
	$tmpcnt+=1;
}

/*** Start Add Data Section ***/
$rowcounter=2;
//Loop through each line of data
foreach($dataar as $dataouter){
	//Loop through the data in each line
	foreach($dataouter as $d=>$data){
		//Correct a few formatting issues
		$val=$data;
		$val=preg_replace('/&#47;/','/',$val);
		$val=preg_replace('/<br>/','',$val);
		$val=preg_replace('/&#60;/','<',$val);
		$val=preg_replace('/&#62;/','>',$val);
		//Add the data to the cell
		$objPHPExcel->setActiveSheetIndex(0)->setCellValue($headeralph[$d] . "$rowcounter", $val);
	}
	$rowcounter+=1;
}
/*** End Add Data Section ***/

/*** Start Custom Formatting ***/
//Auto size colums widths
foreach($headeralph as $alph){
	$objPHPExcel->getActiveSheet()->getColumnDimension($alph)->setAutoSize(true);
}

//Outline and set color background for table
$mytablestyle = new PHPExcel_Style();
$mytablestyle->applyFromArray(
	array(/*'fill' 	=> array(
		'type'		=> PHPExcel_Style_Fill::FILL_SOLID,
		'color'		=> array('argb' => 'c0c0c0')
	),*/
	'borders' => array(
		'top'	=> array('style' => PHPExcel_Style_Border::BORDER_THIN),
		'bottom'	=> array('style' => PHPExcel_Style_Border::BORDER_THIN),
		'right'		=> array('style' => PHPExcel_Style_Border::BORDER_THIN)
	),
	'alignment' => array(
		'horizontal' => PHPExcel_Style_Alignment::HORIZONTAL_LEFT,
	))
);
$rowcountertemp=$rowcounter-1;
$objPHPExcel->getActiveSheet()->setSharedStyle($mytablestyle, "A1:" . $headeralph[$headernum-1] . "$rowcountertemp");

//Bold and set font size for headings
foreach($headeralph as $alph){
	$objPHPExcel->getActiveSheet()->getStyle("$alph" . "1")->getFont()->setSize(15); 
	$objPHPExcel->getActiveSheet()->getStyle("$alph" . "1")->getFont()->setBold(true);
}

//All cells are in text format
/*

http://phpexcel.codeplex.com/discussions/30244

$objPHPExcel->getDefaultStyle()
    ->getNumberFormat()
    ->setFormatCode(PHPExcel_Style_NumberFormat::FORMAT_TEXT);

*/

/*** End Custom Formatting ***/
	
// Rename sheet
$objPHPExcel->getActiveSheet()->setTitle("Syslog Export");

// Set active sheet index to the first sheet, so Excel opens this as the first sheet
$objPHPExcel->setActiveSheetIndex(0);
//echo "<pre>";
//print_r($objPHPExcel);

// Redirect output to a client’s web browser (Excel2007)
header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
header('Content-Disposition: attachment;filename="syslog.xlsx"');
header('Cache-Control: max-age=0');


$objWriter = PHPExcel_IOFactory::createWriter($objPHPExcel, 'Excel2007');
$objWriter->save('php://output');


exit;
?>