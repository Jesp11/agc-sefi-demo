<script setup lang="ts">
import { ref, computed } from 'vue';
import { Head, Link, router } from '@inertiajs/vue3';
import { 
    Users2, Search, Plus, 
    ChevronRight, Trash2, Edit2,
    User
} from 'lucide-vue-next';
import { dashboard as dashboardRoute } from '@/routes';
import AppLayout from '@/layouts/AppLayout.vue';
import type { BreadcrumbItem } from '@/types';

interface Grupo {
    id: number;
    nombre: string;
    asesor: string;
    clientes_count: number;
}

const props = defineProps<{
    grupos: Grupo[];
}>();

const breadcrumbs: BreadcrumbItem[] = [
    { title: 'Dashboard', href: dashboardRoute().url },
    { title: 'Grupos de Clientes', href: '#' },
];

const searchQuery = ref('');

const filteredGrupos = computed(() => {
    if (!searchQuery.value) return props.grupos;
    const query = searchQuery.value.toLowerCase();
    return props.grupos.filter(g => 
        g.nombre.toLowerCase().includes(query) || 
        g.asesor.toLowerCase().includes(query)
    );
});

const deleteGrupo = (id: number) => {
    if (confirm('¿Está seguro de eliminar este grupo?')) {
        router.delete(`/grupos/${id}`);
    }
};
</script>

<template>
    <Head title="Grupos de Clientes" />

    <AppLayout :breadcrumbs="breadcrumbs">
        <div class="w-full mx-auto p-8 lg:p-12">
            
            <!-- Header Premium -->
            <header class="flex items-center justify-between gap-8 mb-12">
                <div>
                    <h1 class="text-3xl font-bold text-slate-900 tracking-tight">Grupos de Clientes</h1>
                    <p class="text-sm text-slate-500 font-medium mt-1">Gestión de carteras grupales y asignación de asesores.</p>
                </div>
                
                <Link 
                    href="/grupos/create"
                    class="px-6 py-2.5 bg-slate-900 text-white font-bold rounded-lg hover:bg-slate-800 transition-all text-xs shadow-sm active:scale-95 flex items-center gap-2"
                >
                    <Plus :size="16" />
                    Nuevo Grupo
                </Link>
            </header>

            <!-- Search Bar -->
            <div class="relative mb-10">
                <Search class="absolute left-4 top-1/2 -translate-y-1/2 text-slate-400" :size="20" />
                <input 
                    v-model="searchQuery"
                    type="text" 
                    placeholder="Buscar por nombre de grupo o asesor..."
                    class="w-full pl-12 pr-6 py-4 bg-white border border-slate-200 rounded-xl outline-none focus:border-slate-900 transition-all text-sm font-medium text-slate-900 shadow-sm"
                />
            </div>

            <!-- Tabla de Grupos -->
            <div class="bg-white border border-slate-100 rounded-xl shadow-sm overflow-hidden">
                <table class="w-full text-left border-collapse">
                    <thead>
                        <tr class="bg-slate-50 border-b border-slate-100">
                            <th class="w-20 px-6 py-4 text-[10px] font-bold text-slate-400 uppercase tracking-widest text-center">ID</th>
                            <th class="px-6 py-4 text-[10px] font-bold text-slate-400 uppercase tracking-widest">Nombre del Grupo</th>
                            <th class="px-6 py-4 text-[10px] font-bold text-slate-400 uppercase tracking-widest">Asesor Asignado</th>
                            <th class="w-32 px-6 py-4 text-[10px] font-bold text-slate-400 uppercase tracking-widest text-center">Integrantes</th>
                            <th class="w-48 px-6 py-4 text-[10px] font-bold text-slate-400 uppercase tracking-widest text-right">Acciones</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-50">
                        <tr v-for="grupo in filteredGrupos" :key="grupo.id" class="hover:bg-slate-50/50 transition-colors group">
                            <td class="px-6 py-5 text-center">
                                <span class="text-xs font-bold text-slate-300 font-mono">#{{ grupo.id }}</span>
                            </td>
                            <td class="px-6 py-5">
                                <div class="flex flex-col">
                                    <span class="text-sm font-bold text-slate-900 group-hover:text-blue-600 transition-colors uppercase tracking-tight">{{ grupo.nombre }}</span>
                                    <span class="text-[10px] text-slate-400 font-bold tracking-tight mt-0.5 uppercase">ID Grupo #{{ grupo.id }}</span>
                                </div>
                            </td>
                            <td class="px-6 py-5">
                                <div class="flex items-center gap-2.5">
                                    <div class="p-1.5 bg-slate-100 text-slate-500 rounded-lg group-hover:bg-white group-hover:text-slate-900 transition-colors">
                                        <User :size="14" />
                                    </div>
                                    <span class="text-sm font-bold text-slate-600 group-hover:text-slate-900 transition-colors">{{ grupo.asesor }}</span>
                                </div>
                            </td>
                            <td class="px-6 py-5 text-center">
                                <span class="px-3 py-1 bg-blue-50 text-blue-700 text-[10px] font-bold rounded-full uppercase tracking-wider border border-blue-100">
                                    {{ grupo.clientes_count }} miembros
                                </span>
                            </td>
                            <td class="px-6 py-5 text-right">
                                <div class="flex items-center justify-end gap-2">
                                    <Link 
                                        :href="`/grupos/${grupo.id}/edit`"
                                        class="p-2 text-slate-400 hover:text-slate-900 hover:bg-slate-100 rounded-lg transition-all"
                                        title="Editar Grupo"
                                    >
                                        <Edit2 :size="16" />
                                    </Link>
                                    <button 
                                        @click="deleteGrupo(grupo.id)"
                                        class="p-2 text-slate-400 hover:text-rose-600 hover:bg-rose-50 rounded-lg transition-all"
                                        title="Eliminar Grupo"
                                    >
                                        <Trash2 :size="16" />
                                    </button>
                                    <Link 
                                        :href="`/grupos/${grupo.id}`"
                                        class="px-4 py-2 bg-slate-900 text-white text-[10px] font-bold uppercase tracking-widest rounded-lg hover:bg-slate-800 transition-all shadow-sm"
                                        title="Ver Detalle"
                                    >
                                        Ver Detalle
                                    </Link>
                                </div>
                            </td>
                        </tr>
                        <tr v-if="filteredGrupos.length === 0">
                            <td colspan="5" class="py-32 text-center">
                                <div class="max-w-xs mx-auto text-slate-400">
                                    <Users2 :size="48" class="mx-auto mb-4 opacity-10" />
                                    <p class="text-sm font-bold tracking-wider text-slate-900">No hay grupos</p>
                                    <p class="text-xs mt-2 text-slate-500 font-medium leading-relaxed">No se encontraron grupos que coincidan con la búsqueda.</p>
                                </div>
                            </td>
                        </tr>
                    </tbody>
                </table>
            </div>
        </div>
    </AppLayout>
</template>
