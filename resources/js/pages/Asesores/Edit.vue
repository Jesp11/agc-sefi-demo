<script setup lang="ts">
import { Head, Link, useForm } from '@inertiajs/vue3';
import { ArrowLeft, Save, UserCheck } from 'lucide-vue-next';
import { dashboard as dashboardRoute } from '@/routes';
import asesorRoutes from '@/routes/asesores';
import AppLayout from '@/layouts/AppLayout.vue';
import type { BreadcrumbItem } from '@/types';

const props = defineProps<{
    asesor: {
        id: number;
        nombre: string;
    }
}>();

const form = useForm({
    nombre: props.asesor.nombre,
});

const breadcrumbs: BreadcrumbItem[] = [
    { title: 'Dashboard', href: dashboardRoute().url },
    { title: 'Asesores', href: asesorRoutes.index().url },
    { title: props.asesor.nombre, href: '#' },
];

const submit = () => {
    form.put(asesorRoutes.update(props.asesor.id).url);
};
</script>

<template>
    <Head :title="`Editar - ${asesor.nombre}`" />

    <AppLayout :breadcrumbs="breadcrumbs">
        <div class="max-w-4xl mx-auto p-8 lg:p-12">
            <!-- Header -->
            <header class="flex items-center justify-between gap-8 mb-12">
                <div class="flex items-center gap-6">
                    <Link 
                        :href="asesorRoutes.index().url" 
                        class="p-2.5 bg-white border border-slate-200 rounded-xl text-slate-400 hover:text-slate-900 transition-all shadow-sm active:scale-95"
                    >
                        <ArrowLeft :size="20" />
                    </Link>
                    <div>
                        <h1 class="text-3xl font-bold text-slate-900 tracking-tight">Editar Asesor</h1>
                        <p class="text-sm text-slate-500 font-medium mt-1">Actualice la información personal del asesor responsable.</p>
                    </div>
                </div>
            </header>

            <!-- Formulario -->
            <div class="bg-white border border-slate-100 rounded-xl shadow-sm p-8 lg:p-10">
                <form @submit.prevent="submit" class="space-y-8">
                    <div class="space-y-6">
                        <div class="flex items-center gap-3 border-b border-slate-50 pb-4">
                            <div class="p-2 bg-blue-50 text-blue-700 rounded-lg">
                                <UserCheck :size="18" />
                            </div>
                            <h2 class="text-sm font-bold text-slate-900 uppercase tracking-tight">Información Personal</h2>
                        </div>

                        <div class="max-w-xl space-y-2">
                            <label class="text-[10px] font-bold text-slate-400 uppercase tracking-widest ml-1">Nombre Completo</label>
                            <input 
                                v-model="form.nombre" 
                                type="text" 
                                placeholder="Nombre completo del asesor"
                                class="w-full px-5 py-3.5 bg-slate-50 border border-slate-200 rounded-xl outline-none focus:border-slate-900 focus:bg-white transition-all text-sm font-bold text-slate-900" 
                                :class="{ 'border-rose-500 ring-rose-500/20': form.errors.nombre }"
                            />
                            <div v-if="form.errors.nombre" class="text-xs text-rose-500 font-bold mt-1 pl-1">{{ form.errors.nombre }}</div>
                        </div>
                    </div>

                    <div class="pt-6 flex justify-end border-t border-slate-50">
                        <button 
                            type="submit"
                            :disabled="form.processing"
                            class="px-8 py-3.5 bg-slate-900 text-white font-bold rounded-xl hover:bg-slate-800 transition-all text-xs shadow-lg shadow-slate-900/10 active:scale-95 disabled:opacity-50 flex items-center gap-3"
                        >
                            <Save :size="18" />
                            {{ form.processing ? 'Actualizando...' : 'Guardar Cambios' }}
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </AppLayout>
</template>
