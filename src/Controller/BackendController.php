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
use OnePlace\Article\Model\ArticleTable;

class BackendController extends CoreController
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
    public function indexAction()
    {
        # Set layout
        $this->layout('layout/json');

        return false;
    }

    /**
     * Foodorder View
     *
     * @return ViewModel - View Object with Data from Controller
     * @since 1.0.0
     */
    public function viewAction()
    {
        # Set layout
        $this->layout('layout/touchscreen');

        $iJobID = $this->params()->fromRoute('id', 0);

        $oJob = false;

        $sMasterUrl = CoreController::$aGlobalSettings['pos-master-url'];
        $sJSONRaw = file_get_contents($sMasterUrl.'/foodorder/api/view/'.$iJobID);
        try {
            $oJson = json_decode($sJSONRaw);
        } catch(\TypeError $e) {
            var_dump($sJSONRaw);
            return false;
        }

        if(is_object($oJson->oJob)) {
            $oJob = $oJson->oJob;
        }

        return new ViewModel([
            'oJob' => $oJob,
        ]);
    }

    /**
     * Touchscreen Index
     *
     * @return ViewModel
     */
    public function touchscreenAction()
    {
        # Set layout
        $this->layout('layout/touchscreen');

        if(!isset(CoreController::$oSession->oUser)) {
            $this->posLogin();
        }

        return new ViewModel([]);
    }

    private function posLogin() {
        $sUser = CoreController::$aGlobalSettings['pos-login'];
        $oUser = $this->aPluginTbls['user']->getSingle($sUser, 'email');
        CoreController::$oSession->oUser = $oUser;
    }

    /**
     * Touch Cash Register
     *
     * @return Redirect
     */
    public function ontheroadAction()
    {
        # Set layout
        $this->layout('layout/json');

        $iJobID = $this->params()->fromRoute('id', 0);

        $sMasterUrl = CoreController::$aGlobalSettings['pos-master-url'];
        $sJSONRaw = file_get_contents($sMasterUrl.'/foodorder/api/deliveryajax/'.$iJobID);

        return $this->redirect()->toRoute('touchscreen');
    }
}
