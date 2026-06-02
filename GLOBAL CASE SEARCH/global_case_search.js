/**
 * Global case search: AJAX fragment fetch, case-type options refresh, pushState, popstate.
 * @see views/partials/global_case_search.php
 */
(function (global) {
  'use strict';

  var activeConfig = null;
  var popstateBound = false;
  /** Latest fragment fetch; abort superseded requests so an older response cannot overwrite a newer Go/submit. */
  var fragmentFetchCtrl = null;

  function paramsFromObj(obj) {
    var p = new URLSearchParams();
    if (!obj) return p;
    Object.keys(obj).forEach(function (k) {
      var v = obj[k];
      if (v !== undefined && v !== null && String(v) !== '') p.set(k, String(v));
    });
    return p;
  }

  function buildFetchUrl(base, queryString) {
    if (base.indexOf('?') >= 0) {
      return base + (queryString ? '&' + queryString : '');
    }
    return base + (queryString ? '?' + queryString : '');
  }

  function applyPayload(cfg, data) {
    var dynId = cfg.dynamicId;
    var formId = cfg.formId;
    var dyn = document.getElementById(dynId);
    if (dyn && data.dynamicHtml !== undefined) dyn.innerHTML = data.dynamicHtml;
    var sct = document.querySelector('#' + formId + ' select[name="case_type"]');
    if (sct && data.caseTypeOptionsHtml) sct.innerHTML = data.caseTypeOptionsHtml;
    if (typeof cfg.onAfterApply === 'function') {
      cfg.onAfterApply(dyn || document.getElementById(dynId));
    }
  }

  function fetchFragment(cfg, params, opts) {
    opts = opts || {};
    if (fragmentFetchCtrl) {
      try {
        fragmentFetchCtrl.abort();
      } catch (e) {}
    }
    var ctrl = new AbortController();
    fragmentFetchCtrl = ctrl;
    var myCtrl = ctrl;

    var p = params instanceof URLSearchParams ? new URLSearchParams(params) : paramsFromObj(params);
    p.set(cfg.ajaxKey, cfg.ajaxValue);
    var q = p.toString();
    var url = buildFetchUrl(cfg.fetchUrl, q);
    return fetch(url, {
      signal: myCtrl.signal,
      credentials: 'same-origin',
      headers: {
        'X-Requested-With': 'XMLHttpRequest',
        Accept: 'application/json',
      },
    })
      .then(function (r) {
        if (fragmentFetchCtrl !== myCtrl) return null;
        if (!r.ok) throw new Error('Network error');
        return r.json();
      })
      .then(function (data) {
        if (fragmentFetchCtrl !== myCtrl) return;
        if (data == null) return;
        // Update the URL before applyPayload so host onAfterApply hooks (e.g. layout
        // that reads window.location.search) see the new case identity on first paint.
        if (!opts.skipPushState) {
          var nav = new URLSearchParams(p);
          nav.delete(cfg.ajaxKey);
          var qs = nav.toString();
          var hb = cfg.historyBase || cfg.fetchUrl.split('?')[0];
          history.pushState({}, '', qs ? hb + '?' + qs : hb);
        }
        applyPayload(cfg, data);
      })
      .catch(function (err) {
        if (err && err.name === 'AbortError') return;
        if (fragmentFetchCtrl !== myCtrl) return;
        throw err;
      });
  }

  function bindPopstate() {
    if (popstateBound) return;
    popstateBound = true;
    window.addEventListener('popstate', function () {
      var cfg = activeConfig;
      if (!cfg) return;
      var p = new URLSearchParams(window.location.search);
      if (!p.toString()) {
        var d = document.getElementById(cfg.dynamicId);
        if (d) d.innerHTML = '';
        return;
      }
      fetchFragment(cfg, p, { skipPushState: true }).catch(function () {
        window.location.reload();
      });
    });
  }

  /**
   * @param {Object} opts
   * @param {HTMLElement} opts.root – element with data-gcs-* attributes (see partial)
   * @param {function(Element|null): void} [opts.onAfterApply]
   */
  function init(opts) {
    var root = opts && opts.root;
    if (!root || !root.getAttribute) return;

    var fetchUrl = root.getAttribute('data-gcs-fetch-url') || 'index.php';
    var formId = root.getAttribute('data-gcs-form-id');
    var dynamicId = root.getAttribute('data-gcs-dynamic-id');
    var ajaxKey = root.getAttribute('data-gcs-ajax-key') || 'ajax';
    var ajaxValue = root.getAttribute('data-gcs-ajax-value') || '1';
    var historyBase = root.getAttribute('data-gcs-history-base') || fetchUrl.split('?')[0];
    var natureClass = root.getAttribute('data-gcs-nature-class') || 'gcs-nature';

    if (!formId || !dynamicId) return;

    var cfg = {
      fetchUrl: fetchUrl,
      formId: formId,
      dynamicId: dynamicId,
      ajaxKey: ajaxKey,
      ajaxValue: ajaxValue,
      historyBase: historyBase,
      onAfterApply: opts.onAfterApply,
    };
    activeConfig = cfg;
    bindPopstate();

    var searchForm = document.getElementById(formId);
    if (searchForm) {
      searchForm.addEventListener('submit', function (e) {
        e.preventDefault();
        if (!searchForm.checkValidity()) {
          searchForm.classList.add('was-validated');
          return;
        }
        fetchFragment(cfg, new URLSearchParams(new FormData(searchForm)), {});
      });
    }

    if (searchForm) {
      var sel = 'input[name="casenature"].' + natureClass;
      searchForm.querySelectorAll(sel).forEach(function (radio) {
        radio.addEventListener('change', function () {
          var fd = new URLSearchParams(new FormData(searchForm));
          fd.set('casenature', radio.value);
          fetchFragment(cfg, fd, {});
        });
      });
    }
  }

  function refetchFromParams(params, opts) {
    if (!activeConfig) return Promise.reject(new Error('GlobalCaseSearch not initialized'));
    return fetchFragment(activeConfig, params, opts || {});
  }

  global.GlobalCaseSearch = {
    init: init,
    paramsFromObj: paramsFromObj,
    refetchFromParams: refetchFromParams,
  };
})(typeof window !== 'undefined' ? window : this);
