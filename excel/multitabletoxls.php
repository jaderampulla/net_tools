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

$excelpropertiesar=$_SESSION['excelpropertiesar'];
if(!is_array($excelpropertiesar)){
	$excelpropertiesar=array(
		 "setTitle"=>"PHP to Excel Export",
		 "setSubject"=>"PHP to Excel Export",
		 "setDescription"=>"PHP to Excel Export",
		 "setKeywords"=>"PHP to Excel Export",
		 "setCategory"=>"PHP to Excel Export",
		 "filename"=>"export.xlsx"
	);
}
// Set properties
$objPHPExcel->getProperties()->setCreator("Jade Rampulla")
							 ->setLastModifiedBy("Jade Rampulla")
							 ->setSubject($excelpropertiesar['setSubject'])
							 ->setDescription($excelpropertiesar['setDescription'])
							 ->setKeywords($excelpropertiesar['setKeywords'])
							 ->setCategory($excelpropertiesar['setCategory']);

//Get info from session variables
//Excelar is an array that contains inside arrays. Each inside array is a table.
//Within each inside array is an array of headers and data.
//The header array is column headings
//Inside the data array are small arrays of data. Each of those arrays is a row of data. Each value is a cell in the row
//Arrays inside cells are supported! Avoid sending an array if the cell is a single value. The cell properties are set to be text wrap
/*
EXAMPLE:
		
Array		***** The outside array (excelar) *****
(
    [0] => Array		***** Inside array - a table *****
        (
            [0] => Array		***** header array for inside table *****
                (
                    [0] => Device Info		***** Column heading for table *****
                    [1] => Value			***** Column heading for table *****
                )
            [1] => Array		***** data array for inside table *****
                (
                    [0] => Array		***** Row of data *****
                        (
                            [0] => System Name:				***** Cell of data *****
                            [1] => 595_5650TD_Core_1		***** Cell of data *****
                        )
                    [1] => Array		***** Row of data *****
                        (
                            [0] => System Description:																			***** Cell of data *****
                            [1] => Ethernet Routing Switch 5650TD HW:05 FW:6.0.0.18 SW:v6.3.2.011 BN:11 (c) Avaya Networks 		***** Cell of data *****
                        )
                )
        )
    [1] => Array		***** Inside array - a table *****
        (
            [0] => Array		***** header array for inside table *****
                (
                    [0] => Description				***** Column heading for table *****
                    [1] => Alias					***** Column heading for table *****
                    [2] => Admin Status				***** Column heading for table *****
                    [3] => Operational Status		***** Column heading for table *****
                    [4] => Speed (In mbps)			***** Column heading for table *****
                    [5] => Duplex					***** Column heading for table *****
                )
            [1] => Array		***** data array for inside table *****
                (
                    [0] => Array		***** Row of data *****
                        (
                            [0] => Port 1 							***** Cell of data *****
                            [1] => IST to 595_5650TD_Core_2 		***** Cell of data *****
                            [2] => up 								***** Cell of data *****
                            [3] => up 								***** Cell of data *****
                            [4] => 1000 							***** Cell of data *****
                            [5] => Full 							***** Cell of data *****
                        )
                    [1] => Array		***** Row of data *****
                        (
                            [0] => Port 2 							***** Cell of data *****
                            [1] => IST to 595_5650TD_Core_2 		***** Cell of data *****
                            [2] => up 								***** Cell of data *****
                            [3] => up 								***** Cell of data *****
                            [4] => 1000 							***** Cell of data *****
                            [5] => Full 							***** Cell of data *****
                        )
                )
        )
)
*/
$excelar=$_SESSION['excelar'];
$freezepanearnum=$_SESSION['freezepanearnum'];
//echo "<pre>";
//print_r($excelar);
//echo "</pre>";
//Loop through each table of the Excel array
$rowcounter=1;
$rowbegin=1;
$freezepanefound=0;
foreach($excelar as $arnum=>$table){
	//Determine freeze pane row
	if($freezepanearnum==$arnum){
		$freezepanefound=$rowcounter+1;
	}
	//Determine beginning row number - Used for bolding headings and outlining tables
	if($rowcounter>1) $rowbegin=$rowcounter;
	$headernum=count($table[0]);
	$headeralph=array();
	$tmpheadercnt=1;
	//If a table header wasn't provided
	$headerprovided=true;
	if(!($headernum>0)){
		$headernum=count($table[1][0]);
		$headerprovided=false;
	}
	foreach(range('A','Z') as $i) {
		if($tmpheadercnt<=$headernum){
			$headeralph[]=$i;
		}
		$tmpheadercnt+=1;
	}
	//Column headings
	$tmpcnt=0;
	foreach($headeralph as $alph){
		if($headerprovided==true){
			$objPHPExcel->setActiveSheetIndex(0)->setCellValue("$alph$rowcounter",$table[0][$tmpcnt]);
		} else {
			$objPHPExcel->setActiveSheetIndex(0)->setCellValue("$alph$rowcounter","Header");
		}
		$tmpcnt+=1;
	}

	/*** Start Add Data Section ***/
	$rowcounter+=1;
	//Loop through each line of data
	foreach($table[1] as $dataouter){
		//Loop through the data in each line
		foreach($dataouter as $d=>$data){
			//If a cell has an array of data, grab each element of the array and put it in a single string with a line return after each array element
			//Code here for multiple lines in a cell and text wrapping: http://stackoverflow.com/questions/5960242/how-to-make-new-lines-in-a-cell-using-phpexcel
			unset($tmpdata);
			if(is_array($data)){
				$tmpsize=sizeof($data);
				$tmpcnt=1;
				foreach($data as $tmpd){
					//Don't add a line return for the last line
					if($tmpcnt==$tmpsize){
						//echo "TMPDATA: $tmpdata, TMPSIZE: $tmpsize - NO RETURN<br />\n";
						$tmpdata=$tmpdata . "$tmpd";
					} else {
						//echo "TMPDATA: $tmpdata, TMPSIZE: $tmpsize<br />\n";
						$tmpdata=$tmpdata . "$tmpd\n";
					}
					$tmpcnt+=1;
				}
				$val=$tmpdata;
				//Store the cells that have an array of values for text wrapping formatting
				$wraptextar[]=$headeralph[$d] . "$rowcounter";
			} else {
				$val=$data;
			}
			//Correct a few formatting issues
			$val=preg_replace('/&#47;/','/',$val);
			$val=preg_replace('/<br>/','',$val);
			$val=preg_replace('/&#60;/','<',$val);
			$val=preg_replace('/&#62;/','>',$val);
			$val=preg_replace('/&nbsp;/',' ',$val);
			//Add the data to the cell
			$objPHPExcel->setActiveSheetIndex(0)->setCellValue($headeralph[$d] . "$rowcounter", "$val");
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
	$objPHPExcel->getActiveSheet()->setSharedStyle($mytablestyle, "A$rowbegin:" . $headeralph[$headernum-1] . "$rowcountertemp");
	//Set vertical alignment to the middle on all cells
	$objPHPExcel->getActiveSheet()->getStyle("A$rowbegin:" . $headeralph[$headernum-1] . "$rowcountertemp")->getAlignment()->setVertical(PHPExcel_Style_Alignment::VERTICAL_CENTER);

	//Bold and set font size for headings
	foreach($headeralph as $alph){
		$objPHPExcel->getActiveSheet()->getStyle("$alph$rowbegin")->getFont()->setSize(15); 
		$objPHPExcel->getActiveSheet()->getStyle("$alph$rowbegin")->getFont()->setBold(true);
		//Background color for cell here: http://stackoverflow.com/questions/7975381/phpexcel-how-to-get-a-cell-color
		$objPHPExcel->getActiveSheet()->getStyle("$alph$rowbegin")->getFill()->applyFromArray(array('type' => PHPExcel_Style_Fill::FILL_SOLID,'startcolor' => array('rgb' =>'c0c0c0')));
	}
	foreach($wraptextar as $wraptext){
		$objPHPExcel->getActiveSheet()->getStyle("$wraptext")->getAlignment()->setWrapText(true);
	}
	//Add a blank row for spacing between multiple tables
	$rowcounter+=1;
}
//If a freeze pane was specified
//Code here: https://phpexcel.codeplex.com/discussions/26843
if($freezepanefound>0){
	$objPHPExcel->getActiveSheet()->freezePane("A$freezepanefound");
//If there's only 1 table with a header array and data array inside, freeze the top row which contains the column headings
} else if(count($excelar)==1 && count($excelar[0])==2){
	$objPHPExcel->getActiveSheet()->freezePane('A2');
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
$objPHPExcel->getActiveSheet()->setTitle($excelpropertiesar['setTitle']);

// Set active sheet index to the first sheet, so Excel opens this as the first sheet
$objPHPExcel->setActiveSheetIndex(0);
//echo "<pre>";
//print_r($objPHPExcel);

// Redirect output to a client’s web browser (Excel2007)
header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
header("Content-Disposition: attachment;filename=\"{$excelpropertiesar['setSubject']}\"");
header('Cache-Control: max-age=0');

$objWriter = PHPExcel_IOFactory::createWriter($objPHPExcel, 'Excel2007');
$objWriter->save('php://output');

exit;
?>