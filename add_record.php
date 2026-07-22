<?php
require_once __DIR__ . '/config.php';
$db = getDb();

$civilOptions = ["ARBA", "ARBAP", "ARBR", "AW", "CA", "CEA", "CER", "CESR", "COMA", "COMP",
                  "CONC", "CONT", "CONTS", "CP", "CR", "CS", "CVLREF", "EA", "EDR", "EP",
                  "FA", "FAM", "FA(MAT)", "ITA", "ITR", "LPA", "MA", "MAC", "MCA", "MCC",
                  "MCCS", "MCP", "MP", "MWP", "OD", "REVP", "SA", "STR", "TAXC", "TPC", 
                ];

$criminalOptions = ["ACQA", "CONTR", "CRA", "CRMP", "CRREA", "MCRC", "MCRCA", "MCRP", "TPCR"];
$writOptions = ["WA", "WP", "WP227", "WPC", "WPCR", "WPHC", "WPL", "WPPIL", "WPS", "WPT", "WTA", "WTR"];

$caseNature      = $_POST['caseNature']    ?? 'Civil';
$caseTypeCode    = $_POST['caseTypeCode']  ?? '';
$caseNo          = $_POST['caseNo']        ?? '';
$caseYear        = $_POST['caseYear']      ?? '';
$advocateName    = $_POST['advocateName']  ?? '';
$advocateContact = $_POST['advocateContact'] ?? '';
$paperbookSets   = $_POST['paperbookSets'] ?? '1';
$status          = 'SUBMITTED';
$remark          = $_POST['remark']        ?? '';
$signaturePath   = '';
$error           = '';

// Fetch advocates from the configured external advocates DB.
$advocates = [];
try {
    $advDb   = getDb('advocates');
    // correct column names: adv_name, adv_reg, adv_mobile
    $advStmt = $advDb->query('SELECT adv_name, adv_reg, adv_mobile FROM advocate_t ORDER BY adv_name');
    $advocates = $advStmt->fetchAll(PDO::FETCH_ASSOC);
} catch (Exception $e) {
    $advocates = [];
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
        if ($caseNo === '' || $caseYear === '' || $advocateName === '' || $remark === '') {
            $error = 'Please complete all required fields.';
        } elseif (!isset($_FILES['signature']) || $_FILES['signature']['error'] === UPLOAD_ERR_NO_FILE) {
            $error = 'Signature image is required for first-time filing.';
        } else {
            if ($_FILES['signature']['error'] === UPLOAD_ERR_OK) {
                $uploadDir = __DIR__ . '/uploads/signatures/';
                if (!is_dir($uploadDir)) {
                    mkdir($uploadDir, 0755, true);
                }

                $allowedExt = ['jpg', 'jpeg', 'png', 'gif', 'bmp', 'webp'];
                $ext = strtolower(pathinfo($_FILES['signature']['name'], PATHINFO_EXTENSION));

                if (in_array($ext, $allowedExt, true)) {
                    $filename = 'sig_new_' . time() . '_' . bin2hex(random_bytes(4)) . '.' . $ext;
                    $uploadPath = $uploadDir . $filename;

                    if (move_uploaded_file($_FILES['signature']['tmp_name'], $uploadPath)) {
                        $signaturePath = 'uploads/signatures/' . $filename;
                        chmod($uploadPath, 0644);
                    } else {
                        $error = 'Failed to move uploaded signature. Check folder permissions.';
                    }
                } else {
                    $error = 'Invalid signature file type. Only image files are allowed.';
                }
            } else {
                $error = 'Signature upload error: ' . $_FILES['signature']['error'];
            }
        }

        if ($error === '') {
            try {
                $filingDate = $_POST['filingDate'] ?? date('Y-m-d');
                $filingTime = $_POST['filingTime'] ?? date('H:i:s');

                $checkStatus       = trim($status);
                $isReturnedStatus  = ($checkStatus === 'RETURNED' || $checkStatus === 'RETURNED TO CENTRAL FILING' || $checkStatus === 'RETURNED TO ADVOCATE');
                $totalReturns      = $isReturnedStatus ? 1 : 0;
            $stmt = $db->prepare(
                'INSERT INTO records (case_no, case_year, advocate_name, advocate_contact, current_status, total_returns, latest_remark, filing_date, filing_time, case_nature, case_type_code, paperbook_sets)
                 VALUES (:case_no, :case_year, :advocate_name, :advocate_contact, :current_status, :total_returns, :latest_remark, :filing_date, :filing_time, :case_nature, :case_type_code, :paperbook_sets)
                 RETURNING id'
            );

            $stmt->execute([
                ':case_no'           => $caseNo,
                ':case_year'         => $caseYear,
                ':advocate_name'     => $advocateName,
                ':advocate_contact'  => $advocateContact ?: null,
                ':current_status'    => $status,
                ':total_returns'     => $totalReturns,
                ':latest_remark'     => $remark,
                ':filing_date'       => $filingDate,
                ':filing_time'       => $filingTime,
                ':case_nature'       => $caseNature,
                ':case_type_code'    => $caseTypeCode ?: null,
                ':paperbook_sets'    => (int) $paperbookSets,
            ]);

            $newRecord = $stmt->fetch();
            $recordId  = $newRecord['id'];

            $historyStmt = $db->prepare('INSERT INTO record_history (record_id, status, remark, signature_path, updated_by) VALUES (:record_id, :status, :remark, :signature_path, :updated_by)');
            $historyStmt->execute([
                ':record_id'     => $recordId,
                ':status'        => $status,
                ':remark'        => $remark,
                ':signature_path'=> $signaturePath,
                ':updated_by'    => 'User ID: ' . ($_SESSION['user_id'] ?? 'Unknown'),
            ]);

            header('Location: record_details.php?id=' . urlencode($recordId));
            exit;
        } catch (Exception $e) {
            $error = 'Database error: ' . $e->getMessage();
        }
    }
}

$caseNatureOptions = ['Civil', 'Criminal', 'Writ'];

require_once __DIR__ . '/includes/header.php';
?>

<main class="container">

    <section class="page-header">
        <div>
            <h1>New Filing Record</h1>
            <p>Create an official filing registry entry with case and advocate details.</p>
        </div>
    </section>

    <?php if ($error): ?>
        <div class="alert alert-error"><?php echo htmlspecialchars($error); ?></div>
    <?php endif; ?>

    <form method="post" class="form-panel add-record-form" enctype="multipart/form-data">

        <!-- Case Nature -->
        <div class="field-group case-nature-field">
            <label>Case Nature</label>
            <div class="nature-selector">
                <?php foreach ($caseNatureOptions as $nature): ?>
                    <label class="nature-option <?php echo $nature === $caseNature ? 'active' : ''; ?>">
                        <input 
                            type="radio" 
                            name="caseNature" 
                            value="<?php echo htmlspecialchars($nature); ?>"
                            <?php echo $nature === $caseNature ? 'checked' : ''; ?>
                        />
                        <span><?php echo htmlspecialchars($nature); ?></span>
                    </label>
                <?php endforeach; ?>
            </div>
        </div>

        <!-- Case Type Code -->
        <div class="field-group">
            <label for="caseTypeCode">Case Type</label>
            <select id="caseTypeCode" name="caseTypeCode">
                <option value="">Select Case Type</option>
                <optgroup label="Civil Cases">
                    <?php foreach ($civilOptions as $option): ?>
                        <option value="<?php echo htmlspecialchars($option); ?>"
                            <?php echo $caseTypeCode === $option ? 'selected' : ''; ?>>
                            <?php echo htmlspecialchars($option); ?>
                        </option>
                    <?php endforeach; ?>
                </optgroup>
                <optgroup label="Criminal Cases">
                    <?php foreach ($criminalOptions as $option): ?>
                        <option value="<?php echo htmlspecialchars($option); ?>"
                            <?php echo $caseTypeCode === $option ? 'selected' : ''; ?>>
                            <?php echo htmlspecialchars($option); ?>
                        </option>
                    <?php endforeach; ?>
                </optgroup>
                <optgroup label="Writ Cases">
                    <?php foreach ($writOptions as $option): ?>
                        <option value="<?php echo htmlspecialchars($option); ?>"
                            <?php echo $caseTypeCode === $option ? 'selected' : ''; ?>>
                            <?php echo htmlspecialchars($option); ?>
                        </option>
                    <?php endforeach; ?>
                </optgroup>
            </select>
        </div>

        
        <!-- Case No -->
        <div class="field-group">
            <label for="caseNo">Case No</label>
            <input type="text" id="caseNo" name="caseNo"
            value="<?php echo htmlspecialchars($caseNo); ?>" required />
        </div>
        
        <!-- Case Year -->
        <div class="field-group">
            <label for="caseYear">Case Year</label>
            <input type="text" id="caseYear" name="caseYear"
                   value="<?php echo htmlspecialchars($caseYear); ?>" required />
        </div>
        <!-- Advocate Name — custom autocomplete -->
        <div class="field-group adv-autocomplete-wrap">
            <label for="advocateName">Advocate Name</label>
            <input
                type="text"
                id="advocateName"
                name="advocateName"
                value="<?php echo htmlspecialchars($advocateName); ?>"
                required
                autocomplete="off"
                placeholder="Type name or registration no…"
            />
            <ul id="advocateSuggestions" class="adv-suggestions" aria-label="Advocate suggestions"></ul>
        </div>

        <!-- Advocate Contact — auto-filled -->
        <div class="field-group">
            <label for="advocateContact">Advocate Contact</label>
            <input
                type="text"
                id="advocateContact"
                name="advocateContact"
                value="<?php echo htmlspecialchars($advocateContact); ?>"
                placeholder="Auto-filled on selection"
            />
        </div>

        <!-- Paperbook Sets -->
        <div class="field-group">
            <label for="paperbookSets">Paperbook Sets</label>
            <input type="number" min="1" id="paperbookSets" name="paperbookSets"
                   value="<?php echo htmlspecialchars($paperbookSets); ?>" required />
        </div>

        <input type="hidden" name="status" value="SUBMITTED" />

        <!-- Remark — full width -->
        <div class="field-group full-width">
            <label for="remark">Remark</label>
            <textarea id="remark" name="remark" rows="4" required><?php echo htmlspecialchars($remark); ?></textarea>
        </div>

        <!-- Signature upload -->
        <div class="field-group full-width" id="signature-field">
            <label>Advocate Signature</label>
            <input type="file" id="signature" name="signature" accept="image/*" hidden required />

            <label for="signature" class="signature-upload-btn">
                📄 Choose Signature
            </label>

            <span id="signature-file-name">No file selected</span>

            <small style="color: var(--muted); margin-top: 4px; display: block;">
                Upload the advocate's signature image for initial filing.
            </small>
        </div>

        <!-- Submit -->
        <div class="full-width form-actions">
            <button type="submit" class="button button-primary">Save Record</button>
        </div>

    </form>

</main>

<!-- Advocate data passed safely to JS -->
<script>
const ADVOCATES = <?php echo json_encode(array_values($advocates), JSON_HEX_TAG | JSON_HEX_APOS | JSON_HEX_QUOT | JSON_HEX_AMP); ?>;

(function () {
    const nameInput     = document.getElementById('advocateName');
    const contactInput  = document.getElementById('advocateContact');
    const box           = document.getElementById('advocateSuggestions');
    let activeIdx       = -1;

    /* ── render dropdown ── */
    function render(list) {
        box.innerHTML = '';
        activeIdx = -1;

        if (!list.length) { box.classList.remove('open'); return; }

        list.forEach(function (adv, i) {
            const li = document.createElement('li');
            li.className   = 'adv-suggestion-item';
            li.dataset.idx = i;

            const nameSpan = document.createElement('span');
            nameSpan.className   = 'adv-name';
            nameSpan.textContent = adv.adv_name;

            const regSpan = document.createElement('span');
            regSpan.className   = 'adv-reg';
            regSpan.textContent = adv.adv_reg ? adv.adv_reg : '';

            li.appendChild(nameSpan);
            if (adv.adv_reg) li.appendChild(regSpan);

            li.addEventListener('mousedown', function (e) {
                e.preventDefault();          // keep focus on input
                selectAdvocate(adv);
            });
            box.appendChild(li);
        });

        box.classList.add('open');
    }

    function selectAdvocate(adv) {
        nameInput.value    = adv.adv_name;
        contactInput.value = adv.adv_mobile || '';
        box.classList.remove('open');
        box.innerHTML = '';
        activeIdx = -1;
    }

    function highlight(idx) {
        const items = box.querySelectorAll('.adv-suggestion-item');
        items.forEach(function (el, i) {
            el.classList.toggle('active', i === idx);
        });
        activeIdx = idx;
    }

    /* ── filter on type ── */
    nameInput.addEventListener('input', function () {
        contactInput.value = '';          // clear stale mobile
        const q = this.value.trim().toLowerCase();
        if (!q) { box.classList.remove('open'); box.innerHTML = ''; return; }

        const matches = ADVOCATES.filter(function (a) {
            return (a.adv_name && a.adv_name.toLowerCase().includes(q)) ||
                   (a.adv_reg  && a.adv_reg.toLowerCase().includes(q));
        }).slice(0, 25);

        render(matches);
    });

    /* ── keyboard navigation ── */
    nameInput.addEventListener('keydown', function (e) {
        const items = box.querySelectorAll('.adv-suggestion-item');
        if (!items.length) return;

        if (e.key === 'ArrowDown') {
            e.preventDefault();
            highlight(Math.min(activeIdx + 1, items.length - 1));
        } else if (e.key === 'ArrowUp') {
            e.preventDefault();
            highlight(Math.max(activeIdx - 1, 0));
        } else if (e.key === 'Enter' && activeIdx >= 0) {
            e.preventDefault();
            const adv = ADVOCATES.filter(function (a) {
                const q = nameInput.value.trim().toLowerCase();
                return (a.adv_name && a.adv_name.toLowerCase().includes(q)) ||
                       (a.adv_reg  && a.adv_reg.toLowerCase().includes(q));
            }).slice(0, 25)[activeIdx];
            if (adv) selectAdvocate(adv);
        } else if (e.key === 'Escape') {
            box.classList.remove('open');
        }
    });

    /* ── close on outside click ── */
    document.addEventListener('click', function (e) {
        if (!nameInput.contains(e.target) && !box.contains(e.target)) {
            box.classList.remove('open');
        }
    });

    /* ── re-open on focus if text present ── */
    nameInput.addEventListener('focus', function () {
        if (this.value.trim()) this.dispatchEvent(new Event('input'));
    });

    const signatureInput = document.getElementById('signature');
    const signatureFileName = document.getElementById('signature-file-name');

    if (signatureInput) {
        signatureInput.addEventListener('change', function () {
            if (this.files && this.files.length > 0) {
                signatureFileName.textContent = this.files[0].name;
            } else {
                signatureFileName.textContent = 'No file selected';
            }
        });
    }

    /* ── Case Nature → filter optgroups ── */
    const natureRadios   = document.querySelectorAll('input[name="caseNature"]');
    const codeSelect     = document.getElementById('caseTypeCode');

    function filterOptgroups(nature) {
        codeSelect.querySelectorAll('optgroup').forEach(function (g) {
            g.style.display = (g.label === nature + ' Cases') ? '' : 'none';
        });
    }

    natureRadios.forEach(function (radio) {
        radio.addEventListener('change', function () {
            filterOptgroups(this.value);
            codeSelect.value = '';
            
            // Update active styling
            document.querySelectorAll('.nature-option').forEach(function (opt) {
                opt.classList.remove('active');
            });
            this.closest('.nature-option').classList.add('active');
        });
    });

    /* ── init on load ── */
    const checkedNature = document.querySelector('input[name="caseNature"]:checked');
    if (checkedNature) {
        filterOptgroups(checkedNature.value);
    }
}());
</script>

<?php require_once __DIR__ . '/includes/footer.php'; ?> 
