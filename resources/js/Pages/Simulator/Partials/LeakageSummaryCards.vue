<script setup>
const props = defineProps({
    cards: {
        type: Object,
        required: true,
    },
    periodLabel: {
        type: String,
        default: '30 Hari Terakhir',
    },
});
</script>

<template>
    <section aria-label="Ringkasan Kebocoran Kas" class="grid grid-cols-1 md:grid-cols-3 gap-5">
        <!-- Kartu 1: Laba Hilang akibat Diskon -->
        <article class="bg-[#0e0f0c] text-white rounded-[26px] p-6 border border-[#2d4000] relative overflow-hidden shadow-lg flex flex-col justify-between">
            <div class="absolute -top-10 -right-10 w-40 h-40 bg-rose-500/10 rounded-full blur-2xl pointer-events-none"></div>

            <header class="relative z-10">
                <div class="flex items-center justify-between gap-2 mb-3">
                    <span class="inline-flex items-center gap-1.5 px-3 py-1 rounded-full bg-rose-500/20 text-rose-300 text-xs font-black uppercase tracking-wider border border-rose-500/30">
                        <span>📉</span> Diskon & Nego
                    </span>
                    <span class="text-[11px] text-rose-300/80 font-bold px-2 py-0.5 rounded-full bg-white/5 border border-white/10">
                        {{ cards.marginSlippage?.periodLabel || props.periodLabel }}
                    </span>
                </div>
                <h2 class="text-sm font-semibold text-neutral-300">
                    Laba Hilang akibat Diskon
                </h2>
                <div class="mt-2 flex items-baseline gap-2">
                    <span class="text-3xl sm:text-4xl font-black text-rose-400 tracking-tight">
                        {{ cards.marginSlippage?.amountFormatted || '-Rp 0' }}
                    </span>
                </div>
            </header>

            <footer class="mt-5 pt-4 border-t border-white/10 relative z-10">
                <div class="bg-white/5 rounded-xl p-3 border border-white/5">
                    <p class="text-xs text-neutral-200 leading-relaxed font-medium">
                        {{ cards.marginSlippage?.note || 'Target margin katalog terpotong negosiasi harga.' }}
                    </p>
                    <p class="text-[11px] text-neutral-400 mt-1.5 flex items-center gap-1.5">
                        <span class="text-amber-400">⚠️</span>
                        {{ cards.marginSlippage?.subtext || 'Diskon penjualan yang melebihi batas toleransi katalog.' }}
                    </p>
                </div>
            </footer>
        </article>

        <!-- Kartu 2: Kerugian Limbah Kulit -->
        <article class="bg-white rounded-[26px] p-6 border border-[#dce0cd] shadow-xs flex flex-col justify-between relative overflow-hidden">
            <header>
                <div class="flex items-center justify-between gap-2 mb-3">
                    <span class="inline-flex items-center gap-1.5 px-3 py-1 rounded-full bg-amber-50 text-amber-800 text-xs font-black uppercase tracking-wider border border-amber-200">
                        <span>✂️</span> Meja Potong
                    </span>
                    <span class="text-[11px] text-[#737868] font-bold px-2 py-0.5 rounded-full bg-[#fafbf7] border border-[#dce0cd]">
                        {{ cards.wasteCost?.periodLabel || props.periodLabel }}
                    </span>
                </div>
                <h2 class="text-sm font-semibold text-[#737868]">
                    Kerugian Limbah Kulit
                </h2>
                <div class="mt-2 flex items-baseline gap-2">
                    <span class="text-3xl sm:text-4xl font-black text-[#0e0f0c] tracking-tight">
                        {{ cards.wasteCost?.amountFormatted || 'Rp 0' }}
                    </span>
                </div>
            </header>

            <footer class="mt-5 pt-4 border-t border-[#eaecdf]">
                <div class="bg-[#fafbf7] rounded-xl p-3 border border-[#eaecdf]">
                    <p class="text-xs text-[#0e0f0c] leading-relaxed font-medium">
                        {{ cards.wasteCost?.note || 'Bahan terbuang di meja potong dan proses seset.' }}
                    </p>
                    <p class="text-[11px] text-[#737868] mt-1.5 flex items-center gap-1.5">
                        <span class="text-amber-600">📌</span>
                        {{ cards.wasteCost?.subtext || 'Kulit sobek saat seset, cacat sol, dan sisa potongan pola.' }}
                    </p>
                </div>
            </footer>
        </article>

        <!-- Kartu 3: Modal Macet di Gudang -->
        <article class="bg-white rounded-[26px] p-6 border border-[#dce0cd] shadow-xs flex flex-col justify-between relative overflow-hidden">
            <header>
                <div class="flex items-center justify-between gap-2 mb-3">
                    <span class="inline-flex items-center gap-1.5 px-3 py-1 rounded-full bg-blue-50 text-blue-800 text-xs font-black uppercase tracking-wider border border-blue-200">
                        <span>📦</span> Rak & Gudang
                    </span>
                    <span class="text-[11px] text-blue-700 font-bold px-2 py-0.5 rounded-full bg-blue-50/60 border border-blue-200">
                        Stok Mandek > 45 Hari
                    </span>
                </div>
                <h2 class="text-sm font-semibold text-[#737868]">
                    Modal Macet di Gudang
                </h2>
                <div class="mt-2 flex items-baseline gap-2">
                    <span class="text-3xl sm:text-4xl font-black text-[#0e0f0c] tracking-tight">
                        {{ cards.idleCapital?.amountFormatted || 'Rp 0' }}
                    </span>
                </div>
            </header>

            <footer class="mt-5 pt-4 border-t border-[#eaecdf]">
                <div class="bg-[#fafbf7] rounded-xl p-3 border border-[#eaecdf]">
                    <p class="text-xs text-[#0e0f0c] leading-relaxed font-medium">
                        {{ cards.idleCapital?.note || 'Stok produk jadi yang lambat laku.' }}
                    </p>
                    <p class="text-[11px] text-[#737868] mt-1.5 flex items-center gap-1.5">
                        <span class="text-blue-600">💡</span>
                        {{ cards.idleCapital?.subtext || 'Modal tertahan di rak tanpa menghasilkan perputaran kas.' }}
                    </p>
                </div>
            </footer>
        </article>
    </section>
</template>

