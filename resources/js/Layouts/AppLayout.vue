<script setup>
import { Head, usePage } from '@inertiajs/vue3';
import { computed, ref, watch } from 'vue';

defineProps({
    title: {
        type: String,
        default: 'OptimaBiz Leathercraft',
    },
});

const page = usePage();
const flash = computed(() => page.props.flash || {});
const showToast = ref(false);
const toastMessage = ref('');
const toastType = ref('success');

watch(
    () => page.props.flash,
    (newFlash) => {
        if (newFlash?.error) {
            toastMessage.value = newFlash.error;
            toastType.value = 'error';
            showToast.value = true;
            setTimeout(() => { showToast.value = false; }, 5000);
        } else if (newFlash?.success) {
            toastMessage.value = newFlash.success;
            toastType.value = 'success';
            showToast.value = true;
            setTimeout(() => { showToast.value = false; }, 4000);
        }
    },
    { immediate: true, deep: true }
);
</script>

<template>
    <Head :title="title" />

    <div class="app-layout min-h-screen w-full bg-[#eaecdf] text-deep-black selection:bg-neon-lime selection:text-deep-black relative">
        <!-- Floating Toast Notification -->
        <transition
            enter-active-class="transform ease-out duration-300 transition"
            enter-from-class="translate-y-2 opacity-0 sm:translate-y-0 sm:translate-x-2"
            enter-to-class="translate-y-0 opacity-100 sm:translate-x-0"
            leave-active-class="transition ease-in duration-200"
            leave-from-class="opacity-100"
            leave-to-class="opacity-0"
        >
            <div
                v-if="showToast"
                class="fixed top-5 right-5 z-50 max-w-md w-full shadow-xl rounded-2xl p-4 flex items-start gap-3 border pointer-events-auto"
                :class="[
                    toastType === 'error'
                        ? 'bg-rose-50 border-rose-200 text-rose-800'
                        : 'bg-[#0e0f0c] text-white border-neutral-800'
                ]"
            >
                <span
                    class="material-symbols-outlined text-xl shrink-0"
                    :class="toastType === 'error' ? 'text-rose-600' : 'text-[#c6ef4e]'"
                >
                    {{ toastType === 'error' ? 'warning' : 'check_circle' }}
                </span>
                <div class="flex-1 min-w-0">
                    <p class="text-xs font-semibold leading-relaxed">{{ toastMessage }}</p>
                </div>
                <button
                    type="button"
                    @click="showToast = false"
                    class="text-neutral-400 hover:text-neutral-600 p-0.5 cursor-pointer"
                >
                    ✕
                </button>
            </div>
        </transition>

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
