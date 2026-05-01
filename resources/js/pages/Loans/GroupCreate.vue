<script setup lang="ts">
import { ref, computed } from 'vue';
import { Head, useForm, Link } from '@inertiajs/vue3';
import { Calculator, Landmark, ChevronDown, ArrowLeft, Package, Save, Clock, Star, Users } from 'lucide-vue-next';
import { dashboard as dashboardRoute } from '@/routes';
import AppLayout from '@/layouts/AppLayout.vue';
import type { BreadcrumbItem } from '@/types';

interface ClienteInfo {
    id: number;
    nombre: string;
}

const props = defineProps<{
    grupo: {
        id: number;
        nombre: string;
        id_asesor: number | null;
        id_responsable: number | null;
        clientes: ClienteInfo[];
    };
    asesores: Array<{ id: number; nombre: string }>;
}>();

const form = useForm({
    id_asesor: props.grupo.id_asesor || '',
    id_responsable: props.grupo.id_responsable || '',
    fecha: new Date().toISOString().split('T')[0],
    plazo: '14',
    prestamos: props.grupo.clientes.map(c => ({
        id_cliente: c.id,
        nombre: c.nombre,
        monto_otorgado: '',
        valor_ficha: '',
    })),
});

// Calculate metrics per loan
const calculateMetrics = (monto: string, ficha: string, plazo: string) => {
    const p = parseInt(plazo) || 0;
    const m = parseFloat(monto) || 0;
    const f = parseFloat(ficha) || 0;

    const total = f * p;
    const interes = Math.max(0, total - m);

    return { total, interes };
};

// Global totals
const globalTotals = computed(() => {
    let totalMonto = 0;
    let totalFicha = 0;
    let totalInteres = 0;
    let granTotal = 0;

    form.prestamos.forEach(p => {
        const metrics = calculateMetrics(p.monto_otorgado, p.valor_ficha, form.plazo);
        totalMonto += parseFloat(p.monto_otorgado) || 0;
        totalFicha += parseFloat(p.valor_ficha) || 0;
        totalInteres += metrics.interes;
        granTotal += metrics.total;
    });

    return { totalMonto, totalFicha, totalInteres, granTotal };
});

const dayOfWeek = computed(() => {
    if (!form.fecha) return '';
    const date = new Date(form.fecha + 'T12:00:00'); 
    const days = ['Domingo', 'Lunes', 'Martes', 'Miércoles', 'Jueves', 'Viernes', 'Sábado'];
    return days[date.getDay()];
});

const submit = () => {
    form.post(`/grupos/${props.grupo.id}/store-loans`);
};

const breadcrumbs: BreadcrumbItem[] = [
    { title: 'Dashboard', href: dashboardRoute().url },
    { title: 'Grupos', href: '/grupos' },
    { title: props.grupo.nombre, href: `/grupos/${props.grupo.id}` },
    { title: 'Apertura Grupal', href: '#' },
];

const formatCurrency = (value: number) => {
    return new Intl.NumberFormat('es-MX', {
        style: 'currency',
        currency: 'MXN',
    }).format(value);
};

// Sugerir ficha del 100/1000 cuando se escriba el monto
const handleMontoChange = (index: number) => {
    const monto = parseFloat(form.prestamos[index].monto_otorgado);
    if (monto) {
        form.prestamos[index].valor_ficha = ((monto / 1000) * 100).toString();
    } else {
        form.prestamos[index].valor_ficha = '';
    }
};

</script>

<template>
    <Head title="Nuevo Préstamo Grupal" />

    <AppLayout :breadcrumbs="breadcrumbs">
        <div class="max-w-7xl mx-auto p-8 lg:p-12 bg-white min-h-screen shadow-sm border-x border-slate-100">
            <!-- Professional Header -->
            <header class="flex items-center justify-between gap-6 mb-16 pb-8 border-b border-slate-200">
                <div class="flex items-center gap-6">
                    <Link 
                        :href="`/grupos/${grupo.id}`" 
                        class="text-slate-400 hover:text-slate-900 transition-colors"
                    >
                        <ArrowLeft :size="24" />
                    </Link>
                    <div>
                        <p class="text-[10px] font-bold uppercase tracking-widest text-slate-400 mb-1">Nueva Operación Grupal</p>
                        <h1 class="text-3xl font-bold text-slate-900 tracking-tight">Apertura: {{ grupo.nombre }}</h1>
                    </div>
                </div>
                <div class="flex items-center gap-4">
                    <Link 
                        :href="`/grupos/${grupo.id}`"
                        class="text-xs font-bold text-slate-500 hover:text-slate-900 px-4"
                    >
                        Cancelar
                    </Link>
                    <button 
                        @click="submit"
                        :disabled="form.processing"
                        class="px-8 py-3 bg-emerald-600 text-white text-xs font-bold rounded-lg hover:bg-emerald-700 transition-all flex items-center gap-2 disabled:opacity-50 shadow-sm active:scale-95"
                    >
                        <Save :size="16" />
                        {{ form.processing ? 'Procesando...' : 'Crear Créditos' }}
                    </button>
                </div>
            </header>

            <div class="grid grid-cols-1 lg:grid-cols-4 gap-12">
                <!-- Form Sidebar / Config General -->
                <aside class="lg:col-span-1 space-y-8">
                    <section class="space-y-6">
                        <div class="flex items-center gap-2 border-b border-slate-100 pb-2">
                            <Package :size="18" class="text-slate-400" />
                            <h2 class="text-xs font-bold text-slate-400 uppercase tracking-widest">Config General</h2>
                        </div>

                        <div class="space-y-4">
                            <!-- Asesor -->
                            <div class="space-y-2">
                                <label class="text-[10px] font-bold text-slate-400 uppercase tracking-widest pl-1">Asesor del Grupo</label>
                                <div class="relative">
                                    <select v-model="form.id_asesor" class="w-full px-4 py-2.5 bg-slate-50 border border-slate-200 focus:border-slate-900 focus:bg-white rounded-lg outline-none transition-all text-sm font-semibold appearance-none pr-10">
                                        <option value="">Seleccionar Asesor</option>
                                        <option v-for="asesor in asesores" :key="asesor.id" :value="asesor.id">{{ asesor.nombre }}</option>
                                    </select>
                                    <ChevronDown class="absolute right-4 top-1/2 -translate-y-1/2 text-slate-400 pointer-events-none" :size="16" />
                                </div>
                            </div>

                            <!-- Responsable -->
                            <div class="space-y-2">
                                <label class="text-[10px] font-bold text-slate-400 uppercase tracking-widest pl-1">Responsable del Grupo</label>
                                <div class="relative">
                                    <select v-model="form.id_responsable" class="w-full px-4 py-2.5 bg-amber-50 border border-amber-200 focus:border-amber-500 focus:bg-white rounded-lg outline-none transition-all text-sm font-semibold appearance-none pr-10 text-amber-900">
                                        <option value="">Ninguno (Opcional)</option>
                                        <option v-for="cliente in grupo.clientes" :key="cliente.id" :value="cliente.id">{{ cliente.nombre }}</option>
                                    </select>
                                    <Star class="absolute right-4 top-1/2 -translate-y-1/2 text-amber-500 pointer-events-none" :size="14" />
                                </div>
                            </div>

                            <!-- Plazos -->
                            <div class="space-y-2">
                                <label class="text-[10px] font-bold text-slate-400 uppercase tracking-widest pl-1">Plazos Compartidos</label>
                                <input v-model="form.plazo" type="number" class="w-full px-4 py-2.5 bg-slate-50 border border-slate-200 focus:border-slate-900 focus:bg-white rounded-lg outline-none transition-all text-sm font-semibold" />
                            </div>

                            <!-- Fecha -->
                            <div class="space-y-2">
                                <label class="text-[10px] font-bold text-slate-400 uppercase tracking-widest pl-1">Fecha de Desembolso</label>
                                <input v-model="form.fecha" type="date" class="w-full px-4 py-2.5 bg-slate-50 border border-slate-200 focus:border-slate-900 focus:bg-white rounded-lg outline-none transition-all text-sm font-semibold" />
                                <div class="flex items-center gap-1 mt-1 pl-1 text-[10px] text-slate-500 font-bold uppercase tracking-widest">
                                    <Clock :size="10" />
                                    <span>Día de pago: <span class="text-slate-900">{{ dayOfWeek }}</span></span>
                                </div>
                            </div>
                        </div>
                    </section>

                    <!-- Resumen General -->
                    <section class="bg-slate-900 text-white p-6 rounded-2xl shadow-lg mt-8">
                        <div class="flex items-center gap-2 mb-6 opacity-60">
                            <Calculator :size="16" />
                            <h3 class="text-[10px] font-bold uppercase tracking-widest">Resumen de Cartera</h3>
                        </div>

                        <div class="space-y-6">
                            <div>
                                <p class="text-[10px] font-bold uppercase tracking-widest opacity-40 mb-1">Monto Total Otorgado</p>
                                <p class="text-2xl font-bold">{{ formatCurrency(globalTotals.totalMonto) }}</p>
                            </div>
                            <div class="grid grid-cols-2 gap-4 pt-4 border-t border-white/10">
                                <div>
                                    <p class="text-[9px] font-bold uppercase tracking-widest opacity-40 mb-1">Fichas Sumadas</p>
                                    <p class="text-sm font-bold text-emerald-400">{{ formatCurrency(globalTotals.totalFicha) }}</p>
                                </div>
                                <div>
                                    <p class="text-[9px] font-bold uppercase tracking-widest opacity-40 mb-1">Interés Global</p>
                                    <p class="text-sm font-bold text-amber-400">{{ formatCurrency(globalTotals.totalInteres) }}</p>
                                </div>
                            </div>
                            <div class="pt-4 border-t border-white/10">
                                <p class="text-[10px] font-bold uppercase tracking-widest opacity-40 mb-1">Gran Total a Recuperar</p>
                                <p class="text-lg font-black">{{ formatCurrency(globalTotals.granTotal) }}</p>
                            </div>
                        </div>
                    </section>
                </aside>

                <!-- Listado de Integrantes -->
                <div class="lg:col-span-3 space-y-6">
                    <div class="flex items-center justify-between border-b border-slate-100 pb-2 mb-4">
                        <div class="flex items-center gap-2">
                            <Users :size="18" class="text-slate-400" />
                            <h2 class="text-xs font-bold text-slate-400 uppercase tracking-widest">Montos por Integrante</h2>
                        </div>
                    </div>

                    <div class="bg-white border border-slate-200 rounded-xl shadow-sm overflow-hidden">
                        <table class="w-full text-left border-collapse table-auto">
                            <thead>
                                <tr class="bg-slate-50 border-b border-slate-200">
                                    <th class="px-6 py-4 text-[10px] font-bold text-slate-400 uppercase tracking-widest">Cliente</th>
                                    <th class="w-40 px-6 py-4 text-[10px] font-bold text-slate-400 uppercase tracking-widest text-right">Monto</th>
                                    <th class="w-40 px-6 py-4 text-[10px] font-bold text-slate-400 uppercase tracking-widest text-right">Ficha</th>
                                    <th class="w-32 px-6 py-4 text-[10px] font-bold text-slate-400 uppercase tracking-widest text-right">Interés</th>
                                    <th class="w-32 px-6 py-4 text-[10px] font-bold text-slate-400 uppercase tracking-widest text-right">Total</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-slate-50">
                                <tr v-for="(prestamo, index) in form.prestamos" :key="prestamo.id_cliente" class="hover:bg-slate-50/50 transition-colors">
                                    <td class="px-6 py-4">
                                        <div class="text-sm font-bold text-slate-900 flex items-center gap-2">
                                            {{ prestamo.nombre }}
                                            <span v-if="form.id_responsable === prestamo.id_cliente" class="px-1.5 py-0.5 bg-amber-100 text-amber-700 text-[8px] font-black uppercase tracking-widest rounded-sm" title="Responsable">Resp</span>
                                        </div>
                                    </td>
                                    <td class="px-6 py-4">
                                        <div class="relative">
                                            <span class="absolute left-3 top-1/2 -translate-y-1/2 text-slate-400 text-xs font-bold">$</span>
                                            <input 
                                                v-model="prestamo.monto_otorgado" 
                                                @input="handleMontoChange(index)"
                                                type="number" 
                                                class="w-full pl-6 pr-3 py-2 bg-white border border-slate-200 focus:border-emerald-500 focus:ring-1 focus:ring-emerald-500 rounded-lg outline-none transition-all text-sm font-bold text-right" 
                                                placeholder="0.00"
                                            />
                                        </div>
                                    </td>
                                    <td class="px-6 py-4">
                                        <div class="relative">
                                            <span class="absolute left-3 top-1/2 -translate-y-1/2 text-slate-400 text-xs font-bold">$</span>
                                            <input 
                                                v-model="prestamo.valor_ficha" 
                                                type="number" 
                                                class="w-full pl-6 pr-3 py-2 bg-white border border-slate-200 focus:border-slate-900 focus:ring-1 focus:ring-slate-900 rounded-lg outline-none transition-all text-sm font-bold text-right" 
                                                placeholder="0.00"
                                            />
                                        </div>
                                    </td>
                                    <td class="px-6 py-4 text-right">
                                        <div class="text-xs font-bold text-slate-500">
                                            {{ formatCurrency(calculateMetrics(prestamo.monto_otorgado, prestamo.valor_ficha, form.plazo).interes) }}
                                        </div>
                                    </td>
                                    <td class="px-6 py-4 text-right">
                                        <div class="text-sm font-black text-slate-900">
                                            {{ formatCurrency(calculateMetrics(prestamo.monto_otorgado, prestamo.valor_ficha, form.plazo).total) }}
                                        </div>
                                    </td>
                                </tr>
                                <tr v-if="form.prestamos.length === 0">
                                    <td colspan="5" class="px-6 py-12 text-center text-slate-500 text-sm font-medium">
                                        No hay integrantes en este grupo.
                                    </td>
                                </tr>
                            </tbody>
                            <tfoot class="bg-slate-50 border-t border-slate-200">
                                <tr>
                                    <td class="px-6 py-4 text-right text-[10px] font-bold text-slate-400 uppercase tracking-widest">Totales</td>
                                    <td class="px-6 py-4 text-right text-sm font-black text-slate-900">{{ formatCurrency(globalTotals.totalMonto) }}</td>
                                    <td class="px-6 py-4 text-right text-sm font-black text-emerald-600">{{ formatCurrency(globalTotals.totalFicha) }}</td>
                                    <td class="px-6 py-4 text-right text-sm font-black text-amber-600">{{ formatCurrency(globalTotals.totalInteres) }}</td>
                                    <td class="px-6 py-4 text-right text-sm font-black text-slate-900">{{ formatCurrency(globalTotals.granTotal) }}</td>
                                </tr>
                            </tfoot>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </AppLayout>
</template>
