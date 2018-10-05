<?php
namespace App\Model\Table;

use Cake\ORM\Query;
use Cake\ORM\RulesChecker;
use Cake\ORM\Table;
use Cake\Validation\Validator;

/**
 * ProductReceipts Model
 *
 * @property \Cake\ORM\Association\BelongsTo $Customers
 * @property \Cake\ORM\Association\HasMany $Kiosk10000PaymentDetails
 * @property \Cake\ORM\Association\HasMany $Kiosk10000ProductSales
 * @property \Cake\ORM\Association\HasMany $Kiosk10PaymentDetails
 * @property \Cake\ORM\Association\HasMany $Kiosk10ProductSales
 * @property \Cake\ORM\Association\HasMany $Kiosk11PaymentDetails
 * @property \Cake\ORM\Association\HasMany $Kiosk11ProductSales
 * @property \Cake\ORM\Association\HasMany $Kiosk12PaymentDetails
 * @property \Cake\ORM\Association\HasMany $Kiosk12ProductSales
 * @property \Cake\ORM\Association\HasMany $Kiosk13PaymentDetails
 * @property \Cake\ORM\Association\HasMany $Kiosk13ProductSales
 * @property \Cake\ORM\Association\HasMany $Kiosk14PaymentDetails
 * @property \Cake\ORM\Association\HasMany $Kiosk14ProductSales
 * @property \Cake\ORM\Association\HasMany $Kiosk15PaymentDetails
 * @property \Cake\ORM\Association\HasMany $Kiosk15ProductSales
 * @property \Cake\ORM\Association\HasMany $Kiosk16PaymentDetails
 * @property \Cake\ORM\Association\HasMany $Kiosk16ProductSales
 * @property \Cake\ORM\Association\HasMany $Kiosk17PaymentDetails
 * @property \Cake\ORM\Association\HasMany $Kiosk17ProductSales
 * @property \Cake\ORM\Association\HasMany $Kiosk18PaymentDetails
 * @property \Cake\ORM\Association\HasMany $Kiosk18ProductSales
 * @property \Cake\ORM\Association\HasMany $Kiosk19PaymentDetails
 * @property \Cake\ORM\Association\HasMany $Kiosk19ProductSales
 * @property \Cake\ORM\Association\HasMany $Kiosk1PaymentDetails
 * @property \Cake\ORM\Association\HasMany $Kiosk1ProductSales
 * @property \Cake\ORM\Association\HasMany $Kiosk20PaymentDetails
 * @property \Cake\ORM\Association\HasMany $Kiosk20ProductSales
 * @property \Cake\ORM\Association\HasMany $Kiosk21PaymentDetails
 * @property \Cake\ORM\Association\HasMany $Kiosk21ProductSales
 * @property \Cake\ORM\Association\HasMany $Kiosk22PaymentDetails
 * @property \Cake\ORM\Association\HasMany $Kiosk22ProductSales
 * @property \Cake\ORM\Association\HasMany $Kiosk2PaymentDetails
 * @property \Cake\ORM\Association\HasMany $Kiosk2ProductSales
 * @property \Cake\ORM\Association\HasMany $Kiosk3PaymentDetails
 * @property \Cake\ORM\Association\HasMany $Kiosk3ProductSales
 * @property \Cake\ORM\Association\HasMany $Kiosk4PaymentDetails
 * @property \Cake\ORM\Association\HasMany $Kiosk4ProductSales
 * @property \Cake\ORM\Association\HasMany $Kiosk5PaymentDetails
 * @property \Cake\ORM\Association\HasMany $Kiosk5ProductSales
 * @property \Cake\ORM\Association\HasMany $Kiosk7PaymentDetails
 * @property \Cake\ORM\Association\HasMany $Kiosk7ProductSales
 * @property \Cake\ORM\Association\HasMany $Kiosk8PaymentDetails
 * @property \Cake\ORM\Association\HasMany $Kiosk8ProductSales
 * @property \Cake\ORM\Association\HasMany $KioskProductSales
 * @property \Cake\ORM\Association\HasMany $KioskProductSales
 * @property \Cake\ORM\Association\HasMany $PaymentDetails
 * @property \Cake\ORM\Association\HasMany $ProductPayments
 * @property \Cake\ORM\Association\HasMany $TKioskProductSales
 * @property \Cake\ORM\Association\HasMany $TPaymentDetails
 * @property \Cake\ORM\Association\HasMany $TempProductDetails
 * @property \Cake\ORM\Association\HasMany $TempProductOrders
 *
 * @method \App\Model\Entity\ProductReceipt get($primaryKey, $options = [])
 * @method \App\Model\Entity\ProductReceipt newEntity($data = null, array $options = [])
 * @method \App\Model\Entity\ProductReceipt[] newEntities(array $data, array $options = [])
 * @method \App\Model\Entity\ProductReceipt|bool save(\Cake\Datasource\EntityInterface $entity, $options = [])
 * @method \App\Model\Entity\ProductReceipt patchEntity(\Cake\Datasource\EntityInterface $entity, array $data, array $options = [])
 * @method \App\Model\Entity\ProductReceipt[] patchEntities($entities, array $data, array $options = [])
 * @method \App\Model\Entity\ProductReceipt findOrCreate($search, callable $callback = null, $options = [])
 *
 * @mixin \Cake\ORM\Behavior\TimestampBehavior
 */
class ProductReceiptsTable extends Table
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

        $this->table('product_receipts');
        $this->displayField('id');
        $this->primaryKey('id');

        $this->addBehavior('Timestamp');

        $this->belongsTo('Customers', [
            'foreignKey' => 'customer_id',
            'joinType' => 'INNER'
        ]);
        $this->hasMany('Kiosk10000PaymentDetails', [
            'foreignKey' => 'product_receipt_id'
        ]);
        $this->hasMany('Kiosk10000ProductSales', [
            'foreignKey' => 'product_receipt_id'
        ]);
        $this->hasMany('Kiosk10PaymentDetails', [
            'foreignKey' => 'product_receipt_id'
        ]);
        $this->hasMany('Kiosk10ProductSales', [
            'foreignKey' => 'product_receipt_id'
        ]);
        $this->hasMany('Kiosk11PaymentDetails', [
            'foreignKey' => 'product_receipt_id'
        ]);
        $this->hasMany('Kiosk11ProductSales', [
            'foreignKey' => 'product_receipt_id'
        ]);
        $this->hasMany('Kiosk12PaymentDetails', [
            'foreignKey' => 'product_receipt_id'
        ]);
        $this->hasMany('Kiosk12ProductSales', [
            'foreignKey' => 'product_receipt_id'
        ]);
        $this->hasMany('Kiosk13PaymentDetails', [
            'foreignKey' => 'product_receipt_id'
        ]);
        $this->hasMany('Kiosk13ProductSales', [
            'foreignKey' => 'product_receipt_id'
        ]);
        $this->hasMany('Kiosk14PaymentDetails', [
            'foreignKey' => 'product_receipt_id'
        ]);
        $this->hasMany('Kiosk14ProductSales', [
            'foreignKey' => 'product_receipt_id'
        ]);
        $this->hasMany('Kiosk15PaymentDetails', [
            'foreignKey' => 'product_receipt_id'
        ]);
        $this->hasMany('Kiosk15ProductSales', [
            'foreignKey' => 'product_receipt_id'
        ]);
        $this->hasMany('Kiosk16PaymentDetails', [
            'foreignKey' => 'product_receipt_id'
        ]);
        $this->hasMany('Kiosk16ProductSales', [
            'foreignKey' => 'product_receipt_id'
        ]);
        $this->hasMany('Kiosk17PaymentDetails', [
            'foreignKey' => 'product_receipt_id'
        ]);
        $this->hasMany('Kiosk17ProductSales', [
            'foreignKey' => 'product_receipt_id'
        ]);
        $this->hasMany('Kiosk18PaymentDetails', [
            'foreignKey' => 'product_receipt_id'
        ]);
        $this->hasMany('Kiosk18ProductSales', [
            'foreignKey' => 'product_receipt_id'
        ]);
        $this->hasMany('Kiosk19PaymentDetails', [
            'foreignKey' => 'product_receipt_id'
        ]);
        $this->hasMany('Kiosk19ProductSales', [
            'foreignKey' => 'product_receipt_id'
        ]);
        $this->hasMany('Kiosk1PaymentDetails', [
            'foreignKey' => 'product_receipt_id'
        ]);
        $this->hasMany('Kiosk1ProductSales', [
            'foreignKey' => 'product_receipt_id'
        ]);
        $this->hasMany('Kiosk20PaymentDetails', [
            'foreignKey' => 'product_receipt_id'
        ]);
        $this->hasMany('Kiosk20ProductSales', [
            'foreignKey' => 'product_receipt_id'
        ]);
        $this->hasMany('Kiosk21PaymentDetails', [
            'foreignKey' => 'product_receipt_id'
        ]);
        $this->hasMany('Kiosk21ProductSales', [
            'foreignKey' => 'product_receipt_id'
        ]);
        $this->hasMany('Kiosk22PaymentDetails', [
            'foreignKey' => 'product_receipt_id'
        ]);
        $this->hasMany('Kiosk22ProductSales', [
            'foreignKey' => 'product_receipt_id'
        ]);
        $this->hasMany('Kiosk2PaymentDetails', [
            'foreignKey' => 'product_receipt_id'
        ]);
        $this->hasMany('Kiosk2ProductSales', [
            'foreignKey' => 'product_receipt_id'
        ]);
        $this->hasMany('Kiosk3PaymentDetails', [
            'foreignKey' => 'product_receipt_id'
        ]);
        $this->hasMany('Kiosk3ProductSales', [
            'foreignKey' => 'product_receipt_id'
        ]);
        $this->hasMany('Kiosk4PaymentDetails', [
            'foreignKey' => 'product_receipt_id'
        ]);
        $this->hasMany('Kiosk4ProductSales', [
            'foreignKey' => 'product_receipt_id'
        ]);
        $this->hasMany('Kiosk5PaymentDetails', [
            'foreignKey' => 'product_receipt_id'
        ]);
        $this->hasMany('Kiosk5ProductSales', [
            'foreignKey' => 'product_receipt_id'
        ]);
        $this->hasMany('Kiosk7PaymentDetails', [
            'foreignKey' => 'product_receipt_id'
        ]);
        $this->hasMany('Kiosk7ProductSales', [
            'foreignKey' => 'product_receipt_id'
        ]);
        $this->hasMany('Kiosk8PaymentDetails', [
            'foreignKey' => 'product_receipt_id'
        ]);
        $this->hasMany('Kiosk8ProductSales', [
            'foreignKey' => 'product_receipt_id'
        ]);
        $this->hasMany('KioskProductSales', [
            'foreignKey' => 'product_receipt_id'
        ]);
        $this->hasMany('KioskProductSales', [
            'foreignKey' => 'product_receipt_id'
        ]);
        $this->hasMany('PaymentDetails', [
            'foreignKey' => 'product_receipt_id'
        ]);
        $this->hasMany('ProductPayments', [
            'foreignKey' => 'product_receipt_id'
        ]);
        $this->hasMany('TKioskProductSales', [
            'foreignKey' => 'product_receipt_id'
        ]);
        $this->hasMany('TPaymentDetails', [
            'foreignKey' => 'product_receipt_id'
        ]);
        $this->hasMany('TempProductDetails', [
            'foreignKey' => 'product_receipt_id'
        ]);
        $this->hasMany('TempProductOrders', [
            'foreignKey' => 'product_receipt_id'
        ]);
    }

    //public function buildRules(RulesChecker $rules)
    //{
    //    $rules->add($rules->isUnique(['email']));
    //    $rules->add($rules->existsIn(['customer_id'], 'Customers'));
    //
    //    return $rules;
    //}
}
