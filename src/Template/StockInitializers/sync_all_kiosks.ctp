<div class="stock_initializer form">
    <?php
    //echo $this->Form->create($article, ['url' => ['action' => 'sync_products']]);
    echo $this->Form->create('StockInitializer',['url' => ['action' => 'sync_products']]); ?>
        <fieldset>
            <legend><?php echo __('Sync Stock'); ?></legend>
        <?php
            #echo $this->Form->select('kiosk_id',$kiosks, array('empty' => 'Choose Kiosk'));			
        ?>
        </fieldset>
    <?php
    echo $this->Form->Submit(__('Click here to sync all kiosk products'),array('name'=>'submit'));
    echo $this->Form->end(); ?>
</div>
<div class="actions">
    <h3><?php echo __('Actions'); ?></h3>
    <ul>
        <li><?php echo $this->Html->link(__('List Kiosks'), array('action' => 'index')); ?></li>
    </ul>
</div>
