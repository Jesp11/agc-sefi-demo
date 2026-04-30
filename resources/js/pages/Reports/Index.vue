<script setup lang="ts">
import { ref, computed, onMounted } from 'vue';
import { Head, Link, usePage, router } from '@inertiajs/vue3';
import type { BreadcrumbItem } from '@/types';
import {
    Download, TrendingUp, AlertCircle, Calendar, BarChart3, Users, Landmark, MapPin, Activity, CheckCircle2, Navigation, FileText, Smartphone, DollarSign, Wallet, ArrowRight, Info
} from 'lucide-vue-next';
import reportsRoutes from '@/routes/reports';
import { dashboard as dashboardRoute } from '@/routes';
import AppLayout from '@/layouts/AppLayout.vue';

const props = defineProps<{
    metrics: {
        collections_today: number;
        collections_period: number;
        overdue_count: number;
        overdue_amount: number;
        total_portfolio: number;
        total_collected: number;
        total_outstanding: number;
        expected_interest: number;
        upcoming_tomorrow_count: number;
        upcoming_tomorrow_amount: number;
        active_loans_count: number;
        collection_health: number;
    },
    recent_activity: Array<{
        date: string;
        type: 'loan' | 'payment';
        customer: string;
        amount: number;
    }>,
    current_period: string,
    custom_start_date?: string,
    custom_end_date?: string,
    period_details?: {
        start: string;
        end: string;
    },
    upcoming_installments?: Array<{
        id: number;
        customer: string;
        loan_id: number;
        installment_number: number;
        amount: number;
        due_date: string;
    }>
}>();

const periodOptions = [
    { value: 'day', label: 'Por Día' },
    { value: 'week', label: 'Semanal' },
    { value: 'month', label: 'Mensual' },
    { value: 'custom', label: 'Personalizado' }
];

const customRange = ref({
    start: props.custom_start_date || new Date().toISOString().split('T')[0],
    end: props.custom_end_date || new Date().toISOString().split('T')[0]
});

const applyCustomRange = () => {
    router.get(reportsRoutes.index().url, { 
        period: 'custom',
        start_date: customRange.value.start,
        end_date: customRange.value.end
    }, {
        preserveState: true,
        preserveScroll: true,
    });
};

const changePeriod = (period: string) => {
    if (period === 'custom') {
        applyCustomRange();
        return;
    }
    router.get(reportsRoutes.index().url, { period }, {
        preserveState: true,
        preserveScroll: true,
    });
};

const pdfExportUrl = computed(() => {
    let url = `${reportsRoutes.exportPdf().url}?period=${props.current_period}`;
    if (props.current_period === 'custom') {
        url += `&start_date=${props.custom_start_date || customRange.value.start}&end_date=${props.custom_end_date || customRange.value.end}`;
    }
    return url;
});

const formatCurrency = (value: number) => {
    return new Intl.NumberFormat('es-MX', {
        style: 'currency',
        currency: 'MXN',
    }).format(value);
};

const breadcrumbs: BreadcrumbItem[] = [
    { title: 'Dashboard', href: dashboardRoute().url },
    { title: 'Métricas', href: reportsRoutes.index().url },
];
</script>

<template>
    <Head title="Métricas y Desempeño" />

    <AppLayout :breadcrumbs="breadcrumbs">
        <div class="p-8 max-w-[1600px] mx-auto pb-24">
            <header class="mb-8 flex flex-col md:flex-row md:items-center justify-between gap-4">
                <div>
                    <h1 class="text-3xl font-bold text-slate-900 tracking-tight">Métricas y Desempeño</h1>
                    <p class="text-slate-500">Estado de cuenta general y analíticas del sistema</p>
                </div>

                <div class="flex flex-col items-end gap-4">
                    <div class="flex items-center gap-4">
                        <div class="hidden md:flex bg-slate-100 p-1 rounded-2xl items-center border border-slate-200">
                            <button 
                                v-for="option in periodOptions" 
                                :key="option.value"
                                @click="changePeriod(option.value)"
                                :class="[
                                    'px-5 py-2.5 text-xs font-bold rounded-xl transition-all cursor-pointer',
                                    current_period === option.value 
                                        ? 'bg-white text-indigo-700 shadow-sm border border-slate-200/50' 
                                        : 'text-slate-500 hover:text-slate-800 hover:bg-slate-200/50'
                                ]"
                            >
                                {{ option.label }}
                            </button>
                        </div>

                        <a 
                            :href="pdfExportUrl"
                            target="_blank"
                            class="inline-flex items-center px-6 py-3 bg-slate-900 text-white font-bold rounded-2xl hover:bg-slate-800 transition-all shadow-xl shadow-slate-200"
                        >
                            <Download :size="18" class="mr-2" />
                            Descargar PDF
                        </a>
                    </div>
                    
                    <!-- Custom Date Picker -->
                    <div v-if="current_period === 'custom'" class="flex items-center gap-2 bg-white p-2 border border-slate-200 rounded-xl shadow-sm animate-in fade-in slide-in-from-top-2">
                        <input 
                            v-model="customRange.start" 
                            type="date" 
                            class="px-3 py-1.5 text-sm bg-slate-50 border border-slate-200 rounded-lg focus:ring-2 focus:ring-indigo-500 outline-none"
                        />
                        <span class="text-slate-400 font-bold text-xs uppercase">a</span>
                        <input 
                            v-model="customRange.end" 
                            type="date" 
                            class="px-3 py-1.5 text-sm bg-slate-50 border border-slate-200 rounded-lg focus:ring-2 focus:ring-indigo-500 outline-none"
                        />
                        <button 
                            @click="applyCustomRange"
                            class="px-4 py-1.5 bg-indigo-600 text-white text-sm font-bold rounded-lg hover:bg-indigo-700 transition-colors"
                        >
                            Aplicar
                        </button>
                    </div>
                </div>
            </header>
            
            <!-- Dynamic Upcoming Payments Banner -->
            <div 
                v-if="metrics.upcoming_tomorrow_count > 0"
                class="mb-8 p-6 bg-indigo-50 rounded-3xl border border-indigo-100 flex flex-col md:flex-row items-center justify-between gap-6 relative overflow-hidden group shadow-sm shadow-indigo-100/50"
            >
                <div class="absolute -right-10 -top-10 w-40 h-40 bg-indigo-500/10 rounded-full blur-3xl group-hover:scale-110 transition-transform duration-700"></div>
                <div class="flex items-center gap-6 relative">
                    <div class="p-4 bg-white text-indigo-600 rounded-2xl shadow-sm border border-indigo-100/50 pulse-teal">
                        <Calendar :size="28" stroke-width="2.5" />
                    </div>
                    <div>
                        <h4 class="text-indigo-900 font-black text-xl mb-1 flex items-center gap-2">
                            Próximos Pagos para Mañana
                            <span class="px-2 py-0.5 bg-indigo-600 text-white text-[10px] font-black rounded-lg uppercase">Prioritario</span>
                        </h4>
                        <p class="text-indigo-700/70 font-medium">
                            Se esperan <span class="text-indigo-900 font-bold underline decoration-indigo-200 underline-offset-4">{{ metrics.upcoming_tomorrow_count }} pagos</span> 
                            para el día de mañana por un total de 
                            <span class="text-indigo-900 font-black">{{ formatCurrency(metrics.upcoming_tomorrow_amount) }}</span>.
                        </p>
                    </div>
                </div>
                <Link 
                    :href="`/reports/upcoming`"
                    class="relative bg-indigo-600 hover:bg-indigo-700 text-white px-8 py-3 rounded-2xl font-black text-sm transition-all shadow-lg shadow-indigo-200 hover:-translate-y-0.5 active:translate-y-0 uppercase tracking-wider flex items-center justify-center gap-2"
                >
                    <Wallet :size="18" />
                    Ver Detalles
                </Link>
            </div>

            <div 
                v-else 
                class="mb-8 p-6 bg-slate-50 rounded-3xl border border-slate-100 flex items-center gap-4 text-slate-500 italic text-sm"
            >
                <CheckCircle2 :size="18" class="text-slate-400" />
                No se registran pagos programados para el día de mañana.
            </div>

            <!-- Metrics Grid -->
            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6 mb-8">
                <!-- Daily Collection -->
                <div class="bg-white p-6 rounded-3xl border border-slate-100 shadow-sm">
                    <div class="flex items-center justify-between mb-4">
                        <div class="p-2 bg-emerald-50 text-emerald-600 rounded-lg">
                            <TrendingUp :size="20" />
                        </div>
                        <span class="text-[10px] font-bold text-emerald-500 bg-emerald-50 px-2 py-0.5 rounded-full uppercase">Hoy</span>
                    </div>
                    <p class="text-slate-500 text-xs font-bold uppercase tracking-widest mb-1">Cobranza del Día</p>
                    <h3 class="text-2xl font-black text-slate-900">{{ formatCurrency(metrics.collections_today) }}</h3>
                    <p class="text-[10px] text-slate-400 mt-2">Recibido en ventanilla</p>
                </div>

                <!-- Period Collection -->
                <div class="bg-white p-6 rounded-3xl border border-slate-100 shadow-sm">
                    <div class="flex items-center justify-between mb-4">
                        <div class="p-2 bg-blue-50 text-blue-600 rounded-lg">
                            <Calendar :size="20" />
                        </div>
                        <span class="text-[10px] font-bold text-blue-500 bg-blue-50 px-2 py-0.5 rounded-full uppercase">Periodo Actual</span>
                    </div>
                    <p class="text-slate-500 text-xs font-bold uppercase tracking-widest mb-1">Cobranza del Periodo</p>
                    <h3 class="text-2xl font-black text-slate-900">{{ formatCurrency(metrics.collections_period) }}</h3>
                    <p class="text-[10px] text-slate-400 mt-2 font-medium" v-if="period_details">
                        {{ new Date(period_details.start + 'T00:00:00').toLocaleDateString('es-MX', { month: 'short', day: 'numeric' }) }} - {{ new Date(period_details.end + 'T00:00:00').toLocaleDateString('es-MX', { month: 'short', day: 'numeric' }) }}
                    </p>
                </div>

                <!-- Overdue -->
                <div class="bg-white p-6 rounded-3xl border border-slate-100 shadow-sm">
                    <div class="flex items-center justify-between mb-4">
                        <div class="p-2 bg-rose-50 text-rose-600 rounded-lg">
                            <AlertCircle :size="20" />
                        </div>
                        <span class="text-[10px] font-bold text-rose-500 bg-rose-50 px-2 py-0.5 rounded-full uppercase">Crítico</span>
                    </div>
                    <div class="relative group/tooltip flex items-center gap-1.5 mb-1 w-max">
                        <p class="text-slate-500 text-xs font-bold uppercase tracking-widest">Monto en Mora</p>
                        <Info :size="14" class="text-slate-400 cursor-help" />
                        <div class="absolute bottom-full mb-2 invisible group-hover/tooltip:visible opacity-0 group-hover/tooltip:opacity-100 transition-all bg-slate-900 text-white text-[10px] px-3 py-2 rounded-lg w-56 z-10 font-medium pointer-events-none normal-case tracking-normal shadow-xl -ml-2">
                            Suma total de cuotas vencidas que no han sido pagadas por los clientes. Representa el dinero atrasado.
                        </div>
                    </div>
                    <h3 class="text-2xl font-black text-slate-900">{{ formatCurrency(metrics.overdue_amount) }}</h3>
                    <div class="flex items-center justify-between mt-2">
                        <p class="text-[10px] text-rose-500 font-bold bg-rose-50 px-2 py-0.5 rounded-md">{{ metrics.overdue_count }} vencidas</p>
                        <Link href="/reports/overdue" class="inline-flex items-center text-[11px] font-black text-rose-600 hover:text-rose-700 transition-colors">
                            Ver Lista <ArrowRight :size="12" class="ml-1" />
                        </Link>
                    </div>
                </div>

                <!-- Expected Yield -->
                <div class="bg-white p-6 rounded-3xl border border-slate-100 shadow-sm">
                    <div class="flex items-center justify-between mb-4">
                        <div class="p-2 bg-amber-50 text-amber-600 rounded-lg">
                            <BarChart3 :size="20" />
                        </div>
                        <span class="text-[10px] font-bold text-amber-500 bg-amber-50 px-2 py-0.5 rounded-full uppercase">Proyectado</span>
                    </div>
                    <div class="relative group/tooltip flex items-center gap-1.5 mb-1 w-max">
                        <p class="text-slate-500 text-xs font-bold uppercase tracking-widest">Interés x Cobrar</p>
                        <Info :size="14" class="text-slate-400 cursor-help" />
                        <div class="absolute bottom-full mb-2 invisible group-hover/tooltip:visible opacity-0 group-hover/tooltip:opacity-100 transition-all bg-slate-900 text-white text-[10px] px-3 py-2 rounded-lg w-56 z-10 font-medium pointer-events-none normal-case tracking-normal shadow-xl -ml-2">
                            Ganancia esperada (intereses no devengados) de todos los préstamos que siguen activos en el sistema.
                        </div>
                    </div>
                    <h3 class="text-2xl font-black text-slate-900">{{ formatCurrency(metrics.expected_interest) }}</h3>
                    <p class="text-[10px] text-slate-400 mt-2">Rendimiento sobre capital activo</p>
                </div>
            </div>

            <div class="grid grid-cols-1 lg:grid-cols-3 gap-8">
                <!-- Recent Activity Table -->
                <div class="lg:col-span-2">
                    <div class="bg-white rounded-3xl border border-slate-100 shadow-sm overflow-hidden">
                        <div class="px-8 py-6 border-b border-slate-50 flex items-center justify-between">
                            <h2 class="text-xl font-bold text-slate-900 flex items-center gap-2">
                                <BarChart3 :size="20" class="text-teal-600" />
                                Últimos Ingresos
                            </h2>
                        </div>
                        <table class="w-full text-left">
                            <thead class="bg-slate-50">
                                <tr>
                                    <th class="px-8 py-4 text-xs font-bold text-slate-500 uppercase tracking-widest">Cliente</th>
                                    <th class="px-8 py-4 text-xs font-bold text-slate-500 uppercase tracking-widest">Monto</th>
                                    <th class="px-8 py-4 text-xs font-bold text-slate-500 uppercase tracking-widest">Fecha</th>
                                    <th class="px-8 py-4"></th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-slate-50">
                                <tr v-for="(activity, index) in recent_activity" :key="index" class="hover:bg-slate-50 transition-colors">
                                    <td class="px-8 py-5">
                                        <div class="font-bold text-slate-900">{{ activity.customer }}</div>
                                        <div class="text-[10px] uppercase font-bold text-slate-400">
                                            {{ activity.type === 'loan' ? 'Emisión de Préstamo' : 'Recepción de Abono' }}
                                        </div>
                                    </td>
                                    <td :class="['px-8 py-5 font-bold', activity.type === 'loan' ? 'text-rose-500' : 'text-emerald-600']">
                                        {{ activity.type === 'loan' ? '-' : '+' }}{{ formatCurrency(activity.amount) }}
                                    </td>
                                    <td class="px-8 py-5 text-slate-500 text-sm">
                                    {{ activity.date }}
                                </td>
                                    <td class="px-8 py-5 text-right">
                                        <div :class="['p-1.5 rounded-full inline-flex', activity.type === 'loan' ? 'bg-rose-50 text-rose-600' : 'bg-green-50 text-green-600']">
                                            <component :is="activity.type === 'loan' ? Landmark : CheckCircle2" :size="14" />
                                        </div>
                                    </td>
                                </tr>
                            </tbody>
                        </table>
                        <div v-if="recent_activity.length === 0" class="p-12 text-center text-slate-400 italic">
                            No hay actividad reciente registrada.
                        </div>
                    </div>
                </div>

                <!-- Portfolio Composition -->
                <div class="space-y-6">
                    <div class="bg-white p-8 rounded-3xl border border-slate-100 shadow-sm relative group">
                        <div class="absolute inset-0 overflow-hidden rounded-3xl pointer-events-none">
                            <div class="absolute -right-4 -top-4 w-32 h-32 bg-indigo-500/5 rounded-full blur-3xl group-hover:bg-indigo-500/10 transition-all"></div>
                        </div>
                        
                        <h3 class="text-sm font-bold text-slate-400 uppercase tracking-[0.2em] mb-8 flex items-center gap-2">
                            Composición de Cartera
                        </h3>
                        
                        <div class="space-y-8">
                            <div>
                            <div class="relative group/tooltip inline-flex items-center gap-1.5 mb-1">
                                <p class="text-slate-400 text-[10px] font-bold uppercase tracking-widest">Valor Esperado de Cartera</p>
                                <Info :size="12" class="text-slate-300 cursor-help" />
                                <div class="absolute bottom-full mb-2 invisible group-hover/tooltip:visible opacity-0 group-hover/tooltip:opacity-100 transition-all bg-slate-900 text-white text-[10px] px-3 py-2 rounded-lg w-56 z-10 font-medium pointer-events-none normal-case tracking-normal shadow-xl -ml-2">
                                    Valor total proyectado de la cartera. Es la suma exacta del total recuperado históricamente más el saldo que aún queda por cobrar de los créditos vigentes.
                                </div>
                            </div>
                                <p class="text-4xl font-black text-slate-900">{{ formatCurrency(metrics.total_portfolio) }}</p>
                            </div>

                            <div class="grid grid-cols-1 gap-4">
                                <div class="p-5 bg-slate-50 rounded-2xl border border-slate-100/50 hover:border-emerald-200 transition-colors">
                                    <p class="text-slate-400 text-[10px] font-bold uppercase mb-1">Total Recuperado</p>
                                    <p class="text-emerald-600 text-xl font-black">{{ formatCurrency(metrics.total_collected) }}</p>
                                </div>
                                <div class="p-5 bg-slate-50 rounded-2xl border border-slate-100/50 hover:border-rose-200 transition-colors">
                                    <p class="text-slate-400 text-[10px] font-bold uppercase mb-1">Saldo por Cobrar</p>
                                    <p class="text-rose-600 text-xl font-black">{{ formatCurrency(metrics.total_outstanding) }}</p>
                                </div>
                            </div>

                            <div class="p-6 bg-indigo-50 rounded-2xl border border-indigo-100/50 relative group/health">
                                <div class="absolute inset-0 overflow-hidden rounded-2xl pointer-events-none">
                                    <div class="absolute right-0 top-0 p-4 opacity-10 group-hover/health:opacity-20 transition-opacity text-indigo-600">
                                        <DollarSign :size="64" />
                                    </div>
                                </div>
                                <div class="relative group/tooltip inline-flex items-center gap-1.5 mb-1">
                                    <p class="text-indigo-900/40 text-[10px] font-bold uppercase tracking-widest">Salud de Cobranza</p>
                                    <Info :size="12" class="text-indigo-900/30 cursor-help" />
                                    <div class="absolute bottom-full mb-2 invisible group-hover/tooltip:visible opacity-0 group-hover/tooltip:opacity-100 transition-all bg-indigo-900 text-white text-[10px] px-3 py-2 rounded-lg w-64 z-10 font-medium pointer-events-none normal-case tracking-normal shadow-xl -ml-2">
                                        Porcentaje del portafolio que está al corriente. Se calcula restando el 'Monto en Mora' del 'Saldo por Cobrar' y dividiéndolo entre el total. (100 = Ningún atraso)
                                    </div>
                                </div>
                                <div class="flex items-end gap-2 mb-3">
                                    <p class="text-3xl font-black text-indigo-900">{{ Math.round(metrics.collection_health) }}%</p>
                                </div>
                                <div class="w-full bg-indigo-200/50 h-2.5 rounded-full overflow-hidden">
                                    <div class="bg-indigo-600 h-full shadow-[0_0_10px_rgba(79,70,229,0.3)] transition-all duration-1000" :style="{ width: metrics.collection_health + '%' }"></div>
                                </div>
                            </div>

                            <div class="pt-2 space-y-4 border-t border-slate-50">
                                <div class="flex items-center justify-between p-3 bg-slate-50/50 rounded-xl hover:bg-slate-50 transition-colors cursor-default border border-transparent hover:border-slate-100">
                                    <span class="text-xs text-slate-500 font-bold uppercase">Préstamos Activos</span>
                                    <span class="font-black text-slate-900">{{ metrics.active_loans_count }}</span>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </AppLayout>
</template>
