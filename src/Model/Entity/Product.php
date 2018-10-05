<?php
namespace App\Model\Entity;

use Cake\ORM\Entity;

/**
 * Product Entity
 *
 * @property string $prefix
 * @property int $id
 * @property string $product
 * @property int $quantity
 * @property string $description
 * @property string $location
 * @property int $category_id
 * @property float $cost_price
 * @property \Cake\I18n\Time $lu_cp
 * @property float $retail_cost_price
 * @property \Cake\I18n\Time $lu_rcp
 * @property float $selling_price
 * @property \Cake\I18n\Time $lu_sp
 * @property float $retail_selling_price
 * @property \Cake\I18n\Time $lu_rsp
 * @property int $brand_id
 * @property string $model
 * @property \Cake\I18n\Time $manufacturing_date
 * @property int $sku
 * @property string $country_make
 * @property string $product_code
 * @property float $weight
 * @property string $color
 * @property int $user_id
 * @property int $featured
 * @property int $discount
 * @property int $retail_discount
 * @property int $discount_status
 * @property int $rt_discount_status
 * @property int $max_discount
 * @property int $min_discount
 * @property int $image_id
 * @property string $image_dir
 * @property string $manufacturer
 * @property int $stock_level
 * @property int $dead_stock_level
 * @property int $status
 * @property \Cake\I18n\Time $created
 * @property \Cake\I18n\Time $modified
 *
 * @property \App\Model\Entity\Image $image
 * @property \App\Model\Entity\Category $category
 * @property \App\Model\Entity\Brand $brand
 * @property \App\Model\Entity\User $user
 * @property \App\Model\Entity\CreditProductDetail[] $credit_product_details
 * @property \App\Model\Entity\DailyStock[] $daily_stocks
 * @property \App\Model\Entity\DefectiveBin[] $defective_bin
 * @property \App\Model\Entity\DefectiveBinTransient[] $defective_bin_transients
 * @property \App\Model\Entity\DefectiveCentralProduct[] $defective_central_products
 * @property \App\Model\Entity\DefectiveKioskProduct[] $defective_kiosk_products
 * @property \App\Model\Entity\DefectiveKioskTransient[] $defective_kiosk_transients
 * @property \App\Model\Entity\FaultyProductDetail[] $faulty_product_details
 * @property \App\Model\Entity\FaultyProduct[] $faulty_products
 * @property \App\Model\Entity\ImportOrderDetail[] $import_order_details
 * @property \App\Model\Entity\InvoiceOrderDetail[] $invoice_order_details
 * @property \App\Model\Entity\Kiosk10000CreditProductDetail[] $kiosk10000_credit_product_details
 * @property \App\Model\Entity\Kiosk10000DailyStock[] $kiosk10000_daily_stocks
 * @property \App\Model\Entity\Kiosk10000InvoiceOrderDetail[] $kiosk10000_invoice_order_details
 * @property \App\Model\Entity\Kiosk10000ProductSale[] $kiosk10000_product_sales
 * @property \App\Model\Entity\Kiosk10ProductSale[] $kiosk10_product_sales
 * @property \App\Model\Entity\Kiosk1CreditProductDetail[] $kiosk1_credit_product_details
 * @property \App\Model\Entity\Kiosk1DailyStock[] $kiosk1_daily_stocks
 * @property \App\Model\Entity\Kiosk1InvoiceOrderDetail[] $kiosk1_invoice_order_details
 * @property \App\Model\Entity\Kiosk1ProductSale[] $kiosk1_product_sales
 * @property \App\Model\Entity\Kiosk2CreditProductDetail[] $kiosk2_credit_product_details
 * @property \App\Model\Entity\Kiosk2DailyStock[] $kiosk2_daily_stocks
 * @property \App\Model\Entity\Kiosk2InvoiceOrderDetail[] $kiosk2_invoice_order_details
 * @property \App\Model\Entity\Kiosk2ProductSale[] $kiosk2_product_sales
 * @property \App\Model\Entity\Kiosk3CreditProductDetail[] $kiosk3_credit_product_details
 * @property \App\Model\Entity\Kiosk3DailyStock[] $kiosk3_daily_stocks
 * @property \App\Model\Entity\Kiosk3DailyStocksBak[] $kiosk3_daily_stocks_bak
 * @property \App\Model\Entity\Kiosk3DailyStocksBak1[] $kiosk3_daily_stocks_bak1
 * @property \App\Model\Entity\Kiosk3InvoiceOrderDetail[] $kiosk3_invoice_order_details
 * @property \App\Model\Entity\Kiosk3ProductSale[] $kiosk3_product_sales
 * @property \App\Model\Entity\Kiosk4CreditProductDetail[] $kiosk4_credit_product_details
 * @property \App\Model\Entity\Kiosk4DailyStock[] $kiosk4_daily_stocks
 * @property \App\Model\Entity\Kiosk4InvoiceOrderDetail[] $kiosk4_invoice_order_details
 * @property \App\Model\Entity\Kiosk4ProductSale[] $kiosk4_product_sales
 * @property \App\Model\Entity\Kiosk5CreditProductDetail[] $kiosk5_credit_product_details
 * @property \App\Model\Entity\Kiosk5DailyStock[] $kiosk5_daily_stocks
 * @property \App\Model\Entity\Kiosk5InvoiceOrderDetail[] $kiosk5_invoice_order_details
 * @property \App\Model\Entity\Kiosk5ProductSale[] $kiosk5_product_sales
 * @property \App\Model\Entity\Kiosk6CreditProductDetail[] $kiosk6_credit_product_details
 * @property \App\Model\Entity\Kiosk6DailyStock[] $kiosk6_daily_stocks
 * @property \App\Model\Entity\Kiosk6InvoiceOrderDetail[] $kiosk6_invoice_order_details
 * @property \App\Model\Entity\Kiosk6ProductSale[] $kiosk6_product_sales
 * @property \App\Model\Entity\Kiosk7CreditProductDetail[] $kiosk7_credit_product_details
 * @property \App\Model\Entity\Kiosk7DailyStock[] $kiosk7_daily_stocks
 * @property \App\Model\Entity\Kiosk7InvoiceOrderDetail[] $kiosk7_invoice_order_details
 * @property \App\Model\Entity\Kiosk7ProductSale[] $kiosk7_product_sales
 * @property \App\Model\Entity\Kiosk8CreditProductDetail[] $kiosk8_credit_product_details
 * @property \App\Model\Entity\Kiosk8DailyStock[] $kiosk8_daily_stocks
 * @property \App\Model\Entity\Kiosk8InvoiceOrderDetail[] $kiosk8_invoice_order_details
 * @property \App\Model\Entity\Kiosk8ProductSale[] $kiosk8_product_sales
 * @property \App\Model\Entity\KioskProductSale[] $kiosk_product_sales
 * @property \App\Model\Entity\KioskCancelledOrderProduct[] $kiosk_cancelled_order_products
 * @property \App\Model\Entity\KioskOrderProduct[] $kiosk_order_products
 * @property \App\Model\Entity\KioskTransferredStock1[] $kiosk_transferred_stock1
 * @property \App\Model\Entity\KioskTransferredStock10000[] $kiosk_transferred_stock10000
 * @property \App\Model\Entity\KioskTransferredStock2[] $kiosk_transferred_stock2
 * @property \App\Model\Entity\KioskTransferredStock3[] $kiosk_transferred_stock3
 * @property \App\Model\Entity\KioskTransferredStock4[] $kiosk_transferred_stock4
 * @property \App\Model\Entity\KioskTransferredStock5[] $kiosk_transferred_stock5
 * @property \App\Model\Entity\KioskTransferredStock6[] $kiosk_transferred_stock6
 * @property \App\Model\Entity\KioskTransferredStock7[] $kiosk_transferred_stock7
 * @property \App\Model\Entity\KioskTransferredStock8[] $kiosk_transferred_stock8
 * @property \App\Model\Entity\MobileRepairPart[] $mobile_repair_parts
 * @property \App\Model\Entity\OnDemandProduct[] $on_demand_products
 * @property \App\Model\Entity\OrderDispute[] $order_disputes
 * @property \App\Model\Entity\ProductSaleStat[] $product_sale_stats
 * @property \App\Model\Entity\ProductSellStat[] $product_sell_stats
 * @property \App\Model\Entity\ReorderLevel[] $reorder_levels
 * @property \App\Model\Entity\ReservedProduct[] $reserved_products
 * @property \App\Model\Entity\RevertStock[] $revert_stocks
 * @property \App\Model\Entity\StockTakingDetail[] $stock_taking_details
 * @property \App\Model\Entity\StockTransfer[] $stock_transfer
 * @property \App\Model\Entity\StockTransferByKiosk[] $stock_transfer_by_kiosk
 * @property \App\Model\Entity\TKioskProductSale[] $t_kiosk_product_sales
 * @property \App\Model\Entity\TProductSaleStat[] $t_product_sale_stats
 * @property \App\Model\Entity\TProductSellStat[] $t_product_sell_stats
 * @property \App\Model\Entity\TTempProductSale[] $t_temp_product_sales
 * @property \App\Model\Entity\TempProductDetail[] $temp_product_details
 * @property \App\Model\Entity\TransferSurplus[] $transfer_surplus
 * @property \App\Model\Entity\TransferUnderstock[] $transfer_understock
 * @property \App\Model\Entity\TransientStock[] $transient_stock
 * @property \App\Model\Entity\UnderstockLevelOrder[] $understock_level_orders
 * @property \App\Model\Entity\WarehouseStock[] $warehouse_stock
 */
class Product extends Entity
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
