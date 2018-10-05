<div class="actions" style="margin-bottom: -118px;">
    <h3><?php echo __('Actions'); ?></h3>
    <ul>
        <li><?php echo $this->Html->link(__('Stock References'), array('action' => 'stock_taking_reference_list')); ?></li>
        <li><?php echo $this->Html->link(__('Stock Taking'), array('action' => 'stock_taking')); ?></li>
    </ul>
</div>
<div style="float: left;width: 1100px;margin-left: 160px;">
    <?php
    $key = '';
    if(isset($this->request->query['key'])){
        $key = $this->request->query['key'];
    }
    if(isset($this->request->query['product'])){
        $product = $this->request->query['product'];
    }else{
		$product = '';
	}
    $kioskId = '';
    if(isset($this->request->query['kiosk'])){
        $kioskId = $this->request->query['kiosk'];
    }
    if(!empty($kiosks)){
		$kiosks[-1] = 'All';
		asort($kiosks);
	}
	
	
    echo $this->Form->create('SearchReference',array('url'=>array('controller'=>'stock_initializers','action'=>'search_stock_tkn_ref'),'type'=>'get'));?>
    <table style="width: 50%;">
        <tr>
			<td style="margin-top: -12px;" ><?php echo $this->Form->input('key',array('type'=>'text','label'=>'Search by Reference','value'=>$key,'style'=>'width: 126px;'))?></td>
			<td style="margin-top: -12px;" ><div id='remote'><?php echo $this->Form->input('product',array('type'=>'text','class'=>"typeahead",'label'=>'product or product code','value'=>$product,'style'=>'width: 150px;'))?></div></td>
            <td><?php echo $this->Form->input('kiosk',array('options'=>$kiosks,'value'=>$kioskId))?></td>
			<td><table style="width: 100px;">
				<tr colspan=2>
					<td>
					<input type="radio" name="merge" value="1" <?php if(isset($merged) && $merged == 1){ echo "checked=checked";} ?>> Merged<br></td><td>
					<input type="radio" name="merge" value="0" <?php if(isset($merged) && $merged == 0){ echo "checked=checked";} ?>> Unmerged<br>
					</td>
				</tr>
			</table></td>
            <td><?php
			echo $this->Form->submit('Search',array('name'=>'submit'));
			echo $this->Form->end();?></td>
			<td style="padding-top: 15px;"><?php echo $this->Form->button('Reset',array('type' => 'button','id' => 'reset','style' => "width: 75px;height: 40px;"))?></td>
        </tr>
    </table>
    <?php
		$screenHint = $hintId = "";
					if(!empty($hint)){
						
					   $screenHint = $hint["hint"];
					   $hintId = $hint["id"];
					}
					$updateUrl = "/img/16_edit_page.png";
	?>
	<h2><?php echo __('Stock Taking Details')."<span style='background: skyblue;color: blue;' title=\"$screenHint\">?</span>"; ?>
	<?php echo $this->Html->link($this->Html->image($updateUrl,array('fullBase' => true)), array('controller' => 'screen_hints', 'action' => 'edit',$hintId), array('escapeTitle' => false, 'title' => 'Edit Hint', 'alt' => 'Update payment','target' => '_blank'));?>				
	</h2>
    <table>
        <tr>
            <th><?php echo $this->Paginator->sort('reference');?></th>
            <th><?php echo $this->Paginator->sort('kiosk_id');?></th>
            <th><?php echo $this->Paginator->sort('user_id');?></th>
            <th><?php echo $this->Paginator->sort('created');?></th>
			<th><?php echo $this->Paginator->sort('net_gain');?></th>
            <th><?php echo 'Actions';?></th>
        </tr>
    <?php
	//pr($gainLossArr);die;
	foreach($stockTakingRefData as $key=>$stockTakingRef){
		$totalLoss = $totalGain = 0;
		if(array_key_exists($stockTakingRef->reference,$gainLoss)){
			$totalGain = $gainLoss[$stockTakingRef->reference][$stockTakingRef->kiosk_id]['total_gain'];
			$totalLoss = $gainLoss[$stockTakingRef->reference][$stockTakingRef->kiosk_id]['total_loss'];
			$finalGain = $totalGain - $totalLoss;
		}else{
			$finalGain = 0;
		}
		//foreach($gainLossArr as $refId => $gainLoss){
		//	
		//}
		?>
        <tr>
            <td><?php echo $stockTakingRef->reference;?></td>
            <td><?php echo $kioskName[$stockTakingRef->kiosk_id];?></td>
            <td><?php
			if(array_key_exists($stockTakingRef->user_id,$userName)){
				echo $userName[$stockTakingRef->user_id];
			}
			?></td>
            <td><?php
			echo date('d-m-Y h:i A',strtotime($stockTakingRef->created));
			//echo $this->Time->format('d-m-Y h:i A',$stockTakingRef->created,null,null);?></td>
			<?php if($finalGain < 0){?>
			<td style="background-color: yellow;"> 
			<?php }else{ ?>
			<td>
			<?php }?>
			<?php echo $finalGain; ?></td>
            <td><?php echo $this->Html->link('View',array('action'=>'view_stock_taking_details',$stockTakingRef->kiosk_id,$stockTakingRef->reference));?></td>
        </tr>
    <?php } ?>
    </table>

    <p>
	<div class="paging">
            <?= $this->Paginator->prev('< ' . __('previous')) ?>
            <?= $this->Paginator->numbers() ?>
            <?= $this->Paginator->next(__('next') . ' >') ?>
        <p><?= $this->Paginator->counter(['format' => __('Page {{page}} of {{pages}},
 showing {{current}} record(s) out of {{count}} total')]) ?></p>
    </div>
</div>
</div>
<script>
$(function() {
  $( document ).tooltip({
   content: function () {
    return $(this).prop('title');
   }
  });
 });

 $(document).on('click','#reset',function(){
	$('#key').val('');
	$('#product').val('');
	$('#kiosk').val(-1);
	});
</script>
<input type='hidden' name='url_category' id='url_category' value=''/>
<script type="text/javascript">
 function update_hidden(){
   
  //var singleValues = $( "#single" ).val();
  var multipleValues = $( "#category_dropdown" ).val() || [];
    //alert(multipleValues);
  $('#url_category').val(multipleValues.join( "," ));
   
 }
</script>
<script>
	var product_dataset = new Bloodhound({
  datumTokenizer: Bloodhound.tokenizers.obj.whitespace('value'),
  queryTokenizer: Bloodhound.tokenizers.whitespace,
  //prefetch: "/products/data",
  remote: {
    url: "/products/admin-Data?category=%CID&search=%QUERY",
                    replace: function (url,query) {
					 var multipleValues = $( "#category_dropdown" ).val() || [];
                     $('#url_category').val(multipleValues.join( "," ));
					 //alert($('#url_category').val());
					 return url.replace('%QUERY', query).replace('%CID', $('#url_category').val());
					},
					
	/*filter: function(x) {
                            return $.map(x, function(item) {
                                return {value: item.product};
                            });
                        },*/
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
    /*empty: [
      '<div class="empty-message">',
        'unable to find matching product',
      '</div>'
    ].join('\n'),*/
    suggestion: Handlebars.compile('<div style="background-color:lightgrey;width:400px;z-index:-5000" class="row_hover"><strong style="width:400px;color:black"><a class="row_hover" href="#-1">{{product_code}}</a></strong></div>'),
	header: Handlebars.compile("<div style='background-color:lightgrey;width:400px;z-index:-5000'><b>Search result for ({{query}}) :'</b></div>"),
	footer: Handlebars.compile("<div style='background-color:lightgrey;width:400px;z-index:-5000'><b>---------<?php echo ADMIN_DOMAIN;?>---------</b></div>"),
  }
});
</script>