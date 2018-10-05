<?php
namespace App\Model\Table;

use Cake\ORM\Query;
use Cake\ORM\RulesChecker;
use Cake\ORM\Table;
use Cake\Validation\Validator;

/**
 * Networks Model
 *
 * @property \Cake\ORM\Association\HasMany $MobileBlkReSales
 * @property \Cake\ORM\Association\HasMany $MobilePrices
 * @property \Cake\ORM\Association\HasMany $MobilePurchases
 * @property \Cake\ORM\Association\HasMany $MobileReSales
 * @property \Cake\ORM\Association\HasMany $MobileTransferLogs
 * @property \Cake\ORM\Association\HasMany $MobileUnlockPrices
 * @property \Cake\ORM\Association\HasMany $MobileUnlocks
 *
 * @method \App\Model\Entity\Network get($primaryKey, $options = [])
 * @method \App\Model\Entity\Network newEntity($data = null, array $options = [])
 * @method \App\Model\Entity\Network[] newEntities(array $data, array $options = [])
 * @method \App\Model\Entity\Network|bool save(\Cake\Datasource\EntityInterface $entity, $options = [])
 * @method \App\Model\Entity\Network patchEntity(\Cake\Datasource\EntityInterface $entity, array $data, array $options = [])
 * @method \App\Model\Entity\Network[] patchEntities($entities, array $data, array $options = [])
 * @method \App\Model\Entity\Network findOrCreate($search, callable $callback = null, $options = [])
 *
 * @mixin \Cake\ORM\Behavior\TimestampBehavior
 */
class NetworksTable extends Table
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

        $this->table('networks');
        $this->displayField('name');
        $this->primaryKey('id');

        $this->addBehavior('Timestamp');

        $this->hasMany('MobileBlkReSales', [
            'foreignKey' => 'network_id'
        ]);
        $this->hasMany('MobilePrices', [
            'foreignKey' => 'network_id'
        ]);
        $this->hasMany('MobilePurchases', [
            'foreignKey' => 'network_id'
        ]);
        $this->hasMany('MobileReSales', [
            'foreignKey' => 'network_id'
        ]);
        $this->hasMany('MobileTransferLogs', [
            'foreignKey' => 'network_id'
        ]);
        $this->hasMany('MobileUnlockPrices', [
            'foreignKey' => 'network_id'
        ]);
        $this->hasMany('MobileUnlocks', [
            'foreignKey' => 'network_id'
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
            ->requirePresence('name', 'create')
            ->notEmpty('name');

        $validator
            ->requirePresence('title', 'create')
            ->notEmpty('title');

        $validator
            ->integer('status')
            ->requirePresence('status', 'create')
            ->notEmpty('status');

        return $validator;
    }
}
