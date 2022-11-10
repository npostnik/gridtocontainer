<?php
declare(strict_types=1);

namespace NP\Gridtocontainer\Controller;

use B13\Container\Tca\Registry;
use NP\Gridtocontainer\Domain\Repository\MigrationRepository;
use TYPO3\CMS\Backend\View\BackendTemplateView;
use TYPO3\CMS\Extbase\Mvc\Controller\ActionController;
use TYPO3\CMS\Extbase\Mvc\View\ViewInterface;

class MigrationController extends ActionController
{

    /**
     * Backend Template Container
     *
     * @var string
     */
    protected $defaultViewObjectName = \TYPO3\CMS\Backend\View\BackendTemplateView::class;

    /**
     * @var MigrationRepository
     */
    protected $migrationRepository = null;

    /**
     * @var array
     */
    protected $dataHandlerData;

    /**
     * @param MigrationRepository $migrationRepository
     * @return void
     */
    public function injectMigrationRepository(MigrationRepository $migrationRepository) {
        $this->migrationRepository = $migrationRepository;
    }

    protected function initializeView(ViewInterface $view)
    {
        /** @var BackendTemplateView $view */
        parent::initializeView($view);
    }

    public function startAction()
    {
        $gridelementTypes = $this->findGridelementTypes();
        $containerTypes = $this->findContainerTypes();
        $this->view->assign('gridelementTypes', $gridelementTypes);
        $this->view->assign('containerTypes', $containerTypes);
    }

    /**
     * @return void
     */
    public function processAction()
    {
        if(!$this->request->hasArgument('gridelement')) {
            $this->addFlashMessage('Bitte Gridelement wählen');
            $this->redirect('start');
        }

        if(!$this->request->hasArgument('container')) {
            $this->addFlashMessage('Bitte Container wählen');
            $this->redirect('start');
        }

        $sourceGridelement = $this->request->getArgument('gridelement');
        $targetContainer = $this->request->getArgument('container');
        if($this->request->hasArgument('max')) {
            $limit = (int)$this->request->getArgument('max');
        }

        $this->dataHandlerData = [];
        $this->dataHandlerData['tt_content'] = [];

        $gridelements = $this->migrationRepository->findByGridelementLayout($sourceGridelement, $limit);
        foreach($gridelements as $element) {
            $nestedContent = $this->migrationRepository->findByParentGridelement($element['uid']);

            // update nested content elements
            foreach($nestedContent as $content) {
                $this->dataHandlerData['tt_content'][$content['uid']] = [
                    'colPos' => $content['tx_gridelements_columns'],
                    'tx_container_parent' => $content['tx_gridelements_container'],
                    'tx_gridelements_container' => 0,
                ];
            }

            // update grid element
            $this->dataHandlerData['tt_content'][$element['uid']] = [
                'CType' => $targetContainer,
                'tx_gridelements_backend_layout' => '',
            ];

            $this->addFlashMessage('Gridelement '.$element['uid'].' with '.count($nestedContent).' content elements migrated');
        }

        $dataHandler = \TYPO3\CMS\Core\Utility\GeneralUtility::makeInstance(\TYPO3\CMS\Core\DataHandling\DataHandler::class);
        $dataHandler->start($this->dataHandlerData, []);
        $dataHandler->process_datamap();

    }

    protected function findContainerTypes()
    {
        /** @var Registry $containerRegistry */
        $containerRegistry = \TYPO3\CMS\Core\Utility\GeneralUtility::makeInstance(\B13\Container\Tca\Registry::class);
        return $containerRegistry->getRegisteredCTypes();

    }

    protected function findGridelementTypes()
    {
        $gridelementRecords = $this->migrationRepository->findGridelementRecords();
        $gridelementTypes = [];
        foreach($gridelementRecords as $record) {
            $layout = $record['tx_gridelements_backend_layout'];
            if(!array_key_exists($layout, $gridelementTypes)) {
                $gridelementTypes[$layout] = [];
                $gridelementTypes[$layout]['count'] = 0;
                $gridelementTypes[$layout]['uid'] = [];
            }
            $gridelementTypes[$layout]['count']++;
            $gridelementTypes[$layout]['uid'][] = $record['uid'];
        }
        return $gridelementTypes;
    }



}
