<?php
/**
 * @var \App\View\AppView $this
 * @var \App\Model\Entity\Color $color
 */
?>
<div class="actions">
	<h3><?php echo __('Actions'); ?></h3>
	<ul>

		 <li><?php //$this->Form->postLink(
                //__('Delete'),
                //['action' => 'delete', $color->id],
                //['confirm' => __('Are you sure you want to delete # {0}?', $color->id)]
            //)
        ?></li>
        <li><?= $this->Html->link(__('List Colors'), ['action' => 'index']) ?></li>
	</ul>
</div>
 
<div class="colors form large-9 medium-8 columns content">
    <?= $this->Form->create($color) ?>
    <fieldset>
        <legend><?= __('Edit Color') ?></legend>
        <?php
            echo $this->Form->control('name');
             echo $this->Form->input('status',['options' => $activeOptions,'type' => 'select']);
        ?>
    </fieldset>
    <?= $this->Form->button(__('Submit')) ?>
    <?= $this->Form->end() ?>
</div>
