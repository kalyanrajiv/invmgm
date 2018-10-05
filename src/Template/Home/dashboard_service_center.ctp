<?php #pr($phonesDispatchedUnrepaired); ?>
<table>
    <tr>
        <td><h1><strong style='color:blue'>Service Center Dashboard</strong></h1></td></tr>
   
</table>
<table width='100%'>
    <tr>
       
        <td> 
            <table>
                <tr>
                    <td colspan="2"><strong>Phones in queue [<span style='background-color: yellow'>Phone(s) dispatched to service center by kiosk</span>]</strong></td><td colspan='2'><strong><?php echo $number; ?></strong></td>
                </tr>
                <tr>
                    <th colspan='4'>Dispatch to Service Center/Receiving Stats</th>
                </tr>
                <tr>
                    <th width='40%'></th>
                    <th width='20%'>Today</th>
                    <th width='20%'>Yesterday</th>
                    <th width='20%'>Month</th>
                </tr>
                <tr>
                    <td><span style='background-color: yellow'><strong>Phones Dispatched</strong><br/>[Total Phone(s) dispatched to service center by kiosk]</td>
                    <td><?php echo $phonesDispatchedTechToday;?></td>
                    <td><?php echo $phonesDispatchedTechYesterday; ?></td>
                    <td><?php echo $phonesDispatchedTechMonth; ?></td>
                </tr>
                
                <tr>
                    <td><span style='background-color: yellow'><strong>Phones Received</strong><br/>[Total Phone(s) received by service center from kiosk]</td>
                    <td><?php echo $phonesReceivedToday;?></td>
                    <td><?php echo $phonesReceivedYesterday; ?></td>
                    <td><?php echo $phonesReceivedMonth; ?></td> 
                </tr>
                <tr>
                    <td style='color:orange;'><strong>Technicians</strong></td>
                    <td colspan='3'></td>
                </tr>
                <?php
                    foreach($users as $userID => $user){
                        $todaysRec = 0;
                        $yesRec = 0;
                        $monRec = 0;
                        if(array_key_exists($userID, $userTodayPhoneReceived)){
                            $todaysRec = $userTodayPhoneReceived[$userID]['total_received'];
                            
                        }
                        if(array_key_exists($userID, $userYesterdayPhoneReceived)){
                            $yesRec = $userYesterdayPhoneReceived[$userID]['total_received'];
                        }
                        if(array_key_exists($userID, $userMonthPhoneReceived)){
                            $monRec = $userMonthPhoneReceived[$userID]['total_received'];
                        }
                        echo "<tr><td style='color:green;'>".ucfirst($user)."</td>
                        <td colspan='1'>$todaysRec</td><td colspan='1'>$yesRec</td><td colspan='1'>$monRec</td></tr>";
                    }
                ?>
                <tr>
                    <th colspan='4'>Repair/Unrepair Stats</th>
                </tr>
                <tr style='background-color: yellow'>
                    <td><span style='background-color: yellow'><strong>Dispatched Repaired</strong><br/>[Total Phone(s) repaired by technician and dispatched back to kiosk]</span></td>
                    <td>
                        <?php
                           // echo $this->Html->link($dispatchedRepairedToday,array('controller' => 'home','action' => 'tech_daily_stats','full_base' => true));
                           echo $dispatchedRepairedToday;
                        ?>
                    </td>
                    <td><?php echo $dispatchedRepairedYesterday; ?></td>
                    <td><?php echo $dispatchedRepairedMonth; ?></td> 
                </tr>
                <tr>
                    <td style='color:orange;'><strong>Technicians</strong></td>
                    <td colspan='3'></td>
                </tr>
                <?php
                    foreach($users as $userID => $user){
                        $todaysRec = 0;
                        $yesRec = 0;
                        $monRec = 0;
                        if(array_key_exists($userID, $userTodayPhoneRepaired)){
                            $todaysRec = $userTodayPhoneRepaired[$userID]['total_repaired'];
                            
                        }
                        if(array_key_exists($userID, $userYesterdayPhoneRepaired)){
                            $yesRec = $userYesterdayPhoneRepaired[$userID]['total_repaired'];
                        }
                        if(array_key_exists($userID, $userMonthPhoneRepaired)){
                            $monRec = $userMonthPhoneRepaired[$userID]['total_repaired'];
                        }
                        echo "<tr><td style='color:green;'>".ucfirst($user)."</td>
                        <td colspan='1'>$todaysRec</td><td colspan='1'>$yesRec</td><td colspan='1'>$monRec</td></tr>";
                    }
                ?>
                <tr style='background-color: yellow'>
                    <td><span style='background-color: yellow'><strong>Dispatched Unrepaired</strong><br/>[Total Phone(s) failed in repair by technician and dispatched back to kiosk]</span></td>
                    <td><?php echo $dispatchedUnrepairedToday;?></td>
                    <td><?php echo $dispatchedUnrepairedYesterday; ?></td>
                    <td><?php echo $dispatchedUnrepairedMonth; ?></td> 
                </tr>
                <tr>
                    <td style='color:orange;'><strong>Technicians</strong></td>
                    <td colspan='3'></td>
                </tr>
                <?php
                    foreach($users as $userID => $user){
                        $todaysRec = 0;
                        $yesRec = 0;
                        $monRec = 0;
                        if(array_key_exists($userID, $userTodayPhoneUnrepaired)){
                            $todaysRec = $userTodayPhoneUnrepaired[$userID]['total_unrepaired'];
                            
                        }
                        if(array_key_exists($userID, $userYesterdayPhoneUnrepaired)){
                            $yesRec = $userYesterdayPhoneUnrepaired[$userID]['total_unrepaired'];
                        }
                        if(array_key_exists($userID, $userMonthPhoneUnrepaired)){
                            $monRec = $userMonthPhoneUnrepaired[$userID]['total_unrepaired'];
                        }
                        echo "<tr><td style='color:green;'>".ucfirst($user)."</td>
                        <td colspan='1'>$todaysRec</td><td colspan='1'>$yesRec</td><td colspan='1'>$monRec</td></tr>";
                    }
                ?>
            </table>
        </td>
    </tr>
</table>