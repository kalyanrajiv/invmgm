<div class="users form">
    <?php echo $this->Form->create('User'); ?>
    <fieldset>
        <legend><?php echo __('Reset Password'); ?></legend>
        <?php #echo $this->Form->input('tokenhash',array('type' => 'hidden'));?>
        <?php #echo $this->Form->input('email',array('style' => 'width:200px;'));?>
        <?php echo $this->Form->input('password',array('name' => 'User[password]','label' => 'Password','style' => 'width:200px;'));?>
        <?php echo $this->Form->input('confirm_password',array('name' => 'User[confirm_password]','label' => 'Confirm Password','style' => 'width:200px;','type' => 'password'));?>
    </fieldset>
    <?php
    echo $this->Form->submit("submit",array('div' => false));
    echo $this->Form->end(); ?>
</div>
<link rel="stylesheet" href="resources/stylesheets/eText/infoboxes.css" type="text/css">