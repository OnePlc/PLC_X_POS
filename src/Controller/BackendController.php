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
        $oJson = json_decode($sJSONRaw);

        if(is_object($oJson->oJob)) {
            $oJob = $oJson->oJob;
        }

        /**

        $oJob = $this->aPluginTbls['job']->getSingle($iJobID);
        $aPositions = [];
        $oPositionsDB = $this->aPluginTbls['job-position']->fetchAll(false, ['job_idfs' => $iJobID]);
        if(count($oPositionsDB) > 0) {
            foreach($oPositionsDB as $oPos) {
                $oPos->oArticle = $this->oTableGateway->getSingle($oPos->article_idfs);
                $aPositions[] = $oPos;
            }
        }
        $oJob->aPositions = $aPositions;
         *
         * **/

        return new ViewModel([
            'oJob' => $oJob,
        ]);
    }

    public function confirmAction(){
        $oRequest = $this->getRequest();

        if($oRequest->isPost()) {
            /**
            $iJobID = $oRequest->getPost('job_id');
            $sTimeVal = (int)$oRequest->getPost('deliverytime_est')*60;

            $this->aPluginTbls['job']->updateAttribute('state_idfs', 17, 'Job_ID', $iJobID);
            $this->aPluginTbls['job']->updateAttribute('deliverytime_est', date('Y-m-d H:i:s', time()+$sTimeVal), 'Job_ID', $iJobID);

             * **/
            return $this->redirect()->toRoute('touchscreen', []);
        }
    }

    public function touchscreenAction() {
        # Set layout
        $this->layout('layout/touchscreen');

        if(!isset(CoreController::$oSession->oUser)) {
            $sUser = 'admin@1plc.ch';
            $oUser = $this->aPluginTbls['user']->getSingle($sUser, 'email');
            CoreController::$oSession->oUser = $oUser;
        }

        return new ViewModel([]);
    }

    public function worktimeAction() {
        # Set layout
        $this->layout('layout/touchscreen');

        if(!isset(CoreController::$oSession->oUser)) {
            $sUser = 'admin@1plc.ch';
            $oUser = $this->aPluginTbls['user']->getSingle($sUser, 'email');
            CoreController::$oSession->oUser = $oUser;
        }

        $aCurrentTimes = [];
        /**
        $oWtTbl = new TableGateway('worktime', CoreController::$oDbAdapter);
        $aCurrentTimes = $oWtTbl->select();
         * **/

        return new ViewModel([
            'aCurrentTimes' => $aCurrentTimes,
        ]);
    }

    public function cashregisterAction()
    {
        # Set layout
        $this->layout('layout/touchscreen');

        if(!isset(CoreController::$oSession->oUser)) {
            $sUser = 'admin@1plc.ch';
            $oUser = $this->aPluginTbls['user']->getSingle($sUser, 'email');
            CoreController::$oSession->oUser = $oUser;
        }

        $aArticles = [];
        /**
        $oArticlesDB = $this->aPluginTbls['article']->fetchAll(false, []);
        $aArticles = [];
        if(count($oArticlesDB) > 0) {
            foreach($oArticlesDB as $oArt) {
                $aArticles[] = $oArt;
            }
        } **/

        return new ViewModel([
            'aArticles' => $aArticles,
        ]);
    }
}
