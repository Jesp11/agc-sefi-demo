<script setup lang="ts">
import { Head, Link } from '@inertiajs/vue3';
import { ArrowLeft, Download, Wallet, CreditCard, ChevronRight, CheckCircle2 } from 'lucide-vue-next';
import AppLayout from '@/layouts/AppLayout.vue';
import { dashboard as dashboardRoute } from '@/routes';
import type { BreadcrumbItem } from '@/types';
import reportsRoutes from '@/routes/reports';
import loanRoutes from '@/routes/loans';

defineProps<{
    upcoming_installments: Array<{
        id: number;
        customer: string;
        loan_id: number;
        amount: number;
        installment_number: number;
        due_date: string;
    }>;
    target_date: string;
}>();

const formatCurrency = (value: number) => {
    return new Intl.NumberFormat('es-MX', {
        style: 'currency',
        currency: 'MXN',
    }).format(value);
};

const breadcrumbs: BreadcrumbItem[] = [
    { title: 'Dashboard', href: dashboardRoute().url },
    { title: 'Métricas', href: reportsRoutes.index().url },
    { title: 'Pagos Mañana', href: reportsRoutes.upcoming().url },
];
</script>

<template>
    <Head title="Pagos Proyectados" />

    <AppLayout :breadcrumbs="breadcrumbs">
        <div class="p-6 max-w-7xl mx-auto">
            <header class="mb-8 flex flex-col md:flex-row md:items-end md:justify-between gap-4">
                <div>
                    <Link 
                        :href="reportsRoutes.index().url"
                        class="inline-flex items-center text-sm font-bold text-slate-500 hover:text-slate-900 transition-colors mb-4"
                    >
                        <ArrowLeft :size="16" class="mr-2" />
                        Volver a Reportes
                    </Link>
                    <h1 class="text-3xl font-bold text-slate-900 tracking-tight">Pagos Esperados: {{ target_date }}</h1>
                    <p class="text-slate-500 mt-1">Lista detallada de clientes con cobro pendiente agendado para mañana.</p>
                </div>
                
                <a 
                    href="/reports/upcoming/export-pdf"
                    target="_blank"
                    class="inline-flex items-center px-6 py-3 bg-slate-900 text-white font-bold rounded-2xl hover:bg-slate-800 transition-all gap-2 shadow-lg shadow-slate-200"
                >
                    <Download :size="18" />
                    Exportar Lista a PDF
                </a>
            </header>

            <div class="bg-white rounded-3xl border border-slate-100 shadow-sm overflow-hidden">
                <div v-if="upcoming_installments && upcoming_installments.length > 0">
                    <table class="w-full text-left border-collapse">
                        <thead class="bg-slate-50 border-b border-slate-100">
                            <tr>
                                <th class="px-8 py-5 text-xs font-bold text-slate-500 uppercase tracking-widest">Cliente</th>
                                <th class="px-8 py-5 text-xs font-bold text-slate-500 uppercase tracking-widest text-center">Folio Crédito</th>
                                <th class="px-8 py-5 text-xs font-bold text-slate-500 uppercase tracking-widest text-center">Cuota No.</th>
                                <th class="px-8 py-5 text-xs font-bold text-slate-500 uppercase tracking-widest text-right">Monto a Cobrar</th>
                                <th class="px-8 py-5 text-xs font-bold text-slate-500 uppercase tracking-widest text-right">Ir a Préstamo</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-slate-50">
                            <tr v-for="installment in upcoming_installments" :key="installment.id" class="hover:bg-slate-50 group transition-colors">
                                <td class="px-8 py-5">
                                    <div class="font-bold text-slate-900 text-base">{{ installment.customer }}</div>
                                </td>
                                <td class="px-8 py-5 text-center font-bold text-slate-400 font-mono">
                                    #{{ String(installment.loan_id).padStart(5, '0') }}
                                </td>
                                <td class="px-8 py-5 text-center">
                                    <span class="inline-flex items-center justify-center w-8 h-8 rounded-full bg-slate-100 text-slate-700 text-sm font-bold border border-slate-200/60">
                                        {{ installment.installment_number }}
                                    </span>
                                </td>
                                <td class="px-8 py-5 text-right">
                                    <span class="font-black text-indigo-700 text-lg">{{ formatCurrency(installment.amount) }}</span>
                                </td>
                                <td class="px-8 py-5 text-right">
                                    <Link 
                                        :href="loanRoutes.show({ loan: installment.loan_id }).url"
                                        class="inline-flex items-center justify-center w-10 h-10 rounded-2xl bg-slate-100 text-slate-500 hover:bg-indigo-600 hover:text-white transition-all shadow-sm group-hover:shadow-md"
                                        title="Ver Detalles del Préstamo"
                                    >
                                        <ChevronRight :size="20" />
                                    </Link>
                                </td>
                            </tr>
                        </tbody>
                    </table>
                </div>

                <div v-else class="text-center py-20 px-8">
                    <div class="mx-auto w-20 h-20 bg-emerald-50 rounded-full flex items-center justify-center mb-6 border border-emerald-100">
                        <CheckCircle2 :size="32" class="text-emerald-500" />
                    </div>
                    <h3 class="text-2xl font-bold text-slate-900 mb-2">Excelente, sin cobros agendados</h3>
                    <p class="text-slate-500 font-medium text-lg max-w-md mx-auto">No tienes cuotas pendientes proyectadas para cobrar el día de mañana.</p>
                </div>
            </div>
        </div>
    </AppLayout>
</template>
