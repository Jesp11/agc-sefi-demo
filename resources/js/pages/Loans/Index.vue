<script setup lang="ts">
import { ref, computed } from 'vue';
import { Head, Link, useForm } from '@inertiajs/vue3';
import { 
    Plus, Search, Download, 
    Calendar, ChevronRight, Phone, CheckCircle2,
    TrendingUp, ArrowDownCircle, ArrowUpCircle, Users, RotateCcw, Filter
} from 'lucide-vue-next';
import { dashboard as dashboardRoute } from '@/routes';
import loanRoutes from '@/routes/loans';
import paymentRoutes from '@/routes/payments';
import type { BreadcrumbItem } from '@/types';
import AppLayout from '@/layouts/AppLayout.vue';
import SearchableSelect from '@/Components/SearchableSelect.vue';
import { router } from '@inertiajs/vue3';
import { watch } from 'vue';

interface Loan {
    id: number;
    customer: { nombre: string; telefono: string };
    grupo: string | null;
    amount: number;
    interes: number;
    valor_ficha: number;
    total: number;
    pagado: number;
    pendiente: number;
    completado: boolean;
    cobrado_en_periodo: boolean;
    fecha: string;
    dia_pago: string;
    plazo: number;
    asesor: string;
}

const props = defineProps<{
    loans: Loan[];
    stats: {
        total_cartera: number;
        total_cobrado: number;
        total_pendiente: number;
        clientes_activos: number;
        meta_periodo: number;
    };
    asesores: Array<{ id_asesor: number, nombre: string }>;
    filters: { search?: string, id_asesor?: string, status?: string, from_date?: string, to_date?: string };
}>();

const breadcrumbs: BreadcrumbItem[] = [
    { title: 'Dashboard', href: dashboardRoute().url },
    { title: 'Cartera de Préstamos', href: '#' },
];

const searchQuery = ref(props.filters.search || '');
const selectedAsesor = ref(props.filters.id_asesor || '');
const selectedStatus = ref(props.filters.status || '');
const fromDate = ref(props.filters.from_date || '');
const toDate = ref(props.filters.to_date || '');

const applyFilters = () => {
    router.get('/loans', {
        search: searchQuery.value,
        id_asesor: selectedAsesor.value,
        status: selectedStatus.value,
        from_date: fromDate.value,
        to_date: toDate.value
    }, { preserveState: true, replace: true });
};

watch([selectedAsesor, selectedStatus, fromDate, toDate], () => {
    applyFilters();
});

const showFilters = ref(!!(props.filters.id_asesor || props.filters.status || props.filters.from_date || props.filters.to_date));

const clearFilters = () => {
    searchQuery.value = '';
    selectedAsesor.value = '';
    selectedStatus.value = '';
    fromDate.value = '';
    toDate.value = '';
    router.get('/loans', {}, { preserveState: true, replace: true });
};

const filteredLoans = computed(() => props.loans);

const formatCurrency = (value: number) => {
    return new Intl.NumberFormat('es-MX', {
        style: 'currency',
        currency: 'MXN',
    }).format(value);
};

const isPaymentModalOpen = ref(false);
const selectedLoanForPayment = ref<Loan | null>(null);

const paymentForm = useForm({
    id_credito: '',
    monto: '',
    fecha_pago: new Date().toISOString().split('T')[0],
});

const openPaymentModal = (loan: Loan) => {
    selectedLoanForPayment.value = loan;
    paymentForm.id_credito = loan.id.toString();
    paymentForm.monto = loan.valor_ficha.toString();
    // Default to fromDate if present, otherwise today
    paymentForm.fecha_pago = fromDate.value || new Date().toISOString().split('T')[0];
    isPaymentModalOpen.value = true;
};

const closePaymentModal = () => {
    isPaymentModalOpen.value = false;
    selectedLoanForPayment.value = null;
    paymentForm.reset();
};

const submitPayment = () => {
    paymentForm.post('/payments', {
        preserveScroll: true,
        onSuccess: () => {
            closePaymentModal();
        },
    });
};


const groupedLoans = computed(() => {
    const groups: Record<string, {
        name: string;
        loans: Loan[];
        totalFichas: number;
        collectableLoans: Loan[];
    }> = {};

    filteredLoans.value.forEach(loan => {
        const groupName = loan.grupo || 'Individuales';
        if (!groups[groupName]) {
            groups[groupName] = { name: groupName, loans: [], totalFichas: 0, collectableLoans: [] };
        }
        groups[groupName].loans.push(loan);
        
        if (!loan.completado && !loan.cobrado_en_periodo) {
            groups[groupName].collectableLoans.push(loan);
            groups[groupName].totalFichas += loan.valor_ficha;
        }
    });
    return groups;
});

const isBulkPaymentModalOpen = ref(false);
const selectedGroupForPayment = ref<any>(null);

const bulkPaymentForm = useForm({
    payments: [] as { id_credito: number, monto: number, fecha_pago: string }[],
});

const openBulkPaymentModal = (group: any) => {
    selectedGroupForPayment.value = group;
    // Default to fromDate if present, otherwise today
    const dateStr = fromDate.value || new Date().toISOString().split('T')[0];
    bulkPaymentForm.payments = group.collectableLoans.map((l: Loan) => ({
        id_credito: l.id,
        monto: l.valor_ficha,
        fecha_pago: dateStr
    }));
    isBulkPaymentModalOpen.value = true;
};

const closeBulkPaymentModal = () => {
    isBulkPaymentModalOpen.value = false;
    selectedGroupForPayment.value = null;
    bulkPaymentForm.reset();
};

const submitBulkPayment = () => {
    bulkPaymentForm.post('/payments/bulk', {
        preserveScroll: true,
        onSuccess: () => {
            closeBulkPaymentModal();
        },
    });
};

</script>

<template>
    <Head title="Cartera de Préstamos" />

    <AppLayout :breadcrumbs="breadcrumbs">
        <div class="w-full mx-auto p-8 lg:p-12">
            
            <!-- Header Minimalista -->
            <header class="flex items-center justify-between gap-8 mb-12">
                <div>
                    <h1 class="text-3xl font-bold text-slate-900 tracking-tight">Cartera de Préstamos</h1>
                    <p class="text-sm text-slate-500 font-medium mt-1">Gestión integral de créditos, ciclos y cobranza.</p>
                </div>
                
                <Link 
                    :href="loanRoutes.create().url"
                    class="px-6 py-2.5 bg-slate-900 text-white font-bold rounded-lg hover:bg-slate-800 transition-all text-xs shadow-sm active:scale-95 flex items-center gap-2"
                >
                    <Plus :size="16" />
                    Nuevo Préstamo
                </Link>
            </header>

            <!-- Statistics Cards -->
            <div class="grid grid-cols-1 md:grid-cols-3 gap-6 mb-12">
                <div class="bg-white p-6 rounded-2xl border border-slate-100 shadow-sm">
                    <div class="flex items-center justify-between mb-4">
                        <div class="p-2 bg-indigo-50 text-indigo-600 rounded-lg">
                            <TrendingUp :size="18" />
                        </div>
                        <span class="text-[10px] font-bold text-slate-400 uppercase tracking-widest">Meta de Recuperación</span>
                    </div>
                    <div class="text-2xl font-black text-indigo-600">{{ formatCurrency(stats.meta_periodo) }}</div>
                    <p class="text-[10px] text-indigo-600/60 font-medium mt-1 italic">Objetivo del periodo</p>
                </div>

                <div class="bg-white p-6 rounded-2xl border border-slate-100 shadow-sm">
                    <div class="flex items-center justify-between mb-4">
                        <div class="p-2 bg-emerald-50 text-emerald-600 rounded-lg">
                            <ArrowDownCircle :size="18" />
                        </div>
                        <span class="text-[10px] font-bold text-slate-400 uppercase tracking-widest">Total Cobrado</span>
                    </div>
                    <div class="text-2xl font-black text-emerald-600">{{ formatCurrency(stats.total_cobrado) }}</div>
                    <p class="text-[10px] text-emerald-600/60 font-medium mt-1 italic">Recuperación del periodo</p>
                </div>

                <div class="bg-white p-6 rounded-2xl border border-slate-100 shadow-sm">
                    <div class="flex items-center justify-between mb-4">
                        <div class="p-2 bg-amber-50 text-amber-600 rounded-lg">
                            <ArrowUpCircle :size="18" />
                        </div>
                        <span class="text-[10px] font-bold text-slate-400 uppercase tracking-widest">Por Cobrar</span>
                    </div>
                    <div class="text-2xl font-black text-amber-600">{{ formatCurrency(stats.total_pendiente) }}</div>
                    <p class="text-[10px] text-amber-600/60 font-medium mt-1 italic">Faltante para cumplir meta</p>
                </div>
            </div>

            <!-- Main Search & Filter Toggle -->
            <div class="flex flex-wrap items-center gap-4 mb-6">
                <div class="flex-1 min-w-[300px] relative">
                    <Search class="absolute left-4 top-1/2 -translate-y-1/2 text-slate-400" :size="18" />
                    <input 
                        v-model="searchQuery"
                        @keyup.enter="applyFilters"
                        type="text" 
                        placeholder="Buscar por cliente, teléfono o ID..."
                        class="w-full pl-12 pr-4 py-3.5 bg-white border border-slate-200 rounded-2xl outline-none focus:border-slate-900 transition-all text-sm font-bold text-slate-900 shadow-sm"
                    />
                </div>
                
                <button 
                    @click="showFilters = !showFilters"
                    class="px-6 py-3.5 rounded-2xl border transition-all flex items-center gap-2 text-sm font-bold shadow-sm active:scale-95"
                    :class="showFilters 
                        ? 'bg-slate-900 text-white border-slate-900' 
                        : 'bg-white text-slate-600 border-slate-200 hover:border-slate-400'"
                >
                    <Filter :size="18" />
                    {{ showFilters ? 'Ocultar Filtros' : 'Filtros Avanzados' }}
                    <span v-if="!showFilters && (selectedAsesor || selectedStatus || fromDate || toDate)" class="w-2 h-2 bg-emerald-500 rounded-full"></span>
                </button>

                <button 
                    @click="clearFilters"
                    class="p-3.5 bg-white border border-slate-200 text-slate-400 rounded-2xl hover:bg-red-50 hover:text-red-600 hover:border-red-200 transition-all active:scale-95 flex items-center justify-center shadow-sm"
                    title="Borrar Filtros"
                >
                    <RotateCcw :size="20" />
                </button>
            </div>

            <!-- Collapsible Advanced Filters -->
            <transition
                enter-active-class="transition duration-200 ease-out"
                enter-from-class="transform -translate-y-4 opacity-0"
                enter-to-class="transform translate-y-0 opacity-100"
                leave-active-class="transition duration-150 ease-in"
                leave-from-class="transform translate-y-0 opacity-100"
                leave-to-class="transform -translate-y-4 opacity-0"
            >
                <div v-if="showFilters" class="bg-slate-50/50 p-6 rounded-[2rem] border border-slate-100 mb-10">
                    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6">
                        <!-- Asesor -->
                        <div class="space-y-2">
                            <label class="text-[10px] font-bold text-slate-400 uppercase tracking-widest ml-1">Asesor Responsable</label>
                            <SearchableSelect 
                                v-model="selectedAsesor"
                                :options="asesores.map(a => ({ id: a.id_asesor, label: a.nombre }))"
                                placeholder="Todos los Asesores"
                                search-placeholder="Buscar asesor..."
                            />
                        </div>

                        <!-- Estatus -->
                        <div class="space-y-2">
                            <label class="text-[10px] font-bold text-slate-400 uppercase tracking-widest ml-1">Estatus del Crédito</label>
                            <SearchableSelect 
                                v-model="selectedStatus"
                                :options="[
                                    { id: 'pendiente', label: 'Pendientes' },
                                    { id: 'liquidado', label: 'Liquidados' }
                                ]"
                                placeholder="Todos los Estatus"
                            />
                        </div>

                        <!-- Fechas -->
                        <div class="space-y-2">
                            <label class="text-[10px] font-bold text-slate-400 uppercase tracking-widest ml-1">Fecha Desde</label>
                            <input 
                                v-model="fromDate"
                                type="date" 
                                class="w-full px-4 py-3 bg-white border border-slate-200 rounded-xl outline-none focus:border-slate-900 transition-all text-sm font-bold text-slate-900 shadow-sm"
                            />
                        </div>

                        <div class="space-y-2">
                            <label class="text-[10px] font-bold text-slate-400 uppercase tracking-widest ml-1">Fecha Hasta</label>
                            <input 
                                v-model="toDate"
                                type="date" 
                                class="w-full px-4 py-3 bg-white border border-slate-200 rounded-xl outline-none focus:border-slate-900 transition-all text-sm font-bold text-slate-900 shadow-sm"
                            />
                        </div>
                    </div>
                </div>
            </transition>

            <!-- Tabla de Datos -->
            <div class="bg-white border border-slate-100 rounded-xl shadow-sm overflow-x-auto min-h-[500px]">
                <table class="w-full text-left border-collapse table-auto">
                    <thead>
                        <tr class="bg-slate-50/50 border-b border-slate-100">
                            <th class="w-20 px-6 py-4 text-[10px] font-bold text-slate-400 uppercase tracking-widest">ID</th>
                            <th class="px-6 py-4 text-[10px] font-bold text-slate-400 uppercase tracking-widest">Cliente</th>
                            <th class="px-6 py-4 text-[10px] font-bold text-slate-400 uppercase tracking-widest">Grupo</th>
                            <th class="w-32 px-6 py-4 text-[10px] font-bold text-slate-400 uppercase tracking-widest">Teléfono</th>
                            <th class="w-32 px-6 py-4 text-[10px] font-bold text-slate-400 uppercase tracking-widest text-center">Día Pago</th>
                            <th class="w-32 px-6 py-4 text-[10px] font-bold text-slate-400 uppercase tracking-widest text-right">V. Ficha</th>
                            <th class="w-32 px-6 py-4 text-[10px] font-bold text-slate-400 uppercase tracking-widest text-right">Pagado</th>
                            <th class="w-32 px-6 py-4 text-[10px] font-bold text-slate-400 uppercase tracking-widest text-right">Pendiente</th>
                            <th class="px-6 py-4 text-[10px] font-bold text-slate-400 uppercase tracking-widest">Asesor</th>
                            <th class="w-32 px-6 py-4 text-[10px] font-bold text-slate-400 uppercase tracking-widest text-center">Estatus</th>
                            <th class="w-40 px-6 py-4 text-[10px] font-bold text-slate-400 uppercase tracking-widest text-right">Acciones</th>
                        </tr>
                    </thead>
                    <template v-for="(group, groupName) in groupedLoans" :key="groupName">
                        <!-- Group Header -->
                        <tbody class="bg-slate-50/80">
                            <tr>
                                <td colspan="11" class="px-6 py-4 border-b border-slate-100">
                                    <div class="flex items-center justify-between">
                                        <div class="flex items-center gap-3">
                                            <div class="w-8 h-8 rounded-full bg-indigo-100 text-indigo-600 flex items-center justify-center font-bold text-xs">
                                                {{ groupName === 'Individuales' ? 'I' : groupName.charAt(0) }}
                                            </div>
                                            <div>
                                                <h3 class="text-sm font-bold text-slate-900 uppercase tracking-tight">{{ groupName }}</h3>
                                                <p class="text-[10px] text-slate-500 font-medium">{{ group.loans.length }} créditos en este bloque</p>
                                            </div>
                                        </div>
                                        <button 
                                            v-if="groupName !== 'Individuales' && group.collectableLoans.length > 0"
                                            @click="openBulkPaymentModal(group)"
                                            class="px-5 py-2 bg-indigo-600 text-white text-xs font-bold rounded-lg hover:bg-indigo-700 transition-all shadow-sm flex items-center gap-2"
                                        >
                                            <Users :size="14" />
                                            Cobrar Grupo ({{ formatCurrency(group.totalFichas) }})
                                        </button>
                                        <span v-else-if="groupName !== 'Individuales'" class="text-[10px] font-bold text-emerald-600 uppercase tracking-widest px-3 py-1.5 bg-emerald-50 rounded-md border border-emerald-100 flex items-center gap-1">
                                            <CheckCircle2 :size="14" /> Grupo al corriente
                                        </span>
                                    </div>
                                </td>
                            </tr>
                        </tbody>
                        <!-- Group Loans -->
                        <tbody class="divide-y divide-slate-50">
                            <tr v-for="loan in group.loans" :key="loan.id" class="hover:bg-slate-50/50 transition-colors">
                                <td class="px-6 py-5">
                                    <span class="text-xs font-bold text-slate-400 font-mono">#{{ loan.id }}</span>
                                </td>
                                <td class="px-6 py-5">
                                    <div class="text-sm font-bold text-slate-900 uppercase tracking-tight">{{ loan.customer?.nombre || 'Desconocido' }}</div>
                                </td>
                                <td class="px-6 py-5">
                                    <span v-if="loan.grupo" class="inline-flex px-2 py-1 bg-indigo-50 text-indigo-600 text-[9px] font-black uppercase tracking-widest rounded-md border border-indigo-100 shadow-sm">
                                        {{ loan.grupo }}
                                    </span>
                                    <span v-else class="text-[10px] font-bold text-slate-300 uppercase tracking-widest italic">Individual</span>
                                </td>
                                <td class="px-6 py-5">
                                    <div class="text-sm font-bold text-slate-900 flex items-center gap-2">
                                        <Phone :size="12" class="text-slate-300" />
                                        {{ loan.customer?.telefono || '---' }}
                                    </div>
                                </td>
                                <td class="px-6 py-5 text-center">
                                    <div class="inline-flex px-2 py-1 bg-slate-100 text-slate-600 text-[10px] font-black uppercase tracking-widest rounded-md">
                                        {{ loan.dia_pago }}
                                    </div>
                                </td>
                                <td class="px-6 py-5 text-right">
                                    <div class="text-sm font-bold text-emerald-600">{{ formatCurrency(loan.valor_ficha) }}</div>
                                </td>
                                <td class="px-6 py-5 text-right">
                                    <div class="text-xs font-bold text-slate-500">{{ formatCurrency(loan.pagado) }}</div>
                                </td>
                                <td class="px-6 py-5 text-right">
                                    <div class="text-sm font-black text-slate-900">{{ formatCurrency(loan.pendiente) }}</div>
                                </td>
                                <td class="px-6 py-5">
                                    <div class="text-xs font-bold text-slate-500 truncate">{{ loan.asesor }}</div>
                                </td>
                                <td class="px-6 py-5 text-center">
                                    <span v-if="loan.completado" class="px-2.5 py-1 bg-emerald-50 text-emerald-600 text-[9px] font-black uppercase tracking-widest rounded-full border border-emerald-100 shadow-sm inline-flex items-center gap-1">
                                        <CheckCircle2 :size="10" />
                                        Liquidado
                                    </span>
                                    <span v-else class="px-2.5 py-1 bg-amber-50 text-amber-600 text-[9px] font-black uppercase tracking-widest rounded-full border border-amber-100 shadow-sm inline-flex items-center gap-1">
                                        <Calendar :size="10" />
                                        Pendiente
                                    </span>
                                </td>
                                <td class="px-6 py-5 text-right">
                                    <div class="flex items-center justify-end gap-2">
                                        <span v-if="loan.cobrado_en_periodo" class="text-[10px] font-bold text-emerald-600 uppercase tracking-widest px-2.5 py-1 bg-emerald-50 rounded-md border border-emerald-100 flex items-center gap-1">
                                            <CheckCircle2 :size="10" /> Cobrado
                                        </span>
                                        <button 
                                            v-else-if="!loan.completado"
                                            @click="openPaymentModal(loan)"
                                            class="px-4 py-2 bg-emerald-600 text-white text-[10px] font-bold uppercase tracking-widest rounded-lg hover:bg-emerald-700 transition-all shadow-sm"
                                        >
                                            Cobrar
                                        </button>

                                        <Link 
                                            :href="loanRoutes.show({ loan: loan.id }).url"
                                            class="px-4 py-2 bg-slate-900 text-white text-[10px] font-bold uppercase tracking-widest rounded-lg hover:bg-slate-800 transition-all shadow-sm"
                                        >
                                            Ver Detalle
                                        </Link>
                                    </div>
                                </td>
                            </tr>
                        </tbody>
                    </template>
                    <tbody v-if="filteredLoans.length === 0">
                        <tr>
                            <td colspan="11" class="py-20 text-center">
                                <p class="text-slate-400 font-bold italic text-sm tracking-wider">No se encontraron resultados.</p>
                            </td>
                        </tr>
                    </tbody>
                </table>
            </div>

            <!-- Modal Cobrar -->
            <transition
                enter-active-class="transition duration-200 ease-out"
                enter-from-class="opacity-0 scale-95"
                enter-to-class="opacity-100 scale-100"
                leave-active-class="transition duration-150 ease-in"
                leave-from-class="opacity-100 scale-100"
                leave-to-class="opacity-0 scale-95"
            >
                <div v-if="isPaymentModalOpen" class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-slate-900/50 backdrop-blur-sm">
                    <div class="bg-white rounded-3xl shadow-xl w-full max-w-md overflow-hidden">
                        <div class="p-6 border-b border-slate-100 flex items-center justify-between">
                            <h3 class="text-xl font-bold text-slate-900">Registrar Cobro</h3>
                            <button @click="closePaymentModal" class="text-slate-400 hover:text-slate-600 p-2">
                                <span class="sr-only">Cerrar</span>
                                <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path></svg>
                            </button>
                        </div>
                        <div class="p-6">
                            <div v-if="selectedLoanForPayment" class="mb-6 p-4 bg-slate-50 rounded-xl border border-slate-100">
                                <p class="text-[10px] font-bold text-slate-400 uppercase tracking-widest mb-1">Cliente</p>
                                <p class="text-sm font-bold text-slate-900 uppercase mb-3">{{ selectedLoanForPayment.customer?.nombre }}</p>
                                
                                <div class="grid grid-cols-2 gap-4">
                                    <div>
                                        <p class="text-[10px] font-bold text-slate-400 uppercase tracking-widest mb-1">Ficha Sugerida</p>
                                        <p class="text-sm font-black text-emerald-600">{{ formatCurrency(selectedLoanForPayment.valor_ficha) }}</p>
                                    </div>
                                    <div>
                                        <p class="text-[10px] font-bold text-slate-400 uppercase tracking-widest mb-1">Pendiente</p>
                                        <p class="text-sm font-black text-slate-900">{{ formatCurrency(selectedLoanForPayment.pendiente) }}</p>
                                    </div>
                                </div>
                            </div>

                            <form @submit.prevent="submitPayment" class="space-y-4">
                                <div>
                                    <label class="block text-[10px] font-bold text-slate-400 uppercase tracking-widest ml-1 mb-2">Monto a Cobrar</label>
                                    <input 
                                        type="number" 
                                        step="0.01" 
                                        v-model="paymentForm.monto"
                                        required
                                        class="w-full px-4 py-3 bg-white border border-slate-200 rounded-xl outline-none focus:border-slate-900 transition-all text-sm font-bold text-slate-900 shadow-sm"
                                    />
                                </div>
                                <div>
                                    <label class="block text-[10px] font-bold text-slate-400 uppercase tracking-widest ml-1 mb-2">Fecha de Pago</label>
                                    <input 
                                        type="date" 
                                        v-model="paymentForm.fecha_pago"
                                        required
                                        class="w-full px-4 py-3 bg-white border border-slate-200 rounded-xl outline-none focus:border-slate-900 transition-all text-sm font-bold text-slate-900 shadow-sm"
                                    />
                                </div>
                                <div class="pt-4 flex gap-3">
                                    <button 
                                        type="button" 
                                        @click="closePaymentModal"
                                        class="flex-1 px-4 py-3 bg-white border border-slate-200 text-slate-600 text-sm font-bold rounded-xl hover:bg-slate-50 transition-all"
                                    >
                                        Cancelar
                                    </button>
                                    <button 
                                        type="submit" 
                                        :disabled="paymentForm.processing"
                                        class="flex-1 px-4 py-3 bg-emerald-600 text-white text-sm font-bold rounded-xl hover:bg-emerald-700 transition-all shadow-sm disabled:opacity-50"
                                    >
                                        {{ paymentForm.processing ? 'Registrando...' : 'Confirmar Cobro' }}
                                    </button>
                                </div>
                            </form>
                        </div>
                    </div>
                </div>
            </transition>

            <!-- Modal Cobro Grupal -->
            <transition
                enter-active-class="transition duration-200 ease-out"
                enter-from-class="opacity-0 scale-95"
                enter-to-class="opacity-100 scale-100"
                leave-active-class="transition duration-150 ease-in"
                leave-from-class="opacity-100 scale-100"
                leave-to-class="opacity-0 scale-95"
            >
                <div v-if="isBulkPaymentModalOpen" class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-slate-900/50 backdrop-blur-sm">
                    <div class="bg-white rounded-3xl shadow-xl w-full max-w-md overflow-hidden">
                        <div class="p-6 border-b border-slate-100 flex items-center justify-between">
                            <h3 class="text-xl font-bold text-slate-900">Cobro Grupal</h3>
                            <button @click="closeBulkPaymentModal" class="text-slate-400 hover:text-slate-600 p-2">
                                <span class="sr-only">Cerrar</span>
                                <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path></svg>
                            </button>
                        </div>
                        <div class="p-6">
                            <div v-if="selectedGroupForPayment" class="mb-6 p-4 bg-slate-50 rounded-xl border border-slate-100">
                                <p class="text-[10px] font-bold text-slate-400 uppercase tracking-widest mb-1">Grupo a Cobrar</p>
                                <p class="text-sm font-bold text-slate-900 uppercase mb-3">{{ selectedGroupForPayment.name }}</p>
                                
                                <div class="grid grid-cols-2 gap-4">
                                    <div>
                                        <p class="text-[10px] font-bold text-slate-400 uppercase tracking-widest mb-1">Clientes</p>
                                        <p class="text-sm font-black text-slate-900">{{ selectedGroupForPayment.collectableLoans.length }} clientes</p>
                                    </div>
                                    <div>
                                        <p class="text-[10px] font-bold text-slate-400 uppercase tracking-widest mb-1">Monto Total</p>
                                        <p class="text-sm font-black text-emerald-600">{{ formatCurrency(selectedGroupForPayment.totalFichas) }}</p>
                                    </div>
                                </div>
                            </div>

                            <div class="text-sm text-slate-500 mb-6 bg-amber-50 text-amber-700 p-4 rounded-xl border border-amber-100">
                                Se registrará un pago por la <strong>ficha correspondiente</strong> a cada integrante activo del grupo.
                            </div>

                            <form @submit.prevent="submitBulkPayment" class="space-y-4">
                                <div class="pt-2 flex gap-3">
                                    <button 
                                        type="button" 
                                        @click="closeBulkPaymentModal"
                                        class="flex-1 px-4 py-3 bg-white border border-slate-200 text-slate-600 text-sm font-bold rounded-xl hover:bg-slate-50 transition-all"
                                    >
                                        Cancelar
                                    </button>
                                    <button 
                                        type="submit" 
                                        :disabled="bulkPaymentForm.processing"
                                        class="flex-1 px-4 py-3 bg-indigo-600 text-white text-sm font-bold rounded-xl hover:bg-indigo-700 transition-all shadow-sm disabled:opacity-50"
                                    >
                                        {{ bulkPaymentForm.processing ? 'Procesando...' : 'Confirmar Cobro Grupal' }}
                                    </button>
                                </div>
                            </form>
                        </div>
                    </div>
                </div>
            </transition>

        </div>
    </AppLayout>
</template>
