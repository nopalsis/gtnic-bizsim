<script setup>
import { Head } from '@inertiajs/vue3';

defineProps({
    title: {
        type: String,
        default: 'GTNIC BizSim',
    },
});
</script>

<template>
    <Head :title="title" />

    <div class="app-layout min-h-screen w-full bg-[#eaecdf] text-deep-black selection:bg-neon-lime selection:text-deep-black">
        <slot />
    </div>
</template>

<style>
/* ======================================================== */
/* TYPOGRAPHY SYSTEM (EPILOGUE + SPACE GROTESK)             */
/* Headline: Epilogue                                       */
/* Body & Label: Space Grotesk                              */
/* ======================================================== */

:root {
    --font-headline: 'Epilogue', system-ui, -apple-system, BlinkMacSystemFont, sans-serif;
    --font-body: 'Space Grotesk', system-ui, -apple-system, BlinkMacSystemFont, sans-serif;
    --font-label: 'Space Grotesk', system-ui, -apple-system, BlinkMacSystemFont, sans-serif;

    /* Global Color & Surface Tokens */
    --color-canvas: #eaecdf;
    --color-surface: #ffffff;
    --color-neon-lime: #c6ef4e;
    --color-deep-black: #0e0f0c;
    --color-card-border: #e1e5d3;
    --color-subtle-gray: #868b7d;
}

/* Base Body and Text */
body,
.app-layout {
    font-family: var(--font-body);
}

/* Headlines (H1 - H6) & Display Text */
.app-layout h1,
.app-layout h2,
.app-layout h3,
.app-layout h4,
.app-layout h5,
.app-layout h6,
.font-headline,
.font-display {
    font-family: var(--font-headline) !important;
}

/* Body & Labels */
.app-layout p,
.app-layout span,
.app-layout label,
.app-layout button,
.app-layout input,
.app-layout select,
.app-layout textarea,
.app-layout table,
.app-layout th,
.app-layout td,
.font-body,
.font-label {
    font-family: var(--font-body);
}

/* Monospace preservation for financial invoices and code */
.app-layout .font-mono,
.app-layout .font-mono *,
.font-mono,
code,
pre {
    font-family: ui-monospace, SFMono-Regular, Menlo, Monaco, Consolas, "Liberation Mono", monospace !important;
}
</style>
