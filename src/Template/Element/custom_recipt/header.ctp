
  <style type="text/css">

    BODY, TD
    {
      color: #000000;
      font-family: monospaced sans serif;
      font-size: 8px;
	  margin-bottom:20px;
	  margin-top:0px;
	  font-weight: 900;
    }

  </style>
  <?php
  $jQueryURL = "http://ajax.googleapis.com/ajax/libs/jquery/1.4.2/jquery.min.js";
  if(defined('URL_SCHEME')){
  	$jQueryURL = URL_SCHEME."ajax.googleapis.com/ajax/libs/jquery/1.4.2/jquery.min.js";
  }
  ?>
  <script type="text/javascript" src="<?php echo $jQueryURL;?>"></script>
  
<?php
echo $this->Html->link("Back To Home", array('controller' => 'home', 'action' => 'dashboard'),array("style"=>"font-size: 25px;"));
echo "</br>";
echo $this->Html->script('jquery.printElement');?>
<input type='button' id='printSelected' name='print' value='Print Receipt' style='width:220px;' align='center' />

<div id='printDiv' style="margin-right: 0px;">
    <table  cellspacing="0" style="margin-top: 0; margin-bottom: 0; width: 150px;" >