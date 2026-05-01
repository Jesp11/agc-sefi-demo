<script setup lang="ts">
import { ref, computed } from 'vue';
import { Head, useForm, Link } from '@inertiajs/vue3';
import { 
    PiggyBank, Save, Download, Plus, 
    Calendar, User, DollarSign, ArrowLeft, History, ChevronDown
} from 'lucide-vue-next';
import { dashboard as dashboardRoute } from '@/routes';
import AppLayout from '@/layouts/AppLayout.vue';
import type { BreadcrumbItem } from '@/types';

const props = defineProps<{
    report: Array<{
        id: number;
        nombre: string;
        months: Record<number, number>;
        total: number;
    }>;
    year: number;
    asesores: Array<{ id_asesor: number; nombre: string }>;
}>();

const showAddModal = ref(false);

const form = useForm({
    id_asesor: '',
    monto: '',
    fecha: new Date().toISOString().split('T')[0],
    observaciones: ''
});

const submit = () => {
    form.post('/savings', {
        onSuccess: () => {
            showAddModal.value = false;
            form.reset();
        }
    });
};

const monthNames = [
    'ENE', 'FEB', 'MZO', 'ABR', 'MAY', 'JUN',
    'JUL', 'AGO', 'SEP', 'OCT', 'NOV', 'DIC'
];

const breadcrumbs: BreadcrumbItem[] = [
    { title: 'Dashboard', href: dashboardRoute().url },
    { title: 'Ahorro Voluntario', href: '#' },
];

const formatCurrency = (value: number) => {
    return new Intl.NumberFormat('es-MX', {
        style: 'currency',
        currency: 'MXN',
        minimumFractionDigits: 0
    }).format(value);
};

const monthlyTotals = computed(() => {
    const totals = array_fill_months(0);
    props.report.forEach(row => {
        for (let m = 1; m <= 12; m++) {
            totals[m] += row.months[m] || 0;
        }
    });
    return totals;
});

const grandTotal = computed(() => {
    return Object.values(monthlyTotals.value).reduce((a, b) => a + b, 0);
});

function array_fill_months(val: number) {
    const obj: Record<number, number> = {};
    for (let i = 1; i <= 12; i++) obj[i] = val;
    return obj;
}

const exportPdf = () => {
    window.location.href = '/savings/export?year=' + props.year;
};
</script>

<template>
    <Head title="Ahorro Voluntario" />

    <AppLayout :breadcrumbs="breadcrumbs">
        <div class="w-full mx-auto p-8 lg:p-12">
            <!-- Header Section -->
            <header class="flex flex-col md:flex-row md:items-end justify-between gap-6 mb-12">
                <div>
                    <p class="text-[10px] font-bold uppercase tracking-widest text-slate-400 mb-1">Personal de AGC Servicios Financieros</p>
                    <h1 class="text-3xl font-black text-slate-900 tracking-tight">RESUMEN DE AHORRO VOLUNTARIO {{ year }}</h1>
                </div>
                <div class="flex gap-4">
                    <button 
                        @click="showAddModal = true"
                        class="px-6 py-2.5 bg-slate-900 text-white text-xs font-bold rounded-lg hover:bg-slate-800 transition-all flex items-center gap-2"
                    >
                        <Plus :size="16" />
                        Registrar Ahorro
                    </button>
                    <button 
                        @click="exportPdf"
                        class="px-6 py-2.5 bg-white border border-slate-200 text-slate-900 text-xs font-bold rounded-lg hover:bg-slate-50 transition-all flex items-center gap-2"
                    >
                        <Download :size="16" />
                        Exportar Reporte
                    </button>
                </div>
            </header>

            <!-- Main Table Grid -->
            <div class="bg-white border border-slate-100 rounded-2xl shadow-sm overflow-hidden">
                <div class="overflow-x-auto">
                    <table class="w-full text-left border-collapse">
                        <thead>
                            <tr class="bg-slate-50 border-b border-slate-100">
                                <th class="px-6 py-4 text-[10px] font-bold text-slate-500 uppercase tracking-widest">Empleado</th>
                                <th class="px-4 py-4 text-[10px] font-bold text-slate-500 uppercase tracking-widest text-center">ID</th>
                                <th v-for="month in monthNames" :key="month" class="px-3 py-4 text-[9px] font-bold text-slate-500 uppercase tracking-widest text-center">
                                    {{ month }}/{{ year.toString().slice(-2) }}
                                </th>
                                <th class="px-6 py-4 text-[10px] font-bold text-slate-900 uppercase tracking-widest text-right bg-slate-100/50">Total</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-slate-50">
                            <tr v-for="row in report" :key="row.id" class="hover:bg-slate-50/50 transition-colors">
                                <td class="px-6 py-4">
                                    <div class="flex flex-col">
                                        <span class="text-sm font-bold text-slate-900 leading-none">{{ row.nombre }}</span>
                                    </div>
                                </td>
                                <td class="px-4 py-4 text-center">
                                    <span class="text-xs font-mono text-slate-400">#{{ row.id }}</span>
                                </td>
                                <td v-for="(amount, month) in row.months" :key="month" class="px-3 py-4 text-center">
                                    <span v-if="amount > 0" class="text-xs font-bold text-indigo-600">{{ formatCurrency(amount) }}</span>
                                    <span v-else class="text-slate-200 font-bold">-</span>
                                </td>
                                <td class="px-6 py-4 text-right bg-slate-50/30">
                                    <span class="text-sm font-black text-slate-900">{{ formatCurrency(row.total) }}</span>
                                </td>
                            </tr>
                        </tbody>
                        <tfoot class="bg-slate-50 border-t-2 border-slate-200">
                            <tr>
                                <td colspan="2" class="px-6 py-5 text-[10px] font-black uppercase tracking-widest text-slate-500">Sumatoria Total</td>
                                <td v-for="month in 12" :key="month" class="px-3 py-5 text-center">
                                    <span class="text-xs font-black text-slate-900">{{ formatCurrency(monthlyTotals[month]) }}</span>
                                </td>
                                <td class="px-6 py-5 text-right bg-indigo-50/50">
                                    <span class="text-sm font-black text-indigo-600">{{ formatCurrency(grandTotal) }}</span>
                                </td>
                            </tr>
                        </tfoot>
                    </table>
                </div>
            </div>

            <!-- Empty State -->
            <div v-if="report.length === 0" class="mt-8 p-12 text-center border-2 border-dashed border-slate-100 rounded-3xl">
                <PiggyBank :size="48" class="mx-auto text-slate-200 mb-4" />
                <p class="text-sm text-slate-400 font-medium">No se han registrado ahorros para el año {{ year }}.</p>
            </div>
        </div>

        <!-- Add Savings Modal -->
        <div v-if="showAddModal" class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-slate-900/60 backdrop-blur-sm">
            <div class="bg-white w-full max-w-md rounded-2xl shadow-2xl overflow-hidden border border-slate-100">
                <div class="p-6 border-b border-slate-100 flex items-center justify-between bg-slate-50">
                    <div class="flex items-center gap-3">
                        <div class="p-2 bg-indigo-600 text-white rounded-lg">
                            <Plus :size="18" />
                        </div>
                        <h2 class="text-sm font-black text-slate-900 uppercase">Registrar Ahorro Voluntario</h2>
                    </div>
                    <button @click="showAddModal = false" class="text-slate-400 hover:text-slate-900">
                        <Plus :size="20" class="rotate-45" />
                    </button>
                </div>
                
                <form @submit.prevent="submit" class="p-8 space-y-6">
                    <div class="space-y-2">
                        <label class="text-[10px] font-bold text-slate-400 uppercase tracking-widest pl-1">Seleccionar Empleado</label>
                        <div class="relative">
                            <select v-model="form.id_asesor" class="w-full px-4 py-3 bg-slate-50 border border-slate-200 focus:border-slate-900 focus:bg-white rounded-lg outline-none transition-all text-sm font-semibold appearance-none pr-10" :class="{ 'border-red-500': form.errors.id_asesor }">
                                <option value="">Seleccionar Asesor...</option>
                                <option v-for="asesor in asesores" :key="asesor.id_asesor" :value="asesor.id_asesor">
                                    {{ asesor.nombre }}
                                </option>
                            </select>
                            <ChevronDown class="absolute right-4 top-1/2 -translate-y-1/2 text-slate-400 pointer-events-none" :size="16" />
                        </div>
                        <p v-if="form.errors.id_asesor" class="text-[10px] text-red-500 font-bold mt-1 pl-1">{{ form.errors.id_asesor }}</p>
                    </div>

                    <div class="grid grid-cols-2 gap-4">
                        <div class="space-y-2">
                            <label class="text-[10px] font-bold text-slate-400 uppercase tracking-widest pl-1">Monto de Ahorro</label>
                            <div class="relative">
                                <span class="absolute left-4 top-1/2 -translate-y-1/2 text-slate-400 font-bold pointer-events-none">$</span>
                                <input v-model="form.monto" type="number" step="0.01" required class="w-full pl-8 pr-4 py-3 bg-slate-50 border border-slate-200 focus:border-slate-900 focus:bg-white rounded-lg outline-none transition-all text-sm font-semibold [appearance:textfield] [&::-webkit-outer-spin-button]:appearance-none [&::-webkit-inner-spin-button]:appearance-none" :class="{ 'border-red-500': form.errors.monto }" />
                            </div>
                            <p v-if="form.errors.monto" class="text-[10px] text-red-500 font-bold mt-1 pl-1">{{ form.errors.monto }}</p>
                        </div>
                        <div class="space-y-2">
                            <label class="text-[10px] font-bold text-slate-400 uppercase tracking-widest pl-1">Fecha</label>
                            <input v-model="form.fecha" type="date" required class="w-full px-4 py-3 bg-slate-50 border border-slate-200 focus:border-slate-900 focus:bg-white rounded-lg outline-none transition-all text-sm font-semibold" :class="{ 'border-red-500': form.errors.fecha }" />
                            <p v-if="form.errors.fecha" class="text-[10px] text-red-500 font-bold mt-1 pl-1">{{ form.errors.fecha }}</p>
                        </div>
                    </div>

                    <div class="space-y-2">
                        <label class="text-[10px] font-bold text-slate-400 uppercase tracking-widest pl-1">Observaciones (Opcional)</label>
                        <textarea v-model="form.observaciones" rows="3" class="w-full px-4 py-3 bg-slate-50 border border-slate-200 focus:border-slate-900 focus:bg-white rounded-lg outline-none transition-all text-sm font-semibold resize-none"></textarea>
                    </div>

                    <button 
                        type="submit"
                        :disabled="form.processing"
                        class="w-full py-4 bg-slate-900 text-white text-xs font-black uppercase tracking-widest rounded-xl hover:bg-slate-800 transition-all shadow-lg flex items-center justify-center gap-2 disabled:opacity-50"
                    >
                        <Save :size="16" />
                        {{ form.processing ? 'Registrando...' : 'Confirmar Registro' }}
                    </button>
                </form>
            </div>
        </div>
    </AppLayout>
</template>

<style scoped>
.overflow-x-auto::-webkit-scrollbar {
    height: 6px;
}
.overflow-x-auto::-webkit-scrollbar-thumb {
    background: #e2e8f0;
    border-radius: 10px;
}
</style>
