<?php
namespace App\Model\Table;

use Cake\ORM\Query;
use Cake\ORM\RulesChecker;
use Cake\ORM\Table;
use Cake\Validation\Validator;

/**
 * Products Model
 *
 * @property \Cake\ORM\Association\BelongsTo $Categories
 * @property \Cake\ORM\Association\BelongsTo $Brands
 * @property \Cake\ORM\Association\BelongsTo $Users
 * @property \Cake\ORM\Association\BelongsTo $Images
 * @property \Cake\ORM\Association\HasMany $CreditProductDetails
 * @property \Cake\ORM\Association\HasMany $DailyStocks
 * @property \Cake\ORM\Association\HasMany $DefectiveBin
 * @property \Cake\ORM\Association\HasMany $DefectiveBinTransients
 * @property \Cake\ORM\Association\HasMany $DefectiveCentralProducts
 * @property \Cake\ORM\Association\HasMany $DefectiveKioskProducts
 * @property \Cake\ORM\Association\HasMany $DefectiveKioskTransients
 * @property \Cake\ORM\Association\HasMany $FaultyProductDetails
 * @property \Cake\ORM\Association\HasMany $FaultyProducts
 * @property \Cake\ORM\Association\HasMany $ImportOrderDetails
 * @property \Cake\ORM\Association\HasMany $InvoiceOrderDetails
 * @property \Cake\ORM\Association\HasMany $Kiosk10000CreditProductDetails
 * @property \Cake\ORM\Association\HasMany $Kiosk10000DailyStocks
 * @property \Cake\ORM\Association\HasMany $Kiosk10000InvoiceOrderDetails
 * @property \Cake\ORM\Association\HasMany $Kiosk10000ProductSales
 * @property \Cake\ORM\Association\HasMany $Kiosk10ProductSales
 * @property \Cake\ORM\Association\HasMany $Kiosk1CreditProductDetails
 * @property \Cake\ORM\Association\HasMany $Kiosk1DailyStocks
 * @property \Cake\ORM\Association\HasMany $Kiosk1InvoiceOrderDetails
 * @property \Cake\ORM\Association\HasMany $Kiosk1ProductSales
 * @property \Cake\ORM\Association\HasMany $Kiosk2CreditProductDetails
 * @property \Cake\ORM\Association\HasMany $Kiosk2DailyStocks
 * @property \Cake\ORM\Association\HasMany $Kiosk2InvoiceOrderDetails
 * @property \Cake\ORM\Association\HasMany $Kiosk2ProductSales
 * @property \Cake\ORM\Association\HasMany $Kiosk3CreditProductDetails
 * @property \Cake\ORM\Association\HasMany $Kiosk3DailyStocks
 * @property \Cake\ORM\Association\HasMany $Kiosk3DailyStocksBak
 * @property \Cake\ORM\Association\HasMany $Kiosk3DailyStocksBak1
 * @property \Cake\ORM\Association\HasMany $Kiosk3InvoiceOrderDetails
 * @property \Cake\ORM\Association\HasMany $Kiosk3ProductSales
 * @property \Cake\ORM\Association\HasMany $Kiosk4CreditProductDetails
 * @property \Cake\ORM\Association\HasMany $Kiosk4DailyStocks
 * @property \Cake\ORM\Association\HasMany $Kiosk4InvoiceOrderDetails
 * @property \Cake\ORM\Association\HasMany $Kiosk4ProductSales
 * @property \Cake\ORM\Association\HasMany $Kiosk5CreditProductDetails
 * @property \Cake\ORM\Association\HasMany $Kiosk5DailyStocks
 * @property \Cake\ORM\Association\HasMany $Kiosk5InvoiceOrderDetails
 * @property \Cake\ORM\Association\HasMany $Kiosk5ProductSales
 * @property \Cake\ORM\Association\HasMany $Kiosk6CreditProductDetails
 * @property \Cake\ORM\Association\HasMany $Kiosk6DailyStocks
 * @property \Cake\ORM\Association\HasMany $Kiosk6InvoiceOrderDetails
 * @property \Cake\ORM\Association\HasMany $Kiosk6ProductSales
 * @property \Cake\ORM\Association\HasMany $Kiosk7CreditProductDetails
 * @property \Cake\ORM\Association\HasMany $Kiosk7DailyStocks
 * @property \Cake\ORM\Association\HasMany $Kiosk7InvoiceOrderDetails
 * @property \Cake\ORM\Association\HasMany $Kiosk7ProductSales
 * @property \Cake\ORM\Association\HasMany $Kiosk8CreditProductDetails
 * @property \Cake\ORM\Association\HasMany $Kiosk8DailyStocks
 * @property \Cake\ORM\Association\HasMany $Kiosk8InvoiceOrderDetails
 * @property \Cake\ORM\Association\HasMany $Kiosk8ProductSales
 * @property \Cake\ORM\Association\HasMany $KioskProductSales
 * @property \Cake\ORM\Association\HasMany $KioskCancelledOrderProducts
 * @property \Cake\ORM\Association\HasMany $KioskOrderProducts
 * @property \Cake\ORM\Association\HasMany $KioskProductSales
 * @property \Cake\ORM\Association\HasMany $KioskTransferredStock1
 * @property \Cake\ORM\Association\HasMany $KioskTransferredStock10000
 * @property \Cake\ORM\Association\HasMany $KioskTransferredStock2
 * @property \Cake\ORM\Association\HasMany $KioskTransferredStock3
 * @property \Cake\ORM\Association\HasMany $KioskTransferredStock4
 * @property \Cake\ORM\Association\HasMany $KioskTransferredStock5
 * @property \Cake\ORM\Association\HasMany $KioskTransferredStock6
 * @property \Cake\ORM\Association\HasMany $KioskTransferredStock7
 * @property \Cake\ORM\Association\HasMany $KioskTransferredStock8
 * @property \Cake\ORM\Association\HasMany $MobileRepairParts
 * @property \Cake\ORM\Association\HasMany $OnDemandProducts
 * @property \Cake\ORM\Association\HasMany $OrderDisputes
 * @property \Cake\ORM\Association\HasMany $ProductSaleStats
 * @property \Cake\ORM\Association\HasMany $ProductSellStats
 * @property \Cake\ORM\Association\HasMany $ReorderLevels
 * @property \Cake\ORM\Association\HasMany $ReservedProducts
 * @property \Cake\ORM\Association\HasMany $RevertStocks
 * @property \Cake\ORM\Association\HasMany $StockTakingDetails
 * @property \Cake\ORM\Association\HasMany $StockTransfer
 * @property \Cake\ORM\Association\HasMany $StockTransferByKiosk
 * @property \Cake\ORM\Association\HasMany $TKioskProductSales
 * @property \Cake\ORM\Association\HasMany $TProductSaleStats
 * @property \Cake\ORM\Association\HasMany $TProductSellStats
 * @property \Cake\ORM\Association\HasMany $TTempProductSales
 * @property \Cake\ORM\Association\HasMany $TempProductDetails
 * @property \Cake\ORM\Association\HasMany $TransferSurplus
 * @property \Cake\ORM\Association\HasMany $TransferUnderstock
 * @property \Cake\ORM\Association\HasMany $TransientStock
 * @property \Cake\ORM\Association\HasMany $UnderstockLevelOrders
 * @property \Cake\ORM\Association\HasMany $WarehouseStock
 *
 * @method \App\Model\Entity\Product get($primaryKey, $options = [])
 * @method \App\Model\Entity\Product newEntity($data = null, array $options = [])
 * @method \App\Model\Entity\Product[] newEntities(array $data, array $options = [])
 * @method \App\Model\Entity\Product|bool save(\Cake\Datasource\EntityInterface $entity, $options = [])
 * @method \App\Model\Entity\Product patchEntity(\Cake\Datasource\EntityInterface $entity, array $data, array $options = [])
 * @method \App\Model\Entity\Product[] patchEntities($entities, array $data, array $options = [])
 * @method \App\Model\Entity\Product findOrCreate($search, callable $callback = null, $options = [])
 *
 * @mixin \Cake\ORM\Behavior\TimestampBehavior
 */
class ProductsTable extends Table
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

        $this->table('products');
        $this->displayField('id');
        $this->primaryKey('id');
        $this->addBehavior('Timestamp');
        $this->addBehavior('Josegonzalez/Upload.Upload', [
            'image' => [
                'fields' => [
                    // if these fields or their defaults exist
                    // the values will be set.
                    // 'path' =>'webroot{DS}files{DS}{model}{DS}{field}{DS}{primaryKey}{DS}',
                
                    'dir' => 'image_dir', // defaults to `dir`
                    'size' => 'photo_size', // defaults to `size`
                   'type' => 'photo_type', // defaults to `type`
                ],
                 'thumbnailMethod'  => 'php', //or php
                                                'thumbnailSizes' => [ 
                                                            'xvga' => '1024x768',
                                                            'vga' => '640x480',
                                                            'thumb' => '80x80',
                                                            'mini' => '30x30',
                       ],
												'keepFilesOnDelete' => false,
            ],
        ]);
        
        
        $this->belongsTo('Categories', [
            'foreignKey' => 'category_id',
            'joinType' => 'INNER'
        ]);
        $this->belongsTo('Brands', [
            'foreignKey' => 'brand_id',
            'joinType' => 'INNER'
        ]);
        $this->belongsTo('Users', [
            'foreignKey' => 'user_id'
        ]);
        //$this->belongsTo('Images', [
        //    'foreignKey' => 'image_id'
        //]);
        $this->hasMany('CreditProductDetails', [
            'foreignKey' => 'product_id'
        ]);
        $this->hasMany('DailyStocks', [
            'foreignKey' => 'product_id'
        ]);
        $this->hasMany('DefectiveBin', [
            'foreignKey' => 'product_id'
        ]);
        $this->hasMany('DefectiveBinTransients', [
            'foreignKey' => 'product_id'
        ]);
        $this->hasMany('DefectiveCentralProducts', [
            'foreignKey' => 'product_id'
        ]);
        $this->hasMany('DefectiveKioskProducts', [
            'foreignKey' => 'product_id'
        ]);
        $this->hasMany('DefectiveKioskTransients', [
            'foreignKey' => 'product_id'
        ]);
        $this->hasMany('FaultyProductDetails', [
            'foreignKey' => 'product_id'
        ]);
        $this->hasMany('FaultyProducts', [
            'foreignKey' => 'product_id'
        ]);
        $this->hasMany('ImportOrderDetails', [
            'foreignKey' => 'product_id'
        ]);
        $this->hasMany('InvoiceOrderDetails', [
            'foreignKey' => 'product_id'
        ]);
        $this->hasMany('Kiosk10000CreditProductDetails', [
            'foreignKey' => 'product_id'
        ]);
        $this->hasMany('Kiosk10000DailyStocks', [
            'foreignKey' => 'product_id'
        ]);
        $this->hasMany('Kiosk10000InvoiceOrderDetails', [
            'foreignKey' => 'product_id'
        ]);
        $this->hasMany('Kiosk10000ProductSales', [
            'foreignKey' => 'product_id'
        ]);
        $this->hasMany('Kiosk10ProductSales', [
            'foreignKey' => 'product_id'
        ]);
        $this->hasMany('Kiosk1CreditProductDetails', [
            'foreignKey' => 'product_id'
        ]);
        $this->hasMany('Kiosk1DailyStocks', [
            'foreignKey' => 'product_id'
        ]);
        $this->hasMany('Kiosk1InvoiceOrderDetails', [
            'foreignKey' => 'product_id'
        ]);
        $this->hasMany('Kiosk1ProductSales', [
            'foreignKey' => 'product_id'
        ]);
        $this->hasMany('Kiosk2CreditProductDetails', [
            'foreignKey' => 'product_id'
        ]);
        $this->hasMany('Kiosk2DailyStocks', [
            'foreignKey' => 'product_id'
        ]);
        $this->hasMany('Kiosk2InvoiceOrderDetails', [
            'foreignKey' => 'product_id'
        ]);
        $this->hasMany('Kiosk2ProductSales', [
            'foreignKey' => 'product_id'
        ]);
        $this->hasMany('Kiosk3CreditProductDetails', [
            'foreignKey' => 'product_id'
        ]);
        $this->hasMany('Kiosk3DailyStocks', [
            'foreignKey' => 'product_id'
        ]);
        $this->hasMany('Kiosk3DailyStocksBak', [
            'foreignKey' => 'product_id'
        ]);
        $this->hasMany('Kiosk3DailyStocksBak1', [
            'foreignKey' => 'product_id'
        ]);
        $this->hasMany('Kiosk3InvoiceOrderDetails', [
            'foreignKey' => 'product_id'
        ]);
        $this->hasMany('Kiosk3ProductSales', [
            'foreignKey' => 'product_id'
        ]);
        $this->hasMany('Kiosk4CreditProductDetails', [
            'foreignKey' => 'product_id'
        ]);
        $this->hasMany('Kiosk4DailyStocks', [
            'foreignKey' => 'product_id'
        ]);
        $this->hasMany('Kiosk4InvoiceOrderDetails', [
            'foreignKey' => 'product_id'
        ]);
        $this->hasMany('Kiosk4ProductSales', [
            'foreignKey' => 'product_id'
        ]);
        $this->hasMany('Kiosk5CreditProductDetails', [
            'foreignKey' => 'product_id'
        ]);
        $this->hasMany('Kiosk5DailyStocks', [
            'foreignKey' => 'product_id'
        ]);
        $this->hasMany('Kiosk5InvoiceOrderDetails', [
            'foreignKey' => 'product_id'
        ]);
        $this->hasMany('Kiosk5ProductSales', [
            'foreignKey' => 'product_id'
        ]);
        $this->hasMany('Kiosk6CreditProductDetails', [
            'foreignKey' => 'product_id'
        ]);
        $this->hasMany('Kiosk6DailyStocks', [
            'foreignKey' => 'product_id'
        ]);
        $this->hasMany('Kiosk6InvoiceOrderDetails', [
            'foreignKey' => 'product_id'
        ]);
        $this->hasMany('Kiosk6ProductSales', [
            'foreignKey' => 'product_id'
        ]);
        $this->hasMany('Kiosk7CreditProductDetails', [
            'foreignKey' => 'product_id'
        ]);
        $this->hasMany('Kiosk7DailyStocks', [
            'foreignKey' => 'product_id'
        ]);
        $this->hasMany('Kiosk7InvoiceOrderDetails', [
            'foreignKey' => 'product_id'
        ]);
        $this->hasMany('Kiosk7ProductSales', [
            'foreignKey' => 'product_id'
        ]);
        $this->hasMany('Kiosk8CreditProductDetails', [
            'foreignKey' => 'product_id'
        ]);
        $this->hasMany('Kiosk8DailyStocks', [
            'foreignKey' => 'product_id'
        ]);
        $this->hasMany('Kiosk8InvoiceOrderDetails', [
            'foreignKey' => 'product_id'
        ]);
        $this->hasMany('Kiosk8ProductSales', [
            'foreignKey' => 'product_id'
        ]);
        $this->hasMany('KioskProductSales', [
            'foreignKey' => 'product_id'
        ]);
        $this->hasMany('KioskCancelledOrderProducts', [
            'foreignKey' => 'product_id'
        ]);
        $this->hasMany('KioskOrderProducts', [
            'foreignKey' => 'product_id'
        ]);
        $this->hasMany('KioskProductSales', [
            'foreignKey' => 'product_id'
        ]);
        $this->hasMany('KioskTransferredStock1', [
            'foreignKey' => 'product_id'
        ]);
        $this->hasMany('KioskTransferredStock10000', [
            'foreignKey' => 'product_id'
        ]);
        $this->hasMany('KioskTransferredStock2', [
            'foreignKey' => 'product_id'
        ]);
        $this->hasMany('KioskTransferredStock3', [
            'foreignKey' => 'product_id'
        ]);
        $this->hasMany('KioskTransferredStock4', [
            'foreignKey' => 'product_id'
        ]);
        $this->hasMany('KioskTransferredStock5', [
            'foreignKey' => 'product_id'
        ]);
        $this->hasMany('KioskTransferredStock6', [
            'foreignKey' => 'product_id'
        ]);
        $this->hasMany('KioskTransferredStock7', [
            'foreignKey' => 'product_id'
        ]);
        $this->hasMany('KioskTransferredStock8', [
            'foreignKey' => 'product_id'
        ]);
        $this->hasMany('MobileRepairParts', [
            'foreignKey' => 'product_id'
        ]);
        $this->hasMany('OnDemandProducts', [
            'foreignKey' => 'product_id'
        ]);
        $this->hasMany('OrderDisputes', [
            'foreignKey' => 'product_id'
        ]);
        $this->hasMany('ProductSaleStats', [
            'foreignKey' => 'product_id'
        ]);
        $this->hasMany('ProductSellStats', [
            'foreignKey' => 'product_id'
        ]);
        $this->hasMany('ReorderLevels', [
            'foreignKey' => 'product_id'
        ]);
        $this->hasMany('ReservedProducts', [
            'foreignKey' => 'product_id'
        ]);
        $this->hasMany('RevertStocks', [
            'foreignKey' => 'product_id'
        ]);
        $this->hasMany('StockTakingDetails', [
            'foreignKey' => 'product_id'
        ]);
        $this->hasMany('StockTransfer', [
            'foreignKey' => 'product_id'
        ]);
        $this->hasMany('StockTransferByKiosk', [
            'foreignKey' => 'product_id'
        ]);
        $this->hasMany('TKioskProductSales', [
            'foreignKey' => 'product_id'
        ]);
        $this->hasMany('TProductSaleStats', [
            'foreignKey' => 'product_id'
        ]);
        $this->hasMany('TProductSellStats', [
            'foreignKey' => 'product_id'
        ]);
        $this->hasMany('TTempProductSales', [
            'foreignKey' => 'product_id'
        ]);
        $this->hasMany('TempProductDetails', [
            'foreignKey' => 'product_id'
        ]);
        $this->hasMany('TransferSurplus', [
            'foreignKey' => 'product_id'
        ]);
        $this->hasMany('TransferUnderstock', [
            'foreignKey' => 'product_id'
        ]);
        $this->hasMany('TransientStock', [
            'foreignKey' => 'product_id'
        ]);
        $this->hasMany('UnderstockLevelOrders', [
            'foreignKey' => 'product_id'
        ]);
        $this->hasMany('WarehouseStock', [
            'foreignKey' => 'product_id'
        ]);
    }

    /**
     * Default validation rules.
     *
     * @param \Cake\Validation\Validator $validator Validator instance.
     * @return \Cake\Validation\Validator
     */
    public function validationDefault(Validator $validator)
    {
        //$validator
        //    ->requirePresence('prefix', 'create')
        //    ->notEmpty('prefix');
        //
        //$validator
        //    ->integer('id')
        //    ->allowEmpty('id', 'create');

        $validator
            ->requirePresence('product', 'create')
            ->notEmpty('product')
            ->add(
            'product',['unique' => 
            ['rule' => 'validateUnique', 
            'provider' => 'table', 
            'message' => 'Product Name Already In Use.Please Choose Different Product Name']]
        );
            
        
        $validator
         ->notEmpty('product_code',"Product Code is missing")
            ->requirePresence('product_code', 'create')
            ->notEmpty('product_code')
            ->add(
            'product_code',[
                            'unique' => [
                                         'rule' => 'validateUnique', 
                                        'provider' => 'table', 
                                        'message' => 'Product Code Already In Use.Please Choose Different Product Code'],
                            'minLength' => [
                                            'rule' => ['minLength', 3],
                                            'last' => true,
                                            'message' => 'Product Code minimum lenght can\'t be less than 3.'],
                            'maxLength' => [
                                'rule' => ['maxLength', 50],
                                'message' => 'Product Code cannot be too long.']
                            ]
        );
        //$validator
        //    ->integer('quantity')
        //    ->requirePresence('quantity', 'create')
        //    ->notEmpty('quantity');

        //$validator
        //    ->requirePresence('description', 'create')
        //    ->notEmpty('description');

        //$validator
        //    ->allowEmpty('location');

        //$validator
        //    ->numeric('cost_price','cost_price price should be numeric')
        //    ->requirePresence('cost_price', 'create')
        //    ->notEmpty('cost_price');
        $validator->add('cost_price',[
                'compare'=>[
                'rule'=>'validate_cost_price',
                'provider'=>'table',
                'message'=>'cost_price price should be numeric and more then zero'
                 ]
        ]);
        //$validator
        //    ->dateTime('lu_cp')
        //    ->allowEmpty('lu_cp');

        //$validator
        //    ->numeric('retail_cost_price','retail_cost_price price should be numeric')
        //    ->allowEmpty('retail_cost_price');
        $validator->add('retail_cost_price',[
                'compare'=>[
                'rule'=>'validate_retail_cost_price',
                //'provider'=>'table',
                'message'=>'retail_cost_price price should be numeric and more then zero'
                 ]
        ]);
        //$validator
        //    ->dateTime('lu_rcp')
        //    ->allowEmpty('lu_rcp');

        //$validator
        //    ->numeric('selling_price','selling price should be numeric')
        //    ->requirePresence('selling_price', 'create')
        //    ->notEmpty('selling_price');

              $validator->add('selling_price',[
        'compare'=>[
        'rule'=>'validate_price',
        'provider'=>'table',
        'message'=>'Selling Price should be greater than cost price'
         ]
        ]);
             
        //$validator
        //    ->dateTime('lu_sp')
        //    ->allowEmpty('lu_sp');

        $validator
            ->numeric('retail_selling_price')
            ->requirePresence('retail_selling_price', 'create')
            ->notEmpty('retail_selling_price')
            ->add('retail_selling_price',[
                        'compare'=>[
                        'rule'=>'validate_retail_price',
                        'provider'=>'table',
                        'message'=>'Retail Selling Price should be greater than Retail cost price'
                         ]
                        ]);
            
        //$validator
        //    ->dateTime('lu_rsp')
        //    ->allowEmpty('lu_rsp');

        //$validator
        //    ->requirePresence('model', 'create')
        //    ->notEmpty('model');

        //$validator
        //    ->date('manufacturing_date')
        //    ->requirePresence('manufacturing_date', 'create')
        //    ->notEmpty('manufacturing_date');

        //$validator
        //    ->integer('sku')
        //    ->requirePresence('sku', 'create')
        //    ->notEmpty('sku');

        //$validator
        //    ->requirePresence('country_make', 'create')
        //    ->notEmpty('country_make');

        

         $validator->add('retail_cost_price',[
                'compare'=>[
                'rule'=>'validate_retail_cost_price',
                'provider'=>'table',
                'message'=>'retail_cost_price price should be numeric and more then zero'
                 ]
        ]);
        
        //$validator
        //    ->numeric('weight')
        //    ->allowEmpty('weight');

        $validator
            ->requirePresence('color', 'create')
            ->notEmpty('color');

        //$validator
        //    ->integer('featured')
        //    ->allowEmpty('featured');

        //$validator
        //    ->integer('discount')
        //    ->allowEmpty('discount');

        //$validator
        //    ->integer('retail_discount')
        //    ->allowEmpty('retail_discount');

        //$validator
        //    ->integer('discount_status')
        //    ->requirePresence('discount_status', 'create')
        //    ->notEmpty('discount_status');

        //$validator
        //    ->integer('rt_discount_status')
        //    ->allowEmpty('rt_discount_status');

        //$validator
        //    ->integer('max_discount')
        //    ->requirePresence('max_discount', 'create')
        //    ->notEmpty('max_discount');

        //$validator
        //    ->integer('min_discount')
        //    ->requirePresence('min_discount', 'create')
        //    ->notEmpty('min_discount');

        $validator
         ->allowEmpty('image');

        $validator
            ->requirePresence('brand_id', 'create')
            ->notEmpty('brand_id');

        //$validator
        //    ->requirePresence('manufacturer', 'create')
        //    ->notEmpty('manufacturer');

        $validator
            ->integer('stock_level','stock level should be numeric')
            ->requirePresence('stock_level', 'create')
            ->notEmpty('stock_level');
        //$validator
        //    ->integer('dead_stock_level')
        //    ->requirePresence('dead_stock_level', 'create')
        //    ->notEmpty('dead_stock_level');

        $validator
            ->integer('status')
            ->requirePresence('status', 'create')
            ->notEmpty('status');

        return $validator;
    }

    /**
     * Returns a rules checker object that will be used for validating
     * application integrity.
     *
     * @param \Cake\ORM\RulesChecker $rules The rules object to be modified.
     * @return \Cake\ORM\RulesChecker
     */
    public function buildRules(RulesChecker $rules)
    {
        $rules->add($rules->isUnique(['product_code']));
        $rules->add($rules->existsIn(['category_id'], 'Categories'));
        $rules->add($rules->existsIn(['brand_id'], 'Brands'));
        $rules->add($rules->existsIn(['user_id'], 'Users'));
       // $rules->add($rules->existsIn(['image_id'], 'Images'));

        return $rules;
    }
     public function validate_price($value,$context) {
		//  pr($context);die;
		if( array_key_exists('selling_price',$context['data'] ) && array_key_exists('cost_price',$context['data']) ){
			if($context['data']['selling_price'] > $context['data']['cost_price']){
				return true;
			}
			return false;
		}else{
          
			return true;
		}
    }
    
    
    public function validate_retail_price($value,$context) {
		//  pr($context);die;
		if( array_key_exists('retail_selling_price',$context['data'] ) && array_key_exists('retail_cost_price',$context['data']) ){
			if($context['data']['retail_selling_price'] > $context['data']['retail_cost_price']){
				return true;
			}
			return false;
		}else{
          
			return true;
		}
    }
    
    public function validate_cost_price($value,$context) {
		//print_r($this->data);die;
		if(array_key_exists('cost_price',$context['data']) ){
			if($context['data']['cost_price'] > 0){
				return true;
			}
			return false;
		}else{
			return true;
		}
    }
	
	public function validate_retail_cost_price($value,$context) {
		//print_r($this->data);die;
		if(array_key_exists('retail_cost_price',$context['data']) ){
			if($context['data']['retail_cost_price'] > 0){
				return true;
			}
			return false;
		}else{
			return true;
		}
    }
}
