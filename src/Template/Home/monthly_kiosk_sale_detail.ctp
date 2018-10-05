<?php
use Cake\Core\Configure;
use Cake\Core\Configure\Engine\PhpConfig;
?>
<?php $currency = Configure::read('CURRENCY_TYPE');
//$this->Number->addFormat('BRL', array('before' => "$currency ", 'negative'=>'-','zero'=>"$currency 0.00", 'escape' => false));
//pr($dateWiseSaleArr);
?>
<?=$this->Form->create('MonthlyKioskSale');?>
<?=$this->Form->input('month', array('style' => "width: 100px;", 'div' => false, 'label' => false, 'id' => 'datepicker1', 'readonly' => 'readonly'));?>
<?=$this->Form->input('kiosk', array('options' => $kiosks, 'div' => false, 'label' => false));?>
<?=$this->Form->submit('Search',array('name'=>'submit'));?>
<?=$this->Form->end();?>
<table width='100%'>
    <tr>
        <th>Day</th>
        <th>Date</th>
        <th>Total Sale</th>
        <th>Mobile Purchase</th>
        <th>Total Refund</th>
        <th>Card Payment</th>
        <th>Cash In Hand</th>
        <th>Net Sale</th>
    </tr>
    <?php if(isset($dateWiseSaleArr)){
        $total_sale = 0;
        $total_mobile_purchase = 0;
        $total_refund = 0;
        $total_card_payment = 0;
        $total_cash_in_hand = 0;
        $total_net_sale = 0;
            foreach($dateWiseSaleArr as $date => $data){
                $total_sale = $total_sale+$data['sale'];
                $total_mobile_purchase = $total_mobile_purchase+$data['mobile_purchase'];
                $total_card_payment = $total_card_payment  + $data['card_payment'];
                $total_cash_in_hand = $total_cash_in_hand + $data['cash_in_hand'];
                $total_net_sale = $total_net_sale + $data['net_sale'];
                $refund = $data['refund'];
                if($data['refund'] < 0){
                    $refund = -1*$data['refund'];
                }
                $total_refund = $total_refund + $refund;
                ?>
            <tr>
                <td><?=$data['day'];?></td>
                <td><?=date_format(date_create($date), "d/m/Y");?></td>
                <td style="background: yellow;"><?=$CURRENCY_TYPE.$data['sale'];?></td>
                <td style="background: lightgreen;"><?=$CURRENCY_TYPE.$data['mobile_purchase'];?></td>
                <td style="background: #FFE4C4;"><?=$CURRENCY_TYPE.$refund;?></td>
                <td style="background: #9E94DE;"><?=$CURRENCY_TYPE.$data['card_payment'];?></td>
                <td style="background: #9ACD32;"><?=$CURRENCY_TYPE.$data['cash_in_hand'];?></td>
                <td style="color: red;"><?=$CURRENCY_TYPE.$data['net_sale'];?></td>
            </tr>
            <?php }?>
            <tr>
                <td></td>
                <td></td>
                <td><?php echo $CURRENCY_TYPE.$total_sale; ?></td>
                <td><?php echo $CURRENCY_TYPE.$total_mobile_purchase; ?></td>
                <td><?php echo $CURRENCY_TYPE.$total_refund; ?></td>
                <td><?php echo $CURRENCY_TYPE.$total_card_payment; ?></td>
                <td><?php echo $CURRENCY_TYPE.$total_cash_in_hand; ?></td>
                <td><?php echo $CURRENCY_TYPE.$total_net_sale; ?></td>
            </tr>
    <?php }?>
</table>

<script>
    $(function () {
       $('#datepicker1').datepicker({
           dateFormat: "MM yy"
       });
    
    });
</script>