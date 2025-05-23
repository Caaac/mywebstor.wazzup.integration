<?

namespace Mywebstor\Wazzup\Integration\Rest\Wazzup;

use Bitrix\Main\Web\HttpClient;
use Bitrix\Rest\RestException;
use Mywebstor\Wazzup\Integration\Helper;

class Chanels extends \IRestService
{
  const NAMESPACE = 'mwi.wazzup.chanels';

  public static $methods = [
    self::NAMESPACE . '.get' => [__CLASS__, 'get'],
  ];


  /**
   * @param array $query | Valiable params:
   *      - filter
   * 
   * @throws RestException
   * @return array
   */
  public static function get($query)
  {
    $httpClient = new HttpClient(['socketTimeout' => 15]);

    $queryToWassup = $httpClient
      ->setHeaders(['Authorization' => 'Bearer ' . Helper::getApiKey()])
      ->get(Helper::CHANELS_URL);

    if ($errors = $httpClient->getError()) {
      throw new RestException(json_encode($errors), $httpClient->getStatus());
      return [];
    }

    $responce = json_decode($queryToWassup, true);

    $result = [];

    /* Filter does not work with array at responce item */
    if (!empty($query['filter']) && is_array($query['filter'])) {
      foreach ($responce as $index => $value) {
        $wasFiltered = false;

        foreach ($query['filter'] as $filterKey => $filterValue) {
          switch (is_array($filterValue)) {
            case true:
              $in = in_array($value[$filterKey], $filterValue);
              $wasFiltered = !$in ? true : $wasFiltered;
              break;
            case false:
              $wasFiltered = $value[$filterKey] != $filterValue ? true : $wasFiltered;
              break;
          }
        }

        if (!$wasFiltered) {
          $result[] = $value;
        }
      }
    } else {
      $result = $responce;
    }

    return $result;
  }
}
