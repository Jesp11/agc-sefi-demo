<script setup lang="ts">
import { ref, computed } from 'vue';
import { Head } from '@inertiajs/vue3';
import { dashboard as dashboardRoute } from '@/routes';
import type { BreadcrumbItem } from '@/types';
import AppLayout from '@/layouts/AppLayout.vue';
import { 
    FileText, 
    BarChart3, 
    Calendar, 
    Users,
    Download,
    ArrowRight,
    X,
    ChevronDown
} from 'lucide-vue-next';

const breadcrumbs: BreadcrumbItem[] = [
    { title: 'Dashboard', href: dashboardRoute().url },
    { title: 'Centro de Reportes', href: '/centro-reportes' },
];

const showPeriodModal = ref(false);
const selectedReportId = ref('');
const selectedPeriod = ref('month');
const customStartDate = ref('');
const customEndDate = ref('');

const openPeriodModal = (reportId: string) => {
    selectedReportId.value = reportId;
    selectedPeriod.value = reportId === 'upcoming' ? 'tomorrow' : 'month';
    customStartDate.value = '';
    customEndDate.value = '';
    showPeriodModal.value = true;
};

const closePeriodModal = () => {
    showPeriodModal.value = false;
    setTimeout(() => {
        selectedReportId.value = '';
    }, 200);
};

const handleReportClick = (report: any) => {
    if (report.id === 'operation' || report.id === 'upcoming') {
        openPeriodModal(report.id);
    } else {
        window.open(report.href, '_blank');
    }
};

const generateReport = () => {
    let url = '';
    if (selectedReportId.value === 'operation') {
        url = `/reports/export-pdf?period=${selectedPeriod.value}`;
    } else if (selectedReportId.value === 'upcoming') {
        url = `/reports/upcoming/export-pdf?period=${selectedPeriod.value}`;
    }
    
    if (selectedPeriod.value === 'custom') {
        if (!customStartDate.value || !customEndDate.value) return;
        url += `&start_date=${customStartDate.value}&end_date=${customEndDate.value}`;
    }
    window.open(url, '_blank');
    closePeriodModal();
};

const reports = [
    {
        id: 'customers',
        title: 'Directorio de Clientes',
        description: 'Exporta el listado completo de todos los clientes registrados en el sistema, incluyendo su información de contacto.',
        icon: Users,
        color: 'teal',
        href: '/customers/export-pdf'
    },
    {
        id: 'operation',
        title: 'Estado de Cuenta / Operación',
        description: 'Genera el reporte financiero general con el resumen de cartera, capital en riesgo y utilidades históricas.',
        icon: BarChart3,
        color: 'indigo'
    },
    {
        id: 'upcoming',
        title: 'Cobranza Proyectada',
        description: 'Descarga las rutas de cobranza y los pagos esperados permitiendo seleccionar el rango de días de interés.',
        icon: Calendar,
        color: 'fuchsia'
    }
];

const getColorClasses = (color: string) => {
    const classes: Record<string, { bg: string, text: string, hoverBg: string, borderHover: string, shadowHover: string }> = {
        teal: { bg: 'bg-teal-50', text: 'text-teal-600', hoverBg: 'group-hover:bg-teal-600', borderHover: 'hover:border-teal-200', shadowHover: 'hover:shadow-teal-100/50' },
        indigo: { bg: 'bg-indigo-50', text: 'text-indigo-600', hoverBg: 'group-hover:bg-indigo-600', borderHover: 'hover:border-indigo-200', shadowHover: 'hover:shadow-indigo-100/50' },
        fuchsia: { bg: 'bg-fuchsia-50', text: 'text-fuchsia-600', hoverBg: 'group-hover:bg-fuchsia-600', borderHover: 'hover:border-fuchsia-200', shadowHover: 'hover:shadow-fuchsia-100/50' },
    };
    return classes[color] || classes.indigo;
};

const modalIcon = computed(() => {
    if (selectedReportId.value === 'operation') return BarChart3;
    if (selectedReportId.value === 'upcoming') return Calendar;
    return FileText;
});

const modalTitle = computed(() => {
    if (selectedReportId.value === 'operation') return 'Reporte de Operación';
    if (selectedReportId.value === 'upcoming') return 'Cobranza Proyectada';
    return 'Seleccionar Intervalo';
});

const modalColorClass = computed(() => {
    if (selectedReportId.value === 'operation') return 'bg-indigo-50 text-indigo-600';
    if (selectedReportId.value === 'upcoming') return 'bg-fuchsia-50 text-fuchsia-600';
    return 'bg-slate-50 text-slate-600';
});

const modalButtonClass = computed(() => {
    if (selectedReportId.value === 'operation') return 'bg-indigo-600 hover:bg-indigo-700 focus:ring-indigo-500';
    if (selectedReportId.value === 'upcoming') return 'bg-fuchsia-600 hover:bg-fuchsia-700 focus:ring-fuchsia-500';
    return 'bg-slate-800 hover:bg-slate-900 focus:ring-slate-500';
});
</script>

<template>
    <Head title="Centro de Reportes" />

    <AppLayout :breadcrumbs="breadcrumbs">
        <div class="p-8 max-w-[1200px] mx-auto pb-24">
            <header class="mb-12 flex flex-col items-center text-center">
                <div class="p-4 bg-slate-100 text-slate-700 rounded-3xl mb-6">
                    <FileText :size="48" stroke-width="1.5" />
                </div>
                <h1 class="text-4xl font-black text-slate-900 tracking-tight">Centro de Reportes</h1>
                <p class="text-slate-500 text-lg mt-4 max-w-2xl">
                    Concentrado general de generación de documentos. Selecciona el tipo de reporte que deseas procesar y descargar en formato PDF.
                </p>
            </header>

            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-8">
                <button 
                    v-for="(report, index) in reports" 
                    :key="index"
                    @click="handleReportClick(report)"
                    :class="['flex flex-col text-left p-8 bg-white rounded-[40px] border border-slate-100 shadow-sm transition-all group hover:shadow-2xl focus:outline-none', getColorClasses(report.color).shadowHover, getColorClasses(report.color).borderHover]"
                >
                    <div class="flex items-center justify-between mb-6">
                        <div :class="['p-5 rounded-3xl transition-all group-hover:text-white', getColorClasses(report.color).bg, getColorClasses(report.color).text, getColorClasses(report.color).hoverBg]">
                            <component :is="report.icon" :size="32" stroke-width="1.5" />
                        </div>
                        <div class="h-10 w-10 rounded-full bg-slate-50 flex items-center justify-center text-slate-400 group-hover:bg-slate-900 group-hover:text-white transition-all">
                            <Download :size="20" stroke-width="2" />
                        </div>
                    </div>
                    
                    <h3 class="text-xl font-bold text-slate-900 mb-3 group-hover:text-slate-700 transition-colors">{{ report.title }}</h3>
                    <p class="text-slate-500 text-sm leading-relaxed mb-8 flex-grow">
                        {{ report.description }}
                    </p>
                    
                    <div class="mt-auto flex items-center text-sm font-bold text-slate-400 group-hover:text-slate-900 transition-colors">
                        Generar Documento
                        <ArrowRight :size="16" class="ml-2 transform group-hover:translate-x-1 transition-transform" />
                    </div>
                </button>
            </div>
        </div>

        <!-- Period Selection Modal -->
        <div v-if="showPeriodModal" class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-slate-900/40 backdrop-blur-sm" @click.self="closePeriodModal">
            <div class="bg-white rounded-[32px] shadow-2xl max-w-md w-full p-8 relative overflow-hidden animate-in fade-in zoom-in-95 duration-200">
                <button @click="closePeriodModal" class="absolute top-6 right-6 text-slate-400 hover:text-slate-700 bg-slate-50 hover:bg-slate-100 p-2 rounded-full transition-colors focus:outline-none">
                    <X :size="20" />
                </button>
                
                <div class="mb-6 mt-2">
                    <div :class="['w-14 h-14 rounded-2xl flex items-center justify-center mb-6', modalColorClass]">
                        <component :is="modalIcon" :size="28" stroke-width="1.5" />
                    </div>
                    <h2 class="text-2xl font-bold text-slate-900 mb-2">{{ modalTitle }}</h2>
                    <p class="text-slate-500 text-sm leading-relaxed">Configura el rango de tiempo del documento que deseas descargar.</p>
                </div>

                <div class="space-y-6 mb-8">
                    <div>
                        <label class="block text-sm font-bold text-slate-700 mb-2">Periodo a consultar</label>
                        <div class="relative">
                            <select 
                                v-model="selectedPeriod"
                                class="w-full pl-4 pr-10 py-3.5 bg-slate-50 border border-slate-200 rounded-xl focus:ring-2 outline-none transition-all text-slate-800 font-semibold appearance-none cursor-pointer hover:border-slate-300"
                                :class="selectedReportId === 'operation' ? 'focus:ring-indigo-500/50' : 'focus:ring-fuchsia-500/50'"
                            >
                                <template v-if="selectedReportId === 'operation'">
                                    <option value="week">Semana actual</option>
                                    <option value="month">Mes actual</option>
                                    <option value="quarter">Trimestre actual</option>
                                    <option value="semester">Semestre actual</option>
                                    <option value="year">Año actual</option>
                                    <option value="custom">Personalizado...</option>
                                </template>
                                <template v-else-if="selectedReportId === 'upcoming'">
                                    <option value="today">Hoy</option>
                                    <option value="tomorrow">Mañana</option>
                                    <option value="week">Esta semana (Lunes a Domingo)</option>
                                    <option value="custom">Personalizado...</option>
                                </template>
                            </select>
                            <div class="absolute inset-y-0 right-0 flex items-center pr-4 pointer-events-none text-slate-400">
                                <ChevronDown :size="20" stroke-width="2" />
                            </div>
                        </div>
                    </div>

                    <div v-show="selectedPeriod === 'custom'" class="grid grid-cols-2 gap-4 pt-2">
                        <div>
                            <label class="block text-sm font-bold text-slate-700 mb-2">Fecha Inicio</label>
                            <input 
                                type="date"
                                v-model="customStartDate"
                                class="w-full px-4 py-3.5 bg-slate-50 border border-slate-200 rounded-xl outline-none transition-all text-sm font-semibold text-slate-800 hover:border-slate-300 focus:ring-2"
                                :class="selectedReportId === 'operation' ? 'focus:ring-indigo-500/50' : 'focus:ring-fuchsia-500/50'"
                            />
                        </div>
                        <div>
                            <label class="block text-sm font-bold text-slate-700 mb-2">Fecha Fin</label>
                            <input 
                                type="date"
                                v-model="customEndDate"
                                class="w-full px-4 py-3.5 bg-slate-50 border border-slate-200 rounded-xl outline-none transition-all text-sm font-semibold text-slate-800 hover:border-slate-300 focus:ring-2"
                                :class="selectedReportId === 'operation' ? 'focus:ring-indigo-500/50' : 'focus:ring-fuchsia-500/50'"
                            />
                        </div>
                    </div>
                </div>

                <div class="flex gap-4 pt-4 border-t border-slate-100">
                    <button 
                        @click="closePeriodModal"
                        class="flex-1 px-6 py-3.5 bg-white text-slate-700 font-bold rounded-xl border border-slate-200 hover:bg-slate-50 hover:text-slate-900 transition-colors focus:outline-none focus:ring-2 focus:ring-slate-200"
                    >
                        Cancelar
                    </button>
                    <button 
                        @click="generateReport"
                        :disabled="selectedPeriod === 'custom' && (!customStartDate || !customEndDate)"
                        :class="['flex-1 flex items-center justify-center gap-2 px-6 py-3.5 text-white font-bold rounded-xl transition-all disabled:opacity-50 disabled:cursor-not-allowed group focus:outline-none focus:ring-2 focus:ring-offset-2', modalButtonClass]"
                    >
                        <Download :size="18" class="group-hover:-translate-y-0.5 transition-transform" stroke-width="2" />
                        Descargar
                    </button>
                </div>
            </div>
        </div>
    </AppLayout>
</template>
