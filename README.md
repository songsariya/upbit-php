# upbit-php

PHP를 이용한 Upbit API 통신하기

---

### 필수 패키지

1. php-jwt
2. guzzlehttp/guzzle

```shell
$ composer require firebase/php-jwt
$ composer require guzzlehttp/guzzle
```



### 메소드

```php
<?php

/**
 * 생성자
 */
public function __construct(string $accessKey, string $secretKey)

/**
 * 마켓 코드 조회
 */
public function getMarketList(): array

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

/**
 * 주 Candle 정보 조회
 *
 * @param string $market 마켓 코드
 * @param integer|null $count 캔들 갯수(최대 200개)
 * @param string|null $to 마지막 캔들 시각 포맷 (yyyy-MM-dd'T'HH:mm:ss'Z') 또는 (yyyy-MM-dd HH:mm:ss), 빈 값 요청시 가장 최근 캔들
 * @return array
 */
public function getCandlesWeeks(string $market, ?int $count = null, ?string $to = null): array

/**
 * 월 Candle 정보 조회
 *
 * @param string $market 마켓 코드
 * @param integer|null $count 캔들 갯수(최대 200개)
 * @param string|null $to 마지막 캔들 시각 포맷 (yyyy-MM-dd'T'HH:mm:ss'Z') 또는 (yyyy-MM-dd HH:mm:ss), 빈 값 요청시 가장 최근 캔들
 * @return array
 */
public function getCandlesMonths(string $market, ?int $count = null, ?string $to = null): array

/**
 * 계좌정보 조회
 *
 * @return array
 */
public function getAccounts(): array

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

/**
 * 주문 취소
 *
 * @param string|null $uuid
 * @param string|null $identifier
 * @return array
 */
public function orderCancel(?string $uuid = null, ?string $identifier = null): array

/**
 * 주문 가능 정보
 *
 * @param string $market
 * @return array
 */
public function orderChance(string $market): array

/**
 * 주문 단건 조회
 *
 * @param string|null $uuid
 * @param string|null $identifier
 * @return array
 */
public function orderInfo(?string $uuid = null, ?string $identifier = null): array

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

/**
 * UPBIT 토큰 만드는 함수
 *
 * @param array|null $queryParam
 * @return string
 */
private function makeToken(?array $queryParam = null): string

/**
 * UPBIT API 서버와 통신
 *
 * @param string $method
 * @param string $uri
 * @param array|null $param
 * @return array
 */
private function send(string $method = self::GET, string $uri, ?array $param = null): array
```

