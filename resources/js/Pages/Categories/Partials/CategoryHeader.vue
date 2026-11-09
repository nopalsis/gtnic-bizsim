<script setup>
import { Link } from '@inertiajs/vue3';

defineEmits(['openAddModal']);
</script>

<template>
    <header class="bg-white rounded-2xl p-3.5 md:p-5 shadow-xs border border-[#dce0cd] flex flex-col lg:flex-row lg:items-center lg:justify-between gap-3.5">
        <div>
            <div class="flex items-center gap-2 mb-1.5">
                <span class="inline-flex items-center px-2 py-0.5 rounded-full text-[10px] bg-[#f2f4ec] text-[#737868] font-bold uppercase tracking-wider">
                    Pengelompokan Barang
                </span>
                <span class="text-[11px] text-[#737868]">
                    • Master Data Kategori
                </span>
            </div>
            <div class="flex items-center gap-2">
                <span class="w-2.5 h-2.5 rounded-full bg-[#c6ef4e] shrink-0"></span>
                <h1 class="text-lg md:text-2xl text-[#0e0f0c] tracking-tight font-black">
                    Kategori Produk & Bahan
                </h1>
            </div>
            <p class="text-xs text-[#737868] mt-0.5 max-w-2xl leading-snug">
                Kelola kelompok sepatu jadi, sandal kulit, bahan baku lembaran kulit, dan perlengkapan bengkel.
            </p>
        </div>

        <div class="flex flex-wrap items-center gap-2 w-full lg:w-auto">
            <!-- Link Kembali ke Stok -->
            <Link
                href="/inventory"
                class="inline-flex items-center justify-center gap-1.5 px-4 py-2 rounded-full bg-[#f2f4ec] border border-[#dce0cd] hover:bg-[#eaecdf] transition-colors text-xs text-[#0e0f0c] shadow-xs cursor-pointer font-semibold flex-1 sm:flex-initial"
            >
                <span class="material-symbols-outlined text-[16px]">arrow_back</span>
                <span>Kembali ke Stok</span>
            </Link>

            <!-- Tombol Tambah Kategori -->
            <button
                type="button"
                @click="$emit('openAddModal')"
                class="inline-flex items-center justify-center gap-1.5 px-4 py-2 rounded-full bg-[#0e0f0c] text-white hover:bg-[#20221c] transition-all text-xs shadow-xs cursor-pointer font-bold flex-1 sm:flex-initial"
            >
                <span class="material-symbols-outlined text-[16px] text-[#c6ef4e]">add_circle</span>
                <span>+ Tambah Kategori Baru</span>
            </button>
        </div>
    </header>
</template>

