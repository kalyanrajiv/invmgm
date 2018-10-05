<?php
use Cake\Core\Configure;
use Cake\Core\Configure\Engine\PhpConfig;
$currency = '';//Configure::read('CURRENCY_TYPE');
$selectedKiosk = 10000;
$selectedUser = 0;
//pr($otherUserData);die;
$norRepairSaleDesc = unserialize($normalUserData['repair_sale_desc']);
$othRepairSaleDesc = unserialize($otherUserData['repair_sale_desc']);

$norUnlockSaleDesc = unserialize($normalUserData['unlock_sale_desc']);
$othUnlockSaleDesc = unserialize($otherUserData['unlock_sale_desc']);

$norProductSaleDesc = unserialize($normalUserData['product_sale_desc']);
$othProductSaleDesc = unserialize($otherUserData['product_sale_desc']);

$norQuotationDesc = unserialize($normalUserData['quotation_desc']);
$othQuotationDesc = unserialize($otherUserData['quotation_desc']);

$norCrediNoteDesc = unserialize($normalUserData['credit_note_desc']);
$othCreditNoteDesc = unserialize($otherUserData['credit_note_desc']);

$norBlkMobileSaleDesc = unserialize($normalUserData['bulk_mobile_sale_desc']);
$othBlkMobileSaleDesc = unserialize($otherUserData['bulk_mobile_sale_desc']);

$norMobileSaleDesc = unserialize($normalUserData['mobile_sale_desc']);
$othMobileSaleDesc = unserialize($otherUserData['mobile_sale_desc']);

$norNetCardDesc = unserialize($normalUserData['net_card_desc']);
$othNetCardDesc = unserialize($otherUserData['net_card_desc']);

$norNetCreditDesc = unserialize($normalUserData['net_credit_desc']);
$othNetCreditDesc = unserialize($otherUserData['net_credit_desc']);

$norbnkTnsfrDesc = unserialize($normalUserData['net_bnk_tnsfer_desc']);
$othbnkTnsfrDesc = unserialize($otherUserData['net_bnk_tnsfer_desc']);

$norNetChequePayDesc = unserialize($normalUserData['net_cheque_payment_desc']);
$othNetChequePayDesc = unserialize($otherUserData['net_cheque_payment_desc']);

$norCashInHandDesc = unserialize($normalUserData['cash_in_hand_desc']);
$othCashInHandDesc = unserialize($otherUserData['cash_in_hand_desc']);

$norCreditToCashDesc = unserialize($normalUserData['credit_to_cash_desc']);
$othCreditToCashDesc = unserialize($otherUserData['credit_to_cash_desc']);

$norCreditToOtherDesc = unserialize($normalUserData['credit_to_other_payment_desc']);
$othCreditToOtherDesc = unserialize($otherUserData['credit_to_other_payment_desc']);
//pr($norCashInHandDesc);
//pr($othCashInHandDesc);
echo $this->Form->create('KioskTotalSale',array('id'=>'KioskTodaySaleDashboardForm','url'=>array('controller'=>'products','action'=>'dashboardData')));
?>
<table width="100%">
    <tr>
        <td>
            <?php
            //pr($this->request->data['date']);die;
            if(!empty($this->request->data['date'])){
                $selectedDate = $this->request->data['date'];
            }else{
                $selectedDate = date('d M Y');
            }
            ?>
            <input type="text" name="date" id="datepicker1" placeholder="start date" style="width: 80px;margin-top: 30px;" value="<?=$selectedDate;?>">
            <input type="text" name="end_date" id="datepicker2" placeholder="end date" style="width: 80px;margin-top: 30px;" >
            <input type="submit" name="submit1" id="submit1" value="Submit">
            <input type="button" name="reset" id="reset" value="Reset" style="margin-left: 97px;width: 68px;">
        </td>
        <td><?php echo $this->Form->input('kiosk',array('options'=>$kiosks,'id'=>'TodaySaleKiosk','default'=>$selectedKiosk))?></td>
        <td><?php echo $this->Form->input('user',array('options'=>$users,'id'=>'TodaySaleUser','default'=>$selectedUser))?></td>
        <td>
    </tr>
    <tr>
        <th width='20%'>&nbsp;</th>
        <th width='20%'>Today(Normal)</th>
        <th width='20%'>Today(Other)</th>
    </tr>
    <tr>
        <td><strong>Repair Sale</strong></td>
        <?php
        $norrepairDetail = "&nbsp;<font color=blue><strong>Detail: </strong>cash(<strong>$norRepairSaleDesc[cash]</strong>),card(<strong>$norRepairSaleDesc[card]</strong>)</font>";
        $othrepairDetail = "&nbsp;<font color=blue><strong>Detail: </strong>cash(<strong>$othRepairSaleDesc[cash]</strong>),card(<strong>$othRepairSaleDesc[card]</strong>)</font>";
        ?>
        <td><?php echo $currency.$normalUserData['repair_sale'];?><?php if(!empty($norRepairSaleDesc)){echo $norrepairDetail;} ?></td>
        <td><?php echo $currency.$otherUserData['repair_sale'];?><?php if(!empty($othRepairSaleDesc)){echo $othrepairDetail;} ?></td>
    </tr>
    <tr style="color: blue">
        <td><strong>Repair Refund</strong></td>
        <td><?php echo $currency.$normalUserData['repair_refund'];?></td>
        <td><?php echo $currency.$otherUserData['repair_refund'];?></td>
    </tr>
    <tr>
        <td><strong>Unlock Sale</strong></td>
        <?php
        $norUnlockSale = "&nbsp;<font color=blue><strong>Detail: </strong>cash(<strong>$norUnlockSaleDesc[cash]</strong>),card(<strong>$norUnlockSaleDesc[card]</strong>)</font>";
        $othUnlockSale = "&nbsp;<font color=blue><strong>Detail: </strong>cash(<strong>$othUnlockSaleDesc[cash]</strong>),card(<strong>$othUnlockSaleDesc[card]</strong>)</font>";
        ?>
        <td><?php echo $currency.$normalUserData['unlock_sale'];?><?php if(!empty($norUnlockSaleDesc)){echo $norUnlockSale;} ?></td>
        <td><?php echo $currency.$otherUserData['unlock_sale'];?><?php if(!empty($othUnlockSaleDesc)){echo $othUnlockSale;} ?></td>
    </tr>
    <tr style="color: blue">
        <td><strong>Unlock Refund</strong></td>
        <td><?php echo $currency.$normalUserData['unlock_refund'];?></td>
        <td><?php echo $currency.$otherUserData['unlock_refund'];?></td>
    </tr>
    <tr>
        <td><strong>Product Sale</strong></td>
        <?php
        $norProductSale = "&nbsp;<font color=blue><strong>Detail: </strong>cash(<strong>$norProductSaleDesc[cash]</strong>),card(<strong>$norProductSaleDesc[card]</strong>),credit(<strong>$norProductSaleDesc[credit]</strong>),bank transfer(<strong>$norProductSaleDesc[bank_transfer]</strong>),cheque(<strong>$norProductSaleDesc[cheque]</strong>)</font>";
        
        $othProductSlae = "&nbsp;<font color=blue><strong>Detail: </strong>cash(<strong>$othProductSaleDesc[cash]</strong>),card(<strong>$othProductSaleDesc[card]</strong>),credit(<strong>$othProductSaleDesc[credit]</strong>),bank transfer(<strong>$othProductSaleDesc[bank_transfer]</strong>),cheque(<strong>$othProductSaleDesc[cheque]</strong>)</font>";
        ?>
        <td><?php echo $currency.$normalUserData['product_sale'];?><?php if(!empty($norProductSaleDesc)){echo $norProductSale;} ?></td>
        <td><?php echo $currency.$otherUserData['product_sale'];?><?php if(!empty($othProductSaleDesc)){echo $othProductSlae;} ?></td>
    </tr>
    <tr>
        <td><strong>Quotation</strong></td>
        <?php
        //$norQuotationDetail = "&nbsp;<strong>Detail: </strong>cash(<strong>$norCrediNoteDesc[cash]</strong>),card(<strong>$norCrediNoteDesc[card]</strong>),credit(<strong>$norCrediNoteDesc[credit]</strong>),bank transfer(<strong>$norCrediNoteDesc[bank_transfer]</strong>),cheque(<strong>$norCrediNoteDesc[cheque]</strong>)";
        $othQuotationDetail = "&nbsp;<font color=blue><strong>Detail: </strong>cash(<strong>$othQuotationDesc[cash]</strong>),card(<strong>$othQuotationDesc[card]</strong>),credit(<strong>$othQuotationDesc[credit]</strong>),bank transfer(<strong>$othQuotationDesc[bank_transfer]</strong>),cheque(<strong>$othQuotationDesc[cheque]</strong>)<font color=blue>";
        ?>
        <td><?php echo $currency.$normalUserData['quotation'];?></td>
        <td><?php echo $currency.$otherUserData['quotation'];?><?php if(!empty($othQuotationDesc)){echo $othQuotationDetail;} ?></td>
    </tr>
    <tr style="color: blue">
        <td><strong>Credit Note</strong></td>
        <?php
        $norCreditNoteDetail = "&nbsp;<strong>Detail: </strong>cash(<strong>$norCrediNoteDesc[cash]</strong>),card(<strong>$norCrediNoteDesc[card]</strong>),credit(<strong>$norCrediNoteDesc[credit]</strong>),bank transfer(<strong>$norCrediNoteDesc[bank_transfer]</strong>),cheque(<strong>$norCrediNoteDesc[cheque]</strong>)";
        $othCreditNoteDetail = "&nbsp;<strong>Detail: </strong>cash(<strong>$othCreditNoteDesc[cash]</strong>),card(<strong>$othCreditNoteDesc[card]</strong>),credit(<strong>$othCreditNoteDesc[credit]</strong>),bank transfer(<strong>$othCreditNoteDesc[bank_transfer]</strong>),cheque(<strong>$othCreditNoteDesc[cheque]</strong>)";
        ?>
        <td><?php echo $currency.$normalUserData['credit_note'];?><?php if(!empty($norCrediNoteDesc)){echo $norCreditNoteDetail;}?></td>
        <td><?php echo $currency.$otherUserData['credit_note'];?><?php if(!empty($othCreditNoteDesc)){echo $othCreditNoteDetail;}?></td>
    </tr>
    <tr>
        <td><strong>Credit Quotation</strong></td>
        <td><?php echo $currency.$normalUserData['credit_quotation'];?></td>
        <td><?php echo $currency.$otherUserData['credit_quotation'];?></td>
    </tr>
    <tr style="color: blue">
        <td><strong>Product Refund</strong></td>
        <td><?php echo $currency.$normalUserData['product_refund'];?></td>
        <td><?php echo $currency.$otherUserData['product_refund'];?></td>
    </tr>
    <tr>
        <td><strong>Bulk Mobile Sale</strong></td>
        <?php
        $norBlkMobileSaleDetail = "<font color=blue>&nbsp;<strong>Detail: </strong>cash(<strong>$norBlkMobileSaleDesc[cash]</strong>),card(<strong>$norBlkMobileSaleDesc[card]</strong>)</font>";
        
        $othBlkMobileSaleDetail = "&nbsp;<font color=blue><strong>Detail: </strong>cash(<strong>$othBlkMobileSaleDesc[cash]</strong>),card(<strong>$othBlkMobileSaleDesc[card]</strong>)</font>";
        ?>
        <td><?php echo $currency.$normalUserData['bulk_mobile_sale'];?><?php if(!empty($norBlkMobileSaleDesc)){echo $norBlkMobileSaleDetail;}?></td>
        <td><?php echo $currency.$otherUserData['bulk_mobile_sale'];?><?php if(!empty($othBlkMobileSaleDesc)){echo $othBlkMobileSaleDetail;}?></td>
    </tr>
    <tr style="color: blue">
        <td><strong>Bulk Mobile Refund</strong></td>
        <td><?php echo $currency.$normalUserData['bulk_mobile_refund'];?></td>
        <td><?php echo $currency.$otherUserData['bulk_mobile_refund'];?></td>
    </tr>
    <tr>
        <td><strong>Mobile Sale</strong></td>
        <?php
        $norMobileSaleDetail = "&nbsp;<font color=blue><strong>Detail: </strong>cash(<strong>$norMobileSaleDesc[cash]</strong>),card(<strong>$norMobileSaleDesc[card]</strong>)</font>";
        
        $othMobileSaleDetail = "&nbsp;<font color=blue><strong>Detail: </strong>cash(<strong>$othMobileSaleDesc[cash]</strong>),card(<strong>$othMobileSaleDesc[card]</strong>)</font>";
        ?>
        <td><?php echo $currency.$normalUserData['mobile_sale'];?><?php if(!empty($norMobileSaleDesc)){echo $norMobileSaleDetail;}?></td>
        <td><?php echo $currency.$otherUserData['mobile_sale'];?><?php if(!empty($othMobileSaleDesc)){echo $othMobileSaleDetail;}?></td>
    </tr>
    <tr>
        <td><strong>Mobile Purchase</strong></td>
        <td><?php echo $currency.$normalUserData['mobile_purchase'];?></td>
        <td><?php echo $currency.$otherUserData['mobile_purchase'];?></td>
    </tr>
    <tr style="color: blue">
        <td><strong>Mobile Refund</strong></td>
        <td><?php echo $currency.$normalUserData['mobile_refund'];?></td>
        <td><?php echo $currency.$otherUserData['mobile_refund'];?></td>
    </tr>
    <tr>
        <td><strong>Total Sale</strong></td>
        <td><?php echo $currency.$normalUserData['total_sale'];?></td>
        <td><?php echo $currency.$otherUserData['total_sale'];?></td>
    </tr>
    <tr style="color: blue">
        <td><strong>Total Refund</strong></td>
        <td><?php echo $currency.$normalUserData['total_refund'];?></td>
        <td><?php echo $currency.$otherUserData['total_refund'];?></td>
    </tr>
    <tr>
        <td><strong>Net Sale</strong></td>
        <td><?php echo $currency.$normalUserData['net_sale'];?></td>
        <td><?php echo $currency.$otherUserData['net_sale'];?></td>
    </tr>
    <tr>
        <td><strong>Net Card</strong></td>
        <?php
        $nortotalNetCardDetail = $norNetCardDesc['repair']+$norNetCardDesc['Unlock']+$norNetCardDesc['Product']+$norNetCardDesc['Blk']+$norNetCardDesc['Mobile']+$norNetCardDesc['prev_recpts_sale']+$norNetCardDesc['credit_note'];
        
        $norNetCardDetail = "&nbsp;<font color=blue><strong>Detail: </strong>repair(<strong>$norNetCardDesc[repair]</strong>)+unlock(<strong>$norNetCardDesc[Unlock]</strong>)+product(<strong>$norNetCardDesc[Product]</strong>)+blk(<strong>$norNetCardDesc[Blk]</strong>)+mobile(<strong>$norNetCardDesc[Mobile]</strong>)+<br/>prev_recpts_sale(<strong>$norNetCardDesc[prev_recpts_sale]</strong>)+credit note(<strong>$norNetCardDesc[credit_note]</strong>) = $nortotalNetCardDetail</font>";
        
        $othtotalNetCardDetail = $othNetCardDesc['repair']+$othNetCardDesc['Unlock']+$othNetCardDesc['Product']+$othNetCardDesc['Blk']+$othNetCardDesc['Mobile']+$othNetCardDesc['prev_recpts_sale']+$othNetCardDesc['credit_note'];
        
        $othNetCardDetail = "&nbsp;<font color=blue><strong>Detail: </strong>repair(<strong>$othNetCardDesc[repair]</strong>)+unlock(<strong>$othNetCardDesc[Unlock]</strong>)+product(<strong>$othNetCardDesc[Product]</strong>)+blk(<strong>$othNetCardDesc[Blk]</strong>)+mobile(<strong>$othNetCardDesc[Mobile]</strong>)+<br/>prev_recpts_sale(<strong>$othNetCardDesc[prev_recpts_sale]</strong>)+credit note(<strong>$othNetCardDesc[credit_note]</strong>) = $othtotalNetCardDetail</font>";
        ?>
        <td><?php echo $currency.$normalUserData['net_card'];?><?php if(!empty($norNetCardDesc)){echo $norNetCardDetail;}?></td>
        <td><?php echo $currency.$otherUserData['net_card'];?><?php if(!empty($othNetCardDesc)){echo $othNetCardDetail;}?></td>
    </tr>
    <tr>
        <td><strong>Net Credit</strong></td>
        <?php
        $norTotalNetCreditDetail = $norNetCreditDesc[0]+$norNetCreditDesc[1];
        $norNetCreditDetail = "&nbsp;<font color=blue><strong>Detail: </strong>($norNetCreditDesc[0])+($norNetCreditDesc[1]) = $norTotalNetCreditDetail";
        
        $othTotalNetCreditDetail = $othNetCreditDesc[0]+$othNetCreditDesc[1];
        $othNetCreditDetail = "&nbsp;<font color=blue><strong>Detail: </strong>($othNetCreditDesc[0])+($othNetCreditDesc[1]) = $othTotalNetCreditDetail";
        ?>
        <td><?php echo $currency.$normalUserData['net_credit'];?><?php if(!empty($norNetCreditDesc)){echo $norNetCreditDetail;}?></td>
        <td><?php echo $currency.$otherUserData['net_credit'];?><?php if(!empty($othNetCreditDesc)){echo $othNetCreditDetail;}?></td>
    </tr>
    <tr>
        <td><strong>Net Bnk Tnsfer</strong></td>
        <?php
        $norTotalNetBnkTrnsfrDetail = $norbnkTnsfrDesc[0]+$norbnkTnsfrDesc[1];
        $norNetBnkTrnsfrDetail = "&nbsp;<font color=blue><strong>Detail: </strong>($norbnkTnsfrDesc[0])+($norbnkTnsfrDesc[1]) = $norTotalNetBnkTrnsfrDetail";
        
        $othTotalNetCreditDetail = $othbnkTnsfrDesc[0]+$othbnkTnsfrDesc[1];
        $othNetBnkTrnsfrDetail = "&nbsp;<font color=blue><strong>Detail: </strong>($othbnkTnsfrDesc[0])+($othbnkTnsfrDesc[1]) = $othTotalNetCreditDetail";
        ?>
        <td><?php echo $currency.$normalUserData['net_bnk_tnsfer'];?><?php if(!empty($norbnkTnsfrDesc)){echo $norNetBnkTrnsfrDetail;}?></td>
        <td><?php echo $currency.$otherUserData['net_bnk_tnsfer'];?><?php if(!empty($othbnkTnsfrDesc)){echo $othNetBnkTrnsfrDetail;}?></td>
    </tr>
    <tr>
        <td><strong>Net Cheque Payment</strong></td>
        <?php
        $norTotalNetChequePayDetail = $norNetChequePayDesc[0]+$norNetChequePayDesc[1];
        $norNetChequePayDetail = "&nbsp;<font color=blue><strong>Detail: </strong>($norNetChequePayDesc[0])+($norNetChequePayDesc[1]) = $norTotalNetBnkTrnsfrDetail";
        
        $othTotalNetChequePayDetail = $othNetChequePayDesc[0]+$othNetChequePayDesc[1];
        $othNetChequePayDetail = "&nbsp;<font color=blue><strong>Detail: </strong>($othNetChequePayDesc[0])+($othNetChequePayDesc[1]) = $othTotalNetCreditDetail";
        ?>
        <td><?php echo $currency.$normalUserData['net_cheque_payment'];?><?php if(!empty($norNetChequePayDesc)){echo $norNetChequePayDetail;}?></td>
        <td><?php echo $currency.$otherUserData['net_cheque_payment'];?><?php if(!empty($othNetChequePayDesc)){echo $othNetChequePayDetail;}?></td>
    </tr>
    <tr>
        <td><strong>Cash In Hand</strong></td>
        <?php
        $norTotalCashInHandSale = $norCashInHandDesc['sale']['Repair']+$norCashInHandDesc['sale']['Unlock']+$norCashInHandDesc['sale']['Product']+$norCashInHandDesc['sale']['Blk']+$norCashInHandDesc['sale']['Mobile']+$norCashInHandDesc['sale']['prv_recpit_amt'];
        
        $norTotalCashInHandRefund = $norCashInHandDesc['refund']['Repair']+$norCashInHandDesc['refund']['Unlock']+$norCashInHandDesc['refund']['Product']+$norCashInHandDesc['refund']['Blk']+$norCashInHandDesc['refund']['Mobile']+$norCashInHandDesc['refund']['Credit_Note']+$norCashInHandDesc['refund']['Mobile_Purchase'];
        
        $norfinalCashInHand = $norTotalCashInHandSale - $norTotalCashInHandRefund;
        
        $norCashInHandDetail = "&nbsp;<font color=blue><strong>Detail: </strong>(Sale(Repair(".$norCashInHandDesc['sale']['Repair'].")+Unlock(".$norCashInHandDesc['sale']['Unlock'].")+Product(".$norCashInHandDesc['sale']['Product'].")+Blk(".$norCashInHandDesc['sale']['Blk'].")+<br/>Mobile(".$norCashInHandDesc['sale']['Mobile'].")+prv_recpit_amt(".$norCashInHandDesc['sale']['prv_recpit_amt']."))= $norTotalCashInHandSale, Refund(Repair(".$norCashInHandDesc['refund']['Repair'].")+Unlock(".$norCashInHandDesc['refund']['Unlock'].")+Product(".$norCashInHandDesc['refund']['Product'].")+Blk(".$norCashInHandDesc['refund']['Blk'].")+Mobile(".$norCashInHandDesc['refund']['Mobile'].")+<br/>Credit_Note(".$norCashInHandDesc['refund']['Credit_Note'].")+Mobile_Purchase(".$norCashInHandDesc['refund']['Mobile_Purchase'].")) ($norTotalCashInHandSale-$norTotalCashInHandRefund) = $norfinalCashInHand)";
        
        $othTotalCashInHandSale = $othCashInHandDesc['sale']['Repair']+$othCashInHandDesc['sale']['Unlock']+$othCashInHandDesc['sale']['Product']+$othCashInHandDesc['sale']['Blk']+$othCashInHandDesc['sale']['Mobile']+$othCashInHandDesc['sale']['special']+$othCashInHandDesc['sale']['prv_recpit_amt']+$othCashInHandDesc['sale']['prv_credit_to_cash'];
        
        $othTotalCashInHandRefund = $othCashInHandDesc['refund']['Repair']+$othCashInHandDesc['refund']['Unlock']+$othCashInHandDesc['refund']['Product']+$othCashInHandDesc['refund']['Blk']+$othCashInHandDesc['refund']['Mobile']+$othCashInHandDesc['refund']['Credit_Note']+$othCashInHandDesc['refund']['Mobile_Purchase']+$othCashInHandDesc['refund']['special_credit_note'];
        
        $othfinalCashInHand = $othTotalCashInHandSale - $othTotalCashInHandRefund;
        
        $othCashInHandDetail = "&nbsp;<font color=blue><strong>Detail: </strong>(Sale(Repair(".$othCashInHandDesc['sale']['Repair'].")+Unlock(".$othCashInHandDesc['sale']['Unlock'].")+Product(".$othCashInHandDesc['sale']['Product'].")+Blk(".$othCashInHandDesc['sale']['Blk'].")+<br/>Mobile(".$othCashInHandDesc['sale']['Mobile'].")+special(".$othCashInHandDesc['sale']['special'].")+prv_recpit_amt(".$othCashInHandDesc['sale']['prv_recpit_amt'].")+prv_credit_to_cash(".$othCashInHandDesc['sale']['prv_credit_to_cash']."))= $othTotalCashInHandSale, Refund(Repair(".$othCashInHandDesc['refund']['Repair'].")+Unlock(".$othCashInHandDesc['refund']['Unlock'].")+Product(".$othCashInHandDesc['refund']['Product'].")+Blk(".$othCashInHandDesc['refund']['Blk'].")+Mobile(".$othCashInHandDesc['refund']['Mobile'].")+<br/>Credit_Note(".$othCashInHandDesc['refund']['Credit_Note'].")+Mobile_Purchase(".$othCashInHandDesc['refund']['Mobile_Purchase'].")+special_credit_note(".$othCashInHandDesc['refund']['special_credit_note'].")) ($othTotalCashInHandSale-$othTotalCashInHandRefund) = $othfinalCashInHand)";
        ?>
        <td><?php echo $currency.$normalUserData['cash_in_hand'];?><?php if(!empty($norCashInHandDesc)){echo $norCashInHandDetail;}?></td>
        <td><?php echo $currency.$otherUserData['cash_in_hand'];?><?php if(!empty($othCashInHandDesc)){echo $othCashInHandDetail;}?></td>
    </tr>
    <tr>
        <td><strong>credit to cash(Prvs Payments)</strong></td>
        <?php
        $norCreditToCashDetail = "&nbsp;<font color=blue><strong>Detail: </strong>invoice cash(<strong>$norCreditToCashDesc[invoice_cash]</strong>),Quotation cash(<strong>$norCreditToCashDesc[Quotation_cash]</strong>),credit cash(<strong>$norCreditToCashDesc[credit_cash]</strong>),credit quotation cash(<strong>$norCreditToCashDesc[credit_quotation_cash]</strong>)</font>";
        
        $othCreditToCashDetail = "&nbsp;<font color=blue><strong>Detail: </strong>invoice cash(<strong>$othCreditToCashDesc[invoice_cash]</strong>),Quotation cash(<strong>$othCreditToCashDesc[Quotation_cash]</strong>),credit cash(<strong>$othCreditToCashDesc[credit_cash]</strong>),credit quotation cash(<strong>$othCreditToCashDesc[credit_quotation_cash]</strong>)</font>";
        ?>
        <td><?php echo $currency.$normalUserData['credit_to_cash'];?><?php if(!empty($norCreditToCashDesc)){echo $norCreditToCashDetail;}?></td>
        <td><?php echo $currency.$otherUserData['credit_to_cash'];?><?php if(!empty($othCreditToCashDesc)){echo $othCreditToCashDetail;}?></td>
    </tr>
    <tr>
        <td><strong>credit to other payment(Prvs Payments)</strong></td>
        <?php
        $norCreditToOtherDetail = "&nbsp;<font color=blue><strong>Detail: </strong>total bank transfer(<strong>$norCreditToOtherDesc[total_bank_transfer]</strong>),total card Payment(<strong>$norCreditToOtherDesc[total_card_Payment]</strong>),total cheque payment(<strong>$norCreditToOtherDesc[total_cheque_payment]</strong>)</font>";
        
        $othCreditToOtherDetail = "&nbsp;<font color=blue><strong>Detail: </strong>total bank transfer(<strong>$othCreditToOtherDesc[total_bank_transfer]</strong>),total card Payment(<strong>$othCreditToOtherDesc[total_card_Payment]</strong>),total cheque payment(<strong>$othCreditToOtherDesc[total_cheque_payment]</strong>)</font>";
        ?>
        <td><?php echo $currency.$normalUserData['credit_to_other_payment'];?><?php if(!empty($norCreditToOtherDesc)){echo $norCreditToOtherDetail;}?></td>
        <td><?php echo $currency.$otherUserData['credit_to_other_payment'];?><?php if(!empty($othCreditToOtherDesc)){echo $othCreditToOtherDetail;}?></td>
    </tr>
</table>
** modified = (repairCash + unlockCash + mobileCash + productCash) - (repairRefund + unlockRefund + mobileRefund + productRefund).</br>
** cashInHand = (repairCash + unlockCash + mobileCash + productCash + blkCash) -(todayMobilePurchase+todayTotalCard+todaysCreditNodeSale)
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