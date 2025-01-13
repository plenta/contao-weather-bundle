<?php

declare(strict_types=1);

/**
 *
 * @copyright     Copyright (c) 2024, Plenta.io
 * @author        Plenta.io <https://plenta.io>
 * @link          https://github.com/plenta/
 */

namespace Plenta\ContaoWeatherBundle\Helper;

use Contao\CoreBundle\Framework\ContaoFramework;
use Contao\System;
use Contao\StringUtil;

class OpenWeatherHelper
{
    private string $key = '';

    private int $fileTime = 60;

    private string $filePath;

    private string $country = 'de';

    private string $language = 'de';

    private $units = 'metric';

    private $url = 'https://api.openweathermap.org/data/2.5/weather?';

    public function __construct()
    {
        $this->filePath = System::getContainer()->getParameter('contao.web_dir'). '/share/wetter.json';
    }

    public function setApiKey(string $key): void
    {
        $this->key = $key;
    }
    public function getByZipcode($zipcode)
    {
        return $this->getWeatherData('zip='.$zipcode.','.$this->country);
    }

    public function getByCity($city)
    {
        return $this->getWeatherData('q='.$city.','.$this->country);
    }

    public function getByCoordinates($lat, $lon)
    {
        return $this->getWeatherData('lat='.$lat.'&lon='.$lon);
    }

    public function decodeJson($json)
    {
        if (null !== $json) {
            return json_decode(utf8_encode($json));
        }
    }

    public function writeFile($json): void
    {
        if (\file_exists($this->filePath)) {
            if (time() - $this->getFileTimeStamp() > $this->fileTime * 60) {
                \file_put_contents($this->filePath, $json);
            }
        } else {
            \file_put_contents($this->filePath, $json);
        }
    }

    public function readFile()
    {
        if (file_exists($this->filePath)) {
            return file_get_contents($this->filePath);
        }
    }

    public function getFileTimeStamp()
    {
        if (file_exists($this->filePath)) {
            return filemtime($this->filePath);
        }
    }

    public function getTimeDifference()
    {
        return time() - $this->getFileTimeStamp();
    }

    public function readFileDecoded($filePath)
    {
        if (\file_exists($this->filePath)) {
            return \json_decode(\utf8_encode(\file_get_contents($this->filePath)));
        }
    }

    public function getInfo($json, string $string)
    {
        if (null !== $json && '' !== $string) {
            return match ($string) {
                'coord'        => $json->coord,
                'lon'          => $json->coord->lon ?? null,
                'lat'          => $json->coord->lat ?? null,
                'weather'      => $json->weather ?? null,
                'main'         => $json->weather[0]->main ?? null,
                'description'  => $json->weather[0]->description ?? null,
                'icon'         => $json->weather[0]->icon ?? null,
                'base'         => $json->base ?? null,
                'temp'         => $json->main->temp ?? null,
                'pressure'     => $json->main->pressure ?? null,
                'humidity'     => $json->main->humidity ?? null,
                'temp_min'     => $json->main->temp_min ?? null,
                'temp_max'     => $json->main->temp_max ?? null,
                'visibility'   => $json->visibility ?? null,
                'wind'         => $json->wind ?? null,
                'speed'        => $json->wind->speed ?? null,
                'deg'          => $json->wind->deg ?? null,
                'country'      => $json->sys->country ?? null,
                'name'         => $json->name ?? null,
                'cod'          => $json->cod ?? null,
                default        => null,
            };
            /*
            switch ($string) {
                case 'coord':
                    return $json->coord;
                    break;
                case 'lon':
                    return $json->coord->lon;
                    break;
                case 'lat':
                    return $json->coord->lat;
                    break;
                case 'weather':
                    return $json->weather;
                    break;
                case 'main':
                    return $json->weather[0]->main;
                    break;
                case 'description':
                    return $json->weather[0]->description;
                    break;
                case 'icon':
                    return $json->weather[0]->icon;
                    break;
                case 'base':
                    return $json->base;
                    break;
                case 'temp':
                    return $json->main->temp;
                    break;
                case 'pressure':
                    return $json->main->pressure;
                    break;
                case 'humidity':
                    return $json->main->humidity;
                    break;
                case 'temp_min':
                    return $json->main->temp_min;
                    break;
                case 'temp_max':
                    return $json->main->temp_max;
                    break;
                case 'visibility':
                    return $json->visibility;
                    break;
                case 'wind':
                    return $json->wind;
                    break;
                case 'speed':
                    return $json->wind->speed;
                    break;
                case 'deg':
                    return $json->wind->deg;
                    break;
                case 'country':
                    return $json->sys->country;
                    break;
                case 'name':
                    return $json->name;
                    break;
                case 'cod':
                    return $json->cod;
                    break;
            }
            */
        }
    }

    private function getWeatherData($params)
    {
        $url = $this->url.''.$params.'&units='.$this->units.'&lang='.$this->language.'&appid='.$this->key;

        $curl = curl_init();

        curl_setopt($curl, CURLOPT_HTTPHEADER, []);
        curl_setopt($curl, CURLOPT_HEADER, false);
        curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($curl, CURLOPT_URL, $url);
        curl_setopt($curl, CURLOPT_TIMEOUT, 30);

        $json = curl_exec($curl);

        if (!$json) {
            curl_close($curl);

            return null;
        }

        curl_close($curl);

        return $json;
    }

    public function getFilePath()
    {
        return StringUtil::stripRootDir(
            System::getContainer()->getParameter('contao.web_dir')
            ).'/share/'.'wetter.xml';
    }
}
