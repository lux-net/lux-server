<?php

namespace AgzHack\Api\Controller;

use AgzHack\Auth\Service\AuthService;
use AgzHack\Geo\Domain\Model\Coordinate;
use AgzHack\Lux\Domain\Model\LightMarker;
use AgzHack\Lux\Domain\Repository\LightMarkerRepository;
use AgzHack\Lux\Service\LightMarkerService;
use Neos\Flow\Mvc\Controller\RestController;
use Neos\Flow\Mvc\View\JsonView;
use Neos\Flow\Annotations as Flow;
use Neos\Flow\Property\TypeConverter\PersistentObjectConverter;

class LightMarkersController extends RestController
{
    protected $defaultViewObjectName = JsonView::class;

    protected $resourceArgumentName = 'lightMarker';


    /**
     * @var LightMarkerRepository
     * @Flow\Inject
     */
    protected $lightMarkerRepository;

    /**
     * @var LightMarkerService
     * @Flow\Inject
     */
    protected $lightMarkerService;


    public function initializeListAction()
    {
        foreach (['northEast', 'southWest'] as $arg) {
            $this->arguments[$arg]->getPropertyMappingConfiguration()
                ->setTypeConverterOption(
                    PersistentObjectConverter::class,
                    PersistentObjectConverter::CONFIGURATION_CREATION_ALLOWED,
                    true
                )->allowAllProperties();
        }
    }

    /**
     * @param Coordinate $northEast
     * @param Coordinate $southWest
     * @throws \Neos\Flow\Persistence\Exception\InvalidQueryException
     */
    public function listAction(Coordinate $northEast, Coordinate $southWest)
    {
        $this->view->setVariablesToRender(array('lightMarkers'));

//        $lightMarkers = $this->lightMarkerRepository->findByBoundaries($northEast, $southWest);
        $lightMarkers = $this->lightMarkerRepository->findAll();

        $this->view->setConfiguration(
            array(
                'lightMarkers' => array(
                    '_descendAll' => $this->getViewConfiguration()
                )
            )
        );

        $this->view->assign('lightMarkers', $lightMarkers);
    }

    /**
     * @param Coordinate $coordsA
     * @param Coordinate $coordsB
     */
    public function getKmlAction(Coordinate $coordsA, Coordinate $coordsB)
    {
    }

    /**
     * Allow modification of resources in updateAction()
     *
     * @return void
     */
    protected function initializeCreateAction()
    {
        $this->arguments[$this->resourceArgumentName]->getPropertyMappingConfiguration()
            ->setTypeConverterOption(
                PersistentObjectConverter::class,
                PersistentObjectConverter::CONFIGURATION_CREATION_ALLOWED,
                true
            )->allowAllProperties();

        $this->arguments[$this->resourceArgumentName]->getPropertyMappingConfiguration()
            ->forProperty('coordinate')
            ->allowAllProperties();
    }


    /**
     * @param LightMarker $lightMarker
     */
    public function createAction(LightMarker $lightMarker)
    {
        $this->view->setVariablesToRender(array('lightMarker'));

        try {
            $discreteLightMarker = $this->lightMarkerService->addDiscreteMarker($lightMarker);

            $this->view->setConfiguration(array(
                'lightMarker' => $this->getViewConfiguration()
            ));

            $this->view->assign('lightMarker', $discreteLightMarker);
        } catch (\Exception $e) {
            $this->response->setStatus(500);
            $this->view->assign('lightMarker', array('lightMarker' => $e->getMessage()));
        }
    }


    /**
     * @param LightMarker $lightMarker
     */
    public function deleteAction(LightMarker $lightMarker)
    {
        try {
            $this->lightMarkerRepository->remove($lightMarker);
        } catch (\Exception $e) {
            $this->response->setStatus(500);
            $this->view->assign('lightMarker', array('error' => $e->getMessage()));
        }
    }

    private function getViewConfiguration()
    {
        return array(
            '_exposeObjectIdentifier' => true,
            '_exposedObjectIdentifierKey' => '__identity',
            '_descend' => [
                'coordinate' => [

                ]
            ]
        );
    }
}
