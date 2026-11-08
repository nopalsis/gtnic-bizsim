<script setup>
import { ref, computed, watch } from 'vue';

const props = defineProps({
    show: Boolean,
    transactions: {
        type: Array,
        default: () => [],
    },
});

const emit = defineEmits(['close']);

const searchQuery = ref('');
const ledgerPage = ref(1);
const ledgerPerPage = ref(8);

const formatRupiah = (number) => {
    if (number === null || number === undefined) return 'Rp 0';
    return new Intl.NumberFormat('id-ID', {
        style: 'currency',
        currency: 'IDR',
        minimumFractionDigits: 0,
        maximumFractionDigits: 0,
    }).format(number);
};

const filteredTransactions = computed(() => {
    if (!searchQuery.value) return props.transactions;
    const q = searchQuery.value.toLowerCase();
    return props.transactions.filter(
        (tx) =>
            tx.invoice_number?.toLowerCase().includes(q) ||
            tx.customer_name?.toLowerCase().includes(q) ||
            tx.sales_person?.toLowerCase().includes(q) ||
            tx.items?.toLowerCase().includes(q)
    );
});

watch(searchQuery, () => {
    ledgerPage.value = 1;
});

const ledgerTotalPages = computed(() => {
    return Math.max(1, Math.ceil(filteredTransactions.value.length / ledgerPerPage.value));
});

const paginatedLedgerTransactions = computed(() => {
    const start = (ledgerPage.value - 1) * ledgerPerPage.value;
    return filteredTransactions.value.slice(start, start + ledgerPerPage.value);
});

const startLedgerIndex = computed(() => {
    if (filteredTransactions.value.length === 0) return 0;
    return (ledgerPage.value - 1) * ledgerPerPage.value + 1;
});

const endLedgerIndex = computed(() => {
    return Math.min(ledgerPage.value * ledgerPerPage.value, filteredTransactions.value.length);
});

const goToLedgerPage = (p) => {
    if (p >= 1 && p <= ledgerTotalPages.value) {
        ledgerPage.value = p;
    }
};

const handleExport = () => {
    const txs = props.transactions;
    const csvContent =
        'data:text/csv;charset=utf-8,' +
        'Invoice,Date,Customer,Sales,Category,Items,Gross,Discount,Net,Status\n' +
        txs
            .map(
                (t) =>
                    `"${t.invoice_number}","${t.date}","${t.customer_name || ''}","${t.sales_person}","${t.primary_category || ''}","${(t.items || '').replace(/"/g, '""')}",${t.total_amount},${t.total_discount},${t.net_amount},"${t.status}"`
            )
            .join('\n');
    const encodedUri = encodeURI(csvContent);
    const link = document.createElement('a');
    link.setAttribute('href', encodedUri);
    link.setAttribute('download', `buku_besar_transaksi_sepatu_${new Date().toISOString().slice(0, 10)}.csv`);
    document.body.appendChild(link);
    link.click();
    document.body.removeChild(link);
};
</script>

<template>
    <div
        v-if="show"
        class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-[#0e0f0c]/60 backdrop-blur-sm"
        @click.self="emit('close')"
    >
        <div class="bg-white rounded-[28px] w-full max-w-4xl max-h-[85vh] flex flex-col shadow-2xl border border-[#dce0cd] overflow-hidden">
            <!-- Header -->
            <div class="p-6 border-b border-neutral-100 flex items-center justify-between bg-[#fafbf7]">
                <div>
                    <h3 class="text-xl font-black text-[#0e0f0c]">Buku Catatan Penjualan</h3>
                    <p class="text-xs text-neutral-500 mt-0.5">Daftar lengkap seluruh transaksi penjualan eceran & pesanan grosir</p>
                </div>
                <button
                    type="button"
                    @click="emit('close')"
                    class="w-9 h-9 rounded-full bg-neutral-200 hover:bg-neutral-300 flex items-center justify-center text-neutral-700 cursor-pointer transition font-bold"
                >
                    ✕
                </button>
            </div>

            <!-- Search & Actions -->
            <div class="p-4 border-b border-neutral-100 flex items-center justify-between gap-4 bg-white">
                <input
                    v-model="searchQuery"
                    type="text"
                    placeholder="Cari nomor nota, nama pembeli/toko, sales, atau model sepatu..."
                    class="w-full bg-[#f4f5ee] rounded-xl px-4 py-2.5 text-xs font-semibold border-0 focus:ring-2 focus:ring-[#0e0f0c] outline-none"
                />
                <button
                    type="button"
                    @click="handleExport"
                    class="px-4 py-2.5 rounded-xl bg-[#0e0f0c] text-white text-xs font-bold hover:bg-neutral-800 transition flex items-center gap-1.5 whitespace-nowrap cursor-pointer shadow-sm"
                >
                    <svg class="w-4 h-4 text-[#c6ef4e]" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                        <path d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4" stroke-linecap="round" stroke-linejoin="round"></path>
                    </svg>
                    <span>Unduh CSV</span>
                </button>
            </div>

            <!-- Table Body -->
            <div class="p-6 overflow-y-auto flex-1">
                <table class="w-full text-left border-collapse text-xs">
                    <thead>
                        <tr class="border-b border-neutral-200 text-[10px] font-bold uppercase tracking-wider text-neutral-400">
                            <th class="py-2.5 px-3">Faktur & Tanggal</th>
                            <th class="py-2.5 px-3">Pelanggan / Toko</th>
                            <th class="py-2.5 px-3">Item Pesanan</th>
                            <th class="py-2.5 px-3">Sales Person</th>
                            <th class="py-2.5 px-3 text-center">Negosiasi / Diskon</th>
                            <th class="py-2.5 px-3 text-right">Nominal Bersih</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-neutral-100">
                        <tr
                            v-for="tx in paginatedLedgerTransactions"
                            :key="tx.id"
                            class="hover:bg-[#fafbf7] transition-colors"
                        >
                            <td class="py-3 px-3">
                                <div class="font-bold text-[#0e0f0c]">{{ tx.invoice_number }}</div>
                                <div class="text-[10px] text-neutral-400">{{ tx.date }}</div>
                            </td>
                            <td class="py-3 px-3">
                                <div class="font-bold text-[#0e0f0c]">{{ tx.customer_name || 'Pelanggan Showroom' }}</div>
                                <span class="text-[9px] font-semibold text-neutral-500">
                                    {{ tx.payment_method || 'Transfer Bank' }}
                                </span>
                            </td>
                            <td class="py-3 px-3 max-w-[240px]">
                                <div class="truncate text-neutral-700 font-medium" :title="tx.items">{{ tx.items }}</div>
                            </td>
                            <td class="py-3 px-3 font-bold text-[#0e0f0c]">{{ tx.sales_person }}</td>
                            <td class="py-3 px-3 text-center">
                                <span
                                    :class="[
                                        'px-2 py-0.5 rounded-full text-[10px] font-bold',
                                        tx.discount_percent === 0 ? 'bg-neutral-100 text-neutral-600' : 'bg-amber-100 text-amber-800'
                                    ]"
                                >
                                    {{ tx.discount_percent === 0 ? 'Normal' : `Diskon ${tx.discount_percent}%` }}
                                </span>
                            </td>
                            <td class="py-3 px-3 text-right font-black text-[#0e0f0c]">
                                {{ formatRupiah(tx.net_amount) }}
                            </td>
                        </tr>
                        <tr v-if="filteredTransactions.length === 0">
                            <td colspan="6" class="text-center py-8 text-neutral-400 text-xs">
                                Tidak ada transaksi yang cocok dengan pencarian.
                            </td>
                        </tr>
                    </tbody>
                </table>
            </div>

            <!-- Footer Pagination Modal -->
            <div class="p-4 border-t border-neutral-100 bg-[#fafbf7] flex flex-col sm:flex-row items-center justify-between gap-3 text-xs">
                <div class="text-[#737868] font-medium text-[11px]">
                    Menampilkan <span class="font-bold text-[#0e0f0c]">{{ startLedgerIndex }} - {{ endLedgerIndex }}</span> dari <span class="font-bold text-[#0e0f0c]">{{ filteredTransactions.length }}</span> transaksi
                </div>

                <div v-if="ledgerTotalPages > 1" class="flex items-center gap-1.5">
                    <button
                        type="button"
                        @click="goToLedgerPage(ledgerPage - 1)"
                        :disabled="ledgerPage === 1"
                        class="px-3 py-1.5 rounded-xl border border-[#dce0cd] font-bold text-[11px] transition-all disabled:opacity-30 disabled:cursor-not-allowed hover:bg-neutral-100 cursor-pointer"
                    >
                        ← Sebelumnya
                    </button>

                    <div class="flex items-center gap-1">
                        <button
                            v-for="p in ledgerTotalPages"
                            :key="'lp-' + p"
                            type="button"
                            @click="goToLedgerPage(p)"
                            :class="[
                                'w-7 h-7 rounded-lg font-bold text-xs flex items-center justify-center transition-all cursor-pointer',
                                ledgerPage === p
                                    ? 'bg-[#0e0f0c] text-[#c6ef4e]'
                                    : 'text-[#737868] hover:bg-neutral-100'
                            ]"
                        >
                            {{ p }}
                        </button>
                    </div>

                    <button
                        type="button"
                        @click="goToLedgerPage(ledgerPage + 1)"
                        :disabled="ledgerPage === ledgerTotalPages"
                        class="px-3 py-1.5 rounded-xl border border-[#dce0cd] font-bold text-[11px] transition-all disabled:opacity-30 disabled:cursor-not-allowed hover:bg-neutral-100 cursor-pointer"
                    >
                        Selanjutnya →
                    </button>
                </div>
            </div>
        </div>
    </div>
</template>
