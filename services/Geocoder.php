<?php

declare(strict_types=1);

namespace app\services;

use Yii;
use yii\httpclient\Client;

/**
 * Provides geocoding services.
 */
class Geocoder
{
    /**
     * Returns coordinates for the specified location.
     *
     * @param string $location Location to geocode.
     *
     * @return array{longitude: float, latitude: float}|null Coordinates
     * or null if the location could not be found.
     */
    public function getCoordinates(string $location): ?array
    {
        $client = new Client();

        $response = $client->createRequest()
            ->setMethod('GET')
            ->setUrl('https://geocode-maps.yandex.ru/v1/')
            ->setData([
                'apikey' => Yii::$app->params['yandexGeocoderApiKey'],
                'geocode' => $location,
                'lang' => 'ru_RU',
                'format' => 'json',
            ])
            ->send();

        if (!$response->isOk) {
            return null;
        }

        $data = $response->data;

        $pos = $data[
            'response'
        ][
            'GeoObjectCollection'
        ][
            'featureMember'
        ][0][
            'GeoObject'
        ][
            'Point'
        ][
            'pos'
        ] ?? null;

        if ($pos === null) {
            return null;
        }

        $coordinates = preg_split('/\s+/', trim($pos));

        if ($coordinates === false || count($coordinates) < 2) {
            return null;
        }

        return [
            'longitude' => (float) $coordinates[0],
            'latitude' => (float) $coordinates[1],
        ];
    }
}
