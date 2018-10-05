<?php
	use Cake\Core\Configure;
	use Cake\Core\Configure\Engine\PhpConfig;

	$siteBaseUrl = Configure::read('SITE_BASE_URL');
	$logoImg = $kioskAddress = "";
	if(array_key_exists('address_1', $kioskDetails) && !empty($kioskDetails['address_1'])) $kioskAddress.=str_replace(",", "<br/>",$kioskDetails['address_1']);
	if(array_key_exists('address_1', $kioskDetails) && !empty($kioskDetails['address_1'])) $kioskAddress.=$kioskDetails['address_2']."<br/>";
	
	$csz = array();
	if(!empty($kioskDetails['city']))$csz['city'] = $kioskDetails['city'];
	if(!empty($kioskDetails['state']))$csz['state'] = $kioskDetails['state'];
	if(!empty($kioskDetails['zip']))$csz['zip'] = $kioskDetails['zip'];
	$cszStr = join(', ', $csz);
	if(!empty($cszStr)) $kioskAddress.=$cszStr."<br/>";
	
	if(empty(trim($kioskDetails['terms'])) && empty($new_kiosk_data[0]->terms)){
		$imgUrl = $siteBaseUrl."/img/".$settingArr['logo_image'];
		$logoImg = $this->Html->image($imgUrl, array('fullBase' => true));
	}else{
		if(!empty(trim($kioskDetails['logo_image']))){
			$imgUrl = $siteBaseUrl."/logo/".$kioskDetails['id']."/".$kioskDetails['logo_image'];
			$logoImg = $this->Html->image($imgUrl, array('fullBase' => true,'style'=> "width: 332px;height: 102px;"));	
		}elseif(!empty($new_kiosk_data) && !empty(trim($new_kiosk_data[0]->logo_image))){
			$imgUrl = $siteBaseUrl."/logo/".$new_kiosk_data[0]->id."/".$new_kiosk_data[0]->logo_image;
			$logoImg=  $this->Html->image($imgUrl, array('fullBase' => true,'style'=> "width: 332px;height: 102px;"));	
		}else{
			$imgUrl = $siteBaseUrl."/img/".$settingArr['logo_image'];
			$logoImg = $this->Html->image($imgUrl, array('fullBase' => true));
		}
	}
	
	//Start: Logic for getting prev balance
	$receptRow = "";
	$oneMonStandSum = $twoMonStandSum = $threeMonStandSum = $fourMonStandSum = $fiveMonStandSum = 0.00;
	$oneMonPmtSum = $twoMonPmtSum = $threeMonPmtSum = $fourMonPmtSum = $fiveMonPmtSum = 0.00;
	$previousBalance = $outStandingSum = $pmtSum = $prevBal = $lastBal = 0.00;
	//echo "<pre>";print_r($recptInOrders);echo "</pre>";
	
	if(isset($startDate) &&!empty($startDate)){
		foreach($recptInOrders as $key => $recptInOrder){
			if(
				array_key_exists('payment_method', $recptInOrder) &&
				trim($recptInOrder['payment_method']) == 'On Credit' &&
				array_key_exists('amount', $recptInOrder) &&
				!empty($recptInOrder['amount'] &&
				!array_key_exists('credit_receipt_id', $recptInOrder)
				)
			  )continue;//if credit_receipt_id ; show record
			
			extract($recptInOrder);
			
			if(isset($pmt_date)){
				$pmtDt = explode(" ", $pmt_date);
			}elseif(isset($inv_date)){
				$pmtDt = explode(" ", $inv_date);
			}
			if(isset($startDate) && !empty($startDate)){
				$dateArr = explode("-", $pmtDt[0]);
				
				$actDateStr = join("-", array($dateArr[0], $dateArr[1], $dateArr[2]));
				if ($actDateStr < $startDate){
					;
				}else{
					unset($inv_date);
					unset($cred_note_date);
					unset($pmt_date);
					unset($amount);
					unset($balance);
					unset($payment_method);
					continue;
				}
			}
			$recptNoStr = "";
			if( array_key_exists('credit_receipt_id', $recptInOrder) ){
				if(array_key_exists('cred_note_date', $recptInOrder)){
					$recptNoStr = "<td></td><td>$id</td>";
				}else{
					$recptNoStr = "<td></td><td>$credit_receipt_id</td>";
					$payment_method = "";
				}
			}elseif(array_key_exists('product_receipt_id', $recptInOrder) || array_key_exists('inv_date', $recptInOrder) ){
				if( array_key_exists('inv_date', $recptInOrder) ){
					$recptNoStr = "<td>$id</td><td></td>";
				}else{
					$recptNoStr = "<td>$product_receipt_id</td><td></td>";
				}
			}
			
			if(!isset($amount))$amount="";
			if(!isset($payment_method))$payment_method = "";
			if( isset($inv_date) || isset($cred_note_date) ){
				;
			}else{
				$orig_bill_amount = "";
			}
			//if($payment_method == 'On Credit' && !empty($amount))continue;
			if($key == 0){
				if(!empty($orig_bill_amount)){
					//outstanding column
					$prevBal = $balance = $orig_bill_amount;
				}elseif(!empty($amount)){
					$prevBal = $balance = (-1) * $amount;
				}
			}else{
				if(!empty($orig_bill_amount)){
					$prevBal = $balance = $prevBal + $orig_bill_amount;
				}elseif(!empty($amount)){
					$prevBal = $balance = $prevBal - $amount;
				}
			}
			$previousBalance = $lastBal = $balance;
			$balance = number_format($balance, 2);
			$outStandingSum+=(float)$orig_bill_amount;
			$pmtSum+=(float)$amount;
			
			$dateStr = "";
			if(!empty($pmtDt[0])) {
				$dateArr = explode("-", $pmtDt[0]);
				$dateStr = join("-", array($dateArr[2], $dateArr[1], $dateArr[0]));
				$montStr = join("-", array($dateArr[1], $dateArr[0]));
			}
			switch( $montStr){
				case date('m-Y'):
					$oneMonStandSum += (float)$orig_bill_amount;
					$oneMonPmtSum += (float)$amount;
					break;
				case date('m-Y', mktime(0, 0, 0, date('m')-1, 1, date('Y'))):
					$twoMonStandSum += (float)$orig_bill_amount;
					$twoMonPmtSum += (float)$amount;
					break;
				case date('m-Y', mktime(0, 0, 0, date('m')-2, 1, date('Y'))):
					$threeMonStandSum += (float)$orig_bill_amount;
					$threeMonPmtSum += (float)$amount;
					break;
				case date('m-Y', mktime(0, 0, 0, date('m')-3, 1, date('Y'))):
					$fourMonStandSum += (float)$orig_bill_amount;
					$fourMonPmtSum += (float)$amount;
					break;
				default:
					$fiveMonStandSum += (float)$orig_bill_amount;
					$fiveMonPmtSum += (float)$amount;
			}
			if(!empty($orig_bill_amount))$orig_bill_amount = "&pound;".$orig_bill_amount;
			if(!empty($amount))$amount = "&pound;".$amount;
			if(!empty($balance))$balance = "&pound;".$balance;
			
			$receptRow.="<tr style='font-size:12px;'>
							<td>$dateStr</td>
							$recptNoStr
							<td>$payment_method</td>
							<td style='text-align:right;'>$orig_bill_amount</td>
							<td style='text-align:right;'>$amount</td>
							<td style='text-align:right;'>$balance</td>
							";
			unset($inv_date);
			unset($cred_note_date);
			unset($pmt_date);
			unset($amount);
			unset($balance);
			unset($payment_method);
		}
		
		//End: Logic for getting prev balance
	}
	$receptRow = empty($previousBalance) ? "" : "<tr><td colspan='5'></td><td><strong>Prev Bal</strong></td><td style='text-align:right;'>&pound;".number_format($previousBalance,2)."</td></tr>";
	//echo "Prev balance:".$previousBalance."<br/>";
	$oneMonStandSum = $twoMonStandSum = $threeMonStandSum = $fourMonStandSum = $fiveMonStandSum = 0.00;
	$oneMonPmtSum = $twoMonPmtSum = $threeMonPmtSum = $fourMonPmtSum = $fiveMonPmtSum = 0.00;
	$outStandingSum = $pmtSum = $prevBal = $lastBal = 0.00;
	//echo "<pre>";print_r($recptInOrders);echo "</pre>";
	//echo "--->".$startDate;
	$loopCounter = 0;
	foreach($recptInOrders as $key => $recptInOrder){
		if(
			array_key_exists('payment_method', $recptInOrder) &&
			trim($recptInOrder['payment_method']) == 'On Credit' &&
			array_key_exists('amount', $recptInOrder) &&
			!empty($recptInOrder['amount'] &&
			!array_key_exists('credit_receipt_id', $recptInOrder)
			)
		  )continue;//if credit_receipt_id ; show record
		
		extract($recptInOrder);
		
		if(isset($pmt_date)){
			$pmtDt = explode(" ", $pmt_date);
		}elseif(isset($inv_date)){
			$pmtDt = explode(" ", $inv_date);
		}
		if(isset($startDate) && !empty($startDate)){
			$dateArr = explode("-", $pmtDt[0]);
			
			$actDateStr = join("-", array($dateArr[0], $dateArr[1], $dateArr[2]));
			if ($actDateStr < $startDate){
				unset($inv_date);
				unset($cred_note_date);
				unset($pmt_date);
				unset($amount);
				unset($balance);
				unset($payment_method);
				continue;
			}
		}
		$loopCounter++;
		$recptNoStr = "";
		if( array_key_exists('credit_receipt_id', $recptInOrder) ){
			if(array_key_exists('cred_note_date', $recptInOrder)){
				$recptNoStr = "<td></td><td>$id</td>";
			}else{
				$recptNoStr = "<td></td><td>$credit_receipt_id</td>";
				$payment_method = "";
			}
		}elseif(array_key_exists('product_receipt_id', $recptInOrder) || array_key_exists('inv_date', $recptInOrder) ){
			if( array_key_exists('inv_date', $recptInOrder) ){
				$recptNoStr = "<td>$id</td><td></td>";
			}else{
				$recptNoStr = "<td>$product_receipt_id</td><td></td>";
			}
		}
		
		if(!isset($amount))$amount="";
		if(!isset($payment_method))$payment_method = "";
		if( isset($inv_date) || isset($cred_note_date) ){
			;
		}else{
			$orig_bill_amount = "";
		}
		//if($payment_method == 'On Credit' && !empty($amount))continue;
		if($key == 0){
			if(!empty($orig_bill_amount)){
				//outstanding column
				$prevBal = $balance = $orig_bill_amount;
			}elseif(!empty($amount)){
				$prevBal = $balance = (-1) * $amount;
			}
		}else{
			if(!empty($orig_bill_amount)){
				$prevBal = $balance = $prevBal + $orig_bill_amount;
			}elseif(!empty($amount)){
				$prevBal = $balance = $prevBal - $amount;
			}
		}
		$unformattedBal = $balance;
		$balance = number_format($balance, 2);
		$outStandingSum+=(int)$orig_bill_amount;
		$pmtSum+=(int)$amount;
		
		if($loopCounter == 1){
			if(!empty($previousBalance)){
				if(!empty($orig_bill_amount)){
					$prevBal = $balance = $previousBalance + $unformattedBal;
					$balance = number_format($balance, 2);
				}else{
					$prevBal = $balance = $previousBalance - $amount;
					$balance = number_format($balance, 2);
				}
			}
		}
		
		$dateStr = "";
		if(!empty($pmtDt[0])) {
			$dateArr = explode("-", $pmtDt[0]);
			$dateStr = join("-", array($dateArr[2], $dateArr[1], $dateArr[0]));
			$montStr = join("-", array($dateArr[1], $dateArr[0]));
		}
		switch( $montStr){
			case date('m-Y'):
				$oneMonStandSum += (int)$orig_bill_amount;
				$oneMonPmtSum += (int)$amount;
				break;
			case date('m-Y', mktime(0, 0, 0, date('m')-1, 1, date('Y'))):
				$twoMonStandSum += (int)$orig_bill_amount;
				$twoMonPmtSum += (int)$amount;
				break;
			case date('m-Y', mktime(0, 0, 0, date('m')-2, 1, date('Y'))):
				$threeMonStandSum += (int)$orig_bill_amount;
				$threeMonPmtSum += (int)$amount;
				break;
			case date('m-Y', mktime(0, 0, 0, date('m')-3, 1, date('Y'))):
				$fourMonStandSum += (int)$orig_bill_amount;
				$fourMonPmtSum += (int)$amount;
				break;
			default:
				$fiveMonStandSum += (int)$orig_bill_amount;
				$fiveMonPmtSum += (int)$amount;
		}
		
		if(!empty($orig_bill_amount))$orig_bill_amount = "&pound;".$orig_bill_amount;
		if(!empty($amount))$amount = "&pound;".$amount;
		if(!empty($balance))$balance = "&pound;".$balance;
		
		$receptRow.="<tr style='font-size:12px;'>
						<td>$dateStr</td>
						$recptNoStr
						<td>$payment_method</td>
						<td style='text-align:right;'>$orig_bill_amount</td>
						<td style='text-align:right;'>$amount</td>
						<td style='text-align:right;'>$balance</td>
						";
		$lastBal = $balance;
		unset($inv_date);
		unset($cred_note_date);
		unset($pmt_date);
		unset($amount);
		unset($balance);
		unset($payment_method);
	}
	$business = $custDetArr['business'];
	$fname = $custDetArr['fname'];
	$customerEmail = $custDetArr['email'];
	$lname = $custDetArr['lname'];
	$address_1 = $custDetArr['address_1'];
	$address_2 = empty($custDetArr['address_2']) ? "" : "<tr><td colspan='2'>".$custDetArr['address_2']."</td></tr>";
	$custNo = $custDetArr['id'];
	$address = join(", ", array($custDetArr['city'], $custDetArr['state'], $custDetArr['zip']));
	$customerStr = <<<CUST_HTML
		<table>
			<tr><td>Business:</td><td><u>$business</u></td></tr>
			<tr><td>Customer:</td><td>$fname $lname</td></tr>
			<tr><td>Customer No:</td><td>$custNo</td></tr>
			<tr><td colspan='2'>$address_1</td></tr>
			$address_2
			<tr><td colspan='2'>$address</td></tr>
		</table>
CUST_HTML;
$jQueryURL = "http://ajax.googleapis.com/ajax/libs/jquery/1.4.2/jquery.min.js";
if(defined('URL_SCHEME')){
	$jQueryURL = URL_SCHEME."ajax.googleapis.com/ajax/libs/jquery/1.4.2/jquery.min.js";
}
?>
<script type="text/javascript" src="<?php echo $jQueryURL;?>"></script>
<?php echo $this->Html->script('jquery.printElement');?>
<input type='button' id='printSelected' name='print' value='Print Receipt' style='width:200px;' align='center' />
<?php ob_start(); ?>
<div id='printDiv'>
<table  style='width: 850px;'>
	<?php
	/*<tr>
		<td width='33%'></td>
		<td width='33%' style="text-align: center;">
			<h1 style="font-size: 150%;">ACCOUNT STATEMENT</h1>
			<?php echo $kioskAddress;?>
		</td>
		<td width='33%' style='text-align:right;'><?php echo $logoImg;?></td>
	</tr>*/
	?>
	<tr>
		<td valign='top'><?php echo $customerStr;?></td>
		<td></td>
		<td></td>
	</tr>
	<tr>
		<td colspan='2'>&nbsp;</td>
		<td style="text-align: right"><span style='padding-right: 50px;'>Date: </span><?php echo date("d-n-y");?></td>
	</tr>
	<tr>
		<td colspan='3'>
			<table>
				<tr style="background-color:rgb(214, 209, 210);">
					<td>DATE</td>
					<td>INV NO</td>
					<td>CR Note NO</td>
					<td>PMT MODE</td>
					<td>OUTSTANDING</td>
					<td>PAYMENT</td>		
					<td>BALANCE</td>
				</tr>
				
				<?php echo $receptRow;?>
				<tr>
					<td colspan='4'></td>
					<td style='text-align: right;'><?php echo "&pound;".number_format($outStandingSum, 2);?></td>
					<td style='text-align: right;'><?php echo "&pound;".number_format($pmtSum,2);?></td>
					<td></td>
				</tr>
				<tr style="text-transform: uppercase;">
					<td colspan="5"></td>
					<td style='text-align: right'>Total Amount Due</td>
					<td style='text-align: right'><?php echo $lastBal;?></td>
				</tr>
			</table>	
		</td>
	</tr>
</table>
</div>
<?php
	echo $output = ob_get_clean();
?>


