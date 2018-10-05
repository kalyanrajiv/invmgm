<?php
namespace App\Model\Table;

use Cake\ORM\Query;
use Cake\ORM\RulesChecker;
use Cake\ORM\Table;
use Cake\Validation\Validator;
 
class BrandsTable extends Table
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

        $this->table('brands');
        $this->displayField('id');
        $this->primaryKey('id');

        $this->addBehavior('Timestamp');

        //$this->hasMany('CsvProducts', [
        //    'foreignKey' => 'brand_id'
        //]);
        //$this->hasMany('CsvProducts2', [
        //    'foreignKey' => 'brand_id'
        //]);
        //$this->hasMany('Kiosk10000Products', [
        //    'foreignKey' => 'brand_id'
        //]);
        //$this->hasMany('Kiosk10Products', [
        //    'foreignKey' => 'brand_id'
        //]);
        //$this->hasMany('Kiosk1Products', [
        //    'foreignKey' => 'brand_id'
        //]);
        //$this->hasMany('Kiosk2Products', [
        //    'foreignKey' => 'brand_id'
        //]);
        //$this->hasMany('Kiosk3Products', [
        //    'foreignKey' => 'brand_id'
        //]);
        //$this->hasMany('Kiosk4Products', [
        //    'foreignKey' => 'brand_id'
        //]);
        //$this->hasMany('Kiosk5Products', [
        //    'foreignKey' => 'brand_id'
        //]);
        //$this->hasMany('Kiosk6Products', [
        //    'foreignKey' => 'brand_id'
        //]);
        //$this->hasMany('Kiosk7Products', [
        //    'foreignKey' => 'brand_id'
        //]);
        //$this->hasMany('Kiosk8Products', [
        //    'foreignKey' => 'brand_id'
        //]);
        //$this->hasMany('KioskProducts', [
        //    'foreignKey' => 'brand_id'
        //]);
        //$this->hasMany('MobileBlkReSales', [
        //    'foreignKey' => 'brand_id'
        //]);
        //$this->hasMany('MobileModels', [
        //    'foreignKey' => 'brand_id'
        //]);
        //$this->hasMany('MobilePrices', [
        //    'foreignKey' => 'brand_id'
        //]);
        //$this->hasMany('MobilePurchases', [
        //    'foreignKey' => 'brand_id'
        //]);
        //$this->hasMany('MobileReSales', [
        //    'foreignKey' => 'brand_id'
        //]);
        //$this->hasMany('MobileRepairPrices', [
        //    'foreignKey' => 'brand_id'
        //]);
        //$this->hasMany('MobileRepairs', [
        //    'foreignKey' => 'brand_id'
        //]);
        //$this->hasMany('MobileUnlockPrices', [
        //    'foreignKey' => 'brand_id'
        //]);
        //$this->hasMany('MobileUnlocks', [
        //    'foreignKey' => 'brand_id'
        //]);
        $this->hasMany('Products', [
            'foreignKey' => 'brand_id'
        ]);
        //$this->hasMany('Products3mar16', [
        //    'foreignKey' => 'brand_id'
        //]);
        //$this->hasMany('Products8oct', [
        //    'foreignKey' => 'brand_id'
        //]);
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
            ->requirePresence('brand', 'create')
            ->notEmpty('brand')
            ->add('brand', 'unique', ['rule' => 'validateUnique', 'provider' => 'table']);

        //$validator
        //    ->requirePresence('company', 'create')
        //    ->notEmpty('company');

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
        $rules->add($rules->isUnique(['brand']));

        return $rules;
    }
}
