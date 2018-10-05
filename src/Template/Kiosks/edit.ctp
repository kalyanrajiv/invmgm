<div class="kiosks form">
<?php
///pr($contractOptions);
	//$url = $this->Html->url(array('controller' => 'customers', 'action' => 'get_address'));
	
	$url = $this->Url->build(["controller" => "customers","action" => "get_address"]);
	
	echo $this->Form->create($kiosk,array('onsubmit'=>'return checkpercentage();','enctype'=>'multipart/form-data'));
?>
	<fieldset>
		<legend><?php echo __('Edit Kiosk'); ?></legend>
		
		<?php
		//pr($this->request);
		$id = $this->request->params['pass'][0];
		//echo $this->Html->link(__('Add Timing'), array('controller' => 'Kiosks', 'action' => 'add_timing',$id)); 
		//echo $this->Html->link(__('Edit Timing'), array('controller' => 'Kiosks', 'action' => 'edit_timing',$id));
		?>
	<?php
		
		echo $this->Form->input('id');
		echo "<table>";
			echo "<tr>";
				echo "<td>";
				echo $this->Form->input('code',array('readonly' => true));
				echo "</td>";
				echo "<td>";
				echo $this->Form->input('name');
				echo "</td>";
				echo "<td>";
				echo $this->Form->input('email');
				echo "</td>";
			echo "</tr>";
		
			echo "<tr>";
				echo "<td>";
					echo "<table>";
						echo "<tr>";
							echo "<td>";
							echo $this->Form->input('zip',array('placeholder' => 'Postcode', 'label'=>false, 'rel' => $url,'size'=>'10px','style'=>'width: 120px;'));
							echo "</td>";
							echo "<td>";
							echo "<button type='button' id='find_address' class='btn' style='margin-top: 6px;margin-left: -8px;width: 130px;height: 29px;'>Find my address</button>";
							echo "</td>";
						echo "</tr>";
					echo "</table>";
				echo "</td>";
				echo "<td>";
				echo $this->Form->input('address_1', array('placeholder' => 'property name/no. and street name'));
	?>
			<select name='street_address' id='street_address'><option>--postcode--</option></select>
				<span id='address_missing'><br/><a href='#-1'>Address is not on the list</a></span>
				
				</td>
	<?php
				echo "<td>";
				echo $this->Form->input('address_2', array('placeholder' => "further address details (optional)"));
				echo "</td>";
			echo "</tr>";
		
		//echo $this->Form->input('communication_password');
			
			echo "<tr>";
				echo "<td>";
				echo $this->Form->input('city',array('label' => 'Town/City','placeholder' => "name of town or city"));
				echo "</td>";
				echo "<td>";
				echo $this->Form->input('state',array('label'=>'County', 'placeholder' => "name of county (optional)"));
				echo "</td>";
				echo "<td>";
				echo $this->Form->input('country', array('options' => $countryOptions));
				echo "</td>";
			echo "</tr>";
			
			echo "<tr>";
				echo "<td>";
				echo $this->Form->input('contact',array('maxLength'=>'11'));
				echo "</td>";
				echo "<td>";
				echo $this->Form->input('rent',array('type'=>'text'));
				echo "</td>";
				echo "<td>";
				echo "<strong>".$this->Form->input('target',array('id' => "KioskTarget",'type'=>'text','label'=>'Target (per week)','onblur'=>'fillallval();'))."</strong>";
				echo "</td>";
			echo "</tr>";
		
			echo "<tr>";
				echo "<td>";
				echo $this->Form->input('target_mon',array('id' => 'KioskTargetMon','type'=>'text','placeholder'=>'In percentage','label'=>'Target Monday(%)','div'=>false, 'style'=>'width: 117px;', 'onblur'=>'fillmonval();'));
				echo "<span style='font-size: 19px;margin-left: 10px;margin-right: -15px;'>&#163;</span>";
				echo "<input type='text' id='target_monday' style='width: 100px;margin-left: 20px;height: 18px;background-color: skyblue;' readonly>";
				echo "</td>";
				echo "<td>";
				echo $this->Form->input('target_tue',array('id' => 'KioskTargetTue','type'=>'text','placeholder'=>'In percentage','label'=>'Target Tuesday(%)','div'=>false, 'style'=>'width: 117px;', 'onblur'=>'filltueval();'));
				echo "<span style='font-size: 19px;margin-left: 10px;margin-right: -15px;'>&#163;</span>";
				echo "<input type='text' id='target_tuesday' style='width: 100px;margin-left: 20px;height: 18px;background-color: skyblue;' readonly>";
				echo "</td>";
				echo "<td>";
				echo $this->Form->input('target_wed',array('id' => 'KioskTargetWed','type'=>'text','placeholder'=>'In percentage','label'=>'Target Wednesday(%)','div'=>false, 'style'=>'width: 117px;', 'onblur'=>'fillwedval();'));
				echo "<span style='font-size: 19px;margin-left: 10px;margin-right: -15px;'>&#163;</span>";
				echo "<input type='text' id='target_wednesday' style='width: 100px;margin-left: 20px;height: 18px;background-color: skyblue;' readonly>";
				echo "</td>";
			echo "</tr>";
		
			echo "<tr>";
				echo "<td>";
				echo $this->Form->input('target_thu',array('id' =>'KioskTargetThu' ,'type'=>'text','placeholder'=>'In percentage','label'=>'Target Thursday(%)','div'=>false, 'style'=>'width: 117px;', 'onblur'=>'fillthuval();'));
				echo "<span style='font-size: 19px;margin-left: 10px;margin-right: -15px;'>&#163;</span>";
				echo "<input type='text' id='target_thursday' style='width: 100px;margin-left: 20px;height: 18px;background-color: skyblue;' readonly>";
				echo "</td>";
				echo "<td>";
				echo $this->Form->input('target_fri',array('id' =>'KioskTargetFri' ,'type'=>'text','placeholder'=>'In percentage','label'=>'Target Friday(%)','div'=>false, 'style'=>'width: 117px;', 'onblur'=>'fillfrival();'));
				echo "<span style='font-size: 19px;margin-left: 10px;margin-right: -15px;'>&#163;</span>";
				echo "<input type='text' id='target_friday' style='width: 100px;margin-left: 20px;height: 18px;background-color: skyblue;' readonly>";
				echo "</td>";
				echo "<td>";
				echo $this->Form->input('target_sat',array('id' =>'KioskTargetSat' ,'type'=>'text','placeholder'=>'In percentage','label'=>'Target Saturday(%)','div'=>false, 'style'=>'width: 117px;', 'onblur'=>'fillsatval();'));
				echo "<span style='font-size: 19px;margin-left: 10px;margin-right: -15px;'>&#163;</span>";
				echo "<input type='text' id='target_saturday' style='width: 100px;margin-left: 20px;height: 18px;background-color: skyblue;' readonly>";
				echo "</td>";
			echo "</tr>";
		
			echo "<tr>";
				echo "<td>";
				echo $this->Form->input('target_sun',array('id' =>'KioskTargetSun','type'=>'text','placeholder'=>'In percentage','label'=>'Target Sunday(%)','div'=>false, 'style'=>'width: 117px;', 'onblur'=>'fillsunval();'));
				echo "<span style='font-size: 19px;margin-left: 10px;margin-right: -15px;'>&#163;</span>";
				echo "<input type='text' id='target_sunday' style='width: 100px;margin-left: 20px;height: 18px;background-color: skyblue;' readonly>";
				echo "</td>";
				echo "<td>";
					echo "<strong>".$this->Form->input('target_month',array('type'=>'text','label'=>'Target (per month)','id' => 'target','onblur'=>'show_week_trget();'))."</strong>";
				echo "</td>";
				echo "<td>";
				echo $this->Form->input('agreement_from',array('minYear'=>date('Y')-10,'maxYear'=>date('Y')));
				echo "</td>";
				echo "</tr>";
				 
				echo "<table>";
					echo "<tr>";
					echo "<td>";
				echo $this->Form->input('agreement_to',array('minYear'=>date('Y'),'maxYear'=>date('Y')+10));
				echo "</td>";
						echo "<td>";
						echo $this->Form->input('contract_type', array('options' => $contractOptions));
						echo "</td>";
						echo "<td>";
						echo $this->Form->input('break_clause');
						echo "</td>";
						echo "<td>";
						echo $this->Form->input('renewal_months',array('style' => 'width:50px;','type'=>'text','label' => 'Renewal Alert(In Months)'));
						echo "</td>";
						echo "<td>";
						echo $this->Form->input('renewal_weeks',array('style' => 'width:50px;','type'=>'text','label' => 'Renewal Alert(In Weeks)'));
						echo "</td>";
					echo "</tr>";
				echo "</table>";
			echo "</tr>";
		
			echo "<tr>";
				echo "<td>";
				echo $this->Form->input('memo');
				
				echo "</td>";
				echo "<td>";
				?>
				<table cellspacing='1' cellpadding='1'>
					<tr colspan=7 >
						<td>Vat Applied?</td>
						<td>Yes <input type='radio' name='vat_applied' value='1' onClick='showhide_info(1);' <?php echo $kiosk->vat_applied == 1 ? "checked='checked'" : "";?>/></td>
						<td>No<input type='radio' name='vat_applied' value='0' onClick='showhide_info(0);' <?php echo $kiosk->vat_applied == 0 ? "checked='checked'" : "";?>/>
						</td>
						<td ></td>
						<td >
							Terms and conditions
						</td>
						<td style="width: 284px;">
							<textarea name="terms" style="width:250px;height:150px;"><?php echo $kiosk->terms;?></textarea>
							</br>
						**If added Terms of condition here, it will override terms set in settings for main sites/kiosks and would be applicable only for kiosk; you are editing
						
						</td>
						<td>
							**<b>Please don't upload images whose names have spaces. </br>Please rename them if spaces. </br>e.g: sony headphone.jpg it should be renamed as sonyheadphone.jpg or sony_headphone.jpg.</b></br>
							<b>spacing are creating problem in mail invoices</b></br>
							Logo :
							<input type="file" name="logo_image" accept="image/*" style="width: 194px;" />
							<?php
									$imageDir = WWW_ROOT."logo".DS.$kiosk->id.DS.$kiosk->logo_image;
									if(!empty($kiosk->logo_image)){
										if(@readlink($imageDir) ||file_exists($imageDir)){
											$imageURL = "$siteBaseURL/logo/".$kiosk->id."/".$kiosk->logo_image;
										}else{
											$imageURL = "$siteBaseURL/thumb_no-image.png";
										}	
									}else{
										$imageURL = "$siteBaseURL/thumb_no-image.png";
									}
									
									
								echo $this->Html->image($imageURL, array('fullBase' => false,'width'=>'100px','height'=>'100px'));
							?>
							
						</td>
						
					</tr>
				</table>
				<?php
				echo "<table id='vat_applied' style='width: 295px;'>";
				echo "<tr>";
					echo "<td>";
						echo $this->Form->input('vat_no',array('type' => "text"));
					echo "</td>";
				echo "<tr>";
				echo "</table>";
				echo "</td>";
				
				echo "</td>";
				echo "<td>";
				echo $this->Form->select('status',$activeOptions,array('empty' => false));
				echo "</td>";
			echo "</tr>";
		echo "</table>";
	?>
	</fieldset>
<?php
echo $this->Form->submit("Submit",array('name'=>'submit'));
echo $this->Form->end(); ?>
</div>
<div class="actions">
	<h3><?php echo __('Actions'); ?></h3>
	<ul>

		<li><?php #echo $this->Form->postLink(__('Delete'), array('action' => 'delete', $this->Form->value('Kiosk.id')), array(), __('Are you sure you want to delete # %s?', $this->Form->value('Kiosk.id'))); ?></li>
		<li><?php echo $this->Html->link(__('List Kiosks'), array('action' => 'index')); ?></li>
		<li><?php #echo $this->Html->link(__('List Mobile Repairs'), array('controller' => 'mobile_repairs', 'action' => 'index')); ?> </li>
		<li><?php echo $this->Html->link(__('New Kiosk'), array('action' => 'add')); ?> </li>
		<li><?php #echo $this->Html->link(__('List Reorder Levels'), array('controller' => 'reorder_levels', 'action' => 'index')); ?> </li>
		<li><?php #echo $this->Html->link(__('New Reorder Level'), array('controller' => 'reorder_levels', 'action' => 'add')); ?> </li>
	</ul>
</div>
<script>
$(function() {
	$('#address_missing').click(function(){
		$('#street_address').hide();
		$('#address-1').show("");
		$('#address-1').val("");
		$('#address-2').val("");
		$('#city').val("");
		$('#state').val("");		
		$(this).hide();
	});
	$( "#street_address" ).select(function() {
		alert($( "#street_address" ).val());
		$('#address-1').val($( "#street_address" ).val());
		$('#address-1').show();
		$('#address_missing').hide();
		$(this).hide();
	});
	$( "#street_address" ).change(function() {
		$('#address-1').val($( "#street_address" ).val());
		$('#address-1').show();
		$('#address_missing').hide();
		$(this).hide();
	});
	$( "#find_address" ).click(function() {
		var zipCode = $("#zip").val();
		//$.blockUI({ message: 'Just a moment...' });
		//focus++;
		//alert("focusout:"+zipCode);
		//$( "#focus-count" ).text( "focusout fired: " + focus + "x" );
		var zipCode = $("#zip").val();
		var targeturl = $("#zip").attr('rel') + '?zip=' + escape(zipCode);		
		$.blockUI({ message: 'Just a moment...' });
		$.ajax({
			type: 'get',
			url: targeturl,
			beforeSend: function(xhr) {
			    xhr.setRequestHeader('Content-type', 'application/x-www-form-urlencoded');
			},
			success: function(response) {
				$.unblockUI();
				var obj = jQuery.parseJSON( response);			
				if (response) {
					if (obj.ErrorNumber == 0) {
						$('#street_address').show();
						$('#address-1').hide("");
						$('#address_missing').show();
						var toAppend = '';
						$('#street_address').find('option').remove().end();
						$.each(obj.Street, function( index, value ) {
							//alert( index + ": " + value );
							toAppend += '<option value="'+value+'">'+value+'</option>';
						});
						$('#street_address').append(toAppend);
						$('#address-2').val(obj.Address2);
						$('#city').val(obj.Town);
						$('#state').val(obj.County);
					}else{
						alert("Error Code: "+obj.ErrorNumber+ ", Error Message: "+ obj.ErrorMessage);
					}					
				}
			},
			error: function(e) {
			    $.unblockUI();
			    alert("Error:"+response);
			    alert("An error occurred: " + e.responseText.message);
			    console.log(e);
			}
		});
	});
	$('#street_address').hide();
	$('#address-1').show();
	$('#address_missing').hide();
	
	$("#KioskContact").keydown(function (event) {  
		if (event.shiftKey == true) {event.preventDefault();}
		if ((event.keyCode >= 48 && event.keyCode <= 57) ||
			(event.keyCode >= 96 && event.keyCode <= 105) ||
			event.keyCode == 8 || event.keyCode == 9 ||
			event.keyCode == 37 || event.keyCode == 39 ||
			event.keyCode == 46 || event.keyCode == 183) {
			;
		}else{
		 event.preventDefault();
		}
    });
	
	$("#KioskTarget").keydown(function (event) {  
		if (event.shiftKey == true) {event.preventDefault();}
		if ((event.keyCode >= 48 && event.keyCode <= 57) ||
			(event.keyCode >= 96 && event.keyCode <= 105) ||
			event.keyCode == 8 || event.keyCode == 9 ||
			event.keyCode == 37 || event.keyCode == 39 ||
			event.keyCode == 46 || event.keyCode == 183 || event.keyCode == 190 || event.keyCode == 110) {
			;
		}else{
		 event.preventDefault();
		}
    });
	
});
	
	function fillmonval(){
		var kiosktarget = document.getElementById("KioskTarget").value;
		var monpercent = document.getElementById("KioskTargetMon").value;
		var monvalue = kiosktarget*monpercent/100;
		document.getElementById("target_monday").value=monvalue;
		
		var monpercent = document.getElementById("KioskTargetMon").value;
		var tuepercent = document.getElementById("KioskTargetTue").value;
		var wedpercent = document.getElementById("KioskTargetWed").value;
		var thupercent = document.getElementById("KioskTargetThu").value;
		var fripercent = document.getElementById("KioskTargetFri").value;
		var satpercent = document.getElementById("KioskTargetSat").value;
		var sunpercent = document.getElementById("KioskTargetSun").value;
		var totalpercentage = 0;
		
		if (!monpercent=="") {
			totalpercentage+=parseFloat(monpercent);
		}
		if (!tuepercent=="") {
			totalpercentage+=parseFloat(tuepercent);
		}
		if (!wedpercent=="") {
			totalpercentage+=parseFloat(wedpercent);
		}
		if (!thupercent=="") {
			totalpercentage+=parseFloat(thupercent);
		}
		if (!fripercent=="") {
			totalpercentage+=parseFloat(fripercent);
		}
		if (!satpercent=="") {
			totalpercentage+=parseFloat(satpercent);
		}
		if (!sunpercent=="") {
			totalpercentage+=parseFloat(sunpercent);
		}
		
		if(monvalue==0){
			document.getElementById("KioskTargetMon").value=0;
		}
		if(parseFloat(totalpercentage)>100){
			alert("Total sum percentage of all weeks must be equal to 100!");
			document.getElementById("KioskTargetMon").value = 0;
			//alert(document.getElementById("KioskTargetMon").value);
			document.getElementById("target_monday").value = 0;
		}
		if (totalpercentage==100) {
			month();
		} else if (totalpercentage<100) {
			document.getElementById("target").value = "";
		}
	}
	
	function filltueval(){
		var kiosktarget = document.getElementById("KioskTarget").value;
		var tuepercent = document.getElementById("KioskTargetTue").value;
		var tuevalue = kiosktarget*tuepercent/100;
		document.getElementById("target_tuesday").value=tuevalue;
		
		var monpercent = document.getElementById("KioskTargetMon").value;
		var tuepercent = document.getElementById("KioskTargetTue").value;
		var wedpercent = document.getElementById("KioskTargetWed").value;
		var thupercent = document.getElementById("KioskTargetThu").value;
		var fripercent = document.getElementById("KioskTargetFri").value;
		var satpercent = document.getElementById("KioskTargetSat").value;
		var sunpercent = document.getElementById("KioskTargetSun").value;
		var totalpercentage = 0;
		
		if (!monpercent=="") {
			totalpercentage+=parseFloat(monpercent);
		}
		if (!tuepercent=="") {
			totalpercentage+=parseFloat(tuepercent);
		}
		if (!wedpercent=="") {
			totalpercentage+=parseFloat(wedpercent);
		}
		if (!thupercent=="") {
			totalpercentage+=parseFloat(thupercent);
		}
		if (!fripercent=="") {
			totalpercentage+=parseFloat(fripercent);
		}
		if (!satpercent=="") {
			totalpercentage+=parseFloat(satpercent);
		}
		if (!sunpercent=="") {
			totalpercentage+=parseFloat(sunpercent);
		}
		
		if(tuevalue==0){
			document.getElementById("KioskTargetTue").value=0;
		}
		if(totalpercentage>100){
			alert("Total sum percentage of all weeks must be equal to 100!");
			document.getElementById("KioskTargetTue").value = 0;
			document.getElementById("target_tuesday").value = 0;
		}
		if (totalpercentage==100) {
			month();
		} else if (totalpercentage<100) {
			document.getElementById("target").value = "";
		}
	}
	
	
	function fillwedval(){
		var kiosktarget = document.getElementById("KioskTarget").value;
		var wedpercent = document.getElementById("KioskTargetWed").value;
		var wedvalue = kiosktarget*wedpercent/100;
		document.getElementById("target_wednesday").value=wedvalue;
		
		var monpercent = document.getElementById("KioskTargetMon").value;
		var tuepercent = document.getElementById("KioskTargetTue").value;
		var wedpercent = document.getElementById("KioskTargetWed").value;
		var thupercent = document.getElementById("KioskTargetThu").value;
		var fripercent = document.getElementById("KioskTargetFri").value;
		var satpercent = document.getElementById("KioskTargetSat").value;
		var sunpercent = document.getElementById("KioskTargetSun").value;
		var totalpercentage = 0;
		
		if (!monpercent=="") {
			totalpercentage+=parseFloat(monpercent);
		}
		if (!tuepercent=="") {
			totalpercentage+=parseFloat(tuepercent);
		}
		if (!wedpercent=="") {
			totalpercentage+=parseFloat(wedpercent);
		}
		if (!thupercent=="") {
			totalpercentage+=parseFloat(thupercent);
		}
		if (!fripercent=="") {
			totalpercentage+=parseFloat(fripercent);
		}
		if (!satpercent=="") {
			totalpercentage+=parseFloat(satpercent);
		}
		if (!sunpercent=="") {
			totalpercentage+=parseFloat(sunpercent);
		}
		
		if(wedvalue==0){
			document.getElementById("KioskTargetWed").value=0;
		}
		if(totalpercentage>100){
			alert("Total sum percentage of all weeks must be equal to 100!");
			document.getElementById("KioskTargetWed").value = 0;
			document.getElementById("target_wednesday").value = 0;
		}
		if (totalpercentage==100) {
			month();
		} else if (totalpercentage<100) {
			document.getElementById("target").value = "";
		}
	}
	
	
		
	function fillthuval(){
		var kiosktarget = document.getElementById("KioskTarget").value;
		var thupercent = document.getElementById("KioskTargetThu").value;
		var thuvalue = kiosktarget*thupercent/100;
		document.getElementById("target_thursday").value=thuvalue;
		
		var monpercent = document.getElementById("KioskTargetMon").value;
		var tuepercent = document.getElementById("KioskTargetTue").value;
		var wedpercent = document.getElementById("KioskTargetWed").value;
		var thupercent = document.getElementById("KioskTargetThu").value;
		var fripercent = document.getElementById("KioskTargetFri").value;
		var satpercent = document.getElementById("KioskTargetSat").value;
		var sunpercent = document.getElementById("KioskTargetSun").value;
		var totalpercentage = 0;
		
		if (!monpercent=="") {
			totalpercentage+=parseFloat(monpercent);
		}
		if (!tuepercent=="") {
			totalpercentage+=parseFloat(tuepercent);
		}
		if (!wedpercent=="") {
			totalpercentage+=parseFloat(wedpercent);
		}
		if (!thupercent=="") {
			totalpercentage+=parseFloat(thupercent);
		}
		if (!fripercent=="") {
			totalpercentage+=parseFloat(fripercent);
		}
		if (!satpercent=="") {
			totalpercentage+=parseFloat(satpercent);
		}
		if (!sunpercent=="") {
			totalpercentage+=parseFloat(sunpercent);
		}
		
		if(thuvalue==0){
			document.getElementById("KioskTargetThu").value=0;
		}
		if(totalpercentage>100){
			alert("Total sum percentage of all weeks must be equal to 100!");
			document.getElementById("KioskTargetThu").value = 0;
			document.getElementById("target_thursday").value = 0;
		}
		if (totalpercentage==100) {
			month();
		} else if (totalpercentage<100) {
			document.getElementById("target").value = "";
		}
	}
	
	function fillfrival(){
		var kiosktarget = document.getElementById("KioskTarget").value;
		var fripercent = document.getElementById("KioskTargetFri").value;
		var frivalue = kiosktarget*fripercent/100;
		document.getElementById("target_friday").value=frivalue;
		
		var monpercent = document.getElementById("KioskTargetMon").value;
		var tuepercent = document.getElementById("KioskTargetTue").value;
		var wedpercent = document.getElementById("KioskTargetWed").value;
		var thupercent = document.getElementById("KioskTargetThu").value;
		var fripercent = document.getElementById("KioskTargetFri").value;
		var satpercent = document.getElementById("KioskTargetSat").value;
		var sunpercent = document.getElementById("KioskTargetSun").value;
		var totalpercentage = 0;
		
		if (!monpercent=="") {
			totalpercentage+=parseFloat(monpercent);
		}
		if (!tuepercent=="") {
			totalpercentage+=parseFloat(tuepercent);
		}
		if (!wedpercent=="") {
			totalpercentage+=parseFloat(wedpercent);
		}
		if (!thupercent=="") {
			totalpercentage+=parseFloat(thupercent);
		}
		if (!fripercent=="") {
			totalpercentage+=parseFloat(fripercent);
		}
		if (!satpercent=="") {
			totalpercentage+=parseFloat(satpercent);
		}
		if (!sunpercent=="") {
			totalpercentage+=parseFloat(sunpercent);
		}
		
		if(frivalue==0){
			document.getElementById("KioskTargetFri").value=0;
		}
		if(totalpercentage>100){
			alert("Total sum percentage of all weeks must be equal to 100!");
			document.getElementById("KioskTargetFri").value = 0;
			document.getElementById("target_friday").value = 0;
		}
		if (totalpercentage==100) {
			month();
		} else if (totalpercentage<100) {
			document.getElementById("target").value = "";
		}
	}
	
	function fillsatval(){
		var kiosktarget = document.getElementById("KioskTarget").value;
		var satpercent = document.getElementById("KioskTargetSat").value;
		var satvalue = kiosktarget*satpercent/100;
		document.getElementById("target_saturday").value=satvalue;
		
		var monpercent = document.getElementById("KioskTargetMon").value;
		var tuepercent = document.getElementById("KioskTargetTue").value;
		var wedpercent = document.getElementById("KioskTargetWed").value;
		var thupercent = document.getElementById("KioskTargetThu").value;
		var fripercent = document.getElementById("KioskTargetFri").value;
		var satpercent = document.getElementById("KioskTargetSat").value;
		var sunpercent = document.getElementById("KioskTargetSun").value;
		var totalpercentage = parseFloat(monpercent)+parseFloat(tuepercent)+parseFloat(wedpercent)+parseFloat(thupercent)+parseFloat(fripercent)+parseFloat(satpercent)+parseFloat(sunpercent);
		
		var totalpercentage = 0;
		
		if (!monpercent=="") {
			totalpercentage+=parseFloat(monpercent);
		}
		if (!tuepercent=="") {
			totalpercentage+=parseFloat(tuepercent);
		}
		if (!wedpercent=="") {
			totalpercentage+=parseFloat(wedpercent);
		}
		if (!thupercent=="") {
			totalpercentage+=parseFloat(thupercent);
		}
		if (!fripercent=="") {
			totalpercentage+=parseFloat(fripercent);
		}
		if (!satpercent=="") {
			totalpercentage+=parseFloat(satpercent);
		}
		if (!sunpercent=="") {
			totalpercentage+=parseFloat(sunpercent);
		}
		
		if(satvalue==0){
			document.getElementById("KioskTargetSat").value=0;
		}
		if(totalpercentage>100){
			alert("Total sum percentage of all weeks must be equal to 100!");
			document.getElementById("KioskTargetSat").value = 0;
			document.getElementById("target_saturday").value = 0;
		}
		if (totalpercentage==100) {
			month();
		} else if (totalpercentage<100) {
			document.getElementById("target").value = "";
		}
	}
	
	function fillsunval(){
		var kiosktarget = document.getElementById("KioskTarget").value;
		var sunpercent = document.getElementById("KioskTargetSun").value;
		var sunvalue = kiosktarget*sunpercent/100;
		document.getElementById("target_sunday").value=sunvalue;
		
		var monpercent = document.getElementById("KioskTargetMon").value;
		var tuepercent = document.getElementById("KioskTargetTue").value;
		var wedpercent = document.getElementById("KioskTargetWed").value;
		var thupercent = document.getElementById("KioskTargetThu").value;
		var fripercent = document.getElementById("KioskTargetFri").value;
		var satpercent = document.getElementById("KioskTargetSat").value;
		var sunpercent = document.getElementById("KioskTargetSun").value;
		var totalpercentage = 0;
		
		if (!monpercent=="") {
			totalpercentage+=parseFloat(monpercent);
		}
		if (!tuepercent=="") {
			totalpercentage+=parseFloat(tuepercent);
		}
		if (!wedpercent=="") {
			totalpercentage+=parseFloat(wedpercent);
		}
		if (!thupercent=="") {
			totalpercentage+=parseFloat(thupercent);
		}
		if (!fripercent=="") {
			totalpercentage+=parseFloat(fripercent);
		}
		if (!satpercent=="") {
			totalpercentage+=parseFloat(satpercent);
		}
		if (!sunpercent=="") {
			totalpercentage+=parseFloat(sunpercent);
		}
		
		if(sunvalue==0){
			document.getElementById("KioskTargetSun").value=0;
		}
		if(totalpercentage>100){
			alert("Total sum percentage of all weeks must be equal to 100!. Current value is "+totalpercentage);
			document.getElementById("KioskTargetSun").value = 0;
			document.getElementById("target_sunday").value = 0;
		}
		if (totalpercentage==100) {
			month();
		} else if (totalpercentage<100) {
			document.getElementById("target").value = "";
		}
	}
	
	function checkpercentage(){
		var kiosktarget = document.getElementById("KioskTarget").value;
		var monpercent = document.getElementById("KioskTargetMon").value;
		var tuepercent = document.getElementById("KioskTargetTue").value;
		var wedpercent = document.getElementById("KioskTargetWed").value;
		var thupercent = document.getElementById("KioskTargetThu").value;
		var fripercent = document.getElementById("KioskTargetFri").value;
		var satpercent = document.getElementById("KioskTargetSat").value;
		var sunpercent = document.getElementById("KioskTargetSun").value;
		var totalpercentage = parseFloat(monpercent)+parseFloat(tuepercent)+parseFloat(wedpercent)+parseFloat(thupercent)+parseFloat(fripercent)+parseFloat(satpercent)+parseFloat(sunpercent);
		var totalpercentage = 0;
		
		if (!monpercent=="") {
			totalpercentage+=parseFloat(monpercent);
		}
		if (!tuepercent=="") {
			totalpercentage+=parseFloat(tuepercent);
		}
		if (!wedpercent=="") {
			totalpercentage+=parseFloat(wedpercent);
		}
		if (!thupercent=="") {
			totalpercentage+=parseFloat(thupercent);
		}
		if (!fripercent=="") {
			totalpercentage+=parseFloat(fripercent);
		}
		if (!satpercent=="") {
			totalpercentage+=parseFloat(satpercent);
		}
		if (!sunpercent=="") {
			totalpercentage+=parseFloat(sunpercent);
		}
		
		if(totalpercentage!=100 && kiosktarget!=0){
			alert("Total sum percentage of all weeks must be equal to 100!. Current value is "+totalpercentage);
			
			return false;
		}
		
		if(totalpercentage!=0 && kiosktarget==0){
			document.getElementById("KioskTargetMon").value=0;
			document.getElementById("KioskTargetTue").value=0;
			document.getElementById("KioskTargetWed").value=0;
			document.getElementById("KioskTargetThu").value=0;
			document.getElementById("KioskTargetFri").value=0;
			document.getElementById("KioskTargetSat").value=0;
			document.getElementById("KioskTargetSun").value=0;
			
			document.getElementById("target_monday").value=0;
			document.getElementById("target_tuesday").value=0;
			document.getElementById("target_wednesday").value=0;
			document.getElementById("target_thursday").value=0;
			document.getElementById("target_friday").value=0;
			document.getElementById("target_saturday").value=0;
			document.getElementById("target_sunday").value=0;
			
			alert("Please enter a value in target!");
			return false;
		}
		
		var totalvalue = parseFloat(document.getElementById("target_monday").value)+parseFloat(document.getElementById("target_tuesday").value)+parseFloat(document.getElementById("target_wednesday").value)+parseFloat(document.getElementById("target_thursday").value)+parseFloat(document.getElementById("target_friday").value)+parseFloat(document.getElementById("target_saturday").value)+parseFloat(document.getElementById("target_sunday").value)
		//alert(totalvalue.toFixed(2));
		//alert(kiosktarget.toFixed(2));
		if(totalvalue.toFixed(2) != kiosktarget.toFixed(2)){
			document.getElementById("KioskTarget").value=0;
			document.getElementById("KioskTargetMon").value=0;
			document.getElementById("KioskTargetTue").value=0;
			document.getElementById("KioskTargetWed").value=0;
			document.getElementById("KioskTargetThu").value=0;
			document.getElementById("KioskTargetFri").value=0;
			document.getElementById("KioskTargetSat").value=0;
			document.getElementById("KioskTargetSun").value=0;
			
			document.getElementById("target_monday").value=0;
			document.getElementById("target_tuesday").value=0;
			document.getElementById("target_wednesday").value=0;
			document.getElementById("target_thursday").value=0;
			document.getElementById("target_friday").value=0;
			document.getElementById("target_saturday").value=0;
			document.getElementById("target_sunday").value=0;
			alert("Please re-enter the values!");
			return false;
		}
	}
	
	function fillallval() {
		fillmonval();
		filltueval();
		fillwedval();
		fillthuval();
		fillfrival();
		fillsatval();
		fillsunval();
	}
	window.onload = function() {
		fillmonval();
		filltueval();
		fillwedval();
		fillthuval();
		fillfrival();
		fillsatval();
		fillsunval();
	};
	
	<?php
		$daysInMonth = date('t');
		$numMondays = $numTues = $numWeds = $numThurs = $numFris = $numSats = $numSuns = 0;
		for($i = 1; $i <= $daysInMonth; $i++){
			$date = date("Y-m-d",strtotime(date("Y-m-$i")));
			$weekDay = date('l',strtotime("$date"));
			if($weekDay=='Monday'){
				    $numMondays++;
			}elseif($weekDay=='Tuesday'){
			   $numTues++;
			}elseif($weekDay=='Wednesday'){
				$numWeds++;
			}elseif($weekDay=='Thursday'){
				$numThurs++;
			}elseif($weekDay=='Friday'){
				$numFris++;
			}elseif($weekDay=='Saturday'){
				$numSats++;
			}elseif($weekDay=='Sunday'){
				$numSuns++;
			}
		}
		echo "var mons = $numMondays;\n";
		echo "var tues = $numTues;\n";
		echo "var weds = $numWeds;\n";
		echo "var thurs = $numThurs;\n";
		echo "var fris = $numFris;\n";
		echo "var sats = $numSats;\n";
		echo "var suns = $numSuns;\n";
	?>
	
		function month(){
		var kiosktarget = document.getElementById("KioskTarget").value;
		var monpercent = document.getElementById("KioskTargetMon").value;
		var monvalue = kiosktarget*monpercent/100;
		var tuepercent = document.getElementById("KioskTargetTue").value;
		var tuevalue = kiosktarget*tuepercent/100;
		var wedpercent = document.getElementById("KioskTargetWed").value;
		var wedvalue = kiosktarget*wedpercent/100;
		var thupercent = document.getElementById("KioskTargetThu").value;
		var thuvalue = kiosktarget*thupercent/100;
		var fripercent = document.getElementById("KioskTargetFri").value;
		var frivalue = kiosktarget*fripercent/100;
		var satpercent = document.getElementById("KioskTargetSat").value;
		var satvalue = kiosktarget*satpercent/100;
		var sunpercent = document.getElementById("KioskTargetSun").value;
		var sunvalue = kiosktarget * sunpercent/100;
		var total_monvalue = mons * monvalue;
		var total_tuevalue = tues * tuevalue;
		var total_wedvalue = weds * wedvalue;
		var total_thuvalue = thurs * thuvalue;
		var total_frivalue = fris * frivalue;
		var total_satvalue = sats* satvalue;
		var total_sunvalue = suns* sunvalue;
		var total_month = total_monvalue+total_tuevalue+total_wedvalue+total_thuvalue+total_frivalue+total_satvalue+total_sunvalue 
		document.getElementById("target").value=total_month;

}

	function show_week_trget() {
		var four_week_percentage = 400;
		var monpercent = document.getElementById("KioskTargetMon").value;
		var tuepercent = document.getElementById("KioskTargetTue").value;
		var wedpercent = document.getElementById("KioskTargetWed").value;
		var thupercent = document.getElementById("KioskTargetThu").value;
		var fripercent = document.getElementById("KioskTargetFri").value;
		var satpercent = document.getElementById("KioskTargetSat").value;
		var sunpercent = document.getElementById("KioskTargetSun").value;
		
		var target = document.getElementById("target").value;
		
		if (mons > 4 ) {
            var diff = mons - 4;
			if (diff > 0) {
                four_week_percentage = (monpercent*diff) + four_week_percentage;
            }
			
        }
		if (tues > 4 ) {
            var diff = mons - 4;
			if (diff > 0) {
                four_week_percentage = (tuepercent*diff) + four_week_percentage;
            }
        }
		if (weds > 4 ) {
            var diff = mons - 4;
			if (diff > 0) {
                four_week_percentage = (wedpercent*diff) + four_week_percentage;
            }
        }
		if (thurs > 4 ) {
            var diff = mons - 4;
			if (diff > 0) {
                four_week_percentage = (thupercent*diff) + four_week_percentage;
            }
        }
		if (fris > 4 ) {
            var diff = mons - 4;
			if (diff > 0) {
                four_week_percentage = (fripercent*diff) + four_week_percentage;
            }
        }
		if (sats > 4 ) {
            var diff = mons - 4;
			if (diff > 0) {
                four_week_percentage = (satpercent*diff) + four_week_percentage;
            }
        }
		if (suns > 4 ) {
            var diff = mons - 4;
			if (diff > 0) {
                four_week_percentage = (sunpercent*diff) + four_week_percentage;
            }
        }
		
		var single_day_percentage = target/four_week_percentage;
        var mon_val = monpercent*single_day_percentage;
		var tue_val = tuepercent*single_day_percentage;
		var wed_val = wedpercent*single_day_percentage;
		var thur_val = thupercent*single_day_percentage;
		var fri_val = fripercent*single_day_percentage;
		var sat_val = satpercent*single_day_percentage;
		var sun_val = sunpercent*single_day_percentage;
		document.getElementById("target_monday").value=mon_val;
		document.getElementById("target_tuesday").value=tue_val;
		document.getElementById("target_wednesday").value=wed_val;
		document.getElementById("target_thursday").value=thur_val;
		document.getElementById("target_friday").value=fri_val;
		document.getElementById("target_saturday").value=sat_val;
		document.getElementById("target_sunday").value=sun_val;
		
		var final_val = mon_val + tue_val + wed_val + thur_val + fri_val + sat_val + sun_val;
		document.getElementById("KioskTarget").value=final_val;
		
    }

</script>
<script type='text/javascript'>
    var optVal = 0;
    function showhide_info(optVal){
        if (optVal == 1){
            document.getElementById('vat_applied').style.display = 'table';
			if (document.getElementById('vat-no').value == "") {
				document.getElementById('vat-no').value = "<?php echo $setting_arr['vat_number'];?>";    
            }
			
        }else{
            document.getElementById('vat_applied').style.display = 'none';
        }
    }
	<?php
		if($kiosk->vat_applied == 0){
			echo "document.getElementById('vat_applied').style.display = 'none'";
		}
	?>
</script>
<script>


$(document).ready(function() {
    if ($('#vat-no').val() == "") {
				$('#vat-no').val("<?php echo $setting_arr['vat_number'];?>");    
            }
});
</script>