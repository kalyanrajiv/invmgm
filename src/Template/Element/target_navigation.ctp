<ul>
		<li><?php echo $this->Html->link(__('User Sale Report'), array('controller' => 'daily_targets', 'action' => 'user_sale_report'));?></li>
		<li><?php echo $this->Html->link(__('Kiosk sale Report'), array('controller' => 'daily_targets', 'action' => 'kiosk_sale_report'));?></li>
		<li><?php echo $this->Html->link(__('Monthly Kiosk <br/> Sale Report'), array('controller' => 'daily_targets', 'action' => 'monthly_kiosk_sale_report'),array('escape' => false));?></li>
		<li><?php echo $this->Html->link(__('Daily All Kiosk Sale'), array('controller' => 'daily_targets', 'action' => 'all'));?></li>
</ul>