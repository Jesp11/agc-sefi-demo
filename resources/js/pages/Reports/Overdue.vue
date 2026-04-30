<script setup lang="ts">
import { Head, Link } from '@inertiajs/vue3';
import { dashboard as dashboardRoute } from '@/routes';
import reportRoutes from '@/routes/reports';
import loanRoutes from '@/routes/loans';
import AppLayout from '@/layouts/AppLayout.vue';
import type { BreadcrumbItem } from '@/types';
import { AlertCircle, ArrowLeft, Calendar, User, TrendingUp, Landmark, ShieldAlert, ArrowRight } from 'lucide-vue-next';

const props = defineProps<{
    installments: any[];
    total_amount: number;
}>();

const breadcrumbs: BreadcrumbItem[] = [
    { title: 'Dashboard', href: dashboardRoute().url },
    { title: 'Métricas', href: reportRoutes.index().url },
    { title: 'Cobranza en Mora', href: '/reports/overdue' },
];

const formatCurrency = (value: number) => {
    return new Intl.NumberFormat('es-MX', {
        style: 'currency',
        currency: 'MXN',
    }).format(value);
};

const formatDate = (dateString: string) => {
    const date = new Date(dateString + 'T00:00:00');
    return date.toLocaleDateString('es-MX', { day: 'numeric', month: 'long', year: 'numeric' });
};
</script>

<template>
    <Head title="Cobranza en Mora" />

    <AppLayout :breadcrumbs="breadcrumbs">
        <div class="p-8 max-w-[1200px] mx-auto pb-24">
            <header class="mb-8">
                <Link 
                    :href="reportRoutes.index().url"
                    class="inline-flex items-center text-sm font-bold text-slate-500 hover:text-slate-900 transition-colors mb-4"
                >
                    <ArrowLeft :size="16" class="mr-2" />
                    Volver a Métricas
                </Link>
                <div class="flex flex-col md:flex-row md:items-end justify-between gap-6">
                    <div>
                        <h1 class="text-3xl font-black text-slate-900 tracking-tight flex items-center gap-3">
                            <ShieldAlert class="text-rose-600" :size="32" stroke-width="2.5" />
                            Cobranza en Mora
                        </h1>
                        <p class="text-slate-500 mt-2 text-lg">Reporte detallado de cuotas atrasadas pendientes de cobro.</p>
                    </div>
                    <div class="bg-rose-50 px-6 py-4 rounded-2xl border border-rose-100 flex items-center gap-6">
                        <div>
                            <p class="text-[10px] font-bold text-rose-500 uppercase tracking-wider mb-1">Monto Total en Mora</p>
                            <p class="text-2xl font-black text-rose-700">{{ formatCurrency(total_amount) }}</p>
                        </div>
                        <div class="h-10 w-px bg-rose-200"></div>
                        <div>
                            <p class="text-[10px] font-bold text-rose-500 uppercase tracking-wider mb-1">Cuotas Atrasadas</p>
                            <p class="text-2xl font-black text-rose-700">{{ installments.length }}</p>
                        </div>
                    </div>
                </div>
            </header>

            <div class="bg-white rounded-[32px] border border-slate-100 shadow-sm overflow-hidden mb-8">
                <div class="overflow-x-auto">
                    <table class="w-full text-left border-collapse">
                        <thead>
                            <tr class="border-b border-slate-100 bg-slate-50/50">
                                <th class="py-5 px-6 font-bold text-slate-500 text-xs uppercase tracking-wider">Cliente / Préstamo</th>
                                <th class="py-5 px-6 font-bold text-slate-500 text-xs uppercase tracking-wider text-center">Cuota</th>
                                <th class="py-5 px-6 font-bold text-slate-500 text-xs uppercase tracking-wider">Fecha de Vencimiento</th>
                                <th class="py-5 px-6 font-bold text-slate-500 text-xs uppercase tracking-wider text-right">Monto</th>
                                <th class="py-5 px-6 font-bold text-slate-500 text-xs uppercase tracking-wider text-right">Acción</th>
                            </tr>
                        </thead>
                        <tbody v-if="installments.length > 0">
                            <tr 
                                v-for="inst in installments" 
                                :key="inst.id"
                                class="border-b border-slate-50 transition-colors hover:bg-slate-50"
                            >
                                <td class="py-4 px-6">
                                    <div class="font-bold text-slate-900 flex items-center gap-2">
                                        <User :size="16" class="text-slate-400" />
                                        {{ inst.loan.customer.name }}
                                    </div>
                                    <div class="text-xs text-slate-500 font-medium mt-1 flex items-center gap-1">
                                        <Landmark :size="12" /> Préstamo #{{ inst.loan_id }}
                                    </div>
                                </td>
                                <td class="py-4 px-6 text-center">
                                    <span class="inline-flex items-center justify-center w-8 h-8 rounded-full bg-slate-100 text-slate-700 font-bold text-sm">
                                        {{ inst.installment_number }}
                                    </span>
                                </td>
                                <td class="py-4 px-6 text-rose-600 font-semibold flex items-center gap-2">
                                    <Calendar :size="16" />
                                    {{ formatDate(inst.due_date) }}
                                    <span class="text-[10px] font-black bg-rose-100 text-rose-700 px-2 py-0.5 rounded-full uppercase ml-2">Vencida</span>
                                </td>
                                <td class="py-4 px-6 text-right font-black text-rose-600 text-lg">
                                    {{ formatCurrency(inst.amount) }}
                                </td>
                                <td class="py-4 px-6 text-right">
                                    <Link 
                                        :href="`/loans/${inst.loan_id}`"
                                        class="inline-flex items-center justify-center p-2 rounded-xl text-indigo-600 hover:bg-indigo-50 hover:text-indigo-700 font-bold transition-all text-sm group"
                                    >
                                        Ver Préstamo
                                        <ArrowRight :size="16" class="ml-1 group-hover:translate-x-1 transition-transform" />
                                    </Link>
                                </td>
                            </tr>
                        </tbody>
                        <tbody v-else>
                            <tr>
                                <td colspan="5" class="py-16 text-center">
                                    <div class="inline-flex items-center justify-center w-16 h-16 rounded-full bg-emerald-50 text-emerald-500 mb-4">
                                        <TrendingUp :size="32" stroke-width="1.5" />
                                    </div>
                                    <h3 class="text-xl font-bold text-slate-900 mb-2">¡Excelente Trabajo!</h3>
                                    <p class="text-slate-500 max-w-sm mx-auto">
                                        No hay ninguna cuota atrasada en todo el sistema en este momento.
                                    </p>
                                </td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </div>
            
        </div>
    </AppLayout>
</template>
