<?php
namespace App\Model\Entity;

use Cake\ORM\Entity;

/**
 * MobilePlacedOrder Entity
 *
 * @property int $id
 * @property int $kiosk_id
 * @property int $kiosk_placed_order_id
 * @property int $imei
 * @property int $brand
 * @property int $model
 * @property int $ntework
 * @property int $sold_by
 * @property \Cake\I18n\Time $created
 * @property \Cake\I18n\Time $modified
 *
 * @property \App\Model\Entity\Kiosk $kiosk
 * @property \App\Model\Entity\KioskPlacedOrder $kiosk_placed_order
 */
class MobilePlacedOrder extends Entity
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
