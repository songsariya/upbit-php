<?php

use Exception;
use Firebase\JWT\JWT;

/**
 * Upbit Api 
 * Upbit 서버와 통신을 하는 메소드로 구성
 */
class UpbitApi
{

    const POST = "POST";
    const GET = "GET";
    const PUT = "PUT";
    const DELETE = "DELETE";

    const HTTP_CODE_200 = "200";

    const SIDE_BID = "bid"; // 매수
    const SIDE_ASK = "ask"; // 매도

    const ORD_TYPE_LIMIT = "limit"; // 지정가 주문
    const ORD_TYPE_PRICE = "price"; // 시장가 주문 (매수)
    const ORD_TYPE_MARKET = "market"; // 시장가 주문 (매도)


    private $accessKey;
    private $secretKey;
    private \GuzzleHttp\Client $client;

    /**
     * 생성자
     */
    public function __construct(string $accessKey, string $secretKey)
    {
        $this->accessKey = $accessKey;
        $this->secretKey = $secretKey;
        $this->client = new \GuzzleHttp\Client(['base_uri' => 'https://api.upbit.com/v1/']);
    }

    /**
     * 마켓 코드 조회
     *
     * @return array
     */
    public function getMarketList(): array
    {
        $uri = "market/all?isDetails=false";

        return $this->send(self::GET, $uri);
    }

    /**
     * 분 Candle 정보 조회
     *
     * @param int $unit 분 단위 1 3 5 15 10 30 60 240 분 캔들 조회
     * @param string $market 마켓 코드
     * @param string|null $to 마지막 캔들 시각 포맷 (yyyy-MM-dd'T'HH:mm:ss'Z') 또는 (yyyy-MM-dd HH:mm:ss), 빈 값 요청시 가장 최근 캔들
     * @param int|null $count 캔들 갯수(최대 200개)
     * @return array
     */
    public function getCandlesMinutes(int $unit, string $market, ?string $to = null, ?int $count = null): array
    {
        $uri = "candles/minutes/{$unit}?market={$market}";
        $uri .= $to ? "&to={$to}" : "";
        $uri .= $count ? "&count={$count}" : "";
        return $this->send(self::GET, $uri);
    }

    /**
     * 일 Candle 정보 조회
     *
     * @param string $market 마켓 코드
     * @param integer|null $count 캔들 갯수(최대 200개)
     * @param string|null $to 마지막 캔들 시각 포맷 (yyyy-MM-dd'T'HH:mm:ss'Z') 또는 (yyyy-MM-dd HH:mm:ss), 빈 값 요청시 가장 최근 캔들
     * @param string|null $convertingPriceUnit 종가 환산 화폐 단위 (생략 가능 KRW로 명시할 시 원화 환산 가격을 반환)
     * @return array
     */
    public function getCandlesDays(string $market, ?int $count = null, ?string $to = null, ?string $convertingPriceUnit): array
    {
        $uri = "candles/days?market={$market}";
        $uri .= $count ? "&count={$count}" : "";
        $uri .= $to ? "&to={$to}" : "";
        $uri .= $convertingPriceUnit ? "&convertingPriceUnit={$convertingPriceUnit}" : "";

        return $this->send(self::GET, $uri);
    }

    /**
     * 주 Candle 정보 조회
     *
     * @param string $market 마켓 코드
     * @param integer|null $count 캔들 갯수(최대 200개)
     * @param string|null $to 마지막 캔들 시각 포맷 (yyyy-MM-dd'T'HH:mm:ss'Z') 또는 (yyyy-MM-dd HH:mm:ss), 빈 값 요청시 가장 최근 캔들
     * @return array
     */
    public function getCandlesWeeks(string $market, ?int $count = null, ?string $to = null): array
    {
        $uri = "candles/weeks?market={$market}";
        $uri .= $count ? "&count={$count}" : "";
        $uri .= $to ? "&to={$to}" : "";

        return $this->send(self::GET, $uri);
    }

    /**
     * 월 Candle 정보 조회
     *
     * @param string $market 마켓 코드
     * @param integer|null $count 캔들 갯수(최대 200개)
     * @param string|null $to 마지막 캔들 시각 포맷 (yyyy-MM-dd'T'HH:mm:ss'Z') 또는 (yyyy-MM-dd HH:mm:ss), 빈 값 요청시 가장 최근 캔들
     * @return array
     */
    public function getCandlesMonths(string $market, ?int $count = null, ?string $to = null): array
    {
        $uri = "candles/months?market={$market}";
        $uri .= $count ? "&count={$count}" : "";
        $uri .= $to ? "&to={$to}" : "";

        return $this->send(self::GET, $uri);
    }

    /**
     * 계좌정보 조회
     *
     * @return array
     */
    public function getAccounts(): array
    {
        $uri = "accounts";
        return $this->send(self::GET, $uri);
    }

    /**
     * 주문
     *
     * @param string $market
     * @param string $side
     * @param string $ord_type
     * @param float|null $volume
     * @param float|null $price
     * @param string|null $identifier
     * @return array
     */
    public function order(string $market, string $side, string $ord_type, ?float $volume = null, ?float $price = null, ?string $identifier = null): array
    {
        $uri = "orders";

        // 검증
        if ($side == self::SIDE_BID && $ord_type == self::ORD_TYPE_MARKET) {
            throw new Exception("매수 주문 인 경우 ord_type 이 market이 될 수 없습니다.");
        } else if ($side == self::SIDE_ASK && $ord_type == self::ORD_TYPE_PRICE) {
            throw new Exception("매도 주문 인 경우 ord_type 이 price가 될 수 없습니다.");
        }

        if ($side == self::SIDE_BID && $ord_type == self::ORD_TYPE_PRICE && (empty($price) || $price == 0)) {
            throw new Exception("시장가 매수 주문 인 경우 price 의 값이 필요로 합니다.");
        } else if ($side == self::SIDE_ASK && $ord_type == self::ORD_TYPE_MARKET && (empty($volume) || $volume == 0)) {
            throw new Exception("시장가 매도 주문 인 경우 volume 의 값이 필요로 합니다.");
        }


        if ($identifier == null) {
            $identifier = "{$market}_{$side}_{$ord_type}_" . date("Ymdhis");
        }

        $queryParam = [
            "market" => $market,
            "side" => $side,
            "volume" => $volume ?? null,
            "price" => $price ?? null,
            "ord_type" => $ord_type,
            "identifier" => $identifier,
        ];
        return $this->send(self::POST, $uri, $queryParam);
    }

    /**
     * 주문 취소
     *
     * @param string|null $uuid
     * @param string|null $identifier
     * @return array
     */
    public function orderCancel(?string $uuid = null, ?string $identifier = null): array
    {
        $uri = "order";

        // 검증
        if ($uuid == null && $identifier == null) {
            throw new Exception("uuid 또는 identifier 둘 중에 하나는 필요로 합니다.");
        }

        $queryParam = [
            "uuid" => $uuid,
            "identifier" => $identifier,
        ];

        return $this->send(self::DELETE, $uri, $queryParam);
    }

    /**
     * 주문 가능 정보
     *
     * @param string $market
     * @return array
     */
    public function orderChance(string $market): array
    {
        $uri = "orders/chance";
        $queryParam = [
            "market" => $market,
        ];

        return $this->send(self::GET, $uri, $queryParam);
    }

    /**
     * 주문 단건 조회
     *
     * @param string|null $uuid
     * @param string|null $identifier
     * @return array
     */
    public function orderInfo(?string $uuid = null, ?string $identifier = null): array
    {
        $uri = "order";

        // 검증
        if ($uuid == null && $identifier == null) {
            throw new Exception("uuid 또는 identifier 둘 중에 하나는 필요로 합니다.");
        }

        $queryParam = [
            "uuid" => $uuid,
            "identifier" => $identifier,
        ];

        return $this->send(self::GET, $uri, $queryParam);
    }


    /**
     * 주문 내역 조회
     *
     * @param string|null $market
     * @param array|null $uuids
     * @param array|null $identifiers
     * @param string|null $state
     * @param array|null $states
     * @param integer|null $page
     * @param integer|null $limit
     * @param string|null $order_by
     * @return array
     */
    public function orderList(?string $market = null, ?array $uuids = null, ?array $identifiers = null, ?string $state = null, ?array $states = null, ?int $page = null, ?int $limit = null, ?string $order_by = null): array
    {
        $uri = "orders";

        $queryParam = [
            "market" => $market,
            "uuids" => $uuids,
            "identifiers" => $identifiers,
            "state" => $state,
            "states" => $states,
            "page" => $page,
            "limit" => $limit,
            "order_by" => $order_by,
        ];

        // 값이 없으면 키를 제거해준다.
        $queryParam = array_filter($queryParam);

        print_r($queryParam);

        if (empty($queryParam)) {
            $queryParam = null;
        }

        return $this->send(self::GET, $uri, $queryParam);
    }

    /**
     * UPBIT 토큰 만드는 함수
     *
     * @param array|null $queryParam
     * @return string
     */
    private function makeToken(?array $queryParam = null): string
    {

        $addPayload = [];
        if ($queryParam !== null) {

            $queryString = http_build_query($queryParam);

            $query_hash =  hash('sha512', $queryString, false);

            $addPayload = [
                "query_hash" => $query_hash,
                "query_hash_alg" => "SHA512",
            ];
        }

        $payload = [
            "access_key" => $this->accessKey,
            "nonce" => "SSR_" . uniqid(),
        ];

        $mergePayload = array_merge($payload, $addPayload);

        $token = JWT::encode($mergePayload, $this->secretKey);

        return $token;
    }


    /**
     * UPBIT API 서버와 통신
     *
     * @param string $method
     * @param string $uri
     * @param array|null $param
     * @return array
     */
    private function send(string $method = self::GET, string $uri, ?array $param = null): array
    {
        $token = $this->makeToken($param);

        $form_params = array();
        if ($param !== null) {
            $form_params = array('form_params' => $param);
        }

        // basic option
        $options = array(
            'headers' => [
                'Accept' => 'application/json',
                'Authorization' => "Bearer " . $token,
            ],
        );

        if (!empty($form_params)) {
            $options = array_merge($options, $form_params);
        }

        $response = $this->client->request($method, $uri, $options);

        if ($response->getStatusCode() != self::HTTP_CODE_200) {
            throw new Exception("Resopnse Code is not 200", 0);
        }

        return json_decode($response->getBody(), true);
    }
}
