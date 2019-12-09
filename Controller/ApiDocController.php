<?php

/*
 * This file is part of the NelmioApiDocBundle.
 *
 * (c) Nelmio <hello@nelm.io>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Nelmio\ApiDocBundle\Controller;

use Nelmio\ApiDocBundle\Extractor\ApiDocExtractor;
use Nelmio\ApiDocBundle\Formatter\HtmlFormatter;
use Nelmio\ApiDocBundle\Formatter\RequestAwareSwaggerFormatter;
use Nelmio\ApiDocBundle\Annotation\ApiDoc;
use Nelmio\ApiDocBundle\Formatter\SwaggerFormatter;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

class ApiDocController
{
    private $apiDocExtractor;
    private $htmlFormatter;
    private $swaggerFormatter;

    public function __construct(ApiDocExtractor $apiDocExtractor, HtmlFormatter $htmlFormatter, SwaggerFormatter $swaggerFormatter)
    {
        $this->apiDocExtractor = $apiDocExtractor;
        $this->htmlFormatter = $htmlFormatter;
        $this->swaggerFormatter = $swaggerFormatter;
    }

    public function indexAction($view = ApiDoc::DEFAULT_VIEW)
    {
        $extractedDoc = $this->apiDocExtractor->all($view);
        $htmlContent  = $this->htmlFormatter->format($extractedDoc);

        return new Response($htmlContent, 200, array('Content-Type' => 'text/html'));
    }

    public function swaggerAction(Request $request, $resource = null)
    {
        $docs = $this->apiDocExtractor->all();
        $formatter = new RequestAwareSwaggerFormatter($request, $this->swaggerFormatter);

        $spec = $formatter->format($docs, $resource ? '/' . $resource : null);

        if ($resource !== null && count($spec['apis']) === 0) {
            throw new NotFoundHttpException(sprintf('Cannot find resource "%s"', $resource));
        }

        return new JsonResponse($spec);
    }
}
