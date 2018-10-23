<?php
    if(count($mobileModels) >= 1){
        $chunks = array_chunk($mobileModels,8,true);
        if(count($chunks)){
            $colmnStr = "";
            foreach($chunks as $c => $chunk){
                $colmnStr.="<tr>";
                foreach($chunk as $ch => $condition){
                    $colmnStr.="<td>".$this->Form->input($condition, array('type' => 'checkbox',
                      'name'=>'Product[additional_model_id][]',
                      'label' => array('style' => "color: blue;"),
                      'value' => $ch,
                      'hiddenField' => false
                      ))."</td>";
                }
                $colmnStr.="</tr>";        
            }
            echo $tblHTML = <<<TBL_HMTL
                <table id = 'additional_model'>
                    <tr><td colspan='8'><h4>Additional Model</h4><hr/></td></tr>
                    $colmnStr
                    </tr>
                </table>
TBL_HMTL;
        }
	}
?>