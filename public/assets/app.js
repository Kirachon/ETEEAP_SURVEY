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
    if (!(assignment instanceof HTMLInputElement) && !(assignment instanceof HTMLSelectElement)) return;

    const region = form.querySelector('#psgc_region_code');
    const province = form.querySelector('#psgc_province_code');
    const city = form.querySelector('#psgc_city_code');

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

      // Clear/disable PSGC drill-down selects (if present on this page)
      [region, province, city].forEach((el) => {
        if (el instanceof HTMLSelectElement) {
          el.value = '';
          el.disabled = true;
          el.classList.add('opacity-60', 'cursor-not-allowed');
        }
      });
    } else {
      assignment.classList.remove('opacity-60', 'cursor-not-allowed');
      if (help) help.classList.add('hidden');

      if (region instanceof HTMLSelectElement) {
        region.disabled = false;
        region.classList.remove('opacity-60', 'cursor-not-allowed');
      }
    }
  };

  const initTomSelectPositions = () => {
    const select = document.querySelector('#current_position[data-tom-select="positions"]');
    if (!(select instanceof HTMLSelectElement)) return;
    if (typeof window.TomSelect !== 'function') return;

    // Avoid double-init
    if (select.tomselect) return;

    const url = select.dataset.tomUrl || '/api/positions';

    // eslint-disable-next-line no-new
    new window.TomSelect(select, {
      valueField: 'value',
      labelField: 'text',
      searchField: ['text'],
      maxItems: 1,
      create: true,
      persist: false,
      preload: false,
      placeholder: select.getAttribute('placeholder') || 'Start typing…',
      closeAfterSelect: true,
      selectOnTab: true,
      load: function (query, callback) {
        const q = normalizeWhitespace(query);
        if (q.length < 1) return callback();

        const reqUrl = `${url}?q=${encodeURIComponent(q)}&limit=50`;
        fetch(reqUrl, { credentials: 'same-origin' })
          .then((res) => res.json())
          .then((json) => {
            const data = json && json.success && Array.isArray(json.data) ? json.data : [];
            callback(data);
          })
          .catch(() => callback());
      },
    });
  };

  const initWillNotApplyReason = () => {
    const wrap = document.getElementById('willNotApplyReasonWrap');
    const textarea = document.getElementById('will_not_apply_reason');
    const counter = document.getElementById('willNotApplyCounter');
    if (!(wrap instanceof HTMLElement) || !(textarea instanceof HTMLTextAreaElement)) return;

    const updateCounter = () => {
      if (counter) counter.textContent = String(textarea.value.length);
    };

    const updateVisibility = () => {
      const checked = document.querySelector('input[name="will_apply"]:checked');
      const val = checked instanceof HTMLInputElement ? checked.value : '';
      const show = val === 'no';
      wrap.classList.toggle('hidden', !show);
      textarea.required = show;
      textarea.disabled = !show;
      updateCounter();
    };

    document.addEventListener('change', (e) => {
      const target = e.target;
      if (!(target instanceof HTMLElement)) return;
      if (target.matches('input[name="will_apply"]')) updateVisibility();
    });

    textarea.addEventListener('input', updateCounter);

    // Initial state (handles saved data / server-side validation errors)
    updateVisibility();
    if ((textarea.value || '').length > 0) wrap.classList.remove('hidden');
    textarea.required = !wrap.classList.contains('hidden');
    textarea.disabled = wrap.classList.contains('hidden');
    updateCounter();
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

  const initTomSelectAcademyCourses = () => {
    const select = document.querySelector('#academy_course_selector[data-tom-select="courses"]');
    const addBtn = document.getElementById('addCourseBtn');
    const container = document.getElementById('selectedCoursesContainer');
    if (!(select instanceof HTMLSelectElement) || !addBtn || !container) return;
    if (typeof window.TomSelect !== 'function') return;

    // Avoid double-init
    if (select.tomselect) return;

    const url = select.dataset.tomUrl || '/api/courses';

    // Get currently selected courses
    const getSelectedCourses = () => {
      const inputs = container.querySelectorAll('input[name="dswd_courses[]"]');
      return Array.from(inputs).map((i) => i.value);
    };

    const tomInstance = new window.TomSelect(select, {
      valueField: 'value',
      labelField: 'text',
      searchField: ['text'],
      maxItems: 1,
      create: false,
      persist: false,
      preload: true,
      placeholder: select.getAttribute('placeholder') || 'Search courses…',
      closeAfterSelect: true,
      selectOnTab: true,
      load: function (query, callback) {
        const reqUrl = query
          ? `${url}?q=${encodeURIComponent(query)}`
          : url;
        fetch(reqUrl, { credentials: 'same-origin' })
          .then((res) => res.json())
          .then((json) => {
            const data = json && json.success && Array.isArray(json.data) ? json.data : [];
            // Filter out already selected courses
            const selected = getSelectedCourses();
            const filtered = data.filter((item) => !selected.includes(item.value));
            callback(filtered);
          })
          .catch(() => callback());
      },
      render: {
        option: function (data, escape) {
          return `<div class="py-2 px-3">${escape(data.text)}</div>`;
        },
      },
    });

    // Add course when clicking the + button
    const addCourse = () => {
      const value = tomInstance.getValue();
      if (!value) return;

      // Check if already selected
      if (getSelectedCourses().includes(value)) {
        tomInstance.clear();
        return;
      }

      // Remove placeholder
      const placeholder = container.querySelector('#noCoursesPlaceholder');
      if (placeholder) placeholder.remove();

      // Create tag element
      const tag = document.createElement('div');
      tag.className = 'course-tag inline-flex items-center gap-2 px-4 py-2 bg-white rounded-xl border border-slate-200 shadow-sm';
      tag.innerHTML = `
        <span class="text-sm font-bold text-slate-700">${value.replace(/</g, '&lt;').replace(/>/g, '&gt;')}</span>
        <button type="button" class="remove-course text-slate-400 hover:text-red-500 transition-colors">
          <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path></svg>
        </button>
        <input type="hidden" name="dswd_courses[]" value="${value.replace(/"/g, '&quot;')}">
      `;

      container.appendChild(tag);

      // Clear the select
      tomInstance.clear();
      tomInstance.clearOptions();
      tomInstance.load('');
    };

    addBtn.addEventListener('click', addCourse);

    // Also add on Enter key
    select.addEventListener('keydown', (e) => {
      if (e.key === 'Enter') {
        e.preventDefault();
        addCourse();
      }
    });

    // Remove course handler (delegated)
    container.addEventListener('click', (e) => {
      const btn = e.target.closest('.remove-course');
      if (!btn) return;
      const tag = btn.closest('.course-tag');
      if (tag) {
        tag.remove();
        // Refresh options to include the removed course
        tomInstance.clearOptions();
        tomInstance.load('');
      }

      // Show placeholder if no courses left
      if (!container.querySelector('.course-tag')) {
        const p = document.createElement('p');
        p.id = 'noCoursesPlaceholder';
        p.className = 'text-sm text-slate-400 italic';
        p.textContent = 'No courses selected yet. Use the dropdown above to add courses.';
        container.appendChild(p);
      }
    });
  };

  const initDswdTrainingToggle = () => {
    const radios = document.querySelectorAll('input[name="availed_dswd_training"]');
    const section = document.getElementById('coursesSection');
    if (!radios.length || !section) return;

    const toggleSection = (show) => {
      if (show) {
        // Show immediately with animation
        section.classList.remove('hidden');
        section.style.opacity = '0';
        section.style.transform = 'translateY(-10px)';
        requestAnimationFrame(() => {
          section.style.transition = 'opacity 150ms ease-out, transform 150ms ease-out';
          section.style.opacity = '1';
          section.style.transform = 'translateY(0)';
        });
      } else {
        // Hide with quick fade
        section.style.transition = 'opacity 100ms ease-out';
        section.style.opacity = '0';
        setTimeout(() => {
          section.classList.add('hidden');
          section.style.transform = '';
          section.style.opacity = '';
          section.style.transition = '';
        }, 100);
      }
    };

    radios.forEach((radio) => {
      radio.addEventListener('change', () => {
        toggleSection(radio.value === 'yes');
      });
    });

    // Set initial state based on checked radio
    const checked = document.querySelector('input[name="availed_dswd_training"]:checked');
    if (checked && checked.value === 'yes' && section.classList.contains('hidden')) {
      section.classList.remove('hidden');
    }
  };

  // Initial pass for pages that start with a saved selection
  document.addEventListener('DOMContentLoaded', () => {
    document.querySelectorAll('form').forEach((form) =>
      applyOfficeAssignmentRules(form)
    );

    initTomSelectPositions();
    initTomSelectAcademyCourses();
    initDswdTrainingToggle();
    initWillNotApplyReason();
  });
})();
