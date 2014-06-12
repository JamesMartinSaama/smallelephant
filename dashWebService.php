<?php
/**
* Description: This file retrieves data for the dashboard section
* Author: David Lee
* Last Updated: 4/16/2014
**/
    require_once('FirePHPCore/fb.php');
    ob_start();
	//Determines what data to fetch for which dashboard
	$graphName =$_REQUEST['graphName']; 

	//DB connect 
	try {
	    $jsonArray = array();

		if (!$conn) {
			$error = 'There was an error in the DB connect';
			throw new Exception($error);
		}
	} 
	catch (Exception $e) {
		$errorMsg =  'Caught exception: '. $e->getMessage();
		array_push($jsonArray, $errorMsg);
	}
/////////////////////////////////////////////////////////////////////////////////////////////////////////////////////
//DASH 1
/////////////////////////////////////////////////////////////////////////////////////////////////////////////////////
	if ($graphName == 'dash1') {
		$jsonArray = array(
		'splunks' => array( ),
		'rsms' => array( ),
		'error' => array(),
		'comment' => array());
		
		$rsmsQuery =  "select TO_CHAR((WEB_UPDATE_DATE), 'MM/DD/YYYY'), WEB_UPDATE_IN_COUNT, WEB_UPDATE_OUT_COUNT from WEB_UPDATES where SYSTEM_ID = 1  order by WEB_UPDATE_DATE asc";
		try {
			$result = oci_parse($conn, $rsmsQuery);
			$r = oci_execute($result);
			if (!$r) {
				$error = 'There was an error in the rsmsQuery query';
				throw new Exception($error);
			}
		} 
		catch (Exception $e) {
			$errorMsg =  'Caught exception: '. $e->getMessage();
		    array_push($jsonArray['error'], $errorMsg);
		}
		while($row=oci_fetch_array($result))
		{
			$dateEpoch = strtotime($row[0]);
			array_push($jsonArray['rsms'], array((int)$dateEpoch*1000, (int)$row[2]));
		}
		
		$commentQuery =  "select TO_CHAR(WEB_UPDATE_DATE+ 15/24, 'MM/DD/YYYY HH24:MI:SS'), DATA_COMMENT from WEB_UPDATES where SYSTEM_ID = 1 and DATA_COMMENT is not null order by WEB_UPDATE_DATE asc";
		try {
			$result = oci_parse($conn, $commentQuery);
			$r = oci_execute($result);
			if (!$r) {
				$error = 'There was an error in the commentQuery query';
				throw new Exception($error);
			}
		} 
		catch (Exception $e) {
			$errorMsg =  'Caught exception: '. $e->getMessage();
		    array_push($jsonArray['error'], $errorMsg);
		}
		while($row=oci_fetch_array($result))
		{
			array_push($jsonArray['comment'], array($row[0], (string)$row[1]));
		}
		
		
		$splunksQuery =  "SELECT TO_CHAR((EDIT_BILL_DATE), 'MM/DD/YYYY'), EDIT_BILL_COUNT FROM EDIT_BILLING WHERE SYSTEM_ID = 7 ORDER BY EDIT_BILL_DATE ASC";
		try {
			$result2 = oci_parse($conn, $splunksQuery);
			$r = oci_execute($result2);
			if (!$r) {
				$error = 'There was an error in the splunksQuery query';
				throw new Exception($error);
			}
		} 
		catch (Exception $e) {
			$errorMsg =  'Caught exception: '. $e->getMessage();
		    array_push($jsonArray['error'], $errorMsg);
		}
		while($row=oci_fetch_array($result2))
		{
			$dateEpoch = strtotime($row[0]);
			array_push($jsonArray['splunks'], array((int)$dateEpoch*1000, (int)$row[1]));
		}
    } 
	
/////////////////////////////////////////////////////////////////////////////////////////////////////////////////////
//DASH 2 Initial
/////////////////////////////////////////////////////////////////////////////////////////////////////////////////////
	elseif ($graphName == 'dash2Start') {
	
		$jsonArray = array(
		'rsms' => array(),
		'eccs_service' => array(),
		'eccs' => array(),
		'crms' => array(),
		'error' => array(),
		'rsms_indicator' => array(),
		'eccs_indicator' => array(),
		'eccs_serv_indicator' => array(),
		'crms_indicator' => array()
		);
		$i =0;
		$inNum = 0;
		$outNum = 0;
		$failNum = 0;
		$rsmsQuery =  "select TO_CHAR(WEB_UPDATE_DATE, 'MM/DD/YYYY'), WEB_UPDATE_IN_COUNT, WEB_UPDATE_OUT_COUNT, WEB_UPDATE_FAIL_COUNT from WEB_UPDATES where SYSTEM_ID = 1 and WEB_UPDATE_DATE = (select max(WEB_UPDATE_DATE) from WEB_UPDATES where SYSTEM_ID =1) order by WEB_UPDATE_DATE asc";
		try {
			$result = oci_parse($conn, $rsmsQuery);
			$r = oci_execute($result);
			if (!$r) {
				$error = 'There was an error in the rsmsQuery query';
				throw new Exception($error);
			}
		} 
		catch (Exception $e) {
			$errorMsg =  'Caught exception: '. $e->getMessage();
		    array_push($jsonArray['error'], $errorMsg);
		}
		while($row=oci_fetch_array($result))
		{
		    if ($i == 0)
			{
				$firstDate = (string)$row[0];
			}
			$i=$i+1;
			$inNum = $inNum + (int)$row[1];
			$outNum = $outNum +(int)$row[2];
			$lastDate = (string)$row[0];
			$failNum = $failNum + (int)$row[3];
			$failPerc = (int)$row[3]/(int)$row[1];
			array_push($jsonArray['rsms'],array("FirstDate" => $firstDate, "LastDate" => $lastDate, "SuccessNum" => $outNum, "FailNum" => $failNum, "FailPerc" => $failPerc));
		}
		
		$i =0;
		$inNum = 0;
		$outNum = 0;
		$failNum = 0;
		$crmsQuery =  "select TO_CHAR(WEB_UPDATE_DATE, 'MM/DD/YYYY'), WEB_UPDATE_IN_COUNT, WEB_UPDATE_OUT_COUNT, WEB_UPDATE_FAIL_COUNT from WEB_UPDATES where SYSTEM_ID = 2 and WEB_UPDATE_DATE = (select max(WEB_UPDATE_DATE) from WEB_UPDATES where SYSTEM_ID =2) order by WEB_UPDATE_DATE asc";
		try {
			$result = oci_parse($conn, $crmsQuery);
			$r = oci_execute($result);
			if (!$r) {
				$error = 'There was an error in the crmsQuery query';
				throw new Exception($error);
			}
		} 
		catch (Exception $e) {
			$errorMsg =  'Caught exception: '. $e->getMessage();
		    array_push($jsonArray['error'], $errorMsg);
		}
		while($row=oci_fetch_array($result))
		{
		    if ($i == 0)
			{
				$firstDate = (string)$row[0];
			}
			$i=$i+1;
			$inNum = $inNum + (int)$row[1];
			$outNum = $outNum +(int)$row[2];
			$lastDate = (string)$row[0];
			$failNum = $failNum + (int)$row[3];
			$failPerc = (int)$row[3]/(int)$row[1];
			array_push($jsonArray['crms'],array("FirstDate" => $firstDate, "LastDate" => $lastDate, "SuccessNum" => $outNum, "FailNum" => $failNum, "FailPerc" => $failPerc));
		}
		
		//ecc 1, graph name = ecc
		
		$i =0;
		$inNum = 0;
		$outNum = 0;
		$failNum = 0;
		$eccsQuery =  "select TO_CHAR(WEB_UPDATE_DATE, 'MM/DD/YYYY'), WEB_UPDATE_IN_COUNT, WEB_UPDATE_OUT_COUNT, WEB_UPDATE_FAIL_COUNT from WEB_UPDATES where SYSTEM_ID = 4 and WEB_UPDATE_DATE = (select max(WEB_UPDATE_DATE) from WEB_UPDATES where SYSTEM_ID =4) order by WEB_UPDATE_DATE asc";
		try {
			$result = oci_parse($conn, $eccsQuery);
			$r = oci_execute($result);
			if (!$r) {
				$error = 'There was an error in the eccsQuery query';
				throw new Exception($error);
			}
		} 
		catch (Exception $e) {
			$errorMsg =  'Caught exception: '. $e->getMessage();
		    array_push($jsonArray['error'], $errorMsg);
		}
		while($row=oci_fetch_array($result))
		{
		    if ($i == 0)
			{
				$firstDate = (string)$row[0];
			}
			$i=$i+1;
			$inNum = $inNum + (int)$row[1];
			$outNum = $outNum +(int)$row[2];
			$lastDate = (string)$row[0];
			$failNum = $failNum + (int)$row[3];
			$failPerc = (int)$row[3]/(int)$row[1];
			array_push($jsonArray['eccs'],array("FirstDate" => $firstDate, "LastDate" => $lastDate, "SuccessNum" => $outNum, "FailNum" => $failNum, "FailPerc" => $failPerc));
		}
		
		
		//ECC 2, graph name = ecc service handler
		
		$i =0;
		$inNum = 0;
		$outNum = 0;
		$failNum = 0;
		$eccsServiceQuery =  "select TO_CHAR(WEB_UPDATE_DATE, 'MM/DD/YYYY'), WEB_UPDATE_IN_COUNT, WEB_UPDATE_OUT_COUNT, WEB_UPDATE_FAIL_COUNT from WEB_UPDATES where SYSTEM_ID = 9 and WEB_UPDATE_DATE = (select max(WEB_UPDATE_DATE) from WEB_UPDATES where SYSTEM_ID =9) order by WEB_UPDATE_DATE asc";
		try {
			$result = oci_parse($conn, $eccsServiceQuery);
			$r = oci_execute($result);
			if (!$r) {
				$error = 'There was an error in the eccsServiceQuery query';
				throw new Exception($error);
			}
		} 
		catch (Exception $e) {
			$errorMsg =  'Caught exception: '. $e->getMessage();
		    array_push($jsonArray['error'], $errorMsg);
		}
		while($row=oci_fetch_array($result))
		{
		    if ($i == 0)
			{
				$firstDate = (string)$row[0];
			}
			$i=$i+1;
			$inNum = $inNum + (int)$row[1];
			$outNum = $outNum +(int)$row[2];
			$lastDate = (string)$row[0];
			$failNum = $failNum + (int)$row[3];
			$failPerc = (int)$row[3]/(int)$row[1];
			array_push($jsonArray['eccs_service'],array("FirstDate" => $firstDate, "LastDate" => $lastDate, "SuccessNum" => $outNum, "FailNum" => $failNum, "FailPerc" => $failPerc));
		}
		
		//RSMS FOR INDICATOR, GOES BACK 30 DAYS...
		$i =0;
		$inNum = 0;
		$outNum = 0;
		$failNum = 0;
		$rsmsQuery =  "select TO_CHAR((WEB_UPDATE_DATE), 'MM/DD/YYYY'), WEB_UPDATE_IN_COUNT, WEB_UPDATE_OUT_COUNT, WEB_UPDATE_FAIL_COUNT from WEB_UPDATES where SYSTEM_ID = 1  and WEB_UPDATE_DATE >= (select (max(WEB_UPDATE_DATE-30)) from WEB_UPDATES where SYSTEM_ID = 1) ORDER BY WEB_UPDATE_DATE";
		try {
			$result = oci_parse($conn, $rsmsQuery);
			$r = oci_execute($result);
			if (!$r) {
				$error = 'There was an error in the rsmsQuery query';
				throw new Exception($error);
			}
		} 
		catch (Exception $e) {
			$errorMsg =  'Caught exception: '. $e->getMessage();
		    array_push($jsonArray['error'], $errorMsg);
		}
		while($row=oci_fetch_array($result))
		{
		    if ($i == 0)
			{
				$firstDate = (string)$row[0];
			}
			$i=$i+1;
			$inNum = $inNum + (int)$row[1];
			$outNum = $outNum +(int)$row[2];
			$lastDate = (string)$row[0];
			$failNum = $failNum + (int)$row[3];
			$failPerc = (int)$row[3]/(int)$row[1];
			array_push($jsonArray['rsms_indicator'],array("FirstDate" => $firstDate, "LastDate" => $lastDate, "SuccessNum" => $outNum, "FailNum" => $failNum, "FailPerc" => $failPerc));
		}
		
		
		//ECCS INDICATOR
		$i =0;
		$inNum = 0;
		$outNum = 0;
		$failNum = 0;
		$eccsQuery =  "select TO_CHAR((WEB_UPDATE_DATE), 'MM/DD/YYYY'), WEB_UPDATE_IN_COUNT, WEB_UPDATE_OUT_COUNT, WEB_UPDATE_FAIL_COUNT from WEB_UPDATES where SYSTEM_ID = 4  and WEB_UPDATE_DATE >= (select (max(WEB_UPDATE_DATE-30)) from WEB_UPDATES where SYSTEM_ID = 4) ORDER BY WEB_UPDATE_DATE";
		try {
			$result = oci_parse($conn, $eccsQuery);
			$r = oci_execute($result);
			if (!$r) {
				$error = 'There was an error in the eccsQuery query';
				throw new Exception($error);
			}
		} 
		catch (Exception $e) {
			$errorMsg =  'Caught exception: '. $e->getMessage();
		    array_push($jsonArray['error'], $errorMsg);
		}
		while($row=oci_fetch_array($result))
		{
		    if ($i == 0)
			{
				$firstDate = (string)$row[0];
			}
			$i=$i+1;
			$inNum = $inNum + (int)$row[1];
			$outNum = $outNum +(int)$row[2];
			$lastDate = (string)$row[0];
			$failNum = $failNum + (int)$row[3];
			$failPerc = (int)$row[3]/(int)$row[1];
			array_push($jsonArray['eccs_indicator'],array("FirstDate" => $firstDate, "LastDate" => $lastDate, "SuccessNum" => $outNum, "FailNum" => $failNum, "FailPerc" => $failPerc));
		}
		
		
		//ECCS SERVICE INDICATOR
		$i =0;
		$inNum = 0;
		$outNum = 0;
		$failNum = 0;
		$eccsServiceQuery =  "select TO_CHAR((WEB_UPDATE_DATE), 'MM/DD/YYYY'), WEB_UPDATE_IN_COUNT, WEB_UPDATE_OUT_COUNT, WEB_UPDATE_FAIL_COUNT from WEB_UPDATES where SYSTEM_ID = 9  and WEB_UPDATE_DATE >= (select (max(WEB_UPDATE_DATE-30)) from WEB_UPDATES where SYSTEM_ID = 9) ORDER BY WEB_UPDATE_DATE";
		try {
			$result = oci_parse($conn, $eccsServiceQuery);
			$r = oci_execute($result);
			if (!$r) {
				$error = 'There was an error in the eccsServiceQuery query';
				throw new Exception($error);
			}
		} 
		catch (Exception $e) {
			$errorMsg =  'Caught exception: '. $e->getMessage();
		    array_push($jsonArray['error'], $errorMsg);
		}
		while($row=oci_fetch_array($result))
		{
		    if ($i == 0)
			{
				$firstDate = (string)$row[0];
			}
			$i=$i+1;
			$inNum = $inNum + (int)$row[1];
			$outNum = $outNum +(int)$row[2];
			$lastDate = (string)$row[0];
			$failNum = $failNum + (int)$row[3];
			$failPerc = (int)$row[3]/(int)$row[1];
			array_push($jsonArray['eccs_serv_indicator'],array("FirstDate" => $firstDate, "LastDate" => $lastDate, "SuccessNum" => $outNum, "FailNum" => $failNum, "FailPerc" => $failPerc));
		}
		
		
		//CRMS INDICATOR
		$i =0;
		$inNum = 0;
		$outNum = 0;
		$failNum = 0;
		$crmsQuery =  "select TO_CHAR((WEB_UPDATE_DATE), 'MM/DD/YYYY'), WEB_UPDATE_IN_COUNT, WEB_UPDATE_OUT_COUNT, WEB_UPDATE_FAIL_COUNT from WEB_UPDATES where SYSTEM_ID = 2  and WEB_UPDATE_DATE >= (select (max(WEB_UPDATE_DATE-30)) from WEB_UPDATES where SYSTEM_ID = 2) ORDER BY WEB_UPDATE_DATE";
		try {
			$result = oci_parse($conn, $crmsQuery);
			$r = oci_execute($result);
			if (!$r) {
				$error = 'There was an error in the crmsQuery query';
				throw new Exception($error);
			}
		} 
		catch (Exception $e) {
			$errorMsg =  'Caught exception: '. $e->getMessage();
		    array_push($jsonArray['error'], $errorMsg);
		}
		while($row=oci_fetch_array($result))
		{
		    if ($i == 0)
			{
				$firstDate = (string)$row[0];
			}
			$i=$i+1;
			$inNum = $inNum + (int)$row[1];
			$outNum = $outNum +(int)$row[2];
			$lastDate = (string)$row[0];
			$failNum = $failNum + (int)$row[3];
			$failPerc = (int)$row[3]/(int)$row[1];
			array_push($jsonArray['crms_indicator'],array("FirstDate" => $firstDate, "LastDate" => $lastDate, "SuccessNum" => $outNum, "FailNum" => $failNum, "FailPerc" => $failPerc));
		}
		
	}
/////////////////////////////////////////////////////////////////////////////////////////////////////////////////////
//DASH 2 Range Selected
/////////////////////////////////////////////////////////////////////////////////////////////////////////////////////
	elseif ($graphName == 'dash2Range') {
	    $startDate =$_REQUEST['startrange'];
		$endDate =$_REQUEST['endrange'];
		$noDataInd = 0;
		$typeDateSelect =$_REQUEST['typedateselect'];
		$jsonArray = array(
		'rsms' => array(),
		'eccs' => array(),
		'eccs_service' => array(),
		'crms' => array(),
		'noDataInd' => array(),
		'error' => array()
		);
		$lastDate = '01/01/1900';
	    $firstDate = '01/01/1900';
		
		//Single day selected
		if($typeDateSelect == '1')
		{
			$i =0;
			$inNum = 0;
			$outNum = 0;
			$failNum = 0;
			
			$rsmsQuery =  "select TO_CHAR(WEB_UPDATE_DATE, 'MM/DD/YYYY'), WEB_UPDATE_IN_COUNT, WEB_UPDATE_OUT_COUNT, WEB_UPDATE_FAIL_COUNT from WEB_UPDATES where system_id = 1 AND TO_CHAR(WEB_UPDATE_DATE, 'MM/DD/YYYY') = :startDate order by WEB_UPDATE_DATE asc";
			try {
				$result = oci_parse($conn, $rsmsQuery);
		        oci_bind_by_name($result, ':startDate', $startDate);
				$r = oci_execute($result);
				if (!$r) {
					$error = 'There was an error in the rsmsQuery query';
					throw new Exception($error);
				}
			} 
			catch (Exception $e) {
				$errorMsg =  'Caught exception: '. $e->getMessage();
				array_push($jsonArray['error'], $errorMsg);
			}
			while($row=oci_fetch_array($result))
			{
			    $noDataInd = 1;
				if ($i == 0)
				{
					$firstDate = (string)$row[0];
				}
				$i=$i+1;
				$inNum = $inNum + (int)$row[1];
				$outNum = $outNum +(int)$row[2];
				$lastDate = (string)$row[0];
				$failNum = $failNum + (int)$row[3];
				
			}
			if ($noDataInd==0)
			{
			    array_push($jsonArray['noDataInd'], "noData");
			}
			else
			{
				array_push($jsonArray['rsms'],array("FirstDate" => $firstDate, "LastDate" => $lastDate, "SuccessNum" => $outNum, "FailNum" => $failNum));
			}
			
			
			$i =0;
			$inNum = 0;
			$outNum = 0;
			$failNum = 0;
			$eccsQuery =  "select TO_CHAR(WEB_UPDATE_DATE, 'MM/DD/YYYY'), WEB_UPDATE_IN_COUNT, WEB_UPDATE_OUT_COUNT, WEB_UPDATE_FAIL_COUNT from WEB_UPDATES where system_id = 4 AND TO_CHAR(WEB_UPDATE_DATE, 'MM/DD/YYYY') = :startDate order by WEB_UPDATE_DATE asc";
			try {
				$result = oci_parse($conn, $eccsQuery);
				oci_bind_by_name($result, ':startDate', $startDate);
				$r = oci_execute($result);
				if (!$r) {
					$error = 'There was an error in the eccsQuery query';
					throw new Exception($error);
				}
			} 
			catch (Exception $e) {
				$errorMsg =  'Caught exception: '. $e->getMessage();
				array_push($jsonArray['error'], $errorMsg);
			}
			
			while($row=oci_fetch_array($result))
			{
			    $noDataInd = 1;
				if ($i == 0)
				{
					$firstDate = (string)$row[0];
				}
				$i=$i+1;
				$inNum = $inNum + (int)$row[1];
				$outNum = $outNum +(int)$row[2];
				$lastDate = (string)$row[0];
				$failNum = $failNum + (int)$row[3];
				
			}
			
	
			if ($noDataInd==0)
			{
			    array_push($jsonArray['noDataInd'], "noData");
			}
			else
			{
				array_push($jsonArray['eccs'],array("FirstDate" => $firstDate, "LastDate" => $lastDate, "SuccessNum" => $outNum, "FailNum" => $failNum));
			}
			
			
			//ECC service 			
			$i =0;
			$inNum = 0;
			$outNum = 0;
			$failNum = 0;
			$eccsServQuery =  "select TO_CHAR(WEB_UPDATE_DATE, 'MM/DD/YYYY'), WEB_UPDATE_IN_COUNT, WEB_UPDATE_OUT_COUNT, WEB_UPDATE_FAIL_COUNT from WEB_UPDATES where system_id = 9 AND TO_CHAR(WEB_UPDATE_DATE, 'MM/DD/YYYY') = :startDate order by WEB_UPDATE_DATE asc";
			try {
				$result = oci_parse($conn, $eccsServQuery);
				oci_bind_by_name($result, ':startDate', $startDate);
				$r = oci_execute($result);
				if (!$r) {
					$error = 'There was an error in the eccsServQuery query';
					throw new Exception($error);
				}
			} 
			catch (Exception $e) {
				$errorMsg =  'Caught exception: '. $e->getMessage();
				array_push($jsonArray['error'], $errorMsg);
			}
			
			while($row=oci_fetch_array($result))
			{
			    $noDataInd = 1;
				if ($i == 0)
				{
					$firstDate = (string)$row[0];
				}
				$i=$i+1;
				$inNum = $inNum + (int)$row[1];
				$outNum = $outNum +(int)$row[2];
				$lastDate = (string)$row[0];
				$failNum = $failNum + (int)$row[3];
				
			}
			
	
			if ($noDataInd==0)
			{
			    array_push($jsonArray['noDataInd'], "noData");
			}
			else
			{
				array_push($jsonArray['eccs_service'],array("FirstDate" => $firstDate, "LastDate" => $lastDate, "SuccessNum" => $outNum, "FailNum" => $failNum));
			}
			
			
			$i =0;
			$inNum = 0;
			$outNum = 0;
			$failNum = 0;
			$crmsQuery =  "select TO_CHAR(WEB_UPDATE_DATE, 'MM/DD/YYYY'), WEB_UPDATE_IN_COUNT, WEB_UPDATE_OUT_COUNT, WEB_UPDATE_FAIL_COUNT from WEB_UPDATES where system_id = 2 AND TO_CHAR(WEB_UPDATE_DATE, 'MM/DD/YYYY') = :startDate order by WEB_UPDATE_DATE asc";
			try {
				$result = oci_parse($conn, $crmsQuery);
				oci_bind_by_name($result, ':startDate', $startDate);
				$r = oci_execute($result);
				if (!$r) {
					$error = 'There was an error in the crmsQuery query';
					throw new Exception($error);
				}
			} 
			catch (Exception $e) {
				$errorMsg =  'Caught exception: '. $e->getMessage();
				array_push($jsonArray['error'], $errorMsg);
			}
			while($row=oci_fetch_array($result))
			{
			    $noDataInd = 1;
				if ($i == 0)
				{
					$firstDate = (string)$row[0];
				}
				$i=$i+1;
				$inNum = $inNum + (int)$row[1];
				$outNum = $outNum +(int)$row[2];
				$lastDate = (string)$row[0];
				$failNum = $failNum + (int)$row[3];
				
			}
			if ($noDataInd==0)
			{
			    array_push($jsonArray['noDataInd'], "noData");
			}
			else
			{
				array_push($jsonArray['crms'],array("FirstDate" => $firstDate, "LastDate" => $lastDate, "SuccessNum" => $outNum, "FailNum" => $failNum));
			}
			
		}
		
		//Range selected
		elseif($typeDateSelect == '2')
		{
		    $i =0;
			$inNum = 0;
			$outNum = 0;
			$failNum = 0;
			$rsmsQuery =  "select TO_CHAR(WEB_UPDATE_DATE, 'MM/DD/YYYY'), WEB_UPDATE_IN_COUNT, WEB_UPDATE_OUT_COUNT, WEB_UPDATE_FAIL_COUNT from WEB_UPDATES where system_id = 1 AND WEB_UPDATE_DATE >= TO_DATE(:startDate, 'MM/DD/YYYY') AND WEB_UPDATE_DATE <(TO_DATE(:endDate, 'MM/DD/YYYY')+1) order by WEB_UPDATE_DATE asc";
			try {
				$result = oci_parse($conn, $rsmsQuery);
				oci_bind_by_name($result, ':startDate', $startDate);
				oci_bind_by_name($result, ':endDate', $endDate);
				$r = oci_execute($result);
				if (!$r) {
					$error = 'There was an error in the crmsQuery query';
					throw new Exception($error);
				}
			} 
			catch (Exception $e) {
				$errorMsg =  'Caught exception: '. $e->getMessage();
				array_push($jsonArray['error'], $errorMsg);
			}
			while($row=oci_fetch_array($result))
			{
			    $noDataInd = 1;
				if ($i == 0)
				{
					$firstDate = (string)$row[0];
				}
				$i=$i+1;
				$inNum = $inNum + (int)$row[1];
				$outNum = $outNum +(int)$row[2];
				$lastDate = (string)$row[0];
				$failNum = $failNum + (int)$row[3];
			}
			if ($noDataInd==0)
			{
			    array_push($jsonArray['noDataInd'], "noData");
			}
			else
			{
				array_push($jsonArray['rsms'],array("FirstDate" => $firstDate, "LastDate" => $lastDate, "SuccessNum" => $outNum, "FailNum" => $failNum));
			}
			
			//ecc
			$i =0;
			$inNum = 0;
			$outNum = 0;
			$failNum = 0;
			$eccsQuery =  "select TO_CHAR(WEB_UPDATE_DATE, 'MM/DD/YYYY'), WEB_UPDATE_IN_COUNT, WEB_UPDATE_OUT_COUNT, WEB_UPDATE_FAIL_COUNT from WEB_UPDATES where system_id = 4 AND WEB_UPDATE_DATE >= TO_DATE(:startDate, 'MM/DD/YYYY') AND WEB_UPDATE_DATE <(TO_DATE(:endDate, 'MM/DD/YYYY')+1) order by WEB_UPDATE_DATE asc";
			try {
				$result = oci_parse($conn, $eccsQuery);
				oci_bind_by_name($result, ':startDate', $startDate);
				oci_bind_by_name($result, ':endDate', $endDate);
				$r = oci_execute($result);
				if (!$r) {
					$error = 'There was an error in the eccsQuery query';
					throw new Exception($error);
				}
			} 
			catch (Exception $e) {
				$errorMsg =  'Caught exception: '. $e->getMessage();
				array_push($jsonArray['error'], $errorMsg);
			}
			while($row=oci_fetch_array($result))
			{
			    $noDataInd = 1;
				if ($i == 0)
				{
					$firstDate = (string)$row[0];
				}
				$i=$i+1;
				$inNum = $inNum + (int)$row[1];
				$outNum = $outNum +(int)$row[2];
				$lastDate = (string)$row[0];
				$failNum = $failNum + (int)$row[3];
			}
			
			if ($noDataInd==0)
			{
			    array_push($jsonArray['noDataInd'], "noData");
			}
			else
			{
				array_push($jsonArray['eccs'],array("FirstDate" => $firstDate, "LastDate" => $lastDate, "SuccessNum" => $outNum, "FailNum" => $failNum));
			}
			
			
			//ecc service range 2
			$i =0;
			$inNum = 0;
			$outNum = 0;
			$failNum = 0;
			$eccsServQuery =  "select TO_CHAR(WEB_UPDATE_DATE, 'MM/DD/YYYY'), WEB_UPDATE_IN_COUNT, WEB_UPDATE_OUT_COUNT, WEB_UPDATE_FAIL_COUNT from WEB_UPDATES where system_id = 9 AND WEB_UPDATE_DATE >= TO_DATE(:startDate, 'MM/DD/YYYY') AND WEB_UPDATE_DATE <(TO_DATE(:endDate, 'MM/DD/YYYY')+1) order by WEB_UPDATE_DATE asc";
			try {
				$result = oci_parse($conn, $eccsServQuery);
				oci_bind_by_name($result, ':startDate', $startDate);
				oci_bind_by_name($result, ':endDate', $endDate);
				$r = oci_execute($result);
				if (!$r) {
					$error = 'There was an error in the eccsServQuery query';
					throw new Exception($error);
				}
			} 
			catch (Exception $e) {
				$errorMsg =  'Caught exception: '. $e->getMessage();
				array_push($jsonArray['error'], $errorMsg);
			}
			while($row=oci_fetch_array($result))
			{
			    $noDataInd = 1;
				if ($i == 0)
				{
					$firstDate = (string)$row[0];
				}
				$i=$i+1;
				$inNum = $inNum + (int)$row[1];
				$outNum = $outNum +(int)$row[2];
				$lastDate = (string)$row[0];
				$failNum = $failNum + (int)$row[3];
			}
			
			if ($noDataInd==0)
			{
			    array_push($jsonArray['noDataInd'], "noData");
			}
			else
			{
				array_push($jsonArray['eccs_service'],array("FirstDate" => $firstDate, "LastDate" => $lastDate, "SuccessNum" => $outNum, "FailNum" => $failNum));
			}
			
			$i =0;
			$inNum = 0;
			$outNum = 0;
			$failNum = 0;
			$crmsQuery =  "select TO_CHAR(WEB_UPDATE_DATE, 'MM/DD/YYYY'), WEB_UPDATE_IN_COUNT, WEB_UPDATE_OUT_COUNT, WEB_UPDATE_FAIL_COUNT from WEB_UPDATES where system_id = 2 AND WEB_UPDATE_DATE >= TO_DATE(:startDate, 'MM/DD/YYYY') AND WEB_UPDATE_DATE <(TO_DATE(:endDate, 'MM/DD/YYYY')+1) order by WEB_UPDATE_DATE asc";
			try {
				$result = oci_parse($conn, $crmsQuery);
				oci_bind_by_name($result, ':startDate', $startDate);
				oci_bind_by_name($result, ':endDate', $endDate);
				$r = oci_execute($result);
				if (!$r) {
					$error = 'There was an error in the crmsQuery query';
					throw new Exception($error);
				}
			} 
			catch (Exception $e) {
				$errorMsg =  'Caught exception: '. $e->getMessage();
				array_push($jsonArray['error'], $errorMsg);
			}
			while($row=oci_fetch_array($result))
			{
			    $noDataInd = 1;
				if ($i == 0)
				{
					$firstDate = (string)$row[0];
				}
				$i=$i+1;
				$inNum = $inNum + (int)$row[1];
				$outNum = $outNum +(int)$row[2];
				$lastDate = (string)$row[0];
				$failNum = $failNum + (int)$row[3];
				
			}
			if ($noDataInd==0)
			{
			    array_push($jsonArray['noDataInd'], "noData");
			}
			else
			{
				array_push($jsonArray['crms'],array("FirstDate" => $firstDate, "LastDate" => $lastDate, "SuccessNum" => $outNum, "FailNum" => $failNum));
			}
			
		
		}
	}
/////////////////////////////////////////////////////////////////////////////////////////////////////////////////////
//DASH 3 Initial
/////////////////////////////////////////////////////////////////////////////////////////////////////////////////////
	elseif ($graphName == 'dash3Start') {
			$jsonArray = array('hend' => array(), 'hend_indicator' => array());
			$i =0;
			$inNum = 0;
			$outNum = 0;
			$failNum = 0;
			$hendQuery =  "select TO_CHAR(CC_UPDATE_DATE, 'MM/DD/YYYY'), CC_UPDATE_IN_COUNT, CC_UPDATE_OUT_COUNT, CC_UPDATE_FAIL_COUNT from CC_UPDATES where SYSTEM_ID = 6 AND CC_UPDATE_DATE = (select max(CC_UPDATE_DATE) from CC_UPDATES)";
			try {
				$result = oci_parse($conn, $hendQuery);
				$r = oci_execute($result);
				if (!$r) {
					$error = 'There was an error in the hendQuery query';
					throw new Exception($error);
				}
			} 
			catch (Exception $e) {
				$errorMsg =  'Caught exception: '. $e->getMessage();
				array_push($jsonArray, $errorMsg);
			}
			while($row=oci_fetch_array($result))
			{
				if ($i == 0)
				{
					$firstDate = (string)$row[0];
				}
				$i=$i+1;
				$inNum = $inNum + (int)$row[1];
				$outNum = $outNum +(int)$row[2];
				$lastDate = (string)$row[0];
				$failNum = $failNum + (int)$row[3];								
			}
			
			array_push($jsonArray['hend'], array("FirstDate" => $firstDate, "LastDate" => $lastDate, "SuccessNum" => $outNum, "FailNum" => $failNum));
			
						
		//HEND INDICATOR			
		$inTeleNum = 0;
		$outTeleNum = 0;
		$failTeleNum = 0;
		
		$hendQuery = "select TO_CHAR(CC_UPDATE_DATE, 'MM/DD/YYYY'), CC_UPDATE_IN_COUNT, CC_UPDATE_OUT_COUNT, CC_UPDATE_FAIL_COUNT from CC_UPDATES where SYSTEM_ID = 6 and CC_UPDATE_DATE >= (select (max(CC_UPDATE_DATE-30)) from CC_UPDATES where SYSTEM_ID = 6) ORDER BY CC_UPDATE_DATE";
		
		try {
				$result = oci_parse($conn, $hendQuery);
				$r = oci_execute($result);
				if (!$r) {
					$error = 'There was an error in the hendQuery query';
					throw new Exception($error);
				}
			} 
		catch (Exception $e) {
				$errorMsg =  'Caught exception: '. $e->getMessage();
				array_push($jsonArray['error'], $errorMsg);
		}

		while($row=oci_fetch_array($result))
		{
		    if ($i == 0)
			{
				$firstDate = (string)$row[0];
			}
			$i=$i+1;
			$inNum = $inNum + (int)$row[1];
			$outNum = $outNum +(int)$row[2];
			$lastDate = (string)$row[0];
			$failNum = $failNum + (int)$row[3];
			$failPerc = (int)$row[3]/(int)$row[1];
			array_push($jsonArray['hend_indicator'],$failPerc);
		}
	   // array_push($jsonArray['hend_indicator'], array("SuccessNum" => $outTeleNum, "FailNum" => $failTeleNum, "FailPerc" => $failPerc));
		
			
	}
/////////////////////////////////////////////////////////////////////////////////////////////////////////////////////
//DASH 3 Range Selected
/////////////////////////////////////////////////////////////////////////////////////////////////////////////////////
	elseif ($graphName == 'dash3Range') {
	    $startDate =$_REQUEST['startrange'];
		$endDate =$_REQUEST['endrange'];
		$typeDateSelect =$_REQUEST['typedateselect'];
		$jsonArray = array();
		$noDataInd = 0;
		$jsonArray = array(
			'hend' => array(),
			'noDataInd' => array(),
		    'error' => array()
		);
		//Single day selected
		if($typeDateSelect == '1')
		{
			$i =0;
			$inNum = 0;
			$outNum = 0;
			$failNum = 0;
			$hendQuery =  "select TO_CHAR(CC_UPDATE_DATE, 'MM/DD/YYYY'), CC_UPDATE_IN_COUNT, CC_UPDATE_OUT_COUNT, CC_UPDATE_FAIL_COUNT from CC_UPDATES
                           where SYSTEM_ID = 6 and TO_DATE(CC_UPDATE_DATE) = TO_DATE(:startDate, 'MM/DD/YYYY') order by CC_UPDATE_DATE asc";
			try {
				$result = oci_parse($conn, $hendQuery);
				oci_bind_by_name($result, ':startDate', $startDate);
			
				$r = oci_execute($result);
				if (!$r) {
					$error = 'There was an error in the hendQuery query';
					throw new Exception($error);
				}
			} 
			catch (Exception $e) {
				$errorMsg =  'Caught exception: '. $e->getMessage();
				array_push($jsonArray, $errorMsg);
			}
			while($row=oci_fetch_array($result))
			{
			    $noDataInd = 1;
				if ($i == 0)
				{
					$firstDate = (string)$row[0];
				}
				$i=$i+1;
				$inNum = $inNum + (int)$row[1];
				$outNum = $outNum +(int)$row[2];
				$lastDate = (string)$row[0];
				$failNum = $failNum + (int)$row[3];
				
			}
			if ($noDataInd==0)
			{
			    array_push($jsonArray['noDataInd'], "noData");
			}
			else
			{
				array_push($jsonArray['hend'], array("FirstDate" => $firstDate, "LastDate" => $lastDate, "SuccessNum" => $outNum, "FailNum" => $failNum));
			}
	    }
		
		//Range selected
		if($typeDateSelect == '2')
		{  
		    $i =0;
			$inNum = 0;
			$outNum = 0;
			$failNum = 0;
			$hendQuery =  "select TO_CHAR(CC_UPDATE_DATE, 'MM/DD/YYYY'), CC_UPDATE_IN_COUNT, CC_UPDATE_OUT_COUNT, CC_UPDATE_FAIL_COUNT from CC_UPDATES where SYSTEM_ID = 6 and  CC_UPDATE_DATE >= TO_DATE(:startDate, 'MM/DD/YYYY') AND CC_UPDATE_DATE <(TO_DATE(:endDate, 'MM/DD/YYYY')+1) order by CC_UPDATE_DATE asc";
			try {
				$result = oci_parse($conn, $hendQuery);
				oci_bind_by_name($result, ':startDate', $startDate);
				oci_bind_by_name($result, ':endDate', $endDate);
				$r = oci_execute($result);
				if (!$r) {
					$error = 'There was an error in the hendQuery query';
					throw new Exception($error);
				}
			} 
			catch (Exception $e) {
				$errorMsg =  'Caught exception: '. $e->getMessage();
				array_push($jsonArray, $errorMsg);
			}
			while($row=oci_fetch_array($result))
			{
			    $noDataInd = 1;
				if ($i == 0)
				{
					$firstDate = (string)$row[0];
				}
				$i=$i+1;
				$inNum = $inNum + (int)$row[1];
				$outNum = $outNum +(int)$row[2];
				$lastDate = (string)$row[0];
				$failNum = $failNum + (int)$row[3];
			}
			if ($noDataInd==0)
			{
			    array_push($jsonArray['noDataInd'], "noData");
			}
			else
			{
				array_push($jsonArray['hend'], array("FirstDate" => $firstDate, "LastDate" => $lastDate, "SuccessNum" => $outNum, "FailNum" => $failNum));
			}
		}
	}
/////////////////////////////////////////////////////////////////////////////////////////////////////////////////////
//DASH 4
/////////////////////////////////////////////////////////////////////////////////////////////////////////////////////
	elseif ($graphName == 'dash4') {
	  $jsonArray = array(
		'totalData' => array(),
		'failData' => array(),
		'error' => array(),
		'comment' => array()
		);
		$manAddrQuery =  "SELECT TO_CHAR((MAN_ADDR_DATE), 'MM/DD/YYYY'), MAN_ADDR_FIXED_COUNT, MAN_ADDR_TOTAL_COUNT FROM MANUAL_ADDRESS_ISSUES ORDER BY MAN_ADDR_DATE ASC";
		try {
				$result = oci_parse($conn, $manAddrQuery);
				$r = oci_execute($result);
				if (!$r) {
					$error = 'There was an error in the manAddrQuery query';
					throw new Exception($error);
				}
			} 
		catch (Exception $e) {
				$errorMsg =  'Caught exception: '. $e->getMessage();
				array_push($jsonArray['error'], $errorMsg);
		}
		while($row=oci_fetch_array($result))
		{
			$dateEpoch = strtotime($row[0]);
			$count = (int)$row[2];
			$total = (int)$row[1];
			$failPerc = ((int)$row[1]/(int)$row[2])*100;
			
			if($count==0 || $total==0)
			{
			   array_push($jsonArray['failData'], array((int)$dateEpoch*1000, 0));
			}
			else 
			{
			   array_push($jsonArray['failData'], array((int)$dateEpoch*1000, $failPerc));
			}
			array_push($jsonArray['totalData'], array((int)$dateEpoch*1000, (int)$row[2]));
			
		}
		$commentQuery =  "select TO_CHAR(MAN_ADDR_DATE+ 15/24, 'MM/DD/YYYY HH24:MI:SS'), DATA_COMMENT from MANUAL_ADDRESS_ISSUES where DATA_COMMENT is not null order by MAN_ADDR_DATE asc";
		try {
			$result = oci_parse($conn, $commentQuery);
			$r = oci_execute($result);
			if (!$r) {
				$error = 'There was an error in the commentQuery query';
				throw new Exception($error);
			}
		} 
		catch (Exception $e) {
			$errorMsg =  'Caught exception: '. $e->getMessage();
		    array_push($jsonArray['error'], $errorMsg);
		}
		while($row=oci_fetch_array($result))
		{
			array_push($jsonArray['comment'], array($row[0], (string)$row[1]));
		}
	}
/////////////////////////////////////////////////////////////////////////////////////////////////////////////////////
//DASH 5
/////////////////////////////////////////////////////////////////////////////////////////////////////////////////////
	elseif ($graphName == 'dash5') {
	  $jsonArray = array(
		'totalData' => array(),
		'failData' => array(),
		'error' => array(),
		'comment' => array()
		);
		$cyberFailQuery =  "SELECT TO_CHAR((CYBER_FAIL_DATE), 'MM/DD/YYYY'), CYBER_FAIL_TOTAL_COUNT, CYBER_FAIL_SEVEN_DAY_COUNT,CYBER_FAIL_FOURTEEN_DAY_COUNT, CYBER_FAIL_ZERO_DAY_COUNT FROM CYBERSOURCE_FAILURES ORDER BY CYBER_FAIL_DATE ASC";
		try {
				$result = oci_parse($conn, $cyberFailQuery);
				$r = oci_execute($result);
				if (!$r) {
					$error = 'There was an error in the cyberFailQuery query';
					throw new Exception($error);
				}
			} 
		catch (Exception $e) {
				$errorMsg =  'Caught exception: '. $e->getMessage();
				array_push($jsonArray['error'], $errorMsg);
		}
		while($row=oci_fetch_array($result))
		{
			$dateEpoch = strtotime($row[0]);
			$sumOfDayRange = ($row[2]+$row[3]+$row[4]);
			array_push($jsonArray['totalData'], array((int)$dateEpoch*1000, (int)$row[1]));
			array_push($jsonArray['failData'], array((int)$dateEpoch*1000, (($sumOfDayRange/(int)$row[1])*100)));
		}
		$commentQuery =  "select TO_CHAR(CYBER_FAIL_DATE+ 15/24, 'MM/DD/YYYY HH24:MI:SS'), DATA_COMMENT from CYBERSOURCE_FAILURES where DATA_COMMENT is not null order by CYBER_FAIL_DATE asc";
		try {
			$result = oci_parse($conn, $commentQuery);
			$r = oci_execute($result);
			if (!$r) {
				$error = 'There was an error in the commentQuery query';
				throw new Exception($error);
			}
		} 
		catch (Exception $e) {
			$errorMsg =  'Caught exception: '. $e->getMessage();
		    array_push($jsonArray['error'], $errorMsg);
		}
		while($row=oci_fetch_array($result))
		{
			array_push($jsonArray['comment'], array($row[0], (string)$row[1]));
		}
	}
/////////////////////////////////////////////////////////////////////////////////////////////////////////////////////
//DASH 6
/////////////////////////////////////////////////////////////////////////////////////////////////////////////////////

       elseif ($graphName == 'dash6') {
         $jsonArray = array(
               'totalData' => array(),
               'failData' => array(),
               'error' => array(),
			   'comment' => array()
         );
               $BSubQuery =  "SELECT TO_CHAR((B_SUB_DATE), 'MM/DD/YYYY'), B_SUB_SUBSCRIPTION, B_SUB_TOTAL_COUNT FROM BILLING_SUBSCRIPTION ORDER BY B_SUB_DATE ASC";
               try {
                               $result = oci_parse($conn, $BSubQuery);
                               $r = oci_execute($result);
                               if (!$r) {
                                       $error = 'There was an error in the BSubQuery query';
                                       throw new Exception($error);
                               }
                       }
               catch (Exception $e) {
                               $errorMsg =  'Caught exception: '. $e->getMessage();
                               array_push($jsonArray['error'], $errorMsg);
               }
               while($row=oci_fetch_array($result))
               {
                       $dateEpoch = strtotime($row[0]);
                       $count = (int)$row[2];
                       $total = (int)$row[1];
                       $failPerc = ((int)$row[1]/(int)$row[2])*100;
                                               
                       if($count==0 || $total==0)
                       {
                          array_push($jsonArray['failData'], array((int)$dateEpoch*1000, 0));
                       }
                       else
                       {
                          array_push($jsonArray['failData'], array((int)$dateEpoch*1000, $failPerc));
                       }
                       array_push($jsonArray['totalData'], array((int)$dateEpoch*1000, (int)$row[2]));
                       
               }
			   $commentQuery =  "select TO_CHAR(B_SUB_DATE+ 15/24, 'MM/DD/YYYY HH24:MI:SS'), DATA_COMMENT from BILLING_SUBSCRIPTION where DATA_COMMENT is not null order by B_SUB_DATE asc";
			   try {
					$result = oci_parse($conn, $commentQuery);
					$r = oci_execute($result);
					if (!$r) {
						$error = 'There was an error in the commentQuery query';
						throw new Exception($error);
					}
				} 
				catch (Exception $e) {
					$errorMsg =  'Caught exception: '. $e->getMessage();
					array_push($jsonArray['error'], $errorMsg);
				}
				while($row=oci_fetch_array($result))
				{
					array_push($jsonArray['comment'], array($row[0], (string)$row[1]));
				}
       }
	   
	   
	   
/////////////////////////////////////////////////////////////////////////////////////////////////////////////////////
//DASH 7
/////////////////////////////////////////////////////////////////////////////////////////////////////////////////////
	elseif ($graphName == 'dash7') {
	
			$jsonArray = array(
			   'series1' => array(),
			   'series2' => array(),
			   'series3' => array(),
			   'series4' => array(),
			   'series5' => array(),
			   'series6' => array(),
			   'series7' => array(),
			   'series8' => array(),
			   'series9' => array(),
			   'series10' => array(),
			   'series11' => array(),
			   'series12' => array(),
			   'series13' => array(),
			   'failData' => array(),
			   'error' => array()
			   
        );	

		 $BSupportQuery =  "SELECT TO_CHAR((B_SUPPORT_DATE), 'MM/DD/YYYY'), B_SUPPORT_REASON, B_SUPPORT_NUM_CASES FROM BILLING_SUPPORT WHERE B_SUPPORT_DATE >= (select (max(B_SUPPORT_DATE-30)) from BILLING_SUPPORT) order by B_SUPPORT_DATE";
		 
		 try {
                $result = oci_parse($conn, $BSupportQuery);
                $r = oci_execute($result);
                if (!$r) {
                        $error = 'There was an error in the BSupportQuery query';
                        throw new Exception($error);
                }
             }
               catch (Exception $e) {
                               $errorMsg =  'Caught exception: '. $e->getMessage();
                               array_push($jsonArray['error'], $errorMsg);
               }
			   
		
		while($row=oci_fetch_array($result))
               {
                       $dateEpoch = strtotime($row[0]);
                       $numCases = (int)$row[2];
                       $reason = $row[1];
					   
                                               
						if ($reason == 'Billing/Credit Card Issue'){
							array_push($jsonArray['series1'], array((int)$dateEpoch*1000, $numCases));
						}
						if ($reason == 'Tax Exempt'){
							array_push($jsonArray['series2'], array((int)$dateEpoch*1000, $numCases));
						}
						if ($reason == 'How To Update Billing Information'){
							array_push($jsonArray['series3'], array((int)$dateEpoch*1000, $numCases));
						}
						if ($reason == 'Technical Error Updating Billing Info'){
							array_push($jsonArray['series4'], array((int)$dateEpoch*1000, $numCases));
						}
						if ($reason == 'Reactivation Of Suspended Subscription'){
							array_push($jsonArray['series5'], array((int)$dateEpoch*1000, $numCases));
						}
						if ($reason == 'Duplicate Charges - Single Order'){
							array_push($jsonArray['series6'], array((int)$dateEpoch*1000, $numCases));
						}
						if ($reason == 'Duplicate Orders'){
							array_push($jsonArray['series7'], array((int)$dateEpoch*1000, $numCases));
						}
						if ($reason == 'Invoice Issue / Request'){
							array_push($jsonArray['series8'], array((int)$dateEpoch*1000, $numCases));
						}
						if ($reason == 'Product Price Issue'){
							array_push($jsonArray['series9'], array((int)$dateEpoch*1000, $numCases));
						}
						if ($reason == 'Tax / VAT Issue'){
							array_push($jsonArray['series10'], array((int)$dateEpoch*1000, $numCases));
						}
						if ($reason == 'Payment Method Failure'){
							array_push($jsonArray['series11'], array((int)$dateEpoch*1000, $numCases));
						}
						if ($reason == 'Change Billing Date'){
							array_push($jsonArray['series12'], array((int)$dateEpoch*1000, $numCases));
						}
						if ($reason == 'Cancellation,  But Account Still Charged'){
							array_push($jsonArray['series13'], array((int)$dateEpoch*1000, $numCases));
						}
                      
                       
               }
			   
		////////////////////////
		//Dash 7 Indicator
		////////////////////////	   
		
               $BSupportInd =  "SELECT TO_CHAR((B_SUB_DATE), 'MM/DD/YYYY'), B_SUB_SUBSCRIPTION, B_SUB_TOTAL_COUNT FROM BILLING_SUBSCRIPTION ORDER BY B_SUB_DATE ASC";
               try {
                               $result = oci_parse($conn, $BSupportInd);
                               $r = oci_execute($result);
                               if (!$r) {
                                       $error = 'There was an error in the BSupportInd query';
                                       throw new Exception($error);
                               }
                       }
               catch (Exception $e) {
                               $errorMsg =  'Caught exception: '. $e->getMessage();
                               array_push($jsonArray['error'], $errorMsg);
               }
               while($row=oci_fetch_array($result))
               {
                       $dateEpoch = strtotime($row[0]);
                       $count = (int)$row[2];
                       $total = (int)$row[1];
                       $failPerc = ((int)$row[1]/(int)$row[2])*100;
                                               
                       if($count==0 || $total==0)
                       {
                          array_push($jsonArray['failData'], array((int)$dateEpoch*1000, 0));
                       }
                       else
                       {
                          array_push($jsonArray['failData'], array((int)$dateEpoch*1000, $failPerc));
                       }
                       
               }
	
	}
	
	



 

/////////////////////////////////////////////////////////////////////////////////////////////////////////////////////
//Summary Page
/////////////////////////////////////////////////////////////////////////////////////////////////////////////////////
	elseif ($graphName == 'dashMain') {
	    $jsonArray = array(
		'dash1' => array(
			'splunks' => array( ),
			'rsms' => array( )),
		'dash2' => array(
			'webSuccess' => array(),
			'teleSuccess' => array()
	       ),
		'dash4' => array(
			'totalData' => array(),
			'failData' => array()),
		'dash5' => array(
			'totalData' => array(),
			'failData' => array()),
		'dash6' => array(
			'totalData' => array(),
			'failData' => array()),
		'dash7' => array(
			   'series1' => array(),
			   'series2' => array(),
			   'series3' => array(),
			   'series4' => array(),
			   'series5' => array(),
			   'series6' => array(),
			   'series7' => array(),
			   'series8' => array(),
			   'series9' => array(),
			   'series10' => array(),
			   'series11' => array(),
			   'series12' => array(),
			   'series13' => array(),
			   'dash7Indicator' => array()
			),
		'error' => array()
		);
		/////////////////////////////////////////////////////////////////////////////////////////////////////////////////////
		//Summary DASH 1
		/////////////////////////////////////////////////////////////////////////////////////////////////////////////////////
		$rsmsQuery =  "select TO_CHAR((WEB_UPDATE_DATE), 'MM/DD/YYYY'), WEB_UPDATE_IN_COUNT, WEB_UPDATE_OUT_COUNT, WEB_UPDATE_FAIL_COUNT from WEB_UPDATES where SYSTEM_ID = 1  and WEB_UPDATE_DATE >= (select (max(WEB_UPDATE_DATE-7)) from WEB_UPDATES where SYSTEM_ID = 1) ORDER BY WEB_UPDATE_DATE";
		try {
				$result = oci_parse($conn, $rsmsQuery);
				$r = oci_execute($result);
				if (!$r) {
					$error = 'There was an error in the rsmsQuery query';
					throw new Exception($error);
				}
			} 
		catch (Exception $e) {
				$errorMsg =  'Caught exception: '. $e->getMessage();
				array_push($jsonArray['error'], $errorMsg);
		}
		while($row=oci_fetch_array($result))
		{
			$dateEpoch = strtotime($row[0]);
			array_push($jsonArray['dash1']['rsms'], array((int)$dateEpoch*1000, (int)$row[1]));
		}
		
		$splunksQuery =  "SELECT TO_CHAR((EDIT_BILL_DATE), 'MM/DD/YYYY'), EDIT_BILL_COUNT FROM EDIT_BILLING WHERE SYSTEM_ID = 7 and EDIT_BILL_DATE >= (select (max(EDIT_BILL_DATE-7)) from EDIT_BILLING where SYSTEM_ID = 7) ORDER BY EDIT_BILL_DATE";
		try {
				$result2 = oci_parse($conn, $splunksQuery);
				$r = oci_execute($result2);
				if (!$r) {
					$error = 'There was an error in the splunksQuery query';
					throw new Exception($error);
				}
			} 
		catch (Exception $e) {
				$errorMsg =  'Caught exception: '. $e->getMessage();
				array_push($jsonArray['error'], $errorMsg);
		}
		while($row=oci_fetch_array($result2))
		{
			$dateEpoch = strtotime($row[0]);
			array_push($jsonArray['dash1']['splunks'], array((int)$dateEpoch*1000, (int)$row[1]));
		}
		/////////////////////////////////////////////////////////////////////////////////////////////////////////////////////
		//Summary DASH 2
		/////////////////////////////////////////////////////////////////////////////////////////////////////////////////////
		$i =0;
		$inRsmNum = 0;
		$outRsmNum = 0;
		$failRsmNum = 0;
		$rsmsQuery =  "select TO_CHAR((WEB_UPDATE_DATE), 'MM/DD/YYYY'), WEB_UPDATE_IN_COUNT, WEB_UPDATE_OUT_COUNT, WEB_UPDATE_FAIL_COUNT from WEB_UPDATES where SYSTEM_ID = 1  and WEB_UPDATE_DATE >= (select (max(WEB_UPDATE_DATE)) from WEB_UPDATES where SYSTEM_ID = 1) ORDER BY WEB_UPDATE_DATE";
		try {
				$result = oci_parse($conn, $rsmsQuery);
				$r = oci_execute($result);
				if (!$r) {
					$error = 'There was an error in the rsmsQuery query';
					throw new Exception($error);
				}
			} 
		catch (Exception $e) {
				$errorMsg =  'Caught exception: '. $e->getMessage();
				array_push($jsonArray['error'], $errorMsg);
		}
		while($row=oci_fetch_array($result))
		{
			$i=$i+1;
			$inRsmNum = $inRsmNum + (int)$row[1];
			$outRsmNum = $outRsmNum +(int)$row[2];
			$failRsmNum = $failRsmNum + (int)$row[3];
		}
		
		$i =0;
		$inCrmNum = 0;
		$outCrmNum = 0;
		$failCrmNum = 0;
		$crmsQuery =  "select TO_CHAR((WEB_UPDATE_DATE), 'MM/DD/YYYY'), WEB_UPDATE_IN_COUNT, WEB_UPDATE_OUT_COUNT, WEB_UPDATE_FAIL_COUNT from WEB_UPDATES where SYSTEM_ID = 2  and WEB_UPDATE_DATE >= (select (max(WEB_UPDATE_DATE)) from WEB_UPDATES where SYSTEM_ID = 2) ORDER BY WEB_UPDATE_DATE";
		try {
				$result = oci_parse($conn, $crmsQuery);
				$r = oci_execute($result);
				if (!$r) {
					$error = 'There was an error in the crmsQuery query';
					throw new Exception($error);
				}
			} 
		catch (Exception $e) {
				$errorMsg =  'Caught exception: '. $e->getMessage();
				array_push($jsonArray['error'], $errorMsg);
		}
		while($row=oci_fetch_array($result))
		{
			$i=$i+1;
			$inCrmNum = $inCrmNum + (int)$row[1];
			$outCrmNum = $outCrmNum +(int)$row[2];
			$failCrmNum = $failCrmNum + (int)$row[3];
		}
		
		//ECC SERVICE
		$i =0;
		$inEccServNum = 0;
		$outEccServNum = 0;
		$failEccServNum = 0;
		$eccsServQuery =  "select TO_CHAR((WEB_UPDATE_DATE), 'MM/DD/YYYY'), WEB_UPDATE_IN_COUNT, WEB_UPDATE_OUT_COUNT, WEB_UPDATE_FAIL_COUNT from WEB_UPDATES where SYSTEM_ID = 9  and WEB_UPDATE_DATE >= (select (max(WEB_UPDATE_DATE)) from WEB_UPDATES where SYSTEM_ID = 9) ORDER BY WEB_UPDATE_DATE";
		try {
				$result = oci_parse($conn, $eccsServQuery);
				$r = oci_execute($result);
				if (!$r) {
					$error = 'There was an error in the eccsServQuery query';
					throw new Exception($error);
				}
			} 
		catch (Exception $e) {
				$errorMsg =  'Caught exception: '. $e->getMessage();
				array_push($jsonArray['error'], $errorMsg);
		}
		while($row=oci_fetch_array($result))
		{
		    if ($i == 0)
			{
				$firstDate = (string)$row[0];
			}
			$i=$i+1;
			$inEccServNum = $inEccServNum + (int)$row[1];
			$outEccServNum = $outEccServNum +(int)$row[2];
			$failEccServNum = $failEccServNum + (int)$row[3];
			// $failPerc = (int)$row[3]/(int)$row[1];						
		}
		
		
		//ECC
		$i =0;
		$inEccNum = 0;
		$outEccNum = 0;
		$failEccNum = 0;
		$eccQuery =  "select TO_CHAR((WEB_UPDATE_DATE), 'MM/DD/YYYY'), WEB_UPDATE_IN_COUNT, WEB_UPDATE_OUT_COUNT, WEB_UPDATE_FAIL_COUNT from WEB_UPDATES where SYSTEM_ID = 4  and WEB_UPDATE_DATE >= (select (max(WEB_UPDATE_DATE)) from WEB_UPDATES where SYSTEM_ID = 4) ORDER BY WEB_UPDATE_DATE";
		try {
				$result = oci_parse($conn, $eccQuery);
				$r = oci_execute($result);
				if (!$r) {
					$error = 'There was an error in the eccQuery query';
					throw new Exception($error);
				}
			} 
		catch (Exception $e) {
				$errorMsg =  'Caught exception: '. $e->getMessage();
				array_push($jsonArray['error'], $errorMsg);
		}
		while($row=oci_fetch_array($result))
		{
		    if ($i == 0)
			{
				$firstDate = (string)$row[0];
			}
			$i=$i+1;
			$inEccNum = $inEccNum + (int)$row[1];
			$outEccNum = $outEccNum +(int)$row[2];
			$failEccNum = $failEccNum + (int)$row[3];
			// $failPerc = (int)$row[3]/(int)$row[1];						
		}
			
		
		$totalSuccessNum = ($outEccNum/$outRsmNum)*100;
		$totalFailNum = 100 - $totalSuccessNum;
		$failPerc = $totalFailNum/$totalSuccessNum;
		
		array_push($jsonArray['dash2']['webSuccess'], array("SuccessNum" => $totalSuccessNum, "FailNum" => $totalFailNum, "FailPerc" => $failPerc));
		
		
		$inTeleNum = 0;
		$outTeleNum = 0;
		$failTeleNum = 0;
		$hendQuery =  "select TO_CHAR(CC_UPDATE_DATE, 'MM/DD/YYYY'), CC_UPDATE_IN_COUNT, CC_UPDATE_OUT_COUNT, CC_UPDATE_FAIL_COUNT from CC_UPDATES where SYSTEM_ID = 6 and CC_UPDATE_DATE >= (select (max(CC_UPDATE_DATE)) from CC_UPDATES where SYSTEM_ID = 6) ORDER BY CC_UPDATE_DATE";
		try {
				$result = oci_parse($conn, $hendQuery);
				$r = oci_execute($result);
				if (!$r) {
					$error = 'There was an error in the hendQuery query';
					throw new Exception($error);
				}
			} 
		catch (Exception $e) {
				$errorMsg =  'Caught exception: '. $e->getMessage();
				array_push($jsonArray['error'], $errorMsg);
		}
		while($row=oci_fetch_array($result))
		{
			$i=$i+1;
			$inTeleNum = $inTeleNum + (int)$row[1];
			$outTeleNum = $outTeleNum +(int)$row[2];
			$failTeleNum = $failTeleNum + (int)$row[3];
			
		}
		$failPerc = $failTeleNum/$inTeleNum;
	    array_push($jsonArray['dash2']['teleSuccess'], array("SuccessNum" => $outTeleNum, "FailNum" => $failTeleNum, "FailPerc" => $failPerc));
		
		/////////////////////////////////////////////////////////////////////////////////////////////////////////////////////
		//Summary DASH 4
		/////////////////////////////////////////////////////////////////////////////////////////////////////////////////////
		
		$manAddrQuery =  "SELECT TO_CHAR((MAN_ADDR_DATE), 'MM/DD/YYYY'), MAN_ADDR_FIXED_COUNT, MAN_ADDR_TOTAL_COUNT FROM MANUAL_ADDRESS_ISSUES where MAN_ADDR_DATE >= (select (max(MAN_ADDR_DATE-7)) from MANUAL_ADDRESS_ISSUES where SYSTEM_ID = 5) and SYSTEM_ID = 5 ORDER BY MAN_ADDR_DATE ASC";
		try {
				$result = oci_parse($conn, $manAddrQuery);
				$r = oci_execute($result);
				if (!$r) {
					$error = 'There was an error in the manAddrQuery query';
					throw new Exception($error);
				}
			} 
		catch (Exception $e) {
				$errorMsg =  'Caught exception: '. $e->getMessage();
				array_push($jsonArray['error'], $errorMsg);
		}
		while($row=oci_fetch_array($result))
		{
			$dateEpoch = strtotime($row[0]);
			$count = (int)$row[2];
			$total = (int)$row[1];
			$failPerc = ((int)$row[1]/(int)$row[2])*100;
			
			if($count==0 || $total==0)
			{
			   array_push($jsonArray['dash4']['failData'], array((int)$dateEpoch*1000, 0));
			}
			else 
			{
			   array_push($jsonArray['dash4']['failData'], array((int)$dateEpoch*1000, $failPerc));
			}
			array_push($jsonArray['dash4']['totalData'], array((int)$dateEpoch*1000, (int)$row[2]));
			
		}
		/////////////////////////////////////////////////////////////////////////////////////////////////////////////////////
		//Summary DASH 5
		/////////////////////////////////////////////////////////////////////////////////////////////////////////////////////
		$cyberFailQuery =  "SELECT TO_CHAR((CYBER_FAIL_DATE), 'MM/DD/YYYY'), CYBER_FAIL_TOTAL_COUNT, CYBER_FAIL_SEVEN_DAY_COUNT,CYBER_FAIL_FOURTEEN_DAY_COUNT, CYBER_FAIL_ZERO_DAY_COUNT FROM CYBERSOURCE_FAILURES where CYBER_FAIL_DATE >= (select (max(CYBER_FAIL_DATE-7)) from CYBERSOURCE_FAILURES where SYSTEM_ID = 3) and SYSTEM_ID = 3 ORDER BY CYBER_FAIL_DATE ASC";
		try {
				$result = oci_parse($conn, $cyberFailQuery);
				$r = oci_execute($result);
				if (!$r) {
					$error = 'There was an error in the cyberFailQuery query';
					throw new Exception($error);
				}
			} 
		catch (Exception $e) {
				$errorMsg =  'Caught exception: '. $e->getMessage();
				array_push($jsonArray['error'], $errorMsg);
		}
		while($row=oci_fetch_array($result))
		{
			$dateEpoch = strtotime($row[0]);
			$sumOfDayRange = ($row[2]+$row[3]+$row[4]);
			array_push($jsonArray['dash5']['totalData'], array((int)$dateEpoch*1000, (int)$row[1]));
			array_push($jsonArray['dash5']['failData'], array((int)$dateEpoch*1000, (($sumOfDayRange/(int)$row[1])*100)));
		}
		
		/////////////////////////////////////////////////////////////////////////////////////////////////////////////////////
		//Summary DASH 6
		/////////////////////////////////////////////////////////////////////////////////////////////////////////////////////
		
		$bSubQuery =  "SELECT TO_CHAR((B_SUB_DATE), 'MM/DD/YYYY'), B_SUB_SUBSCRIPTION, B_SUB_TOTAL_COUNT FROM BILLING_SUBSCRIPTION where B_SUB_DATE >= (select (max(B_SUB_DATE-7)) from BILLING_SUBSCRIPTION where SYSTEM_ID = 8) and SYSTEM_ID = 8 ORDER BY B_SUB_DATE ASC";
		try {
				$result = oci_parse($conn, $bSubQuery);
				$r = oci_execute($result);
				if (!$r) {
					$error = 'There was an error in the bSubQuery query';
					throw new Exception($error);
				}
			} 
		catch (Exception $e) {
				$errorMsg =  'Caught exception: '. $e->getMessage();
				array_push($jsonArray['error'], $errorMsg);
		}
		
		while($row=oci_fetch_array($result))
		{
			$dateEpoch = strtotime($row[0]);
			$total_count = (int)$row[2];
			$subs = (int)$row[1];
			$failPerc = ((int)$row[1]/(int)$row[2])*100;
			
			if($total_count==0 || $subs==0)
			{
			   array_push($jsonArray['dash6']['failData'], array((int)$dateEpoch*1000, 0));
			}
			else 
			{
			   array_push($jsonArray['dash6']['failData'], array((int)$dateEpoch*1000, $failPerc));
			}
			array_push($jsonArray['dash6']['totalData'], array((int)$dateEpoch*1000, (int)$row[2]));			
		}	
		
	
	
/////////////////////////////
/////SUMMARY DASH 7
/////////////////////////////

		 $BSupportQuery =  "SELECT TO_CHAR((B_SUPPORT_DATE), 'MM/DD/YYYY'), B_SUPPORT_REASON, B_SUPPORT_NUM_CASES FROM BILLING_SUPPORT WHERE B_SUPPORT_DATE >= (select (max(B_SUPPORT_DATE-6)) from BILLING_SUPPORT)";
		 
		 try {
                $result = oci_parse($conn, $BSupportQuery);
                $r = oci_execute($result);
                if (!$r) {
                        $error = 'There was an error in the BSupportQuery query';
                        throw new Exception($error);
                }
             }
               catch (Exception $e) {
                               $errorMsg =  'Caught exception: '. $e->getMessage();
                               array_push($jsonArray['error'], $errorMsg);
               }
		
		while($row=oci_fetch_array($result))
               {
                       $dateEpoch = strtotime($row[0]);
                       $numCases = (int)$row[2];
                       $reason = $row[1];
                                               
						if ($reason == 'Billing/Credit Card Issue'){
							array_push($jsonArray['dash7']['series1'], array((int)$dateEpoch*1000, $numCases));
						}
						if ($reason == 'Tax Exempt'){
							array_push($jsonArray['dash7']['series2'], array((int)$dateEpoch*1000, $numCases));
						}
						if ($reason == 'How To Update Billing Information'){
							array_push($jsonArray['dash7']['series3'], array((int)$dateEpoch*1000, $numCases));
						}
						if ($reason == 'Technical Error Updating Billing Info'){
							array_push($jsonArray['dash7']['series4'], array((int)$dateEpoch*1000, $numCases));
						}
						if ($reason == 'Reactivation Of Suspended Subscription'){
							array_push($jsonArray['dash7']['series5'], array((int)$dateEpoch*1000, $numCases));
						}
						if ($reason == 'Duplicate Charges - Single Order'){
							array_push($jsonArray['dash7']['series6'], array((int)$dateEpoch*1000, $numCases));
						}
						if ($reason == 'Duplicate Orders'){
							array_push($jsonArray['dash7']['series7'], array((int)$dateEpoch*1000, $numCases));
						}
						if ($reason == 'Invoice Issue / Request'){
							array_push($jsonArray['dash7']['series8'], array((int)$dateEpoch*1000, $numCases));
						}
						if ($reason == 'Product Price Issue'){
							array_push($jsonArray['dash7']['series9'], array((int)$dateEpoch*1000, $numCases));
						}
						if ($reason == 'Tax / VAT Issue'){
							array_push($jsonArray['dash7']['series10'], array((int)$dateEpoch*1000, $numCases));
						}
						if ($reason == 'Payment Method Failure'){
							array_push($jsonArray['dash7']['series11'], array((int)$dateEpoch*1000, $numCases));
						}
						if ($reason == 'Change Billing Date'){
							array_push($jsonArray['dash7']['series12'], array((int)$dateEpoch*1000, $numCases));
						}
						if ($reason == 'Cancellation,  But Account Still Charged'){
							array_push($jsonArray['dash7']['series13'], array((int)$dateEpoch*1000, $numCases));
						}
                      
                       
               }
			   
			   
		////////////////////////
		//Dash 7 SUMMARY Indicator
		////////////////////////	   
		
		$BSupportSumIndicatorQuery =  "SELECT sum(B_SUPPORT_NUM_CASES) FROM BILLING_SUPPORT WHERE B_SUPPORT_DATE >= (select (max(B_SUPPORT_DATE-7)) from BILLING_SUPPORT) group by B_SUPPORT_DATE";
		 
		 try {
                $result = oci_parse($conn, $BSupportSumIndicatorQuery);
                $r = oci_execute($result);
                if (!$r) {
                        $error = 'There was an error in the BSupportSumIndicatorQuery query';
                        throw new Exception($error);
                }
             }
               catch (Exception $e) {
                               $errorMsg =  'Caught exception: '. $e->getMessage();
                               array_push($error, $errorMsg);
               }
		
		$threshSumm7 = 0;
		while($row=oci_fetch_array($result))
               {
					$CasesTotal = (int)$row[0];
					if ($CasesTotal > 400){
						$threshSumm7 = 1;
					}
				}
		array_push($jsonArray['dash7']['dash7Indicator'], $threshSumm7);
		
	
		
   }
	
    $callback = $_REQUEST['callback'];                            
    echo $callback."(".json_encode($jsonArray).")";
	//echo $callback."(".$test.")";
	oci_close($conn);
?>