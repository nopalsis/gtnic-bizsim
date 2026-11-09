<script setup>
const props = defineProps({
    cards: {
        type: Object,
        required: true,
    },
});

const formatRupiah = (number) => {
    if (number === null || number === undefined) return 'Rp 0';
    return new Intl.NumberFormat('id-ID', {
        style: 'currency',
        currency: 'IDR',
        minimumFractionDigits: 0,
        maximumFractionDigits: 0,
    }).format(number);
};
</script>

<template>
    <section aria-label="Ringkasan Kebocoran Kas" class="grid grid-cols-1 md:grid-cols-3 gap-5">
        <!-- Kartu 1: Selisih Laba Teori vs Riil (Margin Slippage) -->
        <article class="bg-[#0e0f0c] text-white rounded-[26px] p-6 border border-[#2d4000] relative overflow-hidden shadow-lg flex flex-col justify-between">
            <!-- Glow background -->
            <div class="absolute -top-10 -right-10 w-40 h-40 bg-rose-500/10 rounded-full blur-2xl pointer-events-none"></div>

            <header class="relative z-10">
                <div class="flex items-center justify-between gap-2 mb-3">
                    <span class="inline-flex items-center gap-1.5 px-3 py-1 rounded-full bg-rose-500/20 text-rose-300 text-xs font-black uppercase tracking-wider border border-rose-500/30">
                        <span>📉</span> Kartu 1: Margin Slippage
                    </span>
                    <span class="text-[11px] text-neutral-400 font-medium">30 Hari Terakhir</span>
                </div>
                <h2 class="text-sm font-semibold text-neutral-300">
                    Selisih Laba Teori vs Riil
                </h2>
                <div class="mt-2 flex items-baseline gap-2">
                    <span class="text-3xl sm:text-4xl font-black text-rose-400 tracking-tight">
                        {{ cards.marginSlippage?.amountFormatted || '-Rp 3.450.000' }}
                    </span>
                </div>
                <p class="text-xs font-bold text-rose-300/80 mt-1 uppercase tracking-wider">
                    {{ cards.marginSlippage?.tag || 'Penyusutan Laba Bersih' }}
                </p>
            </header>

            <footer class="mt-5 pt-4 border-t border-white/10 relative z-10">
                <div class="bg-white/5 rounded-xl p-3 border border-white/5">
                    <p class="text-xs text-neutral-200 leading-relaxed font-medium">
                        "{{ cards.marginSlippage?.note || 'Laba di atas kertas diproyeksikan 38%, namun realisasi lapangan hanya 26.5%.' }}"
                    </p>
                    <p class="text-[11px] text-neutral-400 mt-1.5 flex items-center gap-1.5">
                        <span class="text-amber-400">⚠️</span>
                        {{ cards.marginSlippage?.subtext || 'Terpangkas akibat diskon nego sales yang kebablasan.' }}
                    </p>
                </div>
            </footer>
        </article>

        <!-- Kartu 2: Uang Kas Terbuang di Meja Potong (Waste Cost) -->
        <article class="bg-white rounded-[26px] p-6 border border-[#dce0cd] shadow-xs flex flex-col justify-between relative overflow-hidden">
            <header>
                <div class="flex items-center justify-between gap-2 mb-3">
                    <span class="inline-flex items-center gap-1.5 px-3 py-1 rounded-full bg-amber-50 text-amber-800 text-xs font-black uppercase tracking-wider border border-amber-200">
                        <span>✂️</span> Kartu 2: Waste Cost
                    </span>
                    <span class="text-[11px] text-[#737868] font-medium">Meja Potong & Seset</span>
                </div>
                <h2 class="text-sm font-semibold text-[#737868]">
                    Uang Kas Terbuang di Meja Potong
                </h2>
                <div class="mt-2 flex items-baseline gap-2">
                    <span class="text-3xl sm:text-4xl font-black text-[#0e0f0c] tracking-tight">
                        {{ cards.wasteCost?.amountFormatted || 'Rp 1.820.000' }}
                    </span>
                </div>
                <p class="text-xs font-bold text-amber-700 mt-1 uppercase tracking-wider">
                    {{ cards.wasteCost?.tag || 'Total Nilai Kulit Rusak / Sisa Cacat' }}
                </p>
            </header>

            <footer class="mt-5 pt-4 border-t border-[#eaecdf]">
                <div class="bg-[#fafbf7] rounded-xl p-3 border border-[#eaecdf]">
                    <p class="text-xs text-[#0e0f0c] leading-relaxed font-medium">
                        "{{ cards.wasteCost?.note || 'Setara dengan ~38 feet kulit sapi pull-up yang tidak menjadi produk jual.' }}"
                    </p>
                    <p class="text-[11px] text-[#737868] mt-1.5 flex items-center gap-1.5">
                        <span class="text-amber-600">📌</span>
                        {{ cards.wasteCost?.subtext || 'Kulit sobek saat skiving, cacat lasting, dan sisa potongan pola.' }}
                    </p>
                </div>
            </footer>
        </article>

        <!-- Kartu 3: Modal Macet di Rak Gudang (Idle Capital) -->
        <article class="bg-white rounded-[26px] p-6 border border-[#dce0cd] shadow-xs flex flex-col justify-between relative overflow-hidden">
            <header>
                <div class="flex items-center justify-between gap-2 mb-3">
                    <span class="inline-flex items-center gap-1.5 px-3 py-1 rounded-full bg-blue-50 text-blue-800 text-xs font-black uppercase tracking-wider border border-blue-200">
                        <span>📦</span> Kartu 3: Idle Capital
                    </span>
                    <span class="text-[11px] text-[#737868] font-medium">Rak Barang Jadi</span>
                </div>
                <h2 class="text-sm font-semibold text-[#737868]">
                    Modal Macet di Rak Gudang
                </h2>
                <div class="mt-2 flex items-baseline gap-2">
                    <span class="text-3xl sm:text-4xl font-black text-[#0e0f0c] tracking-tight">
                        {{ cards.idleCapital?.amountFormatted || 'Rp 4.600.000' }}
                    </span>
                </div>
                <p class="text-xs font-bold text-blue-700 mt-1 uppercase tracking-wider">
                    {{ cards.idleCapital?.tag || 'Stok Mandek > 45 Hari' }}
                </p>
            </header>

            <footer class="mt-5 pt-4 border-t border-[#eaecdf]">
                <div class="bg-[#fafbf7] rounded-xl p-3 border border-[#eaecdf]">
                    <p class="text-xs text-[#0e0f0c] leading-relaxed font-medium">
                        "{{ cards.idleCapital?.note || '42 pasang sepatu kategori lambat laku (slow-moving).' }}"
                    </p>
                    <p class="text-[11px] text-[#737868] mt-1.5 flex items-center gap-1.5">
                        <span class="text-blue-600">💡</span>
                        {{ cards.idleCapital?.subtext || 'Modal kulit & sol tertahan tanpa menghasilkan perputaran kas.' }}
                    </p>
                </div>
            </footer>
        </article>
    </section>
</template>

