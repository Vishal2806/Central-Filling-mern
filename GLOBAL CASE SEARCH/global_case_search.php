<?php

declare(strict_types=1);

/**
 * Global case search (EILR-style): nature radios, case type / no / year, Go.
 *
 * Required keys on $gcs:
 * - wrapper_id (string) Root element id for GlobalCaseSearch.init({ root: ... })
 * - form_id (string)
 * - dynamic_container_id (string)
 * - fetch_url (string) e.g. "index.php" (relative to current page URL)
 * - casenature (string) "1" | "2" | "3"
 * - case_type (string)
 * - case_no (string)
 * - case_year (string)
 * - case_types (iterable rows: case_type, type_name)
 *
 * Optional:
 * - card_title (string) default "Nature & case search"
 * - id_prefix (string) default "gcs"; used for default nature radio ids if nature_radio_ids omitted
 * - nature_radio_ids (list<string>|null) three ids for Civil/Criminal/Writ inputs (for=" must match)
 * - nature_input_class (string) default "gcs-nature" (class on each nature radio for JS)
 * - case_type_input_id, case_no_input_id, case_year_input_id (strings) default {id_prefix}CaseType etc.
 * - ajax_key, ajax_value (strings) defaults ajax / 1
 * - history_base (string|null) default: fetch_url without query string
 * - party_sl_no (string) when present, shows Party Serial No. field (query param pslno1)
 * - party_sl_param_name (string) default "pslno1"
 * - party_sl_input_id (string) default {id_prefix}PartySl
 */
if (!isset($gcs) || !is_array($gcs)) {
    throw new InvalidArgumentException('$gcs config array is required for global_case_search partial.');
}

$wrapper_id = (string) $gcs['wrapper_id'];
$form_id = (string) $gcs['form_id'];
$dynamic_container_id = (string) $gcs['dynamic_container_id'];
$fetch_url = (string) $gcs['fetch_url'];
$casenature = (string) $gcs['casenature'];
$case_type = (string) $gcs['case_type'];
$case_no = (string) $gcs['case_no'];
$case_year = (string) $gcs['case_year'];
$case_types = $gcs['case_types'] ?? [];

$card_title = (string) ($gcs['card_title'] ?? 'Nature & case search');
$id_prefix = (string) ($gcs['id_prefix'] ?? 'gcs');
$nature_class = (string) ($gcs['nature_input_class'] ?? 'gcs-nature');
$ajax_key = (string) ($gcs['ajax_key'] ?? 'ajax');
$ajax_value = (string) ($gcs['ajax_value'] ?? '1');

$nature_radio_ids = $gcs['nature_radio_ids'] ?? null;
if (!is_array($nature_radio_ids) || count($nature_radio_ids) < 3) {
    $nature_radio_ids = [$id_prefix . '_cn1', $id_prefix . '_cn2', $id_prefix . '_cn3'];
}
$nature_radio_ids = array_map('strval', array_slice($nature_radio_ids, 0, 3));

$case_type_input_id = (string) ($gcs['case_type_input_id'] ?? $id_prefix . 'CaseType');
$case_no_input_id = (string) ($gcs['case_no_input_id'] ?? $id_prefix . 'CaseNo');
$case_year_input_id = (string) ($gcs['case_year_input_id'] ?? $id_prefix . 'CaseYear');

$show_party_sl = array_key_exists('party_sl_no', $gcs);
$party_sl_no = $show_party_sl ? (string) $gcs['party_sl_no'] : '';
$party_sl_param_name = (string) ($gcs['party_sl_param_name'] ?? 'pslno1');
$party_sl_input_id = (string) ($gcs['party_sl_input_id'] ?? $id_prefix . 'PartySl');

$history_base = $gcs['history_base'] ?? null;
if (!is_string($history_base) || $history_base === '') {
    $history_base = explode('?', $fetch_url, 2)[0];
}

$nature_labels = [
    ['value' => '1', 'label' => 'Civil'],
    ['value' => '2', 'label' => 'Criminal'],
    ['value' => '3', 'label' => 'Writ'],
];

?>
<div id="<?= htmlspecialchars($wrapper_id, ENT_QUOTES, 'UTF-8') ?>"
    class="global-case-search"
    data-global-case-search="1"
    data-gcs-fetch-url="<?= htmlspecialchars($fetch_url, ENT_QUOTES, 'UTF-8') ?>"
    data-gcs-form-id="<?= htmlspecialchars($form_id, ENT_QUOTES, 'UTF-8') ?>"
    data-gcs-dynamic-id="<?= htmlspecialchars($dynamic_container_id, ENT_QUOTES, 'UTF-8') ?>"
    data-gcs-ajax-key="<?= htmlspecialchars($ajax_key, ENT_QUOTES, 'UTF-8') ?>"
    data-gcs-ajax-value="<?= htmlspecialchars($ajax_value, ENT_QUOTES, 'UTF-8') ?>"
    data-gcs-history-base="<?= htmlspecialchars($history_base, ENT_QUOTES, 'UTF-8') ?>"
    data-gcs-nature-class="<?= htmlspecialchars($nature_class, ENT_QUOTES, 'UTF-8') ?>">
    <div class="card mb-3">
        <div class="card-header">
            <h5 class="mb-0"><?= htmlspecialchars($card_title, ENT_QUOTES, 'UTF-8') ?></h5>
        </div>
        <div class="card-body">
            <form method="get" id="<?= htmlspecialchars($form_id, ENT_QUOTES, 'UTF-8') ?>"
                class="row g-3 align-items-end needs-validation" novalidate>
                <?php
                $gcs_extra_hidden = $gcs['extra_hidden'] ?? [];
                if (is_array($gcs_extra_hidden)) {
                    foreach ($gcs_extra_hidden as $pair) {
                        if (!is_array($pair) || !isset($pair['name'])) {
                            continue;
                        }
                        $hn = (string) $pair['name'];
                        $hv = (string) ($pair['value'] ?? '');
                        ?>
                    <input type="hidden" name="<?= htmlspecialchars($hn, ENT_QUOTES, 'UTF-8') ?>" value="<?= htmlspecialchars($hv, ENT_QUOTES, 'UTF-8') ?>">
                        <?php
                    }
                }
                ?>
                <div class="col-12">
                    <div class="btn-group" role="group">
                        <?php foreach ($nature_labels as $idx => $nl):
                            $nid = $nature_radio_ids[$idx];
                            $val = $nl['value'];
                            $checked = (string) $casenature === (string) $val ? ' checked' : '';
                            ?>
                            <input type="radio" class="btn-check <?= htmlspecialchars($nature_class, ENT_QUOTES, 'UTF-8') ?>"
                                name="casenature" value="<?= htmlspecialchars($val, ENT_QUOTES, 'UTF-8') ?>"
                                id="<?= htmlspecialchars($nid, ENT_QUOTES, 'UTF-8') ?>"<?= $checked ?>>
                            <label class="btn btn-outline-primary" for="<?= htmlspecialchars($nid, ENT_QUOTES, 'UTF-8') ?>"><?= htmlspecialchars($nl['label'], ENT_QUOTES, 'UTF-8') ?></label>
                        <?php endforeach; ?>
                    </div>
                </div>
                <div class="col-md-4 position-relative">
                    <label class="form-label required" for="<?= htmlspecialchars($case_type_input_id, ENT_QUOTES, 'UTF-8') ?>">Case type</label>
                    <select name="case_type" id="<?= htmlspecialchars($case_type_input_id, ENT_QUOTES, 'UTF-8') ?>"
                        class="form-select required" required>
                        <?php
                        require_once dirname(__DIR__, 2) . '/includes/GlobalCaseSearch.php';
                        GlobalCaseSearch::renderCaseTypeOptions($case_types, $case_type);
                        ?>
                    </select>
                    <div class="invalid-feedback position-absolute">This field is required.</div>
                </div>
                <div class="col-md-2 position-relative">
                    <label class="form-label required" for="<?= htmlspecialchars($case_no_input_id, ENT_QUOTES, 'UTF-8') ?>">Case No</label>
                    <input type="text" class="form-control required" id="<?= htmlspecialchars($case_no_input_id, ENT_QUOTES, 'UTF-8') ?>"
                        name="case_no" value="<?= htmlspecialchars($case_no, ENT_QUOTES, 'UTF-8') ?>" maxlength="7" required>
                    <div class="invalid-feedback position-absolute">This field is required.</div>
                </div>
                <div class="col-md-2 position-relative">
                    <label class="form-label required" for="<?= htmlspecialchars($case_year_input_id, ENT_QUOTES, 'UTF-8') ?>">Year</label>
                    <input type="text" class="form-control required" id="<?= htmlspecialchars($case_year_input_id, ENT_QUOTES, 'UTF-8') ?>"
                        name="case_year" value="<?= htmlspecialchars($case_year, ENT_QUOTES, 'UTF-8') ?>" maxlength="4" required>
                    <div class="invalid-feedback position-absolute">This field is required.</div>
                </div>
                <?php if ($show_party_sl): ?>
                <div class="col-md-2 position-relative">
                    <label class="form-label" for="<?= htmlspecialchars($party_sl_input_id, ENT_QUOTES, 'UTF-8') ?>">Party Serial No.</label>
                    <input type="text" class="form-control" id="<?= htmlspecialchars($party_sl_input_id, ENT_QUOTES, 'UTF-8') ?>"
                        name="<?= htmlspecialchars($party_sl_param_name, ENT_QUOTES, 'UTF-8') ?>"
                        value="<?= htmlspecialchars($party_sl_no, ENT_QUOTES, 'UTF-8') ?>"
                        pattern="[0-9]+" maxlength="10" autocomplete="off">
                </div>
                <?php endif; ?>
                <div class="col-md-2">
                    <button type="submit" class="btn btn-primary w-100">Go</button>
                </div>
            </form>
        </div>
    </div>
</div>
<?php // Host page: output the dynamic container div (same id as dynamic_container_id) after this partial.
