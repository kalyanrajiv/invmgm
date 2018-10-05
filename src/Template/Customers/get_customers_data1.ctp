<div class="customers index">
	<?php
		$queryStr = "";
		//$rootURL = $this->html->url('/', true);
		if( isset($this->request->query['search_kw']) ){
			$queryStr.="search_kw=".$this->request->query['search_kw'];
		}
	?>
	<?php echo $this->Form->create('CustomerData');?>
	<h2><?php echo __('Customers Data'); ?>&nbsp;<span><?php echo $this->Form->submit('/img/export.png', array('style' => "height: 30px;width: 30px;", 'div' => false)); ?></span></h2>
	<!--/?<?php //echo $queryStr;?>-->
	<i>**By default only email list will be downloaded. Please add more fields if required by checking the following</i>
	<table>
		<tr>
			<td><?php echo $this->Form->input('customer_fname', array('type' => 'checkbox'));?></td>
			<td><?php echo $this->Form->input('customer_lname', array('type' => 'checkbox'));?></td>
			<!--<td><?php //echo $this->Form->input('customer_email', array('type' => 'checkbox'));?></td> keepin it mandatory-->
			<td><?php echo $this->Form->input('customer_contact', array('type' => 'checkbox'));?></td>
		</tr>
	</table>
	<?php echo $this->Form->end();?>
	<i>Showing first 100 records</i>
	<table cellpadding="0" cellspacing="0">
	<thead>
	<tr>
			<th>First Name</th>
			<th>Last Name</th>
			<th>Email</th>
			<th>Contact</th>
	</tr>
	</thead>
	<tbody>
	<?php
	//pr($finalArray);
	$count = 0;
	foreach ($finalArray as $email => $customer):?>
	<tr>
		<td><?=$customer['customer_fname']?></td>
		<td><?=$customer['customer_lname']?></td>
		<td><?=$customer['customer_email']?></td>
		<td><?=$customer['customer_contact']?></td>
	</tr>
	<?php $count++;
	if($count == 100)break;//for showin only 100 records
	endforeach; ?>
	</tbody>
	</table>
</div>
<div class="actions">
	<ul>
		<li><?php echo $this->Html->link('Customers', array('controller' => 'customers', 'action' => 'index'));?></li>
	</ul>
</div>
