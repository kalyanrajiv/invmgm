<?php
namespace App\Model\Table;

use Cake\ORM\Query;
use Cake\ORM\RulesChecker;
use Cake\ORM\Table;
use Cake\Validation\Validator;
 
class KioskOrdersTable extends Table
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

        $this->table('kiosk_orders');
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
        $this->belongsTo('KioskPlacedOrders', [
            'foreignKey' => 'kiosk_placed_order_id'
        ]);
        $this->hasMany('KioskTransferredStock1', [
            'foreignKey' => 'kiosk_order_id'
        ]);
        $this->hasMany('KioskTransferredStock10', [
            'foreignKey' => 'kiosk_order_id'
        ]);
        $this->hasMany('KioskTransferredStock11', [
            'foreignKey' => 'kiosk_order_id'
        ]);
        $this->hasMany('KioskTransferredStock13', [
            'foreignKey' => 'kiosk_order_id'
        ]);
        $this->hasMany('KioskTransferredStock17', [
            'foreignKey' => 'kiosk_order_id'
        ]);
        $this->hasMany('KioskTransferredStock18', [
            'foreignKey' => 'kiosk_order_id'
        ]);
        $this->hasMany('KioskTransferredStock2', [
            'foreignKey' => 'kiosk_order_id'
        ]);
        $this->hasMany('KioskTransferredStock20', [
            'foreignKey' => 'kiosk_order_id'
        ]);
        $this->hasMany('KioskTransferredStock3', [
            'foreignKey' => 'kiosk_order_id'
        ]);
        $this->hasMany('KioskTransferredStock4', [
            'foreignKey' => 'kiosk_order_id'
        ]);
        $this->hasMany('KioskTransferredStock5', [
            'foreignKey' => 'kiosk_order_id'
        ]);
        $this->hasMany('KioskTransferredStock7', [
            'foreignKey' => 'kiosk_order_id'
        ]);
        $this->hasMany('KioskTransferredStock8', [
            'foreignKey' => 'kiosk_order_id'
        ]);
        $this->hasMany('OrderDisputes', [
            'foreignKey' => 'kiosk_order_id'
        ]);
        $this->hasMany('RevertStocks', [
            'foreignKey' => 'kiosk_order_id'
        ]);
        $this->hasMany('StockTransfer', [
            'foreignKey' => 'kiosk_order_id'
        ]);
        $this->hasMany('StockTransferByKiosk', [
            'foreignKey' => 'kiosk_order_id'
        ]);
        $this->hasMany('TransientStock', [
            'foreignKey' => 'kiosk_order_id'
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
            ->integer('status')
            ->requirePresence('status', 'create')
            ->notEmpty('status');

        //$validator
        //    ->integer('is_on_demand')
        //    ->requirePresence('is_on_demand', 'create')
        //    ->notEmpty('is_on_demand');

        $validator
            ->dateTime('dispatched_on')
            ->requirePresence('dispatched_on', 'create')
            ->notEmpty('dispatched_on');

        $validator
            ->dateTime('received_on')
            ->requirePresence('received_on', 'create')
            ->notEmpty('received_on');

        //$validator
        //    ->integer('received_by')
        //    ->requirePresence('received_by', 'create')
        //    ->notEmpty('received_by');

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
        $rules->add($rules->existsIn(['id'], 'KioskPlacedOrders'));

        return $rules;
    }
    public function is_transient_order($id = 0){
        //status = 1 for transient, status = 2 for confirmed orders 
        $data_query = $this->find('all',array(
                                        'fields' => array('id'),
                                        'conditions' => array('id' => $id,'status' => 1),
                                      
                                        )
                        );
        $data_query = $data_query->first();
           if(!empty($data_query)){
            $data  = $data_query->toArray();
        }
        return $data? true:false;
    }
    
    public function belongs_to_kiosk($order_id,$kiosk_id){
        $data_query = $this->find('all',array(
                                    'fields' => array('id'),
                                    'conditions' => array('id' => $order_id,'kiosk_id' => $kiosk_id),
                                   
                                    )
                    );
         $data_query = $data_query->first();
           if(!empty($data_query)){
            $data  = $data_query->toArray();
        }
        return $data? true:false;
    }
}
