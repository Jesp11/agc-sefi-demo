<script setup lang="ts">
import { ref, computed } from 'vue';
import { Head, Link, router } from '@inertiajs/vue3';
import { 
    UserCheck, Plus, Edit2, Trash2, Search, Info
} from 'lucide-vue-next';
import { dashboard as dashboardRoute } from '@/routes';
import AppLayout from '@/layouts/AppLayout.vue';
import type { BreadcrumbItem } from '@/types';

interface Asesor {
    id: number;
    nombre: string;
    creditos_count: number;
}

const props = defineProps<{
    asesores: Asesor[];
}>();

const breadcrumbs: BreadcrumbItem[] = [
    { title: 'Dashboard', href: dashboardRoute().url },
    { title: 'Cuerpo de Asesores', href: '#' },
];

const searchQuery = ref('');

const filteredAsesores = computed(() => {
    if (!searchQuery.value) return props.asesores;
    const query = searchQuery.value.toLowerCase();
    return props.asesores.filter(a => a.nombre.toLowerCase().includes(query));
});

const deleteAsesor = (id: number) => {
    if (confirm('¿Está seguro de eliminar este asesor? Esta acción no se puede deshacer.')) {
        router.delete(`/asesores/${id}`);
    }
};
</script>

<template>
    <Head title="Asesores" />

    <AppLayout :breadcrumbs="breadcrumbs">
        <div class="w-full mx-auto p-8 lg:p-12">
            
            <!-- Header Minimalista -->
            <header class="flex items-center justify-between gap-8 mb-12">
                <div>
                    <h1 class="text-3xl font-bold text-slate-900 tracking-tight">Cuerpo de Asesores</h1>
                    <p class="text-sm text-slate-500 font-medium mt-1">Gestión del personal y fuerza de ventas.</p>
                </div>
                
                <Link 
                    href="/asesores/create"
                    class="px-6 py-2.5 bg-slate-900 text-white font-bold rounded-lg hover:bg-slate-800 transition-all text-xs shadow-sm active:scale-95 flex items-center gap-2"
                >
                    <Plus :size="16" />
                    Nuevo Asesor
                </Link>
            </header>

            <!-- Search Bar Profesional -->
            <div class="relative mb-10">
                <Search class="absolute left-4 top-1/2 -translate-y-1/2 text-slate-400" :size="20" />
                <input 
                    v-model="searchQuery"
                    type="text" 
                    placeholder="Búsqueda por nombre del asesor..."
                    class="w-full pl-12 pr-6 py-4 bg-white border border-slate-200 rounded-xl outline-none focus:border-slate-900 transition-all text-sm font-medium text-slate-900 shadow-sm"
                />
            </div>

            <!-- Tabla de Datos -->
            <div class="bg-white border border-slate-100 rounded-xl shadow-sm overflow-hidden min-h-[500px]">
                <table class="w-full text-left border-collapse table-fixed">
                    <thead>
                        <tr class="bg-slate-50/50 border-b border-slate-100">
                            <th class="w-20 px-6 py-4 text-[10px] font-bold text-slate-400 uppercase tracking-widest">ID</th>
                            <th class="px-6 py-4 text-[10px] font-bold text-slate-400 uppercase tracking-widest">Nombre Completo</th>
                            <th class="w-48 px-6 py-4 text-[10px] font-bold text-slate-400 uppercase tracking-widest text-center">Créditos</th>
                            <th class="w-48 px-6 py-4 text-[10px] font-bold text-slate-400 uppercase tracking-widest text-right">Acciones</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-50">
                        <tr v-for="asesor in filteredAsesores" :key="asesor.id" class="hover:bg-slate-50/50 transition-colors">
                            <td class="px-6 py-5">
                                <span class="text-xs font-bold text-slate-400 font-mono">#{{ asesor.id }}</span>
                            </td>
                            <td class="px-6 py-5">
                                <div class="text-sm font-bold text-slate-900">{{ asesor.nombre }}</div>
                            </td>
                            <td class="px-6 py-5 text-center">
                                <div class="text-sm font-bold text-slate-900">{{ asesor.creditos_count }}</div>
                            </td>
                             <td class="px-6 py-5 text-right">
                                <Link 
                                    :href="`/asesores/${asesor.id}`"
                                    class="px-4 py-2 bg-slate-900 text-white text-[10px] font-bold uppercase tracking-widest rounded-lg hover:bg-slate-800 transition-all shadow-sm"
                                >
                                    Ver Detalle
                                </Link>
                            </td>
                        </tr>
                        <tr v-if="filteredAsesores.length === 0">
                            <td colspan="4" class="py-20 text-center">
                                <p class="text-slate-400 font-bold italic text-sm tracking-wider">No se encontraron asesores.</p>
                            </td>
                        </tr>
                    </tbody>
                </table>
            </div>

            <!-- Nota Informativa -->
            <div class="mt-8 flex items-center gap-2 px-2">
                <Info :size="14" class="text-slate-300" />
                <p class="text-[10px] text-slate-400 font-bold uppercase tracking-widest leading-relaxed">
                    Los asesores con créditos activos no pueden ser eliminados para mantener el historial financiero.
                </p>
            </div>
        </div>
    </AppLayout>
</template>
