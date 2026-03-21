// main.js — No Vite, no module imports. Plain JS that works directly on any PHP host.

document.addEventListener('DOMContentLoaded', function() {

// ─── Skeleton loader — hide after page is ready ──────────────────────────────
var skeleton = document.getElementById('skeletonLoader')
if (skeleton) {
  skeleton.classList.add('skeleton-hidden')
  setTimeout(function() { skeleton.remove() }, 500)
}

// ─── Static hero subtitle (no typing animation) ─────────────────────────────
var heroSub = document.getElementById('heroSubtitle')
if (heroSub) {
  var text = heroSub.getAttribute('data-text') || ''
  heroSub.textContent = text
  heroSub.style.borderRight = 'none'
}

// ─── Reduced motion check ────────────────────────────────────────────────────
var prefersReducedMotion = window.matchMedia('(prefers-reduced-motion: reduce)').matches
var isNarrowViewport = window.matchMedia('(max-width: 768px)').matches
var isTouchDevice = ('ontouchstart' in window) || (navigator.maxTouchPoints > 0)

// ─── Hero card 3D tilt (mouse-tracking) ──────────────────────────────────────
var heroWrap = document.getElementById('heroCardWrap')
var heroCard = document.getElementById('heroCard')

if (heroWrap && heroCard && heroWrap.getAttribute('data-tilt') === '1' && !prefersReducedMotion && !isNarrowViewport && !isTouchDevice) {
  var tiltMax    = 14
  var tiltActive = false

  heroWrap.addEventListener('mousemove', function(e) {
    if (!tiltActive) {
      tiltActive = true
      window.requestAnimationFrame(function() {
        var rect     = heroWrap.getBoundingClientRect()
        var cx       = rect.left + rect.width  / 2
        var cy       = rect.top  + rect.height / 2
        var dx       = (e.clientX - cx) / (rect.width  / 2)
        var dy       = (e.clientY - cy) / (rect.height / 2)
        var rotateX  =  dy * -tiltMax
        var rotateY  =  dx *  tiltMax
        heroCard.style.webkitTransition = 'none'
        heroCard.style.transition       = 'none'
        heroCard.style.webkitTransform  = 'rotateX(' + rotateX + 'deg) rotateY(' + rotateY + 'deg) translateZ(8px)'
        heroCard.style.transform        = 'rotateX(' + rotateX + 'deg) rotateY(' + rotateY + 'deg) translateZ(8px)'
        tiltActive = false
      })
    }
  })

  heroWrap.addEventListener('mouseleave', function() {
    heroCard.style.webkitTransition = '-webkit-transform 0.6s cubic-bezier(0.23, 1, 0.32, 1)'
    heroCard.style.transition       = 'transform 0.6s cubic-bezier(0.23, 1, 0.32, 1)'
    heroCard.style.webkitTransform  = 'rotateX(0deg) rotateY(0deg) translateZ(0)'
    heroCard.style.transform        = 'rotateX(0deg) rotateY(0deg) translateZ(0)'
  })
}

// ─── Navbar: element refs ─────────────────────────────────────────────────────
var navbar     = document.querySelector('.navbar')
var menuToggle = document.getElementById('menuToggle')
var navLinks   = document.getElementById('navLinks')
var navLinkItems = Array.prototype.slice.call(document.querySelectorAll('.nav-link'))

function setMenuState(isOpen) {
  if (!menuToggle || !navLinks) return
  if (isOpen && navbar) {
    // Ensure the fixed navbar is visible before expanding mobile links.
    navbar.classList.remove('nav-hidden')
  }
  navLinks.classList.toggle('active', isOpen)
  menuToggle.classList.toggle('open', isOpen)
  menuToggle.setAttribute('aria-expanded', String(isOpen))
  document.body.style.overflow = isOpen ? 'hidden' : ''
}

function closeMobileMenu() {
  setMenuState(false)
}

function syncMobileNavViewportVars() {
  if (!navbar) return
  var navHeight = Math.round(navbar.getBoundingClientRect().height || 64)
  var viewportHeight = window.innerHeight || document.documentElement.clientHeight || 0
  var available = Math.max(180, viewportHeight - navHeight)
  document.documentElement.style.setProperty('--mobile-nav-offset', navHeight + 'px')
  document.documentElement.style.setProperty('--mobile-menu-max-height', available + 'px')
}

// ─── Mobile Menu: open/close with hamburger → X animation ────────────────────
if (menuToggle && navLinks) {

  syncMobileNavViewportVars()

  // Toggle menu on hamburger click
  menuToggle.addEventListener('click', function() {
    var isOpen = !navLinks.classList.contains('active')
    if (isOpen) {
      syncMobileNavViewportVars()
    }
    setMenuState(isOpen)
  })

  // Close menu when any nav link is clicked
  navLinkItems.forEach(function(link) {
    link.addEventListener('click', function() {
      closeMobileMenu()
    })
  })

  // Close menu when tapping outside (critical for mobile UX)
  document.addEventListener('click', function(e) {
    if (
      navLinks.classList.contains('active') &&
      !navLinks.contains(e.target) &&
      !menuToggle.contains(e.target)
    ) {
      closeMobileMenu()
    }
  })

  // Close menu with Escape for keyboard and assistive users
  document.addEventListener('keydown', function(e) {
    if (e.key === 'Escape' && navLinks.classList.contains('active')) {
      closeMobileMenu()
      menuToggle.focus()
    }
  })

  // Ensure state is reset when viewport changes to desktop width
  window.addEventListener('resize', function() {
    syncMobileNavViewportVars()
    if (window.matchMedia('(min-width: 769px)').matches) {
      closeMobileMenu()
    }
  })

  if (window.visualViewport) {
    window.visualViewport.addEventListener('resize', syncMobileNavViewportVars)
    window.visualViewport.addEventListener('scroll', syncMobileNavViewportVars)
  }
}

// ─── Navbar scroll + active link (single rAF loop) ──────────────────────────
var lastScrollY = window.scrollY
var scrollTicking = false
var pageSections = Array.prototype.slice.call(document.querySelectorAll('section[id]'))
var hashNavLinks = navLinkItems.filter(function(link) {
  return (link.getAttribute('href') || '').charAt(0) === '#'
})

function updateActiveNavLink() {
  if (!hashNavLinks.length || !pageSections.length) return

  var current = ''
  var triggerY = window.scrollY + 220

  pageSections.forEach(function(section) {
    if (triggerY >= section.offsetTop) {
      current = section.getAttribute('id') || ''
    }
  })

  hashNavLinks.forEach(function(link) {
    var isActive = (link.getAttribute('href') === '#' + current)
    link.classList.toggle('active', isActive)
  })
}

function updateNavbar() {
  if (!navbar) {
    scrollTicking = false
    return
  }

  var currentScrollY = window.scrollY

  // Glass blur — activates after 20px
  if (currentScrollY > 20) {
    navbar.classList.add('scrolled')
  } else {
    navbar.classList.remove('scrolled')
  }

  // Hide on scroll down, show on scroll up — only after 80px
  if (navLinks && navLinks.classList.contains('active')) {
    navbar.classList.remove('nav-hidden')
  } else if (currentScrollY > 80) {
    if (currentScrollY > lastScrollY) {
      navbar.classList.add('nav-hidden')
    } else {
      navbar.classList.remove('nav-hidden')
    }
  } else {
    navbar.classList.remove('nav-hidden')
  }

  updateActiveNavLink()

  lastScrollY = currentScrollY
  scrollTicking = false
}

window.addEventListener('scroll', function() {
  if (!scrollTicking) {
    window.requestAnimationFrame(updateNavbar)
    scrollTicking = true
  }
})
updateNavbar()

// ─── Contact Form — PHP/MySQL backend ────────────────────────────────────────
var contactForm = document.getElementById('contactForm')
var formStatus  = document.getElementById('formStatus')

function showStatus(message, isSuccess) {
  if (!formStatus) return
  formStatus.textContent   = message
  formStatus.style.display = 'block'
  formStatus.style.color   = isSuccess ? '#22c55e' : '#ef4444'
  setTimeout(function() { formStatus.style.display = 'none' }, 6000)
}

if (contactForm) {
  contactForm.addEventListener('invalid', function(e) {
    if (!e.target) return
    e.target.scrollIntoView({ behavior: 'smooth', block: 'center' })
  }, true)

  contactForm.addEventListener('submit', async function(e) {
    e.preventDefault()
    e.stopPropagation()

    var submitBtn    = document.getElementById('submitBtn')
    var originalText = submitBtn.textContent

    submitBtn.disabled    = true
    submitBtn.setAttribute('aria-busy', 'true')
    submitBtn.textContent = 'Sending...'
    if (formStatus) formStatus.style.display = 'none'

    try {
      var response = await fetch('contact.php', {
        method: 'POST',
        body:   new FormData(contactForm)
      })

      var text = await response.text()
      var data

      try {
        data = JSON.parse(text)
      } catch (_) {
        console.error('Non-JSON response from PHP:', text)
        showStatus('Server error. Open browser console for details.', false)
        submitBtn.textContent           = 'Error — Try Again'
        submitBtn.style.backgroundColor = '#ef4444'
        setTimeout(function() {
          submitBtn.textContent           = originalText
          submitBtn.style.backgroundColor = ''
          submitBtn.setAttribute('aria-busy', 'false')
          submitBtn.disabled              = false
        }, 3000)
        return
      }

      if (data.success) {
        showStatus('Your message has been sent successfully!', true)
        contactForm.reset()
        updateCharCount()
        submitBtn.textContent           = 'Message Sent!'
        submitBtn.style.backgroundColor = '#10b981'
        setTimeout(function() {
          submitBtn.textContent           = originalText
          submitBtn.style.backgroundColor = ''
          submitBtn.setAttribute('aria-busy', 'false')
          submitBtn.disabled              = false
        }, 3000)
      } else {
        console.error('PHP error:', data.message)
        showStatus(data.message || 'Something went wrong. Please try again.', false)
        submitBtn.textContent           = 'Error — Try Again'
        submitBtn.style.backgroundColor = '#ef4444'
        setTimeout(function() {
          submitBtn.textContent           = originalText
          submitBtn.style.backgroundColor = ''
          submitBtn.setAttribute('aria-busy', 'false')
          submitBtn.disabled              = false
        }, 3000)
      }

    } catch (err) {
      console.error('Network error:', err)
      showStatus('Could not reach the server. Check your connection.', false)
      submitBtn.textContent           = 'Error — Try Again'
      submitBtn.style.backgroundColor = '#ef4444'
      setTimeout(function() {
        submitBtn.textContent           = originalText
        submitBtn.style.backgroundColor = ''
        submitBtn.setAttribute('aria-busy', 'false')
        submitBtn.disabled              = false
      }, 3000)
    }
  })
}

// ─── Character counter for message textarea ──────────────────────────────────
var messageField = document.getElementById('message')
var charCount    = document.getElementById('charCount')

function updateCharCount() {
  if (!messageField || !charCount) return
  var len = messageField.value.length
  charCount.textContent = len + ' / 2000'
  charCount.style.color = len > 1800 ? '#ef4444' : ''
}

if (messageField && charCount) {
  messageField.addEventListener('input', updateCharCount)
  updateCharCount()
}

// ─── Dark mode toggle ────────────────────────────────────────────────────────
var themeToggle = document.getElementById('themeToggle')
var root        = document.documentElement

function getPreferredTheme() {
  var saved = localStorage.getItem('theme')
  if (saved) return saved
  return window.matchMedia('(prefers-color-scheme: dark)').matches ? 'dark' : 'light'
}

function applyTheme(theme) {
  root.setAttribute('data-theme', theme)
  localStorage.setItem('theme', theme)
}

applyTheme(getPreferredTheme())

if (themeToggle) {
  themeToggle.addEventListener('click', function() {
    var current = root.getAttribute('data-theme') || 'light'
    applyTheme(current === 'dark' ? 'light' : 'dark')
  })
}

// ─── Back to Top button ──────────────────────────────────────────────────────
var backToTop = document.getElementById('backToTop')

if (backToTop) {
  window.addEventListener('scroll', function() {
    if (window.scrollY > 400) {
      backToTop.classList.add('visible')
    } else {
      backToTop.classList.remove('visible')
    }
  })

  backToTop.addEventListener('click', function() {
    window.scrollTo({ top: 0, behavior: 'smooth' })
  })
}

// ─── Scroll reveal animations ────────────────────────────────────────────────
if (!prefersReducedMotion) {
  var revealElements = document.querySelectorAll('.skill-card, .project-card, .about-text, .cert-card')
  if (revealElements.length && 'IntersectionObserver' in window) {
    var revealObserver = new IntersectionObserver(function(entries) {
      entries.forEach(function(entry) {
        if (entry.isIntersecting) {
          entry.target.classList.add('revealed')
          revealObserver.unobserve(entry.target)
        }
      })
    }, { threshold: 0.1, rootMargin: '0px 0px -40px 0px' })

    revealElements.forEach(function(el) {
      el.classList.add('reveal-on-scroll')
      revealObserver.observe(el)
    })
  }
}

// ─── Heatmap cell click → show note panel ────────────────────────────────────
var heatmapGrid   = document.getElementById('heatmapGrid')
var notePanel     = document.getElementById('activityNotePanel')
var noteDate      = document.getElementById('activityNoteDate')
var noteText      = document.getElementById('activityNoteText')
var noteMeta      = document.getElementById('activityNoteMeta')
var activeCell    = null

if (heatmapGrid && notePanel) {
  heatmapGrid.addEventListener('click', function(e) {
    var cell = e.target
    if (!cell.classList.contains('heatmap-cell')) return

    var date = cell.getAttribute('data-date')
    var count = parseInt(cell.getAttribute('data-count') || '0', 10)

    if (activeCell === cell) {
      notePanel.style.display = 'none'
      activeCell = null
      return
    }

    activeCell = cell

    var parts = date.split('-')
    var dateObj = new Date(parts[0], parts[1] - 1, parts[2])
    var options = { weekday: 'long', year: 'numeric', month: 'long', day: 'numeric' }
    var dateStr = dateObj.toLocaleDateString('en-US', options)

    noteDate.textContent = dateStr

    var notes = (typeof window.habitNotes !== 'undefined') ? window.habitNotes : {}
    if (notes[date] && notes[date].note) {
      noteText.textContent = notes[date].note
      noteText.classList.remove('empty')
      var loggedAt = notes[date].created_at ? ('Logged: ' + notes[date].created_at) : ''
      var editedAt = (notes[date].updated_at && notes[date].updated_at !== notes[date].created_at)
        ? (' · Edited: ' + notes[date].updated_at)
        : ''
      noteMeta.textContent = loggedAt + editedAt
    } else if (count > 0) {
      noteText.textContent = 'No note logged for this day.'
      noteText.classList.add('empty')
      noteMeta.textContent = count + ' habit' + (count > 1 ? 's' : '') + ' completed'
    } else {
      noteText.textContent = 'No activity on this day.'
      noteText.classList.add('empty')
      noteMeta.textContent = ''
    }

    notePanel.style.display = 'block'
    notePanel.scrollIntoView({ behavior: 'smooth', block: 'nearest' })
  })
}

}) // end DOMContentLoaded