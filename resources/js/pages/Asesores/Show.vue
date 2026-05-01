<script setup lang="ts">
import { Head, Link } from '@inertiajs/vue3';
import { 
    User, CreditCard, PiggyBank, ArrowLeft, 
    Calendar, History, DollarSign, ChevronRight,
    TrendingUp, Wallet
} from 'lucide-vue-next';
import { dashboard as dashboardRoute } from '@/routes';
import asesorRoutes from '@/routes/asesores';
import savingsRoutes from '@/routes/savings';
import loanRoutes from '@/routes/loans';
import AppLayout from '@/layouts/AppLayout.vue';
import type { BreadcrumbItem } from '@/types';

const props = defineProps<{
    asesor: {
        id: number;
        nombre: string;
        ahorros: Array<any>;
        creditos: Array<any>;
        total_ahorro: number;
    }
}>();

const breadcrumbs: BreadcrumbItem[] = [
    { title: 'Dashboard', href: dashboardRoute().url },
    { title: 'Asesores', href: asesorRoutes.index().url },
    { title: props.asesor.nombre, href: '#' },
];

const formatCurrency = (value: number) => {
    return new Intl.NumberFormat('es-MX', {
        style: 'currency',
        currency: 'MXN'
    }).format(value);
};

const formatDate = (date: string) => {
    return new Date(date).toLocaleDateString('es-MX', {
        day: '2-digit',
        month: 'long',
        year: 'numeric'
    });
};
</script>

<template>
    <Head :title="`Asesor - ${asesor.nombre}`" />

    <AppLayout :breadcrumbs="breadcrumbs">
        <div class="w-full mx-auto p-8 lg:p-12">
            <!-- Header Section -->
            <header class="flex flex-col md:flex-row md:items-end justify-between gap-6 mb-12">
                <div>
                    <div class="flex items-center gap-2 mb-1">
                        <Link :href="asesorRoutes.index().url" class="text-slate-400 hover:text-slate-900 transition-colors">
                            <ArrowLeft :size="16" />
                        </Link>
                        <p class="text-[10px] font-bold uppercase tracking-widest text-slate-400">Expediente de Personal</p>
                    </div>
                    <h1 class="text-3xl font-black text-slate-900 tracking-tight uppercase">{{ asesor.nombre }}</h1>
                </div>
                
                <div class="flex gap-4">
                    <div class="px-6 py-3 bg-white border border-slate-100 rounded-2xl shadow-sm flex items-center gap-4">
                        <div class="p-2 bg-indigo-50 text-indigo-600 rounded-xl">
                            <Wallet :size="20" />
                        </div>
                        <div>
                            <p class="text-[8px] font-bold text-slate-400 uppercase tracking-widest leading-none mb-1">Ahorro Acumulado</p>
                            <p class="text-lg font-black text-slate-900 leading-none">{{ formatCurrency(asesor.total_ahorro) }}</p>
                        </div>
                    </div>
                </div>
            </header>

            <div class="space-y-12 max-w-5xl">
                <!-- Left Column: Voluntary Savings -->
                <div class="space-y-12">
                    <section>
                        <div class="flex items-center justify-between mb-6">
                            <div class="flex items-center gap-3">
                                <div class="p-2 bg-slate-900 text-white rounded-lg">
                                    <PiggyBank :size="18" />
                                </div>
                                <h2 class="text-sm font-black text-slate-900 uppercase tracking-tight">Historial de Ahorro Voluntario</h2>
                            </div>
                            <Link 
                                :href="savingsRoutes.index().url"
                                class="text-[10px] font-bold text-indigo-600 uppercase hover:underline"
                            >
                                Gestionar Ahorros
                            </Link>
                        </div>

                        <div class="bg-white border border-slate-100 rounded-2xl shadow-sm overflow-hidden">
                            <table class="w-full text-left border-collapse">
                                <thead>
                                    <tr class="bg-slate-50 border-b border-slate-100">
                                        <th class="px-6 py-4 text-[9px] font-bold text-slate-500 uppercase tracking-widest">Fecha de Depósito</th>
                                        <th class="px-6 py-4 text-[9px] font-bold text-slate-500 uppercase tracking-widest">Observaciones</th>
                                        <th class="px-6 py-4 text-[9px] font-bold text-slate-500 uppercase tracking-widest text-right">Monto</th>
                                    </tr>
                                </thead>
                                <tbody class="divide-y divide-slate-50">
                                    <tr v-for="ahorro in asesor.ahorros" :key="ahorro.id_ahorro" class="hover:bg-slate-50/50 transition-colors">
                                        <td class="px-6 py-4">
                                            <div class="flex items-center gap-3">
                                                <Calendar :size="14" class="text-slate-300" />
                                                <span class="text-sm font-semibold text-slate-700">{{ formatDate(ahorro.fecha) }}</span>
                                            </div>
                                        </td>
                                        <td class="px-6 py-4">
                                            <span class="text-xs text-slate-500 italic">{{ ahorro.observaciones || 'Sin observaciones' }}</span>
                                        </td>
                                        <td class="px-6 py-4 text-right">
                                            <span class="text-sm font-black text-slate-900">{{ formatCurrency(ahorro.monto) }}</span>
                                        </td>
                                    </tr>
                                    <tr v-if="asesor.ahorros.length === 0">
                                        <td colspan="3" class="px-6 py-12 text-center text-slate-400 italic text-sm">
                                            No se han registrado aportaciones de ahorro para este asesor.
                                        </td>
                                    </tr>
                                </tbody>
                                <tfoot v-if="asesor.ahorros.length > 0" class="bg-slate-50/50">
                                    <tr>
                                        <td colspan="2" class="px-6 py-4 text-[10px] font-bold text-slate-400 uppercase text-right">Total Acumulado</td>
                                        <td class="px-6 py-4 text-right">
                                            <span class="text-base font-black text-indigo-600">{{ formatCurrency(asesor.total_ahorro) }}</span>
                                        </td>
                                    </tr>
                                </tfoot>
                            </table>
                        </div>
                    </section>

                    <!-- Credits Managed -->
                    <section>
                        <div class="flex items-center gap-3 mb-6">
                            <div class="p-2 bg-indigo-600 text-white rounded-lg">
                                <CreditCard :size="18" />
                            </div>
                            <h2 class="text-sm font-black text-slate-900 uppercase tracking-tight">Créditos bajo Gestión</h2>
                        </div>

                        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                            <div v-for="credito in asesor.creditos" :key="credito.id_credito" class="bg-white border border-slate-100 p-6 rounded-2xl hover:border-slate-300 transition-all group">
                                <div class="flex justify-between items-start mb-4">
                                    <div>
                                        <p class="text-[9px] font-bold text-slate-400 uppercase tracking-widest mb-1">Folio #{{ credito.id_credito }}</p>
                                        <h3 class="text-sm font-black text-slate-900 group-hover:text-indigo-600 transition-colors">{{ credito.cliente?.nombre }}</h3>
                                    </div>
                                    <div class="px-2 py-1 bg-slate-100 text-[8px] font-bold rounded uppercase">Ciclo {{ credito.ciclo }}</div>
                                </div>
                                <div class="flex justify-between items-end">
                                    <div>
                                        <p class="text-[8px] font-bold text-slate-400 uppercase mb-1">Capital Otorgado</p>
                                        <p class="text-sm font-bold text-slate-900">{{ formatCurrency(credito.monto_otorgado) }}</p>
                                    </div>
                                    <Link :href="loanRoutes.show(credito.id_credito).url" class="p-2 bg-slate-50 text-slate-400 rounded-lg group-hover:bg-indigo-600 group-hover:text-white transition-all">
                                        <ChevronRight :size="16" />
                                    </Link>
                                </div>
                            </div>
                            <div v-if="asesor.creditos.length === 0" class="col-span-full p-12 text-center border-2 border-dashed border-slate-100 rounded-3xl text-slate-400 text-sm italic">
                                Este asesor aún no tiene créditos asignados.
                            </div>
                        </div>
                    </section>
                </div>
            </div>
        </div>
    </AppLayout>
</template>
