<div class="problemtypes form">
  <?= $this->Form->create($post) ?>
	 <fieldset>
        <legend><?= __('Add Post') ?></legend>
        <?php
            echo $this->Form->input('user_id', ['options' => $users]);
            echo $this->Form->input('title');
            echo $this->Form->input('body');
        ?>
    </fieldset>
    <?= $this->Form->button(__('Submit'),array('name'=>'submit')) ?>
    <?= $this->Form->end() ?>
</div>
<div class="actions">
	<h3><?php echo __('Actions'); ?></h3>
	<ul>

		 <li><?= $this->Html->link(__('List Posts'), ['action' => 'index']) ?></li>
        <li><?= $this->Html->link(__('List Users'), ['controller' => 'Users', 'action' => 'index']) ?></li>
        <li><?= $this->Html->link(__('New User'), ['controller' => 'Users', 'action' => 'add']) ?></li>
        <li><?= $this->Html->link(__('List Comments'), ['controller' => 'Comments', 'action' => 'index']) ?></li>
        
	</ul>
</div>
  