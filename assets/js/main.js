/**
 * THE ROMA PALACE — Luxury Frontend Interactive Controller
 * BTech CSE DBMS Mini Project
 */

document.addEventListener('DOMContentLoaded', () => {
  initHeaderScroll();
  initMobileMenu();
  initBookingDateInputs();
  initMenuTabs();
  initFilterControls();
  initEnhanceStayCalculators();
  initCounterAnimations();
});

/**
 * Dynamic Header Scroll & Contrast Observer
 */
function initHeaderScroll() {
  const header = document.querySelector('.site-header');
  if (!header) return;

  const handleScroll = () => {
    if (window.scrollY > 40) {
      header.classList.add('scrolled');
    } else {
      header.classList.remove('scrolled');
    }
  };

  window.addEventListener('scroll', handleScroll, { passive: true });
  handleScroll(); // Initial invocation
}

/**
 * Mobile Navigation Drawer
 */
function initMobileMenu() {
  const toggleBtn = document.querySelector('.mobile-nav-toggle');
  const navLinks = document.querySelector('.nav-links');
  if (!toggleBtn || !navLinks) return;

  toggleBtn.addEventListener('click', () => {
    const isOpen = navLinks.classList.toggle('mobile-active');
    toggleBtn.innerHTML = isOpen ? '<i class="fa-solid fa-xmark"></i>' : '<i class="fa-solid fa-bars"></i>';
  });
}

/**
 * Auto-set Minimum Dates for Booking Engines
 */
function initBookingDateInputs() {
  const checkinInput = document.getElementById('checkin_date') || document.querySelector('input[name="check_in"]');
  const checkoutInput = document.getElementById('checkout_date') || document.querySelector('input[name="check_out"]');

  if (!checkinInput || !checkoutInput) return;

  const today = new Date();
  const tomorrow = new Date(today);
  tomorrow.setDate(tomorrow.getDate() + 1);

  const formatDate = (d) => d.toISOString().split('T')[0];

  if (!checkinInput.value) checkinInput.value = formatDate(today);
  if (!checkoutInput.value) checkoutInput.value = formatDate(tomorrow);

  checkinInput.min = formatDate(today);
  checkoutInput.min = formatDate(tomorrow);

  checkinInput.addEventListener('change', () => {
    const selectedIn = new Date(checkinInput.value);
    const nextDay = new Date(selectedIn);
    nextDay.setDate(nextDay.getDate() + 1);
    checkoutInput.min = formatDate(nextDay);
    if (new Date(checkoutInput.value) <= selectedIn) {
      checkoutInput.value = formatDate(nextDay);
    }
    if (typeof calculateBookingTotal === 'function') calculateBookingTotal();
  });

  checkoutInput.addEventListener('change', () => {
    if (typeof calculateBookingTotal === 'function') calculateBookingTotal();
  });
}

/**
 * Interactive Menu Category Filter Tabs
 */
function initMenuTabs() {
  const tabs = document.querySelectorAll('.menu-tab-btn');
  const items = document.querySelectorAll('.menu-item-row');
  if (!tabs.length || !items.length) return;

  tabs.forEach(tab => {
    tab.addEventListener('click', () => {
      tabs.forEach(t => t.classList.remove('active'));
      tab.classList.add('active');
      const category = tab.getAttribute('data-category');

      items.forEach(item => {
        if (category === 'all' || item.getAttribute('data-category') === category) {
          item.style.display = 'flex';
        } else {
          item.style.display = 'none';
        }
      });
    });
  });
}

/**
 * Filter Controls (Rooms & Suites)
 */
function initFilterControls() {
  const filterBtns = document.querySelectorAll('.filter-btn[data-filter]');
  const cards = document.querySelectorAll('.room-card[data-type]');
  if (!filterBtns.length || !cards.length) return;

  filterBtns.forEach(btn => {
    btn.addEventListener('click', () => {
      filterBtns.forEach(b => b.classList.remove('active'));
      btn.classList.add('active');
      const filter = btn.getAttribute('data-filter');

      cards.forEach(card => {
        if (filter === 'all' || card.getAttribute('data-type') === filter) {
          card.style.display = 'flex';
        } else {
          card.style.display = 'none';
        }
      });
    });
  });
}

/**
 * Real-time Live Price Calculator for Multi-Step Booking Flow
 */
function initEnhanceStayCalculators() {
  const addonCheckboxes = document.querySelectorAll('.addon-checkbox');
  if (!addonCheckboxes.length) return;

  addonCheckboxes.forEach(cb => {
    cb.addEventListener('change', () => {
      const card = cb.closest('.addon-card');
      if (card) {
        if (cb.checked) card.classList.add('selected');
        else card.classList.remove('selected');
      }
      calculateBookingTotal();
    });
  });
}

function calculateBookingTotal() {
  const roomPriceEl = document.getElementById('base_room_price');
  const checkinEl = document.getElementById('checkin_date');
  const checkoutEl = document.getElementById('checkout_date');
  if (!roomPriceEl || !checkinEl || !checkoutEl) return;

  const basePricePerNight = parseFloat(roomPriceEl.value || 0);
  const checkin = new Date(checkinEl.value);
  const checkout = new Date(checkoutEl.value);

  let nights = Math.max(1, Math.round((checkout - checkin) / (1000 * 60 * 60 * 24)));
  if (isNaN(nights) || nights < 1) nights = 1;

  const totalRoomCharges = basePricePerNight * nights;

  let serviceCharges = 0;
  document.querySelectorAll('.addon-checkbox:checked').forEach(cb => {
    const price = parseFloat(cb.getAttribute('data-price') || 0);
    serviceCharges += price;
  });

  const subtotal = totalRoomCharges + serviceCharges;
  const gstRate = 0.18; // 18% Luxury Hotel GST
  const taxAmount = subtotal * gstRate;
  const grandTotal = subtotal + taxAmount;

  // Update UI Elements
  const nightsCountEl = document.getElementById('display_nights_count');
  const roomChargesEl = document.getElementById('display_room_charges');
  const serviceChargesEl = document.getElementById('display_service_charges');
  const taxAmountEl = document.getElementById('display_tax_amount');
  const grandTotalEl = document.getElementById('display_grand_total');

  if (nightsCountEl) nightsCountEl.textContent = `${nights} Night${nights > 1 ? 's' : ''}`;
  if (roomChargesEl) roomChargesEl.textContent = '₹' + totalRoomCharges.toLocaleString('en-IN');
  if (serviceChargesEl) serviceChargesEl.textContent = '₹' + serviceCharges.toLocaleString('en-IN');
  if (taxAmountEl) taxAmountEl.textContent = '₹' + Math.round(taxAmount).toLocaleString('en-IN');
  if (grandTotalEl) grandTotalEl.textContent = '₹' + Math.round(grandTotal).toLocaleString('en-IN');
}

/**
 * Animated Number Counters
 */
function initCounterAnimations() {
  const counters = document.querySelectorAll('.stat-number[data-target]');
  if (!counters.length) return;

  const observer = new IntersectionObserver((entries) => {
    entries.forEach(entry => {
      if (entry.isIntersecting) {
        const counter = entry.target;
        const target = +counter.getAttribute('data-target');
        const duration = 1500;
        const start = 0;
        const startTime = performance.now();

        const updateCounter = (currentTime) => {
          const elapsed = currentTime - startTime;
          const progress = Math.min(elapsed / duration, 1);
          const current = Math.floor(progress * target);
          counter.textContent = current + (counter.getAttribute('data-suffix') || '');

          if (progress < 1) {
            requestAnimationFrame(updateCounter);
          }
        };

        requestAnimationFrame(updateCounter);
        observer.unobserve(counter);
      }
    });
  }, { threshold: 0.5 });

  counters.forEach(c => observer.observe(c));
}
