<?php
namespace App\Model\Table;

use Cake\ORM\Query;
use Cake\ORM\RulesChecker;
use Cake\ORM\Table;
use Cake\Validation\Validator;

/**
 * DailyStocks Model
 *
 * @property \Cake\ORM\Association\BelongsTo $Products
 *
 * @method \App\Model\Entity\DailyStock get($primaryKey, $options = [])
 * @method \App\Model\Entity\DailyStock newEntity($data = null, array $options = [])
 * @method \App\Model\Entity\DailyStock[] newEntities(array $data, array $options = [])
 * @method \App\Model\Entity\DailyStock|bool save(\Cake\Datasource\EntityInterface $entity, $options = [])
 * @method \App\Model\Entity\DailyStock patchEntity(\Cake\Datasource\EntityInterface $entity, array $data, array $options = [])
 * @method \App\Model\Entity\DailyStock[] patchEntities($entities, array $data, array $options = [])
 * @method \App\Model\Entity\DailyStock findOrCreate($search, callable $callback = null, $options = [])
 *
 * @mixin \Cake\ORM\Behavior\TimestampBehavior
 */
class DailyStocksTable extends Table
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

        $this->table('daily_stocks');
        $this->displayField('id');
        $this->primaryKey('id');

        $this->addBehavior('Timestamp');

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
            ->allowEmpty('id', 'create');

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
        $rules->add($rules->existsIn(['product_id'], 'Products'));

        return $rules;
    }
}
