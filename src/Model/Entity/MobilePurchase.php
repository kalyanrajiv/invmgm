<?php
namespace App\Model\Entity;

use Cake\ORM\Entity;

/**
 * MobilePurchase Entity
 *
 * @property int $id
 * @property int $purchase_number
 * @property string $mobile_purchase_reference
 * @property int $rand_num
 * @property int $kiosk_id
 * @property int $user_id
 * @property int $new_kiosk_id
 * @property int $purchased_by_kiosk
 * @property string $mobile_condition
 * @property string $mobile_condition_remark
 * @property string $function_condition
 * @property int $brand_id
 * @property int $mobile_model_id
 * @property int $network_id
 * @property string $color
 * @property string $imei
 * @property string $brief_history
 * @property string $customer_fname
 * @property string $customer_lname
 * @property \Cake\I18n\Time $date_of_birth
 * @property string $customer_email
 * @property string $customer_contact
 * @property string $customer_address_1
 * @property string $customer_address_2
 * @property string $city
 * @property string $state
 * @property string $country
 * @property string $customer_identification
 * @property string $serial_number
 * @property string $image
 * @property string $image_dir
 * @property string $photo_type
 * @property string $photo_size
 * @property string $path
 * @property string $description
 * @property float $cost_price
 * @property float $topedup_price
 * @property string $grade
 * @property int $type
 * @property \Cake\I18n\Time $purchasing_date
 * @property \Cake\I18n\Time $reserve_date
 * @property int $reserved_by
 * @property \Cake\I18n\Time $transient_date
 * @property int $transient_by
 * @property string $zip
 * @property int $receiving_status
 * @property int $status
 * @property int $mobile_status
 * @property int $purchase_status
 * @property int $custom_grades
 * @property float $selling_price
 * @property float $static_selling_price
 * @property float $lowest_selling_price
 * @property \Cake\I18n\Time $created
 * @property \Cake\I18n\Time $modified
 *
 * @property \App\Model\Entity\Kiosk $kiosk
 * @property \App\Model\Entity\User $user
 * @property \App\Model\Entity\NewKiosk $new_kiosk
 * @property \App\Model\Entity\Brand $brand
 * @property \App\Model\Entity\MobileModel $mobile_model
 * @property \App\Model\Entity\Network $network
 * @property \App\Model\Entity\CommentMobilePurchase[] $comment_mobile_purchases
 * @property \App\Model\Entity\MobileBlkReSalePayment[] $mobile_blk_re_sale_payments
 * @property \App\Model\Entity\MobileBlkReSale[] $mobile_blk_re_sales
 * @property \App\Model\Entity\MobileBlkTransferLog[] $mobile_blk_transfer_logs
 * @property \App\Model\Entity\MobilePayment[] $mobile_payments
 * @property \App\Model\Entity\MobileReSalePayment[] $mobile_re_sale_payments
 * @property \App\Model\Entity\MobileReSale[] $mobile_re_sales
 * @property \App\Model\Entity\MobileTransferLog[] $mobile_transfer_logs
 */
class MobilePurchase extends Entity
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
