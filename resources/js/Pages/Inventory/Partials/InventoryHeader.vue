<script setup>
import { Link } from '@inertiajs/vue3';

defineEmits(['openAddModal', 'openWasteModal']);
</script>

<template>
    <header class="bg-white rounded-2xl p-4 md:p-5 shadow-xs border border-[#dce0cd] flex flex-col lg:flex-row lg:items-center lg:justify-between gap-3.5">
        <div>
            <div class="flex items-center gap-2 mb-1.5">
                <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-[10px] bg-[#f2f4ec] text-[#737868] font-bold uppercase tracking-wider">
                    Gudang & Bengkel
                </span>
                <span class="text-[11px] text-[#737868]">
                    • Data Stok Terkini
                </span>
            </div>
            <div class="flex items-center gap-2.5">
                <span class="w-2.5 h-2.5 rounded-full bg-[#c6ef4e] shrink-0"></span>
                <h1 class="text-xl md:text-2xl text-[#0e0f0c] tracking-tight font-extrabold">
                    Stok Sepatu & Bahan
                </h1>
            </div>
            <p class="text-xs text-[#737868] mt-0.5 max-w-2xl">
                Kelola stok sepatu jadi, bahan kulit & sol, serta pencatatan sisa potongan kulit atau sepatu cacat.
            </p>
        </div>

        <div class="flex flex-wrap items-center gap-2.5 self-start lg:self-auto">
            <!-- Tombol Kelola Kategori -->
            <Link
                href="/categories"
                class="inline-flex items-center gap-1.5 px-4 py-2 rounded-full bg-white border border-[#dce0cd] hover:bg-[#f2f4ec] transition-colors text-xs text-[#0e0f0c] shadow-xs cursor-pointer font-semibold"
                title="Kelola Kategori Sepatu & Bahan"
            >
                <span class="material-symbols-outlined text-[16px] text-[#506600]">category</span>
                <span>Kategori Produk</span>
            </Link>

            <!-- Tombol Catat Bahan Rusak -->
            <button
                type="button"
                @click="$emit('openWasteModal')"
                class="inline-flex items-center gap-1.5 px-4 py-2 rounded-full bg-[#f2f4ec] border border-[#dce0cd] hover:bg-rose-50 hover:border-rose-200 transition-colors text-xs text-[#0e0f0c] shadow-xs cursor-pointer group font-semibold"
            >
                <span class="w-2 h-2 rounded-full bg-rose-500 animate-pulse"></span>
                <span class="material-symbols-outlined text-[16px] text-rose-600 group-hover:text-rose-700">content_cut</span>
                <span>Catat Bahan Rusak / Sisa Kulit</span>
            </button>

            <!-- Tombol Tambah Barang Baru -->
            <button
                type="button"
                @click="$emit('openAddModal')"
                class="inline-flex items-center gap-1.5 px-4 py-2 rounded-full bg-[#0e0f0c] text-white hover:bg-[#20221c] transition-all text-xs shadow-xs cursor-pointer font-bold"
            >
                <span class="material-symbols-outlined text-[16px] text-[#c6ef4e]">add_circle</span>
                <span>+ Tambah Barang Baru</span>
            </button>
        </div>
    </header>
</template>
