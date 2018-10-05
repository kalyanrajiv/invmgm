<?php
    use Cake\Core\Configure;
    use Cake\Core\Configure\Engine\PhpConfig;
    $currency = Configure::read('CURRENCY_TYPE');
    $tooltipTodayRepairSale = "(Card: {$todaysRcardPayment},Cash:{$todaysRcashPayment})";
    $tooltipYesterdayRepairSale = "(Card: {$yesterdaysRcardPayment},Cash:{$yesterdaysRcashPayment})";
    $tooltipCMRepairSale = "(Card: {$currentMonthRcardPayment},Cash:{$currentMonthRcashPayment})";
    $tooltipPMRepairSale = "(Card: {$previousMonthRcashPayment},Cash:{$previousMonthRcardPayment})";
    $f_todaysSale = number_format($todaysSale, 2);
?>
<?php  echo $this->Html->link('Dashboard', array('controller' => 'products', 'action' => 'dashboard-data', 'full_base' => true));?>
<table width='100%'>
    <tr>
        <th width='20%'>&nbsp;</th>
        <th width='20%'>Today</th>
        <th width='20%'>Yesterday</th>
        <th width='20%'>Current Month</th>
        <th width='20%'>Previous Month</th>
    </tr>
    <tr>
        <td><strong>Repair Sale</strong></td>
        <td><?php echo $currency.$f_todaysSale;?><a id='todayRepairSale' alt='<?=$tooltipTodayRepairSale;?>', title='<?=$tooltipTodayRepairSale;?>'>(Detail)</a></td>
        <td><?php $f_yesterdaySale = $yesterdaySale;echo $currency.$f_yesterdaySale;?><a id='yesterdayRepairSale' alt='<?=$tooltipYesterdayRepairSale;?>', title='<?=$tooltipYesterdayRepairSale;?>'>(Detail)</a></td>
        <td><?php echo $currency.number_format($currentMonthRepairSale,2);?><a id='cmRepairSale' alt='<?=$tooltipCMRepairSale;?>', title='<?=$tooltipCMRepairSale;?>'>(Detail)</a></td>
        <td><?php echo $currency.$previousMonthRepairSale;?><a id='pmRepairSale' alt='<?=$tooltipPMRepairSale;?>', title='<?=$tooltipPMRepairSale;?>'>(Detail)</a></td>
    </tr>
<?php
    if($todaysRefund < 0){
        $todaysRefund = $todaysRefund * (-1); 
    }
    $f_todaysRefund = number_format($todaysRefund,2);
    if($yesterdaysRefund < 0){
        $yesterdaysRefund = $yesterdaysRefund * (-1); 
    }
    $f_yesterdaysRefund = number_format($yesterdaysRefund,2);
    $f_currentMonthRepairRefund = number_format($currentMonthRepairRefund,2);
    $f_previousMonthRepairRefund = number_format($previousMonthRepairRefund,2);
    $tooltipTodayUnlockSale = "(Card: {$todaysUcardPayment},Cash:{$todaysUcashPayment})";
    $tooltipYesterdayUnlockSale = "(Card: {$yesterdaysUcardPayment},Cash:{$yesterdaysUcashPayment})";
    $tooltipCMUnlockSale = "(Card: {$currentMonthUcardPayment},Cash:{$currentMonthUcashPayment})";
    $tooltipPMUnlockSale = "(Card: {$previousMonthUcardPayment},Cash:{$previousMonthUcashPayment})";
?>
    <tr style="color: blue">
        <td><strong>Repair Refund</strong></td>
        <td><?php echo $currency.$f_todaysRefund;?></td>
        <td><?php echo $currency.$f_yesterdaysRefund;?></td>
        <td><?php echo $currency.$f_currentMonthRepairRefund;?></td>
        <td><?php echo $currency.$f_previousMonthRepairRefund;?></td>
    </tr>
    <tr>
        <td><strong>Unlock Sale</strong></td>
        <td><?php  $f_todaysUsale = number_format($todaysUsale,2);echo $currency.$f_todaysUsale;?> <a id='todayUnlockSale' alt='<?=$tooltipTodayUnlockSale;?>', title='<?=$tooltipTodayUnlockSale;?>'>(Detail)</a></td>
        <td><?php $f_yesterdayUsale = $yesterdayUsale;echo $currency.$f_yesterdayUsale;?><a id='yesterdayUnlockSale' alt='<?=$tooltipYesterdayUnlockSale;?>', title='<?=$tooltipYesterdayUnlockSale;?>'>(Detail)</a></td>
        <td><?php echo $currency.number_format($currentMonthUnlockSale,2);?><a id='cmUnlockSale' alt='<?=$tooltipCMUnlockSale;?>', title='<?=$tooltipCMUnlockSale;?>'>(Detail)</a></td>
        <td><?php echo $currency.number_format($previousMonthUnlockSale,2);?><a id='pmUnlockSale' alt='<?=$tooltipPMUnlockSale;?>', title='<?=$tooltipPMUnlockSale;?>'>(Detail)</a></td>
    </tr>
<?php
    $f_todaysUrefund = number_format($todaysUrefund,2);
    $f_yesterdaysUrefund = number_format($yesterdaysUrefund,2);
    $f_currentMonthUnlockRefund = number_format($currentMonthUnlockRefund,2);
    $f_previousMonthUnlockRefund = number_format($previousMonthUnlockRefund,2);
?>
    <tr style="color: blue">
        <td><strong>Unlock Refund</strong></td>
        <td><?php echo $currency.$f_todaysUrefund;?></td>
        <td><?php echo $currency.$f_yesterdaysUrefund;?></td>
        <td><?php echo $currency.$f_currentMonthUnlockRefund;?></td>
        <td><?php echo $currency.$f_previousMonthUnlockRefund;?></td>
    </tr>
    
    <?php
        $cmProdCardPmt = $currentMonthProductPmtDetails['card'];
        $cmProdCashPmt = $currentMonthProductPmtDetails['cash'];
        $pmProdCardPmt = $previousMonthProductPmtDetails['card'];
        $pmProdCashPmt = $previousMonthProductPmtDetails['cash'];
        $cash = $todayProductPmtDetails['cash']+$todayCash;
        $f_cash = number_format($cash,2);
        
        $yTotalCash = $yesCash + $yesterdayProductPmtDetails['cash'];
        $f_yTotalCash = number_format($yTotalCash,2);
        
        $cMTotalCash = $thisMnthCash + $cmProdCashPmt;
        $f_cMTotalCash = number_format($cMTotalCash,2);
        
        $priviousMnthCASH = $prvMnthCash + $pmProdCashPmt;
        $f_priviousMnthCASH = number_format($priviousMnthCASH,2);
        
        $card = $todaysPcardPayment + $todayProductPmtDetails['card'];
        $f_card = number_format($card,2);
        
        $yCard = $yesterdaysPcardPayment + $yesterdayProductPmtDetails['card'];
        $f_yCard = number_format($yCard,2);
        
        $cMCard = $currentMonthPcardPayment + $cmProdCardPmt;
        $f_cMCard = number_format($cMCard,2);
        
        $priviousMntCard = $pmProdCardPmt + $previousMonthPcardPayment;
        $f_priviousMntCard = number_format($priviousMntCard,2);
        
        $totdayCridit = $todayProductPmtDetails['credit'];
        $f_totdayCridit = number_format($totdayCridit,2);
        
        $today_bank_Transfer = $todayProductPmtDetails['bank_transfer'];
        $f_today_bank_Transfer = number_format($today_bank_Transfer,2);
        
        $todayCheque = $todayProductPmtDetails['cheque'];
        $f_todayCheque = number_format($todayCheque,2);
        
        $yCridit = $yesterdayProductPmtDetails['credit'];
        $f_yCridit = number_format($yCridit,2);
        
        $y_bank_Transfer = $yesterdayProductPmtDetails['bank_transfer'];
        $f_y_bank_Transfer = number_format($y_bank_Transfer,2);
        
        $yCheque = $yesterdayProductPmtDetails['cheque'];
        $f_yCheque = number_format($yCheque,2);
        
        $cMCridit = $currentMonthProductPmtDetails['credit'];
        $f_cMCridit = number_format($cMCridit,2);
        $cMBank = $currentMonthProductPmtDetails['bank_transfer'];
        $cMBank = number_format($cMBank,2);
        $cMCheque = $currentMonthProductPmtDetails['cheque'];
        $f_cMCheque = number_format($cMCheque,2);
        $privousMCridt = $previousMonthProductPmtDetails['credit'];
        $f_privousMCridt = number_format($privousMCridt,2);
        
        $privousMBank = $previousMonthProductPmtDetails['bank_transfer'];
        $f_privousMBank = number_format($privousMBank,2);
        
        $privousMCheque = $previousMonthProductPmtDetails['cheque'];
        $f_privousMCheque = number_format($privousMCheque,2);
         
        $tooltipTodayProdSale = "(Card: {$f_card},Cash:{$f_cash},Credit:{$f_totdayCridit},Bank:{$f_today_bank_Transfer},cheque:{$f_todayCheque})";
        $tooltipYesterdayProdSale = "(Card: {$f_yCard},Cash:{$f_yTotalCash},Credit:{$f_yCridit},Bank:{$f_y_bank_Transfer},cheque:{$f_yCheque})";
        $tooltipCMProdSale = "(Card: {$f_cMCard},Cash:{$f_cMTotalCash},Credit:{$f_cMCridit},Bank:{$cMBank},cheque:{$f_cMCheque})";
        $tooltipPMProdSale = "(Card: {$f_priviousMntCard},Cash:{$f_priviousMnthCASH},Credit:{$f_privousMCridt},Bank:{$f_privousMBank},cheque:{$f_privousMCheque})";   
    ?>
    
     <tr>
        <td><strong>Product Sale</strong></td>
        <td><?php  //$todayProductSale = number_format($todayProductSale,2);
        echo $currency.$todayProductSale;?><a id='todayProdSale' alt='<?=$tooltipTodayProdSale;?>', title='<?=$tooltipTodayProdSale;?>'>(Detail)</a></td>
        <td><?php  $yesterdayProductSale = $yesterdayProductSale;echo $currency.$yesterdayProductSale;?><a id='yesterdayProdSale' alt='<?=$tooltipYesterdayProdSale;?>', title='<?=$tooltipYesterdayProdSale;?>'>(Detail)</a></td>
        <td><?php  echo $currency.number_format($currentMonthProductSale,2);?><a id='cmProdSale' alt='<?=$tooltipCMProdSale;?>', title='<?=$tooltipCMProdSale;?>'>(Detail)</a></td>
        <td><?php  echo $currency.number_format($previousMonthProductSale,2);?><a id='pmProdSale' alt='<?=$tooltipPMProdSale;?>', title='<?=$tooltipPMProdSale;?>'>(Detail)</a></td>
    </tr>
<?php
    $loggedInUser = $this->request->session()->read('Auth.User.username');
    if(preg_match('/'.QUOT_USER_PREFIX.'/',$loggedInUser)){
        $t_card = $t_cash = $t_today_bank_Transfer = $t_todayCheque = 0;
        $t_card = number_format($t_card,2);
        $t_cash = number_format($t_cash,2);
        if(!empty($t_today_pay_details)){
            $t_card = $t_today_pay_details['card'];
            $t_cash = $t_today_pay_details['cash'];
            $t_today_bank_Transfer = $t_today_pay_details['bank_transfer'];
            $t_todayCheque = $t_today_pay_details['cheque'];
            $t_todayCredit = $t_today_pay_details['credit'];
            $f_t_card = number_format($t_card,2);
            $f_t_cash = number_format($t_cash,2);
            $f_t_todayCredit = number_format($t_todayCredit,2);
            $f_t_today_bank_Transfer = number_format($t_today_bank_Transfer,2);
            $f_t_todayCheque = number_format($t_todayCheque,2);
        }
        $tooltip_t_TodayProdSale = "(Card: {$f_t_card},Cash:{$f_t_cash},Credit:{$f_t_todayCredit},Bank:{$f_t_today_bank_Transfer},cheque:{$f_t_todayCheque})";
        
        if(!empty($yes_pay_details)){
            $t_yCard = $yes_pay_details['card'];
            $t_yTotalCash = $yes_pay_details['cash'];
            $t_y_bank_Transfer = $yes_pay_details['bank_transfer'];
            $t_yCheque = $yes_pay_details['cheque'];
            $t_yCredit = $yes_pay_details['credit'];
            $f_t_yCard = number_format($t_yCard,2);
            $t_y_bank_Transfer = number_format($t_y_bank_Transfer,2);
            $t_yCheque = number_format($t_yCheque,2);
            $t_yCredit = number_format($t_yCredit,2);
        }
        
        $tooltip_t_YesterdayProdSale = "(Card: {$f_t_yCard},Cash:{$t_yTotalCash},Credit:{$t_yCredit},Bank:{$t_y_bank_Transfer},cheque:{$t_yCheque})";
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
    <tr>
        <td><strong>Quotation</strong></td>
        <td><?php echo $currency.$t_today_total_amount;?> <a id='t_todayProdSale' alt='<?=$tooltip_t_TodayProdSale;?>', title='<?=$tooltip_t_TodayProdSale;?>'>(Detail)</a></td>
        <td><?php echo $currency.$t_yes_total_amount;?> <a id='t_yesProdSale' alt='<?=$tooltip_t_YesterdayProdSale;?>', title='<?=$tooltip_t_YesterdayProdSale;?>'>(Detail)</a></td>
        <td><?php echo $currency.$t_this_mnth_total_amount;?> <a id='t_this_mnth_ProdSale' alt='<?=$tooltip_t_CMProdSale;?>', title='<?=$tooltip_t_CMProdSale;?>'>(Detail)</a></td>
        <td><?php echo $currency.$t_prv_mnth_total_amount;?> <a id='t_prv_mnth_ProdSale' alt='<?=$tooltip_t_PMProdSale;?>', title='<?=$tooltip_t_PMProdSale;?>'>(Detail)</a></td>
    </tr>
<?php
    }
?>
    
    <?php //-----------------------my code-------------------?>
	<?php
		 $today_credit_note_sale = $cn_card = $cn_cash = $cn_today_bank_Transfer = $cn_todayCheque = 0;
	//pr($currMonthCNSale);die;
	
        if(!empty($todaysCNSale)){
            $cn_card = $todaysCNSale['card'];$cn_card = number_format($cn_card,2);
            $cn_cash = $todaysCNSale['cash'];$cn_cash = number_format($cn_cash,2);
            $cn_today_bank_Transfer = $todaysCNSale['bank_transfer'];$cn_today_bank_Transfer = number_format($cn_today_bank_Transfer,2);
            $cn_todayCheque = $todaysCNSale['cheque'];$cn_todayCheque = number_format($cn_todayCheque,2);
            $todayCredit = $todaysCNSale['credit'];$todayCredit = number_format($todayCredit,2);
        }
        $tooltip_cn_TodayCnSale = "(Card: {$cn_card},Cash:{$cn_cash},Credit:{$todayCredit},Bank:{$cn_today_bank_Transfer},cheque:{$cn_todayCheque})";
        $y_credit_note_sale = $y_cn_Card = $y_cn_TotalCash = $y_cn_bank_Transfer = $y_cn_Cheque = 0;
        if(!empty($yesterdayCNSale)){
            $y_cn_Card = $yesterdayCNSale['card'];$y_cn_Card = number_format($y_cn_Card,2);
            $y_cn_TotalCash = $yesterdayCNSale['cash'];$y_cn_TotalCash = number_format($y_cn_TotalCash,2);
            $y_cn_bank_Transfer = $yesterdayCNSale['bank_transfer'];$y_cn_bank_Transfer = number_format($y_cn_bank_Transfer,2);
            $y_cn_Cheque = $yesterdayCNSale['cheque'];$y_cn_Cheque = number_format($y_cn_Cheque,2);
            $y_cn_yCredit = $yesterdayCNSale['credit'];$y_cn_yCredit = number_format($y_cn_yCredit,2);
        }
        $tooltip_cn_YesterdayCnSale = "(Card: {$y_cn_Card},Cash:{$y_cn_TotalCash},Credit:{$y_cn_yCredit},Bank:{$y_cn_bank_Transfer},cheque:{$y_cn_Cheque})";
        $special_this_month_credit_note_sale = $cn_cMCard = $cn_cMTotalCash = $cn_cMBank = $cn_cMCheque = 0;
		
        if(!empty($currMonthCNSale)){ 
            $cn_cMCard = $currMonthCNSale['card'];$cn_cMCard = number_format($cn_cMCard,2);
            $cn_cMTotalCash = $currMonthCNSale['cash'] +$currMonthCNSale['cashEntryAmt'] ;$cn_cMTotalCash = number_format($cn_cMTotalCash,2);
            $cn_cMBank = $currMonthCNSale['bank_transfer'];$cn_cMBank = number_format($cn_cMBank,2);
            $cn_cMCheque = $currMonthCNSale['cheque'];$cn_cMCheque = number_format($cn_cMCheque,2);
            $cn_cMCredit = $currMonthCNSale['credit'];$cn_cMCredit = number_format($cn_cMCredit,2);
        }
        $tooltip_cn_CMCnSale = "(Card: {$cn_cMCard},Cash:{$cn_cMTotalCash},Credit:{$cn_cMCredit},Bank:{$cn_cMBank},cheque:{$cn_cMCheque})";
       $special_prv_month_credit_note_sale =  $cn_priviousMntCard = $cn_priviousMnthCASH = $cn_privousMBank = $cn_privousMCheque = 0;
	   
        if(!empty($prevMonthCNSale)){
           $cn_priviousMntCard = $prevMonthCNSale['card'];$cn_priviousMntCard = number_format($cn_priviousMntCard,2);
           $cn_priviousMnthCASH = $prevMonthCNSale['cash']+$prevMonthCNSale['cashEntryAmt'];$cn_priviousMnthCASH = number_format($cn_priviousMnthCASH,2);
           $cn_privousMBank = $prevMonthCNSale['bank_transfer'];$cn_privousMBank = number_format($cn_privousMBank,2);
           $cn_privousMCheque = $prevMonthCNSale['cheque'];$cn_privousMCheque = number_format($cn_privousMCheque,2);
           $cn_privousMCredit = $prevMonthCNSale['credit'];$cn_privousMCredit = number_format($cn_privousMCredit,2);
        }
        
        $tooltip_cn_PMCnSale = "(Card: {$cn_priviousMntCard},Cash:{$cn_priviousMnthCASH},Credit:{$cn_privousMCredit},Bank:{$cn_privousMBank},cheque:{$cn_privousMCheque})";
   
        $todaysCNSale_sum = $todaysCNSale['credit'] + $todaysCNSale['cash'] + $todaysCNSale['bank_transfer'] + $todaysCNSale['cheque'] + $todaysCNSale['card'];
        $yesterdayCNSale_sum = $yesterdayCNSale['credit'] + $yesterdayCNSale['cash'] + $yesterdayCNSale['bank_transfer'] + $yesterdayCNSale['cheque'] + $yesterdayCNSale['card'];
        $currMonthCNSale_sum = $currMonthCNSale['credit'] + $currMonthCNSale['cash'] + $currMonthCNSale['bank_transfer'] + $currMonthCNSale['cheque'] + $currMonthCNSale['card']+ $currMonthCNSale['cashEntryAmt'];
        $prevMonthCNSale_sum = $prevMonthCNSale['credit'] + $prevMonthCNSale['cash'] + $prevMonthCNSale['bank_transfer'] + $prevMonthCNSale['cheque'] + $prevMonthCNSale['card']+ $prevMonthCNSale['cashEntryAmt'];
        $f_todaysCNSale_sum = number_format($todaysCNSale_sum,2);
        $f_yesterdayCNSale_sum = number_format($yesterdayCNSale_sum,2);
        $f_currMonthCNSale_sum = number_format($currMonthCNSale_sum,2);
        $f_prevMonthCNSale_sum = number_format($prevMonthCNSale_sum,2);
    ?>
    <tr style="color: blue">
        <td><strong>Credit Note Refund</strong></td>
        <td><?php echo $currency.$f_todaysCNSale_sum;?><a id='todayCNSale' alt='<?=$tooltip_cn_TodayCnSale;?>', title='<?=$tooltip_cn_TodayCnSale;?>'>(Detail)</a></td>
        <td><?php echo $currency.$f_yesterdayCNSale_sum;?><a id='yesCnSale' alt='<?=$tooltip_cn_YesterdayCnSale;?>', title='<?=$tooltip_cn_YesterdayCnSale;?>'>(Detail)</a></td>
        <td><?php echo $currency.$f_currMonthCNSale_sum;?><a id='CmCnSale' alt='<?=$tooltip_cn_CMCnSale;?>', title='<?=$tooltip_cn_CMCnSale;?>'>(Detail)</a></td>
        <td><?php echo $currency.$f_prevMonthCNSale_sum;?><a id='prvMnSale' alt='<?=$tooltip_cn_PMCnSale;?>', title='<?=$tooltip_cn_PMCnSale;?>'>(Detail)</a></td>
    </tr>
    
<?php
    $loggedInUser = $loggedInUser = $this->request->session()->read('Auth.User.username');
    if(preg_match('/'.QUOT_USER_PREFIX.'/',$loggedInUser)){ 
        $special_today_credit_note_sale = $t_sp_cn_card = $t_sp_cn_cash = $t_sp_cn_today_bank_Transfer = $t_sp_cn_todayCheque = 0;
        //pr($t_todaysCNSale);//pr($t_yesterdayCNSale);
        if(!empty($t_todaysCNSale)){
            $t_sp_cn_card = $t_todaysCNSale['card'];
            $f_t_sp_cn_card = number_format($t_sp_cn_card,2);
            $t_sp_cn_cash = $t_todaysCNSale['cash'];
            $f_t_sp_cn_cash = number_format($t_sp_cn_cash,2);
            $t_sp_cn_today_bank_Transfer = $t_todaysCNSale['bank_transfer'];
            $f_t_sp_cn_today_bank_Transfer = number_format($t_sp_cn_today_bank_Transfer,2);
            $t_sp_cn_todayCheque = $t_todaysCNSale['cheque'];
            $f_t_sp_cn_todayCheque = number_format($t_sp_cn_todayCheque,2);
            $t_todayCredit = $t_todaysCNSale['credit'];
            $f_t_todayCredit = number_format($t_todayCredit,2);
			$special_today_credit_note_sale = $t_todaysCNSale['card']+$t_todaysCNSale['cash']+$t_todaysCNSale['bank_transfer']+$t_todaysCNSale['cheque']+$t_todaysCNSale['credit'];
            $f_special_today_credit_note_sale = number_format($special_today_credit_note_sale,2);
        
        }
        $tooltip_t_TodayProdSale = "(Card: {$f_t_sp_cn_card},Cash:{$f_t_sp_cn_cash},Credit:{$f_t_todayCredit},Bank:{$f_t_sp_cn_today_bank_Transfer},cheque:{$f_t_sp_cn_todayCheque})";
        
        $special_y_credit_note_sale = $y_cn_Card = $y_cn_TotalCash = $y_cn_bank_Transfer = $y_cn_Cheque = 0;
        if(!empty($t_yesterdayCNSale)){
            $y_cn_Card = $t_yesterdayCNSale['card'];
            $f_y_cn_Card = number_format($y_cn_Card,2);
            $y_cn_TotalCash = $t_yesterdayCNSale['cash'];
            $f_y_cn_TotalCash = number_format($y_cn_TotalCash,2);
            $y_cn_bank_Transfer = $t_yesterdayCNSale['bank_transfer'];
            $f_y_cn_bank_Transfer = number_format($y_cn_bank_Transfer,2);
            $y_cn_Cheque = $t_yesterdayCNSale['cheque'];
            $f_y_cn_Cheque = number_format($y_cn_Cheque,2);
            $y_cn_yCredit = $t_yesterdayCNSale['credit'];
            $f_y_cn_yCredit = number_format($y_cn_yCredit,2);
			$special_y_credit_note_sale = $t_yesterdayCNSale['card']+$t_yesterdayCNSale['cash']+$t_yesterdayCNSale['bank_transfer']+$t_yesterdayCNSale['cheque']+$t_yesterdayCNSale['credit'];
            $f_special_y_credit_note_sale = number_format($special_y_credit_note_sale,2);
        }
        $tooltip_t_YesterdayProdSale = "(Card: {$f_y_cn_Card},Cash:{$f_y_cn_TotalCash},Credit:{$f_y_cn_yCredit},Bank:{$f_y_cn_bank_Transfer},cheque:{$f_y_cn_Cheque})";
        $special_this_month_credit_note_sale = $t_cn_cMCard = $t_cn_cMTotalCash = $t_cn_cMBank = $t_cn_cMCheque = 0;
		
        if(!empty($t_currMonthCNSale)){ 
            $t_cn_cMCard = $t_currMonthCNSale['card'];
            $f_t_cn_cMCard = number_format($t_cn_cMCard,2);
            $t_cn_cMTotalCash = $t_currMonthCNSale['cash'] + $t_currMonthCNSale['cashEntryAmt'];
            $f_t_cn_cMTotalCash = number_format($t_cn_cMTotalCash,2);
            $t_cn_cMBank = $t_currMonthCNSale['bank_transfer'];
            $f_t_cn_cMBank = number_format($t_cn_cMBank,2);
            $t_cn_cMCheque = $t_currMonthCNSale['cheque'];
            $f_t_cn_cMCheque = number_format($t_cn_cMCheque,2);
            $t_cn_cMCredit = $t_currMonthCNSale['credit'];
            $f_t_cn_cMCredit = number_format($t_cn_cMCredit,2);
			$special_this_month_credit_note_sale = $t_currMonthCNSale['card']+$t_currMonthCNSale['cash']+$t_currMonthCNSale['bank_transfer']+$t_currMonthCNSale['cheque']+$t_currMonthCNSale['credit'] + $t_currMonthCNSale['cashEntryAmt'];
            $f_special_this_month_credit_note_sale = number_format($special_this_month_credit_note_sale,2);
        }
        $tooltip_t_CMProdSale = "(Card: {$f_t_cn_cMCard},Cash:{$f_t_cn_cMTotalCash},Credit:{$f_t_cn_cMCredit},Bank:{$f_t_cn_cMBank},cheque:{$f_t_cn_cMCheque})";
        $special_prv_month_credit_note_sale =  $t_cn_priviousMntCard = $t_cn_priviousMnthCASH = $t_cn_privousMBank = $t_cn_privousMCheque = 0;
	   
        if(!empty($t_prevMonthCNSale)){
           $t_cn_priviousMntCard = $t_prevMonthCNSale['card'];
           $f_t_cn_priviousMntCard = number_format($t_cn_priviousMntCard,2);
           $t_cn_priviousMnthCASH = $t_prevMonthCNSale['cash'] + $t_prevMonthCNSale['cashEntryAmt'];
           $f_t_cn_priviousMnthCASH = number_format($t_cn_priviousMnthCASH,2);
           $t_cn_privousMBank = $t_prevMonthCNSale['bank_transfer'];
           $f_t_cn_privousMBank = number_format($t_cn_privousMBank,2);
           $t_cn_privousMCheque = $t_prevMonthCNSale['cheque'];
           $f_t_cn_privousMCheque = number_format($t_cn_privousMCheque,2);
           $t_cn_privousMCredit = $t_prevMonthCNSale['credit'];
           $f_t_cn_privousMCredit = number_format($t_cn_privousMCredit,2);
		   $special_prv_month_credit_note_sale = $t_prevMonthCNSale['card']+$t_prevMonthCNSale['cash']+$t_prevMonthCNSale['bank_transfer']+$t_prevMonthCNSale['cheque']+$t_prevMonthCNSale['credit'] + $t_prevMonthCNSale['cashEntryAmt'];
           $f_special_prv_month_credit_note_sale = number_format($special_prv_month_credit_note_sale,2);
        }
        
        $tooltip_t_PMProdSale = "(Card: {$f_t_cn_priviousMntCard},Cash:{$f_t_cn_priviousMnthCASH},Credit:{$f_t_cn_privousMCredit},Bank:{$f_t_cn_privousMBank},cheque:{$f_t_cn_privousMCheque})";
    ?>
    
    <tr>
        <td><strong>Credit Quotation</strong></td>
        <td><?php echo $currency.$f_special_today_credit_note_sale;?> <a id='t_todayProdSale' alt='<?=$tooltip_t_TodayProdSale;?>', title='<?=$tooltip_t_TodayProdSale;?>'>(Detail)</a></td>
        <td><?php echo $currency.$f_special_y_credit_note_sale;?> <a id='t_yesProdSale' alt='<?=$tooltip_t_YesterdayProdSale;?>', title='<?=$tooltip_t_YesterdayProdSale;?>'>(Detail)</a></td>
        <td><?php echo $currency.$f_special_this_month_credit_note_sale;?> <a id='t_this_mnth_ProdSale' alt='<?=$tooltip_t_CMProdSale;?>', title='<?=$tooltip_t_CMProdSale;?>'>(Detail)</a></td>
        <td><?php echo $currency.$f_special_prv_month_credit_note_sale;?> <a id='t_prv_mnth_ProdSale' alt='<?=$tooltip_t_PMProdSale;?>', title='<?=$tooltip_t_PMProdSale;?>'>(Detail)</a></td>
    </tr>
  
<?php
    }
?>
<?php
    $f_todayProductRefund = number_format($todayProductRefund,2);
    $f_yestdayProductRefund = number_format($yestdayProductRefund,2);
    $f_currentMonthProductRefund = number_format($currentMonthProductRefund,2);
    $f_previousMonthProductRefund = number_format($previousMonthProductRefund,2);
    
    $f_todaysMcardPayment = number_format($todaysMcardPayment,2);
    $f_todaysMcashPayment = number_format($todaysMcashPayment,2);
    $f_yesterdaysMcardPayment = number_format($yesterdaysMcardPayment,2);
    $f_yesterdaysMcashPayment = number_format($yesterdaysMcashPayment,2);
    $f_currentMonthMcashPayment = number_format($currentMonthMcashPayment,2);
    $f_previousMonthMcashPayment = number_format($previousMonthMcashPayment,2);
    
    $tooltipTodayMobSale = "(Card: {$f_todaysMcardPayment},Cash:{$f_todaysMcashPayment})";
    $tooltipYesterdayMobSale = "(Card: {$f_yesterdaysMcardPayment},Cash:{$f_yesterdaysMcashPayment})";
    $tooltipCMMobSale = "(Card: {$currentMonthMcardPayment},Cash:{$f_currentMonthMcashPayment})";
    $tooltipPMMobSale = "(Card: {$previousMonthMcardPayment},Cash:{$previousMonthMcashPayment})";
?>
    <tr style="color: blue">
        <td><strong>Product Refund</strong></td>
        <td><?php echo $currency.$f_todayProductRefund; ?></td>
        <td><?php echo $currency.$f_yestdayProductRefund;?></td>
        <td><?php echo $currency.$f_currentMonthProductRefund;?></td>
        <td><?php echo $currency.$f_previousMonthProductRefund;?></td>
    </tr>
    <tr>
        <td><strong>Mobile Sale</strong></td>
        <td><?php echo $currency.$todayMobileSale;?><a id='todayMobSale' alt='<?=$tooltipTodayMobSale;?>', title='<?=$tooltipTodayMobSale;?>'>(Detail)</a></td>
        <td><?php echo $currency.$yesterdayMobileSale;?> <a id='yesterdayMobSale' alt='<?=$tooltipYesterdayMobSale;?>', title='<?=$tooltipYesterdayMobSale;?>'>(Detail)</a></td>
        <td><?php echo $currency.$currentMonthMobileSale;?><a id='cmMobSale' alt='<?=$tooltipCMMobSale;?>', title='<?=$tooltipCMMobSale;?>'>(Detail)</a></td>
        <td><?php echo $currency.$previousMonthMobileSale;?><a id='pmMobSale' alt='<?=$tooltipPMMobSale;?>', title='<?=$tooltipPMMobSale;?>'>(Detail)</a></td>
    </tr>
    <?php
        $tooltipTodayBMSale = "(Card: {$todaysBlkMcardPayment},Cash:{$todaysBlkMcashPayment})";
        $tooltipYesterdayBMSale = "(Card: {$yesterdaysBlkMcardPayment},Cash:{$yesterdaysBlkMcashPayment})";
        $tooltipCMBMSale = "(Card: {$currentMonthBlkMcardPayment},Cash:{$currentMonthBlkMcashPayment})";
        $tooltipPMBMSale = "(Card: {$previousMonthBlkMcardPayment},Cash:{$previousMonthBlkMcashPayment})";
    ?>
    <tr>
        <td><strong>Mobile Blk Sale</strong></td>
        <td><?php echo $currency.$todayBlkMobileSale;?><a id='todayBMSale' alt='<?=$tooltipTodayBMSale;?>', title='<?=$tooltipTodayBMSale;?>'>(Detail)</a></td>
        <td><?php echo $currency.$yesterdayBlkMobileSale;?><a id='yesterdayBMSale' alt='<?=$tooltipYesterdayBMSale;?>', title='<?=$tooltipYesterdayBMSale;?>'>(Detail)</a></td>
        <td><?php echo $currency.$currentMonthBlkMobileSale;?><a id='cmBMSale' alt='<?=$tooltipCMBMSale;?>', title='<?=$tooltipCMBMSale;?>'>(Detail)</a></td>
        <td><?php echo $currency.$previousMonthBlkMobileSale;?><a id='pmBMSale' alt='<?=$tooltipPMBMSale;?>', title='<?=$tooltipPMBMSale;?>'>(Detail)</a></td>
    </tr>
    
     <tr>
        <td><strong>Mobile Blk Refund</strong></td>
        <?php
        $todayBlkMobileRefund = (float)$todayBlkMobileRefund;
        $yesterdayBlkMobileRefund = (float)$yesterdayBlkMobileRefund;
        $currentMonthBlkMobileRefund = (float)$currentMonthBlkMobileRefund;
        $previousMonthBlkMobileRefund = (float)$previousMonthBlkMobileRefund;
        $f_previousMonthBlkMobileRefund = number_format($previousMonthBlkMobileRefund,2);
        
    ?>
        <td><?php echo $currency.$todayBlkMobileRefund;?></td>
        <td><?php echo $currency.$yesterdayBlkMobileRefund;?></td>
        <td><?php echo $currency.$currentMonthBlkMobileRefund;?></td>
        <td><?php  echo $currency.$f_previousMonthBlkMobileRefund;?></td>
    </tr>
<?php
    $f_todayMobilePurchase = number_format($todayMobilePurchase,2);
    $f_yesterdayMobilePurchase = number_format($yesterdayMobilePurchase,2);
    $f_currentMonthMobilePurchase = number_format($currentMonthMobilePurchase,2);
    $f_previousMonthMobilePurchase = number_format($previousMonthMobilePurchase,2);
?>
    <tr>
        <td><strong>Mobile Purchase</strong></td>
        <td><?php echo $currency.$f_todayMobilePurchase;?></td>
        <td><?php echo $currency.$f_yesterdayMobilePurchase;?></td>
        <td><?php echo $currency.$f_currentMonthMobilePurchase;?></td>
        <td><?php echo $currency.$f_previousMonthMobilePurchase;?></td>
    </tr>
    <tr style="color: blue">
        <td><strong>Mobile Refund</strong></td>
        <?php
            $todayMobileRefund = (float)$todayMobileRefund;
            $yesterdayMobileRefund = (float)$yesterdayMobileRefund;
            $currentMonthMobileRefund = (float)$currentMonthMobileRefund;
            $previousMonthMobileRefund = (float)$previousMonthMobileRefund;
        ?>
        <td><?php echo $currency.number_format($todayMobileRefund,2);?></td>
        <td><?php echo $currency.number_format($yesterdayMobileRefund,2);?></td>
        <td><?php echo $currency.number_format($currentMonthMobileRefund,2);?></td>
        <td><?php echo $currency.number_format($previousMonthMobileRefund,2);?></td>
    </tr>
    <?php
    $todayTotalSale = $todaysSale+$todaysUsale+$todayProductSale+$todayMobileSale+$todayBlkMobileSale;
    $yesterdayTotalSale = $yesterdaySale+$yesterdayUsale+$yesterdayProductSale+$yesterdayMobileSale+$yesterdayBlkMobileSale;
    $currentMonthTotalSale = $currentMonthRepairSale+$currentMonthUnlockSale+$currentMonthProductSale+$currentMonthMobileSale+$currentMonthBlkMobileSale;
    
    $previousMonthTotalSale = $previousMonthRepairSale+$previousMonthUnlockSale+$previousMonthProductSale+$previousMonthMobileSale+$previousMonthBlkMobileSale;
    
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
    $f_todayTotalSale1 = $todayTotalSale1;
    $f_yesterdayTotalSale1 = number_format($yesterdayTotalSale1,2);
    $f_currentMonthTotalSale1 = number_format($currentMonthTotalSale1,2);
    $f_previousMonthTotalSale1 = number_format($previousMonthTotalSale1,2);
    ?>
    <tr>
        <td><strong>Total Sale</strong></td>
        <td><?php echo $currency.$f_todayTotalSale1;?></td>
        <td><?php echo $currency.$f_yesterdayTotalSale1;?></td>
        <td><?php echo $currency.$f_currentMonthTotalSale1;?></td>
        <td><?php echo $currency.$f_previousMonthTotalSale1;?></td>
    </tr>
    <?php
    $t_today_cn_sale = array_sum($t_todaysCNSale)-$t_todaysCNSale['cashEntryAmt'];
	$t_yes_cn_sale = array_sum($t_yesterdayCNSale)-$t_yesterdayCNSale['cashEntryAmt'];
	$t_cm_cn_sale = array_sum($t_currMonthCNSale);
	$t_prv_mn_cn_sale = array_sum($t_prevMonthCNSale);
    $todayCNCardRefund = $todaysCNSale['credit'] + $todaysCNSale['cash'] + $todaysCNSale['bank_transfer'] + $todaysCNSale['cheque'] + $todaysCNSale['card'];
     
    $todayTotalRefund = $todaysRefund+$todaysUrefund+$todayProductRefund+$todayMobileRefund+$todayBlkMobileRefund+$todayCNCardRefund;
    if(preg_match('/'.QUOT_USER_PREFIX.'/',$loggedInUser)){
        $todayTotalRefund1 = $todayTotalRefund+$t_today_cn_sale;
    }else{
        $todayTotalRefund1 = $todayTotalRefund;
    }   
    $yesterdayCNCardRefund = array_sum($yesterdayCNSale) -$yesterdayCNSale['cashEntryAmt'] ;
    $yesterdayTotalRefund = $yesterdaysRefund+$yesterdaysUrefund+$yestdayProductRefund+$yesterdayMobileRefund+$yesterdayBlkMobileRefund+$yesterdayCNCardRefund;
        
    if(preg_match('/'.QUOT_USER_PREFIX.'/',$loggedInUser)){
        $yesterdayTotalRefund1 = $yesterdayTotalRefund+$t_yes_cn_sale;
    }else{
        $yesterdayTotalRefund1 = $yesterdayTotalRefund;
    }
        
    $f_todayNetSale = $todayNetSale = $todayTotalSale-$todayTotalRefund;
    $f_yesterdayNetSale = $yesterdayNetSale = $yesterdayTotalSale-$yesterdayTotalRefund;
    
    $currMonthCNCardRefund = array_sum($currMonthCNSale);
    $currentMonthTotalRefund = $currentMonthRepairRefund+$currentMonthUnlockRefund+$currentMonthProductRefund+$currentMonthMobileRefund+$currentMonthBlkMobileRefund+$currMonthCNCardRefund;
        
    if(preg_match('/'.QUOT_USER_PREFIX.'/',$loggedInUser)){
        $currentMonthTotalRefund1 = $currentMonthTotalRefund+$t_cm_cn_sale;
    }else{
        $currentMonthTotalRefund1 = $currentMonthTotalRefund;
    }
    $prevMonthCNCardRefund = array_sum($prevMonthCNSale);
    $previousMonthTotalRefund = $previousMonthRepairRefund+$previousMonthUnlockRefund+$previousMonthProductRefund+$previousMonthMobileRefund+$previousMonthBlkMobileRefund+$prevMonthCNCardRefund;
        
    if(preg_match('/'.QUOT_USER_PREFIX.'/',$loggedInUser)){
        $previousMonthTotalRefund1 = $previousMonthTotalRefund+$t_prv_mn_cn_sale;
    }else{
        $previousMonthTotalRefund1 = $previousMonthTotalRefund;
    }
    $f_currentMonthNetSale  = $currentMonthNetSale = $currentMonthTotalSale-$currentMonthTotalRefund;
    $previousMonthNetSale = $previousMonthTotalSale-$previousMonthTotalRefund;
    
        
    $todayTotalCard = $todaysRcardPayment+$todaysUcardPayment+$todaysPcardPayment+$todaysMcardPayment+$todaysBlkMcardPayment;
    $yesterdayTotalCard = $yesterdaysRcardPayment+$yesterdaysUcardPayment+$yesterdaysPcardPayment+$yesterdaysMcardPayment + $yesterdaysBlkMcardPayment;
    $currentMonthTotalCard = $currentMonthRcardPayment+$currentMonthUcardPayment+$currentMonthPcardPayment+$currentMonthMcardPayment+$currentMonthBlkMcardPayment;
    $previousMonthTotalCard = $previousMonthRcardPayment+$previousMonthUcardPayment+$previousMonthPcardPayment+$previousMonthMcardPayment+$previousMonthBlkMcardPayment;
?>
    <tr style="color: blue">
        <td><strong>Total Refund</strong></td>
        <td><?php $todayTotalRefund1 = number_format($todayTotalRefund1,2);echo $currency.$todayTotalRefund1;?></td>
        <td><?php $yesterdayTotalRefund1 = number_format($yesterdayTotalRefund1,2);echo $currency.$yesterdayTotalRefund1;?></td>
        <td><?php $currentMonthTotalRefund1 = number_format($currentMonthTotalRefund1,2);echo $currency.$currentMonthTotalRefund1;?></td>
        <td><?php $previousMonthTotalRefund1 = number_format($previousMonthTotalRefund1,2);echo $currency.$previousMonthTotalRefund1;?></td>
    </tr>
<?php
    if(preg_match('/'.QUOT_USER_PREFIX.'/',$loggedInUser)){
       $todayNetSale = $todayNetSale + $t_today_total_amount - $special_today_credit_note_sale;
       $yesterdayNetSale = $yesterdayNetSale + $t_yes_total_amount - $special_y_credit_note_sale;
       $currentMonthNetSale = $currentMonthNetSale + $t_this_mnth_total_amount - $special_this_month_credit_note_sale;
       $previousMonthNetSale = $previousMonthNetSale + $t_prv_mnth_total_amount - $special_prv_month_credit_note_sale;
       $f_todayNetSale = number_format($todayNetSale,2);
       $f_yesterdayNetSale = number_format($yesterdayNetSale,2);
       $f_currentMonthNetSale = number_format($currentMonthNetSale,2);
    }
?>
    <tr>
        <td><strong>Net Sale</strong></td>
        <td><?php echo $currency.$f_todayNetSale;?></td>
        <td><?php echo $currency.$f_yesterdayNetSale;?></td>
        <td><?php echo $currency.$f_currentMonthNetSale;?></td>
        <td><?php echo $currency.number_format($previousMonthNetSale,2);?></td>
    </tr>
<?php
    $todayTotalCard = $todayTotalCard + $todayProductPmtDetails['card'];;
    $yesterdayTotalCard = $yesterdayTotalCard + $yesterdayProductPmtDetails['card'];
    $currentMonthTotalCard = $currentMonthTotalCard + $currentMonthProductPmtDetails['card'];
    $previousMonthTotalCard = $previousMonthTotalCard + $previousMonthProductPmtDetails['card'];
    if(preg_match('/'.QUOT_USER_PREFIX.'/',$loggedInUser)){
        $todayTotalCard = $todayTotalCard + $t_card;
        $yesterdayTotalCard = $yesterdayTotalCard + $t_yCard;
        $currentMonthTotalCard = $currentMonthTotalCard + $t_cMCard;
        $previousMonthTotalCard = $previousMonthTotalCard + $t_priviousMntCard;
        
        $todayTotalCard = $todayTotalCard - $t_todaysCNSale['card'];
        $yesterdayTotalCard = $yesterdayTotalCard - $t_yesterdayCNSale['card'];
        $currentMonthTotalCard = $currentMonthTotalCard - $t_currMonthCNSale['card'];
        $previousMonthTotalCard = $previousMonthTotalCard - $t_prevMonthCNSale['card'];
    }
    $todayTotalCard = $todayTotalCard - $todaysCNSale['card']; $todayTotalCard = $todayTotalCard;
    $yesterdayTotalCard = $yesterdayTotalCard- $yesterdayCNSale['card'];$yesterdayTotalCard = number_format($yesterdayTotalCard,2);
    $currentMonthTotalCard = $currentMonthTotalCard- $currMonthCNSale['card'];
    $previousMonthTotalCard = $previousMonthTotalCard - $prevMonthCNSale['card'];

    $card = $todaysPcardPayment + $todayProductPmtDetails['card'];
    $f_2_card = number_format($card,2);
    $prv_card = $credit_to_other_changed['card'];
    $prv_card = number_format($prv_card,2);
    
    if(preg_match('/'.QUOT_USER_PREFIX.'/',$loggedInUser)){
        $t_cash = $t_today_pay_details['card'];
        $f_t_cash = number_format($t_cash,2);
    }
     
    $CardPaymt = $todaysRcardPayment+$todaysUcardPayment+$card+$todaysBlkMcardPayment+$todaysMcardPayment;//+$prv_card;
    $f_CardPaymt = number_format($CardPaymt,2);
    $today_card_paymt = $CardPaymt - $todaysCNSale['card'];
    $yCardPaymt = $yesterdaysRcardPayment + $yesterdaysUcardPayment + $yCard + $yesterdaysBlkMcardPayment + $yesterdaysMcardPayment;
    $y_card_paymt = $yCardPaymt - $yesterdayCNSale['card'];
    $cmProdCardPmt = $currentMonthProductPmtDetails['card'];
    $cMCard = $currentMonthPcardPayment + $cmProdCardPmt;
    $f_cMCard = number_format($cMCard,2);
    $pmProdCardPmt = $previousMonthProductPmtDetails['card'];
    $cMCardPaymt = $currentMonthRcardPayment + $currentMonthUcardPayment + $cMCard + $currentMonthBlkMcardPayment + $currentMonthMcardPayment;
    $cM_card_paymt = $cMCardPaymt - $currMonthCNSale['card'];
    $priviousMntCard = $pmProdCardPmt + $previousMonthPcardPayment;
    $f_priviousMntCard = number_format($priviousMntCard,2);
    $prvsMNthCardPaymt = $previousMonthRcashPayment + $previousMonthUcardPayment + $priviousMntCard + $previousMonthBlkMcardPayment +$previousMonthMcardPayment;
    $prv_Mnth_card = $prvsMNthCardPaymt - $prevMonthCNSale['card'];
      
    $cn_card_today = $todaysCNSale['card'];
    $cn_card_today = number_format($cn_card_today,2);
    if(preg_match('/'.QUOT_USER_PREFIX.'/',$loggedInUser)){
        $CardPaymt = $CardPaymt + $t_cash;
        $f_CardPaymt = number_format($CardPaymt,2);
        $spcl_cn_card = $t_todaysCNSale['card'];
        $spcl_cn_card = number_format($spcl_cn_card,2);
        $tooltipTodayNetCardDetail = "(Repair({$todaysRcardPayment})+Unlock({$todaysUcardPayment})+Product({$f_2_card})+Blk({$todaysBlkMcardPayment})+Mobile({$todaysMcardPayment})+special({$f_t_cash})+prev_recpts_sale({$prv_card})-credit note({$cn_card_today}) - special credit note({$spcl_cn_card}))= {$f_CardPaymt}";
    }else{
        $tooltipTodayNetCardDetail = "(Repair({$todaysRcardPayment})+Unlock({$todaysUcardPayment})+Product({$f_2_card})+Blk({$todaysBlkMcardPayment})+Mobile({$todaysMcardPayment})+prev_recpts_sale({$prv_card})-credit note({$cn_card_today})) = {$f_CardPaymt}";
    }
    $todayCNSaleCard = $todaysCNSale['card'];
    $todayCNSaleCard = number_format($todayCNSaleCard,2);
    if(preg_match('/'.QUOT_USER_PREFIX.'/',$loggedInUser)){
        $todayCNSaleCard = $todayCNSaleCard + $spcl_cn_card;
        $todayCNSaleCard = number_format($todayCNSaleCard,2);
    }
    $CardPaymt = number_format($CardPaymt,2);
    $tooltipTodayNetCardDetail.="({$CardPaymt} - {$todayCNSaleCard}) = {$todayTotalCard}";
    
     $yCNSaleCard = $yesterdayCNSale['card'];
     $yCNSaleCard = number_format($yCNSaleCard,2);
    if(preg_match('/'.QUOT_USER_PREFIX.'/',$loggedInUser)){
        $yCardPaymt = $yCardPaymt + $t_yCard;
        $yCardPaymt = number_format($yCardPaymt,2);
        $yes_sp_credit_note = $t_yesterdayCNSale['card'];
        $yes_sp_credit_note = number_format($yes_sp_credit_note,2);
        $tooltipYNetCardDetail = "(Repair({$yesterdaysRcardPayment})+Unlock({$yesterdaysUcardPayment})+Product({$yCard})+Blk({$yesterdaysBlkMcardPayment})+Mobile({$yesterdaysMcardPayment})+special({$t_yCard})-credit note({$yCNSaleCard})-special credit note({$yes_sp_credit_note}))= {$yCardPaymt}";
    }else{
        $tooltipYNetCardDetail = "(Repair({$yesterdaysRcardPayment})+Unlock({$yesterdaysUcardPayment})+Product({$yCard})+Blk({$yesterdaysBlkMcardPayment})+Mobile({$yesterdaysMcardPayment})-credit note({$yCNSaleCard}))= {$yCardPaymt}";
    }
        
    if(preg_match('/'.QUOT_USER_PREFIX.'/',$loggedInUser)){
        $yCNSaleCard = $yCNSaleCard + $yes_sp_credit_note;
        $yCNSaleCard = number_format($yCNSaleCard,2);
    }
    $tooltipYNetCardDetail.="({$yCardPaymt} - {$yCNSaleCard}) = {$yesterdayTotalCard}";
     
    $mCNSaleCard = $currMonthCNSale['card'];
    $mCNSaleCard = number_format($mCNSaleCard,2);
    if(preg_match('/'.QUOT_USER_PREFIX.'/',$loggedInUser)){
        $cMCardPaymt = $cMCardPaymt + $t_cMCard;
        $cMCardPaymt = number_format($cMCardPaymt,2);
        $cn_currnt_mnth_special = $t_currMonthCNSale['card'];
        $cn_currnt_mnth_special = number_format($cn_currnt_mnth_special,2);
        $tooltipMNetCardDetail = "(Repair({$currentMonthRcardPayment})+Unlock({$currentMonthUcardPayment})+Product({$f_cMCard})+Blk({$currentMonthBlkMcardPayment})+Mobile({$currentMonthMcardPayment})+special({$t_cMCard})-credit note({$mCNSaleCard})-special credit({$cn_currnt_mnth_special}))= {$cMCardPaymt}";
    }else{
        $tooltipMNetCardDetail = "(Repair({$currentMonthRcardPayment})+Unlock({$currentMonthUcardPayment})+Product({$f_cMCard})+Blk({$currentMonthBlkMcardPayment})+Mobile({$currentMonthMcardPayment})-credit note({$mCNSaleCard}))= {$cMCardPaymt}";
    }
        
        if(preg_match('/'.QUOT_USER_PREFIX.'/',$loggedInUser)){
            $mCNSaleCard = $mCNSaleCard + $cn_currnt_mnth_special;
             $mCNSaleCard = number_format($mCNSaleCard,2);
        }
        $tooltipMNetCardDetail.="({$cMCardPaymt} - {$mCNSaleCard}) = {$currentMonthTotalCard}";
        
        $pmCNSaleCard = $prevMonthCNSale['card'];
         $pmCNSaleCard = number_format($pmCNSaleCard,2);
        if(preg_match('/'.QUOT_USER_PREFIX.'/',$loggedInUser)){
            $prvsMNthCardPaymt = $prvsMNthCardPaymt + $t_priviousMntCard;
            $prvsMNthCardPaymt = number_format($prvsMNthCardPaymt,2);
			$prv_mnth_sp_credit_note = $t_prevMonthCNSale['card'];
            $prv_mnth_sp_credit_note = number_format($prv_mnth_sp_credit_note,2);
            // $priviousMntCard = number_format($priviousMntCard,2);
            $pmCNSaleCard = number_format($pmCNSaleCard,2);
            $tooltipPMNetCardDetail = "(Repair({$previousMonthRcashPayment})+Unlock({$previousMonthUcardPayment})+Product({$f_priviousMntCard})+Blk({$previousMonthBlkMcardPayment})+Mobile({$previousMonthMcardPayment})+special({$t_priviousMntCard})-credit note({$pmCNSaleCard})-special credit note({$prv_mnth_sp_credit_note}))= {$prvsMNthCardPaymt}<br/>";
        }else{
            $tooltipPMNetCardDetail = "(Repair({$previousMonthRcashPayment})+Unlock({$previousMonthUcardPayment})+Product({$f_priviousMntCard})+Blk({$previousMonthBlkMcardPayment})+Mobile({$previousMonthMcardPayment})-credit note({$pmCNSaleCard}))= {$prvsMNthCardPaymt}<br/>";
        }
        
        if(preg_match('/'.QUOT_USER_PREFIX.'/',$loggedInUser)){
            $pmCNSaleCard = $pmCNSaleCard + $prv_mnth_sp_credit_note;
             $pmCNSaleCard = number_format($pmCNSaleCard,2);
        }
        $tooltipPMNetCardDetail.="({$prvsMNthCardPaymt} - {$pmCNSaleCard}) = {$previousMonthTotalCard}";
    
        //$today_card_paymt = $today_card_paymt + $credit_to_other_changed['card'];
    ?>
    
    <?php $currentMonthTotalCard = (float)$currentMonthTotalCard;
    $previousMonthTotalCard = (float)$previousMonthTotalCard;
    ?>
    <tr style="color: brown;">
        <td><strong>Total Card Payment</strong></td>
        <td><?php //$todayTotalCard = number_format($todayTotalCard,2);
        echo $currency.$todayTotalCard;?><a id='todayNetCard' title='<?=$tooltipTodayNetCardDetail?>' alt='<?=$tooltipTodayNetCardDetail?>'>(Detail)</a></td>
        <td><?php //$yesterdayTotalCard = number_format($yesterdayTotalCard,2);
        echo $currency.number_format($yesterdayTotalCard,2);?><a id='yesterdayNetCard' title='<?=$tooltipYNetCardDetail?>' alt='<?=$tooltipYNetCardDetail?>'>(Detail)</a></td>
        <td><?php //$currentMonthTotalCard = number_format($currentMonthTotalCard,2);
        echo $currency.number_format($currentMonthTotalCard,2) ;?><a id='cmNetCard' title='<?=$tooltipMNetCardDetail?>' alt='<?=$tooltipMNetCardDetail?>'>(Detail)</a></td>
        <td><?php //$previousMonthTotalCard = number_format($previousMonthTotalCard,2);
        echo $currency.number_format($previousMonthTotalCard,2);?><a id='pmNetCard' title='<?=$tooltipPMNetCardDetail?>' alt='<?=$tooltipPMNetCardDetail?>'>(Detail)</a></td>
    </tr>
    <?php
    $todayCashInHand = $todayNetSale-$todayMobilePurchase-$todayTotalCard;
    $yesterdayCashInHand = $yesterdayNetSale-$yesterdayMobilePurchase-$yesterdayTotalCard;
    $currentMonthCashInHand = $currentMonthNetSale-$currentMonthMobilePurchase-$currentMonthTotalCard;
    $previousMonthCashInHand = $previousMonthNetSale-$previousMonthMobilePurchase-$previousMonthTotalCard;
    //pr($todayProductPmtDetails);die;
    $today_bank_Transfer = $todayProductPmtDetails['bank_transfer'];
    $pCash = $todayCash+ $todayProductPmtDetails['cash']; 
    $total_cash = $todaysRcashPayment + $todaysUcashPayment + $pCash + $todaysBlkMcashPayment + $todaysMcashPayment ;
        
    // $total_cash = number_format($total_cash,2); 
    $total_refund = $todaysRefund + $todaysUrefund + $todayProductRefund + $todayMobileRefund + $todayBlkMobileRefund + $todaysCNSale['cash'] + $todayMobilePurchase+$todaysCNSale['cashEntryAmt'];
    $total_refund = number_format($total_refund,2); 
    $end_today_cash = $total_cash - $total_refund;
//    $end_today_cash = number_format($end_today_cash,2);
    
    if(preg_match('/'.QUOT_USER_PREFIX.'/',$loggedInUser)){
        $t_cash = $t_today_pay_details['cash'];
        // $t_cash = number_format($t_cash,2);
        $total_cash = $total_cash + $t_cash; //$total_cash = number_format($total_cash,2);
    }
    $pYCash = $yesCash+ $yesterdayProductPmtDetails['cash'];
    $yCash = $yesterdaysRcashPayment + $yesterdaysUcashPayment + $pYCash + $yesterdaysBlkMcashPayment + $yesterdaysMcashPayment;
    // $yCash = number_format($yCash,2);
    $yRefund = $yesterdaysRefund + $yesterdaysUrefund + $yestdayProductRefund + $yesterdayMobileRefund + $yesterdayBlkMobileRefund +$yesterdayCNSale['cash'] + $yesterdayMobilePurchase + $yesterdayCNSale['cashEntryAmt'];
    // $yRefund = number_format($yRefund,2);
    $end_y_cash = $yCash - $yRefund;
    // $end_y_cash = number_format($end_y_cash,2);
    $this_mnth_cash = $currentMonthProductPmtDetails['cash'] + $currentMonthRcashPayment + $currentMonthUcashPayment + $currentMonthMcashPayment + $currentMonthBlkMcashPayment + $thisMnthCash;
        
    if(preg_match('/'.QUOT_USER_PREFIX.'/',$loggedInUser)){
        $this_mnth_cash = $this_mnth_cash + $t_cMTotalCash;
    }
    
    //$this_mnth_cash = number_format($this_mnth_cash,2);
    $this_mnth_refund = $currentMonthProductRefund + $currentMonthRepairRefund + $currentMonthUnlockRefund + $currentMonthMobileRefund + $currentMonthBlkMobileRefund + $currentMonthMobilePurchase + $currMonthCNSale['cash']+ $currMonthCNSale['cashEntryAmt'];
    
    // $this_mnth_refund = number_format($this_mnth_refund,2);
    $this_mnth_sale = $this_mnth_cash - $this_mnth_refund;
    if(preg_match('/'.QUOT_USER_PREFIX.'/',$loggedInUser)){
        $cm_cn_cash_sp = $t_currMonthCNSale['cash'] + $t_currMonthCNSale['cashEntryAmt'];
        $this_mnth_refund = $this_mnth_refund + $cm_cn_cash_sp;
        $this_mnth_sale = $this_mnth_sale-$cm_cn_cash_sp;
    }
        
        
         $privous_mnth_cash = $previousMonthProductPmtDetails['cash'] + $previousMonthRcashPayment + $previousMonthUcashPayment + $previousMonthBlkMcashPayment + $previousMonthMcashPayment +$prvMnthCash;
        
        $privious_mnth_refund = $previousMonthProductRefund + $previousMonthRepairRefund + $previousMonthUnlockRefund + $previousMonthBlkMobileRefund + $previousMonthMobileRefund + $previousMonthMobilePurchase + $prevMonthCNSale['cash']+ $prevMonthCNSale['cashEntryAmt'];
        // $privious_mnth_refund = number_format($privious_mnth_refund,2);
        $privious_mnth_sale = $privous_mnth_cash - $privious_mnth_refund;
    
    ?>
    
    <?php if($this->request->session()->read('Auth.User.user_type') == 'wholesale'){ // user type check brace ?>
	<?php
       //pr($credit_to_other_changed);die;
	    $prv_cash = $credit_to_other_changed['cash'];
        $prv_bnk = $credit_to_other_changed['bank_transfer'];
        $prv_credit = $credit_to_other_changed['credit'];
        $prv_card = $credit_to_other_changed['card'];
        $prv_cheque = $credit_to_other_changed['cheque'];
	   
	    $totdayCridit = $todayProductPmtDetails['credit'];
		$yCridit = $yesterdayProductPmtDetails['credit'];
		$cMCridit = $currentMonthProductPmtDetails['credit'];
		$privousMCridt = $previousMonthProductPmtDetails['credit'];
	   
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
            //pr($prv_mnth_pay_details);
            $t_privousMCredit1 = $prv_mnth_pay_details['credit'];
            $privousMCridt = $privousMCridt + $t_privousMCredit1;
            $todayCridit = $todayCridit + $t_todayCredit1 - $today_credit_sp;
            $yCreidt = $yCreidt + $t_yCredit1 - $yes_credit_sp;
            $cMCriedt = $cMCriedt + $t_cMCredit1 - $cm_credit_sp;
            $privousMnthCredit = $privousMnthCredit + $t_privousMCredit1 - $prv_mnth_credit_sp;
        }
        
        $f_totdayCridit = number_format($totdayCridit,2);
        $tcreidt = number_format($tcreidt,2);
         //$todayCridit = number_format($todayCridit,2);
         
        $yCridit = number_format($yCridit,2);
        $ycreidt = number_format($ycreidt,2);
       // $yCreidt = number_format($yCreidt,2);
        
        $cMCridit = number_format($cMCridit,2);
        $cMcreidt = number_format($cMcreidt,2);
       // $cMCriedt = number_format($cMCriedt,2);
        
        $privousMCridt = number_format($privousMCridt,2);
        $pMcreidt = number_format($pMcreidt,2);
       // $privousMnthCredit = number_format($privousMnthCredit,2);
        $tooltipTodayNetCreditDetail ="({$f_totdayCridit} - {$tcreidt}) = {$todayCridit}";
        $tooltipYNetCreditDetail ="({$yCridit} - {$ycreidt}) = {$yCreidt}";
        $tooltipCMNetCreditDetail ="({$cMCridit} - {$cMcreidt}) = {$cMCriedt}";
        $tooltipPMNetCreditDetail ="({$privousMCridt} - {$pMcreidt}) = {$privousMnthCredit}";
        
        //$todayCridit = $todayCridit + $credit_to_other_changed['credit'];
    ?>
   
    <?php $cMCriedt = (float)$cMCriedt;$privousMnthCredit = (float)$privousMnthCredit;
    $yCreidt = (float)$yCreidt;
    ?>
    <tr style="color: black">
        <td ><b>Net Credit</b> </td>
        <td><?php //$todayCridit = number_format($todayCridit,2);
        echo $currency.$todayCridit; ?> <a id='todayNetCredit' title='<?=$tooltipTodayNetCreditDetail?>' alt='<?=$tooltipTodayNetCreditDetail?>'>(Detail)</a></td>
        <td><?php //$yCreidt = number_format($yCreidt,2);
        echo $currency.$yCreidt; ?> <a id='yesterdayNetCredit' title='<?=$tooltipYNetCreditDetail?>' alt='<?=$tooltipYNetCreditDetail?>'>(Detail)</a></td>
        <td><?php //$cMCriedt = number_format($cMCriedt,2);
        echo $currency.$cMCriedt; ?><a id='cmNetCredit' title='<?=$tooltipCMNetCreditDetail?>' alt='<?=$tooltipCMNetCreditDetail?>'>(Detail)</a> </td>
        <td><?php //$privousMnthCredit = number_format($privousMnthCredit,2);
        echo $currency.$privousMnthCredit;?> <a id='pmNetCredit' title='<?=$tooltipPMNetCreditDetail?>' alt='<?=$tooltipPMNetCreditDetail?>'>(Detail)</a> </td>
    </tr>
	
	 <?php
        $tbnk = $todaysCNSale['bank_transfer'];
         $tbnk = number_format($tbnk,2);
        $ybnk = $yesterdayCNSale['bank_transfer'];
        $ybnk = number_format($ybnk,2);  $ybnk = number_format($ybnk,2);
        $cMbnk = $currMonthCNSale['bank_transfer'];//$cMbnk = number_format($cMbnk,2);
        $pMbnk = $prevMonthCNSale['bank_transfer']; $pMbnk = number_format($pMbnk,2);
        
		$cMBank = $currentMonthProductPmtDetails['bank_transfer'];
		$privousMBank = $previousMonthProductPmtDetails['bank_transfer'];
		//pr($y_bank_Transfer);die;
        if(preg_match('/'.QUOT_USER_PREFIX.'/',$loggedInUser)){
            $today_bank_transfer_sp = $t_todaysCNSale['bank_transfer']; //$today_bank_transfer_sp = number_format($today_bank_transfer_sp,2);
			$yes_bank_transfer_sp = $t_yesterdayCNSale['bank_transfer'];// $yes_bank_transfer_sp = number_format($yes_bank_transfer_sp,2);
			$cm_bank_transfer_sp = $t_currMonthCNSale['bank_transfer'];//$cm_bank_transfer_sp = number_format($cm_bank_transfer_sp,2);
			$prv_mnth_bank_transfer_sp = $t_prevMonthCNSale['bank_transfer'];
            //$prv_mnth_bank_transfer_sp = number_format($prv_mnth_bank_transfer_sp,2);
            
            
            $today_bank_Transfer = $today_bank_Transfer + $t_today_bank_Transfer-$today_bank_transfer_sp;
            $today_bank_Transfer = number_format($today_bank_Transfer,2);
            $y_bank_Transfer = $y_bank_Transfer + $t_y_bank_Transfer-$yes_bank_transfer_sp;
            $y_bank_Transfer =   number_format($y_bank_Transfer,2);
            $cMBank = $cMBank + $t_cMBank-$cm_bank_transfer_sp;
           // $cMBank = number_format($cMBank,2);
            $privousMBank = $privousMBank + $t_privousMBank-$prv_mnth_bank_transfer_sp;
           // $privousMBank = number_format($privousMBank,2);
        }
        $today_bank_Transfer = $today_bank_Transfer; //+ $prv_bnk;
        $today_bank_Transfer = number_format($today_bank_Transfer,2);
        $todaybnktrs =  $today_bank_Transfer - $todaysCNSale['bank_transfer'];
        $todaybnktrs = number_format($todaybnktrs,2);
        $ybnktrs =  $y_bank_Transfer - $yesterdayCNSale['bank_transfer']; $ybnktrs = number_format($ybnktrs,2);
        $cMbnktrs =  $cMBank - $currMonthCNSale['bank_transfer'];//$cMbnktrs = number_format($cMbnktrs,2);
        $privousMnthbnktrs =  $privousMBank - $prevMonthCNSale['bank_transfer'];
        //$privousMnthbnktrs = number_format($privousMnthbnktrs,2);
        $tooltipTodayNetBnkDetail ="({$today_bank_Transfer} - {$tbnk}) = {$todaybnktrs}";
        $tooltipYNetBnkDetail ="({$y_bank_Transfer} - {$ybnk}) = {$ybnktrs}";
        $tooltipCMNetBnkDetail ="({$cMBank} - {$cMbnk}) = {$cMbnktrs}";
        $tooltipPMNetBnkDetail ="({$privousMBank} - {$pMbnk}) = {$privousMnthbnktrs}";
    
           // $todaybnktrs = $todaybnktrs + $credit_to_other_changed['bank_transfer'];
    ?>
    
    
     <tr style="color: black">
        <td><b>Net Bnk Tnsfer </b></td>
        <td><?php echo $currency.$todaybnktrs; ?><a id='todayNetbnk' title='<?=$tooltipTodayNetBnkDetail?>' alt='<?=$tooltipTodayNetBnkDetail?>'>(Detail)</a> </td>
        <td><?php echo $currency.$ybnktrs; ?> <a id='yesterdayNetbnk' title='<?=$tooltipYNetBnkDetail?>' alt='<?=$tooltipYNetBnkDetail?>'>(Detail)</a></td>
        <td><?php echo $currency.$cMbnktrs; ?><a id='cmNetbnk' title='<?=$tooltipCMNetBnkDetail?>' alt='<?=$tooltipCMNetBnkDetail?>'>(Detail)</a> </td>
        <td><?php echo $currency.$privousMnthbnktrs;?> <a id='pmNetbnk' title='<?=$tooltipPMNetBnkDetail?>' alt='<?=$tooltipPMNetBnkDetail?>'>(Detail)</a></td>
    </tr>
	
	<?php
        $tcheque = $todaysCNSale['cheque']; //$tcheque = number_format($tcheque,2);
        $ycheque = $yesterdayCNSale['cheque']; //$ycheque = number_format($ycheque,2);
        $cMcheque = $currMonthCNSale['cheque']; //$cMcheque = number_format($cMcheque,2);
        $pMcheque = $prevMonthCNSale['cheque']; //$pMcheque = number_format($pMcheque,2);
		
		$todayCheque = $todayProductPmtDetails['cheque']; //$todayCheque = number_format($todayCheque,2);
		$cMCheque = $currentMonthProductPmtDetails['cheque']; //$cMCheque = number_format($cMCheque,2);
		$privousMCheque = $previousMonthProductPmtDetails['cheque'];// $privousMCheque = number_format($privousMCheque,2);
		
        if(preg_match('/'.QUOT_USER_PREFIX.'/',$loggedInUser)){
            $today_cheque_sp = $t_todaysCNSale['cheque']; //$today_cheque_sp = number_format($today_cheque_sp,2);
			$yes_cheque_sp = $t_yesterdayCNSale['cheque']; //$yes_cheque_sp = number_format($yes_cheque_sp,2);
			$cm_cheque_sp = $t_currMonthCNSale['cheque']; //$cm_cheque_sp = number_format($cm_cheque_sp,2);
			$prv_mnth_cheque_sp = $t_prevMonthCNSale['cheque']; //$prv_mnth_cheque_sp = number_format($prv_mnth_cheque_sp,2);
            
            
            $todayCheque = $todayCheque + $t_todayCheque - $today_cheque_sp;
            $yCheque = $yCheque + $t_yCheque - $yes_cheque_sp;
            $cMCheque = $cMCheque + $t_cMCheque - $cm_cheque_sp;
            $privousMCheque = $privousMCheque + $t_privousMCheque - $prv_mnth_cheque_sp;
        }
		
		
		
        $todayCheque = $todayCheque; //+ $prv_cheque;
        $todayChequePmt =  $todayCheque - $todaysCNSale['cheque'];
        $yChequePmt =  $yCheque- $yesterdayCNSale['cheque'];
        $cMChequePmt =  $cMCheque - $currMonthCNSale['cheque'];
        $privousMnthChequePmt =  $privousMCheque - $prevMonthCNSale['cheque'];
    
        $todayCheque = number_format($todayCheque,2);
        $tcheque = number_format($tcheque,2);
        $todayChequePmt = number_format($todayChequePmt,2);
        
        $yCheque = number_format($yCheque,2);
        $ycheque = number_format($ycheque,2);
        $yChequePmt = number_format($yChequePmt,2);
        
        $cMCheque = number_format($cMCheque,2);
        $cMcheque = number_format($cMcheque,2);
       // $cMChequePmt = number_format($cMChequePmt,2);
        
        $privousMCheque = number_format($privousMCheque,2);
        $pMcheque = number_format($pMcheque,2);
       // $privousMnthChequePmt = number_format($privousMnthChequePmt,2);
        $tooltipTodayNetChequeDetail ="({$todayCheque} - {$tcheque}) = {$todayChequePmt}";
        $tooltipYNetChequeDetail ="({$yCheque} - {$ycheque}) = {$yChequePmt}";
        $tooltipCMNetChequeDetail ="({$cMCheque} - {$cMcheque}) = {$cMChequePmt}";
        $tooltipPMNetChequeDetail ="({$privousMCheque} - {$pMcheque}) = {$privousMnthChequePmt}";
        //$todayChequePmt = $todayChequePmt + $credit_to_other_changed['cheque'];
    ?>
     
      <tr style="color: black">
        <td style="color: black"><b>Net Cheque Payment </b></td>
        <td><?php echo $currency.$todayChequePmt; ?> <a id='todayNetCheque' title='<?=$tooltipTodayNetChequeDetail?>' alt='<?=$tooltipTodayNetChequeDetail?>'>(Detail)</a></td>
        <td><?php echo $currency.$yChequePmt; ?> <a id='yesterdayNetCheque' title='<?=$tooltipYNetChequeDetail?>' alt='<?=$tooltipYNetChequeDetail?>'>(Detail)</a></td>
        <td><?php echo $currency.$cMChequePmt; ?> <a id='cmNetCheque' title='<?=$tooltipCMNetChequeDetail?>' alt='<?=$tooltipCMNetChequeDetail?>'>(Detail)</a> </td>
        <td><?php echo $currency.$privousMnthChequePmt;?> <a id='pmNetCheque' title='<?=$tooltipPMNetChequeDetail?>' alt='<?=$tooltipPMNetChequeDetail?>'>(Detail)</a> </td>
    </tr>
	<?php } // user type check brace closing?>
    
    <?php
    if(preg_match('/'.QUOT_USER_PREFIX.'/',$loggedInUser)){
         $end_today_cash = $end_today_cash + $t_cash; //$end_today_cash = number_format($end_today_cash,2);
         $today_cn_cash_sp = $t_todaysCNSale['cash']+ $t_todaysCNSale['cashEntryAmt'];$today_cn_cash_sp = number_format($today_cn_cash_sp,2);
		 $end_today_cash = $end_today_cash-$today_cn_cash_sp;
         //$end_today_cash = number_format($end_today_cash,2);
         $end_y_cash = $end_y_cash + $t_yTotalCash;
         $yes_cn_cash_sp = $t_yesterdayCNSale['cash']+$t_yesterdayCNSale['cashEntryAmt'];
		 $end_y_cash = $end_y_cash-$yes_cn_cash_sp;
         
//         $this_mnth_sale = $this_mnth_sale + $t_cMTotalCash;
        $cm_cn_cash_sp = $t_currMonthCNSale['cash'];
//		 $this_mnth_sale = $this_mnth_sale-$cm_cn_cash_sp;
         
         
        // $privious_mnth_sale = $privious_mnth_sale + $t_priviousMnthCASH;
        $prv_mnth_cn_cash_sp = $t_prevMonthCNSale['cash'];
         $prv_mnth_cn_cash_sp = number_format($prv_mnth_cn_cash_sp,2);
//		 $privious_mnth_sale = $privious_mnth_sale-$prv_mnth_cn_cash_sp;
    }
    $end_today_cash = $end_today_cash + $credit_to_other_changed['cash'];
    //$end_today_cash = number_format($end_today_cash,2);
    $end_y_cash  = $end_y_cash +$y_credit_to_other_changed['cash'];
    $this_mnth_sale = $this_mnth_sale; //+$current_month_credit_to_other_changed['cash'];
    $privious_mnth_sale = $privious_mnth_sale;//+$prv_month_credit_to_other_changed['cash'];
    ?>
    <?php
    
    //spr($t_todaysCNSale);
    //$this->request->session()->read('Auth.User.user_type');
    if($this->request->session()->read('Auth.User.user_type') =='wholesale'){
        if(preg_match('/'.QUOT_USER_PREFIX.'/',$loggedInUser)){
            $end_today_cash = $end_today_cash + $t_credit_to_other_changed['cash'];
            //$end_today_cash = number_format($end_today_cash,2);
            $end_y_cash = $end_y_cash + $t_y_prv_credit_to_card['cash'];
            $this_mnth_sale = $this_mnth_sale; //+ $t_current_month_credit_to_other_changed['cash'];
            $privious_mnth_sale = $privious_mnth_sale;// + $t_prv_month_credit_to_other_changed['cash'];
            
            $credit_to_cash = $credit_to_other_changed['cash'] + $t_credit_to_other_changed['cash']- ($t_todaysCNSale['cashEntryAmt']+$todaysCNSale['cashEntryAmt']);
            $y_credit_to_cash = $y_credit_to_other_changed['cash'] + $t_y_prv_credit_to_card['cash']- ($t_yesterdayCNSale['cashEntryAmt'] + $yesterdayCNSale['cashEntryAmt']) ;
            $cm_credit_to_cash  = $current_month_credit_to_other_changed['cash'] + $t_current_month_credit_to_other_changed['cash']-  $currMonthCNSale['cashEntryAmt'] -$t_currMonthCNSale['cashEntryAmt'];
            $pm_credit_to_cash  = $prv_month_credit_to_other_changed['cash'] + $t_prv_month_credit_to_other_changed['cash']- $prevMonthCNSale['cashEntryAmt'] - $t_prevMonthCNSale['cashEntryAmt'];    
            
        }else{
            $credit_to_cash = $credit_to_other_changed['cash'] - $todaysCNSale['cashEntryAmt'];
            $y_credit_to_cash = $y_credit_to_other_changed['cash'] - $yesterdayCNSale['cashEntryAmt'] ;
            $cm_credit_to_cash  = $current_month_credit_to_other_changed['cash']- $currMonthCNSale['cashEntryAmt'] ;
            $pm_credit_to_cash  = $prv_month_credit_to_other_changed['cash']- $prevMonthCNSale['cashEntryAmt'] ;
        }
					
	}else{
        $credit_to_cash = $credit_to_other_changed['cash'];
        $y_credit_to_cash = $y_credit_to_other_changed['cash'];
        $cm_credit_to_cash  = $current_month_credit_to_other_changed['cash'];
        $pm_credit_to_cash  = $prv_month_credit_to_other_changed['cash'];
	}
        if($end_y_cash<0){
            $end_y_cash_new = (-1)*$end_y_cash;
            $end_y_cash_new = '-'.$end_y_cash_new;
        }else{
            $end_y_cash_new = $end_y_cash;
        }
    ?>
    <?php
    //$credit_to_cash = number_format($credit_to_cash,2);
    
    $prv_cash = $credit_to_other_changed['cash']; $prv_cash = number_format($prv_cash,2);
    $prv_bnk = $credit_to_other_changed['bank_transfer'];$prv_bnk = number_format($prv_bnk,2);
    $prv_credit = $credit_to_other_changed['credit']; $prv_credit = number_format($prv_credit,2);
    $prv_card = $credit_to_other_changed['card'];$prv_card = number_format($prv_card,2);
    $prv_cheque = $credit_to_other_changed['cheque'];$prv_cheque = number_format($prv_cheque,2);
    $pCash = number_format($pCash,2);
    $pYCash = number_format($pYCash,2);
 
             if(preg_match('/'.QUOT_USER_PREFIX.'/',$loggedInUser)){
                    $prv_crdit_to_cash = $t_credit_to_other_changed['cash'];
                    $total_cash = $total_cash + $prv_crdit_to_cash + $prv_cash;
                    //$prv_crdit_to_cash = number_format($prv_crdit_to_cash,2);
                     //$prv_crdit_to_cash = number_format($prv_crdit_to_cash,2);
                       // $total_cash = number_format($total_cash,2);
                        $tooltipTodayNetCashDetail = "(Repair({$todaysRcashPayment})+Unlock({$todaysUcashPayment})+Product({$pCash})+Blk({$todaysBlkMcashPayment})+Mobile({$todaysMcashPayment})+special({$t_cash})+prv_recpit_amt({$prv_cash})+ prv_credit_to_cash({$prv_crdit_to_cash}))=  $total_cash";
                }else{
                    if(array_key_exists('cashEntryAmt',$credit_to_other_changed)){
                        $total_cash = $total_cash + $credit_to_other_changed['cashEntryAmt'];
                        //$total_cash = number_format($total_cash,2);
                    }
                    
                    $tooltipTodayNetCashDetail = "(Repair({$todaysRcashPayment})+Unlock({$todaysUcashPayment})+Product({$pCash})+Blk({$todaysBlkMcashPayment})+Mobile({$todaysMcashPayment})+ prv_recpit_amt({$prv_cash}))= $total_cash";
                }
                $todayCNSaleCash = $todaysCNSale['cash']+$prv_cash;
                $todayCNSaleCash = number_format($todayCNSaleCash,2);
                if(preg_match('/'.QUOT_USER_PREFIX.'/',$loggedInUser)){
                     $total_refund = number_format($total_refund,2);
                    $total_refund = $total_refund + $today_cn_cash_sp;
                    $total_refund = number_format($total_refund,2);
                     
                    $tooltipTodayNetCashDetail.=", Refund(Repair({$todaysRefund})+Unlock({$todaysUrefund})+Product({$todayProductRefund})+Mobile({$todayMobileRefund})+Blk({$todayBlkMobileRefund})+Credit Note({$todayCNSaleCash})+Mobile Purchase({$todayMobilePurchase})+special credit note({$today_cn_cash_sp}))= ({$total_cash} - {$total_refund}) = {$end_today_cash}";
                }else{
                    $tooltipTodayNetCashDetail.=", Refund(Repair({$todaysRefund})+Unlock({$todaysUrefund})+Product({$todayProductRefund})+Mobile({$todayMobileRefund})+Blk({$todayBlkMobileRefund})+Credit Note({$todayCNSaleCash})+Mobile Purchase({$todayMobilePurchase}))=({$total_cash} - {$total_refund}) = {$end_today_cash}";
                }
                
                
                // yes
                
                if(preg_match('/'.QUOT_USER_PREFIX.'/',$loggedInUser)){
                    $special_changed_cash = $t_y_prv_credit_to_card['cash'];
                    $yCash = $yCash+ $special_changed_cash;
                    $yCash = $yCash + $t_yTotalCash;
                    //$yCash = number_format($yCash,2);
                        $tooltipYesterdayNetCashDetail = "(Repair({$yesterdaysRcashPayment})+Unlock({$yesterdaysUcashPayment})+Product({$pYCash})+Blk({$yesterdaysBlkMcashPayment})+Mobile({$yesterdaysMcashPayment})+special({$t_yTotalCash}) + prv_quotation_cash({$special_changed_cash})) = $yCash ";
                }else{
                    $tooltipYesterdayNetCashDetail = "(Repair({$yesterdaysRcashPayment})+Unlock({$yesterdaysUcashPayment})+Product({$pYCash})+Blk({$yesterdaysBlkMcashPayment})+Mobile({$yesterdaysMcashPayment}))= $yCash ";
                }
                $yesterdayCNSaleCash = $yesterdayCNSale['cash'] + $yesterdayCNSale['cashEntryAmt'];
                 $yesterdayCNSaleCash = number_format($yesterdayCNSaleCash,2);
                if(preg_match('/'.QUOT_USER_PREFIX.'/',$loggedInUser)){
                    $special_entry_yes = $t_yesterdayCNSale['cashEntryAmt'];
                    $yRefund = $yRefund + $t_yesterdayCNSale['cashEntryAmt'];
                  //  $yRefund = $yRefund + $t_yTotalCash;
                     $yRefund = number_format($yRefund,2);
                     $special_credit_note = $t_yesterdayCNSale['cash'];
                  //  $end_y_cash = number_format($end_y_cash,2);
                    $tooltipYesterdayNetCashDetail.="Refund(Repair({$yesterdaysRefund})+Unlock({$f_yesterdaysUrefund})+Product({$yestdayProductRefund})+Mobile({$yesterdayMobileRefund})+Blk({$yesterdayBlkMobileRefund})+Credit Note({$yesterdayCNSaleCash})+Mobile Purchase({$yesterdayMobilePurchase})+special credit note({$special_credit_note}) + credit_to_cash_credit_note({$special_entry_yes}))= ({$yCash} - {$yRefund}) = {$end_y_cash}";
                }else{
                   // $end_y_cash = number_format($end_y_cash,2);
                    $tooltipYesterdayNetCashDetail.="Refund(Repair({$yesterdaysRefund})+Unlock({$f_yesterdaysUrefund})+Product({$yestdayProductRefund})+Mobile({$yesterdayMobileRefund})+Blk({$yesterdayBlkMobileRefund})+Credit Note({$yesterdayCNSaleCash})+Mobile Purchase({$yesterdayMobilePurchase}))= ({$yCash} - {$yRefund}) = {$end_y_cash}";
                }
                
                //cm
                $cmProdCash = $currentMonthProductPmtDetails['cash'] + $thisMnthCash;
                   $cmProdCash = number_format($cmProdCash,2);
                $pmProdCash = $previousMonthProductPmtDetails['cash'] + $prvMnthCash;
                    $pmProdCash = number_format($pmProdCash,2);
                    $this_mnth_cash = (float)$this_mnth_cash;
                   // $this_mnth_cash = number_format($this_mnth_cash,2);
                    
                    //$this_mnth_sale = number_format($this_mnth_sale,2);
                 if(preg_match('/'.QUOT_USER_PREFIX.'/',$loggedInUser)){
                    // $special_changed_cash_cm = $t_current_month_credit_to_other_changed['cash'];
                    //$this_mnth_cash = $this_mnth_cash+ $special_changed_cash_cm;
                    //$normal_product_prv_cash = $current_month_credit_to_other_changed['cash'];
                    //$this_mnth_cash = $this_mnth_cash + $normal_product_prv_cash;
                        $tooltipCMNetCashDetail = "(Repair({$currentMonthRcashPayment})+Unlock({$currentMonthUcashPayment})+Product({$cmProdCash})+Blk({$currentMonthBlkMcashPayment})+Mobile({$currentMonthMcashPayment})+special({$t_cMTotalCash}))= $this_mnth_cash";
                    }else{
                        $tooltipCMNetCashDetail = "(Repair({$currentMonthRcashPayment})+Unlock({$currentMonthUcashPayment})+Product({$cmProdCash})+Blk({$currentMonthBlkMcashPayment})+Mobile({$currentMonthMcashPayment}))= $this_mnth_cash ";
                    }
                    
                    $cmCNSaleCash = $currMonthCNSale['cash'] + $currMonthCNSale['cashEntryAmt'];
                    $cmCNSaleCash = number_format($cmCNSaleCash,2);
                    if(preg_match('/'.QUOT_USER_PREFIX.'/',$loggedInUser)){
                        //$special_entry_cm = $currMonthCNSale['cashEntryAmt'];
                        //$this_mnth_refund = $this_mnth_refund + $special_entry_cm;
                        //$this_mnth_sale = $this_mnth_sale - $special_entry_cm;
                        $cm_cn_cash_sp = $cm_cn_cash_sp + $t_currMonthCNSale['cashEntryAmt'];
                        $tooltipCMNetCashDetail.="Refund(Repair({$f_currentMonthRepairRefund})+Unlock({$f_currentMonthUnlockRefund})+Product({$currentMonthProductRefund})+Mobile({$currentMonthMobileRefund})+Blk({$currentMonthBlkMobileRefund})+Credit Note({$cmCNSaleCash})+Mobile Purchase({$currentMonthMobilePurchase})+special credit note({$cm_cn_cash_sp}))= ({$this_mnth_cash} - {$this_mnth_refund}) = {$this_mnth_sale}";
                    }else{
                        //$special_entry_cm = $currMonthCNSale['cashEntryAmt'];
                        //$this_mnth_refund = $this_mnth_refund + $special_entry_cm;
                        //$this_mnth_sale = $this_mnth_sale - $special_entry_cm;
                        $tooltipCMNetCashDetail.="Refund(Repair({$f_currentMonthRepairRefund})+Unlock({$f_currentMonthUnlockRefund})+Product({$currentMonthProductRefund})+Mobile({$currentMonthMobileRefund})+Blk({$currentMonthBlkMobileRefund})+Credit Note({$cmCNSaleCash})+Mobile Purchase({$currentMonthMobilePurchase}))= ({$this_mnth_cash} - {$this_mnth_refund}) = {$this_mnth_sale}";
                    }
                    
                   // $privious_mnth_sale = number_format($privious_mnth_sale,2);
                   // $privous_mnth_cash = number_format($privous_mnth_cash,2);
                    if(preg_match('/'.QUOT_USER_PREFIX.'/',$loggedInUser)){
                        
                        $privous_mnth_cash = $privous_mnth_cash + $t_priviousMnthCASH;
                        $privious_mnth_sale = $privious_mnth_sale + $t_priviousMnthCASH;
                        $tooltipPMNetCashDetail = "(Repair({$previousMonthRcashPayment})+Unlock({$previousMonthUcashPayment})+Product({$pmProdCash})+Blk({$previousMonthBlkMobileRefund})+Mobile({$previousMonthMcashPayment})+special({$t_priviousMnthCASH}))= $privous_mnth_cash";
                        }else{
                            $tooltipPMNetCashDetail = "(Repair({$previousMonthRcashPayment})+Unlock({$previousMonthUcashPayment})+Product({$pmProdCash})+Blk({$previousMonthBlkMobileRefund})+Mobile({$previousMonthMcashPayment}))= $privous_mnth_cash";
                        }
                        $pmCNSaleCash = $prevMonthCNSale['cash'] + $prevMonthCNSale['cashEntryAmt'];
                         $pmCNSaleCash = number_format($pmCNSaleCash,2);
                        if(preg_match('/'.QUOT_USER_PREFIX.'/',$loggedInUser)){
                            $prv_mnth_cn_cash_sp = (int)$prv_mnth_cn_cash_sp + (int)$t_prevMonthCNSale['cashEntryAmt'];
                            $privious_mnth_refund = $privious_mnth_refund + $prv_mnth_cn_cash_sp;
                            $t_priviousMnthCASH = $t_priviousMnthCASH - $prv_mnth_cn_cash_sp;
                            $prv_mnth_cn_cash_sp = number_format($prv_mnth_cn_cash_sp,2);
                            $privious_mnth_sale = $privous_mnth_cash - $privious_mnth_refund;
                            $tooltipPMNetCashDetail.="Refund(Repair({$f_previousMonthRepairRefund})+Unlock({$f_previousMonthUnlockRefund})+Product({$previousMonthProductRefund})+Mobile({$previousMonthMobileRefund})+Blk({$previousMonthBlkMobileRefund})+Credit Note({$pmCNSaleCash})+Mobile Purchase({$previousMonthMobilePurchase})- special credit note({$prv_mnth_cn_cash_sp}))= ({$privous_mnth_cash} - {$privious_mnth_refund}) = {$privious_mnth_sale}";
                        }else{
                            $tooltipPMNetCashDetail.="Refund(Repair({$previousMonthRepairRefund})+Unlock({$f_previousMonthUnlockRefund})+Product({$previousMonthProductRefund})+Mobile({$previousMonthMobileRefund})+Blk({$previousMonthBlkMobileRefund})+Credit Note({$pmCNSaleCash})+Mobile Purchase({$previousMonthMobilePurchase}))=({$privous_mnth_cash} - {$privious_mnth_refund}) = {$privious_mnth_sale}";
                        }
                    

        ?>
    <?php $privious_mnth_sale = (float)$privious_mnth_sale;
    $this_mnth_sale = (float)$this_mnth_sale;
    ?>
    <tr>
        <td><strong>Cash in hand</strong></td>
        <td><?php echo $currency.number_format($end_today_cash,2);?><a id='todayNetCash' title='<?=$tooltipTodayNetCashDetail?>' alt='<?=$tooltipTodayNetCashDetail?>'>(Detail)</a></td>
        <td><?php echo $currency.number_format($end_y_cash_new,2);?><a id='yesterdayNetCash' title='<?=$tooltipYesterdayNetCashDetail?>' alt='<?=$tooltipYesterdayNetCashDetail?>'>(Detail)</a></td>
        <td><?php echo $currency.number_format($this_mnth_sale,2);?><a id='cmNetCash' title='<?=$tooltipCMNetCashDetail?>' alt='<?=$tooltipCMNetCashDetail?>'>(Detail)</a></td>
        <td><?php echo $currency.number_format($privious_mnth_sale,2);?><a id='pmNetCash' title='<?=$tooltipPMNetCashDetail?>' alt='<?=$tooltipPMNetCashDetail?>'>(Detail)</a></td>
    </tr>
    <?php
    $t_prv_cash = $t_credit_to_other_changed['cash'];  $t_prv_cash = number_format($t_prv_cash,2);
    $t_prv_bnk = $t_credit_to_other_changed['bank_transfer']; $t_prv_bnk = number_format($t_prv_bnk,2);
    $t_prv_credit = $t_credit_to_other_changed['credit'];$t_prv_credit = number_format($t_prv_credit,2);
    $t_prv_card = $t_credit_to_other_changed['card']; $t_prv_card = number_format($t_prv_card,2);    
    $t_prv_cheque = $t_credit_to_other_changed['cheque'];$t_prv_cheque = number_format($t_prv_cheque,2);
    
    $prev_rect_sale = $credit_to_other_changed['cash']; $prev_rect_sale = number_format($prev_rect_sale,2);
    //+$credit_to_other_changed['card']+$credit_to_other_changed['credit']+$credit_to_other_changed['bank_transfer']+$credit_to_other_changed['cheque'];
    $t_prv_recipt_sale = $t_credit_to_other_changed['cash'];// $t_prv_recipt_sale = number_format($t_prv_recipt_sale,2);//+$t_credit_to_other_changed['card']+$t_credit_to_other_changed['credit']+$t_credit_to_other_changed['bank_transfer']+$t_credit_to_other_changed['cheque'];
     if(preg_match('/'.QUOT_USER_PREFIX.'/',$loggedInUser)){
		 $total_prv_sale = $prev_rect_sale + $t_prv_recipt_sale;
	 }else{
		$total_prv_sale = $prev_rect_sale;
	 }
      $total_prv_sale = number_format($total_prv_sale,2);
     $y_prev_rect_sale = $y_credit_to_other_changed['cash']; $y_prev_rect_sale = number_format($y_prev_rect_sale,2);//+$y_credit_to_other_changed['card']+$y_credit_to_other_changed['credit']+$y_credit_to_other_changed['bank_transfer']+$y_credit_to_other_changed['cheque'];
	$y_prv_cash = $y_credit_to_other_changed['cash']; $y_prv_cash = number_format($y_prv_cash,2);
	$y_prv_bnk = $y_credit_to_other_changed['card'];$y_prv_bnk = number_format($y_prv_bnk,2);
	$y_prv_credit = $y_credit_to_other_changed['credit'];$y_prv_credit = number_format($y_prv_credit,2);
	$y_prv_card = $y_credit_to_other_changed['bank_transfer'];$y_prv_card = number_format($y_prv_card,2);
	$y_prv_cheque = $y_credit_to_other_changed['cheque'];$y_prv_cheque = number_format($y_prv_cheque,2);
    
    $credit_note_entry = $todaysCNSale['cashEntryAmt']; $credit_note_entry = number_format($credit_note_entry,2);
    if(preg_match('/'.QUOT_USER_PREFIX.'/',$loggedInUser)){
        $credit_quotataion_entry = $t_todaysCNSale['cashEntryAmt'];$credit_quotataion_entry = number_format($credit_quotataion_entry,2);
        $total_prv_sale = (float)$total_prv_sale - ($credit_note_entry + $credit_quotataion_entry);
        $total_prv_sale = number_format($total_prv_sale, 2);
		$tooltipPrvRcitDetail = "(invoice cash({$prv_cash}) + Quotation cash({$t_prv_cash}) - credit cash({$credit_note_entry}) -  credit quotation cash({$credit_quotataion_entry})) = {$total_prv_sale}";
	 }else{
        $total_prv_sale = $total_prv_sale - ($credit_note_entry);$total_prv_sale = number_format($total_prv_sale,2);
		$tooltipPrvRcitDetail = "(invoice cash({$prv_cash}) - credit cash({$credit_note_entry})) = {$total_prv_sale}";
	 }
     
     $this_mnth_prev_rect_sale = $current_month_credit_to_other_changed['cash']; $this_mnth_prev_rect_sale = number_format($this_mnth_prev_rect_sale,2);//+$current_month_credit_to_other_changed['card']+$current_month_credit_to_other_changed['credit']+$current_month_credit_to_other_changed['bank_transfer']+$current_month_credit_to_other_changed['cheque'];
	$this_mnth_prv_cash = $current_month_credit_to_other_changed['cash']; $this_mnth_prv_cash = number_format($this_mnth_prv_cash,2);
	$this_mnth_prv_bnk = $current_month_credit_to_other_changed['card'];$this_mnth_prv_bnk = number_format($this_mnth_prv_bnk,2);
	$this_mnth_prv_credit = $current_month_credit_to_other_changed['credit'];$this_mnth_prv_credit = number_format($this_mnth_prv_credit,2);
	$this_mnth_prv_card = $current_month_credit_to_other_changed['bank_transfer'];$this_mnth_prv_card = number_format($this_mnth_prv_card,2);
	$this_mnth_prv_cheque = $current_month_credit_to_other_changed['cheque'];$this_mnth_prv_cheque = number_format($this_mnth_prv_cheque,2);
    
     $credit_note_entry = $yesterdayCNSale['cashEntryAmt'];   $credit_note_entry = number_format($credit_note_entry,2);
      if(preg_match('/'.QUOT_USER_PREFIX.'/',$loggedInUser)){
         $credit_quotataion_entry = $t_yesterdayCNSale['cashEntryAmt'];$credit_quotataion_entry = number_format($credit_quotataion_entry,2);
        $t_y_cash = $t_y_prv_credit_to_card['cash'];
        //$t_y_cash =  number_format($t_y_cash,2);
        $y_prev_rect_sale = $y_prev_rect_sale + $t_y_cash;
        $y_prev_rect_sale = $y_prev_rect_sale - ($credit_note_entry+$credit_quotataion_entry);
        //$y_prev_rect_sale =  number_format($y_prev_rect_sale,2);
            $tooltipYPrvRcitDetail = "(invoice cash({$y_prv_cash}) + Quotation cash({$t_y_cash}) - credit cash({$credit_note_entry}) -  credit quotation cash({$credit_quotataion_entry})) = {$y_prev_rect_sale}";   
      }else{
         $y_prev_rect_sale = $y_prev_rect_sale - ($credit_note_entry);
         //$y_prev_rect_sale =  number_format($y_prev_rect_sale,2);
            $tooltipYPrvRcitDetail = "(invoice cash({$y_prv_cash}) - credit cash({$credit_note_entry}))= {$y_prev_rect_sale}";    
      }
     
      $credit_note_entry = $currMonthCNSale['cashEntryAmt'];
  //  $credit_note_entry = number_format($credit_note_entry,2);
     
    if(preg_match('/'.QUOT_USER_PREFIX.'/',$loggedInUser)){
        $credit_quotataion_entry = $t_currMonthCNSale['cashEntryAmt']; //$credit_quotataion_entry = number_format($credit_quotataion_entry,2);
     $t_cm_cash = $t_current_month_credit_to_other_changed['cash']; //$t_cm_cash = number_format($t_cm_cash,2);
        $this_mnth_prev_rect_sale = $this_mnth_prev_rect_sale+$t_cm_cash - ($credit_note_entry+$credit_quotataion_entry); //$this_mnth_prev_rect_sale = number_format($this_mnth_prev_rect_sale,2);
        $tooltipCMPrvRcitDetail = "(invoice cash({$this_mnth_prv_cash}) + Quotation cash({$t_cm_cash}) - credit cash({$credit_note_entry}) -  credit quotation cash({$credit_quotataion_entry})) = {$this_mnth_prev_rect_sale}";
    }else{
        $this_mnth_prev_rect_sale = $this_mnth_prev_rect_sale - ($credit_note_entry);
        $tooltipCMPrvRcitDetail = "(invoice cash({$this_mnth_prv_cash}) - credit cash({$credit_note_entry})) = {$this_mnth_prev_rect_sale}";
    }
        
        
        
        $prv_mnth_prev_rect_sale = $prv_month_credit_to_other_changed['cash']; //$prv_mnth_prev_rect_sale = number_format($prv_mnth_prev_rect_sale,2);  //+$prv_month_credit_to_other_changed['card']+$prv_month_credit_to_other_changed['credit']+$prv_month_credit_to_other_changed['bank_transfer']+$prv_month_credit_to_other_changed['cheque'];
	$prv_mnth_prv_cash = $prv_month_credit_to_other_changed['cash']; //$prv_mnth_prv_cash = number_format($prv_mnth_prv_cash,2);
	$prv_mnth_prv_bnk = $prv_month_credit_to_other_changed['card'];$prv_mnth_prv_bnk = number_format($prv_mnth_prv_bnk,2);
	$prv_mnth_prv_credit = $prv_month_credit_to_other_changed['credit'];$prv_mnth_prv_credit = number_format($prv_mnth_prv_credit,2);
	$prv_mnth_prv_card = $prv_month_credit_to_other_changed['bank_transfer'];$prv_mnth_prv_card = number_format($prv_mnth_prv_card,2);
	$prv_mnth_prv_cheque = $prv_month_credit_to_other_changed['cheque'];$prv_mnth_prv_cheque = number_format($prv_mnth_prv_cheque,2);
    
     $credit_note_entry = $prevMonthCNSale['cashEntryAmt'];
    // $credit_note_entry = number_format($credit_note_entry,2); 
    
    if(preg_match('/'.QUOT_USER_PREFIX.'/',$loggedInUser)){
        $credit_quotataion_entry = $t_prevMonthCNSale['cashEntryAmt']; //$credit_quotataion_entry = number_format($credit_quotataion_entry,2); 
    $t_prv_mnth_cash = $t_prv_month_credit_to_other_changed['cash'];//$t_prv_mnth_cash = number_format($t_prv_mnth_cash,2); 
        $prv_mnth_prev_rect_sale = $prv_mnth_prev_rect_sale + $t_prv_mnth_cash - ($credit_note_entry+$credit_quotataion_entry);
       // $prv_mnth_prev_rect_sale = number_format($prv_mnth_prev_rect_sale,2); 
        $tooltipPrvMPrvRcitDetail = "(invoice cash({$prv_mnth_prv_cash}) + Quotation cash({$t_prv_mnth_cash}) - credit cash({$credit_note_entry}) -  credit quotation cash({$credit_quotataion_entry})) = {$prv_mnth_prev_rect_sale}";
    }else{
        $prv_mnth_prev_rect_sale = $prv_mnth_prev_rect_sale - ($credit_note_entry);
        $tooltipPrvMPrvRcitDetail = "(invoice cash({$prv_mnth_prv_cash}) - credit cash({$credit_note_entry}))";
    }
        
    ?>
    <tr>
    <td><b>credit to cash(Prvs Payments)</b></td>
    <?php //echo "Cash : ".$credit_to_other_changed['cash'];?>
    <td><?php  $credit_to_cash = number_format($credit_to_cash,2);
    echo "Cash : ".$credit_to_cash;?><a id='prev_rect_sale' title='<?=$tooltipPrvRcitDetail?>' alt='<?=$tooltipPrvRcitDetail?>'>(Detail)</a></td>
	<td><?php  $y_credit_to_cash = number_format($y_credit_to_cash,2);
    echo "Cash : ".$y_credit_to_cash;?><a id='prev_rect_sale' title='<?=$tooltipYPrvRcitDetail?>' alt='<?=$tooltipYPrvRcitDetail?>'>(Detail)</a></td>
	<td><?php  $cm_credit_to_cash = number_format($cm_credit_to_cash,2);
    echo "Cash : ".$cm_credit_to_cash;?><a id='prev_rect_sale' title='<?=$tooltipCMPrvRcitDetail?>' alt='<?=$tooltipCMPrvRcitDetail?>'>(Detail)</a></td>
	<td><?php  $pm_credit_to_cash = number_format($pm_credit_to_cash,2);
    echo "Cash : ".$pm_credit_to_cash ;?> <a id='prev_rect_sale' title='<?=$tooltipPrvMPrvRcitDetail?>' alt='<?=$tooltipPrvMPrvRcitDetail?>'>(Detail)</a></td>
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
        // $bnk_trnsfer_total = number_format($bnk_trnsfer_total,2);
    }else{
        $bnk_trnsfer_total = $today_product_sale_changed_to_bank  - ($today_credit_sale_changed_to_bank); //$bnk_trnsfer_total = number_format($bnk_trnsfer_total,2);
    }
    
    
    // card
    
    $today_product_sale_changed_to_card = $credit_to_other_changed['card'];
    $today_credit_sale_changed_to_card = $today_credit_to_other_changes_CN['card'];
    if(preg_match('/'.QUOT_USER_PREFIX.'/',$loggedInUser)){
        $today_product_quotation_sale_changed_to_card = $t_credit_to_other_changed['card'];
        $today_credit_quotation_changed_to_card = $t_today_credit_to_other_changes_CN['card'];    
        $card_trnsfer_total = $today_product_sale_changed_to_card + $today_product_quotation_sale_changed_to_card - ($today_credit_sale_changed_to_card + $today_credit_quotation_changed_to_card);
         //$card_trnsfer_total = number_format($card_trnsfer_total,2);
    }else{
        $card_trnsfer_total = $today_product_sale_changed_to_card - ($today_credit_sale_changed_to_card);     //$card_trnsfer_total = number_format($card_trnsfer_total,2);
    }
    
   
    // cheque
    
    $today_product_sale_changed_to_cheque = $credit_to_other_changed['cheque'];
    $today_credit_sale_changed_to_cheque = $today_credit_to_other_changes_CN['cheque'];
     if(preg_match('/'.QUOT_USER_PREFIX.'/',$loggedInUser)){
            $today_product_quotation_sale_changed_to_cheque = $t_credit_to_other_changed['cheque'];
            $today_credit_quotation_changed_to_cheque = $t_today_credit_to_other_changes_CN['cheque'];
            $cheque_trnsfer_total = $today_product_sale_changed_to_cheque + $today_product_quotation_sale_changed_to_cheque - ($today_credit_sale_changed_to_cheque + $today_credit_quotation_changed_to_cheque);
            //$cheque_trnsfer_total = number_format($cheque_trnsfer_total,2);
     }else{
            $cheque_trnsfer_total = $today_product_sale_changed_to_cheque  - ($today_credit_sale_changed_to_cheque);
            //$cheque_trnsfer_total = number_format($cheque_trnsfer_total,2);
     }
    
   

  //total    
    $credit_to_other_pay = $bnk_trnsfer_total + $card_trnsfer_total + $cheque_trnsfer_total; //$credit_to_other_pay = number_format($credit_to_other_pay,2);
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
    //$y_bnk_trnsfer_total = number_format($y_bnk_trnsfer_total,2);
  //$y_card_trnsfer_total = number_format($y_card_trnsfer_total,2);
  //$y_cheque_trnsfer_total = number_format($y_cheque_trnsfer_total,2);
  
    $y_credit_to_other_pay = $y_bnk_trnsfer_total + $y_card_trnsfer_total + $y_cheque_trnsfer_total;
    // $y_credit_to_other_pay = number_format($y_credit_to_other_pay,2);
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
     $cm_bnk_trnsfer_total = number_format($cm_bnk_trnsfer_total,2);
     $cm_card_trnsfer_total = number_format($cm_card_trnsfer_total,2);
    $cm_cheque_trnsfer_total = number_format($cm_cheque_trnsfer_total,2);
   // $cm_credit_to_other_pay = number_format($cm_credit_to_other_pay,2);
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
     $pm_bnk_trnsfer_total = number_format($pm_bnk_trnsfer_total,2);
  $pm_card_trnsfer_total = number_format($pm_card_trnsfer_total,2);
  $pm_cheque_trnsfer_total = number_format($pm_cheque_trnsfer_total,2);
   // $pm_credit_to_other_pay = number_format($pm_credit_to_other_pay,2);
    $tooltipPrvMOtherPrvRcitDetail = "(total bnk trsfer = {$pm_bnk_trnsfer_total} + total card transfer = {$pm_card_trnsfer_total} + total cheque transfer = {$pm_cheque_trnsfer_total} , total{$pm_credit_to_other_pay})";
    
    ?>
    <?php $credit_to_other_pay = (float)$credit_to_other_pay;
    $cm_credit_to_other_pay = (float)$cm_credit_to_other_pay;
    $pm_credit_to_other_pay = (float)$pm_credit_to_other_pay;
    ?>
     <tr>
    <td><b>credit to other payment(Prvs Payments)</b></td>
    <?php //echo "Cash : ".$credit_to_other_changed['cash'];?>
    <td><?php $credit_to_other_pay = number_format($credit_to_other_pay,2);echo "".$credit_to_other_pay;?><a id='prev_rect_sale' title='<?=$tooltipPrvRcitotherDetail?>' alt='<?=$tooltipPrvRcitotherDetail?>'>(Detail)</a></td>
	<td><?php $y_credit_to_other_pay = number_format($y_credit_to_other_pay,2);echo "".$y_credit_to_other_pay;?><a id='prev_rect_sale' title='<?=$tooltipYPrvOtherRcitDetail?>' alt='<?=$tooltipYPrvOtherRcitDetail?>'>(Detail)</a></td>
	<td><?php $cm_credit_to_other_pay = number_format($cm_credit_to_other_pay,2);echo "".$cm_credit_to_other_pay;?><a id='prev_rect_sale' title='<?=$tooltipCMOtherPrvRcitDetail?>' alt='<?=$tooltipCMOtherPrvRcitDetail?>'>(Detail)</a></td>
	<td><?php $pm_credit_to_other_pay = number_format($pm_credit_to_other_pay,2) ;echo "".$pm_credit_to_other_pay ;?> <a id='prev_rect_sale' title='<?=$tooltipPrvMOtherPrvRcitDetail?>' alt='<?=$tooltipPrvMOtherPrvRcitDetail?>'>(Detail)</a></td>
   </tr>
     
</table>
<script>
 
</script>