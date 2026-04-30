<script setup lang="ts">
import AppLayout from '@/layouts/AppLayout.vue';
import { Head, Link } from '@inertiajs/vue3';
import { Users, CreditCard, Wallet, BarChart3, TrendingUp, AlertCircle, Calendar } from 'lucide-vue-next';
import customerRoutes from '@/routes/customers';
import loanRoutes from '@/routes/loans';
import { dashboard as dashboardRoute } from '@/routes';
import reportRoutes from '@/routes/reports';

defineProps<{
    metrics: {
        total_customers: number;
        active_loans: number;
        total_portfolio: number;
        monthly_collection: number;
        overdue_amount: number;
        upcoming_amount: number;
    }
}>();

const formatCurrency = (value: number) => {
    return new Intl.NumberFormat('es-MX', {
        style: 'currency',
        currency: 'MXN',
    }).format(value);
};

const breadcrumbs = [
    { title: 'Panel General', href: dashboardRoute().url },
];
</script>

<template>
    <Head title="Panel General" />

    <AppLayout :breadcrumbs="breadcrumbs">
        <div class="p-8 max-w-[1600px] mx-auto min-h-[calc(100vh-100px)] flex flex-col justify-center">
            <header class="mb-10 text-center">
                <h1 class="text-4xl font-black text-slate-900 tracking-tight">Panel General</h1>
                <p class="text-slate-500 text-lg">Resumen operativo del sistema de préstamos</p>
            </header>

            <div class="grid grid-cols-2 lg:grid-cols-3 gap-6 mb-12">
                <!-- Total Customers -->
                <div class="bg-white p-6 rounded-3xl shadow-sm border border-slate-100 flex items-center space-x-4">
                    <div class="p-3 bg-teal-50 text-teal-600 rounded-2xl">
                        <Users :size="24" />
                    </div>
                    <div>
                        <p class="text-xs font-bold text-slate-400 uppercase tracking-widest">Clientes</p>
                        <p class="text-2xl font-black text-slate-900">{{ metrics.total_customers }}</p>
                    </div>
                </div>

                <!-- Active Loans -->
                <div class="bg-white p-6 rounded-3xl shadow-sm border border-slate-100 flex items-center space-x-4">
                    <div class="p-3 bg-blue-50 text-blue-600 rounded-2xl">
                        <CreditCard :size="24" />
                    </div>
                    <div>
                        <p class="text-xs font-bold text-slate-400 uppercase tracking-widest">Préstamos</p>
                        <p class="text-2xl font-black text-slate-900">{{ metrics.active_loans }}</p>
                    </div>
                </div>

                <!-- Total Portfolio -->
                <div class="bg-white p-6 rounded-3xl shadow-sm border border-slate-100 flex items-center space-x-4">
                    <div class="p-3 bg-indigo-50 text-indigo-600 rounded-2xl">
                        <Wallet :size="24" />
                    </div>
                    <div>
                        <p class="text-xs font-bold text-slate-400 uppercase tracking-widest">Cartera</p>
                        <p class="text-2xl font-black text-slate-900">{{ formatCurrency(metrics.total_portfolio) }}</p>
                    </div>
                </div>

                <!-- Monthly Collection -->
                <div class="bg-white p-6 rounded-3xl shadow-sm border border-slate-100 flex items-center space-x-4">
                    <div class="p-3 bg-emerald-50 text-emerald-600 rounded-2xl">
                        <TrendingUp :size="24" />
                    </div>
                    <div>
                        <p class="text-xs font-bold text-slate-400 uppercase tracking-widest">Cobranza Mes</p>
                        <p class="text-2xl font-black text-slate-900">{{ formatCurrency(metrics.monthly_collection) }}</p>
                    </div>
                </div>

                <!-- Overdue Portfolio -->
                <div class="bg-white p-6 rounded-3xl shadow-sm border border-slate-100 flex items-center space-x-4">
                    <div class="p-3 bg-rose-50 text-rose-600 rounded-2xl">
                        <AlertCircle :size="24" />
                    </div>
                    <div>
                        <p class="text-xs font-bold text-slate-400 uppercase tracking-widest">En Mora</p>
                        <p class="text-2xl font-black text-rose-600">{{ formatCurrency(metrics.overdue_amount) }}</p>
                    </div>
                </div>

                <!-- Upcoming Maturities -->
                <div class="bg-white p-6 rounded-3xl shadow-sm border border-slate-100 flex items-center space-x-4">
                    <div class="p-3 bg-amber-50 text-amber-600 rounded-2xl">
                        <Calendar :size="24" />
                    </div>
                    <div>
                        <p class="text-xs font-bold text-slate-400 uppercase tracking-widest">Próx. 7 días</p>
                        <p class="text-2xl font-black text-slate-900">{{ formatCurrency(metrics.upcoming_amount) }}</p>
                    </div>
                </div>
            </div>

            <section class="mt-8">
                <h2 class="text-sm font-bold text-slate-400 uppercase tracking-[0.3em] mb-8 text-center">Accesos Rápidos</h2>
                <div class="grid grid-cols-2 lg:grid-cols-5 gap-8">
                    <Link 
                        :href="loanRoutes.index().url"
                        class="flex flex-col items-center justify-center p-6 bg-white rounded-3xl border border-slate-100 shadow-sm hover:shadow-lg hover:shadow-amber-100/50 hover:border-amber-200 transition-all group"
                    >
                        <div class="p-4 bg-amber-50 text-amber-600 rounded-2xl group-hover:scale-110 group-hover:bg-amber-600 group-hover:text-white transition-all mb-4">
                            <Wallet :size="32" stroke-width="1.5" />
                        </div>
                        <span class="text-sm font-bold text-slate-700 group-hover:text-amber-700 transition-colors text-center">Pagar Cuotas</span>
                    </Link>

                    <Link 
                        :href="customerRoutes.index().url"
                        class="flex flex-col items-center justify-center p-6 bg-white rounded-3xl border border-slate-100 shadow-sm hover:shadow-lg hover:shadow-teal-100/50 hover:border-teal-200 transition-all group"
                    >
                        <div class="p-4 bg-teal-50 text-teal-600 rounded-2xl group-hover:scale-110 group-hover:bg-teal-600 group-hover:text-white transition-all mb-4">
                            <Users :size="32" stroke-width="1.5" />
                        </div>
                        <span class="text-sm font-bold text-slate-700 group-hover:text-teal-700 transition-colors text-center">Clientes</span>
                    </Link>

                    <Link 
                        :href="loanRoutes.create().url"
                        class="flex flex-col items-center justify-center p-6 bg-white rounded-3xl border border-slate-100 shadow-sm hover:shadow-lg hover:shadow-blue-100/50 hover:border-blue-200 transition-all group"
                    >
                        <div class="p-4 bg-blue-50 text-blue-600 rounded-2xl group-hover:scale-110 group-hover:bg-blue-600 group-hover:text-white transition-all mb-4">
                            <CreditCard :size="32" stroke-width="1.5" />
                        </div>
                        <span class="text-sm font-bold text-slate-700 group-hover:text-blue-700 transition-colors text-center">Nuevo Préstamo</span>
                    </Link>

                    <Link 
                        :href="reportRoutes.upcoming().url"
                        class="flex flex-col items-center justify-center p-6 bg-white rounded-3xl border border-slate-100 shadow-sm hover:shadow-lg hover:shadow-fuchsia-100/50 hover:border-fuchsia-200 transition-all group"
                    >
                        <div class="p-4 bg-fuchsia-50 text-fuchsia-600 rounded-2xl group-hover:scale-110 group-hover:bg-fuchsia-600 group-hover:text-white transition-all mb-4">
                            <Calendar :size="32" stroke-width="1.5" />
                        </div>
                        <span class="text-sm font-bold text-slate-700 group-hover:text-fuchsia-700 transition-colors text-center">Pagos Mañana</span>
                    </Link>

                    <Link 
                        :href="reportRoutes.index().url"
                        class="flex flex-col items-center justify-center p-6 bg-white rounded-3xl border border-slate-100 shadow-sm hover:shadow-lg hover:shadow-indigo-100/50 hover:border-indigo-200 transition-all group"
                    >
                        <div class="p-4 bg-indigo-50 text-indigo-600 rounded-2xl group-hover:scale-110 group-hover:bg-indigo-600 group-hover:text-white transition-all mb-4">
                            <BarChart3 :size="32" stroke-width="1.5" />
                        </div>
                        <span class="text-sm font-bold text-slate-700 group-hover:text-indigo-700 transition-colors text-center">Métricas</span>
                    </Link>
                </div>
            </section>
        </div>
    </AppLayout>
</template>
