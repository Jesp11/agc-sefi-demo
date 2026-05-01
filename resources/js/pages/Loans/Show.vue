<script setup lang="ts">
import { ref, computed } from 'vue';
import { Head, useForm, Link } from '@inertiajs/vue3';
import { 
    ArrowLeft, Landmark, FileText, CheckCircle2, Clock, 
    PlusCircle, DollarSign, Calendar, TrendingUp, Info,
    Wallet, ChevronRight, X, Phone, History, BarChart3
} from 'lucide-vue-next';
import { dashboard as dashboardRoute } from '@/routes';
import loanRoutes from '@/routes/loans';
import paymentRoutes from '@/routes/payments';
import AppLayout from '@/layouts/AppLayout.vue';
import type { BreadcrumbItem } from '@/types';

const props = defineProps<{
    loan: {
        id_credito: number;
        cliente: { nombre: string; telefono: string };
        monto_otorgado: number;
        interes: number;
        plazo: number;
        fecha: string;
        valor_ficha: number;
        total: number;
        promissory_note_folio: string;
        status: string;
        pagos: Array<{
            id_pago: number;
            monto: number;
            fecha: string;
        }>;
        asesor?: { nombre: string };
    };
    total_pagado: number;
    ganancia: number;
}>();

const showPaymentModal = ref(false);
const showHistoryModal = ref(false);

const today = new Date().toISOString().split('T')[0];

const paymentForm = useForm({
    id_credito: props.loan.id_credito,
    monto: '',
    fecha_pago: today,
});

const openPaymentModal = (amount: string = '') => {
    paymentForm.monto = amount;
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

const formatCurrency = (value: number) => {
    return new Intl.NumberFormat('es-MX', {
        style: 'currency',
        currency: 'MXN',
    }).format(value);
};

// Generate amortization table with partial payment logic
const installments = computed(() => {
    const table = [];
    const startDate = new Date(props.loan.fecha + 'T12:00:00');
    let remainingGlobalPayments = props.total_pagado;

    for (let i = 1; i <= props.loan.plazo; i++) {
        const dueDate = new Date(startDate);
        dueDate.setDate(dueDate.getDate() + (i * 7)); // Weekly
        
        const installmentAmount = props.loan.valor_ficha;
        
        let paidOnThisInstallment = 0;
        if (remainingGlobalPayments >= installmentAmount) {
            paidOnThisInstallment = installmentAmount;
            remainingGlobalPayments -= installmentAmount;
        } else if (remainingGlobalPayments > 0) {
            paidOnThisInstallment = remainingGlobalPayments;
            remainingGlobalPayments = 0;
        }

        const pendingAmount = installmentAmount - paidOnThisInstallment;
        
        let status = 'pending';
        if (pendingAmount === 0) status = 'paid';
        else if (paidOnThisInstallment > 0) status = 'partial';
        
        table.push({
            number: i,
            date: dueDate.toLocaleDateString('es-MX', { day: '2-digit', month: '2-digit', year: 'numeric' }),
            amount: installmentAmount,
            paid: paidOnThisInstallment,
            pending: pendingAmount,
            status: status
        });
    }
    return table;
});

const progressPercent = Math.min(100, Math.round((props.total_pagado / props.loan.total) * 100));

const breadcrumbs: BreadcrumbItem[] = [
    { title: 'Dashboard', href: dashboardRoute().url },
    { title: 'Cartera', href: loanRoutes.index().url },
    { title: `Crédito #${props.loan.id_credito}`, href: '#' },
];
</script>

<template>
    <Head :title="`Crédito #${loan.id_credito}`" />

    <AppLayout :breadcrumbs="breadcrumbs">
        <div class="max-w-6xl mx-auto p-8 lg:p-12 bg-white min-h-screen shadow-sm border-x border-slate-100">
            
            <!-- Professional Header -->
            <header class="flex flex-col md:flex-row md:items-end justify-between gap-6 mb-16 pb-8 border-b border-slate-200">
                <div class="flex items-start gap-6">
                    <Link 
                        :href="loanRoutes.index().url" 
                        class="mt-2 text-slate-400 hover:text-slate-900 transition-colors"
                    >
                        <ArrowLeft :size="24" />
                    </Link>
                    <div>
                        <div class="flex items-center gap-3 mb-1">
                            <p class="text-[10px] font-bold uppercase tracking-widest text-slate-400">Expediente de Crédito #{{ loan.id_credito }}</p>
                            <span class="text-[9px] font-bold uppercase tracking-widest px-2 py-0.5 rounded border bg-emerald-50 text-emerald-600 border-emerald-100">
                                {{ progressPercent === 100 ? 'Liquidado' : 'En Curso' }}
                            </span>
                        </div>
                        <h1 class="text-4xl font-bold text-slate-900 tracking-tight">{{ loan.cliente.nombre }}</h1>
                        <div class="flex items-center gap-4 mt-2">
                            <p class="text-slate-500 font-medium text-sm flex items-center gap-1">
                                <Phone :size="14" class="text-slate-300" />
                                {{ loan.cliente.telefono || 'Sin teléfono' }}
                            </p>
                            <span class="w-1 h-1 bg-slate-300 rounded-full"></span>
                            <p class="text-slate-500 font-medium text-sm">Asesor: {{ loan.asesor?.nombre || 'General' }}</p>
                        </div>
                    </div>
                </div>
                
                <div class="flex flex-wrap gap-3">
                    <a 
                        :href="loanRoutes.exportPdf({ loan: loan.id_credito }).url + '?type=client'" 
                        class="px-5 py-2.5 bg-white text-slate-700 border border-slate-200 font-bold rounded-lg hover:bg-slate-50 transition-all text-xs flex items-center gap-2"
                    >
                        <Download :size="16" class="text-slate-400" />
                        PDF Cliente
                    </a>
                    <a 
                        :href="loanRoutes.exportPdf({ loan: loan.id_credito }).url + '?type=admin'" 
                        class="px-5 py-2.5 bg-white text-slate-700 border border-slate-200 font-bold rounded-lg hover:bg-slate-50 transition-all text-xs flex items-center gap-2"
                    >
                        <FileText :size="16" class="text-slate-400" />
                        PDF Admin
                    </a>
                    <button 
                        @click="showHistoryModal = true"
                        class="px-5 py-2.5 bg-white text-slate-700 border border-slate-200 font-bold rounded-lg hover:bg-slate-50 transition-all text-xs flex items-center gap-2"
                    >
                        <Clock :size="16" class="text-slate-400" />
                        Ver Pagos
                    </button>
                    
                    <button 
                        v-if="progressPercent < 100"
                        @click="openPaymentModal()"
                        class="px-6 py-2.5 bg-slate-900 text-white font-bold rounded-lg hover:bg-slate-800 transition-all text-xs shadow-sm active:scale-95 flex items-center gap-2"
                    >
                        <PlusCircle :size="16" />
                        Registrar Abono
                    </button>
                </div>
            </header>

            <div class="grid grid-cols-1 gap-16">
                
                <!-- KPI Section: Los datos solicitados -->
                <section>
                    <div class="flex items-center gap-2 mb-8 border-b border-slate-100 pb-2">
                        <BarChart3 :size="18" class="text-slate-400" />
                        <h2 class="text-xs font-bold text-slate-400 uppercase tracking-widest">Indicadores de Desempeño</h2>
                    </div>

                    <div class="grid grid-cols-1 md:grid-cols-4 gap-6">
                        <div class="p-6 bg-slate-50 rounded-2xl border border-slate-100 group hover:border-slate-300 transition-all text-center md:text-left">
                            <p class="text-[10px] font-bold text-slate-400 uppercase tracking-widest mb-1">Capital Desembolsado</p>
                            <p class="text-2xl font-black text-slate-900">{{ formatCurrency(loan.monto_otorgado) }}</p>
                            <p class="text-[9px] font-bold text-slate-400 mt-2 uppercase">Monto entregado</p>
                        </div>

                        <div class="p-6 bg-slate-50 rounded-2xl border border-slate-100 group hover:border-emerald-200 transition-all text-center md:text-left">
                            <p class="text-[10px] font-bold text-slate-400 uppercase tracking-widest mb-1">Total Recuperado</p>
                            <p class="text-2xl font-black text-emerald-600">{{ formatCurrency(total_pagado) }}</p>
                            <p class="text-[9px] font-bold text-emerald-600/60 mt-2 uppercase">{{ progressPercent }}% cobrado</p>
                        </div>

                        <div class="p-6 bg-slate-900 rounded-2xl shadow-lg text-center md:text-left">
                            <p class="text-[10px] font-bold text-white/40 uppercase tracking-widest mb-1">Utilidad Bruta</p>
                            <p class="text-2xl font-black text-white">{{ formatCurrency(ganancia) }}</p>
                            <p class="text-[9px] font-bold text-white/40 mt-2 uppercase">Ganancia generada</p>
                        </div>

                        <div class="p-6 bg-rose-50 rounded-2xl border border-rose-100 text-center md:text-left">
                            <p class="text-[10px] font-bold text-rose-400 uppercase tracking-widest mb-1">Saldo Pendiente</p>
                            <p class="text-2xl font-black text-rose-600">{{ formatCurrency(loan.total - total_pagado) }}</p>
                            <p class="text-[9px] font-bold text-rose-400 mt-2 uppercase">Por recuperar</p>
                        </div>
                    </div>
                </section>

                <!-- Section 2: Tabla de Amortización -->
                <section>
                    <div class="flex items-center gap-2 mb-8 border-b border-slate-100 pb-2">
                        <Landmark :size="18" class="text-slate-400" />
                        <h2 class="text-xs font-bold text-slate-400 uppercase tracking-widest">Tabla de Amortización Proyectada</h2>
                    </div>

                    <div class="bg-white border border-slate-100 rounded-xl shadow-sm overflow-hidden">
                        <table class="w-full text-left border-collapse">
                            <thead>
                                <tr class="bg-slate-50/50">
                                    <th class="w-20 px-6 py-4 text-[10px] font-bold text-slate-400 uppercase tracking-widest">Cuota</th>
                                    <th class="px-6 py-4 text-[10px] font-bold text-slate-400 uppercase tracking-widest">Fecha Sugerida</th>
                                    <th class="px-6 py-4 text-[10px] font-bold text-slate-400 uppercase tracking-widest text-right">Monto Ficha</th>
                                    <th class="px-6 py-4 text-[10px] font-bold text-slate-400 uppercase tracking-widest text-right text-emerald-600">Abonado</th>
                                    <th class="px-6 py-4 text-[10px] font-bold text-slate-400 uppercase tracking-widest text-right text-rose-500">Faltante</th>
                                    <th class="px-6 py-4 text-[10px] font-bold text-slate-400 uppercase tracking-widest text-center">Estatus</th>
                                    <th class="w-32 px-6 py-4 text-[10px] font-bold text-slate-400 uppercase tracking-widest text-right">Acción</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-slate-50">
                                <tr v-for="inst in installments" :key="inst.number" class="hover:bg-slate-50/50 transition-colors">
                                    <td class="px-6 py-4">
                                        <span class="text-xs font-bold text-slate-400 font-mono">#{{ inst.number }}</span>
                                    </td>
                                    <td class="px-6 py-4">
                                        <div class="text-sm font-semibold text-slate-900">{{ inst.date }}</div>
                                    </td>
                                    <td class="px-6 py-4 text-right">
                                        <div class="text-sm font-bold text-slate-900">{{ formatCurrency(inst.amount) }}</div>
                                    </td>
                                    <td class="px-6 py-4 text-right">
                                        <div class="text-sm font-bold text-emerald-600">{{ formatCurrency(inst.paid) }}</div>
                                    </td>
                                    <td class="px-6 py-4 text-right">
                                        <div :class="['text-sm font-bold', inst.pending > 0 ? 'text-rose-500' : 'text-slate-300']">
                                            {{ formatCurrency(inst.pending) }}
                                        </div>
                                    </td>
                                    <td class="px-6 py-4 text-center">
                                        <span v-if="inst.status === 'paid'" class="text-[9px] font-bold uppercase tracking-widest px-2 py-0.5 rounded border bg-emerald-50 text-emerald-600 border-emerald-100">
                                            Pagado
                                        </span>
                                        <span v-else-if="inst.status === 'partial'" class="text-[9px] font-bold uppercase tracking-widest px-2 py-0.5 rounded border bg-amber-50 text-amber-600 border-amber-100">
                                            Parcial
                                        </span>
                                        <span v-else class="text-[9px] font-bold uppercase tracking-widest px-2 py-0.5 rounded border bg-slate-100 text-slate-500 border-slate-200">
                                            Pendiente
                                        </span>
                                    </td>
                                    <td class="px-6 py-4 text-right">
                                        <button 
                                            v-if="inst.status !== 'paid' && progressPercent < 100"
                                            @click="openPaymentModal(inst.pending.toString())"
                                            class="text-[10px] font-bold text-slate-900 hover:underline"
                                        >
                                            {{ inst.status === 'partial' ? 'Completar' : 'Cobrar' }}
                                        </button>
                                        <span v-else-if="inst.status === 'paid'" class="text-[9px] font-bold text-emerald-500 uppercase tracking-widest">
                                            Completado
                                        </span>
                                    </td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                </section>
            </div>
        </div>

        <!-- Payment Modal (Minimalist) -->
        <div v-if="showPaymentModal" class="fixed inset-0 bg-slate-900/60 backdrop-blur-sm flex items-center justify-center p-4 z-50">
            <div class="bg-white w-full max-w-md rounded-2xl shadow-2xl overflow-hidden animate-in fade-in zoom-in duration-200">
                <div class="p-8">
                    <div class="flex items-center gap-4 mb-8">
                        <div class="w-12 h-12 bg-slate-900 text-white rounded-xl flex items-center justify-center">
                            <Wallet :size="24" />
                        </div>
                        <div>
                            <h2 class="text-xl font-bold text-slate-900">Registrar Pago</h2>
                            <p class="text-xs text-slate-500 font-medium">Capture el monto recibido del cliente.</p>
                        </div>
                    </div>

                    <form @submit.prevent="submitPayment" class="space-y-6">
                        <div class="space-y-2">
                            <label class="text-[10px] font-bold text-slate-400 uppercase tracking-widest px-1">Monto a Recibir</label>
                            <div class="relative">
                                <span class="absolute left-4 top-1/2 -translate-y-1/2 text-slate-400 font-bold">$</span>
                                <input v-model="paymentForm.monto" type="number" step="0.01" required class="w-full pl-8 pr-4 py-3 bg-slate-50 border border-slate-200 focus:border-slate-900 focus:bg-white rounded-lg outline-none transition-all text-sm font-semibold" />
                            </div>
                        </div>

                        <div class="space-y-2">
                            <label class="text-[10px] font-bold text-slate-400 uppercase tracking-widest px-1">Fecha</label>
                            <input v-model="paymentForm.fecha_pago" type="date" required class="w-full px-4 py-3 bg-slate-50 border border-slate-200 focus:border-slate-900 focus:bg-white rounded-lg outline-none transition-all text-sm font-semibold" />
                        </div>

                        <div class="flex gap-3 pt-4">
                            <button type="button" @click="showPaymentModal = false" class="flex-1 py-3 text-xs font-bold text-slate-500 hover:text-slate-900 transition-all">
                                Cancelar
                            </button>
                            <button type="submit" :disabled="paymentForm.processing" class="flex-1 py-3 bg-slate-900 text-white text-xs font-bold rounded-xl hover:bg-slate-800 transition-all shadow-sm">
                                {{ paymentForm.processing ? 'Registrando...' : 'Confirmar Pago' }}
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>

        <!-- History Modal (Minimalist) -->
        <div v-if="showHistoryModal" class="fixed inset-0 bg-slate-900/60 backdrop-blur-sm flex items-center justify-center p-4 z-50">
            <div class="bg-white w-full max-w-md rounded-2xl shadow-2xl overflow-hidden flex flex-col max-h-[80vh] animate-in fade-in zoom-in duration-200">
                <div class="p-8 border-b border-slate-100 flex justify-between items-center">
                    <div class="flex items-center gap-3">
                        <History :size="20" class="text-slate-400" />
                        <h2 class="text-lg font-bold text-slate-900">Historial de Pagos</h2>
                    </div>
                    <button @click="showHistoryModal = false" class="text-slate-400 hover:text-slate-900 transition-colors">
                        <X :size="20" />
                    </button>
                </div>
                
                <div class="p-8 overflow-y-auto space-y-6">
                    <div v-for="pago in loan.pagos" :key="pago.id_pago" class="flex items-center justify-between group">
                        <div>
                            <p class="font-bold text-slate-900">{{ formatCurrency(pago.monto) }}</p>
                            <p class="text-[10px] text-slate-400 font-bold uppercase tracking-widest">{{ pago.fecha_pago }}</p>
                        </div>
                        <div class="w-8 h-8 rounded-full bg-slate-50 flex items-center justify-center text-emerald-500 opacity-0 group-hover:opacity-100 transition-all">
                            <CheckCircle2 :size="16" />
                        </div>
                    </div>
                    <div v-if="loan.pagos.length === 0" class="py-12 text-center text-slate-400 text-xs font-medium italic">
                        Sin pagos registrados.
                    </div>
                </div>

                <div class="p-8 bg-slate-50">
                    <button @click="showHistoryModal = false" class="w-full py-3 bg-slate-900 text-white text-xs font-bold rounded-xl hover:bg-slate-800 transition-all shadow-sm">
                        Cerrar
                    </button>
                </div>
            </div>
        </div>
    </AppLayout>
</template>
