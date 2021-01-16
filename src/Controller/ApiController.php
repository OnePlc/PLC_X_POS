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
        }
        if(isset($oJson->$bNewOrders)) {
           $bNewOrders = $oJson->bNewOrders;
        }

        return new ViewModel([
            'aCurrentJobs' => $aCurrentJobs,
            'aCurrentJobsDelivery' => $aCurrentJobsDelivery,
            'bNewOrders' => $bNewOrders,
        ]);
    }

    private function ReceiptTitle(\Mike42\Escpos\Printer $printer, $str) {
        $printer -> selectPrintMode(Printer::MODE_DOUBLE_HEIGHT | Printer::MODE_DOUBLE_WIDTH);
        $printer -> text($str);
        $printer -> selectPrintMode();
    }

    public function printAction() {
        $this->layout('layout/json');

        $connector = new FilePrintConnector("/dev/usb/lp0");
        $printer = new Printer($connector);

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
            $printer -> setJustification(Printer::JUSTIFY_CENTER);

            # Image
            $img = EscposImage::load($_SERVER['DOCUMENT_ROOT'].'/../vendor/oneplace/oneplace-pos/public/img/logo.png'); // Load image
            $printer -> bitImage($img);
            $printer -> feed();

            $printer -> setEmphasis(true);
            $printer -> text($oJob->label."\n");
            $printer -> setEmphasis(false);
            $printer -> feed();
            if(count($oJob->aPositions) > 0) {
                foreach($oJob->aPositions as $oPos) {
                    $printer -> text($oPos->amount.'x '.$oPos->oArticle->label."\n");
                    $printer -> feed();
                }
            }
            $printer -> feed();

            # Delivery QR
            $printer -> qrCode($sQRData, Printer::QR_ECLEVEL_L, 5);
            $printer -> setJustification();
            $printer -> feed();

            $printer -> cut();
            $printer -> close();
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
