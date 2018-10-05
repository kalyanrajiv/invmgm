<?php
namespace App\Model\Entity;

use Cake\ORM\Entity;

/**
 * DailyTarget Entity
 *
 * @property int $id
 * @property int $kiosk_id
 * @property int $user_id
 * @property float $target
 * @property float $product_sale
 * @property float $mobile_sale
 * @property float $mobile_repair_sale
 * @property float $mobile_unlock_sale
 * @property float $product_refund
 * @property float $mobile_refund
 * @property float $mobile_repair_refund
 * @property float $mobile_unlock_refund
 * @property float $total_sale
 * @property float $total_refund
 * @property \Cake\I18n\Time $target_date
 * @property int $status
 * @property \Cake\I18n\Time $created
 * @property \Cake\I18n\Time $modified
 *
 * @property \App\Model\Entity\Kiosk $kiosk
 * @property \App\Model\Entity\User $user
 */
class DailyTarget extends Entity
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
