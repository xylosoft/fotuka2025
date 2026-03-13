<?php

namespace common\models;

use yii\helpers\Html;
use common\models\File;
use common\models\Asset;

class WebsiteTemplateRenderer
{
    public static function cssValue($value, $suffix = 'px')
    {
        if ($value === null || $value === '') {
            return '';
        }

        if (is_numeric($value)) {
            return ((float) $value) . $suffix;
        }

        return (string) $value;
    }

    public static function buildBoxStyle(array $component, $absolute = true)
    {
        $style = [];

        if ($absolute) {
            $style[] = 'position:absolute';
            $style[] = 'left:' . static::cssValue($component['x'] ?? 0);
            $style[] = 'top:' . static::cssValue($component['y'] ?? 0);
        }

        $style[] = 'width:' . static::cssValue($component['w'] ?? 300);
        $style[] = 'height:' . static::cssValue($component['h'] ?? 180);
        $style[] = 'z-index:' . (int) ($component['z'] ?? 1);

        if (!empty($component['style']) && is_array($component['style'])) {
            foreach ($component['style'] as $key => $value) {
                if ($value === null || $value === '') {
                    continue;
                }
                $style[] = $key . ':' . $value;
            }
        }

        return implode(';', $style);
    }

    public static function renderComponent(array $component, array $values, array &$lightboxImages)
    {
        $type = $component['type'] ?? '';
        $componentId = (string) ($component['id'] ?? '');
        $bucket = $componentId !== '' ? ($values['components'][$componentId] ?? []) : [];

        $label = Html::encode(
            $component['label']
            ?? $component['field_name']
            ?? ucfirst(str_replace('_', ' ', (string) $type))
        );

        $style = static::buildBoxStyle($component, true);

        switch ($type) {
            case 'static_text':
                return '<div class="tpl-public-component tpl-public-text" style="' . $style . '">' .
                    ($component['html'] ?? '<p></p>') .
                    '</div>';

            case 'dynamic_text':
                $html = $bucket['html'] ?? ($component['default_html'] ?? '<p></p>');
                return '<div class="tpl-public-component tpl-public-text tpl-public-dynamic" style="' . $style . '">' . $html . '</div>';

            case 'image':
                $asset = $bucket['asset'] ?? [];
                $url = Html::encode($asset['preview_url'] ?? ($asset['thumbnail_url'] ?? ''));

                if (!$url) {
                    return '<div class="tpl-public-component tpl-public-empty" style="' . $style . '"><div class="tpl-empty-inner">Missing image<br><small>' . $label . '</small></div></div>';
                }

                $full = Html::encode($asset['preview_url'] ?? ($asset['thumbnail_url'] ?? ''));
                $alt = Html::encode($asset['title'] ?? $label);
                $index = count($lightboxImages);

                $lightboxImages[] = [
                    'url' => $full,
                    'title' => $asset['title'] ?? $label,
                ];

                return '<div class="tpl-public-component tpl-public-image" style="' . $style . '">' .
                    '<img src="' . $url . '" alt="' . $alt . '" class="tpl-lightbox-trigger" data-lightbox-index="' . $index . '">' .
                    '</div>';

            case 'carousel':
                $items = $bucket['items'] ?? [];

                if (!$items) {
                    return '<div class="tpl-public-component tpl-public-empty" style="' . $style . '"><div class="tpl-empty-inner">Missing carousel images<br><small>' . $label . '</small></div></div>';
                }

                $slides = [];

                foreach ($items as $item) {
                    $previewUrl = $item['preview_url'] ?? ($item['thumbnail_url'] ?? '');
                    if (!$previewUrl) {
                        continue;
                    }

                    $idx = count($lightboxImages);
                    $lightboxImages[] = [
                        'url' => $previewUrl,
                        'title' => $item['title'] ?? $label,
                    ];

                    $slides[] = '<div class="tpl-carousel-slide"><img src="' . Html::encode($previewUrl) . '" alt="' . Html::encode($item['title'] ?? $label) . '" class="tpl-lightbox-trigger" data-lightbox-index="' . $idx . '"></div>';
                }

                if (!$slides) {
                    return '<div class="tpl-public-component tpl-public-empty" style="' . $style . '"><div class="tpl-empty-inner">Missing carousel images<br><small>' . $label . '</small></div></div>';
                }

                return '<div class="tpl-public-component tpl-public-carousel" style="' . $style . '">' .
                    '<button type="button" class="tpl-carousel-arrow is-prev" aria-label="Previous">&#10094;</button>' .
                    '<div class="tpl-carousel-viewport"><div class="tpl-carousel-track">' . implode('', $slides) . '</div></div>' .
                    '<button type="button" class="tpl-carousel-arrow is-next" aria-label="Next">&#10095;</button>' .
                    '</div>';

            case 'gallery':
                $items = $bucket['items'] ?? [];

                if (!$items) {
                    return '<div class="tpl-public-component tpl-public-empty" style="' . $style . '"><div class="tpl-empty-inner">Missing gallery images<br><small>' . $label . '</small></div></div>';
                }

                $cards = [];

                foreach ($items as $item) {
                    $previewUrl = $item['preview_url'] ?? ($item['thumbnail_url'] ?? '');
                    if (!$previewUrl) {
                        continue;
                    }

                    $asset = !empty($item['asset_id']) ? Asset::findOne($item['asset_id']) : null;
                    $width = (int) ($asset->file->width ?? 0);
                    $height = (int) ($asset->file->height ?? 0);

                    $idx = count($lightboxImages);
                    $lightboxImages[] = [
                        'url' => $previewUrl,
                        'title' => $item['title'] ?? $label,
                        'width' => $width,
                        'height' => $height,
                    ];

                    $cards[] = '<div class="tpl-gallery-card">
                    <img src="' . Html::encode($previewUrl) . '" alt="' . Html::encode($item['title'] ?? $label) . '" class="tpl-lightbox-trigger" data-lightbox-index="' . $idx . '" width="' . $width . '" height="' . $height . '">
                </div>';
                }

                if (!$cards) {
                    return '<div class="tpl-public-component tpl-public-empty" style="' . $style . '"><div class="tpl-empty-inner">Missing gallery images<br><small>' . $label . '</small></div></div>';
                }

                return '<div class="tpl-public-component tpl-public-gallery" style="' . $style . '">' .
                    '<div class="tpl-gallery-grid">' . implode('', $cards) . '</div>' .
                    '</div>';
        }

        return '<div class="tpl-public-component tpl-public-empty" style="' . $style . '"><div class="tpl-empty-inner">Unsupported component</div></div>';
    }
}