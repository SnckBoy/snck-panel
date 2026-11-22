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

    /* SNCK glass theme: keep the upstream component structure and API intact. */
    body {
        background-color: #080d1b !important;
        background-image:
            radial-gradient(ellipse at 12% 8%, rgba(124, 58, 237, .20), transparent 34rem),
            radial-gradient(ellipse at 88% 76%, rgba(56, 189, 248, .12), transparent 38rem),
            linear-gradient(145deg, #080d1b 0%, #10182b 55%, #0b1222 100%);
        background-attachment: fixed;
    }

    .bg-neutral-900 {
        background-color: rgba(8, 13, 27, .78) !important;
        border-color: rgba(148, 163, 184, .16);
        backdrop-filter: blur(22px);
        -webkit-backdrop-filter: blur(22px);
    }

    .bg-neutral-800 {
        background-color: rgba(15, 23, 42, .62) !important;
        backdrop-filter: blur(18px);
        -webkit-backdrop-filter: blur(18px);
    }

    .bg-neutral-700 {
        background-color: rgba(30, 41, 59, .54) !important;
        border: 1px solid rgba(148, 163, 184, .17);
        box-shadow: 0 14px 38px rgba(0, 0, 0, .16);
        backdrop-filter: blur(16px);
        -webkit-backdrop-filter: blur(16px);
    }

    .bg-neutral-600 {
        background-color: rgba(51, 65, 85, .56) !important;
        border-color: rgba(148, 163, 184, .2);
    }

    .bg-black {
        background-color: rgba(5, 8, 18, .84) !important;
    }

    input, select, textarea {
        background-color: rgba(15, 23, 42, .72) !important;
        border-color: rgba(148, 163, 184, .25) !important;
        border-radius: 10px;
    }

    input:focus, select:focus, textarea:focus {
        border-color: rgba(167, 139, 250, .85) !important;
        box-shadow: 0 0 0 3px rgba(139, 92, 246, .16);
    }

    a, button {
        transition: color 150ms ease, background-color 150ms ease, border-color 150ms ease, box-shadow 150ms ease;
    }

    /* SNCK client control plane navigation and interaction polish. */
    #logo a {
        display: inline-flex;
        align-items: center;
        gap: 11px;
        font-weight: 800;
        letter-spacing: .035em;
    }

    #logo a::before {
        content: 'S';
        display: inline-grid;
        width: 32px;
        height: 32px;
        place-items: center;
        color: white;
        font-size: 17px;
        font-weight: 900;
        border-radius: 10px;
        background: linear-gradient(135deg, #7c3aed, #0891b2);
        box-shadow: 0 5px 18px rgba(124, 58, 237, .32);
    }

    body > #app,
    body #app {
        min-height: 100vh;
    }

    .navigation-link,
    nav a,
    #logo a {
        -webkit-tap-highlight-color: transparent;
    }

    .bg-neutral-900.shadow-md {
        position: sticky;
        top: 0;
        z-index: 40;
        border-bottom: 1px solid rgba(148, 163, 184, .18);
        box-shadow: 0 10px 32px rgba(0, 0, 0, .22);
    }

    .bg-neutral-900.shadow-md > div {
        min-height: 62px;
        padding-left: 12px;
        padding-right: 12px;
    }

    .bg-neutral-900.shadow-md a,
    .bg-neutral-900.shadow-md button {
        border-radius: 10px;
    }

    button:not(:disabled):active {
        transform: translateY(1px);
    }

    :focus-visible {
        outline: 2px solid rgba(34, 211, 238, .9);
        outline-offset: 3px;
    }

    @media (max-width: 640px) {
        .bg-neutral-900.shadow-md > div {
            min-width: 0;
            min-height: 56px;
            padding-left: 8px;
            padding-right: 8px;
        }

        #logo a {
            gap: 8px;
            padding-left: 8px;
            padding-right: 8px;
            font-size: 18px;
        }

        #logo a::before {
            width: 28px;
            height: 28px;
            font-size: 15px;
        }

        .bg-neutral-900.shadow-md a,
        .bg-neutral-900.shadow-md button {
            padding-left: 12px;
            padding-right: 12px;
        }
    }

    @media (prefers-reduced-motion: reduce) {
        a, button {
            transition: none;
        }
    }
`;
