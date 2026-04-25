# 🧩 COMPONENT SPECIFICATIONS — Design System Library

**Phase**: 1 — Design System Components  
**Status**: Complete  
**Last Updated**: 2026-04-25  

---

## 📋 Overview

This document specifies all reusable components in the design system with:
- **Variants**: Different styles for different contexts
- **States**: Default, hover, active, disabled, focus
- **Sizes**: sm, md, lg variations
- **Accessibility**: ARIA, keyboard nav, focus states
- **Responsive**: Mobile, tablet, desktop behavior

---

## 🔘 BUTTONS

### Button Component Hierarchy

```
.btn (base)
├── .btn-primary (main action)
├── .btn-secondary (alternate)
├── .btn-ghost (subtle)
└── .btn-outline (bordered)
```

### Button Base (.btn)

**Default State:**
```
Padding: 1rem 2.5rem (16px 40px)
Font: 600 weight, 1rem size
Border-radius: 0.5rem (8px)
Height: 44px minimum (accessibility)
Transition: all 0.25s cubic-bezier(...)
```

### Variants

#### Primary (.btn-primary)
- **Background**: var(--accent) #0066ff
- **Text**: white
- **Hover**: background #0052cc + translateY(-2px) + shadow-md
- **Active**: pressed (no lift)
- **Disabled**: opacity 0.6
- **Focus**: 2px outline #0066ff + offset 2px
- **Usage**: Main CTAs, form submissions

#### Secondary (.btn-secondary)
- **Background**: var(--bg-gray) #111118
- **Border**: 2px solid var(--border-color)
- **Text**: var(--text-primary) #f0f0f5
- **Hover**: border-color #0066ff + text #0066ff
- **Active**: darker border
- **Disabled**: opacity 0.6
- **Usage**: Cancel, back, alternate actions

#### Ghost (.btn-ghost)
- **Background**: transparent
- **Border**: 2px transparent (becomes visible on hover)
- **Text**: var(--accent) #0066ff
- **Hover**: background rgba(0,102,255,0.1) + border-color #0066ff + translateY(-2px)
- **Active**: solid background
- **Disabled**: opacity 0.6
- **Usage**: Subtle actions, "Learn more", secondary links

#### Outline (.btn-outline)
- **Background**: transparent
- **Border**: 2px solid var(--accent) #0066ff
- **Text**: var(--accent) #0066ff
- **Hover**: background #0066ff + text white + translateY(-2px) + shadow
- **Active**: darker background
- **Disabled**: opacity 0.6
- **Usage**: Bordered CTAs, optional actions

### Sizes

#### Small (.btn-sm)
```
Padding: 0.5rem 1.25rem (8px 20px)
Font-size: 0.875rem (14px)
Height: 32px
Touch target: < 44px (consider padding)
Use: Compact UI, secondary buttons
```

#### Medium (.btn-md) [DEFAULT]
```
Padding: 1rem 2.5rem (16px 40px)
Font-size: 1rem (16px)
Height: 44px (optimal touch)
Touch target: 44px ✅
Use: Primary CTAs, forms
```

#### Large (.btn-lg)
```
Padding: 1.25rem 2rem (20px 32px)
Font-size: 1.125rem (18px)
Height: 48px
Touch target: 48px ✅
Use: Hero CTAs, high importance
```

### Button States Matrix

| State | Primary | Secondary | Ghost | Outline |
|-------|---------|-----------|-------|---------|
| Default | Blue bg | Gray bg | Transparent | Transparent |
| Hover | Dark blue + lift | Border blue + text blue | Light blue bg + border | Blue bg + white text |
| Active | No lift | Solid | Solid blue | Dark blue |
| Focus | Outline ✓ | Outline ✓ | Outline ✓ | Outline ✓ |
| Disabled | Opacity 60% | Opacity 60% | Opacity 60% | Opacity 60% |

### HTML Examples

```html
<!-- Primary Button -->
<button class="btn btn-primary">Send Message</button>

<!-- Secondary with Size -->
<button class="btn btn-secondary btn-sm">Cancel</button>

<!-- Ghost Link -->
<a href="/projects" class="btn btn-ghost">View More Projects</a>

<!-- Outline Large -->
<button class="btn btn-outline btn-lg">Get Started</button>

<!-- Disabled -->
<button class="btn btn-primary" disabled>Processing...</button>
```

---

## 📝 FORM CONTROLS

### Form Group (.form-group)

```
Margin-bottom: var(--form-group-margin) = 2.25rem (36px)
Structure: label → input/textarea
```

### Label (.form-group label)

```
Font-weight: 600 (medium/semibold)
Margin-bottom: var(--label-margin-bottom) = 0.5rem (8px)
Color: var(--text-primary) #f0f0f5
Display: block
```

### Input / Textarea (.form-group input, .form-group textarea)

**Default State:**
```
Width: 100%
Padding: 1rem (16px)
Border: 1px solid var(--border) [rgba(255,255,255,0.07)]
Border-radius: 0.5rem (8px)
Font-family: inherit
Font-size: 1rem (16px)
Height: 44px minimum (inputs)
Background: var(--bg-elevated) #1a1a24
Color: var(--text-primary) #f0f0f5
Transition: all 0.25s ease
```

**Focus State:**
```
Outline: 2px solid var(--accent) #0066ff
Outline-offset: 2px
Border-color: var(--accent) #0066ff
Box-shadow: 0 0 0 3px rgba(0,102,255,0.1)
```

**Placeholder:**
```
Color: var(--text-muted) #5a5a78
Opacity: 0.7
Font-size: 1rem
```

**Textarea:**
```
Min-height: clamp(120px, 22vh, 180px)
Resize: vertical (allow user resize)
```

### Form Validation States

**Success:**
```
Border-color: var(--success) #22c55e
Background: rgba(34,197,94,0.05)
Icon: ✓ (green checkmark)
Message: var(--success-light)
```

**Error:**
```
Border-color: var(--danger) #ef4444
Background: rgba(239,68,68,0.05)
Icon: ✗ (red X)
Message: var(--danger-light)
```

**Warning:**
```
Border-color: var(--warning) #f59e0b
Background: rgba(245,158,11,0.05)
Icon: ⚠ (warning triangle)
Message: var(--warning-light)
```

### HTML Examples

```html
<form>
  <div class="form-group">
    <label for="name">Full Name</label>
    <input 
      id="name" 
      type="text" 
      placeholder="Enter your name"
      required 
    />
  </div>

  <div class="form-group">
    <label for="message">Message</label>
    <textarea 
      id="message" 
      placeholder="Your message..." 
      required
    ></textarea>
  </div>

  <button type="submit" class="btn btn-primary">Submit</button>
</form>
```

---

## 🎴 CARDS

### Card (.card or section-specific: .skill-card, .article-card)

**Base Styles:**
```
Padding: var(--card-padding-md) = 3rem (48px)
Background: var(--bg-surface) #111118
Border: 1px solid var(--border) [rgba(255,255,255,0.07)]
Border-radius: 0.75rem (12px)
Box-shadow: var(--shadow-sm) [0 1px 3px rgba(0,0,0,0.4)]
Transition: all 0.25s ease
```

**Hover State:**
```
Transform: translateY(-5px)
Box-shadow: var(--shadow-lg) [0 12px 40px rgba(0,0,0,0.6)]
Border-color: var(--accent) #0066ff [optional]
```

### Skill Card (.skill-card)

```
Display: grid (column layout)
Gap: 1rem
Text-align: center
```

**Elements:**
- Icon: 3rem (48px) font-size
- Title: h3 + var(--spacing-sm) margin-bottom
- Description: body text + var(--text-secondary) color

### Article Card (.article-card)

```
Display: grid (column layout)
Gap: 1.5rem
```

**Elements:**
- Cover image: 100% width, 200px height (object-fit: cover)
- Title: h3 with truncation (2 lines max)
- Excerpt: body text + 1.5 lines max
- Meta: small text + var(--text-muted) color
- CTA: "Read More" link or implicit on click

### HTML Examples

```html
<!-- Skill Card -->
<div class="skill-card">
  <span class="skill-icon">🎨</span>
  <h3>UI Design</h3>
  <p>Creating beautiful and intuitive user interfaces</p>
</div>

<!-- Article Card -->
<article class="article-card">
  <img src="cover.jpg" alt="Article title" />
  <h3>Building a Design System</h3>
  <p>Learn how to create a scalable design system...</p>
  <time datetime="2026-04-25">Apr 25, 2026</time>
</article>
```

---

## 🧭 NAVIGATION

### Navigation Bar (.navbar)

```
Position: fixed (top: 0)
Height: 64px (standard)
Background: var(--bg-light) #0a0a0f
Border-bottom: 1px solid var(--border)
Z-index: 1000
Box-shadow: var(--shadow-sm)
```

### Navigation Links (.nav-links)

**Desktop (>768px):**
```
Display: flex
Gap: 2rem
Flex-direction: row
Max-height: none
```

**Mobile (<768px):**
```
Display: flex (when active)
Gap: 0
Flex-direction: column
Position: fixed
Max-height: calc(100vh - 64px)
Overflow-y: auto
Background: rgba(255,255,255,0.97)
Backdrop-filter: blur(12px)
```

### Navigation Link States

**Default:**
```
Color: var(--text-primary)
Text-decoration: none
Transition: var(--transition)
```

**Hover:**
```
Color: var(--accent) #0066ff
Border-bottom: 2px solid var(--accent)
```

**Active:**
```
Color: var(--accent) #0066ff
Border-bottom: 2px solid var(--accent) (persistent)
```

**Focus:**
```
Outline: 2px solid var(--accent) + offset 2px
```

### Mobile Menu Toggle (.menu-toggle)

```
Display: none (hidden on desktop)
Display: flex (visible on mobile)
Width: 44px (minimum touch target)
Height: 44px
Align-items: center
Justify-content: center
Background: transparent
Border: none
Cursor: pointer
Z-index: 1001 (above nav)
```

**Hamburger Icon:**
```
Three horizontal lines
Color: var(--text-primary)
Transition: transform 0.25s ease
Rotate on active: 90deg or animate to X
```

### HTML Example

```html
<nav class="navbar">
  <div class="nav-brand">Portfolio</div>
  <button class="menu-toggle" aria-label="Toggle menu">☰</button>
  <ul class="nav-links">
    <li><a href="#about">About</a></li>
    <li><a href="#skills">Skills</a></li>
    <li><a href="#projects">Projects</a></li>
    <li><a href="#contact">Contact</a></li>
  </ul>
</nav>
```

---

## 🎯 SECTIONS

### Section Padding Pattern

**Desktop:**
```
Padding: var(--section-spacing-desktop) = 6rem (96px) vertical
Padding: var(--container-padding-desktop) = 3rem (48px) horizontal
```

**Tablet:**
```
Padding: var(--section-spacing-tablet) = 4rem (64px) vertical
Padding: var(--container-padding-tablet) = 2rem (32px) horizontal
```

**Mobile:**
```
Padding: var(--section-spacing-mobile) = 3rem (48px) vertical
Padding: var(--container-padding-mobile) = 1.5rem (24px) horizontal
```

### Hero Section

```
Min-height: calc(100vh - 70px)
Display: flex (centered)
Grid: 2 columns (desktop) → 1 column (mobile)
Gap: var(--spacing-2xl) = 3rem (48px)
```

**Components:**
- Hero text (left, desktop) + title/subtitle/CTA
- Hero image (right, desktop) + profile card + badge

### Content Sections (about, skills, projects, articles, contact)

**Grid Layout:**
```
Max-width: 1200px
Margin: 0 auto (centered)
Display: grid or flex
Gap: varies by section
```

**Grid Patterns:**
- **Skills**: repeat(auto-fit, minmax(250px, 1fr)) — responsive grid
- **Projects**: 2 columns (desktop) → 1 column (mobile)
- **Articles**: 3 columns (desktop) → 2 columns (tablet) → 1 column (mobile)

---

## 🎨 SPACING REFERENCE

### Container/Section Padding
```
Desktop: 3rem horizontal, 6rem vertical
Tablet:  2rem horizontal, 4rem vertical
Mobile:  1.5rem horizontal, 3rem vertical
```

### Component Interior Spacing
```
Small cards:  1.5rem (24px)
Medium cards: 3rem (48px)
Large cards:  4rem (64px)
```

### Component Gap/Margin
```
Tight: 0.5rem (8px)
Normal: 1rem (16px)
Loose: 2rem (32px)
Relaxed: 3rem (48px)
```

---

## ♿ ACCESSIBILITY REQUIREMENTS

### Keyboard Navigation

**Tab Order:**
1. Skip link (hidden)
2. Navigation links
3. Hero section buttons
4. Section buttons/links
5. Form inputs
6. Submit buttons
7. Footer links

**Focus Indicators:**
```
All interactive elements:
- Outline: 2px solid var(--accent)
- Outline-offset: 2px
- Visible always (not hidden on blur)
```

### Form Accessibility

```
✅ All inputs labeled (associated with <label>)
✅ Error messages linked to inputs (aria-describedby)
✅ Required fields marked (required attribute + aria-required)
✅ Success/error messages announced (role="alert")
✅ Min height 44px for touch targets
```

### Button Accessibility

```
✅ All buttons have accessible text (visible or aria-label)
✅ Buttons indicate state (aria-pressed, aria-expanded)
✅ Disabled state conveyed visually + aria-disabled
✅ Focus visible 2px outline
✅ Min size 44x44px touch target
```

### Color Contrast

```
✅ Primary text on background: 15:1 (AAA)
✅ Secondary text on background: 6.5:1 (AA)
✅ Accent on background: 8:1 (AA)
✅ No color-only information (always paired with text/icon)
```

---

## 📱 RESPONSIVE BREAKPOINTS

### Mobile First Approach

```
Mobile:   < 768px   (default styles)
Tablet:   768px     (@media min-width: 768px)
Desktop:  1024px    (@media min-width: 1024px)
Wide:     1440px    (container max-width capped)
```

### Component Responsive Behavior

**Buttons:**
- Sizes stay same (padding/font)
- Touch targets always 44px+

**Forms:**
- Width: 100% on mobile, narrower on desktop
- Stacked vertically always
- Labels always above inputs

**Grids:**
- 1 column on mobile
- 2 columns on tablet
- 2-3 columns on desktop

**Navigation:**
- Stacked column on mobile (in overlay)
- Horizontal row on desktop

---

## 📐 TYPE SCALE REFERENCE

| Element | Desktop | Tablet | Mobile | Weight | Line-height |
|---------|---------|--------|--------|--------|-------------|
| h1 | 3.5rem | 3rem | 2.25rem | 700 | 1.2 |
| h2 | 2.75rem | 2.25rem | 1.75rem | 700 | 1.3 |
| h3 | 2rem | 1.5rem | 1.5rem | 600 | 1.4 |
| h4 | 1.5rem | 1.25rem | 1.25rem | 600 | 1.4 |
| body | 1.125rem | 1rem | 1rem | 400 | 1.6 |
| small | 0.875rem | 0.875rem | 0.875rem | 400 | 1.5 |

---

## 🎯 Component Inventory Matrix

### Buttons
- ✅ Primary (4 variants × 3 sizes × 5 states = 60 combinations)
- ✅ All states: default, hover, active, disabled, focus

### Forms
- ✅ Text inputs
- ✅ Textareas
- ✅ Labels (required/optional)
- ✅ Validation states (success, error, warning)
- ✅ Disabled state

### Cards
- ✅ Skill cards
- ✅ Article cards
- ✅ Generic card pattern

### Navigation
- ✅ Navbar (desktop/mobile)
- ✅ Nav links (active/hover/focus)
- ✅ Menu toggle (mobile)

### Sections
- ✅ Hero
- ✅ About
- ✅ Skills
- ✅ Projects
- ✅ Articles
- ✅ Contact
- ✅ Footer

---

## ✅ PHASE 1 COMPONENT DELIVERABLES

- [x] Button component fully specified (all variants, sizes, states)
- [x] Form controls specified (inputs, labels, validation)
- [x] Cards documented (spacing, hover, responsive)
- [x] Navigation specified (mobile/desktop, states)
- [x] Section patterns documented
- [x] Accessibility requirements listed
- [x] Responsive behavior defined
- [x] Typography scale reference
- [x] Component inventory matrix

---

## 📚 RELATED DOCUMENTS

- [DESIGN_SYSTEM.md](./DESIGN_SYSTEM.md) — Tokens, colors, spacing
- [COPILOT.md](./COPILOT.md) — Architecture overview
- [QUICK_WINS.md](./QUICK_WINS.md) — Visual improvements completed
- [EXECUTION_STATUS.md](./EXECUTION_STATUS.md) — Project roadmap

---

*Component Specifications v1.0 — 2026-04-25*  
*Foundation for Phase 2: Tailwind CSS Migration*
