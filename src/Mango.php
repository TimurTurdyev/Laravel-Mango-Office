<?php
namespace TimurTurdyev\MangoOffice;

use TimurTurdyev\MangoOffice\MangoApi;
use Illuminate\Support\Facades\Config;
/**
 * Список методов для работы с API mango-office
 *
 * @author Ivan Alexandrov <yahve1989@gmail.com>
 */
class Mango extends MangoApi
{
    /**
     * Уникальный код АТС
     *
     * @var string $apiKey
     */
    private $apiKey = null;

    /**
     * Ключ для создания подписи
     *
     * @var string $apiSalt
     */
    private $apiSalt = null;

    public function __construct()
    {
        $this->apiKey = config('mangooffice.api_key');
        $this->apiSalt = config('mangooffice.api_salt');

        if (empty($this->apiKey) or empty($this->apiSalt)) {
            throw new Exception('bad request', 400, null);
        }
    }

    /**
     * Mетод авторизации для манго
     * @param array $request данные запроса
     * @param string $apiKey Уникальный код АТС
     * @param string $apiSalt Ключ для создания подписи
     * @return array
     */
    private function authParam($request)
    {
        $json = json_encode($request, JSON_UNESCAPED_SLASHES);
        $sign = hash('sha256', $this->apiKey . $json . $this->apiSalt);
        return ['vpbx_api_key' => $this->apiKey,
            'sign' => $sign,
            'json' => $json,
        ];
    }

    /**
     * метод конвертации данных
     * @param string $csvData данные в формате CSV
     * @return array
     */
    private function csvToArray($csvData)
    {
        $lines = str_getcsv($csvData, "\n");

        foreach ($lines as $key => $line) {
            $array = str_getcsv($line, ';');
            $records = array_filter(explode(',', trim($array[0], '[]')));
            $arResult[$key]['records'] = $records;
            $arResult[$key]['start'] = $array[1];
            $arResult[$key]['finish'] = $array[2];
            $arResult[$key]['answer'] = $array[3];
            $arResult[$key]['from_extension'] = $array[4];
            $arResult[$key]['from_number'] = $array[5];
            $arResult[$key]['to_extension'] = $array[6];
            $arResult[$key]['to_number'] = $array[7];
            $arResult[$key]['disconnect_reason'] = $array[8];
            $arResult[$key]['location'] = $array[9];
            $arResult[$key]['line_number'] = $array[10];
            $arResult[$key]['entry_id'] = $array[11];
        }

        return $arResult;
    }

    /**
     * получает список звонков за выбранный период
     * @param string $dateFrom начальная дата
     * @param string $dateTo конечная дата
     * @param integer $extension внутренний номер абонента
     * @return array || null
     */
    public function reportList($dateFrom, $dateTo, $extension = null)
    {
        $request = [];

        if (empty($dateFrom) or empty($dateTo)) {
            throw new Exception('bad request', 400, null);
        }

        if (!empty($extension)) {
            $request['call_party'] = ['extension' => $extension];
        }

        $request['date_from'] = $dateFrom;
        $request['date_to'] = $dateTo;
        $request['fields'] = implode(',', [
            'records',
            'start',
            'finish',
            'answer',
            'from_extension',
            'from_number',
            'to_extension',
            'to_number',
            'disconnect_reason',
            'location',
            'line_number',
            'entry_id',
        ]);

        $init = MangoApi::init();
        $data = $init->setBaseUri('https://app.mango-office.ru')
            ->setPath('/vpbx/stats/request')
            ->setMethod('POST')
            ->setFormParams($this->authParam($request))
            ->execute();
        $response = $data->client->getBody()->getContents();
        /*
         * sleep(5);
         * Потому что
         * - В ответе на запрос приходит ключ, с помощью которого можно будет получить статистику по завершению ее построения.
         * - Пример ответа: {"key":"b3Z7pivetID7uPJCFWMmSaFTtTO3meavk7h6caDaw3dSX++wPR/ZIntjIfssKf/B+DvIt8hPJ ReV8v4MYspQQA=="}
         *  Смотреть - https://www.mango-office.ru/support/integratsiya-api/spisok_integratsiy/emulyator_api_virtualnoy_ats/#/API%20%D0%A1%D1%82%D0%B0%D1%82%D0%B8%D1%81%D1%82%D0%B8%D0%BA%D0%B0/post_stats_request
         */
        sleep(5);
        $data = $init->setBaseUri('https://app.mango-office.ru')
            ->setPath('/vpbx/stats/result')
            ->setMethod('POST')
            ->setFormParams($this->authParam(json_decode($response)))
            ->execute();
        $csvData = $data->client->getBody()->getContents();

        if ($ResponseJson = json_decode($csvData)) {
            return $ResponseJson;
        }

        if (strlen($csvData) > 0) {
            return $this->csvToArray($csvData);
        }

        return null;
    }

    /**
     * получает запись разговора
     * @param string $recordingId уникальный идентификатор записи
     * @param string $action download | play
     * @return mp3 file | array
     */
    public function downloadAudio($recordingId, $action = 'download')
    {
        if (empty($recordingId)) {
            throw new Exception('bad request', 400, null);
        }

        $init = MangoApi::init();
        $data = $init->setBaseUri('https://app.mango-office.ru')
            ->setPath('/vpbx/queries/recording/post')
            ->setMethod('POST')
            ->setFormParams($this->authParam([
                'recording_id' => $recordingId,
                'action' => $action,
            ]))->execute();
        $recording = $data->client->getBody()->getContents();

        if ($ResponseJson = json_decode($recording)) {
            return $ResponseJson;
        }

        return $recording;
    }

    /**
     * получает список пользовател
     * @param integer $extension внутренний номер абонента
     * @return array
     */
    public function userList($extension = null)
    {
        $param = [];

        if (!empty($extension)) {
            $param = ['extension' => $extension];
        }

        $init = MangoApi::init();
        $data = $init->setBaseUri('https://app.mango-office.ru')
            ->setPath('/vpbx/config/users/request')
            ->setMethod('POST')
            ->setFormParams($this->authParam($param))->execute();
        $response = $data->client->getBody()->getContents();

        return json_decode($response);
    }
}
