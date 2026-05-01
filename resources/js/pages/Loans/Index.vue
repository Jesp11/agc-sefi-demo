<script setup lang="ts">
import { reactive, watch } from 'vue';
import { watchDebounced } from '@vueuse/core';
import { Head, Link, router } from '@inertiajs/vue3';
import { Landmark, ArrowLeft, Search, Wallet, ChevronRight, Filter, Calendar, CreditCard } from 'lucide-vue-next';
import { dashboard as dashboardRoute } from '@/routes';
import loanRoutes from '@/routes/loans';
import AppLayout from '@/layouts/AppLayout.vue';
import type { BreadcrumbItem } from '@/types';

const props = defineProps<{
    loans: Array<{
        id: number;
        customer: { nombre: string };
        amount: number;
        total: number;
        fecha: string;
        ciclo: number;
        plazo: number;
        asesor: string;
    }>,
    filters: {
        search?: string;
    }
}>();

const form = reactive({
    search: props.filters?.search || '',
});

watchDebounced(
    () => form,
    (value) => {
        router.get(loanRoutes.index().url, value, {
            preserveState: true,
            replace: true,
        });
    },
    { deep: true, debounce: 300 }
);

const formatCurrency = (value: number) => {
    return new Intl.NumberFormat('es-MX', {
        style: 'currency',
        currency: 'MXN',
    }).format(value);
};

const breadcrumbs: BreadcrumbItem[] = [
    { title: 'Dashboard', href: dashboardRoute().url },
    { title: 'Préstamos', href: loanRoutes.index().url },
];
</script>

<template>
    <Head title="Cartera de Préstamos" />

    <AppLayout :breadcrumbs="breadcrumbs">
        <div class="p-8 max-w-[1600px] mx-auto pb-24">
            <header class="mb-10 flex flex-col md:flex-row md:items-center md:justify-between gap-6">
                <div>
                    <h1 class="text-4xl font-black text-slate-900 tracking-tight mb-2">Cartera de Préstamos</h1>
                    <p class="text-slate-500 font-medium">Control total de créditos otorgados, ciclos y cobranza.</p>
                </div>
                <Link 
                    :href="loanRoutes.create().url"
                    class="inline-flex items-center px-10 py-4 bg-gradient-to-r from-teal-600 to-emerald-600 text-white font-black rounded-2xl hover:from-teal-700 hover:to-emerald-700 transition-all gap-2 shadow-xl shadow-teal-100 active:scale-95"
                >
                    <CreditCard :size="20" />
                    Nuevo Préstamo
                </Link>
            </header>

            <!-- Search Bar -->
            <div class="relative mb-8">
                <Search class="absolute left-5 top-1/2 -translate-y-1/2 text-slate-400" :size="22" />
                <input 
                    v-model="form.search"
                    type="text" 
                    placeholder="Buscar por cliente o ID de crédito..."
                    class="w-full pl-14 pr-6 py-5 bg-white border-2 border-slate-100 rounded-3xl focus:ring-4 focus:ring-teal-500/10 focus:border-teal-500 outline-none transition-all text-lg shadow-sm"
                />
            </div>

            <!-- Loans List -->
            <div class="bg-white rounded-[2.5rem] border border-slate-100 shadow-xl shadow-slate-200/50 overflow-hidden">
                <table class="w-full text-left">
                    <thead class="bg-slate-50/50 border-b border-slate-100">
                        <tr>
                            <th class="px-8 py-6 text-xs font-black text-slate-400 uppercase tracking-widest">ID</th>
                            <th class="px-8 py-6 text-xs font-black text-slate-400 uppercase tracking-widest">Acreditado</th>
                            <th class="px-8 py-6 text-xs font-black text-slate-400 uppercase tracking-widest text-right">Monto</th>
                            <th class="px-8 py-6 text-xs font-black text-slate-400 uppercase tracking-widest text-right">Total c/Int</th>
                            <th class="px-8 py-6 text-xs font-black text-slate-400 uppercase tracking-widest text-center">Ciclo</th>
                            <th class="px-8 py-6 text-xs font-black text-slate-400 uppercase tracking-widest">Fecha</th>
                            <th class="px-8 py-6 text-xs font-black text-slate-400 uppercase tracking-widest text-right px-12">Acciones</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-50">
                        <tr v-for="loan in loans" :key="loan.id" class="hover:bg-slate-50/80 transition-colors group">
                            <td class="px-8 py-5">
                                <span class="bg-slate-100 text-slate-500 px-3 py-1 rounded-lg font-mono text-xs font-bold">#{{ loan.id }}</span>
                            </td>
                            <td class="px-8 py-5">
                                <p class="font-bold text-slate-900 text-lg">{{ loan.customer?.nombre || 'Desconocido' }}</p>
                                <p class="text-xs text-slate-400 font-medium uppercase tracking-wider">Asesor: {{ loan.asesor }}</p>
                            </td>
                            <td class="px-8 py-5 text-right font-semibold text-slate-600">{{ formatCurrency(loan.amount) }}</td>
                            <td class="px-8 py-5 text-right font-black text-slate-900 text-lg">{{ formatCurrency(loan.total) }}</td>
                            <td class="px-8 py-5 text-center">
                                <span class="inline-flex items-center justify-center w-8 h-8 bg-teal-50 text-teal-700 font-black rounded-full border border-teal-100">{{ loan.ciclo || 1 }}</span>
                            </td>
                            <td class="px-8 py-5">
                                <div class="flex items-center text-slate-600 font-semibold">
                                    <Calendar :size="16" class="mr-2 text-slate-400" />
                                    {{ loan.fecha }}
                                </div>
                            </td>
                            <td class="px-8 py-5 text-right px-12">
                                <Link 
                                    :href="loanRoutes.show({ loan: loan.id }).url"
                                    class="inline-flex items-center px-6 py-2 bg-slate-900 text-white text-xs font-black rounded-xl hover:bg-slate-800 transition-all gap-2 shadow-lg active:scale-95"
                                >
                                    <Wallet :size="14" />
                                    Detalle / Cobro
                                </Link>
                            </td>
                        </tr>
                    </tbody>
                </table>
                
                <div v-if="loans.length === 0" class="p-24 text-center">
                    <div class="bg-slate-50 w-20 h-20 rounded-3xl flex items-center justify-center mx-auto mb-4">
                        <Landmark :size="40" class="text-slate-200" />
                    </div>
                    <p class="text-slate-400 font-bold text-xl">No hay préstamos registrados aún.</p>
                    <p class="text-slate-300">Comienza otorgando el primer crédito.</p>
                </div>
            </div>
        </div>
    </AppLayout>
</template>
