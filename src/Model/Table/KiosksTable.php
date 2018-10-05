<?php
namespace App\Model\Table;

use Cake\ORM\Query;
use Cake\ORM\RulesChecker;
use Cake\ORM\Table;
use Cake\Validation\Validator;
use Cake\Routing\Router;
use Cake\ORM\TableRegistry;
use Cake\Core\Configure;
use Cake\Core\Configure\Engine\PhpConfig;
/**
 * Kiosks Model
 *
 * @property \Cake\ORM\Association\HasMany $CenterOrders
 * @property \Cake\ORM\Association\HasMany $CreditProductDetails
 * @property \Cake\ORM\Association\HasMany $Customers
 * @property \Cake\ORM\Association\HasMany $DailyTargets
 * @property \Cake\ORM\Association\HasMany $DeadProducts
 * @property \Cake\ORM\Association\HasMany $DefectiveBinReferences
 * @property \Cake\ORM\Association\HasMany $DefectiveBinTransients
 * @property \Cake\ORM\Association\HasMany $DefectiveBins
 * @property \Cake\ORM\Association\HasMany $DefectiveKioskProducts
 * @property \Cake\ORM\Association\HasMany $DefectiveKioskReferences
 * @property \Cake\ORM\Association\HasMany $DefectiveKioskTransients
 * @property \Cake\ORM\Association\HasMany $FaultyProducts
 * @property \Cake\ORM\Association\HasMany $FaultyReturnedProducts
 * @property \Cake\ORM\Association\HasMany $InvoiceOrderDetails
 * @property \Cake\ORM\Association\HasMany $InvoiceOrders
 * @property \Cake\ORM\Association\HasMany $Kiosk10000ProductSales
 * @property \Cake\ORM\Association\HasMany $Kiosk10CreditProductDetails
 * @property \Cake\ORM\Association\HasMany $Kiosk10InvoiceOrderDetails
 * @property \Cake\ORM\Association\HasMany $Kiosk10InvoiceOrders
 * @property \Cake\ORM\Association\HasMany $Kiosk10ProductSales
 * @property \Cake\ORM\Association\HasMany $Kiosk11CreditProductDetails
 * @property \Cake\ORM\Association\HasMany $Kiosk11InvoiceOrderDetails
 * @property \Cake\ORM\Association\HasMany $Kiosk11InvoiceOrders
 * @property \Cake\ORM\Association\HasMany $Kiosk11ProductSales
 * @property \Cake\ORM\Association\HasMany $Kiosk12ProductSales
 * @property \Cake\ORM\Association\HasMany $Kiosk13ProductSales
 * @property \Cake\ORM\Association\HasMany $Kiosk14ProductSales
 * @property \Cake\ORM\Association\HasMany $Kiosk15ProductSales
 * @property \Cake\ORM\Association\HasMany $Kiosk16ProductSales
 * @property \Cake\ORM\Association\HasMany $Kiosk17ProductSales
 * @property \Cake\ORM\Association\HasMany $Kiosk18ProductSales
 * @property \Cake\ORM\Association\HasMany $Kiosk19CreditProductDetails
 * @property \Cake\ORM\Association\HasMany $Kiosk19InvoiceOrderDetails
 * @property \Cake\ORM\Association\HasMany $Kiosk19InvoiceOrders
 * @property \Cake\ORM\Association\HasMany $Kiosk19ProductSales
 * @property \Cake\ORM\Association\HasMany $Kiosk1CreditProductDetails
 * @property \Cake\ORM\Association\HasMany $Kiosk1ProductSales
 * @property \Cake\ORM\Association\HasMany $Kiosk20CreditProductDetails
 * @property \Cake\ORM\Association\HasMany $Kiosk20InvoiceOrderDetails
 * @property \Cake\ORM\Association\HasMany $Kiosk20InvoiceOrders
 * @property \Cake\ORM\Association\HasMany $Kiosk20ProductSales
 * @property \Cake\ORM\Association\HasMany $Kiosk21CreditProductDetails
 * @property \Cake\ORM\Association\HasMany $Kiosk21InvoiceOrderDetails
 * @property \Cake\ORM\Association\HasMany $Kiosk21InvoiceOrders
 * @property \Cake\ORM\Association\HasMany $Kiosk21ProductSales
 * @property \Cake\ORM\Association\HasMany $Kiosk22ProductSales
 * @property \Cake\ORM\Association\HasMany $Kiosk2ProductSales
 * @property \Cake\ORM\Association\HasMany $Kiosk3ProductSales
 * @property \Cake\ORM\Association\HasMany $Kiosk4ProductSales
 * @property \Cake\ORM\Association\HasMany $Kiosk5CreditProductDetails
 * @property \Cake\ORM\Association\HasMany $Kiosk5InvoiceOrderDetails
 * @property \Cake\ORM\Association\HasMany $Kiosk5InvoiceOrders
 * @property \Cake\ORM\Association\HasMany $Kiosk5ProductSales
 * @property \Cake\ORM\Association\HasMany $Kiosk7CreditProductDetails
 * @property \Cake\ORM\Association\HasMany $Kiosk7InvoiceOrderDetails
 * @property \Cake\ORM\Association\HasMany $Kiosk7InvoiceOrders
 * @property \Cake\ORM\Association\HasMany $Kiosk7ProductSales
 * @property \Cake\ORM\Association\HasMany $Kiosk8CreditProductDetails
 * @property \Cake\ORM\Association\HasMany $Kiosk8InvoiceOrderDetails
 * @property \Cake\ORM\Association\HasMany $Kiosk8InvoiceOrders
 * @property \Cake\ORM\Association\HasMany $Kiosk8ProductSales
 * @property \Cake\ORM\Association\HasMany $KioskFaultyProductDetails
 * @property \Cake\ORM\Association\HasMany $KioskProductSales
 * @property \Cake\ORM\Association\HasMany $KioskCancelledOrderProducts
 * @property \Cake\ORM\Association\HasMany $KioskOrderProducts
 * @property \Cake\ORM\Association\HasMany $KioskOrders
 * @property \Cake\ORM\Association\HasMany $KioskPlacedOrders
 * @property \Cake\ORM\Association\HasMany $KioskProductSales
 * @property \Cake\ORM\Association\HasMany $KioskTiming
 * @property \Cake\ORM\Association\HasMany $KioskTimings
 * @property \Cake\ORM\Association\HasMany $MobileBlkReSalePayments
 * @property \Cake\ORM\Association\HasMany $MobileBlkReSales
 * @property \Cake\ORM\Association\HasMany $MobilePayments
 * @property \Cake\ORM\Association\HasMany $MobilePlacedOrders
 * @property \Cake\ORM\Association\HasMany $MobilePurchases
 * @property \Cake\ORM\Association\HasMany $MobileReSalePayments
 * @property \Cake\ORM\Association\HasMany $MobileReSales
 * @property \Cake\ORM\Association\HasMany $MobileRepairLogs
 * @property \Cake\ORM\Association\HasMany $MobileRepairParts
 * @property \Cake\ORM\Association\HasMany $MobileRepairSales
 * @property \Cake\ORM\Association\HasMany $MobileRepairs
 * @property \Cake\ORM\Association\HasMany $MobileTransferLogs
 * @property \Cake\ORM\Association\HasMany $MobileUnlockLogs
 * @property \Cake\ORM\Association\HasMany $MobileUnlockSales
 * @property \Cake\ORM\Association\HasMany $MobileUnlocks
 * @property \Cake\ORM\Association\HasMany $OnDemandOrders
 * @property \Cake\ORM\Association\HasMany $OnDemandProducts
 * @property \Cake\ORM\Association\HasMany $OrderDisputes
 * @property \Cake\ORM\Association\HasMany $ProductPayments
 * @property \Cake\ORM\Association\HasMany $ReorderLevels
 * @property \Cake\ORM\Association\HasMany $RepairPayments
 * @property \Cake\ORM\Association\HasMany $RetailCustomers
 * @property \Cake\ORM\Association\HasMany $RevertStocks
 * @property \Cake\ORM\Association\HasMany $SessionBackups
 * @property \Cake\ORM\Association\HasMany $StockTakingDetails
 * @property \Cake\ORM\Association\HasMany $StockTakingReferences
 * @property \Cake\ORM\Association\HasMany $TKioskProductSales
 * @property \Cake\ORM\Association\HasMany $TempProductDetails
 * @property \Cake\ORM\Association\HasMany $TempProductOrders
 * @property \Cake\ORM\Association\HasMany $UnderstockLevelOrders
 * @property \Cake\ORM\Association\HasMany $UnlockPayments
 * @property \Cake\ORM\Association\HasMany $UserAttendances
 *
 * @method \App\Model\Entity\Kiosk get($primaryKey, $options = [])
 * @method \App\Model\Entity\Kiosk newEntity($data = null, array $options = [])
 * @method \App\Model\Entity\Kiosk[] newEntities(array $data, array $options = [])
 * @method \App\Model\Entity\Kiosk|bool save(\Cake\Datasource\EntityInterface $entity, $options = [])
 * @method \App\Model\Entity\Kiosk patchEntity(\Cake\Datasource\EntityInterface $entity, array $data, array $options = [])
 * @method \App\Model\Entity\Kiosk[] patchEntities($entities, array $data, array $options = [])
 * @method \App\Model\Entity\Kiosk findOrCreate($search, callable $callback = null, $options = [])
 *
 * @mixin \Cake\ORM\Behavior\TimestampBehavior
 */
class KiosksTable extends Table
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

        $this->table('kiosks');
        $this->displayField('name');
        $this->primaryKey('id');

        $this->addBehavior('Timestamp');

        
        $this->hasMany('Customers', [
            'foreignKey' => 'kiosk_id'
        ]);
         $this->hasMany('MobileRepairs', [
            'foreignKey' => 'kiosk_id'
        ]);
        
        $this->hasMany('ReorderLevels', [
            'foreignKey' => 'kiosk_id'
        ]);
        
        $this->hasMany('ProductSellStats', [
            'foreignKey' => 'kiosk_id'
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
        $validator
            ->integer('id')
            ->allowEmpty('id', 'create');

        $validator
            ->requirePresence('code', 'create')
            ->notEmpty('code');

        $validator
            ->requirePresence('name', 'create')
            ->notEmpty('name');

        $validator
            ->email('email')
            ->requirePresence('email', 'create')
            ->notEmpty('email');

        //$validator
        //    ->requirePresence('communication_password', 'create')
        //    ->notEmpty('communication_password');

        $validator
            ->requirePresence('address_1', 'create')
            ->notEmpty('address_1');
 

        $validator
            ->requirePresence('zip', 'create')
            ->notEmpty('zip');

        $validator
            ->requirePresence('contact', 'create')
            ->notEmpty('contact');

        $validator
            ->numeric('rent')
            ->requirePresence('rent', 'create')
            ->notEmpty('rent');

        

        $validator
            ->date('agreement_from')
            ->requirePresence('agreement_from', 'create')
            ->notEmpty('agreement_from');

        $validator
            ->date('agreement_to')
            ->requirePresence('agreement_to', 'create')
            ->notEmpty('agreement_to');

        $validator
            ->requirePresence('break_clause', 'create')
            ->notEmpty('break_clause');

        //$validator
        //    ->integer('renewal_weeks')
        //    ->requirePresence('renewal_weeks', 'create')
        //    ->notEmpty('renewal_weeks');

        $validator
            ->integer('status')
            ->requirePresence('status', 'create')
            ->notEmpty('status');

        //$validator
        //    ->integer('renewal_months')
        //    ->requirePresence('renewal_months', 'create')
        //    ->notEmpty('renewal_months');

        //$validator
        //    ->integer('kiosk_type')
        //    ->requirePresence('kiosk_type', 'create')
        //    ->notEmpty('kiosk_type');

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
        $rules->add($rules->isUnique(['email']));

        return $rules;
    }
    
      public function beforeFind($event,$query) {
        $user_group_id = Router::getRequest()->session()->read("Auth.User.group_id");//->read((new AuthComponent())->sessionKey);
       // echo $user_group_id;
       $external_site_arry = $CURRENCY_TYPE = Configure::read('external_sites');
        $path = dirname(__FILE__);
        $ext_site = 0;
        if(!empty($external_site_arry)){
            foreach($external_site_arry as $k=>$v){
                    $isboloRam = strpos($path,$v);
                    if($isboloRam){
                        $ext_site = 1;
                    }
            }    
        }
       if($ext_site == 1){
            if($user_group_id && !in_array($user_group_id,array(7,8,1))){
                 if($user_group_id != 1){
                     $kiosk_data = Router::getRequest()->session()->read("Auth.User.kiosk_assigned");//->read((new AuthComponent())->sessionKey);
                     $kiosk_ids = explode("|",$kiosk_data);
                   //  pr($kiosk_ids);
                     if(empty($kiosk_ids)){
                         $kiosk_ids = array(0 => null);
                     }
                     $query->where(['Kiosks.id IN' => $kiosk_ids]);    
                 }
            }
       }
	}
}
