<h3>Upcoming Products</h3>
<?php
	use Cake\Core\Configure;
	use Cake\Core\Configure\Engine\PhpConfig;
	use Cake\I18n\Time;
	$siteBaseURL = Configure::read('SITE_BASE_URL'); //rasu
	$path = realpath(dirname(__FILE__));
	$adminSite = false;
    if (strpos($path, ADMIN_DOMAIN) !== false) {
        $sitePath = ADMIN_DOMAIN;
		$adminSite = true;
    }else{
        $sitePath = 'mbwaheguru.co.uk';
    }
	$adminDomainURL = URL_SCHEME.ADMIN_DOMAIN;
	$group1Str = $group2Str = "";
	//replace WWW_ROOT by this code because of sub-domain or add it to config

    if($productNofification){
			$tableHTML = "";
			$tableHTML1 = "";
			$count = count($productNofification);
			$halfCount = $count/2;
			$firstHalf = array_slice($productNofification,0,$halfCount,true);
			$secondHalf = array_slice($productNofification,$halfCount,$count,true);
			//pr($firstHalf);
			//pr($secondHalf);die;
			foreach($firstHalf as $key => $productNotice){
				//print_r($productNotice);
				$group1Str.="\n$(\".group1{$key}\").colorbox({rel:'group1{$key}'});";
				//pr($productNotice);die;
				$imageDir = WWW_ROOT."files".DS.'Products'.DS.'image'.DS.$productNotice['id'].DS;
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
				$image =  $this->Html->link(
					$this->Html->image($imageURL, array('fullBase' => true,'escapeTitle' => false,'style' => 'width:80px;height:80px;', 'title' => $productNotice['Product'])),
					$largeImageURL,
										 array('escapeTitle' => false, 'title' => $productNotice['Product'],'class' => "group1{$key}")
                            );

				$productNotice['created']->i18nFormat(
                                                                [\IntlDateFormatter::FULL, \IntlDateFormatter::FULL]
                                                        );
				$Created =  $productNotice['created']->i18nFormat('dd-MM-yyyy HH:mm:ss');
				$created = date('d-m-Y h:i:s',strtotime($Created));
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
								  {$productNotice['Product']} with the product-code:{$productNotice['product_code']}<br/>
								And<br/>
								Price of {$CURRENCY_TYPE}{$priceBeforeVat}<br/>
								Created: {$created}
							</td></td>
						</tr>
TABLE;
			}
			
			foreach($secondHalf as $key => $productNotice){
				$group2Str.="\n$(\".group2{$key}\").colorbox({rel:'group2{$key}'});";
				$imageDir = WWW_ROOT."files".DS.'Products'.DS.'image'.DS.$productNotice['id'].DS;
				$imageName =  $productNotice['image'];
				/*
				$absoluteImagePath = $imageDir.$imageName;
				$imageURL = "/thumb_no-image.png";
				
				if(@readlink($absoluteImagePath) ||file_exists($absoluteImagePath)){
					$imageURL = "{$siteBaseURL}/files/Products/image/".$productNotice['id']."/$imageName";
				}
				$image =  $this->Html->image($imageURL, array('fullBase' => true,'escapeTitle' => false, 'style' => 'width:80px;height:80px;','title' => $productNotice['Product']));
				*/
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
				$image =  $this->Html->link(
					$this->Html->image($imageURL, array('fullBase' => true,'escapeTitle' => false,'style' => 'width:80px;height:80px;', 'title' => $productNotice['Product'])),
					$largeImageURL,
										 array('escapeTitle' => false, 'title' => $productNotice['Product'],'class' => "group2{$key}")
                            );
				$productNotice['created']->i18nFormat(
                                                                [\IntlDateFormatter::FULL, \IntlDateFormatter::FULL]
                                                        );
				$Created =  $productNotice['created']->i18nFormat('dd-MM-yyyy HH:mm:ss');
				$created = date('d-m-Y h:i:s',strtotime($Created));
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
								  {$productNotice['Product']} with the product-code:{$productNotice['product_code']}<br/>
								And<br/>
								Price of {$CURRENCY_TYPE}{$priceBeforeVat}<br/>
								Created: {$created}
							</td></td>
						</tr>
TABLE1;
			}
		echo "<table>
			<tr>
			    <td><table cellspacing='0' cellpadding='0' width ='600' style='width:700px;'>$tableHTML1</table></td>
			    <td><table cellspacing='0' cellpadding='0' width ='600' style='width:700px;'>$tableHTML</table></td>
			</tr>
		    </table>";
		
		//    foreach($productNofification as $productNotice){
		//		echo "&raquo; ".$productNotice."<br/>";
		//    }
	
		/*if($warehouseProductNotification){
			foreach($warehouseProductNotification as $warehouseProductNotifice){
				//echo "&raquo; ".$warehouseProductNotifice."<br/>";
			}
			foreach($warehouseProductNotificationArr as $productNotice){
					$imageDir = $www_root."files".DS.'product'.DS.'image'.DS.$productNotice['id'].DS;
					$imageName = 'thumb_'.$productNotice['image'];
					$absoluteImagePath = $imageDir.$imageName;
					$imageURL = "/thumb_no-image.png";
					if(file_exists($absoluteImagePath)){
						$imageURL = "{$siteBaseURL}/files/product/image/".$productNotice['id']."/$imageName";
					}
					$image =  $this->Html->image($imageURL, array('fullBase' => true,'escapeTitle' => false, 'title' => $productNotice['Product']));
		 
					$tableHTML .= <<<TABLE
							<tr>
								<td>&raquo; </td>
								<td>$image</td>
								<td valign='center'>
									A New Product :{$productNotice['Product']} with the product-code:{$productNotice['product_code']}<br/>
									And<br/>
									Price of {$currency}{$productNotice['selling_price']} has been added to the global stock.
								</td>
							</tr>
TABLE;
			}
			echo "<table cellspacing='0' cellpadding='0' width ='600' style='width:700px;'>$tableHTML</table>";
		}*/
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