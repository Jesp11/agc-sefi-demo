<script setup lang="ts">
import { Head, Link, useForm } from '@inertiajs/vue3';
import { 
    ArrowLeft, Phone, User, MapPin, Users, ShieldCheck, 
    Briefcase, Save, Plus, X, FileText, ChevronDown
} from 'lucide-vue-next';
import { dashboard as dashboardRoute } from '@/routes';
import customersRoutes from '@/routes/customers';
import type { BreadcrumbItem } from '@/types';
import AppLayout from '@/layouts/AppLayout.vue';

interface Direccion {
    id_direccion?: number;
    direccion: string;
    entre_calles: string;
    pivot: { tipo: 'casa' | 'trabajo' };
}

interface Referencia {
    id_referencia?: number;
    nombre: string;
    tipo: 'familiar' | 'amistad';
    parentesco: string;
    telefono: string;
    direccion: string;
    anios_relacion: number | null;
}

interface Aval {
    id_aval?: number;
    nombre: string;
    telefono: string;
    direccion: string;
    parentesco: string;
}

const props = defineProps<{
    customer: {
        id_cliente: number;
        nombre: string;
        curp: string;
        clave_elector: string;
        telefono: string;
        ocupacion: string;
        direcciones: Direccion[];
        referencias: Referencia[];
        avales: Aval[];
        id_asesor: number | null;
    };
    asesores: Array<{ id_asesor: number, nombre: string }>;
}>();

const form = useForm({
    nombre: props.customer.nombre,
    id_asesor: props.customer.id_asesor,
    curp: props.customer.curp,
    clave_elector: props.customer.clave_elector,
    telefono: props.customer.telefono,
    ocupacion: props.customer.ocupacion,
    direcciones: props.customer.direcciones,
    referencias: props.customer.referencias,
    avales: props.customer.avales,
});

const addReferencia = () => {
    form.referencias.push({
        nombre: '',
        tipo: 'familiar',
        parentesco: '',
        telefono: '',
        direccion: '',
        anios_relacion: null
    });
};

const removeReferencia = (index: number) => {
    form.referencias.splice(index, 1);
};

const addAval = () => {
    form.avales.push({
        nombre: '',
        telefono: '',
        direccion: '',
        parentesco: ''
    });
};

const removeAval = (index: number) => {
    form.avales.splice(index, 1);
};

const submit = () => {
    form.put(customersRoutes.update({ customer: props.customer.id_cliente }).url);
};

const breadcrumbs: BreadcrumbItem[] = [
    { title: 'Dashboard', href: dashboardRoute().url },
    { title: 'Clientes', href: customersRoutes.index().url },
    { title: props.customer.nombre, href: customersRoutes.show({ customer: props.customer.id_cliente }).url },
    { title: 'Edición de Expediente', href: '#' },
];
</script>

<template>
    <Head :title="`Editar - ${customer.nombre}`" />

    <AppLayout :breadcrumbs="breadcrumbs">
        <div class="max-w-5xl mx-auto p-8 lg:p-12 bg-white min-h-screen shadow-sm border-x border-slate-100">
            <!-- Professional Header -->
            <header class="flex items-center justify-between gap-6 mb-16 pb-8 border-b border-slate-200">
                <div class="flex items-center gap-6">
                    <Link 
                        :href="customersRoutes.show({ customer: customer.id_cliente }).url" 
                        class="text-slate-400 hover:text-slate-900 transition-colors"
                    >
                        <ArrowLeft :size="24" />
                    </Link>
                    <div>
                        <p class="text-[10px] font-bold uppercase tracking-widest text-slate-400 mb-1">Modificar Expediente</p>
                        <h1 class="text-3xl font-bold text-slate-900 tracking-tight">{{ customer.nombre }}</h1>
                    </div>
                </div>
                <div class="flex items-center gap-4">
                    <Link 
                        :href="customersRoutes.show({ customer: customer.id_cliente }).url"
                        class="text-xs font-bold text-slate-500 hover:text-slate-900 px-4"
                    >
                        Cancelar
                    </Link>
                    <button 
                        @click="submit"
                        :disabled="form.processing"
                        class="px-8 py-3 bg-slate-900 text-white text-xs font-bold rounded-lg hover:bg-slate-800 transition-all flex items-center gap-2 disabled:opacity-50"
                    >
                        <Save :size="16" />
                        {{ form.processing ? 'Guardando...' : 'Guardar Cambios' }}
                    </button>
                </div>
            </header>

            <div class="space-y-20">
                
                <!-- Section 1: Identidad -->
                <section>
                    <div class="flex items-center gap-2 mb-8 border-b border-slate-100 pb-2">
                        <FileText :size="18" class="text-slate-400" />
                        <h2 class="text-xs font-bold text-slate-400 uppercase tracking-widest">Datos de Identidad</h2>
                    </div>
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-8">
                        <div class="md:col-span-2 grid grid-cols-1 md:grid-cols-2 gap-8">
                            <div class="space-y-2">
                                <label class="text-[10px] font-bold text-slate-400 uppercase tracking-widest pl-1">Nombre Completo</label>
                                <input v-model="form.nombre" type="text" class="w-full px-4 py-3 bg-slate-50 border border-slate-200 focus:border-slate-900 focus:bg-white rounded-lg outline-none transition-all text-sm font-semibold" />
                            </div>
                            <div class="space-y-2">
                                <label class="text-[10px] font-bold text-slate-400 uppercase tracking-widest pl-1">Asesor Permanente</label>
                                <div class="relative">
                                    <select v-model="form.id_asesor" class="w-full px-4 py-3 bg-slate-50 border border-slate-200 focus:border-slate-900 focus:bg-white rounded-lg outline-none transition-all text-sm font-semibold appearance-none pr-10">
                                        <option value="">Seleccionar Asesor</option>
                                        <option v-for="asesor in asesores" :key="asesor.id_asesor" :value="asesor.id_asesor">
                                            {{ asesor.nombre }}
                                        </option>
                                    </select>
                                    <ChevronDown class="absolute right-4 top-1/2 -translate-y-1/2 text-slate-400 pointer-events-none" :size="16" />
                                </div>
                            </div>
                        </div>
                        <div class="space-y-2">
                            <label class="text-[10px] font-bold text-slate-400 uppercase tracking-widest pl-1">Teléfono</label>
                            <input v-model="form.telefono" type="tel" class="w-full px-4 py-3 bg-slate-50 border border-slate-200 focus:border-slate-900 focus:bg-white rounded-lg outline-none transition-all text-sm font-semibold" />
                        </div>
                        <div class="space-y-2">
                            <label class="text-[10px] font-bold text-slate-400 uppercase tracking-widest pl-1">CURP</label>
                            <input v-model="form.curp" type="text" class="w-full px-4 py-3 bg-slate-50 border border-slate-200 focus:border-slate-900 focus:bg-white rounded-lg outline-none transition-all text-sm font-semibold font-mono" />
                        </div>
                        <div class="space-y-2">
                            <label class="text-[10px] font-bold text-slate-400 uppercase tracking-widest pl-1">Clave Elector</label>
                            <input v-model="form.clave_elector" type="text" class="w-full px-4 py-3 bg-slate-50 border border-slate-200 focus:border-slate-900 focus:bg-white rounded-lg outline-none transition-all text-sm font-semibold font-mono" />
                        </div>
                        <div class="md:col-span-2 space-y-2">
                            <label class="text-[10px] font-bold text-slate-400 uppercase tracking-widest pl-1">Ocupación Actual</label>
                            <input v-model="form.ocupacion" type="text" class="w-full px-4 py-3 bg-slate-50 border border-slate-200 focus:border-slate-900 focus:bg-white rounded-lg outline-none transition-all text-sm font-semibold" />
                        </div>
                    </div>
                </section>

                <!-- Section 2: Ubicaciones -->
                <section>
                    <div class="flex items-center gap-2 mb-8 border-b border-slate-100 pb-2">
                        <MapPin :size="18" class="text-slate-400" />
                        <h2 class="text-xs font-bold text-slate-400 uppercase tracking-widest">Ubicaciones Registradas</h2>
                    </div>
                    <div class="space-y-12">
                        <div v-for="(dir, idx) in form.direcciones" :key="idx" class="pl-6 border-l-2 border-slate-200 space-y-6">
                            <p class="text-[10px] font-bold uppercase tracking-widest text-slate-400">Dirección de {{ dir.pivot.tipo }}</p>
                            <div class="grid grid-cols-1 gap-6">
                                <div class="space-y-2">
                                    <label class="text-[10px] font-bold text-slate-400 uppercase tracking-widest pl-1">Calle, Número y Colonia</label>
                                    <textarea v-model="dir.direccion" rows="2" class="w-full px-4 py-3 bg-slate-50 border border-slate-200 focus:border-slate-900 focus:bg-white rounded-lg outline-none transition-all text-sm font-semibold"></textarea>
                                </div>
                                <div class="space-y-2">
                                    <label class="text-[10px] font-bold text-slate-400 uppercase tracking-widest pl-1">Referencias Visuales</label>
                                    <input v-model="dir.entre_calles" type="text" class="w-full px-4 py-3 bg-slate-50 border border-slate-200 focus:border-slate-900 focus:bg-white rounded-lg outline-none transition-all text-sm font-semibold" />
                                </div>
                            </div>
                        </div>
                    </div>
                </section>

                <!-- Section 3: Referencias -->
                <section>
                    <div class="flex items-center justify-between mb-8 border-b border-slate-100 pb-2">
                        <div class="flex items-center gap-2">
                            <Users :size="18" class="text-slate-400" />
                            <h2 class="text-xs font-bold text-slate-400 uppercase tracking-widest">Referencias Personales</h2>
                        </div>
                        <button type="button" @click="addReferencia" class="text-[10px] font-bold uppercase tracking-widest text-slate-500 hover:text-slate-900 flex items-center gap-1 transition-colors">
                            <Plus :size="14" /> Añadir Nueva
                        </button>
                    </div>

                    <div class="grid grid-cols-1 gap-10">
                        <div v-for="(ref, idx) in form.referencias" :key="idx" class="p-6 bg-slate-50 rounded-lg relative border border-slate-100">
                            <button type="button" @click="removeReferencia(idx)" class="absolute top-4 right-4 text-slate-300 hover:text-rose-600 transition-colors">
                                <X :size="16" />
                            </button>
                            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
                                <div class="lg:col-span-2 space-y-2">
                                    <label class="text-[10px] font-bold text-slate-400 uppercase tracking-widest pl-1">Nombre</label>
                                    <input v-model="ref.nombre" type="text" class="w-full px-3 py-2 bg-white border border-slate-200 rounded outline-none text-sm font-semibold" />
                                </div>
                                <div class="space-y-2">
                                    <label class="text-[10px] font-bold text-slate-400 uppercase tracking-widest pl-1">Tipo</label>
                                    <div class="relative">
                                        <select v-model="ref.tipo" class="w-full px-3 py-2 bg-white border border-slate-200 rounded outline-none text-sm font-semibold appearance-none pr-10 focus:border-slate-900 transition-all">
                                            <option value="familiar">Familiar</option>
                                            <option value="amistad">Amistad</option>
                                        </select>
                                        <div class="absolute right-3 top-1/2 -translate-y-1/2 pointer-events-none text-slate-400">
                                            <svg xmlns="http://www.w3.org/2000/svg" width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="m6 9 6 6 6-6"/></svg>
                                        </div>
                                    </div>
                                </div>
                                <div class="space-y-2">
                                    <label class="text-[10px] font-bold text-slate-400 uppercase tracking-widest pl-1">Parentesco</label>
                                    <input v-model="ref.parentesco" type="text" class="w-full px-3 py-2 bg-white border border-slate-200 rounded outline-none text-sm font-semibold" />
                                </div>
                                <div class="space-y-2">
                                    <label class="text-[10px] font-bold text-slate-400 uppercase tracking-widest pl-1">Teléfono</label>
                                    <input v-model="ref.telefono" type="tel" class="w-full px-3 py-2 bg-white border border-slate-200 rounded outline-none text-sm font-semibold" />
                                </div>
                                <div class="space-y-2">
                                    <label class="text-[10px] font-bold text-slate-400 uppercase tracking-widest pl-1">Años Relación</label>
                                    <input v-model="ref.anios_relacion" type="number" class="w-full px-3 py-2 bg-white border border-slate-200 rounded outline-none text-sm font-semibold" />
                                </div>
                            </div>
                        </div>
                    </div>
                </section>

                <!-- Section 4: Avales -->
                <section>
                    <div class="flex items-center justify-between mb-8 border-b border-slate-100 pb-2">
                        <div class="flex items-center gap-2">
                            <ShieldCheck :size="18" class="text-slate-400" />
                            <h2 class="text-xs font-bold text-slate-400 uppercase tracking-widest">Avales y Garantías</h2>
                        </div>
                        <button type="button" @click="addAval" class="text-[10px] font-bold uppercase tracking-widest text-slate-500 hover:text-slate-900 flex items-center gap-1 transition-colors">
                            <Plus :size="14" /> Añadir Nuevo
                        </button>
                    </div>

                    <div class="grid grid-cols-1 gap-10">
                        <div v-for="(aval, idx) in form.avales" :key="idx" class="p-6 bg-slate-50 rounded-lg relative border border-slate-100">
                            <button type="button" @click="removeAval(idx)" class="absolute top-4 right-4 text-slate-300 hover:text-rose-600 transition-colors">
                                <X :size="16" />
                            </button>
                            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                                <div class="md:col-span-2 space-y-2">
                                    <label class="text-[10px] font-bold text-slate-400 uppercase tracking-widest pl-1">Nombre</label>
                                    <input v-model="aval.nombre" type="text" class="w-full px-3 py-2 bg-white border border-slate-200 rounded outline-none text-sm font-semibold" />
                                </div>
                                <div class="space-y-2">
                                    <label class="text-[10px] font-bold text-slate-400 uppercase tracking-widest pl-1">Parentesco</label>
                                    <input v-model="aval.parentesco" type="text" class="w-full px-3 py-2 bg-white border border-slate-200 rounded outline-none text-sm font-semibold" />
                                </div>
                                <div class="space-y-2">
                                    <label class="text-[10px] font-bold text-slate-400 uppercase tracking-widest pl-1">Teléfono</label>
                                    <input v-model="aval.telefono" type="tel" class="w-full px-3 py-2 bg-white border border-slate-200 rounded outline-none text-sm font-semibold" />
                                </div>
                                <div class="md:col-span-2 space-y-2">
                                    <label class="text-[10px] font-bold text-slate-400 uppercase tracking-widest pl-1">Dirección de Garantía</label>
                                    <textarea v-model="aval.direccion" rows="2" class="w-full px-3 py-2 bg-white border border-slate-200 rounded outline-none text-sm font-semibold"></textarea>
                                </div>
                            </div>
                        </div>
                    </div>
                </section>
            </div>
        </div>
    </AppLayout>
</template>

<style scoped>
input, select, textarea {
    transition: all 0.1s ease-in-out;
}
</style>
