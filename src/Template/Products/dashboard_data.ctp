<?php
use Cake\Core\Configure;
use Cake\Core\Configure\Engine\PhpConfig;
$special_user = 0;
$loggedInUser = $this->request->session()->read('Auth.User.username');
if (preg_match('/'.QUOT_USER_PREFIX.'/',$loggedInUser)){//checking if the user has right for tempering
   $special_user = 1;
}

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
        if($selectedDate != $selectedEndDate){
         $adddata1 = "'".$value1['date']."'";
            $user_type1 = $value1['user_type'];
            if(array_key_exists($adddata1,$skip_date_arr1)){
                if(array_key_exists($user_type1,$skip_date_arr1[$adddata1])){
                    continue;    
                }else{
                    $skip_date_arr1[$adddata1][$user_type1] = $value1['date'];    
                }
            }else{
                $skip_date_arr1[$adddata1][$user_type1] = $value1['date'];    
            }
        }
            //pr($value1['date']);
            //pr($value1['user_type']);
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
        <?php $session_kiosk_id = $this->request->Session()->read('kiosk_id');
        if(!$session_kiosk_id){
        ?>
        <td><?php echo $this->Form->input('kiosk',array('options'=>$kiosks,'id'=>'TodaySaleKiosk','default'=>$selectedKiosk))?></td>
        <?php }?>
        <td><?php echo $this->Form->input('user',array('options'=>$users,'id'=>'TodaySaleUser','default'=>$selectedUser,'disabled'=>'disabled'))?></td>
        <td>
    </tr>
</table>
<b>**highlighted Rows are Special user data.</b>
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
<?php if($special_user){?>
<th style="width: 120px;">(<?=$date_toShow;?>)</th>
<?php }?>
<th style="width: 120px;">(<?=$date_toShow;?>)</th>
<?php
}
?>
</tr><tr>
<td style="width: 11%;">
     <table style="float: left;width: 100%;">
        
        <tr style="height: 57px;">
            <td><strong>Repair Sale</strong></td>
        </tr>
        <tr style="color: blue;height: 37px;">
            <td><strong>Repair Refund</strong></td>
        </tr>
        <tr style="height: 57px;">
            <td><strong>Unlock Sale</strong></td>
        </tr>
        <tr style="color: blue;height: 37px;">
            <td><strong>Unlock Refund</strong></td>
        </tr>
        <tr style="height: 110px;">
            <td><strong>Product Sale</strong></td>
        </tr>
        <?php if($special_user){?>
        <tr style="height: 110px;">
            <td><strong>Quotation</strong></td>
        </tr>
        <?php }?>
        <tr style="color: blue;height: 110px;">
            <td><strong>Credit Note</strong></td>
        </tr>
        <?php if($special_user){?>
        <tr style="height: 110px;">
            <td><strong>Credit Quotation</strong></td>
        </tr>
        <?php }?>
        <tr style="color: blue;height: 37px;">
            <td><strong>Product Refund</strong></td>
        </tr>
        <tr style="height: 53px;">
            <td><strong>Bulk Mobile Sale</strong></td>
        </tr>
        <tr style="color: blue;height: 37px;">
            <td><strong>Bulk Mobile Refund</strong></td>
        </tr>
        <tr style="height: 53px;">
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
        <tr style="height: 166px;">
            <td><strong>Net Card</strong></td>
        </tr>
        <tr style="height: 117px;">
            <td><strong>Net Credit</strong></td>
        </tr>
        <tr style="height: 117px;">
            <td><strong>Net Bnk Tnsfer</strong></td>
        </tr>
        <tr style="height: 117px;">
            <td><strong>Net Cheque Payment</strong></td>
        </tr>
        <tr style="height: 333px;">
            <td><strong>Cash In Hand</strong></br>
            Repair</br>Unlock</br>Product </br>Blk </br>Mobile </br>prv_recpit_amt </br>special</br>prv_credit_to_cash</br> </br>Repair</br>Unlock </br>Product </br>Blk</br>Mobile</br>Credit_Note</br>Mobile_Purchase</br>special_credit_note
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
         if($dash_board_Data['user_type'] =="other" && !$special_user){
            continue;
         }
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
            $repairDetail = "&nbsp;<font color=blue><strong>Detail: <br/></strong>cash(<strong>$repair_sale_desc[cash]</strong>),<br/>card(<strong>$repair_sale_desc[card]</strong>)</font>";
            
            $unlock_sale_desc = unserialize($dash_board_Data['unlock_sale_desc']);
            $unlockDetail = "&nbsp;<font color=blue><strong>Detail: <br/></strong>cash(<strong>$unlock_sale_desc[cash]</strong>),<br/>card(<strong>$unlock_sale_desc[card]</strong>)</font>";
            
            $product_sale_desc = unserialize($dash_board_Data['product_sale_desc']);
            $productSaleDetail = "&nbsp;<font color=blue><strong>Detail: <br/></strong>cash(<strong>$product_sale_desc[cash]</strong>),<br/>card(<strong>$product_sale_desc[card]</strong>),<br/>credit(<strong>$product_sale_desc[credit]</strong>),<br/>bank transfer(<strong>$product_sale_desc[bank_transfer]</strong>),<br/>cheque(<strong>$product_sale_desc[cheque]</strong>)</font>";
            
            $quotation_desc = unserialize($dash_board_Data['quotation_desc']);
            $QuotationDetail = "&nbsp;<font color=blue><strong>Detail: <br/></strong>cash(<strong>$quotation_desc[cash]</strong>),<br/>card(<strong>$quotation_desc[card]</strong>),<br/>credit(<strong>$quotation_desc[credit]</strong>),<br/>bank transfer(<strong>$quotation_desc[bank_transfer]</strong>),<br/>cheque(<strong>$quotation_desc[cheque]</strong>)</font>";
            
            $credit_note_desc = unserialize($dash_board_Data['credit_note_desc']);
            $CreditNoteDetail = "&nbsp;<font color=blue><strong>Detail: <br/></strong>cash(<strong>$credit_note_desc[cash]</strong>),<br/>card(<strong>$credit_note_desc[card]</strong>),<br/>credit(<strong>$credit_note_desc[credit]</strong>),<br/>bank transfer(<strong>$credit_note_desc[bank_transfer]</strong>),<br/>cheque(<strong>$credit_note_desc[cheque]</strong>)</font>";
            
            $sp_credit_note_desc = unserialize($dash_board_Data['credit_quotation_desc']);
            $SpCreditNoteDetail = "&nbsp;<font color=blue><strong>Detail: <br/></strong>cash(<strong>$sp_credit_note_desc[cash]</strong>),<br/>card(<strong>$sp_credit_note_desc[card]</strong>),<br/>credit(<strong>$sp_credit_note_desc[credit]</strong>),<br/>bank transfer(<strong>$sp_credit_note_desc[bank_transfer]</strong>),<br/>cheque(<strong>$sp_credit_note_desc[cheque]</strong>)</font>";
            
            $bulk_mobile_sale_desc = unserialize($dash_board_Data['bulk_mobile_sale_desc']);
            $BlkMobileSaleDetail = "<font color=blue>&nbsp;<strong>Detail: <br/></strong>cash(<strong>$bulk_mobile_sale_desc[cash]</strong>),<br/>card(<strong>$bulk_mobile_sale_desc[card]</strong>)</font>";
            
            $mobile_sale_desc = unserialize($dash_board_Data['mobile_sale_desc']);
            $MobileSaleDetail = "&nbsp;<font color=blue><strong>Detail: <br/></strong>cash(<strong>$mobile_sale_desc[cash]</strong>),<br/>card(<strong>$mobile_sale_desc[card]</strong>)</font>";
            
            $net_card_desc = unserialize($dash_board_Data['net_card_desc']);
            $totalNetCardDetail = $net_card_desc['repair']+$net_card_desc['Unlock']+$net_card_desc['Product']+$net_card_desc['Blk']+$net_card_desc['Mobile']+$net_card_desc['prev_recpts_sale']+$net_card_desc['credit_note'];
            if(array_key_exists('special_credit_note',$net_card_desc)){
               $sp_credt_nt =  $net_card_desc['special_credit_note'];
               $special_card = $net_card_desc['special'];
            }else{
               $sp_credt_nt = 0;
               $special_card = 0;
            }
            $NetCardDetail = "&nbsp;<font color=blue><strong>Detail: <br/></strong>repair(<strong>$net_card_desc[repair]</strong>)+<br/>unlock(<strong>$net_card_desc[Unlock]</strong>)+<br/>product(<strong>$net_card_desc[Product]</strong>)+<br/>blk(<strong>$net_card_desc[Blk]</strong>)+<br/>mobile(<strong>$net_card_desc[Mobile]</strong>)+<br/>quotation(<strong>$special_card</strong>)+<br/>prev_recpts_sale(<strong>$net_card_desc[prev_recpts_sale]</strong>)-<br/>credit note(<strong>$net_card_desc[credit_note]</strong>)-<br/>Special Credit Note (<strong>$sp_credt_nt</storng>) = $totalNetCardDetail</font>";
            
            $net_credit_desc = unserialize($dash_board_Data['net_credit_desc']);
            $TotalNetCreditDetail = $net_credit_desc[0]+$net_credit_desc[1];
           // $NetCreditDetail = "&nbsp;<font color=blue><strong>Detail: <br/></strong>($net_credit_desc[0])+<br/>($net_credit_desc[1]) = <br/>$TotalNetCreditDetail";
           $p_crdit = $product_sale_desc['credit'];
           $sp_crdit =$quotation_desc['credit'];
           $credit_note_credit = $credit_note_desc['credit'];
           $sp_credit_note_credit = $sp_credit_note_desc['credit'];
            $NetCreditDetail = "&nbsp;<font color=blue><strong>Detail: <br/></strong>(product($p_crdit)+
                                                                                 <br/>quotation($sp_crdit))-</br>
                                                                                 (credit_note($credit_note_credit)-
                                                                                 </br>special_credit_note($sp_credit_note_credit))
                                                                                 = <br/>$TotalNetCreditDetail";
            $net_bnk_tnsfer_desc = unserialize($dash_board_Data['net_bnk_tnsfer_desc']);
            $TotalNetBnkTrnsfrDetail = $net_bnk_tnsfer_desc[0]+$net_bnk_tnsfer_desc[1];
            //$NetBnkTrnsfrDetail = "&nbsp;<font color=blue><strong>Detail: <br/></strong>($net_bnk_tnsfer_desc[0])+<br/>($net_bnk_tnsfer_desc[1]) = <br/>$TotalNetBnkTrnsfrDetail";
            
            $p_bnk = $product_sale_desc['bank_transfer'];
           $sp_bnk =$quotation_desc['bank_transfer'];
           $credit_note_bnk = $credit_note_desc['bank_transfer'];
           $sp_credit_note_bnk = $sp_credit_note_desc['bank_transfer'];
            
            $NetBnkTrnsfrDetail = "&nbsp;<font color=blue><strong>Detail: <br/></strong>(product($p_bnk)+
                                                                                    <br/>quotation($sp_bnk))-
                                                                                    </br>(credit_note($credit_note_bnk)
                                                                                    -special_credit_note($sp_credit_note_bnk))
                                                                                    = <br/>$TotalNetBnkTrnsfrDetail";
            
            $net_cheque_payment_desc = unserialize($dash_board_Data['net_cheque_payment_desc']);
            $TotalNetChequePayDetail = $net_cheque_payment_desc[0]+$net_cheque_payment_desc[1];
            //$NetChequePayDetail = "&nbsp;<font color=blue><strong>Detail: <br/></strong>($net_cheque_payment_desc[0])+<br/>($net_cheque_payment_desc[1]) = <br/>$TotalNetChequePayDetail";
            
            $p_cheque = $product_sale_desc['cheque'];
           $sp_cheque =$quotation_desc['cheque'];
           $credit_note_cheque = $credit_note_desc['cheque'];
           $sp_credit_note_cheque = $sp_credit_note_desc['cheque'];
            
            $NetChequePayDetail = "&nbsp;<font color=blue><strong>Detail: <br/></strong>(product($p_cheque)+
                                                                                       <br/>quotation($sp_cheque))-</br>
                                                                                       (credit_note($credit_note_cheque)+</br>
                                                                                       special_credit_note($sp_credit_note_cheque))
                                                                                       = <br/>$TotalNetChequePayDetail";
            
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
            $norCashInHandDetail = "&nbsp;<font color=blue><strong>Detail: <br/></strong>(Sale(
            (".$cash_in_hand_desc['sale']['Repair'].")+
            <br/>(".$cash_in_hand_desc['sale']['Unlock'].")+
            <br/>(".$cash_in_hand_desc['sale']['Product'].")+
            <br/>(".$cash_in_hand_desc['sale']['Blk'].")+
            <br/>(".$cash_in_hand_desc['sale']['Mobile'].")+
            <br/>(".$cash_in_hand_desc['sale']['prv_recpit_amt'].")+
            </br>(".$special.")+
            </br>(".$prv_credit_to_cash."))= $TotalCashInHandSale,
            <br/><br/>
            Refund((".$cash_in_hand_desc['refund']['Repair'].")+
            <br/>(".$cash_in_hand_desc['refund']['Unlock'].")+
            <br/>(".$cash_in_hand_desc['refund']['Product'].")+
            <br/>(".$cash_in_hand_desc['refund']['Blk'].")+
            <br/>(".$cash_in_hand_desc['refund']['Mobile'].")+
            <br/>(".$cash_in_hand_desc['refund']['Credit_Note'].")+
            <br/>(".$cash_in_hand_desc['refund']['Mobile_Purchase'].")+
            </br>(".$special_credit_note."))=$TotalCashInHandRefund <br/>
            ($TotalCashInHandSale-$TotalCashInHandRefund) = $finalCashInHand)";
            
            $credit_to_cash_desc = unserialize($dash_board_Data['credit_to_cash_desc']);
            $CreditToCashDetail = "&nbsp;<font color=blue><strong>Detail: <br/></strong>invoice cash(<strong>$credit_to_cash_desc[invoice_cash]</strong>),<br/>Quotation cash(<strong>$credit_to_cash_desc[Quotation_cash]</strong>),<br/>credit cash(<strong>$credit_to_cash_desc[credit_cash]</strong>),<br/>credit quotation cash(<strong>$credit_to_cash_desc[credit_quotation_cash]</strong>)</font>";
            
            $credit_to_other_payment_desc = unserialize($dash_board_Data['credit_to_other_payment_desc']);
            $CreditToOtherDetail = "&nbsp;<font color=blue><strong>Detail: <br/></strong>total bank transfer(<strong>$credit_to_other_payment_desc[total_bank_transfer]</strong>),<br/>total card Payment(<strong>$credit_to_other_payment_desc[total_card_Payment]</strong>),<br/>total cheque payment(<strong>$credit_to_other_payment_desc[total_cheque_payment]</strong>)</font>";
            
                if($dash_board_Data['user_type'] == 'normal'){
                    $idd = $dash_board_Data['id'];
                    if($dash_board_Data['status'] == 1){
                     echo"<tr style=height:57px;><td><b style=color:red;>ORIGNAL : </b>$repair_sale $repairDetail</td></tr>"  ;
                    }else{
                     echo"<tr style=height:57px;><td>$repair_sale $repairDetail</td></tr>"  ; 
                    }
                    //echo"<tr style=height:57px;><td>$idd</td></tr>"  ;
                    //echo"<tr style=height:57px;><td>test</td></tr>"  ;
                    
                    echo"<tr style=height:37px;><td>$repair_refund</td></tr>"  ;
                    echo"<tr style=height:57px;><td>$unlock_sale $unlockDetail</td></tr>"  ;
                    echo"<tr style=height:37px;><td>$unlock_refund</td></tr>"  ;
                    echo"<tr style=height:110px;><td>$product_sale $productSaleDetail</td></tr>"  ;
                    if($special_user == 1){
                           echo"<tr style=height:110px;><td>$quotation</td></tr>"  ;
                    }
                    echo"<tr style=height:110px;><td>$credit_note $CreditNoteDetail</td></tr>"  ;
                    if($special_user == 1){
                           echo"<tr style=height:110px;><td>$credit_quotation</td></tr>"  ;
                    }
                    echo"<tr style=height:37px;><td>$product_refund</td></tr>"  ;
                    echo"<tr style=height:53px;><td>$bulk_mobile_sale $BlkMobileSaleDetail</td></tr>"  ;
                    echo"<tr style=height:37px;><td>$bulk_mobile_refund</td></tr>"  ;
                    echo"<tr style=height:53px;><td>$mobile_sale $MobileSaleDetail</td></tr>"  ;
                    echo"<tr style=height:37px;><td>$mobile_purchase</td></tr>"  ;
                    echo"<tr style=height:20px;><td>$mobile_refund</td></tr>"  ;
                    echo"<tr style=height:20px;><td>$total_sale</td></tr>"  ;
                    echo"<tr style=height:20px;><td>$total_refund</td></tr>"  ;
                    echo"<tr style=height:20px;><td>$net_sale</td></tr>"  ;
                    echo"<tr style=height:145px;><td>$net_card $NetCardDetail</td></tr>"  ;
                    echo"<tr style=height:69px;><td>$net_credit $NetCreditDetail</td></tr>"  ;
                    echo"<tr style=height:69px;><td>$net_bnk_tnsfer $NetBnkTrnsfrDetail</td></tr>"  ;
                    echo"<tr style=height:69px;><td>$net_cheque_payment $NetChequePayDetail</td></tr>"  ;
                    echo"<tr style=height:285px;><td>$cash_in_hand $norCashInHandDetail</td></tr>"  ;
                    echo"<tr style=height:130px;><td>$credit_to_cash $CreditToCashDetail</td></tr>"  ;
                    echo"<tr style=height:130px;><td>$credit_to_other_payment $CreditToOtherDetail</td></tr>"  ;
                    
                    if(array_key_exists('credit_to_other_payment',$dash_board_Data)){
                        echo"</table></td><td><table>";
                    }
                    
                }
                
                if($dash_board_Data['user_type'] == 'other'){
                    $idd = $dash_board_Data['id'];
                    if($dash_board_Data['status'] == 1){
                        echo"<tr style=height:57px;background:bisque;><td><b style=color:red;>ORIGNAL : </b>$repair_sale$repairDetail</td></tr>" ;
                    }else{
                     echo"<tr style=height:57px;background:bisque;><td>$repair_sale$repairDetail</td></tr>"  ;
                    //echo"<tr style=height:57px;background:bisque;><td>$idd</td></tr>"  ;
                    }
                    echo"<tr style=height:37px;background:bisque;><td>$repair_refund</td></tr>"  ;
                    echo"<tr style=height:57px;background:bisque;><td>$unlock_sale$unlockDetail</td></tr>"  ;
                    echo"<tr style=height:37px;background:bisque;><td>$unlock_refund</td></tr>"  ;
                    echo"<tr style=height:110px;background:bisque;><td>$product_sale $productSaleDetail</td></tr>"  ;
                    echo"<tr style=height:110px;background:bisque;><td>$quotation $QuotationDetail</td></tr>"  ;
                    echo"<tr style=height:110px;background:bisque;><td>$credit_note $CreditNoteDetail</td></tr>"  ;
                    echo"<tr style=height:110px;background:bisque;><td>$credit_quotation $SpCreditNoteDetail</td></tr>"  ;
                    echo"<tr style=height:37px;background:bisque;><td>$product_refund</td></tr>"  ;
                    echo"<tr style=height:53px;background:bisque;><td>$bulk_mobile_sale $BlkMobileSaleDetail</td></tr>"  ;
                    echo"<tr style=height:37px;background:bisque;><td>$bulk_mobile_refund</td></tr>"  ;
                    echo"<tr style=height:53px;background:bisque;><td>$mobile_sale $MobileSaleDetail</td></tr>"  ;
                    echo"<tr style=height:37px;background:bisque;><td>$mobile_purchase</td></tr>"  ;
                    echo"<tr style=height:20px;background:bisque;><td>$mobile_refund</td></tr>";
                    echo"<tr style=height:20px;background:bisque;><td>$total_sale</td></tr>";
                    echo"<tr style=height:20px;background:bisque;><td>$total_refund</td></tr>";
                    echo"<tr style=height:20px;background:bisque;><td>$net_sale</td></tr>"  ;
                    echo"<tr style=height:145px;background:bisque;><td>$net_card $NetCardDetail</td></tr>"  ;
                    echo"<tr style=height:69px;background:bisque;><td>$net_credit $NetCreditDetail</td></tr>"  ;
                    echo"<tr style=height:69px;background:bisque;><td>$net_bnk_tnsfer $NetBnkTrnsfrDetail</td></tr>"  ;
                    echo"<tr style=height:69px;background:bisque;><td>$net_cheque_payment $NetChequePayDetail</td></tr>"  ;
                    echo"<tr style=height:285px;background:bisque;><td>$cash_in_hand $norCashInHandDetail</td></tr>"  ;
                    echo"<tr style=height:130px;background:bisque;><td>$credit_to_cash $CreditToCashDetail</td></tr>"  ;
                    echo"<tr style=height:130px;background:bisque;><td>$credit_to_other_payment $CreditToOtherDetail</td></tr>"  ;
                    
                    if(array_key_exists('credit_to_other_payment',$dash_board_Data)){
                        echo"</table></td><td><table>";
                    }
                }
            
        }
        
        if(!empty($normalUserDataSum['repair_sale_desc'])){
            $repair_sale_desc_cash = $normalUserDataSum['repair_sale_desc']['cash'];
            $repair_sale_desc_card = $normalUserDataSum['repair_sale_desc']['card'];
            $repairDetail = "&nbsp;<font color=blue><strong>Detail: <br/></strong>cash(<strong>$repair_sale_desc_cash</strong>),<br/>card(<strong>$repair_sale_desc_card</strong>)</font>";
            
            $t_repair_sale_desc_cash = $otherUserDataSum['repair_sale_desc']['cash'];
            $t_repair_sale_desc_card = $otherUserDataSum['repair_sale_desc']['card'];
            $t_repairDetail = "&nbsp;<font color=blue><strong>Detail: <br/></strong>cash(<strong>$t_repair_sale_desc_cash</strong>),<br/>card(<strong>$t_repair_sale_desc_card</strong>)</font>";    
        }else{
            $repairDetail = $t_repairDetail = "";
        }
        
        if(!empty($normalUserDataSum['unlock_sale_desc'])){
            $unlock_sale_desc_cash = $normalUserDataSum['unlock_sale_desc']['cash'];
            $unlock_sale_desc_card = $normalUserDataSum['unlock_sale_desc']['card'];
            $unlockDetail = "&nbsp;<font color=blue><strong>Detail: <br/></strong>cash(<strong>$unlock_sale_desc_cash</strong>),<br/>card(<strong>$unlock_sale_desc_card</strong>)</font>";
        
            $t_unlock_sale_desc_cash = $otherUserDataSum['unlock_sale_desc']['cash'];
            $t_unlock_sale_desc_card = $otherUserDataSum['unlock_sale_desc']['card'];
            $t_unlockDetail = "&nbsp;<font color=blue><strong>Detail: <br/></strong>cash(<strong>$t_unlock_sale_desc_cash</strong>),<br/>card(<strong>$t_unlock_sale_desc_card</strong>)</font>";
        }else{
            $unlockDetail = $t_unlockDetail = "";
        }
        
        
        if(!empty($normalUserDataSum['product_sale_desc'])){
            $product_sale_desc_cash = $normalUserDataSum['product_sale_desc']['cash'];
            $product_sale_desc_card = $normalUserDataSum['product_sale_desc']['card'];
            $product_sale_desc_credit = $normalUserDataSum['product_sale_desc']['credit'];
            $product_sale_desc_bank_transfer = $normalUserDataSum['product_sale_desc']['bank_transfer'];
            $product_sale_desc_cheque= $normalUserDataSum['product_sale_desc']['cheque'];
            
            $productSaleDetail = "&nbsp;<font color=blue><strong>Detail: <br/></strong>cash(<strong>$product_sale_desc_cash</strong>),<br/>card(<strong>$product_sale_desc_card</strong>),<br/>credit(<strong>$product_sale_desc_credit</strong>),<br/>bank transfer(<strong>$product_sale_desc_bank_transfer</strong>),<br/>cheque(<strong>$product_sale_desc_cheque</strong>)</font>";
            
            $t_product_sale_desc_cash = $otherUserDataSum['product_sale_desc']['cash'];
            $t_product_sale_desc_card = $otherUserDataSum['product_sale_desc']['card'];
            $t_product_sale_desc_credit = $otherUserDataSum['product_sale_desc']['credit'];
            $t_product_sale_desc_bank_transfer = $otherUserDataSum['product_sale_desc']['bank_transfer'];
            $t_product_sale_desc_cheque= $otherUserDataSum['product_sale_desc']['cheque'];
            $t_productSaleDetail = "&nbsp;<font color=blue><strong>Detail: <br/></strong>cash(<strong>$t_product_sale_desc_cash</strong>),<br/>card(<strong>$t_product_sale_desc_card</strong>),<br/>credit(<strong>$t_product_sale_desc_credit</strong>),<br/>bank transfer(<strong>$t_product_sale_desc_bank_transfer</strong>),<br/>cheque(<strong>$t_product_sale_desc_cheque</strong>)</font>";
        }else{
            $t_productSaleDetail = $productSaleDetail = "";
        }
        
        if(!empty($normalUserDataSum['quotation_desc'])){
            $quotation_cash = $otherUserDataSum['quotation_desc']['cash'];
            $quotation_card = $otherUserDataSum['quotation_desc']['card'];
            $quotation_credit = $otherUserDataSum['quotation_desc']['credit'];
            $quotation_bank_transfer = $otherUserDataSum['quotation_desc']['bank_transfer'];
            $quotation_cheque= $otherUserDataSum['quotation_desc']['cheque'];
            $quotation_Detail = "&nbsp;<font color=blue><strong>Detail: <br/></strong>cash(<strong>$quotation_cash</strong>),<br/>card(<strong>$quotation_card</strong>),<br/>credit(<strong>$quotation_credit</strong>),<br/>bank transfer(<strong>$quotation_bank_transfer</strong>),<br/>cheque(<strong>$quotation_cheque</strong>)</font>";    
        }else{
            $quotation_Detail = "";
        }
        
        
        if(!empty($normalUserDataSum['credit_note_desc'])){
            $credit_note_desc_cash = $normalUserDataSum['credit_note_desc']['cash'];
            $credit_note_desc_card = $normalUserDataSum['credit_note_desc']['card'];
            $credit_note_desc_credit = $normalUserDataSum['credit_note_desc']['credit'];
            $credit_note_desc_bank_transfer = $normalUserDataSum['credit_note_desc']['bank_transfer'];
            $credit_note_desc_cheque= $normalUserDataSum['credit_note_desc']['cheque'];
            $credit_note_desc_Detail = "&nbsp;<font color=blue><strong>Detail: <br/></strong>cash(<strong>$credit_note_desc_cash</strong>),<br/>card(<strong>$credit_note_desc_card</strong>),<br/>credit(<strong>$credit_note_desc_credit</strong>),<br/>bank transfer(<strong>$credit_note_desc_bank_transfer</strong>),<br/>cheque(<strong>$credit_note_desc_cheque</strong>)</font>";
            
            $t_credit_note_desc_cash = $otherUserDataSum['credit_quotation_desc']['cash'];
            $t_credit_note_desc_card = $otherUserDataSum['credit_quotation_desc']['card'];
            $t_credit_note_desc_credit = $otherUserDataSum['credit_quotation_desc']['credit'];
            $t_credit_note_desc_bank_transfer = $otherUserDataSum['credit_quotation_desc']['bank_transfer'];
            $t_credit_note_desc_cheque= $otherUserDataSum['credit_quotation_desc']['cheque'];
            $t_credit_note_desc_Detail = "&nbsp;<font color=blue><strong>Detail: <br/></strong>cash(<strong>$t_credit_note_desc_cash</strong>),<br/>card(<strong>$t_credit_note_desc_card</strong>),<br/>credit(<strong>$t_credit_note_desc_credit</strong>),<br/>bank transfer(<strong>$t_credit_note_desc_bank_transfer</strong>),<br/>cheque(<strong>$t_credit_note_desc_cheque</strong>)</font>";    
        }else{
            $credit_note_desc_Detail = $t_credit_note_desc_Detail = "";
        }
        
        
        if(!empty($normalUserDataSum['bulk_mobile_sale_desc'])){
            $bulk_mobile_sale_desc_cash = $normalUserDataSum['bulk_mobile_sale_desc']['cash'];
            $bulk_mobile_sale_desc_card = $normalUserDataSum['bulk_mobile_sale_desc']['card'];
            $bulk_mobile_sale_descDetail = "&nbsp;<font color=blue><strong>Detail: <br/></strong>cash(<strong>$bulk_mobile_sale_desc_cash</strong>),<br/>card(<strong>$bulk_mobile_sale_desc_card</strong>)</font>";    
        }else{
            $bulk_mobile_sale_descDetail = "";
        }
        
        if(!empty($normalUserDataSum['bulk_mobile_sale_desc'])){
            $mobile_sale_desc_cash = $normalUserDataSum['bulk_mobile_sale_desc']['cash'];
            $mobile_sale_desc_card = $normalUserDataSum['bulk_mobile_sale_desc']['card'];
            $mobile_sale_descDetail = "&nbsp;<font color=blue><strong>Detail:<br/> </strong>cash(<strong>$mobile_sale_desc_cash</strong>),<br/>card(<strong>$mobile_sale_desc_card</strong>)</font>";    
        }else{
            $mobile_sale_descDetail = "";
        }
        
        if(!empty($normalUserDataSum['net_card_desc'])){
            $net_card_desc_repair = $normalUserDataSum['net_card_desc']['repair'];
            $net_card_desc_Unlock = $normalUserDataSum['net_card_desc']['Unlock'];
            $net_card_desc_Product = $normalUserDataSum['net_card_desc']['Product'];
            $net_card_desc_Blk = $normalUserDataSum['net_card_desc']['Blk'];
            $net_card_desc_Mobile = $normalUserDataSum['net_card_desc']['Mobile'];
            $net_card_desc_credit_note = $normalUserDataSum['net_card_desc']['credit_note'];
            
            $net_card_desc = "&nbsp;<font color=blue><strong>Detail: <br/></strong>Repair(<strong>$net_card_desc_repair</strong>)+
                                                                     <br/>Unlock(<strong>$net_card_desc_Unlock</strong>)+
                                                                     <br/>Product(<strong>$net_card_desc_Product</strong>)+
                                                                     <br/>Blk(<strong>$net_card_desc_Blk</strong>)+
                                                                     <br/>Mobile(<strong>$net_card_desc_Mobile</strong>)+
                                                                     <br/>prev_recpts_sale(<strong>$net_card_desc_Mobile</strong>)-
                                                                     <br/>Credit note(<strong>$net_card_desc_credit_note</strong>)
                                                                     
                                                                     </font>";    
        }else{
            $net_card_desc = "";
        }
        
        if(!empty($otherUserDataSum['net_card_desc'])){
            $t_net_card_desc_repair = $otherUserDataSum['net_card_desc']['repair'];
            $t_net_card_desc_Unlock = $otherUserDataSum['net_card_desc']['Unlock'];
            $t_net_card_desc_Product = $otherUserDataSum['net_card_desc']['Product'];
            $t_net_card_desc_Blk = $otherUserDataSum['net_card_desc']['Blk'];
            $t_net_card_desc_Mobile = $otherUserDataSum['net_card_desc']['Mobile'];
            $t_net_card_desc_credit_note = $otherUserDataSum['net_card_desc']['credit_note'];
            $t_prev_recpts_sale = $otherUserDataSum['net_card_desc']['prev_recpts_sale'];
            $t_quotation = $otherUserDataSum['net_card_desc']['special'];
            $t_special_credit_note = $otherUserDataSum['net_card_desc']['special_credit_note'];
            $t_net_card_desc = "&nbsp;<font color=blue><strong>Detail: <br/></strong>Repair(<strong>$t_net_card_desc_repair</strong>)+
                                                                                 <br/>Unlock(<strong>$t_net_card_desc_Unlock</strong>)+
                                                                                 <br/>Product(<strong>$t_net_card_desc_Product</strong>)+
                                                                                 <br/>Blk(<strong>$t_net_card_desc_Blk</strong>)+
                                                                                 <br/>Mobile(<strong>$t_net_card_desc_Mobile</strong>+
                                                                                 <br/>Quotation(<strong>$t_quotation</strong>+
                                                                           <br/>prev_recpts_sale(<strong>$t_prev_recpts_sale</strong>)-
                                                               <br/>Credit note(<strong>$t_net_card_desc_credit_note</strong>)-
                                                               <br/>Special Credit note(<strong>$t_special_credit_note</strong>)
                                                               </font>";    
        }else{
            $t_net_card_desc = "";
        }
        
        if(!empty($normalUserDataSum['net_bnk_tnsfer_desc'])){
            $net_bank_desc_1 = $normalUserDataSum['net_bnk_tnsfer_desc'][0];
            $net_bank_desc_2 = $normalUserDataSum['net_bnk_tnsfer_desc'][1];
            $net_bank_desc = "&nbsp;<font color=blue><strong>Detail: <br/></strong>(<strong>$net_bank_desc_1</strong>),<br/>(<strong>$net_bank_desc_2</strong>)</font>";    
        }else{
            $net_bank_desc = "";
        }
        if(isset($product_sale_desc_credit)){
         
            $t_net_credit_dec = "&nbsp;<font color=blue><strong>Detail: <br/></strong>(
                                                                                       Product(<strong>$product_sale_desc_credit</strong>)+
                                                                                       <br/>Quotation(<strong>$quotation_credit</strong>
                                                                           )-</br>(credit note(<strong>$credit_note_desc_credit</strong>)+
                                                                           (<br/>special credit note<strong>$t_credit_note_desc_credit</strong>
                                                                                       ))</font>";
                                                                                       
            $net_credit_dec = "&nbsp;<font color=blue><strong>Detail: <br/></strong>(
                                                                                       Product(<strong>$product_sale_desc_credit</strong>)+
                                                                                       <br/>Quotation(<strong>0</strong>
                                                                           )-</br>(credit note(<strong>$product_sale_desc_credit</strong>)+
                                                                           (<br/>special credit note<strong>0</strong>
                                                                                       ))</font>";
        }
        if(isset($product_sale_desc_bank_transfer)){
            $t_net_bnk_dec = "&nbsp;<font color=blue><strong>Detail: <br/></strong>(
                                                                                       Product(<strong>$product_sale_desc_bank_transfer</strong>)+
                                                                                       <br/>Quotation(<strong>$quotation_bank_transfer</strong>
                                                                           )-</br>(credit note(<strong>$credit_note_desc_bank_transfer</strong>)+
                                                                           (<br/>special credit note<strong>$t_credit_note_desc_bank_transfer</strong>
                                                                                       ))</font>";
                                                                                       
            $net_bnk_dec = "&nbsp;<font color=blue><strong>Detail: <br/></strong>(
                                                                                       Product(<strong>$product_sale_desc_bank_transfer</strong>)+
                                                                                       <br/>Quotation(<strong>0</strong>
                                                                           )-</br>(credit note(<strong>$credit_note_desc_bank_transfer</strong>)+
                                                                           (<br/>special credit note<strong>0</strong>
                                                                                       ))</font>";
                                                                                       
        }
        if(isset($product_sale_desc_cheque)){
             $t_net_cheque_dec = "&nbsp;<font color=blue><strong>Detail: <br/></strong>(
                                                                                       Product(<strong>$product_sale_desc_cheque</strong>)+
                                                                                       <br/>Quotation(<strong>$quotation_cheque</strong>
                                                                           )-</br>(credit note(<strong>$credit_note_desc_cheque</strong>)+
                                                                           (<br/>special credit note<strong>$t_credit_note_desc_cheque</strong>
                                                                                       ))</font>";
                                                                                       
            $net_cheque_dec = "&nbsp;<font color=blue><strong>Detail: <br/></strong>(
                                                                                       Product(<strong>$product_sale_desc_cheque</strong>)+
                                                                                       <br/>Quotation(<strong>0</strong>
                                                                           )-</br>(credit note(<strong>$credit_note_desc_cheque</strong>)+
                                                                           (<br/>special credit note<strong>0</strong>
                                                                                       ))</font>";                                                                           
            
        }
        if(!empty($otherUserDataSum['net_bnk_tnsfer_desc'])){
            $t_net_bank_desc_1 = $otherUserDataSum['net_bnk_tnsfer_desc'][0];
            $t_net_bank_desc_2 = $otherUserDataSum['net_bnk_tnsfer_desc'][1];
            $t_net_bank_desc = "&nbsp;<font color=blue><strong>Detail: <br/></strong>(<strong>$net_bank_desc_1</strong>),<br/>(<strong>$net_bank_desc_2</strong>)</font>";    
        }else{
            $t_net_bank_desc = "";
        }
        
        if($normalUserDataSum['net_cheque_payment_desc']){
            $net_cheque_desc_1 = $normalUserDataSum['net_cheque_payment_desc'][0];
            $net_cheque_desc_2 = $normalUserDataSum['net_cheque_payment_desc'][1];
            $net_cheque_desc = "&nbsp;<font color=blue><strong>Detail: <br/></strong>(<strong>$net_cheque_desc_1</strong>),<br/>(<strong>$net_cheque_desc_2</strong>)</font>";
            
            $t_net_cheque_desc_1 = $otherUserDataSum['net_cheque_payment_desc'][0];
            $t_net_cheque_desc_2 = $otherUserDataSum['net_cheque_payment_desc'][1];
            $t_net_cheque_desc = "&nbsp;<font color=blue><strong>Detail: <br/></strong>(<strong>$t_net_cheque_desc_1</strong>),<br/>(<strong>$t_net_cheque_desc_2</strong>)</font>";    
        }else{
           $net_cheque_desc =  $t_net_cheque_desc= "";
        }
        
        
        if(!empty($normalUserDataSum['cash_in_hand_desc'])){
         //pr($normalUserDataSum['cash_in_hand_desc']);
                $norTotalCashInHandSale = $normalUserDataSum['cash_in_hand_desc']['sale']['Repair']+$normalUserDataSum['cash_in_hand_desc']['sale']['Unlock']+$normalUserDataSum['cash_in_hand_desc']['sale']['Product']+$normalUserDataSum['cash_in_hand_desc']['sale']['Blk']+$normalUserDataSum['cash_in_hand_desc']['sale']['Mobile'];//+$normalUserDataSum['cash_in_hand_desc']['sale']['prv_recpit_amt'];
            
            $norTotalCashInHandRefund = $normalUserDataSum['cash_in_hand_desc']['refund']['Repair']+$normalUserDataSum['cash_in_hand_desc']['refund']['Unlock']+$normalUserDataSum['cash_in_hand_desc']['refund']['Product']+$normalUserDataSum['cash_in_hand_desc']['refund']['Blk']+$normalUserDataSum['cash_in_hand_desc']['refund']['Mobile']+$normalUserDataSum['cash_in_hand_desc']['refund']['Credit_Note']+$normalUserDataSum['cash_in_hand_desc']['refund']['Mobile_Purchase'];
            
            $norfinalCashInHand = $norTotalCashInHandSale - $norTotalCashInHandRefund;
            
            $norCashInHandDetail = "&nbsp;<font color=blue><strong>Detail:
            </strong></br>(Sale((".$normalUserDataSum['cash_in_hand_desc']['sale']['Repair'].")+
            <br>(".$normalUserDataSum['cash_in_hand_desc']['sale']['Unlock'].")+
            <br>(".$normalUserDataSum['cash_in_hand_desc']['sale']['Product'].")+
            <br>(".$normalUserDataSum['cash_in_hand_desc']['sale']['Blk'].")+
            <br/>(".$normalUserDataSum['cash_in_hand_desc']['sale']['Mobile'].")
            <br/>(".$normalUserDataSum['cash_in_hand_desc']['sale']['prv_recpit_amt'].")
            <br/>(0)
            <br/>(0)
            )=
            $norTotalCashInHandSale,<br><br>
            Refund((".$normalUserDataSum['cash_in_hand_desc']['refund']['Repair'].")
            +<br>(".$normalUserDataSum['cash_in_hand_desc']['refund']['Unlock'].")+
            <br>(".$normalUserDataSum['cash_in_hand_desc']['refund']['Product'].")+
            <br> (".$normalUserDataSum['cash_in_hand_desc']['refund']['Blk'].")+
            <br>(".$normalUserDataSum['cash_in_hand_desc']['refund']['Mobile'].")+
            <br/>(".$normalUserDataSum['cash_in_hand_desc']['refund']['Credit_Note'].")+
            <br> (".$normalUserDataSum['cash_in_hand_desc']['refund']['Mobile_Purchase'].")+
            <br>(0)
            )
            ($norTotalCashInHandSale-$norTotalCashInHandRefund) <br>= $norfinalCashInHand)";    
        }else{
            $norCashInHandDetail = "";
        }
        
        if(!empty($otherUserDataSum['cash_in_hand_desc'])){
            $othTotalCashInHandSale = $otherUserDataSum['cash_in_hand_desc']['sale']['Repair']+$otherUserDataSum['cash_in_hand_desc']['sale']['Unlock']+$otherUserDataSum['cash_in_hand_desc']['sale']['Product']+$otherUserDataSum['cash_in_hand_desc']['sale']['Blk']+$otherUserDataSum['cash_in_hand_desc']['sale']['Mobile']+$otherUserDataSum['cash_in_hand_desc']['sale']['special']+$otherUserDataSum['cash_in_hand_desc']['sale']['prv_recpit_amt']+$otherUserDataSum['cash_in_hand_desc']['sale']['special'];
        
        $othTotalCashInHandRefund = $otherUserDataSum['cash_in_hand_desc']['refund']['Repair']+$otherUserDataSum['cash_in_hand_desc']['refund']['Unlock']+$otherUserDataSum['cash_in_hand_desc']['refund']['Product']+$otherUserDataSum['cash_in_hand_desc']['refund']['Blk']+$otherUserDataSum['cash_in_hand_desc']['refund']['Mobile']+$otherUserDataSum['cash_in_hand_desc']['refund']['special_credit_note'];
        
        $othfinalCashInHand = $othTotalCashInHandSale - $othTotalCashInHandRefund;
        
        $othCashInHandDetail = "&nbsp;<font color=blue><strong>Detail: </strong></br>(Sale((".$otherUserDataSum['cash_in_hand_desc']['sale']['Repair'].")<br>+(".$otherUserDataSum['cash_in_hand_desc']['sale']['Unlock'].")<br>+(".$otherUserDataSum['cash_in_hand_desc']['sale']['Product'].")<br>+(".$otherUserDataSum['cash_in_hand_desc']['sale']['Blk'].")<br>+(".$otherUserDataSum['cash_in_hand_desc']['sale']['Mobile'].")<br>+(".$otherUserDataSum['cash_in_hand_desc']['sale']['prv_recpit_amt'].")<br>+(".$otherUserDataSum['cash_in_hand_desc']['sale']['special'].")<br>+(".$otherUserDataSum['cash_in_hand_desc']['sale']['prv_credit_to_cash'].")= $othTotalCashInHandSale, <br><br>Refund((".$otherUserDataSum['cash_in_hand_desc']['refund']['Repair'].")<br>+(".$otherUserDataSum['cash_in_hand_desc']['refund']['Unlock'].")<br>+(".$otherUserDataSum['cash_in_hand_desc']['refund']['Product'].")<br>+(".$otherUserDataSum['cash_in_hand_desc']['refund']['Blk'].")<br>+(".$otherUserDataSum['cash_in_hand_desc']['refund']['Mobile'].")</br>+(".$otherUserDataSum['cash_in_hand_desc']['refund']['Credit_Note'].")<br>+(".$otherUserDataSum['cash_in_hand_desc']['refund']['Mobile_Purchase'].")<br>+(".$otherUserDataSum['cash_in_hand_desc']['refund']['special_credit_note']."))<br> ($othTotalCashInHandSale-$othTotalCashInHandRefund) = $othfinalCashInHand)";    
        }else{
           $othCashInHandDetail = "";
        }
        
        
        
        //pr($otherUserDataSum);die;
        if(!empty($otherUserDataSum['credit_to_cash_desc'])){
            $t_credit_to_cash_invoiceCash = $otherUserDataSum['credit_to_cash_desc']['invoice_cash'];
            $t_credit_to_cash_quotationCash = $otherUserDataSum['credit_to_cash_desc']['Quotation_cash'];
            $t_credit_to_cash_creditCash = $otherUserDataSum['credit_to_cash_desc']['credit_cash'];
            $t_credit_to_cash_CreditQuotationCash = $otherUserDataSum['credit_to_cash_desc']['credit_quotation_cash'];
            $t_CreditToCashDetail = "&nbsp;<font color=blue><strong>Detail: <br/></strong>invoice cash(<strong>$t_credit_to_cash_invoiceCash</strong>),<br/>Quotation cash(<strong>$t_credit_to_cash_quotationCash</strong>),<br/>credit cash(<strong>$t_credit_to_cash_creditCash</strong>),<br/>credit quotation cash(<strong>$t_credit_to_cash_CreditQuotationCash</strong>)</font>";    
        }else{
            $t_CreditToCashDetail = "";
        }
        
        if(!empty($normalUserDataSum['credit_to_cash_desc'])){
            $credit_to_cash_invoiceCash = $normalUserDataSum['credit_to_cash_desc']['invoice_cash'];
            $credit_to_cash_quotationCash = $normalUserDataSum['credit_to_cash_desc']['Quotation_cash'];
            $credit_to_cash_creditCash = $normalUserDataSum['credit_to_cash_desc']['credit_cash'];
            $credit_to_cash_CreditQuotationCash = $normalUserDataSum['credit_to_cash_desc']['credit_quotation_cash'];
            $CreditToCashDetail = "&nbsp;<font color=blue><strong>Detail: <br/></strong>invoice cash(<strong>$credit_to_cash_invoiceCash</strong>),<br/>Quotation cash(<strong>$credit_to_cash_quotationCash</strong>),<br/>credit cash(<strong>$credit_to_cash_creditCash</strong>),<br/>credit quotation cash(<strong>$credit_to_cash_CreditQuotationCash</strong>)</font>";    
        }else{
            $CreditToCashDetail = "";
        }
        
        if(!empty($normalUserDataSum['credit_to_other_payment_desc'])){
            $credit_to_other_bank_transfer = $normalUserDataSum['credit_to_other_payment_desc']['total_bank_transfer'];
            $credit_to_other_card_Payment = $normalUserDataSum['credit_to_other_payment_desc']['total_card_Payment'];
            $credit_to_other_cheque_payment = $normalUserDataSum['credit_to_other_payment_desc']['total_cheque_payment'];
            
            $CreditToOtherDetail = "&nbsp;<font color=blue><strong>Detail: <br/></strong>total bank transfer(<strong>$credit_to_other_bank_transfer</strong>),<br/>total card Payment(<strong>$credit_to_other_card_Payment</strong>),<br/>total cheque payment(<strong>$credit_to_other_cheque_payment</strong>)</font>";    
        }else{
            $CreditToOtherDetail = "";
        }
        
        if(!empty($otherUserDataSum['credit_to_other_payment_desc'])){
            $t_credit_to_other_bank_transfer = $otherUserDataSum['credit_to_other_payment_desc']['total_bank_transfer'];
            $t_credit_to_other_card_Payment = $otherUserDataSum['credit_to_other_payment_desc']['total_card_Payment'];
            $t_credit_to_other_cheque_payment = $otherUserDataSum['credit_to_other_payment_desc']['total_cheque_payment'];
             $t_CreditToOtherDetail = "&nbsp;<font color=blue><strong>Detail: <br/></strong>total bank transfer(<strong>$t_credit_to_other_bank_transfer</strong>),<br/>total card Payment(<strong>$t_credit_to_other_card_Payment</strong>),<br/>total cheque payment(<strong>$t_credit_to_other_cheque_payment</strong>)</font>";
        }else{
            $t_CreditToOtherDetail = "";
        }
        ?>
<?php if($selectedDate != $selectedEndDate){ ?>
        <table style="margin-top: -42px;">
            <tr>
                <th >Total Normal</th>
                <?php if($special_user){ ?>
                <th >Total Other</th>
                <?php }?>
            </tr>
            <tr style=height:57px;>
            <td style="background-color: lawngreen;"><?php echo $normalUserDataSum['repair_sale'].$repairDetail; ?></td>
            <?php if($special_user){?>
                  <td style="background: #e6fb21;"><?php echo $otherUserDataSum['repair_sale'].$t_repairDetail; ?></td>
            <?php  }?>
            </tr>
            <tr style="height:37px;color: blue">
            <td style="background-color: lawngreen;"><?php echo $normalUserDataSum['repair_refund']; ?></td>
            <?php if($special_user){?>
                  <td style="background: #e6fb21;"><?php echo $otherUserDataSum['repair_refund']; ?></td>
            <?php }?>
            </tr>
            <tr style=height:57px;>
            <td style="background-color: lawngreen;"><?php echo $normalUserDataSum['unlock_sale'].$unlockDetail; ?></td>
            <?php if($special_user){?>
                  <td style="background: #e6fb21;"><?php echo $otherUserDataSum['unlock_sale'].$t_unlockDetail; ?></td>
            <?php } ?>
            </tr>
            <tr style="height:37px;color: blue">
            <td style="background-color: lawngreen;"><?php echo $normalUserDataSum['unlock_refund']; ?></td>
            <?php if($special_user){?>
                  <td style="background: #e6fb21;"><?php echo $otherUserDataSum['unlock_refund']; ?></td>
            <?php }?>
            </tr>
            <tr style=height:110px;>
            <td style="background-color: lawngreen;"><?php echo $normalUserDataSum['product_sale'].$productSaleDetail; ?></td>
            <?php if($special_user){ ?>
               <td style="background: #e6fb21;"><?php echo $otherUserDataSum['product_sale'].$t_productSaleDetail; ?></td>
            <?php }?>
            </tr>
             <?php if($special_user){ ?>
             <tr style=height:110px;>
            <td style="background-color: lawngreen;"><?php echo $normalUserDataSum['quotation']; ?></td>
               <td style="background: #e6fb21;"><?php echo $otherUserDataSum['quotation'].$quotation_Detail; ?></td>
             </tr>
             <?php }?>
            <tr style="height:110px;color: blue">
            <td style="background-color: lawngreen;"><?php echo $normalUserDataSum['credit_note'].$credit_note_desc_Detail; ?></td>
            <?php if($special_user){ ?>
               <td style="background: #e6fb21;"><?php echo $otherUserDataSum['credit_note'].$credit_note_desc_Detail; ?></td>
            <?php }?>
            </tr>
            <?php if($special_user){ ?>
               <tr style=height:115px;>
                <td style="background-color: lawngreen;"><?php echo $normalUserDataSum['credit_quotation']; ?></td>
                <td style="background: #e6fb21;"><?php echo $otherUserDataSum['credit_quotation'].$t_credit_note_desc_Detail ?></td>
            </tr>
               <?php }?>
            <tr style=height:37px;>
                <td style="background-color: lawngreen;"><?php echo $normalUserDataSum['product_refund']; ?></td>
                <?php if($special_user){ ?>
                <td style="background: #e6fb21;"><?php echo $otherUserDataSum['product_refund']; ?></td>
                <?php }?>
            </tr>
            <tr style=height:53px;>
                <td style="background-color: lawngreen;"><?php echo $normalUserDataSum['bulk_mobile_sale'].$bulk_mobile_sale_descDetail; ?></td>
                <?php if($special_user){ ?>
                <td style="background: #e6fb21;"><?php echo $otherUserDataSum['bulk_mobile_sale'].$bulk_mobile_sale_descDetail; ?></td>
                <?php }?>
            </tr>
            <tr style=height:37px;>
                <td style="background-color: lawngreen;"><?php echo $normalUserDataSum['bulk_mobile_refund']; ?></td>
                <?php if($special_user){ ?>
                  <td style="background: #e6fb21;"><?php echo $otherUserDataSum['bulk_mobile_refund']; ?></td>
               <?php }?>
            </tr>
            <tr style=height:53px;>
                <td style="background-color: lawngreen;"><?php echo $normalUserDataSum['mobile_sale'].$mobile_sale_descDetail; ?></td>
                <?php if($special_user){ ?>
                     <td style="background: #e6fb21;"><?php echo $otherUserDataSum['mobile_sale'].$mobile_sale_descDetail; ?></td>
                <?php }?>
            </tr>
            <tr style=height:37px;>
                <td style="background-color: lawngreen;"><?php echo $normalUserDataSum['mobile_purchase']; ?></td>
                <?php if($special_user){ ?>
                     <td style="background: #e6fb21;"><?php echo $otherUserDataSum['mobile_purchase']; ?></td>
                <?php } ?>
            </tr>
            <tr style=height:20px;>
                <td style="background-color: lawngreen;"><?php echo $normalUserDataSum['mobile_refund']; ?></td>
                <?php if($special_user){ ?>
                     <td style="background: #e6fb21;"><?php echo $otherUserDataSum['mobile_refund']; ?></td>
                <?php }?>
            </tr>
            <tr style=height:20px;>
                <td style="background-color: lawngreen;"><?php echo $normalUserDataSum['total_sale']; ?></td>
                <?php if($special_user){ ?>
                     <td style="background: #e6fb21;"><?php echo $otherUserDataSum['total_sale']; ?></td>
                <?php }?>
            </tr>
            <tr style=height:20px;>
                <td style="background-color: lawngreen;"><?php echo $normalUserDataSum['total_refund']; ?></td>
                <?php if($special_user){ ?>
                     <td style="background: #e6fb21;"><?php echo $otherUserDataSum['total_refund']; ?></td>
                <?php } ?>
            </tr>
            <tr style=height:20px;>
                <td style="background-color: lawngreen;"><?php echo $normalUserDataSum['net_sale']; ?></td>
                <?php if($special_user){ ?>
                     <td style="background: #e6fb21;"><?php echo $otherUserDataSum['net_sale']; ?></td>
                <?php }?>
            </tr>
            <tr style=height:163px;>
                <td style="background-color: lawngreen;"><?php echo $normalUserDataSum['net_card'].$net_card_desc; ?></td>
                <?php if($special_user){ ?>
                     <td style="background: #e6fb21;"><?php echo $otherUserDataSum['net_card'].$t_net_card_desc; ?></td>
                <?php }?>
            </tr>
            <tr style=height:118px;>
                <td style="background-color: lawngreen;"><?php echo $normalUserDataSum['net_credit'].$net_credit_dec; ?></td>
                <?php if($special_user){ ?>
                     <td style="background: #e6fb21;"><?php echo $otherUserDataSum['net_credit'].$t_net_credit_dec; ?></td>
                <?php }?>
            </tr>
            <tr style=height:118px;>
                <td style="background-color: lawngreen;"><?php echo $normalUserDataSum['net_bnk_tnsfer'].$net_bnk_dec; ?></td>
                <?php if($special_user){ ?>
                     <td style="background: #e6fb21;"><?php echo $otherUserDataSum['net_bnk_tnsfer'].$t_net_bnk_dec; ?></td>
                <?php }?>
            </tr>
            <tr style=height:118px;>
                <td style="background-color: lawngreen;"><?php echo $normalUserDataSum['net_cheque_payment'].$net_cheque_dec; ?></td>
                <?php if($special_user){ ?>
                     <td style="background: #e6fb21;"><?php echo $otherUserDataSum['net_cheque_payment'].$t_net_cheque_dec; ?></td>
                <?php }?>
            </tr>
            <tr style=height:285px;>
                <td style="background-color: lawngreen;"><?php echo $normalUserDataSum['cash_in_hand'].$norCashInHandDetail; ?></td>
                <?php if($special_user){ ?>
                     <td style="background: #e6fb21;"><?php echo $otherUserDataSum['cash_in_hand'].$othCashInHandDetail; ?></td>
                <?php }?>
            </tr>
            <tr style=height:130px;>
                <td style="background-color: lawngreen;"><?php echo $normalUserDataSum['credit_to_cash'].$CreditToCashDetail; ?></td>
                <?php if($special_user){ ?>
                     <td style="background: #e6fb21;"><?php echo $otherUserDataSum['credit_to_cash'].$t_CreditToCashDetail; ?></td>
                <?php }?>
            </tr>
            <tr style=height:130px;>
                <td style="background-color: lawngreen;"><?php echo $normalUserDataSum['credit_to_other_payment'].$CreditToOtherDetail; ?></td>
                <?php if($special_user){ ?>
                <td style="background: #e6fb21;"><?php echo $otherUserDataSum['credit_to_other_payment'].$t_CreditToOtherDetail; ?></td>
                <?php } ?>
            </tr>
                    
        </table>
     <?php } ?>  
     </table>
     
        <?php
    }else{
        //pr($otherUserDataSum);die;
        $repair_sale_desc_cash = $normalUserDataSum['repair_sale_desc']['cash'];
        $repair_sale_desc_card = $normalUserDataSum['repair_sale_desc']['card'];
       // $repairDetail = "&nbsp;<font color=blue><strong>Detail: </strong>cash(<strong>$repair_sale_desc_cash</strong>),<br/>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;card(<strong>$repair_sale_desc_card</strong>)</font>";
        
        $repairDetail="&nbsp;<font color=blue>
            <table style=width:162px;>
            <tr>
                <td>Cash</td><td>Card</td>
            </tr><strong>
            <tr>
                <td><strong>$repair_sale_desc_cash</strong></td><td><strong>$repair_sale_desc_card</strong></td>
            </tr>
            </table></font>";
        
        $t_repair_sale_desc_cash = $otherUserDataSum['repair_sale_desc']['cash'];
        $t_repair_sale_desc_card = $otherUserDataSum['repair_sale_desc']['card'];
      //  $t_repairDetail = "&nbsp;<font color=blue><strong>Detail: </strong>cash(<strong>$t_repair_sale_desc_cash</strong>),<br/>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;card(<strong>$t_repair_sale_desc_card</strong>)</font>";
        
         $t_repairDetail="&nbsp;<font color=blue>
            <table style=width:162px;>
            <tr>
                <td>Cash</td><td>Card</td>
            </tr><strong>
            <tr>
                <td><strong>$t_repair_sale_desc_cash</strong></td><td><strong>$t_repair_sale_desc_card</strong></td>
            </tr>
            </table></font>";
        
         $unlock_sale_desc_cash = $normalUserDataSum['unlock_sale_desc']['cash'];
        $unlock_sale_desc_card = $normalUserDataSum['unlock_sale_desc']['card'];
        //$unlockDetail = "&nbsp;<font color=blue><strong>Detail: </strong>cash(<strong>$unlock_sale_desc_cash</strong>),<br/>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;card(<strong>$unlock_sale_desc_card</strong>)</font>";
        $unlockDetail = "&nbsp;<font color=blue>
            <table style=width:162px;>
            <tr>
                <td>Cash</td><td>Card</td>
            </tr><strong>
            <tr>
                <td><strong>$unlock_sale_desc_cash</strong></td><td><strong>$unlock_sale_desc_card</strong></td>
            </tr>
            </table></font>";
        
        
        $t_unlock_sale_desc_cash = $otherUserDataSum['unlock_sale_desc']['cash'];
        $t_unlock_sale_desc_card = $otherUserDataSum['unlock_sale_desc']['card'];
        //$t_unlockDetail = "&nbsp;<font color=blue><strong>Detail: </strong>cash(<strong>$t_unlock_sale_desc_cash</strong>),<br/>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;card(<strong>$t_unlock_sale_desc_card</strong>)</font>";
        
        $t_unlockDetail = "&nbsp;<font color=blue>
            <table style=width:162px;>
            <tr>
                <td>Cash</td><td>Card</td>
            </tr><strong>
            <tr>
                <td><strong>$t_unlock_sale_desc_cash</strong></td><td><strong>$t_unlock_sale_desc_card</strong></td>
            </tr>
            </table></font>";
        
        $product_sale_desc_cash = $normalUserDataSum['product_sale_desc']['cash'];
        $product_sale_desc_card = $normalUserDataSum['product_sale_desc']['card'];
        $product_sale_desc_credit = $normalUserDataSum['product_sale_desc']['credit'];
        $product_sale_desc_bank_transfer = $normalUserDataSum['product_sale_desc']['bank_transfer'];
        $product_sale_desc_cheque= $normalUserDataSum['product_sale_desc']['cheque'];
        
       // $productSaleDetail = "&nbsp;<font color=blue><strong>Detail: <br/></strong>cash(<strong>$product_sale_desc_cash</strong>),<br/>card(<strong>$product_sale_desc_card</strong>),<br/>credit(<strong>$product_sale_desc_credit</strong>),<br/>bank transfer(<strong>$product_sale_desc_bank_transfer</strong>),<br/>cheque(<strong>$product_sale_desc_cheque</strong>)</font>";
        
        $productSaleDetail="&nbsp;<font color=blue>
            <table style=width:162px;>
            <tr>
                <td>Cash</td><td>Card</td><td>credit</td><td>bank transfer</td><td>cheque</td>
            </tr><strong>
            <tr>
                <td><strong>$product_sale_desc_cash</strong></td><td><strong>$product_sale_desc_card</strong></td>
                <td><strong>$product_sale_desc_credit</strong></td><td><strong>$product_sale_desc_bank_transfer</strong></td>
                <td><strong>$product_sale_desc_cheque</strong></td>
            </tr>
            </table></font>";
        
        
        $t_product_sale_desc_cash = $otherUserDataSum['product_sale_desc']['cash'];
        $t_product_sale_desc_card = $otherUserDataSum['product_sale_desc']['card'];
        $t_product_sale_desc_credit = $otherUserDataSum['product_sale_desc']['credit'];
        $t_product_sale_desc_bank_transfer = $otherUserDataSum['product_sale_desc']['bank_transfer'];
        $t_product_sale_desc_cheque= $otherUserDataSum['product_sale_desc']['cheque'];
       // $t_productSaleDetail = "&nbsp;<font color=blue><strong>Detail: <br/></strong>cash(<strong>$t_product_sale_desc_cash</strong>),<br/>card(<strong>$t_product_sale_desc_card</strong>),<br/>credit(<strong>$t_product_sale_desc_credit</strong>),<br/>bank transfer(<strong>$t_product_sale_desc_bank_transfer</strong>),<br/>cheque(<strong>$t_product_sale_desc_cheque</strong>)</font>";
        
        $t_productSaleDetail="&nbsp;<font color=blue>
            <table style=width:162px;>
            <tr>
                <td>Cash</td><td>Card</td><td>credit</td><td>bank transfer</td><td>cheque</td>
            </tr><strong>
            <tr>
                <td><strong>$t_product_sale_desc_cash</strong></td><td><strong>$t_product_sale_desc_card</strong></td>
                <td><strong>$t_product_sale_desc_credit</strong></td><td><strong>$t_product_sale_desc_bank_transfer</strong></td>
                <td><strong>$t_product_sale_desc_cheque</strong></td>
            </tr>
            </table></font>";
        
        
        $quotation_cash = $otherUserDataSum['quotation_desc']['cash'];
        $quotation_card = $otherUserDataSum['quotation_desc']['card'];
        $quotation_credit = $otherUserDataSum['quotation_desc']['credit'];
        $quotation_bank_transfer = $otherUserDataSum['quotation_desc']['bank_transfer'];
        $quotation_cheque= $otherUserDataSum['quotation_desc']['cheque'];
        //$quotation_Detail = "&nbsp;<font color=blue><strong>Detail: <br/></strong>cash(<strong>$quotation_cash</strong>),<br/>card(<strong>$quotation_card</strong>),<br/>credit(<strong>$quotation_credit</strong>),<br/>bank transfer(<strong>$quotation_bank_transfer</strong>),<br/>cheque(<strong>$quotation_cheque</strong>)</font>";
        
        $quotation_Detail="&nbsp;<font color=blue>
            <table style=width:162px;>
            <tr>
                <td>Cash</td><td>Card</td><td>credit</td><td>bank transfer</td><td>cheque</td>
            </tr><strong>
            <tr>
                <td><strong>$quotation_cash</strong></td><td><strong>$quotation_card</strong></td>
                <td><strong>$quotation_credit</strong></td><td><strong>$quotation_bank_transfer</strong></td>
                <td><strong>$quotation_cheque</strong></td>
            </tr>
            </table></font>";
        
        $credit_note_desc_cash = $normalUserDataSum['credit_note_desc']['cash'];
        $credit_note_desc_card = $normalUserDataSum['credit_note_desc']['card'];
        $credit_note_desc_credit = $normalUserDataSum['credit_note_desc']['credit'];
        $credit_note_desc_bank_transfer = $normalUserDataSum['credit_note_desc']['bank_transfer'];
        $credit_note_desc_cheque= $normalUserDataSum['credit_note_desc']['cheque'];
       // $credit_note_desc_Detail = "&nbsp;<font color=blue><strong>Detail: <br/></strong>cash(<strong>$credit_note_desc_cash</strong>),<br/>card(<strong>$credit_note_desc_card</strong>),<br/>credit(<strong>$credit_note_desc_credit</strong>),<br/>bank transfer(<strong>$credit_note_desc_bank_transfer</strong>),<br/>cheque(<strong>$credit_note_desc_cheque</strong>)</font>";
        
        $credit_note_desc_Detail ="&nbsp;<font color=blue>
            <table style=width:162px;>
            <tr>
                <td>Cash</td><td>Card</td><td>credit</td><td>bank transfer</td><td>cheque</td>
            </tr><strong>
            <tr>
                <td><strong>$credit_note_desc_cash</strong></td><td><strong>$credit_note_desc_card</strong></td>
                <td><strong>$credit_note_desc_credit</strong></td><td><strong>$credit_note_desc_bank_transfer</strong></td>
                <td><strong>$credit_note_desc_cheque</strong></td>
            </tr>
            </table></font>";
        
        $t_credit_note_desc_cash = $otherUserDataSum['credit_quotation_desc']['cash'];
        $t_credit_note_desc_card = $otherUserDataSum['credit_quotation_desc']['card'];
        $t_credit_note_desc_credit = $otherUserDataSum['credit_quotation_desc']['credit'];
        $t_credit_note_desc_bank_transfer = $otherUserDataSum['credit_quotation_desc']['bank_transfer'];
        $t_credit_note_desc_cheque= $otherUserDataSum['credit_quotation_desc']['cheque'];
       // $t_credit_note_desc_Detail = "&nbsp;<font color=blue><strong>Detail: <br/></strong>cash(<strong>$t_credit_note_desc_cash</strong>),<br/>card(<strong>$t_credit_note_desc_card</strong>),<br/>credit(<strong>$t_credit_note_desc_credit</strong>),<br/>bank transfer(<strong>$t_credit_note_desc_bank_transfer</strong>),<br/>cheque(<strong>$t_credit_note_desc_cheque</strong>)</font>";
        
        $t_credit_note_desc_Detail ="&nbsp;<font color=blue>
            <table style=width:162px;>
            <tr>
                <td>Cash</td><td>Card</td><td>credit</td><td>bank transfer</td><td>cheque</td>
            </tr><strong>
            <tr>
                <td><strong>$t_credit_note_desc_cash</strong></td><td><strong>$t_credit_note_desc_card</strong></td>
                <td><strong>$t_credit_note_desc_credit</strong></td><td><strong>$t_credit_note_desc_bank_transfer</strong></td>
                <td><strong>$t_credit_note_desc_cheque</strong></td>
            </tr>
            </table></font>";
        
        $bulk_mobile_sale_desc_cash = $normalUserDataSum['bulk_mobile_sale_desc']['cash'];
        $bulk_mobile_sale_desc_card = $normalUserDataSum['bulk_mobile_sale_desc']['card'];
        //$bulk_mobile_sale_descDetail = "&nbsp;<font color=blue><strong>Detail: </strong>cash(<strong>$bulk_mobile_sale_desc_cash</strong>),<br/>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;card(<strong>$bulk_mobile_sale_desc_card</strong>)</font>";
        
        $bulk_mobile_sale_descDetail ="&nbsp;<font color=blue>
            <table style=width:162px;>
            <tr>
                <td>Cash</td><td>Card</td>
            </tr><strong>
            <tr>
                <td><strong>$bulk_mobile_sale_desc_cash</strong></td><td><strong>$bulk_mobile_sale_desc_card</strong></td>
            </tr>
            </table></font>";
        
        
        $mobile_sale_desc_cash = $normalUserDataSum['mobile_sale_desc']['cash'];
        $mobile_sale_desc_card = $normalUserDataSum['mobile_sale_desc']['card'];
       // $mobile_sale_descDetail = "&nbsp;<font color=blue><strong>Detail: </strong>cash(<strong>$mobile_sale_desc_cash</strong>),<br/>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;card(<strong>$mobile_sale_desc_card</strong>)</font>";
        
         $mobile_sale_descDetail ="&nbsp;<font color=blue>
            <table style=width:162px;>
            <tr>
                <td>Cash</td><td>Card</td>
            </tr><strong>
            <tr>
                <td><strong>$mobile_sale_desc_cash</strong></td><td><strong>$mobile_sale_desc_card</strong></td>
            </tr>
            </table></font>";
        
        $net_card_desc_repair = $normalUserDataSum['net_card_desc']['repair'];
        $net_card_desc_Unlock = $normalUserDataSum['net_card_desc']['Unlock'];
        $net_card_desc_Product = $normalUserDataSum['net_card_desc']['Product'];
        $net_card_desc_Blk = $normalUserDataSum['net_card_desc']['Blk'];
        $net_card_desc_Mobile = $normalUserDataSum['net_card_desc']['Mobile'];
        $net_card_desc_credit_note = $normalUserDataSum['net_card_desc']['credit_note'];
        $net_card_desc_prev_recpts_sale = $normalUserDataSum['net_card_desc']['prev_recpts_sale'];
        
         $totalNetCardDetail = $net_card_desc_repair+$net_card_desc_Unlock+$net_card_desc_Product+$net_card_desc_Blk+$net_card_desc_Mobile+$net_card_desc_credit_note+$net_card_desc_prev_recpts_sale;
   
        //pr($normalUserDataSum);
        $net_card_desc = "&nbsp;<font color=blue><strong>Detail: <br/></strong>Repair(<strong>$net_card_desc_repair</strong>),<br/>Unlock(<strong>$net_card_desc_Unlock</strong>),<br/>Product(<strong>$net_card_desc_Product</strong>),<br/>Blk(<strong>$net_card_desc_Blk</strong>),<br/>Mobile(<strong>$net_card_desc_Mobile</strong>,<br/>Credit note(<strong>$net_card_desc_credit_note</strong>)</font>";
        
        $net_card_desc ="&nbsp;<font color=blue>
            <table border=1 style=width:162px;>
            <tr>
                <td>repair</td><td>unlock</td><td>product</td><td>blk</td><td>mob</td><td>Quot</td><td>prev Recpts Sale</td><td>credit note</td><td>Credit Quotation</td>
            </tr><strong>
            <tr>
                <td><strong>$net_card_desc_repair</strong></td><td><strong>$net_card_desc_Unlock</strong></td>
                <td><strong>$net_card_desc_Product</strong></td><td><strong>$net_card_desc_Blk</strong></td>
                <td><strong>$net_card_desc_Mobile</strong></td><td><strong>0</strong></td>
                <td><strong>$net_card_desc_prev_recpts_sale</strong></td><td><strong>$net_card_desc_credit_note</strong></td>
                <td><strong>0</strong></td>
            </tr>
            <tr>
            <td colspan=7></td>
            <td>total</td><td>$totalNetCardDetail</td>
            </tr>
            </table></font>";
        
        
        $t_net_card_desc_repair = $otherUserDataSum['net_card_desc']['repair'];
        $t_net_card_desc_Unlock = $otherUserDataSum['net_card_desc']['Unlock'];
        $t_net_card_desc_Product = $otherUserDataSum['net_card_desc']['Product'];
        $t_net_card_desc_Blk = $otherUserDataSum['net_card_desc']['Blk'];
        $t_net_card_desc_Mobile = $otherUserDataSum['net_card_desc']['Mobile'];
        $t_net_card_desc_credit_note = $otherUserDataSum['net_card_desc']['credit_note'];
        
        $t_net_card_desc_special = $otherUserDataSum['net_card_desc']['special'];
        $t_net_card_desc_prv_sale = $otherUserDataSum['net_card_desc']['prev_recpts_sale'];
        $t_net_card_desc_special_credit_note = $otherUserDataSum['net_card_desc']['special_credit_note'];
        
        
        $t_totalNetCardDetail = $t_net_card_desc_repair + $t_net_card_desc_Unlock + $t_net_card_desc_Product + $t_net_card_desc_Blk + $t_net_card_desc_Mobile + $t_net_card_desc_credit_note + $t_net_card_desc_special + $t_net_card_desc_prv_sale + $t_net_card_desc_special_credit_note;
        
        //$t_net_card_desc = "&nbsp;<font color=blue><strong>Detail: <br/></strong>Repair(<strong>$t_net_card_desc_repair</strong>),<br/>Unlock(<strong>$t_net_card_desc_Unlock</strong>),<br/>Product(<strong>$t_net_card_desc_Product</strong>),
        //<br/>Blk(<strong>$t_net_card_desc_Blk</strong>),
        //<br/>Mobile(<strong>$t_net_card_desc_Mobile</strong>,
        //<br/>Credit note(<strong>$t_net_card_desc_credit_note</strong>)
        //<br/>Quotation(<strong>$t_net_card_desc_special</strong>)
        //<br/>prv recipt sale(<strong>$t_net_card_desc_prv_sale</strong>)
        //<br/>special credit note(<strong>$t_net_card_desc_special_credit_note</strong>)
        //</font>";
        
        $t_net_card_desc ="&nbsp;<font color=blue>
            <table border=1 style=width:162px;>
            <tr>
                <td>repair</td><td>unlock</td><td>product</td><td>blk</td><td>mob</td><td>Quot</td><td>prev Recpts Sale</td><td>credit note</td><td>Credit Quotation</td>
            </tr><strong>
            <tr>
                <td><strong>$t_net_card_desc_repair</strong></td><td><strong>$t_net_card_desc_Unlock</strong></td>
                <td><strong>$t_net_card_desc_Product</strong></td><td><strong>$t_net_card_desc_Blk</strong></td>
                <td><strong>$t_net_card_desc_Mobile</strong></td><td><strong>$t_net_card_desc_special</strong></td>
                <td><strong>$t_net_card_desc_prv_sale</strong></td><td><strong>$t_net_card_desc_credit_note</strong></td>
                <td><strong>$t_net_card_desc_special_credit_note</strong></td>
            </tr>
            <tr>
            <td colspan=7></td>
            <td>total</td><td>$t_totalNetCardDetail</td>
            </tr>
            </table></font>";
        
        $net_bank_desc_1 = $normalUserDataSum['net_bnk_tnsfer_desc'][0];
        $net_bank_desc_2 = $normalUserDataSum['net_bnk_tnsfer_desc'][1];
        $TotalNetBnkTrnsfrDetail = $net_bank_desc_1 - $net_bank_desc_2;
        //$net_bank_desc = "&nbsp;<font color=blue><strong>Detail: <br/></strong>(<strong>$net_bank_desc_1</strong>),<br/>(<strong>$net_bank_desc_2</strong>)</font>";
        
        //$net_bank_desc = "&nbsp;<font color=blue><strong>Detail:
        //<br/></strong>product(<strong>$product_sale_desc_bank_transfer</strong>),
        //<br/>quotation(<strong>0</strong>),
        //<br/>credit_note(<strong>$credit_note_desc_bank_transfer</strong>),
        //<br/>special_credit_note(<strong>0</strong>),
        //</font>";
        
        $net_bank_desc ="&nbsp;<font color=blue>
            <table border=1 style=width:162px;>
            <tr>
                <td>product</td><td>quotation</td><td>credit_note</td><td>Credit Quotation</td>
            </tr><strong>
            <tr>
                <td><strong>$product_sale_desc_bank_transfer</strong></td><td><strong>0</strong></td>
                <td><strong>$credit_note_desc_bank_transfer</strong></td><td><strong>0</strong></td>
            </tr>
            <tr>
            <td colspan=2></td>
            <td>total</td><td>$TotalNetBnkTrnsfrDetail</td>
            </tr>
            </table></font>";
        
        $t_net_bank_desc_1 = $otherUserDataSum['net_bnk_tnsfer_desc'][0];
        $t_net_bank_desc_2 = $otherUserDataSum['net_bnk_tnsfer_desc'][1];
        $t_TotalNetBnkTrnsfrDetail = $t_net_bank_desc_1 - $t_net_bank_desc_2;
        //$t_net_bank_desc = "&nbsp;<font color=blue><strong>Detail: <br/></strong>(<strong>$net_bank_desc_1</strong>),<br/>(<strong>$net_bank_desc_2</strong>)</font>";
        
        $t_net_bank_desc = "&nbsp;<font color=blue><strong>Detail:
        <br/></strong>product(<strong>$t_product_sale_desc_bank_transfer</strong>),
        <br/>quotation(<strong>$quotation_bank_transfer</strong>)
        <br/>credit_note(<strong>$credit_note_desc_bank_transfer</strong>)
        <br/>special_credit_note(<strong>$t_credit_note_desc_bank_transfer</strong>)
        </font>";
        
        $t_net_bank_desc ="&nbsp;<font color=blue>
            <table border=1 style=width:162px;>
            <tr>
                <td>product</td><td>quotation</td><td>credit_note</td><td>Credit Quotation</td>
            </tr><strong>
            <tr>
                <td><strong>$t_product_sale_desc_bank_transfer</strong></td><td><strong>$quotation_bank_transfer</strong></td>
                <td><strong>$credit_note_desc_bank_transfer</strong></td><td><strong>$t_credit_note_desc_bank_transfer</strong></td>
            </tr>
            <tr>
            <td colspan=2></td>
            <td>total</td><td>$t_TotalNetBnkTrnsfrDetail</td>
            </tr>
            </table></font>";
        
        $net_cheque_desc_1 = $normalUserDataSum['net_cheque_payment_desc'][0];
        $net_cheque_desc_2 = $normalUserDataSum['net_cheque_payment_desc'][1];
        
        $TotalNetChequePayDetail = $net_cheque_desc_1 - $net_cheque_desc_2;
        //$net_cheque_desc = "&nbsp;<font color=blue><strong>Detail: <br/></strong>(<strong>$net_cheque_desc_1</strong>),<br/>(<strong>$net_cheque_desc_2</strong>)</font>";
        
        //$net_cheque_desc = "&nbsp;<font color=blue><strong>Detail:
        //<br/></strong>product(<strong>$product_sale_desc_cheque</strong>),
        //<br/>quotation(<strong>0</strong>)
        //<br/>credit_note(<strong>$credit_note_desc_cheque</strong>)
        //<br/>special_credit_note(<strong>0</strong>)
        //</font>";
        
        $net_cheque_desc ="&nbsp;<font color=blue>
            <table border=1 style=width:162px;>
            <tr>
                <td>product</td><td>quotation</td><td>credit_note</td><td>Credit Quotation</td>
            </tr><strong>
            <tr>
                <td><strong>$product_sale_desc_cheque</strong></td><td><strong>0</strong></td>
                <td><strong>$credit_note_desc_cheque</strong></td><td><strong>0</strong></td>
            </tr>
            <tr>
            <td colspan=2></td>
            <td>total</td><td>$TotalNetChequePayDetail</td>
            </tr>
            </table></font>"; 
        
        $t_net_cheque_desc_1 = $otherUserDataSum['net_cheque_payment_desc'][0];
        $t_net_cheque_desc_2 = $otherUserDataSum['net_cheque_payment_desc'][1];
        //$t_net_cheque_desc = "&nbsp;<font color=blue><strong>Detail: <br/></strong>(<strong>$net_bank_desc_1</strong>),<br/>(<strong>$net_bank_desc_2</strong>)</font>";
        $t_TotalNetChequePayDetail = $t_net_cheque_desc_1 - $t_net_cheque_desc_2;
        $t_net_cheque_desc = "&nbsp;<font color=blue><strong>Detail:
        <br/></strong>product(<strong>$t_product_sale_desc_cheque</strong>),
        <br/>quotation(<strong>$quotation_cheque</strong>)
        <br/>credit_note(<strong>$credit_note_desc_cheque</strong>)
        <br/>special_credit_note(<strong>$t_credit_note_desc_cheque</strong>)
        </font>";
        
        
        $t_net_cheque_desc ="&nbsp;<font color=blue>
            <table border=1 style=width:162px;>
            <tr>
                <td>product</td><td>quotation</td><td>credit_note</td><td>Credit Quotation</td>
            </tr><strong>
            <tr>
                <td><strong>$t_product_sale_desc_cheque</strong></td><td><strong>$quotation_cheque</strong></td>
                <td><strong>$credit_note_desc_cheque</strong></td><td><strong>$t_credit_note_desc_cheque</strong></td>
            </tr>
            <tr>
            <td colspan=2></td>
            <td>total</td><td>$t_TotalNetChequePayDetail</td>
            </tr>
            </table></font>"; 
        
        $norTotalCashInHandSale = $normalUserDataSum['cash_in_hand_desc']['sale']['Repair']+
        $normalUserDataSum['cash_in_hand_desc']['sale']['Unlock']+
        $normalUserDataSum['cash_in_hand_desc']['sale']['Product']+
        $normalUserDataSum['cash_in_hand_desc']['sale']['Blk']+
        $normalUserDataSum['cash_in_hand_desc']['sale']['Mobile'];//+$normalUserDataSum['cash_in_hand_desc']['sale']['prv_recpit_amt'];
        
        $norTotalCashInHandRefund = $normalUserDataSum['cash_in_hand_desc']['refund']['Repair']+
        $normalUserDataSum['cash_in_hand_desc']['refund']['Unlock']+
        $normalUserDataSum['cash_in_hand_desc']['refund']['Product']+
        $normalUserDataSum['cash_in_hand_desc']['refund']['Blk']+
        $normalUserDataSum['cash_in_hand_desc']['refund']['Mobile']+
        $normalUserDataSum['cash_in_hand_desc']['refund']['Credit_Note']+
        $normalUserDataSum['cash_in_hand_desc']['refund']['Mobile_Purchase'];
        
        $norfinalCashInHand = $norTotalCashInHandSale - $norTotalCashInHandRefund;
        
        $norCashInHandDetail = "&nbsp;<font color=blue><strong>Detail:
        </strong>(Sale(Repair(".$normalUserDataSum['cash_in_hand_desc']['sale']['Repair'].")
        +Unlock(".$normalUserDataSum['cash_in_hand_desc']['sale']['Unlock'].")
        +Product(".$normalUserDataSum['cash_in_hand_desc']['sale']['Product'].")
        +Blk(".$normalUserDataSum['cash_in_hand_desc']['sale']['Blk'].")
        +<br/>Mobile(".$normalUserDataSum['cash_in_hand_desc']['sale']['Mobile'].")
        +<br/>prv_recpit_amt(".$normalUserDataSum['cash_in_hand_desc']['sale']['prv_recpit_amt'].")
        +<br/>special(0)
        +<br/>prv_credit_to_cash(0)
        
        )=
        $norTotalCashInHandSale,
        Refund(Repair(".$normalUserDataSum['cash_in_hand_desc']['refund']['Repair'].")+
        Unlock(".$normalUserDataSum['cash_in_hand_desc']['refund']['Unlock'].")+
        Product(".$normalUserDataSum['cash_in_hand_desc']['refund']['Product'].")+
        Blk(".$normalUserDataSum['cash_in_hand_desc']['refund']['Blk'].")+
        Mobile(".$normalUserDataSum['cash_in_hand_desc']['refund']['Mobile'].")+
        <br/>Credit_Note(".$normalUserDataSum['cash_in_hand_desc']['refund']['Credit_Note'].")+
        <br/>Special Credit_Note(0)+
        Mobile_Purchase(".$normalUserDataSum['cash_in_hand_desc']['refund']['Mobile_Purchase']."))
        ($norTotalCashInHandSale-$norTotalCashInHandRefund) = $norfinalCashInHand)";
        
        //$normalUserDataSum['cash_in_hand_desc']['sale']['Credit_Note']
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
                <td><strong>".$normalUserDataSum['cash_in_hand_desc']['sale']['Repair']."</strong></td><td><strong>".$normalUserDataSum['cash_in_hand_desc']['sale']['Unlock']."</strong></td>
                <td><strong>".$normalUserDataSum['cash_in_hand_desc']['sale']['Product']."</strong></td><td><strong>".$normalUserDataSum['cash_in_hand_desc']['sale']['Blk']."</strong></td>
                <td><strong>".$normalUserDataSum['cash_in_hand_desc']['sale']['Mobile']."</strong></td><td><strong>".'change'."</strong></td>
                <td><strong>0</strong></td><td><strong>0</strong></td>
            </tr>
            <tr>
            <td colspan='6'></td>
            <td>total</td><td>$norTotalCashInHandSale</td>
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
            <tr>
            </tr>
            <td><strong>".$normalUserDataSum['cash_in_hand_desc']['refund']['Repair']."</strong></td>
            <td><strong>".$normalUserDataSum['cash_in_hand_desc']['refund']['Unlock']."</strong></td>
            <td><strong>".$normalUserDataSum['cash_in_hand_desc']['refund']['Product']."</strong></td>
            <td><strong>".$normalUserDataSum['cash_in_hand_desc']['refund']['Blk']."</strong></td>
            <td><strong>".$normalUserDataSum['cash_in_hand_desc']['refund']['Mobile']."</strong></td>
            <td><strong>".$normalUserDataSum['cash_in_hand_desc']['refund']['Credit_Note']."</strong></td>
            <td><strong>".$normalUserDataSum['cash_in_hand_desc']['refund']['Mobile_Purchase']."</strong></td>
            <td><strong>0</td>
            </tr>
            <tr>
            <td colspan='6'></td>
            <td>total</td><td>$norTotalCashInHandRefund</td>
            </tr>
            <tr>
            <td colspan='6'></td>
            <td colspan='3'>($norTotalCashInHandSale-$norTotalCashInHandRefund) = $norfinalCashInHand)</td>
            </tr>
            </table></font>"; 
        
        $othTotalCashInHandSale = $otherUserDataSum['cash_in_hand_desc']['sale']['Repair']+
        $otherUserDataSum['cash_in_hand_desc']['sale']['Unlock']+
        $otherUserDataSum['cash_in_hand_desc']['sale']['Product']+
        $otherUserDataSum['cash_in_hand_desc']['sale']['Blk']+
        $otherUserDataSum['cash_in_hand_desc']['sale']['Mobile']+
        $otherUserDataSum['cash_in_hand_desc']['sale']['prv_credit_to_cash']+
        $otherUserDataSum['cash_in_hand_desc']['sale']['special']+
        $otherUserDataSum['cash_in_hand_desc']['sale']['prv_recpit_amt']
        ;
        
        $othTotalCashInHandRefund = $otherUserDataSum['cash_in_hand_desc']['refund']['Repair']+
        $otherUserDataSum['cash_in_hand_desc']['refund']['Unlock']+
        $otherUserDataSum['cash_in_hand_desc']['refund']['Product']+
        $otherUserDataSum['cash_in_hand_desc']['refund']['Blk']+
        $otherUserDataSum['cash_in_hand_desc']['refund']['Mobile']+
        $otherUserDataSum['cash_in_hand_desc']['refund']['special_credit_note']+
        $otherUserDataSum['cash_in_hand_desc']['refund']['Credit_Note'];
        
        $othfinalCashInHand = $othTotalCashInHandSale - $othTotalCashInHandRefund;
        
        $othCashInHandDetail = "&nbsp;<font color=blue><strong>Detail:
        </strong>(Sale(Repair(".$otherUserDataSum['cash_in_hand_desc']['sale']['Repair'].")+
        Unlock(".$otherUserDataSum['cash_in_hand_desc']['sale']['Unlock'].")+
        Product(".$otherUserDataSum['cash_in_hand_desc']['sale']['Product'].")+
        Blk(".$otherUserDataSum['cash_in_hand_desc']['sale']['Blk'].")+
        <br/>Mobile(".$otherUserDataSum['cash_in_hand_desc']['sale']['Mobile'].")
        <br/>prv_recpit_amt(".$otherUserDataSum['cash_in_hand_desc']['sale']['prv_recpit_amt'].")
        <br/>special(".$otherUserDataSum['cash_in_hand_desc']['sale']['special'].")
        <br/>prv_credit_to_cash(".$otherUserDataSum['cash_in_hand_desc']['sale']['prv_credit_to_cash'].")
        )= $othTotalCashInHandSale,
        Refund(Repair(".$otherUserDataSum['cash_in_hand_desc']['refund']['Repair'].")+
        Unlock(".$otherUserDataSum['cash_in_hand_desc']['refund']['Unlock'].")+
        Product(".$otherUserDataSum['cash_in_hand_desc']['refund']['Product'].")+
        Blk(".$otherUserDataSum['cash_in_hand_desc']['refund']['Blk'].")+
        Mobile(".$otherUserDataSum['cash_in_hand_desc']['refund']['Mobile'].")
        Credit_Note(".$otherUserDataSum['cash_in_hand_desc']['refund']['Credit_Note'].")
        special_credit_note(".$otherUserDataSum['cash_in_hand_desc']['refund']['special_credit_note'].")
        )
        ($othTotalCashInHandSale-$othTotalCashInHandRefund) = $othfinalCashInHand)";
        
        
        $othCashInHandDetail ="&nbsp;<font color=blue>
            <table border=1 style=width:162px;>
            <tr>
                <td>Sale</td>
            </tr>
            <tr>
                <td>Repair</td><td>Unlock</td><td>Product </td><td>Blk</td>
                <td>Mobile</td><td>Prv Recpt amt</td><td>special</td><td>Prv Credit 2 Cash</td>
            </tr>
            <tr>
                <td><strong>".$otherUserDataSum['cash_in_hand_desc']['sale']['Repair']."</strong></td><td><strong>".$otherUserDataSum['cash_in_hand_desc']['sale']['Unlock']."</strong></td>
                <td><strong>".$otherUserDataSum['cash_in_hand_desc']['sale']['Product']."</strong></td><td><strong>".$otherUserDataSum['cash_in_hand_desc']['sale']['Blk']."</strong></td>
                <td><strong>".$otherUserDataSum['cash_in_hand_desc']['sale']['Mobile']."</strong></td><td><strong>".'change'."</strong></td>
                <td><strong>".$otherUserDataSum['cash_in_hand_desc']['sale']['special']."</strong></td><td><strong>".$otherUserDataSum['cash_in_hand_desc']['sale']['prv_credit_to_cash']."</strong></td>
            </tr>
            <tr>
            <td colspan='6'></td>
            <td>total</td><td>$othTotalCashInHandSale</td>
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
            <tr>
            </tr>
            <td><strong>".$otherUserDataSum['cash_in_hand_desc']['refund']['Repair']."</strong></td>
            <td><strong>".$otherUserDataSum['cash_in_hand_desc']['refund']['Unlock']."</strong></td>
            <td><strong>".$otherUserDataSum['cash_in_hand_desc']['refund']['Product']."</strong></td>
            <td><strong>".$otherUserDataSum['cash_in_hand_desc']['refund']['Blk']."</strong></td>
            <td><strong>".$otherUserDataSum['cash_in_hand_desc']['refund']['Mobile']."</strong></td>
            <td><strong>".$otherUserDataSum['cash_in_hand_desc']['refund']['Credit_Note']."</strong></td>
            <td><strong>".$otherUserDataSum['cash_in_hand_desc']['refund']['Mobile_Purchase']."</strong></td>
            <td><strong>".$otherUserDataSum['cash_in_hand_desc']['refund']['special_credit_note']."</td>
            </tr>
            <tr>
            <td colspan='6'></td>
            <td>total</td><td>$othTotalCashInHandRefund</td>
            </tr>
            <tr>
            <td colspan='6'></td>
            <td colspan='3'>($othTotalCashInHandSale-$othTotalCashInHandRefund) = $othfinalCashInHand)</td>
            </tr>
            </table></font>"; 
        
        //pr($otherUserDataSum);die;
        
        $credit_to_cash_invoiceCash = $normalUserDataSum['credit_to_cash_desc']['invoice_cash'];
        $credit_to_cash_quotationCash = $normalUserDataSum['credit_to_cash_desc']['Quotation_cash'];
        $credit_to_cash_creditCash = $normalUserDataSum['credit_to_cash_desc']['credit_cash'];
        $credit_to_cash_CreditQuotationCash = $normalUserDataSum['credit_to_cash_desc']['credit_quotation_cash'];
        //$CreditToCashDetail = "&nbsp;<font color=blue><strong>Detail: <br/>
        //</strong>invoice cash(<strong>$credit_to_cash_invoiceCash</strong>),
        //<br/>Quotation cash(<strong>0</strong>),
        //<br/>credit cash(<strong>$credit_to_cash_creditCash</strong>),
        //<br/>credit quotation cash(<strong>0</strong>)</font>";
        
        $CreditToCashDetail = "&nbsp;<font color=blue><table>
            <tr><td>invoice cash</td><td>Quotation cash</td><td>credit cash</td>
            <td>credit quotation cash</td></tr>
            
            <tr><td><strong>$credit_to_cash_invoiceCash</strong></td><td><strong>0</strong></td>
            <td><strong>$credit_to_cash_creditCash</strong></td><td><strong>0</strong></td>
            </tr>
            </table></font>";
        
        $t_credit_to_cash_invoiceCash = $otherUserDataSum['credit_to_cash_desc']['invoice_cash'];
        $t_credit_to_cash_quotationCash = $otherUserDataSum['credit_to_cash_desc']['Quotation_cash'];
        $t_credit_to_cash_creditCash = $otherUserDataSum['credit_to_cash_desc']['credit_cash'];
        $t_credit_to_cash_CreditQuotationCash = $otherUserDataSum['credit_to_cash_desc']['credit_quotation_cash'];
        $t_CreditToCashDetail = "&nbsp;<font color=blue><strong>Detail: <br/>
        </strong>invoice cash(<strong>$t_credit_to_cash_invoiceCash</strong>),
        <br/>Quotation cash(<strong>$t_credit_to_cash_quotationCash</strong>),
        <br/>credit cash(<strong>$t_credit_to_cash_creditCash</strong>),
        <br/>credit quotation cash(<strong>$t_credit_to_cash_CreditQuotationCash</strong>)</font>";
        
         $t_CreditToCashDetail = "&nbsp;<font color=blue><table>
            <tr><td>invoice cash</td><td>Quotation cash</td><td>credit cash</td>
            <td>credit quotation cash</td></tr>
            
            <tr><td><strong>$t_credit_to_cash_invoiceCash</strong></td><td><strong>$t_credit_to_cash_quotationCash</strong></td>
            <td><strong>$t_credit_to_cash_creditCash</strong></td><td><strong>$t_credit_to_cash_CreditQuotationCash</strong></td>
            </tr>
            </table></font>";
        
        $credit_to_other_bank_transfer = $normalUserDataSum['credit_to_other_payment_desc']['total_bank_transfer'];
        $credit_to_other_card_Payment = $normalUserDataSum['credit_to_other_payment_desc']['total_card_Payment'];
        $credit_to_other_cheque_payment = $normalUserDataSum['credit_to_other_payment_desc']['total_cheque_payment'];
        
        //$CreditToOtherDetail = "&nbsp;<font color=blue><strong>Detail: <br/></strong>total bank transfer(<strong>$credit_to_other_bank_transfer</strong>),<br/>total card Payment(<strong>$credit_to_other_card_Payment</strong>),<br/>total cheque payment(<strong>$credit_to_other_cheque_payment</strong>)</font>";
        
        $CreditToOtherDetail = "&nbsp;<font color=blue><table>
            <tr><td>total bank transfer</td><td>total card Payment</td><td>total cheque payment</td></tr><strong>
            <tr><td><strong>$credit_to_other_bank_transfer</strong></td><td><strong>$credit_to_other_card_Payment</strong></td>
            <td><strong>$credit_to_other_cheque_payment</strong></td>
            </tr>
            </table></font>";
        
        $t_credit_to_other_bank_transfer = $otherUserDataSum['credit_to_other_payment_desc']['total_bank_transfer'];
        $t_credit_to_other_card_Payment = $otherUserDataSum['credit_to_other_payment_desc']['total_card_Payment'];
        $t_credit_to_other_cheque_payment = $otherUserDataSum['credit_to_other_payment_desc']['total_cheque_payment'];
        
        //$t_CreditToOtherDetail = "&nbsp;<font color=blue><strong>Detail: <br/></strong>total bank transfer(<strong>$t_credit_to_other_bank_transfer</strong>),<br/>total card Payment(<strong>$t_credit_to_other_card_Payment</strong>),<br/>total cheque payment(<strong>$t_credit_to_other_cheque_payment</strong>)</font>";
        
        $t_CreditToOtherDetail = "&nbsp;<font color=blue><table>
            <tr><td>total bank transfer</td><td>total card Payment</td><td>total cheque payment</td></tr><strong>
            <tr><td><strong>$t_credit_to_other_bank_transfer</strong></td><td><strong>$t_credit_to_other_card_Payment</strong></td>
            <td><strong>$t_credit_to_other_cheque_payment</strong></td>
            </tr>
            </table></font>";
        
        //$net_credit_desc_all = "&nbsp;<font color=blue><strong>Detail:
        //<br/></strong>product(<strong>$product_sale_desc_credit</strong>),
        //<br/>quotation(<strong>0</strong>),
        //<br/>credit_note(<strong>$credit_note_desc_credit</strong>)
        //<br/>special_credit_note(<strong>0</strong>)
        //</font>";
        $TotalNetCreditDetail = $product_sale_desc_credit - $credit_note_desc_credit;
        $net_credit_desc_all ="&nbsp;<font color=blue>
            <table border=1 style=width:162px;>
            <tr>
                <td>product</td><td>quotation</td><td>credit_note</td><td>Credit Quotation</td>
            </tr><strong>
            <tr>
                <td><strong>$product_sale_desc_credit</strong></td><td><strong>0</strong></td>
                <td><strong>$credit_note_desc_credit</strong></td><td><strong>0</strong></td>
            </tr>
            <tr>
            <td colspan=2></td>
            <td>total</td><td>$TotalNetCreditDetail</td>
            </tr>
            </table></font>";
        
        // $t_net_credit_desc_all = "&nbsp;<font color=blue><strong>Detail:
        //<br/></strong>product(<strong>$t_product_sale_desc_credit</strong>),
        //<br/>quotation(<strong>$quotation_credit</strong>),
        //<br/>credit_note(<strong>$credit_note_desc_credit</strong>)
        //<br/>special_credit_note(<strong>$t_credit_note_desc_credit</strong>)
        //</font>";
        $t_TotalNetCreditDetail = $product_sale_desc_credit + $quotation_credit - ($credit_note_desc_credit + $t_credit_note_desc_credit);
         $t_net_credit_desc_all ="&nbsp;<font color=blue>
            <table border=1 style=width:162px;>
            <tr>
                <td>product</td><td>quotation</td><td>credit_note</td><td>Credit Quotation</td>
            </tr><strong>
            <tr>
                <td><strong>$t_product_sale_desc_credit</strong></td><td><strong>$quotation_credit</strong></td>
                <td><strong>$credit_note_desc_credit</strong></td><td><strong>$t_credit_note_desc_credit</strong></td>
            </tr>
            <tr>
            <td colspan=2></td>
            <td>total</td><td>$t_TotalNetCreditDetail</td>
            </tr>
            </table></font>";
        
        ?>
        <table width="100%">
            <tr>
                <th width='20%'>&nbsp;</th>
                <th width='20%'>Normal</th>
                <th width='20%'>Special</th>
            </tr>
            <tr><td><strong>Repair Sale</strong></td>
            <td><?php echo "<b style=background-color:yellow;text-align:right;font-size:14px;>".$normalUserDataSum['repair_sale']."</b>".$repairDetail; ?></td>
            <td style="background:bisque;"><?php echo "<b style=background-color:yellow;text-align:right;font-size:14px;>".$otherUserDataSum['repair_sale']."</b>".$t_repairDetail; ?></td>
            </tr>
            <tr style="color: blue">
            <td><strong>Repair Refund</strong></td>
            <td><?php echo $normalUserDataSum['repair_refund']; ?></td>
            <td style="background:bisque;"><?php echo $otherUserDataSum['repair_refund']; ?></td>
            </tr>
            <tr>
            <td><strong>Unlock Sale</strong></td>
            <td><?php echo "<b style=background-color:yellow;text-align:right;font-size:14px;>".$normalUserDataSum['unlock_sale']."</b>".$unlockDetail; ?></td>
            <td style="background:bisque;"><?php echo "<b style=background-color:yellow;text-align:right;font-size:14px;>".$otherUserDataSum['unlock_sale']."</b>".$t_unlockDetail; ?></td>
            </tr>
            <tr style="color: blue">
            <td><strong>Unlock Refund</strong></td>
            <td><?php echo $normalUserDataSum['unlock_refund']; ?></td>
            <td style="background:bisque;"><?php echo $otherUserDataSum['unlock_refund']; ?></td>
            </tr>
            <tr>
            <td><strong>Product Sale</strong></td>
            <td><?php echo "<b style=background-color:yellow;text-align:right;font-size:14px;>".$normalUserDataSum['product_sale']."</b>".$productSaleDetail; ?></td>
            <td style="background:bisque;"><?php echo "<b style=background-color:yellow;text-align:right;font-size:14px;>".$otherUserDataSum['product_sale']."</b>".$t_productSaleDetail; ?></td>
            </tr>
             <tr>
            <td><strong>Quotation</strong></td>
            <td><?php echo "<b style=background-color:yellow;text-align:right;font-size:14px;>".$normalUserDataSum['quotation']."</b>"; ?></td>
            <td style="background:bisque;"><?php echo "<b style=background-color:yellow;text-align:right;font-size:14px;>".$otherUserDataSum['quotation']."</b>".$quotation_Detail; ?></td>
             </tr>
            <tr style="color: blue">
            <td><strong>Credit Note</strong></td>
            <td><?php echo "<b style=background-color:yellow;text-align:right;font-size:14px;>".$normalUserDataSum['credit_note']."</b>".$credit_note_desc_Detail; ?></td>
            <td style="background:bisque;"><?php echo "<b style=background-color:yellow;text-align:right;font-size:14px;>".$otherUserDataSum['credit_note']."</b>".$credit_note_desc_Detail; ?></td>
            </tr>
            <tr>
                <td>Credit Quotation</td>
                <td><?php echo "<b style=background-color:yellow;text-align:right;font-size:14px;>".$normalUserDataSum['credit_quotation']."</b>"; ?></td>
                <td style="background:bisque;"><?php echo "<b style=background-color:yellow;text-align:right;font-size:14px;>".$otherUserDataSum['credit_quotation']."</b>".$t_credit_note_desc_Detail; ?></td>
            </tr>
            <tr>
                <td>Product Refund</td>
                <td><?php echo $normalUserDataSum['product_refund']; ?></td>
                <td><?php echo $otherUserDataSum['product_refund']; ?></td>
            </tr>
            <tr>
                <td>Bulk Mobile Sale</td>
                <td><?php echo "<b style=background-color:yellow;text-align:right;font-size:14px;>".$normalUserDataSum['bulk_mobile_sale']."</b>".$bulk_mobile_sale_descDetail; ?></td>
                <td style="background:bisque;"><?php echo "<b style=background-color:yellow;text-align:right;font-size:14px;>".$otherUserDataSum['bulk_mobile_sale']."</b>".$bulk_mobile_sale_descDetail; ?></td>
            </tr>
            <tr>
                <td>Bulk Mobile Refund</td>
                <td><?php echo $normalUserDataSum['bulk_mobile_refund']; ?></td>
                <td style="background:bisque;"><?php echo $otherUserDataSum['bulk_mobile_refund']; ?></td>
            </tr>
            <tr>
                <td>Mobile Sale</td>
                <td><?php echo "<b style=background-color:yellow;text-align:right;font-size:14px;>".$normalUserDataSum['mobile_sale']."</b>".$mobile_sale_descDetail; ?></td>
                <td style="background:bisque;"><?php echo "<b style=background-color:yellow;text-align:right;font-size:14px;>".$otherUserDataSum['mobile_sale']."</b>".$mobile_sale_descDetail; ?></td>
            </tr>
            <tr>
                <td>Mobile Purchase</td>
                <td><?php echo $normalUserDataSum['mobile_purchase']; ?></td>
                <td style="background:bisque;"><?php echo $otherUserDataSum['mobile_purchase']; ?></td>
            </tr>
            <tr>
                <td>Mobile Refund</td>
                <td><?php echo $normalUserDataSum['mobile_refund']; ?></td>
                <td style="background:bisque;"><?php echo $otherUserDataSum['mobile_refund']; ?></td>
            </tr>
            <tr>
                <td>Total Sale</td>
                <td><?php echo $normalUserDataSum['total_sale']; ?></td>
                <td style="background:bisque;"><?php echo $otherUserDataSum['total_sale']; ?></td>
            </tr>
            <tr>
                <td>Total Refund</td>
                <td><?php echo $normalUserDataSum['total_refund']; ?></td>
                <td style="background:bisque;"><?php echo $otherUserDataSum['total_refund']; ?></td>
            </tr>
            <tr>
                <td>Net Sale</td>
                <td><?php echo $normalUserDataSum['net_sale']; ?></td>
                <td style="background:bisque;"><?php echo $otherUserDataSum['net_sale']; ?></td>
            </tr>
            <tr>
                <td>Net Card</td>
                <td><?php echo "<b style=background-color:yellow;text-align:right;font-size:14px;>".$normalUserDataSum['net_card']."</b>".$net_card_desc; ?></td>
                <td style="background:bisque;"><?php echo "<b style=background-color:yellow;text-align:right;font-size:14px;>".$otherUserDataSum['net_card']."</b>".$t_net_card_desc;?></td>
            </tr>
            <tr>
                <td>Net Credit</td>
                <td><?php echo "<b style=background-color:yellow;text-align:right;font-size:14px;>".$normalUserDataSum['net_credit']."</b>".$net_credit_desc_all; ?></td>
                <td style="background:bisque;"><?php echo "<b style=background-color:yellow;text-align:right;font-size:14px;>".$otherUserDataSum['net_credit']."</b>".$t_net_credit_desc_all; ?></td>
            </tr>
            <tr>
                <td>Net Bnk Tnsfer</td>
                <td><?php echo "<b style=background-color:yellow;text-align:right;font-size:14px;>".$normalUserDataSum['net_bnk_tnsfer']."</b>".$net_bank_desc; ?></td>
                <td style="background:bisque;"><?php echo "<b style=background-color:yellow;text-align:right;font-size:14px;>".$otherUserDataSum['net_bnk_tnsfer']."</b>".$t_net_bank_desc; ?></td>
            </tr>
            <tr>
                <td>Net Cheque Payment</td>
                <td><?php echo "<b style=background-color:yellow;text-align:right;font-size:14px;>".$normalUserDataSum['net_cheque_payment']."</b>".$net_cheque_desc; ?></td>
                <td style="background:bisque;"><?php echo "<b style=background-color:yellow;text-align:right;font-size:14px;>".$otherUserDataSum['net_cheque_payment']."</b>".$t_net_cheque_desc; ?></td>
            </tr>
            <tr>
                <td>Cash In Hand</td>
                <td><?php echo "<b style=background-color:yellow;text-align:right;font-size:14px;>".$normalUserDataSum['cash_in_hand'].$norCashInHandDetail; ?></td>
                <td style="background:bisque;"><?php echo "<b style=background-color:yellow;text-align:right;font-size:14px;>".$otherUserDataSum['cash_in_hand']."</b>".$othCashInHandDetail; ?></td>
            </tr>
            <tr>
                <td>credit to cash(Prvs Payments)</td>
                <td><?php echo "<b style=background-color:yellow;text-align:right;font-size:14px;>".$normalUserDataSum['credit_to_cash']."</b>".$CreditToCashDetail; ?></td>
                <td style="background:bisque;"><?php echo "<b style=background-color:yellow;text-align:right;font-size:14px;>".$otherUserDataSum['credit_to_cash']."</b>".$t_CreditToCashDetail; ?></td>
            </tr>
            <tr>
                <td>credit to other payment(Prvs Payments)</td>
                <td><?php echo "<b style=background-color:yellow;text-align:right;font-size:14px;>".$normalUserDataSum['credit_to_other_payment']."</b>".$CreditToOtherDetail; ?></td>
                <td style="background:bisque;"><?php echo "<b style=background-color:yellow;text-align:right;font-size:14px;>".$otherUserDataSum['credit_to_other_payment']."</b>".$t_CreditToOtherDetail; ?></td>
            </tr>
                    
        </table>
<?php }
?>

<?php echo $this->Form->end();?>
<script>
    jQuery(function() {
		jQuery( "#datepicker1" ).datepicker({ dateFormat: "d M yy" });
        jQuery( "#datepicker2" ).datepicker({ dateFormat: "d M yy" });
	});
</script>
<script>
   $('#TodaySaleKiosk').change(function(){
	$.blockUI({ message: 'Loading ...' });
        var kiskId = $('#TodaySaleKiosk').val();
        //alert(kiskId);
        //if (document.getElementById('KioskTodaySaleDashboardForm')) {
            var action = $('#KioskTodaySaleDashboardForm').attr('action');
            var formid = '#KioskTodaySaleDashboardForm';
        //}
        var newAction = action + '/' + kiskId;
        $(formid).attr('action',newAction);
        this.form.submit();
	  });
   $('#TodaySaleUser').change(function(){
//	$.blockUI({ message: 'Loading ...' });
//        var userId = $('#TodaySaleUser').val();
//        //alert(userId);
//        //if (document.getElementById('KioskTodaySaleDashboardForm')) {
//            var action = $('#KioskTodaySaleDashboardForm').attr('action');
//            var formid = '#KioskTodaySaleDashboardForm';
//        //}
//            var newAction = action + '/' + userId;
//            $(formid).attr('action',newAction);
//        this.form.submit();
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