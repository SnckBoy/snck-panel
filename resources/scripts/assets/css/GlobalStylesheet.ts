import tw from 'twin.macro';
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

    body {
        ${tw`font-sans bg-neutral-800 text-neutral-200`};
        letter-spacing: 0.015em;
    }

    h1, h2, h3, h4, h5, h6 {
        ${tw`font-medium tracking-normal font-header`};
    }

    p {
        ${tw`text-neutral-200 leading-snug font-sans`};
    }

    form {
        ${tw`m-0`};
    }

    textarea, select, input, button, button:focus, button:focus-visible {
        ${tw`outline-none`};
    }

    input[type=number]::-webkit-outer-spin-button,
    input[type=number]::-webkit-inner-spin-button {
        -webkit-appearance: none !important;
        margin: 0;
    }

    input[type=number] {
        -moz-appearance: textfield !important;
    }

    /* Scroll Bar Style */
    ::-webkit-scrollbar {
        background: none;
        width: 16px;
        height: 16px;
    }

    ::-webkit-scrollbar-thumb {
        border: solid 0 rgb(0 0 0 / 0%);
        border-right-width: 4px;
        border-left-width: 4px;
        -webkit-border-radius: 9px 4px;
        -webkit-box-shadow: inset 0 0 0 1px hsl(211, 10%, 53%), inset 0 0 0 4px hsl(209deg 18% 30%);
    }

    ::-webkit-scrollbar-track-piece {
        margin: 4px 0;
    }

    ::-webkit-scrollbar-thumb:horizontal {
        border-right-width: 0;
        border-left-width: 0;
        border-top-width: 4px;
        border-bottom-width: 4px;
        -webkit-border-radius: 4px 9px;
    }

    ::-webkit-scrollbar-corner {
        background: transparent;
    }

    /* SNCK matte console theme: solid surfaces, no glass blur or gradient fields. */
    :root {
        color-scheme: dark;
        --snck-client-bg: #090a0c;
        --snck-client-panel: #111416;
        --snck-client-raised: #191d20;
        --snck-client-line: #2a3034;
        --snck-client-text: #e8e8e4;
        --snck-client-muted: #a4a7a7;
        --snck-client-accent: #d8aa57;
    }

    html, body {
        max-width: 100%;
        overflow-x: clip;
        background: var(--snck-client-bg) !important;
        background-image: none !important;
        color: var(--snck-client-text);
    }

    #app { min-height: 100vh; width: 100%; max-width: 100%; overflow-x: clip; }

    .bg-neutral-900 {
        background-color: #0b0d0f !important;
        border-color: var(--snck-client-line) !important;
        backdrop-filter: none !important;
        -webkit-backdrop-filter: none !important;
    }

    .bg-neutral-800 {
        background-color: var(--snck-client-panel) !important;
        backdrop-filter: none !important;
        -webkit-backdrop-filter: none !important;
    }

    .bg-neutral-700 {
        background-color: var(--snck-client-raised) !important;
        border: 1px solid var(--snck-client-line) !important;
        box-shadow: none !important;
        backdrop-filter: none !important;
        -webkit-backdrop-filter: none !important;
    }

    .bg-neutral-600 {
        background-color: #22282c !important;
        border-color: #3a4247 !important;
    }

    .bg-black { background-color: #060708 !important; }

    input, select, textarea {
        background-color: #0d1012 !important;
        border-color: #394146 !important;
        border-radius: 4px !important;
        color: var(--snck-client-text) !important;
    }

    input:focus, select:focus, textarea:focus {
        border-color: var(--snck-client-accent) !important;
        box-shadow: 0 0 0 2px rgba(216, 170, 87, .16) !important;
    }

    a, button {
        -webkit-tap-highlight-color: transparent;
        transition: color 140ms ease, background-color 140ms ease, border-color 140ms ease;
    }

    #logo { flex: 0 0 auto; min-width: max-content; }
    #logo a {
        display: inline-flex;
        align-items: center;
        gap: 9px;
        white-space: nowrap;
        font-weight: 700;
        letter-spacing: .01em;
        color: var(--snck-client-text) !important;
    }

    #logo a::before {
        content: 'S';
        display: inline-grid;
        flex: 0 0 30px;
        width: 30px;
        height: 30px;
        place-items: center;
        color: #101010;
        font-size: 16px;
        font-weight: 800;
        border-radius: 5px;
        background: var(--snck-client-accent);
        box-shadow: none;
    }

    .bg-neutral-900.shadow-md {
        position: sticky;
        top: 0;
        z-index: 40;
        width: 100%;
        max-width: 100%;
        border-bottom: 1px solid var(--snck-client-line);
        box-shadow: none !important;
    }

    .bg-neutral-900.shadow-md > div {
        display: flex;
        flex-wrap: nowrap;
        align-items: center;
        gap: 6px;
        width: 100%;
        max-width: 100%;
        min-width: 0;
        min-height: 60px;
        box-sizing: border-box;
        padding-left: 12px;
        padding-right: 12px;
    }

    .bg-neutral-900.shadow-md > div > * { min-width: 0; }
    .bg-neutral-900.shadow-md a,
    .bg-neutral-900.shadow-md button {
        min-width: 40px;
        min-height: 40px;
        border-radius: 4px !important;
    }

    button:not(:disabled):active { transform: translateY(1px); }

    :focus-visible {
        outline: 2px solid var(--snck-client-accent);
        outline-offset: 3px;
    }

    ::selection { color: #090a0c; background: var(--snck-client-accent); }

    @media (max-width: 640px) {
        .bg-neutral-900.shadow-md > div {
            gap: 2px;
            min-height: 56px;
            padding-left: 7px;
            padding-right: 7px;
        }

        #logo a {
            gap: 6px;
            padding-left: 3px;
            padding-right: 3px;
            font-size: 16px;
        }

        #logo a::before {
            flex-basis: 27px;
            width: 27px;
            height: 27px;
            font-size: 14px;
        }

        .bg-neutral-900.shadow-md a,
        .bg-neutral-900.shadow-md button {
            min-width: 36px;
            padding-left: 8px;
            padding-right: 8px;
        }
    }

    @media (max-width: 360px) {
        #logo a { font-size: 14px; }
        .bg-neutral-900.shadow-md > div { gap: 0; }
    }

    @media (prefers-reduced-motion: reduce) {
        a, button { transition: none; }
    }
`;
