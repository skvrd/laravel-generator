<?php

namespace InfyOm\Generator\Generators\Vuejs;

use Illuminate\Support\Str;
use InfyOm\Generator\Common\CommandData;
use InfyOm\Generator\Generators\BaseGenerator;
use InfyOm\Generator\Utils\TemplateUtil;

class RoutesGenerator extends BaseGenerator
{
    /** @var CommandData */
    private $commandData;

    /** @var string */
    private $path;

    /** @var string */
    private $pathApi;    

    /** @var string */
    private $routeContents;

    /** @var string */
    private $apiRouteContents;    

    /** @var string */
    private $routesTemplate;

    /** @var string */
    private $apiRoutesTemplate;    

    public function __construct(CommandData $commandData)
    {
        $this->commandData = $commandData;
        $this->pathApi = $commandData->config->pathApiRoutes;
        $this->path = $commandData->config->pathRoutes;

        $this->routeContents = file_get_contents($this->path);    

        if (!file_exists($this->pathApi)) {
            file_put_contents($this->pathApi, TemplateUtil::getTemplate('vuejs.routes.api_routes', 'laravel-generator'));
        }

        $this->apiRouteContents = file_get_contents($this->pathApi);

        $routesTemplate = TemplateUtil::getTemplate('vuejs.routes.routes', 'laravel-generator');
        $apiRoutesTemplate = TemplateUtil::getTemplate('vuejs.routes.api_routes_base', 'laravel-generator');
        $this->routesTemplate = TemplateUtil::fillTemplate($this->commandData->dynamicVars, $routesTemplate);
        $this->apiRoutesTemplate = TemplateUtil::fillTemplate($this->commandData->dynamicVars, $apiRoutesTemplate);
    }

    public function generate()
    {
        $this->routeContents .= "\n\n".$this->routesTemplate;
        $this->apiRouteContents .= "\n\n".$this->apiRoutesTemplate;

        file_put_contents($this->path, $this->routeContents);
        file_put_contents($this->pathApi, $this->apiRouteContents);

        $this->commandData->commandComment("\n".$this->commandData->config->mCamelPlural.' routes added.');
    }

    public function rollback()
    {
        if (Str::contains($this->routeContents, $this->routesTemplate)) {
            $this->routeContents = str_replace($this->routesTemplate, '', $this->routeContents);
            file_put_contents($this->path, $this->routeContents);
            $this->commandData->commandComment('vuejs routes deleted');
        }

        if (Str::contains($this->apiRouteContents, $this->apiRoutesTemplate)) {
            $this->apiRouteContents = str_replace($this->apiRoutesTemplate, '', $this->apiRouteContents);
            file_put_contents($this->pathApi, $this->apiRouteContents);
            $this->commandData->commandComment('vuejs api routes deleted');
        }        
    }
}
