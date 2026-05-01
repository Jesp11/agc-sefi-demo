<script setup lang="ts">
import { ref, computed } from 'vue';
import { Head, Link, useForm } from '@inertiajs/vue3';
import { 
    Search, User, ArrowLeft, UserPlus, Users2,
    Phone, CheckCircle2, Calendar, FileText, Star,
    TrendingUp, ArrowDownCircle, ArrowUpCircle, Banknote
} from 'lucide-vue-next';
import { dashboard as dashboardRoute } from '@/routes';
import AppLayout from '@/layouts/AppLayout.vue';
import type { BreadcrumbItem } from '@/types';

interface Member {
    id_cliente: number;
    nombre: string;
    telefono: string;
    latest_loan?: {
        id: number;
        amount: number;
        interes: number;
        valor_ficha: number;
        total: number;
        pagado: number;
        completado: boolean;
        fecha: string;
        dia_pago: string;
        plazo: number;
        asesor: string;
    } | null;
}

interface AvailableClient {
    id: number;
    nombre: string;
}

interface Grupo {
    id: number;
    nombre: string;
    asesor: string;
    id_responsable?: number | null;
    clientes: Member[];
}

const props = defineProps<{
    grupo: Grupo;
    availableClients: AvailableClient[];
}>();

const breadcrumbs: BreadcrumbItem[] = [
    { title: 'Dashboard', href: dashboardRoute().url },
    { title: 'Grupos', href: '/grupos' },
    { title: props.grupo.nombre, href: '#' },
];

const searchQuery = ref('');
const showAddMember = ref(false);

const filteredAvailableClients = computed(() => {
    if (!searchQuery.value) return [];
    const query = searchQuery.value.toLowerCase();
    return props.availableClients.filter(c => 
        c.nombre.toLowerCase().includes(query)
    ).slice(0, 5);
});

const form = useForm({
    id_cliente: null as number | null,
});

const formatCurrency = (value: number) => {
    return new Intl.NumberFormat('es-MX', {
        style: 'currency',
        currency: 'MXN',
    }).format(value);
};

const addClient = (clientId: number) => {
    form.id_cliente = clientId;
    form.post(`/grupos/${props.grupo.id}/add-client`, {
        onSuccess: () => {
            searchQuery.value = '';
            showAddMember.value = false;
        }
    });
};

const groupStats = computed(() => {
    let prestado = 0;
    let recuperado = 0;
    let total = 0;
    let ganancia = 0;

    props.grupo.clientes.forEach(c => {
        if (c.latest_loan) {
            prestado += c.latest_loan.amount || 0;
            total += c.latest_loan.total || 0;
            recuperado += c.latest_loan.pagado || 0;
            ganancia += Math.max(0, (c.latest_loan.pagado || 0) - (c.latest_loan.amount || 0));
        }
    });

    return {
        prestado,
        recuperado,
        porRecuperar: Math.max(0, total - recuperado),
        ganancia
    };
});

const hasActiveLoan = computed(() => {
    return props.grupo.clientes.some(c => c.latest_loan && !c.latest_loan.completado);
});

</script>

<template>
    <Head :title="`Grupo: ${grupo.nombre}`" />

    <AppLayout :breadcrumbs="breadcrumbs">
        <div class="w-full mx-auto p-8 lg:p-12">
            
            <!-- Professional Header -->
            <header class="flex flex-col md:flex-row md:items-end justify-between gap-6 mb-16 pb-8 border-b border-slate-200">
                <div class="flex items-start gap-6">
                    <Link 
                        href="/grupos"
                        class="mt-2 p-2.5 bg-white border border-slate-200 rounded-xl text-slate-400 hover:text-slate-900 transition-all shadow-sm active:scale-95"
                    >
                        <ArrowLeft :size="20" />
                    </Link>
                    <div>
                        <p class="text-[10px] font-bold uppercase tracking-widest text-slate-400 mb-1">Detalle de Grupo de Clientes</p>
                        <h1 class="text-4xl font-bold text-slate-900 tracking-tight">{{ grupo.nombre }}</h1>
                        <p class="text-sm text-slate-500 font-medium mt-1 flex items-center gap-2">
                            <User :size="14" class="text-slate-300" />
                            Asesor Asignado: <span class="text-slate-900 font-bold">{{ grupo.asesor }}</span>
                        </p>
                    </div>
                </div>

                <div class="flex items-center gap-3">
                    <button 
                        v-if="hasActiveLoan"
                        disabled
                        class="px-6 py-2.5 bg-slate-200 text-slate-400 font-bold rounded-lg cursor-not-allowed text-xs flex items-center gap-2 border border-slate-200"
                        title="El grupo ya tiene un préstamo activo"
                    >
                        <FileText :size="16" />
                        Préstamo Activo
                    </button>
                    <Link 
                        v-else
                        :href="`/grupos/${grupo.id}/create-loans`"
                        class="px-6 py-2.5 bg-emerald-600 text-white font-bold rounded-lg hover:bg-emerald-700 transition-all text-xs shadow-sm active:scale-95 flex items-center gap-2"
                        :class="{ 'opacity-50 pointer-events-none': grupo.clientes.length === 0 }"
                    >
                        <FileText :size="16" />
                        Crear Préstamos
                    </Link>
                    <Link 
                        :href="`/customers/create?id_grupo=${grupo.id}`"
                        class="px-6 py-2.5 bg-slate-900 text-white font-bold rounded-lg hover:bg-slate-800 transition-all text-xs shadow-sm active:scale-95 flex items-center gap-2"
                    >
                        <UserPlus :size="16" />
                        Añadir Integrante
                    </Link>
                </div>
            </header>

            <!-- Statistics Cards -->
            <div class="grid grid-cols-1 md:grid-cols-4 gap-6 mb-12">
                <div class="bg-white p-6 rounded-2xl border border-slate-100 shadow-sm hover:shadow-md transition-all">
                    <div class="flex items-center justify-between mb-4">
                        <div class="p-2 bg-blue-50 text-blue-600 rounded-lg">
                            <TrendingUp :size="18" />
                        </div>
                        <span class="text-[10px] font-bold text-slate-400 uppercase tracking-widest">Total Prestado</span>
                    </div>
                    <div class="text-2xl font-black text-blue-600">{{ formatCurrency(groupStats.prestado) }}</div>
                </div>

                <div class="bg-white p-6 rounded-2xl border border-slate-100 shadow-sm hover:shadow-md transition-all">
                    <div class="flex items-center justify-between mb-4">
                        <div class="p-2 bg-emerald-50 text-emerald-600 rounded-lg">
                            <ArrowDownCircle :size="18" />
                        </div>
                        <span class="text-[10px] font-bold text-slate-400 uppercase tracking-widest">Total Recuperado</span>
                    </div>
                    <div class="text-2xl font-black text-emerald-600">{{ formatCurrency(groupStats.recuperado) }}</div>
                </div>

                <div class="bg-white p-6 rounded-2xl border border-slate-100 shadow-sm hover:shadow-md transition-all">
                    <div class="flex items-center justify-between mb-4">
                        <div class="p-2 bg-amber-50 text-amber-600 rounded-lg">
                            <ArrowUpCircle :size="18" />
                        </div>
                        <span class="text-[10px] font-bold text-slate-400 uppercase tracking-widest">Por Recuperar</span>
                    </div>
                    <div class="text-2xl font-black text-amber-600">{{ formatCurrency(groupStats.porRecuperar) }}</div>
                </div>

                <div class="bg-white p-6 rounded-2xl border border-slate-100 shadow-sm hover:shadow-md transition-all">
                    <div class="flex items-center justify-between mb-4">
                        <div class="p-2 bg-purple-50 text-purple-600 rounded-lg">
                            <Banknote :size="18" />
                        </div>
                        <span class="text-[10px] font-bold text-slate-400 uppercase tracking-widest">Ganancia Real</span>
                    </div>
                    <div class="text-2xl font-black text-purple-600">{{ formatCurrency(groupStats.ganancia) }}</div>
                </div>
            </div>

            <!-- Integrantes Table (Igual a Cartera Individual) -->
            <div class="bg-white border border-slate-100 rounded-xl shadow-sm overflow-x-auto min-h-[500px]">
                <table class="w-full text-left border-collapse table-auto">
                    <thead>
                        <tr class="bg-slate-50/50 border-b border-slate-100">
                            <th class="w-20 px-6 py-4 text-[10px] font-bold text-slate-400 uppercase tracking-widest">ID</th>
                            <th class="px-6 py-4 text-[10px] font-bold text-slate-400 uppercase tracking-widest">Cliente</th>
                            <th class="w-32 px-6 py-4 text-[10px] font-bold text-slate-400 uppercase tracking-widest">Teléfono</th>
                            <th class="w-32 px-6 py-4 text-[10px] font-bold text-slate-400 uppercase tracking-widest text-center">Día Pago</th>
                            <th class="w-32 px-6 py-4 text-[10px] font-bold text-slate-400 uppercase tracking-widest text-right">V. Ficha</th>
                            <th class="w-20 px-6 py-4 text-[10px] font-bold text-slate-400 uppercase tracking-widest text-center">Plazos</th>
                            <th class="w-32 px-6 py-4 text-[10px] font-bold text-slate-400 uppercase tracking-widest text-right">Monto</th>
                            <th class="w-32 px-6 py-4 text-[10px] font-bold text-slate-400 uppercase tracking-widest text-right">Total</th>
                            <th class="px-6 py-4 text-[10px] font-bold text-slate-400 uppercase tracking-widest">Asesor</th>
                            <th class="w-32 px-6 py-4 text-[10px] font-bold text-slate-400 uppercase tracking-widest text-center">Estatus</th>
                            <th class="w-32 px-6 py-4 text-[10px] font-bold text-slate-400 uppercase tracking-widest text-right">Acciones</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-50">
                        <tr v-for="member in grupo.clientes" :key="member.id_cliente" class="hover:bg-slate-50/50 transition-colors group">
                            <td class="px-6 py-5">
                                <span class="text-xs font-bold text-slate-400 font-mono">#{{ member.id_cliente }}</span>
                            </td>
                            <td class="px-6 py-5">
                                <div class="text-sm font-bold text-slate-900 uppercase tracking-tight flex items-center gap-2">
                                    {{ member.nombre }}
                                    <span v-if="grupo.id_responsable === member.id_cliente" class="px-2 py-0.5 bg-amber-100 text-amber-700 text-[9px] font-black uppercase tracking-widest rounded flex items-center gap-1" title="Responsable del Grupo">
                                        <Star :size="10" />
                                        Resp
                                    </span>
                                </div>
                            </td>
                            <td class="px-6 py-5">
                                <div class="text-sm font-bold text-slate-900 flex items-center gap-2">
                                    <Phone :size="12" class="text-slate-300" />
                                    {{ member.telefono || '---' }}
                                </div>
                            </td>
                            
                            <!-- Loan Details (or empty state if no loan) -->
                            <template v-if="member.latest_loan">
                                <td class="px-6 py-5 text-center">
                                    <div class="inline-flex px-2 py-1 bg-slate-100 text-slate-600 text-[10px] font-black uppercase tracking-widest rounded-md">
                                        {{ member.latest_loan.dia_pago }}
                                    </div>
                                </td>
                                <td class="px-6 py-5 text-right">
                                    <div class="text-sm font-bold text-emerald-600">{{ formatCurrency(member.latest_loan.valor_ficha) }}</div>
                                </td>
                                <td class="px-6 py-5 text-center">
                                    <div class="text-sm font-black text-slate-900">{{ member.latest_loan.plazo }}</div>
                                </td>
                                <td class="px-6 py-5 text-right">
                                    <div class="text-xs font-bold text-slate-500">{{ formatCurrency(member.latest_loan.amount) }}</div>
                                </td>
                                <td class="px-6 py-5 text-right">
                                    <div class="text-sm font-black text-slate-900">{{ formatCurrency(member.latest_loan.total) }}</div>
                                </td>
                                <td class="px-6 py-5">
                                    <div class="text-xs font-bold text-slate-500 truncate">{{ member.latest_loan.asesor }}</div>
                                </td>
                                <td class="px-6 py-5 text-center">
                                    <span v-if="member.latest_loan.completado" class="px-2.5 py-1 bg-emerald-50 text-emerald-600 text-[9px] font-black uppercase tracking-widest rounded-full border border-emerald-100 shadow-sm inline-flex items-center gap-1">
                                        <CheckCircle2 :size="10" />
                                        Liquidado
                                    </span>
                                    <span v-else class="px-2.5 py-1 bg-amber-50 text-amber-600 text-[9px] font-black uppercase tracking-widest rounded-full border border-amber-100 shadow-sm inline-flex items-center gap-1">
                                        <Calendar :size="10" />
                                        Pendiente
                                    </span>
                                </td>
                            </template>
                            <template v-else>
                                <td colspan="7" class="px-6 py-5 text-center">
                                    <span class="text-[10px] font-bold text-slate-300 uppercase tracking-widest italic">Sin crédito activo</span>
                                </td>
                            </template>

                            <td class="px-6 py-5 text-right">
                                <div class="flex items-center justify-end gap-2">
                                    <Link 
                                        v-if="member.latest_loan"
                                        :href="`/loans/${member.latest_loan.id}`"
                                        class="px-4 py-2 bg-emerald-600 text-white text-[10px] font-bold uppercase tracking-widest rounded-lg hover:bg-emerald-700 transition-all shadow-sm"
                                    >
                                        Ver Préstamo
                                    </Link>
                                    <Link 
                                        :href="`/customers/${member.id_cliente}`"
                                        class="px-4 py-2 bg-slate-900 text-white text-[10px] font-bold uppercase tracking-widest rounded-lg hover:bg-slate-800 transition-all shadow-sm"
                                    >
                                        Ver Cliente
                                    </Link>
                                </div>
                            </td>
                        </tr>
                        <tr v-if="grupo.clientes.length === 0">
                            <td colspan="3" class="px-6 py-32 text-center">
                                <div class="max-w-xs mx-auto text-slate-400">
                                    <Users2 :size="48" class="mx-auto mb-4 opacity-10" />
                                    <p class="text-sm font-bold tracking-wider text-slate-900">Grupo sin integrantes</p>
                                    <p class="text-xs mt-2 text-slate-500 font-medium leading-relaxed">Este grupo aún no tiene clientes asignados. Usa el botón superior para añadir nuevos miembros.</p>
                                </div>
                            </td>
                        </tr>
                    </tbody>
                </table>
            </div>
        </div>
    </AppLayout>
</template>
