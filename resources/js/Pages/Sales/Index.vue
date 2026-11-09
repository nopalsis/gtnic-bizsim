<script setup>
import { Head, router } from '@inertiajs/vue3';
import { ref, computed, watch } from 'vue';
import SidebarNav from '@/Components/SidebarNav.vue';

const props = defineProps({
    transactions: {
        type: Array,
        default: () => [],
    },
    metrics: {
        type: Object,
        default: () => ({}),
    },
    availableProducts: {
        type: Array,
        default: () => [],
    },
});

// UI State
const searchQuery = ref('');
const showNewOrderModal = ref(false);
const isSubmitting = ref(false);
const currentPage = ref(1);
const perPage = ref(10);

// Format Currency
const formatRupiah = (number) => {
    if (number === null || number === undefined) return 'Rp 0';
    return new Intl.NumberFormat('id-ID', {
        style: 'currency',
        currency: 'IDR',
        minimumFractionDigits: 0,
        maximumFractionDigits: 0,
    }).format(number);
};

// Filtered Transactions
const filteredTransactions = computed(() => {
    if (!searchQuery.value) return props.transactions;
    const q = searchQuery.value.toLowerCase();
    return props.transactions.filter(
        (tx) =>
            tx.invoice_number?.toLowerCase().includes(q) ||
            tx.customer_name?.toLowerCase().includes(q) ||
            tx.sales_person_name?.toLowerCase().includes(q)
    );
});

// Reset page ke 1 saat user melakukan pencarian
watch(searchQuery, () => {
    currentPage.value = 1;
});

const totalPages = computed(() => {
    return Math.max(1, Math.ceil(filteredTransactions.value.length / perPage.value));
});

const paginatedTransactions = computed(() => {
    const start = (currentPage.value - 1) * perPage.value;
    return filteredTransactions.value.slice(start, start + perPage.value);
});

const startItemIndex = computed(() => {
    if (filteredTransactions.value.length === 0) return 0;
    return (currentPage.value - 1) * perPage.value + 1;
});

const endItemIndex = computed(() => {
    return Math.min(currentPage.value * perPage.value, filteredTransactions.value.length);
});

const goToPage = (page) => {
    if (page >= 1 && page <= totalPages.value) {
        currentPage.value = page;
    }
};

// New Order Form State
const newOrder = ref({
    customer_name: '',
    sales_person_name: 'Kang Asep (Sales Pengrajin)',
    payment_method: 'Transfer Bank (BCA)',
    notes: '',
    items: [
        { product_id: '', quantity: 1, base_price: 0, cost_price: 0, deal_price: 0 },
    ],
});

const onProductSelect = (index, productId) => {
    const selected = props.availableProducts.find((p) => p.id === Number(productId));
    if (selected) {
        newOrder.value.items[index].base_price = selected.base_price;
        newOrder.value.items[index].cost_price = selected.cost_price;
        newOrder.value.items[index].deal_price = selected.base_price;
    }
};

const addItemRow = () => {
    newOrder.value.items.push({
        product_id: '',
        quantity: 1,
        base_price: 0,
        cost_price: 0,
        deal_price: 0,
    });
};

const removeItemRow = (index) => {
    if (newOrder.value.items.length > 1) {
        newOrder.value.items.splice(index, 1);
    }
};

const computedGross = computed(() => {
    return newOrder.value.items.reduce((acc, it) => acc + (it.base_price * it.quantity), 0);
});

const computedNet = computed(() => {
    return newOrder.value.items.reduce((acc, it) => acc + (it.deal_price * it.quantity), 0);
});

const computedDiscount = computed(() => {
    return Math.max(0, computedGross.value - computedNet.value);
});

const submitOrder = () => {
    if (!newOrder.value.customer_name) {
        alert('Mohon isi nama pelanggan atau nama toko rekanan.');
        return;
    }
    const hasInvalidItem = newOrder.value.items.some((it) => !it.product_id || it.quantity <= 0);
    if (hasInvalidItem) {
        alert('Mohon lengkapi produk dan kuantitas pesanan.');
        return;
    }

    isSubmitting.value = true;
    router.post('/sales', newOrder.value, {
        onSuccess: () => {
            showNewOrderModal.value = false;
            newOrder.value = {
                customer_name: '',
                sales_person_name: 'Kang Asep (Sales Pengrajin)',
                payment_method: 'Transfer Bank (BCA)',
                notes: '',
                items: [{ product_id: '', quantity: 1, base_price: 0, cost_price: 0, deal_price: 0 }],
            };
            isSubmitting.value = false;
        },
        onError: () => {
            isSubmitting.value = false;
        },
    });
};
</script>

<template>
    <Head title="Penjualan & Kasir · OptimaBiz" />

    <div class="min-h-screen w-full bg-[#eaecdf] p-2 md:p-5 flex flex-col md:flex-row gap-5 font-sans">
        <!-- Sidebar Navigation Rail -->
        <SidebarNav active="sales" />

        <!-- Main Content -->
        <main class="flex-1 flex flex-col gap-5 min-w-0">
            <!-- Header Section -->
            <header class="bg-white/80 backdrop-blur-md rounded-[24px] p-5 border border-[#dce0cd] flex flex-col sm:flex-row items-start sm:items-center justify-between gap-4 shadow-sm">
                <div>
                    <div class="flex items-center gap-2">
                        <span class="w-2.5 h-2.5 rounded-full bg-[#c6ef4e] border border-[#a2c833]"></span>
                        <h1 class="text-xl font-black text-[#0e0f0c] tracking-tight">Penjualan & Kasir</h1>
                        <span class="text-[10px] font-bold px-2 py-0.5 rounded-full bg-neutral-100 text-neutral-600">Catatan Penjualan</span>
                    </div>
                    <p class="text-xs text-neutral-500 font-medium mt-1">Daftar transaksi penjualan eceran dan pesanan partai bengkel</p>
                </div>

                <button
                    type="button"
                    @click="showNewOrderModal = true"
                    class="bg-[#0e0f0c] hover:bg-neutral-800 text-white text-xs font-bold px-5 py-2.5 rounded-full transition flex items-center gap-2 shadow-sm cursor-pointer"
                >
                    <span class="text-[#c6ef4e] text-base leading-none">+</span>
                    <span>Catat Pesanan Baru</span>
                </button>
            </header>

            <!-- 4 Metric Cards -->
            <section class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4">
                <div class="bg-white rounded-[24px] p-5 border border-[#dce0cd] shadow-sm">
                    <span class="text-[10px] font-black uppercase tracking-wider text-neutral-400">Total Uang Penjualan</span>
                    <div class="text-2xl font-black text-[#0e0f0c] mt-2 tabular-nums">
                        {{ formatRupiah(props.metrics?.totalNetRevenue) }}
                    </div>
                    <div class="text-[11px] text-neutral-500 font-medium mt-1">
                        Dari {{ props.metrics?.totalTransactions || 0 }} nota penjualan
                    </div>
                </div>

                <div class="bg-[#c6ef4e] rounded-[24px] p-5 border border-[#b2db3a] shadow-sm">
                    <span class="text-[10px] font-black uppercase tracking-wider text-neutral-800">Total Potongan Harga (Diskon)</span>
                    <div class="text-2xl font-black text-[#0e0f0c] mt-2 tabular-nums">
                        {{ formatRupiah(props.metrics?.totalDiscount) }}
                    </div>
                    <div class="text-[11px] text-neutral-800 font-semibold mt-1">
                        Rata-rata diskon {{ props.metrics?.avgDiscountPercent || 0 }}%
                    </div>
                </div>

                <div class="bg-white rounded-[24px] p-5 border border-[#dce0cd] shadow-sm">
                    <span class="text-[10px] font-black uppercase tracking-wider text-neutral-400">Penjualan Harga Asli</span>
                    <div class="text-2xl font-black text-[#0e0f0c] mt-2 tabular-nums">
                        {{ formatRupiah(props.metrics?.totalGrossRevenue) }}
                    </div>
                    <div class="text-[11px] text-neutral-500 font-medium mt-1">
                        Sebelum dipotong diskon
                    </div>
                </div>

                <div class="bg-white rounded-[24px] p-5 border border-[#dce0cd] shadow-sm">
                    <span class="text-[10px] font-black uppercase tracking-wider text-neutral-400">Status Diskon</span>
                    <div class="text-2xl font-black text-[#0e0f0c] mt-2">
                        {{ (props.metrics?.avgDiscountPercent || 0) <= 15 ? 'Diskon Wajar (Aman)' : 'Diskon Terlalu Besar' }}
                    </div>
                    <div class="text-[11px] text-neutral-500 font-medium mt-1">
                        Batas aman diskon di bawah 15%
                    </div>
                </div>
            </section>

            <!-- Table of Transactions -->
            <section class="bg-white rounded-[28px] p-6 border border-[#dce0cd] shadow-sm flex flex-col gap-4">
                <div class="flex flex-col sm:flex-row items-start sm:items-center justify-between gap-3">
                    <div>
                        <h2 class="text-base font-black text-[#0e0f0c]">Riwayat Penjualan & Pesanan</h2>
                        <p class="text-xs text-neutral-400">Catatan transaksi penjualan eceran dan pesanan grosir</p>
                    </div>

                    <div class="w-full sm:w-72">
                        <input
                            v-model="searchQuery"
                            type="text"
                            placeholder="Cari nomor nota, pembeli, atau nama sales..."
                            class="w-full bg-[#f6f7f7] rounded-full px-4 py-2 text-xs font-semibold border-0 outline-none focus:ring-2 focus:ring-[#0e0f0c]"
                        />
                    </div>
                </div>

                <div class="overflow-x-auto">
                    <table class="w-full text-left text-xs border-collapse">
                        <thead>
                            <tr class="border-b border-neutral-100 text-[10px] font-bold uppercase tracking-wider text-neutral-400">
                                <th class="py-3 px-3">Nomor Nota & Tanggal</th>
                                <th class="py-3 px-3">Pembeli / Toko</th>
                                <th class="py-3 px-3">Petugas & Pembayaran</th>
                                <th class="py-3 px-3">Sepatu yang Dibeli</th>
                                <th class="py-3 px-3 text-right">Harga Asli</th>
                                <th class="py-3 px-3 text-center">Diskon</th>
                                <th class="py-3 px-3 text-right">Total Bayar</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-neutral-100">
                            <tr
                                v-for="tx in paginatedTransactions"
                                :key="tx.id"
                                class="hover:bg-[#fafbf7] transition-colors"
                            >
                                <td class="py-3 px-3">
                                    <div class="font-bold text-[#0e0f0c]">{{ tx.invoice_number }}</div>
                                    <div class="text-[10px] text-neutral-400">{{ tx.transaction_date }}</div>
                                </td>
                                <td class="py-3 px-3 font-bold text-[#0e0f0c]">
                                    {{ tx.customer_name }}
                                </td>
                                <td class="py-3 px-3">
                                    <div class="font-medium text-neutral-700">{{ tx.sales_person_name }}</div>
                                    <span class="text-[10px] px-2 py-0.5 rounded-full bg-neutral-100 text-neutral-600 inline-block mt-0.5">
                                        {{ tx.payment_method }}
                                    </span>
                                </td>
                                <td class="py-3 px-3 max-w-[280px]">
                                    <div class="space-y-1">
                                        <div
                                            v-for="item in tx.items"
                                            :key="item.id"
                                            class="text-[11px] text-neutral-700"
                                        >
                                            <span class="font-bold">{{ item.product_name }}</span>
                                            <span class="text-neutral-500"> (x{{ item.quantity }} {{ item.unit }})</span>
                                        </div>
                                    </div>
                                </td>
                                <td class="py-3 px-3 text-right text-neutral-500">
                                    {{ formatRupiah(tx.total_amount) }}
                                </td>
                                <td class="py-3 px-3 text-center">
                                    <span
                                        :class="[
                                            'px-2.5 py-0.5 rounded-full text-[10px] font-bold',
                                            tx.discount_percent === 0
                                                ? 'bg-neutral-100 text-neutral-600'
                                                : tx.discount_percent <= 12
                                                ? 'bg-emerald-50 text-emerald-700 border border-emerald-200'
                                                : 'bg-amber-50 text-amber-800 border border-amber-200'
                                        ]"
                                    >
                                        {{ tx.discount_percent === 0 ? 'Harga Pas' : `Diskon ${tx.discount_percent}% (-${formatRupiah(tx.total_discount)})` }}
                                    </span>
                                </td>
                                <td class="py-3 px-3 text-right font-black text-[#0e0f0c]">
                                    {{ formatRupiah(tx.net_amount) }}
                                </td>
                            </tr>
                            <tr v-if="filteredTransactions.length === 0">
                                <td colspan="7" class="text-center py-8 text-neutral-400">
                                    Belum ada catatan penjualan yang cocok.
                                </td>
                            </tr>
                        </tbody>
                    </table>
                </div>

                <!-- Kontrol Navigasi Halaman (Pagination) -->
                <div class="flex flex-col sm:flex-row items-center justify-between gap-3 pt-3 border-t border-neutral-100 text-xs">
                    <div class="text-[#737868] font-medium text-[11px]">
                        Menampilkan <span class="font-bold text-[#0e0f0c]">{{ startItemIndex }} - {{ endItemIndex }}</span> dari <span class="font-bold text-[#0e0f0c]">{{ filteredTransactions.length }}</span> riwayat penjualan
                    </div>

                    <div v-if="totalPages > 1" class="flex items-center gap-1.5">
                        <button
                            type="button"
                            @click="goToPage(currentPage - 1)"
                            :disabled="currentPage === 1"
                            class="px-3 py-1.5 rounded-xl border border-[#dce0cd] font-bold text-[11px] transition-all disabled:opacity-30 disabled:cursor-not-allowed hover:bg-neutral-100 cursor-pointer flex items-center gap-1"
                        >
                            <span>←</span>
                            <span>Sebelumnya</span>
                        </button>

                        <div class="flex items-center gap-1">
                            <button
                                v-for="p in totalPages"
                                :key="'page-' + p"
                                type="button"
                                @click="goToPage(p)"
                                :class="[
                                    'w-8 h-8 rounded-xl font-black text-xs transition-all cursor-pointer flex items-center justify-center',
                                    currentPage === p
                                        ? 'bg-[#0e0f0c] text-[#c6ef4e] shadow-xs'
                                        : 'text-[#737868] hover:bg-neutral-100'
                                ]"
                            >
                                {{ p }}
                            </button>
                        </div>

                        <button
                            type="button"
                            @click="goToPage(currentPage + 1)"
                            :disabled="currentPage === totalPages"
                            class="px-3 py-1.5 rounded-xl border border-[#dce0cd] font-bold text-[11px] transition-all disabled:opacity-30 disabled:cursor-not-allowed hover:bg-neutral-100 cursor-pointer flex items-center gap-1"
                        >
                            <span>Selanjutnya</span>
                            <span>→</span>
                        </button>
                    </div>
                </div>
            </section>
        </main>

        <!-- New Order Modal -->
        <div
            v-if="showNewOrderModal"
            class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-[#0e0f0c]/60 backdrop-blur-sm"
            @click.self="showNewOrderModal = false"
        >
            <div class="bg-white rounded-[28px] w-full max-w-2xl max-h-[90vh] flex flex-col shadow-2xl border border-[#dce0cd] overflow-hidden">
                <div class="p-6 border-b border-neutral-100 flex items-center justify-between bg-[#fafbf7]">
                    <div>
                        <h3 class="text-xl font-black text-[#0e0f0c]">Catat Pesanan Sepatu / Sandal</h3>
                        <p class="text-xs text-neutral-500 mt-0.5">Catat pesanan baru eceran atau grosir dengan harga yang disepakati</p>
                    </div>
                    <button
                        type="button"
                        @click="showNewOrderModal = false"
                        class="w-9 h-9 rounded-full bg-neutral-200 hover:bg-neutral-300 flex items-center justify-center text-neutral-700 cursor-pointer font-bold"
                    >
                        ✕
                    </button>
                </div>

                <div class="p-6 overflow-y-auto space-y-4">
                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                        <div>
                            <label class="block text-[11px] font-bold text-neutral-600 mb-1">Nama Pembeli / Toko *</label>
                            <input
                                v-model="newOrder.customer_name"
                                type="text"
                                placeholder="Contoh: Toko Sepatu Makmur / Pak Hendra"
                                class="w-full bg-[#f6f7f7] rounded-xl px-3.5 py-2 text-xs font-semibold outline-none focus:ring-2 focus:ring-[#0e0f0c]"
                            />
                        </div>
                        <div>
                            <label class="block text-[11px] font-bold text-neutral-600 mb-1">Petugas / Sales *</label>
                            <select
                                v-model="newOrder.sales_person_name"
                                class="w-full bg-[#f6f7f7] rounded-xl px-3.5 py-2 text-xs font-semibold outline-none focus:ring-2 focus:ring-[#0e0f0c]"
                            >
                                <option value="Kang Asep (Sales Pengrajin)">Kang Asep (Sales Pengrajin)</option>
                                <option value="Pak Bambang (Workshop Head)">Pak Bambang (Workshop Head)</option>
                                <option value="Rian (Wholesale & Store)">Rian (Wholesale & Store)</option>
                                <option value="Siti (Admin Toko & Reseller)">Siti (Admin Toko & Reseller)</option>
                            </select>
                        </div>
                        <div>
                            <label class="block text-[11px] font-bold text-neutral-600 mb-1">Cara Bayar *</label>
                            <select
                                v-model="newOrder.payment_method"
                                class="w-full bg-[#f6f7f7] rounded-xl px-3.5 py-2 text-xs font-semibold outline-none focus:ring-2 focus:ring-[#0e0f0c]"
                            >
                                <option value="Transfer Bank (BCA)">Transfer Bank (BCA)</option>
                                <option value="Transfer Bank (Mandiri)">Transfer Bank (Mandiri)</option>
                                <option value="QRIS">QRIS</option>
                                <option value="Tunai">Tunai</option>
                                <option value="Tempo / Termin 14 Hari">Tempo / Termin 14 Hari</option>
                            </select>
                        </div>
                        <div>
                            <label class="block text-[11px] font-bold text-neutral-600 mb-1">Catatan Pesanan</label>
                            <input
                                v-model="newOrder.notes"
                                type="text"
                                placeholder="Catatan khusus atau pengiriman"
                                class="w-full bg-[#f6f7f7] rounded-xl px-3.5 py-2 text-xs font-semibold outline-none focus:ring-2 focus:ring-[#0e0f0c]"
                            />
                        </div>
                    </div>

                    <!-- Item Rows -->
                    <div class="border-t border-neutral-100 pt-4">
                        <div class="flex items-center justify-between mb-2">
                            <h4 class="text-xs font-black text-[#0e0f0c]">Daftar Sepatu yang Dipesan</h4>
                            <button
                                type="button"
                                @click="addItemRow"
                                class="text-xs font-bold text-[#0e0f0c] hover:underline cursor-pointer"
                            >
                                + Tambah Sepatu
                            </button>
                        </div>

                        <div class="space-y-3">
                            <div
                                v-for="(item, idx) in newOrder.items"
                                :key="idx"
                                class="p-3 bg-[#fafbf7] rounded-2xl border border-neutral-200 space-y-2"
                            >
                                <div class="grid grid-cols-1 sm:grid-cols-12 gap-2 items-center">
                                    <div class="sm:col-span-6">
                                        <label class="text-[10px] font-bold text-neutral-500">Pilih Model Sepatu</label>
                                        <select
                                            v-model="item.product_id"
                                            @change="onProductSelect(idx, item.product_id)"
                                            class="w-full bg-white rounded-lg px-2.5 py-1.5 text-xs font-semibold border border-neutral-200 outline-none"
                                        >
                                            <option value="" disabled>-- Pilih Model --</option>
                                            <option
                                                v-for="p in props.availableProducts"
                                                :key="p.id"
                                                :value="p.id"
                                            >
                                                {{ p.sku }} - {{ p.name }} (Sisa: {{ p.current_stock }})
                                            </option>
                                        </select>
                                    </div>
                                    <div class="sm:col-span-2">
                                        <label class="text-[10px] font-bold text-neutral-500">Jumlah (Pasang)</label>
                                        <input
                                            v-model.number="item.quantity"
                                            type="number"
                                            min="1"
                                            class="w-full bg-white rounded-lg px-2.5 py-1.5 text-xs font-bold border border-neutral-200 outline-none text-center"
                                        />
                                    </div>
                                    <div class="sm:col-span-3">
                                        <label class="text-[10px] font-bold text-neutral-500">Harga Sepakat / Psg</label>
                                        <input
                                            v-model.number="item.deal_price"
                                            type="number"
                                            step="1000"
                                            class="w-full bg-white rounded-lg px-2.5 py-1.5 text-xs font-black border border-neutral-200 outline-none"
                                        />
                                    </div>
                                    <div class="sm:col-span-1 text-right pt-4">
                                        <button
                                            type="button"
                                            @click="removeItemRow(idx)"
                                            class="text-rose-500 hover:text-rose-700 text-xs font-bold"
                                        >
                                            ✕
                                        </button>
                                    </div>
                                </div>
                                <div v-if="item.base_price > 0" class="text-[10px] flex items-center justify-between text-neutral-500 pt-1 border-t border-neutral-100">
                                    <span>Harga Asli: {{ formatRupiah(item.base_price) }} (Modal: {{ formatRupiah(item.cost_price) }})</span>
                                    <span :class="item.deal_price < item.cost_price ? 'text-rose-600 font-bold' : 'text-emerald-700'">
                                        {{ item.deal_price < item.cost_price ? '⚠️ Di bawah modal bahan!' : 'Subtotal: ' + formatRupiah(item.deal_price * item.quantity) }}
                                    </span>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Summary & Submit -->
                    <div class="bg-[#f6f7f7] rounded-2xl p-4 flex items-center justify-between">
                        <div>
                            <div class="text-[11px] text-neutral-500">Harga Asli: {{ formatRupiah(computedGross) }} | Diskon: {{ formatRupiah(computedDiscount) }}</div>
                            <div class="text-sm font-black text-[#0e0f0c]">Total yang Harus Dibayar: {{ formatRupiah(computedNet) }}</div>
                        </div>
                        <button
                            type="button"
                            @click="submitOrder"
                            :disabled="isSubmitting"
                            class="bg-[#0e0f0c] hover:bg-neutral-800 text-white font-bold text-xs px-5 py-2.5 rounded-full transition cursor-pointer"
                        >
                            {{ isSubmitting ? 'Menyimpan...' : 'Simpan Pesanan' }}
                        </button>
                    </div>
                </div>
            </div>
        </div>
    </div>
</template>
