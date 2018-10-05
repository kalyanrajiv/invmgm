<?php
namespace App\Model\Table;

use Cake\ORM\Query;
use Cake\ORM\RulesChecker;
use Cake\ORM\Table;
use Cake\Validation\Validator;
use Cake\Core\Configure;
use Cake\ORM\TableRegistry;
use Cake\Core\Configure\Engine\PhpConfig;

/**
 * KioskProductSales Model
 *
 * @property \Cake\ORM\Association\BelongsTo $Kiosks
 * @property \Cake\ORM\Association\BelongsTo $Products
 * @property \Cake\ORM\Association\BelongsTo $Customers
 * @property \Cake\ORM\Association\BelongsTo $ProductReceipts
 *
 * @method \App\Model\Entity\KioskProductSale get($primaryKey, $options = [])
 * @method \App\Model\Entity\KioskProductSale newEntity($data = null, array $options = [])
 * @method \App\Model\Entity\KioskProductSale[] newEntities(array $data, array $options = [])
 * @method \App\Model\Entity\KioskProductSale|bool save(\Cake\Datasource\EntityInterface $entity, $options = [])
 * @method \App\Model\Entity\KioskProductSale patchEntity(\Cake\Datasource\EntityInterface $entity, array $data, array $options = [])
 * @method \App\Model\Entity\KioskProductSale[] patchEntities($entities, array $data, array $options = [])
 * @method \App\Model\Entity\KioskProductSale findOrCreate($search, callable $callback = null, $options = [])
 *
 * @mixin \Cake\ORM\Behavior\TimestampBehavior
 */
class KioskProductSalesTable extends Table
{

    /**
     * Initialize method
     *
     * @param array $config The configuration for the Table.
     * @return void
     */
    public function initialize(array $config)
    {
        parent::initialize($config);
       $exists = Configure::check('company.table');
		if($exists){
             $table_suffics = Configure::read('company.table');echo "</br>";
		}else{
            $table_suffics = "";
        }
       // Configure::delete('company.table');
        if($table_suffics){
            $table_name = "kiosk_{$table_suffics}_product_sales";
            $products_table = "kiosk_{$table_suffics}_Products";
		
            $recit_table = "kiosk_{$table_suffics}_Product_Receipts";
			
			$recit_class = "kiosk_{$table_suffics}_product_receipts";
			$product_class = "kiosk_{$table_suffics}_products";
        }else{
			$recit_class = "ProductReceipts";
			$product_class = "Products"; 
         $table_name = 'kiosk_product_sales';
         $products_table = 'Products';
         $recit_table = "ProductReceipts";
        }
		//echo $recit_table;die;
        //echo $table_name;echo "<br>";
        //echo $products_table;echo "<br>";die;
        //echo $recit_table;die;echo "<br>";
        $this->table($table_name);
        
        $this->displayField('id');
        $this->primaryKey('id');

        $this->addBehavior('Timestamp');

        $this->belongsTo('Kiosks', [
            'foreignKey' => 'kiosk_id',
            'joinType' => 'INNER'
        ]);
        $this->belongsTo($products_table, [
			 'className' => $product_class,
			 //'propertyName' => $products_table,
            'foreignKey' => 'product_id',
            'joinType' => 'INNER',
			//'propertyName' => $products_table
        ]);
        $this->belongsTo('Customers', [
            'foreignKey' => 'customer_id',
            'joinType' => 'INNER'
        ]);
        $this->belongsTo($recit_table, [
			 'className' => $recit_class,
            'foreignKey' => 'product_receipt_id',
            'joinType' => 'INNER',
			//'propertyName' => $recit_table
        ]);
    }

    /**
     * Default validation rules.
     *
     * @param \Cake\Validation\Validator $validator Validator instance.
     * @return \Cake\Validation\Validator
     */
    
    /**
     * Returns a rules checker object that will be used for validating
     * application integrity.
     *
     * @param \Cake\ORM\RulesChecker $rules The rules object to be modified.
     * @return \Cake\ORM\RulesChecker
     */
    //public function buildRules(RulesChecker $rules)
    //{
    //    $rules->add($rules->existsIn(['kiosk_id'], 'Kiosks'));
    //    $rules->add($rules->existsIn(['product_id'], 'Products'));
    //    $rules->add($rules->existsIn(['customer_id'], 'Customers'));
    //    $rules->add($rules->existsIn(['product_receipt_id'], 'ProductReceipts'));
    //
    //    return $rules;
    //}
}
