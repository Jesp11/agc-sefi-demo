<script setup lang="ts">
import { Head, useForm, Link } from '@inertiajs/vue3';
import { ArrowLeft, Save, ChevronDown } from 'lucide-vue-next';
import { dashboard as dashboardRoute } from '@/routes';
import AppLayout from '@/layouts/AppLayout.vue';
import SearchableSelect from '@/components/SearchableSelect.vue';
import type { BreadcrumbItem } from '@/types';

interface Asesor {
    id: number;
    nombre: string;
}

interface Grupo {
    id_grupo: number;
    nombre: string;
    id_asesor: number;
}

const props = defineProps<{
    grupo: Grupo;
    asesores: Asesor[];
}>();

const breadcrumbs: BreadcrumbItem[] = [
    { title: 'Dashboard', href: dashboardRoute().url },
    { title: 'Grupos', href: '/grupos' },
    { title: 'Editar Grupo', href: '#' },
];

const form = useForm({
    nombre: props.grupo.nombre,
    id_asesor: props.grupo.id_asesor,
});

const submit = () => {
    form.put(`/grupos/${props.grupo.id_grupo}`, {
        onSuccess: () => {},
    });
};
</script>

<template>
    <Head title="Editar Grupo" />

    <AppLayout :breadcrumbs="breadcrumbs">
        <div class="max-w-4xl mx-auto p-8 lg:p-12">
            
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
                        <h1 class="text-3xl font-bold text-slate-900 tracking-tight">Editar Grupo</h1>
                        <p class="text-sm text-slate-500 font-medium mt-1">Actualice los detalles del grupo y el asesor asignado.</p>
                    </div>
                </div>
            </header>

            <!-- Formulario -->
            <div class="bg-white border border-slate-100 rounded-xl shadow-sm p-8 lg:p-10">
                <form @submit.prevent="submit" class="space-y-8">
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-8">
                        <!-- Nombre del Grupo -->
                        <div class="space-y-2">
                            <label class="text-[10px] font-bold text-slate-400 uppercase tracking-widest ml-1">
                                Nombre del Grupo
                            </label>
                            <input 
                                v-model="form.nombre"
                                type="text"
                                placeholder="Ej. Las Flores"
                                class="w-full px-5 py-3.5 bg-slate-50 border border-slate-200 rounded-xl outline-none focus:border-slate-900 focus:bg-white transition-all text-sm font-bold text-slate-900"
                                :class="{ 'border-rose-500 ring-rose-500/20': form.errors.nombre }"
                            />
                            <p v-if="form.errors.nombre" class="text-[10px] text-rose-500 font-bold mt-1 ml-1">{{ form.errors.nombre }}</p>
                        </div>

                        <div class="space-y-2">
                            <label class="text-[10px] font-bold text-slate-400 uppercase tracking-widest ml-1">
                                Asesor Responsable
                            </label>
                            <SearchableSelect 
                                v-model="form.id_asesor"
                                :options="asesores.map(a => ({ id: a.id, label: a.nombre }))"
                                placeholder="Seleccione un asesor..."
                                search-placeholder="Buscar asesor..."
                                empty-text="No se encontraron asesores"
                            />
                            <p v-if="form.errors.id_asesor" class="text-[10px] text-rose-500 font-bold mt-1 ml-1">{{ form.errors.id_asesor }}</p>
                        </div>
                    </div>

                    <div class="pt-6 flex justify-end">
                        <button 
                            type="submit"
                            :disabled="form.processing"
                            class="px-8 py-3.5 bg-slate-900 text-white font-bold rounded-xl hover:bg-slate-800 transition-all text-xs shadow-lg shadow-slate-900/10 active:scale-95 disabled:opacity-50 flex items-center gap-3"
                        >
                            <Save :size="18" />
                            Actualizar Grupo
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </AppLayout>
</template>
