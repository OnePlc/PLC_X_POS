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
use Laminas\Http\ClientStatic;
use OnePlace\Article\Model\ArticleTable;
use Mike42\Escpos\PrintConnectors\FilePrintConnector;
use Mike42\Escpos\Printer;
use Mike42\Escpos\EscposImage;

class CheckoutController extends CoreController
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
     * Worktime Dashboard for Touchscreen POS
     *
     * @since 1.0.0
     * @return ViewModel
     */
    public function indexAction()
    {
        # Set layout
        $this->layout('layout/touchscreen');

        if(!isset(CoreController::$oSession->oUser)) {
            $this->posLogin();
        }

        if(!isset(CoreController::$oSession->oCheckoutList)) {
            CoreController::$oSession->oCheckoutList = [];
        }
        CoreController::$oSession->oCheckoutList = [];

        $aArticles = [];

        $sMasterUrl = CoreController::$aGlobalSettings['pos-master-url'];
        $sApiCall = $sMasterUrl.'/article/api/list?authkey='.CoreController::$aGlobalSettings['pos-master-authkey'].
            '&authtoken='.CoreController::$aGlobalSettings['pos-master-authtoken'].
            '&listmode=entity';

        $sJSONRaw = file_get_contents($sApiCall);
        $oJson = json_decode($sJSONRaw);

        if(is_object($oJson)) {
            $oArticlesDB = $oJson->results;
            if(count($oArticlesDB) > 0) {
                foreach($oArticlesDB as $oArt) {
                    $aArticles[] = $oArt;
                }
            }

            return new ViewModel([
                'aArticles' => $aArticles,
            ]);
        } else {
            echo 'Fehler bei der Kommunikation mit dem Server';

            return false;
        }
    }

    public function listAction()
    {
        $this->layout('layout/json');

        $oRequest = $this->getRequest();

        if($oRequest->isPost()) {
            $iArticleID = $oRequest->getPost('item_id');
            $fArticlePrice = $oRequest->getPost('item_price');
            $sLabel = $oRequest->getPost('item_label');

            if(!isset(CoreController::$oSession->oCheckoutList)) {
                CoreController::$oSession->oCheckoutList = [];
            }

            if(!array_key_exists($iArticleID,CoreController::$oSession->oCheckoutList)) {
                CoreController::$oSession->oCheckoutList[$iArticleID] = (object)[
                    'amount' => 0,
                    'id' => $iArticleID,
                    'price' => $fArticlePrice,
                    'label' => $sLabel];
            }
            CoreController::$oSession->oCheckoutList[$iArticleID]->amount++;

            return new ViewModel([
                'aList' => CoreController::$oSession->oCheckoutList,
            ]);
        }

        echo "Error communicating with server";

        return false;
    }

    public function doneAction() {
        $this->layout('layout/json');

        $bPrint = $this->params()->fromRoute('id', 0);

        $response = ClientStatic::post(
            'https://annas.1plc.ch/foodorder/api/posorder', ['device' => 'kasse01'], [], json_encode(CoreController::$oSession->oCheckoutList)
        );

        $iStatus = $response->getStatusCode();
        $sRespnse = $response->getBody();

        $oJson = json_decode($sRespnse);
        if($oJson->state == 'success') {
            if($bPrint) {
                if(file_exists('/dev/usb/lp0')) {
                    $oConnector = new FilePrintConnector("/dev/usb/lp0");
                    $oPrinter = new Printer($oConnector);

                    # Center
                    $oPrinter -> setJustification(Printer::JUSTIFY_CENTER);

                    # Image
                    $img = EscposImage::load($_SERVER['DOCUMENT_ROOT'].'/../vendor/oneplace/oneplace-pos/public/img/logo.png'); // Load image
                    $oPrinter -> bitImage($img);
                    $oPrinter -> feed();

                    $oPrinter -> setEmphasis(true);
                    $oPrinter -> text("Take-Away Bestellung ".date('d.m.Y H:i', time())."\n");
                    $oPrinter -> setEmphasis(false);
                    $oPrinter -> feed();

                    $oPrinter -> setJustification(Printer::JUSTIFY_LEFT);
                    $oPrinter -> setEmphasis(false);
                    $rightCols = 10;
                    $leftCols = 38;

                    $left = str_pad('', $leftCols) ;
                    $right = str_pad('CHF', $rightCols, ' ', STR_PAD_LEFT);
                    $oPrinter -> text("$left$right\n");
                    $oPrinter -> feed();
                    $fSubtotal = 0;
                    if(count(CoreController::$oSession->oCheckoutList) > 0) {
                        foreach(CoreController::$oSession->oCheckoutList as $oPos) {
                            $fSubtotal+=($oPos->amount*$oPos->price);
                            $left = str_pad($oPos->amount.'x '.$oPos->label, $leftCols) ;
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

                    $oPrinter -> cut();
                    $oPrinter -> close();
                }
            }
            $this->flashMessenger()->addSuccessMessage('Bestellung erfolgreich verbucht');
            $this->redirect()->toRoute('pos-checkout');
        } else {
            echo $sRespnse;
            return false;
        }
    }
}
