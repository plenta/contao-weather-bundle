<?php

declare(strict_types=1);

/**
 *
 * @copyright     Copyright (c) 2024, Plenta.io
 * @author        Plenta.io <https://plenta.io>
 * @link          https://github.com/plenta/
 */

namespace Plenta\ContaoWeatherBundle\Helper;

use Contao\System;
use Contao\StringUtil;
use Symfony\Component\HttpClient\HttpClient;

class OpenWeatherHelper
{
    private string $key = '';

    private int $fileTime = 60;

    private string $filePath;

    private string $country = 'de';

    private string $language = 'de';

    private $units = 'metric';

    private string $url = 'https://api.openweathermap.org/data/2.5/weather?';

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

    public function getInfo($json, string $string): mixed
    {
        if (null !== $json && '' !== $string) {
            return match ($string) {
                'coord' => $json->coord,
                'lon' => $json->coord->lon ?? null,
                'lat' => $json->coord->lat ?? null,
                'weather' => $json->weather ?? null,
                'main' => $json->weather[0]->main ?? null,
                'description' => $json->weather[0]->description ?? null,
                'icon' => $json->weather[0]->icon ?? null,
                'base' => $json->base ?? null,
                'temperature' => $json->main->temp ?? null,
                'pressure' => $json->main->pressure ?? null,
                'humidity' => $json->main->humidity ?? null,
                'temp_min' => $json->main->temp_min ?? null,
                'temp_max' => $json->main->temp_max ?? null,
                'visibility' => $json->visibility ?? null,
                'wind' => $json->wind ?? null,
                'speed' => $json->wind->speed ?? null,
                'deg' => $json->wind->deg ?? null,
                'country' => $json->sys->country ?? null,
                'name' => $json->name ?? null,
                'cod' => $json->cod ?? null,
                default => null,
            };
        }

        return null;
    }

    private function getWeatherData($params)
    {
        $url = sprintf(
            '%s%s&units=%s&lang=%s&appid=%s',
            $this->url,
            $params,
            $this->units,
            $this->language,
            $this->key
        );

        $client = HttpClient::create();

        try {
            $response = $client->request('GET', $url, [
                'headers' => [
                    'Accept' => 'application/json',
                ],
                'timeout' => 30,
            ]);

            dump($response);

            if (200 !== $response->getStatusCode()) {
                return null;
            }

            return $response->getContent();
        } catch (\Exception $e) {
            return null;
        }
    }

    public function getFilePath()
    {
        return StringUtil::stripRootDir(
            System::getContainer()->getParameter('contao.web_dir')
            ).'/share/'.'wetter.xml';
    }
}
