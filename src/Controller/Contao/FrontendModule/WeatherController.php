<?php

declare(strict_types=1);

/**
 *
 * @copyright     Copyright (c) 2024, Plenta.io
 * @author        Plenta.io <https://plenta.io>
 * @link          https://github.com/plenta/
 */

namespace Plenta\ContaoWeatherBundle\Controller\Contao\FrontendModule;

use Plenta\ContaoWeatherBundle\Helper\OpenWeatherHelper;
use Contao\CoreBundle\Controller\FrontendModule\AbstractFrontendModuleController;
use Contao\CoreBundle\DependencyInjection\Attribute\AsFrontendModule;
use Contao\ModuleModel;
use Contao\Template;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

#[AsFrontendModule(type: 'plenta_weather', category: 'miscellaneous')]
class WeatherController extends AbstractFrontendModuleController
{
    public function __construct(protected OpenWeatherHelper $openWeatherHelper)
    {
    }

    function getResponse(Template $template, ModuleModel $model, Request $request): Response
    {
        $template->showWidget = false;

        $this->openWeatherHelper->setApiKey($model->plenta_weather_apikey);

        if (!empty($model->plenta_weather_lat) && !empty($model->plenta_weather_lng)) {
            $data = $this->openWeatherHelper->getByCoordinates($model->plenta_weather_lat, $model->plenta_weather_lng);
        } else {
            $data = $this->openWeatherHelper->getByCity($this->plenta_weather_location);
        }

        if (!is_null($data)) {
            $template->showWidget = true;

            $this->openWeatherHelper->writeFile($data);
            $json = $this->openWeatherHelper->decodeJson($this->openWeatherHelper->readFile());

            if (!empty($this->openWeatherHelper->getInfo($json, 'temperature'))) {
                $template->temp = number_format($this->openWeatherHelper->getInfo($json, 'temp'));
            }

            $template->icon = $this->openWeatherHelper->getInfo($json, 'icon');
        }

        return $template->getResponse();
    }
}