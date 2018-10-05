<?php
namespace App\Model\Entity;

use Cake\ORM\Entity;

/**
 * MobileUnlock Entity
 *
 * @property int $id
 * @property int $unlock_number
 * @property string $token
 * @property string $unlock_code_instructions
 * @property int $kiosk_id
 * @property int $retail_customer_id
 * @property int $booked_by
 * @property int $delivered_by
 * @property int $brand_id
 * @property int $network_id
 * @property int $mobile_model_id
 * @property string $imei
 * @property \Cake\I18n\Time $received_at
 * @property \Cake\I18n\Time $delivered_at
 * @property string $brief_history
 * @property string $customer_fname
 * @property string $customer_lname
 * @property string $customer_email
 * @property string $customer_contact
 * @property string $customer_address_1
 * @property string $customer_address_2
 * @property string $city
 * @property string $state
 * @property string $country
 * @property string $description
 * @property float $estimated_cost
 * @property float $actual_cost
 * @property float $net_cost
 * @property string $zip
 * @property int $status
 * @property int $internal_unlock
 * @property int $status_refund
 * @property int $status_rebooked
 * @property int $status_freezed
 * @property \Cake\I18n\Time $created
 * @property \Cake\I18n\Time $modified
 *
 * @property \App\Model\Entity\Kiosk $kiosk
 * @property \App\Model\Entity\RetailCustomer $retail_customer
 * @property \App\Model\Entity\Brand $brand
 * @property \App\Model\Entity\Network $network
 * @property \App\Model\Entity\MobileModel $mobile_model
 * @property \App\Model\Entity\CommentMobileUnlock[] $comment_mobile_unlocks
 * @property \App\Model\Entity\MobileUnlockLog[] $mobile_unlock_logs
 * @property \App\Model\Entity\MobileUnlockSale[] $mobile_unlock_sales
 * @property \App\Model\Entity\UnlockPayment[] $unlock_payments
 */
class MobileUnlock extends Entity
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

    /**
     * Fields that are excluded from JSON versions of the entity.
     *
     * @var array
     */
    protected $_hidden = [
        'token'
    ];
}
