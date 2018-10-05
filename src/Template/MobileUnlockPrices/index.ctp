<div class="actions">
	<h3><?php echo __('Actions'); ?></h3>
	<ul>
		<li><?php echo $this->Html->link(__('New Mobile <br/>Unlock Price'), array('action' => 'add'),array('escape' => false)); ?></li>
		<?php if($this->request->session()->read('Auth.User.group_id') == MANAGERS || $this->request->session()->read('Auth.User.group_id') == ADMINISTRATORS || $this->request->session()->read('Auth.User.group_id') == FRANCHISE_OWNER){?>
		<li><?php echo $this->Html->link(__('Send unlock <br/> price notification'), array('controller' => 'mobile_unlock_prices', 'action' => 'unlock_price_push_notification'),array('escape' => false)); ?> </li>
		<?php } ?>
	</ul>
    </div>
<div class="mobileUnlockPrices index large-9 medium-8 columns content">
   
	<?php
		
		$queryStr = "";
		$rootURL = "";//$this->html->url('/', true);
		if( isset($this->request->query['search_kw']) ){
			$queryStr.="search_kw=".$this->request->query['search_kw'];
		}
		//echo $queryStr;
	
			
	?>
	<?php
    if(array_key_exists('submit',$this->request->query)){
    ?>
    <h2><?php echo __('Mobile Unlock Prices'); ?>&nbsp;<a href="<?php echo $rootURL;?>export/?<?php echo $queryStr;?>" target='_blank' title='export csv'><?php echo $this->Html->image('/img/export.png', array('fullBase' => true));?></a></h2><?php }else{?>
        <h2><?php echo __('Mobile Unlock Prices'); ?>&nbsp;<a href="<?php echo $rootURL;?>MobileUnlockPrices/export/?<?php echo $queryStr;?>" target='_blank' title='export csv'><?php echo $this->Html->image('/img/export.png', array('fullBase' => true));?></a></h2><?php }?>
    
    <?php if(!empty($this->request->query['search_kw'])){
		$value = $this->request->query['search_kw'];
	}else{
		$value = '';
	};?>
	
    <form action='<?php echo $this->request->webroot; ?>mobile-unlock-prices/search' method = 'get'>
	
		<fieldset>
			<legend>Search</legend>
			<div>
				<table>
					<tr>
						<td><div id='remote'><input type = "text" class='typeahead' name = "search_kw"  value = '<?=$value;?>' placeholder = "brand or mobile model" autofocus style = "width:600px"/></div></td>
						<td><input type = "submit" value = "Search Mobile Unlock Prices" name = "submit"/></td>
					</tr>
				</table>
				
				
			</div>
		</fieldset>	
	</form>
    
    <table cellpadding="0" cellspacing="0">
        <thead>
            <tr>
                <th scope="col"><?= $this->Paginator->sort('id') ?></th>
                <th scope="col"><?= $this->Paginator->sort('brand_id') ?></th>
                <th scope="col"><?= $this->Paginator->sort('mobile_model_id') ?></th>
                <th scope="col"><?= $this->Paginator->sort('network_id') ?></th>
                <th scope="col"><?= $this->Paginator->sort('unlocking_cost') ?></th>
                <th scope="col"><?= $this->Paginator->sort('unlocking_price') ?></th>
                <th scope="col"><?= $this->Paginator->sort('unlocking_days') ?></th>
                <th scope="col"><?= $this->Paginator->sort('unlocking_minutes') ?></th>
                <th scope="col"><?= $this->Paginator->sort('status') ?></th>
                <th scope="col"><?= $this->Paginator->sort('modified') ?></th>
                <th scope="col" class="actions"><?= __('Actions') ?></th>
            </tr>
        </thead>
        <tbody>
            <?php //pr($mobileUnlockPrices);
            foreach ($mobileUnlockPrices as $mobileUnlockPrice): ?>
            <tr>
                <td><?= $this->Number->format($mobileUnlockPrice->id) ?></td>
                <td><?= $mobileUnlockPrice->has('brand') ? $this->Html->link($mobileUnlockPrice->brand->brand, ['controller' => 'Brands', 'action' => 'view', $mobileUnlockPrice->brand->id]) : '' ?></td>
                <td><?= $mobileUnlockPrice->has('mobile_model') ? $this->Html->link($mobileUnlockPrice->mobile_model->model, ['controller' => 'MobileModels', 'action' => 'view', $mobileUnlockPrice->mobile_model->id]) : '' ?></td>
                <td><?= $mobileUnlockPrice->has('network') ? $this->Html->link($mobileUnlockPrice->network->name, ['controller' => 'Networks', 'action' => 'view', $mobileUnlockPrice->network->id]) : '' ?></td>
                <td><?= $this->Number->format($mobileUnlockPrice->unlocking_cost) ?></td>
                <td><?= $this->Number->format($mobileUnlockPrice->unlocking_price) ?></td>
                <td><?= $this->Number->format($mobileUnlockPrice->unlocking_days) ?></td>
                <td><?= $this->Number->format($mobileUnlockPrice->unlocking_minutes) ?></td>
                <td><?= $statusOptions[$mobileUnlockPrice->status]; ?></td>
                
                
                <td><?= h(date('jS M, Y g:i A',strtotime($mobileUnlockPrice->modified))) ?></td>
                <td class="actions">
                    <?= $this->Html->link(__('View'), ['action' => 'view', $mobileUnlockPrice->id]) ?>
                    <?= $this->Html->link(__('Edit Grid'), ['action' => 'edit-grid', $mobileUnlockPrice->mobile_model->id]) ?>
                    <?= $this->Form->postLink(__('Delete'), ['action' => 'delete', $mobileUnlockPrice->id], ['confirm' => __('Are you sure you want to delete # {0}?', $mobileUnlockPrice->id)]) ?>
                </td>
            </tr>
            <?php endforeach; ?>
        </tbody>
    </table>

<div class="paging">
            <?= $this->Paginator->prev('< ' . __('previous')) ?>
            <?= $this->Paginator->numbers() ?>
            <?= $this->Paginator->next(__('next') . ' >') ?>
        <p><?= $this->Paginator->counter(['format' => __('Page {{page}} of {{pages}},
 showing {{current}} record(s) out of {{count}} total')]) ?></p>
    </div>
</div>    
<script>
	var user_dataset = new Bloodhound({
	datumTokenizer: Bloodhound.tokenizers.obj.whitespace('value'),
	queryTokenizer: Bloodhound.tokenizers.whitespace,
	//prefetch: "/products/data",
	remote: {
	  url: "/mobile-repair-prices/brand_suggestions?search=%QUERY",
	  wildcard: "%QUERY"
	}
      });
      
      $('#remote .typeahead').typeahead(null, {
	name: 'model',
	display: 'model',
	source: user_dataset,
	limit:100,
	minlength:3,
	classNames: {
	  input: 'Typeahead-input',
	  hint: 'Typeahead-hint',
	  selectable: 'Typeahead-selectable'
	},
	highlight: true,
	hint:true,
	templates: {
	  suggestion: Handlebars.compile('<div style="background-color:lightgrey;width:400px;z-index:-5000" class="row_hover"><strong style="width:400px;color:black"><a class="row_hover" href="#-1">{{model}}</a></strong></div>'),
	      header: Handlebars.compile("<div style='background-color:lightgrey;width:400px;z-index:-5000'><b>Search result for ({{query}}) :'</b></div>"),
	      footer: Handlebars.compile("<div style='background-color:lightgrey;width:400px;z-index:-5000'><b>---------<?php echo ADMIN_DOMAIN;?>---------</b></div>"),
	}
      });
</script>