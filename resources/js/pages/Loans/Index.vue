<script setup lang="ts">
import { ref, computed } from 'vue';
import { Head, Link, useForm } from '@inertiajs/vue3';
import { 
    Plus, Search, Download, 
    Calendar, ChevronRight, Phone, Wallet, X, CheckCircle2,
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
    amount: number;
    interes: number;
    valor_ficha: number;
    total: number;
    pagado: number;
    completado: boolean;
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

// Payment Modal Logic
const showPaymentModal = ref(false);
const selectedLoan = ref<Loan | null>(null);
const today = new Date().toISOString().split('T')[0];

const paymentForm = useForm({
    id_credito: 0,
    monto: '',
    fecha_pago: today,
});

const openPaymentModal = (loan: Loan) => {
    selectedLoan.value = loan;
    paymentForm.id_credito = loan.id;
    paymentForm.monto = loan.valor_ficha.toString();
    showPaymentModal.value = true;
};

const submitPayment = () => {
    paymentForm.post(paymentRoutes.store().url, {
        onSuccess: () => {
            showPaymentModal.value = false;
            paymentForm.reset('monto');
        }
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
            <div class="grid grid-cols-1 md:grid-cols-2 gap-6 mb-12">
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
            <div class="bg-white border border-slate-100 rounded-xl shadow-sm overflow-x-auto min-h-[500px] pb-4">
                <table class="w-full text-left border-collapse table-auto min-w-[1300px]">
                    <thead>
                        <tr class="bg-slate-50/50 border-b border-slate-100">
                            <th class="px-6 py-4 text-[9px] font-bold text-slate-400 uppercase tracking-widest w-16">ID</th>
                            <th class="px-6 py-4 text-[9px] font-bold text-slate-400 uppercase tracking-widest w-48">Cliente</th>
                            <th class="px-6 py-4 text-[9px] font-bold text-slate-400 uppercase tracking-widest w-32">Teléfono</th>
                            <th class="px-6 py-4 text-[9px] font-bold text-slate-400 uppercase tracking-widest w-24">Día Pago</th>
                            <th class="px-6 py-4 text-[9px] font-bold text-slate-400 uppercase tracking-widest text-right w-28">V. Ficha</th>
                            <th class="px-6 py-4 text-[9px] font-bold text-slate-400 uppercase tracking-widest text-center w-16">Plazos</th>
                            <th class="px-6 py-4 text-[9px] font-bold text-slate-400 uppercase tracking-widest text-right w-28">Monto</th>
                            <th class="px-6 py-4 text-[9px] font-bold text-slate-400 uppercase tracking-widest text-right w-28">Interés</th>
                            <th class="px-6 py-4 text-[9px] font-bold text-slate-400 uppercase tracking-widest text-right w-28">Total</th>
                            <th class="px-6 py-4 text-[9px] font-bold text-slate-400 uppercase tracking-widest w-32">Asesor</th>
                            <th class="px-6 py-4 text-[9px] font-bold text-slate-400 uppercase tracking-widest text-center w-32">Estatus</th>
                            <th class="px-6 py-4 text-[9px] font-bold text-slate-400 uppercase tracking-widest text-right w-48">Acciones</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-50">
                        <tr v-for="loan in filteredLoans" :key="loan.id" class="hover:bg-slate-50/50 transition-colors">
                            <td class="px-6 py-5">
                                <span class="text-[10px] font-bold text-slate-300 font-mono">#{{ loan.id }}</span>
                            </td>
                            <td class="px-6 py-5">
                                <div class="text-[11px] font-bold text-slate-900 truncate">{{ loan.customer?.nombre || 'Desconocido' }}</div>
                            </td>
                            <td class="px-6 py-5">
                                <div class="text-[10px] font-bold text-slate-500 flex items-center gap-1.5">
                                    <Phone :size="10" class="text-slate-300" />
                                    {{ loan.customer?.telefono || '---' }}
                                </div>
                            </td>
                            <td class="px-6 py-5">
                                <div class="text-[10px] font-black text-slate-900 uppercase">{{ loan.dia_pago }}</div>
                            </td>
                            <td class="px-6 py-5 text-right">
                                <div class="text-xs font-bold text-emerald-600">{{ formatCurrency(loan.valor_ficha) }}</div>
                            </td>
                            <td class="px-6 py-5 text-center">
                                <div class="text-xs font-black text-slate-900">{{ loan.plazo }}</div>
                            </td>
                            <td class="px-6 py-5 text-right">
                                <div class="text-[11px] font-bold text-slate-500">{{ formatCurrency(loan.amount) }}</div>
                            </td>
                            <td class="px-6 py-5 text-right">
                                <div class="text-[11px] font-bold text-slate-400">{{ formatCurrency(loan.interes) }}</div>
                            </td>
                            <td class="px-6 py-5 text-right">
                                <div class="text-[11px] font-black text-slate-900">{{ formatCurrency(loan.total) }}</div>
                            </td>
                            <td class="px-6 py-5">
                                <div class="text-[10px] font-bold text-slate-400 truncate">{{ loan.asesor }}</div>
                            </td>
                            <td class="px-6 py-5 text-center">
                                <span v-if="loan.completado" class="px-2.5 py-1 bg-emerald-50 text-emerald-600 text-[8px] font-black uppercase tracking-widest rounded-full border border-emerald-100 shadow-sm inline-flex items-center gap-1">
                                    <CheckCircle2 :size="10" />
                                    Liquidado
                                </span>
                                <span v-else class="px-2.5 py-1 bg-amber-50 text-amber-600 text-[8px] font-black uppercase tracking-widest rounded-full border border-amber-100 shadow-sm inline-flex items-center gap-1">
                                    <Calendar :size="10" />
                                    Pendiente
                                </span>
                            </td>
                            <td class="px-6 py-5 text-right">
                                <div class="flex items-center justify-end gap-2">
                                    <button 
                                        v-if="!loan.completado"
                                        @click="openPaymentModal(loan)"
                                        class="px-3 py-1.5 bg-emerald-600 text-white text-[9px] font-bold uppercase tracking-widest rounded-lg hover:bg-emerald-700 transition-all shadow-sm flex items-center gap-1.5"
                                    >
                                        <Wallet :size="12" />
                                        Cobrar
                                    </button>
                                    <Link 
                                        :href="loanRoutes.show({ loan: loan.id }).url"
                                        class="px-3 py-1.5 bg-slate-900 text-white text-[9px] font-bold uppercase tracking-widest rounded-lg hover:bg-slate-800 transition-all shadow-sm"
                                    >
                                        Ver Detalle
                                    </Link>
                                </div>
                            </td>
                        </tr>
                        <tr v-if="filteredLoans.length === 0">
                            <td colspan="11" class="py-20 text-center">
                                <p class="text-slate-400 font-bold italic text-sm tracking-wider">No se encontraron resultados.</p>
                            </td>
                        </tr>
                    </tbody>
                </table>
            </div>
        </div>

        <!-- Global Payment Modal -->
        <div v-if="showPaymentModal" class="fixed inset-0 bg-slate-900/60 backdrop-blur-sm flex items-center justify-center p-4 z-50">
            <div class="bg-white w-full max-w-md rounded-2xl shadow-2xl overflow-hidden animate-in fade-in zoom-in duration-200">
                <div class="p-8">
                    <div class="flex items-center gap-4 mb-8">
                        <div class="w-12 h-12 bg-emerald-600 text-white rounded-xl flex items-center justify-center shadow-lg shadow-emerald-200">
                            <Wallet :size="24" />
                        </div>
                        <div>
                            <h2 class="text-xl font-bold text-slate-900">Registrar Cobro</h2>
                            <p class="text-xs text-slate-500 font-medium">{{ selectedLoan?.customer?.nombre }}</p>
                        </div>
                    </div>

                    <form @submit.prevent="submitPayment" class="space-y-6">
                        <div class="space-y-2">
                            <label class="text-[10px] font-bold text-slate-400 uppercase tracking-widest px-1">Monto del Abono</label>
                            <div class="relative">
                                <span class="absolute left-4 top-1/2 -translate-y-1/2 text-slate-400 font-bold">$</span>
                                <input v-model="paymentForm.monto" type="number" step="0.01" required class="w-full pl-8 pr-4 py-3 bg-slate-50 border border-slate-200 focus:border-slate-900 focus:bg-white rounded-lg outline-none transition-all text-sm font-semibold" />
                            </div>
                            <p class="text-[9px] text-slate-400 font-medium pl-1 italic">
                                * Si el monto excede la ficha, el resto se aplicará a la siguiente cuota automáticamente.
                            </p>
                        </div>

                        <div class="space-y-2">
                            <label class="text-[10px] font-bold text-slate-400 uppercase tracking-widest px-1">Fecha de Operación</label>
                            <input v-model="paymentForm.fecha_pago" type="date" required class="w-full px-4 py-3 bg-slate-50 border border-slate-200 focus:border-slate-900 focus:bg-white rounded-lg outline-none transition-all text-sm font-semibold" />
                        </div>

                        <div class="flex gap-3 pt-4">
                            <button type="button" @click="showPaymentModal = false" class="flex-1 py-3 text-xs font-bold text-slate-500 hover:text-slate-900 transition-all">
                                Cancelar
                            </button>
                            <button type="submit" :disabled="paymentForm.processing" class="flex-1 py-3 bg-slate-900 text-white text-xs font-bold rounded-xl hover:bg-slate-800 transition-all shadow-sm">
                                {{ paymentForm.processing ? 'Procesando...' : 'Registrar Abono' }}
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </AppLayout>
</template>
