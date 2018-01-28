<?php

namespace AgzHack\LightMarkerImporter\Command;

use AgzHack\Geo\Domain\Model\Coordinate;
use AgzHack\Lux\Domain\Model\LightMarker;
use AgzHack\Lux\Service\LightMarkerService;
use Neos\Flow\Annotations as Flow;
use Neos\Flow\Cli\CommandController;
use Neos\Flow\Persistence\PersistenceManagerInterface;

/**
 * @Flow\Scope("singleton")
 */
class ImporterCommandController extends CommandController
{

    /**
     * @var LightMarkerService
     * @Flow\Inject
     */
    protected $lightMarkerService;

    /**
     * @Flow\Inject
     * @var PersistenceManagerInterface
     */
    protected $persistenceManager;

    public function ImportFromCsvFileCommand()
    {
        $row = 1;
        if (($handle = fopen(FLOW_PATH_PACKAGES . 'Application/AgzHack.LightMarkerImporter/Resources/novembrofsa.csv', "r")) !== false) {
            while (($data = fgetcsv($handle, 1000, ",")) !== false) {
                $row++;
                //2/11/2017 13:20

                $date = \DateTime::createFromFormat('d/m/Y H:i', $data[0]);
                $address = urlencode($data[1] . ', Feira de Santana, Bahia, Brasil');
                $result = file_get_contents("https://maps.googleapis.com/maps/api/geocode/json?address={$address}&key=AIzaSyDASp_574_fcn7CmSlsJ1kz55g2qHzJgHo", "r");
                $resultGoogle = json_decode($result, true)['results'];

                if (count($resultGoogle) == 0) {
                    continue;
                }
                $resultGoogle = $resultGoogle[0];

                if (!array_key_exists('geometry', $resultGoogle)) {
                    continue;
                }
                $coordinate = new Coordinate($resultGoogle['geometry']['location']['lat'], $resultGoogle['geometry']['location']['lng']);
                $lightMarker = new LightMarker($coordinate, false, $date);

                $this->lightMarkerService->addDiscreteMarker($lightMarker);
                $this->persistenceManager->persistAll();
            }
            fclose($handle);
        }
    }
}
