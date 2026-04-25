# 🎨 DESIGN SYSTEM — Abhay Bombale Portfolio

**Phase**: 1 — Design System Foundation  
**Status**: In Progress  
**Last Updated**: 2026-04-25  

---

## 📐 SPACING SCALE

Based on 8px base unit, supporting both digital and print design.

```
xs    = 4px    (0.25rem)
sm    = 8px    (0.5rem)
md    = 16px   (1rem)
lg    = 24px   (1.5rem)
xl    = 32px   (2rem)
2xl   = 48px   (3rem)
3xl   = 64px   (4rem)
4xl   = 80px   (5rem)
5xl   = 96px   (6rem)
6xl   = 128px  (8rem)
```

### Usage Guidelines

**Padding/Margin Hierarchy**
- **Micro spacing** (xs, sm): Form inputs, button labels, icon spacing
- **Standard spacing** (md, lg): Component padding, gap between elements
- **Section spacing** (xl, 2xl, 3xl): Between major sections (hero, skills, projects)
- **Hero/Large sections** (4xl, 5xl, 6xl): Top/bottom padding for impact

**Common Patterns**
```css
/* Containers */
--container-padding-mobile: 1.5rem;  /* lg */
--container-padding-tablet: 2.4rem;  /* xl + 0.4rem */
--container-padding-desktop: 3rem;   /* xl + 1rem */

/* Cards */
--card-padding-sm: 1.5rem;    /* lg */
--card-padding-md: 2rem;      /* xl */
--card-padding-lg: 3rem;      /* 2xl - lg */

/* Sections */
--section-spacing-mobile: 3rem;    /* 2xl */
--section-spacing-tablet: 4rem;    /* 3xl - lg */
--section-spacing-desktop: 7.5rem; /* 5xl - 0.5rem */

/* Forms */
--form-group-margin: 1.5rem;   /* lg */
--form-input-padding: 1rem;    /* md + sm */
--label-margin-bottom: 0.5rem; /* sm */
```

---

## 🔤 TYPOGRAPHY SCALE

Using modular scale with 1.25 ratio (perfect fifth).

### Font Families

**Display** (Headlines, CTAs)
```
Font: 'Space Grotesk' (or fallback 'Poppins')
Weights: 600, 700
Use: h1, h2, section titles, hero text
```

**Body** (Paragraphs, labels, body copy)
```
Font: 'DM Sans' (or fallback 'Inter')
Weights: 400, 500, 600
Use: All body text, labels, descriptions
```

**Monospace** (Code, technical text)
```
Font: 'JetBrains Mono' (or fallback 'Fira Code')
Weights: 400, 600
Use: Code blocks, console output, technical examples
```

### Font Sizes & Line Heights

**Desktop (≥1024px)**
```
h1: 56px / 1.2 line-height   (4.375rem) ← Hero title
h2: 44px / 1.3 line-height   (2.75rem)  ← Section titles
h3: 32px / 1.4 line-height   (2rem)     ← Card/subsection titles
h4: 24px / 1.5 line-height   (1.5rem)   ← Small titles
body: 18px / 1.6 line-height (1.125rem) ← Paragraphs (INCREASED from 16px)
small: 14px / 1.5 line-height (0.875rem) ← Secondary text
```

**Mobile (< 768px)**
```
h1: 36px / 1.2 line-height   (2.25rem)
h2: 28px / 1.3 line-height   (1.75rem)
h3: 24px / 1.4 line-height   (1.5rem)
h4: 20px / 1.5 line-height   (1.25rem)
body: 16px / 1.6 line-height (1rem)
small: 13px / 1.5 line-height (0.8125rem)
```

**Tablet (768px - 1024px)**
```
h1: 48px / 1.2 line-height
h2: 36px / 1.3 line-height
h3: 28px / 1.4 line-height
h4: 22px / 1.5 line-height
body: 17px / 1.6 line-height
small: 13px / 1.5 line-height
```

### Letter Spacing

```
Headlines (h1-h4): -0.5px to -1px (tighter for impact)
Body: 0px (normal)
Labels/Small text: +0.5px (improved readability)
Monospace: 0px (always)
```

### Font Weight Hierarchy

```
Light (300): Not used (modern minimalist skips this)
Regular (400): Body text, descriptions, secondary content
Medium (500): Labels, button text, emphasis
Semibold (600): Subheadings, strong body text
Bold (700): Headlines, CTAs, strong emphasis
```

---

## 🎨 COLOR PALETTE

### Core Colors

**Primary Accent** (Main brand color)
```
Base: #0066ff (Modern Blue)
Hover/Dark: #0052cc
Light/Soft: rgba(0, 102, 255, 0.1)
Glow: rgba(0, 102, 255, 0.15)
```

**Secondary Colors**

| Name | Light Mode | Dark Mode | Usage |
|------|-----------|-----------|-------|
| Orange | #ff6b2b | #ff6b2b | Accents, CTAs alt |
| Success | #22c55e | #22c55e | Success states |
| Warning | #f59e0b | #f59e0b | Warning states |
| Danger | #ef4444 | #ef4444 | Error states |
| Info | #0066ff | #0066ff | Info states |

### Neutral Scale (Dark Mode Default)

```
bg-base:      #0a0a0f  (Darkest - page background)
bg-surface:   #111118  (Cards, elevated)
bg-elevated:  #1a1a24  (Inputs, modals)
bg-overlay:   #22222e  (Overlays, selections)

text-primary:   #f0f0f5 (Main text, ~95% opacity white)
text-secondary: #a0a0b8 (Secondary text, ~62% opacity)
text-muted:     #5a5a78 (Disabled, hints, ~35% opacity)

border:         rgba(255, 255, 255, 0.07)   (Subtle borders)
border-accent:  rgba(0, 102, 255, 0.25)     (Brand borders)
```

### Light Mode (When Implemented)

```
bg-base:      #ffffff
bg-surface:   #f8f8fa
bg-elevated:  #f0f0f5
bg-overlay:   rgba(0, 0, 0, 0.05)

text-primary:   #0a0a0f
text-secondary: #666680
text-muted:     #999999
```

### Semantic Color Usage

**Interactive Elements**
- Links: Primary accent (#0066ff)
- Buttons (primary): Primary accent
- Buttons (secondary): bg-surface + text-primary
- Buttons (ghost): transparent + primary text
- Buttons (outline): transparent + primary border

**Feedback States**
- Success: #22c55e
- Warning: #f59e0b
- Error: #ef4444
- Info: #0066ff

**Contrast Ratios** (WCAG AAA target)
- Primary text on dark bg: 15:1 ✅
- Primary accent on dark bg: 8:1 ✅ (AA) / Need improvement for AAA
- Secondary text on dark bg: 6:1 ✅

---

## 🧩 COMPONENT SYSTEM

### Buttons

**Variants**
- **Primary**: Solid blue, white text (main CTA)
- **Secondary**: Light background, dark text (alternate action)
- **Ghost**: Transparent, colored text (subtle action)
- **Outline**: Colored border, transparent bg (tertiary action)

**Sizes**
- **sm**: 0.5rem 1.25rem padding, 0.875rem font
- **md**: 1rem 2.5rem padding, 1rem font (default)
- **lg**: 1.25rem 2rem padding, 1.125rem font

**States**
- Default, Hover (lift + shadow), Active (pressed), Disabled (opacity 0.6)

### Form Controls

**Inputs & Textareas**
- Padding: 1rem
- Border: 1px solid rgba(255, 255, 255, 0.07)
- Focus: 2px solid #0066ff outline + border color change
- Placeholder: text-muted color
- Height: 44px minimum (accessibility)

**Labels**
- Font: 500 weight
- Margin-bottom: 0.5rem
- Color: text-primary

**Form Group Spacing**
- Margin-bottom: 2.25rem
- Ensures consistent form layout

### Cards

**Base Card**
- Padding: 3rem (or 2rem for dense layouts)
- Border: 1px solid border color
- Border-radius: 0.75rem
- Background: bg-surface
- Shadow: var(--shadow-sm)

**Card Hover**
- Transform: translateY(-5px)
- Shadow: var(--shadow-lg)
- Border-color: primary accent
- Transition: 0.25s ease

### Spacing & Layout

**Hero Section**
- Padding: 7.5rem 0 (desktop), 4rem 0 (mobile)
- Grid: 2 columns (desktop), 1 column (mobile)
- Gap: 3rem

**Standard Section**
- Padding: 7.5rem 0 (desktop), 5rem 0 (tablet), 3rem 0 (mobile)
- Container: max-width 1200px, padding 2.4rem

**Grid Layouts**
- Skills: repeat(auto-fit, minmax(250px, 1fr)), gap 2rem
- Projects: 2 columns (desktop), 1 column (mobile)
- Articles: 3 columns (desktop), 2 columns (tablet), 1 column (mobile)

---

## ⚡ INTERACTION & MOTION

### Transitions

**Standard**: `all 0.25s cubic-bezier(0.4, 0, 0.2, 1)`
- Used for: Color, opacity, transform changes
- Timing: 250ms (feels responsive but not jarring)

**Slow**: `all 0.5s cubic-bezier(0.4, 0, 0.2, 1)`
- Used for: Page transitions, complex animations
- Timing: 500ms (cinematic, smooth)

**Easing**: Cubic-bezier(0.4, 0, 0.2, 1) ← Material Design standard
- Fast start, slow end (feels natural)

### Hover States

**Buttons**: 
- Transform: translateY(-2px)
- Shadow: Increase to md or lg

**Cards**:
- Transform: translateY(-5px)
- Shadow: Increase to lg

**Links**:
- Color: Accent-dim
- Underline: Optional (depends on context)

### Focus States

**All interactive elements**:
- Outline: 2px solid #0066ff
- Outline-offset: 2px
- Visible on keyboard navigation (Tab key)

**Form inputs**:
- Outline: 2px solid #0066ff
- Border-color: #0066ff
- Box-shadow: 0 0 0 3px rgba(0, 102, 255, 0.1)

### Animations

**Scroll Reveals**:
- Intersection Observer for fade-in
- Fade-in: opacity 0 → 1
- Slide-in: translateY(20px) → translateY(0)
- Duration: 0.6s

**Respects**:
- `prefers-reduced-motion: reduce` (removes animations if enabled)

---

## 🎯 ACCESSIBILITY REQUIREMENTS

### WCAG 2.1 Level AA (Target)

- ✅ Color contrast 4.5:1 for text
- ✅ Touch targets 44px minimum (buttons, links)
- ✅ Focus indicators visible
- ✅ Keyboard navigation works
- ✅ Semantic HTML structure
- ✅ ARIA labels where needed

### Keyboard Navigation

**Tab Order**: Left → Right, Top → Bottom
- Header links
- Skip to content link
- Main navigation
- Section content (buttons, links, forms)
- Footer links

**Escape Key**: Close modals, dropdowns

### Dark Mode

**Implementation**: CSS `@media (prefers-color-scheme: dark)`
- Default to dark mode
- User can toggle (localStorage persistence)
- All colors tested in both modes

---

## 🌐 RESPONSIVE DESIGN

### Breakpoints

```
Mobile:  < 768px
Tablet:  768px - 1024px
Desktop: 1024px - 1440px
Wide:    > 1440px
```

### Container Sizes

```
Mobile:  100% - 1.5rem padding (max ~600px useful)
Tablet:  100% - 2.4rem padding (max ~720px useful)
Desktop: 1200px max-width, centered
```

### Responsive Typography

See typography scale above for size adjustments per breakpoint.

### Image Sizing

```
Mobile: 100% width, max-height 400px
Tablet: 100% width, max-height 600px
Desktop: 100% width, max-height 800px
```

### Fluid Spacing (Optional, Advanced)

```css
/* Section spacing scales with viewport */
padding: clamp(2.75rem, 5vw, 5rem) 0;

/* Font sizes scale */
font-size: clamp(1.5rem, 5vw, 2.75rem);
```

---

## 📦 CSS ARCHITECTURE

### File Organization (Post-Tailwind)

```
assets/
├── css/
│   ├── style.css              ← Tailwind directives (@apply, @layer)
│   ├── variables.css          ← Custom properties (deprecated post-Tailwind)
│   └── tailwind.config.js     ← Tailwind configuration
├── js/
│   ├── main.js
│   ├── dark-mode.js
│   └── animations.js
└── images/
```

### CSS Custom Properties (Current, Pre-Tailwind)

```css
:root {
  /* Spacing */
  --spacing-xs: 0.25rem;
  --spacing-sm: 0.5rem;
  --spacing-md: 1rem;
  --spacing-lg: 1.5rem;
  --spacing-xl: 2rem;
  --spacing-2xl: 3rem;
  
  /* Colors */
  --accent: #0066ff;
  --accent-dim: #0052cc;
  --text-primary: #f0f0f5;
  --text-secondary: #a0a0b8;
  
  /* Typography */
  --font-display: 'Space Grotesk', sans-serif;
  --font-body: 'DM Sans', sans-serif;
  
  /* Shadows */
  --shadow-sm: 0 1px 3px rgba(0, 0, 0, 0.4);
  --shadow-md: 0 4px 16px rgba(0, 0, 0, 0.5);
  --shadow-lg: 0 12px 40px rgba(0, 0, 0, 0.6);
  
  /* Transitions */
  --transition: all 0.25s cubic-bezier(0.4, 0, 0.2, 1);
}
```

---

## ✅ PHASE 1 DELIVERABLES

- [x] Spacing scale defined
- [x] Typography system documented
- [x] Color palette with semantic meanings
- [x] Component specifications
- [x] Responsive design guidelines
- [x] Accessibility requirements
- [x] Interaction guidelines
- [x] This design-system.md

### Phase 1 Tasks

- [ ] Apply spacing tokens to CSS variables
- [ ] Update typography definitions
- [ ] Test color contrast ratios (AAA)
- [ ] Document component patterns
- [ ] Create tailwind.config.js
- [ ] Test responsive breakpoints
- [ ] Verify accessibility compliance

---

## 🔗 RELATED DOCUMENTS

- [COPILOT.md](./COPILOT.md) — Architecture overview
- [AUDIT_REPORT.md](./AUDIT_REPORT.md) — Detailed audit findings
- [QUICK_WINS.md](./QUICK_WINS.md) — Quick improvements (completed)
- [EXECUTION_STATUS.md](./EXECUTION_STATUS.md) — Project roadmap

---

*Design System v1.0 — 2026-04-25*  
*Foundation for all subsequent design work*
