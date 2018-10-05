<?php
namespace App\Model\Table;

use Cake\ORM\Query;
use Cake\ORM\RulesChecker;
use Cake\ORM\Table;
use Cake\Validation\Validator;

/**
 * SaleLogs Model
 *
 * @property \Cake\ORM\Association\BelongsTo $Receipts
 * @property \Cake\ORM\Association\BelongsTo $Kiosks
 * @property \Cake\ORM\Association\BelongsTo $Users
 * @property \Cake\ORM\Association\BelongsTo $Sales
 *
 * @method \App\Model\Entity\SaleLog get($primaryKey, $options = [])
 * @method \App\Model\Entity\SaleLog newEntity($data = null, array $options = [])
 * @method \App\Model\Entity\SaleLog[] newEntities(array $data, array $options = [])
 * @method \App\Model\Entity\SaleLog|bool save(\Cake\Datasource\EntityInterface $entity, $options = [])
 * @method \App\Model\Entity\SaleLog patchEntity(\Cake\Datasource\EntityInterface $entity, array $data, array $options = [])
 * @method \App\Model\Entity\SaleLog[] patchEntities($entities, array $data, array $options = [])
 * @method \App\Model\Entity\SaleLog findOrCreate($search, callable $callback = null, $options = [])
 *
 * @mixin \Cake\ORM\Behavior\TimestampBehavior
 */
class SaleLogsTable extends Table
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

        $this->table('sale_logs');
        $this->displayField('id');
        $this->primaryKey('id');

        $this->addBehavior('Timestamp');

        //$this->belongsTo('Receipts', [
        //    'foreignKey' => 'receipt_id',
        //    'joinType' => 'INNER'
        //]);
        //$this->belongsTo('Kiosks', [
        //    'foreignKey' => 'kiosk_id',
        //    'joinType' => 'INNER'
        //]);
        //$this->belongsTo('Users', [
        //    'foreignKey' => 'user_id',
        //    'joinType' => 'INNER'
        //]);
        //$this->belongsTo('Sales', [
        //    'foreignKey' => 'sale_id',
        //    'joinType' => 'INNER'
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
            ->integer('id')
            ->allowEmpty('id', 'create');

        $validator
            ->numeric('orignal_amount')
            ->requirePresence('orignal_amount', 'create')
            ->notEmpty('orignal_amount');

        $validator
            ->numeric('modified_amount')
            ->requirePresence('modified_amount', 'create')
            ->notEmpty('modified_amount');

        $validator
            ->numeric('discount')
            ->allowEmpty('discount');

        $validator
            ->integer('discount_status')
            ->allowEmpty('discount_status');

        $validator
            ->integer('quantity')
            ->allowEmpty('quantity');

        $validator
            ->requirePresence('product_code', 'create')
            ->notEmpty('product_code');

        $validator
            ->requirePresence('product_title', 'create')
            ->notEmpty('product_title');

        $validator
            ->dateTime('sale_date')
            ->requirePresence('sale_date', 'create')
            ->notEmpty('sale_date');

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
        //$rules->add($rules->existsIn(['receipt_id'], 'Receipts'));
        //$rules->add($rules->existsIn(['kiosk_id'], 'Kiosks'));
        //$rules->add($rules->existsIn(['user_id'], 'Users'));
        //$rules->add($rules->existsIn(['sale_id'], 'Sales'));

        return $rules;
    }
}
