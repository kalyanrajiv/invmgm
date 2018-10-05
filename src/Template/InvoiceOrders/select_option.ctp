<?php
use Cake\Core\Configure;
use Cake\Core\Configure\Engine\PhpConfig;
?>
<?php
	$currency = Configure::read('CURRENCY_TYPE');
	//$this->Number->addFormat('BRL', array('before' => "$currency ", 'negative'=>'-','zero'=>"$currency 0.00", 'escape' => false));
?>
<?php
	$screenHint = $hintId = "";
		if(!empty($hint)){
		   $screenHint = $hint["ScreenHint"]["hint"];
		   $hintId = $hint["ScreenHint"]["id"];
		}
      $updateUrl = "/img/16_edit_page.png";
?>
<div class="invoiceOrders index">
	<h2><?php echo __('Please Select Invoice Type')?></a>
	</h2>
	<form action="" method="post">
	<table cellpadding="0" cellspacing="0">
	<thead>
	<tr>
			<?php
		$loggedInUser = $this->request->session()->read('Auth.User.username');
		$specialInvoice = 1;
		if (preg_match('/'.QUOT_USER_PREFIX.'/',$loggedInUser)){//checking if the user has right for tempering
		?>
	 <td style="width: 10px;">Quotation?</td>
	 <td style="width: 10px;">Yes<input id = "special_yes" type='radio' name='special_invoice' value='1' <?=($specialInvoice==1) ? "checked" : "" ; ?>/></td>
	 <td style="width: 59px;">No<input id = "special_no" type='radio' name='special_invoice' value='0' <?=($specialInvoice==0) ? "checked" : "" ; ?>/></td>
	 <input name="_csrfToken" autocomplete="off" value="<?php echo $token = $this->request->getParam('_csrfToken');?>" type="hidden">
    <?php
		} ?>
	</tr>
	<tr><td>
				<input type="submit" name="submit" value = "submit"/>
	</td>
	<td><input type="hidden" name="id" value="<?php echo $id;?>" /></td>
	</re>
	</thead>
	<tbody><?php //pr($invoiceOrders);?>
	
	</tbody>
	</table>
	</form>
	<p>
	
	</div>
</div>
<div class="actions">
	<h3><?php echo __('Actions'); ?></h3>
	<ul>
		<li><?php echo $this->Html->link(__('List Customers'), array('controller' => 'customers', 'action' => 'index')); ?> </li>
	</ul>
</div>
<script>
$(function() {
  $( document ).tooltip({
   content: function () {
    return $(this).prop('title');
   }
  });
 });
</script>