<?php

namespace App\Support;

use DOMDocument;
use DOMElement;
use DOMNode;

/**
 * Làm sạch HTML do người dùng nhập (nội dung chương từ CKEditor) theo cơ chế
 * ALLOWLIST: chỉ giữ lại đúng các thẻ/thuộc tính an toàn, loại bỏ mọi vector XSS
 * (script, iframe, sự kiện on*, javascript:/data: URL, style nguy hiểm...).
 *
 * Dùng DOMDocument (parse thật) thay vì regex để tránh mutation-XSS.
 */
class HtmlSanitizer
{
    /** Thẻ được phép giữ lại (khớp output CKEditor). */
    private const ALLOWED_TAGS = [
        'p', 'br', 'hr', 'strong', 'b', 'em', 'i', 'u', 's', 'strike', 'del', 'ins',
        'sub', 'sup', 'small', 'mark', 'h1', 'h2', 'h3', 'h4', 'h5', 'h6',
        'ul', 'ol', 'li', 'blockquote', 'a', 'img', 'figure', 'figcaption', 'span', 'div',
        'table', 'thead', 'tbody', 'tfoot', 'tr', 'td', 'th', 'caption', 'col', 'colgroup',
        'pre', 'code',
    ];

    /** Thẻ độc: xoá CẢ nội dung bên trong. */
    private const FORBIDDEN_TAGS = [
        'script', 'style', 'iframe', 'object', 'embed', 'form', 'input', 'button',
        'textarea', 'select', 'option', 'link', 'meta', 'base', 'svg', 'math',
        'applet', 'frame', 'frameset', 'noscript', 'template',
    ];

    /** Thuộc tính cho phép theo từng thẻ ('*' = áp cho mọi thẻ). */
    private const ALLOWED_ATTRS = [
        '*'      => ['class'],
        'a'      => ['href', 'title', 'target', 'rel'],
        'img'    => ['src', 'alt', 'title', 'width', 'height', 'style'],
        'figure' => ['style'],
        'p'      => ['style'],
        'span'   => ['style'],
        'div'    => ['style'],
        'table'  => ['style'],
        'td'     => ['colspan', 'rowspan', 'style'],
        'th'     => ['colspan', 'rowspan', 'style'],
    ];

    /** Thuộc tính CSS an toàn được giữ lại trong style (canh lề, đổi kích thước ảnh...). */
    private const ALLOWED_STYLE_PROPS = [
        'width', 'height', 'max-width', 'min-width', 'aspect-ratio',
        'text-align', 'float', 'margin', 'margin-left', 'margin-right', 'margin-top', 'margin-bottom',
    ];

    public static function clean(?string $html): ?string
    {
        if ($html === null) {
            return null;
        }
        $html = trim($html);
        if ($html === '') {
            return '';
        }

        $dom = new DOMDocument('1.0', 'UTF-8');
        $prev = libxml_use_internal_errors(true);
        // Bọc trong 1 div gốc + ép UTF-8; NOIMPLIED/NODEFDTD để không tự thêm <html><body><!DOCTYPE>.
        $dom->loadHTML(
            '<?xml encoding="UTF-8">' . '<div data-root="1">' . $html . '</div>',
            LIBXML_HTML_NOIMPLIED | LIBXML_HTML_NODEFDTD | LIBXML_NONET
        );
        libxml_clear_errors();
        libxml_use_internal_errors($prev);

        // Tìm div gốc (element đầu tiên ở cấp document).
        $root = null;
        foreach ($dom->childNodes as $node) {
            if ($node->nodeType === XML_ELEMENT_NODE) {
                $root = $node;
                break;
            }
        }
        if (!$root instanceof DOMElement) {
            return '';
        }

        self::sanitizeChildren($root);

        $out = '';
        foreach (iterator_to_array($root->childNodes) as $child) {
            $out .= $dom->saveHTML($child);
        }

        return trim($out);
    }

    private static function sanitizeChildren(DOMNode $node): void
    {
        // Snapshot vì sẽ thêm/xoá node trong vòng lặp.
        foreach (iterator_to_array($node->childNodes) as $child) {
            if ($child->nodeType === XML_COMMENT_NODE) {
                $child->parentNode->removeChild($child);
                continue;
            }
            if ($child->nodeType !== XML_ELEMENT_NODE) {
                continue; // text node: giữ nguyên (đã được DOM tự escape khi xuất)
            }

            /** @var DOMElement $child */
            $tag = strtolower($child->nodeName);

            if (in_array($tag, self::FORBIDDEN_TAGS, true)) {
                $child->parentNode->removeChild($child); // xoá cả nội dung
                continue;
            }

            if (!in_array($tag, self::ALLOWED_TAGS, true)) {
                // Thẻ lạ: làm sạch con rồi "unwrap" (bỏ thẻ, giữ text bên trong).
                self::sanitizeChildren($child);
                self::unwrap($child);
                continue;
            }

            self::filterAttributes($child, $tag);
            self::sanitizeChildren($child);
        }
    }

    private static function filterAttributes(DOMElement $el, string $tag): void
    {
        $allowed = array_merge(self::ALLOWED_ATTRS['*'], self::ALLOWED_ATTRS[$tag] ?? []);

        foreach (iterator_to_array($el->attributes) as $attr) {
            $name = strtolower($attr->nodeName);

            // Chặn mọi handler sự kiện (onerror, onload, onclick...) và thuộc tính ngoài allowlist.
            if (str_starts_with($name, 'on') || !in_array($name, $allowed, true)) {
                $el->removeAttribute($attr->nodeName);
                continue;
            }
            // Chặn URL scheme nguy hiểm trên href/src.
            if (in_array($name, ['href', 'src'], true) && self::isDangerousUrl($attr->nodeValue)) {
                $el->removeAttribute($attr->nodeName);
                continue;
            }
            // Lọc style: chỉ giữ thuộc tính CSS an toàn (canh lề, kích thước ảnh).
            if ($name === 'style') {
                $clean = self::sanitizeStyle($attr->nodeValue);
                if ($clean === '') {
                    $el->removeAttribute($attr->nodeName);
                } else {
                    $el->setAttribute('style', $clean);
                }
                continue;
            }
        }

        // Ép link mở tab mới phải an toàn (chống tabnabbing).
        if ($tag === 'a' && $el->hasAttribute('target')) {
            $el->setAttribute('target', '_blank');
            $el->setAttribute('rel', 'noopener noreferrer nofollow');
        }
    }

    private static function sanitizeStyle(string $css): string
    {
        $out = [];
        foreach (explode(';', $css) as $decl) {
            if (trim($decl) === '') {
                continue;
            }
            $parts = explode(':', $decl, 2);
            if (count($parts) !== 2) {
                continue;
            }
            $prop = strtolower(trim($parts[0]));
            $val = trim($parts[1]);
            if (!in_array($prop, self::ALLOWED_STYLE_PROPS, true)) {
                continue;
            }
            // Chặn giá trị nguy hiểm (url(), expression(), javascript:, @import).
            if (preg_match('/url\s*\(|expression|javascript:|@import|&#/i', $val)) {
                continue;
            }
            $out[] = $prop . ': ' . $val;
        }

        return implode('; ', $out);
    }

    private static function isDangerousUrl(string $url): bool
    {
        // Bỏ ký tự điều khiển/khoảng trắng có thể chèn để né bộ lọc ("java\tscript:").
        $u = strtolower(preg_replace('/[\x00-\x20]+/', '', $url) ?? '');
        if ($u === '') {
            return false;
        }
        if (preg_match('#^(javascript|vbscript|file|about|blob):#', $u)) {
            return true;
        }
        // data: chỉ cho phép ảnh.
        if (str_starts_with($u, 'data:') && !str_starts_with($u, 'data:image/')) {
            return true;
        }
        return false;
    }

    private static function unwrap(DOMElement $el): void
    {
        $parent = $el->parentNode;
        while ($el->firstChild) {
            $parent->insertBefore($el->firstChild, $el);
        }
        $parent->removeChild($el);
    }
}
