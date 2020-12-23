<?php

declare(strict_types=1);

namespace LaSalle\StudentTeacher\Shared\Infrastructure\Behat;

use Behat\Mink\Driver\DriverInterface;
use Behat\Mink\Session;
use Symfony\Component\BrowserKit\AbstractBrowser;
use Symfony\Component\DomCrawler\Crawler;

final class MinkHelper
{
    public function __construct(private Session $session)
    {
    }

    public function sendRequest($method, $url, array $optionalParams = []): Crawler
    {
        $defaultOptionalParams = [
            'parameters' => [],
            'files' => [],
            'server' => ['HTTP_ACCEPT' => 'application/json', 'CONTENT_TYPE' => 'application/json'],
            'content' => null,
            'changeHistory' => true,
        ];

        $optionalParams = array_merge($defaultOptionalParams, $optionalParams);

        $crawler = $this->getClient()->request(
            $method,
            $url,
            $optionalParams['parameters'],
            $optionalParams['files'],
            $optionalParams['server'],
            $optionalParams['content'],
            $optionalParams['changeHistory']
        );

        $this->resetRequestStuff();

        return $crawler;
    }

    private function getClient(): AbstractBrowser
    {
        return $this->getDriver()->getClient();
    }

    private function getDriver(): DriverInterface
    {
        return $this->getSession()->getDriver();
    }

    private function getSession(): Session
    {
        return $this->session;
    }

    private function resetRequestStuff(): void
    {
        $this->getSession()->reset();
        $this->resetServerParameters();
    }

    public function resetServerParameters(): void
    {
        $this->getClient()->setServerParameters([]);
    }

    public function getResponse(): string
    {
        return $this->getSession()->getPage()->getContent();
    }

    public function setServerParameter(string $key, string $value): void
    {
        $this->getClient()->setServerParameter($key, $value);
    }

    public function getResponseHeaders(): array
    {
        return $this->normalizeHeaders(
            array_change_key_case($this->getSession()->getResponseHeaders(), CASE_LOWER)
        );
    }

    private function normalizeHeaders(array $headers): array
    {
        return array_map('implode', array_filter($headers));
    }

    public function getRequest(): object
    {
        return $this->getClient()->getRequest();
    }
}

