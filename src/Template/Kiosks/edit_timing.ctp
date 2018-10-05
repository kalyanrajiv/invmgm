<div class="kiosks form">
    <?php
    $id = $this->request->pass[0];
        echo $this->Form->create($kiosk);
    ?>
    <fieldset>
		<legend><?php echo __('Edit Kiosk'); ?></legend>
        
        <?php
            
            echo "<table>";
			echo "<tr>";
				echo "</td>";
						echo "<b style='padding-left: 65px;'>Time IN</b>";
				echo "<td>";
				echo "</td>";
						echo "<b style='padding-left: 376px;'>Time Out</b>";
				echo "<td>";
			echo "</tr>";
			echo "<tr>";
				echo "<td>";
               //echo $this->Form->input('KioskTiming.id');
                    echo $this->Form->input('mon_time_in',array('label' => "Monday",
																'default' => "09:30:00",
																));
				echo "</td>";
                echo "<td>";
                    echo $this->Form->input('mon_time_out',array('label' => "Monday",
																 'default' => "17:30:00",
																 ));
				echo "</td>";
            echo "</tr>";
            echo "<tr>";
				echo "<td>";
                    echo $this->Form->input('tues_time_in',array('label' => "Tuesday",
																 'default' => "09:30:00",
																 ));
				echo "</td>";
                echo "<td>";
                    echo $this->Form->input('tues_time_out',array('label' => "Tuesday",
																  'default' => "17:30:00",
																  ));
				echo "</td>";
            echo "</tr>";
            echo "<tr>";
				echo "<td>";
                    echo $this->Form->input('wed_time_in',array('label' => "Wednesday",
																'default' => "09:30:00",
																));
				echo "</td>";
                echo "<td>";
                    echo $this->Form->input('wed_time_out',array('label' => "Wednesday",
																 'default' => "17:30:00",
																 ));
				echo "</td>";
            echo "</tr>";
            echo "<tr>";
				echo "<td>";
                    echo $this->Form->input('thrus_time_in',array('label' => "Thursday",
																  'default' => "09:30:00",
																  ));
				echo "</td>";
                echo "<td>";
                    echo $this->Form->input('thrus_time_out',array('label' => "Thursday",
																   'default' => "17:30:00",
																   ));
				echo "</td>";
            echo "</tr>";
            echo "<tr>";
				echo "<td>";
                    echo $this->Form->input('fri_time_in',array('label' => "Friday",
																'default' => "09:30:00",
																));
				echo "</td>";
                echo "<td>";
                    echo $this->Form->input('fri_time_out',array('label' => "Friday",
																 'default' => "17:30:00",
																 ));
				echo "</td>";
            echo "</tr>";
            echo "<tr>";
				echo "<td>";
                    echo $this->Form->input('sat_time_in',array('label' => "Saturday",
																'default' => "09:30:00",
																));
				echo "</td>";
                echo "<td>";
                    echo $this->Form->input('sat_time_out',array('label' => "Saturday",
																 'default' => "17:30:00",
																 ));
				echo "</td>";
            echo "</tr>";
                echo "<tr>";
				echo "<td>";
                    echo $this->Form->input('sun_time_in',array('label' => "Sunday",
																'default' => "09:30:00",
																));
				echo "</td>";
                echo "<td>";
                    echo $this->Form->input('sun_time_out',array('label' => "Sunday",
																 'default' => "17:30:00",
																 ));
                     echo $this->Form->input('KioskTiming.kiosk_id',array('type' => 'hidden','value' => $id));
				echo "</td>";
            echo "</tr>";
            echo "</table>";
        ?>
    </fieldset>
    <?php
    echo $this->Form->Submit(__('Submit'),array('name'=>'submit'));
    echo $this->Form->end(); ?>
</div>