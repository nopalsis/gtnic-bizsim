<script setup>
import { Head, Link } from '@inertiajs/vue3';
import { ref, computed } from 'vue';

const props = defineProps({
    kpis: {
        type: Object,
        default: () => ({
            netRevenue: 4653500,
            grossRevenue: 4778000,
            totalDiscount: 124500,
            netProfit: -13182500,
            grossProfit: 2637500,
            profitMargin: 56.7,
            wasteCost: 120000,
            wasteCount: 5,
        }),
    },
    capacity: {
        type: Object,
        default: () => ({
            soldUnits: 106,
            maxCapacity: 5000,
            usagePercent: 2.1,
            status: 'Optimal & Terkendali',
        }),
    },
    costDistribution: {
        type: Array,
        default: () => [
            { category: 'HPP Bahan (COGS)', amount: 2016000, percentage: 11.3 },
            { category: 'Gaji Karyawan', amount: 11200000, percentage: 62.8 },
            { category: 'Operasional / Sewa', amount: 4500000, percentage: 25.2 },
            { category: 'Kerugian Bahan (Waste)', amount: 120000, percentage: 0.7 },
        ],
    },
    recentTransactions: {
        type: Array,
        default: () => [
            {
                id: 20,
                invoice_number: 'INV-202610-0020',
                date: '02 Oct 2026 · 18:47',
                sales_person: 'Dewi Lestari',
                items: 'Caffe Latte (x1), Cappuccino (x2), Earl Grey (x2)',
                total_amount: 124000,
                total_discount: 1500,
                net_amount: 122500,
                discount_percent: 1.2,
                status: 'safe',
            },
            {
                id: 19,
                invoice_number: 'INV-202610-0019',
                date: '01 Oct 2026 · 15:02',
                sales_person: 'Fajar Nugraha',
                items: 'Americano (x2), Signature Blend (x1), Aceh Gayo (x3)',
                total_amount: 441000,
                total_discount: 3500,
                net_amount: 437500,
                discount_percent: 0.8,
                status: 'safe',
            },
            {
                id: 18,
                invoice_number: 'INV-202610-0018',
                date: '29 Sep 2026 · 17:21',
                sales_person: 'Siti Rahmawati',
                items: 'Caramel Macchiato (x3), House Blend (x1), Tumbler (x2)',
                total_amount: 516000,
                total_discount: 20000,
                net_amount: 496000,
                discount_percent: 3.9,
                status: 'safe',
            },
            {
                id: 17,
                invoice_number: 'INV-202610-0017',
                date: '28 Sep 2026 · 20:45',
                sales_person: 'Budi Santoso',
                items: 'Americano (x3), House Blend Beans 250g (x3)',
                total_amount: 321000,
                total_discount: 0,
                net_amount: 321000,
                discount_percent: 0,
                status: 'normal',
            },
            {
                id: 16,
                invoice_number: 'INV-202610-0016',
                date: '26 Sep 2026 · 19:28',
                sales_person: 'Dewi Lestari',
                items: 'Caramel Macchiato (x3)',
                total_amount: 96000,
                total_discount: 0,
                net_amount: 96000,
                discount_percent: 0,
                status: 'normal',
            },
        ],
    },
    healthScore: {
        type: Object,
        default: () => ({
            score: 88,
            grade: 'A-',
            status: 'Stabilitas Keuangan Sehat',
        }),
    },
    alerts: {
        type: Array,
        default: () => [
            {
                type: 'risk',
                title: 'Peringatan Kebocoran Waste',
                message: 'Terjadi kerugian bahan senilai Rp 120.000 (5 unit terbuang). Evaluasi batas kedaluwarsa produk etalase.',
            },
            {
                type: 'negotiation',
                title: 'Proteksi Margin Negosiasi',
                message: 'Rata-rata margin keuntungan berada di level 56.7%. Pastikan diskon tawar-menawar sales tidak melebihi 10% per transaksi.',
            },
        ],
    },
});

// UI State Management
const activeNav = ref('dashboard');
const selectedCategory = ref('Financial');
const showCategoryDropdown = ref(false);
const selectedPeriod = ref('Month');
const showPeriodDropdown = ref(false);
const showNotifications = ref(false);
const showTransactionsModal = ref(false);
const showPlaybookModal = ref(false);
const showCardBalanceToggle = ref(false);
const hoveredRadarIndex = ref(null);
const txSearchQuery = ref('');

// Quick Transfer Interactive State
const selectedRecipient = ref('Budi Santoso');
const transferAmount = ref('150000');
const transferToast = ref(null);
const isSending = ref(false);

const recipients = [
    {
        id: 1,
        name: 'Budi Santoso',
        role: 'Head Barista / Kasir',
        avatar: 'https://lh3.googleusercontent.com/aida-public/AB6AXuAA2WrzwSI_DG1vgiShm7icfPfq1b0WIK2mGHHhIuWxcJXrH4fVkO8FVAonQEE98rtfu_vw0K-sZXYZy5RxshHf6WXbN9Fq3zJYQCCNV2x0D3pAlylniDY4oonIwq7Nt89cfcAj5L8Ng2Y_4LoM1LxA4nYUJSWcE3-xhA22tKFM7KIfyJzKBnGtNveqySw8tSfHAW997Tj1hgf_XdPuMck6yqaYoSuaESvZrHctTgm4z1Oh0eU5iReE',
    },
    {
        id: 2,
        name: 'Siti Rahmawati',
        role: 'Supervisor Operasional',
        avatar: 'https://lh3.googleusercontent.com/aida-public/AB6AXuA5yW2mrtdJCJVPBHpdIwRMldNTaqMFNx_ZhvDDuhHer3EmHeWy1wf3cis7aKNWmBCPsUfSSDBG8epiLuYyM1SRhCIv0C8-k9C-DF-i3UWtSg5qx8_5vK2uXUHFUZILXotN1fxa6vHErvKUGd2qMs6JG7dJTJLV8vbj6Cl5AKpnJ0LfC2UuZMi22-AQkiAdykB2sf9aYQZms0tTvHU4yJI2eXiPOAnowHETFa2jbcy9RXIDu7kX_V8z',
    },
    {
        id: 3,
        name: 'Dewi Lestari',
        role: 'Staff Inventory & Order',
        avatar: 'https://lh3.googleusercontent.com/aida-public/AB6AXuD-zmseFYxdFxupwhPfLU6vZsRVhG5UFkZglkPmRjFY6eLQqiWBGPOfY94bgtmLvPYuqy6X7ocN5N5dZ2sQEoVwCTLb3t1oENxWHxTTAmGt1PTe1hPOpid8a6WkcI8ChJsw9iCrCPWXPo0s0GAlBe-3A2kgZ9z3cOicYFeOXe4vcV0-5g_EBlqy8VZnd3CqLhhfuHZ8F3zXXBli6Pno4rjIp1Tryn4qUsKYyBVeM3uXOJwB-nEDseC0',
    },
    {
        id: 4,
        name: 'Fajar Nugraha',
        role: 'Sales Representative',
        avatar: 'https://lh3.googleusercontent.com/aida-public/AB6AXuChfIt5ipYxz6LRTiXuZyh0XeScH9q5G0Z0Ce0l07qVsU7rO4-tXfNBKK2uxNQNn5RJW_IXveZkDReR5Li55B4ixTJ5ErqxsmDTkxEKywmmMFUiGjBLTDNip7IiQ3Ufza73aCipZDdT0O26JGmfYoSn4l5fRrGJEQXu8NGF-gtXwOqRq_pkSvY143D_NTsAyZZiHDRQlrU2mrIRSlnhBGDHHRMXmY5_bBMnDxX9TAQa2dS5pH0VuX4S',
    },
];

// Currency Formatter
const formatRupiah = (number) => {
    if (number === null || number === undefined) return 'Rp 0';
    return new Intl.NumberFormat('id-ID', {
        style: 'currency',
        currency: 'IDR',
        minimumFractionDigits: 0,
        maximumFractionDigits: 0,
    }).format(number);
};

// Compact Number Formatter (e.g. 4.6M, 120k)
const formatCompact = (number) => {
    if (number === null || number === undefined) return '0';
    if (Math.abs(number) >= 1_000_000) {
        return (number / 1_000_000).toFixed(1) + 'M';
    }
    if (Math.abs(number) >= 1_000) {
        return (number / 1_000).toFixed(0) + 'k';
    }
    return number.toString();
};

// Card balance computed
const displayedCardBalance = computed(() => {
    if (showCardBalanceToggle.value) {
        return {
            title: 'Gross Revenue',
            amount: props.kpis.grossRevenue,
            formatted: formatRupiah(props.kpis.grossRevenue),
            subLabel: 'Total Penjualan Kotor',
        };
    }
    return {
        title: 'Universal',
        amount: props.kpis.netRevenue,
        formatted: formatRupiah(props.kpis.netRevenue),
        subLabel: 'OptimaBiz Core Vault',
    };
});

// Radar Chart Categories & Polygon Coordinates
// Categories placed at 6 vertices: 0° (top), 60°, 120°, 180° (bottom), 240°, 300°
const radarCategories = computed(() => [
    { label: 'Food & COGS', key: 'HPP', percentage: props.costDistribution[0]?.percentage || 18, amount: props.costDistribution[0]?.amount || 0 },
    { label: 'Bills / Sewa', key: 'Bills', percentage: props.costDistribution[2]?.percentage || 25, amount: props.costDistribution[2]?.amount || 0 },
    { label: 'Staff Payroll', key: 'Payroll', percentage: props.costDistribution[1]?.percentage || 62, amount: props.costDistribution[1]?.amount || 0 },
    { label: 'Waste Loss', key: 'Waste', percentage: Math.min((props.kpis.wasteCost / (props.kpis.grossRevenue || 1)) * 100 * 3, 40), amount: props.kpis.wasteCost },
    { label: 'Store Margin', key: 'Margin', percentage: Math.min(props.kpis.profitMargin, 100), amount: props.kpis.grossProfit },
    { label: 'Operasional', key: 'Ops', percentage: 22, amount: 980000 },
]);

// Convert polar to cartesian centered at (200, 180) with max radius 150
const getRadarCoords = (percentage, index, count = 6, maxR = 140) => {
    const angle = (Math.PI * 2 * index) / count - Math.PI / 2;
    const r = Math.max(Math.min(percentage / 100, 1), 0.15) * maxR;
    const x = 200 + r * Math.cos(angle);
    const y = 180 + r * Math.sin(angle);
    return { x: Math.round(x), y: Math.round(y) };
};

// SVG polygon points string for current month radar
const radarMonthPoints = computed(() => {
    return radarCategories.value
        .map((cat, i) => {
            const pt = getRadarCoords(cat.percentage, i);
            return `${pt.x},${pt.y}`;
        })
        .join(' ');
});

// Filtered transactions for the modal
const filteredTransactions = computed(() => {
    if (!txSearchQuery.value) return props.recentTransactions;
    const q = txSearchQuery.value.toLowerCase();
    return props.recentTransactions.filter(
        (tx) =>
            tx.invoice_number.toLowerCase().includes(q) ||
            tx.sales_person.toLowerCase().includes(q) ||
            (tx.items && tx.items.toLowerCase().includes(q))
    );
});

// Quick Transfer Execution
const handleQuickTransfer = () => {
    const num = parseFloat(transferAmount.value);
    if (isNaN(num) || num <= 0) {
        alert('Silakan masukkan nominal transfer yang valid.');
        return;
    }

    isSending.value = true;
    setTimeout(() => {
        isSending.value = false;
        transferToast.value = {
            name: selectedRecipient.value,
            amount: formatRupiah(num),
        };
        setTimeout(() => {
            transferToast.value = null;
        }, 4000);
    }, 600);
};

// Export Functionality
const handleExport = () => {
    const csvContent =
        'data:text/csv;charset=utf-8,' +
        'Invoice,Date,Sales,Items,Total,Discount,Net\n' +
        props.recentTransactions
            .map(
                (t) =>
                    `"${t.invoice_number}","${t.date}","${t.sales_person}","${t.items}",${t.total_amount},${t.total_discount},${t.net_amount}`
            )
            .join('\n');
    const encodedUri = encodeURI(csvContent);
    const link = document.createElement('a');
    link.setAttribute('href', encodedUri);
    link.setAttribute('download', `optimabiz_ledger_${new Date().toISOString().slice(0, 10)}.csv`);
    document.body.appendChild(link);
    link.click();
    document.body.removeChild(link);
};
</script>

<template>
    <Head title="OptimaBiz - Financial & Operations Intelligence Dashboard" />

    <div class="dashboard-wrapper min-h-screen w-full bg-[#eaecdf] font-sans text-deep-black p-2 md:p-4 lg:p-5 flex selection:bg-neon-lime selection:text-deep-black bg-grid-ambient">
        <!-- Toast Notification on Transfer Simulation -->
        <Transition
            enter-active-class="transform ease-out duration-300 transition"
            enter-from-class="translate-y-2 opacity-0 sm:translate-y-0 sm:translate-x-2"
            enter-to-class="translate-y-0 opacity-100 sm:translate-x-0"
            leave-active-class="transition ease-in duration-200"
            leave-from-class="opacity-100"
            leave-to-class="opacity-0"
        >
            <div
                v-if="transferToast"
                class="fixed bottom-6 right-6 z-50 bg-deep-black text-white px-5 py-3.5 rounded-2xl shadow-2xl border border-neutral-700 flex items-center gap-3.5"
            >
                <div class="w-8 h-8 rounded-full bg-neon-lime text-deep-black flex items-center justify-center font-black">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24">
                        <path d="M5 13l4 4L19 7" stroke-linecap="round" stroke-linejoin="round"></path>
                    </svg>
                </div>
                <div>
                    <div class="text-xs font-bold text-neutral-300">Transfer Simulasi Berhasil</div>
                    <div class="text-sm font-extrabold text-neon-lime">
                        {{ transferToast.amount }} → {{ transferToast.name }}
                    </div>
                </div>
            </div>
        </Transition>

        <!-- BEGIN: Main Dashboard Shell matching Stitch layout -->
        <div class="w-full bg-[#eaecdf]/90 backdrop-blur-md rounded-[36px] p-2 md:p-5 flex flex-col md:flex-row gap-5 border border-[#dce0cd] min-h-screen shadow-sm">
            <!-- ======================================================== -->
            <!-- BEGIN: LeftSidebar (Architectural Dark Dock)            -->
            <!-- ======================================================== -->
            <aside class="w-full md:w-[76px] bg-deep-black rounded-[28px] py-6 px-3 flex flex-row md:flex-col items-center justify-between shadow-2xl flex-shrink-0 z-20 md:sticky md:top-5 md:self-start md:h-[calc(100vh-2.5rem)]">
                <!-- Brand Logo / Lime Ring Indicator -->
                <div class="flex items-center gap-3 md:flex-col">
                    <Link href="/" class="group relative flex items-center justify-center w-12 h-12" title="OptimaBiz Home">
                        <svg class="w-10 h-10 transition-transform duration-300 group-hover:scale-105" fill="none" viewBox="0 0 44 44">
                            <circle cx="22" cy="22" r="18" stroke="#22241e" stroke-linecap="round" stroke-width="4.5"></circle>
                            <path d="M22 4C31.9411 4 40 12.0589 40 22C40 25.5 39 28.5 37.5 31" stroke="#c6ef4e" stroke-linecap="round" stroke-width="4.5"></path>
                            <circle cx="12" cy="34" fill="#c6ef4e" r="3"></circle>
                        </svg>
                    </Link>
                </div>

                <!-- Navigation Icons List -->
                <nav aria-label="Main Navigation" class="flex flex-row md:flex-col items-center gap-2.5 my-auto">
                    <!-- Active Dashboard Link -->
                    <button
                        type="button"
                        @click="activeNav = 'dashboard'"
                        :class="[
                            'w-12 h-12 rounded-2xl flex items-center justify-center transition-all cursor-pointer',
                            activeNav === 'dashboard' ? 'bg-[#262822] text-white shadow-inner ring-1 ring-white/10' : 'text-[#828876] hover:text-white hover:bg-[#20221c]'
                        ]"
                        title="Dashboard"
                    >
                        <svg class="w-5 h-5 text-white" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                            <path d="M3 12l2-2m0 0l7-7 7 7M5 10v10a1 1 0 001 1h3m10-11l2 2m-2-2v10a1 1 0 01-1 1h-3m-6 0a1 1 0 001-1v-4a1 1 0 011-1h2a1 1 0 011 1v4a1 1 0 001 1m-6 0h6" stroke-linecap="round" stroke-linejoin="round"></path>
                        </svg>
                    </button>

                    <!-- Simulator / Cashflow -->
                    <button
                        type="button"
                        @click="activeNav = 'simulator'"
                        :class="[
                            'w-12 h-12 rounded-2xl flex items-center justify-center transition-all cursor-pointer',
                            activeNav === 'simulator' ? 'bg-[#262822] text-white shadow-inner' : 'text-[#828876] hover:text-white hover:bg-[#20221c]'
                        ]"
                        title="Cost & Cashflow Simulator"
                    >
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                            <path d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z" stroke-linecap="round" stroke-linejoin="round"></path>
                        </svg>
                    </button>

                    <!-- Wallet / Financial Transactions -->
                    <button
                        type="button"
                        @click="showTransactionsModal = true"
                        :class="[
                            'w-12 h-12 rounded-2xl flex items-center justify-center transition-all cursor-pointer',
                            activeNav === 'wallet' ? 'bg-[#262822] text-white shadow-inner' : 'text-[#828876] hover:text-white hover:bg-[#20221c]'
                        ]"
                        title="Financial Accounts & Ledger"
                    >
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                            <path d="M3 10h18M7 15h1m4 0h1m-7 4h12a3 3 0 003-3V8a3 3 0 00-3-3H6a3 3 0 00-3 3v8a3 3 0 003 3z" stroke-linecap="round" stroke-linejoin="round"></path>
                        </svg>
                    </button>

                    <!-- Calendar / Schedule -->
                    <button
                        type="button"
                        @click="showPeriodDropdown = !showPeriodDropdown"
                        :class="[
                            'w-12 h-12 rounded-2xl flex items-center justify-center transition-all cursor-pointer',
                            activeNav === 'schedule' ? 'bg-[#262822] text-white shadow-inner' : 'text-[#828876] hover:text-white hover:bg-[#20221c]'
                        ]"
                        title="Calendar & Period Cycles"
                    >
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                            <path d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z" stroke-linecap="round" stroke-linejoin="round"></path>
                        </svg>
                    </button>

                    <!-- Chat / Support AI Advisor -->
                    <button
                        type="button"
                        @click="showPlaybookModal = true"
                        class="w-12 h-12 rounded-2xl text-[#828876] hover:text-white hover:bg-[#20221c] flex items-center justify-center transition-all cursor-pointer relative"
                        title="AI Financial Advisor & Playbook"
                    >
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                            <path d="M8 12h.01M12 12h.01M16 12h.01M21 12c0 4.418-4.03 8-9 8a9.863 9.863 0 01-4.255-.949L3 20l1.395-3.72C3.512 15.042 3 13.574 3 12c0-4.418 4.03-8 9-8s9 3.582 9 8z" stroke-linecap="round" stroke-linejoin="round"></path>
                        </svg>
                        <span v-if="props.alerts.length" class="absolute top-2 right-2 w-2 h-2 rounded-full bg-neon-lime"></span>
                    </button>
                </nav>

                <!-- Bottom Profile or Secondary Action -->
                <div class="hidden md:flex flex-col items-center">
                    <Link
                        href="/"
                        class="w-10 h-10 rounded-full border border-neutral-700 hover:border-neutral-500 flex items-center justify-center text-neutral-400 hover:text-white transition-colors cursor-pointer"
                        title="Kembali ke Beranda"
                    >
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                            <path d="M10 19l-7-7m0 0l7-7m-7 7h18" stroke-linecap="round" stroke-linejoin="round"></path>
                        </svg>
                    </Link>
                </div>
            </aside>
            <!-- END: LeftSidebar -->

            <!-- ======================================================== -->
            <!-- BEGIN: DashboardContentArea                             -->
            <!-- ======================================================== -->
            <main class="flex-1 flex flex-col gap-6 min-w-0">
                <!-- BEGIN: TopBar -->
                <header class="flex flex-wrap items-center justify-between gap-4 pt-2 px-1">
                    <!-- Title & Category Selector -->
                    <div class="flex items-center gap-4">
                        <h1 class="text-3xl font-extrabold tracking-tight text-deep-black">Dashboard</h1>
                        <!-- Category Filter Pill -->
                        <div class="relative inline-block">
                            <button
                                type="button"
                                @click="showCategoryDropdown = !showCategoryDropdown"
                                class="flex items-center gap-2 bg-white/90 hover:bg-white text-deep-black px-4 py-2 rounded-full border border-card-border shadow-sm text-sm font-semibold transition-all cursor-pointer"
                            >
                                <span>{{ selectedCategory }}</span>
                                <svg class="w-4 h-4 text-neutral-500 transition-transform duration-200" :class="{ 'rotate-180': showCategoryDropdown }" fill="none" stroke="currentColor" stroke-width="2.2" viewBox="0 0 24 24">
                                    <path d="M19 9l-7 7-7-7" stroke-linecap="round" stroke-linejoin="round"></path>
                                </svg>
                            </button>

                            <!-- Category Dropdown Menu -->
                            <div
                                v-if="showCategoryDropdown"
                                class="absolute left-0 mt-2 w-44 bg-white rounded-2xl border border-card-border shadow-xl py-1.5 z-40"
                            >
                                <button
                                    type="button"
                                    @click="selectedCategory = 'Financial'; showCategoryDropdown = false"
                                    class="w-full text-left px-4 py-2 text-xs font-bold text-deep-black hover:bg-neutral-100/70"
                                >
                                    Financial
                                </button>
                                <button
                                    type="button"
                                    @click="selectedCategory = 'Operasional'; showCategoryDropdown = false"
                                    class="w-full text-left px-4 py-2 text-xs font-semibold text-neutral-700 hover:bg-neutral-100/70"
                                >
                                    Operasional
                                </button>
                                <button
                                    type="button"
                                    @click="selectedCategory = 'Inventaris & Waste'; showCategoryDropdown = false"
                                    class="w-full text-left px-4 py-2 text-xs font-semibold text-neutral-700 hover:bg-neutral-100/70"
                                >
                                    Inventaris & Waste
                                </button>
                            </div>
                        </div>
                    </div>

                    <!-- Top Right Actions & User Profile -->
                    <div class="flex items-center gap-3">
                        <!-- Notification Bell with real alert counter -->
                        <div class="relative">
                            <button
                                type="button"
                                @click="showNotifications = !showNotifications"
                                aria-label="Notifications"
                                class="relative w-11 h-11 bg-white rounded-full flex items-center justify-center border border-card-border shadow-pill hover:bg-neutral-50 transition-colors cursor-pointer"
                            >
                                <svg class="w-5 h-5 text-neutral-800" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                                    <path d="M15 17h5l-1.405-1.405A2.032 2.032 0 0118 14.158V11a6.002 6.002 0 00-4-5.659V5a2 2 0 10-4 0v.341C7.67 6.165 6 8.388 6 11v3.159c0 .538-.214 1.055-.595 1.436L4 17h5m6 0v1a3 3 0 11-6 0v-1m6 0H9" stroke-linecap="round" stroke-linejoin="round"></path>
                                </svg>
                                <!-- Red Notification Dot -->
                                <span v-if="props.alerts.length" class="absolute top-2.5 right-2.5 w-2.5 h-2.5 bg-rose-500 rounded-full ring-2 ring-white animate-pulse"></span>
                            </button>

                            <!-- Notifications Dropdown Box -->
                            <div
                                v-if="showNotifications"
                                class="absolute right-0 mt-2 w-80 bg-white rounded-3xl border border-card-border shadow-2xl p-4 z-40"
                            >
                                <div class="flex items-center justify-between pb-3 border-b border-neutral-100">
                                    <span class="text-xs font-black text-deep-black uppercase tracking-wider">Pemberitahuan Sistem</span>
                                    <span class="text-[10px] font-bold px-2 py-0.5 rounded-full bg-rose-100 text-rose-700">
                                        {{ props.alerts.length }} Aktif
                                    </span>
                                </div>
                                <div class="space-y-3 mt-3 max-h-72 overflow-y-auto">
                                    <div
                                        v-for="(alert, i) in props.alerts"
                                        :key="i"
                                        class="p-2.5 rounded-2xl bg-[#fafbf7] border border-[#e5e9db] text-xs"
                                    >
                                        <div class="font-bold text-deep-black flex items-center gap-1.5">
                                            <span class="w-2 h-2 rounded-full" :class="alert.type === 'risk' ? 'bg-rose-500' : 'bg-neon-lime ring-1 ring-black/40'"></span>
                                            {{ alert.title }}
                                        </div>
                                        <p class="text-[11px] text-neutral-600 mt-1 leading-relaxed">{{ alert.message }}</p>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Export / Archive Pill -->
                        <button
                            type="button"
                            @click="handleExport"
                            aria-label="Archive or Export"
                            class="w-11 h-11 bg-white rounded-full flex items-center justify-center border border-card-border shadow-pill hover:bg-neutral-50 transition-colors cursor-pointer"
                            title="Export Ledger (CSV)"
                        >
                            <svg class="w-5 h-5 text-neutral-800" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                                <path d="M16 11V7a4 4 0 00-8 0v4M5 9h14l1 12H4L5 9z" stroke-linecap="round" stroke-linejoin="round"></path>
                            </svg>
                        </button>

                        <!-- Profile Badge -->
                        <div class="flex items-center gap-3 bg-white pl-4 pr-1.5 py-1.5 rounded-full border border-card-border shadow-pill">
                            <div class="flex flex-col text-right">
                                <span class="text-sm font-bold text-deep-black tracking-tight leading-none">Joseph Mitchell</span>
                                <span class="text-[10px] text-neutral-400 font-semibold mt-0.5">Owner · Studio Primary</span>
                            </div>
                            <div class="w-8 h-8 rounded-full overflow-hidden bg-neutral-200 border border-neutral-300">
                                <img
                                    alt="Joseph Mitchell portrait"
                                    class="w-full h-full object-cover"
                                    src="https://lh3.googleusercontent.com/aida-public/AB6AXuD5CLCKipbjABn6ktHJtUrRbtj7ntSuESkSte6uGk5a-PTi_cr789bpEE_XbSRfPlxZSRSLwegUzq0lxCrIMZyfYPjtNHpXLGKi3mpHJc-IENACkX4rH86uR64R-gr4Eui9BQNpar6N14WokQUAT83ljmCULu3Wk_iAgZXo8dwlj2PUQJeO6OVY8XUvmxavATMnlqi23jdbZUKUFXiIkqUCJYp5vs9MOomWo6xw7Vm4w-ElSTYKaAoU"
                                    onerror="this.src='https://ui-avatars.com/api/?name=Joseph+Mitchell&background=0e0f0c&color=c6ef4e'"
                                />
                            </div>
                        </div>
                    </div>
                </header>
                <!-- END: TopBar -->

                <!-- BEGIN: ThreeColumnGrid from Stitch Design -->
                <div class="grid grid-cols-1 lg:grid-cols-12 gap-5 items-stretch">
                    <!-- ============================================== -->
                    <!-- COLUMN 1: TRANSACTIONS & UNIVERSAL PLATINA CARD -->
                    <!-- ============================================== -->
                    <div class="lg:col-span-4 flex flex-col gap-5">
                        <!-- Recent Transactions Card -->
                        <div class="bg-white rounded-3xl p-6 border border-card-border shadow-soft flex-1 flex flex-col justify-between">
                            <div>
                                <!-- Header -->
                                <div class="flex items-center justify-between mb-5">
                                    <h2 class="text-lg font-bold text-deep-black">Recent transactions</h2>
                                    <button
                                        type="button"
                                        @click="showTransactionsModal = true"
                                        class="inline-flex items-center gap-1.5 bg-deep-black hover:bg-neutral-800 text-white text-xs font-semibold px-4 py-2 rounded-full transition-colors cursor-pointer"
                                    >
                                        <span>View all</span>
                                        <svg class="w-3 h-3" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24">
                                            <path d="M9 5l7 7-7 7" stroke-linecap="round" stroke-linejoin="round"></path>
                                        </svg>
                                    </button>
                                </div>

                                <!-- Transaction Item List -->
                                <div class="space-y-4">
                                    <div
                                        v-for="(tx, idx) in props.recentTransactions.slice(0, 5)"
                                        :key="tx.id"
                                        class="flex items-center justify-between py-1 hover:bg-neutral-50/70 rounded-xl px-1 transition-colors cursor-pointer group"
                                        @click="showTransactionsModal = true"
                                    >
                                        <div class="flex items-center gap-3.5">
                                            <!-- Dynamically Styled Brand / Type Icons -->
                                            <div
                                                :class="[
                                                    'w-10 h-10 rounded-2xl flex items-center justify-center font-bold text-sm flex-shrink-0',
                                                    idx === 0 ? 'bg-[#0079C1]/10 text-[#0079C1]' : (idx === 1 ? 'bg-[#9146FF]/10 text-[#9146FF]' : (idx === 2 ? 'bg-[#FF5A5F]/10 text-[#FF5A5F]' : (idx === 3 ? 'bg-[#EA4C89]/10 text-[#EA4C89]' : 'bg-[#c6ef4e]/20 text-deep-black')))
                                                ]"
                                            >
                                                <!-- Icon 1: Paypal/QRIS -->
                                                <svg v-if="idx === 0" class="w-5 h-5 fill-current" viewBox="0 0 24 24">
                                                    <path d="M7.076 21.337H2.47a.641.641 0 0 1-.633-.74L4.944 3.72a.78.78 0 0 1 .77-.655h6.637c3.27 0 5.602 1.34 5.922 4.09.208 1.785-.436 3.328-1.787 4.254-1.285.882-3.13 1.134-5.074 1.134h-2.12a.64.64 0 0 0-.632.535l-.973 5.485-.24 1.353a.64.64 0 0 1-.421.421z"></path>
                                                </svg>
                                                <!-- Icon 2: Twitch/Digital -->
                                                <svg v-else-if="idx === 1" class="w-5 h-5 fill-current" viewBox="0 0 24 24">
                                                    <path d="M2.149 0l-1.612 4.119v16.8h5.275V24h3.224l3.224-3.081h3.974L23.463 13.7V0H2.149zm19.467 12.693l-3.224 3.081H13.67l-3.224 3.082v-3.082H5.812V1.847h15.804v10.846zM17.15 4.922h-1.846v5.539H17.15V4.922zm-4.921 0h-1.846v5.539h1.846V4.922z"></path>
                                                </svg>
                                                <!-- Icon 3: Airbnb/F&B -->
                                                <svg v-else-if="idx === 2" class="w-5 h-5 fill-current" viewBox="0 0 24 24">
                                                    <path d="M12 0c-4.107 0-6.979 2.943-7.002 6.442-.016 2.457 1.34 5.253 3.655 8.948 1.482 2.365 3.09 4.606 3.347 4.965.257-.359 1.865-2.6 3.347-4.965 2.315-3.695 3.671-6.491 3.655-8.948C18.979 2.943 16.107 0 12 0zm0 15.656c-1.88 0-3.404-1.524-3.404-3.404 0-1.88 1.524-3.404 3.404-3.404s3.404 1.524 3.404 3.404c0 1.88-1.524 3.404-3.404 3.404z"></path>
                                                </svg>
                                                <!-- Icon 4: Dribbble -->
                                                <svg v-else-if="idx === 3" class="w-5 h-5 fill-current" viewBox="0 0 24 24">
                                                    <path d="M12 0C5.373 0 0 5.373 0 12s5.373 12 12 12 12-5.373 12-12S18.627 0 12 0zm9.849 10.741c-.424-.047-2.318-.216-4.576.549-.24-.543-.497-1.096-.77-1.656 3.238-1.391 4.542-3.216 4.671-3.404 1.085 1.341 1.727 3.033 1.727 4.876 0 .285-.018.567-.052.844v-.209zm-2.585-5.313c-.159.208-1.408 1.782-4.488 3.08-1.432-2.617-2.999-4.839-3.218-5.148 1.458-.466 3.018-.722 4.642-.722 1.139 0 2.228.188 3.245.529l-.181.261zM9.418 3.864c.209.289 1.758 2.476 3.21 5.084-3.923 1.201-7.859.988-8.225.966.862-2.616 2.766-4.757 5.23-5.91l.215-.14zM2.083 12.015c0-.063.004-.125.006-.188.354.024 4.887.288 9.176-1.091.246.505.477.999.692 1.481-3.666 1.15-7.393 4.225-7.659 4.453-.787-1.332-1.215-2.884-1.215-4.655zm4.019 6.271c.208-.184 3.427-2.923 7.039-4.148 1.173 3.076 1.704 5.922 1.83 6.643-1.01.442-2.124.686-3.292.686-2.093 0-4.007-.779-5.467-2.071l-.11-.11zm10.748.887c-.126-.649-.619-3.321-1.745-6.302 2.052-.779 3.862-.647 4.229-.616-.245 2.659-1.572 4.981-3.535 6.452l-.11-.072z"></path>
                                                </svg>
                                                <!-- Icon 5: Coffee/Optima Core -->
                                                <svg v-else class="w-5 h-5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                                                    <path d="M12 8v13m0-13V6a2 2 0 112 2h-2zm0 0V5.5A2.5 2.5 0 109.5 8H12zm-7 4h14M5 12a2 2 0 01-2-2V6a2 2 0 012-2h14a2 2 0 012 2v4a2 2 0 01-2 2M5 12v7a2 2 0 002 2h10a2 2 0 002-2v-7"></path>
                                                </svg>
                                            </div>

                                            <div>
                                                <h4 class="text-sm font-bold text-deep-black flex items-center gap-1.5">
                                                    <span>{{ tx.sales_person }}</span>
                                                    <span v-if="tx.discount_percent > 0" class="text-[10px] font-mono px-1.5 py-0.2 rounded bg-amber-100 text-amber-800">
                                                        -{{ tx.discount_percent }}%
                                                    </span>
                                                </h4>
                                                <p class="text-xs text-neutral-400 font-medium truncate max-w-[150px] md:max-w-[180px]">
                                                    {{ tx.date }}
                                                </p>
                                            </div>
                                        </div>

                                        <div class="text-right">
                                            <span class="text-sm font-bold text-deep-black tracking-tight block">
                                                - {{ formatRupiah(tx.net_amount) }}
                                            </span>
                                            <span class="text-[10px] font-mono text-neutral-400">
                                                {{ tx.invoice_number }}
                                            </span>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Bottom Card: Stacked / Layered Credit Card Container -->
                        <div class="relative pt-6">
                            <!-- Background Peek Card (Platina) -->
                            <div class="absolute top-0 left-4 right-4 bg-[#e2e5d5] rounded-3xl h-14 px-6 pt-2.5 flex items-center justify-between text-xs font-semibold text-neutral-600 border border-neutral-300/60 shadow-sm z-0">
                                <span class="font-bold text-neutral-700">Platina Laba</span>
                                <span class="font-bold text-neutral-700">{{ formatRupiah(props.kpis.grossProfit) }}</span>
                            </div>

                            <!-- Foreground Card: Neon Lime Universal Card -->
                            <div
                                @click="showCardBalanceToggle = !showCardBalanceToggle"
                                class="relative bg-neon-lime rounded-3xl p-6 shadow-xl border border-[#b2d941] z-10 cursor-pointer hover:shadow-2xl transition-all duration-300 transform hover:-translate-y-0.5"
                                title="Klik untuk beralih antara Omzet Bersih dan Omzet Kotor"
                            >
                                <div class="flex justify-between items-start">
                                    <div>
                                        <div class="flex items-center gap-2">
                                            <span class="text-xs font-bold uppercase tracking-wider text-black/70">
                                                {{ displayedCardBalance.title }}
                                            </span>
                                            <span class="text-[9px] font-bold px-2 py-0.5 rounded-full bg-black/10 text-deep-black">
                                                Tap Switch
                                            </span>
                                        </div>
                                        <div class="text-3xl font-extrabold tracking-tight text-deep-black mt-2 tabular-nums">
                                            {{ displayedCardBalance.formatted }}
                                        </div>
                                    </div>

                                    <!-- EMV Chip & Contactless Icon -->
                                    <div class="flex items-center gap-2">
                                        <div class="w-10 h-7 rounded-md bg-[#b4d845] border border-black/15 flex flex-col justify-center px-1.5 gap-0.5 shadow-inner">
                                            <div class="h-0.5 bg-black/30 rounded"></div>
                                            <div class="h-0.5 bg-black/30 rounded"></div>
                                            <div class="h-0.5 bg-black/30 rounded"></div>
                                        </div>
                                        <svg class="w-6 h-6 text-black/70 rotate-90" fill="none" stroke="currentColor" stroke-width="2.2" viewBox="0 0 24 24">
                                            <path d="M13 10V3L4 14h7v7l9-11h-7z" stroke-linecap="round" stroke-linejoin="round"></path>
                                        </svg>
                                    </div>
                                </div>

                                <!-- Card Number Preview -->
                                <div class="mt-6 flex items-center justify-between text-xs font-bold text-black/60 tracking-widest">
                                    <span>•••• 4829</span>
                                    <span class="text-[11px] font-extrabold uppercase tracking-normal">
                                        {{ displayedCardBalance.subLabel }}
                                    </span>
                                </div>
                            </div>
                        </div>
                    </div>
                    <!-- END COLUMN 1 -->

                    <!-- ============================================== -->
                    <!-- COLUMN 2: RADAR CHART & SALES BUDGET TREND    -->
                    <!-- ============================================== -->
                    <div class="lg:col-span-5 flex flex-col gap-5">
                        <!-- Spending Distribution Card (Radar Chart) -->
                        <div class="bg-white rounded-3xl p-6 border border-card-border shadow-soft flex flex-col">
                            <!-- Header with Legend -->
                            <div class="flex items-center justify-between mb-2">
                                <div>
                                    <h2 class="text-lg font-bold text-deep-black">Spending</h2>
                                    <p class="text-[11px] text-neutral-400 font-medium">Distribusi alokasi beban usaha & HPP</p>
                                </div>
                                <div class="flex items-center gap-3.5 text-xs font-semibold">
                                    <span class="inline-flex items-center gap-1.5 text-neutral-800">
                                        <span class="w-2.5 h-2.5 rounded-full bg-neon-lime border border-[#a2c833]"></span>
                                        This month
                                    </span>
                                    <span class="inline-flex items-center gap-1.5 text-neutral-400">
                                        <span class="w-2.5 h-2.5 rounded-full border border-neutral-400"></span>
                                        Average
                                    </span>
                                </div>
                            </div>

                            <!-- Radar / Spider Web SVG Chart -->
                            <div class="relative w-full aspect-square max-h-[300px] flex items-center justify-center my-auto py-2">
                                <svg class="w-full h-full overflow-visible" viewBox="0 0 400 360">
                                    <!-- Outer Polygonal Grid Rings (Hexagon) -->
                                    <!-- Level 3 (Outer) -->
                                    <polygon fill="none" points="200,30 335,108 335,252 200,330 65,252 65,108" stroke="#e8ebe0" stroke-dasharray="3 3" stroke-width="1.2"></polygon>
                                    <!-- Level 2 -->
                                    <polygon fill="none" points="200,80 290,132 290,228 200,280 110,228 110,132" stroke="#edf0e6" stroke-width="1.2"></polygon>
                                    <!-- Level 1 -->
                                    <polygon fill="none" points="200,130 245,156 245,204 200,230 155,204 155,156" stroke="#f1f3eb" stroke-width="1"></polygon>

                                    <!-- Spokes / Axis Lines -->
                                    <line stroke="#eaede2" stroke-width="1.2" x1="200" x2="200" y1="180" y2="30"></line>
                                    <line stroke="#eaede2" stroke-width="1.2" x1="200" x2="335" y1="180" y2="108"></line>
                                    <line stroke="#eaede2" stroke-width="1.2" x1="200" x2="335" y1="180" y2="252"></line>
                                    <line stroke="#eaede2" stroke-width="1.2" x1="200" x2="200" y1="180" y2="330"></line>
                                    <line stroke="#eaede2" stroke-width="1.2" x1="200" x2="65" y1="180" y2="252"></line>
                                    <line stroke="#eaede2" stroke-width="1.2" x1="200" x2="65" y1="180" y2="108"></line>

                                    <!-- Category Labels -->
                                    <text fill="#525648" font-size="11" font-weight="700" text-anchor="middle" x="200" y="16">Food (HPP)</text>
                                    <text fill="#717666" font-size="11" font-weight="600" text-anchor="start" x="345" y="108">Bills & Sewa</text>
                                    <text fill="#717666" font-size="11" font-weight="600" text-anchor="start" x="345" y="260">Gaji Karyawan</text>
                                    <text fill="#717666" font-size="11" font-weight="600" text-anchor="middle" x="200" y="352">Kerugian Waste</text>
                                    <text fill="#717666" font-size="11" font-weight="600" text-anchor="end" x="50" y="260">Margin Toko</text>
                                    <text fill="#717666" font-size="11" font-weight="600" text-anchor="end" x="52" y="108">Operasional</text>

                                    <!-- Thin Black Contour Comparison Line (Average) -->
                                    <path d="M 200,60 C 260,65 310,130 318,175 C 324,210 260,285 200,288 C 160,290 120,230 100,185 C 80,140 140,55 200,60 Z" fill="none" stroke="#252720" stroke-dasharray="2 0" stroke-width="1.8"></path>

                                    <!-- Vibrant Neon Lime Polygon Contour (This Month) -->
                                    <polygon
                                        :points="radarMonthPoints"
                                        fill="#c6ef4e"
                                        fill-opacity="0.88"
                                        stroke="#aedc30"
                                        stroke-width="2.5"
                                        class="transition-all duration-500 ease-out"
                                    ></polygon>

                                    <!-- Interactive Data Point Nodes with Hover -->
                                    <g
                                        v-for="(cat, idx) in radarCategories"
                                        :key="'node-' + idx"
                                        @mouseenter="hoveredRadarIndex = idx"
                                        @mouseleave="hoveredRadarIndex = null"
                                        class="cursor-pointer"
                                    >
                                        <circle
                                            :cx="getRadarCoords(cat.percentage, idx).x"
                                            :cy="getRadarCoords(cat.percentage, idx).y"
                                            fill="#ffffff"
                                            :r="hoveredRadarIndex === idx ? 7 : 4.5"
                                            stroke="#1f211c"
                                            stroke-width="2"
                                            class="transition-all duration-200"
                                        ></circle>
                                    </g>
                                </svg>
                            </div>

                            <!-- Active Node Breakdown Bar -->
                            <div class="mt-2 pt-3 border-t border-neutral-100 flex items-center justify-between text-xs">
                                <span class="text-neutral-500">
                                    {{ hoveredRadarIndex !== null ? radarCategories[hoveredRadarIndex].label : 'Total Pengeluaran Bulan Ini' }}
                                </span>
                                <span class="font-bold text-deep-black font-mono">
                                    {{ hoveredRadarIndex !== null ? formatRupiah(radarCategories[hoveredRadarIndex].amount) : formatRupiah(props.costDistribution.reduce((acc, c) => acc + c.amount, 0)) }}
                                </span>
                            </div>
                        </div>

                        <!-- Budget & Sales Trend Line Card -->
                        <div class="bg-white rounded-3xl p-6 border border-card-border shadow-soft flex-1 flex flex-col justify-between">
                            <!-- Header -->
                            <div class="flex items-center justify-between mb-4">
                                <div>
                                    <h2 class="text-lg font-bold text-deep-black">Budget & Sales</h2>
                                    <p class="text-[11px] text-neutral-400 font-medium">Batas kapasitas unit & proyeksi omzet</p>
                                </div>

                                <!-- Period Dropdown -->
                                <div class="relative">
                                    <button
                                        type="button"
                                        @click="showPeriodDropdown = !showPeriodDropdown"
                                        class="flex items-center gap-1.5 text-xs font-bold text-neutral-700 bg-neutral-100 hover:bg-neutral-200 px-3.5 py-1.5 rounded-full transition-colors cursor-pointer"
                                    >
                                        <span>{{ selectedPeriod }}</span>
                                        <svg class="w-3.5 h-3.5 text-neutral-600 transition-transform duration-200" :class="{ 'rotate-180': showPeriodDropdown }" fill="none" stroke="currentColor" stroke-width="2.2" viewBox="0 0 24 24">
                                            <path d="M19 9l-7 7-7-7" stroke-linecap="round" stroke-linejoin="round"></path>
                                        </svg>
                                    </button>

                                    <div
                                        v-if="showPeriodDropdown"
                                        class="absolute right-0 mt-2 w-36 bg-white rounded-2xl border border-card-border shadow-lg py-1 z-30"
                                    >
                                        <button
                                            type="button"
                                            @click="selectedPeriod = 'Day'; showPeriodDropdown = false"
                                            class="w-full text-left px-3.5 py-1.5 text-xs font-semibold text-neutral-700 hover:bg-neutral-50"
                                        >
                                            Day
                                        </button>
                                        <button
                                            type="button"
                                            @click="selectedPeriod = 'Week'; showPeriodDropdown = false"
                                            class="w-full text-left px-3.5 py-1.5 text-xs font-semibold text-neutral-700 hover:bg-neutral-50"
                                        >
                                            Week
                                        </button>
                                        <button
                                            type="button"
                                            @click="selectedPeriod = 'Month'; showPeriodDropdown = false"
                                            class="w-full text-left px-3.5 py-1.5 text-xs font-bold text-deep-black bg-neutral-100/60"
                                        >
                                            Month
                                        </button>
                                    </div>
                                </div>
                            </div>

                            <!-- Bézier Line Chart with Tooltip Pill -->
                            <div class="relative w-full h-44 mt-2">
                                <!-- Y-Axis Scale Labels -->
                                <div class="absolute left-0 top-0 bottom-6 flex flex-col justify-between text-[11px] font-bold text-neutral-400">
                                    <span>{{ formatCompact(props.kpis.netRevenue * 1.5) }}</span>
                                    <span>{{ formatCompact(props.kpis.netRevenue) }}</span>
                                    <span>{{ formatCompact(props.kpis.netRevenue * 0.5) }}</span>
                                </div>

                                <!-- Main SVG Area -->
                                <div class="ml-9 h-full relative">
                                    <svg class="w-full h-full overflow-visible" preserveAspectRatio="none" viewBox="0 0 460 160">
                                        <!-- Subtle Horizontal Grid Lines -->
                                        <line stroke="#f1f3eb" stroke-dasharray="3 3" stroke-width="1.2" x1="0" x2="460" y1="15" y2="15"></line>
                                        <line stroke="#f1f3eb" stroke-dasharray="3 3" stroke-width="1.2" x1="0" x2="460" y1="75" y2="75"></line>
                                        <line stroke="#f1f3eb" stroke-width="1.2" x1="0" x2="460" y1="135" y2="135"></line>

                                        <!-- Smooth Curve Line -->
                                        <path
                                            d="M 0,145 
                                               C 25,120 40,80 65,110 
                                               C 90,140 120,150 155,125 
                                               C 185,100 205,30 235,95 
                                               C 250,130 268,140 285,55 
                                               C 298,0 315,10 330,85 
                                               C 345,150 370,145 395,95 
                                               C 420,45 440,75 460,50"
                                            fill="none"
                                            stroke="#0e0f0c"
                                            stroke-linecap="round"
                                            stroke-linejoin="round"
                                            stroke-width="2.5"
                                        ></path>

                                        <!-- Peak Indicator Point on Curve at 235,95 (with vertical stem) -->
                                        <line stroke="#000000" stroke-dasharray="2 2" stroke-width="1.5" x1="235" x2="235" y1="95" y2="140"></line>
                                        <circle cx="235" cy="95" fill="#000000" r="4.5" stroke="#c6ef4e" stroke-width="2.5"></circle>
                                    </svg>

                                    <!-- Tooltip floating pill badge matching Stitch reference -->
                                    <div class="absolute left-[47%] -top-1 -translate-x-1/2 bg-deep-black text-white text-[11px] font-bold px-3 py-1 rounded-full shadow-lg border border-neutral-700 pointer-events-none whitespace-nowrap flex items-center gap-1.5">
                                        <span class="w-1.5 h-1.5 rounded-full bg-neon-lime"></span>
                                        <span>{{ formatRupiah(props.kpis.netRevenue) }}</span>
                                    </div>
                                </div>
                            </div>

                            <!-- Subtitle Status & Capacity Ratio -->
                            <div class="mt-2 pt-3 border-t border-neutral-100 flex items-center justify-between text-xs text-neutral-500">
                                <span>Utilisasi: {{ props.capacity.soldUnits }} / {{ props.capacity.maxCapacity }} unit</span>
                                <span class="font-bold text-emerald-700 bg-emerald-50 px-2.5 py-0.5 rounded-full border border-emerald-200">
                                    {{ props.capacity.status }}
                                </span>
                            </div>
                        </div>
                    </div>
                    <!-- END COLUMN 2 -->

                    <!-- ============================================== -->
                    <!-- COLUMN 3: QUICK TRANSFER & ADVISORY BANNER    -->
                    <!-- ============================================== -->
                    <div class="lg:col-span-3 flex flex-col gap-5">
                        <!-- Quick Transfer / Simulator Action Card -->
                        <div class="bg-white rounded-3xl p-6 border border-card-border shadow-soft flex flex-col justify-between">
                            <div>
                                <div class="flex items-center justify-between mb-4">
                                    <h2 class="text-lg font-bold text-deep-black">Quick transfer</h2>
                                    <span class="text-[10px] font-mono text-neutral-400 font-bold">Simulasi Kas</span>
                                </div>

                                <!-- Beneficiary / Team Member Avatars List -->
                                <div class="flex items-center justify-between mb-5">
                                    <button
                                        v-for="rec in recipients"
                                        :key="rec.id"
                                        type="button"
                                        @click="selectedRecipient = rec.name"
                                        :class="[
                                            'w-10 h-10 rounded-full overflow-hidden border-2 shadow-sm transition-transform cursor-pointer relative',
                                            selectedRecipient === rec.name ? 'ring-2 ring-deep-black scale-110 border-neon-lime' : 'border-white ring-1 ring-neutral-200 hover:scale-105'
                                        ]"
                                        :title="`${rec.name} (${rec.role})`"
                                    >
                                        <img
                                            :alt="rec.name"
                                            class="w-full h-full object-cover"
                                            :src="rec.avatar"
                                            :onerror="`this.src='https://ui-avatars.com/api/?name=${encodeURIComponent(rec.name)}&background=0e0f0c&color=c6ef4e'`"
                                        />
                                    </button>

                                    <!-- Add New Contact Button -->
                                    <button
                                        type="button"
                                        @click="showPlaybookModal = true"
                                        class="w-10 h-10 rounded-full border-2 border-dashed border-neutral-400 hover:border-deep-black flex items-center justify-center text-neutral-600 hover:text-deep-black transition-colors cursor-pointer"
                                        title="Kelola Tim & Penerima"
                                    >
                                        <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24">
                                            <path d="M12 4v16m8-8H4" stroke-linecap="round" stroke-linejoin="round"></path>
                                        </svg>
                                    </button>
                                </div>

                                <!-- Selected Target Name Subtext -->
                                <div class="text-[11px] font-bold text-neutral-500 mb-2 flex items-center justify-between">
                                    <span>Penerima:</span>
                                    <span class="text-deep-black font-extrabold">{{ selectedRecipient }}</span>
                                </div>

                                <!-- Input Amount Field -->
                                <div class="mb-4">
                                    <div class="relative rounded-2xl bg-neutral-100/90 border border-neutral-200 flex items-center px-4 py-3">
                                        <span class="text-neutral-400 font-bold text-sm mr-2">Rp</span>
                                        <input
                                            v-model="transferAmount"
                                            class="w-full bg-transparent border-0 p-0 text-deep-black font-extrabold text-sm focus:ring-0 outline-none"
                                            placeholder="Nominal"
                                            type="text"
                                        />
                                    </div>
                                </div>
                            </div>

                            <!-- Send Action CTA Button -->
                            <button
                                type="button"
                                @click="handleQuickTransfer"
                                :disabled="isSending"
                                class="w-full bg-deep-black hover:bg-neutral-800 disabled:opacity-50 text-white font-bold py-3.5 px-6 rounded-2xl transition-all shadow-md active:scale-[0.99] flex items-center justify-center cursor-pointer gap-2"
                            >
                                <svg v-if="isSending" class="animate-spin w-4 h-4 text-neon-lime" fill="none" viewBox="0 0 24 24">
                                    <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                                    <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8v8H4z"></path>
                                </svg>
                                <span>{{ isSending ? 'Memproses...' : 'Send' }}</span>
                            </button>
                        </div>

                        <!-- Advisory Callout Card: How to reduce expenses by 25%? -->
                        <div class="bg-neon-lime rounded-3xl p-7 border border-[#b8e239] shadow-soft flex-1 flex flex-col justify-between relative overflow-hidden group">
                            <div class="z-10">
                                <h3 class="text-[28px] leading-[1.18] font-extrabold text-deep-black tracking-tight">
                                    How to<br>
                                    reduce<br>
                                    expenses<br>
                                    by 
                                    <span class="inline-block border-2 border-deep-black px-2.5 py-0.5 rounded-full font-black text-2xl tracking-tighter bg-neon-lime">
                                        25%
                                    </span>
                                    ?
                                </h3>
                            </div>

                            <!-- Bottom Mascot/Doodle Graphic Accent -->
                            <div class="relative z-10 pt-4 flex items-end justify-between">
                                <button
                                    type="button"
                                    @click="showPlaybookModal = true"
                                    class="text-xs font-bold text-black/75 underline cursor-pointer hover:text-black transition-colors"
                                >
                                    Read playbook →
                                </button>

                                <!-- Whimsical Bear / Savings Mascot Outline in Bottom Corner -->
                                <div class="w-20 h-20 text-deep-black opacity-90 transition-transform group-hover:scale-105 duration-300">
                                    <svg class="w-full h-full" fill="none" stroke="currentColor" stroke-linecap="round" stroke-linejoin="round" stroke-width="3" viewBox="0 0 100 100">
                                        <!-- Left Ear -->
                                        <circle cx="28" cy="30" r="10" stroke-width="3"></circle>
                                        <circle cx="28" cy="30" r="5" stroke-width="2.5"></circle>
                                        <!-- Right Ear -->
                                        <circle cx="72" cy="30" r="10" stroke-width="3"></circle>
                                        <circle cx="72" cy="30" r="5" stroke-width="2.5"></circle>
                                        <!-- Head -->
                                        <ellipse cx="50" cy="58" fill="#c6ef4e" rx="34" ry="30" stroke-width="3"></ellipse>
                                        <!-- Eyes -->
                                        <circle cx="39" cy="52" fill="currentColor" r="3.5"></circle>
                                        <circle cx="61" cy="52" fill="currentColor" r="3.5"></circle>
                                        <!-- Snout & Nose -->
                                        <ellipse cx="50" cy="67" rx="13" ry="10" stroke-width="2.5"></ellipse>
                                        <path d="M46 64 C48 62, 52 62, 54 64 L50 68 Z" fill="currentColor"></path>
                                        <path d="M50 68 L50 73 M46 72 Q50 76 54 72" stroke-width="2"></path>
                                    </svg>
                                </div>
                            </div>

                            <!-- Soft background accent blur -->
                            <div class="absolute -right-8 -bottom-8 w-32 h-32 bg-white/20 rounded-full blur-xl pointer-events-none"></div>
                        </div>
                    </div>
                    <!-- END COLUMN 3 -->
                </div>
                <!-- END: ThreeColumnGrid -->
            </main>
            <!-- END: DashboardContentArea -->
        </div>
        <!-- END: MainDashboardShell -->

        <!-- ======================================================== -->
        <!-- MODAL 1: VIEW ALL TRANSACTIONS (Financial Ledger)       -->
        <!-- ======================================================== -->
        <div
            v-if="showTransactionsModal"
            class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-deep-black/60 backdrop-blur-sm"
            @click.self="showTransactionsModal = false"
        >
            <div class="bg-white rounded-3xl w-full max-w-4xl max-h-[85vh] flex flex-col shadow-2xl border border-card-border overflow-hidden">
                <div class="p-6 border-b border-neutral-100 flex items-center justify-between bg-neutral-50/50">
                    <div>
                        <h3 class="text-xl font-extrabold text-deep-black">Buku Besar Transaksi & Penjualan</h3>
                        <p class="text-xs text-neutral-500 mt-0.5">Semua rekaman transaksi riil terhubung ke database GTNIC</p>
                    </div>
                    <button
                        type="button"
                        @click="showTransactionsModal = false"
                        class="w-9 h-9 rounded-full bg-neutral-200/80 hover:bg-neutral-300 flex items-center justify-center text-neutral-700 cursor-pointer transition"
                    >
                        ✕
                    </button>
                </div>

                <!-- Search Input Bar -->
                <div class="p-4 border-b border-neutral-100 flex items-center justify-between gap-4">
                    <div class="relative flex-1">
                        <input
                            v-model="txSearchQuery"
                            type="text"
                            placeholder="Cari faktur, sales, atau item..."
                            class="w-full bg-neutral-100 rounded-xl px-4 py-2.5 text-xs font-semibold border-0 focus:ring-2 focus:ring-deep-black outline-none"
                        />
                    </div>
                    <button
                        type="button"
                        @click="handleExport"
                        class="px-4 py-2.5 rounded-xl bg-deep-black text-white text-xs font-bold hover:bg-neutral-800 transition flex items-center gap-1.5 cursor-pointer"
                    >
                        <span>Export CSV</span>
                    </button>
                </div>

                <!-- Ledger Table Body -->
                <div class="p-6 overflow-y-auto flex-1">
                    <table class="w-full text-left border-collapse text-xs">
                        <thead>
                            <tr class="border-b border-neutral-200 text-[10px] font-bold uppercase tracking-wider text-neutral-400">
                                <th class="py-2.5 px-3">Faktur & Tanggal</th>
                                <th class="py-2.5 px-3">Produk / Item</th>
                                <th class="py-2.5 px-3">Sales</th>
                                <th class="py-2.5 px-3 text-center">Status Diskon</th>
                                <th class="py-2.5 px-3 text-right">Nominal Bersih</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-neutral-100">
                            <tr
                                v-for="tx in filteredTransactions"
                                :key="tx.id"
                                class="hover:bg-neutral-50 transition-colors"
                            >
                                <td class="py-3 px-3">
                                    <div class="font-mono font-bold text-deep-black">{{ tx.invoice_number }}</div>
                                    <div class="text-[10px] text-neutral-400">{{ tx.date }}</div>
                                </td>
                                <td class="py-3 px-3 max-w-[240px]">
                                    <div class="truncate text-neutral-700 font-medium" :title="tx.items">{{ tx.items }}</div>
                                </td>
                                <td class="py-3 px-3 font-bold text-deep-black">{{ tx.sales_person }}</td>
                                <td class="py-3 px-3 text-center">
                                    <span
                                        :class="[
                                            'px-2 py-0.5 rounded-full text-[10px] font-bold',
                                            tx.status === 'normal' ? 'bg-neutral-100 text-neutral-600' : (tx.status === 'safe' ? 'bg-emerald-100 text-emerald-800' : 'bg-rose-100 text-rose-700')
                                        ]"
                                    >
                                        {{ tx.discount_percent === 0 ? 'Normal (0%)' : `Diskon ${tx.discount_percent}%` }}
                                    </span>
                                </td>
                                <td class="py-3 px-3 text-right font-mono font-black text-deep-black tabular-nums">
                                    {{ formatRupiah(tx.net_amount) }}
                                </td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>

        <!-- ======================================================== -->
        <!-- MODAL 2: READ PLAYBOOK / AI ADVISORY MODAL              -->
        <!-- ======================================================== -->
        <div
            v-if="showPlaybookModal"
            class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-deep-black/60 backdrop-blur-sm"
            @click.self="showPlaybookModal = false"
        >
            <div class="bg-white rounded-3xl w-full max-w-2xl max-h-[85vh] flex flex-col shadow-2xl border border-card-border overflow-hidden">
                <div class="p-6 bg-neon-lime border-b border-[#b2d941] flex items-center justify-between">
                    <div>
                        <span class="text-[10px] font-extrabold uppercase tracking-wider bg-deep-black text-white px-2.5 py-0.5 rounded-full">
                            OptimaBiz Playbook
                        </span>
                        <h3 class="text-2xl font-black text-deep-black mt-2">Strategi Reduksi Beban 25%</h3>
                    </div>
                    <button
                        type="button"
                        @click="showPlaybookModal = false"
                        class="w-9 h-9 rounded-full bg-deep-black/10 hover:bg-deep-black/20 flex items-center justify-center text-deep-black cursor-pointer font-bold"
                    >
                        ✕
                    </button>
                </div>

                <div class="p-6 overflow-y-auto space-y-4 text-xs">
                    <div class="p-4 rounded-2xl bg-[#f6f7f2] border border-[#e2e6d6]">
                        <h4 class="font-extrabold text-sm text-deep-black mb-1">1. Pengendalian Kebocoran Bahan (Waste Reduction)</h4>
                        <p class="text-neutral-600 leading-relaxed">
                            Berdasarkan data saat ini, tercatat kerugian limbah sebesar <strong class="text-rose-600">{{ formatRupiah(props.kpis.wasteCost) }}</strong> ({{ props.kpis.wasteCount }} unit). Lakukan sistem FIFO (First-In-First-Out) ketat pada stok beans dan fresh milk di etalase.
                        </p>
                    </div>

                    <div class="p-4 rounded-2xl bg-[#f6f7f2] border border-[#e2e6d6]">
                        <h4 class="font-extrabold text-sm text-deep-black mb-1">2. Batas Ambang Diskon Sales (Negotiation Margin)</h4>
                        <p class="text-neutral-600 leading-relaxed">
                            Rata-rata margin keuntungan berada di level <strong class="text-emerald-700">{{ props.kpis.profitMargin }}%</strong>. Pastikan tidak ada diskon kasir melebihi 10% per transaksi untuk menjaga ketahanan cashflow operasional.
                        </p>
                    </div>

                    <div class="p-4 rounded-2xl bg-[#f6f7f2] border border-[#e2e6d6]">
                        <h4 class="font-extrabold text-sm text-deep-black mb-1">3. Utilisasi Kapasitas Toko (Cap: {{ props.capacity.maxCapacity }} Unit)</h4>
                        <p class="text-neutral-600 leading-relaxed">
                            Kapasitas saat ini baru terpakai {{ props.capacity.soldUnits }} unit ({{ props.capacity.usagePercent }}%). Tingkatkan promosi jam sepi (Happy Hours) untuk mendongkrak penjualan tanpa menambah biaya sewa tetap toko.
                        </p>
                    </div>

                    <div class="p-4 rounded-2xl bg-[#f6f7f2] border border-[#e2e6d6]">
                        <h4 class="font-extrabold text-sm text-deep-black mb-1">4. Health Score Indeks ({{ props.healthScore.score }}/100 - Grade {{ props.healthScore.grade }})</h4>
                        <p class="text-neutral-600 leading-relaxed">
                            {{ props.healthScore.status }}. Lanjutkan pelacakan pengeluaran mingguan dengan fitur Quick Transfer dan simulasi alokasi.
                        </p>
                    </div>
                </div>

                <div class="p-4 bg-neutral-50 border-t border-neutral-100 flex justify-end">
                    <button
                        type="button"
                        @click="showPlaybookModal = false"
                        class="px-5 py-2.5 rounded-full bg-deep-black text-white text-xs font-bold hover:bg-neutral-800 cursor-pointer"
                    >
                        Tutup & Terapkan
                    </button>
                </div>
            </div>
        </div>
    </div>
</template>

<style scoped>
.custom-scroll::-webkit-scrollbar {
    display: none;
}
.custom-scroll {
    -ms-overflow-style: none;
    scrollbar-width: none;
}
</style>
