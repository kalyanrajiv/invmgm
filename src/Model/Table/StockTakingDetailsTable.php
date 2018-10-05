<?php
namespace App\Model\Table;

use Cake\ORM\Query;
use Cake\ORM\RulesChecker;
use Cake\ORM\Table;
use Cake\Validation\Validator;

/**
 * StockTakingDetails Model
 *
 * @property \Cake\ORM\Association\BelongsTo $Users
 * @property \Cake\ORM\Association\BelongsTo $Kiosks
 * @property \Cake\ORM\Association\BelongsTo $StockTakingReferences
 * @property \Cake\ORM\Association\BelongsTo $Products
 *
 * @method \App\Model\Entity\StockTakingDetail get($primaryKey, $options = [])
 * @method \App\Model\Entity\StockTakingDetail newEntity($data = null, array $options = [])
 * @method \App\Model\Entity\StockTakingDetail[] newEntities(array $data, array $options = [])
 * @method \App\Model\Entity\StockTakingDetail|bool save(\Cake\Datasource\EntityInterface $entity, $options = [])
 * @method \App\Model\Entity\StockTakingDetail patchEntity(\Cake\Datasource\EntityInterface $entity, array $data, array $options = [])
 * @method \App\Model\Entity\StockTakingDetail[] patchEntities($entities, array $data, array $options = [])
 * @method \App\Model\Entity\StockTakingDetail findOrCreate($search, callable $callback = null, $options = [])
 *
 * @mixin \Cake\ORM\Behavior\TimestampBehavior
 */
class StockTakingDetailsTable extends Table
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

        $this->table('stock_taking_details');
        $this->displayField('id');
        $this->primaryKey('id');

        $this->addBehavior('Timestamp');

        $this->belongsTo('Users', [
            'foreignKey' => 'user_id',
            'joinType' => 'INNER'
        ]);
        $this->belongsTo('Kiosks', [
            'foreignKey' => 'kiosk_id',
            'joinType' => 'INNER'
        ]);
        $this->belongsTo('StockTakingReferences', [
            'foreignKey' => 'stock_taking_reference_id',
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
            ->requirePresence('product_code', 'create')
            ->notEmpty('product_code');

        $validator
            ->numeric('cost_price')
            ->requirePresence('cost_price', 'create')
            ->notEmpty('cost_price');

        $validator
            ->numeric('selling_price')
            ->requirePresence('selling_price', 'create')
            ->notEmpty('selling_price');

        $validator
            ->integer('quantity')
            ->requirePresence('quantity', 'create')
            ->notEmpty('quantity');

        $validator
            ->integer('difference')
            ->requirePresence('difference', 'create')
            ->notEmpty('difference');

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
        $rules->add($rules->existsIn(['user_id'], 'Users'));
        $rules->add($rules->existsIn(['kiosk_id'], 'Kiosks'));
        $rules->add($rules->existsIn(['stock_taking_reference_id'], 'StockTakingReferences'));
        $rules->add($rules->existsIn(['product_id'], 'Products'));

        return $rules;
    }
}
