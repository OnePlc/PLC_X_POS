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

class WorktimeController extends CoreController
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

        return new ViewModel([
        ]);
    }

    /**
     * Employee Worktime Saldo View for Touchscreen POS
     *
     * @since 1.0.0
     * @return ViewModel
     */
    public function saldoAction()
    {
        $this->layout('layout/touchscreen');

        $sMasterUrl = CoreController::$aGlobalSettings['pos-master-url'];
        $sApiCall = $sMasterUrl.'/worktime/api/employees?authkey='.CoreController::$aGlobalSettings['pos-master-authkey'].
            '&authtoken='.CoreController::$aGlobalSettings['pos-master-authtoken'].
            '&listmode=entity';
        $sJSONRaw = file_get_contents($sApiCall);
        $oJson = json_decode($sJSONRaw);

        $aEmployees = [];
        if(is_array($oJson->results)) {
            $aEmployees = $oJson->results;
        }

        return new ViewModel([
            'aEmployees' => $aEmployees,
        ]);
    }

    /**
     * Current Worktime Widget
     *
     * @return ViewModel - View Object with Data from Controller
     * @since 1.0.0
     */
    public function currentAction() {
        $this->layout('layout/json');

        $aCurrentTimes = [];
        $sMasterUrl = CoreController::$aGlobalSettings['pos-master-url'];
        $sApiCall = $sMasterUrl.'/worktime/api/list?authkey='.CoreController::$aGlobalSettings['pos-master-authkey'].
            '&authtoken='.CoreController::$aGlobalSettings['pos-master-authtoken'].
            '&listmode=entity&limit=5';

        $sJSONRaw = file_get_contents($sApiCall);
        $oJson = json_decode($sJSONRaw);

        if(is_array($oJson->results)) {
            $aCurrentTimes = $oJson->results;
        }

        return new ViewModel([
            'aCurrentTimes' => $aCurrentTimes,
        ]);
    }


    /**
     * List Worktime for a specific month and employee
     *
     * @return ViewModel - View Object with Data from Controller
     * @since 1.0.0
     */
    public function monthAction() {
        $this->layout('layout/json');

        $oRequest = $this->getRequest();

        if($oRequest->isPost()) {
            $iEmployeeID = $oRequest->getPost('employee_id');
            $iMonth = str_pad($oRequest->getPost('month'),2,'0',STR_PAD_LEFT);

            $aCurrentTimes = [];
            $sMasterUrl = CoreController::$aGlobalSettings['pos-master-url'];
            $sApiCall = $sMasterUrl.'/worktime/api/list?authkey='.CoreController::$aGlobalSettings['pos-master-authkey'].
                '&authtoken='.CoreController::$aGlobalSettings['pos-master-authtoken'].
                '&listmode=entity&filter=["created_by","wtmonth"]&filtervalue=["'.$iEmployeeID.'","'.$iMonth.'"]';
            $sJSONRaw = file_get_contents($sApiCall);
            $oJson = json_decode($sJSONRaw);

            if(is_array($oJson->results)) {
                $aCurrentTimes = $oJson->results;
            }

            $sMonth = strftime('%B',strtotime(date('Y',time()).'-'.$iMonth.'-01'));

            return new ViewModel([
                'aCurrentTimes' => $aCurrentTimes,
                'sMonth' => $sMonth,
            ]);
        }
    }
}
