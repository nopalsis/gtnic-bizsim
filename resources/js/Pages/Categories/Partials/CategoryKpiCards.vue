<script setup>
const props = defineProps({
    counts: {
        type: Object,
        default: () => ({
            all: 0,
            finished: 0,
            raw: 0,
            active: 0,
            total_products: 0,
        }),
    },
});
</script>

<template>
    <section class="grid grid-cols-1 md:grid-cols-3 gap-3.5 md:gap-4">
        <!-- KPI 1: Total Kategori Terdaftar -->
        <div class="bg-white rounded-2xl p-4 shadow-xs border border-[#dce0cd] flex flex-col justify-between hover:border-[#c6ef4e] transition-all">
            <div class="flex items-center justify-between mb-2">
                <span class="text-[10px] md:text-[11px] uppercase tracking-wider text-[#737868] font-bold">
                    Total Kategori Terdaftar
                </span>
                <div class="w-8 h-8 rounded-full bg-[#f2f4ec] flex items-center justify-center text-[#0e0f0c]">
                    <span class="material-symbols-outlined text-[18px]">category</span>
                </div>
            </div>
            <div>
                <div class="flex items-baseline gap-2 mb-1.5">
                    <span class="text-xl md:text-2xl font-bold text-[#0e0f0c] tracking-tight">
                        {{ props.counts.all }} Kategori
                    </span>
                    <span class="text-[11px] text-[#506600] font-semibold">
                        ({{ props.counts.active }} Aktif)
                    </span>
                </div>
                <p class="text-[11px] text-[#737868]">
                    Menaungi total {{ props.counts.total_products }} barang di gudang & showroom
                </p>
            </div>
        </div>

        <!-- KPI 2: Signature Neon Lime Card (Kategori Produk Jadi) -->
        <div class="bg-[#c6ef4e] rounded-2xl p-4 shadow-xs flex flex-col justify-between relative overflow-hidden text-[#0e0f0c]">
            <div class="flex items-center justify-between mb-2">
                <span class="text-[10px] md:text-[11px] uppercase tracking-wider text-[#0e0f0c]/80 font-bold">
                    Sepatu & Sandal Jadi
                </span>
                <span class="px-2 py-0.5 rounded-full bg-[#0e0f0c] text-white text-[10px] uppercase tracking-wider font-bold shadow-xs">
                    Siap Jual
                </span>
            </div>
            <div>
                <div class="flex items-baseline gap-2 mb-1">
                    <span class="text-xl md:text-2xl font-bold text-[#0e0f0c] tracking-tight">
                        {{ props.counts.finished }} Kategori
                    </span>
                </div>
                <p class="text-xs text-[#0e0f0c] mb-1 font-semibold">
                    Sepatu pria, sepatu wanita, dan sandal kulit
                </p>
                <p class="text-[11px] text-[#0e0f0c]/80">
                    Dijual melalui kasir eceran dan pesanan partai grosir
                </p>
            </div>
        </div>

        <!-- KPI 3: Bahan Baku & Komponen Bengkel -->
        <div class="bg-white rounded-2xl p-4 shadow-xs border border-[#dce0cd] flex flex-col justify-between hover:border-[#c6ef4e] transition-all">
            <div class="flex items-center justify-between mb-2">
                <span class="text-[10px] md:text-[11px] uppercase tracking-wider text-[#737868] font-bold">
                    Bahan Baku & Bengkel
                </span>
                <div class="w-8 h-8 rounded-full bg-[#f2f4ec] flex items-center justify-center text-[#0e0f0c]">
                    <span class="material-symbols-outlined text-[18px]">layers</span>
                </div>
            </div>
            <div>
                <div class="flex items-baseline gap-2 mb-1.5">
                    <span class="text-xl md:text-2xl font-bold text-[#0e0f0c] tracking-tight">
                        {{ props.counts.raw }} Kategori
                    </span>
                </div>
                <p class="text-xs text-[#0e0f0c] mb-1 font-semibold">
                    Lembaran kulit mentah, sol, lem & aksesoris
                </p>
                <p class="text-[11px] text-[#737868]">
                    Dipakai untuk produksi dan pencatatan sisa limbah/waste
                </p>
            </div>
        </div>
    </section>
</template>

