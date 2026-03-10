<?php

namespace common\models;

use yii\helpers\Html;

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
        $field = $component['field_name'] ?? null;
        $label = Html::encode($component['label'] ?? ucfirst(str_replace('_', ' ', (string) $field)));
        $style = static::buildBoxStyle($component, true);

        switch ($type) {
            case 'static_text':
                return '<div class="tpl-public-component tpl-public-text" style="' . $style . '">' .
                    ($component['html'] ?? '<p></p>') .
                    '</div>';

            case 'dynamic_text':
                $html = $values['dynamic_text'][$field]['html'] ?? ($component['default_html'] ?? '<p></p>');
                return '<div class="tpl-public-component tpl-public-text tpl-public-dynamic" style="' . $style . '">' . $html . '</div>';

            case 'image':
                $asset = $values['image'][$field] ?? [];
                $url = Html::encode($asset['preview_url'] ?? '');
                if (!$url) {
                    return '<div class="tpl-public-component tpl-public-empty" style="' . $style . '"><div class="tpl-empty-inner">Missing image<br><small>' . $label . '</small></div></div>';
                }
                $full = Html::encode($asset['preview_url'] ?? '');
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
                $items = $values['carousel'][$field]['items'] ?? [];
                if (!$items) {
                    return '<div class="tpl-public-component tpl-public-empty" style="' . $style . '"><div class="tpl-empty-inner">Missing carousel images<br><small>' . $label . '</small></div></div>';
                }
                $slides = [];
                foreach ($items as $item) {
                    $idx = count($lightboxImages);
                    $lightboxImages[] = [
                        'url' => $item['preview_url'] ?? '',
                        'title' => $item['title'] ?? $label,
                    ];
                    $slides[] = '<div class="tpl-carousel-slide"><img src="' . Html::encode($item['preview_url'] ?? '') . '" alt="' . Html::encode($item['title'] ?? $label) . '" class="tpl-lightbox-trigger" data-lightbox-index="' . $idx . '"></div>';
                }
                return '<div class="tpl-public-component tpl-public-carousel" style="' . $style . '">' .
                    '<button type="button" class="tpl-carousel-arrow is-prev" aria-label="Previous">&#10094;</button>' .
                    '<div class="tpl-carousel-viewport"><div class="tpl-carousel-track">' . implode('', $slides) . '</div></div>' .
                    '<button type="button" class="tpl-carousel-arrow is-next" aria-label="Next">&#10095;</button>' .
                    '</div>';

            case 'gallery':
                $items = $values['gallery'][$field]['items'] ?? [];
                if (!$items) {
                    return '<div class="tpl-public-component tpl-public-empty" style="' . $style . '"><div class="tpl-empty-inner">Missing gallery images<br><small>' . $label . '</small></div></div>';
                }
                $cards = [];
                foreach ($items as $item) {
                    $idx = count($lightboxImages);
                    $lightboxImages[] = [
                        'url' => $item['preview_url'] ?? '',
                        'title' => $item['title'] ?? $label,
                    ];
                    $cards[] = '<div class="tpl-gallery-card"><img src="' . Html::encode($item['preview_url'] ?? '') . '" alt="' . Html::encode($item['title'] ?? $label) . '" class="tpl-lightbox-trigger" data-lightbox-index="' . $idx . '"></div>';
                }
                return '<div class="tpl-public-component tpl-public-gallery" style="' . $style . '">' .
                    '<div class="tpl-gallery-grid">' . implode('', $cards) . '</div>' .
                    '</div>';
        }

        return '<div class="tpl-public-component tpl-public-empty" style="' . $style . '"><div class="tpl-empty-inner">Unsupported component</div></div>';
    }
}