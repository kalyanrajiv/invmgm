<?php
namespace App\Model\Entity;

use Cake\ORM\Entity;

/**
 * MobileReSale Entity
 *
 * @property int $id
 * @property int $sale_id
 * @property int $mobile_purchase_id
 * @property int $kiosk_id
 * @property int $user_id
 * @property int $brand_id
 * @property int $mobile_model_id
 * @property int $network_id
 * @property string $color
 * @property string $imei
 * @property string $brief_history
 * @property int $retail_customer_id
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
 * @property int $type
 * @property int $grade
 * @property float $cost_price
 * @property float $selling_price
 * @property float $discounted_price
 * @property int $discount
 * @property \Cake\I18n\Time $selling_date
 * @property string $zip
 * @property float $refund_price
 * @property float $refund_gain
 * @property int $refund_by
 * @property int $refund_status
 * @property string $refund_remarks
 * @property \Cake\I18n\Time $refund_date
 * @property int $status
 * @property \Cake\I18n\Time $created
 * @property \Cake\I18n\Time $modified
 *
 * @property \App\Model\Entity\Sale $sale
 * @property \App\Model\Entity\MobilePurchase $mobile_purchase
 * @property \App\Model\Entity\Kiosk $kiosk
 * @property \App\Model\Entity\User $user
 * @property \App\Model\Entity\Brand $brand
 * @property \App\Model\Entity\MobileModel $mobile_model
 * @property \App\Model\Entity\Network $network
 * @property \App\Model\Entity\RetailCustomer $retail_customer
 * @property \App\Model\Entity\CommentMobileReSale[] $comment_mobile_re_sales
 * @property \App\Model\Entity\MobileReSalePayment[] $mobile_re_sale_payments
 */
class MobileReSale extends Entity
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
