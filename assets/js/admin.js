/**
 * THE ROMA PALACE — Admin Control Center & Analytics Scripts
 * BTech CSE DBMS Mini Project
 */

document.addEventListener('DOMContentLoaded', () => {
  initAdminLiveClock();
  initTableSearch();
  initSqlPresentationTabs();
  initDeleteConfirmations();
});

/**
 * Live Clock in Header
 */
function initAdminLiveClock() {
  const clockEl = document.getElementById('adminLiveClock');
  if (!clockEl) return;

  const updateClock = () => {
    const now = new Date();
    clockEl.textContent = now.toLocaleDateString('en-IN', {
      day: '2-digit', month: 'short', year: 'numeric',
      hour: '2-digit', minute: '2-digit', second: '2-digit'
    });
  };

  updateClock();
  setInterval(updateClock, 1000);
}

/**
 * Instant Client-Side Table Search Filter
 */
function initTableSearch() {
  const searchInputs = document.querySelectorAll('[data-table-search]');
  searchInputs.forEach(input => {
    const targetTableId = input.getAttribute('data-table-search');
    const table = document.getElementById(targetTableId);
    if (!table) return;

    input.addEventListener('input', () => {
      const term = input.value.toLowerCase().trim();
      const rows = table.querySelectorAll('tbody tr');

      rows.forEach(row => {
        const text = row.textContent.toLowerCase();
        row.style.display = text.includes(term) ? '' : 'none';
      });
    });
  });
}

/**
 * Project Demo & Viva Presentation SQL Runner Controller
 */
function initSqlPresentationTabs() {
  const sqlTabs = document.querySelectorAll('.sql-tab-btn[data-query]');
  const sqlEditor = document.getElementById('sqlLiveEditor');
  const sqlTitle = document.getElementById('sqlQueryTitle');
  const sqlConcept = document.getElementById('sqlQueryConcept');

  if (!sqlTabs.length || !sqlEditor) return;

  sqlTabs.forEach(tab => {
    tab.addEventListener('click', () => {
      sqlTabs.forEach(t => t.classList.remove('active'));
      tab.classList.add('active');

      const query = tab.getAttribute('data-query');
      const title = tab.getAttribute('data-title');
      const concept = tab.getAttribute('data-concept');

      if (sqlEditor) sqlEditor.value = query;
      if (sqlTitle) sqlTitle.textContent = title;
      if (sqlConcept) sqlConcept.textContent = concept;
    });
  });
}

/**
 * Confirmation dialogs for administrative deletions
 */
function initDeleteConfirmations() {
  document.querySelectorAll('[data-confirm]').forEach(el => {
    el.addEventListener('click', (e) => {
      const msg = el.getAttribute('data-confirm') || 'Are you sure you want to proceed with this action?';
      if (!confirm(msg)) {
        e.preventDefault();
      }
    });
  });
}
