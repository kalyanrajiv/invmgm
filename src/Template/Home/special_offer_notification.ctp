<?php
	use Cake\Core\Configure;
	use Cake\Core\Configure\Engine\PhpConfig;
	$siteBaseURL = Configure::read('SITE_BASE_URL'); //rasu
	?>
	<h3>Products with special offer price:</h3>
	<?php
	$path = realpath(dirname(__FILE__));
    if (strpos($path,'hpwaheguru') !== false) {
        $sitePath = 'hpwaheguru.co.uk';
    }else{
        $sitePath = 'mbwaheguru.co.uk';
    }
	$adminDomainURL = URL_SCHEME.ADMIN_DOMAIN;
	$www_root = "/var/www/vhosts/{$sitePath}/httpdocs/app/webroot/";
	$group1Str = $group2Str = "";
	//replace WWW_ROOT by this code because of sub-domain or add it to config
?>
<?php
    if($productPriceNotification){
			$tableHTML = "";
			$tableHTML1 = "";
			$count = count($productPriceNotification);
			$halfCount = $count/2;
			$firstHalf = array_slice($productPriceNotification,0,$halfCount,true);
			$secondHalf = array_slice($productPriceNotification,$halfCount,$count,true);
			//pr($firstHalf);die;
			//pr($secondHalf);
			foreach($firstHalf as $key=> $productNotice){
				$group1Str.="\n$(\".group1{$key}\").colorbox({rel:'group1{$key}'});";
				$imageDir = WWW_ROOT."files".DS.'Products'.DS.'image'.DS.$productNotice['id'].DS;
				$imageName = $productNotice['image'];
				$largeImageName = 'vga_'.$imageName;
				
				$absoluteImagePath = $imageDir.$imageName;
				$largeImageURL = $imageURL = "/thumb_no-image.png";
				$afterDiscountPrice = number_format($productNotice['selling_price']-$productNotice['selling_price']*$productNotice['discount']/100,2);
				//pr($absoluteImagePath);die;
				if(@file_get_contents($adminDomainURL.'/files/Products/image/'.$productNotice['id'].DS.$imageName)){
					$imageURL = $adminDomainURL.'/files/Products/image/'.$productNotice['id'].DS."thumb_".$imageName;
					$largeImageURL = $adminDomainURL.'/files/Products/image/'.$productNotice['id'].DS.$largeImageName;
				}elseif( @readlink($absoluteImagePath) ||file_exists($absoluteImagePath) ){
					$imageURL = "{$siteBaseURL}/files/Products/image/".$productNotice['id'].DS."thumb_".$imageName;
					$largeImageURL = "$siteBaseURL/files/Products/image/".$productNotice['id'].DS.$largeImageName; //rasu
				}
                //echo $imageURL;die;
				$image =  $this->Html->link(
											$this->Html->image($imageURL, array('fullBase' => true,'escapeTitle' => false, 'title' => $productNotice['Product'], 'style' => 'width:80px;height:80px;')),
											$largeImageURL,
										 array('escapeTitle' => false, 'title' => $productNotice['Product'],'class' => "group1{$key}")
                            );
				$priceUpdatedDate = date('jS M, Y g:i:A',strtotime($productNotice['modified']));//$this->Time->format('jS M, Y g:i:A',$productNotice['modified'],null,null);
	 
				$tableHTML .= <<<TABLE
						<tr>
							<td>&raquo; </td>
							<td>$image</td>
							<td valign='center'>
								<table>
									<tr><td>Product: <span style='color:green;'>{$productNotice['Product']}</span></td></tr>
									<tr><td>Code: <span style='color:green;'>{$productNotice['product_code']}</span></td></tr>
									<tr><td>Price:<span style='color:green;'>{$CURRENCY_TYPE}{$productNotice['selling_price']}</span></td></tr>
									<tr><td>After Discount Price:<span style='color:green;'>{$CURRENCY_TYPE}{$afterDiscountPrice}</span></td></tr>
									<tr><td>Price updated on:<span style='color:green;'>$priceUpdatedDate</span></td></tr>
								</table>
							</td>
						</tr>
TABLE;
			}
			
			foreach($secondHalf as $key => $productNotice){
				$group2Str.="\n$(\".group2{$key}\").colorbox({rel:'group2{$key}'});";
				$imageDir = WWW_ROOT."files".DS.'Products'.DS.'image'.DS.$productNotice['id'].DS;
				$imageName = $productNotice['image'];
				$largeImageName = 'vga_'.$imageName;
				
				$absoluteImagePath = $imageDir.$imageName;
				$imageURL = "/thumb_no-image.png";
				$afterDiscountPrice = number_format($productNotice['selling_price']-$productNotice['selling_price']*$productNotice['discount']/100,2);
				if(@file_get_contents($adminDomainURL.'/files/Products/image/'.$productNotice['id'].DS.$imageName)){
					$imageURL = $adminDomainURL.'/files/Products/image/'.$productNotice['id'].DS."thumb_".$imageName;
					$largeImageURL = $adminDomainURL.'/files/Products/image/'.$productNotice['id'].DS.$largeImageName;
				}elseif( @readlink($absoluteImagePath) ||file_exists($absoluteImagePath) ){
					$imageURL = "{$siteBaseURL}/files/Products/image/".$productNotice['id'].DS."thumb_".$imageName;
					$largeImageURL = "$siteBaseURL/files/Products/image/".$productNotice['id'].DS.$largeImageName; //rasu
				}
				$image =  $this->Html->link(
											$this->Html->image($imageURL, array('fullBase' => true,'escapeTitle' => false, 'title' => $productNotice['Product'], 'style' => 'width:80px;height:80px;')),
											$largeImageURL,
										 array('escapeTitle' => false, 'title' => $productNotice['Product'],'class' => "group2{$key}")
                            );
				$priceUpdatedDate = date('jS M, Y g:i:A',strtotime($productNotice['modified']));//$this->Time->format('jS M, Y g:i:A',$productNotice['modified'],null,null);
	 
				$tableHTML1 .= <<<TABLE1
						<tr>
							<td>&raquo; </td>
							<td>$image</td>
							<td valign='center'>
								<table>
									<tr><td>Product: <span style='color:green;'>{$productNotice['Product']}</span></td></tr>
									<tr><td>Code: <span style='color:green;'>{$productNotice['product_code']}</span></td></tr>
									<tr><td>Price:<span style='color:green;'>{$CURRENCY_TYPE}{$productNotice['selling_price']}</span></td></tr>
									<tr><td>After Discount Price:<span style='color:green;'>{$CURRENCY_TYPE}{$afterDiscountPrice}</span></td></tr>
									<tr><td>Price updated on:<span style='color:green;'>$priceUpdatedDate</span></td></tr>
								</table>
							</td>
						</tr>
TABLE1;
			}
		echo "<table width='100%'>
			<tr>
			    <td><table cellspacing='0' cellpadding='0' style='width:580px;'>$tableHTML1</table></td>
			    <td><table cellspacing='0' cellpadding='0' style='width:580px;'>$tableHTML</table></td>
			</tr>
		    </table>";
		
		//    foreach($productNofification as $productNotice){
		//		echo "&raquo; ".$productNotice."<br/>";
		//    }
	
    }else{
		echo "<h4>No notification for today!</h4>";
    }  
?>
<script>
	$(document).ready(function(){
	<?php echo $group1Str;?>
	<?php echo $group2Str;?>
	});
</script>