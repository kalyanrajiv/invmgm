 <div class="actions">
	<h3><?php echo __('Actions'); ?></h3>
	<ul>
		<li><?= $this->Html->link(__('New Kiosk'), ['action' => 'add']) ?></li>
         <li><?= $this->Html->link(__('List Mobile Repairs'), ['controller' => 'mobile_repairs', 'action' => 'index']) ?></li>
		<li><?php echo $this->Html->link(__('New Mobile Repair'), array('controller' => 'mobile_repairs', 'action' => 'add')); ?> </li>
		<li><?php echo $this->Html->link(__('List Reorder Levels'), array('controller' => 'reorder_levels', 'action' => 'index')); ?> </li>
		<li><?php echo $this->Html->link(__('New Reorder Level'), array('controller' => 'reorder_levels', 'action' => 'add')); ?> </li>
	</ul>
</div>
 
<div class="kiosks index">
	<form action='<?php echo $this->request->webroot;?>kiosks/search' method = 'get'>
		<div class="search_div">
			<fieldset>
				<legend>Search</legend>
				<div id='remote'><input type = "text" class='typeahead' name = "search_kw" placeholder = "name" style = "width:500px" autofocus/></div>
				<input type = "submit" name = "submit" value = "Search Kiosk"/>
			</fieldset>	
		</div>
	</form>
	<?php echo $this->Html->link('Update Monthly Target',array('action'=>'intialize_daily_targets_4_month'),array('style'=>"float: right;margin-top: -64px;margin-right: 120px;"));?>
	<?php
		
		$queryStr = "";
		$rootURL = "";//$this->html->url('/', true);
		if( isset($this->request->query['search_kw']) ){
			$queryStr.="search_kw=".$this->request->query['search_kw'];
		}
			
	?>
    <?php if(array_key_exists('submit',$this->request->query)){ ?>
	<h2><?php echo __('kiosks'); ?>&nbsp;<a href="<?php echo $rootURL;?>export/?<?php echo $queryStr;?>" target='_blank' title='export csv'><?php echo $this->Html->image('/img/export.png', array('fullBase' => true));?></a></h2>
    <?php }else{ ?>
    <h2><?php echo __('kiosks'); ?>&nbsp;<a href="<?php echo $rootURL;?>kiosks/export/?<?php echo $queryStr;?>" target='_blank' title='export csv'><?php echo $this->Html->image('/img/export.png', array('fullBase' => true));?></a></h2>
    <?php } ?>
    <table cellpadding="0" cellspacing="0">
        <thead>
            <tr>
                <th scope="col"><?= $this->Paginator->sort('id') ?></th>
                <th scope="col"><?= $this->Paginator->sort('code') ?></th>
                <th scope="col"><?= $this->Paginator->sort('name') ?></th>
                
              
                <th scope="col"><?= $this->Paginator->sort('address_1') ?></th>
                
                <th scope="col"><?= $this->Paginator->sort('contact') ?></th>
               
                <th scope="col"><?= $this->Paginator->sort('status') ?></th>
                  <th scope="col"><?= $this->Paginator->sort('kiosk_type') ?></th>
                
                <th scope="col"><?= $this->Paginator->sort('created') ?></th>
                <th scope="col"><?= $this->Paginator->sort('modified') ?></th>
              
                <th scope="col" class="actions"><?= __('Actions') ?></th>
            </tr>
        </thead>
        <tbody>
            <?php foreach ($kiosks as $kiosk):
            //pr($kiosk);die;?>
            <tr>
                <td><?= $this->Number->format($kiosk->id) ?></td>
                <td><?= h($kiosk->code) ?></td>
                <td><?= h($kiosk->name) ?></td>
                <td><?= h($kiosk->address_1) ?></td>
                <td><?= h($kiosk->contact) ?></td>
                <td><?= $activeOptions[$kiosk->status] ?></td>
                <td><?= $kioskTypeOptions[$kiosk->kiosk_type] ?></td>
                <td><?= h(date('jS M, Y g:i A',strtotime($kiosk->created))) ?></td>
                <td><?= h(date('jS M, Y g:i A',strtotime($kiosk->modified))) ?></td>
              
                <td class="actions">
                    <?= $this->Html->link(__('View'), ['action' => 'view', $kiosk->id]) ?>
                    <?= $this->Html->link(__('Edit'), ['action' => 'edit', $kiosk->id]) ?>
                    <?= $this->Html->link(__('Edit Timing'), array('controller' => 'Kiosks', 'action' => 'edit_timing',$kiosk->id)); ?>
                </td>
            </tr>
            <?php endforeach; ?>
        </tbody>
    </table>
    <div class="paging">
            <?= $this->Paginator->prev('< ' . __('previous')) ?>
            <?= $this->Paginator->numbers() ?>
            <?= $this->Paginator->next(__('next') . ' >') ?>
        <p><?= $this->Paginator->counter(['format' => __('Page {{page}} of {{pages}}, showing {{current}} record(s) out of {{count}} total')]) ?></p>
    </div>
</div>
