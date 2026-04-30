<script setup lang="ts">
import { reactive, watch } from 'vue';
import { watchDebounced } from '@vueuse/core';
import { Head, Link, router } from '@inertiajs/vue3';
import { Landmark, ArrowLeft, Search, Wallet, ChevronRight, Filter, Calendar } from 'lucide-vue-next';
import { dashboard as dashboardRoute } from '@/routes';
import loanRoutes from '@/routes/loans';
import AppLayout from '@/layouts/AppLayout.vue';
import type { BreadcrumbItem } from '@/types';

const props = defineProps<{
    loans: Array<{
        id: number;
        customer: { name: string };
        amount: number;
        interest_rate: number;
        outstanding_balance: number;
        status: string;
        next_installment?: {
            due_date: string;
            amount: number;
        };
    }>,
    filters: {
        search?: string;
        status?: string;
    }
}>();

const form = reactive({
    search: props.filters?.search || '',
    status: props.filters?.status || 'all',
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

const getStatusClass = (status: string) => {
    switch(status) {
        case 'active': return 'bg-green-100 text-green-700 border-green-200';
        case 'paid': return 'bg-blue-100 text-blue-700 border-blue-200';
        default: return 'bg-slate-100 text-slate-700 border-slate-200';
    }
};

const breadcrumbs: BreadcrumbItem[] = [
    { title: 'Dashboard', href: dashboardRoute().url },
    { title: 'Préstamos', href: loanRoutes.index().url },
];
</script>

<template>
    <Head title="Cartera de Préstamos" />

    <AppLayout :breadcrumbs="breadcrumbs">
        <div class="p-6 max-w-7xl mx-auto">
            <header class="mb-8 flex flex-col md:flex-row md:items-end md:justify-between gap-4">
                <div>
                    <h1 class="text-3xl font-bold text-slate-900 tracking-tight">Cartera de Préstamos</h1>
                    <p class="text-slate-500">Listado general de créditos y estado de cuenta</p>
                </div>
                <Link 
                    :href="loanRoutes.create().url"
                    class="inline-flex items-center px-6 py-3 bg-teal-600 text-white font-bold rounded-2xl hover:bg-teal-500 transition-all gap-2 shadow-lg shadow-teal-100"
                >
                    <CreditCard :size="18" />
                    Nuevo Préstamo
                </Link>
            </header>

            <!-- Filters Row -->
            <div class="mb-6 bg-white p-4 rounded-3xl border border-slate-100 shadow-sm flex flex-col lg:flex-row gap-4">
                <!-- Search Box -->
                <div class="relative flex-1">
                    <Search class="absolute left-4 top-1/2 -translate-y-1/2 text-slate-400" :size="20" />
                    <input 
                        v-model="form.search"
                        type="text" 
                        placeholder="Buscar por cliente o ID de préstamo..."
                        class="w-full pl-12 pr-4 py-3.5 bg-slate-50 border border-slate-200 rounded-2xl focus:ring-2 focus:ring-teal-500 focus:border-teal-500 outline-none transition-all text-sm font-medium"
                    />
                </div>
                
                <div class="flex items-center gap-4 w-full lg:w-auto">
                    <!-- Status Filter -->
                    <div class="relative min-w-[170px] flex-1 lg:flex-none">
                        <Filter class="absolute left-4 top-1/2 -translate-y-1/2 text-slate-400" :size="18" />
                        <select 
                            v-model="form.status"
                            class="w-full pl-11 pr-10 py-3.5 bg-slate-50 border border-slate-200 rounded-2xl focus:ring-2 focus:ring-teal-500 focus:border-teal-500 outline-none transition-all text-sm font-bold text-slate-700 appearance-none cursor-pointer"
                        >
                            <option value="all">Todos los Estatus</option>
                            <option value="active">Activos</option>
                            <option value="paid">Pagados</option>
                        </select>
                        <ChevronRight class="absolute right-4 top-1/2 -translate-y-1/2 text-slate-400 rotate-90 pointer-events-none" :size="16" />
                    </div>
                </div>
            </div>

            <!-- Loans List -->
            <div class="bg-white rounded-3xl border border-slate-100 shadow-sm overflow-hidden">
                <table class="w-full text-left">
                    <thead class="bg-slate-50 border-b border-slate-100">
                        <tr>
                            <th class="px-8 py-4 text-xs font-bold text-slate-500 uppercase tracking-widest text-center">ID</th>
                            <th class="px-8 py-4 text-xs font-bold text-slate-500 uppercase tracking-widest">Cliente</th>
                            <th class="px-8 py-4 text-xs font-bold text-slate-500 uppercase tracking-widest text-right">Monto Original</th>
                            <th class="px-8 py-4 text-xs font-bold text-slate-500 uppercase tracking-widest text-right">Saldo Pendiente</th>
                            <th class="px-8 py-4 text-xs font-bold text-slate-500 uppercase tracking-widest text-center">Progreso</th>
                            <th class="px-8 py-4 text-xs font-bold text-slate-500 uppercase tracking-widest">Próxima</th>
                            <th class="px-8 py-4 text-xs font-bold text-slate-500 uppercase tracking-widest">Estatus</th>
                            <th class="px-8 py-4 text-xs font-bold text-slate-500 uppercase tracking-widest text-right">Acciones</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-50">
                        <tr v-for="loan in loans" :key="loan.id" class="hover:bg-slate-50 transition-colors">
                            <td class="px-8 py-5 text-center font-bold text-slate-400 font-mono">#{{ loan.id }}</td>
                            <td class="px-8 py-5">
                                <p class="font-bold text-slate-900">{{ loan.customer.name }}</p>
                            </td>
                            <td class="px-8 py-5 text-right text-slate-600">{{ formatCurrency(loan.amount) }}</td>
                            <td class="px-8 py-5 text-right font-black text-slate-900">{{ formatCurrency(loan.outstanding_balance) }}</td>
                            <td class="px-8 py-5 w-48">
                                <div class="flex items-center gap-3">
                                    <div class="flex-1 bg-slate-100 h-2 rounded-full overflow-hidden border border-slate-200">
                                        <div 
                                            class="bg-teal-500 h-full transition-all duration-700"
                                            :style="{ width: Math.min(100, Math.round(((loan.amount * (1 + loan.interest_rate / 100)) - loan.outstanding_balance) / (loan.amount * (1 + loan.interest_rate / 100)) * 100)) + '%' }"
                                        ></div>
                                    </div>
                                    <span class="text-[10px] font-bold text-slate-500 min-w-[30px]">
                                        {{ Math.min(100, Math.round(((loan.amount * (1 + loan.interest_rate / 100)) - loan.outstanding_balance) / (loan.amount * (1 + loan.interest_rate / 100)) * 100)) }}%
                                    </span>
                                </div>
                            </td>
                            <td class="px-8 py-5">
                                <div v-if="loan.next_installment" class="flex flex-col">
                                    <span class="text-sm font-bold text-slate-700">{{ loan.next_installment.due_date }}</span>
                                    <span class="text-[10px] font-bold text-slate-400">{{ formatCurrency(loan.next_installment.amount) }}</span>
                                </div>
                                <span v-else class="text-xs text-slate-400 italic">Sin pagos</span>
                            </td>
                            <td class="px-8 py-5">
                                <span :class="['px-3 py-1 text-[10px] font-bold rounded-full border', getStatusClass(loan.status)]">
                                    {{ loan.status.toUpperCase() }}
                                </span>
                            </td>
                            <td class="px-8 py-5 text-right">
                                <Link 
                                    :href="loanRoutes.show({ loan: loan.id }).url"
                                    class="inline-flex items-center text-indigo-600 hover:text-indigo-800 bg-indigo-50 hover:bg-indigo-100 px-4 py-2 rounded-xl text-xs font-bold transition-colors gap-2 whitespace-nowrap"
                                >
                                    <Wallet :size="14" />
                                    Pagar / Ver
                                </Link>
                            </td>
                        </tr>
                    </tbody>
                </table>
                
                <div v-if="loans.length === 0" class="p-16 text-center">
                    <div class="mx-auto w-16 h-16 bg-slate-50 flex items-center justify-center rounded-full mb-4 border border-slate-100">
                        <Search class="text-slate-400" :size="24" />
                    </div>
                    <h3 class="text-lg font-bold text-slate-900 mb-1">Sin Resultados</h3>
                    <p class="text-slate-500 font-medium">No se encontraron préstamos que coincidan con los criterios de búsqueda.</p>
                    <button 
                        v-if="form.search || form.status !== 'all'"
                        @click="form.search = ''; form.status = 'all';"
                        class="mt-6 px-6 py-2.5 bg-slate-100 text-slate-600 hover:bg-slate-200 hover:text-slate-900 rounded-xl font-bold transition-colors text-sm"
                    >
                        Limpiar Filtros
                    </button>
                </div>
            </div>
        </div>
    </AppLayout>
</template>
