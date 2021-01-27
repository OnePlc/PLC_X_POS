<?php
/**
 * WebController.php - Web Controller
 *
 * Main Controller for Foorder Web Frontend
 *
 * @category Controller
 * @package Foodorder
 * @author Verein onePlace
 * @copyright (C) 2020 Verein onePlace <admin@1plc.ch>
 * @license https://opensource.org/licenses/BSD-3-Clause
 * @version 1.0.0
 * @since 1.0.0
 */

declare(strict_types=1);

namespace OnePlace\POS\Controller;

use Application\Controller\CoreEntityController;
use Application\Controller\CoreController;
use Laminas\View\Model\ViewModel;
use Laminas\Db\Adapter\AdapterInterface;
use Laminas\Db\TableGateway\TableGateway;
use Laminas\Db\Sql\Where;
use Laminas\Session\Container;
use Laminas\Http\Request;
use Laminas\Http\ClientStatic;
use Mike42\Escpos\PrintConnectors\FilePrintConnector;
use Mike42\Escpos\Printer;
use Mike42\Escpos\EscposImage;


class ApiController extends CoreController
{
    /**
     * User Table Object
     *
     * @var UserTable Gateway to UserTable
     * @since 1.0.0
     */
    private $oTableGateway;
    private $aPluginTbls;

    /**
     * UserController constructor.
     *
     * @param AdapterInterface $oDbAdapter
     * @param UserTable $oTableGateway
     * @param $oServiceManager
     * @since 1.0.0
     */
    public function __construct(AdapterInterface $oDbAdapter, $oTableGateway, $oServiceManager,$aPluginTbls)
    {
        $this->oTableGateway = $oTableGateway;
        $this->sSingleForm = 'pos-single';
        $this->aPluginTbls = $aPluginTbls;
        parent::__construct($oDbAdapter, $oTableGateway, $oServiceManager);
    }

    /**
     * User Index
     *
     * @return ViewModel - View Object with Data from Controller
     * @since 1.0.0
     */
    public function orderlistAction()
    {
        $this->layout('layout/json');

        $bNewOrders = false;
        $aCurrentJobs = [];
        $aCurrentJobsDelivery = [];

        $sMasterUrl = CoreController::$aGlobalSettings['pos-master-url'];

        $sJSONRaw = file_get_contents($sMasterUrl.'/foodorder/api/orderlist');
        $oJson = json_decode($sJSONRaw);

        if(is_array($oJson->aCurrentJobsDelivery)) {
           $aCurrentJobsDelivery = $oJson->aCurrentJobsDelivery;
        }
        if(is_array($oJson->aCurrentJobs)) {
           $aCurrentJobs = $oJson->aCurrentJobs;
           if(!isset(CoreController::$oSession->iOrderCount)) {
               CoreController::$oSession->iOrderCount = count($aCurrentJobs);
           }
           if(CoreController::$oSession->iOrderCount < count($aCurrentJobs)) {
               $bNewOrders = true;
           }
           CoreController::$oSession->iOrderCount = count($aCurrentJobs);
        }

        return new ViewModel([
            'aCurrentJobs' => $aCurrentJobs,
            'aCurrentJobsDelivery' => $aCurrentJobsDelivery,
            'bNewOrders' => $bNewOrders,
        ]);
    }

    /**
     * Title Settings for Receipt Printer
     */
    private function ReceiptTitle(\Mike42\Escpos\Printer $oPrinter, $str)
    {
        $oPrinter -> selectPrintMode(Printer::MODE_DOUBLE_HEIGHT | Printer::MODE_DOUBLE_WIDTH);
        $oPrinter -> text($str);
        $oPrinter -> selectPrintMode();
    }

    /**
     * Print Receipt for Order
     *
     * @return mixed
     */
    public function printAction()
    {
        $this->layout('layout/json');

        $oConnector = new FilePrintConnector("/dev/usb/lp0");
        $oPrinter = new Printer($oConnector);

        $iJobID = $this->params()->fromRoute('id', 0);
        $sMasterUrl = CoreController::$aGlobalSettings['pos-master-url'];
        $sJSONRaw = file_get_contents($sMasterUrl.'/foodorder/api/view/'.$iJobID);
        $oJson = json_decode($sJSONRaw);

        $oJob = false;
        if(is_object($oJson->oJob)) {
            $oJob = $oJson->oJob;
        }

        if($oJob) {

            $sQRData = 'https://annas.1plc.ch/foodorder/api/delivery/'.$oJob->id;

            # Center
            $oPrinter -> setJustification(Printer::JUSTIFY_CENTER);

            # Image
            $img = EscposImage::load($_SERVER['DOCUMENT_ROOT'].'/../vendor/oneplace/oneplace-pos/public/img/logo.png'); // Load image
            $oPrinter -> bitImage($img);
            $oPrinter -> feed();

            $oPrinter -> setEmphasis(true);
            $oPrinter -> text($oJob->label."\n");
            $oPrinter -> setEmphasis(false);
            $oPrinter -> feed();

             $oPrinter -> setJustification(Printer::JUSTIFY_LEFT);
             $oPrinter -> setEmphasis(false);
            $rightCols = 10;
            $leftCols = 38;
            //return "$left$right\n";

            $left = str_pad('', $leftCols) ;
            $right = str_pad('CHF', $rightCols, ' ', STR_PAD_LEFT);
            $oPrinter -> text("$left$right\n");
            $oPrinter -> feed();
            $fSubtotal = 0;
            if(count($oJob->aPositions) > 0) {
                foreach($oJob->aPositions as $oPos) {
                    $fSubtotal+=($oPos->amount*$oPos->price);
                    $left = str_pad($oPos->amount.'x '.$oPos->oArticle->oCategory->label.' '.$oPos->oArticle->label, $leftCols) ;
                    $right = str_pad(number_format(($oPos->amount*$oPos->price),2,'.','\''), $rightCols, ' ', STR_PAD_LEFT);
                    $oPrinter -> text("$left$right\n");
                    $oPrinter -> feed();
                }
            }

            $rightCols = 14;
            $leftCols = 10;

            /**
             * Total
             */
            $oPrinter->setTextSize(2,2);
            $left = str_pad('Total', $leftCols) ;
            $right = str_pad(number_format($fSubtotal,2,'.','\''), $rightCols, ' ', STR_PAD_LEFT);
            $oPrinter -> text("$left$right\n");
            $oPrinter -> feed();


            # Delivery QR
            $sName = $oJob->oContact->firstname.' '.$oJob->oContact->lastname;
            $sAddr = $oJob->oContact->oAddress->street.' '.$oJob->oContact->oAddress->appartment;
            $sCity = $oJob->oContact->oAddress->zip.' '.$oJob->oContact->oAddress->city;

            $oPrinter -> setJustification(Printer::JUSTIFY_LEFT);
            $oPrinter->setTextSize(1,1);

            $left = str_pad("$sName\n$sAddr\n$sCity\n", $leftCols) ;
            $oPrinter -> text("$left\n");

            $oPrinter -> setJustification(Printer::JUSTIFY_LEFT);
            $oPrinter -> qrCode($sQRData, Printer::QR_ECLEVEL_L, 5);
            $oPrinter -> setJustification();
            $oPrinter -> feed();

            $oPrinter -> cut();
            $oPrinter -> close();
        }

        return $this->redirect()->toRoute('touchscreen');
    }


    /**
     * Foodorder View
     *
     * @return ViewModel - View Object with Data from Controller
     * @since 1.0.0
     */
    public function confirmAction()
    {
        # Set layout
        $this->layout('layout/touchscreen');

        $oRequest = $this->getRequest();

        if($oRequest->isPost()) {
            $iJobID = $oRequest->getPost('job_id');

            $sMasterUrl = CoreController::$aGlobalSettings['pos-master-url'];
            $oApiCall = ClientStatic::post(
                $sMasterUrl.'/foodorder/api/confirm',
                [
                    'job_id' => $iJobID,
                ]
            );
        }

        return $this->redirect()->toRoute('touchscreen', []);
    }
}
