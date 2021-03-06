<?php declare(strict_types=1);

namespace Shopware\Storefront\Theme\Twig;

use Shopware\Core\Framework\Adapter\Twig\NamespaceHierarchy\TemplateNamespaceHierarchyBuilderInterface;
use Shopware\Core\SalesChannelRequest;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\Event\RequestEvent;
use Symfony\Component\HttpKernel\KernelEvents;

class ThemeNamespaceHierarchyBuilder implements TemplateNamespaceHierarchyBuilderInterface, EventSubscriberInterface
{
    /**
     * @var array
     */
    private $themes;

    /**
     * @var ThemeInheritanceBuilderInterface
     */
    private $themeInheritanceBuilder;

    public function __construct(ThemeInheritanceBuilderInterface $themeInheritanceBuilder)
    {
        $this->themeInheritanceBuilder = $themeInheritanceBuilder;
    }

    public static function getSubscribedEvents()
    {
        return [
            KernelEvents::REQUEST => 'requestEvent',
        ];
    }

    public function requestEvent(RequestEvent $event): void
    {
        $request = $event->getRequest();

        $this->themes = $this->detectedThemes($request);
    }

    public function buildNamespaceHierarchy(array $namespaceHierarchy): array
    {
        if (empty($this->themes)) {
            return $namespaceHierarchy;
        }

        return $this->themeInheritanceBuilder->build($namespaceHierarchy, $this->themes);
    }

    private function detectedThemes(Request $request): array
    {
        // detect active themes of request
        $theme = $request->attributes->get(SalesChannelRequest::ATTRIBUTE_THEME_NAME);
        if (!$theme) {
            return [];
        }

        $themes = [
            $theme => true,
        ];

        $theme = $request->attributes->get(SalesChannelRequest::ATTRIBUTE_THEME_BASE_NAME);
        if ($theme) {
            $themes[$theme] = true;
        }

        if (!isset($themes['Storefront'])) {
            $themes['Storefront'] = true;
        }

        return $themes;
    }
}
