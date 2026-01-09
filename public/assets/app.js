(() => {
  const normalizeWhitespace = (value) =>
    String(value ?? '')
      .replace(/\s+/g, ' ')
      .trim();

  const normalizeUpperText = (value) => {
    const normalized = normalizeWhitespace(value);
    return normalized ? normalized.toUpperCase() : '';
  };

  const applyOfficeAssignmentRules = (form) => {
    if (!(form instanceof HTMLFormElement)) return;

    const assignment = form.querySelector('#office_assignment');
    if (!(assignment instanceof HTMLSelectElement)) return;

    const help = form.querySelector('#officeAssignmentHelp');
    const checked = form.querySelector('input[name="office_type"]:checked');
    const officeType = checked ? checked.value : '';

    const shouldDisable =
      officeType === 'central_office' || officeType === 'attached_agency';

    assignment.disabled = shouldDisable;
    if (shouldDisable) {
      assignment.value = '';
      assignment.classList.add('opacity-60', 'cursor-not-allowed');
      if (help) help.classList.remove('hidden');
    } else {
      assignment.classList.remove('opacity-60', 'cursor-not-allowed');
      if (help) help.classList.add('hidden');
    }
  };

  document.addEventListener('change', (e) => {
    const target = e.target;
    if (!(target instanceof HTMLElement)) return;

    if (target.matches('input[name="office_type"]')) {
      const form = target.closest('form');
      if (form) applyOfficeAssignmentRules(form);
    }
  });

  document.addEventListener('submit', (e) => {
    const form = e.target;
    if (!(form instanceof HTMLFormElement)) return;

    // Normalize all free-text inputs (uppercase + whitespace cleanup)
    form.querySelectorAll('input[type="text"], textarea').forEach((el) => {
      if (el instanceof HTMLInputElement || el instanceof HTMLTextAreaElement) {
        if (el.dataset.noNormalize === 'true') return;
        el.value = normalizeUpperText(el.value);
      }
    });

    // Email: trim + lowercase (do not uppercase)
    form.querySelectorAll('input[type="email"]').forEach((el) => {
      if (el instanceof HTMLInputElement) {
        el.value = normalizeWhitespace(el.value).toLowerCase();
      }
    });

    // Phone: trim only
    form.querySelectorAll('input[type="tel"]').forEach((el) => {
      if (el instanceof HTMLInputElement) {
        el.value = normalizeWhitespace(el.value);
      }
    });

    // Enforce office assignment disabling/clearing right before submit
    applyOfficeAssignmentRules(form);
  });

  // Initial pass for pages that start with a saved selection
  document.addEventListener('DOMContentLoaded', () => {
    document.querySelectorAll('form').forEach((form) =>
      applyOfficeAssignmentRules(form)
    );
  });
})();
