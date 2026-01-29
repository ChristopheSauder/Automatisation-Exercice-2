<?php

namespace App\Twig;

use Twig\Extension\AbstractExtension;
use Twig\TwigFilter;
use Twig\TwigFunction;

class MyExtension extends AbstractExtension
{
    private array $manifest = [];

    public function __construct()
    {
        $manifestPath = __DIR__ . '/../../public/build/manifest.json';
        if (file_exists($manifestPath)) {
            $this->manifest = json_decode(file_get_contents($manifestPath), true) ?? [];
        }
    }

    public function getName(): string
    {
        return 'my-extension';
    }

    public function getFilters(): array
    {
        return [
            new TwigFilter('hashString', [$this, 'hashString']),
            new TwigFilter('myReplace', [$this, 'myReplace'])
        ];
    }

    public function getFunctions(): array
    {
        return [
            new TwigFunction('vite_asset', [$this, 'viteAsset']),
            new TwigFunction('vite_client', [$this, 'viteClient']),
        ];
    }

    public function hashString(string $string): string
    {
        return hash('sha256', $string);
    }

    public function myReplace(string $string, string $search, string $replace): string
    {
        return str_replace($search, $replace, $string);
    }

    public function viteAsset(string $entry): string
    {
        $isDev = getenv('APP_ENV') === 'dev';

        if ($isDev) {
            // In dev mode, serve directly from Vite dev server
            return 'http://localhost:5173/' . $entry;
        }

        // In production, use manifest from build
        if (isset($this->manifest[$entry])) {
            return '/build/' . $this->manifest[$entry]['file'];
        }
        return "/build/{$entry}";
    }

    public function viteClient(): string
    {
        $isDev = getenv('APP_ENV') === 'dev';

        if ($isDev) {
            return '<script type="module" src="http://localhost:5173/@vite/client"></script>';
        }

        return '';
    }
}
