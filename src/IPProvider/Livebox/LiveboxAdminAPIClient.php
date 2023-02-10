<?php

declare(strict_types=1);

namespace VPNDetector\IPProvider\Livebox;

use Symfony\Component\HttpClient\HttpClient;
use Symfony\Contracts\HttpClient\Exception\ClientExceptionInterface;
use Symfony\Contracts\HttpClient\Exception\DecodingExceptionInterface;
use Symfony\Contracts\HttpClient\Exception\ExceptionInterface;
use Symfony\Contracts\HttpClient\Exception\RedirectionExceptionInterface;
use Symfony\Contracts\HttpClient\Exception\ServerExceptionInterface;
use Symfony\Contracts\HttpClient\Exception\TransportExceptionInterface;
use Symfony\Contracts\HttpClient\HttpClientInterface;
use Symfony\Contracts\HttpClient\ResponseInterface;

final class LiveboxAdminAPIClient implements LiveboxAdminAPI
{
    public const LIVEBOX_URL_PARAM = 'url';
    public const LIVEBOX_USR_PARAM = 'username';
    public const LIVEBOX_PWD_PARAM = 'password';

    public const DEFAULT_URL      = 'http://192.168.1.1';
    public const DEFAULT_USER     = 'admin';
    public const DEFAULT_PASSWORD = 'admin';

    public const WEBSERVICE_PATH = '/ws';

    private const BASE_COOKIES = [
        'cdda1e19/accept-language' => 'fr-FR,fr',
        'UILang'                   => 'fr',
    ];
    private const BASE_HEADERS = [
        'Content-Type' => 'application/x-sah-ws-4-call+json',
    ];

    private ?string $contextId = null;

    /** @var array<string, string>|null */
    private ?array  $sessionCookie = null;

    private function __construct(
        private readonly HttpClientInterface $httpClient,
        private readonly string $baseUrl,
        private readonly string $username,
        private readonly string $password,
    ) {
    }

    /**
     * @throws LiveboxAdminAPIException
     */
    public function authenticate(): void
    {
        if ($this->isAuthenticated()) {
            return;
        }

        try {
            $this->doAuthenticate();
        } catch (ExceptionInterface $e) {
            throw LiveboxAdminAPIException::authenticationFailed($e);
        }
    }

    /**
     * @return array<string, array<string, string>>
     *
     * @throws LiveboxAdminAPIException
     */
    public function getWANStatus(): array
    {
        try {
            return $this->authenticatedCall(
                [
                    'service'    => 'NMC',
                    'method'     => 'getWANStatus',
                    'parameters' => [],
                ]
            );
        } catch (ExceptionInterface|LiveboxAdminAPIException $e) {
            throw LiveboxAdminAPIException::from($e);
        }
    }

    public function isAuthenticated(): bool
    {
        return $this->contextId !== null && $this->sessionCookie !== null;
    }

    /**
     * @throws LiveboxAdminAPIException
     */
    private function ensureAuthentication(): void
    {
        if (!$this->isAuthenticated()) {
            $this->authenticate();
        }
    }

    /**
     * @param array<string, mixed> $payload
     *
     * @return array<string, array<string, string>>
     *
     * @throws ExceptionInterface
     * @throws LiveboxAdminAPIException
     */
    private function authenticatedCall(array $payload): array
    {
        $this->ensureAuthentication();

        return $this->call($payload)->toArray();
    }

    /**
     * @throws ExceptionInterface
     * @throws ClientExceptionInterface
     * @throws DecodingExceptionInterface
     * @throws RedirectionExceptionInterface
     * @throws ServerExceptionInterface
     * @throws TransportExceptionInterface
     */
    public function doAuthenticate(): void
    {
        $payload = [
            'service'    => 'sah.Device.Information',
            'method'     => 'createContext',
            'parameters' => [
                'applicationName' => 'webui',
                'username'        => $this->username,
                'password'        => $this->password,
            ],
        ];

        $response = $this->call(
            $payload,
            ['Authorization' => 'X-Sah-Login']
        );

        $content = $response->toArray();
        $headers = $response->getHeaders();

        $cookieChain                = $headers['set-cookie'][0];
        [$cookieName, $cookieValue] = explode('=', explode('; ', $cookieChain)[0]);
        $this->sessionCookie        = [$cookieName => $cookieValue];

        $this->contextId = $content['data']['contextID'] ?? null;
    }

    /**
     * @param array<string, mixed>  $payload
     * @param array<string, string> $headers
     *
     * @throws ExceptionInterface
     */
    private function call(array $payload, array $headers = []): ResponseInterface
    {
        $computedHeaders = $this->getHeaders($headers);

        return $this->httpClient->request(
            'POST',
            $this->baseUrl.self::WEBSERVICE_PATH,
            [
                'headers' => $computedHeaders,
                'json'    => $payload,
            ]
        );
    }

    /**
     * @param array<string, string> $contextHeaders
     *
     * @return array<string, string>
     */
    private function getHeaders(array $contextHeaders = []): array
    {
        return [...self::BASE_HEADERS, ...['Cookie' => $this->getFormattedCookies()], ...$this->getAuthHeaders(), ...$contextHeaders];
    }

    private function getFormattedCookies(): string
    {
        return self::formatCookies(
            array_merge(
                self::BASE_COOKIES,
                $this->getAuthCookies()
            )
        );
    }

    /**
     * @return array<string, string>
     */
    private function getAuthHeaders(): array
    {
        if ($this->contextId === null) {
            return [];
        }

        return [
            'Authorization' => 'X-Sah '.$this->contextId,
            'X-Context'     => $this->contextId,
        ];
    }

    /**
     * @return array<string, string>
     */
    private function getAuthCookies(): array
    {
        if ($this->contextId === null || $this->sessionCookie === null) {
            return [];
        }

        return array_merge(
            $this->sessionCookie,
            [
                'sah/contextId'         => $this->contextId,
                'lastKnownIpv6TabState' => 'visible',
            ]
        );
    }

    /**
     * Cookies are provided as such:
     * ```
     * $cookies = [
     *     'cookieKey1' => 'cookieValue1',
     *     'cookieKey2' => 'cookieValue2',
     *     ...
     * ];
     * ```
     * and return as a valid formatted string:
     * ````
     * 'cookieKey1=cookieValue1; cookieKey2=cookieValue2; ...'
     * ```.
     *
     * @param array<string, string> $cookies
     */
    private static function formatCookies(array $cookies): string
    {
        return implode(
            '; ',
            array_reduce(
                array_keys($cookies),
                static function (array $cookiesArray, string $cookieName) use ($cookies): array {
                    $cookiesArray[] = $cookieName.'='.$cookies[$cookieName];

                    return $cookiesArray;
                },
                []
            )
        );
    }

    /**
     * Allowed options for configuration:
     * ```
     * $options = [
     *     LiveboxAdminApiClient::LIVEBOX_URL_PARAM => '<livebox_http_url>', // defaults to 'http://192.168.1.1'
     *     LiveboxAdminApiClient::LIVEBOX_USR_PARAM => '<livebox_username>', // defaults to 'admin'
     *     LiveboxAdminApiClient::LIVEBOX_PWD_PARAM => '<livebox_password>', // defaults to 'admin'
     * ];
     * ```
     * All $options have a default value if not provided.
     *
     * @param array<string, string> $options
     */
    public static function build(
        ?HttpClientInterface $httpClient = null,
        array $options = []
    ): self {
        if ($httpClient === null) {
            $httpClient = HttpClient::create();
        }

        return new self(
            $httpClient,
            $options[self::LIVEBOX_URL_PARAM] ?? self::DEFAULT_URL,
            $options[self::LIVEBOX_USR_PARAM] ?? self::DEFAULT_USER,
            $options[self::LIVEBOX_PWD_PARAM] ?? self::DEFAULT_PASSWORD
        );
    }
}
