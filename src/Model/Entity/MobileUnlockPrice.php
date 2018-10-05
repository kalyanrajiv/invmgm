<?php
namespace App\Model\Entity;

use Cake\ORM\Entity;

/**
 * MobileUnlockPrice Entity
 *
 * @property int $id
 * @property int $brand_id
 * @property int $mobile_model_id
 * @property int $network_id
 * @property float $unlocking_cost
 * @property float $unlocking_price
 * @property int $unlocking_days
 * @property int $unlocking_minutes
 * @property int $status
 * @property \Cake\I18n\Time $status_change_date
 * @property \Cake\I18n\Time $created
 * @property \Cake\I18n\Time $modified
 *
 * @property \App\Model\Entity\Brand $brand
 * @property \App\Model\Entity\MobileModel $mobile_model
 * @property \App\Model\Entity\Network $network
 */
class MobileUnlockPrice extends Entity
{

    /**
     * Fields that can be mass assigned using newEntity() or patchEntity().
     *
     * Note that when '*' is set to true, this allows all unspecified fields to
     * be mass assigned. For security purposes, it is advised to set '*' to false
     * (or remove it), and explicitly make individual fields accessible as needed.
     *
     * @var array
     */
    protected $_accessible = [
        '*' => true,
        'id' => false
    ];
}
