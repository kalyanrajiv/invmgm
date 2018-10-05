<?php
namespace App\Model\Table;

use Cake\ORM\Query;
use Cake\ORM\RulesChecker;
use Cake\ORM\Table;
use Cake\Validation\Validator;

/**
 * TempProductDetails Model
 *
 * @property \Cake\ORM\Association\BelongsTo $Kiosks
 * @property \Cake\ORM\Association\BelongsTo $Users
 * @property \Cake\ORM\Association\BelongsTo $TempProductOrders
 * @property \Cake\ORM\Association\BelongsTo $ProductReceipts
 * @property \Cake\ORM\Association\BelongsTo $Products
 *
 * @method \App\Model\Entity\TempProductDetail get($primaryKey, $options = [])
 * @method \App\Model\Entity\TempProductDetail newEntity($data = null, array $options = [])
 * @method \App\Model\Entity\TempProductDetail[] newEntities(array $data, array $options = [])
 * @method \App\Model\Entity\TempProductDetail|bool save(\Cake\Datasource\EntityInterface $entity, $options = [])
 * @method \App\Model\Entity\TempProductDetail patchEntity(\Cake\Datasource\EntityInterface $entity, array $data, array $options = [])
 * @method \App\Model\Entity\TempProductDetail[] patchEntities($entities, array $data, array $options = [])
 * @method \App\Model\Entity\TempProductDetail findOrCreate($search, callable $callback = null, $options = [])
 *
 * @mixin \Cake\ORM\Behavior\TimestampBehavior
 */
class TempProductDetailsTable extends Table
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

        $this->table('temp_product_details');
        $this->displayField('id');
        $this->primaryKey('id');

        $this->addBehavior('Timestamp');

        $this->belongsTo('Kiosks', [
            'foreignKey' => 'kiosk_id',
            'joinType' => 'INNER'
        ]);
        $this->belongsTo('Users', [
            'foreignKey' => 'user_id',
            'joinType' => 'INNER'
        ]);
        $this->belongsTo('TempProductOrders', [
            'foreignKey' => 'temp_product_order_id',
            'joinType' => 'INNER'
        ]);
        $this->belongsTo('ProductReceipts', [
            'foreignKey' => 'product_receipt_id',
            'joinType' => 'INNER'
        ]);
        $this->belongsTo('Products', [
            'foreignKey' => 'product_id',
            'joinType' => 'INNER'
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
            ->requirePresence('product', 'create')
            ->notEmpty('product');

        $validator
            ->integer('quantity')
            ->requirePresence('quantity', 'create')
            ->notEmpty('quantity');

        $validator
            ->numeric('amount')
            ->requirePresence('amount', 'create')
            ->notEmpty('amount');

        $validator
            ->requirePresence('remarks', 'create')
            ->notEmpty('remarks');

        $validator
            ->integer('discount')
            ->requirePresence('discount', 'create')
            ->notEmpty('discount');

        $validator
            ->integer('discount_status')
            ->requirePresence('discount_status', 'create')
            ->notEmpty('discount_status');

        $validator
            ->integer('status')
            ->allowEmpty('status');

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
        $rules->add($rules->existsIn(['kiosk_id'], 'Kiosks'));
        $rules->add($rules->existsIn(['user_id'], 'Users'));
        $rules->add($rules->existsIn(['temp_product_order_id'], 'TempProductOrders'));
        $rules->add($rules->existsIn(['product_receipt_id'], 'ProductReceipts'));
        $rules->add($rules->existsIn(['product_id'], 'Products'));

        return $rules;
    }
}
