<?php
    if(count($mobileModels) >= 1){
        echo "<option value='0'>Select Model</option>";
        foreach ($mobileModels as $key => $value):
?>
            <option value="<?php echo $key; ?>"><?php echo $value; ?></option>
<?php
        endforeach;
    }else{
        echo '<option value="0">No Option Available</option>';
    }
?>