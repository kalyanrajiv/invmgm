<?php
namespace App\Model\Entity;

use Cake\ORM\Entity;

/**
 * OrderDispute Entity
 *
 * @property int $id
 * @property int $kiosk_id
 * @property int $kiosk_order_id
 * @property int $product_id
 * @property int $receiving_status
 * @property int $disputed_by
 * @property int $quantity
 * @property string $kiosk_user_remarks
 * @property string $admin_remarks
 * @property int $approval_status
 * @property int $approval_by
 * @property \Cake\I18n\Time $admin_acted
 * @property \Cake\I18n\Time $created
 * @property \Cake\I18n\Time $modified
 *
 * @property \App\Model\Entity\Kiosk $kiosk
 * @property \App\Model\Entity\KioskOrder $kiosk_order
 * @property \App\Model\Entity\Product $product
 */
class OrderDispute extends Entity
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
