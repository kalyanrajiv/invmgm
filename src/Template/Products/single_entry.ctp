<?php
use Cake\Core\Configure;
use Cake\Core\Configure\Engine\PhpConfig;
$currency = '';//Configure::read('CURRENCY_TYPE');
$selectedKiosk = 10000;
$selectedUser = 0;
$skip_date_arr1 = $date_arr = array();

    if(!empty($this->request->data['date'])){
       $selectedDate = $this->request->data['date'];
   }else{
       $selectedDate = date('d M Y');
   }
   
   if(!empty($this->request->data['end_date'])){
       $selectedEndDate = $this->request->data['end_date'];
   }else{
       $selectedEndDate = date('d M Y');
   }
if(!empty($dashboardData)){
    foreach($dashboardData as $key1 => $value1){
        if($key1 == 0){
            $date_arr[] = $value1['date'];    
        }else{
            if($key1%2 == 0){
                $date_arr[] = $value1['date'];    
            }
        }
        
    }
}
echo $this->Form->create('KioskTotalSale',array('id'=>'KioskTodaySaleDashboardForm','url'=>array('controller'=>'products','action'=>'dashboardData')));
//pr($dashboardData);die;
?>
<table width="100%">
    <tr>
        <td>
            <?php
            if(!empty($this->request->data['date'])){
                $selectedDate = $this->request->data['date'];
            }else{
                $selectedDate = date('d M Y');
            }
            
            if(!empty($this->request->data['end_date'])){
                $selectedEndDate = $this->request->data['end_date'];
            }else{
                $selectedEndDate = date('d M Y');
            }
            ?>
            <input type="text" name="date" id="datepicker1" placeholder="start date" style="width: 80px;margin-top: 30px;" value="<?=$selectedDate;?>">
            <input type="text" name="end_date" id="datepicker2" placeholder="end date" style="width: 80px;margin-top: 30px;" value="<?=$selectedEndDate;?>">
            <input type="submit" name="submit1" id="submit1" value="Submit">
            <input type="button" name="reset" id="reset" value="Reset" style="margin-left: 97px;width: 68px;">
        </td>
        <td><?php echo $this->Form->input('kiosk',array('options'=>$kiosks,'id'=>'TodaySaleKiosk','default'=>$selectedKiosk))?></td>
        <td><?php echo $this->Form->input('user',array('options'=>$users,'id'=>'TodaySaleUser','default'=>$selectedUser))?></td>
        <td>
    </tr>
</table>
<b>**highlighted sssRows are Special user data.</b>
<?php 
    if(count($dashboardData)<=8){
        ?>
        <table><tr>
<th>&nbsp;</th>
<?php
$totalRecords = count($dashboardData)/2;
for($i=0;$i<$totalRecords;$i++){
    if(!array_key_exists($i,$date_arr)){
        continue;
    }
    if($i == 0){
        $date_toShow = date("d-m-Y",strtotime($date_arr[$i]));    
    }else{
        $date_toShow = date("d-m-Y",strtotime($date_arr[$i]));
    }
?>
<th style="width: 436px;">(<?=$date_toShow;?>)</th>
<th style="width: 436px;">(<?=$date_toShow;?>)</th>
<?php
}
?>
</tr><tr>
<td style="width: 11%;">
     <table style="float: left;width: 100%;">
        
        <tr style="height: 96px;">
            <td><strong>Repair Sale</strong></td>
        </tr>
        <tr style="color: blue;height: 37px;">
            <td><strong>Repair Refund</strong></td>
        </tr>
        <tr style="height: 82px;">
            <td><strong>Unlock Sale</strong></td>
        </tr>
        <tr style="color: blue;height: 37px;">
            <td><strong>Unlock Refund</strong></td>
        </tr>
        <tr style="height: 110px;">
            <td><strong>Product Sale</strong></td>
        </tr>
        <tr style="height: 110px;">
            <td><strong>Quotation</strong></td>
        </tr>
        <tr style="color: blue;height: 110px;">
            <td><strong>Credit Note</strong></td>
        </tr>
        <tr style="height: 110px;">
            <td><strong>Credit Quotation</strong></td>
        </tr>
        <tr style="color: blue;height: 37px;">
            <td><strong>Product Refund</strong></td>
        </tr>
        <tr style="height: 88px;">
            <td><strong>Bulk Mobile Sale</strong></td>
        </tr>
        <tr style="color: blue;height: 37px;">
            <td><strong>Bulk Mobile Refund</strong></td>
        </tr>
        <tr style="height: 77px;">
            <td><strong>Mobile Sale</strong></td>
        </tr>
        <tr style="height: 37px;">
            <td><strong>Mobile Purchase</strong></td>
        </tr>
        <tr style="color: blue">
            <td><strong>Mobile Refund</strong></td>
        </tr>
        <tr>
            <td><strong>Total Sale</strong></td>
        </tr>
        <tr style="color: blue">
            <td><strong>Total Refund</strong></td>
        </tr>
        <tr>
            <td><strong>Net Sale</strong></td>
        </tr>
        <tr style="height: 175px;">
            <td><strong>Net Card</strong></td>
        </tr>
        <tr style="height: 117px;">
            <td><strong>Net Credit</strong></td>
        </tr>
        <tr style="height: 137px;">
            <td><strong>Net Bnk Tnsfer</strong></td>
        </tr>
        <tr style="height: 117px;">
            <td><strong>Net Cheque Payment</strong></td>
        </tr>
        <tr style="height: 333px;">
            <td><strong>Cash In Hand</strong>
            </td>
        </tr>
        <tr style="height: 130px;">
            <td><strong>credit to cash(Prvs Payments)</strong></td>
        </tr>
        <tr style="height: 130px;">
            <td><strong>credit to other payment(Prvs Payments)</strong></td>
        </tr>
                
    </table></td><td><table>
        <?php
      //  pr(unserialize($dashboardData[0]['credit_quotation_desc']));die;
        $skip_date_arr = array();
        foreach($dashboardData as $key => $dash_board_Data){
            if($selectedDate != $selectedEndDate){
                $adddata = "'".$dash_board_Data['date']."'";
                $user_type = $dash_board_Data['user_type'];
                if(array_key_exists($adddata,$skip_date_arr)){
                    if(array_key_exists($user_type,$skip_date_arr[$adddata])){
                        continue;    
                    }else{
                        $skip_date_arr[$adddata][$user_type] = $dash_board_Data['date'];    
                    }
                }else{
                    $skip_date_arr[$adddata][$user_type] = $dash_board_Data['date'];    
                }
            }
            
            $repair_sale = $dash_board_Data['repair_sale'];
            $repair_refund = $dash_board_Data['repair_refund'];
            $unlock_sale = $dash_board_Data['unlock_sale'];
            $unlock_refund = $dash_board_Data['unlock_refund'];
            $product_sale = $dash_board_Data['product_sale'];
            $quotation = $dash_board_Data['quotation'];
            if(empty($quotation)){
                $quotation = 0;
            }
            $credit_note = $dash_board_Data['credit_note'];
            $credit_quotation = $dash_board_Data['credit_quotation'];
            if(empty($credit_quotation)){
                $credit_quotation = 0;
            }
            $product_refund = $dash_board_Data['product_refund'];
            $bulk_mobile_sale = $dash_board_Data['bulk_mobile_sale'];
            $bulk_mobile_refund = $dash_board_Data['bulk_mobile_refund'];
            $mobile_sale = $dash_board_Data['mobile_sale'];
            $mobile_purchase = $dash_board_Data['mobile_purchase'];
            $mobile_refund = $dash_board_Data['mobile_refund'];
            $total_sale = $dash_board_Data['total_sale'];
            $total_refund = $dash_board_Data['total_refund'];
            $net_sale = $dash_board_Data['net_sale'];
            $net_card = $dash_board_Data['net_card'];
            $net_credit = $dash_board_Data['net_credit'];
            $net_bnk_tnsfer = $dash_board_Data['net_bnk_tnsfer'];
            $net_cheque_payment = $dash_board_Data['net_cheque_payment'];
            $cash_in_hand = $dash_board_Data['cash_in_hand'];
            $credit_to_cash = $dash_board_Data['credit_to_cash'];
            $credit_to_other_payment = $dash_board_Data['credit_to_other_payment'];
            
            $repair_sale_desc = unserialize($dash_board_Data['repair_sale_desc']);
            $repairDetail = "&nbsp;<font color=blue>
            <table style=width:162px;>
            <tr>
                <td>Cash</td><td>Card</td>
            </tr><strong>
            <tr>
                <td><strong>$repair_sale_desc[cash]</strong></td><td><strong>$repair_sale_desc[card]</strong></td>
            </tr>
            </table></font>";
            
            
            $unlock_sale_desc = unserialize($dash_board_Data['unlock_sale_desc']);
            $unlockDetail = "&nbsp;<font color=blue>
            <table style=width:162px;>
            <tr>
                <td>Cash</td><td>Card</td>
            </tr><strong>
            <tr>
                <td><strong>$unlock_sale_desc[cash]</strong></td><td><strong>$unlock_sale_desc[card]</strong></td>
            </tr>
            </table></font>";
            
            $product_sale_desc = unserialize($dash_board_Data['product_sale_desc']);
           // $productSaleDetail = "&nbsp;<font color=blue><strong> <br/></strong>cash(<strong>$product_sale_desc[cash]</strong>),<br/>card(<strong>$product_sale_desc[card]</strong>),<br/>credit(<strong>$product_sale_desc[credit]</strong>),<br/>bank transfer(<strong>$product_sale_desc[bank_transfer]</strong>),<br/>cheque(<strong>$product_sale_desc[cheque]</strong>)</font>";
            
            $productSaleDetail="&nbsp;<font color=blue>
            <table style=width:162px;>
            <tr>
                <td>Cash</td><td>Card</td><td>credit</td><td>bank transfer</td><td>cheque</td>
            </tr><strong>
            <tr>
                <td><strong>$product_sale_desc[cash]</strong></td><td><strong>$product_sale_desc[card]</strong></td>
                <td><strong>$product_sale_desc[credit]</strong></td><td><strong>$product_sale_desc[bank_transfer]</strong></td>
                <td><strong>$product_sale_desc[cheque]</strong></td>
            </tr>
            </table></font>";
            
            $quotation_desc = unserialize($dash_board_Data['quotation_desc']);
            //$QuotationDetail = "&nbsp;<font color=blue><strong> <br/></strong>cash(<strong>$quotation_desc[cash]</strong>),<br/>card(<strong>$quotation_desc[card]</strong>),<br/>credit(<strong>$quotation_desc[credit]</strong>),<br/>bank transfer(<strong>$quotation_desc[bank_transfer]</strong>),<br/>cheque(<strong>$quotation_desc[cheque]</strong>)</font>";
            
            $QuotationDetail="&nbsp;<font color=blue>
            <table style=width:162px;>
            <tr>
                <td>Cash</td><td>Card</td><td>credit</td><td>bank transfer</td><td>cheque</td>
            </tr><strong>
            <tr>
                <td><strong>$quotation_desc[cash]</strong></td><td><strong>$quotation_desc[card]</strong></td>
                <td><strong>$quotation_desc[credit]</strong></td><td><strong>$quotation_desc[bank_transfer]</strong></td>
                <td><strong>$quotation_desc[cheque]</strong></td>
            </tr>
            </table></font>";
            
            $credit_note_desc = unserialize($dash_board_Data['credit_note_desc']);
            //$CreditNoteDetail = "&nbsp;<font color=blue><strong> <br/></strong>cash(<strong>$credit_note_desc[cash]</strong>),<br/>card(<strong>$credit_note_desc[card]</strong>),<br/>credit(<strong>$credit_note_desc[credit]</strong>),<br/>bank transfer(<strong>$credit_note_desc[bank_transfer]</strong>),<br/>cheque(<strong>$credit_note_desc[cheque]</strong>)</font>";
            
            $CreditNoteDetail ="&nbsp;<font color=blue>
            <table style=width:162px;>
            <tr>
                <td>Cash</td><td>Card</td><td>credit</td><td>bank transfer</td><td>cheque</td>
            </tr><strong>
            <tr>
                <td><strong>$credit_note_desc[cash]</strong></td><td><strong>$credit_note_desc[card]</strong></td>
                <td><strong>$credit_note_desc[credit]</strong></td><td><strong>$credit_note_desc[bank_transfer]</strong></td>
                <td><strong>$credit_note_desc[cheque]</strong></td>
            </tr>
            </table></font>";
            
            
            $sp_credit_note_desc = unserialize($dash_board_Data['credit_quotation_desc']);
           // $SpCreditNoteDetail = "&nbsp;<font color=blue><strong> <br/></strong>cash(<strong>$sp_credit_note_desc[cash]</strong>),<br/>card(<strong>$sp_credit_note_desc[card]</strong>),<br/>credit(<strong>$sp_credit_note_desc[credit]</strong>),<br/>bank transfer(<strong>$sp_credit_note_desc[bank_transfer]</strong>),<br/>cheque(<strong>$sp_credit_note_desc[cheque]</strong>)</font>";
            
            $SpCreditNoteDetail ="&nbsp;<font color=blue>
            <table style=width:162px;>
            <tr>
                <td>Cash</td><td>Card</td><td>credit</td><td>bank transfer</td><td>cheque</td>
            </tr><strong>
            <tr>
                <td><strong>$sp_credit_note_desc[cash]</strong></td><td><strong>$sp_credit_note_desc[card]</strong></td>
                <td><strong>$sp_credit_note_desc[credit]</strong></td><td><strong>$sp_credit_note_desc[bank_transfer]</strong></td>
                <td><strong>$sp_credit_note_desc[cheque]</strong></td>
            </tr>
            </table></font>";
            
            
            $bulk_mobile_sale_desc = unserialize($dash_board_Data['bulk_mobile_sale_desc']);
            //$BlkMobileSaleDetail = "<font color=blue>&nbsp;<strong> <br/></strong>cash(<strong>$bulk_mobile_sale_desc[cash]</strong>),<br/>card(<strong>$bulk_mobile_sale_desc[card]</strong>)</font>";
            
            $BlkMobileSaleDetail ="&nbsp;<font color=blue>
            <table style=width:162px;>
            <tr>
                <td>Cash</td><td>Card</td>
            </tr><strong>
            <tr>
                <td><strong>$bulk_mobile_sale_desc[cash]</strong></td><td><strong>$bulk_mobile_sale_desc[card]</strong></td>
            </tr>
            </table></font>";
            
            $mobile_sale_desc = unserialize($dash_board_Data['mobile_sale_desc']);
           // $MobileSaleDetail = "&nbsp;<font color=blue><strong> <br/></strong>cash(<strong>$mobile_sale_desc[cash]</strong>),<br/>card(<strong>$mobile_sale_desc[card]</strong>)</font>";
            
            $MobileSaleDetail ="&nbsp;<font color=blue>
            <table style=width:162px;>
            <tr>
                <td>Cash</td><td>Card</td>
            </tr><strong>
            <tr>
                <td><strong>$mobile_sale_desc[cash]</strong></td><td><strong>$mobile_sale_desc[card]</strong></td>
            </tr>
            </table></font>";
            
            
            $net_card_desc = unserialize($dash_board_Data['net_card_desc']);
            $totalNetCardDetail = $net_card_desc['repair']+$net_card_desc['Unlock']+$net_card_desc['Product']+$net_card_desc['Blk']+$net_card_desc['Mobile']+$net_card_desc['prev_recpts_sale']+$net_card_desc['credit_note'];
            if(array_key_exists('special_credit_note',$net_card_desc)){
               $sp_credt_nt =  $net_card_desc['special_credit_note'];
               $special_card = $net_card_desc['special'];
            }else{
               $sp_credt_nt = 0;
               $special_card = 0;
            }
           // $NetCardDetail = "&nbsp;<font color=blue><strong> <br/></strong>repair(<strong>$net_card_desc[repair]</strong>)+<br/>unlock(<strong>$net_card_desc[Unlock]</strong>)+<br/>product(<strong>$net_card_desc[Product]</strong>)+<br/>blk(<strong>$net_card_desc[Blk]</strong>)+<br/>mobile(<strong>$net_card_desc[Mobile]</strong>)+<br/>quotation(<strong>$special_card</strong>)+<br/>prev_recpts_sale(<strong>$net_card_desc[prev_recpts_sale]</strong>)-<br/>credit note(<strong>$net_card_desc[credit_note]</strong>)-<br/>Special Credit Note (<strong>$sp_credt_nt</storng>) = $totalNetCardDetail</font>";
            
            $NetCardDetail ="&nbsp;<font color=blue>
            <table border=1 style=width:162px;>
            <tr>
                <td>repair</td><td>unlock</td><td>product</td><td>blk</td><td>mob</td><td>Quot</td><td>prev Recpts Sale</td><td>credit note</td><td>Credit Quotation</td>
            </tr><strong>
            <tr>
                <td><strong>$net_card_desc[repair]</strong></td><td><strong>$net_card_desc[Unlock]</strong></td>
                <td><strong>$net_card_desc[Product]</strong></td><td><strong>$net_card_desc[Blk]</strong></td>
                <td><strong>$net_card_desc[Mobile]</strong></td><td><strong>$special_card</strong></td>
                <td><strong>$net_card_desc[prev_recpts_sale]</strong></td><td><strong>$net_card_desc[credit_note]</strong></td>
                <td><strong>$sp_credt_nt</strong></td>
            </tr>
            <tr>
            <td colspan=7></td>
            <td>total</td><td>$totalNetCardDetail</td>
            </tr>
            </table></font>";
            
            
            $net_credit_desc = unserialize($dash_board_Data['net_credit_desc']);
            $TotalNetCreditDetail = $net_credit_desc[0]+$net_credit_desc[1];
           // $NetCreditDetail = "&nbsp;<font color=blue><strong> <br/></strong>($net_credit_desc[0])+<br/>($net_credit_desc[1]) = <br/>$TotalNetCreditDetail";
           $p_crdit = $product_sale_desc['credit'];
           $sp_crdit =$quotation_desc['credit'];
           $credit_note_credit = $credit_note_desc['credit'];
           $sp_credit_note_credit = $sp_credit_note_desc['credit'];
            //$NetCreditDetail = "&nbsp;<font color=blue><strong> <br/></strong>(product($p_crdit)+
            //                                                                     <br/>quotation($sp_crdit))-</br>
            //                                                                     (credit_note($credit_note_credit)-
            //                                                                     </br>special_credit_note($sp_credit_note_credit))
            //                                                                     = <br/>$TotalNetCreditDetail";
                                                                                 
                                                                                 
              $NetCreditDetail ="&nbsp;<font color=blue>
            <table border=1 style=width:162px;>
            <tr>
                <td>product</td><td>quotation</td><td>credit_note</td><td>Credit Quotation</td>
            </tr><strong>
            <tr>
                <td><strong>$p_crdit</strong></td><td><strong>$sp_crdit</strong></td>
                <td><strong>$credit_note_credit</strong></td><td><strong>$sp_credit_note_credit</strong></td>
            </tr>
            <tr>
            <td colspan=2></td>
            <td>total</td><td>$TotalNetCreditDetail</td>
            </tr>
            </table></font>";
            
            $net_bnk_tnsfer_desc = unserialize($dash_board_Data['net_bnk_tnsfer_desc']);
            $TotalNetBnkTrnsfrDetail = $net_bnk_tnsfer_desc[0]+$net_bnk_tnsfer_desc[1];
            //$NetBnkTrnsfrDetail = "&nbsp;<font color=blue><strong> <br/></strong>($net_bnk_tnsfer_desc[0])+<br/>($net_bnk_tnsfer_desc[1]) = <br/>$TotalNetBnkTrnsfrDetail";
            
            $p_bnk = $product_sale_desc['bank_transfer'];
           $sp_bnk =$quotation_desc['bank_transfer'];
           $credit_note_bnk = $credit_note_desc['bank_transfer'];
           $sp_credit_note_bnk = $sp_credit_note_desc['bank_transfer'];
            
            //$NetBnkTrnsfrDetail = "&nbsp;<font color=blue><strong> <br/></strong>(product($p_bnk)+
            //                                                                        <br/>quotation($sp_bnk))-
            //                                                                        </br>(credit_note($credit_note_bnk)
            //                                                                        -special_credit_note($sp_credit_note_bnk))
            //                                                                        = <br/>$TotalNetBnkTrnsfrDetail";
            
            $NetBnkTrnsfrDetail ="&nbsp;<font color=blue>
            <table border=1 style=width:162px;>
            <tr>
                <td>product</td><td>quotation</td><td>credit_note</td><td>Credit Quotation</td>
            </tr><strong>
            <tr>
                <td><strong>$p_bnk</strong></td><td><strong>$sp_bnk</strong></td>
                <td><strong>$credit_note_bnk</strong></td><td><strong>$sp_credit_note_bnk</strong></td>
            </tr>
            <tr>
            <td colspan=2></td>
            <td>total</td><td>$TotalNetBnkTrnsfrDetail</td>
            </tr>
            </table></font>";
            
            $net_cheque_payment_desc = unserialize($dash_board_Data['net_cheque_payment_desc']);
            $TotalNetChequePayDetail = $net_cheque_payment_desc[0]+$net_cheque_payment_desc[1];
            //$NetChequePayDetail = "&nbsp;<font color=blue><strong> <br/></strong>($net_cheque_payment_desc[0])+<br/>($net_cheque_payment_desc[1]) = <br/>$TotalNetChequePayDetail";
            
            $p_cheque = $product_sale_desc['cheque'];
           $sp_cheque =$quotation_desc['cheque'];
           $credit_note_cheque = $credit_note_desc['cheque'];
           $sp_credit_note_cheque = $sp_credit_note_desc['cheque'];
            
            //$NetChequePayDetail = "&nbsp;<font color=blue><strong> <br/></strong>(product($p_cheque)+
            //                                                                           <br/>quotation($sp_cheque))-</br>
            //                                                                           (credit_note($credit_note_cheque)+</br>
            //                                                                           special_credit_note($sp_credit_note_cheque))
            //                                                                           = <br/>$TotalNetChequePayDetail";
            
            $NetChequePayDetail ="&nbsp;<font color=blue>
            <table border=1 style=width:162px;>
            <tr>
                <td>product</td><td>quotation</td><td>credit_note</td><td>Credit Quotation</td>
            </tr><strong>
            <tr>
                <td><strong>$p_cheque</strong></td><td><strong>$sp_cheque</strong></td>
                <td><strong>$credit_note_cheque</strong></td><td><strong>$sp_credit_note_cheque</strong></td>
            </tr>
            <tr>
            <td colspan=2></td>
            <td>total</td><td>$TotalNetChequePayDetail</td>
            </tr>
            </table></font>"; 
            
            $cash_in_hand_desc = unserialize($dash_board_Data['cash_in_hand_desc']);
           // echo $dash_board_Data['cash_in_hand'];
           // pr($cash_in_hand_desc);
            $TotalCashInHandSale = $cash_in_hand_desc['sale']['Repair']+
                                    $cash_in_hand_desc['sale']['Unlock']+
                                    $cash_in_hand_desc['sale']['Product']+
                                    $cash_in_hand_desc['sale']['Blk']+
                                    $cash_in_hand_desc['sale']['Mobile']+
                                    $cash_in_hand_desc['sale']['prv_recpit_amt'];
            if(array_key_exists('special',$cash_in_hand_desc['sale'])){
                $TotalCashInHandSale += $cash_in_hand_desc['sale']['special'] +
                                       $cash_in_hand_desc['sale']['prv_credit_to_cash'];
            }
            $TotalCashInHandRefund = $cash_in_hand_desc['refund']['Repair']+
                                       $cash_in_hand_desc['refund']['Unlock']+
                                       $cash_in_hand_desc['refund']['Product']+
                                       $cash_in_hand_desc['refund']['Blk']+
                                       $cash_in_hand_desc['refund']['Mobile']+
                                       $cash_in_hand_desc['refund']['Credit_Note']+
                                       $cash_in_hand_desc['refund']['Mobile_Purchase'];
            if(array_key_exists('special_credit_note',$cash_in_hand_desc['refund'])){
                $TotalCashInHandRefund += $cash_in_hand_desc['refund']['special_credit_note'];
            }
            $finalCashInHand = $TotalCashInHandSale - $TotalCashInHandRefund;
            if(array_key_exists('special',$cash_in_hand_desc['sale'])){
                $special = $cash_in_hand_desc['sale']['special'];
                $prv_credit_to_cash = $cash_in_hand_desc['sale']['prv_credit_to_cash'];
                $special_credit_note = $cash_in_hand_desc['refund']['special_credit_note'];  
            }else{
                $special = 0;
                $prv_credit_to_cash = 0;
                $special_credit_note = 0;  
            }
            //$norCashInHandDetail = "&nbsp;<font color=blue><strong> <br/></strong>(Sale(
            //(".$cash_in_hand_desc['sale']['Repair'].")+
            //<br/>(".$cash_in_hand_desc['sale']['Unlock'].")+
            //<br/>(".$cash_in_hand_desc['sale']['Product'].")+
            //<br/>(".$cash_in_hand_desc['sale']['Blk'].")+
            //<br/>(".$cash_in_hand_desc['sale']['Mobile'].")+
            //<br/>(".$cash_in_hand_desc['sale']['prv_recpit_amt'].")+
            //</br>(".$special.")+
            //</br>(".$prv_credit_to_cash."))= $TotalCashInHandSale,
            //<br/><br/>
            //Refund((".$cash_in_hand_desc['refund']['Repair'].")+
            //<br/>(".$cash_in_hand_desc['refund']['Unlock'].")+
            //<br/>(".$cash_in_hand_desc['refund']['Product'].")+
            //<br/>(".$cash_in_hand_desc['refund']['Blk'].")+
            //<br/>(".$cash_in_hand_desc['refund']['Mobile'].")+
            //<br/>(".$cash_in_hand_desc['refund']['Credit_Note'].")+
            //<br/>(".$cash_in_hand_desc['refund']['Mobile_Purchase'].")+
            //</br>(".$special_credit_note."))=$TotalCashInHandRefund <br/>
            //($TotalCashInHandSale-$TotalCashInHandRefund) = $finalCashInHand)";
            
            $norCashInHandDetail ="&nbsp;<font color=blue>
            <table border=1 style=width:162px;>
            <tr>
                <td>Sale</td>
            </tr>
            <tr>
                <td>Repair</td><td>Unlock</td><td>Product </td><td>Blk</td>
                <td>Mobile</td><td>Prv Recpt amt</td><td>special</td><td>Prv Credit 2 Cash</td>
            </tr>
            <tr>
                <td><strong>".$cash_in_hand_desc['sale']['Repair']."</strong></td><td><strong>".$cash_in_hand_desc['sale']['Unlock']."</strong></td>
                <td><strong>".$cash_in_hand_desc['sale']['Product']."</strong></td><td><strong>".$cash_in_hand_desc['sale']['Blk']."</strong></td>
                <td><strong>".$cash_in_hand_desc['sale']['Mobile']."</strong></td><td><strong>".$cash_in_hand_desc['sale']['prv_recpit_amt']."</strong></td>
                <td><strong>$special</strong></td><td><strong>$prv_credit_to_cash</strong></td>
            </tr>
            <tr>
            <td colspan='6'></td>
            <td>total</td><td>$TotalCashInHandSale</td>
            </tr>
            <tr>
                <td>
                    Refund
                </td>
            </tr>
            <tr>
                <td>Repair</td>
                <td>Unlock</td>
                <td>Product</td>
                <td>Blk</td>
                <td>Mobile</td>
                <td>Credit Note</td>
                <td>Mobile Purchase</td>
                <td>Credit Quotation</td>
            </tr>
            <tr>
            <td><strong>".$cash_in_hand_desc['refund']['Repair']."</strong></td>
            <td><strong>".$cash_in_hand_desc['refund']['Unlock']."</strong></td>
            <td><strong>".$cash_in_hand_desc['refund']['Product']."</strong></td>
            <td><strong>".$cash_in_hand_desc['refund']['Blk']."</strong></td>
            <td><strong>".$cash_in_hand_desc['refund']['Mobile']."</strong></td>
            <td><strong>".$cash_in_hand_desc['refund']['Credit_Note']."</strong></td>
            <td><strong>".$cash_in_hand_desc['refund']['Mobile_Purchase']."</strong></td>
            <td><strong>$special_credit_note</td>
            </tr>
            <tr>
            <td colspan='6'></td>
            <td>total</td><td>$TotalCashInHandRefund</td>
            </tr>
            <tr>
            <td colspan='6'></td>
            <td colspan='3'>($TotalCashInHandSale-$TotalCashInHandRefund) = $finalCashInHand)</td>
            </tr>
            </table></font>"; 
            
            
            $credit_to_cash_desc = unserialize($dash_board_Data['credit_to_cash_desc']);
            //$CreditToCashDetail = "&nbsp;<font color=blue><strong> <br/></strong>invoice cash(<strong>$credit_to_cash_desc[invoice_cash]</strong>),<br/>Quotation cash(<strong>$credit_to_cash_desc[Quotation_cash]</strong>),<br/>credit cash(<strong>$credit_to_cash_desc[credit_cash]</strong>),<br/>credit quotation cash(<strong>$credit_to_cash_desc[credit_quotation_cash]</strong>)</font>";
            
            $CreditToCashDetail = "&nbsp;<font color=blue><table>
            <tr><td>invoice cash</td><td>Quotation cash</td><td>credit cash</td>
            <td>credit quotation cash</td></tr>
            
            <tr><td><strong>$credit_to_cash_desc[invoice_cash]</strong></td><td><strong>$credit_to_cash_desc[Quotation_cash]</strong></td>
            <td><strong>$credit_to_cash_desc[credit_cash]</strong></td><td><strong>$credit_to_cash_desc[credit_quotation_cash]</strong></td>
            </tr>
            </table></font>";
            
            $credit_to_other_payment_desc = unserialize($dash_board_Data['credit_to_other_payment_desc']);
           // $CreditToOtherDetail = "&nbsp;<font color=blue><strong> <br/></strong>total bank transfer(<strong>$credit_to_other_payment_desc[total_bank_transfer]</strong>),<br/>total card Payment(<strong>$credit_to_other_payment_desc[total_card_Payment]</strong>),<br/>total cheque payment(<strong>$credit_to_other_payment_desc[total_cheque_payment]</strong>)</font>";
            
            $CreditToOtherDetail = "&nbsp;<font color=blue><table>
            <tr><td>total bank transfer</td><td>total card Payment</td><td>total cheque payment</td></tr><strong>
            <tr><td><strong>$credit_to_other_payment_desc[total_bank_transfer]</strong></td><td><strong>$credit_to_other_payment_desc[total_card_Payment]</strong></td>
            <td><strong>$credit_to_other_payment_desc[total_cheque_payment]</strong></td>
            </tr>
            </table></font>";
            
            $CURRENCY_TYPE = Configure::read('CURRENCY_TYPE');
                if($dash_board_Data['user_type'] == 'normal'){
                    $idd = $dash_board_Data['id'];
                    if($dash_board_Data['status'] == 1){
                     echo"<tr style=height:57px;><td><b style=color:red;>ORIGNAL : </br></b><b style=background-color:yellow;text-align:right;font-size:14px;> $CURRENCY_TYPE$repair_sale</b> $repairDetail</td></tr>"  ;
                    }else{
                     echo"<tr style=height:57px;><td><b style=background-color:yellow;text-align:right;font-size:14px;> $CURRENCY_TYPE$repair_sale</b> $repairDetail</td></tr>"  ; 
                    }
                    //echo"<tr style=height:57px;><td>$idd</td></tr>"  ;
                    //echo"<tr style=height:57px;><td>test</td></tr>"  ;
                    
                    echo"<tr style=height:37px;><td><b style=background-color:yellow;text-align:right;font-size:14px;> $CURRENCY_TYPE$repair_refund</b></td></tr>"  ;
                    echo"<tr style=height:57px;><td><b style=background-color:yellow;text-align:right;font-size:14px;> $CURRENCY_TYPE$unlock_sale</b> $unlockDetail</td></tr>"  ;
                    echo"<tr style=height:37px;><td><b style=background-color:yellow;text-align:right;font-size:14px;>$CURRENCY_TYPE$unlock_refund</b></td></tr>"  ;
                    echo"<tr style=height:110px;><td><b style=background-color:yellow;text-align:right;font-size:14px;> $CURRENCY_TYPE$product_sale</b> $productSaleDetail</td></tr>"  ;
                    echo"<tr style=height:110px;><td><b style=background-color:yellow;text-align:right;font-size:14px;> $CURRENCY_TYPE$quotation</b></td></tr>"  ;
                    echo"<tr style=height:110px;><td><b style=background-color:yellow;text-align:right;font-size:14px;> $CURRENCY_TYPE$credit_note</b> $CreditNoteDetail</td></tr>"  ;
                    echo"<tr style=height:110px;><td><b style=background-color:yellow;text-align:right;font-size:14px;> $CURRENCY_TYPE$credit_quotation</b></td></tr>"  ;
                    echo"<tr style=height:37px;><td><b style=background-color:yellow;text-align:right;font-size:14px;> $CURRENCY_TYPE$product_refund</b></td></tr>"  ;
                    echo"<tr style=height:53px;><td><b style=background-color:yellow;text-align:right;font-size:14px;> $CURRENCY_TYPE$bulk_mobile_sale</b> $BlkMobileSaleDetail</td></tr>"  ;
                    echo"<tr style=height:37px;><td><b style=background-color:yellow;text-align:right;font-size:14px;> $CURRENCY_TYPE$bulk_mobile_refund</b></td></tr>"  ;
                    echo"<tr style=height:53px;><td><b style=background-color:yellow;text-align:right;font-size:14px;> $CURRENCY_TYPE$mobile_sale</b> $MobileSaleDetail</td></tr>"  ;
                    echo"<tr style=height:37px;><td><b style=background-color:yellow;text-align:right;font-size:14px;> $CURRENCY_TYPE$mobile_purchase</b></td></tr>"  ;
                    echo"<tr style=height:20px;><td><b style=background-color:yellow;text-align:right;font-size:14px;> $CURRENCY_TYPE$mobile_refund</b></td></tr>"  ;
                    echo"<tr style=height:20px;><td><b style=background-color:yellow;text-align:right;font-size:14px;>$CURRENCY_TYPE$total_sale</b></td></tr>"  ;
                    echo"<tr style=height:20px;><td><b style=background-color:yellow;text-align:right;font-size:14px;>$CURRENCY_TYPE$total_refund</b></td></tr>"  ;
                    echo"<tr style=height:20px;><td><b style=background-color:yellow;text-align:right;font-size:14px;> $CURRENCY_TYPE$net_sale</b></td></tr>"  ;
                    echo"<tr style=height:167px;><td><b style=background-color:yellow;text-align:right;font-size:14px;> $CURRENCY_TYPE$net_card</b> $NetCardDetail</td></tr>"  ;
                    echo"<tr style=height:119px;><td><b style=background-color:yellow;text-align:right;font-size:14px;> $CURRENCY_TYPE$net_credit</b> $NetCreditDetail</td></tr>"  ;
                    echo"<tr style=height:69px;><td><b style=background-color:yellow;text-align:right;font-size:14px;> $CURRENCY_TYPE$net_bnk_tnsfer</b> $NetBnkTrnsfrDetail</td></tr>"  ;
                    echo"<tr style=height:127px;><td><b style=background-color:yellow;text-align:right;font-size:14px;> $CURRENCY_TYPE$net_cheque_payment</b> $NetChequePayDetail</td></tr>"  ;
                    echo"<tr style=height:331px;><td><b style=background-color:yellow;text-align:right;font-size:14px;> $CURRENCY_TYPE$cash_in_hand</b> $norCashInHandDetail</td></tr>"  ;
                    echo"<tr style=height:130px;><td><b style=background-color:yellow;text-align:right;font-size:14px;> $CURRENCY_TYPE$credit_to_cash</b> $CreditToCashDetail</td></tr>"  ;
                    echo"<tr style=height:130px;><td><b style=background-color:yellow;text-align:right;font-size:14px;>$CURRENCY_TYPE$credit_to_other_payment</b> $CreditToOtherDetail</td></tr>"  ;
                    
                    if(array_key_exists('credit_to_other_payment',$dash_board_Data)){
                        echo"</table></td><td><table>";
                    }
                    
                }
                
                if($dash_board_Data['user_type'] == 'other'){
                    $idd = $dash_board_Data['id'];
                    if($dash_board_Data['status'] == 1){
                        echo"<tr style=height:57px;background:bisque;><td><b style=color:red;>ORIGNAL : </br></b><b style=background-color:yellow;text-align:right;font-size:14px;> $CURRENCY_TYPE$repair_sale </b>$repairDetail</td></tr>" ;
                    }else{
                        echo"<tr style=height:57px;background:bisque;><td><b style=background-color:yellow;text-align:right;font-size:14px;> $CURRENCY_TYPE$repair_sale</b>$repairDetail</td></tr>";
                    }
                    echo"<tr style=height:37px;background:bisque;><td><b style=background-color:yellow;text-align:right;font-size:14px;> $CURRENCY_TYPE$repair_refund</b></td></tr>"  ;
                    echo"<tr style=height:57px;background:bisque;><td><b style=background-color:yellow;text-align:right;font-size:14px;> $CURRENCY_TYPE$unlock_sale</b>$unlockDetail</td></tr>"  ;
                    echo"<tr style=height:37px;background:bisque;><td><b style=background-color:yellow;text-align:right;font-size:14px;> $CURRENCY_TYPE$unlock_refund</b></td></tr>"  ;
                    echo"<tr style=height:110px;background:bisque;><td><b style=background-color:yellow;text-align:right;font-size:14px;> $CURRENCY_TYPE$product_sale</b> $productSaleDetail</td></tr>"  ;
                    echo"<tr style=height:110px;background:bisque;><td><b style=background-color:yellow;text-align:right;font-size:14px;> $CURRENCY_TYPE$quotation</b> $QuotationDetail</td></tr>"  ;
                    echo"<tr style=height:110px;background:bisque;><td><b style=background-color:yellow;text-align:right;font-size:14px;> $CURRENCY_TYPE$credit_note</b> $CreditNoteDetail</td></tr>"  ;
                    echo"<tr style=height:110px;background:bisque;><td><b style=background-color:yellow;text-align:right;font-size:14px;> $CURRENCY_TYPE$credit_quotation</b> $SpCreditNoteDetail</td></tr>"  ;
                    echo"<tr style=height:37px;background:bisque;><td><b style=background-color:yellow;text-align:right;font-size:14px;> $CURRENCY_TYPE$product_refund</b></td></tr>"  ;
                    echo"<tr style=height:53px;background:bisque;><td><b style=background-color:yellow;text-align:right;font-size:14px;> $CURRENCY_TYPE$bulk_mobile_sale</b> $BlkMobileSaleDetail</td></tr>"  ;
                    echo"<tr style=height:37px;background:bisque;><td><b style=background-color:yellow;text-align:right;font-size:14px;> $CURRENCY_TYPE$bulk_mobile_refund</b></td></tr>"  ;
                    echo"<tr style=height:53px;background:bisque;><td><b style=background-color:yellow;text-align:right;font-size:14px;> $CURRENCY_TYPE$mobile_sale</b> $MobileSaleDetail</td></tr>"  ;
                    echo"<tr style=height:37px;background:bisque;><td><b style=background-color:yellow;text-align:right;font-size:14px;> $CURRENCY_TYPE$mobile_purchase</b></td></tr>"  ;
                    echo"<tr style=height:20px;background:bisque;><td><b style=background-color:yellow;text-align:right;font-size:14px;>$CURRENCY_TYPE$mobile_refund</b></td></tr>";
                    echo"<tr style=height:20px;background:bisque;><td><b style=background-color:yellow;text-align:right;font-size:14px;> $CURRENCY_TYPE$total_sale</b></td></tr>";
                    echo"<tr style=height:20px;background:bisque;><td><b style=background-color:yellow;text-align:right;font-size:14px;> $CURRENCY_TYPE$total_refund</b></td></tr>";
                    echo"<tr style=height:20px;background:bisque;><td><b style=background-color:yellow;text-align:right;font-size:14px;> $CURRENCY_TYPE$net_sale</b></td></tr>"  ;
                    echo"<tr style=height:167px;background:bisque;><td><b style=background-color:yellow;text-align:right;font-size:14px;> $CURRENCY_TYPE$net_card</b> $NetCardDetail</td></tr>"  ;
                    echo"<tr style=height:119px;background:bisque;><td><b style=background-color:yellow;text-align:right;font-size:14px;> $CURRENCY_TYPE$net_credit</b> $NetCreditDetail</td></tr>"  ;
                    echo"<tr style=height:69px;background:bisque;><td><b style=background-color:yellow;text-align:right;font-size:14px;> $CURRENCY_TYPE$net_bnk_tnsfer</b> $NetBnkTrnsfrDetail</td></tr>"  ;
                    echo"<tr style=height:127px;background:bisque;><td><b style=background-color:yellow;text-align:right;font-size:14px;> $CURRENCY_TYPE$net_cheque_payment</b> $NetChequePayDetail</td></tr>"  ;
                    echo"<tr style=height:331px;background:bisque;><td><b style=background-color:yellow;text-align:right;font-size:14px;> $CURRENCY_TYPE$cash_in_hand</b> $norCashInHandDetail</td></tr>"  ;
                    echo"<tr style=height:130px;background:bisque;><td><b style=background-color:yellow;text-align:right;font-size:14px;> $CURRENCY_TYPE$credit_to_cash</b> $CreditToCashDetail</td></tr>"  ;
                    echo"<tr style=height:130px;background:bisque;><td><b style=background-color:yellow;text-align:right;font-size:14px;> $CURRENCY_TYPE$credit_to_other_payment</b> $CreditToOtherDetail</td></tr>"  ;
                    
                    if(array_key_exists('credit_to_other_payment',$dash_board_Data)){
                        echo"</table></td><td><table>";
                    }
                }   
        } ?>
        <?php
    }
    ?>

<?php echo $this->Form->end();
?>
<script>
    jQuery(function() {
		jQuery( "#datepicker1" ).datepicker({ dateFormat: "d M yy" });
        jQuery( "#datepicker2" ).datepicker({ dateFormat: "d M yy" });
	});
</script>

<script>
    $('#reset').click(function(){
        $('#datepicker1').val('');
        $('#datepicker2').val('');
        //$('#TodaySaleKiosk').val('');
        //$('#TodaySaleUser').val('');
    });
</script>
