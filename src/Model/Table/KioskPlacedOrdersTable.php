<?php
namespace App\Model\Table;

use Cake\ORM\Query;
use Cake\ORM\RulesChecker;
use Cake\ORM\Table;
use Cake\Validation\Validator;

/**
 * KioskPlacedOrders Model
 *
 * @property \Cake\ORM\Association\BelongsTo $Kiosks
 * @property \Cake\ORM\Association\BelongsTo $Users
 * @property \Cake\ORM\Association\HasMany $KioskCancelledOrderProducts
 * @property \Cake\ORM\Association\HasMany $KioskOrderProducts
 * @property \Cake\ORM\Association\HasMany $KioskOrders
 * @property \Cake\ORM\Association\HasMany $KioskTransferredStock10
 * @property \Cake\ORM\Association\HasMany $KioskTransferredStock11
 * @property \Cake\ORM\Association\HasMany $KioskTransferredStock17
 * @property \Cake\ORM\Association\HasMany $KioskTransferredStock7
 * @property \Cake\ORM\Association\HasMany $MobilePlacedOrders
 * @property \Cake\ORM\Association\HasMany $OnDemandProducts
 * @property \Cake\ORM\Association\HasMany $StockTransfer
 *
 * @method \App\Model\Entity\KioskPlacedOrder get($primaryKey, $options = [])
 * @method \App\Model\Entity\KioskPlacedOrder newEntity($data = null, array $options = [])
 * @method \App\Model\Entity\KioskPlacedOrder[] newEntities(array $data, array $options = [])
 * @method \App\Model\Entity\KioskPlacedOrder|bool save(\Cake\Datasource\EntityInterface $entity, $options = [])
 * @method \App\Model\Entity\KioskPlacedOrder patchEntity(\Cake\Datasource\EntityInterface $entity, array $data, array $options = [])
 * @method \App\Model\Entity\KioskPlacedOrder[] patchEntities($entities, array $data, array $options = [])
 * @method \App\Model\Entity\KioskPlacedOrder findOrCreate($search, callable $callback = null, $options = [])
 *
 * @mixin \Cake\ORM\Behavior\TimestampBehavior
 */
class KioskPlacedOrdersTable extends Table
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

        $this->table('kiosk_placed_orders');
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
        
        $this->hasMany('KioskOrderProducts', [
            'foreignKey' => 'kiosk_placed_order_id'
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
        //$validator
        //    ->integer('id')
        //    ->allowEmpty('id', 'create');
        //
        //$validator
        //    ->integer('weekly_order')
        //    ->allowEmpty('weekly_order');
        //
        //$validator
        //    ->integer('status')
        //    ->requirePresence('status', 'create')
        //    ->notEmpty('status');

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

        return $rules;
    }
}
