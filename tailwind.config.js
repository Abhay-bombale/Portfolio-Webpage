// tailwind.config.js
// Tailwind CSS Configuration for Abhay Bombale Portfolio
// Based on Design System v1.0

module.exports = {
  // Content paths for PurgeCSS
  content: [
    './index.php',
    './admin.php',
    './contact.php',
    './certifications.php',
    './article.php',
    './404.php',
    './assets/js/**/*.js',
  ],

  theme: {
    extend: {
      // ──────────────────────────────────────────────────────────────────────────
      // SPACING SCALE (8px base)
      // ──────────────────────────────────────────────────────────────────────────
      spacing: {
        xs: '0.25rem',    // 4px
        sm: '0.5rem',     // 8px
        md: '1rem',       // 16px
        lg: '1.5rem',     // 24px
        xl: '2rem',       // 32px
        '2xl': '3rem',    // 48px
        '3xl': '4rem',    // 64px
        '4xl': '5rem',    // 80px
        '5xl': '6rem',    // 96px
        '6xl': '8rem',    // 128px
      },

      // ──────────────────────────────────────────────────────────────────────────
      // COLORS (from design system)
      // ──────────────────────────────────────────────────────────────────────────
      colors: {
        // Transparent
        transparent: 'transparent',

        // Primary Accent
        accent: {
          DEFAULT: '#0066ff',  // Primary
          dark: '#0052cc',     // Hover
          light: 'rgba(0, 102, 255, 0.1)',
          glow: 'rgba(0, 102, 255, 0.15)',
        },

        // Semantic Colors
        success: {
          DEFAULT: '#22c55e',
          light: 'rgba(34, 197, 94, 0.1)',
        },
        warning: {
          DEFAULT: '#f59e0b',
          light: 'rgba(245, 158, 11, 0.1)',
        },
        danger: {
          DEFAULT: '#ef4444',
          light: 'rgba(239, 68, 68, 0.1)',
        },
        info: {
          DEFAULT: '#0066ff',  // Same as accent
          light: 'rgba(0, 102, 255, 0.1)',
        },

        // Orange (secondary)
        orange: {
          DEFAULT: '#ff6b2b',
          glow: 'rgba(255, 107, 43, 0.15)',
        },

        // Background Colors
        bg: {
          base: '#0a0a0f',       // Page background
          surface: '#111118',     // Cards, elevated
          elevated: '#1a1a24',    // Inputs, modals
          overlay: '#22222e',     // Overlays
        },

        // Text Colors
        text: {
          primary: '#f0f0f5',     // Main text
          secondary: '#a0a0b8',   // Secondary text
          muted: '#5a5a78',       // Disabled, hints
        },

        // Border Colors
        border: {
          DEFAULT: 'rgba(255, 255, 255, 0.07)',
          accent: 'rgba(0, 102, 255, 0.25)',
          hover: 'rgba(0, 102, 255, 0.5)',
        },

        // Gray scale (for compatibility)
        gray: {
          900: '#0a0a0f',
          800: '#111118',
          700: '#1a1a24',
          600: '#22222e',
          500: '#5a5a78',
          400: '#a0a0b8',
          100: '#f0f0f5',
        },

        // White/Black for edge cases
        white: '#ffffff',
        black: '#000000',
      },

      // ──────────────────────────────────────────────────────────────────────────
      // TYPOGRAPHY
      // ──────────────────────────────────────────────────────────────────────────
      fontFamily: {
        display: [
          'Space Grotesk',
          'Poppins',
          'sans-serif',
        ],
        body: [
          'DM Sans',
          'Inter',
          'system-ui',
          '-apple-system',
          'sans-serif',
        ],
        mono: [
          'JetBrains Mono',
          'Fira Code',
          'monospace',
        ],
      },

      fontSize: {
        // Heading sizes (desktop base)
        'h1': ['3.5rem', { lineHeight: '1.2', letterSpacing: '-0.01em' }],    // 56px
        'h2': ['2.75rem', { lineHeight: '1.3', letterSpacing: '-0.01em' }],   // 44px
        'h3': ['2rem', { lineHeight: '1.4', letterSpacing: '-0.01em' }],      // 32px
        'h4': ['1.5rem', { lineHeight: '1.4', letterSpacing: '-0.01em' }],    // 24px

        // Body sizes
        'body': ['1.125rem', { lineHeight: '1.6' }],  // 18px
        'base': ['1rem', { lineHeight: '1.6' }],      // 16px
        'sm': ['0.875rem', { lineHeight: '1.5' }],    // 14px
        'xs': ['0.75rem', { lineHeight: '1.5' }],     // 12px
      },

      fontWeight: {
        light: '300',      // Not used in modern minimalist
        normal: '400',     // Body text
        medium: '500',     // Labels, buttons
        semibold: '600',   // Subheadings
        bold: '700',       // Headlines, emphasis
      },

      lineHeight: {
        tight: '1.2',      // Headlines
        normal: '1.4',     // Subheadings
        relaxed: '1.6',    // Body text
        loose: '1.8',      // Paragraphs
      },

      letterSpacing: {
        tighter: '-0.05em',
        tight: '-0.01em',
        normal: '0em',
        wide: '0.03em',
      },

      // ──────────────────────────────────────────────────────────────────────────
      // BORDERS & RADIUS
      // ──────────────────────────────────────────────────────────────────────────
      borderRadius: {
        sm: '0.5rem',    // 8px
        md: '0.75rem',   // 12px
        lg: '1rem',      // 16px
        xl: '1.5rem',    // 24px
      },

      // ──────────────────────────────────────────────────────────────────────────
      // SHADOWS
      // ──────────────────────────────────────────────────────────────────────────
      boxShadow: {
        sm: '0 1px 3px rgba(0, 0, 0, 0.4)',
        md: '0 4px 16px rgba(0, 0, 0, 0.5)',
        lg: '0 12px 40px rgba(0, 0, 0, 0.6)',
        accent: '0 0 30px rgba(0, 102, 255, 0.12)',
        glow: '0 0 60px rgba(0, 102, 255, 0.08)',
        none: 'none',
      },

      // ──────────────────────────────────────────────────────────────────────────
      // TRANSITIONS & ANIMATIONS
      // ──────────────────────────────────────────────────────────────────────────
      transitionDuration: {
        DEFAULT: '250ms',
        slow: '500ms',
      },

      transitionTimingFunction: {
        DEFAULT: 'cubic-bezier(0.4, 0, 0.2, 1)',
        material: 'cubic-bezier(0.4, 0, 0.2, 1)',
      },

      animation: {
        fadeIn: 'fadeIn 0.6s ease-out',
        slideUp: 'slideUp 0.6s ease-out',
      },

      keyframes: {
        fadeIn: {
          '0%': { opacity: '0' },
          '100%': { opacity: '1' },
        },
        slideUp: {
          '0%': {
            opacity: '0',
            transform: 'translateY(20px)',
          },
          '100%': {
            opacity: '1',
            transform: 'translateY(0)',
          },
        },
      },

      // ──────────────────────────────────────────────────────────────────────────
      // RESPONSIVE CONTAINER SIZES
      // ──────────────────────────────────────────────────────────────────────────
      container: {
        center: true,
        padding: {
          DEFAULT: '1.5rem',    // Mobile
          sm: '1.5rem',         // Mobile
          md: '2rem',           // Tablet
          lg: '3rem',           // Desktop
          xl: '3rem',
          '2xl': '3rem',
        },
      },

      // ──────────────────────────────────────────────────────────────────────────
      // SCREEN SIZES (breakpoints)
      // ──────────────────────────────────────────────────────────────────────────
      screens: {
        'xs': '360px',    // Small mobile
        'sm': '640px',    // Mobile
        'md': '768px',    // Tablet
        'lg': '1024px',   // Desktop
        'xl': '1280px',   // Wide desktop
        '2xl': '1536px',  // Ultra wide
      },

      // ──────────────────────────────────────────────────────────────────────────
      // HEIGHT
      // ──────────────────────────────────────────────────────────────────────────
      height: {
        button: '44px',   // Minimum touch target
        'hero': 'calc(100vh - 70px)',  // Hero section (nav height 70px)
      },

      minHeight: {
        button: '44px',
      },

      // ──────────────────────────────────────────────────────────────────────────
      // WIDTH
      // ──────────────────────────────────────────────────────────────────────────
      maxWidth: {
        container: '1200px',
        prose: '65ch',
      },

      minWidth: {
        button: '44px',
      },

      // ──────────────────────────────────────────────────────────────────────────
      // CUSTOM UTILITIES (via @layer)
      // ──────────────────────────────────────────────────────────────────────────
      // See 'layer' section below
    },
  },

  plugins: [],

  // Dark mode disabled - site is now light-mode only
  darkMode: false,

  // CorePlugins configuration
  corePlugins: {
    preflight: true,  // Include Tailwind's reset
  },
};

/*
================================================================================
USAGE GUIDE

1. SPACING
   <div class="p-lg m-2xl gap-xl">
     Padding: large (24px)
     Margin: 2xl (48px)
     Gap: xl (32px)
   </div>

2. COLORS
   <button class="bg-accent text-white hover:bg-accent-dark">
     Primary button with blue background
   </button>

   <div class="bg-success-light text-success border-success">
     Success message with light background
   </div>

3. TYPOGRAPHY
   <h1 class="text-h1 font-bold">Main Title</h1>
   <p class="text-base font-normal leading-relaxed">Body text</p>

4. BUTTONS (with custom components to be added)
   <button class="btn btn-primary">
   <button class="btn btn-secondary">
   <button class="btn btn-ghost">
   <button class="btn btn-outline">

5. FORMS
   <input class="w-full px-md py-md border border-border rounded-md focus:border-accent focus:outline-none focus:ring-2 focus:ring-accent/10" />

6. CARDS
   <div class="bg-surface p-2xl rounded-lg border border-border shadow-sm hover:shadow-lg transition-shadow">
   </div>

7. RESPONSIVE
   <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-xl">

8. FOCUS VISIBLE
   All interactive elements automatically get focus ring:
   outline outline-2 outline-offset-2 outline-accent (with :focus-visible)

================================================================================
*/
