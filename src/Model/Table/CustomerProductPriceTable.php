<?php
namespace App\Model\Table;

use Cake\ORM\Query;
use Cake\ORM\RulesChecker;
use Cake\ORM\Table;
use Cake\Validation\Validator;

/**
 * CustomerProductPrice Model
 *
 * @property \App\Model\Table\CustomersTable|\Cake\ORM\Association\BelongsTo $Customers
 * @property \App\Model\Table\ProductsTable|\Cake\ORM\Association\BelongsTo $Products
 *
 * @method \App\Model\Entity\CustomerProductPrice get($primaryKey, $options = [])
 * @method \App\Model\Entity\CustomerProductPrice newEntity($data = null, array $options = [])
 * @method \App\Model\Entity\CustomerProductPrice[] newEntities(array $data, array $options = [])
 * @method \App\Model\Entity\CustomerProductPrice|bool save(\Cake\Datasource\EntityInterface $entity, $options = [])
 * @method \App\Model\Entity\CustomerProductPrice patchEntity(\Cake\Datasource\EntityInterface $entity, array $data, array $options = [])
 * @method \App\Model\Entity\CustomerProductPrice[] patchEntities($entities, array $data, array $options = [])
 * @method \App\Model\Entity\CustomerProductPrice findOrCreate($search, callable $callback = null, $options = [])
 *
 * @mixin \Cake\ORM\Behavior\TimestampBehavior
 */
class CustomerProductPriceTable extends Table
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

        $this->setTable('customer_product_price');
        $this->setDisplayField('id');
        $this->setPrimaryKey('id');

        $this->addBehavior('Timestamp');

        $this->belongsTo('Customers', [
            'foreignKey' => 'customer_id',
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
            ->numeric('sale_price')
            ->requirePresence('sale_price', 'create')
            ->notEmpty('sale_price');

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
        $rules->add($rules->existsIn(['customer_id'], 'Customers'));
        $rules->add($rules->existsIn(['product_id'], 'Products'));

        return $rules;
    }
}
