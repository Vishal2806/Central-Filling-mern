<?php

declare(strict_types=1);

/**
 * Shared AJAX fragment + case-type option markup helpers for the global case search UI.
 */
final class GlobalCaseSearch
{
    public static function isAjaxCaseSearchFragmentRequest(?string $key = null, ?string $value = null): bool
    {
        $key = $key ?? 'ajax';
        $value = $value ?? '1';

        return isset($_GET[$key]) && (string) $_GET[$key] === $value;
    }

    public static function jsonEncodeFlags(): int
    {
        $flags = JSON_UNESCAPED_UNICODE;
        if (defined('JSON_INVALID_UTF8_SUBSTITUTE')) {
            $flags |= JSON_INVALID_UTF8_SUBSTITUTE;
        }

        return $flags;
    }

    /**
     * @param callable(): void $renderDynamicHtml
     * @param callable(): void $renderCaseTypeOptionsHtml
     */
    public static function emitCaseSearchFragmentJson(callable $renderDynamicHtml, callable $renderCaseTypeOptionsHtml): void
    {
        header('Content-Type: application/json; charset=UTF-8');
        ob_start();
        $renderDynamicHtml();
        $dynamicHtml = ob_get_clean();
        ob_start();
        $renderCaseTypeOptionsHtml();
        $caseTypeOptionsHtml = ob_get_clean();
        echo json_encode([
            'dynamicHtml' => $dynamicHtml,
            'caseTypeOptionsHtml' => $caseTypeOptionsHtml,
        ], self::jsonEncodeFlags());
        exit;
    }

    /**
     * @param iterable<int|string, array<string, mixed>> $caseTypes
     */
    public static function renderCaseTypeOptions(iterable $caseTypes, string $selectedCaseType): void
    {
        echo '<option value="">— Select —</option>';
        foreach ($caseTypes as $r) {
            $ct = (string) ($r['case_type'] ?? '');
            $name = (string) ($r['type_name'] ?? '');
            $sel = ((string) $selectedCaseType === (string) $ct) ? ' selected' : '';
            $val = (string) (int) $r['case_type'];
            echo '<option value="' . htmlspecialchars($val, ENT_QUOTES, 'UTF-8') . '"' . $sel . '>'
                . htmlspecialchars($name, ENT_QUOTES, 'UTF-8') . '</option>';
        }
    }
}
