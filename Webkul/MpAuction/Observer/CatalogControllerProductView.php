<?php
/**
 * Webkul_MpAuction Product View Observer.
 * @category  Webkul
 * @package   Webkul_MpAuction
 * @author    Webkul
 * @copyright Copyright (c) 2010-2017 Webkul Software Private Limited (https://webkul.com)
 * @license   https://store.webkul.com/license.html
 */
namespace Webkul\MpAuction\Observer;

use Magento\Framework\Event\ObserverInterface;
use Magento\ConfigurableProduct\Model\Product\Type\Configurable;
use Magento\Catalog\Model\Product;
use Magento\Framework\Stdlib\DateTime\TimezoneInterface;
use Webkul\MpAuction\Helper\Data as HelperData;
use Webkul\MpAuction\Helper\Email as HelperEmail;
use Webkul\MpAuction\Model\ProductFactory as AuctionProductFactory;
use Webkul\MpAuction\Model\AmountFactory as AuctionAmount;
use Webkul\MpAuction\Model\AutoAuctionFactory as AutoAuction;
use Webkul\MpAuction\Model\WinnerDataFactory as WinnerData;

/**
 * Reports Event observer model.
 */
class CatalogControllerProductView implements ObserverInterface
{
    /**
     * @var \Magento\Framework\Stdlib\DateTime\TimezoneInterface
     */
    private $dateTime;

    /**
     * @var Configurable
     */
    private $configurableProTypeModel;

    /**
     * @var \Webkul\MpAuction\Helper\Data
     */
    private $helperData;

    /**
     * @var \Webkul\MpAuction\Helper\Email
     */
    private $helperEmail;

    /**
     * @var \Webkul\MpAuction\Model\ProductFactory
     */
    private $auctionProductFactory;

    /**
     * @var \Webkul\MpAuction\Model\Amount
     */
    private $auctionAmount;

    /**
     * @var \Webkul\MpAuction\Model\AutoAuction
     */
    private $autoAuction;

    /**
     * @var \Webkul\MpAuction\Model\WinnerData
     */
    private $winnerData;

    /**
     * $resPrice
     * @var float
     */
    private $resPrice;

    /**
     * $bidDay
     * @var int
     */
    private $bidDay;

    /**
     * $bidId bid id
     * @var int
     */
    private $bidId;

    /**
     * $incPrice
     * @var float
     */
    private $incPrice;

    /**
     * $datestop auction stop date
     * @var string
     */
    private $datestop;

    /**
     * $datestart auction start date
     * @var string
     */
    private $datestart;

    /**
     * $winDataTemp winner data
     * @var array
     */
    private $winDataTemp = [];

    /**
     * $auctionConfig auction config
     * @var array
     */
    private $auctionConfig = [];

    /**
     * @param TimezoneInterface              $dateTime
     * @param Configurable          $configProTypeModel
     * @param HelperData            $helperData
     * @param HelperEmail           $helperEmail
     * @param AuctionProductFactory $auctionProductFactory
     * @param AuctionAmount         $auctionAmount
     * @param AutoAuction           $autoAuction
     * @param WinnerData            $winnerData
     */

    public function __construct(
        TimezoneInterface $dateTime,
        Configurable $configProTypeModel,
        HelperData $helperData,
        HelperEmail $helperEmail,
        AuctionProductFactory $auctionProductFactory,
        AuctionAmount $auctionAmount,
        AutoAuction $autoAuction,
        WinnerData $winnerData
    ) {
        $this->dateTime = $dateTime;
        $this->configurableProTypeModel = $configProTypeModel;
        $this->helperData = $helperData;
        $this->helperEmail = $helperEmail;
        $this->auctionProductFactory = $auctionProductFactory;
        $this->auctionAmount = $auctionAmount;
        $this->autoAuction = $autoAuction;
        $this->winnerData = $winnerData;
    }

    /**
     * View Catalog Product View observer.
     * @param \Magento\Framework\Event\Observer $observer
     * @return $this
     */
    public function execute(\Magento\Framework\Event\Observer $observer)
    {
        $this->auctionConfig = $this->helperData->getAuctionConfiguration();
        $product = $observer->getEvent()->getProduct();
        $productId = $product->getId();
        if ($product && $this->auctionConfig['enable']) {
            $productId = $product->getId();
            if ($product->getTypeId() == 'configurable') {
                $childPro = $this->configurableProTypeModel->getChildrenIds($productId);
                $childProIds = isset($childPro[0]) ? $childPro[0]:[0];
                $auctionActPro = $this->auctionProductFactory->create()->getCollection()
                                            ->addFieldToFilter('product_id', ['in' => $childProIds])
                                            ->addFieldToFilter('auction_status', 1)
                                            ->addFieldToFilter('status', 1);
                if (!empty($auctionActPro)) {
                    foreach ($auctionActPro as $value) {
                        $this->biddingOperation($value->getProductId());
                    }
                }
            } else {
                $this->biddingOperation($productId);
            }
        }
        return $this;
    }

    /**
     * biddingOperation
     * @param int $productId on which auction apply
     * @return void
     */
    private function biddingOperation($productId)
    {
        $auctionActPro = $this->getAuctionProduct($productId);
        if ($auctionActPro && ($this->datestop >= $this->datestart)) {
            $this->winDataTemp['auction_id'] = $auctionActPro->getEntityId();
            $today = $this->dateTime->date()->format('m/d/y H:i:s');
            $current = strtotime($today);
            $difference = $this->datestop - $current;

            if ($difference <= 0) {
                $this->saveWinnerData($auctionActPro);
            } else {
                $winnerDataModel = $this->winnerData->create()->getCollection()
                                                ->addFieldToFilter('product_id', ['eq' => $productId])
                                                ->addFieldToFilter('status', ['eq'=>1])->setPageSize(1);
                $winnerDataModel = $this->getFirstItemFromRecord($winnerDataModel);
                                                
                if ($winnerDataModel && $winnerDataModel->getEntityId()) {
                    $this->bidDay = $winnerDataModel->getDays() ? 1: $winnerDataModel->getDays();
                    $current = strtotime($this->dateTime->date()->format('m/d/y H:i:s'));
                    $day = strtotime($winnerDataModel->getStopBiddingTime().' + '.$this->bidDay.' days');
                    $difference = $day - $current;
                    if ($difference < 0) {
                        $winnerDataModel->setStatus(0);
                        $winnerDataModel->setId($winnerDataModel->getEntityId());
                        $this->saveObj($winnerDataModel);
                    }
                }
            }
        }
    }

    /**
     * getAuctionProduct get auction product
     * @param  int $productId
     * @return Object
     */
    private function getAuctionProduct($productId)
    {
        $auctionProduct =  $this->auctionProductFactory->create()->getCollection()
                                                        ->addFieldToFilter('product_id', ['eq' => $productId])
                                                        ->addFieldToFilter('auction_status', 1)
                                                        ->addFieldToFilter('status', 0)->setPageSize(1);
        $auctionProduct = $this->getFirstItemFromRecord($auctionProduct);

        if ($auctionProduct && $auctionProduct->getEntityId()) {
            $this->resPrice = 0;
            $this->resPriceConfig = $this->auctionConfig['reserve_enable'] ? $this->auctionConfig['reserve_price'] : '';
            
            $this->bidDay = $auctionProduct->getDays();
            $this->bidId = $auctionProduct->getEntityId();
            
            $this->incPrice = $auctionProduct->getIncrementPrice();
            $this->resPrice = $auctionProduct->getReservePrice();
           
            $this->resPrice = $this->resPrice == 0 ? $this->resPriceConfig:$this->resPrice;
            $this->datestop = strtotime($auctionProduct->getStopAuctionTime());
            $this->datestart = strtotime($auctionProduct->getStartAuctionTime());
            return $auctionProduct;
        }

        return false;
    }

    /**
     * getWinnerData save winner
     * @param  Webkul\MpAuction\Model\ProductFactory $auctionActPro
     * @return void
     */
    private function saveWinnerData($auctionActPro)
    {
        $productId = $auctionActPro->getProductId();
        $bidArray = [0];

        $bidAmountList = $this->auctionAmount->create()->getCollection()
                                ->addFieldToFilter('auction_id', ['eq' => $this->bidId])
                                ->addFieldToFilter('status', ['eq' => 1])
                                ->setOrder('auction_amount', 'ASC');
        foreach ($bidAmountList as $bidAmt) {
            $bidArray[$bidAmt->getCustomerId()] = $bidAmt->getAuctionAmount();
        }
             
        if (!empty($bidArray)) {
            $customerIds = array_keys($bidArray, max($bidArray));
            $this->winDataTemp['customer_id'] = $customerIds[0];
            $this->winDataTemp['amount'] = max($bidArray);
            $this->winDataTemp['type'] = 'normal';
        }
      
        if ($this->auctionConfig['auto_enable'] && $auctionActPro->getAutoAuctionOpt()) {
            $this->calculateIfAutoBidEnable($bidArray, $auctionActPro);
        }

        if (isset($this->winDataTemp['customer_id']) && $this->resPrice <= $this->winDataTemp['amount']) {
            if ($this->winDataTemp['type'] == 'auto') {
                $this->winnerByAutoBid($productId);
            } elseif ($this->winDataTemp['type']== 'normal') {
                $this->winnerByNormalBid($productId);
            }

            //save winner record in winner data table
            $winnerDataModel = $this->winnerData->create();
            $auctionModel = $this->auctionProductFactory->create()->load($this->bidId)->getData();
            $auctionModel['customer_id'] = $this->winDataTemp['customer_id'];
            $auctionModel['status'] = 1;
            $auctionModel['auction_id'] = $auctionModel['entity_id'];
            $auctionModel['win_amount'] = $this->winDataTemp['amount'];
            unset($auctionModel['entity_id']);
            $winnerDataModel->setData($auctionModel);
            $winnerDataModel->save();
            $auctionActPro->setAuctionStatus(0);
        } elseif (isset($this->winDataTemp['type']) && $this->auctionConfig['auto_enable'] && $this->winDataTemp['type'] == 'auto') {
            $autoBiddList = $this->autoAuction->create()->getCollection()
                ->addFieldToFilter('auction_id', ['eq' => $this->bidId])
                ->addFieldToFilter('status', 1);
            foreach ($autoBiddList as $autoBid) {
                if ($autoBid->getCustomerId() == $this->winDataTemp['customer_id']) {
                    $autoBid->setFlag(1);
                    $autoBid->setWinningPrice($auctionActPro->getReservePrice());
                    /** send notification mail to winner */
                    if ($this->auctionConfig['enable_winner_notify_email']) {
                        $this->helperEmail->sendWinnerMail(
                            $this->winDataTemp['customer_id'],
                            $productId,
                            $auctionActPro->getReservePrice()
                        );
                    }
                }
                $autoBid->setStatus(0);
                $autoBid->setId($autoBid->getEntityId());
                $this->saveObj($autoBid);
            }

            //save winner record in winner data table
            $winnerDataModel = $this->winnerData->create();
            $auctionModel = $this->auctionProductFactory->create()->load($this->bidId)->getData();
            $auctionModel['customer_id'] = $this->winDataTemp['customer_id'];
            $auctionModel['status'] = 1;
            $auctionModel['auction_id'] = $auctionModel['entity_id'];
            $auctionModel['win_amount'] = $auctionActPro->getReservePrice();//$auctionModel['min_amount'];
            unset($auctionModel['entity_id']);
            $winnerDataModel->setData($auctionModel);
            $this->saveObj($winnerDataModel);
            $auctionActPro->setAuctionStatus(0);
        } else {
            $auctionActPro->setAuctionStatus(2);
        }
        $auctionActPro->setId($auctionActPro->getEntityId());
        $this->saveObj($auctionActPro);
    }

    /**
     * winnerByAutoBid
     * @param int $productId
     * @return void
     */
    private function winnerByAutoBid($productId)
    {
        $autoBiddList = $this->autoAuction->create()->getCollection()
                                    ->addFieldToFilter('auction_id', ['eq' => $this->bidId])
                                    ->addFieldToFilter('status', 1);

        foreach ($autoBiddList as $autoBid) {
            if ($autoBid->getCustomerId() == $this->winDataTemp['customer_id']) {
                $autoBid->setFlag(1);
                $autoBid->setWinningPrice($this->winDataTemp['amount']);
                // send notification mail to winner
                if ($this->auctionConfig['enable_winner_notify_email']) {
                    $this->helperEmail->sendWinnerMail(
                        $this->winDataTemp['customer_id'],
                        $productId,
                        $this->winDataTemp['amount']
                    );
                }
            }
            $autoBid->setStatus(0);
            $autoBid->setId($autoBid->getEntityId());
            $this->saveObj($autoBid);
        }
    }

    /**
     * winnerByNormalBid
     * @param int $productId
     * @return void
     */
    private function winnerByNormalBid($productId)
    {
        $normalBidList = $this->auctionAmount->create()->getCollection()
                                            ->addFieldToFilter('auction_id', ['eq' => $this->bidId])
                                            ->addFieldToFilter('status', ['eq' => 1]);

        foreach ($normalBidList as $normalBid) {
            if ($normalBid->getCustomerId() == $this->winDataTemp['customer_id']) {
                $normalBid->setWinningStatus(1);
                // send notification mail to winner
                if ($this->auctionConfig['enable_winner_notify_email']) {
                    $this->helperEmail->sendWinnerMail(
                        $this->winDataTemp['customer_id'],
                        $productId,
                        $this->winDataTemp['amount']
                    );
                }
            }
            $normalBid->setStatus(0);
            $normalBid->setId($normalBid->getEntityId());
            $this->saveObj($normalBid);
        }
    }
    
    /**
     * calculateIfAutoBidEnable
     * @param array $bidArray
     * @param \Webkul\MpAuction\Model\Product $auctionActPro
     * @return void
     */
    private function calculateIfAutoBidEnable($bidArray, $auctionActPro)
    {
        $autoBidArray = [0];
        $autoBidList = $this->autoAuction->create()->getCollection()
                                    ->addFieldToFilter('auction_id', ['eq' => $this->bidId])
                                    ->addFieldToFilter('status', ['eq' => 1]);
        if (count($bidArray)) {
            $autoBidList->addFieldToFilter('amount', ['gteq'=> max($bidArray)]);
            $this->winDataTemp['amount'] = max($bidArray);
        } else {
            $this->resPrice = $auctionActPro->getReservePrice();
            $starPrice = $auctionActPro->getStartingPrice();
            $this->winDataTemp['amount'] = $this->resPrice ? $this->resPrice : $starPrice;
        }

        if (count($autoBidList)) {
            foreach ($autoBidList as $autoBid) {
                $custId = $autoBid->getCustomerId();
                $autoBidArray[$custId] = $autoBid->getAmount();
            }

            $customerIds = array_keys($autoBidArray, max($autoBidArray));
            if (max($autoBidArray) >= $this->resPrice) {
                $this->winDataTemp['customer_id'] = $customerIds[0];
                $this->winDataTemp['type'] = 'auto';
            }
        }
    }

    /**
     * saveObj
     * @param Object
     * @return void
     */
    private function saveObj($object)
    {
        $object->save();
    }

    /**
     * getFirstItemFromRecord
     * @param Collection Object
     * @return false | data
     */
    private function getFirstItemFromRecord($collection)
    {
        foreach ($collection as $row) {
            return $row;
        }
        return false;
    }
}
