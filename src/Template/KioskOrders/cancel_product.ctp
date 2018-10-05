 <style>
 #remote .tt-dropdown-menu {
  max-height: 250px;
  overflow-y: auto;
}
 #remote .twitter-typehead {
  max-height: 250px;
  overflow-y: auto;
}
.tt-dataset, .tt-dataset-product {
  max-height: 250px;
  overflow-y: auto;
}
.row_hover:hover{
 color:blue;
 background-color:yellow;
}
</style>
 <?php
    if(!isset($search_kw)){
        $search_kw = "";
    }
    if(!isset($start_date)){
        $start_date = "";
    }
    if(!isset($end_date)){
        $end_date = "";
    }
 ?>
 <div class="mobilePurchases index">
     <form action='<?php echo $this->request->webroot; ?>KioskOrders/search_cancel' method = 'get'>
        <fieldset>
            <legend>Search</legend>
            <div style="height: 69px;">
                <table>
                    <tr>
                        <td>
                            <div id='remote'>
                                <input class="typeahead" type = "text" name = "search_kw" id = "search_kw" placeholder = "product name or code" autofocus style = "width:150px;height: 25px;margin-top: 20px;"value='<?php echo $search_kw;?>'/>
                            </div>
                            
                        </td>
                        <td><input type = "text" id='datepicker1' readonly='readonly' name = "start_date" placeholder = "From Date"  style = "width:90px;height: 25px;margin-top: 20px;" value='<?php echo $start_date;?>' /></td>
						<td><input type = "text" id='datepicker2' readonly='readonly' name = "end_date" placeholder = "To Date"  style = "width:90px;height: 25px;margin-top: 20px;" value='<?php echo $end_date;?>'  /></td>
                        <?php
                            if( $this->request->session()->read('Auth.User.group_id') == ADMINISTRATORS || $this->request->session()->read('Auth.User.group_id') == FRANCHISE_OWNER ||
                                $this->request->session()->read('Auth.User.group_id') == MANAGERS){
                                echo "<td>";
                                    if(!empty($kiosk_id)){
                                        //echo $kiosk_id;
                                        //pr($kiosks);
                                        echo $this->Form->input(null, array(
                                                                                'options' => $kiosks,
                                                                                'label' => 'Kiosk',
                                                                                'div' => false,
                                                                                'name' => 'cancel[kiosk_id]',
                                                                                'id'=> 'kioskid',
                                                                                'value' => $kiosk_id,
                                                                               'empty' => 'Select Kiosk',
                                                                                'style' => 'width:185px'
                                                                            )
                                                                );
                                    }else{
        								echo $this->Form->input(null, array(
											'options' => $kiosks,
											'label' => 'Kiosk',
											'div' => false,
											 'name' => 'cancel[kiosk_id]',
											'id'=> 'kioskid',
                                             'empty' => 'Select Kiosk',
											'style' => 'width:185px'
												)
											);
                                    }
                            }
                        ?>
                        </td>
                        <td><input type = "submit" value = "Search" name = "submit" 'style' ='width:185px'/>
						<td><input type='button' name='reset' value='Reset' style='padding:6px 8px;color:#333; border:1px solid #bbb;border-radius:4px;' onClick='reset_search();'/>
                        <table>
                             
                            <tr>
								<td colspan='2'>
                                     <?php $loggedInUser = $this->request->session()->read('Auth.User.username') ;
                                            if (preg_match('/'.QUOT_USER_PREFIX.'/',$loggedInUser)){//checking if the user has right for tempering
                                                echo $this->Html->link(__('View special Invoices'), array('controller' => 'kiosk_product_sales', 'action' => 'dr_index')); 
                                             }
									?>
                                </td>
                            </tr>
                        </table>
                    </tr>
                </table>
            </div>    
        </fieldset>
        </form>
        <table cellpadding="0" cellspacing="0">
            <thead>
                <tr>
                    <th>Product Code</th>
                    <th>Product Title</th>
                    <th>Kiosk Name</th>
                    <th>Ordered Quantity</th>
                    <th>Remarks</th>
                    <th><?php echo $this->Paginator->sort('created'); ?></th>
                </tr>
            </thead>
            <tbody>
            <?php
                $total_quantity = 0;
                      if(!empty($result)){
                         $total_quantity = 0;
                          foreach($result as $key => $value){ 
                                $product_id = $value->product_id;
                                 $kiosk_id = $value->kiosk_id;
                                $qantity = $value->quantity;
                                $created = $value->created;
                                $remarks = $value->remarks;
                                $total_quantity += $qantity;
            ?>
                        <tr>
                                <td><?=$product_code[$product_id];?></td>
                                <td><?=$product_title[$product_id];?></td>
                                <td><?=$kiosks[$kiosk_id];?></td>
                                <td><?=$qantity;?></td>
                                <td style="width: 152px;"><?=$remarks;?></td>
                                <td><?=date("d-m-y",strtotime($created));?></td>
                        </tr>
                           
                        <?php  }
                      } 
            ?><tr>
	 <td colspan=2></td>
	 <td><b>Total Quantity</b></td>
	 <td><?=$total_quantity;?></td>
	</tr>
	</tbody>
	</table>
	 <div class="paging">
            <?= $this->Paginator->prev('< ' . __('previous')) ?>
            <?= $this->Paginator->numbers() ?>
            <?= $this->Paginator->next(__('next') . ' >') ?>
        <p><?= $this->Paginator->counter(['format' => __('Page {{page}} of {{pages}}, showing {{current}} record(s) out of {{count}} total')]) ?></p>
    </div>
</div>
<div class="actions">
	<h3><?php echo __('Actions'); ?></h3>
	<?php  if($this->request->session()->read('Auth.User.group_id') == ADMINISTRATORS || $this->request->session()->read('Auth.User.group_id') == FRANCHISE_OWNER ||
					$this->request->session()->read('Auth.User.group_id') == MANAGERS ||
					$this->request->session()->read('Auth.User.group_id') == inventory_manager){
					echo $this->element('sidebar/order_menus');
				}else{
					echo $this->element('sidebar/kiosk_order_menus');
		}?>
</div>
<script>
	function reset_search(){
		jQuery( "#datepicker1" ).val("");
		jQuery( "#datepicker2" ).val("");
		jQuery("#search_kw").val("");
        jQuery("#kioskid").val("");
	}
	jQuery(function() {
		jQuery( "#datepicker1" ).datepicker({ dateFormat: "d M yy" });
		jQuery( "#datepicker2" ).datepicker({ dateFormat: "d M yy " });
	});
</script>
<script>
 var product_dataset = new Bloodhound({
  datumTokenizer: Bloodhound.tokenizers.obj.whitespace('value'),
  queryTokenizer: Bloodhound.tokenizers.whitespace,
  //prefetch: "/products/data",
  remote: {
   url: "/products/admin_data?search=%QUERY",
    wildcard: "%QUERY"
    
  }
});

$('#remote .typeahead').typeahead(null, {
  name: 'product',
  display: 'product',
  source: product_dataset,
  limit:120,
  minlength:3,
  classNames: {
    input: 'Typeahead-input',
    hint: 'Typeahead-hint',
    selectable: 'Typeahead-selectable'
  },
  highlight: true,
  hint:true,
  templates: {
    suggestion: Handlebars.compile('<div style="background-color:lightgrey;width:400px;z-index:-5000" class="row_hover"><strong style="width:400px;color:black"><a class="row_hover" href="#-1">{{product_code}}</a></strong></div>'),
	header: Handlebars.compile("<div style='background-color:lightgrey;width:400px;z-index:-5000'><b>Search result for ({{query}}) :'</b></div>"),
	footer: Handlebars.compile("<div style='background-color:lightgrey;width:400px;z-index:-5000'><b>---------<?php echo ADMIN_DOMAIN;?>---------</b></div>"),
  }
});
</script>
<script>
$(function() {
  $( document ).tooltip({
   content: function () {
    return $(this).prop('title');
   }
  });
 });
</script>