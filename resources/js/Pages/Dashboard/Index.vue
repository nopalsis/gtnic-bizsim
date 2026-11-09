<script setup>
import { Head, usePage } from '@inertiajs/vue3';
import { ref, computed } from 'vue';
import SidebarNav from '@/Components/SidebarNav.vue';
import TransactionLedgerModal from '@/Components/TransactionLedgerModal.vue';
import AdminDashboard from './Partials/AdminDashboard.vue';
import AdminOperationsDashboard from './Partials/AdminOperationsDashboard.vue';
import SalesDashboard from './Partials/SalesDashboard.vue';
import CraftsmanDashboard from './Partials/CraftsmanDashboard.vue';

const props = defineProps({
    currentRole: String,
    workshopName: String,
    kpis: Object,
    capacity: Object,
    categorySales: Array,
    costDistribution: Array,
    recentTransactions: Array,
    allTransactions: Array,
    topProducts: Array,
    trendHistory: Array,
    inventoryStats: Object,
    staffPerformance: Array,
    healthScore: Object,
    alerts: Array,
    quickSimulatorBaseline: Object,
    adminOperationsData: Object,
    salesData: Object,
    craftsmanData: Object,
});

const page = usePage();
const activeRole = computed(() => {
    return page.props.auth?.user?.role || props.currentRole || 'superadmin';
});

const pageTitle = computed(() => {
    if (activeRole.value === 'superadmin') {
        return 'Super Admin · Laporan & Keuangan · OptimaBiz';
    }
    if (activeRole.value === 'admin') {
        return 'Admin Toko · Pencatatan Harian · OptimaBiz';
    }
    if (activeRole.value === 'sales') {
        return 'Sales & Kasir · Penjualan · OptimaBiz';
    }
    if (activeRole.value === 'craftsman') {
        return 'Pengrajin Sepatu · Catatan Kerja · OptimaBiz';
    }
    return 'Halaman Utama · OptimaBiz';
});

// Modal State for Super Admin Ledger
const showLedgerModal = ref(false);
</script>

<template>
    <Head :title="pageTitle" />

    <div class="min-h-screen w-full max-w-full overflow-x-hidden bg-[#eaecdf] p-2.5 md:p-5 pb-24 md:pb-5 flex flex-col md:flex-row gap-5 font-sans">
        <!-- Sidebar Navigation Rail -->
        <SidebarNav active="dashboard" />

        <!-- Role Tailored Dashboard Views -->
        <!-- 1. Executive & Financial View (Super Admin Only: Keuntungan, Omzet, Margin, HPP) -->
        <AdminDashboard
            v-if="activeRole === 'superadmin'"
            :workshop-name="props.workshopName"
            :kpis="props.kpis"
            :capacity="props.capacity"
            :category-sales="props.categorySales"
            :cost-distribution="props.costDistribution"
            :recent-transactions="props.recentTransactions"
            :top-products="props.topProducts"
            :trend-history="props.trendHistory"
            :health-score="props.healthScore"
            :alerts="props.alerts"
            :quick-simulator-baseline="props.quickSimulatorBaseline"
            @open-ledger="showLedgerModal = true"
        />

        <!-- 2. Admin Operasional View (Admin Biasa: Input Transaksi, Produk & Mutasi Stok - Tanpa Finansial) -->
        <AdminOperationsDashboard
            v-else-if="activeRole === 'admin'"
            :workshop-name="props.workshopName"
            :admin-operations-data="props.adminOperationsData"
        />

        <!-- 3. Sales Performance & Deal View (Sales / Kasir) -->
        <SalesDashboard
            v-else-if="activeRole === 'sales'"
            :workshop-name="props.workshopName"
            :sales-data="props.salesData"
        />

        <!-- 4. Workshop Operations & Task View (Craftsman / Pengrajin) -->
        <CraftsmanDashboard
            v-else-if="activeRole === 'craftsman'"
            :workshop-name="props.workshopName"
            :craftsman-data="props.craftsmanData"
        />

        <!-- Fallback to Admin Operations if undefined -->
        <AdminOperationsDashboard
            v-else
            :workshop-name="props.workshopName"
            :admin-operations-data="props.adminOperationsData"
        />

        <!-- Transaction Ledger Modal (Super Admin Only) -->
        <TransactionLedgerModal
            v-if="activeRole === 'superadmin'"
            :show="showLedgerModal"
            :transactions="props.allTransactions && props.allTransactions.length > 0 ? props.allTransactions : props.recentTransactions"
            @close="showLedgerModal = false"
        />
    </div>
</template>
