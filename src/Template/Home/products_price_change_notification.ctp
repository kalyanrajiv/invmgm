<h3>Products with updated price:</h3>
<?php
	use Cake\Core\Configure;
	use Cake\Core\Configure\Engine\PhpConfig;
	$siteBaseURL = Configure::read('SITE_BASE_URL'); //rasu
	$path = realpath(dirname(__FILE__));
	$adminSite = false;
	
    if (strpos($path,ADMIN_DOMAIN) !== false) {
        $sitePath = ADMIN_DOMAIN;
		$adminSite = true;
    }else{
        $sitePath = 'mbwaheguru.co.uk';
    }
	$adminDomainURL = URL_SCHEME.ADMIN_DOMAIN;
	$www_root = "/var/www/vhosts/{$sitePath}/httpdocs/app/webroot/";
	$group1Str = $group2Str = "";
	//replace WWW_ROOT by this code because of sub-domain or add it to config

    if($productPriceNotification){
		$tableHTML = "";
		$tableHTML1 = "";
		$count = count($productPriceNotification);
		$halfCount = $count/2;
		$firstHalf = array_slice($productPriceNotification,0,$halfCount,true);
		$secondHalf = array_slice($productPriceNotification,$halfCount,$count,true);
		foreach($firstHalf as $key => $productNotice){
			$group1Str.="\n$(\".group1{$key}\").colorbox({rel:'group1{$key}'});";
			$imageDir = WWW_ROOT.DS."files".DS.'Products'.DS.'image'.DS.$productNotice['id'].DS;
			$imageName = $productNotice['image'];
			$largeImageName = 'vga_'.$imageName;
			$absoluteImagePath = $imageDir.$imageName;
			$imageURL = "/thumb_no-image.png";
			
			$largeImageURL = $imageURL;    
			if(@file_get_contents($adminDomainURL.'/files/Products/image/'.$productNotice['id'].DS.$imageName)){
				$imageURL = $adminDomainURL.'/files/Products/image/'.$productNotice['id'].DS."thumb_".$imageName;
				$largeImageURL = $adminDomainURL.'/files/Products/image/'.$productNotice['id'].DS.$largeImageName;
			}elseif( @readlink($absoluteImagePath) ||file_exists($absoluteImagePath) ){
				$imageURL = "{$siteBaseURL}/files/Products/image/".$productNotice['id'].DS."thumb_".$imageName;
				$largeImageURL = "$siteBaseURL/files/Products/image/".$productNotice['id'].DS.$largeImageName; //rasu
			}
			
			$image =  $this->Html->link($this->Html->image($imageURL, array(
																			'fullBase' => true,
																			'escapeTitle' => false,
																			'title' => $productNotice['Product'],
																			'style' => 'width:80px;height:80px;'
																			)),
											$largeImageURL,
											array('escapeTitle' => false, 'title' => $productNotice['Product'],'class' => "group1{$key}")
										);
			$priceUpdatedDate = $this->Time->format('jS M, Y g:i:A',$productNotice['lu_sp'],null,null);
			
			$withVATSP = $productNotice['selling_price'];//The gross price, including VAT.
			$vatDivisor = 1 + ($vat / 100);	//Divisor (for our math).
			$priceBeforeVat = $withVATSP / $vatDivisor; //Determine the price before VAT.
			$vatAmount = $withVATSP - $priceBeforeVat;
			if(!$adminSite){$priceBeforeVat =$withVATSP;}
			$tableHTML .= <<<TABLE
					<tr>
						<td>&raquo; </td>
						<td>$image</td>
						<td valign='center'>
							<table>
								<tr><td>Product: <span style='color:green;'>{$productNotice['Product']}</span></td></tr>
								<tr><td>Code: <span style='color:green;'>{$productNotice['product_code']}</span></td></tr>
								<tr><td>Price:<span style='color:green;'>{$currency}{$priceBeforeVat}</span></td></tr>
								<tr><td>Price updated on:<span style='color:green;'>$priceUpdatedDate</span></td></tr>
							</table>
						</td>
					</tr>
TABLE;
		}
		
		foreach($secondHalf as $key => $productNotice){
			$group2Str.="\n$(\".group2{$key}\").colorbox({rel:'group2{$key}'});";
			$imageDir = WWW_ROOT.DS."files".DS.'Products'.DS.'image'.DS.$productNotice['id'].DS;
			$imageName = $productNotice['image'];
			$largeImageName = 'vga_'.$imageName;
			$absoluteImagePath = $imageDir.$imageName;
			$imageURL = "/thumb_no-image.png";
			$largeImageURL = $imageURL;
			
			if(@file_get_contents($adminDomainURL.'/files/Products/image/'.$productNotice['id'].DS.$imageName)){
				$imageURL = $adminDomainURL.'/files/Products/image/'.$productNotice['id'].DS."thumb_".$imageName;
				$largeImageURL = $adminDomainURL.'/files/Products/image/'.$productNotice['id'].DS.$largeImageName;
			}elseif( @readlink($absoluteImagePath) ||file_exists($absoluteImagePath) ){
				$imageURL = "{$siteBaseURL}/files/Products/image/".$productNotice['id'].DS."thumb_".$imageName;
				$largeImageURL = "$siteBaseURL/files/Products/image/".$productNotice['id'].DS.$largeImageName; //rasu
			}
			
			$image =  $this->Html->link($this->Html->image($imageURL, array(
																			'fullBase' => true,
																			'escapeTitle' => false,
																			'title' => $productNotice['Product'],
																			'style' => 'width:80px;height:80px;')
															),
															$largeImageURL,
															array('escapeTitle' => false, 'title' => $productNotice['Product'],'class' => "group2{$key}")
									);
			
			$priceUpdatedDate = date('jS M, Y g:i:A',strtotime($productNotice['lu_sp']));//$this->Time->format('jS M, Y g:i:A',$productNotice['lu_sp'],null,null);
			$withVATSP = $productNotice['selling_price'];//The gross price, including VAT.
			$vatDivisor = 1 + ($vat / 100);	//Divisor (for our math).
			$priceBeforeVat = $withVATSP / $vatDivisor; //Determine the price before VAT.
			$vatAmount = $withVATSP - $priceBeforeVat;
			if(!$adminSite){$priceBeforeVat =$withVATSP;}
			
			$tableHTML1 .= <<<TABLE1
					<tr>
						<td>&raquo; </td>
						<td>$image</td>
						<td valign='center'>
							<table>
								<tr><td>Product: <span style='color:green;'>{$productNotice['Product']}</span></td></tr>
								<tr><td>Code: <span style='color:green;'>{$productNotice['product_code']}</span></td></tr>
								<tr><td>Price:<span style='color:green;'>{$CURRENCY_TYPE}{$priceBeforeVat}</span></td></tr>
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