<?php

declare(strict_types=1);

namespace Kanti\WebVitalsTracker\Middleware;

use Kanti\WebVitalsTracker\Domain\Repository\MeasureRepository;
use Psr\Container\ContainerInterface;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\MiddlewareInterface;
use Psr\Http\Server\RequestHandlerInterface;
use TYPO3\CMS\Core\Http\NullResponse;
use TYPO3\CMS\Core\Routing\PageArguments;
use TYPO3\CMS\Core\Site\Entity\SiteLanguage;
use TYPO3\CMS\Core\Utility\GeneralUtility;

final class MeasureMiddleware implements MiddlewareInterface
{

    public function __construct(private ContainerInterface $container)
    {
    }

    /**
     * @throws \JsonException
     */
    public function process(ServerRequestInterface $request, RequestHandlerInterface $handler): ResponseInterface
    {
        if (!isset($request->getQueryParams()['webvitalstracker'])) {
            return $handler->handle($request);
        }

        $pageArguments = $request->getAttribute('routing');
        assert($pageArguments instanceof PageArguments);

        $language = $request->getAttribute('language');
        assert($language instanceof SiteLanguage);

        $measureRepository = $this->container->get(MeasureRepository::class);
        assert($measureRepository instanceof MeasureRepository);

        $bodyString = $request->getBody()->getContents();
        $body = \json_decode($bodyString, true, 512, JSON_THROW_ON_ERROR);

        $measureRepository->insertOrUpdateMeasure(
            $body['requestUuid'],
            strtolower($body['name']),
            (float)$body['value'],
            (int)$body['counter'],
            $pageArguments->getPageId(),
            $language->getLanguageId()
        );

        return new NullResponse();
    }
}
