<script setup lang="ts">
import { ref } from 'vue';
import { Head, useForm, Link } from '@inertiajs/vue3';
import { ArrowLeft, Landmark, FileText, CheckCircle2, Clock, PlusCircle } from 'lucide-vue-next';
import { dashboard as dashboardRoute } from '@/routes';
import loanRoutes from '@/routes/loans';
import paymentRoutes from '@/routes/payments';
import AppLayout from '@/layouts/AppLayout.vue';
import type { BreadcrumbItem } from '@/types';

const props = defineProps<{
    loan: {
        id: number;
        customer: { name: string; phone: string };
        amount: number;
        interest_rate: number;
        periodicity: string;
        num_installments: number;
        first_payment_date: string;
        promissory_note_folio: string;
        status: string;
        outstanding_balance: number;
        installments: Array<{
            id: number;
            installment_number: number;
            due_date: string;
            amount: number;
            status: string;
            paid_at: string | null;
        }>;
        payments: Array<{
            id: number;
            amount: number;
            payment_date: string;
        }>;
    }
}>();

const showPaymentModal = ref(false);
const showHistoryModal = ref(false);
const activeInstallmentNumber = ref<number | null>(null);

const today = new Date();
const localDate = today.getFullYear() + '-' + String(today.getMonth() + 1).padStart(2, '0') + '-' + String(today.getDate()).padStart(2, '0');

const paymentForm = useForm({
    loan_id: props.loan.id,
    amount: '',
    payment_date: localDate,
});

const openPaymentModal = (amount: string = '', installmentNumber: number | null = null) => {
    activeInstallmentNumber.value = installmentNumber;
    // Round to 2 decimals if amount is provided
    paymentForm.amount = amount ? Number(parseFloat(amount).toFixed(2)).toString() : '';
    showPaymentModal.value = true;
};

const submitPayment = () => {
    paymentForm.post(paymentRoutes.store().url, {
        onSuccess: () => {
            showPaymentModal.value = false;
            activeInstallmentNumber.value = null;
            paymentForm.reset('amount');
        }
    });
};

const formatCurrency = (value: number) => {
    return new Intl.NumberFormat('es-MX', {
        style: 'currency',
        currency: 'MXN',
    }).format(value);
};

const initialTotal = props.loan.amount * (1 + props.loan.interest_rate / 100);
const paidAmount = Math.max(0, initialTotal - props.loan.outstanding_balance);
const progressPercent = Math.min(100, Math.round((paidAmount / initialTotal) * 100));

const getStatusClass = (status: string) => {
    switch(status) {
        case 'paid': return 'bg-green-100 text-green-700 border-green-200';
        case 'pending': return 'bg-amber-100 text-amber-700 border-amber-200';
        case 'liquidated': return 'bg-blue-100 text-blue-700 border-blue-200';
        case 'active': return 'bg-green-100 text-green-700 border-green-200';
        default: return 'bg-slate-100 text-slate-700 border-slate-200';
    }
};

const breadcrumbs: BreadcrumbItem[] = [
    { title: 'Dashboard', href: dashboardRoute().url },
    { title: 'Préstamos', href: loanRoutes.index().url },
    { title: 'Detalle de Préstamo', href: loanRoutes.show({ loan: props.loan.id }).url },
];
</script>

<template>
    <Head :title="`Préstamo #${loan.id}`" />

    <AppLayout :breadcrumbs="breadcrumbs">
        <div class="p-8 max-w-[1600px] mx-auto pb-24">
            <header class="mb-8 flex flex-col md:flex-row md:items-end justify-between gap-4">
                <div>
                    <Link 
                        :href="loanRoutes.index().url"
                        class="inline-flex items-center text-sm font-bold text-slate-500 hover:text-slate-900 transition-colors mb-4"
                    >
                        <ArrowLeft :size="16" class="mr-2" />
                        Volver a Cartera
                    </Link>
                    <div class="flex items-center gap-3">
                        <h1 class="text-3xl font-bold text-slate-900">Crédito #{{ loan.id }}</h1>
                        <span :class="['px-3 py-1 text-xs font-bold rounded-full border', getStatusClass(loan.status)]">
                            {{ loan.status.toUpperCase() }}
                        </span>
                    </div>
                    <p class="text-slate-500 mt-1 font-medium">Cliente: {{ loan.customer.name }}</p>
                </div>

                <div class="flex flex-wrap gap-3">
                    <a 
                        v-if="loan.status === 'active'"
                        :href="loanRoutes.exportPdf({ loan: loan.id }).url"
                        target="_blank"
                        class="inline-flex items-center px-6 py-4 bg-white text-slate-900 border border-slate-200 font-bold rounded-2xl hover:bg-slate-50 transition-all shadow-sm"
                    >
                        <FileText :size="20" class="mr-2 text-rose-500" />
                        Descargar PDF
                    </a>

                    <button 
                        @click="showHistoryModal = true"
                        class="inline-flex items-center px-6 py-4 bg-white text-slate-900 border border-slate-200 font-bold rounded-2xl hover:bg-slate-50 transition-all shadow-sm"
                    >
                        <CheckCircle2 :size="20" class="mr-2 text-teal-600" />
                        Historial de Abonos
                    </button>

                    <button 
                        v-if="loan.status === 'active'"
                        @click="openPaymentModal()"
                        class="inline-flex items-center px-8 py-4 bg-teal-600 text-white font-bold rounded-2xl hover:bg-teal-700 transition-all shadow-xl shadow-teal-100"
                    >
                        <PlusCircle :size="20" class="mr-2" />
                        Registrar Abono
                    </button>
                </div>
            </header>

            <div class="grid grid-cols-1 lg:grid-cols-3 gap-8">
                <!-- Table of Amortization -->
                <div class="lg:col-span-2">
                    <div class="bg-white rounded-3xl border border-slate-100 shadow-sm overflow-hidden">
                        <div class="px-8 py-6 border-b border-slate-50 flex items-center justify-between">
                            <h2 class="text-xl font-bold text-slate-900 flex items-center gap-2">
                                <Clock :size="20" class="text-teal-600" />
                                Tabla de Amortización
                            </h2>
                        </div>
                        <div class="overflow-x-auto">
                            <table class="w-full text-left">
                                <thead class="bg-slate-50">
                                    <tr>
                                        <th class="px-8 py-4 text-xs font-bold text-slate-500 uppercase tracking-widest text-center whitespace-nowrap">No.</th>
                                        <th class="px-8 py-4 text-xs font-bold text-slate-500 uppercase tracking-widest whitespace-nowrap">Vencimiento</th>
                                        <th class="px-8 py-4 text-xs font-bold text-slate-500 uppercase tracking-widest text-right whitespace-nowrap">Monto</th>
                                        <th class="px-8 py-4 text-xs font-bold text-slate-500 uppercase tracking-widest text-center whitespace-nowrap">Estatus</th>
                                        <th class="px-8 py-4 text-xs font-bold text-slate-500 uppercase tracking-widest text-center whitespace-nowrap">Fecha Pago</th>
                                        <th class="px-8 py-4 text-xs font-bold text-slate-500 uppercase tracking-widest text-right whitespace-nowrap">Acciones</th>
                                    </tr>
                                </thead>
                                <tbody class="divide-y divide-slate-50">
                                    <tr v-for="(inst, index) in loan.installments" :key="inst.id" class="hover:bg-slate-50 transition-colors">
                                        <td class="px-8 py-5 text-center font-bold text-slate-400 font-mono whitespace-nowrap">#{{ inst.installment_number }}</td>
                                        <td class="px-8 py-5 font-medium text-slate-700 whitespace-nowrap">{{ inst.due_date }}</td>
                                        <td class="px-8 py-5 font-bold text-slate-900 text-right whitespace-nowrap">{{ formatCurrency(inst.amount) }}</td>
                                        <td class="px-8 py-5 text-center whitespace-nowrap">
                                            <span :class="['px-3 py-1 text-[10px] font-bold rounded-full border', getStatusClass(inst.status)]">
                                                {{ inst.status === 'paid' ? 'PAGADO' : 'PENDIENTE' }}
                                            </span>
                                        </td>
                                        <td class="px-8 py-5 text-center text-xs font-medium text-slate-500 whitespace-nowrap">
                                            {{ inst.paid_at || '-' }}
                                        </td>
                                        <td class="px-8 py-5 text-right whitespace-nowrap">
                                            <button 
                                                v-if="inst.status === 'pending' && (!loan.installments[index-1] || loan.installments[index-1].status === 'paid')"
                                                @click="openPaymentModal(inst.amount.toString(), inst.installment_number)"
                                                class="inline-flex items-center px-3 py-1.5 bg-teal-50 text-teal-600 text-[10px] font-bold rounded-lg hover:bg-teal-100 transition-all"
                                            >
                                                Pagar Cuota
                                            </button>
                                        </td>
                                    </tr>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>

                <!-- Summary Sidebar -->
                <div class="space-y-6">
                    <!-- Loan Info Card -->
                    <div class="bg-white p-8 rounded-3xl border border-slate-100 shadow-sm relative overflow-hidden group">
                        <div class="absolute -right-4 -top-4 w-32 h-32 bg-teal-500/5 rounded-full blur-3xl group-hover:bg-teal-500/10 transition-all"></div>
                        
                        <h3 class="text-sm font-bold text-slate-400 uppercase tracking-[0.2em] mb-8 flex items-center gap-2">
                            Detalles del Crédito
                        </h3>
                        
                        <div class="space-y-8">
                            <div>
                                <div class="flex justify-between items-end mb-3">
                                    <div>
                                        <p class="text-slate-400 text-[10px] font-bold uppercase tracking-widest mb-1">Restante por Pagar</p>
                                        <p class="text-4xl font-black text-slate-900">{{ formatCurrency(loan.outstanding_balance) }}</p>
                                    </div>
                                    <div class="text-right">
                                        <p class="text-teal-600 text-sm font-black">{{ progressPercent }}% Pagado</p>
                                    </div>
                                </div>
                                <!-- Progress Bar -->
                                <div class="w-full bg-slate-100 h-3 rounded-full overflow-hidden border border-slate-200/50">
                                    <div 
                                        class="bg-teal-500 h-full transition-all duration-1000 ease-out shadow-[0_0_15px_rgba(20,184,166,0.3)]"
                                        :style="{ width: progressPercent + '%' }"
                                    ></div>
                                </div>
                                <div class="flex justify-between mt-3">
                                    <p class="text-[10px] text-slate-400 font-bold uppercase tracking-tight">Pagado: {{ formatCurrency(paidAmount) }}</p>
                                    <p class="text-[10px] text-slate-400 font-bold uppercase tracking-tight">Total: {{ formatCurrency(initialTotal) }}</p>
                                </div>
                            </div>

                            <div class="grid grid-cols-1 gap-4">
                                <div class="flex items-center gap-4 p-5 bg-slate-50 rounded-2xl border border-slate-100/50 hover:border-teal-200/50 transition-colors">
                                    <div class="p-3 bg-white rounded-xl shadow-sm text-teal-600">
                                        <Landmark :size="20" stroke-width="2.5" />
                                    </div>
                                    <div>
                                        <p class="text-slate-400 text-[10px] font-bold uppercase tracking-wider">Monto Original</p>
                                        <p class="font-black text-slate-900">{{ formatCurrency(loan.amount) }}</p>
                                    </div>
                                </div>
                                <div class="flex items-center gap-4 p-5 bg-slate-50 rounded-2xl border border-slate-100/50 hover:border-teal-200/50 transition-colors">
                                    <div class="p-3 bg-white rounded-xl shadow-sm text-teal-600">
                                        <FileText :size="20" stroke-width="2.5" />
                                    </div>
                                    <div>
                                        <p class="text-slate-400 text-[10px] font-bold uppercase tracking-wider">Folio Pagaré</p>
                                        <p class="font-black text-slate-900">{{ loan.promissory_note_folio || 'Sin Folio' }}</p>
                                    </div>
                                </div>
                            </div>

                            <div class="pt-2 space-y-4 border-t border-slate-50">
                                <div class="flex justify-between items-center text-sm">
                                    <span class="text-slate-500 font-medium">Tasa de Interés</span>
                                    <span class="font-black text-teal-600 bg-teal-50 px-3 py-1 rounded-full text-xs">{{ loan.interest_rate }}% Global</span>
                                </div>
                                <div class="flex justify-between items-center text-sm">
                                    <span class="text-slate-500 font-medium">Periodicidad</span>
                                    <span class="font-black text-slate-900 capitalize">{{ loan.periodicity }}</span>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- History modal replaces the sidebar block -->
                </div>
            </div>

            <!-- Payment Modal -->
            <div v-if="showPaymentModal" class="fixed inset-0 bg-slate-900/40 backdrop-blur-sm flex items-center justify-center p-4 z-50">
                <div class="bg-white w-full max-w-md rounded-3xl shadow-2xl p-8 transform transition-all animate-in fade-in zoom-in duration-300">
                    <h2 class="text-2xl font-bold text-slate-900 mb-2">
                        {{ activeInstallmentNumber ? `Pagar Cuota #${activeInstallmentNumber}` : 'Registrar Abono' }}
                    </h2>
                    <p class="text-slate-500 mb-6">
                        {{ activeInstallmentNumber ? 'Confirma el pago de esta cuota específica.' : 'El monto se aplicará a las cuotas según el orden de vencimiento.' }}
                    </p>
                    
                    <form @submit.prevent="submitPayment" class="space-y-6">
                        <div>
                            <div class="flex justify-between items-center mb-2">
                                <label class="block text-sm font-bold text-slate-700">Monto del Pago ($)</label>
                                <button 
                                    v-if="!activeInstallmentNumber"
                                    type="button" 
                                    @click="paymentForm.amount = loan.outstanding_balance.toString()"
                                    class="text-xs font-bold text-teal-600 hover:text-teal-700 bg-teal-50 px-2 py-1 rounded-md transition-colors"
                                >
                                    Liquidar Saldo
                                </button>
                            </div>
                            <input 
                                v-model="paymentForm.amount"
                                type="number" 
                                step="0.01"
                                required
                                class="w-full px-4 py-3 bg-slate-50 border border-slate-200 rounded-xl focus:ring-2 focus:ring-teal-500 outline-none transition-all"
                                placeholder="0.00"
                            />
                        </div>
                        <div>
                            <label class="block text-sm font-bold text-slate-700 mb-2">Fecha de Recepción</label>
                            <input 
                                v-model="paymentForm.payment_date"
                                type="date" 
                                required
                                class="w-full px-4 py-3 bg-slate-50 border border-slate-200 rounded-xl focus:ring-2 focus:ring-teal-500 outline-none transition-all"
                            />
                        </div>
                        
                        <div class="flex gap-3 pt-4">
                            <button 
                                type="button" 
                                @click="showPaymentModal = false"
                                class="flex-1 px-4 py-3 text-slate-500 font-bold hover:bg-slate-50 rounded-xl transition-all"
                            >
                                Cancelar
                            </button>
                            <button 
                                type="submit" 
                                :disabled="paymentForm.processing"
                                class="flex-1 px-4 py-3 bg-slate-900 text-white font-bold rounded-xl hover:bg-slate-800 transition-all disabled:opacity-50"
                            >
                                {{ paymentForm.processing ? 'Registrando...' : 'Confirmar Pago' }}
                            </button>
                        </div>
                    </form>
                </div>
            </div>

            <!-- History Modal -->
            <div v-if="showHistoryModal" class="fixed inset-0 bg-slate-900/40 backdrop-blur-sm flex items-center justify-center p-4 z-50">
                <div class="bg-white w-full max-w-md rounded-3xl shadow-2xl p-8 transform transition-all animate-in fade-in zoom-in duration-300 flex flex-col max-h-[85vh]">
                    <div class="flex justify-between items-center mb-6">
                        <h2 class="text-2xl font-bold text-slate-900 flex items-center gap-2">
                            <CheckCircle2 :size="24" class="text-green-500" />
                            Historial de Abonos
                        </h2>
                        <button @click="showHistoryModal = false" class="text-slate-400 hover:text-slate-600 font-bold">
                            ✕
                        </button>
                    </div>
                    
                    <div class="space-y-4 overflow-y-auto pr-2 custom-scrollbar flex-1 mb-6">
                        <div v-for="payment in loan.payments" :key="payment.id" class="flex items-center justify-between border-b border-slate-50 pb-4 last:border-0 last:pb-0">
                            <div>
                                <p class="font-bold text-slate-800">{{ formatCurrency(payment.amount) }}</p>
                                <p class="text-xs text-slate-500 font-medium">{{ payment.payment_date }}</p>
                            </div>
                            <span class="p-2 bg-green-50 text-green-600 rounded-full">
                                <CheckCircle2 :size="14" />
                            </span>
                        </div>
                        <p v-if="loan.payments.length === 0" class="text-center text-slate-400 py-8 italic">Sin pagos registrados</p>
                    </div>
                    
                    <div class="pt-2 border-t border-slate-100 flex-shrink-0">
                        <button 
                            @click="showHistoryModal = false"
                            class="w-full px-4 py-3 bg-slate-900 text-white font-bold rounded-xl hover:bg-slate-800 transition-all shadow-xl shadow-slate-200"
                        >
                            Cerrar Historial
                        </button>
                    </div>
                </div>
            </div>
        </div>
    </AppLayout>
</template>
