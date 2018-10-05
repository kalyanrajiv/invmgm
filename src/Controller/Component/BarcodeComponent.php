<?php
    namespace App\Controller\Component;
    use Cake\Controller\Component;
    use Cake\ORM\TableRegistry;
    use Cake\Network\Session;
	use Cake\Core\Configure;
	use Cake\Core\Configure\Engine\PhpConfig;
    
    //usage e.g : $this->TableDefinition->get_table_defination('product_table',$kiosk_id);
    class BarcodeComponent extends Component {
        public function generate_bar_code($number,$type="png"){
            include('src/BarcodeGenerator.php');
            include('src/BarcodeGeneratorPNG.php');
            include('src/BarcodeGeneratorSVG.php');
            include('src/BarcodeGeneratorJPG.php');
            include('src/BarcodeGeneratorHTML.php');
            
			$barcode = "";
			if($type == "html"){
				 $generator = new \Picqer\Barcode\BarcodeGeneratorHTML();
				 $barcode =  $generator->getBarcode($number, $generator::TYPE_CODE_128);
			}elseif($type == "svg"){
				 $generator = new \Picqer\Barcode\BarcodeGeneratorSVG();
                 $barcode = $generator->getBarcode($number, $generator::TYPE_CODE_128);
			}elseif($type == "png"){
				  $generator = new \Picqer\Barcode\BarcodeGeneratorPNG();
				  $barcode = '<img src="data:image/png;base64,' . base64_encode($generator->getBarcode($number, $generator::TYPE_CODE_128)) . '">';
			}elseif($type == "jpg"){
				 $generator = new \Picqer\Barcode\BarcodeGeneratorJPG();
			 	 $barcode = '<img src="data:image/jpg;base64,' . base64_encode($generator->getBarcode($number, $generator::TYPE_CODE_128)) . '">';
			}
           return $barcode;
        }
    }
    
    
    
    
    
    
    
    
    
    
    
    
    
    
    
    
    
    
    
    
    
    
    
    
    
    
    
    
    