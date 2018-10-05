<div class="stock index">
    <?php $stock_level_session = $this->request->Session()->read('stock_level_session');
	if(!$stock_level_session){?>
	  <h4>No items added to the basket!</h4>  
    <?php }else{
	    echo "<h3>Stock Level Checkout</h3>";
	    echo $this->Form->create();
	    //'null', array('url' => array('controller' => 'stock', 'action' => 'edit_stock_level_session'))
	    $rows = '';
		foreach($stock_level_session as $prdct_id => $prdctQtt){
		    $rows.= "<tr>
				    <td>".$productDetArr[$prdct_id]['product_code']."</td>
				    <td>".$productDetArr[$prdct_id]['product']."</td>
					<td>".$categoryNames[$productDetArr[$prdct_id]['category_id']]."</td>
				    <td>".$productDetArr[$prdct_id]['color']."</td>
				    <td>".$this->Form->input('quantity', array('label' => false, 'type' => 'text', 'name' => "quantity[$prdct_id]", 'value' => $prdctQtt, 'style' => 'width: 70px;'))."</td>
				    <td>".$this->Html->link('Delete',array('action'=>'delete_stock_level_session',$prdct_id),array('id'=>$productDetArr[$prdct_id]['product_code'],'onClick'=>'return reply_click(this.id)','value'=>'delete','name'=>'delete_product'))."</td>
			    </tr>";
	    }
	    
	    $flashTable = '';
	    if(!empty($rows)){
		    $flashTable = "<table>
					    <tr>
						    <th>Product Code</th>
						    <th>Product</th>
						   <th>Category</th>
						    <th>Color</th>
						    <th>Quantity</th>
					    </tr>".$rows."
					    <tr>
						<td></td>
						<td>
						    <table>
							<tr>
							    <td>".$this->Form->input('Update Quantity', array('label' => false, 'type' => 'submit', 'name' => 'update_quantity', 'style' => 'float: right;margin-top: 1px;'))."</td>
							    <td class='actions' style='padding-top: 27px;'>".$this->Html->link(__('Edit Basket'), array('action' => 'stock_level'), array('style' => 'background: #5BA150;color: #fff;background-image: -webkit-linear-gradient(top, #76BF6B, #3B8230);border-color: #2d6324;text-shadow: rgba(0, 0, 0, 0.5) 0px -1px 0px;padding: 8px 10px;'))."</td>
							</tr>
						    </table>
						</td>
						<td>".$this->Form->submit("Save Listing",array('type' => 'submit', 'name' => 'save_listing')).$this->Form->end()."</td>
					    </tr>
				    </table>";
	    }
	    
	    echo $flashTable;
    }
    ?>
</div>
<div class="actions">
	<h3><?php echo __('Actions'); ?></h3>
	<ul>
		<li><?php echo $this->Html->link(__('Stock Below Level'), array('action' => 'stock_level')); ?></li>
	</ul>
</div>
<script>
    function reply_click(clicked_id)
    {
	if(!confirm("Do you really want to delete "+clicked_id))
	return false;
    }
</script>