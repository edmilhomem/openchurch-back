<?php
/**
 * Created by PhpStorm.
 * User: Jackson
 * Date: 31/12/2015
 * Time: 01:13
 */
//https://github.com/gonzalo123/AngularPostRequestServiceProvider

namespace OpenChurch;
use Silex\Application;
use Symfony\Component\HttpFoundation\Request;
use Silex\ServiceProviderInterface;

class AngularPostRequestServiceProvider implements ServiceProviderInterface
{
    public function register(Application $app)
    {
        $app->before(function (Request $request) {
            if ($this->isRequestTransformable($request)) {
                if ($request->getContent()) {
                    $transformedRequest = $this->transformContent($request->getContent());
                    if ($transformedRequest) {
                        $request->request->replace($transformedRequest);
                    }
                }
            }
        });
    }
    public function boot(Application $app)
    {
    }
    private function transformContent($content)
    {
        return json_decode($content, true);
    }
    private function isRequestTransformable(Request $request)
    {
        return 0 === strpos($request->headers->get('Content-Type'), 'application/json');
    }
}
