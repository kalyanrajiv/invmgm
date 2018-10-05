<?php
namespace App\Model\Table;

use Cake\ORM\Query;
use Cake\ORM\RulesChecker;
use Cake\ORM\Table;
use Cake\Validation\Validator;

/**
 * MobileReSalePayments Model
 *
 * @property \Cake\ORM\Association\BelongsTo $Kiosks
 * @property \Cake\ORM\Association\BelongsTo $Users
 * @property \Cake\ORM\Association\BelongsTo $MobilePurchases
 * @property \Cake\ORM\Association\BelongsTo $MobileReSales
 *
 * @method \App\Model\Entity\MobileReSalePayment get($primaryKey, $options = [])
 * @method \App\Model\Entity\MobileReSalePayment newEntity($data = null, array $options = [])
 * @method \App\Model\Entity\MobileReSalePayment[] newEntities(array $data, array $options = [])
 * @method \App\Model\Entity\MobileReSalePayment|bool save(\Cake\Datasource\EntityInterface $entity, $options = [])
 * @method \App\Model\Entity\MobileReSalePayment patchEntity(\Cake\Datasource\EntityInterface $entity, array $data, array $options = [])
 * @method \App\Model\Entity\MobileReSalePayment[] patchEntities($entities, array $data, array $options = [])
 * @method \App\Model\Entity\MobileReSalePayment findOrCreate($search, callable $callback = null, $options = [])
 *
 * @mixin \Cake\ORM\Behavior\TimestampBehavior
 */
class ProductSaleStatNew extends Table
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

        $this->table('product_sale_stats_new');
        $this->displayField('id');
        $this->primaryKey('id');

        $this->addBehavior('Timestamp');

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
        return $rules;
    }
}
