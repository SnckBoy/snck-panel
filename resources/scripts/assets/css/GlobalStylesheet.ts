import { createGlobalStyle } from 'styled-components/macro';
// @ts-expect-error untyped font file
import font from '@fontsource-variable/ibm-plex-sans/files/ibm-plex-sans-latin-wght-normal.woff2';

export default createGlobalStyle`
    @font-face {
        font-family: 'IBM Plex Sans';
        font-style: normal;
        font-display: swap;
        font-weight: 100 700;
        src: url(${font}) format('woff2-variations');
        unicode-range: U+0000-00FF,U+0131,U+0152-0153,U+02BB-02BC,U+02C6,U+02DA,U+02DC,U+0304,U+0308,U+0329,U+2000-206F,U+20AC,U+2122,U+2191,U+2193,U+2212,U+2215,U+FEFF,U+FFFD;
    }

    :root {
        color-scheme: dark;
        --snck-client-bg: #0b0d0f;
        --snck-client-line: #2a3237;
        --snck-client-text: #e6e9e6;
        --snck-client-accent: #c99a55;
    }

    body {
        min-height: 100vh;
        color: var(--snck-client-text);
        background: var(--snck-client-bg) !important;
        font-family: 'IBM Plex Sans', system-ui, -apple-system, BlinkMacSystemFont, 'Segoe UI', sans-serif;
        letter-spacing: 0.005em;
    }

    h1, h2, h3, h4, h5, h6 { color: var(--snck-client-text); font-weight: 600; }
    p { color: var(--snck-client-text); }
    form { margin: 0; }

    input[type=number]::-webkit-outer-spin-button,
    input[type=number]::-webkit-inner-spin-button {
        -webkit-appearance: none !important;
        margin: 0;
    }
    input[type=number] { -moz-appearance: textfield !important; }
    input[type=checkbox], input[type=radio] { accent-color: var(--snck-client-accent); }

    ::-webkit-scrollbar { width: 12px; height: 12px; }
    ::-webkit-scrollbar-track { background: #0b0d0f; }
    ::-webkit-scrollbar-thumb { background: #343c40; border: 3px solid #0b0d0f; border-radius: 8px; }
    ::-webkit-scrollbar-thumb:hover { background: #4a555a; }
    ::-webkit-scrollbar-corner { background: #0b0d0f; }

    /* Flat graphite surfaces. Avoid global blur and translucent overlays. */
    .bg-neutral-900 { background-color: #101417 !important; border-color: var(--snck-client-line); }
    .bg-neutral-800 { background-color: #151a1e !important; }
    .bg-neutral-700 { background-color: #1d2428 !important; border-color: var(--snck-client-line); }
    .bg-neutral-600 { background-color: #273035 !important; border-color: #384248; }
    .bg-black { background-color: #070809 !important; }
    .text-neutral-200 { color: #dfe4e0 !important; }
    .text-neutral-300 { color: #c5cdc8 !important; }
    .text-neutral-400 { color: #a0aaa5 !important; }

    a { color: #d8b47f; transition: color 120ms ease, background-color 120ms ease, border-color 120ms ease; }
    a:hover { color: #f0cf9c; }

    input:not([type=checkbox]):not([type=radio]):not([type=submit]):not([type=button]),
    select, textarea {
        color: var(--snck-client-text);
        background-color: #101518;
        border: 1px solid #364047;
        border-radius: 6px;
    }
    input:not([type=checkbox]):not([type=radio]):not([type=submit]):not([type=button]):focus,
    select:focus, textarea:focus {
        border-color: var(--snck-client-accent);
        box-shadow: 0 0 0 2px rgba(201, 154, 85, .16);
    }

    button:not(:disabled):active { filter: brightness(.94); }
    :focus:not(:focus-visible) { outline: none; }
    :focus-visible { outline: 2px solid var(--snck-client-accent); outline-offset: 3px; }

    body > #app, body #app { min-height: 100vh; }
    .bg-neutral-900.shadow-md {
        position: sticky;
        top: 0;
        z-index: 40;
        border-bottom: 1px solid var(--snck-client-line);
        box-shadow: 0 4px 14px rgba(0, 0, 0, .18);
    }
    .bg-neutral-900.shadow-md > div { min-height: 60px; padding-left: 12px; padding-right: 12px; }
    .bg-neutral-900.shadow-md a, .bg-neutral-900.shadow-md button { border-radius: 6px; }

    #logo a {
        display: inline-flex;
        align-items: center;
        gap: 10px;
        font-weight: 700;
        letter-spacing: .025em;
    }
    #logo a::before {
        content: 'S';
        display: inline-grid;
        width: 29px;
        height: 29px;
        place-items: center;
        color: #111416;
        font-size: 15px;
        font-weight: 800;
        border-radius: 5px;
        background: var(--snck-client-accent);
    }
    .navigation-link, nav a, #logo a { -webkit-tap-highlight-color: transparent; }

    @media (max-width: 640px) {
        .bg-neutral-900.shadow-md > div { min-width: 0; min-height: 54px; padding-left: 8px; padding-right: 8px; }
        #logo a { gap: 7px; padding-left: 7px; padding-right: 7px; font-size: 17px; }
        #logo a::before { width: 26px; height: 26px; font-size: 14px; }
        .bg-neutral-900.shadow-md a, .bg-neutral-900.shadow-md button { padding-left: 10px; padding-right: 10px; }
    }

    @media (prefers-reduced-motion: reduce) {
        *, *::before, *::after {
            scroll-behavior: auto !important;
            animation-duration: .01ms !important;
            animation-iteration-count: 1 !important;
            transition-duration: .01ms !important;
        }
    }

    /* Keep the client navigation in one row on narrow phone screens. */
    html, body, #app { width:100%; max-width:100%; overflow-x:clip; }
    .bg-neutral-900.shadow-md { width:100%; max-width:100%; }
    .bg-neutral-900.shadow-md > div {
        display:flex;
        flex-wrap:nowrap;
        align-items:center;
        gap:6px;
        width:100%;
        max-width:100%;
        min-width:0;
        box-sizing:border-box;
    }
    .bg-neutral-900.shadow-md > div > * { min-width:0; }
    #logo { flex:0 0 auto; min-width:max-content; }
    #logo a { display:inline-flex; align-items:center; gap:7px; white-space:nowrap; flex-shrink:0; }
    .bg-neutral-900.shadow-md a, .bg-neutral-900.shadow-md button { min-width:36px; min-height:40px; }
    @media (max-width:640px) {
        .bg-neutral-900.shadow-md > div { gap:2px; padding-left:7px; padding-right:7px; }
        #logo a { font-size:16px; gap:6px; padding-left:3px; padding-right:3px; }
        #logo a::before { flex:0 0 27px; width:27px; height:27px; }
        .bg-neutral-900.shadow-md a, .bg-neutral-900.shadow-md button { padding-left:8px; padding-right:8px; }
    }
`;