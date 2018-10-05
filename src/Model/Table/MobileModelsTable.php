<?php
namespace App\Model\Table;

use Cake\ORM\Query;
use Cake\ORM\RulesChecker;
use Cake\ORM\Table;
use Cake\Validation\Validator;

/**
 * MobileModels Model
 *
 * @property \Cake\ORM\Association\BelongsTo $Brands
 * @property \Cake\ORM\Association\HasMany $MobileBlkReSales
 * @property \Cake\ORM\Association\HasMany $MobilePrices
 * @property \Cake\ORM\Association\HasMany $MobilePurchases
 * @property \Cake\ORM\Association\HasMany $MobileReSales
 * @property \Cake\ORM\Association\HasMany $MobileRepairPrices
 * @property \Cake\ORM\Association\HasMany $MobileRepairs
 * @property \Cake\ORM\Association\HasMany $MobileUnlockPrices
 * @property \Cake\ORM\Association\HasMany $MobileUnlocks
 *
 * @method \App\Model\Entity\MobileModel get($primaryKey, $options = [])
 * @method \App\Model\Entity\MobileModel newEntity($data = null, array $options = [])
 * @method \App\Model\Entity\MobileModel[] newEntities(array $data, array $options = [])
 * @method \App\Model\Entity\MobileModel|bool save(\Cake\Datasource\EntityInterface $entity, $options = [])
 * @method \App\Model\Entity\MobileModel patchEntity(\Cake\Datasource\EntityInterface $entity, array $data, array $options = [])
 * @method \App\Model\Entity\MobileModel[] patchEntities($entities, array $data, array $options = [])
 * @method \App\Model\Entity\MobileModel findOrCreate($search, callable $callback = null, $options = [])
 *
 * @mixin \Cake\ORM\Behavior\TimestampBehavior
 */
class MobileModelsTable extends Table
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

        $this->table('mobile_models');
        $this->displayField('id');
        $this->primaryKey('id');

        $this->addBehavior('Timestamp');

        $this->belongsTo('Brands', [
            'foreignKey' => 'brand_id',
            'joinType' => 'INNER'
        ]);
        $this->hasMany('MobileBlkReSales', [
            'foreignKey' => 'mobile_model_id'
        ]);
        $this->hasMany('MobilePrices', [
            'foreignKey' => 'mobile_model_id'
        ]);
        $this->hasMany('MobilePurchases', [
            'foreignKey' => 'mobile_model_id'
        ]);
        $this->hasMany('MobileReSales', [
            'foreignKey' => 'mobile_model_id'
        ]);
        $this->hasMany('MobileRepairPrices', [
            'foreignKey' => 'mobile_model_id'
        ]);
        $this->hasMany('MobileRepairs', [
            'foreignKey' => 'mobile_model_id'
        ]);
        $this->hasMany('MobileUnlockPrices', [
            'foreignKey' => 'mobile_model_id'
        ]);
        $this->hasMany('MobileUnlocks', [
            'foreignKey' => 'mobile_model_id'
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
            ->requirePresence('model', 'create')
            ->notEmpty('model');

        //$validator
        //    ->requirePresence('brief_description', 'create')
        //    ->notEmpty('brief_description');

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
        $rules->add($rules->existsIn(['brand_id'], 'Brands'));

        return $rules;
    }
}
