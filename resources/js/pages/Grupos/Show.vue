<script setup lang="ts">
import { ref, computed } from 'vue';
import { Head, Link, useForm } from '@inertiajs/vue3';
import { 
    Users2, ArrowLeft, UserPlus, Trash2, 
    Search, User, CreditCard, ChevronRight
} from 'lucide-vue-next';
import { dashboard as dashboardRoute } from '@/routes';
import AppLayout from '@/layouts/AppLayout.vue';
import type { BreadcrumbItem } from '@/types';

interface Member {
    id: number;
    nombre: string;
    creditos_count: number;
}

interface AvailableClient {
    id: number;
    nombre: string;
}

interface Grupo {
    id: number;
    nombre: string;
    asesor: string;
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

const addClient = (clientId: number) => {
    form.id_cliente = clientId;
    form.post(`/grupos/${props.grupo.id}/add-client`, {
        onSuccess: () => {
            searchQuery.value = '';
            showAddMember.value = false;
        }
    });
};

const removeClient = (clientId: number) => {
    if (confirm('¿Está seguro de remover a este cliente del grupo? Volverá a la cartera individual.')) {
        useForm({}).delete(`/grupos/${props.grupo.id}/remove-client/${clientId}`);
    }
};
</script>

<template>
    <Head :title="`Grupo: ${grupo.nombre}`" />

    <AppLayout :breadcrumbs="breadcrumbs">
        <div class="w-full mx-auto p-8 lg:p-12">
            
            <!-- Header -->
            <header class="flex items-center justify-between gap-8 mb-12">
                <div class="flex items-center gap-6">
                    <Link 
                        href="/grupos"
                        class="p-2.5 bg-white border border-slate-200 rounded-xl text-slate-400 hover:text-slate-900 transition-all shadow-sm active:scale-95"
                    >
                        <ArrowLeft :size="20" />
                    </Link>
                    <div>
                        <h1 class="text-3xl font-bold text-slate-900 tracking-tight">{{ grupo.nombre }}</h1>
                        <p class="text-sm text-slate-500 font-medium mt-1 flex items-center gap-2">
                            <User :size="14" class="text-slate-300" />
                            Asesor Asignado: <span class="text-slate-900 font-bold">{{ grupo.asesor }}</span>
                        </p>
                    </div>
                </div>

                <Link 
                    :href="`/customers/create?id_grupo=${grupo.id}`"
                    class="px-6 py-2.5 bg-slate-900 text-white font-bold rounded-lg hover:bg-slate-800 transition-all text-xs shadow-sm active:scale-95 flex items-center gap-2"
                >
                    <UserPlus :size="16" />
                    Añadir Integrante
                </Link>
            </header>

            <!-- Integrantes Table -->
            <div class="bg-white border border-slate-100 rounded-xl shadow-sm overflow-hidden min-h-[400px]">
                <table class="w-full text-left border-collapse">
                    <thead>
                        <tr class="bg-slate-50/50 border-b border-slate-100">
                            <th class="px-6 py-4 text-[10px] font-bold text-slate-400 uppercase tracking-widest">Integrante del Grupo</th>
                            <th class="w-48 px-6 py-4 text-[10px] font-bold text-slate-400 uppercase tracking-widest text-center">Créditos Activos</th>
                            <th class="w-32 px-6 py-4 text-[10px] font-bold text-slate-400 uppercase tracking-widest text-right">Acciones</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-50">
                        <tr v-for="member in grupo.clientes" :key="member.id" class="hover:bg-slate-50/50 transition-colors group">
                            <td class="px-6 py-5">
                                <div class="flex items-center gap-4">
                                    <div class="w-10 h-10 bg-slate-50 rounded-full flex items-center justify-center text-slate-400 group-hover:bg-slate-900 group-hover:text-white transition-all duration-300 shadow-sm border border-slate-100">
                                        <User :size="18" />
                                    </div>
                                    <div class="flex flex-col">
                                        <span class="text-sm font-bold text-slate-900">{{ member.nombre }}</span>
                                        <span class="text-[10px] text-slate-400 font-mono font-bold tracking-tight">ID #{{ member.id }}</span>
                                    </div>
                                </div>
                            </td>
                            <td class="px-6 py-5 text-center">
                                <span class="px-2.5 py-1 bg-blue-50 text-blue-700 text-[10px] font-bold rounded-md uppercase tracking-wider">
                                    {{ member.creditos_count }} créditos
                                </span>
                            </td>
                            <td class="px-6 py-5 text-right">
                                <div class="flex items-center justify-end gap-2">
                                    <Link 
                                        :href="`/customers/${member.id}`"
                                        class="p-2 text-slate-400 hover:text-slate-900 hover:bg-slate-100 rounded-lg transition-all"
                                        title="Ver expediente"
                                    >
                                        <ChevronRight :size="18" />
                                    </Link>
                                    <button 
                                        @click="removeClient(member.id)"
                                        class="p-2 text-slate-400 hover:text-rose-600 hover:bg-rose-50 rounded-lg transition-all"
                                        title="Remover del grupo"
                                    >
                                        <Trash2 :size="18" />
                                    </button>
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
