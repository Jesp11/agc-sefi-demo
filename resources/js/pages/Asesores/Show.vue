<script setup lang="ts">
import { computed } from 'vue';
import { 
    User, CreditCard, PiggyBank, ArrowLeft, 
    Calendar, History, DollarSign, ChevronRight,
    TrendingUp, Wallet, Trash2, Edit2, Hash,
    Users2
} from 'lucide-vue-next';
import { Head, Link, router } from '@inertiajs/vue3';
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

const deleteAsesor = () => {
    if (confirm('¿Está seguro de eliminar este asesor? Esta acción no se puede deshacer.')) {
        router.delete(`/asesores/${props.asesor.id}`);
    }
};

const individualLatestCredits = computed(() => {
    const map = new Map();
    props.asesor.creditos.filter(c => !c.cliente?.id_grupo).forEach(credito => {
        const clientId = credito.id_cliente;
        if (!map.has(clientId) || map.get(clientId).ciclo < credito.ciclo) {
            map.set(clientId, credito);
        }
    });
    return Array.from(map.values()).sort((a, b) => b.id_credito - a.id_credito);
});

const groupLatestCredits = computed(() => {
    const map = new Map();
    props.asesor.creditos_grupales.filter(c => c.cliente?.id_grupo).forEach(credito => {
        const clientId = credito.id_cliente;
        if (!map.has(clientId) || map.get(clientId).ciclo < credito.ciclo) {
            map.set(clientId, credito);
        }
    });
    return Array.from(map.values()).sort((a, b) => b.id_credito_grupal - a.id_credito_grupal);
});
</script>

<template>
    <Head :title="`Asesor - ${asesor.nombre}`" />

    <AppLayout :breadcrumbs="breadcrumbs">
        <div class="w-full mx-auto p-8 lg:p-12">
            <!-- Header Section -->
            <header class="flex flex-col md:flex-row md:items-center justify-between gap-6 mb-12">
                <div class="flex items-center gap-6">
                    <Link 
                        :href="asesorRoutes.index().url" 
                        class="p-2.5 bg-white border border-slate-200 rounded-xl text-slate-400 hover:text-slate-900 transition-all shadow-sm active:scale-95"
                    >
                        <ArrowLeft :size="20" />
                    </Link>
                    <div>
                        <p class="text-[10px] font-bold uppercase tracking-widest text-slate-400 mb-1">Expediente de Personal</p>
                        <h1 class="text-3xl font-bold text-slate-900 tracking-tight uppercase">{{ asesor.nombre }}</h1>
                    </div>
                </div>
                
                <div class="flex items-center gap-4">
                    <Link 
                        :href="asesorRoutes.edit(asesor.id).url"
                        class="px-6 py-2.5 bg-slate-100 text-slate-900 text-xs font-bold rounded-xl hover:bg-slate-900 hover:text-white transition-all flex items-center gap-2 shadow-sm"
                    >
                        <Edit2 :size="16" />
                        Editar Perfil
                    </Link>
                    <div class="px-6 py-3 bg-white border border-slate-100 rounded-xl shadow-sm flex items-center gap-4">
                        <div class="p-2 bg-blue-50 text-blue-700 rounded-lg">
                            <Wallet :size="20" />
                        </div>
                        <div>
                            <p class="text-[8px] font-bold text-slate-400 uppercase tracking-widest leading-none mb-1">Ahorro Acumulado</p>
                            <p class="text-lg font-bold text-slate-900 leading-none">{{ formatCurrency(asesor.total_ahorro) }}</p>
                        </div>
                    </div>
                </div>
            </header>

            <div class="space-y-12 max-w-5xl">
                <!-- Left Column: Voluntary Savings -->
                <div class="space-y-12">
                    <section>
                        <div class="flex items-center justify-between mb-6 pl-1">
                            <div class="flex items-center gap-3">
                                <div class="p-2 bg-slate-900 text-white rounded-lg">
                                    <PiggyBank :size="18" />
                                </div>
                                <h2 class="text-sm font-bold text-slate-900 uppercase tracking-tight">Historial de Ahorro Voluntario</h2>
                            </div>
                            <Link 
                                :href="savingsRoutes.index().url"
                                class="text-[10px] font-bold text-blue-600 uppercase hover:underline"
                            >
                                Gestionar Ahorros
                            </Link>
                        </div>

                        <div class="bg-white border border-slate-100 rounded-xl shadow-sm overflow-hidden min-h-[200px]">
                            <table class="w-full text-left border-collapse">
                                <thead>
                                    <tr class="bg-slate-50 border-b border-slate-100">
                                        <th class="px-6 py-4 text-[10px] font-bold text-slate-400 uppercase tracking-widest">Fecha de Depósito</th>
                                        <th class="px-6 py-4 text-[10px] font-bold text-slate-400 uppercase tracking-widest">Observaciones</th>
                                        <th class="px-6 py-4 text-[10px] font-bold text-slate-400 uppercase tracking-widest text-right">Monto</th>
                                    </tr>
                                </thead>
                                <tbody class="divide-y divide-slate-50">
                                    <tr v-for="ahorro in asesor.ahorros" :key="ahorro.id_ahorro" class="hover:bg-slate-50/50 transition-colors">
                                        <td class="px-6 py-4">
                                            <div class="flex items-center gap-3">
                                                <Calendar :size="14" class="text-slate-300" />
                                                <span class="text-sm font-bold text-slate-700">{{ formatDate(ahorro.fecha) }}</span>
                                            </div>
                                        </td>
                                        <td class="px-6 py-4">
                                            <span class="text-xs text-slate-500 italic font-medium">{{ ahorro.observaciones || 'Sin observaciones' }}</span>
                                        </td>
                                        <td class="px-6 py-4 text-right">
                                            <span class="text-sm font-bold text-slate-900">{{ formatCurrency(ahorro.monto) }}</span>
                                        </td>
                                    </tr>
                                    <tr v-if="asesor.ahorros.length === 0">
                                        <td colspan="3" class="px-6 py-16 text-center text-slate-400 italic text-sm font-medium">
                                            No se han registrado aportaciones de ahorro para este asesor.
                                        </td>
                                    </tr>
                                </tbody>
                                <tfoot v-if="asesor.ahorros.length > 0" class="bg-slate-50/50 border-t border-slate-100">
                                    <tr>
                                        <td colspan="2" class="px-6 py-5 text-[10px] font-bold text-slate-400 uppercase text-right">Total Acumulado</td>
                                        <td class="px-6 py-5 text-right">
                                            <span class="text-base font-bold text-blue-600">{{ formatCurrency(asesor.total_ahorro) }}</span>
                                        </td>
                                    </tr>
                                </tfoot>
                            </table>
                        </div>
                    </section>

                    <!-- Credits Managed: Individual -->
                    <section>
                        <div class="flex items-center gap-3 mb-6 pl-1">
                            <div class="p-2 bg-slate-900 text-white rounded-lg">
                                <CreditCard :size="18" />
                            </div>
                            <h2 class="text-sm font-bold text-slate-900 uppercase tracking-tight">Cartera Individual Administrada</h2>
                        </div>

                        <div class="bg-white border border-slate-100 rounded-xl shadow-sm overflow-hidden min-h-[200px]">
                            <table class="w-full text-left border-collapse">
                                <thead>
                                    <tr class="bg-slate-50 border-b border-slate-100">
                                        <th class="px-6 py-4 text-[10px] font-bold text-slate-400 uppercase tracking-widest">Folio</th>
                                        <th class="px-6 py-4 text-[10px] font-bold text-slate-400 uppercase tracking-widest">Nombre del Cliente</th>
                                        <th class="w-32 px-6 py-4 text-[10px] font-bold text-slate-400 uppercase tracking-widest text-center">Ciclo</th>
                                        <th class="w-48 px-6 py-4 text-[10px] font-bold text-slate-400 uppercase tracking-widest text-right">Monto</th>
                                        <th class="w-24 px-6 py-4 text-[10px] font-bold text-slate-400 uppercase tracking-widest text-right">Acción</th>
                                    </tr>
                                </thead>
                                <tbody class="divide-y divide-slate-50">
                                    <tr v-for="credito in individualLatestCredits" :key="credito.id_credito" class="hover:bg-slate-50/50 transition-colors group">
                                        <td class="px-6 py-4">
                                            <div class="flex items-center gap-2">
                                                <Hash :size="12" class="text-slate-300" />
                                                <span class="text-xs font-mono font-bold text-slate-400">#{{ credito.id_credito }}</span>
                                            </div>
                                        </td>
                                        <td class="px-6 py-4">
                                            <span class="text-sm font-bold text-slate-900">{{ credito.cliente?.nombre }}</span>
                                        </td>
                                        <td class="px-6 py-4 text-center">
                                            <span class="px-2.5 py-1 bg-slate-100 text-[10px] font-bold rounded-md uppercase tracking-wider text-slate-600">Ciclo {{ credito.ciclo }}</span>
                                        </td>
                                        <td class="px-6 py-4 text-right">
                                            <span class="text-sm font-bold text-slate-900">{{ formatCurrency(credito.monto_otorgado) }}</span>
                                        </td>
                                        <td class="px-6 py-4 text-right">
                                            <Link :href="loanRoutes.show(credito.id_credito).url" class="inline-flex p-2 bg-slate-50 text-slate-400 rounded-lg group-hover:bg-slate-900 group-hover:text-white transition-all">
                                                <ChevronRight :size="18" />
                                            </Link>
                                        </td>
                                    </tr>
                                    <tr v-if="individualLatestCredits.length === 0">
                                        <td colspan="5" class="px-6 py-16 text-center text-slate-400 italic text-sm font-medium">
                                            No hay créditos individuales registrados bajo este asesor.
                                        </td>
                                    </tr>
                                </tbody>
                            </table>
                        </div>
                    </section>

                    <!-- Credits Managed: Group -->
                    <section>
                        <div class="flex items-center gap-3 mb-6 pl-1">
                            <div class="p-2 bg-slate-900 text-white rounded-lg">
                                <Users2 :size="18" />
                            </div>
                            <h2 class="text-sm font-bold text-slate-900 uppercase tracking-tight">Cartera Grupal Administrada</h2>
                        </div>

                        <div class="bg-white border border-slate-100 rounded-xl shadow-sm overflow-hidden min-h-[200px]">
                            <table class="w-full text-left border-collapse">
                                <thead>
                                    <tr class="bg-slate-50 border-b border-slate-100">
                                        <th class="px-6 py-4 text-[10px] font-bold text-slate-400 uppercase tracking-widest">Grupo</th>
                                        <th class="px-6 py-4 text-[10px] font-bold text-slate-400 uppercase tracking-widest">Cliente</th>
                                        <th class="w-32 px-6 py-4 text-[10px] font-bold text-slate-400 uppercase tracking-widest text-center">Ciclo</th>
                                        <th class="w-48 px-6 py-4 text-[10px] font-bold text-slate-400 uppercase tracking-widest text-right">Monto</th>
                                        <th class="w-24 px-6 py-4 text-[10px] font-bold text-slate-400 uppercase tracking-widest text-right">Acción</th>
                                    </tr>
                                </thead>
                                <tbody class="divide-y divide-slate-50">
                                    <tr v-for="credito in groupLatestCredits" :key="credito.id_credito_grupal" class="hover:bg-slate-50/50 transition-colors group">
                                        <td class="px-6 py-4">
                                            <div class="flex flex-col">
                                                <span class="text-sm font-bold text-slate-900">{{ credito.grupo?.nombre || 'Sin Grupo' }}</span>
                                                <span class="text-[10px] text-slate-400 font-mono font-bold">#{{ credito.id_credito_grupal }}</span>
                                            </div>
                                        </td>
                                        <td class="px-6 py-4">
                                            <span class="text-sm font-medium text-slate-700 font-bold">{{ credito.cliente?.nombre }}</span>
                                        </td>
                                        <td class="px-6 py-4 text-center">
                                            <span class="px-2.5 py-1 bg-blue-50 text-[10px] font-bold rounded-md uppercase tracking-wider text-blue-700">Ciclo {{ credito.ciclo }}</span>
                                        </td>
                                        <td class="px-6 py-4 text-right">
                                            <span class="text-sm font-bold text-slate-900">{{ formatCurrency(credito.monto_otorgado) }}</span>
                                        </td>
                                        <td class="px-6 py-4 text-right">
                                            <Link :href="`/loans-grupales/${credito.id_credito_grupal}`" class="inline-flex p-2 bg-slate-50 text-slate-400 rounded-lg group-hover:bg-slate-900 group-hover:text-white transition-all">
                                                <ChevronRight :size="18" />
                                            </Link>
                                        </td>
                                    </tr>
                                    <tr v-if="groupLatestCredits.length === 0">
                                        <td colspan="5" class="px-6 py-16 text-center text-slate-400 italic text-sm font-medium">
                                            No hay créditos grupales registrados bajo este asesor.
                                        </td>
                                    </tr>
                                </tbody>
                            </table>
                        </div>
                    </section>
                </div>
            </div>
        </div>
    </AppLayout>
</template>
