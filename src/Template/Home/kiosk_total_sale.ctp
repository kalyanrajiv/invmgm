<?php
use Cake\Core\Configure;
use Cake\Core\Configure\Engine\PhpConfig;
?>
<style>
    .ui-tooltip{
      max-width: 800px !important;
      width: auto !important;
      overflow:auto !important;
      }
  .ui-tooltip-content{
      background-color: #fdf8ef;
      }
</style>
<?php $currency = Configure::read('CURRENCY_TYPE');?>
<?php
//pr($previousMonthProductPmtDetails);//die;prv_recpit_amt
    $prv_cash = $credit_to_other_changed['cash'];
    $prv_bnk = $credit_to_other_changed['bank_transfer'];
    $prv_credit = $credit_to_other_changed['credit'];
    $prv_card = $credit_to_other_changed['card'];
    $prv_cheque = $credit_to_other_changed['cheque'];
	
	$t_prv_cash = $t_credit_to_other_changed['cash'];
    $t_prv_bnk = $t_credit_to_other_changed['bank_transfer'];
    $t_prv_credit = $t_credit_to_other_changed['credit'];
    $t_prv_card = $t_credit_to_other_changed['card'];
    $t_prv_cheque = $t_credit_to_other_changed['cheque'];




$total_today_cash = $todaysRcashPayment + $todaysUcashPayment + $todaysMcashPayment + $todayCash;
 $total_yes_cash = $yesCash + $yesterdaysRcashPayment + $yesterdaysUcashPayment + $yesterdaysMcardPayment;
 $total_mnth_cash = $currentMonthMcashPayment + $currentMonthUcashPayment + $currentMonthRcashPayment + $thisMnthCash;
 $total_prv_mnth_cash = $previousMonthMcashPayment + $previousMonthUcashPayment + $previousMonthRcashPayment + $prvMnthCash ;
//$this->Number->addFormat('BRL', array('before' => "$currency ", 'negative'=>'-','zero'=>"$currency 0.00", 'escape' => false));
echo $this->Form->create('KioskTotalSale',array('id'=>'KioskTotalSaleDashboardForm','url'=>array('controller'=>'home','action'=>'kiosk_total_sale')));
if(!empty($this->request->params['pass']) && array_key_exists(0,$this->request->params['pass'])){
    $selectedKiosk = $this->request->params['pass'][0];
}else{
    $selectedKiosk = 0;
}
?>
<table width='100%'>
    <tr>
        <td><?php
		if( $this->request->session()->read('Auth.User.group_id')!= MANAGERS){
			echo $this->Form->input('kiosk',array('options'=>$kiosks,'id'=>'KioskTotalSaleKiosk','default'=>$selectedKiosk));
		}else{
			echo $this->Form->input('kiosk',array('options'=>$kiosks,'id'=>'KioskTotalSaleKiosk','default'=>$selectedKiosk));
		}
		?></td>
		<td>
			<?php  echo $this->Html->link('Dashboard', array('controller' => 'products', 'action' => 'dashboard-data', 'full_base' => true));?>
		</td>
    </tr>
    <tr>
        <th width='20%'>&nbsp;</th>
        <th width='20%'>Today</th>
        <th width='20%'>Yesterday</th>
        <th width='20%'>Current Month</th>
        <th width='20%'>Previous Month</th>
    </tr>
    
    <?php  
    $tooltipTodayRepairSale = "(Card: {$todaysRcardPayment},Cash:{$todaysRcashPayment})";
    $tooltipYesterdayRepairSale = "(Card: {$yesterdaysRcardPayment},Cash:{$yesterdaysRcashPayment})";
    $tooltipCMRepairSale = "(Card: {$currentMonthRcardPayment},Cash:{$currentMonthRcashPayment})";
    $tooltipPMRepairSale = "(Card: {$previousMonthRcashPayment},Cash:{$previousMonthRcardPayment})";
?>  
    <tr>
        <td><strong>Repair Sale</strong></td>
		<?php
		$todaysSale1 = number_format($todaysSale,2);
		$yesterdaySale1 = number_format($yesterdaySale,2);
		$currentMonthRepairSale1 = number_format($currentMonthRepairSale,2);
		$previousMonthRepairSale1 = number_format($previousMonthRepairSale,2);
		?>
        <td><?php echo $CURRENCY_TYPE.$todaysSale1;?> <a id='todayRepairSale' alt='<?=$tooltipTodayRepairSale;?>', title='<?=$tooltipTodayRepairSale;?>'>(Detail)</a></td>
        <td><?php echo $CURRENCY_TYPE.$yesterdaySale1;?> <a id='yesterdayRepairSale' alt='<?=$tooltipYesterdayRepairSale;?>', title='<?=$tooltipYesterdayRepairSale;?>'>(Detail)</a></td>
        <td><?php echo $CURRENCY_TYPE.$currentMonthRepairSale1;?> <a id='cmRepairSale' alt='<?=$tooltipCMRepairSale;?>', title='<?=$tooltipCMRepairSale;?>'>(Detail)</a></td>
        <td><?php echo $CURRENCY_TYPE.$previousMonthRepairSale1;?> <a id='pmRepairSale' alt='<?=$tooltipPMRepairSale;?>', title='<?=$tooltipPMRepairSale;?>'>(Detail)</a></td>
    </tr>
    <script type="text/javascript">
        jQuery('#todayRepairSale').tooltip({content:"<?=$tooltipTodayRepairSale;?>",track:true});
        jQuery('#yesterdayRepairSale').tooltip({content:"<?=$tooltipYesterdayRepairSale;?>",track:true});
        jQuery('#cmRepairSale').tooltip({content:"<?=$tooltipCMRepairSale;?>",track:true});
        jQuery('#pmRepairSale').tooltip({content:"<?=$tooltipPMRepairSale;?>",track:true});
    </script>
    
    <tr style="color: blue">
        <td><strong>Repair Refund</strong></td>
        <td><?php
            if($todaysRefund < 0){
               $todaysRefund = $todaysRefund * (-1); 
             }
			 $todaysRefund1 = number_format($todaysRefund,2);
            echo $CURRENCY_TYPE.$todaysRefund1;?></td>
        <td><?php
        if($yesterdaysRefund < 0){
               $yesterdaysRefund = $yesterdaysRefund * (-1); 
             }
			 $yesterdaysRefund1 = number_format($yesterdaysRefund,2);
            echo $CURRENCY_TYPE.$yesterdaysRefund1;?></td>
		<?php 
		$currentMonthRepairRefund1 = number_format($currentMonthRepairRefund,2);
		$previousMonthRepairRefund1 = number_format($previousMonthRepairRefund,2);
		?>
        <td><?php echo $CURRENCY_TYPE.$currentMonthRepairRefund1;?></td>
        <td><?php echo $CURRENCY_TYPE.$previousMonthRepairRefund1;?></td>
    </tr>
    
    
    <?php  
        $tooltipTodayUnlockSale = "(Card: {$todaysUcardPayment},Cash:{$todaysUcashPayment})";
        $tooltipYesterdayUnlockSale = "(Card: {$yesterdaysUcardPayment},Cash:{$yesterdaysUcashPayment})";
        $tooltipCMUnlockSale = "(Card: {$currentMonthUcardPayment},Cash:{$currentMonthUcashPayment})";
        $tooltipPMUnlockSale = "(Card: {$previousMonthUcardPayment},Cash:{$previousMonthUcashPayment})";
    ?>
     <tr> 
        <td><strong>Unlock Sale</strong></td>
		<?php
		$todaysUsale1 = number_format($todaysUsale,2);
		$yesterdayUsale1 = number_format($yesterdayUsale,2);
		$currentMonthUnlockSale1 = number_format($currentMonthUnlockSale,2);
		$previousMonthUnlockSale1 = number_format($previousMonthUnlockSale,2);
		?>
        <td><?php echo $CURRENCY_TYPE.$todaysUsale1;?>  <a id='todayUnlockSale' alt='<?=$tooltipTodayUnlockSale;?>', title='<?=$tooltipTodayUnlockSale;?>'>(Detail)</a></td>
        <td><?php echo $CURRENCY_TYPE.$yesterdayUsale1;?> <a id='yesterdayUnlockSale' alt='<?=$tooltipYesterdayUnlockSale;?>', title='<?=$tooltipYesterdayUnlockSale;?>'>(Detail)</a></td>
        <td><?php echo $CURRENCY_TYPE.$currentMonthUnlockSale1;?> <a id='cmUnlockSale' alt='<?=$tooltipCMUnlockSale;?>', title='<?=$tooltipCMUnlockSale;?>'>(Detail)</a></td>
        <td><?php echo $CURRENCY_TYPE.$previousMonthUnlockSale1;?> <a id='pmUnlockSale' alt='<?=$tooltipPMUnlockSale;?>', title='<?=$tooltipPMUnlockSale;?>'>(Detail)</a></td>
    </tr>
    <script type="text/javascript">
        jQuery('#todayUnlockSale').tooltip({content:"<?=$tooltipTodayUnlockSale;?>",track:true});
        jQuery('#yesterdayUnlockSale').tooltip({content:"<?=$tooltipYesterdayUnlockSale;?>",track:true});
        jQuery('#cmUnlockSale').tooltip({content:"<?=$tooltipCMUnlockSale;?>",track:true});
        jQuery('#pmUnlockSale').tooltip({content:"<?=$tooltipPMUnlockSale;?>",track:true});
    </script>
    <?php
		$todaysUrefund1 = number_format($todaysUrefund,2);
		$yesterdaysUrefund1 = number_format($yesterdaysUrefund,2);
		$currentMonthUnlockRefund1 = number_format($currentMonthUnlockRefund,2);
		$previousMonthUnlockRefund1 = number_format($previousMonthUnlockRefund,2);
	?>
    
    <tr style="color: blue">
        <td><strong>Unlock Refund</strong></td>
        <td><?php echo $CURRENCY_TYPE.$todaysUrefund1;?></td>
        <td><?php echo $CURRENCY_TYPE.$yesterdaysUrefund1;?></td>
        <td><?php echo $CURRENCY_TYPE.$currentMonthUnlockRefund1;?></td>
        <td><?php echo $CURRENCY_TYPE.$previousMonthUnlockRefund1;?></td>
    </tr>
     <?php
        $cmProdCardPmt = $currentMonthProductPmtDetails['card'];
        $cmProdCashPmt = $currentMonthProductPmtDetails['cash'];
        
        $pmProdCardPmt = $previousMonthProductPmtDetails['card'];
        $pmProdCashPmt = $previousMonthProductPmtDetails['cash'];
        
       // $a = $yesterdayProductPmtDetails['cash'];
       // echo "$yesCash + $a";die;
        
        $cash = $todayProductPmtDetails['cash']+$todayCash;
        $yTotalCash = $yesCash + $yesterdayProductPmtDetails['cash'];
        $cMTotalCash = $thisMnthCash + $cmProdCashPmt;
        $priviousMnthCASH = $prvMnthCash + $pmProdCashPmt;
        
        $card = $todaysPcardPayment + $todayProductPmtDetails['card'];
        $yCard = $yesterdaysPcardPayment + $yesterdayProductPmtDetails['card'];
        $cMCard = $currentMonthPcardPayment + $cmProdCardPmt;
        $priviousMntCard = $pmProdCardPmt + $previousMonthPcardPayment;
        
       
        
        $totdayCridit = $todayProductPmtDetails['credit'];
        $today_bank_Transfer = $todayProductPmtDetails['bank_transfer'];
        $todayCheque = $todayProductPmtDetails['cheque'];
        
        $yCridit = $yesterdayProductPmtDetails['credit'];
        $y_bank_Transfer = $yesterdayProductPmtDetails['bank_transfer'];
        $yCheque = $yesterdayProductPmtDetails['cheque'];
        
        $cMCridit = $currentMonthProductPmtDetails['credit'];
        $cMBank = $currentMonthProductPmtDetails['bank_transfer'];
        $cMCheque = $currentMonthProductPmtDetails['cheque'];
        
        $privousMCridt = $previousMonthProductPmtDetails['credit'];
        $privousMBank = $previousMonthProductPmtDetails['bank_transfer'];
        $privousMCheque = $previousMonthProductPmtDetails['cheque'];
        
		$yTotalCash = round($yTotalCash,2);
		
        $tooltipTodayProdSale = "(Card: {$card},Cash:{$cash},Credit:{$totdayCridit},Bank:{$today_bank_Transfer},cheque:{$todayCheque})";
        $tooltipYesterdayProdSale = "(Card: {$yCard},Cash:{$yTotalCash},Credit:{$yCridit},Bank:{$y_bank_Transfer},cheque:{$yCheque})";
        $tooltipCMProdSale = "(Card: {$cMCard},Cash:{$cMTotalCash},Credit:{$cMCridit},Bank:{$cMBank},cheque:{$cMCheque})";
        $tooltipPMProdSale = "(Card: {$priviousMntCard},Cash:{$priviousMnthCASH},Credit:{$privousMCridt},Bank:{$privousMBank},cheque:{$privousMCheque})";
        
    ?>
	<?php
		$todayProductSale1 = number_format($todayProductSale,2);
		$yesterdayProductSale1 = number_format($yesterdayProductSale,2);
		$currentMonthProductSale1 = number_format($currentMonthProductSale,2);
		$previousMonthProductSale1 = number_format($previousMonthProductSale,2);
		?>
    <tr>
        <td><strong>Product Sale</strong></td>
        <td><?php echo $CURRENCY_TYPE.$todayProductSale1;?> <a id='todayProdSale' alt='<?=$tooltipTodayProdSale;?>', title='<?=$tooltipTodayProdSale;?>'>(Detail)</a></td>
        <td><?php echo $CURRENCY_TYPE.$yesterdayProductSale1;?> <a id='yesterdayProdSale' alt='<?=$tooltipYesterdayProdSale;?>', title='<?=$tooltipYesterdayProdSale;?>'>(Detail)</a></td>
        <td><?php echo $CURRENCY_TYPE.$currentMonthProductSale1;?> <a id='cmProdSale' alt='<?=$tooltipCMProdSale;?>', title='<?=$tooltipCMProdSale;?>'>(Detail)</a></td>
        <td><?php echo $CURRENCY_TYPE.$previousMonthProductSale1;?> <a id='pmProdSale' alt='<?=$tooltipPMProdSale;?>', title='<?=$tooltipPMProdSale;?>'>(Detail)</a></td>
    </tr>
    <script type="text/javascript">
        jQuery('#todayProdSale').tooltip({content:"<?=$tooltipTodayProdSale;?>",track:true});
        jQuery('#yesterdayProdSale').tooltip({content:"<?=$tooltipYesterdayProdSale;?>",track:true});
        jQuery('#cmProdSale').tooltip({content:"<?=$tooltipCMProdSale;?>",track:true});
        jQuery('#pmProdSale').tooltip({content:"<?=$tooltipPMProdSale;?>",track:true});
    </script>
	
    
    <?php
    $loggedInUser =  $this->request->session()->read('Auth.User.username');
    if(preg_match('/'.QUOT_USER_PREFIX.'/',$loggedInUser)){ 
    $t_card = $t_cash = $t_today_bank_Transfer = $t_todayCheque = 0;
        if(!empty($t_today_pay_details)){
            $t_card = $t_today_pay_details['card'];
            $t_cash = $t_today_pay_details['cash'];
            $t_today_bank_Transfer = $t_today_pay_details['bank_transfer'];
            $t_todayCheque = $t_today_pay_details['cheque'];
            $t_todayCredit = $t_today_pay_details['credit'];
        }
        $tooltip_t_TodayProdSale = "(Card: {$t_card},Cash:{$t_cash},Credit:{$t_todayCredit},Bank:{$t_today_bank_Transfer},cheque:{$t_todayCheque})";
        $t_yCard = $t_yTotalCash = $t_y_bank_Transfer = $t_yCheque = 0;
        if(!empty($yes_pay_details)){
            $t_yCard = $yes_pay_details['card'];
            $t_yTotalCash = $yes_pay_details['cash'];
            $t_y_bank_Transfer = $yes_pay_details['bank_transfer'];
            $t_yCheque = $yes_pay_details['cheque'];
            $t_yCredit = $yes_pay_details['credit'];
        }
        $tooltip_t_YesterdayProdSale = "(Card: {$t_yCard},Cash:{$t_yTotalCash},Credit:{$t_yCredit},Bank:{$t_y_bank_Transfer},cheque:{$t_yCheque})";
        $t_cMCard = $t_cMTotalCash = $t_cMBank = $t_cMCheque = 0;
        if(!empty($t_this_mnth_pay_details)){
            $t_cMCard = $t_this_mnth_pay_details['card'];
            $t_cMTotalCash = $t_this_mnth_pay_details['cash'];
            $t_cMBank = $t_this_mnth_pay_details['bank_transfer'];
            $t_cMCheque = $t_this_mnth_pay_details['cheque'];
            $t_cMCredit = $t_this_mnth_pay_details['credit'];
        }
        $tooltip_t_CMProdSale = "(Card: {$t_cMCard},Cash:{$t_cMTotalCash},Credit:{$t_cMCredit},Bank:{$t_cMBank},cheque:{$t_cMCheque})";
        $t_priviousMntCard = $t_priviousMnthCASH = $t_privousMBank = $t_privousMCheque = 0;
        if(!empty($prv_mnth_pay_details)){
           $t_priviousMntCard = $prv_mnth_pay_details['card'];
           $t_priviousMnthCASH = $prv_mnth_pay_details['cash'];
           $t_privousMBank = $prv_mnth_pay_details['bank_transfer'];
           $t_privousMCheque = $prv_mnth_pay_details['cheque'];
           $t_privousMCredit = $prv_mnth_pay_details['credit'];
        }
        
        $tooltip_t_PMProdSale = "(Card: {$t_priviousMntCard},Cash:{$t_priviousMnthCASH},Credit:{$t_privousMCredit},Bank:{$t_privousMBank},cheque:{$t_privousMCheque})";
    ?>
	
	<?php
		$t_today_total_amount1 = number_format($t_today_total_amount,2);
		$t_yes_total_amount1 = number_format($t_yes_total_amount,2);
		$t_this_mnth_total_amount1 = number_format($t_this_mnth_total_amount,2);
		$t_prv_mnth_total_amount1 = number_format($t_prv_mnth_total_amount,2);
	?>
	
    <tr>
        <td><strong>Quotation</strong></td>
        <td><?php echo $CURRENCY_TYPE.$t_today_total_amount1;?> <a id='t_todayProdSale' alt='<?=$tooltip_t_TodayProdSale;?>', title='<?=$tooltip_t_TodayProdSale;?>'>(Detail)</a></td>
        <td><?php echo $CURRENCY_TYPE.$t_yes_total_amount1;?> <a id='t_yesProdSale' alt='<?=$tooltip_t_YesterdayProdSale;?>', title='<?=$tooltip_t_YesterdayProdSale;?>'>(Detail)</a></td>
        <td><?php echo $CURRENCY_TYPE.$t_this_mnth_total_amount1;?> <a id='t_this_mnth_ProdSale' alt='<?=$tooltip_t_CMProdSale;?>', title='<?=$tooltip_t_CMProdSale;?>'>(Detail)</a></td>
        <td><?php echo $CURRENCY_TYPE.$t_prv_mnth_total_amount1;?> <a id='t_prv_mnth_ProdSale' alt='<?=$tooltip_t_PMProdSale;?>', title='<?=$tooltip_t_PMProdSale;?>'>(Detail)</a></td>
    </tr>
    
    <?php } ?>
    <?php //-----------------------my code-------------------?>
	<?php
		 $today_credit_note_sale = $cn_card = $cn_cash = $cn_today_bank_Transfer = $cn_todayCheque = 0;
	//pr($currMonthCNSale);die;
	
        if(!empty($todaysCNSale)){
            $cn_card = $todaysCNSale['card'];
            $cn_cash = $todaysCNSale['cash'];
            $cn_today_bank_Transfer = $todaysCNSale['bank_transfer'];
            $cn_todayCheque = $todaysCNSale['cheque'];
            $todayCredit = $todaysCNSale['credit'];
        }
        $tooltip_cn_TodayCnSale = "(Card: {$cn_card},Cash:{$cn_cash},Credit:{$todayCredit},Bank:{$cn_today_bank_Transfer},cheque:{$cn_todayCheque})";
        $y_credit_note_sale = $y_cn_Card = $y_cn_TotalCash = $y_cn_bank_Transfer = $y_cn_Cheque = 0;
        if(!empty($yesterdayCNSale)){
            $y_cn_Card = $yesterdayCNSale['card'];
            $y_cn_TotalCash = $yesterdayCNSale['cash'];
            $y_cn_bank_Transfer = $yesterdayCNSale['bank_transfer'];
            $y_cn_Cheque = $yesterdayCNSale['cheque'];
            $y_cn_yCredit = $yesterdayCNSale['credit'];
        }
        $tooltip_cn_YesterdayCnSale = "(Card: {$y_cn_Card},Cash:{$y_cn_TotalCash},Credit:{$y_cn_yCredit},Bank:{$y_cn_bank_Transfer},cheque:{$y_cn_Cheque})";
        $special_this_month_credit_note_sale = $cn_cMCard = $cn_cMTotalCash = $cn_cMBank = $cn_cMCheque = 0;
		
        if(!empty($currMonthCNSale)){ 
            $cn_cMCard = $currMonthCNSale['card'];
            $cn_cMTotalCash = $currMonthCNSale['cash'] + $currMonthCNSale['cashEntryAmt'];
            $cn_cMBank = $currMonthCNSale['bank_transfer'];
            $cn_cMCheque = $currMonthCNSale['cheque'];
            $cn_cMCredit = $currMonthCNSale['credit'];
        }
        $tooltip_cn_CMCnSale = "(Card: {$cn_cMCard},Cash:{$cn_cMTotalCash},Credit:{$cn_cMCredit},Bank:{$cn_cMBank},cheque:{$cn_cMCheque})";
       $special_prv_month_credit_note_sale =  $cn_priviousMntCard = $cn_priviousMnthCASH = $cn_privousMBank = $cn_privousMCheque = 0;
	   
        if(!empty($prevMonthCNSale)){
           $cn_priviousMntCard = $prevMonthCNSale['card'];
           $cn_priviousMnthCASH = $prevMonthCNSale['cash']+$prevMonthCNSale['cashEntryAmt'];
           $cn_privousMBank = $prevMonthCNSale['bank_transfer'];
           $cn_privousMCheque = $prevMonthCNSale['cheque'];
           $cn_privousMCredit = $prevMonthCNSale['credit'];
        }
        
        $tooltip_cn_PMCnSale = "(Card: {$cn_priviousMntCard},Cash:{$cn_priviousMnthCASH},Credit:{$cn_privousMCredit},Bank:{$cn_privousMBank},cheque:{$cn_privousMCheque})";
    ?>
	
	<?php //-----------------------my code-------------------?>
	<?php //pr($yesterdayCNSale); ?>
	<tr style="color: blue">
        <td><strong>Credit Note</strong></td>
		<?php $todaysCNSale_sum = $todaysCNSale['credit'] + $todaysCNSale['cash'] + $todaysCNSale['bank_transfer'] + $todaysCNSale['cheque'] + $todaysCNSale['card'];
        $yesterdayCNSale_sum = $yesterdayCNSale['credit'] + $yesterdayCNSale['cash'] + $yesterdayCNSale['bank_transfer'] + $yesterdayCNSale['cheque'] + $yesterdayCNSale['card'];
        $currMonthCNSale_sum = $currMonthCNSale['credit'] + $currMonthCNSale['cash'] + $currMonthCNSale['bank_transfer'] + $currMonthCNSale['cheque'] + $currMonthCNSale['card'] + $currMonthCNSale['cashEntryAmt'];
        $prevMonthCNSale_sum = $prevMonthCNSale['credit'] + $prevMonthCNSale['cash'] + $prevMonthCNSale['bank_transfer'] + $prevMonthCNSale['cheque'] + $prevMonthCNSale['card'] + $prevMonthCNSale['cashEntryAmt'];
        ?>
		<?php
		$todaysCNSale_sum1 = number_format($todaysCNSale_sum,2);
		$yesterdayCNSale_sum1 = number_format($yesterdayCNSale_sum,2);
		$currMonthCNSale_sum1 = number_format($currMonthCNSale_sum,2);
		$prevMonthCNSale_sum1 = number_format($prevMonthCNSale_sum,2);
		?>
        <td><?php echo $CURRENCY_TYPE.$todaysCNSale_sum1;?><a id='todayCNSale' alt='<?=$tooltip_cn_TodayCnSale;?>', title='<?=$tooltip_cn_TodayCnSale;?>'>(Detail)</a></td>
        <td><?php echo $CURRENCY_TYPE.$yesterdayCNSale_sum1;?><a id='yesCnSale' alt='<?=$tooltip_cn_YesterdayCnSale;?>', title='<?=$tooltip_cn_YesterdayCnSale;?>'>(Detail)</a></td>
        <td><?php echo $CURRENCY_TYPE.$currMonthCNSale_sum1;?><a id='CmCnSale' alt='<?=$tooltip_cn_CMCnSale;?>', title='<?=$tooltip_cn_CMCnSale;?>'>(Detail)</a></td>
        <td><?php echo $CURRENCY_TYPE.$prevMonthCNSale_sum1;?><a id='prvMnSale' alt='<?=$tooltip_cn_PMCnSale;?>', title='<?=$tooltip_cn_PMCnSale;?>'>(Detail)</a></td>
    </tr>	
	
    <?php
    $loggedInUser =  $this->request->session()->read('Auth.User.username');
    if(preg_match('/'.QUOT_USER_PREFIX.'/',$loggedInUser)){ 
    $special_today_credit_note_sale = $t_sp_cn_card = $t_sp_cn_cash = $t_sp_cn_today_bank_Transfer = $t_sp_cn_todayCheque = 0;
	//pr($t_todaysCNSale);
	
        if(!empty($t_todaysCNSale)){
            $t_sp_cn_card = $t_todaysCNSale['card'];
            $t_sp_cn_cash = $t_todaysCNSale['cash'];
            $t_sp_cn_today_bank_Transfer = $t_todaysCNSale['bank_transfer'];
            $t_sp_cn_todayCheque = $t_todaysCNSale['cheque'];
            $t_todayCredit = $t_todaysCNSale['credit'];
			$special_today_credit_note_sale = $t_todaysCNSale['card']+$t_todaysCNSale['cash']+$t_todaysCNSale['bank_transfer']+$t_todaysCNSale['cheque']+$t_todaysCNSale['credit'];
        }
        $tooltip_t_TodayProdSale = "(Card: {$t_sp_cn_card},Cash:{$t_sp_cn_cash},Credit:{$t_todayCredit},Bank:{$t_sp_cn_today_bank_Transfer},cheque:{$t_sp_cn_todayCheque})";
        $special_y_credit_note_sale = $y_cn_Card = $y_cn_TotalCash = $y_cn_bank_Transfer = $y_cn_Cheque = 0;
		//pr($t_yesterdayCNSale);die;
        if(!empty($t_yesterdayCNSale)){
            $y_cn_Card = $t_yesterdayCNSale['card'];
            $y_cn_TotalCash = $t_yesterdayCNSale['cash'];
            $y_cn_bank_Transfer = $t_yesterdayCNSale['bank_transfer'];
            $y_cn_Cheque = $t_yesterdayCNSale['cheque'];
            $y_cn_yCredit = $t_yesterdayCNSale['credit'];
			$special_y_credit_note_sale = $t_yesterdayCNSale['card']+$t_yesterdayCNSale['cash']+$t_yesterdayCNSale['bank_transfer']+$t_yesterdayCNSale['cheque']+$t_yesterdayCNSale['credit'];
        }
        $tooltip_t_YesterdayProdSale = "(Card: {$y_cn_Card},Cash:{$y_cn_TotalCash},Credit:{$y_cn_yCredit},Bank:{$y_cn_bank_Transfer},cheque:{$y_cn_Cheque})";
        $special_this_month_credit_note_sale = $t_cn_cMCard = $t_cn_cMTotalCash = $t_cn_cMBank = $t_cn_cMCheque = 0;
		
        if(!empty($t_currMonthCNSale)){ 
            $t_cn_cMCard = $t_currMonthCNSale['card'];
            $t_cn_cMTotalCash = $t_currMonthCNSale['cash'] + $t_currMonthCNSale['cashEntryAmt'];
            $t_cn_cMBank = $t_currMonthCNSale['bank_transfer'];
            $t_cn_cMCheque = $t_currMonthCNSale['cheque'];
            $t_cn_cMCredit = $t_currMonthCNSale['credit'];
			$special_this_month_credit_note_sale = $t_currMonthCNSale['card']+$t_currMonthCNSale['cash']+$t_currMonthCNSale['bank_transfer']+$t_currMonthCNSale['cheque']+$t_currMonthCNSale['credit'] + $t_currMonthCNSale['cashEntryAmt'];
        }
        $tooltip_t_CMProdSale = "(Card: {$t_cn_cMCard},Cash:{$t_cn_cMTotalCash},Credit:{$t_cn_cMCredit},Bank:{$t_cn_cMBank},cheque:{$t_cn_cMCheque})";
       $special_prv_month_credit_note_sale =  $t_cn_priviousMntCard = $t_cn_priviousMnthCASH = $t_cn_privousMBank = $t_cn_privousMCheque = 0;
	   
        if(!empty($t_prevMonthCNSale)){
           $t_cn_priviousMntCard = $t_prevMonthCNSale['card'];
           $t_cn_priviousMnthCASH = $t_prevMonthCNSale['cash'] + $t_prevMonthCNSale['cashEntryAmt'];
           $t_cn_privousMBank = $t_prevMonthCNSale['bank_transfer'];
           $t_cn_privousMCheque = $t_prevMonthCNSale['cheque'];
           $t_cn_privousMCredit = $t_prevMonthCNSale['credit'];
		   $special_prv_month_credit_note_sale = $t_prevMonthCNSale['card']+$t_prevMonthCNSale['cash']+$t_prevMonthCNSale['bank_transfer']+$t_prevMonthCNSale['cheque']+$t_prevMonthCNSale['credit'] + $t_prevMonthCNSale['cashEntryAmt'];
        }
        
        $tooltip_t_PMProdSale = "(Card: {$t_cn_priviousMntCard},Cash:{$t_cn_priviousMnthCASH},Credit:{$t_cn_privousMCredit},Bank:{$t_cn_privousMBank},cheque:{$t_cn_privousMCheque})";
    ?>
	<?php
		$special_today_credit_note_sale1 = number_format($special_today_credit_note_sale,2);
		$special_y_credit_note_sale1 = number_format($special_y_credit_note_sale,2);
		$special_this_month_credit_note_sale1 = number_format($special_this_month_credit_note_sale,2);
		$special_prv_month_credit_note_sale1 = number_format($special_prv_month_credit_note_sale,2);
	?>
    <tr>
        <td><strong>credit Quotation</strong></td>
        <td><?php echo $CURRENCY_TYPE.$special_today_credit_note_sale1;?> <a id='t_todayProdSale' alt='<?=$tooltip_t_TodayProdSale;?>', title='<?=$tooltip_t_TodayProdSale;?>'>(Detail)</a></td>
        <td><?php echo $CURRENCY_TYPE.$special_y_credit_note_sale1;?> <a id='t_yesProdSale' alt='<?=$tooltip_t_YesterdayProdSale;?>', title='<?=$tooltip_t_YesterdayProdSale;?>'>(Detail)</a></td>
        <td><?php echo $CURRENCY_TYPE.$special_this_month_credit_note_sale1;?> <a id='t_this_mnth_ProdSale' alt='<?=$tooltip_t_CMProdSale;?>', title='<?=$tooltip_t_CMProdSale;?>'>(Detail)</a></td>
        <td><?php echo $CURRENCY_TYPE.$special_prv_month_credit_note_sale1;?> <a id='t_prv_mnth_ProdSale' alt='<?=$tooltip_t_PMProdSale;?>', title='<?=$tooltip_t_PMProdSale;?>'>(Detail)</a></td>
    </tr>
    
    <?php } ?>
    
	 
	<?php
		$todayProductRefund1 = number_format($todayProductRefund,2);
		$yestdayProductRefund1 = number_format($yestdayProductRefund,2);
		$currentMonthProductRefund1 = number_format($currentMonthProductRefund,2);
		$previousMonthProductRefund1 = number_format($previousMonthProductRefund,2);
	?>
	
   
    
    
    <tr style="color: blue">
        <td><strong>Product Refund</strong></td>
        <td><?php echo $CURRENCY_TYPE.$todayProductRefund1;?></td>
        <td><?php echo $CURRENCY_TYPE.$yestdayProductRefund1;?></td>
        <td><?php echo $CURRENCY_TYPE.$currentMonthProductRefund1;?></td>
        <td><?php echo $CURRENCY_TYPE.$previousMonthProductRefund1;?></td>
    </tr>
<?php
    #pr($todaysCNSale);
?>
   
    
    
    <?php 
        $tooltipTodayBMSale = "(Card: {$todaysBlkMcardPayment},Cash:{$todaysBlkMcashPayment})";
        $tooltipYesterdayBMSale = "(Card: {$yesterdaysBlkMcardPayment},Cash:{$yesterdaysBlkMcashPayment})";
        $tooltipCMBMSale = "(Card: {$currentMonthBlkMcardPayment},Cash:{$currentMonthBlkMcashPayment})";
        $tooltipPMBMSale = "(Card: {$previousMonthBlkMcardPayment},Cash:{$previousMonthBlkMcashPayment})";
    ?>
    
	<?php
		$todayBlkMobileSale1 = number_format($todayBlkMobileSale,2);
		$yesterdayBlkMobileSale1 = number_format($yesterdayBlkMobileSale,2);
		$currentMonthBlkMobileSale1 = number_format($currentMonthBlkMobileSale,2);
		$previousMonthBlkMobileSale1 = number_format($previousMonthBlkMobileSale,2);
	?>
	
    <tr>  
        <td><b>Bulk Mobile Sale</b></td>  
        <td><?php echo $CURRENCY_TYPE.$todayBlkMobileSale1;?> <a id='todayBMSale' alt='<?=$tooltipTodayBMSale;?>', title='<?=$tooltipTodayBMSale;?>'>(Detail)</a></td>
        <td><?php echo $CURRENCY_TYPE.$yesterdayBlkMobileSale1;?> <a id='yesterdayBMSale' alt='<?=$tooltipYesterdayBMSale;?>', title='<?=$tooltipYesterdayBMSale;?>'>(Detail)</a></td>
        <td><?php echo $CURRENCY_TYPE.$currentMonthBlkMobileSale1;?> <a id='cmBMSale' alt='<?=$tooltipCMBMSale;?>', title='<?=$tooltipCMBMSale;?>'>(Detail)</a></td>
        <td><?php echo $CURRENCY_TYPE.$previousMonthBlkMobileSale1;?> <a id='pmBMSale' alt='<?=$tooltipPMBMSale;?>', title='<?=$tooltipPMBMSale;?>'>(Detail)</a></td>
    </tr>
     <script type="text/javascript">
        jQuery('#todayBMSale').tooltip({content:"<?=$tooltipTodayBMSale;?>",track:true});
        jQuery('#yesterdayBMSale').tooltip({content:"<?=$tooltipYesterdayBMSale;?>",track:true});
        jQuery('#cmBMSale').tooltip({content:"<?=$tooltipCMBMSale;?>",track:true});
        jQuery('#pmBMSale').tooltip({content:"<?=$tooltipPMBMSale;?>",track:true});
    </script>
    
    <?php
	if(empty($todayBlkMobileRefund)){
		$todayBlkMobileRefund = 0;
	}
	if(empty($yesterdayBlkMobileRefund)){
		$yesterdayBlkMobileRefund = 0;
	}
	$todayBlkMobileRefund1 = number_format($todayBlkMobileRefund,2);
	$yesterdayBlkMobileRefund1 = number_format($yesterdayBlkMobileRefund,2);
	$currentMonthBlkMobileRefund1 = number_format($currentMonthBlkMobileRefund,2);
	$previousMonthBlkMobileRefund1 = number_format($previousMonthBlkMobileRefund,2);
	?>
     <tr style="color: blue">
        <td><strong>Bulk Mobile Refund</strong></td>
        <td><?php echo $CURRENCY_TYPE.$todayBlkMobileRefund1;?></td>
        <td><?php echo $CURRENCY_TYPE.$yesterdayBlkMobileRefund1;?></td>
        <td><?php echo $CURRENCY_TYPE.$currentMonthBlkMobileRefund1;?></td>
        <td><?php echo $CURRENCY_TYPE.$previousMonthBlkMobileRefund1;?></td>
    </tr>
      
    <?php
        $tooltipTodayMobSale = "(Card: {$todaysMcardPayment},Cash:{$todaysMcashPayment})";
        $tooltipYesterdayMobSale = "(Card: {$yesterdaysMcardPayment},Cash:{$yesterdaysMcashPayment})";
        $tooltipCMMobSale = "(Card: {$currentMonthMcardPayment},Cash:{$currentMonthMcashPayment})";
        $tooltipPMMobSale = "(Card: {$previousMonthMcardPayment},Cash:{$previousMonthMcashPayment})";
    ?>
	
	<?php
		$todayMobileSale1 = number_format($todayMobileSale,2);
		$yesterdayMobileSale1 = number_format($yesterdayMobileSale,2);
		$currentMonthMobileSale1 = number_format($currentMonthMobileSale,2);
		$previousMonthMobileSale1 = number_format($previousMonthMobileSale,2);
	?>
    <tr>   
      <td><strong>Mobile Sale</strong></td>   
     <td><?php echo $CURRENCY_TYPE.$todayMobileSale1;?> <a id='todayMobSale' alt='<?=$tooltipTodayMobSale;?>', title='<?=$tooltipTodayMobSale;?>'>(Detail)</a></td>
      <td><?php echo $CURRENCY_TYPE.$yesterdayMobileSale1;?> <a id='yesterdayMobSale' alt='<?=$tooltipYesterdayMobSale;?>', title='<?=$tooltipYesterdayMobSale;?>'>(Detail)</a></td>
      <td><?php echo $CURRENCY_TYPE.$currentMonthMobileSale1;?> <a id='cmMobSale' alt='<?=$tooltipCMMobSale;?>', title='<?=$tooltipCMMobSale;?>'>(Detail)</a></td>
      <td><?php echo $CURRENCY_TYPE.$previousMonthMobileSale1;?> <a id='pmMobSale' alt='<?=$tooltipPMMobSale;?>', title='<?=$tooltipPMMobSale;?>'>(Detail)</a></td>
    </tr>
    <script type="text/javascript">
        jQuery('#todayMobSale').tooltip({content:"<?=$tooltipTodayMobSale;?>",track:true});
        jQuery('#yesterdayMobSale').tooltip({content:"<?=$tooltipYesterdayMobSale;?>",track:true});
        jQuery('#cmMobSale').tooltip({content:"<?=$tooltipCMMobSale;?>",track:true});
        jQuery('#pmMobSale').tooltip({content:"<?=$tooltipPMMobSale;?>",track:true});
    </script>
    
    <?php
		$todayMobilePurchase1 = number_format($todayMobilePurchase,2);
		$yesterdayMobilePurchase1 = number_format($yesterdayMobilePurchase,2);
		$currentMonthMobilePurchase1 = number_format($currentMonthMobilePurchase,2);
		$previousMonthMobilePurchase1 = number_format($previousMonthMobilePurchase,2);
	?>
    <tr>
        <td><strong>Mobile Purchase</strong></td>
        <td><?php echo $CURRENCY_TYPE.$todayMobilePurchase1;?></td>
        <td><?php echo $CURRENCY_TYPE.$yesterdayMobilePurchase1;?></td>
        <td><?php echo $CURRENCY_TYPE.$currentMonthMobilePurchase1;?></td>
        <td><?php echo $CURRENCY_TYPE.$previousMonthMobilePurchase1;?></td>
    </tr>
	<?php
	if(empty($todayMobileRefund)){
		$todayMobileRefund = 0;
	}
	if(empty($yesterdayMobileRefund)){
		$yesterdayMobileRefund = 0;
	}
	$todayMobileRefund1 = number_format($todayMobileRefund,2);
	$yesterdayMobileRefund1 = number_format($yesterdayMobileRefund,2);
	$currentMonthMobileRefund1 = number_format($currentMonthMobileRefund,2);
	$previousMonthMobileRefund1 = number_format($previousMonthMobileRefund,2);
	?>
	
	
    <tr style="color: blue">
        <td><strong>Mobile Refund</strong></td>
        <td><?php echo $CURRENCY_TYPE.$todayMobileRefund1;?></td>
        <td><?php echo $CURRENCY_TYPE.$yesterdayMobileRefund1;?></td>
        <td><?php echo $CURRENCY_TYPE.$currentMonthMobileRefund1;?></td>
        <td><?php echo $CURRENCY_TYPE.$previousMonthMobileRefund1;?></td>
    </tr>
   
    <?php
        $todayTotalSale = $todaysSale+$todaysUsale+$todayProductSale+$todayMobileSale+$todayBlkMobileSale;
        //echo "$yesterdaySale+$yesterdayUsale+$yesterdayProductSale+$yesterdayMobileSale+$yesterdayBlkMobileSale";
        $yesterdayTotalSale = $yesterdaySale+$yesterdayUsale+$yesterdayProductSale+$yesterdayMobileSale+$yesterdayBlkMobileSale;
        $currentMonthTotalSale = $currentMonthRepairSale+$currentMonthUnlockSale+$currentMonthProductSale+$currentMonthMobileSale+$currentMonthBlkMobileSale;
        $previousMonthTotalSale = $previousMonthRepairSale+$previousMonthUnlockSale+$previousMonthProductSale+$previousMonthMobileSale+$previousMonthBlkMobileSale;
        $todayTotalCard = $todaysRcardPayment + $todaysUcardPayment + $card + $todaysMcardPayment;
        $yesterdayTotalCard = $yesterdaysRcardPayment+$yesterdaysUcardPayment+$yCard+$yesterdaysMcardPayment;
        $currentMonthTotalCard = $currentMonthRcardPayment+$currentMonthUcardPayment+$cMCard+$currentMonthMcardPayment;
        $previousMonthTotalCard = $previousMonthRcardPayment+$previousMonthUcardPayment+$priviousMntCard+$previousMonthMcardPayment;
        
        if(preg_match('/'.QUOT_USER_PREFIX.'/',$loggedInUser)){
            $todayTotalSale1 = $todayTotalSale + $t_today_total_amount;
            $yesterdayTotalSale1 = $yesterdayTotalSale + $t_yes_total_amount;
            $currentMonthTotalSale1 = $currentMonthTotalSale + $t_this_mnth_total_amount;
            $previousMonthTotalSale1 = $previousMonthTotalSale + $t_prv_mnth_total_amount;
        }else{
            $todayTotalSale1 = $todayTotalSale;
            $yesterdayTotalSale1 = $yesterdayTotalSale;
            $currentMonthTotalSale1 = $currentMonthTotalSale;
            $previousMonthTotalSale1 = $previousMonthTotalSale;
        }
        
        
    ?>
	<?php
	$todayTotalSale1_1 = number_format($todayTotalSale1,2);
	$yesterdayTotalSale1_1 = number_format($yesterdayTotalSale1,2);
	$currentMonthTotalSale1_1 = number_format($currentMonthTotalSale1,2);
	$previousMonthTotalSale1_1 = number_format($previousMonthTotalSale1,2);
	?>
	
    <tr>
        <td><strong>Total Sale</strong></td>
        <td><?php echo $CURRENCY_TYPE.$todayTotalSale1_1;?></td>
        <td><?php echo $CURRENCY_TYPE.$yesterdayTotalSale1_1;?></td>
        <td><?php echo $CURRENCY_TYPE.$currentMonthTotalSale1_1;?></td>
        <td><?php echo $CURRENCY_TYPE.$previousMonthTotalSale1_1;?></td>
    </tr>
    
    <?php
    $t_today_cn_sale = array_sum($t_todaysCNSale)- $t_todaysCNSale['cashEntryAmt'] ;
	$t_yes_cn_sale = array_sum($t_yesterdayCNSale) - $t_yesterdayCNSale['cashEntryAmt'];
	$t_cm_cn_sale = array_sum($t_currMonthCNSale);
	$t_prv_mn_cn_sale = array_sum($t_prevMonthCNSale);
    
    
        $todayCNCardRefund = array_sum($todaysCNSale)-$todaysCNSale['cashEntryAmt'];//- $todaysCNSale['cash'];
        $todayTotalRefund = $todaysRefund+$todaysUrefund+$todayProductRefund+$todayMobileRefund + $todayCNCardRefund + $todayBlkMobileRefund;
         if(preg_match('/'.QUOT_USER_PREFIX.'/',$loggedInUser)){
			$todayTotalRefund1 = $todayTotalRefund+$t_today_cn_sale;
		}else{
			$todayTotalRefund1 = $todayTotalRefund;
		}
        $yesterdayCNCardRefund = array_sum($yesterdayCNSale) - $yesterdayCNSale['cashEntryAmt'];
        $yesterdayTotalRefund = $yesterdaysRefund+$yesterdaysUrefund+$yestdayProductRefund+$yesterdayMobileRefund + $yesterdayCNCardRefund+$yesterdayBlkMobileRefund;
        
        if(preg_match('/'.QUOT_USER_PREFIX.'/',$loggedInUser)){
			$yesterdayTotalRefund1 = $yesterdayTotalRefund+$t_yes_cn_sale;
		}else{
			$yesterdayTotalRefund1 = $yesterdayTotalRefund;
		}
        
        $todayNetSale = round($todayTotalSale,2) - round($todayTotalRefund,2);
        $yesterdayNetSale = $yesterdayTotalSale - $yesterdayTotalRefund;
        
        $currMonthCNCardRefund = array_sum($currMonthCNSale);
        $currentMonthTotalRefund = $currentMonthRepairRefund + $currentMonthUnlockRefund + $currentMonthProductRefund + $currentMonthMobileRefund + $currMonthCNCardRefund+$currentMonthBlkMobileRefund;
        
         if(preg_match('/'.QUOT_USER_PREFIX.'/',$loggedInUser)){
			$currentMonthTotalRefund1 = $currentMonthTotalRefund+$t_cm_cn_sale;
		}else{
			$currentMonthTotalRefund1 = $currentMonthTotalRefund;
		}
        
        $prevMonthCNCardRefund = array_sum($prevMonthCNSale);
        $previousMonthTotalRefund = $previousMonthRepairRefund + $previousMonthUnlockRefund + $previousMonthProductRefund + $previousMonthMobileRefund+ $prevMonthCNCardRefund+$previousMonthBlkMobileRefund;
        
        if(preg_match('/'.QUOT_USER_PREFIX.'/',$loggedInUser)){
			$previousMonthTotalRefund1 = $previousMonthTotalRefund+$t_prv_mn_cn_sale;
		}else{
			$previousMonthTotalRefund1 = $previousMonthTotalRefund;
		}
        
        $currentMonthNetSale = $currentMonthTotalSale-$currentMonthTotalRefund;
        $previousMonthNetSale = $previousMonthTotalSale-$previousMonthTotalRefund;
    ?>
	<?php
		$todayTotalRefund1_1 = number_format($todayTotalRefund1,2);
		$yesterdayTotalRefund1_1 = number_format($yesterdayTotalRefund1,2);
		$currentMonthTotalRefund1_1 = number_format($currentMonthTotalRefund1,2);
		$previousMonthTotalRefund1_1 = number_format($previousMonthTotalRefund1,2);
	?>
	
    <tr style="color: blue">
        <td><strong>Total Refund</strong></td>
        <td><?php echo $CURRENCY_TYPE.$todayTotalRefund1_1;?></td>
        <td><?php echo $CURRENCY_TYPE.$yesterdayTotalRefund1_1;?></td>
        <td><?php echo $CURRENCY_TYPE.$currentMonthTotalRefund1_1;?></td>
        <td><?php echo $CURRENCY_TYPE.$previousMonthTotalRefund1_1;?></td>
    </tr>
    <tr>
        <?php
		
            if(preg_match('/'.QUOT_USER_PREFIX.'/',$loggedInUser)){
                $todayNetSale = $todayNetSale + $t_today_total_amount - $special_today_credit_note_sale;
                $yesterdayNetSale = $yesterdayNetSale + $t_yes_total_amount - $special_y_credit_note_sale;
                $currentMonthNetSale = $currentMonthNetSale + $t_this_mnth_total_amount - $special_this_month_credit_note_sale;
                $previousMonthNetSale = $previousMonthNetSale + $t_prv_mnth_total_amount - $special_prv_month_credit_note_sale;
            }
			$todayNetSale1 = number_format($todayNetSale,2);
			$yesterdayNetSale1 = number_format($yesterdayNetSale,2);
			$currentMonthNetSale1 = number_format($currentMonthNetSale,2);
			$previousMonthNetSale1 = number_format($previousMonthNetSale,2);
			
        ?>
        <td style="color: black"><strong>Net Sale</strong></td>
        <td><?php echo $CURRENCY_TYPE.$todayNetSale1;?></td>
        <td><?php echo $CURRENCY_TYPE.$yesterdayNetSale1;?></td>
        <td><?php echo $CURRENCY_TYPE.$currentMonthNetSale1;?></td>
        <td><?php echo $CURRENCY_TYPE.$previousMonthNetSale1;?></td>
    </tr>
    
    <?php
    $pCash = $todayCash+ $todayProductPmtDetails['cash'];
        $total_cash = $todaysRcashPayment + $todaysUcashPayment + $pCash + $todaysBlkMcashPayment + $todaysMcashPayment ;
        if(preg_match('/'.QUOT_USER_PREFIX.'/',$loggedInUser)){
            $total_cash = $total_cash + $t_cash + $t_credit_to_other_changed['cash'];
        }
        $total_refund = $todaysRefund + $todaysUrefund + $todayProductRefund + $todayMobileRefund + $todayBlkMobileRefund + $todaysCNSale['cash'] + $todayMobilePurchase + $todaysCNSale['cashEntryAmt'];
        
        $total_cash = $total_cash + $credit_to_other_changed['cash'];
        $end_today_cash = round($total_cash,2) - round($total_refund,2);
		 if(preg_match('/'.QUOT_USER_PREFIX.'/',$loggedInUser)){
			$today_cn_cash_sp = $t_todaysCNSale['cash'] + $t_todaysCNSale['cashEntryAmt'];
			$total_refund = $total_refund+$today_cn_cash_sp;
			$end_today_cash = round($end_today_cash,2)-round($today_cn_cash_sp,2);
		 }
         
			
        //tooltip for today's cash
         if(preg_match('/'.QUOT_USER_PREFIX.'/',$loggedInUser)){
                    $prv_crdit_to_cash = $t_credit_to_other_changed['cash'];
                        $tooltipTodayNetCashDetail = "(Repair({$todaysRcashPayment})+Unlock({$todaysUcashPayment})+Product({$pCash})+Blk({$todaysBlkMcashPayment})+Mobile({$todaysMcashPayment})+special({$t_cash})+prv_recpit_amt({$prv_cash})+ prv_credit_to_cash({$prv_crdit_to_cash}))=  $total_cash";
                }else{
                    $tooltipTodayNetCashDetail = "(Repair({$todaysRcashPayment})+Unlock({$todaysUcashPayment})+Product({$pCash})+Blk({$todaysBlkMcashPayment})+Mobile({$todaysMcashPayment})+ prv_recpit_amt({$prv_cash}))= $total_cash";
                }
                $todayCNSaleCash = $todaysCNSale['cash']+$todaysCNSale['cashEntryAmt'];
                if(preg_match('/'.QUOT_USER_PREFIX.'/',$loggedInUser)){
                    $tooltipTodayNetCashDetail.=", Refund(Repair({$todaysRefund})+Unlock({$todaysUrefund})+Product({$todayProductRefund})+Mobile({$todayMobileRefund})+Blk({$todayBlkMobileRefund})+Credit Note({$todayCNSaleCash})+Mobile Purchase({$todayMobilePurchase})+special credit note({$today_cn_cash_sp}))= ({$total_cash} - {$total_refund}) = {$end_today_cash}";
                }else{
                    $tooltipTodayNetCashDetail.=", Refund(Repair({$todaysRefund})+Unlock({$todaysUrefund})+Product({$todayProductRefund})+Mobile({$todayMobileRefund})+Blk({$todayBlkMobileRefund})+Credit Note({$todayCNSaleCash})+Mobile Purchase({$todayMobilePurchase}))=({$total_cash} - {$total_refund}) = {$end_today_cash}";
                }
        
           
           $pYCash = $yesCash+ $yesterdayProductPmtDetails['cash'];
        $yCash = $yesterdaysRcashPayment + $yesterdaysUcashPayment + $pYCash + $yesterdaysBlkMcashPayment + $yesterdaysMcashPayment ;
        if(preg_match('/'.QUOT_USER_PREFIX.'/',$loggedInUser)){
            $yCash = $yCash + $t_yTotalCash;
        }
        $yRefund = $yesterdaysRefund + $yesterdaysUrefund + $yestdayProductRefund + $yesterdayMobileRefund + $yesterdayBlkMobileRefund +$yesterdayCNSale['cash'] + $yesterdayMobilePurchase + $yesterdayCNSale['cashEntryAmt'];
        $end_y_cash = $yCash - $yRefund;
		if(preg_match('/'.QUOT_USER_PREFIX.'/',$loggedInUser)){
			$yes_cn_cash_sp = $t_yesterdayCNSale['cash'] + $t_yesterdayCNSale['cashEntryAmt'];
			$yRefund = $yRefund + $yes_cn_cash_sp;
			$end_y_cash = $end_y_cash-$yes_cn_cash_sp;
			$end_y_cash = $end_y_cash + $t_y_prv_credit_to_card['cash'];
		}
       $end_y_cash  = $end_y_cash +$y_credit_to_other_changed['cash'];
        //tooltip for yesterdays's cash
		$end_y_cash = round($end_y_cash,2);
		$pYCash = round($pYCash,2);
         if(preg_match('/'.QUOT_USER_PREFIX.'/',$loggedInUser)){
					$special_changed_cash = $t_y_prv_credit_to_card['cash'];
                    $yCash = $yCash+ $special_changed_cash;
					$yCash = round($yCash,2);
                        $tooltipYesterdayNetCashDetail = "(Repair({$yesterdaysRcashPayment})+Unlock({$yesterdaysUcashPayment})+Product({$pYCash})+Blk({$yesterdaysBlkMcashPayment})+Mobile({$yesterdaysMcashPayment})+special({$t_yTotalCash})+ prv_quotation_cash({$special_changed_cash})) = $yCash ";
                }else{
                    $tooltipYesterdayNetCashDetail = "(Repair({$yesterdaysRcashPayment})+Unlock({$yesterdaysUcashPayment})+Product({$pYCash})+Blk({$yesterdaysBlkMcashPayment})+Mobile({$yesterdaysMcashPayment}))= $yCash ";
                }
                $yesterdayCNSaleCash = $yesterdayCNSale['cash'] + $yesterdayCNSale['cashEntryAmt'];
				
                if(preg_match('/'.QUOT_USER_PREFIX.'/',$loggedInUser)){
					$special_entry_yes = $t_yesterdayCNSale['cashEntryAmt'];
					$yCash = round($yCash,2);
					$special_credit_note = $t_yesterdayCNSale['cash'];
                    $tooltipYesterdayNetCashDetail.="Refund(Repair({$yesterdaysRefund})+Unlock({$yesterdaysUrefund})+Product({$yestdayProductRefund})+Mobile({$yesterdayMobileRefund})+Blk({$yesterdayBlkMobileRefund})+Credit Note({$yesterdayCNSaleCash})+Mobile Purchase({$yesterdayMobilePurchase})+special credit note({$special_credit_note})+ credit_to_cash_credit_note({$special_entry_yes}))= ({$yCash} - {$yRefund}) = {$end_y_cash}";
                }else{
                    $tooltipYesterdayNetCashDetail.="Refund(Repair({$yesterdaysRefund})+Unlock({$yesterdaysUrefund})+Product({$yestdayProductRefund})+Mobile({$yesterdayMobileRefund})+Blk({$yesterdayBlkMobileRefund})+Credit Note({$yesterdayCNSaleCash})+Mobile Purchase({$yesterdayMobilePurchase}))= ({$yCash} - {$yRefund}) = {$end_y_cash}";
                }
        
           
        $this_mnth_cash = $currentMonthProductPmtDetails['cash'] + $currentMonthRcashPayment + $currentMonthUcashPayment + $currentMonthMcashPayment + $currentMonthBlkMcashPayment + $thisMnthCash;
        
        if(preg_match('/'.QUOT_USER_PREFIX.'/',$loggedInUser)){
            $this_mnth_cash = $this_mnth_cash + $t_cMTotalCash;
        }
        
        $this_mnth_refund = $currentMonthProductRefund + $currentMonthRepairRefund + $currentMonthUnlockRefund + $currentMonthMobileRefund + $currentMonthBlkMobileRefund + $currentMonthMobilePurchase + $currMonthCNSale['cash'] + $currMonthCNSale['cashEntryAmt'];
           
        $this_mnth_sale = $this_mnth_cash - $this_mnth_refund;
        if(preg_match('/'.QUOT_USER_PREFIX.'/',$loggedInUser)){
			$cm_cn_cash_sp = $t_currMonthCNSale['cash'] + $t_currMonthCNSale['cashEntryAmt'];
			$this_mnth_refund = $this_mnth_refund + $cm_cn_cash_sp;
			$this_mnth_sale = $this_mnth_sale-$cm_cn_cash_sp;
		}
		
        //tooltip for current months's cash
        $cmProdCash = $currentMonthProductPmtDetails['cash'] + $thisMnthCash;
		 $cmProdCash = round($cmProdCash,2);
         
                    $this_mnth_cash = round($this_mnth_cash,2);
                    $this_mnth_sale = round($this_mnth_sale,2);
                 if(preg_match('/'.QUOT_USER_PREFIX.'/',$loggedInUser)){
                        $tooltipCMNetCashDetail = "(Repair({$currentMonthRcashPayment})+Unlock({$currentMonthUcashPayment})+Product({$cmProdCash})+Blk({$currentMonthBlkMcashPayment})+Mobile({$currentMonthMcashPayment})+special({$t_cMTotalCash}))= $this_mnth_cash";
                    }else{
                        $tooltipCMNetCashDetail = "(Repair({$currentMonthRcashPayment})+Unlock({$currentMonthUcashPayment})+Product({$cmProdCash})+Blk({$currentMonthBlkMcashPayment})+Mobile({$currentMonthMcashPayment}))= $this_mnth_cash ";
                    }
                    
                    $cmCNSaleCash = $currMonthCNSale['cash'] + $currMonthCNSale['cashEntryAmt'];
                    if(preg_match('/'.QUOT_USER_PREFIX.'/',$loggedInUser)){
                        $tooltipCMNetCashDetail.="Refund(Repair({$currentMonthRepairRefund})+Unlock({$currentMonthUnlockRefund})+Product({$currentMonthProductRefund})+Mobile({$currentMonthMobileRefund})+Blk({$currentMonthBlkMobileRefund})+Credit Note({$cmCNSaleCash})+Mobile Purchase({$currentMonthMobilePurchase})+special credit note({$cm_cn_cash_sp}))= ({$this_mnth_cash} - {$this_mnth_refund}) = {$this_mnth_sale}";
                    }else{
                        $tooltipCMNetCashDetail.="Refund(Repair({$currentMonthRepairRefund})+Unlock({$currentMonthUnlockRefund})+Product({$currentMonthProductRefund})+Mobile({$currentMonthMobileRefund})+Blk({$currentMonthBlkMobileRefund})+Credit Note({$cmCNSaleCash})+Mobile Purchase({$currentMonthMobilePurchase}))= ({$this_mnth_cash} - {$this_mnth_refund}) = {$this_mnth_sale}";
                    }
                    
        
           
        $privous_mnth_cash = $previousMonthProductPmtDetails['cash'] + $previousMonthRcashPayment + $previousMonthUcashPayment + $previousMonthBlkMcashPayment + $previousMonthMcashPayment +$prvMnthCash;
        if(preg_match('/'.QUOT_USER_PREFIX.'/',$loggedInUser)){
            $privous_mnth_cash = $privous_mnth_cash + $t_priviousMnthCASH;
        }
        $privious_mnth_refund = $previousMonthProductRefund + $previousMonthRepairRefund + $previousMonthUnlockRefund + $previousMonthBlkMobileRefund + $previousMonthMobileRefund + $previousMonthMobilePurchase + $prevMonthCNSale['cash'] + $prevMonthCNSale['cashEntryAmt'];
        $privious_mnth_sale = $privous_mnth_cash - $privious_mnth_refund;
        if(preg_match('/'.QUOT_USER_PREFIX.'/',$loggedInUser)){
			$prv_mnth_cn_cash_sp = $t_prevMonthCNSale['cash']  + $t_prevMonthCNSale['cashEntryAmt'];
			$privious_mnth_refund = $privious_mnth_refund+$prv_mnth_cn_cash_sp;
			$privious_mnth_sale = $privious_mnth_sale-$prv_mnth_cn_cash_sp;
		}
		
        //tooltip for previous months's cash
        $pmProdCash = $previousMonthProductPmtDetails['cash'] + $prvMnthCash;
		$pmProdCash = round($pmProdCash,2);
        $privious_mnth_sale = round($privious_mnth_sale,2);
                    $privous_mnth_cash = round($privous_mnth_cash,2);
                    if(preg_match('/'.QUOT_USER_PREFIX.'/',$loggedInUser)){
                        $tooltipPMNetCashDetail = "(Repair({$previousMonthRcashPayment})+Unlock({$previousMonthUcashPayment})+Product({$pmProdCash})+Blk({$previousMonthBlkMobileRefund})+Mobile({$previousMonthMcashPayment})+special({$t_priviousMnthCASH}))= $privous_mnth_cash";
                        }else{
                            $tooltipPMNetCashDetail = "(Repair({$previousMonthRcashPayment})+Unlock({$previousMonthUcashPayment})+Product({$pmProdCash})+Blk({$previousMonthBlkMobileRefund})+Mobile({$previousMonthMcashPayment}))= $privous_mnth_cash";
                        }
                        $pmCNSaleCash = $prevMonthCNSale['cash'] + $prevMonthCNSale['cashEntryAmt'];
                        
                        if(preg_match('/'.QUOT_USER_PREFIX.'/',$loggedInUser)){
                            $tooltipPMNetCashDetail.="Refund(Repair({$previousMonthRepairRefund})+Unlock({$previousMonthUnlockRefund})+Product({$previousMonthProductRefund})+Mobile({$previousMonthMobileRefund})+Blk({$previousMonthBlkMobileRefund})+Credit Note({$pmCNSaleCash})+Mobile Purchase({$previousMonthMobilePurchase})- special credit note({$prv_mnth_cn_cash_sp}))= ({$privous_mnth_cash} - {$privious_mnth_refund}) = {$privious_mnth_sale}";
                        }else{
                            $tooltipPMNetCashDetail.="Refund(Repair({$previousMonthRepairRefund})+Unlock({$previousMonthUnlockRefund})+Product({$previousMonthProductRefund})+Mobile({$previousMonthMobileRefund})+Blk({$previousMonthBlkMobileRefund})+Credit Note({$pmCNSaleCash})+Mobile Purchase({$previousMonthMobilePurchase}))=({$privous_mnth_cash} - {$privious_mnth_refund}) = {$privious_mnth_sale}";
                        }
        
    ?>
    
    
   <?php
        $CardPaymt = $todaysRcardPayment+$todaysUcardPayment+$card+$todaysBlkMcardPayment+$todaysMcardPayment;
        if(preg_match('/'.QUOT_USER_PREFIX.'/',$loggedInUser)){
            $CardPaymt = $CardPaymt + $t_card;
        }
        $CardPaymt = $CardPaymt; //+ $prv_card;
        $today_card_paymt = $CardPaymt - $todaysCNSale['card'];
		if(preg_match('/'.QUOT_USER_PREFIX.'/',$loggedInUser)){
			$today_card_paymt = $today_card_paymt-$t_todaysCNSale['card'];
		}
		
        $yCardPaymt = $yesterdaysRcardPayment + $yesterdaysUcardPayment + $yCard + $yesterdaysBlkMcardPayment + $yesterdaysMcardPayment;
        
        if(preg_match('/'.QUOT_USER_PREFIX.'/',$loggedInUser)){
            $yCardPaymt = $yCardPaymt + $t_yCard;
        }
        
        $y_card_paymt = $yCardPaymt - $yesterdayCNSale['card'];
		if(preg_match('/'.QUOT_USER_PREFIX.'/',$loggedInUser)){
            $y_card_paymt = $y_card_paymt - $t_yesterdayCNSale['card'];
        }
		
        $cMCardPaymt = $currentMonthRcardPayment + $currentMonthUcardPayment + $cMCard + $currentMonthBlkMcardPayment + $currentMonthMcardPayment;
        
        if(preg_match('/'.QUOT_USER_PREFIX.'/',$loggedInUser)){
            $cMCardPaymt  = $cMCardPaymt + $t_cMCard;
        }
        
        $cM_card_paymt = $cMCardPaymt - $currMonthCNSale['card'];
		if(preg_match('/'.QUOT_USER_PREFIX.'/',$loggedInUser)){
			$cM_card_paymt = $cM_card_paymt-$t_currMonthCNSale['card'];
		}
        $prvsMNthCardPaymt = $previousMonthRcashPayment + $previousMonthUcardPayment + $priviousMntCard + $previousMonthBlkMcardPayment +$previousMonthMcardPayment;
        
        if(preg_match('/'.QUOT_USER_PREFIX.'/',$loggedInUser)){
            $prvsMNthCardPaymt  = $prvsMNthCardPaymt + $t_priviousMntCard;
        }
        $prv_Mnth_card = $prvsMNthCardPaymt - $prevMonthCNSale['card'];
		if(preg_match('/'.QUOT_USER_PREFIX.'/',$loggedInUser)){
			$prv_Mnth_card = $prv_Mnth_card-$t_prevMonthCNSale['card'];
		}
		
   ?>
    <?php
	$todayCNSaleCard = $todaysCNSale['card'];
        if(preg_match('/'.QUOT_USER_PREFIX.'/',$loggedInUser)){
			//$CardPaymt = $CardPaymt + $t_card;
			$spcl_cn_card = $t_todaysCNSale['card'];
        $tooltipTodayNetCardDetail = "(Repair({$todaysRcardPayment})+Unlock({$todaysUcardPayment})+Product({$card})+Blk({$todaysBlkMcardPayment})+Mobile({$todaysMcardPayment})+special({$t_card})+prev_recpts_sale({$prv_card})-credit note({$todayCNSaleCard})-special credit note({$spcl_cn_card}))= {$CardPaymt}";
        }else{
            $tooltipTodayNetCardDetail = "(Repair({$todaysRcardPayment})+Unlock({$todaysUcardPayment})+Product({$card})+Blk({$todaysBlkMcardPayment})+Mobile({$todaysMcardPayment})+prev_recpts_sale({$prv_card})-credit note({$todayCNSaleCard})) = {$CardPaymt}";
        }
         if(preg_match('/'.QUOT_USER_PREFIX.'/',$loggedInUser)){
            $todayCNSaleCard = $todayCNSaleCard + $spcl_cn_card;    
        }
        $tooltipTodayNetCardDetail.="({$CardPaymt} - {$todayCNSaleCard}) = {$today_card_paymt}";
        
		
		$yCNSaleCard = $yesterdayCNSale['card'];
        if(preg_match('/'.QUOT_USER_PREFIX.'/',$loggedInUser)){
			//$yCardPaymt = $yCardPaymt + $t_yCard;
			$yes_sp_credit_note = $t_yesterdayCNSale['card'];
         $tooltipYNetCardDetail = "(Repair({$yesterdaysRcardPayment})+Unlock({$yesterdaysUcardPayment})+Product({$yCard})+Blk({$yesterdaysBlkMcardPayment})+Mobile({$yesterdaysMcardPayment})+special({$t_yCard})-credit note({$yCNSaleCard})-special credit note({$yes_sp_credit_note}))= {$yCardPaymt}";
        }else{
            $tooltipYNetCardDetail = "(Repair({$yesterdaysRcardPayment})+Unlock({$yesterdaysUcardPayment})+Product({$yCard})+Blk({$yesterdaysBlkMcardPayment})+Mobile({$yesterdaysMcardPayment})-credit note({$yCNSaleCard}))= {$yCardPaymt}";
        }
        
         if(preg_match('/'.QUOT_USER_PREFIX.'/',$loggedInUser)){
            $yCNSaleCard = $yCNSaleCard + $yes_sp_credit_note;    
        }
        $tooltipYNetCardDetail.="({$yCardPaymt} - {$yCNSaleCard}) = {$y_card_paymt}";
        
		$mCNSaleCard = $currMonthCNSale['card'];
		
        if(preg_match('/'.QUOT_USER_PREFIX.'/',$loggedInUser)){
			//$cMCardPaymt = $cMCardPaymt + $t_cMCard;
			$cn_currnt_mnth_special = $t_currMonthCNSale['card'];
                $tooltipMNetCardDetail = "(Repair({$currentMonthRcardPayment})+Unlock({$currentMonthUcardPayment})+Product({$cMCard})+Blk({$currentMonthBlkMcardPayment})+Mobile({$currentMonthMcardPayment})+special({$t_cMCard})-credit note({$mCNSaleCard})-special credit({$cn_currnt_mnth_special}))= {$cMCardPaymt}";
        }else{
            $tooltipMNetCardDetail = "(Repair({$currentMonthRcardPayment})+Unlock({$currentMonthUcardPayment})+Product({$cMCard})+Blk({$currentMonthBlkMcardPayment})+Mobile({$currentMonthMcardPayment})-credit note({$mCNSaleCard}))= {$cMCardPaymt}";
        }
        
		 if(preg_match('/'.QUOT_USER_PREFIX.'/',$loggedInUser)){
            $mCNSaleCard = $mCNSaleCard + $cn_currnt_mnth_special;    
        }
        $tooltipMNetCardDetail.="({$cMCardPaymt} - {$mCNSaleCard}) = {$cM_card_paymt}";
        
		$pmCNSaleCard = $prevMonthCNSale['card'];
        if(preg_match('/'.QUOT_USER_PREFIX.'/',$loggedInUser)){
			//$prvsMNthCardPaymt = $prvsMNthCardPaymt + $t_priviousMntCard;
			$prv_mnth_sp_credit_note = $t_prevMonthCNSale['card'];
            $tooltipPMNetCardDetail = "(Repair({$previousMonthRcashPayment})+Unlock({$previousMonthUcardPayment})+Product({$priviousMntCard})+Blk({$previousMonthBlkMcardPayment})+Mobile({$previousMonthMcardPayment})+special({$t_priviousMntCard})-credit note({$pmCNSaleCard})-special credit note({$prv_mnth_sp_credit_note}))= {$prvsMNthCardPaymt}<br/>";
        }else{
            $tooltipPMNetCardDetail = "(Repair({$previousMonthRcashPayment})+Unlock({$previousMonthUcardPayment})+Product({$priviousMntCard})+Blk({$previousMonthBlkMcardPayment})+Mobile({$previousMonthMcardPayment})-credit note({$pmCNSaleCard}))= {$prvsMNthCardPaymt}<br/>";
        }
        
        
		if(preg_match('/'.QUOT_USER_PREFIX.'/',$loggedInUser)){
            $pmCNSaleCard = $pmCNSaleCard + $prv_mnth_sp_credit_note;    
        }
		
        $tooltipPMNetCardDetail.="({$prvsMNthCardPaymt} - {$pmCNSaleCard}) = {$prv_Mnth_card}";
    
        //$today_card_paymt = $today_card_paymt + $credit_to_other_changed['card'];
    ?>
    <?php
	$today_card_paymt1 = number_format($today_card_paymt,2);
	$y_card_paymt1 = number_format($y_card_paymt,2);
	$cM_card_paymt1 = number_format($cM_card_paymt,2);
	$prv_Mnth_card1 = number_format($prv_Mnth_card,2);
	?>
    <tr style="color: black">
        <td ><b>Net Card</b></td>
        <td><?php echo $CURRENCY_TYPE.$today_card_paymt1; ?> <a id='todayNetCard' title='<?=$tooltipTodayNetCardDetail?>' alt='<?=$tooltipTodayNetCardDetail?>'>(Detail)</a></td>
        <td><?php echo $CURRENCY_TYPE.$y_card_paymt1; ?> <a id='yesterdayNetCard' title='<?=$tooltipYNetCardDetail?>' alt='<?=$tooltipYNetCardDetail?>'>(Detail)</a></td>
        <td><?php echo $CURRENCY_TYPE.$cM_card_paymt1; ?> <a id='cmNetCard' title='<?=$tooltipMNetCardDetail?>' alt='<?=$tooltipMNetCardDetail?>'>(Detail)</a></td>
        <td><?php echo $CURRENCY_TYPE.$prv_Mnth_card1;?> <a id='pmNetCard' title='<?=$tooltipPMNetCardDetail?>' alt='<?=$tooltipPMNetCardDetail?>'>(Detail)</a></td>
    </tr>
    
    <?php
        $tcreidt = $todaysCNSale['credit'];
		if(preg_match('/'.QUOT_USER_PREFIX.'/',$loggedInUser)){
             $tcreidt = $tcreidt + $t_todaysCNSale['credit'];
        }	
        $ycreidt = $yesterdayCNSale['credit'];
		if(preg_match('/'.QUOT_USER_PREFIX.'/',$loggedInUser)){
             $ycreidt = $ycreidt + $t_yesterdayCNSale['credit'];
        }	
		
        $cMcreidt = $currMonthCNSale['credit'];
		if(preg_match('/'.QUOT_USER_PREFIX.'/',$loggedInUser)){
             $cMcreidt = $cMcreidt + $t_currMonthCNSale['credit'];
        }	
		
        $pMcreidt = $prevMonthCNSale['credit'];
		if(preg_match('/'.QUOT_USER_PREFIX.'/',$loggedInUser)){
             $pMcreidt = $pMcreidt + $t_prevMonthCNSale['credit'];
        }
		
    
        $totdayCridit = $totdayCridit + $prv_credit;
        $todayCridit =  $totdayCridit - $todaysCNSale['credit'];
        $yCreidt =  $yCridit - $yesterdayCNSale['credit'];
        $cMCriedt =  $cMCridit - $currMonthCNSale['credit'];
        $privousMnthCredit =  $privousMCridt - $prevMonthCNSale['credit'];
        
        if(preg_match('/'.QUOT_USER_PREFIX.'/',$loggedInUser)){
			$today_credit_sp = $t_todaysCNSale['credit'];
			$yes_credit_sp = $t_yesterdayCNSale['credit'];
			$cm_credit_sp = $t_currMonthCNSale['credit'];
			$prv_mnth_credit_sp = $t_prevMonthCNSale['credit'];
            
                $t_todayCredit1 = $t_today_pay_details['credit'];
                $totdayCridit = $totdayCridit + $t_todayCredit1;
                
                $t_yCredit1 = $yes_pay_details['credit'];
                $yCridit = $yCridit+$t_yCredit1;
                
                $t_cMCredit1 = $t_this_mnth_pay_details['credit'];
                $cMCridit = $cMCridit + $t_cMCredit1;
                
                $t_privousMCredit1 = $prv_mnth_pay_details['credit'];
                $privousMCridt = $privousMCridt + $t_privousMCredit1;
            
                $todayCridit = $todayCridit + $t_todayCredit1 - $today_credit_sp;
                $yCreidt = $yCreidt + $t_yCredit1 - $yes_credit_sp;
                $cMCriedt = $cMCriedt + $t_cMCredit1 - $cm_credit_sp;
                $privousMnthCredit = $privousMnthCredit + $t_privousMCredit1 - $prv_mnth_credit_sp;
        }
        
        $tooltipTodayNetCreditDetail ="({$totdayCridit} - {$tcreidt}) = {$todayCridit}";
        $tooltipYNetCreditDetail ="({$yCridit} - {$ycreidt}) = {$yCreidt}";
        $tooltipCMNetCreditDetail ="({$cMCridit} - {$cMcreidt}) = {$cMCriedt}";
        $tooltipPMNetCreditDetail ="({$privousMCridt} - {$pMcreidt}) = {$privousMnthCredit}";
        
        //$todayCridit = $todayCridit + $credit_to_other_changed['credit'];
    ?>
   
    <?php
		$todayCridit1 = number_format($todayCridit,2);
		$yCreidt1 = number_format($yCreidt,2);
		$cMCriedt1 = number_format($cMCriedt,2);
		$privousMnthCredit1 = number_format($privousMnthCredit,2);
	?>
    <tr style="color: black">
        <td ><b>Net Credit</b> </td>
        <td><?php echo $CURRENCY_TYPE.$todayCridit1; ?> <a id='todayNetCredit' title='<?=$tooltipTodayNetCreditDetail?>' alt='<?=$tooltipTodayNetCashDetail?>'>(Detail)</a></td>
        <td><?php echo $CURRENCY_TYPE.$yCreidt1; ?> <a id='yesterdayNetCredit' title='<?=$tooltipYNetCreditDetail?>' alt='<?=$tooltipTodayNetCashDetail?>'>(Detail)</a></td>
        <td><?php echo $CURRENCY_TYPE.$cMCriedt1; ?> <a id='cmNetCredit' title='<?=$tooltipCMNetCreditDetail?>' alt='<?=$tooltipTodayNetCashDetail?>'>(Detail)</a></td>
        <td><?php echo $CURRENCY_TYPE.$privousMnthCredit1;?> <a id='pmNetCredit' title='<?=$tooltipPMNetCreditDetail?>' alt='<?=$tooltipTodayNetCashDetail?>'>(Detail)</a></td>
    </tr>
    
    <?php
        $tbnk = $todaysCNSale['bank_transfer'];
        $ybnk = $yesterdayCNSale['bank_transfer'];
        $cMbnk = $currMonthCNSale['bank_transfer'];
        $pMbnk = $prevMonthCNSale['bank_transfer'];
        
        if(preg_match('/'.QUOT_USER_PREFIX.'/',$loggedInUser)){
			$today_bank_transfer_sp = $t_todaysCNSale['bank_transfer'];
			$yes_bank_transfer_sp = $t_yesterdayCNSale['bank_transfer'];
			$cm_bank_transfer_sp = $t_currMonthCNSale['bank_transfer'];
			$prv_mnth_bank_transfer_sp = $t_prevMonthCNSale['bank_transfer'];
			
            $today_bank_Transfer = $today_bank_Transfer + $t_today_bank_Transfer-$today_bank_transfer_sp;
            $y_bank_Transfer = $y_bank_Transfer + $t_y_bank_Transfer-$yes_bank_transfer_sp;
            $cMBank = $cMBank + $t_cMBank-$cm_bank_transfer_sp;
            $privousMBank = $privousMBank + $t_privousMBank-$prv_mnth_bank_transfer_sp;
        }
        $today_bank_Transfer = $today_bank_Transfer; //+ $prv_bnk;
        $todaybnktrs =  $today_bank_Transfer - $todaysCNSale['bank_transfer'];
        $ybnktrs =  $y_bank_Transfer - $yesterdayCNSale['bank_transfer'];
        $cMbnktrs =  $cMBank - $currMonthCNSale['bank_transfer'];
        $privousMnthbnktrs =  $privousMBank - $prevMonthCNSale['bank_transfer'];
    
        $tooltipTodayNetBnkDetail ="({$today_bank_Transfer} - {$tbnk}) = {$todaybnktrs}";
        $tooltipYNetBnkDetail ="({$y_bank_Transfer} - {$ybnk}) = {$ybnktrs}";
        $tooltipCMNetBnkDetail ="({$cMBank} - {$cMbnk}) = {$cMbnktrs}";
        $tooltipPMNetBnkDetail ="({$privousMBank} - {$pMbnk}) = {$privousMnthbnktrs}";
    
           // $todaybnktrs = $todaybnktrs + $credit_to_other_changed['bank_transfer'];
    ?>
    
    <?php
	$todaybnktrs1 = number_format($todaybnktrs,2);
	$ybnktrs1 = number_format($ybnktrs,2);
	$cMbnktrs1 = number_format($cMbnktrs,2);
	$privousMnthbnktrs1 = number_format($privousMnthbnktrs,2);
	?>
     <tr style="color: black">
        <td><b>Net Bnk Tnsfer </b></td>
        <td><?php echo $CURRENCY_TYPE.$todaybnktrs1; ?> <a id='todayNetbnk' title='<?=$tooltipTodayNetBnkDetail?>' alt='<?=$tooltipTodayNetCashDetail?>'>(Detail)</a></td>
        <td><?php echo $CURRENCY_TYPE.$ybnktrs1; ?> <a id='yesterdayNetbnk' title='<?=$tooltipYNetBnkDetail?>' alt='<?=$tooltipTodayNetCashDetail?>'>(Detail)</a></td>
        <td><?php echo $CURRENCY_TYPE.$cMbnktrs1; ?> <a id='cmNetbnk' title='<?=$tooltipCMNetBnkDetail?>' alt='<?=$tooltipTodayNetCashDetail?>'>(Detail)</a></td>
        <td><?php echo $CURRENCY_TYPE.$privousMnthbnktrs1;?> <a id='pmNetbnk' title='<?=$tooltipPMNetBnkDetail?>' alt='<?=$tooltipTodayNetCashDetail?>'>(Detail)</a></td>
    </tr>
    <?php
        $tcheque = $todaysCNSale['cheque'];
        $ycheque = $yesterdayCNSale['cheque'];
        $cMcheque = $currMonthCNSale['cheque'];
        $pMcheque = $prevMonthCNSale['cheque'];
        if(preg_match('/'.QUOT_USER_PREFIX.'/',$loggedInUser)){
			$today_cheque_sp = $t_todaysCNSale['cheque'];
			$yes_cheque_sp = $t_yesterdayCNSale['cheque'];
			$cm_cheque_sp = $t_currMonthCNSale['cheque'];
			$prv_mnth_cheque_sp = $t_prevMonthCNSale['cheque'];
			
            $todayCheque = $todayCheque + $t_todayCheque - $today_cheque_sp;
            $yCheque = $yCheque + $t_yCheque - $yes_cheque_sp;
            $cMCheque = $cMCheque + $t_cMCheque - $cm_cheque_sp;
            $privousMCheque = $privousMCheque + $t_privousMCheque - $prv_mnth_cheque_sp;
        }
        $todayCheque = $todayCheque; //+ $prv_cheque;
        $todayChequePmt =  $todayCheque - $todaysCNSale['cheque'];
        $yChequePmt =  $yCheque - $yesterdayCNSale['cheque'];
        $cMChequePmt =  $cMCheque - $currMonthCNSale['cheque'];
        $privousMnthChequePmt =  $privousMCheque - $prevMonthCNSale['cheque'];
    
        $tooltipTodayNetChequeDetail ="({$todayCheque} - {$tcheque}) = {$todayChequePmt}";
        $tooltipYNetChequeDetail ="({$yCheque} - {$ycheque}) = {$yChequePmt}";
        $tooltipCMNetChequeDetail ="({$cMCheque} - {$cMcheque}) = {$cMChequePmt}";
        $tooltipPMNetChequeDetail ="({$privousMCheque} - {$pMcheque}) = {$privousMnthChequePmt}";
        //$todayChequePmt = $todayChequePmt + $credit_to_other_changed['cheque'];
    ?>
     
	 <?php
	 $todayChequePmt1 = number_format($todayChequePmt,2);
	 $yChequePmt1 = number_format($yChequePmt,2);
	 $cMChequePmt1 = number_format($cMChequePmt,2);
	 $privousMnthChequePmt1 = number_format($privousMnthChequePmt,2);
	 ?>
      <tr style="color: black">
        <td style="color: black"><b>Net Cheque Payment </b></td>
        <td><?php echo $CURRENCY_TYPE.$todayChequePmt1; ?> <a id='todayNetCheque' title='<?=$tooltipTodayNetChequeDetail?>' alt='<?=$tooltipTodayNetCashDetail?>'>(Detail)</a></td>
        <td><?php echo $CURRENCY_TYPE.$yChequePmt1; ?> <a id='yesterdayNetCheque' title='<?=$tooltipYNetChequeDetail?>' alt='<?=$tooltipTodayNetCashDetail?>'>(Detail)</a></td>
        <td><?php echo $CURRENCY_TYPE.$cMChequePmt1; ?> <a id='cmNetCheque' title='<?=$tooltipCMNetChequeDetail?>' alt='<?=$tooltipTodayNetCashDetail?>'>(Detail)</a></td>
        <td><?php echo $CURRENCY_TYPE.$privousMnthChequePmt1;?> <a id='pmNetCheque' title='<?=$tooltipPMNetChequeDetail?>' alt='<?=$tooltipTodayNetCashDetail?>'>(Detail)</a></td>
    </tr>
     
    
    <script type="text/javascript">
        //jQuery('#todayNetCash').tooltip({content:"<?=($tooltipTodayNetCashDetail);?>",track:true});
        jQuery(document).tooltip();
        jQuery("#todayNetCash").tooltip({content:function(){return "<?php echo $tooltipTodayNetCashDetail;?>";}});
        jQuery("#yesterdayNetCash").tooltip({content:function(){return "<?php echo $tooltipYesterdayNetCashDetail;?>";}});
        jQuery("#cmNetCash").tooltip({content:function(){return "<?php echo $tooltipCMNetCashDetail;?>";}});
        jQuery("#pmNetCash").tooltip({content:function(){return "<?php echo $tooltipPMNetCashDetail;?>";}});
        
        //----------
        jQuery("#todayNetCard").tooltip({content:function(){return "<?php echo $tooltipTodayNetCardDetail;?>";}});
        jQuery("#yesterdayNetCard").tooltip({content:function(){return "<?php echo $tooltipYNetCardDetail;?>";}});
        jQuery("#cmNetCard").tooltip({content:function(){return "<?php echo $tooltipMNetCardDetail;?>";}});
        jQuery("#pmNetCard").tooltip({content:function(){return "<?php echo $tooltipPMNetCardDetail;?>";}});
    </script>
    <?php //pr($todayProductPmtDetails);?>
    
    <?php
    //    if($todayNetSale < 0){
    //         $todayCashInHand = $todayNetSale+($todayMobilePurchase+$todayTotalCard+$todaysCNSale['cash']);
    //    }else{
    //         $todayCashInHand = $todayNetSale-($todayMobilePurchase+$todayTotalCard+$todaysCNSale['cash']);
    //    }
    //    
    //   // echo "<br/>$todayCashInHand = $todayNetSale-$todayMobilePurchase-$todayTotalCard-".$todaysCNSale['cash'];
    //    $yesterdayCashInHand = $yesterdayNetSale-$yesterdayMobilePurchase-$yesterdayTotalCard-$yesterdayCNSale['cash'];
    //    $currentMonthCashInHand = $currentMonthNetSale-$currentMonthMobilePurchase-$currentMonthTotalCard-$currMonthCNSale['cash'];
    //    $previousMonthCashInHand = $previousMonthNetSale-$previousMonthMobilePurchase-$previousMonthTotalCard-$prevMonthCNSale['cash'];
    ?>
   
   <tr>
    <?php //$end_today_cash = $end_today_cash + $credit_to_other_changed['cash'];?>
	<?php
		$end_today_cash1 = number_format($end_today_cash,2);
		$end_y_cash1 = number_format($end_y_cash,2);
		$this_mnth_sale1 = number_format($this_mnth_sale,2);
		$privious_mnth_sale1 = number_format($privious_mnth_sale,2);
	?>
        <td><b>Cash In Hand</b></td>
        <td><?php echo $CURRENCY_TYPE.$end_today_cash1; ?> <a id='todayNetCash' title='<?=$tooltipTodayNetCashDetail?>' alt='<?=$tooltipTodayNetCashDetail?>'>(Detail)</a></td>
        <td><?php echo $CURRENCY_TYPE.$end_y_cash1; ?> <a id='yesterdayNetCash' title='<?=$tooltipYesterdayNetCashDetail?>' alt='<?=$tooltipTodayNetCashDetail?>'>(Detail)</a></td>
        <td><?php echo $CURRENCY_TYPE.$this_mnth_sale1; ?> <a id='cmNetCash' title='<?=$tooltipCMNetCashDetail?>' alt='<?=$tooltipTodayNetCashDetail?>'>(Detail)</a></td>
        <td><?php echo $CURRENCY_TYPE.$privious_mnth_sale1;?> <a id='pmNetCash' title='<?=$tooltipPMNetCashDetail?>' alt='<?=$tooltipTodayNetCashDetail?>'>(Detail)</a></td>
    </tr>
   
   <tr>
    <td><b>credit to cash(Prvs Payments)</b></td>
    <?php
	//pr($credit_to_other_changed);
	$prev_rect_sale = $credit_to_other_changed['cash'];//+$credit_to_other_changed['card']+$credit_to_other_changed['credit']+$credit_to_other_changed['bank_transfer']+$credit_to_other_changed['cheque']-$todaysCNSale['cashEntryAmt'];
    $t_prv_recipt_sale = $t_credit_to_other_changed['cash'];//+$t_credit_to_other_changed['card']+$t_credit_to_other_changed['credit']+$t_credit_to_other_changed['bank_transfer']+$t_credit_to_other_changed['cheque']-$t_todaysCNSale['cashEntryAmt'];
     if(preg_match('/'.QUOT_USER_PREFIX.'/',$loggedInUser)){
		 $total_prv_sale = $prev_rect_sale + $t_prv_recipt_sale;
	 }else{
		$total_prv_sale = $prev_rect_sale;
	 }
    $credit_note_entry = $todaysCNSale['cashEntryAmt'];
	 if(preg_match('/'.QUOT_USER_PREFIX.'/',$loggedInUser)){
		$credit_quotataion_entry = $t_todaysCNSale['cashEntryAmt'];
		$total_prv_sale = $total_prv_sale - ($credit_note_entry+$credit_quotataion_entry);
		$tooltipPrvRcitDetail = "(invoice cash({$prv_cash}) + Quotation cash({$t_prv_cash}) - credit cash({$credit_note_entry}) -  credit quotation cash({$credit_quotataion_entry})) = {$total_prv_sale}";
	 }else{
		$tooltipPrvRcitDetail = "(invoice cash - credit cash)";
		$tooltipPrvRcitDetail = "(invoice cash({$prv_cash}) - credit cash({$credit_note_entry})) = {$total_prv_sale}";
	 }
    
	$y_prev_rect_sale = $y_credit_to_other_changed['cash'];//+$y_credit_to_other_changed['card']+$y_credit_to_other_changed['credit']+$y_credit_to_other_changed['bank_transfer']+$y_credit_to_other_changed['cheque'];
	$y_prv_cash = $y_credit_to_other_changed['cash'];
	$y_prv_bnk = $y_credit_to_other_changed['card'];
	$y_prv_credit = $y_credit_to_other_changed['credit'];
	$y_prv_card = $y_credit_to_other_changed['bank_transfer'];
	$y_prv_cheque = $y_credit_to_other_changed['cheque'];
	
	 $credit_note_entry = $yesterdayCNSale['cashEntryAmt'];
	if(preg_match('/'.QUOT_USER_PREFIX.'/',$loggedInUser)){
		$credit_quotataion_entry = $t_yesterdayCNSale['cashEntryAmt'];
		$t_y_cash = $t_y_prv_credit_to_card['cash'];
		$y_prev_rect_sale = $y_prev_rect_sale+$t_y_cash - ($credit_note_entry+$credit_quotataion_entry);
		 $tooltipYPrvRcitDetail = "(invoice cash({$y_prv_cash}) + Quotation cash({$t_y_cash}) - credit cash({$credit_note_entry}) -  credit quotation cash({$credit_quotataion_entry})) = {$y_prev_rect_sale}";   	
	}else{
		$y_prev_rect_sale = $y_prev_rect_sale - ($credit_note_entry);
            $tooltipYPrvRcitDetail = "(invoice cash - credit cash)";
            $tooltipYPrvRcitDetail = "(invoice cash({$y_prv_cash}) - credit cash({$credit_note_entry}))= {$y_prev_rect_sale}";    
	}
	
	
	$this_mnth_prev_rect_sale = $current_month_credit_to_other_changed['cash'];//+$current_month_credit_to_other_changed['card']+$current_month_credit_to_other_changed['credit']+$current_month_credit_to_other_changed['bank_transfer']+$current_month_credit_to_other_changed['cheque'];
	$this_mnth_prv_cash = $current_month_credit_to_other_changed['cash'];
	$this_mnth_prv_bnk = $current_month_credit_to_other_changed['card'];
	$this_mnth_prv_credit = $current_month_credit_to_other_changed['credit'];
	$this_mnth_prv_card = $current_month_credit_to_other_changed['bank_transfer'];
	$this_mnth_prv_cheque = $current_month_credit_to_other_changed['cheque'];
	
	$credit_note_entry = $currMonthCNSale['cashEntryAmt'];
    
	if(preg_match('/'.QUOT_USER_PREFIX.'/',$loggedInUser)){
		$credit_quotataion_entry = $t_currMonthCNSale['cashEntryAmt'];
     $t_cm_cash = $t_current_month_credit_to_other_changed['cash'];
		$this_mnth_prev_rect_sale = $this_mnth_prev_rect_sale+$t_cm_cash - ($credit_note_entry+$credit_quotataion_entry);
		$tooltipCMPrvRcitDetail = "(invoice cash({$this_mnth_prv_cash}) + Quotation cash({$t_cm_cash}) - credit cash({$credit_note_entry}) -  credit quotation cash({$credit_quotataion_entry})) = {$this_mnth_prev_rect_sale}";
	}else{
			$this_mnth_prev_rect_sale = $this_mnth_prev_rect_sale - ($credit_note_entry);
         $tooltipCMPrvRcitDetail = "(invoice cash({$this_mnth_prv_cash}) - credit cash({$credit_note_entry})) = {$this_mnth_prev_rect_sale}";
	}
	
	
	$prv_mnth_prev_rect_sale = $prv_month_credit_to_other_changed['cash'];//+$prv_month_credit_to_other_changed['card']+$prv_month_credit_to_other_changed['credit']+$prv_month_credit_to_other_changed['bank_transfer']+$prv_month_credit_to_other_changed['cheque'];
	$prv_mnth_prv_cash = $prv_month_credit_to_other_changed['cash'];
	$prv_mnth_prv_bnk = $prv_month_credit_to_other_changed['card'];
	$prv_mnth_prv_credit = $prv_month_credit_to_other_changed['credit'];
	$prv_mnth_prv_card = $prv_month_credit_to_other_changed['bank_transfer'];
	$prv_mnth_prv_cheque = $prv_month_credit_to_other_changed['cheque'];
	
	$credit_note_entry = $prevMonthCNSale['cashEntryAmt'];
    
	if(preg_match('/'.QUOT_USER_PREFIX.'/',$loggedInUser)){
		$credit_quotataion_entry = $t_prevMonthCNSale['cashEntryAmt'];
		$t_prv_mnth_cash = $t_prv_month_credit_to_other_changed['cash'];
		
		$prv_mnth_prev_rect_sale = $prv_mnth_prev_rect_sale + $t_prv_mnth_cash - ($credit_note_entry+$credit_quotataion_entry);
		 $tooltipPrvMPrvRcitDetail = "(invoice cash({$prv_mnth_prv_cash}) + Quotation cash({$t_prv_mnth_cash}) - credit cash({$credit_note_entry}) -  credit quotation cash({$credit_quotataion_entry})) = {$prv_mnth_prev_rect_sale}";
	}else{
		$prv_mnth_prev_rect_sale = $prv_mnth_prev_rect_sale - ($credit_note_entry);
         $tooltipPrvMPrvRcitDetail = "(invoice cash({$prv_mnth_prv_cash}) - credit cash({$credit_note_entry}))";
	}
    ?>
	<?php
		$total_prv_sale1 = number_format($total_prv_sale,2);
		$y_prev_rect_sale1 = number_format($y_prev_rect_sale,2);
		$this_mnth_prev_rect_sale1 = number_format($this_mnth_prev_rect_sale,2);
		$prv_mnth_prev_rect_sale1 = number_format($prv_mnth_prev_rect_sale,2);
	?>
     <td><?php echo $CURRENCY_TYPE.$total_prv_sale1; ?> <a id='prev_rect_sale' title='<?=$tooltipPrvRcitDetail?>' alt='<?=$tooltipPrvRcitDetail?>'>(Detail)</a></td>
	 <td><?php echo $CURRENCY_TYPE.$y_prev_rect_sale1; ?> <a id='prev_rect_sale' title='<?=$tooltipYPrvRcitDetail?>' alt='<?=$tooltipYPrvRcitDetail?>'>(Detail)</a></td>
	 <td><?php echo $CURRENCY_TYPE.$this_mnth_prev_rect_sale1; ?> <a id='prev_rect_sale' title='<?=$tooltipCMPrvRcitDetail?>' alt='<?=$tooltipCMPrvRcitDetail?>'>(Detail)</a></td>
	 <td><?php echo $CURRENCY_TYPE.$prv_mnth_prev_rect_sale1; ?> <a id='prev_rect_sale' title='<?=$tooltipPrvMPrvRcitDetail?>' alt='<?=$tooltipPrvMPrvRcitDetail?>'>(Detail)</a></td>
   </tr>
   
    <?php
    // today
    // bank
    $today_product_sale_changed_to_bank = $credit_to_other_changed['bank_transfer'];
    $today_credit_sale_changed_to_bank = $today_credit_to_other_changes_CN['bank_transfer'];
    if(preg_match('/'.QUOT_USER_PREFIX.'/',$loggedInUser)){
        $today_product_quotation_sale_changed_to_bank = $t_credit_to_other_changed['bank_transfer'];
        $today_credit_quotation_changed_to_bank = $t_today_credit_to_other_changes_CN['bank_transfer'];
        $bnk_trnsfer_total = $today_product_sale_changed_to_bank + $today_product_quotation_sale_changed_to_bank - ($today_credit_sale_changed_to_bank + $today_credit_quotation_changed_to_bank);
    }else{
        $bnk_trnsfer_total = $today_product_sale_changed_to_bank  - ($today_credit_sale_changed_to_bank);
    }
    
    
    // card
    
    $today_product_sale_changed_to_card = $credit_to_other_changed['card'];
    $today_credit_sale_changed_to_card = $today_credit_to_other_changes_CN['card'];
    if(preg_match('/'.QUOT_USER_PREFIX.'/',$loggedInUser)){
        $today_product_quotation_sale_changed_to_card = $t_credit_to_other_changed['card'];
        $today_credit_quotation_changed_to_card = $t_today_credit_to_other_changes_CN['card'];    
        $card_trnsfer_total = $today_product_sale_changed_to_card + $today_product_quotation_sale_changed_to_card - ($today_credit_sale_changed_to_card + $today_credit_quotation_changed_to_card);         
    }else{
        $card_trnsfer_total = $today_product_sale_changed_to_card - ($today_credit_sale_changed_to_card);    
    }
    
   
    // cheque
    
    $today_product_sale_changed_to_cheque = $credit_to_other_changed['cheque'];
    $today_credit_sale_changed_to_cheque = $today_credit_to_other_changes_CN['cheque'];
     if(preg_match('/'.QUOT_USER_PREFIX.'/',$loggedInUser)){
            $today_product_quotation_sale_changed_to_cheque = $t_credit_to_other_changed['cheque'];
            $today_credit_quotation_changed_to_cheque = $t_today_credit_to_other_changes_CN['cheque'];
            $cheque_trnsfer_total = $today_product_sale_changed_to_cheque + $today_product_quotation_sale_changed_to_cheque - ($today_credit_sale_changed_to_cheque + $today_credit_quotation_changed_to_cheque);
     }else{
            $cheque_trnsfer_total = $today_product_sale_changed_to_cheque  - ($today_credit_sale_changed_to_cheque);     
     }
    
   

  //total    
    $credit_to_other_pay = $bnk_trnsfer_total + $card_trnsfer_total + $cheque_trnsfer_total;
    $tooltipPrvRcitotherDetail = "(total bnk trsfer = {$bnk_trnsfer_total} + total card transfer = {$card_trnsfer_total} + total cheque transfer = {$cheque_trnsfer_total} , total{$credit_to_other_pay})";
    
    // yesterday
    
    
     // bank
    $y_product_sale_changed_to_bank = $y_credit_to_other_changed['bank_transfer'];
    $y_credit_sale_changed_to_bank = $y_credit_to_other_changes_CN['bank_transfer'];
    if(preg_match('/'.QUOT_USER_PREFIX.'/',$loggedInUser)){
        $y_product_quotation_sale_changed_to_bank = $t_y_prv_credit_to_card['bank_transfer'];
        $y_credit_quotation_changed_to_bank = $t_y_credit_to_other_changes_CN['bank_transfer'];
        $y_bnk_trnsfer_total = $y_product_sale_changed_to_bank + $y_product_quotation_sale_changed_to_bank - ($y_credit_sale_changed_to_bank + $y_credit_quotation_changed_to_bank);    
    }else{
        $y_bnk_trnsfer_total = $y_product_sale_changed_to_bank  - ($y_credit_sale_changed_to_bank);
    }
    
    
    
    // card
    $y_product_sale_changed_to_card = $y_credit_to_other_changed['card'];
    $y_credit_sale_changed_to_card = $y_credit_to_other_changes_CN['card'];
    if(preg_match('/'.QUOT_USER_PREFIX.'/',$loggedInUser)){
        $y_product_quotation_sale_changed_to_card = $t_y_prv_credit_to_card['card'];
        $y_credit_quotation_changed_to_card = $t_y_credit_to_other_changes_CN['card'];
        $y_card_trnsfer_total = $y_product_sale_changed_to_card + $y_product_quotation_sale_changed_to_card - ($y_credit_sale_changed_to_card + $y_credit_quotation_changed_to_card);       
    }else{
        $y_card_trnsfer_total = $y_product_sale_changed_to_card  - ($y_credit_sale_changed_to_card);  
    }
    
   
    // cheque
    
    $y_product_sale_changed_to_cheque = $y_credit_to_other_changed['cheque'];
    
    $y_credit_sale_changed_to_cheque = $y_credit_to_other_changes_CN['cheque'];
    
    if(preg_match('/'.QUOT_USER_PREFIX.'/',$loggedInUser)){
        $y_product_quotation_sale_changed_to_cheque = $t_y_prv_credit_to_card['cheque'];
        $y_credit_quotation_changed_to_cheque = $t_y_credit_to_other_changes_CN['cheque'];    
        $y_cheque_trnsfer_total = $y_product_sale_changed_to_cheque + $y_product_quotation_sale_changed_to_cheque - ($y_credit_sale_changed_to_cheque + $y_credit_quotation_changed_to_cheque);     
    }else{
        $y_cheque_trnsfer_total = $y_product_sale_changed_to_cheque - ($y_credit_sale_changed_to_cheque);
    }
    
   
   
   //total
    $y_credit_to_other_pay = $y_bnk_trnsfer_total + $y_card_trnsfer_total + $y_cheque_trnsfer_total;
    $tooltipYPrvOtherRcitDetail = "(total bnk trsfer = {$y_bnk_trnsfer_total} + total card transfer = {$y_card_trnsfer_total} + total cheque transfer = {$y_cheque_trnsfer_total} , total{$y_credit_to_other_pay})";
    
    // current month
    
    // bank
    $cm_product_sale_changed_to_bank = $current_month_credit_to_other_changed['bank_transfer'];
    $cm_credit_sale_changed_to_bank = $cm_credit_to_other_changes_CN['bank_transfer'];
    if(preg_match('/'.QUOT_USER_PREFIX.'/',$loggedInUser)){
        $cm_product_quotation_sale_changed_to_bank = $t_current_month_credit_to_other_changed['bank_transfer'];
        $cm_credit_quotation_changed_to_bank = $t_cm_credit_to_other_changes_CN['bank_transfer'];
        $cm_bnk_trnsfer_total = $cm_product_sale_changed_to_bank + $cm_product_quotation_sale_changed_to_bank - ($cm_credit_sale_changed_to_bank + $cm_credit_quotation_changed_to_bank);
    }else{
        $cm_bnk_trnsfer_total = $cm_product_sale_changed_to_bank - ($cm_credit_sale_changed_to_bank);    
    }
    
    
    
     // card
    $cm_product_sale_changed_to_card = $current_month_credit_to_other_changed['card'];
    $cm_credit_sale_changed_to_card = $cm_credit_to_other_changes_CN['card'];
    if(preg_match('/'.QUOT_USER_PREFIX.'/',$loggedInUser)){
         $cm_product_quotation_sale_changed_to_card = $t_current_month_credit_to_other_changed['card'];
         $cm_credit_quotation_changed_to_card = $t_cm_credit_to_other_changes_CN['card'];     
        $cm_card_trnsfer_total = $cm_product_sale_changed_to_card + $cm_product_quotation_sale_changed_to_card - ($cm_credit_sale_changed_to_card + $cm_credit_quotation_changed_to_card);      
     }else{
        $cm_card_trnsfer_total = $cm_product_sale_changed_to_card- ($cm_credit_sale_changed_to_card);
     }
    
   
   
   // cheque
    $cm_product_sale_changed_to_cheque = $current_month_credit_to_other_changed['cheque'];
    $cm_credit_sale_changed_to_cheque = $cm_credit_to_other_changes_CN['cheque'];
    if(preg_match('/'.QUOT_USER_PREFIX.'/',$loggedInUser)){
        $cm_product_quotation_sale_changed_to_cheque = $t_current_month_credit_to_other_changed['cheque'];
        $cm_credit_quotation_changed_to_cheque = $t_cm_credit_to_other_changes_CN['cheque'];
        $cm_cheque_trnsfer_total = $cm_product_sale_changed_to_cheque + $cm_product_quotation_sale_changed_to_cheque - ($cm_credit_sale_changed_to_cheque + $cm_credit_quotation_changed_to_cheque);
    }else{
        $cm_cheque_trnsfer_total = $cm_product_sale_changed_to_cheque - ($cm_credit_sale_changed_to_cheque);
    }
    
   
   
    
     //total    
    $cm_credit_to_other_pay = $cm_bnk_trnsfer_total + $cm_card_trnsfer_total + $cm_cheque_trnsfer_total;
    $tooltipCMOtherPrvRcitDetail = "(total bnk trsfer = {$cm_bnk_trnsfer_total} + total card transfer = {$cm_card_trnsfer_total} + total cheque transfer = {$cm_cheque_trnsfer_total} , total{$cm_credit_to_other_pay})";
    
    // privious month
    // bank
    $pm_product_sale_changed_to_bank = $prv_month_credit_to_other_changed['bank_transfer'];
    $pm_credit_sale_changed_to_bank = $pm_credit_to_other_changes_CN['bank_transfer'];
    if(preg_match('/'.QUOT_USER_PREFIX.'/',$loggedInUser)){
        $pm_product_quotation_sale_changed_to_bank = $t_prv_month_credit_to_other_changed['bank_transfer'];
        $pm_credit_quotation_changed_to_bank = $t_pm_credit_to_other_changes_CN['bank_transfer'];
        $pm_bnk_trnsfer_total = $pm_product_sale_changed_to_bank + $pm_product_quotation_sale_changed_to_bank - ($pm_credit_sale_changed_to_bank + $pm_credit_quotation_changed_to_bank);
    }else{
        $pm_bnk_trnsfer_total = $pm_product_sale_changed_to_bank - ($pm_credit_sale_changed_to_bank);    
    }
    
    
    
     // card
    $pm_product_sale_changed_to_card = $prv_month_credit_to_other_changed['card'];
    
    $pm_credit_sale_changed_to_card = $pm_credit_to_other_changes_CN['card'];
    
    if(preg_match('/'.QUOT_USER_PREFIX.'/',$loggedInUser)){
        $pm_product_quotation_sale_changed_to_card = $t_prv_month_credit_to_other_changed['card'];
        $pm_credit_quotation_changed_to_card = $t_pm_credit_to_other_changes_CN['card'];
        $pm_card_trnsfer_total = $pm_product_sale_changed_to_card + $pm_product_quotation_sale_changed_to_card - ($pm_credit_sale_changed_to_card + $pm_credit_quotation_changed_to_card);
    }else{
        $pm_card_trnsfer_total = $pm_product_sale_changed_to_card - ($pm_credit_sale_changed_to_card);     
    }
    
   
   
   
   // cheque
    
    $pm_product_sale_changed_to_cheque = $prv_month_credit_to_other_changed['cheque'];
    
    $pm_credit_sale_changed_to_cheque = $pm_credit_to_other_changes_CN['cheque'];
    
    if(preg_match('/'.QUOT_USER_PREFIX.'/',$loggedInUser)){
        $pm_product_quotation_sale_changed_to_cheque = $t_prv_month_credit_to_other_changed['cheque'];
        $pm_credit_quotation_changed_to_cheque = $t_pm_credit_to_other_changes_CN['cheque'];
        $pm_cheque_trnsfer_total = $pm_product_sale_changed_to_cheque + $pm_product_quotation_sale_changed_to_cheque - ($pm_credit_sale_changed_to_cheque + $pm_credit_quotation_changed_to_cheque);
    }else{
        $pm_cheque_trnsfer_total = $pm_product_sale_changed_to_cheque - ($pm_credit_sale_changed_to_cheque);     
    }
    
   
    
    //total    
    $pm_credit_to_other_pay = $pm_bnk_trnsfer_total + $pm_card_trnsfer_total + $pm_cheque_trnsfer_total;
    $tooltipPrvMOtherPrvRcitDetail = "(total bnk trsfer = {$pm_bnk_trnsfer_total} + total card transfer = {$pm_card_trnsfer_total} + total cheque transfer = {$pm_cheque_trnsfer_total} , total{$pm_credit_to_other_pay})";
    
    ?>
	<?php
	$credit_to_other_pay1 = number_format($credit_to_other_pay,2);
	$y_credit_to_other_pay1 = number_format($y_credit_to_other_pay,2);
	$cm_credit_to_other_pay1 = number_format($cm_credit_to_other_pay,2);
	$pm_credit_to_other_pay1 = number_format($pm_credit_to_other_pay,2);
	?>
     <tr>
    <td><b>credit to other payment(Prvs Payments)</b></td>
    <?php //echo "Cash : ".$credit_to_other_changed['cash'];?>
    <td><?php echo "".$credit_to_other_pay1;?><a id='prev_rect_sale' title='<?=$tooltipPrvRcitotherDetail?>' alt='<?=$tooltipPrvRcitotherDetail?>'>(Detail)</a></td>
	<td><?php echo "".$y_credit_to_other_pay1;?><a id='prev_rect_sale' title='<?=$tooltipYPrvOtherRcitDetail?>' alt='<?=$tooltipYPrvOtherRcitDetail?>'>(Detail)</a></td>
	<td><?php echo "".$cm_credit_to_other_pay1;?><a id='prev_rect_sale' title='<?=$tooltipCMOtherPrvRcitDetail?>' alt='<?=$tooltipCMOtherPrvRcitDetail?>'>(Detail)</a></td>
	<td><?php echo "".$pm_credit_to_other_pay1 ;?> <a id='prev_rect_sale' title='<?=$tooltipPrvMOtherPrvRcitDetail?>' alt='<?=$tooltipPrvMOtherPrvRcitDetail?>'>(Detail)</a></td>
   </tr>
   
   
   
    <?php $todayRefund = $todaysUrefund + $todaysRefund + $todayProductRefund + $todayMobileRefund;
    $yesRefund = $yesterdaysRefund+$yesterdaysUrefund+$yestdayProductRefund+$yesterdayMobileRefund;
    $total_mnth_refund = $currentMonthRepairRefund + $currentMonthUnlockRefund + $currentMonthProductRefund + $currentMonthMobileRefund + $currMonthCNCardRefund;
    $total_prv_mnth_refund = $previousMonthRepairRefund + $previousMonthUnlockRefund + $previousMonthProductRefund + $previousMonthMobileRefund+ $prevMonthCNCardRefund;
        $total = $total_today_cash - $todayRefund;
        $ytotal = $total_yes_cash - $yesRefund;
        $thismnthtotal = $total_mnth_cash - $total_mnth_refund;
        $prv_mnth_cash = $total_prv_mnth_cash + $total_prv_mnth_refund;
    ?>
    
    
</table>
** modified = (repairCash + unlockCash + mobileCash + productCash) - (repairRefund + unlockRefund + mobileRefund + productRefund).</br>
** cashInHand = (repairCash + unlockCash + mobileCash + productCash + blkCash) -(todayMobilePurchase+todayTotalCard+todaysCreditNodeSale)


<?php echo $this->Form->end();?>
<script>
    $('#KioskTotalSaleKiosk').change(function(){
        var kiskId = $('#KioskTotalSaleKiosk').val();
        //alert(kiskId);
        if (document.getElementById('KioskTotalSaleDashboardForm')) {
            var action = $('#KioskTotalSaleDashboardForm').attr('action');
            var formid = '#KioskTotalSaleDashboardForm';
        } else {
            var action = $('#KioskTotalSaleKioskTotalSaleForm').attr('action');
            var formid = '#KioskTotalSaleKioskTotalSaleForm';
        }
            var newAction = action + '/' + kiskId;
            $(formid).attr('action',newAction);
        this.form.submit();
    });
</script>
<script>
   $('#KioskTotalSaleKiosk').change(function(){
	$.blockUI({ message: 'Loading ...' });
	document.getElementById("KioskTotalSaleDashboardForm").submit();
	  }); 
</script>