<script setup lang="ts">
import { Head, Link, useForm } from '@inertiajs/vue3';
import { ArrowLeft, Save, UserPlus } from 'lucide-vue-next';
import { dashboard as dashboardRoute } from '@/routes';
import asesorRoutes from '@/routes/asesores';
import AppLayout from '@/layouts/AppLayout.vue';
import type { BreadcrumbItem } from '@/types';

const form = useForm({
    nombre: '',
});

const breadcrumbs: BreadcrumbItem[] = [
    { title: 'Dashboard', href: dashboardRoute().url },
    { title: 'Asesores', href: asesorRoutes.index().url },
    { title: 'Nuevo Registro', href: '#' },
];

const submit = () => {
    form.post(asesorRoutes.store().url);
};
</script>

<template>
    <Head title="Nuevo Asesor" />

    <AppLayout :breadcrumbs="breadcrumbs">
        <div class="max-w-2xl mx-auto p-8 lg:p-12 bg-white min-h-screen shadow-sm border-x border-slate-100">
            <!-- Professional Header -->
            <header class="flex items-center justify-between gap-6 mb-16 pb-8 border-b border-slate-200">
                <div class="flex items-center gap-6">
                    <Link 
                        :href="asesorRoutes.index().url" 
                        class="text-slate-400 hover:text-slate-900 transition-colors"
                    >
                        <ArrowLeft :size="24" />
                    </Link>
                    <div>
                        <p class="text-[10px] font-bold uppercase tracking-widest text-slate-400 mb-1">Fuerza de Ventas</p>
                        <h1 class="text-3xl font-bold text-slate-900 tracking-tight">Registro de Asesor</h1>
                    </div>
                </div>
                <button 
                    @click="submit"
                    :disabled="form.processing"
                    class="px-8 py-3 bg-slate-900 text-white text-xs font-bold rounded-lg hover:bg-slate-800 transition-all flex items-center gap-2 disabled:opacity-50"
                >
                    <Save :size="16" />
                    {{ form.processing ? 'Registrando...' : 'Guardar' }}
                </button>
            </header>

            <form @submit.prevent="submit" class="space-y-8">
                <div class="space-y-4">
                    <div class="flex items-center gap-2 border-b border-slate-100 pb-2">
                        <UserPlus :size="18" class="text-slate-400" />
                        <h2 class="text-xs font-bold text-slate-400 uppercase tracking-widest">Información Personal</h2>
                    </div>

                    <div class="space-y-2">
                        <label class="text-[10px] font-bold text-slate-400 uppercase tracking-widest pl-1">Nombre Completo</label>
                        <input 
                            v-model="form.nombre" 
                            type="text" 
                            placeholder="Ej. Julio César López" 
                            autofocus
                            class="w-full px-4 py-3 bg-slate-50 border border-slate-200 focus:border-slate-900 focus:bg-white rounded-lg outline-none transition-all text-sm font-semibold" 
                        />
                        <div v-if="form.errors.nombre" class="text-xs text-rose-500 font-bold mt-1 pl-1">{{ form.errors.nombre }}</div>
                    </div>
                </div>

                <div class="pt-8 text-center">
                    <p class="text-[11px] text-slate-400 font-medium">Al registrar un asesor, este podrá ser asignado a nuevos créditos y cobranza.</p>
                </div>
            </form>
        </div>
    </AppLayout>
</template>
